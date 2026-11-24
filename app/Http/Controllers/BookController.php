<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\NovelVolume;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Services\BookStockService;
use App\Services\DigitalBorrowingService;
use App\Services\DigitalFileStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Book::class);

        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $publisherId = $request->query('publisher_id');
        $shelfId = $request->query('shelf_id');
        $availability = trim((string) $request->query('availability', ''));
        $collectionType = trim((string) $request->query('collection_type', ''));
        $status = trim((string) $request->query('status', ''));

        $books = Book::query()
            ->with(['category', 'shelf', 'publisher'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null && $categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', (int) $categoryId);
            })
            ->when($publisherId !== null && $publisherId !== '', function ($query) use ($publisherId) {
                $query->where('publisher_id', (int) $publisherId);
            })
            ->when($shelfId !== null && $shelfId !== '', function ($query) use ($shelfId) {
                $query->where('shelf_id', (int) $shelfId);
            })
            ->when($availability === 'available', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('collection_type', 'physical')
                        ->where(function ($stockQuery) {
                            $stockQuery->where('available_copies', '>', 0)
                                ->orWhere('stock_available', '>', 0);
                        })
                        ->orWhere(function ($sub2) {
                            $sub2->whereIn('collection_type', ['ebook', 'e-book'])
                                ->where(function ($digitalSub) {
                                    $digitalSub->whereNotNull('read_url')
                                        ->orWhereNotNull('download_url')
                                        ->orWhereNotNull('digital_file_path');
                                });
                        });
                });
            })
            ->when($availability === 'empty', function ($query) {
                $query->where('collection_type', 'physical')
                    ->where('available_copies', '<=', 0)
                    ->where('stock_available', '<=', 0);
            })
            ->when($collectionType !== '', function ($query) use ($collectionType) {
                if ($collectionType === 'physical') {
                    $query->where('collection_type', 'physical');
                } elseif (in_array($collectionType, ['ebook', 'e-book'], true)) {
                    $query->whereIn('collection_type', ['ebook', 'e-book']);
                } elseif ($collectionType === 'novel') {
                    $query->where('collection_type', 'novel');
                }
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $shelves = Shelf::orderBy('code')->get();
        $publishers = Publisher::orderBy('name')->get();

        return view('books.index', [
            'books' => $books,
            'categories' => $categories,
            'shelves' => $shelves,
            'publishers' => $publishers,
            'search' => $search,
            'categoryId' => $categoryId,
            'publisherId' => $publisherId,
            'shelfId' => $shelfId,
            'availability' => $availability,
            'collectionType' => $collectionType,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Book::class);

        $categories = Category::orderBy('name')->get();
        $shelves = Shelf::orderBy('code')->get();
        $publishers = Publisher::orderBy('name')->get();

        return view('books.form', [
            'book' => new Book([
                'collection_type' => 'physical',
                'stock_total' => 1,
                'stock_available' => 1,
                'total_copies' => 1,
                'available_copies' => 1,
                'status' => 'active',
            ]),
            'categories' => $categories,
            'shelves' => $shelves,
            'publishers' => $publishers,
            'isEdit' => false,
            'digitalAvailable' => 0,
        ]);
    }

    public function store(
        StoreBookRequest $request,
        BookStockService $stockService,
        DigitalFileStorage $fileStorage,
    ): RedirectResponse {
        Gate::authorize('create', Book::class);

        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $digitalFilePath = null;
        if ($request->hasFile('digital_file')) {
            $digitalFilePath = $fileStorage->store($request->file('digital_file'), 'digital-books');
            $data['digital_file_path'] = $digitalFilePath;
        }
        unset($data['digital_file']);

        try {
            $book = $stockService->createBook($data);
        } catch (ValidationException $exception) {
            if ($digitalFilePath) {
                Storage::disk('local')->delete($digitalFilePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('books.manage', $book)
            ->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }

    public function publicShow(
        Book $book,
        Request $request,
        DigitalBorrowingService $digitalBorrowingService,
    ): View {
        $book->load(['category', 'shelf', 'publisher']);

        $isNovel = $book->collection_type === 'novel'
            || str_contains(strtolower($book->category?->name ?? ''), 'novel');
        $user = $request->user();
        $ebookBorrowing = null;
        $ebookAvailable = 0;
        $ebookCanReadOnline = false;
        $ebookCanDownload = false;
        $hasDigitalAccessFile = false;
        $volumes = collect();

        if (! $user) {
            $request->session()->put('url.intended', route('books.show', $book));
        }

        if ($isNovel) {
            $volumes = $book->novelVolumes()
                ->where('is_published', true)
                ->withCount([
                    'borrowingItems as active_digital_loan_count' => fn (Builder $query) => $query
                        ->whereHas('borrowing', fn (Builder $borrowingQuery) => $borrowingQuery->activeDigital()),
                ])
                ->orderBy('volume_number')
                ->get();

            if ($user && $volumes->isNotEmpty()) {
                $activeVolumeItems = BorrowingItem::query()
                    ->with('borrowing')
                    ->whereIn('novel_volume_id', $volumes->modelKeys())
                    ->whereHas('borrowing', fn (Builder $query) => $query
                        ->activeDigital()
                        ->where('borrower_user_id', $user->id))
                    ->get()
                    ->keyBy('novel_volume_id');

                foreach ($volumes as $volume) {
                    $volume->setAttribute(
                        'my_active_borrowing',
                        $activeVolumeItems->get($volume->id)?->borrowing,
                    );
                }
            }

            foreach ($volumes as $volume) {
                $volume->setAttribute(
                    'digital_download_available',
                    $volume->digital_lending_allowed
                        && filled($volume->digital_file_path)
                        && Storage::disk('local')->exists($volume->digital_file_path),
                );
                $volume->setAttribute(
                    'digital_access_available',
                    max(0, (int) $volume->digital_license_count - (int) $volume->active_digital_loan_count),
                );
            }
            $hasDigitalAccessFile = $volumes->contains(fn (NovelVolume $volume) => $volume->digital_download_available);
        } else {
            $ebookAvailable = $digitalBorrowingService->availableLicensesForBook($book);
            $ebookBorrowing = $user
                ? $digitalBorrowingService->activeBorrowingForBook($book, $user)
                : null;
            $fileIsAvailable = $book->digital_lending_allowed
                && filled($book->digital_file_path)
                && Storage::disk('local')->exists($book->digital_file_path);
            $ebookCanReadOnline = ($fileIsAvailable
                && strtolower(pathinfo($book->digital_file_path, PATHINFO_EXTENSION)) === 'pdf')
                || filled($book->read_url);
            $ebookCanDownload = $fileIsAvailable || filled($book->download_url);
            $hasDigitalAccessFile = $fileIsAvailable || filled($book->read_url) || filled($book->download_url);
        }

        return view('books.detail', [
            'book' => $book,
            'isNovel' => $isNovel,
            'volumes' => $volumes,
            'description' => $book->getRawOriginal($isNovel ? 'synopsis' : 'description') ?? $book->synopsis,
            'backLabel' => $isNovel ? 'Kembali ke Novel' : 'Kembali ke E-book',
            'backGenre' => $isNovel ? 'novel-fiksi' : 'keilmuan-edukasi',
            'isFavorited' => $user?->hasFavorited($book) ?? false,
            'ebookAvailable' => $ebookAvailable,
            'ebookBorrowing' => $ebookBorrowing,
            'ebookCanReadOnline' => $ebookCanReadOnline,
            'ebookCanDownload' => $ebookCanDownload,
            'hasDigitalAccessFile' => $hasDigitalAccessFile,
        ]);
    }

    public function show(Request $request, Book $book): View
    {
        Gate::authorize('view', $book);
        abort_unless($request->user()?->isAdmin() || $request->user()?->isPetugas(), 403);

        $book->load([
            'category',
            'shelf',
            'publisher',
            'borrowingItems' => function ($query) {
                $query->with(['borrowing.member', 'borrowing.staff'])->latest()->limit(10);
            },
        ]);

        return view('books.show', [
            'book' => $book,
        ]);
    }

    public function edit(Book $book, DigitalBorrowingService $digitalBorrowingService): View
    {
        Gate::authorize('update', $book);

        $categories = Category::orderBy('name')->get();
        $shelves = Shelf::orderBy('code')->get();
        $publishers = Publisher::orderBy('name')->get();

        return view('books.form', [
            'book' => $book,
            'categories' => $categories,
            'shelves' => $shelves,
            'publishers' => $publishers,
            'isEdit' => true,
            'digitalAvailable' => $digitalBorrowingService->availableLicensesForBook($book),
        ]);
    }

    public function update(
        UpdateBookRequest $request,
        Book $book,
        BookStockService $stockService,
        DigitalFileStorage $fileStorage,
    ): RedirectResponse {
        Gate::authorize('update', $book);

        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $digitalFilePath = null;
        if ($request->hasFile('digital_file')) {
            $digitalFilePath = $fileStorage->store($request->file('digital_file'), 'digital-books');
            $data['digital_file_path'] = $digitalFilePath;
        }
        unset($data['digital_file']);

        try {
            $updatedBook = $stockService->updateBook($book, $data);
        } catch (ValidationException $exception) {
            if ($digitalFilePath) {
                Storage::disk('local')->delete($digitalFilePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('books.manage', $updatedBook)
            ->with('success', 'Data buku dan inventaris berhasil diperbarui.');
    }

    public function destroy(Book $book, BookStockService $stockService): RedirectResponse
    {
        Gate::authorize('delete', $book);

        $stockService->archiveBook($book);

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil diarsipkan.');
    }
}

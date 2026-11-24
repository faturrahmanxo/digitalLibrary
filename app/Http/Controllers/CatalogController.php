<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\NovelVolume;
use App\Services\DigitalBorrowingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $collectionType = trim((string) $request->query('collection_type', $request->query('type', '')));
        $author = trim((string) $request->query('author', ''));
        $isbn = trim((string) $request->query('isbn', ''));
        $publisher = trim((string) $request->query('publisher', ''));
        $year = $request->query('year');
        $categoryId = $request->query('category_id');
        $genre = trim((string) $request->query('genre', ''));
        $availability = trim((string) $request->query('availability', ''));
        $sort = trim((string) $request->query('sort', 'newest'));

        $query = Book::query()->with(['category', 'shelf', 'publisher']);

        // Only active/published books visible in public catalog
        $query->where(function ($q) {
            $q->where('status', Book::STATUS_ACTIVE)
                ->orWhereNull('status');
        });

        // Filter by collection type: physical vs ebook
        if ($collectionType === 'physical') {
            $query->whereIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik']);
        } elseif ($collectionType === 'ebook' || $collectionType === 'e-book') {
            $query->whereNotIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik']);
        }

        // Genre query filter support
        if ($genre !== '') {
            $genreMap = [
                'novel-fiksi' => ['Sastra & Fiksi', 'Novel'],
                'keilmuan-edukasi' => ['Sains & Matematika', 'Keilmuan & Edukasi', 'Literasi Digital', 'Pendidikan'],
                'sejarah-budaya' => ['Sejarah'],
                'sastra-klasik' => ['Sastra & Fiksi', 'Novel'],
                'teknologi-pemrograman' => ['Teknologi & Pemrograman'],
                'ekonomi-manajemen' => ['Ekonomi & Manajemen'],
            ];

            if (isset($genreMap[$genre])) {
                $categoryNames = $genreMap[$genre];
                $query->whereHas('category', function ($categoryQuery) use ($categoryNames) {
                    $categoryQuery->where(function ($matches) use ($categoryNames) {
                        foreach ($categoryNames as $categoryName) {
                            $matches->orWhere('name', 'like', "%{$categoryName}%");
                        }
                    });
                });
            } else {
                $slugSearch = str_replace('-', ' ', $genre);
                $query->where(function ($sub) use ($slugSearch) {
                    $sub->whereHas('category', fn ($c) => $c->where('name', 'like', "%{$slugSearch}%"))
                        ->orWhere('title', 'like', "%{$slugSearch}%");
                });
            }
        }

        // Global search term
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%")
                    ->orWhereHas('publisher', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        // Specific field filters (Gunadarma-style advanced filters)
        if ($author !== '') {
            $query->where('author', 'like', "%{$author}%");
        }

        if ($isbn !== '') {
            $query->where('isbn', 'like', "%{$isbn}%");
        }

        if ($publisher !== '') {
            $query->where(function ($sub) use ($publisher) {
                $sub->where('publisher', 'like', "%{$publisher}%")
                    ->orWhereHas('publisher', fn ($pq) => $pq->where('name', 'like', "%{$publisher}%"));
            });
        }

        if ($year !== null && $year !== '') {
            $query->where('publication_year', (int) $year);
        }

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        if ($availability === 'available') {
            $query->where(function ($sub) {
                $sub->where('available_copies', '>', 0)
                    ->orWhere('stock_available', '>', 0)
                    ->orWhereNotIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik']);
            });
        } elseif ($availability === 'empty') {
            $query->whereIn('collection_type', [Book::COLLECTION_TYPE_PHYSICAL, 'fisik'])
                ->where(function ($sub) {
                    $sub->where('available_copies', '<=', 0)
                        ->orWhere('stock_available', '<=', 0);
                });
        }

        if ($sort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'title_desc') {
            $query->orderBy('title', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('stock_total', 'desc')->orderBy('total_copies', 'desc');
        } elseif ($sort === 'year_desc') {
            $query->orderBy('publication_year', 'desc');
        } else {
            $query->latest('id');
        }

        $books = $query->paginate(15)->withQueryString();

        $categories = Category::withCount('books')->orderBy('name')->get();

        $years = Book::select('publication_year')
            ->distinct()
            ->whereNotNull('publication_year')
            ->orderByDesc('publication_year')
            ->pluck('publication_year');

        $userFavoriteIds = [];
        if (Auth::check()) {
            $userFavoriteIds = Favorite::where('user_id', Auth::id())
                ->pluck('book_id')
                ->toArray();
        }

        return view('catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'years' => $years,
            'search' => $search,
            'collectionType' => $collectionType,
            'author' => $author,
            'isbn' => $isbn,
            'publisher' => $publisher,
            'year' => $year ? (int) $year : null,
            'categoryId' => $categoryId ? (int) $categoryId : null,
            'genre' => $genre,
            'availability' => $availability,
            'sort' => $sort,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }

    public function show(
        Book $book,
        Request $request,
        DigitalBorrowingService $digitalBorrowingService,
    ): View {
        if ($book->trashed() || ($book->status && $book->status === Book::STATUS_INACTIVE)) {
            abort(404);
        }

        $book->load(['category', 'shelf', 'publisher']);

        $user = $request->user();
        if (! $user) {
            $request->session()->put('url.intended', route('books.show', $book));
        }

        $isPhysical = $book->isPhysical();
        $isFavorited = $user ? $user->hasFavorited($book) : false;

        $userBorrowing = null;
        if ($user && $isPhysical) {
            $userBorrowing = Borrowing::query()
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('borrower_user_id', $user->id);
                    if ($user->email) {
                        $q->orWhereHas('member', fn ($mq) => $mq->where('email', $user->email));
                    }
                })
                ->where(function ($q) use ($book) {
                    $q->where('book_id', $book->id)
                        ->orWhereHas('items', fn ($iq) => $iq->where('book_id', $book->id));
                })
                ->whereIn('status', [
                    Borrowing::STATUS_PENDING,
                    Borrowing::STATUS_APPROVED,
                    Borrowing::STATUS_BORROWED,
                ])
                ->latest('id')
                ->first();
        }

        $isNovel = ! $isPhysical && (
            $book->collection_type === 'novel'
            || strcasecmp($book->category?->name ?? '', 'Novel') === 0
            || str_contains(strtolower($book->category?->name ?? ''), 'novel')
        );

        $volumes = collect();
        $ebookBorrowing = null;
        $ebookAvailable = 0;
        $ebookCanReadOnline = false;
        $ebookCanDownload = false;
        $hasDigitalAccessFile = false;

        if (! $isPhysical) {
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
        }

        $readLink = ! $isPhysical ? $book->getReadLink() : null;
        $downloadLink = ! $isPhysical ? $book->getDownloadLink() : null;

        return view('books.detail', [
            'book' => $book,
            'isPhysical' => $isPhysical,
            'isNovel' => $isNovel,
            'isFavorited' => $isFavorited,
            'userBorrowing' => $userBorrowing,
            'volumes' => $volumes,
            'readLink' => $readLink,
            'downloadLink' => $downloadLink,
            'description' => $book->synopsis ?: $book->description,
            'backLabel' => $isPhysical ? 'Kembali ke Katalog' : ($isNovel ? 'Kembali ke Novel' : 'Kembali ke E-book'),
            'backGenre' => $isPhysical ? '' : ($isNovel ? 'novel-fiksi' : 'keilmuan-edukasi'),
            'ebookBorrowing' => $ebookBorrowing,
            'ebookAvailable' => $ebookAvailable,
            'ebookCanReadOnline' => $ebookCanReadOnline,
            'ebookCanDownload' => $ebookCanDownload,
            'hasDigitalAccessFile' => $hasDigitalAccessFile,
        ]);
    }
}

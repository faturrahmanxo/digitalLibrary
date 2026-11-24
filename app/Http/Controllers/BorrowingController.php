<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Services\BorrowingService;
use App\Services\ReturnService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Borrowing::class);

        $search = trim((string) $request->query('q', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $todayJakarta = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $borrowings = Borrowing::query()
            ->with(['member', 'staff', 'items'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('transaction_code', 'like', "%{$search}%")
                        ->orWhere('member_name_snapshot', 'like', "%{$search}%")
                        ->orWhereHas('member', fn ($m) => $m->withTrashed()->where('member_code', 'like', "%{$search}%"));
                });
            })
            ->when($statusFilter === Borrowing::STATUS_BORROWED, fn ($q) => $q->where('status', Borrowing::STATUS_BORROWED))
            ->when($statusFilter === Borrowing::STATUS_RETURNED, fn ($q) => $q->where('status', Borrowing::STATUS_RETURNED))
            ->when($statusFilter === 'overdue', function ($q) use ($todayJakarta) {
                $q->where('status', Borrowing::STATUS_BORROWED)
                    ->whereDate('due_at', '<', $todayJakarta);
            })
            ->when($request->user()?->isAnggota(), function ($q) use ($request) {
                $q->whereHas('member', fn ($m) => $m->where('email', $request->user()->email));
            })
            ->latest('borrowed_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $availableBooks = Book::with('category')
            ->where('stock_available', '>', 0)
            ->orderBy('title')
            ->get();

        $members = ($request->user()?->isAdmin() || $request->user()?->isPetugas())
            ? Member::orderBy('name')->get()
            : collect();

        return view('borrowings.index', [
            'borrowings' => $borrowings,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'availableBooks' => $availableBooks,
            'members' => $members,
        ]);
    }

    public function create(Request $request): View
    {
        if ($request->user()?->isAnggota()) {
            abort(403, 'Akses formulir sirkulasi staf hanya untuk Admin dan Petugas.');
        }

        Gate::authorize('create', Borrowing::class);

        $members = Member::orderBy('name')->get();
        $books = Book::with('category')->orderBy('title')->get();

        $previewBorrowDate = Carbon::now('Asia/Jakarta');
        $previewDueDate = $previewBorrowDate->copy()->addDays(7);

        return view('borrowings.create', [
            'members' => $members,
            'books' => $books,
            'requestKey' => (string) Str::uuid(),
            'previewBorrowDate' => $previewBorrowDate,
            'previewDueDate' => $previewDueDate,
        ]);
    }

    public function store(StoreBorrowingRequest $request, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('create', Borrowing::class);

        $durationDays = (int) ($request->validated('duration_days') ?: 7);

        $borrowing = $borrowingService->borrow(
            staff: $request->user(),
            memberId: (int) $request->validated('member_id'),
            items: $request->validated('items'),
            requestKey: (string) $request->validated('request_key'),
            durationDays: $durationDays,
        );

        $successMsg = $request->user()?->isAnggota()
            ? 'Peminjaman buku berhasil diajukan! Harap kembalikan tepat waktu sebelum ' . $borrowing->due_at?->format('d M Y') . '.'
            : 'Peminjaman berhasil dicatat. Stok buku telah diperbarui.';

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', $successMsg);
    }

    public function borrowBook(Request $request, Book $book, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('create', Borrowing::class);

        $user = $request->user();

        if ($book->stock_available < 1) {
            return back()->with('error', "Stok buku \"{$book->title}\" sedang tidak tersedia untuk dipinjam.");
        }

        if ($user->isAnggota()) {
            $member = Member::firstOrCreate(
                ['email' => $user->email],
                [
                    'member_code' => sprintf('AGT-%05d', Member::withTrashed()->count() + 1),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => '081200000000',
                    'address' => 'Anggota Perpustakaan',
                ]
            );
            $memberId = $member->id;
        } else {
            $memberId = (int) $request->input('member_id', Member::first()?->id);
            if (! $memberId) {
                return back()->with('error', 'Belum ada data anggota terdaftar.');
            }
        }

        $requestKey = (string) ($request->input('request_key') ?: Str::uuid());
        $durationDays = (int) ($request->input('duration_days') ?: 7);

        try {
            $borrowing = $borrowingService->borrow(
                staff: $user,
                memberId: $memberId,
                items: [
                    [
                        'book_id' => $book->id,
                        'quantity' => 1,
                    ],
                ],
                requestKey: $requestKey,
                durationDays: $durationDays,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', "Buku \"{$book->title}\" berhasil dipinjam! Harap kembalikan buku sebelum {$borrowing->due_at?->format('d M Y')}.");
    }

    public function show(Borrowing $borrowing): View
    {
        Gate::authorize('view', $borrowing);

        $borrowing->load(['items.book', 'items.novelVolume', 'member', 'staff', 'returnedByUser', 'borrowerUser']);

        return view('borrowings.show', [
            'borrowing' => $borrowing,
        ]);
    }

    public function returnBorrowing(Request $request, Borrowing $borrowing, ReturnService $returnService): RedirectResponse
    {
        Gate::authorize('return', $borrowing);

        $returnService->returnBorrowing($borrowing, $request->user());

        $message = $borrowing->isDigital()
            ? 'Akses digital berhasil dikembalikan. Kuota lisensi tersedia kembali.'
            : 'Semua buku pada transaksi ini berhasil dikembalikan.';

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', $message);
    }
}

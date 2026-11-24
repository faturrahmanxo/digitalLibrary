<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Services\BorrowingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Borrowing::class);

        $search = trim((string) $request->query('q', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $memberFilter = $request->query('member_id');
        $todayJakarta = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $borrowings = Borrowing::query()
            ->with(['member', 'staff', 'approver', 'handoverStaff', 'returnStaff', 'items.book', 'book'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('transaction_code', 'like', "%{$search}%")
                        ->orWhere('member_name_snapshot', 'like', "%{$search}%")
                        ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn ($i) => $i->where('book_title_snapshot', 'like', "%{$search}%"))
                        ->orWhereHas('member', fn ($m) => $m->withTrashed()->where('member_code', 'like', "%{$search}%"));
                });
            })
            ->when($statusFilter === Borrowing::STATUS_PENDING, fn ($q) => $q->where('status', Borrowing::STATUS_PENDING))
            ->when($statusFilter === Borrowing::STATUS_APPROVED, fn ($q) => $q->where('status', Borrowing::STATUS_APPROVED))
            ->when($statusFilter === Borrowing::STATUS_BORROWED, fn ($q) => $q->where('status', Borrowing::STATUS_BORROWED))
            ->when($statusFilter === Borrowing::STATUS_RETURNED, fn ($q) => $q->where('status', Borrowing::STATUS_RETURNED))
            ->when($statusFilter === Borrowing::STATUS_REJECTED, fn ($q) => $q->where('status', Borrowing::STATUS_REJECTED))
            ->when($statusFilter === Borrowing::STATUS_CANCELLED, fn ($q) => $q->where('status', Borrowing::STATUS_CANCELLED))
            ->when($statusFilter === 'overdue', function ($q) use ($todayJakarta) {
                $q->where('status', Borrowing::STATUS_BORROWED)
                    ->where(function ($sub) use ($todayJakarta) {
                        $sub->whereDate('due_at', '<', $todayJakarta)
                            ->orWhereDate('due_date', '<', $todayJakarta);
                    });
            })
            ->when(filled($memberFilter), fn ($q) => $q->where('member_id', $memberFilter))
            ->when($request->user()?->isAnggota(), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('user_id', $request->user()->id)
                        ->orWhere('borrower_user_id', $request->user()->id)
                        ->orWhereHas('member', fn ($m) => $m->where('email', $request->user()->email));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $availableBooks = Book::with('category')
            ->where('collection_type', '!=', Book::COLLECTION_TYPE_EBOOK)
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
        $books = Book::with('category')
            ->where('collection_type', '!=', Book::COLLECTION_TYPE_EBOOK)
            ->orderBy('title')
            ->get();

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

        $user = $request->user();

        // If user is Anggota, route to member loan request (Pending status)
        if ($user?->isAnggota()) {
            $items = $request->validated('items');
            $firstBookId = (int) ($items[0]['book_id'] ?? $request->input('book_id'));

            try {
                $borrowing = $borrowingService->requestBorrow(
                    user: $user,
                    bookId: $firstBookId,
                    requestKey: (string) $request->validated('request_key'),
                    notes: $request->input('notes'),
                );
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }

            return redirect()
                ->route('borrowings.show', $borrowing)
                ->with('success', 'Pengajuan peminjaman buku berhasil diajukan dan menunggu persetujuan petugas perpustakaan.');
        }

        // Direct borrow for Staff / Admin
        $durationDays = (int) ($request->validated('duration_days') ?: 7);

        try {
            $borrowing = $borrowingService->borrow(
                staff: $user,
                memberId: (int) $request->validated('member_id'),
                items: $request->validated('items'),
                requestKey: (string) $request->validated('request_key'),
                durationDays: $durationDays,
                notes: $request->input('notes'),
                isDirectBorrow: true
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', 'Peminjaman berhasil dicatat. Stok buku telah diperbarui.');
    }

    public function borrowBook(Request $request, Book $book, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('create', Borrowing::class);

        $user = $request->user();

        if ($book->collection_type === Book::COLLECTION_TYPE_EBOOK && $book->total_copies === 0 && $book->stock_total === 0) {
            return back()->with('error', 'Peminjaman fisik tidak berlaku untuk e-book murni tanpa eksemplar fisik.');
        }

        if ($book->available_copies < 1) {
            return back()->with('error', "Stok buku \"{$book->title}\" sedang tidak tersedia untuk dipinjam.");
        }

        if ($user->isAnggota()) {
            $member = Member::withTrashed()->where('email', $user->email)->first();
            if ($member && $member->trashed()) {
                return back()->with('error', 'Akun anggota Anda tidak aktif atau diarsipkan.');
            }

            $requestKey = (string) ($request->input('request_key') ?: Str::uuid());

            try {
                $borrowing = $borrowingService->requestBorrow(
                    user: $user,
                    bookId: $book->id,
                    requestKey: $requestKey,
                    notes: $request->input('notes')
                );
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }

            return redirect()
                ->route('borrowings.show', $borrowing)
                ->with('success', "Pengajuan peminjaman untuk buku \"{$book->title}\" berhasil dikirim dan menunggu persetujuan petugas.");
        }

        // Walk-in direct borrow by Staff/Admin
        $memberId = (int) $request->input('member_id', Member::first()?->id);
        if (! $memberId) {
            return back()->with('error', 'Belum ada data anggota terdaftar.');
        }

        $requestKey = (string) ($request->input('request_key') ?: Str::uuid());
        $durationDays = (int) ($request->input('duration_days') ?: 7);

        try {
            $borrowing = $borrowingService->directBorrow(
                staff: $user,
                memberId: $memberId,
                bookOrId: $book->id,
                requestKey: $requestKey,
                durationDays: $durationDays,
                notes: $request->input('notes')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', "Buku \"{$book->title}\" berhasil dipinjam! Harap kembalikan buku sebelum {$borrowing->due_date?->format('d M Y')}.");
    }

    public function show(Borrowing $borrowing): View
    {
        Gate::authorize('view', $borrowing);

        $borrowing->load([
            'items.book',
            'items.novelVolume',
            'book',
            'member',
            'staff',
            'approver',
            'handoverStaff',
            'returnStaff',
            'returnedByUser',
            'borrowerUser',
            'user',
        ]);

        return view('borrowings.show', [
            'borrowing' => $borrowing,
        ]);
    }

    public function approve(Request $request, Borrowing $borrowing, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('approve', $borrowing);

        try {
            $borrowingService->approve($borrowing, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', 'Pengajuan peminjaman berhasil disetujui. 1 eksemplar buku telah direservasi untuk peminjam.');
    }

    public function handover(Request $request, Borrowing $borrowing, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('handover', $borrowing);

        $durationDays = (int) ($request->input('duration_days') ?: 7);

        try {
            $borrowingService->handover($borrowing, $request->user(), $durationDays);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', "Buku telah diserahkan kepada peminjam. Masa peminjaman aktif selama {$durationDays} hari kalender.");
    }

    public function reject(Request $request, Borrowing $borrowing, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('reject', $borrowing);

        $reason = $request->input('rejection_reason') ?: $request->input('reason');

        try {
            $borrowingService->reject($borrowing, $request->user(), $reason);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', 'Pengajuan peminjaman berhasil ditolak.');
    }

    public function cancel(Request $request, Borrowing $borrowing, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('cancel', $borrowing);

        $reason = $request->input('reason') ?: $request->input('notes');

        try {
            $borrowingService->cancel($borrowing, $request->user(), $reason);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', 'Transaksi peminjaman berhasil dibatalkan.');
    }

    public function returnBorrowing(Request $request, Borrowing $borrowing, BorrowingService $borrowingService): RedirectResponse
    {
        Gate::authorize('return', $borrowing);

        try {
            $borrowingService->returnBorrowing($borrowing, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $message = $borrowing->isDigital()
            ? 'Akses digital berhasil dikembalikan. Kuota lisensi tersedia kembali.'
            : 'Semua buku pada transaksi ini berhasil dikembalikan. Stok tersedia telah dipulihkan.';

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', $message);
    }
}

<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    /**
     * Submit an online borrowing request by a library member (Pending status).
     * Pending does NOT decrement available copies or reserve stock.
     */
    public function requestBorrow(User $user, int $bookId, ?string $requestKey = null, ?string $notes = null): Borrowing
    {
        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'user' => 'Akun pengguna Anda tidak aktif atau telah diarsipkan.',
            ]);
        }

        $member = Member::withTrashed()->where('email', $user->email)->first();
        if ($member && $member->trashed()) {
            throw ValidationException::withMessages([
                'member' => 'Akun anggota Anda tidak aktif atau telah diarsipkan.',
            ]);
        }

        if (! $member) {
            $member = Member::create([
                'member_code' => sprintf('AGT-%05d', Member::withTrashed()->count() + 1),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081200000000',
                'address' => 'Anggota Perpustakaan',
            ]);
        }

        $requestKey = trim((string) ($requestKey ?: Str::uuid()));
        $payloadHash = hash('sha256', json_encode([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'book_id' => $bookId,
            'action' => 'request',
        ], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($user, $member, $bookId, $requestKey, $payloadHash, $notes) {
            /** @var Book $book */
            $book = Book::withTrashed()->lockForUpdate()->find($bookId);
            if (! $book || $book->trashed()) {
                throw ValidationException::withMessages([
                    'book_id' => 'Buku tidak ditemukan atau telah diarsipkan.',
                ]);
            }

            if ($book->collection_type === Book::COLLECTION_TYPE_EBOOK && $book->total_copies === 0 && $book->stock_total === 0) {
                throw ValidationException::withMessages([
                    'book_id' => 'Peminjaman fisik tidak berlaku untuk koleksi e-book murni tanpa eksemplar fisik.',
                ]);
            }

            if (! $book->isActive()) {
                throw ValidationException::withMessages([
                    'book_id' => 'Buku sedang tidak aktif untuk dipinjam.',
                ]);
            }

            if ($book->available_copies < 1) {
                throw ValidationException::withMessages([
                    'book_id' => "Stok buku \"{$book->title}\" sedang tidak tersedia untuk dipinjam.",
                ]);
            }

            // Check duplicate active borrowing (pending, approved, or borrowed)
            $existingActive = Borrowing::where('member_id', $member->id)
                ->where(function ($query) use ($bookId) {
                    $query->where('book_id', $bookId)
                        ->orWhereHas('items', fn ($q) => $q->where('book_id', $bookId));
                })
                ->whereIn('status', [
                    Borrowing::STATUS_PENDING,
                    Borrowing::STATUS_APPROVED,
                    Borrowing::STATUS_BORROWED,
                ])
                ->exists();

            if ($existingActive) {
                throw ValidationException::withMessages([
                    'book_id' => "Anda sudah memiliki pengajuan tertunda atau pinjaman aktif untuk buku \"{$book->title}\".",
                ]);
            }

            $borrowing = Borrowing::create([
                'transaction_code' => $this->generateTransactionCode(),
                'book_id' => $book->id,
                'staff_id' => null,
                'borrower_user_id' => $user->id,
                'user_id' => $user->id,
                'approved_by' => null,
                'approved_at' => null,
                'handed_over_by' => null,
                'returned_by' => null,
                'member_id' => $member->id,
                'member_name_snapshot' => $member->name,
                'loan_type' => Borrowing::LOAN_TYPE_PHYSICAL,
                'borrowed_at' => null,
                'due_at' => null,
                'due_date' => null,
                'returned_at' => null,
                'status' => Borrowing::STATUS_PENDING,
                'request_key' => $requestKey,
                'payload_hash' => $payloadHash,
                'notes' => $notes,
                'rejection_reason' => null,
                'is_direct_borrow' => false,
            ]);

            BorrowingItem::create([
                'borrowing_id' => $borrowing->id,
                'book_id' => $book->id,
                'book_title_snapshot' => $book->title,
                'quantity' => 1,
            ]);

            // Invariant assertion: pending does not reduce available stock
            $this->assertStockInvariants($book);

            return $borrowing->load(['book', 'items.book', 'member', 'user']);
        }, attempts: 3);
    }

    /**
     * Approve a pending borrowing request.
     * Atomically reserves 1 copy of the book (Available decreases by 1, Reserved increases by 1).
     */
    public function approve(Borrowing $borrowing, User $staff): Borrowing
    {
        $this->ensureStaffPermission($staff);

        return DB::transaction(function () use ($borrowing, $staff) {
            /** @var Borrowing $lockedBorrowing */
            $lockedBorrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            if ($lockedBorrowing->status === Borrowing::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah disetujui sebelumnya.',
                ]);
            }

            if ($lockedBorrowing->status !== Borrowing::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => "Hanya pengajuan berstatus pending yang dapat disetujui. Status saat ini: {$lockedBorrowing->status}.",
                ]);
            }

            $bookId = $lockedBorrowing->book_id ?? $lockedBorrowing->items()->first()?->book_id;
            if (! $bookId) {
                throw ValidationException::withMessages([
                    'book' => 'Data buku untuk transaksi ini tidak ditemukan.',
                ]);
            }

            /** @var Book $book */
            $book = Book::withTrashed()->lockForUpdate()->findOrFail($bookId);
            if ($book->trashed()) {
                throw ValidationException::withMessages([
                    'book' => "Buku \"{$book->title}\" telah diarsipkan dan tidak dapat disetujui.",
                ]);
            }

            if ($book->available_copies < 1) {
                throw ValidationException::withMessages([
                    'stock' => "Stok tersedia buku \"{$book->title}\" tidak mencukupi untuk disetujui.",
                ]);
            }

            // Reserve 1 copy atomically
            $newAvailable = $book->available_copies - 1;
            $newReserved = $book->reserved_copies + 1;

            $book->update([
                'available_copies' => $newAvailable,
                'stock_available' => $newAvailable,
                'reserved_copies' => $newReserved,
            ]);

            $this->assertStockInvariants($book);

            $nowUtc = Carbon::now('UTC');
            $lockedBorrowing->update([
                'status' => Borrowing::STATUS_APPROVED,
                'approved_by' => $staff->id,
                'approved_at' => $nowUtc,
                'staff_id' => $lockedBorrowing->staff_id ?: $staff->id,
            ]);

            return $lockedBorrowing->load(['book', 'items.book', 'member', 'user', 'approver']);
        }, attempts: 3);
    }

    /**
     * Handover book to member upon physical pickup (Approved -> Borrowed).
     * Shifts reserved copy to borrowed without double-decrementing available copies.
     * Starts calendar loan period (default 7 days).
     */
    public function handover(Borrowing $borrowing, User $staff, int $durationDays = 7): Borrowing
    {
        $this->ensureStaffPermission($staff);
        $durationDays = max(1, min(90, $durationDays ?: 7));

        return DB::transaction(function () use ($borrowing, $staff, $durationDays) {
            /** @var Borrowing $lockedBorrowing */
            $lockedBorrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            if ($lockedBorrowing->status === Borrowing::STATUS_BORROWED) {
                throw ValidationException::withMessages([
                    'status' => 'Buku pada transaksi ini sudah diserahkan sebelumnya.',
                ]);
            }

            if ($lockedBorrowing->status !== Borrowing::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'status' => "Hanya transaksi berstatus approved (disetujui) yang dapat diserahkan. Status saat ini: {$lockedBorrowing->status}.",
                ]);
            }

            $bookId = $lockedBorrowing->book_id ?? $lockedBorrowing->items()->first()?->book_id;
            if (! $bookId) {
                throw ValidationException::withMessages([
                    'book' => 'Data buku untuk transaksi ini tidak ditemukan.',
                ]);
            }

            /** @var Book $book */
            $book = Book::withTrashed()->lockForUpdate()->findOrFail($bookId);

            // Shift reserved to borrowed; available stock remains untouched (already decremented at approval)
            $newReserved = max(0, $book->reserved_copies - 1);
            $newBorrowed = $book->borrowed_copies + 1;

            $book->update([
                'reserved_copies' => $newReserved,
                'borrowed_copies' => $newBorrowed,
            ]);

            $this->assertStockInvariants($book);

            $borrowedAtUtc = Carbon::now('UTC');
            $dueAtJakarta = Carbon::now('Asia/Jakarta')->startOfDay()->addDays($durationDays)->format('Y-m-d');

            $lockedBorrowing->update([
                'status' => Borrowing::STATUS_BORROWED,
                'handed_over_by' => $staff->id,
                'staff_id' => $staff->id,
                'borrowed_at' => $borrowedAtUtc,
                'due_at' => $dueAtJakarta,
                'due_date' => $dueAtJakarta,
            ]);

            return $lockedBorrowing->load(['book', 'items.book', 'member', 'user', 'handoverStaff']);
        }, attempts: 3);
    }

    /**
     * Reject a pending borrowing request.
     * Rejection of Pending does NOT alter stock.
     */
    public function reject(Borrowing $borrowing, User $staff, ?string $reason = null): Borrowing
    {
        $this->ensureStaffPermission($staff);

        return DB::transaction(function () use ($borrowing, $staff, $reason) {
            /** @var Borrowing $lockedBorrowing */
            $lockedBorrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            if ($lockedBorrowing->status === Borrowing::STATUS_REJECTED) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah ditolak sebelumnya.',
                ]);
            }

            if ($lockedBorrowing->status !== Borrowing::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => "Hanya pengajuan berstatus pending yang dapat ditolak. Status saat ini: {$lockedBorrowing->status}.",
                ]);
            }

            $nowUtc = Carbon::now('UTC');
            $lockedBorrowing->update([
                'status' => Borrowing::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'approved_by' => $staff->id,
                'approved_at' => $nowUtc,
            ]);

            return $lockedBorrowing->load(['book', 'items.book', 'member', 'user']);
        }, attempts: 3);
    }

    /**
     * Cancel a borrowing request or reservation.
     * - Pending: member or staff can cancel without stock changes.
     * - Approved: staff can cancel and atomically release reserved copy back to available stock.
     */
    public function cancel(Borrowing $borrowing, User $actor, ?string $reason = null): Borrowing
    {
        return DB::transaction(function () use ($borrowing, $actor, $reason) {
            /** @var Borrowing $lockedBorrowing */
            $lockedBorrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            if ($lockedBorrowing->status === Borrowing::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            if ($lockedBorrowing->status === Borrowing::STATUS_PENDING) {
                // Ensure actor is owner or staff
                if ($actor->isAnggota()) {
                    $isOwner = (int) $lockedBorrowing->user_id === $actor->id
                        || (int) $lockedBorrowing->borrower_user_id === $actor->id
                        || $lockedBorrowing->member?->email === $actor->email;

                    if (! $isOwner) {
                        throw ValidationException::withMessages([
                            'status' => 'Anda tidak berhak membatalkan pengajuan ini.',
                        ]);
                    }
                }

                $lockedBorrowing->update([
                    'status' => Borrowing::STATUS_CANCELLED,
                    'notes' => $reason ?? 'Pengajuan dibatalkan oleh pemohon.',
                    'rejection_reason' => $reason,
                ]);

                return $lockedBorrowing->load(['book', 'items.book', 'member', 'user']);
            }

            if ($lockedBorrowing->status === Borrowing::STATUS_APPROVED) {
                // Only staff can cancel approved reservation
                if ($actor->isAnggota()) {
                    throw ValidationException::withMessages([
                        'status' => 'Reservasi yang telah disetujui hanya dapat dibatalkan oleh petugas perpustakaan.',
                    ]);
                }

                $bookId = $lockedBorrowing->book_id ?? $lockedBorrowing->items()->first()?->book_id;
                if ($bookId) {
                    /** @var Book $book */
                    $book = Book::withTrashed()->lockForUpdate()->findOrFail($bookId);

                    // Release reserved stock back to available
                    $newReserved = max(0, $book->reserved_copies - 1);
                    $newAvailable = min($book->total_copies, $book->available_copies + 1);

                    $book->update([
                        'reserved_copies' => $newReserved,
                        'available_copies' => $newAvailable,
                        'stock_available' => $newAvailable,
                    ]);

                    $this->assertStockInvariants($book);
                }

                $lockedBorrowing->update([
                    'status' => Borrowing::STATUS_CANCELLED,
                    'notes' => $reason ?? 'Reservasi dibatalkan oleh petugas perpustakaan.',
                    'rejection_reason' => $reason,
                ]);

                return $lockedBorrowing->load(['book', 'items.book', 'member', 'user']);
            }

            throw ValidationException::withMessages([
                'status' => "Transaksi tidak dapat dibatalkan pada status {$lockedBorrowing->status}.",
            ]);
        }, attempts: 3);
    }

    /**
     * Direct walk-in borrowing processed by staff at the circulation desk.
     * Immediately borrows and updates stock atomically without separate pending step.
     */
    public function directBorrow(User $staff, int $memberId, int|Book $bookOrId, string $requestKey, int $durationDays = 7, ?string $notes = null): Borrowing
    {
        $this->ensureStaffPermission($staff);
        $bookId = is_object($bookOrId) ? $bookOrId->id : (int) $bookOrId;

        return $this->borrow(
            staff: $staff,
            memberId: $memberId,
            items: [
                [
                    'book_id' => $bookId,
                    'quantity' => 1,
                ],
            ],
            requestKey: $requestKey,
            durationDays: $durationDays,
            notes: $notes,
            isDirectBorrow: true
        );
    }

    /**
     * Record a multi-book borrowing transaction atomically with row-level locking and idempotency.
     * Used for direct staff circulation and walk-in lending.
     *
     * @param  User  $staff  The authenticated staff/admin recording the transaction
     * @param  int  $memberId  Target active member ID
     * @param  array<int, array{book_id: int|string, quantity: int|string}>  $items
     * @param  string  $requestKey  Idempotency key for duplicate submission protection
     * @param  int  $durationDays  Loan duration in days (default 7)
     */
    public function borrow(User $staff, int $memberId, array $items, string $requestKey, int $durationDays = 7, ?string $notes = null, bool $isDirectBorrow = true): Borrowing
    {
        $requestKey = trim($requestKey);
        if ($requestKey === '') {
            throw ValidationException::withMessages([
                'request_key' => 'Kunci permintaan transaksi wajib disertakan.',
            ]);
        }

        $durationDays = max(1, min(90, $durationDays ?: 7));

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu buku untuk dipinjam.',
            ]);
        }

        $normalizedItems = [];
        $seenBooks = [];
        $originalIndexes = [];

        foreach (array_values($items) as $index => $item) {
            $rawBookId = $item['book_id'] ?? null;
            $rawQty = $item['quantity'] ?? null;

            if ($rawBookId === null || $rawBookId === '' || ! is_numeric($rawBookId)) {
                throw ValidationException::withMessages([
                    "items.{$index}.book_id" => 'Pilih buku yang valid.',
                ]);
            }

            if ($rawQty === null || $rawQty === '' || filter_var($rawQty, FILTER_VALIDATE_INT) === false) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => 'Jumlah pinjam harus berupa bilangan bulat positif.',
                ]);
            }

            $bookId = (int) $rawBookId;
            $qty = (int) $rawQty;

            if ($qty < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => 'Jumlah pinjam minimal 1 eksemplar.',
                ]);
            }

            if (isset($seenBooks[$bookId])) {
                throw ValidationException::withMessages([
                    "items.{$index}.book_id" => 'Buku ini sudah dipilih. Ubah jumlah pada baris sebelumnya.',
                    'items' => 'Buku ini sudah dipilih. Ubah jumlah pada baris sebelumnya.',
                ]);
            }

            $seenBooks[$bookId] = true;
            $originalIndexes[$bookId] = $index;
            $normalizedItems[] = [
                'book_id' => $bookId,
                'quantity' => $qty,
            ];
        }

        // Sort by book_id ascending for deterministic lock ordering (deadlock prevention)
        usort($normalizedItems, fn (array $a, array $b) => $a['book_id'] <=> $b['book_id']);

        $payloadHash = $this->computePayloadHash($staff->id, $memberId, $normalizedItems, $durationDays);

        // Pre-check idempotency key before starting transaction
        $existing = Borrowing::where('request_key', $requestKey)->first();
        if ($existing) {
            if ($existing->payload_hash === $payloadHash) {
                return $existing->load(['items.book', 'book', 'member', 'staff']);
            }

            throw ValidationException::withMessages([
                'request_key' => 'Permintaan transaksi tidak dapat diproses karena kunci permintaan sudah digunakan untuk data berbeda.',
            ]);
        }

        try {
            return DB::transaction(function () use ($staff, $memberId, $normalizedItems, $originalIndexes, $requestKey, $payloadHash, $durationDays, $notes, $isDirectBorrow) {
                // Re-check idempotency inside transaction
                $existingInTx = Borrowing::where('request_key', $requestKey)->lockForUpdate()->first();
                if ($existingInTx) {
                    if ($existingInTx->payload_hash === $payloadHash) {
                        return $existingInTx->load(['items.book', 'book', 'member', 'staff']);
                    }

                    throw ValidationException::withMessages([
                        'request_key' => 'Permintaan transaksi tidak dapat diproses karena kunci permintaan sudah digunakan untuk data berbeda.',
                    ]);
                }

                // 1. Lock and verify Member is active
                $member = Member::withTrashed()->lockForUpdate()->find($memberId);
                if (! $member || $member->trashed()) {
                    throw ValidationException::withMessages([
                        'member_id' => 'Anggota tidak ditemukan atau sudah diarsipkan.',
                    ]);
                }

                // 2. Lock all requested books in ascending ID order
                $bookIds = array_column($normalizedItems, 'book_id');
                $books = Book::withTrashed()
                    ->whereIn('id', $bookIds)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($normalizedItems as $item) {
                    $bookId = $item['book_id'];
                    $qty = $item['quantity'];
                    $formIndex = $originalIndexes[$bookId] ?? 0;

                    /** @var Book|null $book */
                    $book = $books->get($bookId);

                    if (! $book || $book->trashed()) {
                        throw ValidationException::withMessages([
                            "items.{$formIndex}.book_id" => 'Buku tidak ditemukan atau sudah diarsipkan.',
                        ]);
                    }

                    if ($book->available_copies < $qty) {
                        throw ValidationException::withMessages([
                            "items.{$formIndex}.quantity" => "Stok {$book->title} tidak mencukupi. Tersedia {$book->available_copies} eksemplar, diminta {$qty}.",
                            'stock' => "Stok {$book->title} tidak mencukupi. Tersedia {$book->available_copies} eksemplar, diminta {$qty}.",
                        ]);
                    }
                }

                // 3. Determine authoritative server timestamps & due date
                $borrowedAtUtc = Carbon::now('UTC');
                $dueAtJakarta = Carbon::now('Asia/Jakarta')->startOfDay()->addDays($durationDays)->format('Y-m-d');
                $primaryBookId = $normalizedItems[0]['book_id'] ?? null;

                $borrowerUserId = User::where('email', $member->email)->value('id');

                $borrowing = Borrowing::create([
                    'transaction_code' => $this->generateTransactionCode(),
                    'book_id' => $primaryBookId,
                    'staff_id' => $staff->id,
                    'approved_by' => $staff->id,
                    'approved_at' => $borrowedAtUtc,
                    'handed_over_by' => $staff->id,
                    'returned_by' => null,
                    'member_id' => $member->id,
                    'member_name_snapshot' => $member->name,
                    'user_id' => $borrowerUserId,
                    'borrower_user_id' => $borrowerUserId,
                    'loan_type' => Borrowing::LOAN_TYPE_PHYSICAL,
                    'borrowed_at' => $borrowedAtUtc,
                    'due_at' => $dueAtJakarta,
                    'due_date' => $dueAtJakarta,
                    'returned_at' => null,
                    'status' => Borrowing::STATUS_BORROWED,
                    'request_key' => $requestKey,
                    'payload_hash' => $payloadHash,
                    'notes' => $notes,
                    'rejection_reason' => null,
                    'is_direct_borrow' => $isDirectBorrow,
                ]);

                // 4. Create items with snapshots and update stock atomically
                foreach ($normalizedItems as $item) {
                    /** @var Book $book */
                    $book = $books->get($item['book_id']);
                    $qty = $item['quantity'];

                    BorrowingItem::create([
                        'borrowing_id' => $borrowing->id,
                        'book_id' => $book->id,
                        'book_title_snapshot' => $book->title,
                        'quantity' => $qty,
                    ]);

                    $newAvailable = $book->available_copies - $qty;
                    $newBorrowed = $book->borrowed_copies + $qty;

                    $book->update([
                        'available_copies' => $newAvailable,
                        'stock_available' => $newAvailable,
                        'borrowed_copies' => $newBorrowed,
                    ]);

                    $this->assertStockInvariants($book);
                }

                return $borrowing->load(['items.book', 'book', 'member', 'staff', 'handoverStaff']);
            }, attempts: 3);
        } catch (QueryException $e) {
            // Handle concurrent duplicate request_key collision outside transaction closure
            $existingAfterCollision = Borrowing::where('request_key', $requestKey)->first();
            if ($existingAfterCollision) {
                if ($existingAfterCollision->payload_hash === $payloadHash) {
                    return $existingAfterCollision->load(['items.book', 'book', 'member', 'staff']);
                }

                throw ValidationException::withMessages([
                    'request_key' => 'Permintaan transaksi tidak dapat diproses karena kunci permintaan sudah digunakan untuk data berbeda.',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Return all items in a borrowing transaction atomically and restore stock exactly once.
     * Prevents double-return and calculates days overdue.
     */
    public function returnBorrowing(Borrowing $borrowing, User $staff): Borrowing
    {
        return DB::transaction(function () use ($borrowing, $staff) {
            /** @var Borrowing $lockedBorrowing */
            $lockedBorrowing = Borrowing::lockForUpdate()->findOrFail($borrowing->id);

            if ($lockedBorrowing->status !== Borrowing::STATUS_BORROWED || $lockedBorrowing->returned_at !== null) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dikembalikan. Stok tidak diubah.',
                ]);
            }

            $items = $lockedBorrowing->items()->orderBy('book_id', 'asc')->get();
            if ($items->isEmpty() && $lockedBorrowing->book_id) {
                $items = collect([
                    (object) [
                        'book_id' => $lockedBorrowing->book_id,
                        'quantity' => 1,
                        'book_title_snapshot' => $lockedBorrowing->book?->title ?? 'Buku',
                    ],
                ]);
            }

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi peminjaman tidak memiliki daftar buku yang valid.',
                ]);
            }

            if (! $lockedBorrowing->isDigital()) {
                $bookIds = $items->pluck('book_id')->unique()->sort()->values()->all();

                $books = Book::withTrashed()
                    ->whereIn('id', $bookIds)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    /** @var Book|null $book */
                    $book = $books->get($item->book_id);
                    if (! $book) {
                        throw ValidationException::withMessages([
                            'stock' => "Data buku untuk item '{$item->book_title_snapshot}' tidak ditemukan.",
                        ]);
                    }

                    $qty = (int) $item->quantity;
                    $newBorrowed = max(0, $book->borrowed_copies - $qty);
                    $newAvailable = min($book->total_copies, $book->available_copies + $qty);

                    $book->update([
                        'borrowed_copies' => $newBorrowed,
                        'available_copies' => $newAvailable,
                        'stock_available' => $newAvailable,
                    ]);

                    $this->assertStockInvariants($book);
                }
            }

            $returnedAtUtc = Carbon::now('UTC');
            if ($lockedBorrowing->borrowed_at && $returnedAtUtc->lt($lockedBorrowing->borrowed_at)) {
                $returnedAtUtc = $lockedBorrowing->borrowed_at->copy();
            }

            $lockedBorrowing->update([
                'status' => Borrowing::STATUS_RETURNED,
                'returned_at' => $returnedAtUtc,
                'returned_by' => $staff->id,
            ]);

            return $lockedBorrowing->load(['items.book', 'book', 'member', 'staff', 'returnedByUser', 'returnStaff']);
        }, attempts: 3);
    }

    /**
     * Assert stock invariants across total, available, reserved, and borrowed counts.
     */
    protected function assertStockInvariants(Book $book): void
    {
        $total = $book->total_copies;
        $available = $book->available_copies;
        $reserved = $book->reserved_copies;
        $borrowed = $book->borrowed_copies;

        if ($available < 0) {
            throw ValidationException::withMessages([
                'stock' => "Invarian stok gagal: stok tersedia buku \"{$book->title}\" bernilai negatif ({$available}).",
            ]);
        }

        if ($reserved < 0 || $borrowed < 0 || $total < 0) {
            throw ValidationException::withMessages([
                'stock' => "Invarian stok gagal: komponen eksemplar buku \"{$book->title}\" tidak boleh bernilai negatif.",
            ]);
        }

        if ($reserved + $borrowed > $total) {
            throw ValidationException::withMessages([
                'stock' => "Invarian stok gagal: total reservasi ({$reserved}) dan pinjaman ({$borrowed}) melebihi total eksemplar ({$total}) buku \"{$book->title}\".",
            ]);
        }
    }

    /**
     * Ensure staff or admin permission for circulation operations.
     */
    protected function ensureStaffPermission(User $staff): void
    {
        if (! $staff->isAdmin() && ! $staff->isPetugas()) {
            throw ValidationException::withMessages([
                'permission' => 'Tindakan ini memerlukan izin Petugas atau Admin perpustakaan.',
            ]);
        }
    }

    public function computePayloadHash(int $staffId, int $memberId, array $normalizedItems, int $durationDays = 7): string
    {
        $canonical = json_encode([
            'staff_id' => $staffId,
            'member_id' => $memberId,
            'items' => $normalizedItems,
            'duration_days' => $durationDays,
        ], JSON_THROW_ON_ERROR);

        return hash('sha256', $canonical);
    }

    protected function generateTransactionCode(): string
    {
        $datePart = Carbon::now('Asia/Jakarta')->format('Ymd');

        do {
            $code = 'BRW-'.$datePart.'-'.strtoupper(Str::random(6));
        } while (Borrowing::where('transaction_code', $code)->exists());

        return $code;
    }
}

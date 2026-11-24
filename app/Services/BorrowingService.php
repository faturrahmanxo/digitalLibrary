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
     * Record a multi-book borrowing transaction atomically with row-level locking and idempotency.
     *
     * @param  User  $staff  The authenticated staff/admin user recording the transaction
     * @param  int  $memberId  Target active member ID
     * @param  array<int, array{book_id: int|string, quantity: int|string}>  $items
     * @param  string  $requestKey  Idempotency key for duplicate submission protection
     * @param  int  $durationDays  Loan duration in days (default 7)
     */
    public function borrow(User $staff, int $memberId, array $items, string $requestKey, int $durationDays = 7): Borrowing
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
                return $existing->load(['items.book', 'member', 'staff']);
            }

            throw ValidationException::withMessages([
                'request_key' => 'Permintaan transaksi tidak dapat diproses karena kunci permintaan sudah digunakan untuk data berbeda.',
            ]);
        }

        try {
            return DB::transaction(function () use ($staff, $memberId, $normalizedItems, $originalIndexes, $requestKey, $payloadHash, $durationDays) {
                // Re-check idempotency inside transaction
                $existingInTx = Borrowing::where('request_key', $requestKey)->lockForUpdate()->first();
                if ($existingInTx) {
                    if ($existingInTx->payload_hash === $payloadHash) {
                        return $existingInTx->load(['items.book', 'member', 'staff']);
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

                    if ($book->stock_available < $qty) {
                        throw ValidationException::withMessages([
                            "items.{$formIndex}.quantity" => "Stok {$book->title} tidak mencukupi. Tersedia {$book->stock_available} eksemplar, diminta {$qty}.",
                            'stock' => "Stok {$book->title} tidak mencukupi. Tersedia {$book->stock_available} eksemplar, diminta {$qty}.",
                        ]);
                    }
                }

                // 3. Determine authoritative server timestamps & due date (H+$durationDays calendar days in Asia/Jakarta)
                $borrowedAtUtc = Carbon::now('UTC');
                $dueAtJakarta = Carbon::now('Asia/Jakarta')->startOfDay()->addDays($durationDays)->format('Y-m-d');

                $borrowing = Borrowing::create([
                    'transaction_code' => $this->generateTransactionCode(),
                    'staff_id' => $staff->id,
                    'returned_by' => null,
                    'member_id' => $member->id,
                    'member_name_snapshot' => $member->name,
                    'borrowed_at' => $borrowedAtUtc,
                    'due_at' => $dueAtJakarta,
                    'returned_at' => null,
                    'status' => Borrowing::STATUS_BORROWED,
                    'request_key' => $requestKey,
                    'payload_hash' => $payloadHash,
                ]);

                // 4. Create items with snapshots and decrement stock atomically
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

                    $newAvailable = $book->stock_available - $qty;
                    if ($newAvailable < 0 || $newAvailable > $book->stock_total) {
                        throw ValidationException::withMessages([
                            'stock' => "Invarian stok gagal untuk buku {$book->title}.",
                        ]);
                    }

                    $book->update([
                        'stock_available' => $newAvailable,
                    ]);
                }

                return $borrowing->load(['items.book', 'member', 'staff']);
            }, attempts: 3);
        } catch (QueryException $e) {
            // Handle concurrent duplicate request_key collision outside transaction closure
            $existingAfterCollision = Borrowing::where('request_key', $requestKey)->first();
            if ($existingAfterCollision) {
                if ($existingAfterCollision->payload_hash === $payloadHash) {
                    return $existingAfterCollision->load(['items.book', 'member', 'staff']);
                }

                throw ValidationException::withMessages([
                    'request_key' => 'Permintaan transaksi tidak dapat diproses karena kunci permintaan sudah digunakan untuk data berbeda.',
                ]);
            }

            throw $e;
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
            $code = 'BRW-' . $datePart . '-' . strtoupper(Str::random(6));
        } while (Borrowing::where('transaction_code', $code)->exists());

        return $code;
    }
}

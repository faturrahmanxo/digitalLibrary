<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    /**
     * Return all items in a borrowing transaction atomically and restore stock exactly once.
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

                    $newAvailable = (int) $book->stock_available + (int) $item->quantity;
                    if ($newAvailable > (int) $book->stock_total) {
                        throw ValidationException::withMessages([
                            'stock' => "Anomali inventaris pada buku {$book->title}: pengembalian melebihi stok total tercatat.",
                        ]);
                    }

                    $book->update([
                        'stock_available' => $newAvailable,
                    ]);
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

            return $lockedBorrowing->load(['items.book', 'member', 'staff', 'returnedByUser']);
        }, attempts: 3);
    }
}

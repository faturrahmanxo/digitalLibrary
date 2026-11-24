<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Member;
use App\Models\NovelVolume;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DigitalBorrowingService
{
    public function availableLicensesForBook(Book $book): int
    {
        $activeQuantity = $this->activeBookItems($book->id)->sum('quantity');

        return max(0, (int) $book->digital_license_count - (int) $activeQuantity);
    }

    public function availableLicensesForVolume(NovelVolume $volume): int
    {
        $activeQuantity = $this->activeVolumeItems($volume->id)->sum('quantity');

        return max(0, (int) $volume->digital_license_count - (int) $activeQuantity);
    }

    public function activeBorrowingForBook(Book $book, User $user): ?Borrowing
    {
        return Borrowing::query()
            ->activeDigital()
            ->where('borrower_user_id', $user->id)
            ->whereHas('items', fn (Builder $query) => $query
                ->where('book_id', $book->id)
                ->whereNull('novel_volume_id'))
            ->latest('id')
            ->first();
    }

    public function activeBorrowingForVolume(NovelVolume $volume, User $user): ?Borrowing
    {
        return Borrowing::query()
            ->activeDigital()
            ->where('borrower_user_id', $user->id)
            ->whereHas('items', fn (Builder $query) => $query->where('novel_volume_id', $volume->id))
            ->latest('id')
            ->first();
    }

    public function borrowBook(Book $book, User $user): Borrowing
    {
        return DB::transaction(function () use ($book, $user): Borrowing {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);

            if ($lockedBook->trashed() || $lockedBook->collection_type !== 'e-book') {
                throw ValidationException::withMessages([
                    'book' => 'E-book tidak ditemukan atau tidak dapat dipinjam.',
                ]);
            }

            $this->ensureDigitalFileIsAllowed(
                $lockedBook->digital_lending_allowed,
                $lockedBook->digital_file_path,
            );

            if ($this->activeBorrowingForBook($lockedBook, $user)) {
                throw ValidationException::withMessages([
                    'book' => 'Anda sudah memiliki pinjaman aktif untuk E-book ini.',
                ]);
            }

            if ($this->availableLicensesForBook($lockedBook) < 1) {
                throw ValidationException::withMessages([
                    'book' => 'Kuota lisensi E-book sedang penuh.',
                ]);
            }

            return $this->createBorrowing($lockedBook, null, $user);
        }, attempts: 3);
    }

    public function borrowVolume(NovelVolume $volume, User $user): Borrowing
    {
        return DB::transaction(function () use ($volume, $user): Borrowing {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($volume->book_id);
            /** @var NovelVolume $lockedVolume */
            $lockedVolume = NovelVolume::withTrashed()->lockForUpdate()->findOrFail($volume->id);

            if (
                $lockedVolume->trashed()
                || $lockedVolume->book_id !== $lockedBook->id
                || $lockedBook->trashed()
                || $lockedBook->collection_type !== 'novel'
                || ! $lockedVolume->is_published
            ) {
                throw ValidationException::withMessages([
                    'volume' => 'Volume novel tidak tersedia untuk dipinjam.',
                ]);
            }

            $this->ensureDigitalFileIsAllowed(
                $lockedVolume->digital_lending_allowed,
                $lockedVolume->digital_file_path,
            );

            if ($this->activeBorrowingForVolume($lockedVolume, $user)) {
                throw ValidationException::withMessages([
                    'volume' => 'Anda sudah memiliki pinjaman aktif untuk volume ini.',
                ]);
            }

            if ($this->availableLicensesForVolume($lockedVolume) < 1) {
                throw ValidationException::withMessages([
                    'volume' => 'Kuota lisensi volume ini sedang penuh.',
                ]);
            }

            return $this->createBorrowing($lockedBook, $lockedVolume, $user);
        }, attempts: 3);
    }

    public function assertBookReadAccess(Book $book, User $user): void
    {
        abort_unless($this->activeBorrowingForBook($book, $user), 403);
        abort_unless(
            $book->collection_type === 'e-book'
                && $book->digital_lending_allowed
                && filled($book->digital_file_path)
                && strtolower(pathinfo($book->digital_file_path, PATHINFO_EXTENSION)) === 'pdf'
                && Storage::disk('local')->exists($book->digital_file_path),
            404,
        );
    }

    public function assertBookDownloadAccess(Book $book, User $user): void
    {
        abort_unless($this->activeBorrowingForBook($book, $user), 403);
        abort_unless(
            $book->collection_type === 'e-book'
                && $book->digital_lending_allowed
                && filled($book->digital_file_path)
                && Storage::disk('local')->exists($book->digital_file_path),
            404,
        );
    }

    public function assertVolumeDownloadAccess(NovelVolume $volume, User $user): void
    {
        abort_unless($this->activeBorrowingForVolume($volume, $user), 403);
        abort_unless(
            $volume->is_published
                && $volume->digital_lending_allowed
                && filled($volume->digital_file_path)
                && Storage::disk('local')->exists($volume->digital_file_path),
            404,
        );
    }

    private function activeBookItems(int $bookId): Builder
    {
        return BorrowingItem::query()
            ->where('book_id', $bookId)
            ->whereNull('novel_volume_id')
            ->whereHas('borrowing', fn (Builder $query) => $query->activeDigital());
    }

    private function activeVolumeItems(int $volumeId): Builder
    {
        return BorrowingItem::query()
            ->where('novel_volume_id', $volumeId)
            ->whereHas('borrowing', fn (Builder $query) => $query->activeDigital());
    }

    private function createBorrowing(Book $book, ?NovelVolume $volume, User $user): Borrowing
    {
        $member = Member::query()
            ->where('email', $user->email)
            ->lockForUpdate()
            ->first();

        if (! $member) {
            $member = Member::create([
                'member_code' => $this->generateMemberCode(),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => null,
                'address' => 'Anggota Perpustakaan Digital',
            ]);
        }

        $borrowedAt = Carbon::now('UTC');
        $dueAt = Carbon::now('Asia/Jakarta')->startOfDay()->addDays(7);
        $requestKey = 'digital-'.Str::uuid();
        $payloadHash = hash('sha256', json_encode([
            'borrower_user_id' => $user->id,
            'loan_type' => Borrowing::LOAN_TYPE_DIGITAL,
            'book_id' => $book->id,
            'volume_id' => $volume?->id,
        ], JSON_THROW_ON_ERROR));

        $borrowing = Borrowing::create([
            'transaction_code' => $this->generateTransactionCode(),
            'staff_id' => null,
            'borrower_user_id' => $user->id,
            'returned_by' => null,
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'loan_type' => Borrowing::LOAN_TYPE_DIGITAL,
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt->toDateString(),
            'returned_at' => null,
            'status' => Borrowing::STATUS_BORROWED,
            'request_key' => $requestKey,
            'payload_hash' => $payloadHash,
        ]);

        BorrowingItem::create([
            'borrowing_id' => $borrowing->id,
            'book_id' => $book->id,
            'book_title_snapshot' => $book->title,
            'novel_volume_id' => $volume?->id,
            'volume_number_snapshot' => $volume?->volume_number,
            'volume_title_snapshot' => $volume?->title,
            'quantity' => 1,
        ]);

        return $borrowing->load(['items.book', 'items.novelVolume', 'member', 'staff', 'borrowerUser']);
    }

    private function ensureDigitalFileIsAllowed(bool $isAllowed, ?string $filePath): void
    {
        if (! $isAllowed || blank($filePath) || ! Storage::disk('local')->exists($filePath)) {
            throw ValidationException::withMessages([
                'digital_file' => 'File digital belum tersedia atau izin peminjaman belum diberikan.',
            ]);
        }
    }

    private function generateMemberCode(): string
    {
        do {
            $memberCode = 'AGT-'.strtoupper(Str::random(20));
        } while (Member::withTrashed()->where('member_code', $memberCode)->exists());

        return $memberCode;
    }

    private function generateTransactionCode(): string
    {
        $datePart = Carbon::now('Asia/Jakarta')->format('Ymd');

        do {
            $transactionCode = 'DGB-'.$datePart.'-'.strtoupper(Str::random(8));
        } while (Borrowing::where('transaction_code', $transactionCode)->exists());

        return $transactionCode;
    }
}

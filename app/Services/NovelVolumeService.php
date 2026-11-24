<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BorrowingItem;
use App\Models\NovelVolume;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NovelVolumeService
{
    public function create(Book $book, array $data): NovelVolume
    {
        return DB::transaction(function () use ($book, $data): NovelVolume {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);

            if ($lockedBook->trashed() || $lockedBook->collection_type !== 'novel') {
                throw ValidationException::withMessages([
                    'book' => 'Volume hanya dapat ditambahkan pada Novel yang aktif.',
                ]);
            }

            $this->ensureDigitalFileIsAllowed(
                (bool) $data['digital_lending_allowed'],
                $data['digital_file_path'] ?? null,
            );

            return NovelVolume::create([
                'book_id' => $lockedBook->id,
                'volume_number' => (int) $data['volume_number'],
                'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : null,
                'digital_file_path' => $data['digital_file_path'] ?? null,
                'digital_license_count' => (int) $data['digital_license_count'],
                'digital_lending_allowed' => (bool) $data['digital_lending_allowed'],
                'is_published' => (bool) $data['is_published'],
            ]);
        }, attempts: 3);
    }

    public function update(Book $book, NovelVolume $volume, array $data): NovelVolume
    {
        return DB::transaction(function () use ($book, $volume, $data): NovelVolume {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);
            /** @var NovelVolume $lockedVolume */
            $lockedVolume = NovelVolume::withTrashed()->lockForUpdate()->findOrFail($volume->id);

            if (
                $lockedBook->trashed()
                || $lockedBook->collection_type !== 'novel'
                || $lockedVolume->trashed()
                || $lockedVolume->book_id !== $lockedBook->id
            ) {
                throw ValidationException::withMessages([
                    'volume' => 'Volume tidak ditemukan atau tidak dapat diubah.',
                ]);
            }

            $activeLoans = $this->activeDigitalLoanCount($lockedVolume);
            $newLicenseCount = (int) $data['digital_license_count'];

            if ($newLicenseCount < $activeLoans) {
                throw ValidationException::withMessages([
                    'digital_license_count' => 'Jumlah lisensi tidak boleh lebih kecil daripada jumlah pinjaman digital yang masih aktif.',
                ]);
            }

            $newFilePath = $data['digital_file_path'] ?? $lockedVolume->digital_file_path;
            if ($activeLoans > 0 && $newFilePath !== $lockedVolume->digital_file_path) {
                throw ValidationException::withMessages([
                    'digital_file' => 'File volume tidak dapat diganti selama masih ada pinjaman aktif.',
                ]);
            }

            $this->ensureDigitalFileIsAllowed(
                (bool) $data['digital_lending_allowed'],
                $newFilePath,
            );

            $lockedVolume->update([
                'volume_number' => (int) $data['volume_number'],
                'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : null,
                'digital_file_path' => $newFilePath,
                'digital_license_count' => $newLicenseCount,
                'digital_lending_allowed' => (bool) $data['digital_lending_allowed'],
                'is_published' => (bool) $data['is_published'],
            ]);

            return $lockedVolume->fresh();
        }, attempts: 3);
    }

    public function archive(Book $book, NovelVolume $volume): void
    {
        DB::transaction(function () use ($book, $volume): void {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);
            /** @var NovelVolume $lockedVolume */
            $lockedVolume = NovelVolume::withTrashed()->lockForUpdate()->findOrFail($volume->id);

            if ($lockedVolume->book_id !== $lockedBook->id) {
                throw ValidationException::withMessages([
                    'volume' => 'Volume tidak terdaftar pada Novel ini.',
                ]);
            }

            if ($this->activeDigitalLoanCount($lockedVolume) > 0) {
                throw ValidationException::withMessages([
                    'archive' => 'Volume tidak dapat diarsipkan selama masih memiliki pinjaman aktif.',
                ]);
            }

            $lockedVolume->delete();
        }, attempts: 3);
    }

    private function activeDigitalLoanCount(NovelVolume $volume): int
    {
        return (int) BorrowingItem::query()
            ->where('novel_volume_id', $volume->id)
            ->whereHas('borrowing', fn (Builder $query) => $query->activeDigital())
            ->sum('quantity');
    }

    private function ensureDigitalFileIsAllowed(bool $isAllowed, ?string $filePath): void
    {
        if ($isAllowed && (blank($filePath) || ! Storage::disk('local')->exists($filePath))) {
            throw ValidationException::withMessages([
                'digital_file' => 'Unggah file PDF atau EPUB sebelum mengizinkan peminjaman digital.',
            ]);
        }
    }
}

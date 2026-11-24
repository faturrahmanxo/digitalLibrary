<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BookStockService
{
    /**
     * Create a new book with initial stock_available equal to stock_total.
     */
    public function createBook(array $data): Book
    {
        return DB::transaction(function () use ($data) {
            $category = Category::find($data['category_id'] ?? null);
            if (! $category) {
                throw ValidationException::withMessages([
                    'category_id' => 'Kategori yang dipilih tidak valid atau sudah diarsipkan.',
                ]);
            }

            $collectionType = $data['collection_type'] ?? 'physical';
            $isPhysical = $collectionType === 'physical';
            $isEbook = in_array($collectionType, ['ebook', 'e-book'], true);

            $stockTotal = (int) ($data['stock_total'] ?? $data['total_copies'] ?? ($isPhysical ? 1 : 0));
            if ($stockTotal < 0) {
                throw ValidationException::withMessages([
                    'stock_total' => 'Stok total harus berupa bilangan bulat non-negatif.',
                ]);
            }

            $digitalFilePath = $isPhysical ? null : ($data['digital_file_path'] ?? null);
            $downloadUrl = (! $isPhysical && isset($data['download_url']) && trim((string) $data['download_url']) !== '')
                ? trim((string) $data['download_url'])
                : null;
            $readUrl = (! $isPhysical && isset($data['read_url']) && trim((string) $data['read_url']) !== '')
                ? trim((string) $data['read_url'])
                : null;
            $digitalLendingAllowed = $isEbook && (bool) ($data['digital_lending_allowed'] ?? false);

            $this->ensureDigitalFileIsAllowed($digitalLendingAllowed, $digitalFilePath);

            $publisherId = $data['publisher_id'] ?? null;
            $publisherName = isset($data['publisher']) && trim((string) $data['publisher']) !== ''
                ? trim((string) $data['publisher'])
                : null;

            if (! $publisherId && $publisherName !== null) {
                $pub = Publisher::where('name', $publisherName)->first();
                if ($pub) {
                    $publisherId = $pub->id;
                }
            }

            $shelfId = $isPhysical ? ($data['shelf_id'] ?? null) : null;
            $status = $data['status'] ?? 'active';

            return Book::create([
                'category_id' => $category->id,
                'shelf_id' => $shelfId,
                'publisher_id' => $publisherId,
                'title' => trim((string) $data['title']),
                'author' => trim((string) $data['author']),
                'collection_type' => $collectionType,
                'description' => $data['description'] ?? null,
                'synopsis' => $data['synopsis'] ?? null,
                'isbn' => isset($data['isbn']) && trim((string) $data['isbn']) !== '' ? trim((string) $data['isbn']) : null,
                'publisher' => $publisherName,
                'publication_year' => isset($data['publication_year']) && $data['publication_year'] !== '' ? (int) $data['publication_year'] : null,
                'stock_total' => $stockTotal,
                'stock_available' => $stockTotal,
                'total_copies' => $stockTotal,
                'available_copies' => $stockTotal,
                'reserved_copies' => 0,
                'borrowed_copies' => 0,
                'digital_file_path' => $digitalFilePath,
                'read_url' => $readUrl,
                'download_url' => $downloadUrl,
                'status' => $status,
                'digital_license_count' => $isEbook ? max(0, (int) ($data['digital_license_count'] ?? 0)) : 0,
                'digital_lending_allowed' => $digitalLendingAllowed,
                'cover_path' => $data['cover_path'] ?? null,
            ]);
        });
    }

    /**
     * Update book metadata and adjust stock_total atomically while preserving stock invariants.
     */
    public function updateBook(Book $book, array $data): Book
    {
        return DB::transaction(function () use ($book, $data) {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);

            if ($lockedBook->trashed()) {
                throw ValidationException::withMessages([
                    'title' => 'Data buku sudah diarsipkan dan tidak dapat diubah.',
                ]);
            }

            $category = Category::find($data['category_id'] ?? null);
            if (! $category) {
                throw ValidationException::withMessages([
                    'category_id' => 'Kategori yang dipilih tidak valid atau sudah diarsipkan.',
                ]);
            }

            $newCollectionType = $data['collection_type'] ?? $lockedBook->collection_type;
            $typeChanged = false;
            if ($newCollectionType !== $lockedBook->collection_type) {
                $isOldDigital = in_array($lockedBook->collection_type, ['ebook', 'e-book'], true);
                $isNewDigital = in_array($newCollectionType, ['ebook', 'e-book'], true);
                if (! ($isOldDigital && $isNewDigital)) {
                    $typeChanged = true;
                }
            }

            if (
                $typeChanged
                && (
                    $lockedBook->borrowingItems()->exists()
                    || $lockedBook->novelVolumes()->exists()
                    || filled($lockedBook->digital_file_path)
                    || (int) $lockedBook->digital_license_count > 0
                )
            ) {
                throw ValidationException::withMessages([
                    'collection_type' => 'Jenis koleksi tidak dapat diubah setelah memiliki file, lisensi, volume, atau riwayat peminjaman.',
                ]);
            }

            $isPhysical = $newCollectionType === 'physical';
            $isEbook = in_array($newCollectionType, ['ebook', 'e-book'], true);

            $oldTotal = (int) $lockedBook->stock_total;
            $oldAvailable = (int) $lockedBook->stock_available;
            $newTotal = (int) ($data['stock_total'] ?? $data['total_copies'] ?? $oldTotal);

            if ($newTotal < 0) {
                throw ValidationException::withMessages([
                    'stock_total' => 'Stok total tidak boleh bernilai negatif.',
                ]);
            }

            $minRequired = (int) $lockedBook->borrowed_copies + (int) $lockedBook->reserved_copies;
            if ($newTotal < $minRequired) {
                throw ValidationException::withMessages([
                    'stock_total' => "Total stok tidak dapat dikurangi karena sebagian eksemplar sedang dipinjam atau direservasi ({$minRequired}).",
                ]);
            }

            $delta = $newTotal - $oldTotal;
            $newAvailable = $oldAvailable + $delta;

            if ($newAvailable < 0) {
                throw ValidationException::withMessages([
                    'stock_total' => 'Total stok tidak dapat dikurangi karena sebagian eksemplar sedang dipinjam.',
                ]);
            }

            if ($newAvailable > $newTotal) {
                throw ValidationException::withMessages([
                    'stock_total' => 'Stok tersedia tidak boleh melebihi stok total.',
                ]);
            }

            $newDigitalLicenseCount = $isEbook
                ? (int) ($data['digital_license_count'] ?? $lockedBook->digital_license_count)
                : 0;
            $activeDigitalLoans = $this->activeDigitalLoanCount($lockedBook);

            if ($newDigitalLicenseCount < $activeDigitalLoans) {
                throw ValidationException::withMessages([
                    'digital_license_count' => 'Jumlah lisensi tidak boleh lebih kecil daripada jumlah pinjaman digital yang masih aktif.',
                ]);
            }

            $newDigitalFilePath = $isPhysical
                ? null
                : (array_key_exists('digital_file_path', $data) ? $data['digital_file_path'] : $lockedBook->digital_file_path);

            $newDownloadUrl = $isPhysical
                ? null
                : (array_key_exists('download_url', $data)
                    ? (isset($data['download_url']) && trim((string) $data['download_url']) !== '' ? trim((string) $data['download_url']) : null)
                    : $lockedBook->download_url);

            $newReadUrl = $isPhysical
                ? null
                : (array_key_exists('read_url', $data)
                    ? (isset($data['read_url']) && trim((string) $data['read_url']) !== '' ? trim((string) $data['read_url']) : null)
                    : $lockedBook->read_url);

            if ($activeDigitalLoans > 0 && $newDigitalFilePath !== $lockedBook->digital_file_path) {
                throw ValidationException::withMessages([
                    'digital_file' => 'File E-book tidak dapat diganti selama masih ada pinjaman aktif.',
                ]);
            }

            $digitalLendingAllowed = $isEbook
                && (bool) ($data['digital_lending_allowed'] ?? $lockedBook->digital_lending_allowed);
            $this->ensureDigitalFileIsAllowed($digitalLendingAllowed, $newDigitalFilePath);

            $publisherId = array_key_exists('publisher_id', $data) ? $data['publisher_id'] : $lockedBook->publisher_id;
            $publisherName = array_key_exists('publisher', $data) && trim((string) $data['publisher']) !== ''
                ? trim((string) $data['publisher'])
                : $lockedBook->getRawOriginal('publisher');

            if (! $publisherId && $publisherName !== null) {
                $pub = Publisher::where('name', $publisherName)->first();
                if ($pub) {
                    $publisherId = $pub->id;
                }
            }

            $shelfId = $isPhysical
                ? (array_key_exists('shelf_id', $data) ? $data['shelf_id'] : $lockedBook->shelf_id)
                : null;

            $status = array_key_exists('status', $data) ? $data['status'] : ($lockedBook->status ?? 'active');

            $updatePayload = [
                'category_id' => $category->id,
                'shelf_id' => $shelfId,
                'publisher_id' => $publisherId,
                'title' => trim((string) $data['title']),
                'author' => trim((string) $data['author']),
                'collection_type' => $newCollectionType,
                'description' => array_key_exists('description', $data) ? $data['description'] : $lockedBook->description,
                'synopsis' => array_key_exists('synopsis', $data) ? $data['synopsis'] : $lockedBook->synopsis,
                'isbn' => array_key_exists('isbn', $data) && trim((string) $data['isbn']) !== '' ? trim((string) $data['isbn']) : null,
                'publisher' => $publisherName,
                'publication_year' => array_key_exists('publication_year', $data) && $data['publication_year'] !== '' && $data['publication_year'] !== null ? (int) $data['publication_year'] : null,
                'stock_total' => $newTotal,
                'stock_available' => $newAvailable,
                'total_copies' => $newTotal,
                'available_copies' => $newAvailable,
                'digital_file_path' => $newDigitalFilePath,
                'read_url' => $newReadUrl,
                'download_url' => $newDownloadUrl,
                'status' => $status,
                'digital_license_count' => $newDigitalLicenseCount,
                'digital_lending_allowed' => $digitalLendingAllowed,
            ];

            if (array_key_exists('cover_path', $data)) {
                $updatePayload['cover_path'] = $data['cover_path'];
            }

            $lockedBook->update($updatePayload);

            return $lockedBook->fresh(['category', 'shelf', 'publisher']);
        });
    }

    /**
     * Soft-delete (archive) a book only if it has no active borrowings.
     */
    public function archiveBook(Book $book): void
    {
        DB::transaction(function () use ($book) {
            /** @var Book $lockedBook */
            $lockedBook = Book::withTrashed()->lockForUpdate()->findOrFail($book->id);

            if ($lockedBook->trashed()) {
                return;
            }

            $hasActiveLoan = $lockedBook->borrowingItems()
                ->whereHas('borrowing', fn ($query) => $query
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('loan_type', Borrowing::LOAN_TYPE_PHYSICAL)
                                ->where('status', Borrowing::STATUS_BORROWED);
                        })->orWhere(fn ($query) => $query->activeDigital());
                    }))
                ->exists();

            if ($hasActiveLoan || $lockedBook->stock_available < $lockedBook->stock_total) {
                throw ValidationException::withMessages([
                    'archive' => 'Buku tidak dapat diarsipkan karena masih memiliki peminjaman aktif.',
                ]);
            }

            $lockedBook->delete();
        });
    }

    private function activeDigitalLoanCount(Book $book): int
    {
        return (int) BorrowingItem::query()
            ->where('book_id', $book->id)
            ->whereNull('novel_volume_id')
            ->whereHas('borrowing', fn ($query) => $query->activeDigital())
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

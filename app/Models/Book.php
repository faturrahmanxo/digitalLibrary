<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const COLLECTION_TYPE_EBOOK = 'e-book';

    public const COLLECTION_TYPE_NOVEL = 'novel';

    public const COLLECTION_TYPE_PHYSICAL = 'physical';

    protected $fillable = [
        'category_id',
        'shelf_id',
        'publisher_id',
        'title',
        'author',
        'collection_type',
        'description',
        'synopsis',
        'isbn',
        'publisher',
        'publication_year',
        'stock_total',
        'stock_available',
        'total_copies',
        'available_copies',
        'reserved_copies',
        'borrowed_copies',
        'read_url',
        'download_url',
        'status',
        'digital_file_path',
        'digital_license_count',
        'digital_lending_allowed',
        'cover_path',
    ];

    public function hasDownload(): bool
    {
        return filled($this->download_url) || filled($this->digital_file_path);
    }

    public function getDownloadLink(): ?string
    {
        if (filled($this->download_url)) {
            return $this->download_url;
        }

        if (filled($this->digital_file_path)) {
            return route('books.digital.download', $this);
        }

        return null;
    }

    public function getReadLink(): ?string
    {
        if (filled($this->read_url)) {
            return $this->read_url;
        }

        if (filled($this->digital_file_path)) {
            return route('books.read', $this);
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'stock_total' => 'integer',
            'stock_available' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'reserved_copies' => 'integer',
            'borrowed_copies' => 'integer',
            'digital_license_count' => 'integer',
            'digital_lending_allowed' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function borrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function novelVolumes(): HasMany
    {
        return $this->hasMany(NovelVolume::class);
    }

    public function activeBorrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class)->whereHas('borrowing', function ($query) {
            $query->where('status', Borrowing::STATUS_BORROWED);
        });
    }

    public function getPublisherAttribute($value): mixed
    {
        if ($this->relationLoaded('publisher')) {
            return $this->getRelation('publisher') ?? $value;
        }

        if ($this->publisher_id) {
            return $this->getRelationValue('publisher') ?? $value;
        }

        return $value;
    }

    public function getTotalCopiesAttribute($value): int
    {
        return (int) ($value ?? $this->attributes['stock_total'] ?? 0);
    }

    public function getAvailableCopiesAttribute($value): int
    {
        return (int) ($value ?? $this->attributes['stock_available'] ?? 0);
    }

    public function getBorrowedCopiesAttribute($value): int
    {
        $total = (int) ($this->attributes['stock_total'] ?? $this->attributes['total_copies'] ?? 0);
        $available = (int) ($this->attributes['stock_available'] ?? $this->attributes['available_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);

        return max(0, $total - $available - $reserved);
    }

    public function getReservedCopiesAttribute($value): int
    {
        return (int) ($value ?? 0);
    }

    public function setStockTotalAttribute($value): void
    {
        $intVal = (int) $value;
        $this->attributes['stock_total'] = $intVal;
        $this->attributes['total_copies'] = $intVal;
        $available = (int) ($this->attributes['stock_available'] ?? $this->attributes['available_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);
        $this->attributes['borrowed_copies'] = max(0, $intVal - $available - $reserved);
    }

    public function setTotalCopiesAttribute($value): void
    {
        $intVal = (int) $value;
        $this->attributes['total_copies'] = $intVal;
        $this->attributes['stock_total'] = $intVal;
        $available = (int) ($this->attributes['stock_available'] ?? $this->attributes['available_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);
        $this->attributes['borrowed_copies'] = max(0, $intVal - $available - $reserved);
    }

    public function setStockAvailableAttribute($value): void
    {
        $intVal = (int) $value;
        $this->attributes['stock_available'] = $intVal;
        $this->attributes['available_copies'] = $intVal;
        $total = (int) ($this->attributes['stock_total'] ?? $this->attributes['total_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);
        $this->attributes['borrowed_copies'] = max(0, $total - $intVal - $reserved);
    }

    public function setAvailableCopiesAttribute($value): void
    {
        $intVal = (int) $value;
        $this->attributes['available_copies'] = $intVal;
        $this->attributes['stock_available'] = $intVal;
        $total = (int) ($this->attributes['stock_total'] ?? $this->attributes['total_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);
        $this->attributes['borrowed_copies'] = max(0, $total - $intVal - $reserved);
    }

    public function getStockOnLoanAttribute(): int
    {
        if (isset($this->attributes['borrowed_copies'])) {
            return (int) $this->attributes['borrowed_copies'];
        }

        $total = (int) ($this->attributes['stock_total'] ?? $this->attributes['total_copies'] ?? 0);
        $available = (int) ($this->attributes['stock_available'] ?? $this->attributes['available_copies'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_copies'] ?? 0);

        return max(0, $total - $available - $reserved);
    }

    public function isAvailable(): bool
    {
        return ($this->attributes['stock_available'] ?? $this->attributes['available_copies'] ?? 0) > 0;
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function isPhysical(): bool
    {
        return in_array($this->collection_type, [self::COLLECTION_TYPE_PHYSICAL, 'fisik'], true);
    }

    public function isEbook(): bool
    {
        return ! $this->isPhysical();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}

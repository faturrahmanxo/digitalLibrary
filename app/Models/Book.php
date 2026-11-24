<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
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
        'digital_file_path',
        'download_url',
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

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'stock_total' => 'integer',
            'stock_available' => 'integer',
            'digital_license_count' => 'integer',
            'digital_lending_allowed' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
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

    public function getStockOnLoanAttribute(): int
    {
        return max(0, (int) $this->stock_total - (int) $this->stock_available);
    }

    public function isAvailable(): bool
    {
        return $this->stock_available > 0;
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NovelVolume extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'volume_number',
        'title',
        'digital_file_path',
        'digital_license_count',
        'digital_lending_allowed',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'volume_number' => 'integer',
            'digital_license_count' => 'integer',
            'digital_lending_allowed' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    public function borrowingItems(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }
}

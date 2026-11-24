<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_id',
        'book_id',
        'book_title_snapshot',
        'novel_volume_id',
        'volume_number_snapshot',
        'volume_title_snapshot',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'volume_number_snapshot' => 'integer',
        ];
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    public function novelVolume(): BelongsTo
    {
        return $this->belongsTo(NovelVolume::class)->withTrashed();
    }
}

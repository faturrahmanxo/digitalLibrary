<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    use HasFactory;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    public const LOAN_TYPE_PHYSICAL = 'physical';

    public const LOAN_TYPE_DIGITAL = 'digital';

    protected $fillable = [
        'transaction_code',
        'staff_id',
        'borrower_user_id',
        'returned_by',
        'member_id',
        'member_name_snapshot',
        'loan_type',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'request_key',
        'payload_hash',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id')->withTrashed();
    }

    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by')->withTrashed();
    }

    public function borrowerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_user_id')->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function isBorrowed(): bool
    {
        return $this->status === self::STATUS_BORROWED;
    }

    public function isDigital(): bool
    {
        return $this->loan_type === self::LOAN_TYPE_DIGITAL;
    }

    public function scopeActiveDigital(Builder $query): Builder
    {
        return $query->where('loan_type', self::LOAN_TYPE_DIGITAL)
            ->where('status', self::STATUS_BORROWED)
            ->whereNull('returned_at')
            ->whereDate('due_at', '>=', Carbon::now('Asia/Jakarta')->toDateString());
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isOverdue(): bool
    {
        if (! $this->isBorrowed() || ! $this->due_at) {
            return false;
        }

        $todayJakarta = Carbon::now('Asia/Jakarta')->startOfDay();
        $dueJakarta = Carbon::createFromFormat('Y-m-d', $this->due_at->format('Y-m-d'), 'Asia/Jakarta')->startOfDay();

        return $todayJakarta->gt($dueJakarta);
    }

    public function getTotalQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('quantity');
        }

        return (int) $this->items()->sum('quantity');
    }
}

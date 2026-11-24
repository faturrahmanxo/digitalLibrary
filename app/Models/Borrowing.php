<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    /** @use HasFactory<BorrowingFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_BORROWED,
        self::STATUS_RETURNED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    public const LOAN_TYPE_PHYSICAL = 'physical';

    public const LOAN_TYPE_DIGITAL = 'digital';

    protected $fillable = [
        'transaction_code',
        'book_id',
        'staff_id',
        'borrower_user_id',
        'user_id',
        'approved_by',
        'approved_at',
        'handed_over_by',
        'returned_by',
        'member_id',
        'member_name_snapshot',
        'loan_type',
        'borrowed_at',
        'due_at',
        'due_date',
        'returned_at',
        'status',
        'request_key',
        'payload_hash',
        'notes',
        'rejection_reason',
        'is_direct_borrow',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'date',
            'due_date' => 'date',
            'returned_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_direct_borrow' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function borrowerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_user_id')->withTrashed();
    }

    public function getUserAttribute($value): mixed
    {
        if ($this->relationLoaded('user')) {
            return $this->getRelation('user');
        }

        if ($this->user_id) {
            return $this->getRelationValue('user');
        }

        if ($this->borrower_user_id) {
            return $this->borrowerUser;
        }

        return $value;
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id')->withTrashed();
    }

    public function getBookAttribute($value): mixed
    {
        if ($this->relationLoaded('book')) {
            return $this->getRelation('book');
        }

        if ($this->book_id) {
            return $this->getRelationValue('book');
        }

        if ($this->relationLoaded('items')) {
            return $this->items->first()?->book;
        }

        return $value;
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id')->withTrashed();
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id')->withTrashed();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    public function handoverStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by')->withTrashed();
    }

    public function returnStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by')->withTrashed();
    }

    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(BorrowingItem::class);
    }

    public function getDueDateAttribute($value): ?Carbon
    {
        $raw = $value ?? ($this->attributes['due_at'] ?? null);

        return $raw ? Carbon::parse($raw) : null;
    }

    public function getDueAtAttribute($value): ?Carbon
    {
        $raw = $value ?? ($this->attributes['due_date'] ?? null);

        return $raw ? Carbon::parse($raw) : null;
    }

    public function setDueDateAttribute($value): void
    {
        $this->attributes['due_date'] = $value;
        if (! isset($this->attributes['due_at'])) {
            $this->attributes['due_at'] = $value;
        }
    }

    public function setDueAtAttribute($value): void
    {
        $this->attributes['due_at'] = $value;
        if (! isset($this->attributes['due_date'])) {
            $this->attributes['due_date'] = $value;
        }
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isBorrowed(): bool
    {
        return $this->status === self::STATUS_BORROWED;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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

    public function isOverdue(): bool
    {
        if (! $this->isBorrowed() || (! $this->due_at && ! $this->due_date)) {
            return false;
        }

        $due = $this->due_at ?? $this->due_date;
        $todayJakarta = Carbon::now('Asia/Jakarta')->startOfDay();
        $dueJakarta = Carbon::createFromFormat('Y-m-d', $due->format('Y-m-d'), 'Asia/Jakarta')->startOfDay();

        return $todayJakarta->gt($dueJakarta);
    }

    public function isDirectBorrow(): bool
    {
        return (bool) $this->is_direct_borrow;
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        $due = $this->due_at ?? $this->due_date;
        $todayJakarta = Carbon::now('Asia/Jakarta')->startOfDay();
        $dueJakarta = Carbon::createFromFormat('Y-m-d', $due->format('Y-m-d'), 'Asia/Jakarta')->startOfDay();

        return (int) $dueJakarta->diffInDays($todayJakarta);
    }

    public function getOverdueDaysAttribute(): int
    {
        return $this->getDaysOverdueAttribute();
    }

    public function getTotalQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('quantity');
        }

        $count = (int) $this->items()->sum('quantity');

        return $count > 0 ? $count : 1;
    }
}

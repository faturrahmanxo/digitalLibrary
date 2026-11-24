<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PETUGAS = 'petugas';

    public const ROLE_ANGGOTA = 'anggota';

    public const ROLE_STAFF = 'staff';

    public const ROLE_MEMBER = 'member';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_PETUGAS,
        self::ROLE_ANGGOTA,
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPetugas(): bool
    {
        return in_array($this->role, [self::ROLE_PETUGAS, self::ROLE_STAFF], true);
    }

    public function isStaff(): bool
    {
        return $this->isPetugas();
    }

    public function isAnggota(): bool
    {
        return in_array($this->role, [self::ROLE_ANGGOTA, self::ROLE_MEMBER], true);
    }

    public function isMember(): bool
    {
        return $this->isAnggota();
    }

    public function isActive(): bool
    {
        return ! $this->trashed();
    }

    public function isInactive(): bool
    {
        return $this->trashed();
    }

    public function getStatusAttribute(): string
    {
        return $this->trashed() ? 'inactive' : 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->trashed() ? 'Nonaktif' : 'Aktif';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PETUGAS, self::ROLE_STAFF => 'Petugas',
            self::ROLE_ANGGOTA, self::ROLE_MEMBER => 'Anggota',
            default => ucfirst($this->role),
        };
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'email', 'email')->withTrashed();
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'staff_id');
    }

    public function userBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'user_id');
    }

    public function approvedBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'approved_by');
    }

    public function handedOverBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'handed_over_by');
    }

    public function digitalBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'borrower_user_id');
    }

    public function returnedBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'returned_by');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'favorites')->withTimestamps();
    }

    public function hasFavorited(Book $book): bool
    {
        return $this->favorites()->where('book_id', $book->id)->exists();
    }
}

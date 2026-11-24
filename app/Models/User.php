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
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isAnggota(): bool
    {
        return $this->role === self::ROLE_ANGGOTA;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PETUGAS => 'Petugas',
            self::ROLE_ANGGOTA => 'Anggota',
            default => ucfirst($this->role),
        };
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'staff_id');
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

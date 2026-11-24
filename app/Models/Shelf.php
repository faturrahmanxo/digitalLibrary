<?php

namespace App\Models;

use Database\Factories\ShelfFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    /** @use HasFactory<ShelfFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'notes',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function __toString(): string
    {
        return (string) ($this->name ?? $this->code ?? '');
    }
}

<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA], true);
    }

    public function view(User $user, Book $book): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA, User::ROLE_STAFF, User::ROLE_MEMBER], true);
    }

    public function manage(User $user, Book $book): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function update(User $user, Book $book): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }
}

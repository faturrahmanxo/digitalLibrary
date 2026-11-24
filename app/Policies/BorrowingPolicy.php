<?php

namespace App\Policies;

use App\Models\Borrowing;
use App\Models\User;

class BorrowingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA], true);
    }

    public function view(User $user, Borrowing $borrowing): bool
    {
        if ($user->isAdmin() || $user->isPetugas()) {
            return true;
        }

        if ($user->isAnggota()) {
            return $borrowing->member?->email === $user->email;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA], true);
    }

    public function return(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin()
            || $user->isPetugas()
            || ($borrowing->isDigital() && (int) $borrowing->borrower_user_id === $user->id);
    }

    public function update(User $user, Borrowing $borrowing): bool
    {
        return false;
    }

    public function delete(User $user, Borrowing $borrowing): bool
    {
        return false;
    }
}

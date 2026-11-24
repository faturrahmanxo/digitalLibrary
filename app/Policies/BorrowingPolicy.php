<?php

namespace App\Policies;

use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;

class BorrowingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA, User::ROLE_STAFF, User::ROLE_MEMBER], true);
    }

    public function view(User $user, Borrowing $borrowing): bool
    {
        if ($user->isAdmin() || $user->isPetugas()) {
            return true;
        }

        if ($user->isAnggota()) {
            return $borrowing->member?->email === $user->email
                || (int) $borrowing->borrower_user_id === $user->id
                || (int) ($borrowing->user_id ?? 0) === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        if ($user->isAnggota()) {
            $member = Member::withTrashed()->where('email', $user->email)->first();
            if ($member && $member->trashed()) {
                return false;
            }
        }

        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA, User::ROLE_STAFF, User::ROLE_MEMBER], true);
    }

    public function approve(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function reject(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function handover(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function cancel(User $user, Borrowing $borrowing): bool
    {
        if ($user->isAdmin() || $user->isPetugas()) {
            return true;
        }

        if ($user->isAnggota()) {
            $isOwner = $borrowing->member?->email === $user->email
                || (int) $borrowing->borrower_user_id === $user->id
                || (int) ($borrowing->user_id ?? 0) === $user->id;

            return $isOwner && in_array($borrowing->status, ['pending', 'approved'], true);
        }

        return false;
    }

    public function return(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin()
            || $user->isPetugas()
            || ($borrowing->isDigital() && (int) $borrowing->borrower_user_id === $user->id);
    }

    public function update(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function delete(User $user, Borrowing $borrowing): bool
    {
        return $user->isAdmin();
    }
}

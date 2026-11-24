<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function view(User $user, Member $member): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function update(User $user, Member $member): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function restore(User $user, Member $member): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function forceDelete(User $user, Member $member): bool
    {
        return false;
    }
}

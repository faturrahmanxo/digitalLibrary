<?php

namespace App\Policies;

use App\Models\Shelf;
use App\Models\User;

class ShelfPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function view(User $user, Shelf $shelf): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Shelf $shelf): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Shelf $shelf): bool
    {
        return $user->isAdmin();
    }
}

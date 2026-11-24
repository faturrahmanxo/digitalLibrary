<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\User;

class ReturnService
{
    public function __construct(
        protected BorrowingService $borrowingService
    ) {}

    /**
     * Return all items in a borrowing transaction atomically and restore stock exactly once.
     */
    public function returnBorrowing(Borrowing $borrowing, User $staff): Borrowing
    {
        return $this->borrowingService->returnBorrowing($borrowing, $staff);
    }
}

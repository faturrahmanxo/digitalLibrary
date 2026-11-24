<?php

namespace Database\Factories;

use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    protected $model = Borrowing::class;

    public function definition(): array
    {
        $borrowedAt = Carbon::now('UTC');
        $dueAt = Carbon::now('Asia/Jakarta')->addDays(7)->format('Y-m-d');

        return [
            'transaction_code' => 'BRW-' . strtoupper(Str::random(8)),
            'staff_id' => User::factory(),
            'returned_by' => null,
            'member_id' => Member::factory(),
            'member_name_snapshot' => fake()->name(),
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'returned_at' => null,
            'status' => Borrowing::STATUS_BORROWED,
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', Str::random(32)),
        ];
    }
}

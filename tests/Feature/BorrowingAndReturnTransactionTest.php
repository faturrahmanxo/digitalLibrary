<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BorrowingAndReturnTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST-007 & TEST-008: Multi-item borrowing records authoritative staff_id, member_id, H+7 due date,
     * status borrowed, snapshots, and decrements stock per item. Ignores spoofed staff_id.
     */
    public function test_007_and_008_borrowing_records_metadata_ignores_spoofed_staff_and_decrements_stock(): void
    {
        $loggedInPetugas = User::factory()->petugas()->create(['name' => 'Petugas Asli']);
        $otherStaff = User::factory()->admin()->create(['name' => 'Staf Lain']);
        $member = Member::factory()->create(['name' => 'Alya Pratama']);

        $bookA = Book::factory()->create(['title' => 'Buku A', 'stock_total' => 5, 'stock_available' => 5]);
        $bookB = Book::factory()->create(['title' => 'Buku B', 'stock_total' => 4, 'stock_available' => 4]);

        $requestKey = (string) Str::uuid();

        $response = $this->actingAs($loggedInPetugas)->post(route('borrowings.store'), [
            'staff_id' => $otherStaff->id, // Spoofed staff_id attempt (EDGE-007)
            'status' => 'returned', // Spoofed status attempt
            'member_id' => $member->id,
            'request_key' => $requestKey,
            'items' => [
                ['book_id' => $bookB->id, 'quantity' => 3],
                ['book_id' => $bookA->id, 'quantity' => 2],
            ],
        ]);

        $borrowing = Borrowing::where('request_key', $requestKey)->firstOrFail();
        $response->assertRedirect(route('borrowings.show', $borrowing));

        // Authoritative staff_id is the logged-in user, NOT the spoofed payload
        $this->assertSame($loggedInPetugas->id, $borrowing->staff_id);
        $this->assertSame($member->id, $borrowing->member_id);
        $this->assertSame('Alya Pratama', $borrowing->member_name_snapshot);
        $this->assertSame(Borrowing::STATUS_BORROWED, $borrowing->status);
        $this->assertNull($borrowing->returned_at);

        // Due date is H+7 calendar days in Asia/Jakarta
        $expectedDue = Carbon::now('Asia/Jakarta')->startOfDay()->addDays(7)->format('Y-m-d');
        $this->assertSame($expectedDue, $borrowing->due_at->format('Y-m-d'));

        // Stock decremented accurately
        $this->assertSame(3, $bookA->fresh()->stock_available);
        $this->assertSame(1, $bookB->fresh()->stock_available);
        $this->assertSame(2, $borrowing->items()->count());
        $this->assertSame(5, $borrowing->total_quantity);
    }

    /**
     * TEST-009 & TEST-010: Insufficient stock on any item rolls back the entire transaction (atomicity)
     * and never produces negative stock when two requests compete for the last copy.
     */
    public function test_009_and_010_insufficient_stock_and_competing_borrows_rollback_completely_without_negative_stock(): void
    {
        $staff1 = User::factory()->petugas()->create();
        $staff2 = User::factory()->admin()->create();
        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();

        $bookPlenty = Book::factory()->create(['title' => 'Buku Banyak', 'stock_total' => 10, 'stock_available' => 10]);
        $bookLastCopy = Book::factory()->create(['title' => 'Buku Terakhir', 'stock_total' => 1, 'stock_available' => 1]);

        // TEST-010: Multi-item request where item 1 has plenty of stock (10) but item 2 requests 2 when only 1 is available
        $this->actingAs($staff1)->from(route('borrowings.create'))
            ->post(route('borrowings.store'), [
                'member_id' => $member1->id,
                'request_key' => (string) Str::uuid(),
                'items' => [
                    ['book_id' => $bookPlenty->id, 'quantity' => 4],
                    ['book_id' => $bookLastCopy->id, 'quantity' => 2], // Exceeds stock_available (1)
                ],
            ])
            ->assertRedirect(route('borrowings.create'))
            ->assertSessionHasErrors('items.1.quantity');

        // Full rollback verified: neither borrowing header nor items saved, and bookPlenty stock is untouched (still 10)
        $this->assertSame(0, Borrowing::count());
        $this->assertSame(0, BorrowingItem::count());
        $this->assertSame(10, $bookPlenty->fresh()->stock_available);
        $this->assertSame(1, $bookLastCopy->fresh()->stock_available);

        // TEST-009: Two staff members compete for the last 1 copy of $bookLastCopy
        $this->actingAs($staff1)->post(route('borrowings.store'), [
            'member_id' => $member1->id,
            'request_key' => (string) Str::uuid(),
            'items' => [
                ['book_id' => $bookLastCopy->id, 'quantity' => 1],
            ],
        ])->assertRedirect();

        $this->assertSame(0, $bookLastCopy->fresh()->stock_available);

        // Second staff attempts to borrow the same last copy -> rejected, stock remains 0 (never negative)
        $this->actingAs($staff2)->from(route('borrowings.create'))
            ->post(route('borrowings.store'), [
                'member_id' => $member2->id,
                'request_key' => (string) Str::uuid(),
                'items' => [
                    ['book_id' => $bookLastCopy->id, 'quantity' => 1],
                ],
            ])
            ->assertRedirect(route('borrowings.create'))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, $bookLastCopy->fresh()->stock_available);
        $this->assertSame(1, Borrowing::count());
    }

    /**
     * TEST-011 & TEST-012: Returning a borrowing sets status='returned', records returned_at & returned_by,
     * restores stock exactly once, and rejects duplicate/second return attempts without incrementing stock again.
     */
    public function test_011_and_012_return_restores_stock_once_and_duplicate_return_is_rejected(): void
    {
        $staffBorrow = User::factory()->petugas()->create();
        $staffReturn = User::factory()->admin()->create();
        $member = Member::factory()->create();

        $book1 = Book::factory()->create(['stock_total' => 6, 'stock_available' => 6]);
        $book2 = Book::factory()->create(['stock_total' => 3, 'stock_available' => 3]);

        $this->actingAs($staffBorrow)->post(route('borrowings.store'), [
            'member_id' => $member->id,
            'request_key' => (string) Str::uuid(),
            'items' => [
                ['book_id' => $book1->id, 'quantity' => 2],
                ['book_id' => $book2->id, 'quantity' => 3],
            ],
        ])->assertRedirect();

        $borrowing = Borrowing::firstOrFail();
        $this->assertSame(4, $book1->fresh()->stock_available);
        $this->assertSame(0, $book2->fresh()->stock_available);

        // First return succeeds
        $returnResponse = $this->actingAs($staffReturn)
            ->post(route('borrowings.return', $borrowing));

        $returnResponse->assertRedirect(route('borrowings.show', $borrowing));
        $returnResponse->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertSame(Borrowing::STATUS_RETURNED, $borrowing->status);
        $this->assertNotNull($borrowing->returned_at);
        $this->assertSame($staffReturn->id, $borrowing->returned_by);
        $this->assertTrue($borrowing->returned_at->gte($borrowing->borrowed_at));

        // Stock restored to original levels
        $this->assertSame(6, $book1->fresh()->stock_available);
        $this->assertSame(3, $book2->fresh()->stock_available);

        // Second (duplicate) return attempt is rejected and stock does NOT increase again
        $duplicateResponse = $this->actingAs($staffReturn)
            ->from(route('borrowings.show', $borrowing))
            ->post(route('borrowings.return', $borrowing));

        $duplicateResponse->assertRedirect(route('borrowings.show', $borrowing));
        $duplicateResponse->assertSessionHasErrors('status');

        $this->assertSame(6, $book1->fresh()->stock_available);
        $this->assertSame(3, $book2->fresh()->stock_available);
    }

    /**
     * TEST-018: Idempotent request_key prevents duplicate borrowing on retry, and rejects key reuse with altered payload.
     */
    public function test_018_duplicate_submit_with_same_request_key_is_idempotent(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member = Member::factory()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $requestKey = (string) Str::uuid();
        $payload = [
            'member_id' => $member->id,
            'request_key' => $requestKey,
            'items' => [
                ['book_id' => $book->id, 'quantity' => 2],
            ],
        ];

        // First submission
        $firstResponse = $this->actingAs($petugas)->post(route('borrowings.store'), $payload);
        $borrowing = Borrowing::firstOrFail();
        $firstResponse->assertRedirect(route('borrowings.show', $borrowing));
        $this->assertSame(3, $book->fresh()->stock_available);
        $this->assertSame(1, Borrowing::count());

        // Second submission with identical request_key and identical payload (network retry / double click)
        $secondResponse = $this->actingAs($petugas)->post(route('borrowings.store'), $payload);
        $secondResponse->assertRedirect(route('borrowings.show', $borrowing));

        // Still only 1 borrowing and stock was only decremented once!
        $this->assertSame(1, Borrowing::count());
        $this->assertSame(3, $book->fresh()->stock_available);

        // Third submission with the same request_key but different quantity -> rejected
        $alteredPayload = $payload;
        $alteredPayload['items'][0]['quantity'] = 1;

        $this->actingAs($petugas)->from(route('borrowings.create'))
            ->post(route('borrowings.store'), $alteredPayload)
            ->assertRedirect(route('borrowings.create'))
            ->assertSessionHasErrors('request_key');

        $this->assertSame(1, Borrowing::count());
        $this->assertSame(3, $book->fresh()->stock_available);
    }

    /**
     * TEST-019: Borrowing supports custom loan duration options (e.g. 3, 7, 14, 21, 30 days).
     */
    public function test_019_borrowing_supports_custom_loan_duration_options(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member = Member::factory()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        // Borrow with 14 days duration
        $response14 = $this->actingAs($petugas)->post(route('borrowings.store'), [
            'request_key' => (string) \Illuminate\Support\Str::uuid(),
            'member_id' => $member->id,
            'duration_days' => 14,
            'items' => [
                ['book_id' => $book->id, 'quantity' => 1],
            ],
        ]);

        $borrowing14 = Borrowing::latest('id')->firstOrFail();
        $response14->assertRedirect(route('borrowings.show', $borrowing14));
        $expectedDue14 = Carbon::now('Asia/Jakarta')->startOfDay()->addDays(14)->format('Y-m-d');
        $this->assertSame($expectedDue14, $borrowing14->due_at->format('Y-m-d'));

        // Borrow directly via book route with 3 days duration
        $response3 = $this->actingAs($petugas)->post(route('books.borrow', $book), [
            'request_key' => (string) \Illuminate\Support\Str::uuid(),
            'member_id' => $member->id,
            'duration_days' => 3,
        ]);

        $borrowing3 = Borrowing::latest('id')->firstOrFail();
        $response3->assertRedirect(route('borrowings.show', $borrowing3));
        $expectedDue3 = Carbon::now('Asia/Jakarta')->startOfDay()->addDays(3)->format('Y-m-d');
        $this->assertSame($expectedDue3, $borrowing3->due_at->format('Y-m-d'));
    }
}

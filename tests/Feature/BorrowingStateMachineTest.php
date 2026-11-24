<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BorrowingStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_requests_borrowing_records_pending_status_and_does_not_reduce_stock(): void
    {
        $memberUser = User::factory()->anggota()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.com']);
        $book = Book::factory()->create([
            'title' => 'Algoritma Pemrograman',
            'collection_type' => 'physical',
            'stock_total' => 5,
            'stock_available' => 5,
            'total_copies' => 5,
            'available_copies' => 5,
            'reserved_copies' => 0,
            'borrowed_copies' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($memberUser)->post(route('books.borrow', $book), [
            'notes' => 'Keperluan tugas akhir semester',
        ]);

        $response->assertRedirect();
        $borrowing = Borrowing::latest('id')->firstOrFail();

        $this->assertSame(Borrowing::STATUS_PENDING, $borrowing->status);
        $this->assertSame($book->id, $borrowing->book_id);
        $this->assertSame($memberUser->id, $borrowing->user_id);
        $this->assertFalse($borrowing->is_direct_borrow);
        $this->assertNull($borrowing->staff_id);
        $this->assertNull($borrowing->approved_by);
        $this->assertNull($borrowing->borrowed_at);
        $this->assertNull($borrowing->due_date);

        // Crucial requirement: Pending does NOT reduce stock
        $freshBook = $book->fresh();
        $this->assertSame(5, $freshBook->available_copies);
        $this->assertSame(5, $freshBook->stock_available);
        $this->assertSame(0, $freshBook->reserved_copies);
        $this->assertSame(0, $freshBook->borrowed_copies);
    }

    public function test_inactive_or_archived_member_cannot_request_borrowing(): void
    {
        $memberUser = User::factory()->anggota()->create(['name' => 'Anggota Nonaktif', 'email' => 'nonaktif@example.com']);
        $member = Member::factory()->create(['name' => 'Anggota Nonaktif', 'email' => 'nonaktif@example.com']);
        $member->delete(); // Soft delete/archive

        $book = Book::factory()->create(['collection_type' => 'physical', 'stock_total' => 3, 'stock_available' => 3]);

        $response = $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $response->assertForbidden();

        $this->assertDatabaseMissing('borrowings', [
            'member_id' => $member->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_duplicate_active_request_for_same_book_is_rejected(): void
    {
        $memberUser = User::factory()->anggota()->create(['email' => 'citra@example.com']);
        $book = Book::factory()->create(['collection_type' => 'physical', 'stock_total' => 3, 'stock_available' => 3]);

        // First request succeeds
        $this->actingAs($memberUser)->post(route('books.borrow', $book))->assertRedirect();

        // Second request for same book while pending is rejected
        $secondResponse = $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $secondResponse->assertSessionHasErrors(['book_id']);

        $this->assertSame(1, Borrowing::where('book_id', $book->id)->count());
    }

    public function test_staff_approves_pending_request_reserves_stock_atomically(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create([
            'collection_type' => 'physical',
            'stock_total' => 5,
            'stock_available' => 5,
            'total_copies' => 5,
            'available_copies' => 5,
            'reserved_copies' => 0,
            'borrowed_copies' => 0,
        ]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();

        // Petugas approves
        $response = $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_APPROVED, $freshBorrowing->status);
        $this->assertSame($petugas->id, $freshBorrowing->approved_by);
        $this->assertNotNull($freshBorrowing->approved_at);

        // Stock requirement: Approved reserves 1 copy atomically (Available: 5 -> 4, Reserved: 0 -> 1)
        $freshBook = $book->fresh();
        $this->assertSame(4, $freshBook->available_copies);
        $this->assertSame(4, $freshBook->stock_available);
        $this->assertSame(1, $freshBook->reserved_copies);
        $this->assertSame(0, $freshBook->borrowed_copies);
    }

    public function test_double_approval_is_prevented(): void
    {
        $petugas1 = User::factory()->petugas()->create();
        $petugas2 = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();

        // First approval succeeds
        $this->actingAs($petugas1)->post(route('borrowings.approve', $borrowing))->assertRedirect();

        // Second approval attempt fails
        $response2 = $this->actingAs($petugas2)->post(route('borrowings.approve', $borrowing));
        $response2->assertSessionHasErrors(['status']);

        // Stock was reserved exactly once
        $this->assertSame(4, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);
    }

    public function test_staff_hands_over_book_transitions_to_borrowed_without_double_decrement(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create([
            'stock_total' => 5,
            'stock_available' => 5,
            'total_copies' => 5,
            'available_copies' => 5,
        ]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));

        // Handover book
        $response = $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing), [
            'duration_days' => 7,
        ]);
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_BORROWED, $freshBorrowing->status);
        $this->assertSame($petugas->id, $freshBorrowing->handed_over_by);
        $this->assertNotNull($freshBorrowing->borrowed_at);

        $expectedDue = Carbon::now('Asia/Jakarta')->startOfDay()->addDays(7)->format('Y-m-d');
        $this->assertSame($expectedDue, $freshBorrowing->due_date->format('Y-m-d'));

        // Stock requirement: Borrowed does NOT double-decrement available stock
        // Reserved shifts to Borrowed (Available remains 4, Reserved: 1 -> 0, Borrowed: 0 -> 1)
        $freshBook = $book->fresh();
        $this->assertSame(4, $freshBook->available_copies);
        $this->assertSame(4, $freshBook->stock_available);
        $this->assertSame(0, $freshBook->reserved_copies);
        $this->assertSame(1, $freshBook->borrowed_copies);
    }

    public function test_double_handover_is_prevented(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));
        $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing));

        // Second handover attempt
        $response = $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing));
        $response->assertSessionHasErrors(['status']);

        $this->assertSame(1, $book->fresh()->borrowed_copies);
    }

    public function test_staff_rejects_pending_request_leaves_stock_untouched(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();

        $response = $this->actingAs($petugas)->post(route('borrowings.reject', $borrowing), [
            'rejection_reason' => 'Buku sedang dalam proses perbaikan fisik jilid.',
        ]);
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_REJECTED, $freshBorrowing->status);
        $this->assertSame('Buku sedang dalam proses perbaikan fisik jilid.', $freshBorrowing->rejection_reason);

        // Stock requirement: Rejection of Pending does NOT reduce stock
        $freshBook = $book->fresh();
        $this->assertSame(5, $freshBook->available_copies);
        $this->assertSame(0, $freshBook->reserved_copies);
        $this->assertSame(0, $freshBook->borrowed_copies);
    }

    public function test_member_can_cancel_pending_request_leaves_stock_untouched(): void
    {
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();

        $response = $this->actingAs($memberUser)->post(route('borrowings.cancel', $borrowing), [
            'reason' => 'Salah pilih judul buku.',
        ]);
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_CANCELLED, $freshBorrowing->status);

        $freshBook = $book->fresh();
        $this->assertSame(5, $freshBook->available_copies);
        $this->assertSame(0, $freshBook->reserved_copies);
    }

    public function test_staff_cancels_approved_reservation_releases_reserved_stock(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));

        // Reserved: 1, Available: 4
        $this->assertSame(4, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);

        // Staff cancels reservation
        $response = $this->actingAs($petugas)->post(route('borrowings.cancel', $borrowing), [
            'reason' => 'Peminjam melewati batas waktu pengambilan buku.',
        ]);
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_CANCELLED, $freshBorrowing->status);

        // Stock requirement: Cancellation of Approved releases reserved stock (Available 4 -> 5, Reserved 1 -> 0)
        $freshBook = $book->fresh();
        $this->assertSame(5, $freshBook->available_copies);
        $this->assertSame(5, $freshBook->stock_available);
        $this->assertSame(0, $freshBook->reserved_copies);
    }

    public function test_member_cannot_cancel_approved_reservation(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));

        // Member attempts to cancel after approval
        $response = $this->actingAs($memberUser)->post(route('borrowings.cancel', $borrowing));
        $response->assertSessionHasErrors(['status']);

        $this->assertSame(Borrowing::STATUS_APPROVED, $borrowing->fresh()->status);
    }

    public function test_staff_returns_borrowed_book_increments_available_stock_once(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));
        $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing));

        $this->assertSame(4, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->borrowed_copies);

        // Return book
        $response = $this->actingAs($petugas)->post(route('borrowings.return', $borrowing));
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $freshBorrowing = $borrowing->fresh();
        $this->assertSame(Borrowing::STATUS_RETURNED, $freshBorrowing->status);
        $this->assertNotNull($freshBorrowing->returned_at);
        $this->assertSame($petugas->id, $freshBorrowing->returned_by);

        // Stock restored exactly once
        $freshBook = $book->fresh();
        $this->assertSame(5, $freshBook->available_copies);
        $this->assertSame(5, $freshBook->stock_available);
        $this->assertSame(0, $freshBook->borrowed_copies);
    }

    public function test_double_return_is_prevented(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));
        $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing));
        $this->actingAs($petugas)->post(route('borrowings.return', $borrowing));

        // Duplicate return attempt
        $response = $this->actingAs($petugas)->post(route('borrowings.return', $borrowing));
        $response->assertSessionHasErrors(['status']);

        // Stock does not increment beyond total
        $this->assertSame(5, $book->fresh()->available_copies);
    }

    public function test_direct_borrow_for_walk_in_immediately_borrows_and_updates_stock(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member = Member::factory()->create(['name' => 'Walk In Member']);
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $requestKey = (string) Str::uuid();
        $response = $this->actingAs($petugas)->post(route('borrowings.store'), [
            'member_id' => $member->id,
            'book_id' => $book->id,
            'quantity' => 1,
            'duration_days' => 7,
            'request_key' => $requestKey,
            'notes' => 'Peminjaman langsung di meja sirkulasi',
        ]);

        $borrowing = Borrowing::where('request_key', $requestKey)->firstOrFail();
        $response->assertRedirect(route('borrowings.show', $borrowing));

        $this->assertSame(Borrowing::STATUS_BORROWED, $borrowing->status);
        $this->assertTrue($borrowing->is_direct_borrow);
        $this->assertSame($petugas->id, $borrowing->staff_id);
        $this->assertSame($petugas->id, $borrowing->handed_over_by);
        $this->assertSame($petugas->id, $borrowing->approved_by);
        $this->assertNotNull($borrowing->borrowed_at);
        $this->assertNotNull($borrowing->due_date);

        // Stock immediately decremented from available and added to borrowed
        $freshBook = $book->fresh();
        $this->assertSame(4, $freshBook->available_copies);
        $this->assertSame(4, $freshBook->stock_available);
        $this->assertSame(0, $freshBook->reserved_copies);
        $this->assertSame(1, $freshBook->borrowed_copies);
    }

    public function test_overdue_detection_and_days_calculation(): void
    {
        $borrowing = Borrowing::factory()->create([
            'status' => Borrowing::STATUS_BORROWED,
            'borrowed_at' => Carbon::now('Asia/Jakarta')->subDays(12),
            'due_at' => Carbon::now('Asia/Jakarta')->subDays(5)->format('Y-m-d'),
            'due_date' => Carbon::now('Asia/Jakarta')->subDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        $this->assertTrue($borrowing->isOverdue());
        $this->assertSame(5, $borrowing->days_overdue);
        $this->assertSame(5, $borrowing->overdue_days);

        // Overdue must NOT automatically restore stock or change status
        $this->assertSame(Borrowing::STATUS_BORROWED, $borrowing->status);
    }

    public function test_permission_enforcement_for_circulation_actions(): void
    {
        $anggota = User::factory()->anggota()->create();
        $otherAnggota = User::factory()->anggota()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);

        $this->actingAs($anggota)->post(route('books.borrow', $book));
        $borrowing = Borrowing::latest('id')->firstOrFail();

        // Guest cannot access mutating routes
        auth()->logout();
        $this->post(route('borrowings.approve', $borrowing))->assertRedirect(route('login'));
        $this->post(route('borrowings.handover', $borrowing))->assertRedirect(route('login'));
        $this->post(route('borrowings.return', $borrowing))->assertRedirect(route('login'));

        // Another anggota cannot approve or cancel someone else's request
        $this->actingAs($otherAnggota)->post(route('borrowings.approve', $borrowing))->assertForbidden();
        $this->actingAs($otherAnggota)->post(route('borrowings.cancel', $borrowing))->assertForbidden();
    }
}

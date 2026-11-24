<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use App\Services\BorrowingService;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST-005: Book CRUD, category relationship, initial stock invariant, and guard against archiving a category in use.
     */
    public function test_005_book_crud_and_category_in_use_archive_guard(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();
        $category = Category::factory()->create(['name' => 'Sejarah']);

        // Petugas creates a book
        $response = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Sejarah Maritim Nusantara',
            'author' => 'Dr.Bahari',
            'category_id' => $category->id,
            'stock_total' => 7,
            'isbn' => '978-602-1111-22-3',
            'publisher' => 'Samudra Press',
            'publication_year' => 2024,
        ]);

        $book = Book::where('title', 'Sejarah Maritim Nusantara')->firstOrFail();
        $response->assertRedirect(route('books.index'));

        // Initial stock_available equals stock_total
        $this->assertSame(7, $book->stock_total);
        $this->assertSame(7, $book->stock_available);
        $this->assertSame($category->id, $book->category_id);

        // Category with active book cannot be archived even by Admin (BR-013)
        $this->actingAs($admin)->from(route('categories.index'))
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHasErrors('archive');

        $this->assertNull($category->fresh()->deleted_at);

        // Once the book is archived (and has no active loans), the category can be archived
        $this->actingAs($petugas)->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));
        $this->assertSoftDeleted('books', ['id' => $book->id]);

        $this->actingAs($admin)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /**
     * TEST-006: Member CRUD, unique member_code, and guard against archiving a member with an active loan.
     */
    public function test_006_member_crud_unique_code_and_active_loan_guard(): void
    {
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)->post(route('members.store'), [
            'member_code' => 'agt-00101',
            'name' => 'Alya Pratama',
            'email' => 'alya@example.test',
            'phone' => '0812000101',
            'address' => 'Bandung',
        ])->assertRedirect();

        $member = Member::where('member_code', 'AGT-00101')->firstOrFail();
        $this->assertSame('Alya Pratama', $member->name);

        // Duplicate member_code is rejected
        $this->actingAs($petugas)->from(route('members.create'))
            ->post(route('members.store'), [
                'member_code' => 'AGT-00101',
                'name' => 'Orang Lain',
            ])
            ->assertRedirect(route('members.create'))
            ->assertSessionHasErrors('member_code');

        // Create an active borrowing for this member
        $book = Book::factory()->create(['stock_total' => 3, 'stock_available' => 3]);
        app(BorrowingService::class)->borrow(
            staff: $petugas,
            memberId: $member->id,
            items: [['book_id' => $book->id, 'quantity' => 1]],
            requestKey: (string) Str::uuid()
        );

        // Member with active borrowing cannot be archived
        $this->actingAs($petugas)->from(route('members.index'))
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'))
            ->assertSessionHasErrors('archive');

        $this->assertNull($member->fresh()->deleted_at);
    }

    /**
     * TEST-014: Archiving master data (after loans are returned) preserves historical borrowing records & snapshots.
     */
    public function test_014_archiving_master_data_preserves_borrowing_history(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->petugas()->create(['name' => 'Petugas Historis']);
        $member = Member::factory()->create(['member_code' => 'AGT-00500', 'name' => 'Anggota Historis']);
        $book = Book::factory()->create(['title' => 'Buku Historis Nusantara', 'stock_total' => 4, 'stock_available' => 4]);

        $borrowing = app(BorrowingService::class)->borrow(
            staff: $staff,
            memberId: $member->id,
            items: [['book_id' => $book->id, 'quantity' => 2]],
            requestKey: (string) Str::uuid()
        );

        // While loan is active, archiving the book is blocked
        $this->actingAs($admin)->from(route('books.index'))
            ->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'))
            ->assertSessionHasErrors('archive');

        // Return the loan first
        app(ReturnService::class)->returnBorrowing($borrowing, $staff);

        // Now archive the staff, member, and book
        $this->actingAs($admin)->delete(route('users.destroy', $staff))->assertRedirect(route('users.index'));
        $this->actingAs($admin)->delete(route('members.destroy', $member))->assertRedirect(route('members.index'));
        $this->actingAs($admin)->delete(route('books.destroy', $book))->assertRedirect(route('books.index'));

        $this->assertSoftDeleted('users', ['id' => $staff->id]);
        $this->assertSoftDeleted('members', ['id' => $member->id]);
        $this->assertSoftDeleted('books', ['id' => $book->id]);

        // Historical borrowing detail page still loads cleanly with snapshots and relationships intact
        $detailResponse = $this->actingAs($admin)->get(route('borrowings.show', $borrowing));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Anggota Historis');
        $detailResponse->assertSee('Buku Historis Nusantara');
        $detailResponse->assertSee('Petugas Historis');
    }

    /**
     * TEST-015: Comprehensive input validation across forms (required, foreign keys, negative qty, duplicate items).
     */
    public function test_015_input_validation_rejects_invalid_requests_without_persisting(): void
    {
        $petugas = User::factory()->petugas()->create();
        $book = Book::factory()->create(['stock_total' => 5, 'stock_available' => 5]);
        $member = Member::factory()->create();

        // Invalid book creation (missing title, negative stock, non-existent category)
        $this->actingAs($petugas)->post(route('books.store'), [
            'title' => '',
            'author' => '',
            'category_id' => 99999,
            'stock_total' => -3,
        ])->assertSessionHasErrors(['title', 'author', 'category_id', 'stock_total']);

        // Invalid borrowing: quantity 0 or negative
        $this->actingAs($petugas)->post(route('borrowings.store'), [
            'member_id' => $member->id,
            'request_key' => (string) Str::uuid(),
            'items' => [
                ['book_id' => $book->id, 'quantity' => 0],
            ],
        ])->assertSessionHasErrors(['items.0.quantity']);

        // Invalid borrowing: duplicate book in the same transaction
        $this->actingAs($petugas)->post(route('borrowings.store'), [
            'member_id' => $member->id,
            'request_key' => (string) Str::uuid(),
            'items' => [
                ['book_id' => $book->id, 'quantity' => 1],
                ['book_id' => $book->id, 'quantity' => 2],
            ],
        ])->assertSessionHasErrors(['items.0.book_id', 'items.1.book_id']);

        $this->assertSame(0, Borrowing::count());
        $this->assertSame(5, $book->fresh()->stock_available);
    }

    /**
     * TEST-019: Adjusting stock_total updates stock_available by delta and rejects reducing stock_total below currently borrowed copies.
     */
    public function test_019_book_stock_total_adjustment_rules_and_invariants(): void
    {
        $petugas = User::factory()->petugas()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'title' => 'Buku Inventaris Uji',
            'author' => 'Penulis Uji',
            'stock_total' => 5,
            'stock_available' => 5,
        ]);
        $member = Member::factory()->create();

        // Borrow 3 copies -> stock_available becomes 2, on_loan is 3
        app(BorrowingService::class)->borrow(
            staff: $petugas,
            memberId: $member->id,
            items: [['book_id' => $book->id, 'quantity' => 3]],
            requestKey: (string) Str::uuid()
        );

        $this->assertSame(2, $book->fresh()->stock_available);

        // Attempt to reduce stock_total to 2 (less than 3 currently on loan -> new_available = -1) -> MUST BE REJECTED
        $this->actingAs($petugas)->from(route('books.edit', $book))
            ->put(route('books.update', $book), [
                'title' => 'Buku Inventaris Uji',
                'author' => 'Penulis Uji',
                'category_id' => $category->id,
                'stock_total' => 2,
                'stock_available' => 999, // Direct manipulation attempt must also be ignored
            ])
            ->assertRedirect(route('books.edit', $book))
            ->assertSessionHasErrors('stock_total');

        $this->assertSame(5, $book->fresh()->stock_total);
        $this->assertSame(2, $book->fresh()->stock_available);

        // Increase stock_total from 5 to 8 (+3 delta) -> stock_available becomes 2 + 3 = 5
        $this->actingAs($petugas)->put(route('books.update', $book), [
            'title' => 'Buku Inventaris Uji',
            'author' => 'Penulis Uji',
            'category_id' => $category->id,
            'stock_total' => 8,
            'stock_available' => 999, // Must be ignored; server computes 5
        ])->assertRedirect(route('books.index'));

        $this->assertSame(8, $book->fresh()->stock_total);
        $this->assertSame(5, $book->fresh()->stock_available);

        // Reduce stock_total from 8 to 3 (-5 delta, exact number on loan) -> stock_available becomes 0
        $this->actingAs($petugas)->put(route('books.update', $book), [
            'title' => 'Buku Inventaris Uji',
            'author' => 'Penulis Uji',
            'category_id' => $category->id,
            'stock_total' => 3,
        ])->assertRedirect(route('books.index'));

        $this->assertSame(3, $book->fresh()->stock_total);
        $this->assertSame(0, $book->fresh()->stock_available);
    }
}

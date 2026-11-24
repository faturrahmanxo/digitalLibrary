<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaAndModelContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shelf_and_publisher_schema_and_relations(): void
    {
        $shelf = Shelf::create([
            'code' => 'RAK-A1',
            'name' => 'Rak Fiksi Populer',
            'location' => 'Lantai 1 Sayap Barat',
            'notes' => 'Dekat pintu masuk utama',
        ]);

        $publisher = Publisher::create([
            'name' => 'Pustaka Utama',
            'city' => 'Jakarta',
            'website' => 'https://pustakautama.test',
            'notes' => 'Mitra resmi literasi',
        ]);

        $category = Category::factory()->create();

        $book = Book::create([
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'title' => 'Petualangan Literasi Nusantara',
            'author' => 'Ahmad Penulis',
            'collection_type' => 'physical',
            'isbn' => '978-602-000-001-1',
            'publication_year' => 2026,
            'stock_total' => 10,
            'stock_available' => 8,
            'read_url' => 'https://reader.litera.test/book/1',
            'status' => Book::STATUS_ACTIVE,
        ]);

        $this->assertTrue($shelf->books->contains($book));
        $this->assertTrue($publisher->books->contains($book));
        $this->assertSame('Rak Fiksi Populer', (string) $shelf);
        $this->assertSame('Pustaka Utama', (string) $publisher);

        $this->assertInstanceOf(Shelf::class, $book->shelf);
        $this->assertSame('RAK-A1', $book->shelf->code);
        $this->assertInstanceOf(Publisher::class, $book->publisher);
        $this->assertSame('Pustaka Utama', $book->publisher->name);
        $this->assertSame(10, $book->total_copies);
        $this->assertSame(8, $book->available_copies);
        $this->assertSame(2, $book->borrowed_copies);
        $this->assertTrue($book->isActive());
    }

    public function test_book_stock_and_copies_synchronization(): void
    {
        $category = Category::factory()->create();

        $book = Book::create([
            'category_id' => $category->id,
            'title' => 'Buku Uji Sinkronisasi',
            'author' => 'Tester',
            'total_copies' => 15,
            'available_copies' => 12,
        ]);

        $this->assertSame(15, $book->stock_total);
        $this->assertSame(12, $book->stock_available);
        $this->assertSame(3, $book->borrowed_copies);

        $book->update([
            'stock_total' => 20,
            'stock_available' => 18,
        ]);

        $refreshed = $book->fresh();
        $this->assertSame(20, $refreshed->total_copies);
        $this->assertSame(18, $refreshed->available_copies);
        $this->assertSame(2, $refreshed->borrowed_copies);
    }

    public function test_borrowing_state_machine_and_direct_borrow_support(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $staff = User::factory()->create(['role' => User::ROLE_PETUGAS]);
        $borrower = User::factory()->create(['role' => User::ROLE_ANGGOTA]);
        $member = Member::factory()->create();
        $category = Category::factory()->create();

        $book = Book::create([
            'category_id' => $category->id,
            'title' => 'Algoritma Pemrograman Modern',
            'author' => 'Budi Santoso',
            'collection_type' => 'physical',
            'stock_total' => 5,
            'stock_available' => 4,
        ]);

        $now = Carbon::now('UTC');
        $due = Carbon::now('Asia/Jakarta')->addDays(7)->toDateString();

        $borrowing = Borrowing::create([
            'transaction_code' => 'BRW-DIR-0001',
            'book_id' => $book->id,
            'user_id' => $borrower->id,
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'staff_id' => $staff->id,
            'approved_by' => $admin->id,
            'approved_at' => $now,
            'handed_over_by' => $staff->id,
            'borrowed_at' => $now,
            'due_date' => $due,
            'status' => Borrowing::STATUS_APPROVED,
            'is_direct_borrow' => true,
            'notes' => 'Peminjaman langsung di loket A',
            'request_key' => 'test-req-direct-001',
            'payload_hash' => hash('sha256', 'payload-1'),
        ]);

        $this->assertTrue($borrowing->isApproved());
        $this->assertFalse($borrowing->isBorrowed());
        $this->assertTrue($borrowing->is_direct_borrow);
        $this->assertSame($due, $borrowing->due_at->format('Y-m-d'));
        $this->assertSame($due, $borrowing->due_date->format('Y-m-d'));

        $this->assertSame($book->id, $borrowing->book->id);
        $this->assertSame($borrower->id, $borrowing->user->id);
        $this->assertSame($member->id, $borrowing->member->id);
        $this->assertSame($admin->id, $borrowing->approver->id);
        $this->assertSame($staff->id, $borrowing->handoverStaff->id);

        $this->assertTrue($book->borrowings->contains($borrowing));

        // State machine transition to borrowed
        $borrowing->update(['status' => Borrowing::STATUS_BORROWED]);
        $this->assertTrue($borrowing->fresh()->isBorrowed());

        // State machine transition to returned
        $borrowing->update([
            'status' => Borrowing::STATUS_RETURNED,
            'returned_by' => $staff->id,
            'returned_at' => Carbon::now('UTC'),
        ]);
        $this->assertTrue($borrowing->fresh()->isReturned());
        $this->assertSame($staff->id, $borrowing->fresh()->returnStaff->id);
    }
}

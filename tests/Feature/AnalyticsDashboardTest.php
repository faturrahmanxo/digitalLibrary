<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createBorrowing(array $attributes): Borrowing
    {
        return Borrowing::create(array_merge([
            'transaction_code' => 'BOR-'.strtoupper(Str::random(8)),
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', Str::random(10)),
            'borrowed_at' => Carbon::now('Asia/Jakarta'),
            'due_at' => Carbon::now('Asia/Jakarta')->addDays(7),
            'loan_type' => Borrowing::LOAN_TYPE_PHYSICAL,
        ], $attributes));
    }

    public function test_admin_dashboard_zero_state_renders_accurately_with_real_database_queries(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalBookTitles', 0);
        $response->assertViewHas('totalPhysicalTitles', 0);
        $response->assertViewHas('totalEbookTitles', 0);
        $response->assertViewHas('totalActiveMembers', 0);
        $response->assertViewHas('activeLoansCount', 0);
        $response->assertViewHas('overdueLoansCount', 0);
        $response->assertViewHas('totalPhysicalCopies', 0);
        $response->assertViewHas('totalAvailableStock', 0);
        $response->assertViewHas('totalReservedStock', 0);
        $response->assertViewHas('totalBorrowedStock', 0);
        $response->assertViewHas('outOfStockPhysicalTitles', 0);

        // Verify trend data has zero-filled arrays
        $trendData = $response->viewData('trendData');
        $this->assertCount(7, $trendData['7d']['labels']);
        $this->assertCount(7, $trendData['7d']['borrowed']);
        $this->assertEquals(array_fill(0, 7, 0), $trendData['7d']['borrowed']);
        $this->assertCount(30, $trendData['30d']['labels']);
        $this->assertEquals(array_fill(0, 30, 0), $trendData['30d']['borrowed']);
        $this->assertCount(12, $trendData['12m']['labels']);
        $this->assertEquals(array_fill(0, 12, 0), $trendData['12m']['borrowed']);

        // Verify empty popular books and attention state
        $this->assertCount(0, $response->viewData('popularBooks'));
        $this->assertCount(0, $response->viewData('recentTransactions'));
        $this->assertFalse($response->viewData('attentionRequired')['has_attention']);
    }

    public function test_admin_dashboard_sections_a_through_i_reflect_database_data(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->petugas()->create();
        $member = Member::factory()->create();

        $categoryFiction = Category::factory()->create(['name' => 'Fiksi & Sastra']);
        $categoryScience = Category::factory()->create(['name' => 'Sains & Teknologi']);

        // Physical Books
        $book1 = Book::factory()->create([
            'category_id' => $categoryFiction->id,
            'title' => 'Laskar Pelangi',
            'collection_type' => Book::COLLECTION_TYPE_PHYSICAL,
            'stock_total' => 10,
            'stock_available' => 6,
            'total_copies' => 10,
            'available_copies' => 6,
            'borrowed_copies' => 4,
            'reserved_copies' => 0,
        ]);

        $book2 = Book::factory()->create([
            'category_id' => $categoryScience->id,
            'title' => 'Kecerdasan Buatan',
            'collection_type' => Book::COLLECTION_TYPE_PHYSICAL,
            'stock_total' => 5,
            'stock_available' => 0, // Out of stock
            'total_copies' => 5,
            'available_copies' => 0,
            'borrowed_copies' => 5,
            'reserved_copies' => 0,
        ]);

        // E-book
        $ebook = Book::factory()->create([
            'category_id' => $categoryScience->id,
            'title' => 'Algoritma Pemrograman Digital',
            'collection_type' => Book::COLLECTION_TYPE_EBOOK,
            'stock_total' => 0,
            'stock_available' => 0,
            'total_copies' => 0,
            'available_copies' => 0,
            'download_url' => 'https://example.com/ebook.pdf',
        ]);

        $today = Carbon::now('Asia/Jakarta');

        // 1. Pending borrowing
        $this->createBorrowing([
            'transaction_code' => 'BOR-PEND-01',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book1->id,
            'status' => Borrowing::STATUS_PENDING,
            'created_at' => $today,
        ]);

        // 2. Approved borrowing (awaiting pickup)
        $this->createBorrowing([
            'transaction_code' => 'BOR-APPR-01',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book1->id,
            'status' => Borrowing::STATUS_APPROVED,
            'approved_by' => $staff->id,
            'approved_at' => $today,
            'created_at' => $today,
        ]);

        // 3. Active on-time borrowing
        $activeBorrowing = $this->createBorrowing([
            'transaction_code' => 'BOR-ACT-01',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book1->id,
            'staff_id' => $staff->id,
            'borrowed_at' => (clone $today)->subDays(2),
            'due_at' => (clone $today)->addDays(5),
            'status' => Borrowing::STATUS_BORROWED,
        ]);
        BorrowingItem::create([
            'borrowing_id' => $activeBorrowing->id,
            'book_id' => $book1->id,
            'book_title_snapshot' => $book1->title,
            'quantity' => 1,
        ]);

        // 4. Overdue borrowing
        $overdueBorrowing = $this->createBorrowing([
            'transaction_code' => 'BOR-OVER-01',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book2->id,
            'staff_id' => $staff->id,
            'borrowed_at' => (clone $today)->subDays(10),
            'due_at' => (clone $today)->subDays(3),
            'status' => Borrowing::STATUS_BORROWED,
        ]);
        BorrowingItem::create([
            'borrowing_id' => $overdueBorrowing->id,
            'book_id' => $book2->id,
            'book_title_snapshot' => $book2->title,
            'quantity' => 2,
        ]);

        // 5. Returned today
        $returnedBorrowing = $this->createBorrowing([
            'transaction_code' => 'BOR-RET-01',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book1->id,
            'staff_id' => $staff->id,
            'borrowed_at' => (clone $today)->subDays(5),
            'due_at' => (clone $today)->addDays(2),
            'returned_at' => $today,
            'returned_by' => $staff->id,
            'status' => Borrowing::STATUS_RETURNED,
        ]);
        BorrowingItem::create([
            'borrowing_id' => $returnedBorrowing->id,
            'book_id' => $book1->id,
            'book_title_snapshot' => $book1->title,
            'quantity' => 1,
        ]);

        // Request dashboard as Admin
        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();

        // Section A: Main Summary
        $response->assertViewHas('totalBookTitles', 3);
        $response->assertViewHas('totalPhysicalTitles', 2);
        $response->assertViewHas('totalEbookTitles', 1);
        $response->assertViewHas('totalActiveMembers', 1);
        $response->assertViewHas('activeLoansCount', 2); // 1 on-time + 1 overdue
        $response->assertViewHas('overdueLoansCount', 1);

        // Section B: Inventory Summary
        $response->assertViewHas('totalPhysicalCopies', 15); // 10 + 5
        $response->assertViewHas('totalAvailableStock', 6);  // 6 + 0
        $response->assertViewHas('outOfStockPhysicalTitles', 1); // book2 is out of stock

        // Section F: Borrowing Status Breakdown
        $statusBreakdown = $response->viewData('statusBreakdown');
        $this->assertEquals(1, $statusBreakdown['pending']);
        $this->assertEquals(1, $statusBreakdown['approved']);
        $this->assertEquals(1, $statusBreakdown['active_on_time']);
        $this->assertEquals(1, $statusBreakdown['overdue']);
        $this->assertEquals(2, $statusBreakdown['active_total']);
        $this->assertEquals(1, $statusBreakdown['returned']);

        // Section I: Attention Required
        $attentionRequired = $response->viewData('attentionRequired');
        $this->assertTrue($attentionRequired['has_attention']);
        $this->assertEquals(1, $attentionRequired['pending']);
        $this->assertEquals(1, $attentionRequired['awaiting_pickup']);
        $this->assertEquals(1, $attentionRequired['overdue']);
        $this->assertEquals(1, $attentionRequired['out_of_stock']);

        // Section G: Popular Books
        $popularBooks = $response->viewData('popularBooks');
        $this->assertNotEmpty($popularBooks);
        // book1 was borrowed 3 times (active, returned, pending/approved), book2 was borrowed 1 time
        $this->assertEquals($book1->id, $popularBooks->first()->id);

        // Section H: Recent Transactions
        $recentTransactions = $response->viewData('recentTransactions');
        $this->assertCount(5, $recentTransactions);
        $this->assertEquals('BOR-RET-01', $recentTransactions->first()->transaction_code);
    }

    public function test_staff_dashboard_operational_focus_per_section_26(): void
    {
        $staff = User::factory()->petugas()->create();
        $member = Member::factory()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => Book::COLLECTION_TYPE_PHYSICAL,
            'stock_total' => 4,
            'stock_available' => 3,
        ]);

        $today = Carbon::now('Asia/Jakarta');

        // Create pending loan
        $this->createBorrowing([
            'transaction_code' => 'BOR-STAFF-PEND',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book->id,
            'status' => Borrowing::STATUS_PENDING,
            'created_at' => $today,
        ]);

        // Create return today
        $this->createBorrowing([
            'transaction_code' => 'BOR-STAFF-RET',
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book->id,
            'staff_id' => $staff->id,
            'borrowed_at' => (clone $today)->subDays(3),
            'returned_at' => $today,
            'status' => Borrowing::STATUS_RETURNED,
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Petugas — Operasional Sirkulasi');
        $response->assertSee('1 Pengembalian Hari Ini');
        $response->assertSee('Pengajuan Pending');
        $response->assertSee('Catat Peminjaman');
        $response->assertSee('Peminjaman Buku');
        $response->assertSee('Data Buku');

        // Ensure internal user management is NOT accessible to Petugas
        $response->assertDontSee('Kelola Pengguna Internal');
        $response->assertDontSee('users.index');
    }
}

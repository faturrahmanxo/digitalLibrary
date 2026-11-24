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
use Illuminate\Support\Str;
use Tests\TestCase;

class Section33EndToEndVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // 1. BOOK CRUD, SPLIT PHYSICAL VS EBOOK, ISBN & SYNOPSIS
    // ==========================================

    public function test_physical_book_crud_with_optional_isbn_and_synopsis(): void
    {
        $petugas = User::factory()->petugas()->create();
        $category = Category::factory()->create(['name' => 'Sains Komputer']);
        $shelf = Shelf::factory()->create(['code' => 'RAK-SK-01']);
        $publisher = Publisher::factory()->create(['name' => 'Penerbit Informatika']);

        // 1. Create Physical Book without ISBN (nullable ISBN test)
        $response = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Buku Algoritma Klasik',
            'author' => 'Donald Knuth',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2022,
            'isbn' => null, // Optional ISBN
            'total_copies' => 5,
            'synopsis' => '<p>Buku rujukan utama tentang struktur data dan analisis algoritma.</p>',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $book = Book::where('title', 'Buku Algoritma Klasik')->firstOrFail();

        $this->assertNull($book->isbn);
        $this->assertSame('physical', $book->collection_type);
        $this->assertSame(5, $book->total_copies);
        $this->assertSame(5, $book->available_copies);
        $this->assertSame(5, $book->stock_total);
        $this->assertSame(5, $book->stock_available);
        $this->assertSame($shelf->id, $book->shelf_id);
        $this->assertSame($publisher->id, $book->publisher_id);
        $this->assertStringContainsString('Buku rujukan utama', $book->synopsis);

        // 2. Physical Book rejects read_url and download_url
        $invalidPhysical = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Buku Fisik Salah',
            'author' => 'Penulis X',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'total_copies' => 3,
            'read_url' => 'https://example.com/read',
            'download_url' => 'https://example.com/download.pdf',
        ]);
        $invalidPhysical->assertSessionHasErrors(['read_url', 'download_url']);

        // 3. Physical Book rejects invalid ISBN format
        $invalidIsbn = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Buku ISBN Ngawur',
            'author' => 'Penulis Y',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'total_copies' => 3,
            'isbn' => 'invalid-isbn-12345',
        ]);
        $invalidIsbn->assertSessionHasErrors('isbn');

        // 4. Update with valid ISBN and sanitized synopsis (XSS stripped)
        $updateResponse = $this->actingAs($petugas)->put(route('books.update', $book), [
            'title' => 'Buku Algoritma Klasik Edisi 2',
            'author' => 'Donald Knuth',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2024,
            'isbn' => '978-0-201-89683-1',
            'total_copies' => 8,
            'synopsis' => '<p>Deskripsi aman <script>alert("xss")</script><img src="x" onerror="alert(1)"></p>',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect();

        $freshBook = $book->fresh();
        $this->assertSame('978-0-201-89683-1', $freshBook->isbn);
        $this->assertSame(8, $freshBook->total_copies);
        $this->assertSame(8, $freshBook->available_copies);
        // Verify HTML Sanitizer stripped script and event handlers
        $this->assertStringNotContainsString('<script>', $freshBook->synopsis);
        $this->assertStringNotContainsString('onerror', $freshBook->synopsis);
        $this->assertStringContainsString('Deskripsi aman', $freshBook->synopsis);
    }

    public function test_ebook_crud_and_ssrf_dangerous_url_validation(): void
    {
        $petugas = User::factory()->petugas()->create();
        $category = Category::factory()->create();
        $shelf = Shelf::factory()->create();

        // 1. E-book rejects physical shelf
        $invalidEbook = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Ebook Tak Valid',
            'author' => 'Author A',
            'collection_type' => 'ebook',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id, // Prohibited on ebook
            'read_url' => 'https://openlibrary.org/read/1',
        ]);
        $invalidEbook->assertSessionHasErrors(['shelf_id']);

        // 2. E-book rejects dangerous schemes (javascript:, ftp:, data:, file:)
        $dangerousScheme = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Ebook Skrip Berbahaya',
            'author' => 'Hacker',
            'collection_type' => 'ebook',
            'category_id' => $category->id,
            'read_url' => 'javascript:alert(document.cookie)',
            'download_url' => 'ftp://malicious.host/file.pdf',
        ]);
        $dangerousScheme->assertSessionHasErrors(['read_url', 'download_url']);

        // 3. E-book rejects private IP / cloud metadata SSRF
        $ssrfAttempt = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Ebook SSRF Test',
            'author' => 'Attacker',
            'collection_type' => 'ebook',
            'category_id' => $category->id,
            'read_url' => 'http://169.254.169.254/latest/meta-data/',
            'download_url' => 'http://127.0.0.1:8000/internal-admin',
        ]);
        $ssrfAttempt->assertSessionHasErrors(['read_url', 'download_url']);

        // 4. Valid E-book creation with HTTPS legal links and zero physical stock
        $validEbook = $this->actingAs($petugas)->post(route('books.store'), [
            'title' => 'Pemrograman Modern dengan Go',
            'author' => 'Alan Donovan',
            'collection_type' => 'ebook',
            'category_id' => $category->id,
            'publication_year' => 2023,
            'read_url' => 'https://www.gopl.io/',
            'download_url' => 'https://www.gopl.io/ch1.pdf',
            'synopsis' => 'Panduan komprehensif bahasa Go.',
            'status' => 'active',
        ]);
        $validEbook->assertRedirect();

        $ebook = Book::where('title', 'Pemrograman Modern dengan Go')->firstOrFail();
        $this->assertSame('ebook', $ebook->collection_type);
        $this->assertNull($ebook->shelf_id);
        $this->assertSame(0, $ebook->total_copies);
        $this->assertSame(0, $ebook->available_copies);
        $this->assertSame('https://www.gopl.io/', $ebook->read_url);
        $this->assertSame('https://www.gopl.io/ch1.pdf', $ebook->download_url);
    }

    // ==========================================
    // 2. BORROWING LIFECYCLE: PENDING -> APPROVED -> BORROWED -> RETURNED, REJECTED, CANCELLED, OVERDUE
    // ==========================================

    public function test_complete_borrowing_lifecycle_pending_to_approved_to_borrowed_to_returned(): void
    {
        $memberUser = User::factory()->anggota()->create(['email' => 'siti@example.com']);
        $petugas = User::factory()->petugas()->create(['name' => 'Petugas Sirkulasi']);
        $book = Book::factory()->create([
            'collection_type' => 'physical',
            'stock_total' => 4,
            'stock_available' => 4,
            'total_copies' => 4,
            'available_copies' => 4,
            'reserved_copies' => 0,
            'borrowed_copies' => 0,
            'status' => 'active',
        ]);

        // STEP 1: Member submits loan request -> Status is PENDING, stock is NOT decremented
        $requestResponse = $this->actingAs($memberUser)->post(route('books.borrow', $book), [
            'notes' => 'Pinjam untuk riset semester 4',
        ]);
        $requestResponse->assertRedirect();

        $borrowing = Borrowing::latest('id')->firstOrFail();
        $this->assertSame(Borrowing::STATUS_PENDING, $borrowing->status);
        $this->assertTrue($borrowing->isPending());
        $this->assertNull($borrowing->approved_at);
        $this->assertNull($borrowing->borrowed_at);

        // Stock invariant: Available = 4, Reserved = 0, Borrowed = 0
        $this->assertSame(4, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->reserved_copies);
        $this->assertSame(0, $book->fresh()->borrowed_copies);

        // STEP 2: Petugas approves request -> Status is APPROVED, 1 copy is RESERVED (available 4 -> 3)
        $approveResponse = $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing));
        $approveResponse->assertRedirect();

        $borrowing->refresh();
        $this->assertSame(Borrowing::STATUS_APPROVED, $borrowing->status);
        $this->assertTrue($borrowing->isApproved());
        $this->assertSame($petugas->id, $borrowing->approved_by);
        $this->assertNotNull($borrowing->approved_at);

        // Stock invariant: Available = 3, Reserved = 1, Borrowed = 0
        $this->assertSame(3, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);
        $this->assertSame(0, $book->fresh()->borrowed_copies);

        // STEP 3: Member comes to desk, Petugas hands over book -> Status is BORROWED
        // Reserved shifts to Borrowed without double decrementing available stock!
        $handoverResponse = $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing));
        $handoverResponse->assertRedirect();

        $borrowing->refresh();
        $this->assertSame(Borrowing::STATUS_BORROWED, $borrowing->status);
        $this->assertTrue($borrowing->isBorrowed());
        $this->assertSame($petugas->id, $borrowing->handed_over_by);
        $this->assertNotNull($borrowing->borrowed_at);
        $this->assertNotNull($borrowing->due_date);

        // Due date is strictly 7 days from handover
        $expectedDueDate = Carbon::parse($borrowing->borrowed_at)->addDays(7)->format('Y-m-d');
        $this->assertSame($expectedDueDate, $borrowing->due_date->format('Y-m-d'));

        // Stock invariant: Available = 3, Reserved = 0, Borrowed = 1
        $this->assertSame(3, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->reserved_copies);
        $this->assertSame(1, $book->fresh()->borrowed_copies);

        // STEP 4: Member returns book -> Status is RETURNED, stock restored exactly once (available 3 -> 4)
        $returnResponse = $this->actingAs($petugas)->post(route('borrowings.return', $borrowing));
        $returnResponse->assertRedirect();

        $borrowing->refresh();
        $this->assertSame(Borrowing::STATUS_RETURNED, $borrowing->status);
        $this->assertTrue($borrowing->isReturned());
        $this->assertSame($petugas->id, $borrowing->returned_by);
        $this->assertNotNull($borrowing->returned_at);

        // Stock invariant restored: Available = 4, Reserved = 0, Borrowed = 0
        $this->assertSame(4, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->reserved_copies);
        $this->assertSame(0, $book->fresh()->borrowed_copies);
    }

    public function test_borrowing_rejection_and_cancellation_flows(): void
    {
        $memberUser = User::factory()->anggota()->create();
        $petugas = User::factory()->petugas()->create();
        $book = Book::factory()->create([
            'collection_type' => 'physical',
            'stock_total' => 2,
            'stock_available' => 2,
            'total_copies' => 2,
            'available_copies' => 2,
            'reserved_copies' => 0,
            'borrowed_copies' => 0,
        ]);

        // 1. Pending request rejected by Petugas -> Stock untouched
        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing1 = Borrowing::latest('id')->firstOrFail();

        $this->actingAs($petugas)->post(route('borrowings.reject', $borrowing1), [
            'rejection_reason' => 'Buku sedang dalam preservasi',
        ]);

        $borrowing1->refresh();
        $this->assertSame(Borrowing::STATUS_REJECTED, $borrowing1->status);
        $this->assertSame('Buku sedang dalam preservasi', $borrowing1->rejection_reason);
        $this->assertSame(2, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->reserved_copies);

        // 2. Member cancels own pending request -> Stock untouched
        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing2 = Borrowing::latest('id')->firstOrFail();

        $this->actingAs($memberUser)->post(route('borrowings.cancel', $borrowing2));
        $borrowing2->refresh();
        $this->assertSame(Borrowing::STATUS_CANCELLED, $borrowing2->status);
        $this->assertSame(2, $book->fresh()->available_copies);

        // 3. Approved reservation cancelled by Staff -> Releases reserved stock (available 1 -> 2)
        $this->actingAs($memberUser)->post(route('books.borrow', $book));
        $borrowing3 = Borrowing::latest('id')->firstOrFail();
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing3));

        $this->assertSame(1, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);

        $this->actingAs($petugas)->post(route('borrowings.cancel', $borrowing3));
        $borrowing3->refresh();
        $this->assertSame(Borrowing::STATUS_CANCELLED, $borrowing3->status);
        // Reserved released back to available!
        $this->assertSame(2, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->reserved_copies);
    }

    public function test_overdue_detection_and_days_calculation(): void
    {
        $petugas = User::factory()->petugas()->create();
        $memberUser = User::factory()->anggota()->create();
        $member = Member::factory()->create(['email' => $memberUser->email]);
        $book = Book::factory()->create(['stock_total' => 2, 'stock_available' => 2]);

        // Create loan that was borrowed 12 days ago (due 5 days ago)
        $pastBorrowedAt = Carbon::now('Asia/Jakarta')->subDays(12);
        $pastDueDate = Carbon::now('Asia/Jakarta')->subDays(5);

        $borrowing = Borrowing::create([
            'transaction_code' => 'BRW-OVERDUE-001',
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', 'overdue-test'),
            'book_id' => $book->id,
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'user_id' => $memberUser->id,
            'staff_id' => $petugas->id,
            'handed_over_by' => $petugas->id,
            'status' => Borrowing::STATUS_BORROWED,
            'loan_type' => Borrowing::LOAN_TYPE_PHYSICAL,
            'borrowed_at' => $pastBorrowedAt,
            'due_at' => $pastDueDate,
            'due_date' => $pastDueDate->format('Y-m-d'),
        ]);

        $this->assertTrue($borrowing->isOverdue());
        $this->assertGreaterThanOrEqual(5, $borrowing->days_overdue);
        $this->assertNull($borrowing->returned_at);
    }

    // ==========================================
    // 3. CONCURRENCY, LOCKING & STOCK INVARIANTS
    // ==========================================

    public function test_concurrency_guards_prevent_negative_stock_and_double_actions(): void
    {
        $petugas = User::factory()->petugas()->create();
        $member1 = User::factory()->anggota()->create();
        $member2 = User::factory()->anggota()->create();

        // Single copy available
        $book = Book::factory()->create([
            'collection_type' => 'physical',
            'stock_total' => 1,
            'stock_available' => 1,
            'total_copies' => 1,
            'available_copies' => 1,
            'reserved_copies' => 0,
            'borrowed_copies' => 0,
        ]);

        // Member 1 requests
        $this->actingAs($member1)->post(route('books.borrow', $book));
        $borrowing1 = Borrowing::latest('id')->firstOrFail();

        // Staff approves Member 1's request -> Available copies drops to 0, Reserved becomes 1
        $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing1));
        $this->assertSame(0, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);

        // Attempt double approval on borrowing1 -> Must be rejected!
        $doubleApprove = $this->actingAs($petugas)->post(route('borrowings.approve', $borrowing1));
        $doubleApprove->assertSessionHasErrors();
        $this->assertSame(0, $book->fresh()->available_copies);
        $this->assertSame(1, $book->fresh()->reserved_copies);

        // Handover borrowing1
        $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing1));
        $this->assertSame(1, $book->fresh()->borrowed_copies);

        // Attempt double handover on borrowing1 -> Must be rejected!
        $doubleHandover = $this->actingAs($petugas)->post(route('borrowings.handover', $borrowing1));
        $doubleHandover->assertSessionHasErrors();
        $this->assertSame(1, $book->fresh()->borrowed_copies);

        // Return borrowing1
        $this->actingAs($petugas)->post(route('borrowings.return', $borrowing1));
        $this->assertSame(1, $book->fresh()->available_copies);
        $this->assertSame(0, $book->fresh()->borrowed_copies);

        // Attempt double return on borrowing1 -> Must be rejected!
        $doubleReturn = $this->actingAs($petugas)->post(route('borrowings.return', $borrowing1));
        $doubleReturn->assertSessionHasErrors();
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    public function test_total_copies_cannot_be_reduced_below_active_borrowed_and_reserved_copies(): void
    {
        $petugas = User::factory()->petugas()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->create([
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'stock_total' => 5,
            'stock_available' => 2,
            'total_copies' => 5,
            'available_copies' => 2,
            'reserved_copies' => 1,
            'borrowed_copies' => 2,
        ]);

        // Active obligations = reserved (1) + borrowed (2) = 3 copies
        // Attempt to reduce total_copies to 2 (below 3) -> Must fail validation!
        $response = $this->actingAs($petugas)->put(route('books.update', $book), [
            'title' => $book->title,
            'author' => $book->author,
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'total_copies' => 2,
        ]);

        $response->assertSessionHasErrors('stock_total');
        $this->assertSame(5, $book->fresh()->total_copies);
    }

    // ==========================================
    // 4. ROLE PERMISSIONS (ADMIN VS PETUGAS VS MEMBER VS GUEST)
    // ==========================================

    public function test_role_permissions_across_all_user_types(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();
        $member = User::factory()->anggota()->create();

        // 1. Guest: Access public catalog, redirected from internal back-office
        $this->get(route('catalog.index'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('books.index'))->assertRedirect(route('login'));
        $this->get(route('users.index'))->assertRedirect(route('login'));

        // 2. Member: Can access public and own collection, forbidden from management
        $this->actingAs($member)->get(route('collection.index'))->assertOk();
        $this->actingAs($member)->get(route('users.index'))->assertForbidden();
        $this->actingAs($member)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($member)->get(route('shelves.create'))->assertForbidden();
        $this->actingAs($member)->get(route('publishers.create'))->assertForbidden();

        // 3. Petugas: Can access dashboard, catalog management, circulation, members; forbidden from User management
        $this->actingAs($petugas)->get(route('dashboard'))->assertOk();
        $this->actingAs($petugas)->get(route('books.index'))->assertOk();
        $this->actingAs($petugas)->get(route('borrowings.index'))->assertOk();
        $this->actingAs($petugas)->get(route('members.index'))->assertOk();
        $this->actingAs($petugas)->get(route('users.index'))->assertForbidden(); // Strictly admin-only!

        // 4. Admin: Has full access to everything including User management
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('shelves.index'))->assertOk();
        $this->actingAs($admin)->get(route('publishers.index'))->assertOk();
    }

    // ==========================================
    // 5. DASHBOARD STATS ACCURACY AGAINST REAL DATABASE COUNTS
    // ==========================================

    public function test_dashboard_statistics_match_real_database_records(): void
    {
        $admin = User::factory()->admin()->create();

        // Create 3 Physical Books (total 15 copies) and 2 E-books
        $cat1 = Category::factory()->create(['name' => 'Teknologi']);
        $cat2 = Category::factory()->create(['name' => 'Fiksi']);

        $phys1 = Book::factory()->create(['collection_type' => 'physical', 'category_id' => $cat1->id, 'stock_total' => 6, 'stock_available' => 4, 'total_copies' => 6, 'available_copies' => 4, 'reserved_copies' => 1, 'borrowed_copies' => 1]);
        $phys2 = Book::factory()->create(['collection_type' => 'physical', 'category_id' => $cat2->id, 'stock_total' => 4, 'stock_available' => 4, 'total_copies' => 4, 'available_copies' => 4, 'reserved_copies' => 0, 'borrowed_copies' => 0]);
        $phys3 = Book::factory()->create(['collection_type' => 'physical', 'category_id' => $cat1->id, 'stock_total' => 5, 'stock_available' => 0, 'total_copies' => 5, 'available_copies' => 0, 'reserved_copies' => 0, 'borrowed_copies' => 5]); // Out of stock

        $ebook1 = Book::factory()->create(['collection_type' => 'ebook', 'category_id' => $cat1->id, 'stock_total' => 0, 'stock_available' => 0, 'total_copies' => 0, 'available_copies' => 0]);
        $ebook2 = Book::factory()->create(['collection_type' => 'ebook', 'category_id' => $cat2->id, 'stock_total' => 0, 'stock_available' => 0, 'total_copies' => 0, 'available_copies' => 0]);

        $m1 = Member::factory()->create();
        $m2 = Member::factory()->create();

        // Query admin dashboard
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        // Total Titles: 3 physical + 2 ebook = 5
        $response->assertViewHas('totalBookTitles', 5);
        $response->assertViewHas('totalPhysicalTitles', 3);
        $response->assertViewHas('totalEbookTitles', 2);
        $response->assertViewHas('totalActiveMembers', 2);

        // Physical inventory counts: 6 + 4 + 5 = 15 total physical copies
        // Available: 4 + 4 + 0 = 8
        // Reserved: 1
        // Borrowed: 1 + 5 = 6
        // Out of stock titles: 1 ($phys3)
        $response->assertViewHas('totalPhysicalCopies', 15);
        $response->assertViewHas('totalAvailableStock', 8);
        $response->assertViewHas('totalReservedStock', 1);
        $response->assertViewHas('totalBorrowedStock', 6);
        $response->assertViewHas('outOfStockPhysicalTitles', 1);
    }
}

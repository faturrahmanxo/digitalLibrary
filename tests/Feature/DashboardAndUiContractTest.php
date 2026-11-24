<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use App\Services\BorrowingService;
use App\Services\ReturnService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardAndUiContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST-013: Four Dashboard KPIs match exact definitions before borrow, after borrow, and after return, including zero state.
     */
    public function test_013_dashboard_four_kpis_are_accurate_across_circulation_lifecycle(): void
    {
        $admin = User::factory()->admin()->create();

        // Zero state check
        $zeroResponse = $this->actingAs($admin)->get(route('dashboard'));
        $zeroResponse->assertOk()
            ->assertSee('src="'.asset('images/logo-Litera.png').'"', false)
            ->assertSee('images/favicon.png');
        $zeroResponse->assertViewHas('totalBookTitles', 0);
        $zeroResponse->assertViewHas('totalAvailableStock', 0);
        $zeroResponse->assertViewHas('totalActiveMembers', 0);
        $zeroResponse->assertViewHas('totalBorrowedCopies', 0);

        // Setup fixture: 2 active books (total stock: 5 + 3 = 8), 1 archived book, 2 active members, 1 archived member
        $category = Category::factory()->create();
        $book1 = Book::factory()->create(['category_id' => $category->id, 'stock_total' => 5, 'stock_available' => 5]);
        $book2 = Book::factory()->create(['category_id' => $category->id, 'stock_total' => 3, 'stock_available' => 3]);
        $archivedBook = Book::factory()->create(['category_id' => $category->id, 'stock_total' => 10, 'stock_available' => 10]);
        $archivedBook->delete();

        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();
        $archivedMember = Member::factory()->create();
        $archivedMember->delete();

        // Check KPIs before borrowing
        $beforeResponse = $this->actingAs($admin)->get(route('dashboard'));
        $beforeResponse->assertOk();
        $beforeResponse->assertViewHas('totalBookTitles', 2);
        $beforeResponse->assertViewHas('totalAvailableStock', 8);
        $beforeResponse->assertViewHas('totalActiveMembers', 2);
        $beforeResponse->assertViewHas('totalBorrowedCopies', 0);

        // Borrow 2 copies of book1 and 1 copy of book2 (total 3 copies in 1 transaction)
        $borrowing = app(BorrowingService::class)->borrow(
            staff: $admin,
            memberId: $member1->id,
            items: [
                ['book_id' => $book1->id, 'quantity' => 2],
                ['book_id' => $book2->id, 'quantity' => 1],
            ],
            requestKey: (string) Str::uuid()
        );

        // Check KPIs after borrowing: available stock = 8 - 3 = 5; borrowed copies = 3 (copies, NOT 1 transaction!)
        $afterBorrowResponse = $this->actingAs($admin)->get(route('dashboard'));
        $afterBorrowResponse->assertOk();
        $afterBorrowResponse->assertViewHas('totalBookTitles', 2);
        $afterBorrowResponse->assertViewHas('totalAvailableStock', 5);
        $afterBorrowResponse->assertViewHas('totalActiveMembers', 2);
        $afterBorrowResponse->assertViewHas('totalBorrowedCopies', 3);

        // Return the borrowing
        app(ReturnService::class)->returnBorrowing($borrowing, $admin);

        // Check KPIs after return: available stock = 8; borrowed copies = 0
        $afterReturnResponse = $this->actingAs($admin)->get(route('dashboard'));
        $afterReturnResponse->assertOk();
        $afterReturnResponse->assertViewHas('totalBookTitles', 2);
        $afterReturnResponse->assertViewHas('totalAvailableStock', 8);
        $afterReturnResponse->assertViewHas('totalActiveMembers', 2);
        $afterReturnResponse->assertViewHas('totalBorrowedCopies', 0);
    }

    /**
     * TEST-016: All 15 screens render with proper Indonesian lang attribute, semantic H1, role-aware sidebar, and overdue badge.
     */
    public function test_016_all_screens_render_cleanly_with_role_aware_navigation_and_accessibility_semantics(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@perpustakaan.local')->firstOrFail();
        $petugas = User::where('email', 'petugas@perpustakaan.local')->firstOrFail();
        $category = Category::firstOrFail();
        $book = Book::firstOrFail();
        $member = Member::firstOrFail();
        $borrowing = Borrowing::where('status', Borrowing::STATUS_BORROWED)->firstOrFail();

        // SCR-001 Login
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('lang="id"', false)
            ->assertSee('<h1', false)
            ->assertSee('Masuk');

        // Admin can access SCR-002..SCR-015
        $adminScreens = [
            route('dashboard'),
            route('users.index'),
            route('users.create'),
            route('users.edit', $petugas),
            route('categories.index'),
            route('categories.create'),
            route('categories.edit', $category),
            route('books.index'),
            route('books.create'),
            route('books.manage', $book),
            route('books.edit', $book),
            route('members.index'),
            route('members.create'),
            route('members.show', $member),
            route('members.edit', $member),
            route('borrowings.index'),
            route('borrowings.create'),
            route('borrowings.show', $borrowing),
        ];

        foreach ($adminScreens as $url) {
            $res = $this->actingAs($admin)->get($url);
            $res->assertOk();
            $res->assertSee('lang="id"', false);
            $res->assertSee('<h1', false);
        }

        // Petugas navigation does not render "Pengguna" menu link
        $petugasDashboard = $this->actingAs($petugas)->get(route('dashboard'));
        $petugasDashboard->assertOk();
        $petugasDashboard->assertDontSee(route('users.index'));

        // Overdue borrowing displays both "Dipinjam" and derived "Terlambat" badges
        $overdueBorrowing = Borrowing::where('transaction_code', 'BRW-20261105-0002')->firstOrFail();
        $this->assertTrue($overdueBorrowing->isOverdue());

        $this->actingAs($petugas)->get(route('borrowings.show', $overdueBorrowing))
            ->assertOk()
            ->assertSee('Dipinjam')
            ->assertSee('Terlambat')
            ->assertSee('Kembalikan Semua Buku');
    }

    /**
     * TEST-017: DatabaseSeeder runs reproducibly and maintains exact stock invariants across all seeded books and borrowings.
     */
    public function test_017_seeder_reproducibility_and_stock_invariants(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(2, User::count());
        $this->assertGreaterThanOrEqual(5, Category::count());
        $this->assertGreaterThanOrEqual(6, Book::count());
        $this->assertGreaterThanOrEqual(4, Member::count());
        $this->assertGreaterThanOrEqual(3, Borrowing::count());

        // Verify stock invariant for every seeded book: 0 <= stock_available <= stock_total
        // and (stock_total - stock_available) == sum of active borrowed quantities for that book
        foreach (Book::all() as $book) {
            $this->assertGreaterThanOrEqual(0, $book->stock_available);
            $this->assertLessThanOrEqual($book->stock_total, $book->stock_available);

            $activeBorrowedQty = (int) BorrowingItem::where('book_id', $book->id)
                ->whereHas('borrowing', fn ($q) => $q->where('status', Borrowing::STATUS_BORROWED))
                ->sum('quantity');

            $this->assertSame($book->stock_total - $book->stock_available, $activeBorrowedQty);
        }
    }
}

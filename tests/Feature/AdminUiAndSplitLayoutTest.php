<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUiAndSplitLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_sidebar_contains_all_restructured_menus(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Manajemen Buku');
        $response->assertSee('Data Buku');
        $response->assertSee('Kategori Buku');
        $response->assertSee('Rak Buku');
        $response->assertSee('Peminjaman Buku');
        $response->assertSee('Penerbit');
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Anggota');
        $response->assertSee('Admin &amp; Petugas', false);
    }

    public function test_petugas_sidebar_hides_admin_only_menus(): void
    {
        $petugas = User::where('role', User::ROLE_PETUGAS)->firstOrFail();

        $response = $this->actingAs($petugas)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Manajemen Buku');
        $response->assertSee('Data Buku');
        $response->assertSee('Kategori Buku');
        $response->assertSee('Rak Buku');
        $response->assertSee('Peminjaman Buku');
        $response->assertSee('Penerbit');
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Anggota');
        // Admin & Petugas must NOT be visible to Petugas
        $response->assertDontSee('Admin &amp; Petugas', false);
    }

    public function test_split_layout_renders_on_book_create_and_edit(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $category = Category::firstOrFail();
        $shelf = Shelf::firstOrCreate(['code' => 'RAK-01'], ['name' => 'Rak Utama', 'location' => 'Lantai 1']);
        $publisher = Publisher::firstOrCreate(['name' => 'Pustaka Utama']);

        // 1. Create Book Split Layout
        $createRes = $this->actingAs($admin)->get(route('books.create'));
        $createRes->assertOk();
        $createRes->assertSee('Cover Buku');
        $createRes->assertSee('preview-type-badge', false);
        $createRes->assertSee('preview-stock-panel', false);
        $createRes->assertSee('Jenis Koleksi');
        $createRes->assertSee('Rak Buku');
        $createRes->assertSee('Sinopsis / Ringkasan Buku');

        // 2. Create Physical Book
        $book = Book::create([
            'title' => 'Buku Uji Split Layout',
            'author' => 'Penulis Split',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'total_copies' => 10,
            'available_copies' => 10,
            'status' => 'active',
            'synopsis' => 'Sinopsis buku uji.',
        ]);

        // 3. Edit Book Split Layout
        $editRes = $this->actingAs($admin)->get(route('books.edit', $book));
        $editRes->assertOk();
        $editRes->assertSee('Edit Koleksi Buku');
        $editRes->assertSee('Cover Buku');
        $editRes->assertSee('Buku Uji Split Layout');
        $editRes->assertSee('Informasi Inventaris Fisik');
    }

    public function test_centralized_data_buku_table_view_and_filters(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $category = Category::firstOrFail();

        $physicalBook = Book::create([
            'title' => 'Fisik Master Test',
            'author' => 'Author Fisik',
            'collection_type' => 'physical',
            'category_id' => $category->id,
            'total_copies' => 5,
            'available_copies' => 5,
            'status' => 'active',
        ]);

        $ebook = Book::create([
            'title' => 'Ebook Master Test',
            'author' => 'Author Ebook',
            'collection_type' => 'e-book',
            'category_id' => $category->id,
            'status' => 'active',
            'read_url' => 'https://example.com/read',
        ]);

        $response = $this->actingAs($admin)->get(route('books.index'));
        $response->assertOk();
        $response->assertSee('Data Buku');
        $response->assertSee('Tambah Buku');
        $response->assertSee('Fisik Master Test');
        $response->assertSee('Ebook Master Test');
        // E-book shows Digital badge, not fake 0 stock
        $response->assertSee('Digital');
        $response->assertSee('Buku Fisik');
        // Actions
        $response->assertSee('Detail');
        $response->assertSee('Edit');
        $response->assertSee('Hapus');
    }

    public function test_borrowing_management_page_actions_and_direct_borrow(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $member = Member::firstOrFail();
        $category = Category::firstOrFail();
        $book = Book::firstOrCreate(
            ['title' => 'Buku Fisik Sirkulasi Uji'],
            [
                'author' => 'Penulis Sirkulasi',
                'collection_type' => 'physical',
                'category_id' => $category->id,
                'total_copies' => 5,
                'available_copies' => 5,
                'stock_total' => 5,
                'stock_available' => 5,
                'status' => 'active',
            ]
        );

        // Pending Borrowing
        $pendingLoan = Borrowing::create([
            'transaction_code' => 'TRX-TEST-PENDING',
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', 'test'),
            'member_id' => $member->id,
            'member_name_snapshot' => $member->name,
            'book_id' => $book->id,
            'status' => Borrowing::STATUS_PENDING,
            'total_quantity' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('borrowings.index'));
        $response->assertOk();
        $response->assertSee('TRX-TEST-PENDING');
        // Pending action buttons
        $response->assertSee('Setujui');
        $response->assertSee('Tolak');
        // Direct borrow walk-in modal button
        $response->assertSee('Peminjaman Langsung (Walk-in)');
    }

    public function test_shelves_and_publishers_management_views_render(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $shelf = Shelf::create([
            'code' => 'RAK-TEST-99',
            'name' => 'Rak Eksperimental',
            'location' => 'Lantai 3',
        ]);

        $publisher = Publisher::create([
            'name' => 'Penerbit Eksperimental',
            'city' => 'Bandung',
            'website' => 'https://example.com',
        ]);

        // Shelves index
        $shelfRes = $this->actingAs($admin)->get(route('shelves.index'));
        $shelfRes->assertOk();
        $shelfRes->assertSee('RAK-TEST-99');
        $shelfRes->assertSee('Rak Eksperimental');

        // Publishers index
        $pubRes = $this->actingAs($admin)->get(route('publishers.index'));
        $pubRes->assertOk();
        $pubRes->assertSee('Penerbit Eksperimental');
        $pubRes->assertSee('Bandung');
    }
}

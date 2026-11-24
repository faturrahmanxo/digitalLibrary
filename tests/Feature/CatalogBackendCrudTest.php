<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogBackendCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_shelves_index_and_search_for_internal_staff(): void
    {
        $petugas = User::factory()->petugas()->create();
        Shelf::create(['code' => 'RAK-A1', 'name' => 'Rak Komputer', 'location' => 'Lantai 1']);
        Shelf::create(['code' => 'RAK-B2', 'name' => 'Rak Sastra', 'location' => 'Lantai 2']);

        $response = $this->actingAs($petugas)->get(route('shelves.index', ['q' => 'Komputer']));
        $response->assertOk();
        $response->assertSee('RAK-A1');
        $response->assertDontSee('RAK-B2');
    }

    public function test_admin_can_create_and_update_shelf(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('shelves.store'), [
            'code' => 'RAK-TEC-01',
            'name' => 'Rak Teknologi',
            'location' => 'Sayap Barat Lantai 2',
            'notes' => 'Buku sains dan komputer',
        ]);
        $response->assertRedirect(route('shelves.index'));

        $shelf = Shelf::where('code', 'RAK-TEC-01')->firstOrFail();
        $this->assertSame('Rak Teknologi', $shelf->name);

        // Update shelf
        $updateResponse = $this->actingAs($admin)->put(route('shelves.update', $shelf), [
            'code' => 'RAK-TEC-01',
            'name' => 'Rak Teknologi & Digital',
            'location' => 'Sayap Barat Lantai 2',
        ]);
        $updateResponse->assertRedirect(route('shelves.index'));
        $this->assertSame('Rak Teknologi & Digital', $shelf->fresh()->name);
    }

    public function test_shelf_code_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Shelf::create(['code' => 'RAK-DUP', 'name' => 'Rak 1']);

        $response = $this->actingAs($admin)->post(route('shelves.store'), [
            'code' => 'RAK-DUP',
            'name' => 'Rak 2',
        ]);
        $response->assertSessionHasErrors('code');
    }

    public function test_cannot_delete_shelf_with_attached_books(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $shelf = Shelf::create(['code' => 'RAK-IN-USE', 'name' => 'Rak Terpakai']);

        Book::factory()->create([
            'category_id' => $category->id,
            'shelf_id' => $shelf->id,
            'collection_type' => 'physical',
            'stock_total' => 2,
            'stock_available' => 2,
        ]);

        $response = $this->actingAs($admin)->delete(route('shelves.destroy', $shelf));
        $response->assertSessionHasErrors('shelf');
        $this->assertDatabaseHas('shelves', ['id' => $shelf->id]);

        // Delete without attached books succeeds
        $emptyShelf = Shelf::create(['code' => 'RAK-EMPTY', 'name' => 'Rak Kosong']);
        $deleteResponse = $this->actingAs($admin)->delete(route('shelves.destroy', $emptyShelf));
        $deleteResponse->assertRedirect(route('shelves.index'));
        $this->assertDatabaseMissing('shelves', ['id' => $emptyShelf->id]);
    }

    public function test_petugas_cannot_delete_shelf_due_to_policy(): void
    {
        $petugas = User::factory()->petugas()->create();
        $shelf = Shelf::create(['code' => 'RAK-PETUGAS', 'name' => 'Rak']);

        $response = $this->actingAs($petugas)->delete(route('shelves.destroy', $shelf));
        $response->assertForbidden();
    }

    public function test_publishers_crud_and_safe_delete(): void
    {
        $admin = User::factory()->admin()->create();

        // Create publisher
        $response = $this->actingAs($admin)->post(route('publishers.store'), [
            'name' => 'Penerbit Erlangga',
            'city' => 'Jakarta',
            'website' => 'https://erlangga.co.id',
            'notes' => 'Penerbit buku pendidikan',
        ]);
        $response->assertRedirect(route('publishers.index'));

        $publisher = Publisher::where('name', 'Penerbit Erlangga')->firstOrFail();
        $this->assertSame('Jakarta', $publisher->city);

        // Update publisher
        $this->actingAs($admin)->put(route('publishers.update', $publisher), [
            'name' => 'Penerbit Erlangga Mahameru',
            'city' => 'Jakarta Barat',
            'website' => 'https://erlangga.co.id',
        ])->assertRedirect(route('publishers.index'));
        $this->assertSame('Penerbit Erlangga Mahameru', $publisher->fresh()->name);

        // Attach book to publisher -> safe delete prevents destructive deletion
        $category = Category::factory()->create();
        Book::factory()->create([
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'collection_type' => 'physical',
        ]);

        $this->actingAs($admin)->delete(route('publishers.destroy', $publisher))
            ->assertSessionHasErrors('publisher');
        $this->assertDatabaseHas('publishers', ['id' => $publisher->id]);

        // Unused publisher can be safely deleted
        $unusedPub = Publisher::create(['name' => 'Penerbit Kosong']);
        $this->actingAs($admin)->delete(route('publishers.destroy', $unusedPub))
            ->assertRedirect(route('publishers.index'));
        $this->assertDatabaseMissing('publishers', ['id' => $unusedPub->id]);
    }

    public function test_publisher_website_validates_scheme_and_ssrf(): void
    {
        $admin = User::factory()->admin()->create();

        // Reject javascript scheme
        $this->actingAs($admin)->post(route('publishers.store'), [
            'name' => 'Bad Publisher 1',
            'website' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('website');

        // Reject localhost / SSRF
        $this->actingAs($admin)->post(route('publishers.store'), [
            'name' => 'Bad Publisher 2',
            'website' => 'http://localhost/secret',
        ])->assertSessionHasErrors('website');

        // Reject cloud metadata SSRF
        $this->actingAs($admin)->post(route('publishers.store'), [
            'name' => 'Bad Publisher 3',
            'website' => 'http://169.254.169.254/latest/meta-data',
        ])->assertSessionHasErrors('website');
    }

    public function test_book_crud_physical_allows_nullable_isbn_and_shelf(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $shelf = Shelf::create(['code' => 'R-PHY-1', 'name' => 'Rak Fisik 1']);
        $publisher = Publisher::create(['name' => 'Gramedia']);

        $response = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Struktur Data & Algoritma',
            'author' => 'Prof. Haryanto',
            'category_id' => $category->id,
            'collection_type' => 'physical',
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'stock_total' => 10,
            'isbn' => null, // Nullable ISBN
            'publication_year' => 2023,
            'status' => 'active',
        ]);

        $book = Book::where('title', 'Struktur Data & Algoritma')->firstOrFail();
        $response->assertRedirect(route('books.index'));

        $this->assertSame(10, $book->stock_total);
        $this->assertSame(10, $book->stock_available);
        $this->assertSame($shelf->id, $book->shelf_id);
        $this->assertSame($publisher->id, $book->publisher_id);
        $this->assertNull($book->isbn);
    }

    public function test_physical_book_prohibits_read_url_and_download_url(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Buku Fisik Ilegal URL',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'collection_type' => 'physical',
            'stock_total' => 5,
            'read_url' => 'https://example.com/read',
            'download_url' => 'https://example.com/download.pdf',
        ]);

        $response->assertSessionHasErrors(['read_url', 'download_url']);
    }

    public function test_ebook_crud_rejects_physical_shelf_and_prohibits_invalid_url(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $shelf = Shelf::create(['code' => 'R-EB-1', 'name' => 'Rak']);

        // Ebook cannot have physical shelf
        $shelfResponse = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Digital Computing Guide',
            'author' => 'Alan Turing',
            'category_id' => $category->id,
            'collection_type' => 'ebook',
            'shelf_id' => $shelf->id,
            'read_url' => 'https://example.com/read',
        ]);
        $shelfResponse->assertSessionHasErrors('shelf_id');

        // Ebook rejects SSRF and javascript URLs
        $badUrlResponse = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Digital Computing Guide',
            'author' => 'Alan Turing',
            'category_id' => $category->id,
            'collection_type' => 'ebook',
            'read_url' => 'javascript:alert(1)',
            'download_url' => 'http://127.0.0.1/admin',
        ]);
        $badUrlResponse->assertSessionHasErrors(['read_url', 'download_url']);

        // Valid Ebook
        $validResponse = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Digital Computing Guide',
            'author' => 'Alan Turing',
            'category_id' => $category->id,
            'collection_type' => 'ebook',
            'read_url' => 'https://books.example.com/view/computing',
            'download_url' => 'https://books.example.com/download/computing.pdf',
            'status' => 'active',
        ]);

        $ebook = Book::where('title', 'Digital Computing Guide')->firstOrFail();
        $validResponse->assertRedirect(route('books.index'));
        $this->assertNull($ebook->shelf_id);
        $this->assertSame('https://books.example.com/view/computing', $ebook->read_url);
        $this->assertSame('https://books.example.com/download/computing.pdf', $ebook->download_url);
    }

    public function test_synopsis_html_is_sanitized_against_xss(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $dirtySynopsis = '<p>Buku cerita <b>bagus</b>.</p><script>alert("XSS")</script><img src="x" onerror="stealCookie()">';

        $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Buku Cerita Sanitasi',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'collection_type' => 'physical',
            'stock_total' => 3,
            'synopsis' => $dirtySynopsis,
        ]);

        $book = Book::where('title', 'Buku Cerita Sanitasi')->firstOrFail();
        $this->assertStringNotContainsString('<script>', $book->synopsis);
        $this->assertStringNotContainsString('onerror', $book->synopsis);
        $this->assertStringContainsString('<b>bagus</b>', $book->synopsis);
    }

    public function test_books_manage_route_is_forbidden_to_anggota(): void
    {
        $anggota = User::factory()->anggota()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($anggota)->get(route('books.manage', $book));
        $response->assertForbidden();

        // But staff/admin can access
        $petugas = User::factory()->petugas()->create();
        $staffResponse = $this->actingAs($petugas)->get(route('books.manage', $book));
        $staffResponse->assertOk();
    }

    public function test_books_index_filters_and_search_with_pagination(): void
    {
        $petugas = User::factory()->petugas()->create();
        $cat1 = Category::factory()->create(['name' => 'Filsafat']);
        $cat2 = Category::factory()->create(['name' => 'Teknologi']);
        $shelf = Shelf::create(['code' => 'R-FL-1', 'name' => 'Rak Filsafat']);
        $publisher = Publisher::create(['name' => 'Mizan']);

        $book1 = Book::factory()->create([
            'title' => 'Filsafat Ilmu',
            'author' => 'Jujun S',
            'isbn' => '978-602-1234-56-7',
            'category_id' => $cat1->id,
            'shelf_id' => $shelf->id,
            'publisher_id' => $publisher->id,
            'collection_type' => 'physical',
            'stock_total' => 5,
            'stock_available' => 5,
            'status' => 'active',
        ]);

        $book2 = Book::factory()->create([
            'title' => 'Pemrograman Modern',
            'author' => 'Bjarne S',
            'isbn' => null,
            'category_id' => $cat2->id,
            'collection_type' => 'ebook',
            'stock_total' => 0,
            'stock_available' => 0,
            'status' => 'inactive',
        ]);

        // Search by title
        $res1 = $this->actingAs($petugas)->get(route('books.index', ['q' => 'Filsafat']));
        $res1->assertOk();
        $res1->assertSee('Filsafat Ilmu');
        $res1->assertDontSee('Pemrograman Modern');

        // Filter by collection_type
        $res2 = $this->actingAs($petugas)->get(route('books.index', ['collection_type' => 'ebook']));
        $res2->assertOk();
        $res2->assertSee('Pemrograman Modern');
        $res2->assertDontSee('Filsafat Ilmu');

        // Filter by shelf
        $res3 = $this->actingAs($petugas)->get(route('books.index', ['shelf_id' => $shelf->id]));
        $res3->assertOk();
        $res3->assertSee('Filsafat Ilmu');
        $res3->assertDontSee('Pemrograman Modern');

        // Filter by status
        $res4 = $this->actingAs($petugas)->get(route('books.index', ['status' => 'inactive']));
        $res4->assertOk();
        $res4->assertSee('Pemrograman Modern');
        $res4->assertDontSee('Filsafat Ilmu');
    }
}

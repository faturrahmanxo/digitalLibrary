<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_ebook_metadata_cover_and_category_return_link(): void
    {
        $category = Category::factory()->create(['name' => 'Literasi Digital']);
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'e-book',
            'title' => 'Mengenal Literasi Digital',
            'author' => 'Ayu Penulis',
            'publisher' => 'Litera Press',
            'publication_year' => 2024,
            'isbn' => '978-602-1234-56-7',
            'cover_path' => 'covers/literasi-digital.jpg',
        ]);

        $detailResponse = $this->get(route('books.show', $book));
        $coverUrl = asset('storage/'.$book->cover_path);

        $detailResponse->assertOk()
            ->assertSee('E-BOOK / KEILMUAN')
            ->assertSee('Kembali ke E-book')
            ->assertSee('href="'.route('catalog.index', ['genre' => 'keilmuan-edukasi']).'"', false)
            ->assertSee('Mengenal Literasi Digital')
            ->assertSee('Ayu Penulis')
            ->assertSee('2024')
            ->assertSee('Literasi Digital')
            ->assertSee('Litera Press')
            ->assertSee('978-602-1234-56-7')
            ->assertSee('src="'.$coverUrl.'"', false)
            ->assertSee('alt="Sampul e-book Mengenal Literasi Digital"', false)
            ->assertSee('Tentang Buku')
            ->assertSee('Informasi Buku')
            ->assertSee('Deskripsi buku belum tersedia.')
            ->assertSee('Belum ada file digital yang dapat diakses untuk buku ini.')
            ->assertDontSee('Simpan Buku')
            ->assertSee('detail-year', false)
            ->assertDontSee('Login untuk Mengakses')
            ->assertDontSee('Baca Online')
            ->assertDontSee('Download E-book')
            ->assertDontSee('Baca Selengkapnya')
            ->assertDontSee('Bahasa')
            ->assertDontSee('Format File')
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertDontSee('Stok Fisik')
            ->assertDontSee('Pinjam Fisik')
            ->assertDontSee('Peminjaman Saya')
            ->assertDontSee('Buku referensi komprehensif');

        $this->get(route('catalog.index', ['genre' => 'keilmuan-edukasi']))
            ->assertOk()
            ->assertSee('src="'.$coverUrl.'"', false)
            ->assertSee('Mengenal Literasi Digital');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('src="'.$coverUrl.'"', false)
            ->assertSee('Mengenal Literasi Digital');
    }

    public function test_guest_novel_detail_uses_the_shared_layout_and_novel_category_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'novel',
            'title' => 'Senja di Peron',
            'author' => 'Bima Pencerita',
            'publication_year' => 2023,
        ]);

        $response = $this->get(route('books.show', $book));

        $response->assertOk()
            ->assertSee('NOVEL')
            ->assertSee('Kembali ke Novel')
            ->assertSee('href="'.route('catalog.index', ['genre' => 'novel-fiksi']).'"', false)
            ->assertSee('Senja di Peron')
            ->assertSee('Bima Pencerita')
            ->assertSee('Sastra &amp; Fiksi', false)
            ->assertSee('Tentang Buku')
            ->assertSee('Deskripsi buku belum tersedia.')
            ->assertSee('aria-label="Sampul novel tidak tersedia untuk Senja di Peron"', false)
            ->assertSee('book-cover-fallback', false)
            ->assertSee('LT')
            ->assertSee('Belum ada file digital yang dapat diakses untuk buku ini.')
            ->assertDontSee('Simpan Buku')
            ->assertDontSee('Baca Online')
            ->assertDontSee('Download Novel')
            ->assertDontSee('Buku referensi komprehensif')
            ->assertDontSee('Stok Fisik')
            ->assertDontSee('Pinjam Fisik');
    }

    public function test_login_from_guest_detail_returns_to_that_book_after_authentication(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->anggota()->create();

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSessionHas('url.intended', route('books.show', $book));

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_member_can_save_the_book_and_see_saved_state_in_collection(): void
    {
        $user = User::factory()->anggota()->create();
        $book = Book::factory()->create(['title' => 'Buku Pilihan']);

        $this->actingAs($user)->get(route('books.show', $book))
            ->assertOk()
            ->assertDontSee('Simpan Buku');

        $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('books.favorite', $book))
            ->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user)->get(route('books.show', $book))
            ->assertOk()
            ->assertDontSee('Simpan Buku');

        $this->actingAs($user)
            ->get(route('collection.index', ['tab' => 'daftar-bacaan']))
            ->assertOk()
            ->assertSee('Buku Pilihan');
    }

    public function test_authenticated_reader_request_is_forbidden_without_an_active_borrowing(): void
    {
        $user = User::factory()->anggota()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->get(route('books.read', $book))
            ->assertForbidden();

        $this->actingAs($user)->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Belum ada file digital yang dapat diakses untuk buku ini.')
            ->assertDontSee('Baca Online')
            ->assertDontSee('Download E-book')
            ->assertDontSee('Download Novel');
    }

    public function test_internal_book_management_page_remains_protected(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.manage', $book))
            ->assertRedirect(route('login'));
    }

    public function test_ebook_detail_displays_related_series_and_volumes_section(): void
    {
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);
        $book1 = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'novel',
            'title' => 'Hikayat Samudra - Bagian 1',
            'author' => 'Pramudya',
            'series_title' => 'Hikayat Samudra',
            'volume_label' => 'Volume 1',
            'publication_year' => 2023,
        ]);

        $book2 = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'novel',
            'title' => 'Hikayat Samudra - Bagian 2',
            'author' => 'Pramudya',
            'series_id' => $book1->id,
            'series_title' => 'Hikayat Samudra',
            'volume_label' => 'Volume 2',
            'publication_year' => 2024,
        ]);

        $response = $this->get(route('books.show', $book1));

        $response->assertOk()
            ->assertSee('Bagian Seri Ini')
            ->assertSee('Hikayat Samudra - Bagian 2')
            ->assertSee('Volume 2')
            ->assertSee('Tahun 2024')
            ->assertSee('href="'.route('books.show', $book2).'"', false)
            ->assertSee('Buka Detail');

        // And from book 2 detail, book 1 is shown
        $response2 = $this->get(route('books.show', $book2));
        $response2->assertOk()
            ->assertSee('Bagian Seri Ini')
            ->assertSee('Hikayat Samudra - Bagian 1')
            ->assertSee('Volume 1')
            ->assertSee('Tahun 2023')
            ->assertSee('href="'.route('books.show', $book1).'"', false);
    }
}

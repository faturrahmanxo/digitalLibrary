<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EbookCardRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_book_cards_link_to_the_public_detail_page(): void
    {
        $category = Category::factory()->create(['name' => 'Teknologi']);
        $ebook = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'e-book',
            'title' => 'Panduan Data',
            'author' => 'Ayu Penulis',
            'publication_year' => 2025,
            'cover_path' => 'covers/panduan-data.jpg',
        ]);
        $novel = Book::factory()->create([
            'collection_type' => 'novel',
            'title' => 'Novel Senja',
        ]);

        $response = $this->get(route('catalog.index'));

        $response->assertOk()
            ->assertSee('href="'.route('books.show', $ebook).'"', false)
            ->assertSee('alt="Sampul e-book Panduan Data"', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('object-cover', false)
            ->assertSee('Panduan Data')
            ->assertSee('2025')
            ->assertDontSee('Ayu Penulis')
            ->assertSee('Teknologi')
            ->assertSee('href="'.route('books.show', $novel).'"', false);
    }

    public function test_ebook_card_shows_fallback_cover_and_omits_empty_category_badge(): void
    {
        $category = Category::factory()->create(['name' => '']);
        Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'e-book',
            'title' => 'Tanpa Sampul',
            'cover_path' => null,
        ]);

        $response = $this->get(route('catalog.index'));

        $response->assertOk()
            ->assertSee('aria-label="Sampul e-book tidak tersedia untuk Tanpa Sampul"', false)
            ->assertSee('book-cover-fallback', false)
            ->assertSee('LT')
            ->assertDontSee('rounded-full border border-brand-200/70 bg-brand-50', false);
    }

    public function test_landing_page_displays_five_latest_ebook_cards(): void
    {
        $category = Category::factory()->create(['name' => 'Literasi Digital']);
        $titles = [
            'Panduan Belajar Digital Satu',
            'Panduan Belajar Digital Dua',
            'Panduan Belajar Digital Tiga',
            'Panduan Belajar Digital Empat',
            'Panduan Belajar Digital Lima',
        ];

        foreach ($titles as $title) {
            Book::factory()->create([
                'category_id' => $category->id,
                'collection_type' => 'e-book',
                'title' => $title,
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('E-Book Terbaru')
            ->assertSee('Koleksi buku digital yang bisa dibaca secara online atau diakses langsung secara digital')
            ->assertSee('Lihat Semua E-Book');
        foreach ($titles as $title) {
            $response->assertSee($title);
        }
    }

    public function test_landing_page_displays_five_latest_physical_books_section(): void
    {
        $category = Category::factory()->create(['name' => 'Sains & Matematika']);
        $titles = [
            'Fisika Dasar Eksperimental 1',
            'Fisika Dasar Eksperimental 2',
            'Fisika Dasar Eksperimental 3',
            'Fisika Dasar Eksperimental 4',
            'Fisika Dasar Eksperimental 5',
        ];

        foreach ($titles as $title) {
            Book::factory()->create([
                'category_id' => $category->id,
                'collection_type' => 'physical',
                'title' => $title,
                'author' => 'Prof. Fisika',
                'stock_available' => 5,
                'available_copies' => 5,
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Buku Fisik Terbaru')
            ->assertSee('Koleksi buku fisik terbaru yang tersedia di rak perpustakaan')
            ->assertSee('Jelajahi Buku Fisik');
        foreach ($titles as $title) {
            $response->assertSee($title);
        }
    }

    public function test_keilmuan_genre_includes_literasi_digital_ebooks(): void
    {
        $category = Category::factory()->create(['name' => 'Literasi Digital']);
        $ebook = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'e-book',
            'title' => 'Riset Literasi Digital',
        ]);

        $response = $this->get(route('catalog.index', ['genre' => 'keilmuan-edukasi']));

        $response->assertOk()
            ->assertSee($ebook->title)
            ->assertSee('href="'.route('books.show', $ebook).'"', false);
    }

    public function test_landing_page_renders_novels_with_the_shared_card_layout(): void
    {
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);
        $novel = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'novel',
            'title' => 'Novel Senja',
        ]);

        $response = $this->get(route('home'));
        $catalogResponse = $this->get(route('catalog.index', ['genre' => 'novel-fiksi']));

        $response->assertOk()
            ->assertSee('E-Book Terbaru')
            ->assertSee($novel->title)
            ->assertSee('href="'.route('books.show', $novel).'"', false);

        $catalogResponse->assertOk()
            ->assertSee($novel->title)
            ->assertSee('href="'.route('books.show', $novel).'"', false);
    }

    public function test_landing_page_shows_five_latest_novels_and_links_to_the_novel_catalog(): void
    {
        $titles = [];

        foreach (range(1, 6) as $number) {
            $titles[] = 'Novel Pilihan '.$number;

            Book::factory()->create([
                'collection_type' => 'novel',
                'title' => end($titles),
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('E-Book Terbaru');

        foreach (array_slice($titles, 1) as $title) {
            $response->assertSee($title);
        }

        $response->assertDontSee($titles[0]);
    }
}

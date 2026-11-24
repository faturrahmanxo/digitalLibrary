<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_navbar_has_litera_brand_and_navigation_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Litera.');
        $response->assertSee('src="'.asset('images/logo-Litera.png').'"', false);
        $response->assertSee('images/favicon.png');
        $response->assertSee('Beranda');
        $response->assertSee('Jelajahi');
        $response->assertSee('Tentang Kami');
        $response->assertSee('/jelajahi');
        $response->assertSee('genre=novel-fiksi', false);
        $response->assertSee('genre=keilmuan-edukasi', false);
        $response->assertSee('Novel');
        $response->assertSee('Keilmuan');
        $response->assertDontSee('Lihat Semua Koleksi Buku');
        $response->assertDontSee('Koleksi roman, fiksi sastra');
        $response->assertDontSee('Sains, teknologi, riset');
        $response->assertSee('Masuk');
        $response->assertSee('id="theme-toggle-btn"', false);
        $response->assertSee('id="nav-search-toggle-btn"', false);
    }

    public function test_guest_auth_pages_use_the_litera_logo_and_png_favicon(): void
    {
        foreach (['/login', '/register'] as $path) {
            $response = $this->get($path);

            $response->assertOk()
                ->assertSee('src="'.asset('images/logo-Litera.png').'"', false)
                ->assertSee('images/favicon.png');
        }
    }

    public function test_landing_hero_copy_search_and_book_illustration_are_current(): void
    {
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'title' => 'Petualangan Menjelang Senja',
            'author' => 'Ayu Prameswari',
            'collection_type' => 'novel',
        ]);

        $response = $this->get(route('home'));

        $this->assertSame(4, substr_count($response->getContent(), 'hero-decoration-icon'));

        $response->assertOk()
            ->assertSee('PERPUSTAKAAN DIGITAL')
            ->assertSee('Setiap Halaman,')
            ->assertSee('Petualangan Baru.')
            ->assertSee('Pengetahuan Baru.')
            ->assertSee('Cerita Baru.')
            ->assertSee('Dari pengetahuan yang memperluas wawasan hingga cerita yang membawa imajinasi. Temukan bacaan favoritmu dan jelajahi dunia literasi bersama Litera.')
            ->assertSee('JELAJAHI KOLEKSI')
            ->assertSee('Dua Dunia, Satu Perpustakaan.')
            ->assertSee('Jelajahi beragam pengetahuan dan temukan kisah yang menginspirasi, semuanya dalam satu ruang baca digital.')
            ->assertSee('E-book / Keilmuan')
            ->assertSee('Jelajahi Novel')
            ->assertSee('href="'.route('catalog.index', ['genre' => 'keilmuan-edukasi']).'"', false)
            ->assertSee('href="'.route('catalog.index', ['genre' => 'novel-fiksi']).'"', false)
            ->assertSee('card-interactive', false)
            ->assertDontSee('Jam Operasional Perpustakaan')
            ->assertDontSee('id="jam-layanan"', false)
            ->assertSee('action="'.route('catalog.index').'" method="GET"', false)
            ->assertSee('name="q"', false)
            ->assertSee('placeholder="Cari judul buku, novel, atau penulis..."', false)
            ->assertSee('aria-label="Cari judul buku, novel, atau penulis"', false)
            ->assertSee('Telusuri')
            ->assertSee('class="hero-page-left"', false)
            ->assertSee('class="hero-page-right"', false)
            ->assertSee('hero-book-float', false)
            ->assertSee('hero-word-fade-up', false)
            ->assertSee('hero-float-1', false)
            ->assertSee('hero-float-4', false)
            ->assertDontSee('💡')
            ->assertDontSee('📖')
            ->assertDontSee('✨')
            ->assertDontSee('🌟')
            ->assertDontSee('Q 155, 125, 160 165', false)
            ->assertDontSee('hero-float-5', false)
            ->assertDontSee('animate-discipline-1', false)
            ->assertSee('@media (prefers-reduced-motion: reduce)', false);

        $this->get(route('catalog.index', ['q' => 'Petualangan Menjelang Senja']))
            ->assertOk()
            ->assertSee($book->title);
    }

    public function test_authenticated_user_sees_avatar_and_profile_menu(): void
    {
        $user = User::factory()->create([
            'name' => 'Adinda Saraswati',
            'email' => 'adinda@litera.local',
            'role' => User::ROLE_ANGGOTA,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Adinda Saraswati');
        $response->assertSee('Koleksi Saya');
        $response->assertSee('Profil Saya');
        $response->assertSee('Keluar');
        $response->assertSee('href="'.route('collection.index').'"', false);
        $response->assertSee('href="'.route('profile.edit').'"', false);
        $response->assertDontSee('href="'.route('collection.index', ['tab' => 'daftar-bacaan']).'"', false);
        $response->assertDontSee('href="'.route('dashboard').'"', false);
        $response->assertDontSee('Masuk ke Litera');
    }

    public function test_route_jelajahi_and_genre_filtering_work(): void
    {
        $response = $this->get('/jelajahi');
        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false);

        $genreResponse = $this->get('/jelajahi?genre=novel-fiksi');
        $genreResponse->assertOk()
            ->assertSee('id="theme-toggle-btn"', false);
    }

    public function test_landing_page_renders_novel_cards_without_cover_images(): void
    {
        $category = Category::factory()->create([
            'name' => 'Teknologi & Pemrograman',
            'description' => 'Literatur rekayasa perangkat lunak dan arsitektur sistem.',
        ]);

        $book = Book::factory()->create([
            'category_id' => $category->id,
            'collection_type' => 'novel',
            'title' => 'Arsitektur Komputasi Cloud Skala Besar',
            'author' => 'Dr. Hendra Wijaya',
            'publication_year' => 2025,
            'stock_available' => 4,
            'isbn' => '978-602-0001-99-9',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Arsitektur Komputasi Cloud Skala Besar');
        $response->assertSee('2025');
        $response->assertSee('Teknologi &amp; Pemrograman', false);
        $response->assertSee('href="'.route('books.show', $book).'"', false);
        $response->assertSee('aria-label="Sampul novel tidak tersedia untuk Arsitektur Komputasi Cloud Skala Besar"', false);
        // Ensure book cover container aspect ratio is removed
        $response->assertDontSee('aspect-[3/3.8]');
    }
}

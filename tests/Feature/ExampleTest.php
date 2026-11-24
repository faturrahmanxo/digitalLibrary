<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Root URL displays the public campus digital library portal.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $category = Category::create(['name' => 'Informatika']);
        Book::create([
            'category_id' => $category->id,
            'title' => 'Struktur Data dan Algoritma',
            'author' => 'Prof. Dr. Ir. Gunadarma',
            'isbn' => '978-602-0001-01-1',
            'publisher' => 'Penerbit Kampus',
            'publication_year' => 2024,
            'stock_total' => 10,
            'stock_available' => 10,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Litera')
            ->assertSee('Setiap Halaman,')
            ->assertSee('Struktur Data dan Algoritma');
    }

    public function test_guest_reader_request_redirects_to_login_and_remembers_the_book(): void
    {
        $category = Category::create(['name' => 'Sains Komputer']);
        $book = Book::create([
            'category_id' => $category->id,
            'title' => 'Pengenalan Machine Learning',
            'author' => 'Dr. Riset Kampus',
            'isbn' => '978-602-0002-02-2',
            'publisher' => 'Lab Informatika',
            'publication_year' => 2025,
            'stock_total' => 5,
            'stock_available' => 5,
        ]);

        $response = $this->get(route('books.read', $book));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('books.read', $book));
    }

    /**
     * Campus members can toggle favorite books.
     */
    public function test_member_can_toggle_favorite_book(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ANGGOTA]);
        $category = Category::create(['name' => 'Matematika']);
        $book = Book::create([
            'category_id' => $category->id,
            'title' => 'Kalkulus Lanjut',
            'author' => 'Dosen Matematika',
            'isbn' => '978-602-0003-03-3',
            'publisher' => 'Mitra Akademik',
            'publication_year' => 2023,
            'stock_total' => 8,
            'stock_available' => 8,
        ]);

        $response = $this->actingAs($user)->postJson(route('books.favorite', $book));

        $response->assertOk()
            ->assertJson(['status' => 'added']);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Toggle again to remove
        $response2 = $this->actingAs($user)->postJson(route('books.favorite', $book));
        $response2->assertOk()
            ->assertJson(['status' => 'removed']);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Member can access their favorites page.
     */
    public function test_member_can_view_favorites_page(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ANGGOTA]);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertRedirect(route('collection.index', ['tab' => 'daftar-bacaan']));

        $this->actingAs($user)
            ->get(route('collection.index', ['tab' => 'daftar-bacaan']))
            ->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Koleksi Saya')
            ->assertSee('E-book / Keilmuan')
            ->assertSee('Belum ada e-book yang disimpan.')
            ->assertSee('Belum ada novel yang disimpan.');
    }

    public function test_user_can_register_then_sign_in_with_the_new_account(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Akun berhasil dibuat. Silakan masuk dengan email dan kata sandi Anda.');
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'role' => User::ROLE_ANGGOTA,
        ]);

        $this->assertDatabaseHas('members', [
            'email' => 'budi@example.test',
            'name' => 'Budi Santoso',
            'address' => null,
        ]);

        $this->post(route('login.submit'), [
            'email' => 'budi@example.test',
            'password' => 'secret123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_auth_pages_have_theme_controls_and_general_account_copy(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Belum memiliki akun Litera?')
            ->assertDontSee('kampus')
            ->assertDontSee('akademika')
            ->assertDontSee('sivitas');

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Buat Akun Litera')
            ->assertSee('nama@email.com')
            ->assertDontSee('kampus')
            ->assertDontSee('akademika')
            ->assertDontSee('sivitas');
    }

    /**
     * Admin/Petugas redirect to dashboard; Anggota redirects to home.
     */
    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
        ]);

        $responseAdmin = $this->post(route('login.submit'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);
        $responseAdmin->assertRedirect(route('dashboard'));

        $this->post(route('logout'));

        $anggota = User::factory()->create([
            'role' => User::ROLE_ANGGOTA,
            'password' => 'password123',
        ]);

        $responseAnggota = $this->post(route('login.submit'), [
            'email' => $anggota->email,
            'password' => 'password123',
        ]);
        $responseAnggota->assertRedirect(route('home'));
    }

    /**
     * Public campus library profile and history page is accessible.
     */
    public function test_library_profile_page_is_accessible(): void
    {
        $response = $this->get(route('info.profil'));

        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Profil &amp; Sejarah Perpustakaan Kampus', false)
            ->assertSee('Sejarah Perkembangan Perpustakaan')
            ->assertSee('Visi &amp; Misi Perpustakaan', false);
    }

    /**
     * Public campus library services page is accessible.
     */
    public function test_library_services_page_is_accessible(): void
    {
        $response = $this->get(route('info.layanan'));

        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Layanan Perpustakaan Kampus')
            ->assertSee('Layanan Keanggotaan &amp; Sirkulasi', false)
            ->assertSee('E-Resources &amp; Database Jurnal Internasional', false)
            ->assertSee('Repositori Karya Ilmiah &amp; Skripsi', false);
    }
}

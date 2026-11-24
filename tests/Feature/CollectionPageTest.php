<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_collection_page(): void
    {
        $this->get(route('collection.index'))
            ->assertRedirect(route('login'));
    }

    public function test_default_tab_shows_empty_state_without_fake_reading_progress(): void
    {
        $user = User::factory()->anggota()->create();

        $response = $this->actingAs($user)->get(route('collection.index'));

        $response->assertOk()
            ->assertSee('Koleksi Saya')
            ->assertSee('Sedang Dibaca')
            ->assertSee('aria-current="page"', false)
            ->assertSee('Belum ada buku yang sedang dibaca.');
    }

    public function test_collection_page_renders_its_theme_toggle_control(): void
    {
        $user = User::factory()->anggota()->create();

        $response = $this->actingAs($user)->get(route('collection.index'));

        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false);
    }

    public function test_reading_list_groups_only_the_authenticated_users_saved_books(): void
    {
        $user = User::factory()->anggota()->create();
        $otherUser = User::factory()->anggota()->create();
        $ebookCategory = Category::factory()->create(['name' => 'Teknologi']);
        $novelCategory = Category::factory()->create(['name' => 'Sastra']);
        $ebook = Book::factory()->create([
            'category_id' => $ebookCategory->id,
            'collection_type' => 'e-book',
            'title' => 'Riset Digital',
            'author' => 'Ayu Penulis',
        ]);
        $novel = Book::factory()->create([
            'category_id' => $novelCategory->id,
            'collection_type' => 'novel',
            'title' => 'Senja di Peron',
            'author' => 'Bima Pencerita',
        ]);
        $otherUserBook = Book::factory()->create([
            'collection_type' => 'e-book',
            'title' => 'Bukan Milik Saya',
        ]);
        $archivedBook = Book::factory()->create([
            'collection_type' => 'e-book',
            'title' => 'Buku yang Diarsipkan',
        ]);

        $user->favoriteBooks()->attach([$ebook->id, $novel->id, $archivedBook->id]);
        $otherUser->favoriteBooks()->attach($otherUserBook);
        $archivedBook->delete();

        $response = $this->actingAs($user)->get(route('collection.index', ['tab' => 'daftar-bacaan']));
        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('E-book / Keilmuan')
            ->assertSee('Novel')
            ->assertSee('Riset Digital')
            ->assertSee('Ayu Penulis')
            ->assertSee('Teknologi')
            ->assertSee('Senja di Peron')
            ->assertSee('Bima Pencerita')
            ->assertSee('href="'.route('books.show', $ebook).'"', false)
            ->assertSee('href="'.route('books.show', $novel).'"', false)
            ->assertSee('aria-current="page"', false)
            ->assertDontSee('Bukan Milik Saya')
            ->assertDontSee('Buku yang Diarsipkan');

        $ebookHeadingPosition = strpos($content, '<h2 id="ebooks-heading"');
        $novelHeadingPosition = strpos($content, '<h2 id="novels-heading"');

        $this->assertIsInt($ebookHeadingPosition);
        $this->assertIsInt($novelHeadingPosition);
        $this->assertLessThan($novelHeadingPosition, $ebookHeadingPosition);
    }

    public function test_finished_tab_shows_its_empty_state(): void
    {
        $user = User::factory()->anggota()->create();

        $response = $this->actingAs($user)->get(route('collection.index', ['tab' => 'selesai-dibaca']));

        $response->assertOk()
            ->assertSee('Belum ada buku yang selesai dibaca.')
            ->assertSee('aria-current="page"', false);
    }

    public function test_unknown_collection_tab_returns_not_found(): void
    {
        $user = User::factory()->anggota()->create();

        $this->actingAs($user)
            ->get(route('collection.index', ['tab' => 'bukan-tab']))
            ->assertNotFound();
    }
}

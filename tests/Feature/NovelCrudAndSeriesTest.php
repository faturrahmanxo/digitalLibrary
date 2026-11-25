<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelCrudAndSeriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_petugas_can_create_novel_with_series_and_volume(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);
        $publisher = Publisher::factory()->create(['name' => 'Bentang Pustaka']);

        $response = $this->actingAs($admin)->post(route('books.store'), [
            'title' => 'Laskar Pelangi Volume 1',
            'author' => 'Andrea Hirata',
            'collection_type' => 'novel',
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2005,
            'series_title' => 'Tetralogi Laskar Pelangi',
            'volume_number' => 1,
            'volume_label' => 'Volume 1',
            'edition' => 'Edisi Pertama',
            'read_url' => 'https://example.com/baca/laskar-pelangi-1',
            'download_url' => 'https://example.com/download/laskar-pelangi-1.pdf',
            'status' => 'active',
            'synopsis' => 'Kisah perjuangan sepuluh anak di Belitung.',
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Laskar Pelangi Volume 1',
            'collection_type' => 'novel',
            'series_title' => 'Tetralogi Laskar Pelangi',
            'volume_number' => 1,
            'volume_label' => 'Volume 1',
            'edition' => 'Edisi Pertama',
        ]);
    }

    public function test_novel_with_same_series_renders_in_related_volumes_section(): void
    {
        $category = Category::factory()->create(['name' => 'Sastra & Fiksi']);

        $volume1 = Book::factory()->create([
            'title' => 'Laskar Pelangi Vol 1',
            'author' => 'Andrea Hirata',
            'collection_type' => 'novel',
            'category_id' => $category->id,
            'series_title' => 'Tetralogi Laskar Pelangi',
            'volume_number' => 1,
            'volume_label' => 'Volume 1',
            'read_url' => 'https://example.com/read1',
            'status' => 'active',
        ]);

        $volume2 = Book::factory()->create([
            'title' => 'Sang Pemimpi Vol 2',
            'author' => 'Andrea Hirata',
            'collection_type' => 'novel',
            'category_id' => $category->id,
            'series_title' => 'Tetralogi Laskar Pelangi',
            'volume_number' => 2,
            'volume_label' => 'Volume 2',
            'read_url' => 'https://example.com/read2',
            'status' => 'active',
        ]);

        $response = $this->get(route('books.show', $volume1));

        $response->assertOk();
        $response->assertSee('Laskar Pelangi Vol 1');
        $response->assertSee('Bagian Seri Ini');
        $response->assertSee('Sang Pemimpi Vol 2');
        $response->assertSee('Volume 2');
        $response->assertSee(route('books.show', $volume2));
    }

    public function test_catalog_pages_render_unified_hero_and_streamlined_filter(): void
    {
        $physicalResponse = $this->get(route('catalog.index', ['collection_type' => 'physical']));
        $physicalResponse->assertOk();
        $physicalResponse->assertSee('Katalog Buku Fisik');
        $physicalResponse->assertSee('Koleksi Fisik Perpustakaan');
        $physicalResponse->assertSee('Tersedia di Rak Kampus.');

        $ebookResponse = $this->get(route('catalog.index', ['collection_type' => 'ebook']));
        $ebookResponse->assertOk();
        $ebookResponse->assertSee('Katalog E-Book');
        $ebookResponse->assertSee('Pustaka Digital Kampus');
        $ebookResponse->assertSee('Akses Digital & Kapan Saja.');
    }

    public function test_borrowing_detail_page_uses_plus_jakarta_sans(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $member = Member::factory()->create();
        $book = Book::factory()->create(['collection_type' => 'physical']);

        $borrowing = Borrowing::factory()->create([
            'transaction_code' => 'TRX-TEST-001',
            'member_id' => $member->id,
            'staff_id' => $admin->id,
            'book_id' => $book->id,
            'member_name_snapshot' => $member->name,
            'status' => Borrowing::STATUS_BORROWED,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($admin)->get(route('borrowings.show', $borrowing));

        $response->assertOk();
        $response->assertSee('loan-detail-plus-jakarta', false);
        $response->assertSee('Plus Jakarta Sans', false);
        $response->assertSee('Detail Peminjaman TRX-TEST-001');
    }
}

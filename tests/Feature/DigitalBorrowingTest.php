<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\NovelVolume;
use App\Models\User;
use App\Services\DigitalBorrowingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalBorrowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_digital_book_metadata_but_must_authenticate_to_borrow_or_download(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-books/ebook.pdf', "%PDF-1.4\nDigital content");
        $book = $this->createEbook(['digital_file_path' => 'digital-books/ebook.pdf']);

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee($book->title)
            ->assertSee('Login untuk Meminjam E-book')
            ->assertDontSee('digital-books/ebook.pdf');

        $this->post(route('books.digital.borrow', $book))->assertRedirect(route('login'));
        $this->get(route('books.digital.download', $book))->assertRedirect(route('login'));
        $this->get(route('books.read', $book))->assertRedirect(route('login'));

        $this->assertDatabaseCount('borrowings', 0);
    }

    public function test_ebook_borrowing_reserves_a_license_for_seven_days_without_changing_physical_stock(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-books/ebook.pdf', "%PDF-1.4\nDigital content");
        $this->travelTo(Carbon::create(2025, 3, 1, 10, 0, 0, 'Asia/Jakarta'));

        $book = $this->createEbook([
            'digital_file_path' => 'digital-books/ebook.pdf',
            'stock_total' => 4,
            'stock_available' => 4,
        ]);
        $member = User::factory()->anggota()->create();
        $otherMember = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('books.digital.borrow', $book))
            ->assertRedirect(route('books.show', $book));

        $borrowing = Borrowing::query()->firstOrFail();
        $this->assertSame(Borrowing::LOAN_TYPE_DIGITAL, $borrowing->loan_type);
        $this->assertSame($member->id, $borrowing->borrower_user_id);
        $this->assertNull($borrowing->staff_id);
        $this->assertSame('2025-03-08', $borrowing->due_at->format('Y-m-d'));
        $this->assertDatabaseHas('borrowing_items', [
            'borrowing_id' => $borrowing->id,
            'book_id' => $book->id,
            'novel_volume_id' => null,
            'quantity' => 1,
        ]);
        $this->assertSame(4, $book->fresh()->stock_available);

        $this->actingAs($member)->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Baca Online')
            ->assertSee('Download E-book')
            ->assertSee('08 Mar 2025');

        $readResponse = $this->actingAs($member)->get(route('books.read', $book))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('private', $readResponse->headers->get('cache-control'));
        $this->assertStringContainsString('no-store', $readResponse->headers->get('cache-control'));
        $this->assertStringNotContainsString('public', $readResponse->headers->get('cache-control'));

        $downloadUrl = route('books.digital.download', $book);
        $this->actingAs($member)->get($downloadUrl)->assertOk();
        $downloadResponse = $this->actingAs($member)->get($downloadUrl)->assertOk();
        $this->assertStringContainsString('private', $downloadResponse->headers->get('cache-control'));
        $this->assertStringContainsString('no-store', $downloadResponse->headers->get('cache-control'));
        $this->assertDatabaseCount('borrowings', 1);

        $this->actingAs($otherMember)
            ->from(route('books.show', $book))
            ->post(route('books.digital.borrow', $book))
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHasErrors('book');
        $this->actingAs($otherMember)
            ->get(route('books.digital.download', $book))
            ->assertForbidden();

        $this->assertDatabaseCount('borrowings', 1);
        $this->assertSame(0, app(DigitalBorrowingService::class)->availableLicensesForBook($book->fresh()));
    }

    public function test_ebook_duplicate_loans_and_license_reduction_below_active_loans_are_rejected(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-books/ebook.pdf', "%PDF-1.4\nDigital content");
        $book = $this->createEbook([
            'digital_license_count' => 2,
            'digital_file_path' => 'digital-books/ebook.pdf',
            'stock_total' => 2,
            'stock_available' => 2,
        ]);
        $member = User::factory()->anggota()->create();
        $staff = User::factory()->petugas()->create();

        $this->actingAs($member)->post(route('books.digital.borrow', $book))->assertRedirect();
        $borrowing = Borrowing::query()->firstOrFail();

        $this->actingAs($member)
            ->from(route('books.show', $book))
            ->post(route('books.digital.borrow', $book))
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHasErrors('book');

        $this->actingAs($staff)
            ->from(route('books.edit', $book))
            ->put(route('books.update', $book), [
                'title' => $book->title,
                'author' => $book->author,
                'collection_type' => 'e-book',
                'category_id' => $book->category_id,
                'stock_total' => $book->stock_total,
                'digital_license_count' => 0,
                'digital_lending_allowed' => 1,
            ])
            ->assertRedirect(route('books.edit', $book))
            ->assertSessionHasErrors('digital_license_count');

        $this->assertSame(2, $book->fresh()->digital_license_count);
        $this->assertSame(1, Borrowing::count());

        $this->actingAs($member)
            ->post(route('borrowings.return', $borrowing))
            ->assertRedirect(route('borrowings.show', $borrowing));

        $this->assertSame(Borrowing::STATUS_RETURNED, $borrowing->fresh()->status);
        $this->assertSame(2, $book->fresh()->stock_available);
        $this->assertSame(2, app(DigitalBorrowingService::class)->availableLicensesForBook($book->fresh()));
    }

    public function test_expired_ebook_borrowing_releases_its_license_and_denies_file_access(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-books/ebook.pdf', "%PDF-1.4\nDigital content");
        $this->travelTo(Carbon::create(2025, 3, 1, 10, 0, 0, 'Asia/Jakarta'));
        $book = $this->createEbook(['digital_file_path' => 'digital-books/ebook.pdf']);
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)->post(route('books.digital.borrow', $book))->assertRedirect();
        $this->travelTo(Carbon::create(2025, 3, 9, 10, 0, 0, 'Asia/Jakarta'));

        $this->actingAs($member)->get(route('books.digital.download', $book))->assertForbidden();
        $this->assertSame(1, app(DigitalBorrowingService::class)->availableLicensesForBook($book->fresh()));

        $this->actingAs($member)
            ->post(route('books.digital.borrow', $book))
            ->assertRedirect(route('books.show', $book));

        $this->assertDatabaseCount('borrowings', 2);
    }

    public function test_novel_volume_licenses_are_independent_and_volume_crud_preserves_active_borrowing_invariants(): void
    {
        Storage::fake('local');
        $staff = User::factory()->petugas()->create();
        $book = Book::factory()->create([
            'collection_type' => 'novel',
            'title' => 'Cerita dari Selatan',
            'digital_license_count' => 0,
            'digital_lending_allowed' => false,
        ]);

        $createData = [
            'volume_number' => 1,
            'title' => 'Awal Perjalanan',
            'digital_license_count' => 1,
            'digital_lending_allowed' => 1,
            'is_published' => 1,
            'digital_file' => UploadedFile::fake()->createWithContent('volume-1.pdf', "%PDF-1.4\nVolume 1"),
        ];

        $this->actingAs($staff)
            ->post(route('books.volumes.store', $book), $createData)
            ->assertRedirect(route('books.volumes.index', $book));

        $volumeOne = NovelVolume::query()->where('book_id', $book->id)->firstOrFail();
        Storage::disk('local')->assertExists($volumeOne->digital_file_path);
        $this->assertSame(1, $volumeOne->volume_number);

        $this->actingAs($staff)
            ->from(route('books.volumes.create', $book))
            ->post(route('books.volumes.store', $book), [
                'volume_number' => 1,
                'title' => 'Nomor Duplikat',
                'digital_license_count' => 1,
                'digital_lending_allowed' => 0,
                'is_published' => 0,
            ])
            ->assertRedirect(route('books.volumes.create', $book))
            ->assertSessionHasErrors('volume_number');

        $this->actingAs($staff)
            ->put(route('books.volumes.update', [$book, $volumeOne]), [
                'volume_number' => 1,
                'title' => 'Judul Diperbarui',
                'digital_license_count' => 2,
                'digital_lending_allowed' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('books.volumes.index', $book));
        $volumeOne->refresh();
        $this->assertSame('Judul Diperbarui', $volumeOne->title);
        $this->assertSame(2, $volumeOne->digital_license_count);

        $volumeTwo = NovelVolume::create([
            'book_id' => $book->id,
            'volume_number' => 2,
            'title' => 'Jalan Baru',
            'digital_file_path' => $volumeOne->digital_file_path,
            'digital_license_count' => 1,
            'digital_lending_allowed' => true,
            'is_published' => true,
        ]);
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('novel-volumes.digital.borrow', $volumeOne))
            ->assertRedirect(route('books.show', $book));

        $this->actingAs($member)->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Volume 1')
            ->assertSee('Download Volume')
            ->assertDontSee('Baca Online');

        $volumeDownload = $this->actingAs($member)
            ->get(route('novel-volumes.digital.download', $volumeOne))
            ->assertOk();
        $this->assertStringContainsString(
            'cerita-dari-selatan-judul-diperbarui.pdf',
            $volumeDownload->headers->get('content-disposition'),
        );
        $this->actingAs($member)->get(route('books.read', $book))->assertForbidden();

        $this->assertSame(1, app(DigitalBorrowingService::class)->availableLicensesForVolume($volumeOne->fresh()));
        $this->assertSame(1, app(DigitalBorrowingService::class)->availableLicensesForVolume($volumeTwo->fresh()));

        $this->actingAs($staff)
            ->from(route('books.volumes.edit', [$book, $volumeOne]))
            ->put(route('books.volumes.update', [$book, $volumeOne]), [
                'volume_number' => 1,
                'title' => 'Judul Diperbarui',
                'digital_license_count' => 0,
                'digital_lending_allowed' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('books.volumes.edit', [$book, $volumeOne]))
            ->assertSessionHasErrors('digital_license_count');

        $this->actingAs($staff)
            ->from(route('books.volumes.index', $book))
            ->delete(route('books.volumes.destroy', [$book, $volumeOne]))
            ->assertRedirect(route('books.volumes.index', $book))
            ->assertSessionHasErrors('archive');

        $this->assertSame(2, $volumeOne->fresh()->digital_license_count);
        $this->assertDatabaseCount('borrowings', 1);

        $borrowing = Borrowing::query()->firstOrFail();
        $this->actingAs($member)
            ->post(route('borrowings.return', $borrowing))
            ->assertRedirect(route('borrowings.show', $borrowing));

        $this->assertSame(2, app(DigitalBorrowingService::class)->availableLicensesForVolume($volumeOne->fresh()));
        $this->assertSame(1, app(DigitalBorrowingService::class)->availableLicensesForVolume($volumeTwo->fresh()));

        $this->actingAs($staff)
            ->delete(route('books.volumes.destroy', [$book, $volumeOne]))
            ->assertRedirect(route('books.volumes.index', $book));
        $this->assertSoftDeleted('novel_volumes', ['id' => $volumeOne->id]);
    }

    public function test_novel_book_crud_stores_its_type_and_keeps_digital_licenses_on_volumes(): void
    {
        $staff = User::factory()->petugas()->create();
        $category = Category::factory()->create();

        $this->actingAs($staff)->post(route('books.store'), [
            'title' => 'Cerita dari Selatan',
            'author' => 'Penulis Novel',
            'collection_type' => 'novel',
            'category_id' => $category->id,
            'stock_total' => 3,
            'synopsis' => 'Sinopsis asli novel.',
            'digital_license_count' => 50,
            'digital_lending_allowed' => 1,
        ])->assertRedirect();

        $book = Book::query()->where('title', 'Cerita dari Selatan')->firstOrFail();
        $this->assertSame('novel', $book->collection_type);
        $this->assertSame('Sinopsis asli novel.', $book->synopsis);
        $this->assertSame(0, $book->digital_license_count);
        $this->assertFalse($book->digital_lending_allowed);

        $this->actingAs($staff)->put(route('books.update', $book), [
            'title' => $book->title,
            'author' => $book->author,
            'collection_type' => 'novel',
            'category_id' => $category->id,
            'stock_total' => 3,
            'synopsis' => 'Sinopsis diperbarui.',
        ])->assertRedirect(route('books.manage', $book));

        $this->assertSame('Sinopsis diperbarui.', $book->fresh()->synopsis);
        $this->assertSame(0, $book->fresh()->digital_license_count);
    }

    public function test_digital_borrowing_is_denied_without_distribution_permission(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('digital-books/restricted.pdf', "%PDF-1.4\nRestricted content");
        $book = $this->createEbook([
            'digital_file_path' => 'digital-books/restricted.pdf',
            'digital_lending_allowed' => false,
        ]);
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('books.show', $book))
            ->post(route('books.digital.borrow', $book))
            ->assertRedirect(route('books.show', $book))
            ->assertSessionHasErrors('digital_file');

        $this->assertDatabaseCount('borrowings', 0);
        $this->assertSame(1, app(DigitalBorrowingService::class)->availableLicensesForBook($book));
    }

    private function createEbook(array $attributes = []): Book
    {
        return Book::factory()->create(array_merge([
            'collection_type' => 'e-book',
            'digital_license_count' => 1,
            'digital_lending_allowed' => true,
            'digital_file_path' => null,
        ], $attributes));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST-001: Valid user can login and logout; invalid credentials fail safely; archived user cannot login.
     */
    public function test_001_user_can_login_and_logout_and_invalid_or_archived_user_is_rejected(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@perpustakaan.local',
            'password' => Hash::make('password123'),
        ]);

        // Wrong password rejected with generic message
        $badResponse = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'admin@perpustakaan.local',
            'password' => 'wrong-password',
        ]);
        $badResponse->assertRedirect(route('login'));
        $badResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Valid credentials succeed and redirect to dashboard
        $goodResponse = $this->post(route('login.submit'), [
            'email' => 'admin@perpustakaan.local',
            'password' => 'password123',
        ]);
        $goodResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Logout invalidates session and returns to landing page
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();

        // Archived (soft-deleted) user cannot login
        $archivedUser = User::factory()->petugas()->create([
            'email' => 'nonaktif@perpustakaan.local',
            'password' => Hash::make('password123'),
        ]);
        $archivedUser->delete();

        $archivedAttempt = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'nonaktif@perpustakaan.local',
            'password' => 'password123',
        ]);
        $archivedAttempt->assertRedirect(route('login'));
        $archivedAttempt->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TEST-002: Guest cannot access any internal route and is redirected to login.
     */
    public function test_002_guest_cannot_access_protected_internal_routes(): void
    {
        $protectedGetRoutes = [
            route('dashboard'),
            route('users.index'),
            route('users.create'),
            route('categories.index'),
            route('categories.create'),
            route('books.index'),
            route('books.create'),
            route('members.index'),
            route('members.create'),
            route('borrowings.index'),
            route('borrowings.create'),
        ];

        foreach ($protectedGetRoutes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $this->post(route('books.store'), [])->assertRedirect(route('login'));
        $this->post(route('members.store'), [])->assertRedirect(route('login'));
        $this->post(route('borrowings.store'), [])->assertRedirect(route('login'));
    }

    /**
     * TEST-003: Admin can manage users and roles; Petugas receives 403 on all User endpoints; last-admin/self-archive guards enforced.
     */
    public function test_003_admin_manages_users_and_petugas_is_forbidden_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();

        // Petugas receives 403 on all user endpoints
        $this->actingAs($petugas)->get(route('users.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('users.create'))->assertForbidden();
        $this->actingAs($petugas)->post(route('users.store'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.test',
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();
        $this->actingAs($petugas)->get(route('users.edit', $admin))->assertForbidden();
        $this->actingAs($petugas)->put(route('users.update', $admin), [
            'name' => 'Changed',
            'email' => $admin->email,
            'role' => User::ROLE_PETUGAS,
        ])->assertForbidden();
        $this->actingAs($petugas)->delete(route('users.destroy', $admin))->assertForbidden();

        // Admin can create a new user and assign role
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Staf Baru',
            'email' => 'staf.baru@perpustakaan.local',
            'role' => User::ROLE_PETUGAS,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $createdUser = User::where('email', 'staf.baru@perpustakaan.local')->firstOrFail();
        $this->assertSame(User::ROLE_PETUGAS, $createdUser->role);
        $this->assertTrue(Hash::check('password123', $createdUser->password));

        // Admin can update user role to admin
        $this->actingAs($admin)->put(route('users.update', $createdUser), [
            'name' => 'Staf Baru Promosi',
            'email' => 'staf.baru@perpustakaan.local',
            'role' => User::ROLE_ADMIN,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('users.index'));

        $this->assertSame(User::ROLE_ADMIN, $createdUser->fresh()->role);

        // Admin cannot archive their own active session account
        $this->actingAs($admin)->from(route('users.index'))
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('archive');

        $this->assertNull($admin->fresh()->deleted_at);

        // Archive second admin, leaving $admin as the only active admin
        $this->actingAs($admin)->delete(route('users.destroy', $createdUser))
            ->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['id' => $createdUser->id]);

        // Last active admin cannot demote themselves to petugas
        $this->actingAs($admin)->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_PETUGAS,
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors('role');

        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);
    }

    /**
     * TEST-004: Admin can CRUD categories; Petugas can only view categories and gets 403 on write operations.
     */
    public function test_004_category_permissions_for_admin_and_petugas(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();

        // Admin creates category
        $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Sains Komputer',
            'description' => 'Koleksi buku komputasi',
        ])->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Sains Komputer')->firstOrFail();

        // Case-insensitive duplicate category name is rejected
        $this->actingAs($admin)->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'sains komputer',
                'description' => 'Duplikat',
            ])
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('name');

        // Petugas can view category list (200 OK)
        $this->actingAs($petugas)->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Sains Komputer')
            ->assertDontSee('Tambah Kategori');

        // Petugas gets 403 on all category write endpoints
        $this->actingAs($petugas)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($petugas)->post(route('categories.store'), ['name' => 'Ilegal'])->assertForbidden();
        $this->actingAs($petugas)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($petugas)->put(route('categories.update', $category), ['name' => 'Ubah Ilegal'])->assertForbidden();
        $this->actingAs($petugas)->delete(route('categories.destroy', $category))->assertForbidden();

        // Admin can update and archive unused category
        $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Ilmu Komputer',
            'description' => 'Diperbarui',
        ])->assertRedirect(route('categories.index'));

        $this->assertSame('Ilmu Komputer', $category->fresh()->name);

        $this->actingAs($admin)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /**
     * TEST-020: Member data is internal-only; passwords are never exposed in HTML; .gitignore protects .env.
     */
    public function test_020_privacy_and_secret_protection(): void
    {
        $member = Member::factory()->create([
            'member_code' => 'AGT-99001',
            'name' => 'Rahasia Internal',
            'phone' => '081299998888',
        ]);

        // Guest cannot access member list or detail
        $this->get(route('members.index'))->assertRedirect(route('login'));
        $this->get(route('members.show', $member))->assertRedirect(route('login'));

        // Admin can view user list, and password hash is never rendered in HTML
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('super-secret-password'),
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertOk();
        $response->assertDontSee($admin->password);
        $response->assertDontSee('super-secret-password');

        // Verify .gitignore contains .env
        $gitignore = file_get_contents(base_path('.gitignore'));
        $this->assertStringContainsString('.env', (string) $gitignore);
    }

    /**
     * TEST-021: Anggota role can access dashboard, catalog, and own borrowings; forbidden from user & circulation management.
     */
    public function test_021_anggota_role_authorization_and_restricted_access(): void
    {
        $anggota = User::factory()->anggota()->create([
            'email' => 'anggota.test@perpustakaan.local',
        ]);

        // Anggota can login and view dashboard
        $this->actingAs($anggota)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="theme-toggle-btn"', false);

        // Anggota can view catalog books
        $this->actingAs($anggota)->get(route('books.index'))->assertOk();

        // Anggota can view borrowing list (their own)
        $this->actingAs($anggota)->get(route('borrowings.index'))->assertOk();

        // Anggota is forbidden from creating borrowings
        $this->actingAs($anggota)->get(route('borrowings.create'))->assertForbidden();

        // Anggota is forbidden from user management
        $this->actingAs($anggota)->get(route('users.index'))->assertForbidden();

        // Anggota is forbidden from member management
        $this->actingAs($anggota)->get(route('members.index'))->assertForbidden();

        // Anggota is forbidden from category management
        $this->actingAs($anggota)->get(route('categories.create'))->assertForbidden();
    }

    /**
     * TEST-022: Anggota role can borrow available books for themselves and respects stock invariants.
     */
    public function test_022_anggota_can_borrow_books_for_themselves_and_respects_stock_invariants(): void
    {
        $anggota = User::factory()->anggota()->create([
            'email' => 'peminjam.anggota@perpustakaan.local',
            'name' => 'Siti Nurhaliza',
        ]);

        $book = Book::factory()->create([
            'title' => 'Pemrograman Web Modern',
            'stock_total' => 5,
            'stock_available' => 5,
        ]);

        // Anggota borrows book via books.borrow
        $response = $this->actingAs($anggota)->post(route('books.borrow', $book));
        $response->assertRedirect();

        $this->assertSame(4, $book->fresh()->stock_available);

        $borrowing = Borrowing::where('member_name_snapshot', 'Siti Nurhaliza')->first();
        $this->assertNotNull($borrowing);
        $this->assertSame(Borrowing::STATUS_BORROWED, $borrowing->status);

        // Anggota can view their borrowing transaction detail
        $this->actingAs($anggota)->get(route('borrowings.show', $borrowing))->assertOk();

        // Also test through borrowings.store route with automatic member binding
        $book2 = Book::factory()->create([
            'stock_total' => 3,
            'stock_available' => 3,
        ]);

        $response2 = $this->actingAs($anggota)->post(route('borrowings.store'), [
            'book_id' => $book2->id,
            'quantity' => 1,
        ]);
        $response2->assertRedirect();
        $this->assertSame(2, $book2->fresh()->stock_available);

        // Zero stock book cannot be borrowed
        $emptyBook = Book::factory()->create([
            'stock_total' => 1,
            'stock_available' => 0,
        ]);

        $responseEmpty = $this->actingAs($anggota)->post(route('books.borrow', $emptyBook));
        $responseEmpty->assertSessionHas('error');
        $this->assertSame(0, $emptyBook->fresh()->stock_available);
    }
}

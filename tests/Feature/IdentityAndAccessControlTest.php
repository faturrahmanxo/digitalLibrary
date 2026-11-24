<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdentityAndAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 1: Roles 'admin', 'petugas' / 'staff', 'anggota' / 'member'.
     */
    public function test_roles_and_aliases_are_recognized_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $staff = User::factory()->create(['role' => 'staff']);
        $anggota = User::factory()->create(['role' => 'anggota']);
        $member = User::factory()->create(['role' => 'member']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isPetugas());
        $this->assertFalse($admin->isAnggota());

        $this->assertTrue($petugas->isPetugas());
        $this->assertTrue($petugas->isStaff());
        $this->assertFalse($petugas->isAdmin());

        $this->assertTrue($staff->isPetugas());
        $this->assertTrue($staff->isStaff());

        $this->assertTrue($anggota->isAnggota());
        $this->assertTrue($anggota->isMember());
        $this->assertFalse($anggota->isAdmin());

        $this->assertTrue($member->isAnggota());
        $this->assertTrue($member->isMember());
    }

    /**
     * Requirement 2: Admin full access to internal modules.
     */
    public function test_admin_has_full_access_to_dashboard_books_master_data_borrowings_members_users(): void
    {
        $admin = User::factory()->admin()->create();
        $member = Member::factory()->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('books.index'))->assertOk();
        $this->actingAs($admin)->get(route('categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('members.index'))->assertOk();
        $this->actingAs($admin)->get(route('members.show', $member))->assertOk();
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('borrowings.index'))->assertOk();
    }

    /**
     * Requirement 3: Petugas has operational access to Borrowings, Books, Categories, Members,
     * but CANNOT delete/demote Admin or manage internal staff accounts.
     */
    public function test_petugas_operational_access_and_prohibition_from_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $petugas = User::factory()->petugas()->create();
        $member = Member::factory()->create();

        // Operational access allowed
        $this->actingAs($petugas)->get(route('books.index'))->assertOk();
        $this->actingAs($petugas)->get(route('categories.index'))->assertOk();
        $this->actingAs($petugas)->get(route('members.index'))->assertOk();
        $this->actingAs($petugas)->get(route('members.show', $member))->assertOk();
        $this->actingAs($petugas)->get(route('borrowings.index'))->assertOk();

        // Forbidden from managing internal users
        $this->actingAs($petugas)->get(route('users.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('users.create'))->assertForbidden();
        $this->actingAs($petugas)->post(route('users.store'), [
            'name' => 'Staf Baru',
            'email' => 'hacker@example.com',
            'role' => 'petugas',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();
        $this->actingAs($petugas)->get(route('users.edit', $admin))->assertForbidden();
        $this->actingAs($petugas)->put(route('users.update', $admin), [
            'name' => 'Demoted Admin',
            'email' => $admin->email,
            'role' => 'petugas',
        ])->assertForbidden();
        $this->actingAs($petugas)->delete(route('users.destroy', $admin))->assertForbidden();
    }

    /**
     * Requirement 4: Member portal access, view own borrowings, profile,
     * CANNOT access admin area or other members' data.
     */
    public function test_member_can_access_portal_and_own_data_but_forbidden_from_admin_area(): void
    {
        $anggota = User::factory()->anggota()->create(['email' => 'siti@example.com']);
        $otherUser = User::factory()->anggota()->create(['email' => 'other@example.com']);

        $memberRecord = Member::factory()->create(['email' => 'siti@example.com']);
        $otherMemberRecord = Member::factory()->create(['email' => 'other@example.com']);

        $staff = User::factory()->petugas()->create();
        $category = Category::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id]);

        $ownBorrowing = Borrowing::create([
            'transaction_code' => 'TRX-OWN-001',
            'staff_id' => $staff->id,
            'member_id' => $memberRecord->id,
            'member_name_snapshot' => $memberRecord->name,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
            'status' => 'borrowed',
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', 'test'),
        ]);

        $otherBorrowing = Borrowing::create([
            'transaction_code' => 'TRX-OTHER-002',
            'staff_id' => $staff->id,
            'member_id' => $otherMemberRecord->id,
            'member_name_snapshot' => $otherMemberRecord->name,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
            'status' => 'borrowed',
            'request_key' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', 'test-other'),
        ]);

        // Own data accessible
        $this->actingAs($anggota)->get(route('borrowings.show', $ownBorrowing))->assertOk();
        $this->actingAs($anggota)->get(route('profile.edit'))->assertOk();

        // Other member's data is forbidden (IDOR protection)
        $this->actingAs($anggota)->get(route('borrowings.show', $otherBorrowing))->assertForbidden();

        // Admin-only areas forbidden for member
        $this->actingAs($anggota)->get(route('users.index'))->assertForbidden();
        $this->actingAs($anggota)->get(route('members.index'))->assertForbidden();
        $this->actingAs($anggota)->get(route('members.show', $otherMemberRecord))->assertForbidden();
        $this->actingAs($anggota)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($anggota)->get(route('borrowings.create'))->assertForbidden();
    }

    /**
     * Requirement 5: Inactive members cannot submit new borrow requests.
     */
    public function test_inactive_members_cannot_submit_new_borrow_requests(): void
    {
        $anggota = User::factory()->anggota()->create(['email' => 'budi.nonaktif@example.com']);
        $memberRecord = Member::factory()->create([
            'email' => 'budi.nonaktif@example.com',
            'name' => 'Budi Nonaktif',
        ]);
        // Soft delete / deactivate the member
        $memberRecord->delete();

        $category = Category::factory()->create();
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'stock_available' => 5,
            'stock_total' => 5,
        ]);

        // 1. Single-book borrow attempt via borrowBook endpoint
        $response1 = $this->actingAs($anggota)->post(route('books.borrow', $book));
        $this->assertTrue(
            $response1->isForbidden() || $response1->isRedirect(),
            'Inactive member must be rejected from borrowing'
        );
        if ($response1->isRedirect()) {
            $response1->assertSessionHas('error');
        }

        // 2. Full borrowing form submission via borrowings.store
        $response2 = $this->actingAs($anggota)->post(route('borrowings.store'), [
            'request_key' => (string) Str::uuid(),
            'items' => [
                ['book_id' => $book->id, 'quantity' => 1],
            ],
        ]);
        $this->assertTrue(
            $response2->isForbidden() || $response2->isRedirect(),
            'Inactive member must be rejected from borrowing'
        );

        // Verify no new borrowing was created for this member
        $this->assertDatabaseMissing('borrowings', [
            'member_id' => $memberRecord->id,
        ]);
    }

    /**
     * Requirement 6: Admin & Petugas management:
     * - Password hashing
     * - Cannot delete the last active admin
     * - Cannot demote the last active admin
     * - Safe soft-deactivation and restoration
     */
    public function test_admin_and_petugas_management_security_and_lifecycle(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'first.admin@example.com']);

        // Cannot delete self or last admin
        $this->actingAs($admin)->from(route('users.index'))
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('archive');

        $this->assertNull($admin->fresh()->deleted_at);

        // Create second admin with password hashing
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Second Admin',
            'username' => 'admin_dua',
            'email' => 'second.admin@example.com',
            'role' => 'admin',
            'password' => 'secret_password_123',
            'password_confirmation' => 'secret_password_123',
        ])->assertRedirect(route('users.index'));

        $secondAdmin = User::where('email', 'second.admin@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secret_password_123', $secondAdmin->password));
        $this->assertSame('admin_dua', $secondAdmin->username);

        // Safe soft-deactivation of second admin
        $this->actingAs($admin)->delete(route('users.destroy', $secondAdmin))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $secondAdmin->id]);

        // Attempting to demote the now-only-active first admin fails
        $this->actingAs($admin)->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'petugas',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors('role');

        // Restore second admin
        $this->actingAs($admin)->post(route('users.restore', $secondAdmin->id))
            ->assertRedirect(route('users.index'));

        $this->assertNull($secondAdmin->fresh()->deleted_at);
    }
}

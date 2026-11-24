<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_profile_routes(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('profile.update'), [])
            ->assertRedirect(route('login'));

        $this->put(route('profile.password.update'), [])
            ->assertRedirect(route('login'));

        $this->post(route('profile.photo.store'), [])
            ->assertRedirect(route('login'));

        $this->delete(route('profile.photo.destroy'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_page_shows_account_data_and_read_only_email_without_sidebar(): void
    {
        $user = User::factory()->anggota()->create([
            'name' => 'Adinda Saraswati',
            'email' => 'adinda@litera.local',
        ]);
        $user->username = 'adinda_saraswati';
        $user->save();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk()
            ->assertSee('id="theme-toggle-btn"', false)
            ->assertSee('Profil Saya')
            ->assertSee('Adinda Saraswati')
            ->assertSee('value="adinda_saraswati"', false)
            ->assertSee('value="adinda@litera.local"', false)
            ->assertSee('readonly', false)
            ->assertSee('Foto Profil')
            ->assertSee('Informasi Akun')
            ->assertSee('Keamanan Akun')
            ->assertSee('id="password-fields"', false)
            ->assertSee('hidden', false)
            ->assertDontSee('name="role"', false)
            ->assertDontSee('id="app-sidebar"', false);
    }

    public function test_profile_update_changes_only_the_authenticated_users_allowed_fields(): void
    {
        $user = User::factory()->anggota()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@litera.local',
        ]);
        $otherUser = User::factory()->anggota()->create([
            'name' => 'Pengguna Lain',
            'email' => 'lain@litera.local',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Dewi <script>alert("x")</script>',
            'username' => 'dewi.reader',
            'email' => 'ubah@litera.local',
            'role' => User::ROLE_ADMIN,
            'user_id' => $otherUser->id,
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Dewi <script>alert("x")</script>',
            'username' => 'dewi.reader',
            'email' => 'lama@litera.local',
            'role' => User::ROLE_ANGGOTA,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'name' => 'Pengguna Lain',
            'email' => 'lain@litera.local',
            'role' => User::ROLE_ANGGOTA,
        ]);

        $profileResponse = $this->get(route('profile.edit'));
        $profileResponse->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        $user = User::factory()->anggota()->create();
        $existingUser = User::factory()->anggota()->create();
        $existingUser->username = 'already_used';
        $existingUser->save();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'username' => 'already_used',
            ])
            ->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'username' => 'already_used',
        ]);
    }

    public function test_username_rejects_characters_outside_its_allowed_format(): void
    {
        $user = User::factory()->anggota()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'username' => 'Invalid Name!',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->anggota()->create([
            'password' => 'Current-password-1!',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'New-password-2!',
                'password_confirmation' => 'New-password-2!',
            ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('Current-password-1!', $user->fresh()->password));
    }

    public function test_password_change_rejects_mismatched_confirmation(): void
    {
        $user = User::factory()->anggota()->create([
            'password' => 'Current-password-1!',
        ]);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'Current-password-1!',
                'password' => 'New-password-2!',
                'password_confirmation' => 'Different-password-3!',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('Current-password-1!', $user->fresh()->password));
    }

    public function test_password_change_persists_a_hash_after_valid_confirmation(): void
    {
        $user = User::factory()->anggota()->create([
            'password' => 'Current-password-1!',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'Current-password-1!',
            'password' => 'New-password-2!',
            'password_confirmation' => 'New-password-2!',
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('New-password-2!', $user->fresh()->password));
        $this->assertFalse(Hash::check('Current-password-1!', $user->fresh()->password));
    }

    public function test_profile_photo_upload_stores_a_new_file_and_updates_the_navbar(): void
    {
        Storage::fake('public');

        $user = User::factory()->anggota()->create([
            'name' => 'Foto Pengguna',
        ]);
        $oldPhotoPath = 'profile-photos/old.png';
        Storage::disk('public')->put($oldPhotoPath, 'old photo');
        $user->profile_photo_path = $oldPhotoPath;
        $user->save();

        $response = $this->actingAs($user)->post(route('profile.photo.store'), [
            'photo' => $this->fakePng('avatar.png'),
        ]);

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $newPhotoPath = $user->fresh()->profile_photo_path;
        $this->assertIsString($newPhotoPath);
        $this->assertNotSame($oldPhotoPath, $newPhotoPath);
        Storage::disk('public')->assertExists($newPhotoPath);
        Storage::disk('public')->assertMissing($oldPhotoPath);

        $navbarResponse = $this->get(route('home'));
        $navbarResponse->assertOk()
            ->assertSee('src="'.asset('storage/'.$newPhotoPath).'"', false)
            ->assertSee('Foto Pengguna');
    }

    public function test_non_image_profile_photo_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->anggota()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.store'), [
                'photo' => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->fresh()->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles('profile-photos'));
    }

    public function test_profile_photo_larger_than_two_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->anggota()->create();

        $this->actingAs($user)
            ->post(route('profile.photo.store'), [
                'photo' => $this->fakePng('large.png', 2_097_153),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->fresh()->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles('profile-photos'));
    }

    public function test_deleting_profile_photo_restores_initial_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->anggota()->create([
            'name' => 'Adinda Saraswati',
        ]);
        $photoPath = 'profile-photos/avatar.png';
        Storage::disk('public')->put($photoPath, 'profile photo');
        $user->profile_photo_path = $photoPath;
        $user->save();

        $response = $this->actingAs($user)->delete(route('profile.photo.destroy'));

        $response->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertNull($user->fresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($photoPath);

        $navbarResponse = $this->get(route('home'));
        $navbarResponse->assertOk()
            ->assertSee('AD')
            ->assertDontSee('src="'.asset('storage/'.$photoPath).'"', false);
    }

    private function fakePng(string $name, int $extraBytes = 0): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0TYAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $png.str_repeat('x', $extraBytes));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavbarGlassmorphismAndAccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_and_petugas_navbar_has_glassmorphism_no_koleksi_saya_and_no_dropdown(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertOk();
        // Glassmorphism navbar styling present
        $response->assertSee('backdrop-blur-xl', false);
        // Direct dashboard link for admin
        $response->assertSee('href="'.route('dashboard').'"', false);
        $response->assertSee('Dashboard &rarr;', false);
        // Direct logout button present
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('Keluar');
        // Koleksi Saya must NOT be present for admin
        $response->assertDontSee('Koleksi Saya');
        // The old user dropdown menu container should NOT be present for admin
        $response->assertDontSee('id="nav-user-dropdown-menu"', false);
    }

    public function test_anggota_navbar_retains_koleksi_saya_and_user_dropdown(): void
    {
        $anggota = User::where('role', User::ROLE_ANGGOTA)->firstOrFail();

        $response = $this->actingAs($anggota)->get(route('home'));

        $response->assertOk();
        // Anggota retains Koleksi Saya
        $response->assertSee('Koleksi Saya');
        $response->assertSee('Profil Saya');
        // Anggota retains dropdown
        $response->assertSee('id="nav-user-dropdown-menu"', false);
        // Anggota does NOT have direct dashboard link
        $response->assertDontSee('href="'.route('dashboard').'"', false);
    }

    public function test_dashboard_contains_redesigned_hero_banner_and_sidebar_links_to_dedicated_settings_pages(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        // Redesigned modern hero banner with legible text
        $response->assertSee('Halo, '.$admin->name.'! 👋');
        $response->assertSee('Admin Dashboard — Sistem Sirkulasi Lengkap');

        // Sidebar Account Settings link present with 3 dedicated page submenus
        $response->assertSee('id="sidebar-account-settings-link"', false);
        $response->assertSee('href="'.route('settings.profile').'"', false);
        $response->assertSee('href="'.route('settings.security').'"', false);
        $response->assertSee('href="'.route('settings.appearance').'"', false);

        // Pengaturan Akun section is now on dedicated pages, not embedded on dashboard
        $response->assertDontSee('id="pengaturan-akun"', false);
    }

    public function test_dedicated_settings_pages_are_accessible_and_render_per_menu(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        // 1. Settings index redirects to profile
        $this->actingAs($admin)->get(route('settings.index'))
            ->assertRedirect(route('settings.profile'));

        // 2. Profile Page
        $profileResponse = $this->actingAs($admin)->get(route('settings.profile'));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Pengaturan Profil');
        $profileResponse->assertDontSee('tab-btn-profil');
        $profileResponse->assertSee('panel-profil', false);
        $profileResponse->assertSee($admin->name);
        $profileResponse->assertSee($admin->email);
        $profileResponse->assertSee('action="'.route('profile.update').'"', false);
        $profileResponse->assertSee('action="'.route('profile.photo.store').'"', false);

        // 3. Security Page
        $securityResponse = $this->actingAs($admin)->get(route('settings.security'));
        $securityResponse->assertOk();
        $securityResponse->assertSee('Pengaturan Keamanan');
        $securityResponse->assertDontSee('tab-btn-keamanan');
        $securityResponse->assertSee('panel-keamanan', false);
        $securityResponse->assertSee('action="'.route('profile.password.update').'"', false);
        $securityResponse->assertSee('current_password');

        // 4. Appearance Page
        $appearanceResponse = $this->actingAs($admin)->get(route('settings.appearance'));
        $appearanceResponse->assertOk();
        $appearanceResponse->assertSee('Pengaturan Tampilan');
        $appearanceResponse->assertDontSee('tab-btn-tampilan');
        $appearanceResponse->assertSee('panel-tampilan', false);
        $appearanceResponse->assertSee('Mode Terang');
        $appearanceResponse->assertSee('Mode Gelap');
        $appearanceResponse->assertSee('Ikuti Sistem');

        // Verify simplified layout: extraneous info cards are removed
        $securityResponse->assertDontSee('Informasi Keamanan &amp; Sesi', false);
        $securityResponse->assertDontSee('Standar Bcrypt Terproteksi');
        $appearanceResponse->assertDontSee('Design System Litera');
    }

    public function test_seeder_creates_3_accounts_per_role_with_gmail_domain(): void
    {
        $names = ['fatur', 'genbi', 'damar'];
        $roles = [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA];

        foreach ($roles as $role) {
            foreach ($names as $name) {
                $email = "{$name}.{$role}@gmail.com";
                $this->assertDatabaseHas('users', [
                    'email' => $email,
                    'role' => $role,
                ]);
            }
        }

        $this->assertDatabaseHas('users', ['email' => 'fatur@gmail.com', 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('users', ['email' => 'genbi@gmail.com', 'role' => User::ROLE_PETUGAS]);
        $this->assertDatabaseHas('users', ['email' => 'damar@gmail.com', 'role' => User::ROLE_ANGGOTA]);
    }

    public function test_landing_page_book_sections_use_centered_layout(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('flex flex-wrap justify-center', false);
    }

    public function test_form_does_not_contain_tanda_bintang_wajib_diisi(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('books.create'));

        $response->assertOk();
        $response->assertDontSee('Tanda * wajib diisi');
        $response->assertDontSee('wajib diisi</span>', false);
    }

    public function test_petugas_has_identical_sidebar_settings_and_topbar_role_badge(): void
    {
        $petugas = User::where('role', User::ROLE_PETUGAS)->firstOrFail();

        $response = $this->actingAs($petugas)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Petugas');
        $response->assertSee('id="sidebar-account-settings-link"', false);
        $response->assertSee('href="'.route('settings.profile').'"', false);
        $response->assertSee('href="'.route('settings.security').'"', false);
        $response->assertSee('href="'.route('settings.appearance').'"', false);
        $response->assertSee('action="'.route('logout').'"', false);
    }
}

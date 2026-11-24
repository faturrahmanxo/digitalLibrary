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

    public function test_dashboard_contains_redesigned_hero_banner_and_pengaturan_akun(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        // Redesigned modern hero banner with legible text
        $response->assertSee('Halo, '.$admin->name.'! 👋');
        $response->assertSee('Admin Dashboard — Sistem Sirkulasi Lengkap');
        $response->assertSee('href="'.route('dashboard').'#pengaturan-akun"', false);

        // Sidebar Account Settings link present with 3 submenus
        $response->assertSee('id="sidebar-account-settings-link"', false);
        $response->assertSee('sidebar-account-settings-link', false);
        $response->assertSee('href="'.route('dashboard').'#panel-profil"', false);
        $response->assertSee('href="'.route('dashboard').'#panel-keamanan"', false);
        $response->assertSee('href="'.route('dashboard').'#panel-tampilan"', false);

        // Pengaturan Akun section present
        $response->assertSee('id="pengaturan-akun"', false);
        $response->assertSee('Pengaturan Akun');

        // Tab 1: Profil
        $response->assertSee('tab-btn-profil', false);
        $response->assertSee('panel-profil', false);
        $response->assertSee($admin->name);
        $response->assertSee($admin->email);
        $response->assertSee('action="'.route('profile.update').'"', false);
        $response->assertSee('action="'.route('profile.photo.store').'"', false);

        // Tab 2: Keamanan
        $response->assertSee('tab-btn-keamanan', false);
        $response->assertSee('panel-keamanan', false);
        $response->assertSee('action="'.route('profile.password.update').'"', false);
        $response->assertSee('current_password');

        // Tab 3: Tampilan
        $response->assertSee('tab-btn-tampilan', false);
        $response->assertSee('panel-tampilan', false);
        $response->assertSee('Mode Terang');
        $response->assertSee('Mode Gelap');
        $response->assertSee('Ikuti Sistem');
        $response->assertSee('setThemeDashboard', false);
    }

    public function test_form_does_not_contain_tanda_bintang_wajib_diisi(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('books.create'));

        $response->assertOk();
        $response->assertDontSee('Tanda * wajib diisi');
        $response->assertDontSee('wajib diisi</span>', false);
    }
}

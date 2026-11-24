<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_portal_renders_personalized_ui(): void
    {
        $this->seed(DatabaseSeeder::class);

        $anggota = User::where('role', User::ROLE_ANGGOTA)->firstOrFail();

        // 1. Dashboard Beranda (Sivitas Akademika Member Hub)
        $dashboardRes = $this->actingAs($anggota)->get(route('dashboard'));
        $dashboardRes->assertOk();
        $dashboardRes->assertSee('Selamat Datang');
        $dashboardRes->assertSee('Akun Sivitas Akademika Aktif');
        $dashboardRes->assertSee('Buku Sedang Dipinjam');
        $dashboardRes->assertSee('Jatuh Tempo Pengembalian');
        $dashboardRes->assertSee('Litera');

        // 2. Books / Katalog
        $booksRes = $this->actingAs($anggota)->get(route('books.index'));
        $booksRes->assertOk();
        $booksRes->assertSee('Katalog Koleksi Buku');
        $booksRes->assertDontSee('Tambah Buku'); // Action restricted for members

        // 3. Peminjaman Saya
        $borrowingsRes = $this->actingAs($anggota)->get(route('borrowings.index'));
        $borrowingsRes->assertOk();
        $borrowingsRes->assertSee('Peminjaman Saya');
    }
}

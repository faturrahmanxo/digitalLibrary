<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with synthetic demo fixtures.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@perpustakaan.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        $petugas = User::updateOrCreate(
            ['email' => 'petugas@perpustakaan.local'],
            [
                'name' => 'Petugas Perpustakaan',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_PETUGAS,
                'email_verified_at' => now(),
            ]
        );

        // Bersihkan seluruh akun anggota dummy/lama di database agar daftar anggota bersih
        User::withTrashed()->whereIn('email', [
            'anggota@perpustakaan.local',
            'fatur@perpustakan.local',
            'budi@perpustakaan.local',
            'dono@gmail.com',
            'fatur.anggota@gmail.com',
            'genbi.anggota@gmail.com',
            'damar.anggota@gmail.com',
        ])->forceDelete();

        Member::whereIn('email', [
            'anggota@perpustakaan.local',
            'fatur@perpustakan.local',
            'budi@perpustakaan.local',
            'dono@gmail.com',
            'fatur.anggota@gmail.com',
            'genbi.anggota@gmail.com',
            'damar.anggota@gmail.com',
            'damar@gmail.com',
        ])->forceDelete();

        // 3 Akun untuk Admin & Petugas (Fatur, Genbi, Damar)
        $staffRoles = [
            'admin' => User::ROLE_ADMIN,
            'petugas' => User::ROLE_PETUGAS,
        ];
        $staffNames = ['Fatur', 'Genbi', 'Damar'];

        foreach ($staffRoles as $roleKey => $roleValue) {
            foreach ($staffNames as $sName) {
                $email = strtolower($sName).'.'.$roleKey.'@gmail.com';
                $username = strtolower($sName).'_'.$roleKey;

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $sName,
                        'username' => $username,
                        'password' => Hash::make('password123'),
                        'role' => $roleValue,
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Tepat 3 akun Anggota resmi: Fatur, Genbi, Damar (@gmail.com)
        $memberAccounts = [
            ['name' => 'Fatur', 'email' => 'fatur@gmail.com', 'username' => 'fatur_member', 'code' => 'AGT-FAT01'],
            ['name' => 'Genbi', 'email' => 'genbi@gmail.com', 'username' => 'genbi_member', 'code' => 'AGT-GEN02'],
            ['name' => 'Damar', 'email' => 'damar@gmail.com', 'username' => 'damar_member', 'code' => 'AGT-DAM03'],
        ];

        foreach ($memberAccounts as $idx => $mAcc) {
            User::updateOrCreate(
                ['email' => $mAcc['email']],
                [
                    'name' => $mAcc['name'],
                    'username' => $mAcc['username'],
                    'password' => Hash::make('password123'),
                    'role' => User::ROLE_ANGGOTA,
                    'email_verified_at' => now(),
                ]
            );

            Member::updateOrCreate(
                ['email' => $mAcc['email']],
                [
                    'member_code' => $mAcc['code'],
                    'name' => $mAcc['name'],
                    'phone' => '0812'.sprintf('%08d', 11112222 + ($idx * 111)),
                    'address' => 'Jl. Litera Kampus No. '.($idx + 1),
                ]
            );
        }

        $memberAlya = Member::firstOrCreate(
            ['member_code' => 'AGT-00042'],
            [
                'name' => 'Alya Pratama',
                'email' => 'alya.pratama@example.test',
                'phone' => '081200000042',
                'address' => 'Jl. Pendidikan No. 12, Bandung (Data Fiktif)',
            ]
        );

        $memberBudi = Member::firstOrCreate(
            ['member_code' => 'AGT-00043'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.test',
                'phone' => '081200000043',
                'address' => 'Jl. Merdeka No. 45, Yogyakarta (Data Fiktif)',
            ]
        );

        $memberCitra = Member::firstOrCreate(
            ['member_code' => 'AGT-00044'],
            [
                'name' => 'Citra Lestari',
                'email' => 'citra.lestari@example.test',
                'phone' => '081200000044',
                'address' => 'Jl. Diponegoro No. 8, Surabaya (Data Fiktif)',
            ]
        );

        // Kategori, buku, dan transaksi hanya di-seed saat unit test otomatis berjalan (runningUnitTests()).
        // Pada database lokal, seluruh buku dan kategori dimulai dalam kondisi bersih (0 data) sesuai permintaan pengguna.
        if (app()->runningUnitTests()) {
            $catSejarah = Category::firstOrCreate(
                ['name' => 'Sejarah'],
                ['description' => 'Koleksi buku sejarah Nusantara, peradaban klasik, dan sejarah dunia.']
            );

            $catTeknologi = Category::firstOrCreate(
                ['name' => 'Teknologi & Pemrograman'],
                ['description' => 'Literatur rekayasa perangkat lunak, basis data, dan arsitektur sistem.']
            );

            $catSastra = Category::firstOrCreate(
                ['name' => 'Sastra & Fiksi'],
                ['description' => 'Karya sastra Indonesia dan novel pilihan.']
            );

            $catNovel = Category::firstOrCreate(
                ['name' => 'Novel & Fiksi'],
                ['description' => 'Koleksi novel, prosa kontemporer, dan karya fiksi pilihan.']
            );

            $catKeilmuan = Category::firstOrCreate(
                ['name' => 'Keilmuan & Edukasi'],
                ['description' => 'Buku teks akademik, kajian ilmiah, dan referensi edukasi riset.']
            );

            $catSains = Category::firstOrCreate(
                ['name' => 'Sains & Matematika'],
                ['description' => 'Referensi ilmu pengetahuan alam, fisika, dan matematika terapan.']
            );

            $catEkonomi = Category::firstOrCreate(
                ['name' => 'Ekonomi & Manajemen'],
                ['description' => 'Buku manajemen organisasi, tata kelola, dan ekonomi.']
            );

            $bookSejarah = Book::firstOrCreate(
                ['title' => 'Pengantar Sejarah Nusantara'],
                [
                    'category_id' => $catSejarah->id,
                    'author' => 'Dr. Hendra Wijaya',
                    'isbn' => '978-602-0001-01-1',
                    'publisher' => 'Pustaka Cendekia',
                    'publication_year' => 2024,
                    'stock_total' => 8,
                    'stock_available' => 8,
                ]
            );

            $bookArsitektur = Book::firstOrCreate(
                ['title' => 'Arsitektur Sistem Informasi Modern'],
                [
                    'category_id' => $catKeilmuan->id,
                    'author' => 'Rina Kusuma, M.Kom.',
                    'isbn' => '978-602-0001-02-8',
                    'publisher' => 'Informatika Nusantara',
                    'publication_year' => 2025,
                    'stock_total' => 5,
                    'stock_available' => 5,
                ]
            );

            $bookSastra = Book::firstOrCreate(
                ['title' => 'Jejak Langkah di Pelabuhan Lama'],
                [
                    'category_id' => $catNovel->id,
                    'author' => 'Surya Darmawan',
                    'isbn' => '978-602-0001-03-5',
                    'publisher' => 'Balai Sastra',
                    'publication_year' => 2022,
                    'stock_total' => 4,
                    'stock_available' => 4,
                ]
            );

            $bookRpl = Book::firstOrCreate(
                ['title' => 'Rekayasa Perangkat Lunak Berbasis Web'],
                [
                    'category_id' => $catKeilmuan->id,
                    'author' => 'Arif Budiman',
                    'isbn' => '978-602-0001-04-2',
                    'publisher' => 'Tekno Press',
                    'publication_year' => 2025,
                    'stock_total' => 3,
                    'stock_available' => 3,
                ]
            );

            $bookAlgoritma = Book::firstOrCreate(
                ['title' => 'Dasar-Dasar Algoritma dan Struktur Data'],
                [
                    'category_id' => $catKeilmuan->id,
                    'author' => 'Prof. Lukman Hakim',
                    'isbn' => '978-602-0001-05-9',
                    'publisher' => 'Informatika Nusantara',
                    'publication_year' => 2023,
                    'stock_total' => 7,
                    'stock_available' => 7,
                ]
            );

            $bookMatematika = Book::firstOrCreate(
                ['title' => 'Matematika Diskrit untuk Ilmu Komputer'],
                [
                    'category_id' => $catKeilmuan->id,
                    'author' => 'Dewi Sartika, M.Si.',
                    'isbn' => '978-602-0001-06-6',
                    'publisher' => 'Sains Media',
                    'publication_year' => 2024,
                    'stock_total' => 6,
                    'stock_available' => 6,
                ]
            );

            $bookLembayung = Book::firstOrCreate(
                ['title' => 'Lembayung Sutra di Cakrawala Senja'],
                [
                    'category_id' => $catNovel->id,
                    'author' => 'Anisa Rahmawati',
                    'isbn' => '978-602-0001-07-3',
                    'publisher' => 'Pustaka Literasi',
                    'publication_year' => 2023,
                    'stock_total' => 5,
                    'stock_available' => 5,
                ]
            );

            $borrow1 = Borrowing::create([
                'transaction_code' => 'BRW-20261116-0001',
                'staff_id' => $petugas->id,
                'returned_by' => null,
                'member_id' => $memberAlya->id,
                'member_name_snapshot' => $memberAlya->name,
                'borrowed_at' => Carbon::now('UTC')->subDays(2),
                'due_at' => Carbon::now('Asia/Jakarta')->addDays(5)->format('Y-m-d'),
                'returned_at' => null,
                'status' => Borrowing::STATUS_BORROWED,
                'request_key' => (string) Str::uuid(),
                'payload_hash' => hash('sha256', 'seed-borrow-1'),
            ]);

            BorrowingItem::create([
                'borrowing_id' => $borrow1->id,
                'book_id' => $bookSejarah->id,
                'book_title_snapshot' => $bookSejarah->title,
                'quantity' => 2,
            ]);

            BorrowingItem::create([
                'borrowing_id' => $borrow1->id,
                'book_id' => $bookSastra->id,
                'book_title_snapshot' => $bookSastra->title,
                'quantity' => 1,
            ]);

            $borrow2 = Borrowing::create([
                'transaction_code' => 'BRW-20261105-0002',
                'staff_id' => $admin->id,
                'returned_by' => null,
                'member_id' => $memberBudi->id,
                'member_name_snapshot' => $memberBudi->name,
                'borrowed_at' => Carbon::now('UTC')->subDays(10),
                'due_at' => Carbon::now('Asia/Jakarta')->subDays(3)->format('Y-m-d'),
                'returned_at' => null,
                'status' => Borrowing::STATUS_BORROWED,
                'request_key' => (string) Str::uuid(),
                'payload_hash' => hash('sha256', 'seed-borrow-2'),
            ]);

            BorrowingItem::create([
                'borrowing_id' => $borrow2->id,
                'book_id' => $bookAlgoritma->id,
                'book_title_snapshot' => $bookAlgoritma->title,
                'quantity' => 3,
            ]);

            $borrow3 = Borrowing::create([
                'transaction_code' => 'BRW-20261101-0003',
                'staff_id' => $petugas->id,
                'returned_by' => $petugas->id,
                'member_id' => $memberCitra->id,
                'member_name_snapshot' => $memberCitra->name,
                'borrowed_at' => Carbon::now('UTC')->subDays(14),
                'due_at' => Carbon::now('Asia/Jakarta')->subDays(7)->format('Y-m-d'),
                'returned_at' => Carbon::now('UTC')->subDays(9),
                'status' => Borrowing::STATUS_RETURNED,
                'request_key' => (string) Str::uuid(),
                'payload_hash' => hash('sha256', 'seed-borrow-3'),
            ]);

            BorrowingItem::create([
                'borrowing_id' => $borrow3->id,
                'book_id' => $bookMatematika->id,
                'book_title_snapshot' => $bookMatematika->title,
                'quantity' => 2,
            ]);

            $bookSejarah->update(['stock_available' => $bookSejarah->stock_total - 2]);
            $bookSastra->update(['stock_available' => $bookSastra->stock_total - 1]);
            $bookAlgoritma->update(['stock_available' => $bookAlgoritma->stock_total - 3]);
        }
    }
}

<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Layanan Perpustakaan Kampus — Litera</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-brand-script {
            font-family: 'Cormorant Garamond', Georgia, serif;
            letter-spacing: 0.01em;
        }
    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">

    <!-- Public Multi-Genre Litera Navigation Bar -->
    <x-navbar-public />

    <!-- Main Content Area -->
    <main class="flex-1">
        <!-- Hero Section -->
        <section class="py-16 md:py-20 bg-gradient-to-b from-canvas via-surface/40 to-canvas border-b border-line">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-surface border border-line shadow-xs text-xs font-bold text-brand-600 dark:text-brand-400">
                    <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                    LAYANAN &amp; FASILITAS
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-ink tracking-tight font-serif">
                    Layanan Perpustakaan Kampus
                </h1>
                <p class="text-sm sm:text-base text-text-muted max-w-2xl mx-auto leading-relaxed">
                    Menyediakan fasilitas komprehensif bagi sivitas akademika dalam kegiatan belajar, riset ilmiah, dan pemenuhan syarat kelulusan akademik.
                </p>
            </div>
        </section>

        <!-- Services Grid (Gunadarma Library Inspired) -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 1. Layanan Keanggotaan & Sirkulasi -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">1. Layanan Keanggotaan &amp; Sirkulasi</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Layanan pendaftaran keanggotaan perpustakaan bagi seluruh sivitas akademika kampus (mahasiswa aktif, dosen pengajar, dan tenaga kependidikan). Melayani peminjaman mandiri buku teks kuliah, buku diktat acuan, serta pengembalian dan perpanjangan masa pinjam.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-brand-600 dark:text-brand-400">
                        Maksimal peminjaman 3 judul per anggota
                    </div>
                </div>

                <!-- 2. Layanan Digital, E-Paper & E-Journal -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">2. E-Resources &amp; Database Jurnal Internasional</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Akses teks lengkap ribuan artikel ilmiah bereputasi dunia dari penyedia terkemuka seperti <strong>Emerald Insight</strong>, <strong>IGI Global InfoSci</strong>, dan kerja sama Perpustakaan Nasional RI (Perpusnas), yang dapat diakses secara gratis dari jaringan kampus.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-brand-600 dark:text-brand-400">
                        Akses penuh PDF teks ilmiah
                    </div>
                </div>

                <!-- 3. Deposit Paper & Repositori Karya Ilmiah -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">3. Repositori Karya Ilmiah &amp; Skripsi</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Pusat arsip dan publikasi digital karya ilmiah sivitas akademika, mencakup Penulisan Ilmiah (PI), Tugas Akhir, Skripsi, Tesis, dan Disertasi. Setiap naskah terverifikasi melalui uji kemiripan (similarity test) Turnitin sebelum disahkan dalam repositori institusi.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        Terintegrasi dengan akun mahasiswa
                    </div>
                </div>

                <!-- 4. OPAC (Online Public Access Catalog) -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">4. Katalog OPAC Terpusat</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Sistem pencarian katalog publik terotomasi untuk memudahkan penelusuran lokasi rak fisik, ketersediaan stok buku, nomor panggil (call number), klasifikasi subjek, serta ISBN koleksi yang tersebar di perpustakaan pusat maupun perpustakaan fakultas.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-amber-600 dark:text-amber-400">
                        Pencarian judul, pengarang, dan topik
                    </div>
                </div>

                <!-- 5. Perpustakaan Audio-Visual & Multimedia -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">5. Fasilitas Audio-Visual &amp; Multimedia</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Fasilitas penunjang belajar mandiri interaktif dilengkapi perangkat komputer multimedia, perangkat audio, serta studio produksi materi pembelajaran digital dan rekaman kuliah daring.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-rose-600 dark:text-rose-400">
                        Ruang dengar-pandang &amp; studio digital
                    </div>
                </div>

                <!-- 6. Layanan Administrasi & Bebas Perpustakaan -->
                <div class="card-interactive p-6 rounded-3xl bg-surface border border-line shadow-xs hover:border-brand-600 dark:hover:border-brand-400">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xl mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-ink">6. Surat Bebas Perpustakaan &amp; Sumbang Buku</h3>
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Pelayanan administrasi bagi mahasiswa tingkat akhir untuk penerbitan <strong>Surat Keterangan Bebas Perpustakaan (SKBP)</strong> sebagai prasyarat sidang sarjana/wisuda, serta alur penyerahan buku sumbangan alumni untuk memperkaya koleksi sirkulasi.
                    </p>
                    <div class="mt-4 pt-4 border-t border-line text-xs font-semibold text-brand-600 dark:text-brand-400">
                        Prasyarat yudisium &amp; wisuda
                    </div>
                </div>
            </div>

            <!-- CTA to Catalog -->
            <div class="p-8 rounded-3xl bg-surface border border-line text-center space-y-4 shadow-sm">
                <h3 class="text-xl font-bold text-ink">Mulai Gunakan Layanan Perpustakaan Hari Ini</h3>
                <p class="text-xs sm:text-sm text-text-muted max-w-xl mx-auto leading-relaxed">
                    Masuk dengan akun sivitas kampus Anda untuk mulai meminjam buku fisik, membaca buku digital, atau menandai buku favorit Anda.
                </p>
                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm">
                        Jelajahi Katalog Buku
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl border border-line bg-surface hover:bg-surface-soft text-ink text-xs font-semibold transition-all">
                            Masuk Akun Sivitas
                        </a>
                    @endguest
                </div>
            </div>

        </div>
    </main>

    <x-footer-public />

</body>
</html>

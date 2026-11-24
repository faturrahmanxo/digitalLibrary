<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil &amp; Sejarah Perpustakaan Kampus — Litera</title>
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
                    TENTANG KAMI
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-ink tracking-tight font-serif">
                    Profil &amp; Sejarah Perpustakaan Kampus
                </h1>
                <p class="text-sm sm:text-base text-text-muted max-w-2xl mx-auto leading-relaxed">
                    Mengenal perjalanan panjang transformasi perpustakaan perguruan tinggi dari era konvensional hingga menjadi pusat rujukan sains dan repositori digital modern.
                </p>
            </div>
        </section>

        <!-- Content Body -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
            
            <!-- Sejarah Singkat (Gunadarma Inspired) -->
            <section class="space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-line">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                        01
                    </div>
                    <h2 class="text-2xl font-extrabold text-ink tracking-tight">Sejarah Perkembangan Perpustakaan</h2>
                </div>

                <p class="text-sm text-text-muted leading-relaxed">
                    Perpustakaan kampus didirikan seiring dengan berdirinya lembaga pendidikan tinggi, bermula dari komitmen kuat untuk menyediakan referensi akademik bermutu bagi seluruh mahasiswa dan staf pengajar. Sejalan dengan perkembangan institusi dari waktu ke waktu, perpustakaan telah melalui beberapa fase transformasi strategis:
                </p>

                <!-- Timeline Milestones -->
                <div class="relative border-l-2 border-brand-600/30 ml-4 pl-6 space-y-8 my-8">
                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 border-4 border-canvas"></span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-700 dark:text-brand-300">1987</span>
                        <h3 class="text-base font-bold text-ink mt-1">Perpustakaan Sekolah Tinggi Komputer</h3>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">
                            Bermula dari perpustakaan rintisan Sekolah Tinggi Komputer Gunadarma (STKG) di kampus Depok, mengoleksi literatur awal bidang sistem komputer, algoritma, dan pemrograman dasar.
                        </p>
                    </div>

                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 border-4 border-canvas"></span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-700 dark:text-brand-300">1992</span>
                        <h3 class="text-base font-bold text-ink mt-1">Ekspansi Koleksi Ekonomi &amp; Bisnis</h3>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">
                            Didirikan unit perpustakaan tambahan di kampus Kelapa Dua untuk memfasilitasi mahasiswa bidang ilmu ekonomi, manajemen perbankan, dan akuntansi bisnis.
                        </p>
                    </div>

                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 border-4 border-canvas"></span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-700 dark:text-brand-300">1996</span>
                        <h3 class="text-base font-bold text-ink mt-1">Penggabungan &amp; Pembentukan Universitas</h3>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">
                            Seiring resmi bergabungnya STMIK dan STIE menjadi Universitas Gunadarma, didirikan 4 perpustakaan fakultas: Fakultas Teknik Sipil &amp; Perencanaan, Teknik Industri, Psikologi, dan Sastra.
                        </p>
                    </div>

                    <div class="relative">
                        <span class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-brand-600 border-4 border-canvas"></span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-700 dark:text-brand-300">2000 &ndash; Sekarang</span>
                        <h3 class="text-base font-bold text-ink mt-1">Peleburan Terpadu &amp; Era Perpustakaan Digital</h3>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">
                            Seluruh unit perpustakaan dilebur secara menyeluruh menjadi <strong>Perpustakaan Universitas Terpadu</strong> berbasis Teknologi Informasi dan Komunikasi (TIK), meluncurkan katalog OPAC online, e-paper, repositori deposit paper karya ilmiah, dan studio multimedia interaktif.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Visi & Misi -->
            <section class="space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-line">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                        02
                    </div>
                    <h2 class="text-2xl font-extrabold text-ink tracking-tight">Visi &amp; Misi Perpustakaan</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 rounded-3xl bg-surface border border-line shadow-xs">
                        <h3 class="text-base font-bold text-ink flex items-center gap-2 mb-3">
                            <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                            Visi Perpustakaan
                        </h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Menjadi perpustakaan riset perguruan tinggi bertaraf internasional yang unggul berbasis teknologi informasi untuk mendukung terwujudnya institusi pendidikan yang bermutu, inovatif, dan berdaya saing global.
                        </p>
                    </div>

                    <div class="p-6 rounded-3xl bg-surface border border-line shadow-xs">
                        <h3 class="text-base font-bold text-ink flex items-center gap-2 mb-3">
                            <span class="w-2 h-2 rounded-full bg-gold"></span>
                            Misi Perpustakaan
                        </h3>
                        <ul class="text-xs text-text-muted space-y-2 list-disc list-inside leading-relaxed">
                            <li>Menyediakan sumber referensi mutakhir bagi kegiatan pembelajaran dan penelitian.</li>
                            <li>Mengembangkan layanan perpustakaan digital terintegrasi tanpa batas ruang dan waktu.</li>
                            <li>Mengelola dan melestarikan karya ilmiah sivitas akademika secara andal.</li>
                            <li>Membangun kemitraan dan kolaborasi sumber informasi ilmiah nasional maupun internasional.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- CTA Back to Catalog -->
            <div class="p-8 rounded-3xl bg-gradient-to-r from-brand-700 to-brand-900 text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
                <div>
                    <h3 class="text-lg font-bold">Ingin Menelusuri Koleksi Literatur?</h3>
                    <p class="text-xs text-white/80 mt-1">Jelajahi ribuan buku teks, jurnal ilmiah, dan karya penelitian sivitas kampus.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-2xl bg-white text-brand-700 hover:bg-white/90 text-xs font-bold transition-all shadow-md shrink-0">
                    Buka Katalog Koleksi &rarr;
                </a>
            </div>

        </div>
    </main>

    <x-footer-public />

</body>
</html>

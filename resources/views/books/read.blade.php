<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Baca: {{ $book->title }} — Litera</title>
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
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col transition-colors duration-200">

    <!-- Reader Navbar -->
    <header class="sticky top-0 z-40 bg-surface/95 backdrop-blur-md border-b border-line px-4 sm:px-6 py-3 flex items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors shrink-0">
                &larr; Beranda
            </a>
            <div class="min-w-0 truncate">
                <span class="text-xs font-medium text-brand-600 dark:text-brand-400 block truncate">{{ $book->category?->name ?? 'Koleksi Umum' }}</span>
                <h1 class="text-sm font-bold text-ink truncate">{{ $book->title }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <button
                type="button"
                id="theme-toggle-btn"
                class="p-2 rounded-xl border border-line bg-surface text-text-muted hover:text-ink hover:bg-surface-soft active:scale-90 transition-all focus-visible:outline-2 focus-visible:outline-focus"
                aria-label="Ganti mode gelap/terang"
                title="Ganti mode gelap/terang"
            >
                <span class="relative flex h-4 w-4 items-center justify-center">
                    <svg class="absolute h-4 w-4 text-amber-400 transition-all dark:rotate-0 dark:scale-100 dark:opacity-100 -rotate-90 scale-0 opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="absolute h-4 w-4 text-ink transition-all dark:rotate-90 dark:scale-0 dark:opacity-0 rotate-0 scale-100 opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </span>
            </button>

            <!-- Reader Theme Selector -->
            <div class="hidden sm:flex items-center bg-surface-soft p-1 rounded-xl border border-line text-xs font-medium">
                <button type="button" onclick="setReaderTheme('normal')" class="reader-theme-btn px-2.5 py-1 rounded-lg text-ink font-semibold transition-all" data-theme="normal" title="Tema Terang">Terang</button>
                <button type="button" onclick="setReaderTheme('sepia')" class="reader-theme-btn px-2.5 py-1 rounded-lg text-text-muted hover:text-ink transition-all" data-theme="sepia" title="Tema Sepia">Sepia</button>
                <button type="button" onclick="setReaderTheme('dark')" class="reader-theme-btn px-2.5 py-1 rounded-lg text-text-muted hover:text-ink transition-all" data-theme="dark" title="Tema Gelap">Gelap</button>
            </div>

            <!-- Reader Font Size -->
            <div class="flex items-center bg-surface-soft p-1 rounded-xl border border-line text-xs">
                <button type="button" onclick="changeFontSize(-1)" class="px-2 py-1 rounded-lg font-bold text-ink hover:bg-surface transition-all" title="Perkecil Font">A-</button>
                <button type="button" onclick="changeFontSize(1)" class="px-2 py-1 rounded-lg font-bold text-ink hover:bg-surface transition-all" title="Perbesar Font">A+</button>
            </div>

            <!-- Favorite Button -->
            @auth
                <button
                    type="button"
                    onclick="toggleFavorite({{ $book->id }}, this)"
                    class="p-2 rounded-xl border border-line bg-surface hover:bg-surface-soft text-text-muted hover:text-red-500 transition-colors"
                    title="Simpan ke Favorit"
                    data-book-id="{{ $book->id }}"
                >
                    <svg class="w-4 h-4 {{ auth()->user()->hasFavorited($book) ? 'fill-red-500 text-red-500' : 'fill-none stroke-current' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            @else
                <a href="{{ route('login') }}" class="p-2 rounded-xl border border-line bg-surface hover:bg-surface-soft text-text-muted hover:text-red-500 transition-colors" title="Masuk untuk menyimpan favorit">
                    <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </a>
            @endauth

            <!-- Borrow Button -->
            <button
                type="button"
                onclick="openBorrowModal()"
                class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition-all"
            >
                Pinjam Fisik
            </button>
        </div>
    </header>

    <!-- Reader Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row gap-8">
        
        <!-- Left Side: Table of Contents & Metadata Sidebar -->
        <aside class="w-full lg:w-80 shrink-0 space-y-6">
            <!-- Book Info Card -->
            <div class="p-5 rounded-2xl bg-surface border border-line shadow-xs">
                <div class="aspect-3/4 rounded-xl overflow-hidden bg-brand-50 dark:bg-brand-950/40 border border-line mb-4 flex items-center justify-center">
                    @if ($book->cover_path)
                        <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-4">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-brand-600 text-white mb-2">{{ $book->category?->name ?? 'Akademik' }}</span>
                            <p class="text-xs font-bold text-ink line-clamp-3">{{ $book->title }}</p>
                            <p class="text-[11px] text-text-muted mt-1">{{ $book->author }}</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-line">
                        <span class="text-text-muted">Penulis</span>
                        <span class="font-semibold text-ink text-right">{{ $book->author }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-line">
                        <span class="text-text-muted">Tahun Terbit</span>
                        <span class="font-year font-bold text-ink">{{ $book->publication_year }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-line">
                        <span class="text-text-muted">ISBN</span>
                        <span class="font-mono text-ink">{{ $book->isbn }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-line">
                        <span class="text-text-muted">Stok Fisik</span>
                        <span class="font-semibold {{ $book->stock_available > 0 ? 'text-success-fg' : 'text-danger-fg' }}">
                            {{ $book->stock_available }} dari {{ $book->stock_total }} tersedia
                        </span>
                    </div>
                </div>
            </div>

            <!-- Chapters Index Navigation -->
            <div class="p-5 rounded-2xl bg-surface border border-line shadow-xs">
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-3">Daftar Isi Pratinjau</h3>
                <nav class="space-y-1.5">
                    @foreach ($chapters as $index => $chap)
                        <a
                            href="#bab-{{ $chap['number'] }}"
                            class="block px-3 py-2 rounded-xl text-xs font-medium text-ink hover:bg-surface-soft hover:text-brand-600 border border-transparent hover:border-line transition-all"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-brand-600 dark:text-brand-400">Bab {{ $chap['number'] }}</span>
                                <span class="text-[10px] text-text-muted">Hal {{ $chap['pages'] }}</span>
                            </div>
                            <p class="text-xs text-ink truncate mt-0.5">{{ $chap['title'] }}</p>
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <!-- Right Side: Reading Area -->
        <section class="flex-1 min-w-0">
            <!-- Reader Paper Container -->
            <div id="reader-paper" class="p-6 sm:p-10 lg:p-12 rounded-3xl bg-surface border border-line shadow-md transition-colors duration-200">
                
                <!-- Notice Badge -->
                <div class="mb-8 p-4 rounded-2xl bg-brand-50/60 dark:bg-brand-950/40 border border-brand-200/60 dark:border-brand-500/30 text-xs text-brand-900 dark:text-brand-200 flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Akses Digital Sivitas Akademika Kampus</p>
                        <p class="mt-0.5 opacity-90">Pratinjau digital ini disajikan untuk keperluan telaah materi kuliah dan referensi karya ilmiah kampus. Eksemplar buku fisik lengkap dapat dipinjam melalui loket sirkulasi perpustakaan.</p>
                    </div>
                </div>

                <!-- Book Header -->
                <div class="mb-10 text-center border-b border-line pb-8">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 mb-3">
                        {{ $book->category?->name ?? 'Koleksi Akademik' }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight font-serif">{{ $book->title }}</h2>
                    <p class="mt-2 text-sm text-text-muted">Karya <span class="font-semibold text-ink">{{ $book->author }}</span>, Diterbitkan oleh {{ $book->publisher }} (<span class="font-year font-bold">{{ $book->publication_year }}</span>)</p>
                </div>

                <!-- Synopsis Section -->
                <div class="mb-10 p-6 rounded-2xl bg-surface-soft/60 border border-line">
                    <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-2">Sinopsis Buku</h3>
                    <p class="text-sm leading-relaxed text-text-muted">{{ $synopsis }}</p>
                </div>

                <!-- Chapters Content -->
                <div id="reader-text" class="space-y-12 text-base leading-relaxed text-ink">
                    @foreach ($chapters as $chap)
                        <article id="bab-{{ $chap['number'] }}" class="scroll-mt-24 border-b border-line pb-10">
                            <div class="mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Bab {{ $chap['number'] }} - Halaman {{ $chap['pages'] }}</span>
                                <h3 class="text-xl font-bold text-ink mt-1 font-serif">{{ $chap['title'] }}</h3>
                            </div>
                            <div class="prose max-w-none space-y-4 text-justify">
                                <p>{{ $chap['content'] }}</p>
                                <p>Dalam perspektif studi kepustakaan dan riset empiris di perguruan tinggi, pemahaman menyeluruh terhadap bab ini memberikan fondasi yang esensial. Setiap mahasiswa dan peneliti diharapkan dapat mengidentifikasi keterkaitan antara teori yang dijabarkan dengan studi kasus faktual di lapangan. Referensi silang ke publikasi internasional yang terkait dalam indeks Emerald Insight dan IGI Global sangat dianjurkan untuk memperkaya analisis.</p>
                                <p>Melalui telaah komparatif pada bagian ini, pembaca dibimbing untuk menyusun sintesis komprehensif. Penulis menekankan pentingnya akurasi data serta metodologi yang teruji sebelum melangkah ke bab analisis eksperimental selanjutnya.</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Footer Call to Action -->
                <div class="mt-12 p-8 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-800 text-white text-center">
                    <h4 class="text-lg font-bold">Ingin membaca keseluruhan isi buku fisik ini?</h4>
                    <p class="text-xs sm:text-sm text-brand-100 max-w-lg mx-auto mt-2">
                        Koleksi fisik lengkap tersedia di rak perpustakaan kampus. Anda dapat mengajukan peminjaman buku fisik secara online dan mengambilnya di loket sirkulasi.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                        <button
                            type="button"
                            onclick="openBorrowModal()"
                            class="px-5 py-2.5 rounded-xl bg-white text-brand-700 font-bold text-xs sm:text-sm hover:bg-brand-50 transition-all shadow-md"
                        >
                            Ajukan Peminjaman Fisik
                        </button>
                        <a
                            href="{{ route('home') }}"
                            class="px-5 py-2.5 rounded-xl bg-brand-700/60 hover:bg-brand-700 text-white font-semibold text-xs sm:text-sm border border-white/20 transition-all"
                        >
                            Kembali ke Katalog
                        </a>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <!-- Borrow Modal -->
    <div id="borrow-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up">
            @auth
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <h3 class="text-base font-bold text-ink">Pinjam Buku Fisik</h3>
                    <button type="button" onclick="closeBorrowModal()" class="text-text-muted hover:text-ink text-sm p-1">&times;</button>
                </div>
                <form method="POST" action="{{ route('books.borrow', $book) }}" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <p class="text-xs text-text-muted">Judul Buku:</p>
                        <p class="text-sm font-bold text-ink">{{ $book->title }}</p>
                    </div>

                    <!-- Duration Choice -->
                    <div>
                        <label for="modal_duration_days" class="block text-xs font-semibold text-ink mb-1">
                            Durasi Peminjaman:
                        </label>
                        <select
                            id="modal_duration_days"
                            name="duration_days"
                            class="w-full px-3 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-500"
                        >
                            <option value="3">3 Hari (Baca Cepat / Ujian)</option>
                            <option value="7" selected>7 Hari (Standar Mahasiswa)</option>
                            <option value="14">14 Hari (2 Minggu / Tugas Besar)</option>
                            <option value="21">21 Hari (3 Minggu / Riset Laboratorium)</option>
                            <option value="30">30 Hari (1 Bulan / Penulisan Skripsi &amp; Dosen)</option>
                        </select>
                    </div>

                    <div class="p-3 rounded-xl bg-surface-soft border border-line text-[11px] text-text-muted">
                        Peminjam: <strong class="text-ink">{{ auth()->user()->name }}</strong> ({{ auth()->user()->role_label }}). Buku dapat diambil di konter sirkulasi dengan menunjukkan kartu sivitas akademika.
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="closeBorrowModal()" class="px-4 py-2 rounded-xl border border-line text-xs font-semibold text-text-muted hover:bg-surface-soft">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs">
                            Konfirmasi Pinjam
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 dark:bg-brand-900/40 text-brand-600 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink">Khusus Sivitas Akademika</h3>
                    <p class="text-xs text-text-muted mt-1 px-4">
                        Peminjaman buku fisik perpustakaan hanya tersedia bagi sivitas akademika kampus (Mahasiswa, Dosen, &amp; Staf). Silakan masuk ke akun kampus Anda.
                    </p>
                    <div class="mt-6 flex flex-col gap-2">
                        <a href="{{ route('login') }}" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold text-center shadow-xs">
                            Masuk dengan Akun Kampus
                        </a>
                        <a href="{{ route('register') }}" class="w-full py-2.5 rounded-xl border border-line bg-surface hover:bg-surface-soft text-ink text-xs font-semibold text-center">
                            Daftar Akun Baru
                        </a>
                        <button type="button" onclick="closeBorrowModal()" class="mt-1 text-xs text-text-muted hover:underline">
                            Nanti Saja
                        </button>
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <!-- Scripts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function openBorrowModal() {
            document.getElementById('borrow-modal').classList.remove('hidden');
        }
        function closeBorrowModal() {
            document.getElementById('borrow-modal').classList.add('hidden');
        }

        let currentFontSize = 16;
        function changeFontSize(delta) {
            currentFontSize = Math.max(14, Math.min(24, currentFontSize + delta * 2));
            document.getElementById('reader-text').style.fontSize = currentFontSize + 'px';
        }

        function setReaderTheme(theme) {
            const paper = document.getElementById('reader-paper');
            const btns = document.querySelectorAll('.reader-theme-btn');
            
            btns.forEach(btn => {
                if (btn.getAttribute('data-theme') === theme) {
                    btn.classList.add('font-semibold', 'text-ink', 'bg-surface');
                    btn.classList.remove('text-text-muted');
                } else {
                    btn.classList.remove('font-semibold', 'text-ink', 'bg-surface');
                    btn.classList.add('text-text-muted');
                }
            });

            if (theme === 'sepia') {
                paper.style.backgroundColor = '#fbf0d9';
                paper.style.color = '#3b2f2f';
            } else if (theme === 'dark') {
                paper.style.backgroundColor = '#111827';
                paper.style.color = '#f3f4f6';
            } else {
                paper.style.backgroundColor = '';
                paper.style.color = '';
            }
        }

        function toggleFavorite(bookId, buttonEl) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/books/${bookId}/favorite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(res => res.json())
            .then(data => {
                const svg = buttonEl.querySelector('svg');
                if (data.status === 'added') {
                    svg.classList.remove('fill-none');
                    svg.classList.add('fill-red-500', 'text-red-500');
                } else {
                    svg.classList.remove('fill-red-500', 'text-red-500');
                    svg.classList.add('fill-none');
                }
            })
            .catch(err => {
                console.error(err);
            });
        }
    </script>
</body>
</html>

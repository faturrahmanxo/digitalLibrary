<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @if ($collectionType === 'physical')
            Katalog Buku Fisik &mdash; Litera
        @elseif (in_array($collectionType, ['ebook', 'e-book'], true))
            Katalog E-Book &mdash; Litera
        @else
            Katalog Pustaka &amp; Koleksi Riset &mdash; Litera
        @endif
    </title>
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
        @keyframes orbitClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes orbitCounterClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }
        @keyframes planetPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 20px rgba(0, 90, 157, 0.35)); }
            50% { transform: scale(1.04); filter: drop-shadow(0 0 35px rgba(2, 132, 199, 0.6)); }
        }
        .animate-orbit-slow {
            animation: orbitClockwise 36s linear infinite;
        }
        .animate-counter-slow {
            animation: orbitCounterClockwise 36s linear infinite;
        }
        .animate-orbit-slower-rev {
            animation: orbitCounterClockwise 52s linear infinite;
        }
        .animate-counter-slower-rev {
            animation: orbitClockwise 52s linear infinite;
        }
        .animate-planet-pulse {
            animation: planetPulse 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">

    <!-- Public Multi-Genre Litera Navigation Bar -->
    <x-navbar-public />

    @php
        $isPhysicalPage = ($collectionType === 'physical');
        $isEbookPage = in_array($collectionType, ['ebook', 'e-book'], true);

        if ($isPhysicalPage) {
            $heroEyebrow = 'Koleksi Fisik Perpustakaan';
            $heroTitle = 'Katalog Buku Fisik';
            $heroHighlight = 'Tersedia di Rak Kampus.';
            $heroDescription = 'Koleksi buku fisik terlengkap yang tersedia di rak perpustakaan dan siap dipinjam secara langsung oleh sivitas akademika.';
        } elseif ($isEbookPage) {
            $heroEyebrow = 'Pustaka Digital Kampus';
            $heroTitle = 'Katalog E-Book';
            $heroHighlight = 'Akses Digital & Kapan Saja.';
            $heroDescription = 'Koleksi buku digital, karya ilmiah, dan novel yang dapat dibaca secara daring maupun diunduh langsung untuk riset perkuliahan.';
        } else {
            $heroEyebrow = 'Eksplorasi Literatur Kampus';
            $heroTitle = 'Katalog Koleksi Pustaka';
            $heroHighlight = 'Koleksi Riset Akademik.';
            $heroDescription = 'Akses terkurasi menuju ribuan buku fisik perkuliahan dan koleksi digital perpustakaan kampus.';
        }

        $activeFilterCount = (int)($categoryId !== null) + (int)(isset($shelfId) && $shelfId !== null) + (int)($author !== '') + (int)($isbn !== '') + (int)($publisher !== '') + (int)($year !== null) + (int)($availability !== '') + (int)($collectionType !== '');
        $hasActiveFilters = ($search || $categoryId || (isset($shelfId) && $shelfId) || $author || $isbn || $publisher || $year || $availability || $collectionType);
    @endphp

    <!-- Reusable Hero Component (Identical Layout & Animation, Contextual Text) -->
    <x-catalog-hero
        :eyebrow="$heroEyebrow"
        :title="$heroTitle"
        :highlight="$heroHighlight"
        :description="$heroDescription"
        :search="$search"
        :collection-type="$collectionType"
        :total-books="$books->total()"
        :active-filter-count="$activeFilterCount"
        :has-active-filters="$hasActiveFilters"
    />

    <!-- ======================================================== -->
    <!-- MAIN CATALOG BOOK GRID (Unified Consistent Card Style)   -->
    <!-- ======================================================== -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        
        <!-- Format Koleksi Quick Switcher (Semua, Buku Fisik, E-book) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="inline-flex p-1 rounded-2xl bg-surface border border-line shadow-2xs self-start font-sans">
                <a
                    href="{{ route('catalog.index', array_filter(array_merge(request()->query(), ['collection_type' => '']))) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ empty($collectionType) ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    Semua Koleksi
                </a>
                <a
                    href="{{ route('catalog.index', array_merge(request()->query(), ['collection_type' => 'physical'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $isPhysicalPage ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    Buku Fisik
                </a>
                <a
                    href="{{ route('catalog.index', array_merge(request()->query(), ['collection_type' => 'ebook'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $isEbookPage ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    E-book
                </a>
            </div>

            @if ($collectionType)
                <span class="text-xs font-medium text-text-muted font-sans">
                    Menampilkan format: <strong class="text-ink">{{ $isPhysicalPage ? 'Buku Fisik' : 'E-book' }}</strong>
                </span>
            @endif
        </div>

        @if ($books->isEmpty())
            <div class="p-16 text-center rounded-3xl border border-line bg-surface max-w-lg mx-auto shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink font-serif">Tidak Ada Koleksi yang Cocok</h3>
                <p class="text-xs text-text-muted mt-1.5 mb-5 max-w-sm mx-auto font-sans leading-relaxed">
                    Koleksi yang Anda cari belum ditemukan. Silakan periksa kembali filter pencarian atau kata kunci Anda.
                </p>
                <a href="{{ route('catalog.index', $collectionType ? ['collection_type' => $collectionType] : []) }}" class="inline-flex px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs font-sans">
                    Reset Filter Pencarian
                </a>
            </div>
        @else
            <!-- Unified 5-Column Responsive Card Grid (Identical Style as Beranda) -->
            <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 sm:gap-x-5 mb-12">
                @foreach ($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-8 flex justify-center">
                {{ $books->links() }}
            </div>
        @endif

    </main>

    <!-- ======================================================== -->
    <!-- STREAMLINED CONTEXTUAL FILTER MODAL                      -->
    <!-- ======================================================== -->
    <div id="filter-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="filter-modal-backdrop" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
        
        <!-- Modal Content Card -->
        <div class="relative w-full max-w-lg rounded-3xl bg-surface border border-line p-6 sm:p-7 shadow-2xl z-10 animate-fade-in-up">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-line mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-500/10 dark:bg-brand-400/15 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-ink">
                        @if ($isPhysicalPage)
                            Filter Buku Fisik
                        @elseif ($isEbookPage)
                            Filter E-Book
                        @else
                            Filter Koleksi Pustaka
                        @endif
                    </h2>
                </div>
                <button
                    type="button"
                    id="btn-close-filter-modal"
                    class="p-2 rounded-xl text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                    aria-label="Tutup filter"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('catalog.index') }}" class="space-y-4">
                <!-- Keep Current Search Term -->
                @if ($search)<input type="hidden" name="q" value="{{ $search }}">@endif

                @if ($isPhysicalPage)
                    {{-- 1. BUKU FISIK CONTEXTUAL FILTERS --}}
                    <input type="hidden" name="collection_type" value="physical">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Klasifikasi Kategori</label>
                            <select
                                name="category_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected((int) $categoryId === $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lokasi Rak Buku -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Lokasi Rak Buku</label>
                            <select
                                name="shelf_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Rak</option>
                                @foreach ($shelves ?? [] as $sh)
                                    <option value="{{ $sh->id }}" @selected((int) ($shelfId ?? 0) === $sh->id)>
                                        {{ $sh->name }} ({{ $sh->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Penerbit -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Penerbit</label>
                            <input
                                type="text"
                                name="publisher"
                                value="{{ $publisher }}"
                                placeholder="Nama penerbit..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"
                            >
                        </div>

                        <!-- Ketersediaan Stok -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Status Ketersediaan</label>
                            <select
                                name="availability"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Status</option>
                                <option value="available" @selected($availability === 'available')>Tersedia untuk Dipinjam</option>
                                <option value="empty" @selected($availability === 'empty')>Sedang Dipinjam</option>
                            </select>
                        </div>
                    </div>

                @elseif ($isEbookPage)
                    {{-- 2. E-BOOK CONTEXTUAL FILTERS --}}
                    <input type="hidden" name="collection_type" value="ebook">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Klasifikasi Kategori</label>
                            <select
                                name="category_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected((int) $categoryId === $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tahun Terbit -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Tahun Terbit</label>
                            <select
                                name="year"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Tahun</option>
                                @foreach ($years as $yr)
                                    <option value="{{ $yr }}" @selected((int) $year === (int) $yr)>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Penulis / Pengarang -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Penulis / Pengarang</label>
                            <input
                                type="text"
                                name="author"
                                value="{{ $author }}"
                                placeholder="Nama penulis..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"
                            >
                        </div>

                        <!-- Penerbit -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Penerbit</label>
                            <input
                                type="text"
                                name="publisher"
                                value="{{ $publisher }}"
                                placeholder="Nama penerbit..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"
                            >
                        </div>
                    </div>

                @else
                    {{-- 3. GENERAL / COMBINED CATALOG FILTERS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Format Koleksi</label>
                            <select
                                name="collection_type"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Format (Fisik &amp; E-book)</option>
                                <option value="physical">Buku Fisik</option>
                                <option value="ebook">E-book</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Klasifikasi Kategori</label>
                            <select
                                name="category_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected((int) $categoryId === $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Pengarang / Penerbit</label>
                            <input
                                type="text"
                                name="author"
                                value="{{ $author }}"
                                placeholder="Nama pengarang..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1.5">Tahun Terbit</label>
                            <select
                                name="year"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                            >
                                <option value="">Semua Tahun</option>
                                @foreach ($years as $yr)
                                    <option value="{{ $yr }}" @selected((int) $year === (int) $yr)>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <!-- Common Field: Urutan / Sort -->
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Urutan Tampilan</label>
                    <select
                        name="sort"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 cursor-pointer"
                    >
                        <option value="newest" @selected($sort === 'newest')>Terbaru Masuk</option>
                        <option value="popular" @selected($sort === 'popular')>Stok Terbanyak</option>
                        <option value="title_asc" @selected($sort === 'title_asc')>Judul A &ndash; Z</option>
                        <option value="title_desc" @selected($sort === 'title_desc')>Judul Z &ndash; A</option>
                        <option value="year_desc" @selected($sort === 'year_desc')>Tahun Terbit (Terbaru)</option>
                    </select>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between pt-5 border-t border-line mt-5">
                    <a
                        href="{{ route('catalog.index', array_filter(['collection_type' => $collectionType, 'q' => $search])) }}"
                        class="text-xs font-semibold text-text-muted hover:text-ink transition-colors"
                    >
                        Reset Filter
                    </a>

                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            onclick="closeFilterModal()"
                            class="px-4 py-2 rounded-xl border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                        >
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <x-footer-public />

    <script>
        // Filter Modal Controls
        const filterModal = document.getElementById('filter-modal');
        const filterBackdrop = document.getElementById('filter-modal-backdrop');
        const openFilterBtn = document.getElementById('btn-open-filter-modal');
        const closeFilterBtn = document.getElementById('btn-close-filter-modal');

        function openFilterModal() {
            if (filterModal) {
                filterModal.classList.remove('hidden');
                filterModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeFilterModal() {
            if (filterModal) {
                filterModal.classList.add('hidden');
                filterModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        }

        if (openFilterBtn) {
            openFilterBtn.addEventListener('click', openFilterModal);
        }

        if (closeFilterBtn) {
            closeFilterBtn.addEventListener('click', closeFilterModal);
        }

        if (filterBackdrop) {
            filterBackdrop.addEventListener('click', closeFilterModal);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && filterModal && !filterModal.classList.contains('hidden')) {
                closeFilterModal();
            }
        });
    </script>
</body>
</html>

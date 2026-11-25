<x-layouts.app :title="auth()->user()?->isAnggota() ? 'Katalog Koleksi Buku' : 'Data Buku — Manajemen Terpusat'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ auth()->user()?->isAnggota() ? 'Katalog Koleksi' : 'Data Buku' }}</span>
    </x-slot:breadcrumbs>

    @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();
        $isAnggota = $authUser?->isAnggota();
        $shelves = $shelves ?? \App\Models\Shelf::orderBy('name')->get();
        $publishers = $publishers ?? \App\Models\Publisher::orderBy('name')->get();

        $search = request('q', $search ?? '');
        $categoryId = request('category_id', $categoryId ?? '');
        $publisherId = request('publisher_id', '');
        $shelfId = request('shelf_id', '');
        $availability = request('availability', $availability ?? '');
        $collectionType = request('collection_type', $collectionType ?? '');
        $statusFilter = request('status', '');
        $sort = request('sort', 'title_asc');

        $hasActiveFilter = $search !== '' || $categoryId !== '' || $publisherId !== '' || $shelfId !== '' || $availability !== '' || $collectionType !== '' || $statusFilter !== '' || $sort !== 'title_asc';
    @endphp

    @if (session('success'))
        <div role="status" aria-live="polite" class="mb-6 rounded-card border border-success-fg/20 bg-success-bg p-4 text-success-fg flex items-start justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-danger-fg/20 bg-danger-bg p-4 text-danger-fg flex items-start justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isAnggota ? 'Katalog Koleksi Buku' : 'Data Buku' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isAnggota ? 'Temukan pustaka referensi dan periksa ketersediaan buku fisik di perpustakaan.' : 'Pusat pengelolaan koleksi buku fisik, e-book, kategori, rak, dan status sirkulasi perpustakaan.' }}
            </p>
        </div>

        @if (! $isAnggota)
            @can('create', \App\Models\Book::class)
                <a
                    href="{{ route('books.create') }}"
                    class="min-h-11 inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Buku</span>
                </a>
            @endcan
        @endif
    </div>

    <!-- 07. CENTRALIZED SEARCH & STREAMLINED FILTERS -->
    <form method="GET" action="{{ route('books.index') }}" class="bg-surface rounded-card border border-line p-4 sm:p-5 mb-6 shadow-xs space-y-3">
        <!-- Main Filter Row: 6 Core Aligned Controls -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
            <!-- 1. Keyword Search -->
            <div class="sm:col-span-2 md:col-span-3">
                <label for="q" class="block text-xs font-semibold text-text-muted mb-1">Pencarian</label>
                <div class="relative">
                    <input
                        type="search"
                        id="q"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Judul, penulis, ISBN..."
                        class="w-full min-h-9.5 pl-8.5 pr-3 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                    >
                    <svg class="w-4 h-4 text-text-muted absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- 2. Jenis Koleksi -->
            <div class="md:col-span-2">
                <label for="collection_type" class="block text-xs font-semibold text-text-muted mb-1">Jenis Koleksi</label>
                <select id="collection_type" name="collection_type" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Jenis</option>
                    <option value="physical" @selected($collectionType === 'physical')>Buku Fisik</option>
                    <option value="e-book" @selected(in_array($collectionType, ['ebook', 'e-book'], true))>E-book</option>
                    <option value="novel" @selected($collectionType === 'novel')>Novel Digital</option>
                </select>
            </div>

            <!-- 3. Kategori -->
            <div class="md:col-span-2">
                <label for="category_id" class="block text-xs font-semibold text-text-muted mb-1">Kategori</label>
                <select id="category_id" name="category_id" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Penerbit -->
            <div class="md:col-span-2">
                <label for="publisher_id" class="block text-xs font-semibold text-text-muted mb-1">Penerbit</label>
                <select id="publisher_id" name="publisher_id" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Penerbit</option>
                    @foreach ($publishers as $pub)
                        <option value="{{ $pub->id }}" @selected((string) $publisherId === (string) $pub->id)>{{ $pub->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Stok / Ketersediaan -->
            <div class="md:col-span-2">
                <label for="availability" class="block text-xs font-semibold text-text-muted mb-1">Ketersediaan Stok</label>
                <select id="availability" name="availability" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Stok</option>
                    <option value="available" @selected($availability === 'available')>Stok Tersedia (> 0)</option>
                    <option value="empty" @selected($availability === 'empty')>Stok Habis (0)</option>
                </select>
            </div>

            <!-- 6. Status Katalog -->
            <div class="md:col-span-1">
                <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Status</label>
                <select id="status" name="status" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua</option>
                    <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                    <option value="inactive" @selected($statusFilter === 'inactive')>Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- Advanced Filter (Collapsible details for Rak Buku Fisik) -->
        <div id="advanced-filters" class="{{ $shelfId ? 'block' : 'hidden' }} pt-3 border-t border-line/60">
            <div class="max-w-xs">
                <label for="shelf_id" class="block text-xs font-semibold text-text-muted mb-1">Rak Buku Fisik</label>
                <select id="shelf_id" name="shelf_id" class="w-full min-h-9.5 px-2.5 py-1.5 rounded-control border border-line text-xs sm:text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Lokasi Rak</option>
                    @foreach ($shelves as $shelf)
                        <option value="{{ $shelf->id }}" @selected((string) $shelfId === (string) $shelf->id)>
                            {{ $shelf->name }} ({{ $shelf->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Filter Action Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-line/60 text-xs">
            <div class="flex items-center gap-3">
                <span class="text-text-muted font-medium">
                    Total: <strong class="text-ink">{{ $books->total() }}</strong> buku
                </span>
                <button
                    type="button"
                    onclick="document.getElementById('advanced-filters').classList.toggle('hidden')"
                    class="text-brand-600 dark:text-brand-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>Filter Lanjutan (Rak)</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                @if ($hasActiveFilter)
                    <a href="{{ route('books.index') }}" class="min-h-9 inline-flex items-center px-3.5 py-1.5 rounded-control border border-line text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors">
                        Reset
                    </a>
                @endif
                <button type="submit" class="min-h-9 px-4 py-1.5 rounded-control bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors shadow-xs cursor-pointer">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <!-- View Mode Switcher for Staff & Admin -->
    @if (! $isAnggota)
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-text-muted">Tampilan:</span>
                <div class="inline-flex rounded-control border border-line bg-surface p-1 text-xs">
                    <button type="button" id="toggle-view-table" class="px-3 py-1 rounded-control font-semibold bg-brand-600 text-white cursor-pointer transition-colors">
                        Tabel Terpusat
                    </button>
                    <button type="button" id="toggle-view-grid" class="px-3 py-1 rounded-control font-medium text-text-muted hover:text-ink cursor-pointer transition-colors">
                        Kartu Sampul
                    </button>
                </div>
            </div>

            <p class="text-xs text-text-muted">Halaman {{ $books->currentPage() }} dari {{ max(1, $books->lastPage()) }}</p>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 07. AUTHORITATIVE CENTRALIZED DATA BUKU TABLE            -->
    <!-- ======================================================== -->
    <div id="books-table-container" class="bg-surface rounded-card border border-line overflow-hidden shadow-xs mb-6">
        @if ($books->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-surface-soft border border-line text-text-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Tidak ada buku yang sesuai kriteria</h3>
                <p class="mt-1 text-xs text-text-muted">Coba bersihkan filter atau gunakan kata kunci pencarian yang lain.</p>
                @if ($hasActiveFilter)
                    <a href="{{ route('books.index') }}" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors">
                        Bersihkan Filter
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4 w-16">Cover</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[240px]">Judul &amp; Informasi</th>
                            <th scope="col" class="py-3.5 px-4">Penulis</th>
                            <th scope="col" class="py-3.5 px-4">Jenis Koleksi</th>
                            <th scope="col" class="py-3.5 px-4">Kategori &amp; Rak</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Stok</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Status</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($books as $book)
                            @php
                                $isPhysical = $book->collection_type === 'physical';
                                $available = (int) ($book->available_copies ?? $book->stock_available ?? 0);
                                $total = (int) ($book->total_copies ?? $book->stock_total ?? 0);
                                $isActive = ($book->status ?? 'active') === 'active';
                            @endphp
                            <tr class="hover:bg-surface-soft/50 transition-colors">
                                <!-- Cover Thumbnail -->
                                <td class="py-3.5 px-4">
                                    <div class="w-11 h-14 rounded-lg bg-surface-soft border border-line overflow-hidden shrink-0 shadow-xs flex items-center justify-center">
                                        @if ($book->cover_path)
                                            <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($book->title, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Judul & Metadata -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('books.manage', $book) }}" class="font-bold text-ink hover:text-brand-600 line-clamp-1 transition-colors" title="{{ $book->title }}">
                                        {{ $book->title }}
                                    </a>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5 text-xs text-text-muted">
                                        @if ($book->isbn)
                                            <span class="font-mono">ISBN: {{ $book->isbn }}</span>
                                        @endif
                                        @if ($book->publication_year)
                                            <span>({{ $book->publication_year }})</span>
                                        @endif
                                        @if ($book->publisher || $book->publisher_id)
                                            <span class="truncate max-w-[160px]">&bull; {{ $book->publisher?->name ?? $book->publisher }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Penulis -->
                                <td class="py-3.5 px-4 text-text-muted text-xs font-medium">
                                    {{ $book->author }}
                                </td>

                                <!-- Jenis Koleksi Badge -->
                                <td class="py-3.5 px-4">
                                    @if ($isPhysical)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                            Buku Fisik
                                        </span>
                                    @elseif ($book->collection_type === 'novel')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40">
                                            Novel
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">
                                            E-book
                                        </span>
                                    @endif
                                </td>

                                <!-- Kategori & Rak -->
                                <td class="py-3.5 px-4 text-xs">
                                    <span class="font-semibold text-ink block">{{ $book->category?->name ?? '—' }}</span>
                                    @if ($isPhysical && ($book->shelf || $book->shelf_id))
                                        <span class="text-text-muted block mt-0.5">
                                            Rak: <strong class="text-ink font-mono">{{ $book->shelf?->code ?? 'R-' . $book->shelf_id }}</strong>
                                        </span>
                                    @endif
                                </td>

                                <!-- Stok (Fisik: angka; E-book: Digital) -->
                                <td class="py-3.5 px-4 text-center">
                                    @if ($isPhysical)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-sm font-bold tabular-nums {{ $available > 0 ? 'text-ink' : 'text-danger-fg' }}">
                                                {{ $available }}
                                            </span>
                                            <span class="text-[10px] text-text-muted tabular-nums">dari {{ $total }} eks</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 border border-brand-200 dark:border-brand-500/30">
                                            Digital
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Aktif/Nonaktif -->
                                <td class="py-3.5 px-4 text-center">
                                    @if ($isActive)
                                        <x-status-badge variant="success">Aktif</x-status-badge>
                                    @else
                                        <x-status-badge variant="neutral">Nonaktif</x-status-badge>
                                    @endif
                                </td>

                                <!-- Aksi (Detail, Edit, Hapus) -->
                                <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a
                                        href="{{ route('books.manage', $book) }}"
                                        class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-brand-600 hover:text-brand-700 hover:bg-surface-soft transition-colors"
                                        title="Detail Buku"
                                    >
                                        Detail
                                    </a>

                                    @can('update', $book)
                                        <a
                                            href="{{ route('books.edit', $book) }}"
                                            class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors"
                                            title="Edit Buku"
                                        >
                                            Edit
                                        </a>
                                    @endcan

                                    @can('delete', $book)
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-danger-fg hover:bg-danger-bg hover:border-danger-fg/30 transition-colors cursor-pointer"
                                            data-archive-action="{{ route('books.destroy', $book) }}"
                                            data-archive-title="Arsipkan Buku?"
                                            data-archive-desc="Buku '{{ $book->title }}' akan diarsipkan dari katalog aktif namun data sirkulasi peminjaman tetap tersimpan."
                                            data-archive-label="Ya, Arsipkan"
                                            title="Hapus / Arsipkan Buku"
                                        >
                                            Hapus
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($books->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $books->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- CARD GRID VIEW (Optional / Member Experience)            -->
    <!-- ======================================================== -->
    <div id="books-grid-container" class="{{ $isAnggota ? 'block' : 'hidden' }} mb-6">
        @if ($books->isNotEmpty())
            <div class="grid grid-cols-1 min-[400px]:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                @foreach ($books as $bookItem)
                    @if ($bookItem->collection_type === 'e-book')
                        <x-book-card-ebook :book="$bookItem" :href="route('books.manage', $bookItem)" />
                    @else
                        <div class="group card-interactive bg-surface rounded-2xl border border-line p-4 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                            <div>
                                <div class="w-full aspect-[4/5] rounded-xl bg-gradient-to-tr from-brand-800 via-brand-600 to-brand-400 text-white p-4 flex flex-col justify-between shadow-xs mb-3.5 relative overflow-hidden group-hover:scale-[1.03] transition-transform duration-300 ease-out">
                                    @if ($bookItem->cover_path)
                                        <img src="{{ asset('storage/' . $bookItem->cover_path) }}" alt="{{ $bookItem->title }}" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                    @endif
                                    <span class="relative z-10 text-[10px] font-bold uppercase tracking-wider bg-white/20 backdrop-blur-md px-2 py-0.5 rounded w-max border border-white/20 truncate max-w-full text-white">
                                        {{ $bookItem->category?->name ?? 'Koleksi' }}
                                    </span>
                                    <div class="relative z-10">
                                        <p class="text-[11px] uppercase tracking-wider text-white/70 font-semibold mb-0.5">Litera</p>
                                        <p class="text-xs font-bold leading-snug line-clamp-2 text-white">{{ $bookItem->title }}</p>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-sm font-bold text-ink leading-snug line-clamp-1 group-hover:text-brand-600 transition-colors">
                                        {{ $bookItem->title }}
                                    </h3>
                                    <p class="text-xs text-text-muted line-clamp-1">{{ $bookItem->author }}</p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-line mt-3 flex items-center justify-between text-xs">
                                <div>
                                    @if ($bookItem->collection_type === 'physical')
                                        <span class="font-bold text-ink tabular-nums">{{ $bookItem->available_copies ?? $bookItem->stock_available ?? 0 }}</span>
                                        <span class="text-text-muted text-[10px]">tersedia</span>
                                    @else
                                        <span class="text-[10px] font-semibold text-brand-600">Digital</span>
                                    @endif
                                </div>
                                <a href="{{ route('books.manage', $bookItem) }}" class="font-semibold text-brand-600 hover:text-brand-700">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($books->hasPages())
                <div class="mt-6">
                    {{ $books->links() }}
                </div>
            @endif
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBtn = document.getElementById('toggle-view-table');
            const gridBtn = document.getElementById('toggle-view-grid');
            const tableContainer = document.getElementById('books-table-container');
            const gridContainer = document.getElementById('books-grid-container');

            if (tableBtn && gridBtn && tableContainer && gridContainer) {
                tableBtn.addEventListener('click', function () {
                    tableContainer.classList.remove('hidden');
                    gridContainer.classList.add('hidden');
                    tableBtn.classList.add('bg-brand-600', 'text-white');
                    tableBtn.classList.remove('text-text-muted');
                    gridBtn.classList.remove('bg-brand-600', 'text-white');
                    gridBtn.classList.add('text-text-muted');
                });

                gridBtn.addEventListener('click', function () {
                    gridContainer.classList.remove('hidden');
                    tableContainer.classList.add('hidden');
                    gridBtn.classList.add('bg-brand-600', 'text-white');
                    gridBtn.classList.remove('text-text-muted');
                    tableBtn.classList.remove('bg-brand-600', 'text-white');
                    tableBtn.classList.add('text-text-muted');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

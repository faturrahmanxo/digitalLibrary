<x-layouts.app :title="auth()->user()?->isAnggota() ? 'Katalog Koleksi Buku' : 'Katalog Buku'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ auth()->user()?->isAnggota() ? 'Katalog Koleksi' : 'Buku' }}</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <div role="alert" class="mb-6 rounded-card border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ auth()->user()?->isAnggota() ? 'Katalog Koleksi Buku' : 'Katalog Buku' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ auth()->user()?->isAnggota() ? 'Temukan buku, referensi pustaka, dan periksa ketersediaan eksemplar fisik di rak perpustakaan.' : 'Kelola katalog koleksi buku fisik, kategori, dan persediaan eksemplar.' }}
            </p>
        </div>
        @can('create', \App\Models\Book::class)
            <button
                type="button"
                id="btn-open-create-book-popup"
                class="min-h-11 inline-flex items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
            >
                Tambah Buku
            </button>
        @endcan
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('books.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 grid grid-cols-1 sm:grid-cols-12 gap-3 shadow-xs">
        <div class="sm:col-span-5">
            <label for="q" class="sr-only">Cari judul, penulis, atau ISBN</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Cari judul buku, penulis, atau ISBN..." class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
        </div>
        <div class="sm:col-span-3">
            <label for="category_id" class="sr-only">Filter Kategori</label>
            <select id="category_id" name="category_id" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label for="availability" class="sr-only">Filter Ketersediaan</label>
            <select id="availability" name="availability" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
                <option value="">Semua Stok</option>
                <option value="available" @selected($availability === 'available')>Tersedia</option>
                <option value="empty" @selected($availability === 'empty')>Habis (0)</option>
            </select>
        </div>
        <input type="hidden" name="collection_type" value="{{ $collectionType }}">
        <div class="sm:col-span-2 flex items-center gap-2">
            <button type="submit" class="flex-1 min-h-10 px-4 py-2 rounded-control bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || ($categoryId !== null && $categoryId !== '') || $availability !== '' || $collectionType !== '')
                <a href="{{ route('books.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-xs font-semibold text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- View Mode Switcher for Staff & Admin -->
    @if (!auth()->user()?->isAnggota())
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs text-text-muted font-medium">Menampilkan {{ $books->total() }} koleksi buku</p>
            <div class="inline-flex rounded-control border border-line bg-surface p-1 text-xs">
                <button type="button" id="toggle-view-grid" class="px-3 py-1 rounded-control font-semibold bg-brand-600 text-white cursor-pointer transition-colors">
                    Tampilan Kartu
                </button>
                <button type="button" id="toggle-view-table" class="px-3 py-1 rounded-control font-medium text-text-muted hover:text-ink cursor-pointer transition-colors">
                    Tampilan Tabel
                </button>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODERN BOOK CARDS GRID (Reference UI)     -->
    <!-- ========================================== -->
    <div id="books-grid-container" class="{{ $books->isEmpty() ? 'hidden' : 'block' }}">
        @if ($books->isEmpty())
            <div class="bg-surface rounded-card border border-line p-12 text-center shadow-xs">
                <p class="text-base font-semibold text-ink">Tidak ada koleksi buku yang cocok.</p>
                <p class="mt-1 text-sm text-text-muted">Coba ubah kata kunci atau bersihkan filter pencarian.</p>
                <a href="{{ route('books.index') }}" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors">
                    Bersihkan Filter
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 min-[400px]:grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6 mb-6">
                @foreach ($books as $bookItem)
                    @if ($bookItem->collection_type === 'e-book')
                        <x-book-card-ebook :book="$bookItem" :href="route('books.manage', $bookItem)" />
                    @else
                    <div class="group card-interactive bg-surface rounded-2xl border border-line p-4 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                        <div>
                            <!-- Modern Book Cover Art Thumbnail -->
                            <div class="w-full aspect-[4/5] rounded-xl bg-gradient-to-tr from-brand-800 via-brand-600 to-brand-400 text-white p-4 flex flex-col justify-between shadow-xs mb-3.5 relative overflow-hidden group-hover:scale-[1.03] transition-transform duration-300 ease-out">
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 backdrop-blur-md px-2 py-0.5 rounded w-max border border-white/20 truncate max-w-full">
                                    {{ $bookItem->category?->name ?? 'Koleksi' }}
                                </span>
                                <div>
                                    <p class="text-[11px] uppercase tracking-wider text-white/70 font-semibold mb-1">Litera</p>
                                    <p class="text-xs font-bold leading-snug line-clamp-2 text-white">{{ $bookItem->title }}</p>
                                </div>
                            </div>

                            <a href="{{ route('books.manage', $bookItem) }}" class="block">
                                <h3 class="text-sm font-bold text-ink line-clamp-1 group-hover:text-brand-600 transition-colors">
                                    {{ $bookItem->title }}
                                </h3>
                                <p class="text-xs text-text-muted truncate mt-0.5">
                                    {{ $bookItem->author }}
                                </p>
                            </a>
                        </div>

                        <div class="mt-4 pt-3 border-t border-line flex items-center justify-between gap-2">
                            @if ($bookItem->stock_available > 0)
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-200/60 truncate">
                                    Tersedia ({{ $bookItem->stock_available }})
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onclick="openCatalogBorrowModal('{{ $bookItem->id }}', '{{ addslashes($bookItem->title) }}', '{{ addslashes($bookItem->author) }}', '{{ $bookItem->stock_available }}', '{{ $bookItem->category?->name ?? 'Umum' }}', '{{ $bookItem->isbn ?: '—' }}')"
                                        class="inline-flex items-center text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 px-3 py-1.5 rounded-control transition-colors shadow-xs cursor-pointer"
                                    >
                                        {{ auth()->user()?->isAnggota() ? 'Pinjam' : 'Pinjamkan' }}
                                    </button>
                                    <a href="{{ route('books.manage', $bookItem) }}" class="inline-flex items-center text-xs font-medium text-text-muted hover:text-ink px-2 py-1.5 transition-colors">
                                        Detail
                                    </a>
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                    Habis
                                </span>
                                <a href="{{ route('books.manage', $bookItem) }}" class="inline-flex items-center text-xs font-semibold text-text-muted hover:text-ink px-2 py-1.5 transition-colors">
                                    Detail
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($books->hasPages())
                <div class="bg-surface rounded-card border border-line p-4">
                    {{ $books->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ========================================== -->
    <!-- STAFF & ADMIN TABLE VIEW (Optional View)   -->
    <!-- ========================================== -->
    @if (!auth()->user()?->isAnggota())
        <div id="books-table-container" class="hidden bg-surface rounded-card border border-line overflow-hidden shadow-xs">
            @if ($books->isEmpty())
                <div class="p-10 text-center">
                    <p class="text-base font-semibold text-ink">Tidak ada buku yang cocok dengan kriteria pencarian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                                <th scope="col" class="py-3.5 px-4 min-w-[220px]">Judul Buku</th>
                                <th scope="col" class="py-3.5 px-4">Penulis</th>
                                <th scope="col" class="py-3.5 px-4">Kategori</th>
                                <th scope="col" class="py-3.5 px-4 font-mono">ISBN</th>
                                <th scope="col" class="py-3.5 px-4 text-right">Tersedia</th>
                                <th scope="col" class="py-3.5 px-4 text-right">Total</th>
                                <th scope="col" class="py-3.5 px-4">Status</th>
                                <th scope="col" class="py-3.5 px-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach ($books as $book)
                                <tr class="hover:bg-surface-soft/50">
                                    <td class="py-3.5 px-4 font-medium text-ink">
                                        <a href="{{ route('books.manage', $book) }}" class="hover:text-brand-600 transition-colors">
                                            {{ $book->title }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-text-muted">{{ $book->author }}</td>
                                    <td class="py-3.5 px-4 text-text-muted">{{ $book->category?->name ?? '—' }}</td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-text-muted">{{ $book->isbn ?: '—' }}</td>
                                    <td class="py-3.5 px-4 text-right font-semibold tabular-nums {{ $book->stock_available > 0 ? 'text-ink' : 'text-danger-fg' }}">
                                        {{ $book->stock_available }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right tabular-nums text-text-muted">{{ $book->stock_total }}</td>
                                    <td class="py-3.5 px-4">
                                        @if ($book->stock_available > 0)
                                            <x-status-badge variant="success">Tersedia</x-status-badge>
                                        @else
                                            <x-status-badge variant="danger">Habis</x-status-badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('books.manage', $book) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                                            Detail
                                        </a>
                                        @can('update', $book)
                                            <button
                                                type="button"
                                                class="btn-edit-book text-xs font-semibold text-text-muted hover:text-ink cursor-pointer"
                                                data-book-id="{{ $book->id }}"
                                                data-book-title="{{ $book->title }}"
                                                data-book-author="{{ $book->author }}"
                                                data-book-category="{{ $book->category_id }}"
                                                data-book-collection-type="{{ $book->collection_type }}"
                                                data-book-stock="{{ $book->stock_total }}"
                                                data-book-year="{{ $book->publication_year }}"
                                                data-book-isbn="{{ $book->isbn }}"
                                                data-book-publisher="{{ $book->publisher }}"
                                                data-action-url="{{ route('books.update', $book) }}"
                                            >
                                                Edit
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
    @endif

    <!-- ======================================================== -->
    <!-- POPUP CREATE BUKU BARU (Latar Digelapin)                 -->
    <!-- ======================================================== -->
    @can('create', \App\Models\Book::class)
        <div id="create-book-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="create-book-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <!-- Darkened Backdrop -->
                <div id="create-book-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div>
                            <h3 id="create-book-title" class="text-lg font-bold text-ink">Tambah Koleksi Buku</h3>
                            <p class="text-xs text-text-muted mt-0.5">Daftarkan judul dan eksemplar buku baru ke dalam perpustakaan.</p>
                        </div>
                        <button type="button" id="btn-close-create-book" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                    <form id="create-book-form" method="POST" action="{{ route('books.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <!-- Title -->
                        <div>
                            <label for="create-title" class="block text-xs font-semibold text-ink mb-1">
                                Judul Buku <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="create-title"
                                name="title"
                                required
                                placeholder="Contoh: Pemrograman Web Modern"
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                            <p id="err-create-title" class="hidden text-xs text-rose-500 font-medium mt-1">Judul buku wajib diisi.</p>
                        </div>

                        <!-- Author & Category -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="create-author" class="block text-xs font-semibold text-ink mb-1">
                                    Penulis <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="create-author"
                                    name="author"
                                    required
                                    placeholder="Contoh: Su Rahman"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="create-category" class="block text-xs font-semibold text-ink mb-1">
                                    Kategori <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    id="create-category"
                                    name="category_id"
                                    required
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="create-collection-type" class="block text-xs font-semibold text-ink mb-1">
                                Jenis Koleksi <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="create-collection-type"
                                name="collection_type"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                                <option value="e-book">E-book</option>
                                <option value="novel">Novel</option>
                            </select>
                        </div>


                        <!-- Stock & Year -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="create-stock" class="block text-xs font-semibold text-ink mb-1">
                                    Jumlah Stok Awal <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="create-stock"
                                    name="stock_total"
                                    required
                                    min="0"
                                    value="5"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="create-year" class="block text-xs font-semibold text-ink mb-1">
                                    Tahun Terbit
                                </label>
                                <input
                                    type="number"
                                    id="create-year"
                                    name="publication_year"
                                    placeholder="{{ date('Y') }}"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                        </div>

                        <!-- ISBN & Publisher -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="create-isbn" class="block text-xs font-semibold text-ink mb-1">
                                    Nomor ISBN
                                </label>
                                <input
                                    type="text"
                                    id="create-isbn"
                                    name="isbn"
                                    placeholder="978-623-..."
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="create-publisher" class="block text-xs font-semibold text-ink mb-1">
                                    Penerbit
                                </label>
                                <input
                                    type="text"
                                    id="create-publisher"
                                    name="publisher"
                                    placeholder="Informatika"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                        </div>

                        <!-- Buttons with Validation, No Icons -->
                        <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                id="btn-cancel-create-book"
                                class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-submit-create-book"
                                class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Simpan Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- POPUP EDIT BUKU (Latar Digelapin)                        -->
        <!-- ======================================================== -->
        <div id="edit-book-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="edit-book-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <!-- Darkened Backdrop -->
                <div id="edit-book-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div>
                            <h3 id="edit-book-title" class="text-lg font-bold text-ink">Edit Koleksi Buku</h3>
                            <p class="text-xs text-text-muted mt-0.5">Perbarui data judul, penulis, stok, dan klasifikasi buku.</p>
                        </div>
                        <button type="button" id="btn-close-edit-book" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                    <form id="edit-book-form" method="POST" action="" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <label for="edit-title" class="block text-xs font-semibold text-ink mb-1">
                                Judul Buku <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit-title"
                                name="title"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                        </div>

                        <!-- Author & Category -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit-author" class="block text-xs font-semibold text-ink mb-1">
                                    Penulis <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="edit-author"
                                    name="author"
                                    required
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="edit-category" class="block text-xs font-semibold text-ink mb-1">
                                    Kategori <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    id="edit-category"
                                    name="category_id"
                                    required
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="edit-collection-type" class="block text-xs font-semibold text-ink mb-1">
                                Jenis Koleksi <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="edit-collection-type"
                                name="collection_type"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                                <option value="e-book">E-book</option>
                                <option value="novel">Novel</option>
                            </select>
                        </div>

                        <!-- Stock & Year -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit-stock" class="block text-xs font-semibold text-ink mb-1">
                                    Total Stok Eksemplar <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="edit-stock"
                                    name="stock_total"
                                    required
                                    min="0"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="edit-year" class="block text-xs font-semibold text-ink mb-1">
                                    Tahun Terbit
                                </label>
                                <input
                                    type="number"
                                    id="edit-year"
                                    name="publication_year"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                        </div>

                        <!-- ISBN & Publisher -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit-isbn" class="block text-xs font-semibold text-ink mb-1">
                                    Nomor ISBN
                                </label>
                                <input
                                    type="text"
                                    id="edit-isbn"
                                    name="isbn"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                            <div>
                                <label for="edit-publisher" class="block text-xs font-semibold text-ink mb-1">
                                    Penerbit
                                </label>
                                <input
                                    type="text"
                                    id="edit-publisher"
                                    name="publisher"
                                    class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                            </div>
                        </div>

                        <!-- Buttons with Validation, No Icons -->
                        <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                id="btn-cancel-edit-book"
                                class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-submit-edit-book"
                                class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Perbarui Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- ======================================================== -->
    <!-- POPUP KONFIRMASI PINJAM BUKU (Latar Digelapin)           -->
    <!-- ======================================================== -->
    <div id="catalog-borrow-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="catalog-modal-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <!-- Darkened Backdrop -->
            <div id="catalog-borrow-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div>
                        <h3 id="catalog-modal-title" class="text-base font-bold text-ink">Konfirmasi Peminjaman Buku</h3>
                        <p class="text-xs text-text-muted mt-0.5">Periksa rincian peminjaman sebelum konfirmasi.</p>
                    </div>
                    <button type="button" id="btn-close-catalog-modal" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>

                <!-- Book Preview in Popup -->
                <div class="mt-4 p-4 rounded-xl bg-surface-soft border border-line space-y-2 text-xs">
                    <div>
                        <span class="text-text-muted">Judul Koleksi:</span>
                        <p id="cat-modal-title" class="font-bold text-ink text-sm mt-0.5">-</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-line">
                        <div>
                            <span class="text-text-muted">Penulis:</span>
                            <p id="cat-modal-author" class="font-medium text-ink">-</p>
                        </div>
                        <div>
                            <span class="text-text-muted">Kategori:</span>
                            <p id="cat-modal-category" class="font-medium text-ink">-</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-line">
                        <div>
                            <span class="text-text-muted">ISBN:</span>
                            <p id="cat-modal-isbn" class="font-mono text-ink">-</p>
                        </div>
                        <div>
                            <span class="text-text-muted">Stok Tersedia:</span>
                            <p id="cat-modal-stock" class="font-semibold text-emerald-600 dark:text-emerald-400">-</p>
                        </div>
                    </div>
                </div>

                <!-- Form Submit with Loan Duration Options -->
                <form id="catalog-borrow-form" method="POST" action="" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                    <!-- Loan Terms & Duration Option -->
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-line">
                            <span class="text-text-muted">Peminjam</span>
                            <span class="font-semibold text-ink">{{ auth()->user()->name }}</span>
                        </div>

                        <div>
                            <label for="cat-duration-select" class="block text-xs font-semibold text-ink mb-1.5">
                                Pilihan Waktu Peminjaman <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="cat-duration-select"
                                name="duration_days"
                                class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-xs bg-surface text-ink font-medium focus:outline-2 focus:outline-focus cursor-pointer"
                            >
                                <option value="3">3 Hari (Peminjaman Singkat)</option>
                                <option value="7" selected>7 Hari (1 Minggu — Standar)</option>
                                <option value="14">14 Hari (2 Minggu)</option>
                                <option value="21">21 Hari (3 Minggu)</option>
                                <option value="30">30 Hari (1 Bulan)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between py-2 px-3 rounded-xl bg-surface-soft border border-line">
                            <span class="text-text-muted">Batas Pengembalian:</span>
                            <span id="cat-due-date-preview" class="font-bold text-brand-600 tabular-nums">{{ \Carbon\Carbon::now('Asia/Jakarta')->addDays(7)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-line flex items-center justify-end gap-3">
                        <button
                            type="button"
                            id="btn-cancel-catalog-modal"
                            class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="btn-submit-catalog-modal"
                            class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                        >
                            Konfirmasi Pinjam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // View Mode Switcher
            const gridBtn = document.getElementById('toggle-view-grid');
            const tableBtn = document.getElementById('toggle-view-table');
            const gridContainer = document.getElementById('books-grid-container');
            const tableContainer = document.getElementById('books-table-container');

            if (gridBtn && tableBtn && gridContainer && tableContainer) {
                gridBtn.addEventListener('click', function () {
                    gridContainer.classList.remove('hidden');
                    tableContainer.classList.add('hidden');
                    gridBtn.classList.add('bg-brand-600', 'text-white');
                    gridBtn.classList.remove('text-text-muted');
                    tableBtn.classList.remove('bg-brand-600', 'text-white');
                    tableBtn.classList.add('text-text-muted');
                });

                tableBtn.addEventListener('click', function () {
                    gridContainer.classList.add('hidden');
                    tableContainer.classList.remove('hidden');
                    tableBtn.classList.add('bg-brand-600', 'text-white');
                    tableBtn.classList.remove('text-text-muted');
                    gridBtn.classList.remove('bg-brand-600', 'text-white');
                    gridBtn.classList.add('text-text-muted');
                });
            }

            // Create Book Popup
            const openCreateBookBtn = document.getElementById('btn-open-create-book-popup');
            const createBookModal = document.getElementById('create-book-popup');
            const closeCreateBookBtn = document.getElementById('btn-close-create-book');
            const cancelCreateBookBtn = document.getElementById('btn-cancel-create-book');
            const createBookBackdrop = document.getElementById('create-book-backdrop');
            const createBookForm = document.getElementById('create-book-form');
            const submitCreateBookBtn = document.getElementById('btn-submit-create-book');

            window.openCreateBook = function () {
                const modal = document.getElementById('create-book-popup');
                if (modal) modal.classList.remove('hidden');
            };

            window.closeCreateBook = function () {
                const modal = document.getElementById('create-book-popup');
                if (modal) modal.classList.add('hidden');
            };

            if (openCreateBookBtn) openCreateBookBtn.addEventListener('click', window.openCreateBook);
            if (closeCreateBookBtn) closeCreateBookBtn.addEventListener('click', window.closeCreateBook);
            if (cancelCreateBookBtn) cancelCreateBookBtn.addEventListener('click', window.closeCreateBook);
            if (createBookBackdrop) createBookBackdrop.addEventListener('click', window.closeCreateBook);

            if (createBookForm && submitCreateBookBtn) {
                createBookForm.addEventListener('submit', function (e) {
                    const title = document.getElementById('create-title');
                    const err = document.getElementById('err-create-title');

                    if (title && !title.value.trim()) {
                        e.preventDefault();
                        if (err) err.classList.remove('hidden');
                        title.classList.add('border-rose-500');
                        title.focus();
                        return;
                    }

                    submitCreateBookBtn.disabled = true;
                    submitCreateBookBtn.textContent = 'Menyimpan Buku...';
                    submitCreateBookBtn.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });

        // Helper to format Indonesian Date
        function formatIndoDate(daysToAdd) {
            const d = new Date();
            d.setDate(d.getDate() + parseInt(daysToAdd, 10));
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        }

        // Catalog Borrow Modal
        window.openCatalogBorrowModal = function (bookId, title, author, stock, category, isbn) {
            const titleEl = document.getElementById('cat-modal-title');
            const authorEl = document.getElementById('cat-modal-author');
            const catEl = document.getElementById('cat-modal-category');
            const isbnEl = document.getElementById('cat-modal-isbn');
            const stockEl = document.getElementById('cat-modal-stock');
            const modal = document.getElementById('catalog-borrow-modal');
            const form = document.getElementById('catalog-borrow-form');
            const durationSelect = document.getElementById('cat-duration-select');
            const duePreview = document.getElementById('cat-due-date-preview');

            if (titleEl) titleEl.textContent = title;
            if (authorEl) authorEl.textContent = author;
            if (catEl) catEl.textContent = category;
            if (isbnEl) isbnEl.textContent = isbn;
            if (stockEl) stockEl.textContent = stock + ' eksemplar';

            if (durationSelect && duePreview) {
                durationSelect.value = '7';
                duePreview.textContent = formatIndoDate(7);
            }

            if (form) {
                form.action = '{{ url("/books") }}/' + bookId + '/borrow';
            }

            if (modal) modal.classList.remove('hidden');
        };

        const catDurationSelect = document.getElementById('cat-duration-select');
        const catDuePreview = document.getElementById('cat-due-date-preview');
        if (catDurationSelect && catDuePreview) {
            catDurationSelect.addEventListener('change', function () {
                catDuePreview.textContent = formatIndoDate(this.value);
            });
        }

        window.closeCatalogBorrowModal = function () {
            const modal = document.getElementById('catalog-borrow-modal');
            if (modal) modal.classList.add('hidden');
        };

        const closeCatBtn = document.getElementById('btn-close-catalog-modal');
        const cancelCatBtn = document.getElementById('btn-cancel-catalog-modal');
        const catalogBackdrop = document.getElementById('catalog-borrow-backdrop');
        const catForm = document.getElementById('catalog-borrow-form');
        const submitCatBtn = document.getElementById('btn-submit-catalog-modal');

        if (closeCatBtn) closeCatBtn.addEventListener('click', window.closeCatalogBorrowModal);
        if (cancelCatBtn) cancelCatBtn.addEventListener('click', window.closeCatalogBorrowModal);
        if (catalogBackdrop) catalogBackdrop.addEventListener('click', window.closeCatalogBorrowModal);

        // Edit Book Modal Controls
        const editBookModal = document.getElementById('edit-book-popup');
        const closeEditBookBtn = document.getElementById('btn-close-edit-book');
        const cancelEditBookBtn = document.getElementById('btn-cancel-edit-book');
        const editBookBackdrop = document.getElementById('edit-book-backdrop');
        const editBookForm = document.getElementById('edit-book-form');
        const editTitle = document.getElementById('edit-title');
        const editAuthor = document.getElementById('edit-author');
        const editCategory = document.getElementById('edit-category');
        const editCollectionType = document.getElementById('edit-collection-type');
        const editStock = document.getElementById('edit-stock');
        const editYear = document.getElementById('edit-year');
        const editIsbn = document.getElementById('edit-isbn');
        const editPublisher = document.getElementById('edit-publisher');

        window.closeEditBook = function () {
            if (editBookModal) editBookModal.classList.add('hidden');
        };

        document.querySelectorAll('.btn-edit-book').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!editBookModal) return;
                editBookForm.action = btn.getAttribute('data-action-url');
                editTitle.value = btn.getAttribute('data-book-title') || '';
                editAuthor.value = btn.getAttribute('data-book-author') || '';
                editCategory.value = btn.getAttribute('data-book-category') || '';
                if (editCollectionType) {
                    const collectionType = btn.getAttribute('data-book-collection-type');
                    editCollectionType.value = collectionType === 'novel' ? 'novel' : 'e-book';
                }
                editStock.value = btn.getAttribute('data-book-stock') || '';
                editYear.value = btn.getAttribute('data-book-year') || '';
                editIsbn.value = btn.getAttribute('data-book-isbn') || '';
                editPublisher.value = btn.getAttribute('data-book-publisher') || '';
                editBookModal.classList.remove('hidden');
            });
        });

        if (closeEditBookBtn) closeEditBookBtn.addEventListener('click', window.closeEditBook);
        if (cancelEditBookBtn) cancelEditBookBtn.addEventListener('click', window.closeEditBook);
        if (editBookBackdrop) editBookBackdrop.addEventListener('click', window.closeEditBook);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeCatalogBorrowModal();
                window.closeCreateBook();
                window.closeEditBook();
            }
        });

        if (catForm && submitCatBtn) {
            catForm.addEventListener('submit', function () {
                submitCatBtn.disabled = true;
                submitCatBtn.textContent = 'Memproses Peminjaman...';
                submitCatBtn.classList.add('opacity-75', 'cursor-not-allowed');
            });
        }
    </script>
    @endpush
</x-layouts.app>

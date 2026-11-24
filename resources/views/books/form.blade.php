<x-layouts.app :title="$isEdit ? 'Edit Buku — ' . $book->title : 'Tambah Buku'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.index') }}" class="hover:text-ink">Data Buku</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Buku' : 'Tambah Buku' }}</span>
    </x-slot:breadcrumbs>

    @php
        $shelves = $shelves ?? \App\Models\Shelf::orderBy('name')->get();
        $publishers = $publishers ?? \App\Models\Publisher::orderBy('name')->get();
        $currentType = old('collection_type', $book->collection_type ?? 'physical');
        $currentStatus = old('status', $book->status ?? 'active');
        $totalCopies = (int) old('total_copies', old('stock_total', $book->total_copies ?? $book->stock_total ?? 1));
        $availableCopies = (int) ($book->available_copies ?? $book->stock_available ?? $totalCopies);
        $borrowedCopies = (int) ($book->borrowed_copies ?? $book->stock_on_loan ?? 0);
        $reservedCopies = (int) ($book->reserved_copies ?? 0);
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
            {{ $isEdit ? 'Edit Koleksi Buku' : 'Tambah Buku Baru' }}
        </h1>
        <p class="mt-1 text-sm text-text-muted">
            {{ $isEdit ? 'Perbarui informasi katalog, jenis koleksi, lokasi rak, atau tautan digital.' : 'Kelola penambahan koleksi buku fisik atau pustaka digital (e-book) ke dalam sistem katalog Litera.' }}
        </p>
    </div>

    <!-- 08. CREATE & EDIT BOOK — SPLIT LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <!-- ================================================= -->
        <!-- LEFT PANEL: COVER & PREVIEW (30% - 35% on Desktop)-->
        <!-- ================================================= -->
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-24">
            <div class="bg-surface rounded-card border border-line p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-line pb-3">
                    <h2 class="text-base font-bold text-ink flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Cover Buku</span>
                    </h2>
                    <span id="preview-type-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400 border border-brand-200 dark:border-brand-500/30">
                        {{ $currentType === 'physical' ? 'Buku Fisik' : ($currentType === 'novel' ? 'Novel' : 'E-book') }}
                    </span>
                </div>

                <!-- Live Cover Preview Box -->
                <div class="flex flex-col items-center">
                    <div id="cover-preview-wrapper" class="w-full max-w-[220px] aspect-[3/4] rounded-xl border-2 border-dashed border-line bg-surface-soft/60 relative overflow-hidden shadow-md flex items-center justify-center transition-all duration-200">
                        @if ($book->cover_path)
                            <img id="cover-preview-img" src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                            <div id="cover-preview-placeholder" class="hidden text-center p-4">
                                <svg class="w-12 h-12 mx-auto text-text-muted mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="text-xs text-text-muted">Pilih cover buku</span>
                            </div>
                        @else
                            <img id="cover-preview-img" src="" alt="Pratinjau Cover" class="w-full h-full object-cover hidden">
                            <div id="cover-preview-placeholder" class="text-center p-4">
                                <div class="w-12 h-12 mx-auto rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-2">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-ink">Belum Ada Sampul</p>
                                <p class="text-[11px] text-text-muted mt-0.5">Unggah gambar JPG, PNG, atau WEBP</p>
                            </div>
                        @endif
                    </div>

                    <button type="button" id="btn-trigger-cover-upload" class="mt-3.5 inline-flex items-center gap-1.5 px-4 py-2 rounded-control border border-line bg-surface text-xs font-semibold text-ink hover:bg-surface-soft hover:border-brand-500/40 transition-colors shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>{{ $book->cover_path ? 'Ganti Sampul Buku' : 'Pilih Sampul Buku' }}</span>
                    </button>
                    <p class="mt-1.5 text-[11px] text-text-muted text-center">Rasio 3:4 disarankan. Maks. 2MB.</p>
                </div>

                <!-- Status Badge Preview -->
                <div class="pt-3 border-t border-line flex items-center justify-between text-xs">
                    <span class="text-text-muted font-medium">Status Katalog:</span>
                    <span id="preview-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold {{ $currentStatus === 'active' ? 'bg-success-bg text-success-fg border border-success-fg/20' : 'bg-surface-soft text-text-muted border border-line' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $currentStatus === 'active' ? 'bg-success-fg' : 'bg-text-muted' }}"></span>
                        <span id="preview-status-text">{{ $currentStatus === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                    </span>
                </div>

                <!-- Physical Stock Summary (Only if Physical) -->
                <div id="preview-stock-panel" class="{{ $currentType === 'physical' ? 'block' : 'hidden' }} pt-3 border-t border-line space-y-2">
                    <h3 class="text-xs font-bold text-ink uppercase tracking-wider">Informasi Inventaris Fisik</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-control bg-surface-soft border border-line">
                            <span class="block text-text-muted text-[11px]">Tersedia</span>
                            <span id="preview-stock-available" class="text-sm font-bold text-ink tabular-nums">{{ $availableCopies }}</span> eks
                        </div>
                        <div class="p-2.5 rounded-control bg-surface-soft border border-line">
                            <span class="block text-text-muted text-[11px]">Total Koleksi</span>
                            <span id="preview-stock-total" class="text-sm font-bold text-ink tabular-nums">{{ $totalCopies }}</span> eks
                        </div>
                        <div class="p-2.5 rounded-control bg-surface-soft border border-line">
                            <span class="block text-text-muted text-[11px]">Dipinjam</span>
                            <span id="preview-stock-borrowed" class="text-sm font-bold text-ink tabular-nums">{{ $borrowedCopies }}</span> eks
                        </div>
                        <div class="p-2.5 rounded-control bg-surface-soft border border-line">
                            <span class="block text-text-muted text-[11px]">Direservasi</span>
                            <span id="preview-stock-reserved" class="text-sm font-bold text-ink tabular-nums">{{ $reservedCopies }}</span> eks
                        </div>
                    </div>
                </div>

                <!-- Digital Format Indicator (Only if E-book / Novel) -->
                <div id="preview-digital-panel" class="{{ $currentType !== 'physical' ? 'block' : 'hidden' }} pt-3 border-t border-line">
                    <div class="p-3 rounded-control bg-brand-50/60 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-500/20 text-xs">
                        <div class="flex items-center gap-2 text-brand-700 dark:text-brand-400 font-semibold mb-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Koleksi Digital</span>
                        </div>
                        <p class="text-[11px] text-text-muted leading-relaxed">
                            E-book diakses secara daring atau diunduh via tautan valid. Tidak memerlukan rak buku fisik atau stok eksemplar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================= -->
        <!-- RIGHT PANEL: REUSABLE BOOK FORM (65% - 70%)       -->
        <!-- ================================================= -->
        <div class="lg:col-span-8">
            <form method="POST" action="{{ $isEdit ? route('books.update', $book) : route('books.store') }}" enctype="multipart/form-data" class="bg-surface rounded-card border border-line p-6 sm:p-7 shadow-xs space-y-6" novalidate>
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <!-- Hidden file input triggered by preview button -->
                <input
                    id="cover"
                    name="cover"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="sr-only"
                >

                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div>
                        <h2 class="text-lg font-bold text-ink">Informasi Bibliografi</h2>
                        <p class="text-xs text-text-muted mt-0.5">Lengkapi data buku dengan akurat sesuai format perpustakaan.</p>
                    </div>
                    <span class="text-xs text-text-muted">Tanda <span class="text-danger-fg font-bold">*</span> wajib diisi</span>
                </div>

                <!-- Common Field: Judul Buku -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-ink mb-1.5">
                        Judul Buku <span class="text-danger-fg" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        required
                        value="{{ old('title', $book->title) }}"
                        aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}"
                        placeholder="Contoh: Pengantar Sejarah Nusantara"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('title') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('title')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Common Field: Jenis Koleksi -->
                    <div>
                        <label for="collection_type" class="block text-sm font-semibold text-ink mb-1.5">
                            Jenis Koleksi <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="collection_type"
                            name="collection_type"
                            required
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('collection_type') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus cursor-pointer"
                        >
                            <option value="physical" @selected($currentType === 'physical')>Buku Fisik (Inventaris Perpustakaan)</option>
                            <option value="e-book" @selected($currentType === 'e-book')>E-book (Pustaka Digital)</option>
                            <option value="novel" @selected($currentType === 'novel')>Novel Digital</option>
                        </select>
                        @error('collection_type')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: Kategori -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-ink mb-1.5">
                            Kategori <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="category_id"
                            name="category_id"
                            required
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('category_id') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus cursor-pointer"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) old('category_id', $book->category_id) === (string) $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: Penulis -->
                    <div>
                        <label for="author" class="block text-sm font-semibold text-ink mb-1.5">
                            Penulis <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="author"
                            name="author"
                            type="text"
                            required
                            value="{{ old('author', $book->author) }}"
                            placeholder="Nama penulis atau pengarang"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('author') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                        @error('author')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: ISBN (Opsional) -->
                    <div>
                        <label for="isbn" class="block text-sm font-semibold text-ink mb-1.5">
                            ISBN <span class="text-xs text-text-muted font-normal">(Opsional)</span>
                        </label>
                        <input
                            id="isbn"
                            name="isbn"
                            type="text"
                            value="{{ old('isbn', $book->isbn) }}"
                            placeholder="Contoh: 978-602-0000-00-0"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('isbn') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm font-mono focus:outline-2 focus:outline-focus"
                        >
                        @error('isbn')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: Penerbit (Opsional) -->
                    <div>
                        <label for="publisher_id" class="block text-sm font-semibold text-ink mb-1.5">
                            Penerbit <span class="text-xs text-text-muted font-normal">(Opsional)</span>
                        </label>
                        <select
                            id="publisher_id"
                            name="publisher_id"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('publisher_id') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus cursor-pointer"
                        >
                            <option value="">-- Pilih Penerbit Terdaftar --</option>
                            @foreach ($publishers as $pub)
                                <option value="{{ $pub->id }}" @selected((string) old('publisher_id', $book->publisher_id) === (string) $pub->id)>
                                    {{ $pub->name }}
                                </option>
                            @endforeach
                        </select>
                        <input
                            type="hidden"
                            name="publisher"
                            id="publisher_fallback"
                            value="{{ old('publisher', $book->publisher) }}"
                        >
                        @error('publisher_id')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: Tahun Terbit (Opsional) -->
                    <div>
                        <label for="publication_year" class="block text-sm font-semibold text-ink mb-1.5">
                            Tahun Terbit <span class="text-xs text-text-muted font-normal">(Opsional)</span>
                        </label>
                        <input
                            id="publication_year"
                            name="publication_year"
                            type="number"
                            min="1000"
                            max="2100"
                            value="{{ old('publication_year', $book->publication_year) }}"
                            placeholder="Contoh: 2024"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('publication_year') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                        >
                        @error('publication_year')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Common Field: Status Aktif / Nonaktif -->
                    <div class="sm:col-span-2">
                        <label for="status" class="block text-sm font-semibold text-ink mb-1.5">
                            Status Publikasi Katalog <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full sm:w-64 min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('status') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus cursor-pointer"
                        >
                            <option value="active" @selected($currentStatus === 'active')>Aktif (Tampil di Katalog)</option>
                            <option value="inactive" @selected($currentStatus === 'inactive')>Nonaktif (Disembunyikan)</option>
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Common Field: Sinopsis / Deskripsi Buku -->
                <div>
                    <label for="synopsis" class="block text-sm font-semibold text-ink mb-1.5">
                        Sinopsis / Ringkasan Buku
                    </label>
                    <textarea
                        id="synopsis"
                        name="synopsis"
                        rows="5"
                        placeholder="Tuliskan sinopsis atau gambaran isi buku ini untuk informasi pembaca..."
                        class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('synopsis') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus leading-relaxed"
                    >{{ old('synopsis', $book->synopsis ?: $book->description) }}</textarea>
                    <input type="hidden" name="description" id="description_mirror" value="{{ old('description', $book->description ?: $book->synopsis) }}">
                    @error('synopsis')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ================================================= -->
                <!-- DYNAMIC SECTION: PHYSICAL BOOK INVENTORY          -->
                <!-- ================================================= -->
                <div id="section-physical-book" class="{{ $currentType === 'physical' ? 'block' : 'hidden' }} pt-5 border-t border-line space-y-5">
                    <div>
                        <h3 class="text-base font-bold text-ink">Inventaris &amp; Lokasi Fisik</h3>
                        <p class="text-xs text-text-muted mt-0.5">Tentukan rak penyimpanan dan stok eksemplar buku di perpustakaan.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Rak Buku -->
                        <div>
                            <label for="shelf_id" class="block text-sm font-semibold text-ink mb-1.5">
                                Rak Buku <span class="text-danger-fg" aria-hidden="true">*</span>
                            </label>
                            <select
                                id="shelf_id"
                                name="shelf_id"
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('shelf_id') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus cursor-pointer"
                            >
                                <option value="">-- Pilih Rak Buku --</option>
                                @foreach ($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" @selected((string) old('shelf_id', $book->shelf_id) === (string) $shelf->id)>
                                        {{ $shelf->name }} ({{ $shelf->code }}){{ filled($shelf->location) ? ' - ' . $shelf->location : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shelf_id')
                                <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total Copies / Jumlah Eksemplar -->
                        <div>
                            <label for="total_copies" class="block text-sm font-semibold text-ink mb-1.5">
                                Jumlah Eksemplar (Total) <span class="text-danger-fg" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="total_copies"
                                name="total_copies"
                                type="number"
                                min="{{ $isEdit ? $borrowedCopies : 0 }}"
                                step="1"
                                value="{{ $totalCopies }}"
                                placeholder="1"
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('total_copies') || $errors->has('stock_total') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                            >
                            {{-- Sync legacy stock_total field for backend controller compatibility --}}
                            <input type="hidden" name="stock_total" id="stock_total_sync" value="{{ $totalCopies }}">

                            <p class="mt-1 text-xs text-text-muted">
                                @if ($isEdit)
                                    Sedang dipinjam: <strong class="text-ink">{{ $borrowedCopies }} eksemplar</strong>. Total eksemplar tidak boleh lebih kecil dari jumlah pinjaman aktif.
                                @else
                                    Buku baru akan memiliki stok tersedia sama dengan jumlah total eksemplar.
                                @endif
                            </p>
                            @error('total_copies')
                                <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                            @enderror
                            @error('stock_total')
                                <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- DYNAMIC SECTION: E-BOOK & DIGITAL NOVEL LINKS     -->
                <!-- ================================================= -->
                <div id="section-ebook-links" class="{{ $currentType !== 'physical' ? 'block' : 'hidden' }} pt-5 border-t border-line space-y-5">
                    <div>
                        <h3 class="text-base font-bold text-ink">Akses Digital E-book</h3>
                        <p class="text-xs text-text-muted mt-0.5">Sediakan minimal satu tautan valid (HTTP/HTTPS) untuk membaca online atau mengunduh berkas.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Link Baca Online -->
                        <div>
                            <label for="read_url" class="block text-sm font-semibold text-ink mb-1.5">
                                Link Baca Online
                            </label>
                            <input
                                id="read_url"
                                name="read_url"
                                type="url"
                                value="{{ old('read_url', $book->read_url) }}"
                                placeholder="https://example.com/baca/buku-ini"
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('read_url') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                            >
                            <p class="mt-1 text-[11px] text-text-muted">Tautan pembaca digital eksternal atau viewer online.</p>
                            @error('read_url')
                                <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Link Download File -->
                        <div>
                            <label for="download_url" class="block text-sm font-semibold text-ink mb-1.5">
                                Link Unduh File (PDF/EPUB)
                            </label>
                            <input
                                id="download_url"
                                name="download_url"
                                type="url"
                                value="{{ old('download_url', $book->download_url) }}"
                                placeholder="https://example.com/download/file.pdf"
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('download_url') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                            >
                            <p class="mt-1 text-[11px] text-text-muted">Tautan langsung bagi anggota untuk mengunduh koleksi.</p>
                            @error('download_url')
                                <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="pt-5 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <a href="{{ route('books.index') }}" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="min-h-11 inline-flex items-center justify-center px-6 py-2.5 rounded-control bg-brand-600 hover:bg-brand-700 text-sm font-semibold text-white shadow-xs transition-colors cursor-pointer">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Buku ke Katalog' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const collectionTypeSelect = document.getElementById('collection_type');
            const sectionPhysical = document.getElementById('section-physical-book');
            const sectionEbook = document.getElementById('section-ebook-links');
            const previewTypeBadge = document.getElementById('preview-type-badge');
            const previewStockPanel = document.getElementById('preview-stock-panel');
            const previewDigitalPanel = document.getElementById('preview-digital-panel');

            const statusSelect = document.getElementById('status');
            const previewStatusText = document.getElementById('preview-status-text');
            const previewStatusBadge = document.getElementById('preview-status-badge');

            const totalCopiesInput = document.getElementById('total_copies');
            const stockTotalSync = document.getElementById('stock_total_sync');
            const previewStockTotal = document.getElementById('preview-stock-total');
            const previewStockAvailable = document.getElementById('preview-stock-available');

            const synopsisTextarea = document.getElementById('synopsis');
            const descriptionMirror = document.getElementById('description_mirror');

            const publisherSelect = document.getElementById('publisher_id');
            const publisherFallback = document.getElementById('publisher_fallback');

            // Cover upload trigger & preview
            const coverInput = document.getElementById('cover');
            const uploadTriggerBtn = document.getElementById('btn-trigger-cover-upload');
            const previewImg = document.getElementById('cover-preview-img');
            const previewPlaceholder = document.getElementById('cover-preview-placeholder');

            if (uploadTriggerBtn && coverInput) {
                uploadTriggerBtn.addEventListener('click', () => coverInput.click());
            }

            if (coverInput) {
                coverInput.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (previewImg) {
                                previewImg.src = e.target.result;
                                previewImg.classList.remove('hidden');
                            }
                            if (previewPlaceholder) {
                                previewPlaceholder.classList.add('hidden');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Sync collection type changes
            function updateCollectionType() {
                const type = collectionTypeSelect.value;
                const isPhysical = type === 'physical';

                if (sectionPhysical) sectionPhysical.classList.toggle('hidden', !isPhysical);
                if (sectionEbook) sectionEbook.classList.toggle('hidden', isPhysical);
                if (previewStockPanel) previewStockPanel.classList.toggle('hidden', !isPhysical);
                if (previewDigitalPanel) previewDigitalPanel.classList.toggle('hidden', isPhysical);

                if (previewTypeBadge) {
                    if (isPhysical) {
                        previewTypeBadge.textContent = 'Buku Fisik';
                    } else if (type === 'novel') {
                        previewTypeBadge.textContent = 'Novel';
                    } else {
                        previewTypeBadge.textContent = 'E-book';
                    }
                }
            }

            if (collectionTypeSelect) {
                collectionTypeSelect.addEventListener('change', updateCollectionType);
            }

            // Status select preview sync
            if (statusSelect && previewStatusText && previewStatusBadge) {
                statusSelect.addEventListener('change', function () {
                    const isActive = this.value === 'active';
                    previewStatusText.textContent = isActive ? 'Aktif' : 'Nonaktif';
                    if (isActive) {
                        previewStatusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold bg-success-bg text-success-fg border border-success-fg/20';
                    } else {
                        previewStatusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold bg-surface-soft text-text-muted border border-line';
                    }
                });
            }

            // Stock input live updates
            if (totalCopiesInput) {
                totalCopiesInput.addEventListener('input', function () {
                    const val = parseInt(this.value, 10) || 0;
                    if (stockTotalSync) stockTotalSync.value = val;
                    if (previewStockTotal) previewStockTotal.textContent = val;
                    // On create, available copies matches total copies
                    @if (!$isEdit)
                        if (previewStockAvailable) previewStockAvailable.textContent = val;
                    @endif
                });
            }

            // Synopsis & description mirror
            if (synopsisTextarea && descriptionMirror) {
                synopsisTextarea.addEventListener('input', function () {
                    descriptionMirror.value = this.value;
                });
            }

            // Publisher fallback mirror
            if (publisherSelect && publisherFallback) {
                publisherSelect.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.value) {
                        publisherFallback.value = opt.text;
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

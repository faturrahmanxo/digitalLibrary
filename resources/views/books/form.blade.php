<x-layouts.app :title="$isEdit ? 'Edit Buku' : 'Tambah Buku'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.index') }}" class="hover:text-ink">Buku</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Buku' : 'Tambah Buku' }}</span>
    </x-slot:breadcrumbs>

    <!-- SCR-009: Book Create / Edit -->
    <div class="max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Buku' : 'Tambah Buku' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui informasi katalog dan inventaris buku.' : 'Tambahkan judul koleksi beserta informasi katalog dan inventarisnya.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('books.update', $book) : route('books.store') }}" enctype="multipart/form-data" class="bg-surface rounded-card border border-line p-6 space-y-6" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <p class="text-xs text-text-muted">Bidang dengan tanda <span class="text-danger-fg font-semibold">*</span> wajib diisi.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
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
                        @if($errors->has('title')) aria-describedby="title-error" @endif
                        placeholder="Contoh: Pengantar Sejarah Nusantara"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('title') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('title')
                        <p id="title-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

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
                        aria-invalid="{{ $errors->has('author') ? 'true' : 'false' }}"
                        @if($errors->has('author')) aria-describedby="author-error" @endif
                        placeholder="Nama penulis buku"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('author') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('author')
                        <p id="author-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-semibold text-ink mb-1.5">
                        Kategori <span class="text-danger-fg" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}"
                        @if($errors->has('category_id')) aria-describedby="category-error" @endif
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('category_id') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) old('category_id', $book->category_id) === (string) $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p id="category-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="collection_type" class="block text-sm font-semibold text-ink mb-1.5">
                        Jenis Koleksi <span class="text-danger-fg" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="collection_type"
                        name="collection_type"
                        required
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('collection_type') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                        <option value="e-book" @selected(old('collection_type', $book->collection_type ?? 'e-book') === 'e-book')>E-book</option>
                        <option value="novel" @selected(old('collection_type', $book->collection_type) === 'novel')>Novel</option>
                    </select>
                    @error('collection_type')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="isbn" class="block text-sm font-semibold text-ink mb-1.5">
                        ISBN (Opsional)
                    </label>
                    <input
                        id="isbn"
                        name="isbn"
                        type="text"
                        value="{{ old('isbn', $book->isbn) }}"
                        placeholder="978-602-XXXX-XX-X"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm font-mono focus:outline-2 focus:outline-focus"
                    >
                    @error('isbn')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="publisher" class="block text-sm font-semibold text-ink mb-1.5">
                        Penerbit (Opsional)
                    </label>
                    <input
                        id="publisher"
                        name="publisher"
                        type="text"
                        value="{{ old('publisher', $book->publisher) }}"
                        placeholder="Nama penerbit"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('publisher')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="publication_year" class="block text-sm font-semibold text-ink mb-1.5">
                        Tahun Terbit (Opsional)
                    </label>
                    <input
                        id="publication_year"
                        name="publication_year"
                        type="number"
                        min="1000"
                        max="2100"
                        value="{{ old('publication_year', $book->publication_year) }}"
                        placeholder="2025"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                    >
                    @error('publication_year')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div id="ebook-description-field" class="sm:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-ink mb-1.5">Deskripsi E-book</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('description') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >{{ old('description', $book->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div id="novel-synopsis-field" class="sm:col-span-2 hidden">
                    <label for="synopsis" class="block text-sm font-semibold text-ink mb-1.5">Sinopsis Novel</label>
                    <textarea
                        id="synopsis"
                        name="synopsis"
                        rows="4"
                        class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('synopsis') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >{{ old('synopsis', $book->synopsis) }}</textarea>
                    @error('synopsis')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="download_url" class="block text-sm font-semibold text-ink mb-1.5">
                        Link Download File (Opsional)
                    </label>
                    <input
                        id="download_url"
                        name="download_url"
                        type="url"
                        value="{{ old('download_url', $book->download_url) }}"
                        placeholder="Contoh: https://drive.google.com/... atau tautan file langsung"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('download_url') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    <p class="mt-1 text-xs text-text-muted">Tautan bagi pembaca/anggota untuk mendownload file novel atau buku ini.</p>
                    @error('download_url')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cover" class="block text-sm font-semibold text-ink mb-1.5">
                        Sampul Buku (Opsional)
                    </label>
                    <input
                        id="cover"
                        name="cover"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="w-full text-xs text-text-muted file:mr-3 file:py-2 file:px-3 file:rounded-control file:border file:border-line file:text-xs file:font-semibold file:bg-surface-soft file:text-ink hover:file:bg-line/40"
                    >
                    @error('cover')
                        <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Existing physical inventory remains separate from digital licenses. -->
            <div class="pt-4 border-t border-line">
                <h2 class="text-base font-semibold text-ink mb-3">Inventaris Fisik Existing</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="stock_total" class="block text-sm font-semibold text-ink mb-1.5">
                            Stok Total (Eksemplar) <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="stock_total"
                            name="stock_total"
                            type="number"
                            min="0"
                            step="1"
                            required
                            value="{{ old('stock_total', $book->stock_total) }}"
                            aria-describedby="stock-help @error('stock_total') stock-error @enderror"
                            aria-invalid="{{ $errors->has('stock_total') ? 'true' : 'false' }}"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('stock_total') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                        >
                        <p id="stock-help" class="mt-1 text-xs text-text-muted">
                            @if ($isEdit)
                                Mengubah stok total akan menyesuaikan stok tersedia sebesar selisihnya. Stok total tidak boleh lebih kecil dari jumlah yang sedang dipinjam ({{ $book->stock_on_loan }} eksemplar).
                            @else
                                Saat buku baru dibuat, stok tersedia otomatis disamakan dengan stok total.
                            @endif
                        </p>
                        @error('stock_total')
                            <p id="stock-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($isEdit)
                        <div class="rounded-control bg-surface-soft border border-line p-4 text-sm">
                            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Stok Tersedia Saat Ini (Baca-Saja)</p>
                            <p class="mt-1 text-xl font-bold text-ink tabular-nums">{{ $book->stock_available }} eksemplar</p>
                            <p class="mt-1 text-xs text-text-muted">
                                Sedang dipinjam: <span class="font-semibold text-ink">{{ $book->stock_on_loan }} eksemplar</span>. Stok tersedia dihitung otomatis oleh server.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <section id="ebook-digital-settings" class="pt-4 border-t border-line">
                <h2 class="text-base font-semibold text-ink mb-1">Lisensi Digital E-book</h2>
                <p class="mb-4 text-xs leading-relaxed text-text-muted">
                    Lisensi mengatur peminjaman digital 7 hari dan terpisah dari inventaris fisik. Izin distribusi nonaktif sampai dikonfirmasi petugas.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="digital_license_count" class="block text-sm font-semibold text-ink mb-1.5">
                            Jumlah Lisensi Digital
                        </label>
                        <input
                            id="digital_license_count"
                            name="digital_license_count"
                            type="number"
                            min="0"
                            max="1000000"
                            step="1"
                            value="{{ old('digital_license_count', $book->digital_license_count ?? 0) }}"
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('digital_license_count') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                        >
                        @error('digital_license_count')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                        @if ($isEdit)
                            <p class="mt-1 text-xs text-text-muted">
                                Akses tersedia: {{ $digitalAvailable }} dari {{ $book->digital_license_count }} lisensi.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="digital_file" class="block text-sm font-semibold text-ink mb-1.5">File PDF / EPUB</label>
                        <input
                            id="digital_file"
                            name="digital_file"
                            type="file"
                            accept=".pdf,.epub,application/pdf,application/epub+zip"
                            class="w-full text-xs text-text-muted file:mr-3 file:py-2 file:px-3 file:rounded-control file:border file:border-line file:text-xs file:font-semibold file:bg-surface-soft file:text-ink"
                        >
                        @if (filled($book->digital_file_path))
                            <p class="mt-1 text-xs text-text-muted">File digital privat sudah tersimpan.</p>
                        @endif
                        @error('digital_file')
                            <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <label class="mt-4 flex items-start gap-2.5 text-sm text-ink">
                    <input type="hidden" name="digital_lending_allowed" value="0">
                    <input
                        type="checkbox"
                        name="digital_lending_allowed"
                        value="1"
                        @checked(old('digital_lending_allowed', $book->digital_lending_allowed ?? false))
                        class="mt-0.5 rounded border-line text-brand-600 focus:ring-focus"
                    >
                    <span>
                        Izin distribusi dan model lisensi mengizinkan peminjaman digital.
                        <span class="mt-0.5 block text-xs text-text-muted">Jangan aktifkan tanpa izin yang sesuai.</span>
                    </span>
                </label>
                @error('digital_lending_allowed')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </section>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ $isEdit ? route('books.manage', $book) : route('books.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 cursor-pointer">
                    Simpan Buku
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const collectionType = document.getElementById('collection_type');
            const ebookDescription = document.getElementById('ebook-description-field');
            const novelSynopsis = document.getElementById('novel-synopsis-field');
            const ebookSettings = document.getElementById('ebook-digital-settings');

            const updateCollectionFields = () => {
                const isNovel = collectionType.value === 'novel';
                ebookDescription.classList.toggle('hidden', isNovel);
                novelSynopsis.classList.toggle('hidden', !isNovel);
                ebookSettings.classList.toggle('hidden', isNovel);
            };

            collectionType.addEventListener('change', updateCollectionFields);
            updateCollectionFields();
        });
    </script>
</x-layouts.app>

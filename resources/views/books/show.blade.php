<x-layouts.app :title="$book->title">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.index') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Katalog Koleksi' : 'Buku' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Detail Buku</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <div role="alert" class="mb-6 rounded-card border border-line bg-surface p-4 text-sm text-ink flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-line bg-surface p-4 text-sm text-ink flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @php
        $isNovel = $book->collection_type === 'novel';
        $synopsis = $isNovel ? ($book->synopsis ?: $book->description) : ($book->description ?: $book->synopsis);
        $hasDownload = $book->hasDownload();
        $downloadLink = $book->getDownloadLink();
    @endphp

    <!-- Detail Buku: Mengikuti Layout Landing Page -->
    <div class="bg-surface rounded-card border border-line p-6 sm:p-8 shadow-xs mb-8">
        <section class="grid min-w-0 grid-cols-1 items-start gap-6 min-[579px]:grid-cols-[minmax(13rem,16rem)_minmax(0,1fr)] min-[579px]:gap-8 lg:gap-12">
            <!-- Kolom Kiri: Sampul Buku -->
            <div class="mx-auto w-full max-w-none min-[579px]:mx-0 min-[579px]:max-w-64">
                <div class="aspect-[3/4] overflow-hidden rounded-xl border border-line bg-surface-soft shadow-sm">
                    @if ($book->cover_path)
                        <img
                            src="{{ asset('storage/' . $book->cover_path) }}"
                            alt="Sampul {{ $isNovel ? 'novel' : 'e-book' }} {{ $book->title }}"
                            width="600"
                            height="800"
                            class="h-full w-full object-contain"
                        >
                    @else
                        <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul tidak tersedia untuk {{ $book->title }}">
                            <span class="font-serif text-5xl font-bold tracking-wide" aria-hidden="true">LT</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Kanan: Detail Ringkas, Sinopsis & Aksi -->
            <div class="min-w-0 pt-1">
                <!-- Kategori & Jenis Koleksi -->
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-400">
                    {{ $isNovel ? 'NOVEL' : 'E-BOOK / KEILMUAN' }}
                    @if ($book->category)
                        &bull; {{ $book->category->name }}
                    @endif
                </p>

                <!-- Judul Buku -->
                <h1 class="break-words font-serif text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight tracking-tight text-ink mt-2">
                    {{ $book->title }}
                </h1>

                <!-- Penulis -->
                @if (filled($book->author))
                    <p class="mt-2 text-base text-text-muted">
                        Oleh <span class="font-semibold text-ink">{{ $book->author }}</span>
                    </p>
                @endif

                <!-- Detail Buku Sedikit (Tahun, Penerbit, ISBN) -->
                <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-text-muted">
                    @if ($book->publication_year)
                        <span>Tahun Terbit: <strong class="text-ink font-mono">{{ $book->publication_year }}</strong></span>
                    @endif
                    @if ($book->publisher)
                        <span>Penerbit: <strong class="text-ink">{{ $book->publisher }}</strong></span>
                    @endif
                    @if ($book->isbn)
                        <span>ISBN: <strong class="text-ink font-mono">{{ $book->isbn }}</strong></span>
                    @endif
                </div>

                <!-- Sinopsis (Opsional) -->
                @if (filled($synopsis))
                    <section class="mt-5 pt-4 border-t border-line" aria-labelledby="synopsis-heading">
                        <h2 id="synopsis-heading" class="font-serif text-lg font-bold text-ink sm:text-xl">
                            {{ $isNovel ? 'Sinopsis' : 'Tentang Buku' }}
                        </h2>
                        <p class="mt-2 whitespace-pre-line text-[15px] leading-7 text-text-muted">
                            {{ $synopsis }}
                        </p>
                    </section>
                @endif

                <!-- Tombol Download & Tindakan -->
                <div class="mt-6 pt-5 border-t border-line flex flex-wrap items-center gap-3">
                    @if ($hasDownload)
                        <a
                            href="{{ $downloadLink }}"
                            @if ($book->download_url) target="_blank" rel="noopener noreferrer" @endif
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Download {{ $isNovel ? 'Novel' : 'File' }}</span>
                        </a>
                    @endif

                    @if (!$isNovel && filled($book->digital_file_path))
                        <a
                            href="{{ route('books.read', $book) }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-control border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink hover:bg-surface-soft transition-colors"
                        >
                            Baca Online
                        </a>
                    @endif

                    <!-- Aksi Manajemen Staf (Tanpa warna merah/hijau, serasi dengan tema web) -->
                    @can('update', $book)
                        <a
                            href="{{ route('books.edit', $book) }}"
                            class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-xs font-semibold text-ink hover:bg-surface-soft transition-colors"
                        >
                            Edit Buku
                        </a>
                    @endcan

                    @can('delete', $book)
                        <button
                            type="button"
                            data-archive-action="{{ route('books.destroy', $book) }}"
                            data-archive-title="Arsipkan Buku?"
                            data-archive-desc="Arsipkan buku '{{ $book->title }}'? Riwayat peminjaman terdahulu tetap dipertahankan."
                            data-archive-label="Arsipkan Buku"
                            class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft cursor-pointer transition-colors"
                        >
                            Arsipkan
                        </button>
                    @endcan

                    <a
                        href="{{ route('books.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors"
                    >
                        Kembali ke Katalog
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>

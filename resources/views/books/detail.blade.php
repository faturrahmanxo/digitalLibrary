<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $book->title }} — Litera</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .detail-year {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        .book-description {
            overflow: hidden;
            transition: max-height 240ms ease;
        }

        @media (prefers-reduced-motion: no-preference) {
            @keyframes book-detail-fade-in {
                from {
                    opacity: 0;
                    transform: translateY(5px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .book-detail-enter {
                animation: book-detail-fade-in 320ms ease-out both;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .book-description {
                transition: none;
            }
        }
    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">
    <x-navbar-public />

    <main class="w-full flex-1">
        <div class="book-detail-enter mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-600 dark:text-brand-400">
                    {{ $isPhysical ? 'BUKU FISIK' : ($isNovel ? 'NOVEL' : 'E-BOOK / KEILMUAN') }}
                </p>
                <a
                    href="{{ route('catalog.index', $backGenre ? ['genre' => $backGenre] : []) }}"
                    class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-line/70 bg-transparent px-3 py-2 text-xs font-semibold text-ink transition-colors hover:border-line/70 hover:bg-surface-soft/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus motion-reduce:transition-none"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ $backLabel }}</span>
                </a>
            </header>

            @if (session('success'))
                <div role="status" class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-xs sm:text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-800 dark:text-rose-200 text-xs sm:text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('status'))
                <p role="status" class="mb-4 text-xs leading-relaxed text-text-muted">
                    {{ session('status') }}
                </p>
            @endif

            <section class="grid min-w-0 grid-cols-1 items-start gap-6 min-[579px]:grid-cols-[minmax(13rem,16rem)_minmax(0,1fr)] min-[579px]:gap-8 lg:gap-12">
                <div class="mx-auto w-full max-w-none min-[579px]:mx-0 min-[579px]:max-w-64">
                    <div class="aspect-[3/4] overflow-hidden rounded-xl border border-line/70 bg-surface-soft shadow-sm">
                        @if ($book->cover_path)
                            <img
                                src="{{ asset('storage/' . $book->cover_path) }}"
                                alt="Sampul {{ $isNovel ? 'novel' : 'e-book' }} {{ $book->title }}"
                                width="600"
                                height="800"
                                fetchpriority="high"
                                decoding="async"
                                class="h-full w-full object-contain"
                            >
                        @else
                            <div
                                class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white"
                                role="img"
                                aria-label="Sampul {{ $isNovel ? 'novel' : 'e-book' }} tidak tersedia untuk {{ $book->title }}"
                            >
                                <span class="font-serif text-5xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="min-w-0 pt-1 md:pt-2">
                    <h1 class="break-words font-serif text-3xl font-bold leading-tight tracking-tight text-ink sm:text-3xl lg:text-4xl">
                        {{ $book->title }}
                    </h1>
                    @if (filled($book->author))
                        <p class="mt-2 text-base text-text-muted">
                            Oleh <span class="font-semibold text-ink">{{ $book->author }}</span>
                        </p>
                    @endif
                    @if (filled($book->publication_year))
                        <p class="mt-1.5 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-text-muted">
                            Tahun terbit
                            <span class="detail-year text-sm font-medium text-ink">{{ $book->publication_year }}</span>
                        </p>
                    @endif

                    <!-- ============================================== -->
                    <!-- PHYSICAL BOOK: SHELF & STOCK INFORMATION       -->
                    <!-- ============================================== -->
                    @if ($isPhysical)
                        <div class="mt-5 p-4 rounded-2xl bg-surface border border-line shadow-2xs space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-text-muted">Lokasi Rak:</span>
                                    <strong class="font-bold text-ink">
                                        @if ($book->shelf)
                                            {{ $book->shelf->name }} ({{ $book->shelf->code }})
                                            @if ($book->shelf->location)
                                                <span class="text-text-muted font-normal">· {{ $book->shelf->location }}</span>
                                            @endif
                                        @else
                                            Rak Utama Perpustakaan
                                        @endif
                                    </strong>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-text-muted">Ketersediaan Stok:</span>
                                    @if ($book->available_copies > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            {{ $book->available_copies }} eksemplar tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-700 dark:text-rose-400 font-bold">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            Stok Habis
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Synopsis / Description -->
                    <section class="mt-5" aria-labelledby="book-description-heading">
                        <h2 id="book-description-heading" class="font-serif text-lg font-bold text-ink sm:text-xl">Tentang Buku</h2>
                        @if (filled($description))
                            <p id="book-description" class="book-description mt-2 whitespace-pre-line text-[15px] leading-7 text-text-muted">{{ $description }}</p>
                            <button
                                id="book-description-toggle"
                                type="button"
                                aria-controls="book-description"
                                aria-expanded="false"
                                hidden
                                class="mt-2 text-xs font-semibold text-brand-600 underline decoration-transparent underline-offset-4 transition-colors hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus dark:text-brand-400 dark:hover:text-brand-300 motion-reduce:transition-none cursor-pointer"
                            >
                                Baca Selengkapnya
                            </button>
                        @else
                            <p class="mt-2 text-[15px] leading-7 text-text-muted">Deskripsi buku belum tersedia.</p>
                        @endif
                    </section>

                    @if (! $isPhysical && ! $hasDigitalAccessFile)
                        <p role="status" class="mt-4 text-xs leading-relaxed text-text-muted">
                            Belum ada file digital yang dapat diakses untuk buku ini.
                        </p>
                    @endif

                    <!-- ============================================== -->
                    <!-- ACTIONS: PHYSICAL LOAN VS DIGITAL ACCESS       -->
                    <!-- ============================================== -->
                    @if ($isPhysical)
                        <!-- Physical Book Actions -->
                        <div class="mt-6 border-t border-line/70 pt-5">
                            @guest
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 px-6 py-2.5 text-sm font-bold text-white transition-all shadow-md active:scale-95 cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Masuk untuk Meminjam</span>
                                </a>
                                <p class="mt-2 text-xs text-text-muted">Silakan masuk dengan akun kampus untuk mengajukan peminjaman buku.</p>
                            @endguest

                            @auth
                                @if ($userBorrowing)
                                    @if ($userBorrowing->isPending())
                                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="font-bold text-sm">Pengajuan Peminjaman Sedang Menunggu Verifikasi</span>
                                            </div>
                                            <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-300/80">
                                                Pengajuan peminjaman untuk buku ini diajukan pada {{ $userBorrowing->created_at->format('d M Y H:i') }}. Petugas perpustakaan akan segera memverifikasi ketersediaan buku.
                                            </p>
                                        </div>
                                    @elseif ($userBorrowing->isApproved())
                                        <div class="p-4 rounded-2xl bg-sky-500/10 border border-sky-500/30 text-sky-900 dark:text-sky-200">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="font-bold text-sm">Pengajuan Disetujui — Siap Diambil</span>
                                            </div>
                                            <p class="mt-1 text-xs text-sky-800/80 dark:text-sky-300/80">
                                                Buku telah direservasi untuk Anda. Silakan tunjukkan kode transaksi <strong>{{ $userBorrowing->transaction_code }}</strong> kepada petugas di meja sirkulasi.
                                            </p>
                                        </div>
                                    @elseif ($userBorrowing->isBorrowed())
                                        <div class="p-4 rounded-2xl bg-brand-500/10 border border-brand-500/30 text-brand-900 dark:text-brand-200">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="font-bold text-sm">Buku Sedang Anda Pinjam</span>
                                            </div>
                                            <p class="mt-1 text-xs text-brand-800/80 dark:text-brand-300/80">
                                                Batas waktu pengembalian: <strong class="text-ink">{{ $userBorrowing->due_date?->format('d M Y') }}</strong>
                                                @if ($userBorrowing->isOverdue())
                                                    <span class="inline-block ml-1 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold">Terlambat</span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                @else
                                    @if ($book->available_copies > 0)
                                        <form method="POST" action="{{ route('books.borrow', $book) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 px-6 py-2.5 text-sm font-bold text-white transition-all shadow-md active:scale-95 cursor-pointer"
                                            >
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                                <span>Pinjam Buku</span>
                                            </button>
                                            <p class="mt-2 text-xs text-text-muted">Masa pinjam 7 hari kalender sejak penyerahan buku fisik di perpustakaan.</p>
                                        </form>
                                    @else
                                        <div class="flex items-center gap-3">
                                            <button type="button" disabled class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-surface-soft border border-line px-6 py-2.5 text-sm font-semibold text-text-muted cursor-not-allowed opacity-60">
                                                Stok Habis
                                            </button>
                                            <span class="text-xs text-text-muted">Seluruh eksemplar buku ini sedang dipinjam oleh civitas lain.</span>
                                        </div>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    @else
                        <!-- E-book / Novel Digital Actions -->
                        @if ($book->hasDownload())
                            <div class="mt-5 border-t border-line/70 pt-4">
                                <a
                                    href="{{ $book->getDownloadLink() }}"
                                    @if ($book->download_url) target="_blank" rel="noopener noreferrer" @endif
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus shadow-xs"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Download {{ $isNovel ? 'Novel' : 'File' }}</span>
                                </a>
                            </div>
                        @endif

                        @if (! $isNovel)
                            <section class="mt-5 border-t border-line/70 pt-4" aria-label="Akses E-book">
                                @if ($ebookCanDownload)
                                    @if ($ebookBorrowing)
                                        <p class="text-sm text-text-muted">
                                            Akses tersedia sampai
                                            <span class="font-semibold text-ink">{{ $ebookBorrowing->due_at?->format('d M Y') }}</span>.
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if ($ebookCanReadOnline)
                                                <a href="{{ $readLink ?: route('books.read', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                    Baca Online
                                                </a>
                                            @endif
                                            <a href="{{ $downloadLink ?: route('books.digital.download', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-line px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                Download E-book
                                            </a>
                                            <a href="{{ route('borrowings.show', $ebookBorrowing) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">
                                                Kelola Pinjaman
                                            </a>
                                        </div>
                                    @elseif ($ebookAvailable > 0)
                                        <p class="text-sm text-text-muted">Akses Tersedia · {{ $ebookAvailable }} lisensi</p>
                                        @auth
                                            <form method="POST" action="{{ route('books.digital.borrow', $book) }}" class="mt-3">
                                                @csrf
                                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                    Pinjam E-book
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('login') }}" class="mt-3 inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                Login untuk Meminjam E-book
                                            </a>
                                        @endauth
                                    @else
                                        <p role="status" class="text-sm text-text-muted">Kuota Penuh</p>
                                    @endif
                                @elseif ($readLink || $downloadLink)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($readLink)
                                            <a href="{{ $readLink }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                Baca Online
                                            </a>
                                        @endif
                                        @if ($downloadLink)
                                            <a href="{{ $downloadLink }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-line px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                Download E-book
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </section>
                        @endif

                        <!-- Novel Volumes List If Available -->
                        @if ($volumes->isNotEmpty())
                            <section class="mt-6 border-t border-line/70 pt-5" aria-labelledby="novel-volumes-heading">
                                <h2 id="novel-volumes-heading" class="font-serif text-lg font-bold text-ink">Daftar Volume</h2>
                                <ul class="mt-3 divide-y divide-line/70">
                                    @foreach ($volumes as $volume)
                                        <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-sm text-ink">
                                                    Volume {{ $volume->volume_number }}
                                                    @if (filled($volume->title))
                                                        <span class="font-normal text-text-muted">· {{ $volume->title }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if (filled($volume->download_url))
                                                    <a
                                                        href="{{ $volume->download_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="px-3.5 py-1.5 rounded-lg border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors"
                                                    >
                                                        Download Volume
                                                    </a>
                                                @elseif (filled($volume->digital_file_path))
                                                    <a
                                                        href="{{ route('novel-volumes.digital.download', $volume) }}"
                                                        class="px-3.5 py-1.5 rounded-lg border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors"
                                                    >
                                                        Download Volume
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endif

                    <!-- Favorite Toggle -->
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @guest
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-line/70 bg-transparent px-3.5 py-2 text-xs font-semibold text-ink transition-colors hover:border-line/70 hover:bg-surface-soft/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus motion-reduce:transition-none"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z" />
                                </svg>
                                Simpan Buku
                            </a>
                        @endguest

                        @auth
                            @if ($isFavorited)
                                <span class="inline-flex min-h-9 items-center gap-2 px-1 text-xs font-semibold text-brand-700 dark:text-brand-200">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Tersimpan
                                </span>
                            @else
                                <form method="POST" action="{{ route('books.favorite', $book) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-line/70 bg-transparent px-3.5 py-2 text-xs font-semibold text-ink transition-colors hover:border-line/70 hover:bg-surface-soft/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus motion-reduce:transition-none"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z" />
                                        </svg>
                                        Simpan Buku
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </section>

            <!-- ============================================== -->
            <!-- BAGIAN SERI & VOLUME TERKAIT (E-BOOK / NOVEL)  -->
            <!-- ============================================== -->
            @if (! $isPhysical && (isset($relatedSeriesBooks) && $relatedSeriesBooks->isNotEmpty() || $volumes->isNotEmpty()))
                <section class="mt-8 border-t border-line/70 pt-6 sm:mt-10 sm:pt-8" aria-labelledby="series-volume-heading">
                    <div class="mb-5 sm:mb-6 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="inline-flex items-center gap-2">
                                <span class="w-6 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 font-sans">
                                    Seri &amp; Volume
                                </span>
                            </div>
                            <h2 id="series-volume-heading" class="font-serif text-xl sm:text-2xl font-bold text-ink tracking-tight mt-1">
                                Bagian Seri Ini
                            </h2>
                            <p class="text-xs text-text-muted mt-1 font-sans">
                                Volume lanjutan, edisi lain, atau seri buku yang masih berkaitan dengan judul ini.
                            </p>
                        </div>
                    </div>

                    <!-- Responsive Lightweight Grid -->
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 sm:gap-5">
                        {{-- 1. Related Series Books --}}
                        @if (isset($relatedSeriesBooks))
                            @foreach ($relatedSeriesBooks as $seriesBook)
                                <div class="group/series-card flex flex-col h-full rounded-2xl bg-surface border border-line hover:border-brand-600 dark:hover:border-brand-400 p-3 sm:p-3.5 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                                    <!-- Cover -->
                                    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface-soft border border-line/60 shadow-2xs mb-2.5">
                                        @if ($seriesBook->cover_path)
                                            <img
                                                src="{{ asset('storage/' . $seriesBook->cover_path) }}"
                                                alt="Sampul {{ $seriesBook->title }}"
                                                loading="lazy"
                                                decoding="async"
                                                class="h-full w-full object-contain transition-transform duration-300 motion-safe:group-hover/series-card:scale-105"
                                            >
                                        @else
                                            <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul tidak tersedia untuk {{ $seriesBook->title }}">
                                                <span class="font-serif text-3xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
                                            </div>
                                        @endif

                                        @if (filled($seriesBook->volume_label))
                                            <span class="absolute top-2 left-2 z-10 max-w-[calc(100%-1rem)] truncate rounded-md bg-brand-600/90 dark:bg-brand-500/90 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs uppercase tracking-wide">
                                                {{ $seriesBook->volume_label }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Metadata -->
                                    <div class="flex flex-col flex-1 min-w-0">
                                        @if (filled($seriesBook->volume_label))
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 font-sans">
                                                {{ $seriesBook->volume_label }}
                                            </span>
                                        @endif

                                        <h3 class="font-bold text-xs sm:text-sm text-ink group-hover/series-card:text-brand-600 dark:group-hover/series-card:text-brand-400 transition-colors line-clamp-2 leading-snug mt-0.5 font-sans" title="{{ $seriesBook->title }}">
                                            {{ $seriesBook->title }}
                                        </h3>

                                        @if ($seriesBook->publication_year)
                                            <p class="font-year text-xs text-text-muted mt-1 font-sans">
                                                Tahun {{ $seriesBook->publication_year }}
                                            </p>
                                        @endif

                                        <div class="mt-3 pt-2 border-t border-line/60">
                                            <a
                                                href="{{ route('books.show', $seriesBook) }}"
                                                class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-line bg-surface hover:bg-brand-600 hover:text-white hover:border-brand-600 dark:hover:bg-brand-500 dark:hover:border-brand-500 text-xs font-semibold text-ink transition-all shadow-2xs group/btn cursor-pointer"
                                            >
                                                <span>Buka Detail</span>
                                                <svg class="w-3.5 h-3.5 transform group-hover/btn:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- 2. Novel Volumes --}}
                        @foreach ($volumes as $volume)
                            <div class="group/series-card flex flex-col h-full rounded-2xl bg-surface border border-line hover:border-brand-600 dark:hover:border-brand-400 p-3 sm:p-3.5 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                                <!-- Thumbnail -->
                                <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface-soft border border-line/60 shadow-2xs mb-2.5">
                                    @if ($book->cover_path)
                                        <img
                                            src="{{ asset('storage/' . $book->cover_path) }}"
                                            alt="Sampul Volume {{ $volume->volume_number }} - {{ $book->title }}"
                                            loading="lazy"
                                            decoding="async"
                                            class="h-full w-full object-contain transition-transform duration-300 motion-safe:group-hover/series-card:scale-105"
                                        >
                                    @else
                                        <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul tidak tersedia">
                                            <span class="font-serif text-3xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
                                        </div>
                                    @endif

                                    <span class="absolute top-2 left-2 z-10 rounded-md bg-brand-600/90 dark:bg-brand-500/90 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs uppercase tracking-wide">
                                        Volume {{ $volume->volume_number }}
                                    </span>
                                </div>

                                <div class="flex flex-col flex-1 min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 font-sans">
                                        Volume {{ $volume->volume_number }}
                                    </span>

                                    <h3 class="font-bold text-xs sm:text-sm text-ink group-hover/series-card:text-brand-600 dark:group-hover/series-card:text-brand-400 transition-colors line-clamp-2 leading-snug mt-0.5 font-sans" title="{{ $volume->title ?: ('Volume ' . $volume->volume_number) }}">
                                        {{ $volume->title ?: ($book->title . ' — Volume ' . $volume->volume_number) }}
                                    </h3>

                                    @if ($book->publication_year)
                                        <p class="font-year text-xs text-text-muted mt-1 font-sans">
                                            Tahun {{ $book->publication_year }}
                                        </p>
                                    @endif

                                    <div class="mt-3 pt-2 border-t border-line/60">
                                        @if (filled($volume->download_url))
                                            <a
                                                href="{{ $volume->download_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-line bg-surface hover:bg-brand-600 hover:text-white hover:border-brand-600 text-xs font-semibold text-ink transition-all shadow-2xs"
                                            >
                                                <span>Download</span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                        @elseif (filled($volume->digital_file_path))
                                            <a
                                                href="{{ route('novel-volumes.digital.download', $volume) }}"
                                                class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-line bg-surface hover:bg-brand-600 hover:text-white hover:border-brand-600 text-xs font-semibold text-ink transition-all shadow-2xs"
                                            >
                                                <span>Download</span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                        @else
                                            <span class="inline-flex w-full items-center justify-center px-3 py-1.5 rounded-lg bg-surface-soft text-[11px] text-text-muted">
                                                Akses Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- ============================================== -->
            <!-- INFORMASI BUKU (METADATA TABLE)                -->
            <!-- ============================================== -->
            @if (filled($book->category?->name) || filled($book->publisher) || filled($book->isbn) || ($isPhysical && $book->shelf))
                <section class="mt-7 border-t border-line/70 pt-5 sm:mt-8" aria-labelledby="book-information-heading">
                    <h2 id="book-information-heading" class="font-serif text-lg font-bold text-ink sm:text-xl">Informasi Buku</h2>
                    <dl class="mt-2 grid grid-cols-1 sm:grid-cols-2 sm:gap-x-8">
                        @if (filled($book->category?->name))
                            <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-3 border-b border-line/60 py-2.5">
                                <dt class="text-sm text-text-muted">Kategori</dt>
                                <dd class="break-words text-sm font-medium text-ink">{{ $book->category->name }}</dd>
                            </div>
                        @endif
                        @if (filled($book->publisher))
                            <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-3 border-b border-line/60 py-2.5">
                                <dt class="text-sm text-text-muted">Penerbit</dt>
                                <dd class="break-words text-sm font-medium text-ink">{{ $book->publisher }}</dd>
                            </div>
                        @endif
                        @if (filled($book->isbn))
                            <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-3 border-b border-line/60 py-2.5">
                                <dt class="text-sm text-text-muted">ISBN</dt>
                                <dd class="break-all text-sm font-medium text-ink">{{ $book->isbn }}</dd>
                            </div>
                        @endif
                        @if ($isPhysical && $book->shelf)
                            <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-3 border-b border-line/60 py-2.5">
                                <dt class="text-sm text-text-muted">Lokasi Rak</dt>
                                <dd class="break-words text-sm font-medium text-ink">
                                    {{ $book->shelf->name }} ({{ $book->shelf->code }})
                                    @if ($book->shelf->location)
                                        · {{ $book->shelf->location }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif
        </div>
    </main>

    <x-footer-public />

    @if (filled($description))
        <script>
            const initializeBookDescription = () => {
                const description = document.getElementById('book-description');
                const toggle = document.getElementById('book-description-toggle');

                if (!description || !toggle) {
                    return;
                }

                const lineHeight = Number.parseFloat(window.getComputedStyle(description).lineHeight);
                const collapsedHeight = lineHeight * 4;

                if (!Number.isFinite(collapsedHeight) || description.scrollHeight <= collapsedHeight + 1) {
                    return;
                }

                description.style.maxHeight = `${collapsedHeight}px`;
                toggle.hidden = false;
                toggle.addEventListener('click', () => {
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                    toggle.setAttribute('aria-expanded', String(!isExpanded));
                    toggle.textContent = isExpanded ? 'Baca Selengkapnya' : 'Tampilkan Lebih Sedikit';
                    description.style.maxHeight = isExpanded ? `${collapsedHeight}px` : `${description.scrollHeight}px`;
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeBookDescription, { once: true });
            } else {
                initializeBookDescription();
            }
        </script>
    @endif
</body>
</html>

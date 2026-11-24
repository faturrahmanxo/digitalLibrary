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
                    {{ $isNovel ? 'NOVEL' : 'E-BOOK / KEILMUAN' }}
                </p>
                <a
                    href="{{ route('catalog.index', ['genre' => $backGenre]) }}"
                    class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-line/70 bg-transparent px-3 py-2 text-xs font-semibold text-ink transition-colors hover:border-line/70 hover:bg-surface-soft/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus motion-reduce:transition-none"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ $backLabel }}</span>
                </a>
            </header>

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
                                class="mt-2 text-xs font-semibold text-brand-600 underline decoration-transparent underline-offset-4 transition-colors hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus dark:text-brand-400 dark:hover:text-brand-300 motion-reduce:transition-none"
                            >
                                Baca Selengkapnya
                            </button>
                        @else
                            <p class="mt-2 text-[15px] leading-7 text-text-muted">Deskripsi buku belum tersedia.</p>
                        @endif
                    </section>

                    @if (! $hasDigitalAccessFile)
                        <p role="status" class="mt-4 text-xs leading-relaxed text-text-muted">
                            Belum ada file digital yang dapat diakses untuk buku ini.
                        </p>
                    @endif

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
                                            <a href="{{ route('books.read', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                Baca Online
                                            </a>
                                        @endif
                                        <a href="{{ route('books.digital.download', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-line px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
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
                                <p class="mt-3 text-xs leading-relaxed text-text-muted">
                                    Akses digital berakhir saat jatuh tempo. File yang sudah diunduh tidak otomatis kedaluwarsa; pembatasan akses offline memerlukan DRM.
                                </p>
                            @endif
                        </section>
                    @else
                        <section class="mt-5 border-t border-line/70 pt-4" aria-labelledby="novel-volumes-heading">
                            <h2 id="novel-volumes-heading" class="font-serif text-lg font-bold text-ink">Daftar Volume</h2>
                            @if ($volumes->isEmpty())
                                <p class="mt-2 text-sm text-text-muted">Belum ada volume yang diterbitkan.</p>
                            @else
                                <ul class="mt-2 divide-y divide-line/70">
                                    @foreach ($volumes as $volume)
                                        <li class="py-4 first:pt-2">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-ink">
                                                        Volume {{ $volume->volume_number }}
                                                        @if (filled($volume->title))
                                                            <span class="font-normal text-text-muted">· {{ $volume->title }}</span>
                                                        @endif
                                                    </p>
                                                    @if ($volume->digital_download_available)
                                                        <p class="mt-1 text-xs text-text-muted">Akses Tersedia · {{ $volume->digital_access_available }} lisensi</p>
                                                    @else
                                                        <p class="mt-1 text-xs text-text-muted">Peminjaman digital belum tersedia.</p>
                                                    @endif
                                                    @if ($volume->my_active_borrowing)
                                                        <p class="mt-1 text-xs text-text-muted">
                                                            Pinjaman aktif sampai {{ $volume->my_active_borrowing->due_at?->format('d M Y') }}.
                                                        </p>
                                                    @endif
                                                </div>

                                                <div class="flex shrink-0 flex-wrap gap-2">
                                                    @if ($volume->my_active_borrowing)
                                                        @if ($volume->digital_download_available)
                                                            <a href="{{ route('novel-volumes.digital.download', $volume) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-line px-3 py-1.5 text-xs font-semibold text-ink hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                                Download Volume
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('borrowings.show', $volume->my_active_borrowing) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg px-2 py-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">
                                                            Kelola Pinjaman
                                                        </a>
                                                    @elseif ($volume->digital_download_available && $volume->digital_access_available > 0)
                                                        @auth
                                                            <form method="POST" action="{{ route('novel-volumes.digital.borrow', $volume) }}">
                                                                @csrf
                                                                <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                                    Pinjam Volume
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a href="{{ route('login') }}" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                                Login untuk Meminjam Volume
                                                            </a>
                                                        @endauth
                                                    @elseif ($volume->digital_download_available)
                                                        <span role="status" class="inline-flex min-h-9 items-center px-2 text-xs text-text-muted">Kuota Penuh</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs leading-relaxed text-text-muted">
                                    Akses digital berakhir saat jatuh tempo. File yang sudah diunduh tidak otomatis kedaluwarsa; pembatasan akses offline memerlukan DRM.
                                </p>
                            @endif
                        </section>
                    @endif

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

            @if (filled($book->category?->name) || filled($book->publisher) || filled($book->isbn))
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
                        @if ($ebookCanDownload)
                            <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] gap-3 border-b border-line/60 py-2.5">
                                <dt class="text-sm text-text-muted">Format File</dt>
                                <dd class="text-sm font-medium uppercase text-ink">{{ pathinfo($book->digital_file_path, PATHINFO_EXTENSION) }}</dd>
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

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Koleksi Saya — Litera</title>
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
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">
    <x-navbar-public />

    <main class="w-full flex-1">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <nav aria-label="Breadcrumb" class="text-sm text-text-muted">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="transition-colors hover:text-ink">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-medium text-ink">Koleksi Saya</li>
                </ol>
            </nav>

            <header class="mt-6 sm:mt-8">
                <h1 class="font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Koleksi Saya</h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-text-muted sm:text-base">
                    Temukan kembali buku yang kamu simpan dan lanjutkan perjalanan membacamu.
                </p>
            </header>

            <nav aria-label="Navigasi koleksi" class="mt-8 max-w-full overflow-x-auto">
                <div class="flex min-w-max gap-6 border-b border-line sm:min-w-0 sm:gap-8">
                    @foreach ([
                        'sedang-dibaca' => 'Sedang Dibaca',
                        'daftar-bacaan' => 'Daftar Bacaan',
                        'selesai-dibaca' => 'Selesai Dibaca',
                    ] as $tabKey => $tabLabel)
                        <a
                            href="{{ route('collection.index', ['tab' => $tabKey]) }}"
                            @if ($tab === $tabKey) aria-current="page" @endif
                            class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold transition-colors motion-reduce:transition-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus {{ $tab === $tabKey ? 'border-brand-600 text-brand-700 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-text-muted hover:text-ink' }}"
                        >
                            {{ $tabLabel }}
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="mt-8 sm:mt-10">
                @if ($tab === 'sedang-dibaca')
                    <section aria-labelledby="reading-now-heading" class="py-12 text-center sm:py-16">
                        <h2 id="reading-now-heading" class="sr-only">Sedang Dibaca</h2>
                        <p class="text-sm text-text-muted">Belum ada buku yang sedang dibaca.</p>
                    </section>
                @elseif ($tab === 'daftar-bacaan')
                    <div class="space-y-12 sm:space-y-14">
                        <section aria-labelledby="ebooks-heading">
                            <div class="flex items-center gap-4">
                                <h2 id="ebooks-heading" class="shrink-0 font-serif text-2xl font-semibold text-ink sm:text-3xl">E-book / Keilmuan</h2>
                                <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                            </div>

                            @if ($ebooks->isEmpty())
                                <p class="py-8 text-sm text-text-muted">Belum ada e-book yang disimpan.</p>
                            @else
                                <div class="mt-6 grid grid-cols-1 gap-x-4 gap-y-7 min-[360px]:grid-cols-2 sm:grid-cols-3 sm:gap-x-5 lg:grid-cols-4 xl:grid-cols-5">
                                    @foreach ($ebooks as $book)
                                        <x-book-card-ebook
                                            :book="$book"
                                            :href="route('books.show', $book)"
                                            cover-label="e-book"
                                            :show-author="true"
                                        />
                                    @endforeach
                                </div>
                                @if ($ebooks->hasPages())
                                    <div class="mt-6">{{ $ebooks->links() }}</div>
                                @endif
                            @endif
                        </section>

                        <section aria-labelledby="novels-heading">
                            <div class="flex items-center gap-4">
                                <h2 id="novels-heading" class="shrink-0 font-serif text-2xl font-semibold text-ink sm:text-3xl">Novel</h2>
                                <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                            </div>

                            @if ($novels->isEmpty())
                                <p class="py-8 text-sm text-text-muted">Belum ada novel yang disimpan.</p>
                            @else
                                <div class="mt-6 grid grid-cols-1 gap-x-4 gap-y-7 min-[360px]:grid-cols-2 sm:grid-cols-3 sm:gap-x-5 lg:grid-cols-4 xl:grid-cols-5">
                                    @foreach ($novels as $book)
                                        <x-book-card-ebook
                                            :book="$book"
                                            :href="route('books.show', $book)"
                                            cover-label="novel"
                                            :show-author="true"
                                        />
                                    @endforeach
                                </div>
                                @if ($novels->hasPages())
                                    <div class="mt-6">{{ $novels->links() }}</div>
                                @endif
                            @endif
                        </section>
                    </div>
                @else
                    <section aria-labelledby="finished-reading-heading" class="py-12 text-center sm:py-16">
                        <h2 id="finished-reading-heading" class="sr-only">Selesai Dibaca</h2>
                        <p class="text-sm text-text-muted">Belum ada buku yang selesai dibaca.</p>
                    </section>
                @endif
            </div>
        </div>
    </main>

    <x-footer-public />

</body>
</html>

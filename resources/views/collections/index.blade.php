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

            <header class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h1 class="font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Koleksi &amp; Peminjaman Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-text-muted">
                        Pantau status pengajuan peminjaman, tanggal jatuh tempo buku aktif, riwayat pengembalian, dan daftar bacaan Anda.
                    </p>
                </div>
                <a
                    href="{{ route('catalog.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm self-start sm:self-auto shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Jelajahi Katalog</span>
                </a>
            </header>

            @if (session('success'))
                <div class="mt-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 dark:text-emerald-200 text-xs sm:text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Navigation Tabs with Count Badges -->
            <nav aria-label="Navigasi koleksi" class="mt-8 max-w-full overflow-x-auto">
                <div class="flex min-w-max gap-6 border-b border-line sm:min-w-0 sm:gap-8">
                    @php
                        $tabs = [
                            'sedang-dibaca' => ['label' => 'Sedang Dibaca', 'count' => $activeCount],
                            'pengajuan' => ['label' => 'Daftar Pengajuan', 'count' => $pendingCount],
                            'selesai-dibaca' => ['label' => 'Selesai Dibaca', 'count' => $historyCount],
                            'daftar-bacaan' => ['label' => 'Daftar Bacaan', 'count' => $favoritesCount],
                        ];
                    @endphp

                    @foreach ($tabs as $tabKey => $tabData)
                        <a
                            href="{{ route('collection.index', ['tab' => $tabKey]) }}"
                            @if ($tab === $tabKey) aria-current="page" @endif
                            class="shrink-0 border-b-2 px-1 pb-3 text-sm font-semibold transition-colors flex items-center gap-2 {{ $tab === $tabKey ? 'border-brand-600 text-brand-700 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-text-muted hover:text-ink' }}"
                        >
                            <span>{{ $tabData['label'] }}</span>
                            @if ($tabData['count'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $tab === $tabKey ? 'bg-brand-600 text-white dark:bg-brand-400 dark:text-brand-950' : 'bg-surface-soft text-text-muted border border-line' }}">
                                    {{ $tabData['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="mt-8 sm:mt-10">
                <!-- ============================================== -->
                <!-- TAB 1: SEDANG DIBACA / PEMINJAMAN AKTIF        -->
                <!-- ============================================== -->
                @if ($tab === 'sedang-dibaca')
                    @if (! $activeBorrowings || $activeBorrowings->isEmpty())
                        <div class="py-16 text-center rounded-3xl border border-line bg-surface max-w-lg mx-auto shadow-2xs">
                            <div class="w-14 h-14 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-ink">Belum ada buku yang sedang dibaca.</h3>
                            <p class="text-xs text-text-muted mt-1.5 mb-5 max-w-sm mx-auto">
                                Anda belum memiliki buku yang sedang dipinjam saat ini. Temukan koleksi buku fisik di katalog kami.
                            </p>
                            <a href="{{ route('catalog.index', ['collection_type' => 'physical']) }}" class="inline-flex px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm">
                                Cari Buku Fisik
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($activeBorrowings as $borrowing)
                                @php
                                    $book = $borrowing->book ?? $borrowing->items->first()?->book;
                                    $isOverdue = $borrowing->isOverdue();
                                    $today = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
                                    $dueDate = $borrowing->due_date ? $borrowing->due_date->copy()->startOfDay() : null;
                                    $daysRemaining = $dueDate ? (int) $today->diffInDays($dueDate, false) : 0;
                                    $daysOverdue = ($dueDate && $isOverdue) ? (int) $today->diffInDays($dueDate) : 0;
                                @endphp

                                <div class="p-5 sm:p-6 rounded-3xl border border-line bg-surface shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-line/90 transition-all">
                                    <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                                        <!-- Book Cover Thumbnail -->
                                        <div class="w-16 h-22 sm:w-20 sm:h-28 rounded-xl overflow-hidden bg-surface-soft border border-line shrink-0 relative flex items-center justify-center">
                                            @if ($book && $book->cover_path)
                                                <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full p-2 flex items-center justify-center text-white" style="background: linear-gradient(135deg, #15241B 0%, #294638 100%);">
                                                    <span class="font-serif font-bold text-lg">{{ $book ? strtoupper(substr($book->title, 0, 2)) : 'LT' }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Book Details -->
                                        <div class="min-w-0 space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-mono text-[11px] text-text-muted">{{ $borrowing->transaction_code }}</span>
                                                @if ($borrowing->isApproved())
                                                    <span class="px-2.5 py-0.5 rounded-full bg-sky-500/10 text-sky-700 dark:text-sky-300 text-[10px] font-bold">
                                                        Disetujui · Siap Diambil
                                                    </span>
                                                @elseif ($borrowing->isBorrowed())
                                                    @if ($isOverdue)
                                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-700 dark:text-rose-400 text-[10px] font-bold">
                                                            Terlambat {{ $daysOverdue }} Hari
                                                        </span>
                                                    @else
                                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold">
                                                            Sedang Dipinjam · {{ $daysRemaining }} Hari Tersisa
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>

                                            <h3 class="text-base sm:text-lg font-bold text-ink leading-snug">
                                                @if ($book)
                                                    <a href="{{ route('books.show', $book) }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                                        {{ $book->title }}
                                                    </a>
                                                @else
                                                    {{ $borrowing->items->first()?->book_title_snapshot ?? 'Buku Perpustakaan' }}
                                                @endif
                                            </h3>

                                            <p class="text-xs text-text-muted">
                                                Dipinjam: <strong class="text-ink">{{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('d M Y') : '-' }}</strong>
                                                · Batas Kembali: <strong class="text-ink">{{ $borrowing->due_date ? $borrowing->due_date->format('d M Y') : '-' }}</strong>
                                            </p>

                                            @if ($book && $book->shelf)
                                                <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold">
                                                    Lokasi Pengembalian: Rak {{ $book->shelf->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                                        @if ($book)
                                            <a
                                                href="{{ route('books.show', $book) }}"
                                                class="px-4 py-2.5 rounded-xl border border-line hover:bg-surface-soft text-ink text-xs font-bold transition-all"
                                            >
                                                Lihat Detail Buku
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if ($activeBorrowings->hasPages())
                                <div class="mt-8 flex justify-center">
                                    {{ $activeBorrowings->links() }}
                                </div>
                            @endif
                        </div>
                    @endif

                <!-- ============================================== -->
                <!-- TAB 2: DAFTAR PENGAJUAN (PENDING)              -->
                <!-- ============================================== -->
                @elseif ($tab === 'pengajuan')
                    @if (! $pendingBorrowings || $pendingBorrowings->isEmpty())
                        <div class="py-16 text-center rounded-3xl border border-line bg-surface max-w-lg mx-auto shadow-2xs">
                            <div class="w-14 h-14 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-ink">Tidak Ada Pengajuan Menunggu</h3>
                            <p class="text-xs text-text-muted mt-1.5 mb-5 max-w-sm mx-auto">
                                Semua pengajuan peminjaman Anda telah diproses. Ajukan peminjaman buku baru melalui halaman detail buku di katalog.
                            </p>
                            <a href="{{ route('catalog.index') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm">
                                Jelajahi Katalog
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($pendingBorrowings as $borrowing)
                                @php
                                    $book = $borrowing->book ?? $borrowing->items->first()?->book;
                                @endphp

                                <div class="p-5 sm:p-6 rounded-3xl border border-line bg-surface shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-line/90 transition-all">
                                    <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                                        <div class="w-16 h-22 sm:w-20 sm:h-28 rounded-xl overflow-hidden bg-surface-soft border border-line shrink-0 relative flex items-center justify-center">
                                            @if ($book && $book->cover_path)
                                                <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full p-2 flex items-center justify-center text-white" style="background: linear-gradient(135deg, #15241B 0%, #294638 100%);">
                                                    <span class="font-serif font-bold text-lg">{{ $book ? strtoupper(substr($book->title, 0, 2)) : 'LT' }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-mono text-[11px] text-text-muted">{{ $borrowing->transaction_code }}</span>
                                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-300 text-[10px] font-bold">
                                                    Menunggu Verifikasi Petugas
                                                </span>
                                            </div>

                                            <h3 class="text-base sm:text-lg font-bold text-ink leading-snug">
                                                @if ($book)
                                                    <a href="{{ route('books.show', $book) }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                                        {{ $book->title }}
                                                    </a>
                                                @else
                                                    {{ $borrowing->items->first()?->book_title_snapshot ?? 'Buku Perpustakaan' }}
                                                @endif
                                            </h3>

                                            <p class="text-xs text-text-muted">
                                                Diajukan pada: <strong class="text-ink">{{ $borrowing->created_at->format('d M Y, H:i') }}</strong>
                                            </p>
                                            <p class="text-xs text-text-muted">
                                                Petugas perpustakaan akan segera meninjau ketersediaan fisik di rak.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                                        @if ($book)
                                            <a
                                                href="{{ route('books.show', $book) }}"
                                                class="px-4 py-2.5 rounded-xl border border-line hover:bg-surface-soft text-ink text-xs font-bold transition-all"
                                            >
                                                Lihat Detail Buku
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if ($pendingBorrowings->hasPages())
                                <div class="mt-8 flex justify-center">
                                    {{ $pendingBorrowings->links() }}
                                </div>
                            @endif
                        </div>
                    @endif

                <!-- ============================================== -->
                <!-- TAB 3: SELESAI DIBACA / RIWAYAT TRANSAKSI      -->
                <!-- ============================================== -->
                @elseif ($tab === 'selesai-dibaca')
                    @if (! $historyBorrowings || $historyBorrowings->isEmpty())
                        <div class="py-16 text-center rounded-3xl border border-line bg-surface max-w-lg mx-auto shadow-2xs">
                            <div class="w-14 h-14 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-ink">Belum ada buku yang selesai dibaca.</h3>
                            <p class="text-xs text-text-muted mt-1.5 mb-5 max-w-sm mx-auto">
                                Riwayat buku yang telah Anda kembalikan atau status pengajuan sebelumnya akan tercatat di sini.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-3xl border border-line bg-surface shadow-2xs">
                            <table class="w-full text-left text-xs text-ink">
                                <thead class="border-b border-line bg-surface-soft/60 uppercase tracking-wider text-[11px] text-text-muted font-semibold">
                                    <tr>
                                        <th class="px-6 py-4">Buku</th>
                                        <th class="px-6 py-4">Kode Transaksi</th>
                                        <th class="px-6 py-4">Tanggal Pinjam</th>
                                        <th class="px-6 py-4">Tanggal Kembali</th>
                                        <th class="px-6 py-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/60">
                                    @foreach ($historyBorrowings as $borrowing)
                                        @php
                                            $book = $borrowing->book ?? $borrowing->items->first()?->book;
                                        @endphp
                                        <tr class="hover:bg-surface-soft/30 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-sm text-ink">
                                                    @if ($book)
                                                        <a href="{{ route('books.show', $book) }}" class="hover:text-brand-600 dark:hover:text-brand-400">
                                                            {{ $book->title }}
                                                        </a>
                                                    @else
                                                        {{ $borrowing->items->first()?->book_title_snapshot ?? 'Buku' }}
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-text-muted mt-0.5">{{ $book?->author }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-[11px] text-text-muted">
                                                {{ $borrowing->transaction_code }}
                                            </td>
                                            <td class="px-6 py-4 text-text-muted">
                                                {{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('d M Y') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-text-muted">
                                                {{ $borrowing->returned_at ? $borrowing->returned_at->format('d M Y') : '-' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($borrowing->isReturned())
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Dikembalikan
                                                    </span>
                                                @elseif ($borrowing->isRejected())
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 dark:text-rose-400 font-bold text-[10px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Ditolak
                                                    </span>
                                                    @if ($borrowing->rejection_reason)
                                                        <p class="text-[10px] text-text-muted mt-1 italic">{{ $borrowing->rejection_reason }}</p>
                                                    @endif
                                                @elseif ($borrowing->isCancelled())
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-soft text-text-muted font-bold text-[10px]">
                                                        Dibatalkan
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($historyBorrowings->hasPages())
                            <div class="mt-8 flex justify-center">
                                {{ $historyBorrowings->links() }}
                            </div>
                        @endif
                    @endif

                <!-- ============================================== -->
                <!-- TAB 4: DAFTAR BACAAN / FAVORIT                 -->
                <!-- ============================================== -->
                @elseif ($tab === 'daftar-bacaan')
                    <div class="space-y-12 sm:space-y-14">
                        <!-- E-book / Keilmuan Section -->
                        <section aria-labelledby="ebooks-heading">
                            <div class="flex items-center gap-4">
                                <h2 id="ebooks-heading" class="shrink-0 font-serif text-2xl font-semibold text-ink sm:text-3xl">E-book / Keilmuan</h2>
                                <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                            </div>

                            @if (! $ebooks || $ebooks->isEmpty())
                                <p class="py-8 text-sm text-text-muted">Belum ada e-book yang disimpan.</p>
                            @else
                                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5 sm:gap-x-5">
                                    @foreach ($ebooks as $book)
                                        <div class="relative group">
                                            <x-book-card-ebook
                                                :book="$book"
                                                :href="route('books.show', $book)"
                                                :show-author="true"
                                                class="w-full"
                                            />
                                            <form method="POST" action="{{ route('books.favorite', $book) }}" class="absolute top-2 right-2 z-20">
                                                @csrf
                                                <button type="submit" title="Hapus dari daftar bacaan" class="p-1.5 rounded-full bg-surface/80 backdrop-blur-md border border-line text-text-muted hover:text-rose-600 hover:bg-rose-500/10 transition-colors shadow-xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                                @if (method_exists($ebooks, 'hasPages') && $ebooks->hasPages())
                                    <div class="mt-6">{{ $ebooks->links() }}</div>
                                @endif
                            @endif
                        </section>

                        <!-- Novel Section -->
                        <section aria-labelledby="novels-heading">
                            <div class="flex items-center gap-4">
                                <h2 id="novels-heading" class="shrink-0 font-serif text-2xl font-semibold text-ink sm:text-3xl">Novel</h2>
                                <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                            </div>

                            @if (! $novels || $novels->isEmpty())
                                <p class="py-8 text-sm text-text-muted">Belum ada novel yang disimpan.</p>
                            @else
                                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5 sm:gap-x-5">
                                    @foreach ($novels as $book)
                                        <div class="relative group">
                                            <x-book-card-ebook
                                                :book="$book"
                                                :href="route('books.show', $book)"
                                                cover-label="novel"
                                                :show-author="true"
                                                class="w-full"
                                            />
                                            <form method="POST" action="{{ route('books.favorite', $book) }}" class="absolute top-2 right-2 z-20">
                                                @csrf
                                                <button type="submit" title="Hapus dari daftar bacaan" class="p-1.5 rounded-full bg-surface/80 backdrop-blur-md border border-line text-text-muted hover:text-rose-600 hover:bg-rose-500/10 transition-colors shadow-xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                                @if (method_exists($novels, 'hasPages') && $novels->hasPages())
                                    <div class="mt-6">{{ $novels->links() }}</div>
                                @endif
                            @endif
                        </section>

                        <!-- Buku Fisik Section -->
                        @if ($physicalBooks && $physicalBooks->isNotEmpty())
                            <section aria-labelledby="physical-heading">
                                <div class="flex items-center gap-4">
                                    <h2 id="physical-heading" class="shrink-0 font-serif text-2xl font-semibold text-ink sm:text-3xl">Buku Fisik</h2>
                                    <span class="h-px flex-1 bg-line" aria-hidden="true"></span>
                                </div>
                                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5 sm:gap-x-5">
                                    @foreach ($physicalBooks as $book)
                                        <div class="relative group">
                                            <x-book-card-ebook
                                                :book="$book"
                                                :href="route('books.show', $book)"
                                                cover-label="buku fisik"
                                                :show-author="true"
                                                class="w-full"
                                            />
                                            <form method="POST" action="{{ route('books.favorite', $book) }}" class="absolute top-2 right-2 z-20">
                                                @csrf
                                                <button type="submit" title="Hapus dari daftar bacaan" class="p-1.5 rounded-full bg-surface/80 backdrop-blur-md border border-line text-text-muted hover:text-rose-600 hover:bg-rose-500/10 transition-colors shadow-xs cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-4-7 4V5z"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                                @if (method_exists($physicalBooks, 'hasPages') && $physicalBooks->hasPages())
                                    <div class="mt-6">{{ $physicalBooks->links() }}</div>
                                @endif
                            </section>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </main>

    <x-footer-public />

</body>
</html>

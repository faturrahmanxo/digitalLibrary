<x-layouts.app :title="$isAnggota ? 'Beranda' : 'Dashboard'">
    <x-slot:breadcrumbs>
        <span class="font-medium text-ink">{{ $isAnggota ? 'Beranda' : 'Dashboard' }}</span>
    </x-slot:breadcrumbs>

    @if ($isAnggota)
        <!-- ============================================================== -->
        <!-- SIVITAS AKADEMIKA MEMBER DASHBOARD (Personal Circulation Hub)  -->
        <!-- ============================================================== -->

        <!-- 1. Member Digital Card & Welcome Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-700 via-brand-600 to-brand-600 dark:from-brand-800 dark:via-brand-700 dark:to-brand-800 p-6 sm:p-8 text-white shadow-md mb-8 border border-white/20">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold text-brand-200 dark:text-brand-400 border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Akun Sivitas Akademika Aktif</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Selamat Datang, {{ auth()->user()->name }}! 👋
                    </h1>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-white/90 pt-1">
                        <span class="px-2.5 py-1 rounded-lg bg-black/25 font-mono">
                            ID: {{ $member?->member_code ?? ('MHS-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT)) }}
                        </span>
                        <span class="text-white/60">/</span>
                        <span>{{ auth()->user()->email }}</span>
                        <span class="text-white/60">/</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 font-semibold text-[10px]">
                            Status: Aktif Meminjam
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('home') }}" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-brand-700 hover:bg-brand-50 shadow-md transition-all cursor-pointer">
                        Jelajahi Portal Kampus &rarr;
                    </a>
                    <a href="{{ route('favorites.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white/15 backdrop-blur-md border border-white/25 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-all">
                        Buku Favorit ({{ $userFavoritesCount }})
                    </a>
                </div>
            </div>
            <!-- Subtle ambient glows -->
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-brand-400/20 blur-3xl pointer-events-none" aria-hidden="true"></div>
        </div>

        <!-- 2. Personal Member Circulation Metrics -->
        <section aria-label="Statistik Sirkulasi Pribadi" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-8">
            <!-- Metric 1: Buku Sedang Dipinjam -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-400 px-2.5 py-0.5 rounded-full border border-brand-200/50 mb-2">
                        Pinjaman Aktif
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Buku Sedang Dipinjam</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ $memberActiveCount }}
                        <span class="text-sm font-normal text-text-muted font-sans">eksemplar</span>
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Maksimal 3 judul per akun</span>
                    <span class="font-semibold text-brand-600 dark:text-brand-400">{{ 3 - min(3, $memberActiveCount) }} kuota tersisa</span>
                </p>
            </div>

            <!-- Metric 2: Tenggat Pengembalian Terdekat -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-amber-700 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-300 px-2.5 py-0.5 rounded-full border border-amber-200/50 mb-2">
                        Tenggat Terdekat
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Jatuh Tempo Pengembalian</p>
                    @if ($nextDueBorrowing)
                        @php
                            $daysLeft = now()->diffInDays($nextDueBorrowing->due_at, false);
                        @endphp
                        <p class="mt-1 text-xl sm:text-2xl font-extrabold text-ink tabular-nums tracking-tight">
                            {{ $nextDueBorrowing->due_at?->format('d M Y') }}
                        </p>
                        <p class="text-xs font-bold {{ $daysLeft < 0 ? 'text-danger-fg' : ($daysLeft <= 2 ? 'text-amber-600' : 'text-emerald-600') }} mt-0.5">
                            {{ $daysLeft < 0 ? 'Terlambat ' . abs($daysLeft) . ' hari' : ($daysLeft === 0 ? 'Jatuh tempo hari ini' : $daysLeft . ' hari tersisa') }}
                        </p>
                    @else
                        <p class="mt-1 text-2xl font-bold text-ink tracking-tight">
                            &mdash;
                        </p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">Tidak ada tunggakan pinjaman</p>
                    @endif
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Loket Sirkulasi Lt. 1</span>
                    <span class="text-text-muted">Pusat Kampus</span>
                </p>
            </div>

            <!-- Metric 3: Koleksi Favorit Disimpan -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-red-700 bg-red-50 dark:bg-red-950/40 dark:text-red-300 px-2.5 py-0.5 rounded-full border border-red-200/50 mb-2">
                        Daftar Bacaan
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Koleksi Buku Favorit</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ $userFavoritesCount }}
                        <span class="text-sm font-normal text-text-muted font-sans">buku</span>
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Disimpan untuk riset</span>
                    <a href="{{ route('favorites.index') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Buka Favorit &rarr;</a>
                </p>
            </div>

            <!-- Metric 4: Riwayat Peminjaman Selesai -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 px-2.5 py-0.5 rounded-full border border-emerald-200/50 mb-2">
                        Histori Sirkulasi
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Peminjaman Selesai</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ $memberHistoryCount }}
                        <span class="text-sm font-normal text-text-muted font-sans">transaksi</span>
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Total riwayat selesai</span>
                    <a href="{{ route('borrowings.index', ['status' => 'returned']) }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Lihat Riwayat &rarr;</a>
                </p>
            </div>
        </section>

        <!-- 3. Active Borrowings & Fast Book Access Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            
            <!-- Left: Active Loans Table (Col 8) -->
            <section aria-labelledby="active-loans-heading" class="lg:col-span-8 bg-surface rounded-2xl border border-line overflow-hidden shadow-xs flex flex-col justify-between">
                <div>
                    <div class="p-5 border-b border-line flex items-center justify-between gap-3 bg-surface-soft/40">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                            <h2 id="active-loans-heading" class="text-base font-bold text-ink">Buku yang Sedang Anda Pinjam</h2>
                        </div>
                        <a href="{{ route('borrowings.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                            Lihat Semua Riwayat &rarr;
                        </a>
                    </div>

                    @if ($activeBorrowings->isEmpty())
                        <div class="p-12 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-600 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-ink">Tidak Ada Buku yang Sedang Dipinjam</h3>
                            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
                                Anda sedang tidak membawa pinjaman buku fisik. Jelajahi katalog buku perpustakaan untuk meminjam koleksi referensi baru.
                            </p>
                            <a href="{{ route('home') }}#katalog" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                Cari Buku di Portal Kampus &rarr;
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-surface-soft/60 border-b border-line text-xs font-semibold text-text-muted">
                                        <th scope="col" class="py-3 px-4">Judul Buku</th>
                                        <th scope="col" class="py-3 px-4 whitespace-nowrap">Tgl Pinjam</th>
                                        <th scope="col" class="py-3 px-4 whitespace-nowrap">Jatuh Tempo</th>
                                        <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-sm">
                                    @foreach ($activeBorrowings as $loan)
                                        @foreach ($loan->items as $item)
                                            <tr class="hover:bg-brand-50/20 transition-colors">
                                                <td class="py-3.5 px-4">
                                                    <p class="font-semibold text-ink line-clamp-1">{{ $item->book?->title ?? 'Buku Perpustakaan' }}</p>
                                                    <p class="text-xs text-text-muted font-mono mt-0.5">{{ $loan->transaction_code }} / {{ $item->book?->author }}</p>
                                                </td>
                                                <td class="py-3.5 px-4 text-xs text-text-muted tabular-nums whitespace-nowrap">
                                                    {{ $loan->borrowed_at?->format('d M Y') }}
                                                </td>
                                                <td class="py-3.5 px-4 text-xs tabular-nums whitespace-nowrap">
                                                    <span class="font-semibold text-ink">{{ $loan->due_at?->format('d M Y') }}</span>
                                                    @if ($loan->isOverdue())
                                                        <span class="block text-[10px] text-danger-fg font-bold">Terlambat</span>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                                    <a href="{{ route('borrowings.show', $loan) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-line bg-surface hover:bg-surface-soft text-xs font-semibold text-brand-600 transition-colors">
                                                        Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Footer tip -->
                <div class="p-4 border-t border-line bg-surface-soft/40 text-[11px] text-text-muted flex items-center justify-between">
                    <span>Pengembalian buku fisik dilakukan di loket sirkulasi perpustakaan pusat lantai 1.</span>
                    <a href="{{ route('home') }}#jam-layanan" class="font-semibold text-brand-600 hover:underline">Jam Buka &rarr;</a>
                </div>
            </section>

            <!-- Right: Favorite Quick Access & Campus Rules (Col 4) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Favorite Books Preview -->
                <div class="bg-surface rounded-2xl border border-line p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                        <h3 class="text-sm font-bold text-ink">Buku Favorit Saya</h3>
                        <a href="{{ route('favorites.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Lihat Semua</a>
                    </div>

                    @if ($favoriteBooks->isEmpty())
                        <p class="text-xs text-text-muted py-4 text-center">Belum ada buku favorit yang disimpan.</p>
                        <a href="{{ route('home') }}#katalog" class="block w-full text-center py-2 rounded-xl border border-line text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors">
                            Jelajahi Katalog
                        </a>
                    @else
                        <div class="space-y-3">
                            @foreach ($favoriteBooks as $favBook)
                                <div class="flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-surface-soft transition-colors">
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-ink truncate">{{ $favBook->title }}</h4>
                                        <p class="text-[11px] text-text-muted truncate">{{ $favBook->author }}</p>
                                    </div>
                                    <a href="{{ route('books.read', $favBook) }}" class="px-2.5 py-1 rounded-lg bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400 text-xs font-semibold shrink-0 hover:bg-brand-100 transition-colors">
                                        Baca
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Academic Guidance Card -->
                <div class="bg-surface rounded-2xl border border-line p-5 shadow-xs space-y-3">
                    <h3 class="text-sm font-bold text-ink">Panduan Sirkulasi Sivitas</h3>
                    <ul class="space-y-2 text-xs text-text-muted">
                        <li class="flex items-start gap-2">
                            <span class="text-brand-600 font-bold">&ndash;</span>
                            <span>Maksimal peminjaman <strong>3 eksemplar buku</strong> sekaligus.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-brand-600 font-bold">&ndash;</span>
                            <span>Pilihan durasi peminjaman: <strong>3, 7, 14, 21, hingga 30 hari</strong>.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-brand-600 font-bold">&ndash;</span>
                            <span>Tunjukkan kartu anggota digital atau KTM saat pengambilan di konter sirkulasi.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>

    @else
        <!-- ============================================================== -->
        <!-- STAFF & ADMIN OPERATIONAL DASHBOARD (Litera Internal Management) -->
        <!-- ============================================================== -->

        <!-- Modern Welcome Hero Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-800 via-brand-600 to-brand-400 p-6 sm:p-8 text-white shadow-md mb-8 border border-white/20">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold text-white border border-white/30 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        <span>Litera Sirkulasi Perpustakaan Aktif</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Halo, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-sm text-brand-50 leading-relaxed font-normal">
                        Kelola sirkulasi buku, monitor ketersediaan eksemplar, dan pantau pengembalian tepat waktu secara akurat dari satu dasbor terpadu.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" id="btn-open-dashboard-borrow-modal" data-testid="dashboard-create-borrowing" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-brand-700 dark:text-brand-700 hover:bg-brand-50 shadow-md transition-all duration-150 cursor-pointer">
                        Catat Peminjaman Baru
                    </button>
                    <a href="{{ route('borrowings.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white/15 backdrop-blur-md border border-white/30 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-all duration-150">
                        Lihat Riwayat
                    </a>
                </div>
            </div>
            <!-- Decorative subtle ambient glow -->
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-brand-400/20 blur-3xl pointer-events-none" aria-hidden="true"></div>
        </div>

        <!-- 4 Mandatory KPI Stat Cards (FR-008 & ADR-009) -->
        <section aria-label="Indikator Kinerja Utama Perpustakaan" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-8">
            <!-- KPI 1: Jumlah Judul Buku -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-book-titles">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-400 dark:border-brand-400/30 px-2.5 py-0.5 rounded-full border border-brand-200/50 mb-2">
                            Katalog Aktif
                        </span>
                        <p class="text-xs font-semibold text-text-muted">Jumlah Judul Buku</p>
                        <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                            {{ number_format($totalBookTitles, 0, ',', '.') }}
                            <span class="text-sm font-normal text-text-muted font-sans">judul</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-600/20 border border-brand-100 dark:border-brand-400/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Koleksi judul buku aktif</span>
                    <a href="{{ route('books.index') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Kelola &rarr;</a>
                </p>
            </div>

            <!-- KPI 2: Stok Buku Tersedia -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-available-stock">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-400 dark:border-brand-400/30 px-2.5 py-0.5 rounded-full border border-brand-200/50 mb-2">
                            Siap Dipinjam
                        </span>
                        <p class="text-xs font-semibold text-text-muted">Stok Buku Tersedia</p>
                        <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                            {{ number_format($totalAvailableStock, 0, ',', '.') }}
                            <span class="text-sm font-normal text-text-muted font-sans">eksemplar</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-600/20 border border-brand-100 dark:border-brand-400/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Eksemplar fisik di rak</span>
                    <span class="text-brand-600 dark:text-brand-400 font-semibold">Tersedia</span>
                </p>
            </div>

            <!-- KPI 3: Jumlah Anggota Aktif -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-active-members">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-400 dark:border-brand-400/30 px-2.5 py-0.5 rounded-full border border-brand-200/50 mb-2">
                            Peminjam Aktif
                        </span>
                        <p class="text-xs font-semibold text-text-muted">Jumlah Anggota Aktif</p>
                        <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                            {{ number_format($totalActiveMembers, 0, ',', '.') }}
                            <span class="text-sm font-normal text-text-muted font-sans">anggota</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-600/20 border border-brand-100 dark:border-brand-400/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Dapat meminjam koleksi</span>
                    <a href="{{ route('members.index') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Kelola &rarr;</a>
                </p>
            </div>

            <!-- KPI 4: Buku Sedang Dipinjam -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-borrowed-copies">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-400 dark:border-brand-400/30 px-2.5 py-0.5 rounded-full border border-brand-200/50 mb-2">
                            Dalam Sirkulasi
                        </span>
                        <p class="text-xs font-semibold text-text-muted">Buku Sedang Dipinjam</p>
                        <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                            {{ number_format($totalBorrowedCopies, 0, ',', '.') }}
                            <span class="text-sm font-normal text-text-muted font-sans">eksemplar</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-600/20 border border-brand-100 dark:border-brand-400/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Total eksemplar di peminjam</span>
                    <a href="{{ route('borrowings.index', ['status' => 'borrowed']) }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Pantau &rarr;</a>
                </p>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 2-GENRE TRACKING & ANALYTICS: Novel & Fiksi vs Keilmuan & Edukasi -->
        <!-- ============================================================== -->
        <section aria-label="Tracking Dua Genre Utama" class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <div class="inline-flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                        <h2 class="text-base font-bold text-ink">Tracking 2 Genre Utama</h2>
                    </div>
                    <p class="text-xs text-text-muted mt-0.5 font-sans">
                        Pemantauan sirkulasi koleksi genre Novel &amp; Fiksi dan Keilmuan &amp; Edukasi.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('catalog.index', ['genre' => 'novel-fiksi']) }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                        Katalog Novel &rarr;
                    </a>
                    <span class="text-line text-xs">&bull;</span>
                    <a href="{{ route('catalog.index', ['genre' => 'keilmuan-edukasi']) }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                        Katalog Keilmuan &rarr;
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Genre Card 1: Novel & Fiksi -->
                <div class="card-interactive bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs hover:border-amber-400/60 dark:hover:border-amber-400/40 flex flex-col justify-between">
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-start justify-between gap-3 pb-4 border-b border-line">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20 shadow-xs shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-700 dark:text-amber-300 text-[10px] font-bold uppercase tracking-wider font-sans mb-1">
                                        {{ $genreTracking['novel']['badge'] }}
                                    </span>
                                    <h3 class="text-base font-bold text-ink leading-tight font-sans">
                                        {{ $genreTracking['novel']['name'] }}
                                    </h3>
                                </div>
                            </div>
                            <span class="font-year font-bold text-lg text-amber-600 dark:text-amber-400">
                                {{ $genreTracking['novel']['rate'] }}%
                                <span class="text-[10px] font-sans font-normal text-text-muted block text-right">Tersedia</span>
                            </span>
                        </div>

                        <!-- Progress Bar Ketersediaan -->
                        <div class="my-3.5">
                            <div class="w-full h-2 rounded-full bg-surface-soft overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: {{ $genreTracking['novel']['rate'] }}%"></div>
                            </div>
                        </div>

                        <!-- 3 Mini Stats -->
                        <div class="grid grid-cols-3 gap-2 py-3 bg-surface-soft/60 rounded-xl px-3 border border-line/60 text-center">
                            <div>
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Judul</span>
                                <span class="font-italiana font-bold text-lg text-ink">{{ $genreTracking['novel']['titles'] }}</span>
                            </div>
                            <div class="border-x border-line/60">
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Tersedia</span>
                                <span class="font-year font-bold text-lg text-emerald-600 dark:text-emerald-400">{{ $genreTracking['novel']['available'] }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Dipinjam</span>
                                <span class="font-year font-bold text-lg text-amber-600 dark:text-amber-400">{{ $genreTracking['novel']['borrowed'] }}</span>
                            </div>
                        </div>

                        <!-- Sample Titles in this Genre -->
                        <div class="mt-4 space-y-2">
                            <span class="text-[11px] font-bold text-text-muted uppercase tracking-wider font-sans block">Koleksi Terkait:</span>
                            @forelse ($genreTracking['novel']['books'] as $nBook)
                                <div class="flex items-center justify-between text-xs py-1 border-b border-line/40 last:border-0">
                                    <span class="font-medium text-ink truncate max-w-[220px] sm:max-w-[280px]" title="{{ $nBook->title }}">{{ $nBook->title }}</span>
                                    <span class="text-[11px] text-text-muted shrink-0">{{ $nBook->stock_available }} eks</span>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic py-1">Belum ada buku di kategori ini.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-4 mt-3 border-t border-line flex items-center justify-between">
                        <span class="text-xs text-text-muted">Total Fisik: {{ $genreTracking['novel']['total_stock'] }} eksemplar</span>
                        <a href="{{ route('catalog.index', ['genre' => 'novel-fiksi']) }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1 font-sans">
                            <span>Buka Genre</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Genre Card 2: Keilmuan & Edukasi -->
                <div class="card-interactive bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs hover:border-brand-600 dark:hover:border-brand-400 flex flex-col justify-between">
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-start justify-between gap-3 pb-4 border-b border-line">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center border border-brand-500/20 shadow-xs shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-brand-500/10 text-brand-700 dark:text-brand-300 text-[10px] font-bold uppercase tracking-wider font-sans mb-1">
                                        {{ $genreTracking['keilmuan']['badge'] }}
                                    </span>
                                    <h3 class="text-base font-bold text-ink leading-tight font-sans">
                                        {{ $genreTracking['keilmuan']['name'] }}
                                    </h3>
                                </div>
                            </div>
                            <span class="font-year font-bold text-lg text-brand-600 dark:text-brand-400">
                                {{ $genreTracking['keilmuan']['rate'] }}%
                                <span class="text-[10px] font-sans font-normal text-text-muted block text-right">Tersedia</span>
                            </span>
                        </div>

                        <!-- Progress Bar Ketersediaan -->
                        <div class="my-3.5">
                            <div class="w-full h-2 rounded-full bg-surface-soft overflow-hidden">
                                <div class="h-full bg-brand-600 dark:bg-brand-400 rounded-full transition-all duration-500" style="width: {{ $genreTracking['keilmuan']['rate'] }}%"></div>
                            </div>
                        </div>

                        <!-- 3 Mini Stats -->
                        <div class="grid grid-cols-3 gap-2 py-3 bg-surface-soft/60 rounded-xl px-3 border border-line/60 text-center">
                            <div>
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Judul</span>
                                <span class="font-italiana font-bold text-lg text-ink">{{ $genreTracking['keilmuan']['titles'] }}</span>
                            </div>
                            <div class="border-x border-line/60">
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Tersedia</span>
                                <span class="font-year font-bold text-lg text-brand-700 dark:text-brand-300">{{ $genreTracking['keilmuan']['available'] }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-text-muted uppercase font-semibold font-sans block">Dipinjam</span>
                                <span class="font-year font-bold text-lg text-brand-600 dark:text-brand-400">{{ $genreTracking['keilmuan']['borrowed'] }}</span>
                            </div>
                        </div>

                        <!-- Sample Titles in this Genre -->
                        <div class="mt-4 space-y-2">
                            <span class="text-[11px] font-bold text-text-muted uppercase tracking-wider font-sans block">Koleksi Terkait:</span>
                            @forelse ($genreTracking['keilmuan']['books'] as $kBook)
                                <div class="flex items-center justify-between text-xs py-1 border-b border-line/40 last:border-0">
                                    <span class="font-medium text-ink truncate max-w-[220px] sm:max-w-[280px]" title="{{ $kBook->title }}">{{ $kBook->title }}</span>
                                    <span class="text-[11px] text-text-muted shrink-0">{{ $kBook->stock_available }} eks</span>
                                </div>
                            @empty
                                <p class="text-xs text-text-muted italic py-1">Belum ada buku di kategori ini.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-4 mt-3 border-t border-line flex items-center justify-between">
                        <span class="text-xs text-text-muted">Total Fisik: {{ $genreTracking['keilmuan']['total_stock'] }} eksemplar</span>
                        <a href="{{ route('catalog.index', ['genre' => 'keilmuan-edukasi']) }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1 font-sans">
                            <span>Buka Genre</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Borrowings Panel (Staff View) - Full Width Expansion -->
        <div class="w-full">
            <section aria-labelledby="recent-borrowings-heading" class="w-full bg-surface rounded-2xl border border-line overflow-hidden shadow-xs">
                <div class="p-5 border-b border-line flex items-center justify-between gap-2 bg-surface-soft/40">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                        <h2 id="recent-borrowings-heading" class="text-base font-bold text-ink">Riwayat Peminjaman Terbaru</h2>
                    </div>
                    <a href="{{ route('borrowings.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1">
                        <span>Semua Riwayat</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                @if ($recentBorrowings->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-ink">Belum ada riwayat transaksi peminjaman.</p>
                        <p class="mt-1 text-xs text-text-muted">Catatan peminjaman akan otomatis tampil saat ada peminjaman.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-soft/60 border-b border-line text-xs font-semibold text-text-muted">
                                    <th scope="col" class="py-3 px-4 whitespace-nowrap">Kode</th>
                                    <th scope="col" class="py-3 px-4 min-w-[140px]">Anggota</th>
                                    <th scope="col" class="py-3 px-4 whitespace-nowrap">Jatuh Tempo</th>
                                    <th scope="col" class="py-3 px-4 text-right whitespace-nowrap">Jumlah</th>
                                    <th scope="col" class="py-3 px-4 whitespace-nowrap">Status</th>
                                    <th scope="col" class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-sm">
                                @foreach ($recentBorrowings as $loan)
                                    <tr class="hover:bg-brand-50/20 transition-colors">
                                        <td class="py-3.5 px-4 font-mono text-xs font-semibold text-ink whitespace-nowrap">
                                            <a href="{{ route('borrowings.show', $loan) }}" class="hover:text-brand-600">
                                                {{ $loan->transaction_code }}
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-brand-50 dark:bg-brand-600/25 border border-brand-100 dark:border-brand-400/30 text-brand-700 dark:text-brand-400 font-bold text-[11px] flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($loan->member_name_snapshot, 0, 2)) }}
                                                </div>
                                                <span class="font-medium text-ink truncate">{{ $loan->member_name_snapshot }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-text-muted tabular-nums whitespace-nowrap text-xs">
                                            {{ $loan->due_at?->format('d M Y') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right tabular-nums whitespace-nowrap text-xs font-semibold text-ink">
                                            {{ $loan->total_quantity }} <span class="text-text-muted font-normal">eks</span>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @if ($loan->isBorrowed())
                                                    <x-status-badge variant="info">Dipinjam</x-status-badge>
                                                    @if ($loan->isOverdue())
                                                        <x-status-badge variant="warning">Terlambat</x-status-badge>
                                                    @endif
                                                @else
                                                    <x-status-badge variant="success">Dikembalikan</x-status-badge>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-2">
                                                @can('return', $loan)
                                                    @if ($loan->isBorrowed())
                                                        <button
                                                            type="button"
                                                            class="btn-open-return-modal inline-flex items-center px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                            data-loan-code="{{ $loan->transaction_code }}"
                                                            data-member-name="{{ $loan->member_name_snapshot }}"
                                                            data-quantity="{{ $loan->total_quantity }}"
                                                            data-action-url="{{ route('borrowings.return', $loan) }}"
                                                            title="Kembalikan Buku"
                                                        >
                                                            Kembalikan
                                                        </button>
                                                    @endif
                                                @endcan
                                                <a href="{{ route('borrowings.show', $loan) }}" class="inline-flex items-center px-2.5 py-1 rounded-md border border-line bg-surface text-xs font-semibold text-brand-600 hover:bg-brand-50 hover:border-brand-200 transition-colors">
                                                    Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- POPUP CATAT PEMINJAMAN DASHBOARD (Latar Digelapin)      -->
    <!-- ======================================================== -->
    @can('create', \App\Models\Borrowing::class)
        <div id="dashboard-borrow-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="dash-modal-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <!-- Darkened Backdrop -->
                <div id="dashboard-borrow-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div>
                            <h3 id="dash-modal-title" class="text-lg font-bold text-ink">Catat Peminjaman Buku</h3>
                            <p class="text-xs text-text-muted mt-0.5">Pilih anggota dan koleksi buku yang dipinjam.</p>
                        </div>
                        <button type="button" id="btn-close-dash-modal" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                    <form id="dashboard-borrow-form" method="POST" action="{{ route('borrowings.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                        @if (!auth()->user()?->isAnggota())
                            <div>
                                <label for="dash-member-select" class="block text-xs font-semibold text-ink mb-1.5">
                                    Anggota Peminjam <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    id="dash-member-select"
                                    name="member_id"
                                    required
                                    class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                                >
                                    <option value="">-- Pilih Anggota Peminjam --</option>
                                    @foreach ($allMembers ?? [] as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->member_code }})</option>
                                    @endforeach
                                </select>
                                <p id="err-dash-member" class="hidden text-xs text-rose-500 font-medium mt-1">Silakan pilih anggota peminjam.</p>
                            </div>
                        @else
                            <div class="p-3.5 rounded-xl bg-surface-soft border border-line text-xs space-y-1">
                                <p class="font-semibold text-ink">Informasi Akun Peminjam</p>
                                <p class="text-text-muted">Nama: <span class="font-medium text-ink">{{ auth()->user()->name }}</span></p>
                            </div>
                        @endif

                        <div>
                            <label for="dash-book-select" class="block text-xs font-semibold text-ink mb-1.5">
                                Koleksi Buku <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="dash-book-select"
                                name="book_id"
                                required
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                                <option value="">-- Pilih Buku yang Tersedia --</option>
                                @foreach ($availableBooks ?? [] as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }} (Sisa: {{ $b->stock_available }} eks)</option>
                                @endforeach
                            </select>
                            <p id="err-dash-book" class="hidden text-xs text-rose-500 font-medium mt-1">Silakan pilih buku yang ingin dipinjam.</p>
                        </div>

                        <!-- Duration Option -->
                        <div>
                            <label for="dash-duration-select" class="block text-xs font-semibold text-ink mb-1.5">
                                Pilihan Waktu Peminjaman <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="dash-duration-select"
                                name="duration_days"
                                class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer"
                            >
                                <option value="3">3 Hari (Peminjaman Singkat)</option>
                                <option value="7" selected>7 Hari (1 Minggu — Standar)</option>
                                <option value="14">14 Hari (2 Minggu)</option>
                                <option value="21">21 Hari (3 Minggu)</option>
                                <option value="30">30 Hari (1 Bulan)</option>
                            </select>
                        </div>

                        <div class="p-3.5 rounded-xl bg-surface-soft border border-line text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-text-muted">Batas Pengembalian:</span>
                                <span id="dash-due-date-preview" class="font-bold text-brand-600 tabular-nums">{{ \Carbon\Carbon::now('Asia/Jakarta')->addDays(7)->translatedFormat('d F Y') }}</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                id="btn-cancel-dash-modal"
                                class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-submit-dash-modal"
                                class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Konfirmasi Pinjam
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- Quick Return Modal for Dashboard -->
    <div id="dash-quick-return-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="dash-return-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div id="dash-quick-return-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="dash-return-title" class="text-base font-bold text-ink">
                                Konfirmasi Pengembalian
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">Sirkulasi penerimaan buku perpustakaan.</p>
                        </div>
                    </div>
                    <button type="button" id="btn-close-dash-return" class="px-2.5 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        &times;
                    </button>
                </div>

                <form id="dash-quick-return-form" method="POST" action="" class="space-y-4 mt-4">
                    @csrf

                    <div class="rounded-xl border border-line bg-surface-soft/40 p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">No. Transaksi</span>
                            <span id="dash-return-code" class="font-mono font-bold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">Nama Peminjam</span>
                            <span id="dash-return-member" class="font-semibold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">Total Eksemplar</span>
                            <span id="dash-return-quantity" class="font-bold text-emerald-600">-</span>
                        </div>
                    </div>

                    <p class="text-xs text-text-muted leading-relaxed">
                        Dengan menekan tombol di bawah, status transaksi akan ditandai <strong>Sudah Dikembalikan</strong> dan stok buku fisik akan otomatis bertambah kembali ke sistem katalog.
                    </p>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                        <button type="button" id="btn-cancel-dash-return" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-dash-return" class="min-h-10 px-5 py-2 rounded-control bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                            Ya, Kembalikan Buku
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick Return Modal for Dashboard
            const dashReturnModal = document.getElementById('dash-quick-return-modal');
            const dashReturnBackdrop = document.getElementById('dash-quick-return-backdrop');
            const dashReturnCloseBtn = document.getElementById('btn-close-dash-return');
            const dashReturnCancelBtn = document.getElementById('btn-cancel-dash-return');
            const dashReturnForm = document.getElementById('dash-quick-return-form');
            const dashReturnSubmit = document.getElementById('btn-submit-dash-return');
            const dashReturnCode = document.getElementById('dash-return-code');
            const dashReturnMember = document.getElementById('dash-return-member');
            const dashReturnQuantity = document.getElementById('dash-return-quantity');
            const dashReturnButtons = document.querySelectorAll('.btn-open-return-modal');

            function openDashReturnModal(btn) {
                const actionUrl = btn.getAttribute('data-action-url');
                const loanCode = btn.getAttribute('data-loan-code') || '-';
                const memberName = btn.getAttribute('data-member-name') || '-';
                const quantity = btn.getAttribute('data-quantity') || '1';

                if (dashReturnForm) dashReturnForm.action = actionUrl;
                if (dashReturnCode) dashReturnCode.textContent = loanCode;
                if (dashReturnMember) dashReturnMember.textContent = memberName;
                if (dashReturnQuantity) dashReturnQuantity.textContent = quantity + ' eksemplar';

                if (dashReturnModal) dashReturnModal.classList.remove('hidden');
            }

            function closeDashReturnModal() {
                if (dashReturnModal) dashReturnModal.classList.add('hidden');
            }

            dashReturnButtons.forEach(btn => {
                btn.addEventListener('click', () => openDashReturnModal(btn));
            });

            if (dashReturnCloseBtn) dashReturnCloseBtn.addEventListener('click', closeDashReturnModal);
            if (dashReturnCancelBtn) dashReturnCancelBtn.addEventListener('click', closeDashReturnModal);
            if (dashReturnBackdrop) dashReturnBackdrop.addEventListener('click', closeDashReturnModal);

            if (dashReturnForm && dashReturnSubmit) {
                dashReturnForm.addEventListener('submit', function () {
                    dashReturnSubmit.disabled = true;
                    dashReturnSubmit.textContent = 'Memproses Pengembalian...';
                    dashReturnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }

            const dashOpenBtn = document.getElementById('btn-open-dashboard-borrow-modal');
            const dashModal = document.getElementById('dashboard-borrow-modal');
            const dashCloseBtn = document.getElementById('btn-close-dash-modal');
            const dashCancelBtn = document.getElementById('btn-cancel-dash-modal');
            const dashBackdrop = document.getElementById('dashboard-borrow-backdrop');
            const dashForm = document.getElementById('dashboard-borrow-form');
            const dashSubmit = document.getElementById('btn-submit-dash-modal');

            function openDashModal() {
                if (dashModal) dashModal.classList.remove('hidden');
            }

            function closeDashModal() {
                if (dashModal) dashModal.classList.add('hidden');
            }

            function formatIndoDate(daysToAdd) {
                const d = new Date();
                d.setDate(d.getDate() + parseInt(daysToAdd, 10));
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
            }

            const dashDuration = document.getElementById('dash-duration-select');
            const dashDuePreview = document.getElementById('dash-due-date-preview');
            if (dashDuration && dashDuePreview) {
                dashDuration.addEventListener('change', function () {
                    dashDuePreview.textContent = formatIndoDate(this.value);
                });
            }

            if (dashOpenBtn) dashOpenBtn.addEventListener('click', openDashModal);
            if (dashCloseBtn) dashCloseBtn.addEventListener('click', closeDashModal);
            if (dashCancelBtn) dashCancelBtn.addEventListener('click', closeDashModal);
            if (dashBackdrop) dashBackdrop.addEventListener('click', closeDashModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && dashModal && !dashModal.classList.contains('hidden')) {
                    closeDashModal();
                }
            });

            if (dashForm && dashSubmit) {
                dashForm.addEventListener('submit', function (e) {
                    const memberSelect = document.getElementById('dash-member-select');
                    const errMember = document.getElementById('err-dash-member');
                    const bookSelect = document.getElementById('dash-book-select');
                    const errBook = document.getElementById('err-dash-book');

                    let hasError = false;
                    if (memberSelect && !memberSelect.value) {
                        e.preventDefault();
                        if (errMember) errMember.classList.remove('hidden');
                        memberSelect.classList.add('border-rose-500');
                        memberSelect.focus();
                        hasError = true;
                    }

                    if (bookSelect && !bookSelect.value) {
                        e.preventDefault();
                        if (errBook) errBook.classList.remove('hidden');
                        bookSelect.classList.add('border-rose-500');
                        if (!hasError) bookSelect.focus();
                        hasError = true;
                    }

                    if (hasError) return;

                    dashSubmit.disabled = true;
                    dashSubmit.textContent = 'Memproses Peminjaman...';
                    dashSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }

            // Connect "Pinjam" buttons in member books grid
            document.querySelectorAll('.btn-trigger-borrow-book').forEach(btn => {
                btn.addEventListener('click', function () {
                    const bookId = this.dataset.bookId;
                    const bookSelect = document.getElementById('dash-book-select');
                    if (bookSelect && bookId) {
                        bookSelect.value = bookId;
                    }
                    if (dashModal) dashModal.classList.remove('hidden');
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>

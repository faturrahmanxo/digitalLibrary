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
                        <svg class="w-2.5 h-2.5 fill-current text-emerald-300 animate-pulse shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
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
                    <span>Maksimal 3 eksemplar</span>
                    <span class="font-medium text-brand-600 dark:text-brand-400">Aktif</span>
                </p>
            </div>

            <!-- Metric 2: Jatuh Tempo Terdekat -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-amber-700 bg-amber-50 dark:bg-amber-900/40 dark:text-amber-400 px-2.5 py-0.5 rounded-full border border-amber-200/50 mb-2">
                        Batas Waktu
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Jatuh Tempo Pengembalian</p>
                    <p class="mt-1 text-xl font-bold text-ink tabular-nums tracking-tight">
                        @if ($nextDueBorrowing && $nextDueBorrowing->due_at)
                            {{ $nextDueBorrowing->due_at->translatedFormat('d M Y') }}
                        @else
                            <span class="text-text-muted font-normal text-base">Tidak ada pinjaman</span>
                        @endif
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5">
                    @if ($nextDueBorrowing && $nextDueBorrowing->isOverdue())
                        <span class="text-rose-600 font-semibold">Telah melewati batas waktu!</span>
                    @elseif ($nextDueBorrowing && $nextDueBorrowing->due_at)
                        <span class="text-amber-600 font-medium">{{ \Carbon\Carbon::now('Asia/Jakarta')->diffInDays($nextDueBorrowing->due_at, false) >= 0 ? \Carbon\Carbon::now('Asia/Jakarta')->diffInDays($nextDueBorrowing->due_at) . ' hari lagi' : 'Hari ini' }}</span>
                    @else
                        <span>Tertib administrasi</span>
                    @endif
                </p>
            </div>

            <!-- Metric 3: Total Riwayat Pengembalian -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-900/40 dark:text-emerald-400 px-2.5 py-0.5 rounded-full border border-emerald-200/50 mb-2">
                        Histori Sukses
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Total Pengembalian</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ $memberHistoryCount }}
                        <span class="text-sm font-normal text-text-muted font-sans">kali</span>
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Riwayat peminjaman selesai</span>
                    <a href="{{ route('collection.index') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Detail &rarr;</a>
                </p>
            </div>

            <!-- Metric 4: Koleksi Favorit Tersimpan -->
            <div class="card-interactive bg-surface rounded-2xl border border-line p-5.5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[11px] font-semibold text-rose-700 bg-rose-50 dark:bg-rose-900/40 dark:text-rose-400 px-2.5 py-0.5 rounded-full border border-rose-200/50 mb-2">
                        Daftar Bacaan
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Buku Disimpan / Favorit</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ $userFavoritesCount }}
                        <span class="text-sm font-normal text-text-muted font-sans">buku</span>
                    </p>
                </div>
                <p class="mt-3 text-xs text-text-muted border-t border-line/60 pt-2.5 flex items-center justify-between">
                    <span>Koleksi yang ditandai</span>
                    <a href="{{ route('favorites.index') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Buka &rarr;</a>
                </p>
            </div>
        </section>

        <!-- 3. Active Loans & Recent Returns Table -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            <section aria-labelledby="active-loans-heading" class="lg:col-span-8 bg-surface rounded-2xl border border-line overflow-hidden shadow-xs flex flex-col justify-between">
                <div>
                    <div class="p-5 border-b border-line flex items-center justify-between gap-2 bg-surface-soft/40">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-2.5 h-2.5 fill-current text-brand-600 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            <h2 id="active-loans-heading" class="text-base font-bold text-ink">Buku yang Sedang Anda Pinjam</h2>
                        </div>
                        <a href="{{ route('collection.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1">
                            <span>Koleksi Saya</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    @if ($activeBorrowings->isEmpty())
                        <div class="p-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-ink">Anda tidak memiliki pinjaman aktif saat ini.</p>
                            <p class="mt-1 text-xs text-text-muted">Jelajahi koleksi kami dan pinjam buku fisik atau baca e-book kapan saja.</p>
                            <div class="mt-4">
                                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors">
                                    Mulai Cari Buku
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-surface-soft/60 border-b border-line text-xs font-semibold text-text-muted">
                                        <th class="py-3 px-4">Buku</th>
                                        <th class="py-3 px-4 whitespace-nowrap">Tanggal Pinjam</th>
                                        <th class="py-3 px-4 whitespace-nowrap">Jatuh Tempo</th>
                                        <th class="py-3 px-4 whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line text-sm">
                                    @foreach ($activeBorrowings as $loan)
                                        @foreach ($loan->items as $item)
                                            <tr class="hover:bg-brand-50/20 transition-colors">
                                                <td class="py-3.5 px-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-14 bg-surface-soft rounded-lg overflow-hidden shrink-0 border border-line shadow-xs">
                                                            @if ($item->book?->cover_path)
                                                                <img src="{{ asset('storage/' . $item->book->cover_path) }}" alt="{{ $item->book->title }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex items-center justify-center text-text-muted">
                                                                    <svg class="w-5 h-5 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('books.show', $item->book) }}" class="font-bold text-ink hover:text-brand-600 line-clamp-1">
                                                                {{ $item->book?->title ?? 'Judul Tidak Tersedia' }}
                                                            </a>
                                                            <p class="text-xs text-text-muted mt-0.5">{{ $item->book?->author }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3.5 px-4 text-xs text-text-muted tabular-nums whitespace-nowrap">
                                                    {{ $loan->borrowed_at?->translatedFormat('d M Y') }}
                                                </td>
                                                <td class="py-3.5 px-4 text-xs tabular-nums whitespace-nowrap font-medium {{ $loan->isOverdue() ? 'text-rose-600 font-bold' : 'text-ink' }}">
                                                    {{ $loan->due_at?->translatedFormat('d M Y') }}
                                                </td>
                                                <td class="py-3.5 px-4 whitespace-nowrap">
                                                    @if ($loan->isOverdue())
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                            Terlambat
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            Dipinjam
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Right Column: Quick Reading List / Favorites -->
            <section aria-labelledby="favorite-books-heading" class="lg:col-span-4 bg-surface rounded-2xl border border-line p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-rose-500 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            <h2 id="favorite-books-heading" class="text-sm font-bold text-ink">Daftar Bacaan Favorit</h2>
                        </div>
                        <a href="{{ route('favorites.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">Semua</a>
                    </div>

                    @if ($favoriteBooks->isEmpty())
                        <div class="py-8 text-center text-xs text-text-muted">
                            <p>Belum ada buku dalam favorit Anda.</p>
                            <p class="mt-1">Tandai buku favorit di halaman katalog!</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($favoriteBooks as $fav)
                                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-surface-soft transition-colors">
                                    <div class="w-9 h-12 bg-surface-soft rounded border border-line overflow-hidden shrink-0">
                                        @if ($fav->cover_path)
                                            <img src="{{ asset('storage/' . $fav->cover_path) }}" alt="{{ $fav->title }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('books.show', $fav) }}" class="text-xs font-bold text-ink hover:text-brand-600 truncate block">
                                            {{ $fav->title }}
                                        </a>
                                        <p class="text-[11px] text-text-muted truncate">{{ $fav->author }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

    @else
        <!-- ============================================================== -->
        <!-- STAFF & ADMIN OPERATIONAL & ANALYTICS DASHBOARD (Sections 25 & 26) -->
        <!-- ============================================================== -->

        <!-- Modern Glassmorphism Welcome Hero Banner (Litera Forest Green & Ivory) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1b4332] via-[#21503c] to-[#122e22] dark:from-[#133023] dark:via-[#193d2c] dark:to-[#0c1f16] border border-emerald-400/30 dark:border-emerald-500/20 p-5 sm:p-7 text-white shadow-xl shadow-emerald-950/20 mb-6 backdrop-blur-xl">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-1.5 max-w-xl">
                    <p class="text-xs sm:text-sm font-semibold tracking-wider text-emerald-200/90 dark:text-emerald-300/90 uppercase font-sans">
                        {{ $isAdmin ? 'Admin Dashboard — Sistem Sirkulasi Lengkap' : 'Dashboard Petugas — Operasional Sirkulasi' }}
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight font-sans drop-shadow-xs">
                        Halo, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-sm text-emerald-100/95 dark:text-emerald-100/90 leading-relaxed font-normal">
                        {{ $isAdmin ? 'Pantau metrik perpustakaan, analitik sirkulasi, ketersediaan inventaris, dan aktivitas peminjaman secara menyeluruh.' : 'Kelola sirkulasi operasional, validasi pengajuan buku, konfirmasi pengambilan, dan pantau pengembalian harian.' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
                    <button type="button" id="btn-open-dashboard-borrow-modal" data-testid="dashboard-create-borrowing" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-emerald-950 hover:bg-emerald-50 shadow-md hover:shadow-lg transition-all duration-150 cursor-pointer">
                        <svg class="w-4 h-4 mr-2 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Catat Peminjaman
                    </button>
                    <a href="{{ route('borrowings.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 px-4 sm:px-5 py-2.5 text-sm font-semibold text-white transition-all duration-150 shadow-xs">
                        Sirkulasi Buku
                    </a>
                    <a href="{{ route('books.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 px-4 sm:px-5 py-2.5 text-sm font-semibold text-white transition-all duration-150 shadow-xs">
                        Katalog Buku
                    </a>
                    <a href="#pengaturan-akun" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 px-3.5 sm:px-4 py-2.5 text-sm font-semibold text-emerald-200 hover:text-white transition-all duration-150 shadow-xs gap-1.5" title="Pengaturan Akun (Profil, Keamanan, Tampilan)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="hidden xl:inline">Pengaturan</span>
                    </a>
                </div>
            </div>
            <!-- Decorative subtle ambient glow -->
            <div class="absolute -right-12 -top-12 w-80 h-80 rounded-full bg-emerald-400/20 dark:bg-emerald-500/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-amber-400/15 dark:bg-brand-400/10 blur-3xl pointer-events-none" aria-hidden="true"></div>
        </div>

        <!-- ============================================================== -->
        <!-- SECTION A: MAIN SUMMARY (6 Authoritative KPIs - TOP PLACEMENT)  -->
        <!-- ============================================================== -->
        <section aria-label="Indikator Kinerja Utama Perpustakaan" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3.5 sm:gap-4 mb-6">
            <!-- 1. Total Judul Buku -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-book-titles">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 dark:bg-brand-900/40 dark:text-brand-300 px-2 py-0.5 rounded-md mb-2">
                        Katalog
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Total Judul Buku</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ number_format($totalBookTitles, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>Semua jenis judul</span>
                    <a href="{{ route('books.index') }}" class="text-brand-600 font-semibold hover:underline">Kelola &rarr;</a>
                </p>
            </div>

            <!-- 2. Total Buku Fisik -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 dark:bg-emerald-900/40 dark:text-emerald-300 px-2 py-0.5 rounded-md mb-2">
                        Fisik
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Total Buku Fisik</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ number_format($totalPhysicalTitles, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>Judul di rak fisik</span>
                    <a href="{{ route('books.index', ['collection_type' => 'physical']) }}" class="text-brand-600 font-semibold hover:underline">Filter &rarr;</a>
                </p>
            </div>

            <!-- 3. Total E-book -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-brand-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 dark:bg-indigo-900/40 dark:text-indigo-300 px-2 py-0.5 rounded-md mb-2">
                        Digital
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Total E-book</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ number_format($totalEbookTitles, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>Baca daring &amp; unduh</span>
                    <a href="{{ route('books.index', ['collection_type' => 'e-book']) }}" class="text-brand-600 font-semibold hover:underline">Filter &rarr;</a>
                </p>
            </div>

            <!-- 4. Total Anggota -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-active-members">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 dark:bg-amber-900/40 dark:text-amber-300 px-2 py-0.5 rounded-md mb-2">
                        Peminjam
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Total Anggota</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ number_format($totalActiveMembers, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>Sivitas terdaftar</span>
                    <a href="{{ route('members.index') }}" class="text-brand-600 font-semibold hover:underline">Kelola &rarr;</a>
                </p>
            </div>

            <!-- 5. Peminjaman Aktif -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-brand-400 flex flex-col justify-between" data-testid="kpi-borrowed-copies">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-cyan-700 bg-cyan-50 dark:bg-cyan-900/40 dark:text-cyan-300 px-2 py-0.5 rounded-md mb-2">
                        Sirkulasi
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Peminjaman Aktif</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana">
                        {{ number_format($activeLoansCount, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>{{ $totalBorrowedCopies }} eks dipinjam</span>
                    <a href="{{ route('borrowings.index', ['status' => 'borrowed']) }}" class="text-brand-600 font-semibold hover:underline">Pantau &rarr;</a>
                </p>
            </div>

            <!-- 6. Peminjaman Terlambat -->
            <div class="group card-interactive bg-surface rounded-2xl border border-line p-4.5 sm:p-5 shadow-xs hover:border-rose-400 flex flex-col justify-between">
                <div>
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider {{ $overdueLoansCount > 0 ? 'text-rose-700 bg-rose-50 dark:bg-rose-900/40 dark:text-rose-300' : 'text-slate-600 bg-slate-100 dark:bg-slate-800' }} px-2 py-0.5 rounded-md mb-2">
                        Jatuh Tempo
                    </span>
                    <p class="text-xs font-semibold text-text-muted">Peminjaman Terlambat</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tabular-nums tracking-tight font-italiana {{ $overdueLoansCount > 0 ? 'text-rose-600' : '' }}">
                        {{ number_format($overdueLoansCount, 0, ',', '.') }}
                    </p>
                </div>
                <p class="mt-3 text-[11px] text-text-muted border-t border-line/60 pt-2 flex items-center justify-between">
                    <span>Lewat batas waktu</span>
                    <a href="{{ route('borrowings.index', ['status' => 'overdue']) }}" class="text-rose-600 font-semibold hover:underline">Tindak &rarr;</a>
                </p>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- SECTION I & SECTION 26: ATTENTION REQUIRED / PERHATIAN OPERASIONAL -->
        <!-- ============================================================== -->
        <section aria-label="Informasi Perhatian Operasional" class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-2.5 h-2.5 fill-current {{ $attentionRequired['has_attention'] ? 'text-amber-500 animate-pulse' : 'text-emerald-500' }} shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <h2 class="text-sm font-bold text-ink uppercase tracking-wider font-sans">
                        {{ $isPetugas ? 'Fokus Operasional Petugas' : 'Perhatian Operasional & Tindakan' }}
                    </h2>
                </div>
                @if ($returnsTodayCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $returnsTodayCount }} Pengembalian Hari Ini</span>
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Attention 1: Pengajuan Pending -->
                <div class="p-4.5 rounded-2xl border {{ $pendingCount > 0 ? 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/40' : 'bg-surface border-line' }} flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium {{ $pendingCount > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-text-muted' }}">
                            Pengajuan Pending
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-ink tabular-nums font-italiana">
                            {{ $pendingCount }}
                            <span class="text-xs font-normal text-text-muted font-sans">transaksi</span>
                        </p>
                        <p class="mt-2 text-[11px] text-text-muted">
                            {{ $pendingCount > 0 ? 'Perlu tinjauan persetujuan petugas' : 'Tidak ada antrean pending' }}
                        </p>
                    </div>
                    <a href="{{ route('borrowings.index', ['status' => 'pending']) }}" class="p-2 rounded-xl {{ $pendingCount > 0 ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-surface-soft text-text-muted hover:text-ink' }} transition-colors" title="Lihat Pengajuan">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Attention 2: Approved Menunggu Pengambilan -->
                <div class="p-4.5 rounded-2xl border {{ $awaitingPickupCount > 0 ? 'bg-blue-50/60 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800/40' : 'bg-surface border-line' }} flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium {{ $awaitingPickupCount > 0 ? 'text-blue-800 dark:text-blue-300' : 'text-text-muted' }}">
                            Menunggu Pengambilan
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-ink tabular-nums font-italiana">
                            {{ $awaitingPickupCount }}
                            <span class="text-xs font-normal text-text-muted font-sans">buku disetujui</span>
                        </p>
                        <p class="mt-2 text-[11px] text-text-muted">
                            {{ $awaitingPickupCount > 0 ? 'Stok direservasi, tunggu penyerahan' : 'Semua buku diserahkan' }}
                        </p>
                    </div>
                    <a href="{{ route('borrowings.index', ['status' => 'approved']) }}" class="p-2 rounded-xl {{ $awaitingPickupCount > 0 ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : 'bg-surface-soft text-text-muted hover:text-ink' }} transition-colors" title="Lihat Penyerahan">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Attention 3: Peminjaman Terlambat -->
                <div class="p-4.5 rounded-2xl border {{ $overdueLoansCount > 0 ? 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800/40' : 'bg-surface border-line' }} flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium {{ $overdueLoansCount > 0 ? 'text-rose-800 dark:text-rose-300' : 'text-text-muted' }}">
                            Peminjaman Terlambat
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-ink tabular-nums font-italiana">
                            {{ $overdueLoansCount }}
                            <span class="text-xs font-normal text-text-muted font-sans">peminjaman</span>
                        </p>
                        <p class="mt-2 text-[11px] text-text-muted">
                            {{ $overdueLoansCount > 0 ? 'Melewati batas jatuh tempo pengembalian' : 'Tidak ada peminjaman terlambat' }}
                        </p>
                    </div>
                    <a href="{{ route('borrowings.index', ['status' => 'overdue']) }}" class="p-2 rounded-xl {{ $overdueLoansCount > 0 ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-surface-soft text-text-muted hover:text-ink' }} transition-colors" title="Lihat Keterlambatan">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Attention 4: Buku Fisik Stok Habis -->
                <div class="p-4.5 rounded-2xl border {{ $outOfStockPhysicalTitles > 0 ? 'bg-slate-50 dark:bg-slate-900 border-line' : 'bg-surface border-line' }} flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-text-muted">
                            Buku Fisik Stok Habis
                        </p>
                        <p class="mt-1 text-2xl font-extrabold text-ink tabular-nums font-italiana">
                            {{ $outOfStockPhysicalTitles }}
                            <span class="text-xs font-normal text-text-muted font-sans">judul</span>
                        </p>
                        <p class="mt-2 text-[11px] text-text-muted">
                            {{ $outOfStockPhysicalTitles > 0 ? 'Koleksi dengan sisa stok 0 eksemplar' : 'Semua judul fisik tersedia' }}
                        </p>
                    </div>
                    <a href="{{ route('books.index', ['stock' => 'empty']) }}" class="p-2 rounded-xl bg-surface-soft text-text-muted hover:text-ink transition-colors" title="Lihat Stok Habis">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- SECTION B: INVENTORY SUMMARY (Physical Books Only)             -->
        <!-- ============================================================== -->
        <section aria-label="Ringkasan Inventaris Eksemplar Fisik" class="mb-6 bg-surface rounded-2xl border border-line p-4.5 sm:p-5.5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-line mb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        <h2 class="text-base font-bold text-ink font-sans">Ringkasan Inventaris Eksemplar Fisik</h2>
                    </div>
                    <p class="text-xs text-text-muted mt-0.5 font-sans">
                        Perhitungan real-time fisik perpustakaan berdasarkan formula stok: Total = Tersedia + Direservasi + Sedang Dipinjam.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-surface-soft border border-line text-text-muted font-mono">
                        E-book tidak dihitung sebagai eksemplar fisik
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- 1. Total Eksemplar Fisik -->
                <div class="p-3.5 rounded-xl bg-surface-soft/60 border border-line text-center">
                    <p class="text-[11px] font-medium text-text-muted">Total Eksemplar Fisik</p>
                    <p class="mt-1 text-2xl font-bold text-ink tabular-nums font-italiana">
                        {{ number_format($totalPhysicalCopies, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-text-muted">Koleksi keseluruhan</p>
                </div>

                <!-- 2. Stok Tersedia -->
                <div class="p-3.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 text-center" data-testid="kpi-available-stock">
                    <p class="text-[11px] font-medium text-emerald-800 dark:text-emerald-300">Stok Tersedia</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums font-italiana">
                        {{ number_format($totalAvailableStock, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-400">Siap dipinjam di rak</p>
                </div>

                <!-- 3. Stok Direservasi -->
                <div class="p-3.5 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/40 text-center">
                    <p class="text-[11px] font-medium text-blue-800 dark:text-blue-300">Stok Direservasi</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-400 tabular-nums font-italiana">
                        {{ number_format($totalReservedStock, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-blue-600 dark:text-blue-400">Approved, belum ambil</p>
                </div>

                <!-- 4. Stok Sedang Dipinjam -->
                <div class="p-3.5 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-center">
                    <p class="text-[11px] font-medium text-amber-800 dark:text-amber-300">Sedang Dipinjam</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-400 tabular-nums font-italiana">
                        {{ number_format($totalBorrowedStock, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-amber-600 dark:text-amber-400">Di tangan peminjam</p>
                </div>

                <!-- 5. Stok Tidak Layak / Hilang -->
                <div class="p-3.5 rounded-xl bg-surface-soft/60 border border-line text-center">
                    <p class="text-[11px] font-medium text-text-muted">Rusak / Hilang</p>
                    <p class="mt-1 text-2xl font-bold text-text-muted tabular-nums font-italiana">
                        {{ number_format($damagedLostStock, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-text-muted">Kondisi nonaktif</p>
                </div>

                <!-- 6. Judul Fisik Stok Habis -->
                <div class="p-3.5 rounded-xl bg-surface-soft/60 border border-line text-center">
                    <p class="text-[11px] font-medium text-text-muted">Judul Stok Habis</p>
                    <p class="mt-1 text-2xl font-bold text-ink tabular-nums font-italiana {{ $outOfStockPhysicalTitles > 0 ? 'text-rose-600' : '' }}">
                        {{ number_format($outOfStockPhysicalTitles, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[10px] text-text-muted">Stok tersedia = 0</p>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- SECTION C & SECTION D: BORROWING TREND & COLLECTION CHARTS    -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 mb-6">
            <!-- SECTION C: BORROWING TREND CHART (Col 8) -->
            <section aria-labelledby="chart-trend-heading" class="lg:col-span-8 bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-line mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                <h2 id="chart-trend-heading" class="text-base font-bold text-ink">Grafik Tren Peminjaman &amp; Pengembalian</h2>
                            </div>
                            <p class="text-xs text-text-muted mt-0.5">
                                Menampilkan frekuensi aktual peminjaman dan pengembalian buku (titik nol terisi konsisten).
                            </p>
                        </div>
                        <!-- Period filter toggles -->
                        <div class="inline-flex rounded-xl p-1 bg-surface-soft border border-line text-xs font-semibold" role="tablist">
                            <button type="button" class="btn-period-toggle px-3 py-1.5 rounded-lg transition-colors cursor-pointer bg-brand-600 text-white shadow-xs" data-period="7d">
                                7 Hari
                            </button>
                            <button type="button" class="btn-period-toggle px-3 py-1.5 rounded-lg transition-colors cursor-pointer text-text-muted hover:text-ink" data-period="30d">
                                30 Hari
                            </button>
                            <button type="button" class="btn-period-toggle px-3 py-1.5 rounded-lg transition-colors cursor-pointer text-text-muted hover:text-ink" data-period="12m">
                                12 Bulan
                            </button>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="relative w-full h-[280px] sm:h-[320px]">
                        <canvas id="borrowingTrendChart" class="w-full h-full"></canvas>
                        <div id="chart-loading-state" class="absolute inset-0 flex items-center justify-center bg-surface/50 hidden">
                            <span class="text-xs text-text-muted">Memuat grafik tren...</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-line text-xs text-text-muted mt-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-brand-600 shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span class="font-medium text-ink">Peminjaman Aktual</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-emerald-500 shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span class="font-medium text-ink">Pengembalian Selesai</span>
                        </div>
                    </div>
                    <span class="text-[11px]">Zona Waktu: WIB (Asia/Jakarta)</span>
                </div>
            </section>

            <!-- SECTION D: COLLECTION DISTRIBUTION (Col 4) -->
            <section aria-labelledby="chart-collection-heading" class="lg:col-span-4 bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-line mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            <h2 id="chart-collection-heading" class="text-base font-bold text-ink">Distribusi Koleksi</h2>
                        </div>
                        <span class="text-xs text-text-muted">{{ number_format($totalBookTitles, 0, ',', '.') }} Judul</span>
                    </div>

                    <div class="relative w-full h-[220px] flex items-center justify-center">
                        <canvas id="collectionDistributionChart" class="max-h-full"></canvas>
                    </div>

                    <div class="mt-4 space-y-3 pt-4 border-t border-line">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <svg class="w-2.5 h-2.5 fill-current text-brand-700 dark:text-brand-400 shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <span class="font-semibold text-ink">Buku Fisik</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-ink">{{ number_format($totalPhysicalTitles, 0, ',', '.') }} judul</span>
                                <span class="text-text-muted ml-1">({{ $collectionDistribution['physical_percent'] }}%)</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <svg class="w-2.5 h-2.5 fill-current text-emerald-400 shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <span class="font-semibold text-ink">E-book Digital</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-ink">{{ number_format($totalEbookTitles, 0, ',', '.') }} judul</span>
                                <span class="text-text-muted ml-1">({{ $collectionDistribution['ebook_percent'] }}%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-line mt-4">
                    <a href="{{ route('books.index') }}" class="w-full inline-flex items-center justify-center py-2 rounded-xl bg-surface-soft hover:bg-surface-soft/80 text-xs font-semibold text-ink transition-colors">
                        Lihat Seluruh Koleksi &rarr;
                    </a>
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- SECTION E & SECTION F: CATEGORY & STATUS BREAKDOWN             -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 mb-6">
            <!-- SECTION E: CATEGORY DISTRIBUTION (Col 6) -->
            <section aria-labelledby="category-distribution-heading" class="lg:col-span-6 bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-line mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            <h2 id="category-distribution-heading" class="text-base font-bold text-ink">Distribusi Kategori Buku</h2>
                        </div>
                        <a href="{{ route('categories.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                            Kelola Kategori &rarr;
                        </a>
                    </div>

                    @if ($categoryDistribution->isEmpty())
                        <div class="py-12 text-center text-xs text-text-muted">
                            Belum ada data kategori buku terdaftar.
                        </div>
                    @else
                        <div class="space-y-3.5">
                            @foreach ($categoryDistribution->take(6) as $cat)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-semibold text-ink">{{ $cat['name'] }}</span>
                                        <span class="text-text-muted tabular-nums">
                                             <strong>{{ $cat['count'] }}</strong> judul ({{ $cat['percent'] }}%)
                                        </span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-surface-soft overflow-hidden">
                                        <div class="h-full bg-brand-600 rounded-full transition-all duration-500" style="width: {{ max(4, $cat['percent']) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-line mt-4 text-xs text-text-muted flex items-center justify-between">
                    <span>Data dihitung dari total {{ $totalBookTitles }} judul buku terdaftar</span>
                    <a href="{{ route('books.index') }}" class="text-brand-600 font-semibold hover:underline">Filter Katalog &rarr;</a>
                </div>
            </section>

            <!-- SECTION F: BORROWING STATUS BREAKDOWN (Col 6) -->
            <section aria-labelledby="status-breakdown-heading" class="lg:col-span-6 bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-line mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            <h2 id="status-breakdown-heading" class="text-base font-bold text-ink">Ringkasan Status Transaksi Sirkulasi</h2>
                        </div>
                        <a href="{{ route('borrowings.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                            Semua Transaksi &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <!-- 1. Pending -->
                        <a href="{{ route('borrowings.index', ['status' => 'pending']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-amber-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                Pending
                            </span>
                            <p class="mt-2 text-xl font-bold text-ink tabular-nums font-italiana">{{ $statusBreakdown['pending'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Menunggu Persetujuan</p>
                        </a>

                        <!-- 2. Approved -->
                        <a href="{{ route('borrowings.index', ['status' => 'approved']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-blue-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200">
                                Disetujui
                            </span>
                            <p class="mt-2 text-xl font-bold text-ink tabular-nums font-italiana">{{ $statusBreakdown['approved'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Menunggu Pengambilan</p>
                        </a>

                        <!-- 3. Borrowed (Tepat Waktu) -->
                        <a href="{{ route('borrowings.index', ['status' => 'borrowed']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-emerald-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-cyan-50 text-cyan-700 border border-cyan-200">
                                Aktif
                            </span>
                            <p class="mt-2 text-xl font-bold text-ink tabular-nums font-italiana">{{ $statusBreakdown['active_on_time'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Sedang Dipinjam</p>
                        </a>

                        <!-- 4. Terlambat -->
                        <a href="{{ route('borrowings.index', ['status' => 'overdue']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-rose-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                Terlambat
                            </span>
                            <p class="mt-2 text-xl font-bold text-rose-600 tabular-nums font-italiana">{{ $statusBreakdown['overdue'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Lewat Jatuh Tempo</p>
                        </a>

                        <!-- 5. Dikembalikan -->
                        <a href="{{ route('borrowings.index', ['status' => 'returned']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-emerald-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Selesai
                            </span>
                            <p class="mt-2 text-xl font-bold text-ink tabular-nums font-italiana">{{ $statusBreakdown['returned'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Sudah Dikembalikan</p>
                        </a>

                        <!-- 6. Ditolak / Batal -->
                        <a href="{{ route('borrowings.index', ['status' => 'rejected']) }}" class="p-3.5 rounded-xl border border-line bg-surface hover:border-slate-400 transition-colors block">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                Ditolak
                            </span>
                            <p class="mt-2 text-xl font-bold text-ink tabular-nums font-italiana">{{ $statusBreakdown['rejected'] }}</p>
                            <p class="text-[11px] text-text-muted mt-0.5">Batal / Ditolak</p>
                        </a>
                    </div>
                </div>

                <div class="pt-4 border-t border-line mt-4 text-xs text-text-muted">
                    Klik salah satu status di atas untuk membuka daftar peminjaman dengan filter terkait.
                </div>
            </section>
        </div>

        <!-- ============================================================== -->
        <!-- SECTION G: POPULAR BOOKS (TOP 5 MOST BORROWED PHYSICAL)        -->
        <!-- ============================================================== -->
        <section aria-labelledby="popular-books-heading" class="bg-surface rounded-2xl border border-line overflow-hidden shadow-xs mb-6">
            <div class="p-5 border-b border-line flex items-center justify-between gap-2 bg-surface-soft/40">
                <div class="flex items-center gap-2.5">
                    <svg class="w-2.5 h-2.5 fill-current text-amber-500 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <h2 id="popular-books-heading" class="text-base font-bold text-ink">Buku Fisik Paling Sering Dipinjam (Top 5)</h2>
                </div>
                <span class="text-xs text-text-muted">Dihitung dari transaksi peminjaman riil</span>
            </div>

            @if ($popularBooks->isEmpty())
                <div class="p-12 text-center text-xs text-text-muted">
                    Belum ada data transaksi peminjaman buku fisik.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-soft/60 border-b border-line text-xs font-semibold text-text-muted">
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4">Koleksi Buku</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center whitespace-nowrap">Sisa Stok</th>
                                <th class="py-3 px-4 text-right whitespace-nowrap">Total Peminjaman</th>
                                <th class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach ($popularBooks as $index => $book)
                                <tr class="hover:bg-brand-50/20 transition-colors">
                                    <td class="py-3.5 px-4 text-center font-bold text-xs text-text-muted font-mono">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-14 bg-surface-soft rounded-lg overflow-hidden shrink-0 border border-line shadow-xs">
                                                @if ($book->cover_path)
                                                    <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-text-muted">
                                                        <svg class="w-5 h-5 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('books.show', $book) }}" class="font-bold text-ink hover:text-brand-600 line-clamp-1">
                                                    {{ $book->title }}
                                                </a>
                                                <p class="text-xs text-text-muted mt-0.5">{{ $book->author }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                        <span class="px-2.5 py-1 rounded-md bg-surface-soft border border-line text-ink font-medium">
                                            {{ $book->category?->name ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-semibold tabular-nums {{ $book->stock_available > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $book->stock_available }} eks
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold text-xs tabular-nums border border-brand-200/60">
                                            {{ $book->total_borrow_count ?? $book->borrow_count ?? 0 }} kali
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('books.show', $book) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- ============================================================== -->
        <!-- SECTION H: RECENT TRANSACTIONS (5–10 LATEST)                   -->
        <!-- ============================================================== -->
        <section aria-labelledby="recent-borrowings-heading" class="w-full bg-surface rounded-2xl border border-line overflow-hidden shadow-xs mb-6">
            <div class="p-5 border-b border-line flex items-center justify-between gap-2 bg-surface-soft/40">
                <div class="flex items-center gap-2.5">
                    <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <h2 id="recent-borrowings-heading" class="text-base font-bold text-ink">Aktivitas Transaksi Sirkulasi Terbaru</h2>
                </div>
                <a href="{{ route('borrowings.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1">
                    <span>Lihat Semua Transaksi</span>
                    <span>&rarr;</span>
                </a>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-ink">Belum ada riwayat transaksi peminjaman.</p>
                    <p class="mt-1 text-xs text-text-muted">Catatan transaksi sirkulasi akan otomatis tampil begitu peminjaman dicatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-soft/60 border-b border-line text-xs font-semibold text-text-muted">
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Kode</th>
                                <th scope="col" class="py-3 px-4 min-w-[140px]">Anggota</th>
                                <th scope="col" class="py-3 px-4 min-w-[180px]">Buku Dipinjam</th>
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Tanggal Pinjam</th>
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Status</th>
                                <th scope="col" class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach ($recentTransactions as $loan)
                                <tr class="hover:bg-brand-50/20 transition-colors">
                                    <td class="py-3.5 px-4 font-mono text-xs font-semibold text-ink whitespace-nowrap">
                                        <a href="{{ route('borrowings.show', $loan) }}" class="hover:text-brand-600">
                                            {{ $loan->transaction_code }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-brand-50 dark:bg-brand-600/25 border border-brand-100 dark:border-brand-400/30 text-brand-700 dark:text-brand-400 font-bold text-[11px] flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($loan->member_name_snapshot ?? ($loan->member?->name ?? 'AN'), 0, 2)) }}
                                            </div>
                                            <span class="font-medium text-ink truncate">{{ $loan->member_name_snapshot ?? ($loan->member?->name ?? '-') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-medium text-ink text-xs line-clamp-1">
                                            {{ $loan->book?->title ?? ($loan->items->first()?->book?->title ?? 'Judul Buku') }}
                                        </p>
                                        @if ($loan->items->count() > 1)
                                            <span class="text-[10px] text-text-muted">+{{ $loan->items->count() - 1 }} buku lainnya</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-text-muted tabular-nums whitespace-nowrap text-xs">
                                        {{ $loan->borrowed_at?->format('d M Y') ?? ($loan->created_at?->format('d M Y') ?? '-') }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($loan->isPending())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Pending
                                            </span>
                                        @elseif ($loan->isApproved())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                Disetujui
                                            </span>
                                        @elseif ($loan->isBorrowed())
                                            @if ($loan->isOverdue())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Terlambat
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                                    Dipinjam
                                                </span>
                                            @endif
                                        @elseif ($loan->isReturned())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Dikembalikan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            @can('return', $loan)
                                                @if ($loan->isBorrowed())
                                                    <button
                                                        type="button"
                                                        class="btn-open-return-modal inline-flex items-center px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                        data-loan-code="{{ $loan->transaction_code }}"
                                                        data-member-name="{{ $loan->member_name_snapshot ?? ($loan->member?->name ?? '-') }}"
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

        <!-- ============================================================== -->
        <!-- PENGATURAN AKUN DASHBOARD (Profil, Keamanan, dan Tampilan)     -->
        <!-- ============================================================== -->
        <section id="pengaturan-akun" aria-labelledby="account-settings-heading" class="w-full bg-surface rounded-2xl border border-line overflow-hidden shadow-xs mb-6 transition-colors">
            <div class="p-5 sm:p-6 border-b border-line bg-surface-soft/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-2.5 h-2.5 fill-current text-brand-600 dark:text-brand-400 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        <h2 id="account-settings-heading" class="text-lg font-bold text-ink">Pengaturan Akun</h2>
                    </div>
                    <p class="text-xs text-text-muted mt-1">Kelola informasi profil pengelola, keamanan autentikasi kata sandi, dan preferensi tampilan antarmuka.</p>
                </div>

                <!-- 3 Navigation Tabs with Glassmorphic Pills -->
                <div class="inline-flex items-center p-1 rounded-xl bg-surface-soft border border-line text-xs font-semibold" role="tablist" aria-label="Tab Pengaturan Akun">
                    <button
                        type="button"
                        id="tab-btn-profil"
                        role="tab"
                        aria-selected="true"
                        aria-controls="panel-profil"
                        onclick="switchAccountTab('profil')"
                        class="account-tab-btn px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 cursor-pointer bg-surface text-ink font-bold shadow-xs border border-line/60"
                    >
                        <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profil</span>
                    </button>
                    <button
                        type="button"
                        id="tab-btn-keamanan"
                        role="tab"
                        aria-selected="false"
                        aria-controls="panel-keamanan"
                        onclick="switchAccountTab('keamanan')"
                        class="account-tab-btn px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 cursor-pointer text-text-muted hover:text-ink hover:bg-surface/50"
                    >
                        <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Keamanan</span>
                    </button>
                    <button
                        type="button"
                        id="tab-btn-tampilan"
                        role="tab"
                        aria-selected="false"
                        aria-controls="panel-tampilan"
                        onclick="switchAccountTab('tampilan')"
                        class="account-tab-btn px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 cursor-pointer text-text-muted hover:text-ink hover:bg-surface/50"
                    >
                        <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span>Tampilan</span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: PROFIL PENGELOLA -->
            <div id="panel-profil" role="tabpanel" aria-labelledby="tab-btn-profil" class="p-5 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Column Left: Foto Profil & Avatar Management -->
                    <div class="lg:col-span-4 p-5 rounded-2xl border border-line bg-surface-soft/40 flex flex-col items-center text-center">
                        <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-full bg-brand-100 text-brand-700 ring-2 ring-brand-500/20 dark:bg-brand-900/40 dark:text-brand-300 shadow-md mb-4">
                            <img
                                id="dash-profile-photo-preview"
                                src="{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : '' }}"
                                alt="Foto profil {{ auth()->user()->name }}"
                                @if (!auth()->user()->profile_photo_path) hidden @endif
                                class="h-full w-full object-cover"
                            >
                            <div
                                id="dash-profile-avatar-fallback"
                                @if (auth()->user()->profile_photo_path) hidden @endif
                                class="flex h-full w-full items-center justify-center text-2xl font-bold font-sans"
                            >
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>

                        <p class="font-bold text-ink text-sm">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-text-muted mt-0.5">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300 border border-brand-200/50">
                            {{ auth()->user()->role_label }}
                        </span>

                        <div class="w-full border-t border-line mt-4 pt-4">
                            <form
                                id="dash-photo-upload-form"
                                method="POST"
                                action="{{ route('profile.photo.store') }}"
                                enctype="multipart/form-data"
                                class="space-y-2.5"
                            >
                                @csrf
                                <input
                                    type="file"
                                    id="dash-photo-input"
                                    name="photo"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="hidden"
                                    onchange="handleDashPhotoSelect(this)"
                                >
                                <button
                                    type="button"
                                    onclick="document.getElementById('dash-photo-input').click()"
                                    class="w-full py-2 px-3.5 rounded-xl border border-line bg-surface hover:bg-surface-soft text-xs font-semibold text-ink transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs"
                                >
                                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Pilih Foto Baru</span>
                                </button>
                                <button
                                    type="submit"
                                    id="dash-photo-save-btn"
                                    class="hidden w-full py-2 px-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                                >
                                    Simpan Foto
                                </button>
                            </form>

                            @if (auth()->user()->profile_photo_path)
                                <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="w-full py-1.5 px-3 rounded-lg text-xs font-medium text-danger-fg hover:bg-danger-bg transition-colors cursor-pointer"
                                    >
                                        Hapus Foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Column Right: Data Akun Form -->
                    <div class="lg:col-span-8 space-y-5">
                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="dash-input-name" class="block text-xs font-semibold text-ink mb-1.5">
                                        Nama Lengkap <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="dash-input-name"
                                        name="name"
                                        value="{{ old('name', auth()->user()->name) }}"
                                        required
                                        maxlength="100"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                                    >
                                </div>
                                <div>
                                    <label for="dash-input-username" class="block text-xs font-semibold text-ink mb-1.5">
                                        Username <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="dash-input-username"
                                        name="username"
                                        value="{{ old('username', auth()->user()->username) }}"
                                        required
                                        minlength="3"
                                        maxlength="30"
                                        pattern="[a-z0-9._]+"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm font-mono focus:outline-2 focus:outline-focus"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="dash-input-email" class="block text-xs font-semibold text-ink mb-1.5">
                                        Alamat Email Login
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="email"
                                            id="dash-input-email"
                                            value="{{ auth()->user()->email }}"
                                            disabled
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface-soft/60 text-text-muted text-sm cursor-not-allowed"
                                        >
                                        <span class="absolute right-3 top-2.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200/50">
                                            Terverifikasi
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-1">Email digunakan sebagai identitas akun utama.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink mb-1.5">
                                        Peran Sistem
                                    </label>
                                    <div class="px-3.5 py-2.5 rounded-xl border border-line bg-surface-soft/60 text-sm flex items-center justify-between">
                                        <span class="font-medium text-ink">{{ auth()->user()->role_label }}</span>
                                        <span class="text-[10px] font-bold text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/40 px-2 py-0.5 rounded-md border border-brand-200/50">
                                            Akses Terproteksi
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-1">Peran menentukan otorisasi modul administrasi.</p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-line flex items-center justify-end">
                                <button
                                    type="submit"
                                    class="min-h-10 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                                >
                                    Simpan Perubahan Profil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KEAMANAN & KATA SANDI -->
            <div id="panel-keamanan" role="tabpanel" aria-labelledby="tab-btn-keamanan" class="hidden p-5 sm:p-8">
                <div class="max-w-2xl space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Perbarui Kata Sandi</h3>
                        <p class="text-xs text-text-muted mt-0.5">Pastikan kata sandi baru Anda minimal 8 karakter dengan kombinasi yang aman.</p>
                    </div>

                    <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="dash-current-password" class="block text-xs font-semibold text-ink mb-1.5">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                id="dash-current-password"
                                name="current_password"
                                required
                                autocomplete="current-password"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                                placeholder="Masukkan kata sandi lama Anda"
                            >
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="dash-new-password" class="block text-xs font-semibold text-ink mb-1.5">
                                    Kata Sandi Baru <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    id="dash-new-password"
                                    name="password"
                                    required
                                    minlength="8"
                                    autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                                    placeholder="Minimal 8 karakter"
                                >
                            </div>
                            <div>
                                <label for="dash-password-confirmation" class="block text-xs font-semibold text-ink mb-1.5">
                                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    id="dash-password-confirmation"
                                    name="password_confirmation"
                                    required
                                    minlength="8"
                                    autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                                    placeholder="Ulangi kata sandi baru"
                                >
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end">
                            <button
                                type="submit"
                                class="min-h-10 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                            >
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>

                    <!-- Security Status Indicators -->
                    <div class="pt-6 border-t border-line grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3.5 rounded-xl border border-line bg-surface-soft/40 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-ink">Enkripsi Bcrypt</p>
                                <p class="text-[11px] text-text-muted mt-0.5">Sandi disimpan dengan salted hash 12 rounds.</p>
                            </div>
                        </div>
                        <div class="p-3.5 rounded-xl border border-line bg-surface-soft/40 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-ink">Proteksi Sesi</p>
                                <p class="text-[11px] text-text-muted mt-0.5">Sesi kadaluarsa otomatis saat tidak aktif.</p>
                            </div>
                        </div>
                        <div class="p-3.5 rounded-xl border border-line bg-surface-soft/40 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-ink">CSRF Protection</p>
                                <p class="text-[11px] text-text-muted mt-0.5">Token unik memvalidasi seluruh formulir.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: TAMPILAN & TEMA (Light, Dark, System) -->
            <div id="panel-tampilan" role="tabpanel" aria-labelledby="tab-btn-tampilan" class="hidden p-5 sm:p-8">
                <div class="max-w-3xl space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-ink">Preferensi Tema &amp; Antarmuka</h3>
                        <p class="text-xs text-text-muted mt-0.5">Pilih skema visual yang paling nyaman untuk Anda. Pengaturan ini langsung diterapkan dan tersimpan di peramban Anda.</p>
                    </div>

                    <!-- 3 Theme Cards with Live Switching -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Theme Card 1: Mode Terang (Ivory & Emerald) -->
                        <div
                            id="theme-card-light"
                            onclick="setThemeDashboard('light')"
                            class="theme-card-choice relative p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer hover:shadow-md flex flex-col justify-between border-line bg-surface"
                        >
                            <div class="space-y-3">
                                <!-- Mini Mockup Light Mode -->
                                <div class="w-full h-24 rounded-xl bg-[#FAF9F5] border border-stone-200 p-2.5 flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <div class="w-12 h-3 rounded-full bg-[#1b4332]/80"></div>
                                        <div class="w-4 h-4 rounded-full bg-amber-400"></div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-3/4 h-2 rounded bg-stone-300"></div>
                                        <div class="w-1/2 h-2 rounded bg-stone-200"></div>
                                    </div>
                                    <div class="flex gap-1.5">
                                        <div class="w-1/3 h-3 rounded bg-emerald-700"></div>
                                        <div class="w-1/3 h-3 rounded bg-stone-200"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold text-ink flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            <span>Mode Terang</span>
                                        </h4>
                                        <span id="badge-active-light" class="hidden text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                            Aktif
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                                        Palet Ivory hangat khas Litera dengan kontras optimal untuk pencahayaan terang.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 w-full py-1.5 px-3 rounded-lg border border-line bg-surface-soft hover:bg-surface text-xs font-semibold text-ink transition-colors cursor-pointer"
                            >
                                Pilih Terang
                            </button>
                        </div>

                        <!-- Theme Card 2: Mode Gelap (Forest Green) -->
                        <div
                            id="theme-card-dark"
                            onclick="setThemeDashboard('dark')"
                            class="theme-card-choice relative p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer hover:shadow-md flex flex-col justify-between border-line bg-surface"
                        >
                            <div class="space-y-3">
                                <!-- Mini Mockup Dark Mode -->
                                <div class="w-full h-24 rounded-xl bg-[#0e1d17] border border-emerald-900/60 p-2.5 flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <div class="w-12 h-3 rounded-full bg-emerald-500/80"></div>
                                        <div class="w-4 h-4 rounded-full bg-emerald-400"></div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-3/4 h-2 rounded bg-emerald-800/60"></div>
                                        <div class="w-1/2 h-2 rounded bg-emerald-900/50"></div>
                                    </div>
                                    <div class="flex gap-1.5">
                                        <div class="w-1/3 h-3 rounded bg-emerald-600"></div>
                                        <div class="w-1/3 h-3 rounded bg-emerald-900/60"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold text-ink flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                            <span>Mode Gelap</span>
                                        </h4>
                                        <span id="badge-active-dark" class="hidden text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                            Aktif
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                                        Nuansa hijau hutan malam yang ramah mata untuk pencahayaan redup.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 w-full py-1.5 px-3 rounded-lg border border-line bg-surface-soft hover:bg-surface text-xs font-semibold text-ink transition-colors cursor-pointer"
                            >
                                Pilih Gelap
                            </button>
                        </div>

                        <!-- Theme Card 3: Mengikuti Sistem -->
                        <div
                            id="theme-card-system"
                            onclick="setThemeDashboard('system')"
                            class="theme-card-choice relative p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer hover:shadow-md flex flex-col justify-between border-line bg-surface"
                        >
                            <div class="space-y-3">
                                <!-- Mini Mockup Split System -->
                                <div class="w-full h-24 rounded-xl overflow-hidden border border-line flex shadow-2xs">
                                    <div class="w-1/2 h-full bg-[#FAF9F5] p-2 flex flex-col justify-between">
                                        <div class="w-8 h-2.5 rounded-full bg-[#1b4332]/80"></div>
                                        <div class="w-full h-1.5 rounded bg-stone-300"></div>
                                        <div class="w-8 h-2.5 rounded bg-emerald-700"></div>
                                    </div>
                                    <div class="w-1/2 h-full bg-[#0e1d17] p-2 flex flex-col justify-between border-l border-line">
                                        <div class="w-8 h-2.5 rounded-full bg-emerald-500/80 ml-auto"></div>
                                        <div class="w-full h-1.5 rounded bg-emerald-800/60"></div>
                                        <div class="w-8 h-2.5 rounded bg-emerald-600 ml-auto"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold text-ink flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Ikuti Sistem</span>
                                        </h4>
                                        <span id="badge-active-system" class="hidden text-[9px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded">
                                            Aktif
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-1 leading-relaxed">
                                        Otomatis menyesuaikan dengan preferensi tema perangkat atau browser Anda.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-4 w-full py-1.5 px-3 rounded-lg border border-line bg-surface-soft hover:bg-surface text-xs font-semibold text-ink transition-colors cursor-pointer"
                            >
                                Pilih Sistem
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- ======================================================== -->
    <!-- POPUP CATAT PEMINJAMAN DASHBOARD (Modal Reusable)        -->
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ==============================================================
        // PENGATURAN AKUN DASHBOARD FUNCTIONS (GLOBAL SCOPE)
        // ==============================================================
        window.switchAccountTab = function (tabKey) {
            const panels = {
                'profil': document.getElementById('panel-profil'),
                'keamanan': document.getElementById('panel-keamanan'),
                'tampilan': document.getElementById('panel-tampilan')
            };
            const buttons = {
                'profil': document.getElementById('tab-btn-profil'),
                'keamanan': document.getElementById('tab-btn-keamanan'),
                'tampilan': document.getElementById('tab-btn-tampilan')
            };

            for (const [key, panel] of Object.entries(panels)) {
                if (!panel) continue;
                const btn = buttons[key];
                if (key === tabKey) {
                    panel.classList.remove('hidden');
                    if (btn) {
                        btn.classList.add('bg-surface', 'text-ink', 'font-bold', 'shadow-xs', 'border', 'border-line/60');
                        btn.classList.remove('text-text-muted', 'hover:bg-surface/50');
                        btn.setAttribute('aria-selected', 'true');
                    }
                } else {
                    panel.classList.add('hidden');
                    if (btn) {
                        btn.classList.remove('bg-surface', 'text-ink', 'font-bold', 'shadow-xs', 'border', 'border-line/60');
                        btn.classList.add('text-text-muted', 'hover:bg-surface/50');
                        btn.setAttribute('aria-selected', 'false');
                    }
                }
            }
        };

        window.handleDashPhotoSelect = function (input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('dash-profile-photo-preview');
                    const fallback = document.getElementById('dash-profile-avatar-fallback');
                    const saveBtn = document.getElementById('dash-photo-save-btn');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.hidden = false;
                    }
                    if (fallback) fallback.hidden = true;
                    if (saveBtn) saveBtn.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.setThemeDashboard = function (mode) {
            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else if (mode === 'light') {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            updateThemeCardsUI();
        };

        window.updateThemeCardsUI = function () {
            const currentTheme = localStorage.theme || 'system';
            const cards = {
                'light': document.getElementById('theme-card-light'),
                'dark': document.getElementById('theme-card-dark'),
                'system': document.getElementById('theme-card-system')
            };
            const badges = {
                'light': document.getElementById('badge-active-light'),
                'dark': document.getElementById('badge-active-dark'),
                'system': document.getElementById('badge-active-system')
            };

            for (const [key, card] of Object.entries(cards)) {
                if (!card) continue;
                const badge = badges[key];
                if (key === currentTheme) {
                    card.classList.add('border-brand-600', 'dark:border-brand-400', 'bg-brand-50/20', 'dark:bg-brand-900/10');
                    card.classList.remove('border-line');
                    if (badge) badge.classList.remove('hidden');
                } else {
                    card.classList.remove('border-brand-600', 'dark:border-brand-400', 'bg-brand-50/20', 'dark:bg-brand-900/10');
                    card.classList.add('border-line');
                    if (badge) badge.classList.add('hidden');
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            updateThemeCardsUI();

            // Hash anchor auto-scroll
            if (window.location.hash === '#pengaturan-akun') {
                const el = document.getElementById('pengaturan-akun');
                if (el) el.scrollIntoView({ behavior: 'smooth' });
            }

            // ==============================================================
            // CHART.JS INTEGRATION (SECTIONS C & D)
            // ==============================================================
            const trendData = @json($trendData ?? []);
            const collectionData = @json($collectionDistribution ?? []);
            let currentPeriod = '7d';
            let trendChartInstance = null;

            if (typeof Chart !== 'undefined') {
                // 1. Borrowing Trend Chart
                const trendCtx = document.getElementById('borrowingTrendChart');
                if (trendCtx && trendData[currentPeriod]) {
                    trendChartInstance = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: trendData[currentPeriod].labels,
                            datasets: [
                                {
                                    label: 'Peminjaman',
                                    data: trendData[currentPeriod].borrowed,
                                    borderColor: '#2d6a4f',
                                    backgroundColor: 'rgba(45, 106, 79, 0.1)',
                                    fill: true,
                                    tension: 0.35,
                                    pointBackgroundColor: '#2d6a4f',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                },
                                {
                                    label: 'Pengembalian',
                                    data: trendData[currentPeriod].returned,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.parsed.y} transaksi`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                }
                            }
                        }
                    });

                    // Period Toggle Buttons Listener
                    const periodButtons = document.querySelectorAll('.btn-period-toggle');
                    periodButtons.forEach(btn => {
                        btn.addEventListener('click', function () {
                            const period = this.getAttribute('data-period');
                            if (!trendData[period] || period === currentPeriod) return;

                            currentPeriod = period;

                            // Update active button styling
                            periodButtons.forEach(b => {
                                b.classList.remove('bg-brand-600', 'text-white', 'shadow-xs');
                                b.classList.add('text-text-muted');
                            });
                            this.classList.remove('text-text-muted');
                            this.classList.add('bg-brand-600', 'text-white', 'shadow-xs');

                            // Update chart datasets
                            trendChartInstance.data.labels = trendData[period].labels;
                            trendChartInstance.data.datasets[0].data = trendData[period].borrowed;
                            trendChartInstance.data.datasets[1].data = trendData[period].returned;
                            trendChartInstance.update();
                        });
                    });
                }

                // 2. Collection Distribution Donut Chart
                const collectionCtx = document.getElementById('collectionDistributionChart');
                if (collectionCtx) {
                    new Chart(collectionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Buku Fisik', 'E-book Digital'],
                            datasets: [{
                                data: [
                                    collectionData.physical || 0,
                                    collectionData.ebook || 0
                                ],
                                backgroundColor: ['#1b4332', '#52b788'],
                                hoverBackgroundColor: ['#2d6a4f', '#74c69d'],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = (collectionData.physical || 0) + (collectionData.ebook || 0);
                                            const val = context.parsed;
                                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                            return `${context.label}: ${val} judul (${pct}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '72%'
                        }
                    });
                }
            }

            // ==============================================================
            // MODAL LOGIC (BORROW & QUICK RETURN)
            // ==============================================================
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
        });
    </script>
    @endpush
</x-layouts.app>

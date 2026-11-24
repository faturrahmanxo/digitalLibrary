<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ isset($title) ? $title . ' — ' : '' }}Litera</title>
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon.png?v=2') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon.png?v=2') }}">

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
    <style>
        .font-brand-script {
            font-family: 'Cormorant Garamond', Georgia, serif;
            letter-spacing: 0.01em;
        }
    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased">
    @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();
        $isAnggota = $authUser?->isAnggota();
        $navItems = [
            [
                'label' => $isAnggota ? 'Beranda' : 'Dashboard',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
                'show' => true,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            ],
            [
                'label' => $isAnggota ? 'Peminjaman Saya' : 'Riwayat Peminjaman',
                'route' => 'borrowings.index',
                'active' => request()->routeIs('borrowings.*'),
                'show' => true,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
            ],
            [
                'label' => $isAnggota ? 'Katalog Koleksi' : 'Buku',
                'route' => 'books.index',
                'active' => request()->routeIs('books.*') && ! request()->routeIs('books.read'),
                'show' => true,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
            ],
            [
                'label' => 'Buku Favorit',
                'route' => 'favorites.index',
                'active' => request()->routeIs('favorites.*'),
                'show' => $isAnggota,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
            ],
            [
                'label' => 'Anggota',
                'route' => 'members.index',
                'active' => request()->routeIs('members.*'),
                'show' => ! $isAnggota,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ],
            [
                'label' => 'Kategori',
                'route' => 'categories.index',
                'active' => request()->routeIs('categories.*'),
                'show' => ! $isAnggota,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
            ],
            [
                'label' => 'Pengguna',
                'route' => 'users.index',
                'active' => request()->routeIs('users.*'),
                'show' => $authUser?->isAdmin() ?? false,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
            ],
        ];
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Mobile Drawer Backdrop -->
        <div id="mobile-drawer-backdrop" class="fixed inset-0 z-40 bg-ink/50 hidden lg:hidden" aria-hidden="true"></div>

        <!-- Sidebar Navigation (FIXED permanently on Desktop, Drawer on Mobile/Tablet) -->
        <aside id="app-sidebar" aria-label="Menu Utama" class="fixed inset-y-0 left-0 z-50 w-64 bg-sidebar border-r border-line transform -translate-x-full transition-transform duration-200 ease-in-out lg:translate-x-0 lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 lg:h-screen lg:flex lg:flex-col lg:shrink-0">
            <div class="h-16 px-5 flex items-center justify-between border-b border-line shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 text-ink font-bold text-lg tracking-tight focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus rounded">
                    <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-8 w-auto max-w-[140px] shrink-0 object-contain">
                </a>
                <button type="button" id="close-drawer-btn" class="lg:hidden p-2 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-focus" aria-label="Tutup menu navigasi">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
                @if ($isAnggota)
                    @foreach ($navItems as $item)
                        @if ($item['show'])
                            <a href="{{ route($item['route']) }}"
                               @if($item['active']) aria-current="page" @endif
                               class="group flex items-center justify-between px-3.5 py-2.5 rounded-control text-sm font-medium transition-all duration-200 ease-out hover:translate-x-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus {{ $item['active'] ? 'bg-sidebar-active text-ink font-semibold shadow-xs border-l-4 border-brand-600 dark:border-brand-400 pl-2.5' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $item['active'] ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted group-hover:text-ink' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        {!! $item['icon'] !!}
                                    </svg>
                                    <span class="{{ $item['active'] ? 'text-brand-700 dark:text-ink font-semibold' : '' }}">{{ $item['label'] }}</span>
                                </div>
                                @if($item['active'])
                                    <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400 animate-pulse"></span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                @else
                    {{-- 06. ADMIN SIDEBAR STRUCTURE --}}
                    {{-- 1. Dashboard --}}
                    @php
                        $isDashboardActive = request()->routeIs('dashboard');
                    @endphp
                    <a href="{{ route('dashboard') }}"
                       @if($isDashboardActive) aria-current="page" @endif
                       class="group flex items-center justify-between px-3.5 py-2.5 rounded-control text-sm font-medium transition-all duration-200 ease-out hover:translate-x-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus {{ $isDashboardActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs border-l-4 border-brand-600 dark:border-brand-400 pl-2.5' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $isDashboardActive ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted group-hover:text-ink' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="{{ $isDashboardActive ? 'text-brand-700 dark:text-ink font-semibold' : '' }}">Dashboard</span>
                        </div>
                        @if($isDashboardActive)
                            <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400 animate-pulse"></span>
                        @endif
                    </a>

                    {{-- 2. Manajemen Buku (Data Buku, Kategori Buku, Rak Buku, Peminjaman Buku, Penerbit) --}}
                    @php
                        $isBookManagementActive = request()->routeIs('books.*') || request()->routeIs('categories.*') || request()->routeIs('shelves.*') || request()->routeIs('borrowings.*') || request()->routeIs('publishers.*') || request()->is('shelves*') || request()->is('publishers*');
                        $isBooksActive = request()->routeIs('books.*') && !request()->routeIs('books.read');
                        $isCategoriesActive = request()->routeIs('categories.*');
                        $isShelvesActive = request()->routeIs('shelves.*') || request()->is('shelves*');
                        $isBorrowingsActive = request()->routeIs('borrowings.*');
                        $isPublishersActive = request()->routeIs('publishers.*') || request()->is('publishers*');
                    @endphp
                    <details id="sidebar-book-management" class="group/book-mgr" @if ($isBookManagementActive) open @endif>
                        <summary id="sidebar-book-management-summary" class="list-none group cursor-pointer flex items-center justify-between px-3.5 py-2.5 rounded-control text-sm font-medium transition-all duration-200 ease-out hover:translate-x-1 hover:bg-surface-soft hover:text-ink {{ $isBookManagementActive ? 'bg-sidebar-active/70 text-ink font-semibold' : 'text-text-muted' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $isBookManagementActive ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted group-hover:text-ink' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="{{ $isBookManagementActive ? 'text-brand-700 dark:text-ink font-semibold' : '' }}">Manajemen Buku</span>
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200 group-open/book-mgr:rotate-180 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="grid transition-[grid-template-rows] duration-300 ease-out" style="grid-template-rows: 1fr">
                            <div class="overflow-hidden">
                                <div class="ml-8 mt-1 space-y-1 border-l border-line pl-3">
                                    <a href="{{ route('books.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isBooksActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Data Buku</span>
                                        @if($isBooksActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                    <a href="{{ route('categories.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isCategoriesActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Kategori Buku</span>
                                        @if($isCategoriesActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                    <a href="{{ Route::has('shelves.index') ? route('shelves.index') : url('/shelves') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isShelvesActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Rak Buku</span>
                                        @if($isShelvesActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                    <a href="{{ route('borrowings.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isBorrowingsActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Peminjaman Buku</span>
                                        @if($isBorrowingsActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                    <a href="{{ Route::has('publishers.index') ? route('publishers.index') : url('/publishers') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isPublishersActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Penerbit</span>
                                        @if($isPublishersActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- 3. Manajemen Pengguna (Anggota, Admin & Petugas) --}}
                    @php
                        $isUserManagementActive = request()->routeIs('members.*') || request()->routeIs('users.*');
                        $isMembersActive = request()->routeIs('members.*');
                        $isUsersActive = request()->routeIs('users.*');
                        $canManageUsers = $authUser?->isAdmin() ?? false;
                    @endphp
                    <details id="sidebar-user-management" class="group/user-mgr" @if ($isUserManagementActive) open @endif>
                        <summary id="sidebar-user-management-summary" class="list-none group cursor-pointer flex items-center justify-between px-3.5 py-2.5 rounded-control text-sm font-medium transition-all duration-200 ease-out hover:translate-x-1 hover:bg-surface-soft hover:text-ink {{ $isUserManagementActive ? 'bg-sidebar-active/70 text-ink font-semibold' : 'text-text-muted' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ $isUserManagementActive ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted group-hover:text-ink' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="{{ $isUserManagementActive ? 'text-brand-700 dark:text-ink font-semibold' : '' }}">Manajemen Pengguna</span>
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200 group-open/user-mgr:rotate-180 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="grid transition-[grid-template-rows] duration-300 ease-out" style="grid-template-rows: 1fr">
                            <div class="overflow-hidden">
                                <div class="ml-8 mt-1 space-y-1 border-l border-line pl-3">
                                    <a href="{{ route('members.index') }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isMembersActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                        <span>Anggota</span>
                                        @if($isMembersActive)
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                        @endif
                                    </a>
                                    @if ($canManageUsers)
                                        <a href="{{ route('users.index') }}"
                                           class="flex items-center justify-between px-3 py-2 rounded-control text-xs font-medium transition-colors {{ $isUsersActive ? 'bg-sidebar-active text-ink font-semibold shadow-xs' : 'text-text-muted hover:bg-surface-soft hover:text-ink' }}">
                                            <span>Admin &amp; Petugas</span>
                                            @if($isUsersActive)
                                                <span class="w-1.5 h-1.5 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </details>
                @endif
            </nav>

            <!-- Sidebar User Profile Card (Without redundant logout button) -->
            <div class="p-4 border-t border-line bg-surface-soft/60 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-brand-100 dark:bg-brand-900/40 border border-brand-200 dark:border-brand-400/30 text-brand-700 dark:text-brand-400 font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($authUser?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink truncate">{{ $authUser?->name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $authUser?->isAdmin() ? 'bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400 border border-brand-600/20 dark:border-brand-400/30' : ($authUser?->isPetugas() ? 'bg-surface border border-line text-text-muted' : 'bg-success-bg text-success-fg border border-success-fg/20') }}">
                                {{ $authUser?->role_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Column (Offset by fixed sidebar width on desktop) -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Topbar with Glassmorphism -->
            <header class="h-16 bg-white/75 dark:bg-[#121c17]/80 backdrop-blur-xl backdrop-saturate-150 border-b border-line/70 dark:border-white/10 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="open-drawer-btn" class="lg:hidden p-2 -ml-2 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-focus" aria-label="Buka menu" aria-controls="app-sidebar" aria-expanded="false">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    @if (isset($breadcrumbs))
                        <nav aria-label="Breadcrumb" class="text-sm text-text-muted truncate">
                            {{ $breadcrumbs }}
                        </nav>
                    @else
                        <span class="text-sm font-medium text-text-muted truncate">Litera / {{ $isAnggota ? 'Portal Anggota' : 'Panel Manajemen' }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <!-- Link to Campus Landing Portal (Kembali ke Beranda via Navbar) -->
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-control border border-brand-200 dark:border-brand-500/30 bg-brand-50/80 dark:bg-brand-900/30 backdrop-blur-sm text-xs font-semibold text-brand-700 dark:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-800/40 transition-colors font-sans shadow-2xs" title="Kembali ke Beranda Kampus">
                        <span>Beranda</span>
                    </a>

                    <!-- Dark / Light Mode Toggle Button with Smooth Rotational Flipping Animation -->
                    <button type="button" id="theme-toggle-btn" class="p-2.5 rounded-control border border-line/70 dark:border-white/10 bg-white/50 dark:bg-white/5 backdrop-blur-md text-text-muted hover:text-ink hover:bg-surface-soft active:scale-90 transition-all cursor-pointer overflow-hidden shadow-2xs" aria-label="Ganti mode gelap/terang" title="Ganti mode gelap/terang">
                        <div class="relative w-4.5 h-4.5 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400 absolute transition-all duration-400 ease-out transform dark:rotate-0 dark:scale-100 dark:opacity-100 -rotate-90 scale-0 opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg class="w-4 h-4 text-ink absolute transition-all duration-400 ease-out transform dark:rotate-90 dark:scale-0 dark:opacity-0 rotate-0 scale-100 opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </div>
                    </button>

                    <a href="{{ route('dashboard') }}#pengaturan-akun" class="hidden sm:flex items-center gap-2.5 text-sm pl-2.5 border-l border-line/70 hover:opacity-85 transition-opacity" title="Buka Pengaturan Akun di Dashboard">
                        <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/40 border border-brand-200 dark:border-brand-400/30 text-brand-700 dark:text-brand-400 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($authUser?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-xs text-ink leading-tight">{{ $authUser?->name }}</p>
                            <span class="inline-flex items-center text-[10px] font-medium text-brand-600 dark:text-brand-400">{{ $authUser?->role_label }}</span>
                        </div>
                    </a>

                    <!-- Single Authoritative Logout Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-9 px-3.5 py-1.5 rounded-control border border-line/70 dark:border-white/10 bg-white/50 dark:bg-white/5 backdrop-blur-md text-xs font-semibold text-ink hover:bg-surface-soft hover:border-danger-fg/40 hover:text-danger-fg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus transition-colors cursor-pointer shadow-2xs" title="Keluar dari akun">
                            Keluar
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content with Fluid Fade-In Animation -->
            <main class="flex-1 w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 animate-fade-in-up">
                <!-- Flash Success Notice -->
                @if (session('success'))
                    <div role="status" aria-live="polite" class="mb-6 rounded-card border border-success-fg/20 bg-success-bg p-4 text-success-fg flex items-start justify-between gap-3 animate-fade-in-up shadow-xs">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Validation / Archive / Domain Error Summary -->
                @if ($errors->any())
                    <div role="alert" tabindex="-1" id="validation-error-summary" class="mb-6 rounded-card border border-danger-fg/25 bg-danger-bg p-4 text-danger-fg animate-fade-in-up shadow-xs">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <h2 class="text-sm font-semibold">Periksa kembali data yang ditandai:</h2>
                                <ul class="mt-1.5 list-disc list-inside text-sm space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Reusable Archive Confirmation Dialog -->
    <div id="archive-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="archive-modal-title" aria-describedby="archive-modal-desc" class="modal-animated fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200">
        <div class="modal-content-box bg-surface rounded-2xl border border-line shadow-2xl max-w-md w-full p-6 space-y-4">
            <div>
                <h2 id="archive-modal-title" class="text-lg font-bold text-ink">Arsipkan Data?</h2>
                <p id="archive-modal-desc" class="mt-1 text-sm text-text-muted">
                    Data yang diarsipkan tidak akan muncul pada daftar aktif, namun riwayat transaksi peminjaman tetap dipertahankan.
                </p>
            </div>
            <form id="archive-modal-form" method="POST" action="" class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" id="archive-modal-cancel" class="min-h-11 px-4 py-2 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    Batal
                </button>
                <button type="submit" id="archive-modal-submit" class="min-h-11 px-4 py-2 rounded-control bg-danger-fg text-white text-sm font-semibold hover:opacity-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    Arsipkan
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile drawer toggle
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('mobile-drawer-backdrop');
            const openBtn = document.getElementById('open-drawer-btn');
            const closeBtn = document.getElementById('close-drawer-btn');

            const sidebarBooksMenu = document.getElementById('sidebar-books-menu');
            const sidebarBooksSummary = document.getElementById('sidebar-books-summary');
            const sidebarBookCollectionIsActive = {{ in_array(request('collection_type'), ['e-book', 'novel'], true) ? 'true' : 'false' }};

            if (sidebarBooksMenu && sidebarBooksSummary) {
                if (sidebarBookCollectionIsActive) {
                    sidebarBooksMenu.open = true;
                    sidebarBooksSummary.addEventListener('click', function (event) {
                        event.preventDefault();
                    });
                }
            }

            function openDrawer() {
                if (!sidebar || !backdrop) return;
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
                if (closeBtn) closeBtn.focus();
            }

            function closeDrawer() {
                if (!sidebar || !backdrop) return;
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                if (openBtn) {
                    openBtn.setAttribute('aria-expanded', 'false');
                    openBtn.focus();
                }
            }

            if (openBtn) openBtn.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);

            // Archive confirmation modal logic with focus restoration
            const archiveModal = document.getElementById('archive-confirm-modal');
            const archiveTitle = document.getElementById('archive-modal-title');
            const archiveDesc = document.getElementById('archive-modal-desc');
            const archiveForm = document.getElementById('archive-modal-form');
            const archiveCancel = document.getElementById('archive-modal-cancel');
            const archiveSubmit = document.getElementById('archive-modal-submit');
            let lastArchiveTrigger = null;

            document.querySelectorAll('[data-archive-action]').forEach(function (button) {
                button.addEventListener('click', function () {
                    lastArchiveTrigger = button;
                    const action = button.getAttribute('data-archive-action');
                    const title = button.getAttribute('data-archive-title') || 'Arsipkan Data?';
                    const desc = button.getAttribute('data-archive-desc') || 'Data akan diarsipkan tetapi riwayat transaksi tetap tersimpan.';
                    const label = button.getAttribute('data-archive-label') || 'Arsipkan';

                    archiveForm.setAttribute('action', action);
                    archiveTitle.textContent = title;
                    archiveDesc.textContent = desc;
                    archiveSubmit.textContent = label;

                    archiveModal.classList.remove('hidden');
                    archiveModal.classList.add('flex');
                    archiveCancel.focus();
                });
            });

            function closeArchiveModal() {
                if (!archiveModal || archiveModal.classList.contains('hidden')) return;
                archiveModal.classList.add('hidden');
                archiveModal.classList.remove('flex');
                if (lastArchiveTrigger) lastArchiveTrigger.focus();
            }

            if (archiveCancel) archiveCancel.addEventListener('click', closeArchiveModal);
            if (archiveForm && archiveSubmit) {
                archiveForm.addEventListener('submit', function () {
                    archiveSubmit.disabled = true;
                    archiveSubmit.textContent = 'Memproses...';
                    archiveSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
            if (archiveModal) {
                archiveModal.addEventListener('click', function (e) {
                    if (e.target === archiveModal) closeArchiveModal();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeArchiveModal();
                    if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                        closeDrawer();
                    }
                }
            });

            // Auto-focus validation summary if present
            const errorSummary = document.getElementById('validation-error-summary');
            if (errorSummary) {
                errorSummary.focus();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

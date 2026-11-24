@php
    $currentUser = auth()->user();
    $isJelajahiActive = request()->is('jelajahi*') || request()->routeIs('catalog.*');
    $isHomeActive = request()->routeIs('home');
    $isAboutActive = request()->routeIs('info.*');
@endphp

<!-- Main Navigation Bar -->
<header class="sticky top-0 z-40 bg-white/75 dark:bg-[#121c17]/80 backdrop-blur-xl backdrop-saturate-150 border-b border-white/40 dark:border-white/10 shadow-[0_4px_30px_rgba(0,0,0,0.03)] dark:shadow-[0_4px_30px_rgba(0,0,0,0.2)] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-3">
        
        <!-- Brand Lockup -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 group focus-visible:outline-2 focus-visible:outline-focus rounded-xl">
            <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-8 w-auto object-contain transition-transform duration-200 group-hover:scale-[1.02]">
        </a>

        <!-- Desktop Navigation Links (Center) -->
        <nav class="hidden lg:flex items-center gap-0.5 text-[13px] font-semibold" aria-label="Navigasi Utama">
            <!-- Beranda -->
            <a
                href="{{ route('home') }}"
                class="px-2.5 py-1.5 rounded-xl transition-all duration-200 {{ $isHomeActive ? 'font-bold text-brand-600 dark:text-brand-400 relative after:absolute after:-bottom-1.5 after:left-3 after:right-3 after:h-0.75 after:bg-brand-600 dark:after:bg-brand-400 after:rounded-full' : 'text-text-muted hover:text-ink hover:bg-surface-soft' }}"
            >
                <span>Beranda</span>
            </a>

            <!-- Jelajahi Menu (Unified Menu Trigger with Chevron & Active Underline) -->
            <div class="relative" id="nav-jelajahi-container">
                <button
                    type="button"
                    id="nav-jelajahi-toggle-btn"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="Menu Jelajahi Genre"
                    class="group inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl transition-all duration-200 cursor-pointer focus-visible:outline-2 focus-visible:outline-focus {{ $isJelajahiActive ? 'font-bold text-brand-600 dark:text-brand-400 relative after:absolute after:-bottom-1.5 after:left-3 after:right-3 after:h-0.75 after:bg-brand-600 dark:after:bg-brand-400 after:rounded-full' : 'text-text-muted hover:text-ink hover:bg-surface-soft' }}"
                >
                    <span>Jelajahi</span>
                    <svg id="nav-jelajahi-chevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Mega Dropdown Panel (Styled like the reference design in user image) -->
                <div
                    id="nav-jelajahi-dropdown-menu"
                    class="hidden fixed inset-x-4 sm:inset-x-6 top-[4.25rem] w-auto rounded-b-2xl bg-surface/98 dark:bg-surface/98 backdrop-blur-xl border border-line shadow-2xl p-4 sm:p-5 z-50 animate-fade-in-up"
                    role="menu"
                >
                    <div class="mx-auto grid max-w-7xl grid-cols-12 gap-4 sm:gap-6 items-center">
                        <!-- Left Editorial Column -->
                        <div class="col-span-3 border-r border-line pr-3 sm:pr-6">
                            <h3 class="font-serif font-bold text-lg sm:text-xl text-ink leading-tight">
                                Jelajahi
                            </h3>
                        </div>

                        <!-- Right Genre Column (Hanya Novel & Keilmuan) -->
                        <div class="col-span-9 grid grid-cols-2 gap-2 sm:gap-5">
                            <a
                                href="{{ url('/jelajahi?genre=novel-fiksi') }}"
                                class="flex min-h-11 items-center rounded-xl px-3 py-2 text-sm font-semibold text-ink hover:bg-surface-soft hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                                role="menuitem"
                            >
                                Novel
                            </a>
                            <a
                                href="{{ url('/jelajahi?genre=keilmuan-edukasi') }}"
                                class="flex min-h-11 items-center rounded-xl px-3 py-2 text-sm font-semibold text-ink hover:bg-surface-soft hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                                role="menuitem"
                            >
                                Keilmuan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tentang Kami -->
            <a
                href="{{ route('info.profil') }}"
                class="px-2.5 py-1.5 rounded-xl transition-all duration-200 {{ $isAboutActive ? 'font-bold text-brand-600 dark:text-brand-400 relative after:absolute after:-bottom-1.5 after:left-3 after:right-3 after:h-0.75 after:bg-brand-600 dark:after:bg-brand-400 after:rounded-full' : 'text-text-muted hover:text-ink hover:bg-surface-soft' }}"
            >
                <span>Tentang Kami</span>
            </a>
        </nav>

        <!-- Right Side Controls: Search, Theme Toggle, Auth, Mobile Toggle -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            
            <!-- Search Button (Opens Expandable Desktop Search / Modal) -->
            <div class="relative hidden lg:block" id="nav-search-container">
                <button
                    type="button"
                    id="nav-search-toggle-btn"
                    aria-label="Pencarian Buku"
                    class="p-2 rounded-xl border border-line/70 dark:border-white/10 bg-white/50 dark:bg-white/5 backdrop-blur-md text-text-muted hover:text-ink hover:bg-white/80 dark:hover:bg-white/10 active:scale-95 transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-focus shadow-2xs"
                    title="Cari Buku &amp; Penulis"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>

            <!-- Theme Toggle with Smooth Rotational Flipping Animation -->
            <button
                type="button"
                id="theme-toggle-btn"
                class="p-2 rounded-xl border border-line/70 dark:border-white/10 bg-white/50 dark:bg-white/5 backdrop-blur-md text-text-muted hover:text-ink hover:bg-white/80 dark:hover:bg-white/10 active:scale-90 transition-all cursor-pointer overflow-hidden focus-visible:outline-2 focus-visible:outline-focus shadow-2xs"
                title="Ganti Tema (Gelap / Terang)"
                aria-label="Ganti Tema"
            >
                <div class="relative w-4.5 h-4.5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400 absolute transition-all duration-400 ease-out transform dark:rotate-0 dark:scale-100 dark:opacity-100 -rotate-90 scale-0 opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="w-4 h-4 text-ink absolute transition-all duration-400 ease-out transform dark:rotate-90 dark:scale-0 dark:opacity-0 rotate-0 scale-100 opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </div>
            </button>

            <!-- Authentication Area -->
            @auth
                @if ($currentUser->isAdmin() || $currentUser->isPetugas())
                    <!-- Admin / Petugas: Direct Dashboard Navigation & Single-Click Logout (Tanpa Dropdown) -->
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center gap-2 p-1 pl-1.5 pr-2.5 sm:pr-3 rounded-full border border-line/70 dark:border-white/15 bg-white/60 dark:bg-white/5 backdrop-blur-md hover:bg-white/90 dark:hover:bg-white/10 transition-all shadow-xs group focus-visible:outline-2 focus-visible:outline-focus"
                            title="Buka Dashboard Pengelola"
                            aria-label="Buka Dashboard Pengelola ({{ $currentUser->name }})"
                        >
                            @if ($currentUser->profile_photo_path)
                                <img
                                    src="{{ asset('storage/' . $currentUser->profile_photo_path) }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover ring-1 ring-brand-500/20"
                                >
                            @else
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="flex flex-col text-left">
                                <span class="text-xs font-bold text-ink max-w-[90px] sm:max-w-[120px] truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                    {{ $currentUser->name }}
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-brand-700 dark:text-brand-400">
                                    {{ $currentUser->role_label }}
                                </span>
                            </div>
                            <span class="hidden md:inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 dark:text-brand-400 ml-1 pl-2 border-l border-line/60">
                                Dashboard &rarr;
                            </span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="p-2 sm:px-3 sm:py-2 rounded-xl border border-line/70 dark:border-white/15 bg-white/60 dark:bg-white/5 backdrop-blur-md text-text-muted hover:text-danger-fg hover:border-danger-fg/30 hover:bg-danger-bg shadow-xs transition-all text-xs font-semibold flex items-center gap-1.5 cursor-pointer focus-visible:outline-2 focus-visible:outline-focus"
                                title="Keluar dari akun"
                                aria-label="Keluar dari akun"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="hidden lg:inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Anggota (Patrons): Dropdown menu untuk peminjaman pribadi & profil -->
                    <div class="relative" id="nav-user-container">
                        <button
                            type="button"
                            id="nav-user-toggle-btn"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Menu akun pengguna"
                            class="flex items-center gap-2 p-1 pl-1.5 pr-2.5 rounded-full border border-line/70 dark:border-white/15 bg-white/60 dark:bg-white/5 backdrop-blur-md hover:bg-white/90 dark:hover:bg-white/10 transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-focus shadow-xs"
                        >
                            @if ($currentUser->profile_photo_path)
                                <img
                                    src="{{ asset('storage/' . $currentUser->profile_photo_path) }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-8 w-8 rounded-full object-cover"
                                >
                            @else
                                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400 font-bold text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                                </div>
                            @endif
                            <span class="hidden md:inline text-xs font-semibold text-ink max-w-[100px] truncate">
                                {{ $currentUser->name }}
                            </span>
                            <svg id="nav-user-chevron" class="w-3.5 h-3.5 text-text-muted mr-0.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- User Dropdown Menu dengan konsep glassmorphism pekat (tanpa ghosting) -->
                        <div
                            id="nav-user-dropdown-menu"
                            class="hidden absolute right-0 top-full mt-2 w-56 rounded-2xl bg-surface/98 dark:bg-surface/98 backdrop-blur-2xl border border-line/80 dark:border-white/15 shadow-2xl p-2 z-50 animate-fade-in-up"
                            role="menu"
                        >
                            <div class="px-3 py-2 border-b border-line mb-1">
                                <p class="text-xs font-bold text-ink truncate">{{ $currentUser->name }}</p>
                                <p class="text-[11px] text-text-muted truncate">{{ $currentUser->email }}</p>
                            </div>

                            <!-- 1. Koleksi Saya (Khusus Anggota) -->
                            <a href="{{ route('collection.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-ink hover:bg-surface-soft hover:text-brand-600 dark:hover:text-brand-400 transition-colors" role="menuitem">
                                <span>Koleksi Saya</span>
                                <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </a>

                            <!-- 2. Profil Saya -->
                            <a href="{{ route('profile.edit') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-ink hover:bg-surface-soft hover:text-brand-600 dark:hover:text-brand-400 transition-colors" role="menuitem">
                                <span>Profil Saya</span>
                                <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </a>

                            <div class="my-1 border-t border-line"></div>

                            <!-- 3. Keluar -->
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-danger-fg hover:bg-danger-bg transition-colors cursor-pointer text-left" role="menuitem">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @else
                <!-- Guest: Tombol Masuk -->
                <a
                    href="{{ route('login') }}"
                    class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm hover:shadow transition-all duration-200 focus-visible:outline-2 focus-visible:outline-focus"
                >
                    Masuk
                </a>
            @endauth

            <!-- Mobile Hamburger Toggle Button -->
            <button
                type="button"
                id="nav-mobile-toggle-btn"
                aria-expanded="false"
                aria-controls="nav-mobile-drawer"
                aria-label="Buka navigasi mobile"
                class="lg:hidden p-2.5 rounded-xl border border-line bg-surface hover:bg-surface-soft text-text-muted hover:text-ink active:scale-95 transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-focus"
            >
                <svg id="hamburger-icon-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="hamburger-icon-close" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</header>

    <!-- Mobile Navigation Drawer -->
    <div id="nav-mobile-drawer" class="hidden fixed inset-0 z-50 w-screen opacity-0 transition-opacity duration-200 ease-out motion-reduce:transition-none lg:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi mobile" aria-hidden="true">
        <!-- Backdrop -->
        <div id="nav-mobile-backdrop" class="fixed inset-0 bg-black/60 opacity-0 backdrop-blur-xs transition-opacity duration-200 ease-out motion-reduce:transition-none cursor-pointer"></div>

        <!-- Drawer Content Box -->
        <div id="nav-mobile-panel" class="fixed inset-0 z-10 flex min-h-dvh w-screen max-w-none -translate-y-full flex-col justify-between overflow-y-auto bg-surface p-5 opacity-0 shadow-2xl transition-[translate,opacity] duration-300 ease-out motion-reduce:transition-none dark:bg-surface">
            <div class="space-y-5">
                <!-- Drawer Header -->
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-7 w-auto object-contain">
                    </a>
                    <button type="button" id="nav-mobile-close-btn" class="p-2 rounded-xl text-text-muted hover:text-ink hover:bg-surface-soft cursor-pointer text-lg leading-none" aria-label="Tutup navigasi">
                        &times;
                    </button>
                </div>

                <!-- Search Input Mobile -->
                <form action="{{ url('/jelajahi') }}" method="GET" class="relative">
                    <input
                        type="search"
                        name="q"
                        placeholder="Cari judul atau genre..."
                        value="{{ request('q') }}"
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-line bg-surface-soft text-ink text-xs placeholder:text-text-muted focus:outline-2 focus:outline-focus"
                    >
                    <svg class="w-4 h-4 text-text-muted absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>

                <!-- Navigation Links List (Vertical) -->
                <nav class="space-y-1.5 text-sm font-semibold">
                    <!-- Beranda -->
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center px-3.5 py-2.5 rounded-xl transition-colors {{ $isHomeActive ? 'bg-brand-500/10 dark:bg-brand-400/15 text-brand-600 dark:text-brand-400 font-bold' : 'text-ink hover:bg-surface-soft' }}"
                    >
                        Beranda
                    </a>

                    <!-- Jelajahi Accordion -->
                    <div class="rounded-xl overflow-hidden border border-line/60 bg-surface-soft/40">
                        <button
                            type="button"
                            id="mobile-jelajahi-accordion-btn"
                            aria-expanded="{{ $isJelajahiActive ? 'true' : 'false' }}"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-left font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            <span class="{{ $isJelajahiActive ? 'text-brand-600 dark:text-brand-400 font-bold' : '' }}">Jelajahi</span>
                            <svg id="mobile-jelajahi-chevron" class="w-4 h-4 text-text-muted transition-transform duration-200 {{ $isJelajahiActive ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Collapsible Genre Items (Novel & Keilmuan) -->
                        <div
                            id="mobile-jelajahi-accordion-panel"
                            aria-hidden="{{ $isJelajahiActive ? 'false' : 'true' }}"
                            @unless ($isJelajahiActive) inert @endunless
                            @unless ($isJelajahiActive) hidden @endunless
                        >
                            <div class="px-2 pb-2.5 pt-1 space-y-1 border-t border-line/40 text-sm">
                                <a href="{{ url('/jelajahi?genre=novel-fiksi') }}" class="block px-3 py-2 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft">Novel</a>
                                <a href="{{ url('/jelajahi?genre=keilmuan-edukasi') }}" class="block px-3 py-2 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft">Keilmuan</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tentang Kami -->
                    <a
                        href="{{ route('info.profil') }}"
                        class="flex items-center px-3.5 py-2.5 rounded-xl transition-colors {{ $isAboutActive ? 'bg-brand-500/10 dark:bg-brand-400/15 text-brand-600 dark:text-brand-400 font-bold' : 'text-ink hover:bg-surface-soft' }}"
                    >
                        Tentang Kami
                    </a>
                </nav>
            </div>

            <!-- Drawer Footer (Auth & User Info) -->
            <div class="pt-4 border-t border-line mt-6">
                @auth
                    <div class="p-3 rounded-2xl bg-surface-soft border border-line mb-3">
                        <div class="flex items-center gap-2.5">
                            @if ($currentUser->profile_photo_path)
                                <img
                                    src="{{ asset('storage/' . $currentUser->profile_photo_path) }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-9 w-9 shrink-0 rounded-full object-cover"
                                >
                            @else
                                <div class="w-9 h-9 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-ink truncate">{{ $currentUser->name }}</p>
                                <p class="text-[11px] text-text-muted truncate">{{ $currentUser->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 text-xs font-medium mb-3">
                        @if ($currentUser->isAdmin() || $currentUser->isPetugas())
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-brand-600 dark:text-brand-400 font-bold hover:bg-surface-soft">
                                Dashboard Pengelola &rarr;
                            </a>
                        @endif
                        @if ($currentUser->isAnggota())
                            <a href="{{ route('collection.index') }}" class="block px-3 py-2 rounded-xl text-ink hover:bg-surface-soft">
                                Koleksi Saya
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-xl text-ink hover:bg-surface-soft">
                            Profil Saya
                        </a>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-danger-bg text-danger-fg text-xs font-bold hover:opacity-90 transition-opacity cursor-pointer">
                            Keluar dari Akun
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="w-full flex items-center justify-center py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm transition-all"
                    >
                        Masuk ke Litera
                    </a>
                @endauth
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Jelajahi Genre Dropdown Desktop ---
        const jelajahiToggleBtn = document.getElementById('nav-jelajahi-toggle-btn');
        const jelajahiDropdown = document.getElementById('nav-jelajahi-dropdown-menu');
        const jelajahiChevron = document.getElementById('nav-jelajahi-chevron');
        const jelajahiContainer = document.getElementById('nav-jelajahi-container');

        function toggleJelajahi(open = null) {
            if (!jelajahiDropdown) return;
            const isOpen = open !== null ? !open : !jelajahiDropdown.classList.contains('hidden');
            if (isOpen) {
                jelajahiDropdown.classList.add('hidden');
                if (jelajahiChevron) jelajahiChevron.classList.remove('rotate-180');
                if (jelajahiToggleBtn) jelajahiToggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                jelajahiDropdown.classList.remove('hidden');
                if (jelajahiChevron) jelajahiChevron.classList.add('rotate-180');
                if (jelajahiToggleBtn) jelajahiToggleBtn.setAttribute('aria-expanded', 'true');
                closeUserMenu();
                closeSearchPopup();
            }
        }

        if (jelajahiToggleBtn) {
            jelajahiToggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleJelajahi();
            });
        }

        // --- 2. Search Popup Desktop ---
        const searchToggleBtn = document.getElementById('nav-search-toggle-btn');
        const searchPopup = document.getElementById('nav-search-popup');
        const searchCloseBtn = document.getElementById('nav-search-close-btn');
        const searchInput = document.getElementById('nav-search-input');
        const searchContainer = document.getElementById('nav-search-container');

        function toggleSearch(open = null) {
            if (!searchPopup) return;
            const isOpen = open !== null ? !open : !searchPopup.classList.contains('hidden');
            if (isOpen) {
                searchPopup.classList.add('hidden');
            } else {
                searchPopup.classList.remove('hidden');
                if (searchInput) setTimeout(() => searchInput.focus(), 60);
                toggleJelajahi(false);
                closeUserMenu();
            }
        }

        function closeSearchPopup() {
            if (searchPopup) searchPopup.classList.add('hidden');
        }

        if (searchToggleBtn) {
            searchToggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleSearch();
            });
        }

        if (searchCloseBtn) {
            searchCloseBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeSearchPopup();
            });
        }

        // --- 3. User Profile Dropdown Desktop ---
        const userToggleBtn = document.getElementById('nav-user-toggle-btn');
        const userDropdown = document.getElementById('nav-user-dropdown-menu');
        const userChevron = document.getElementById('nav-user-chevron');
        const userContainer = document.getElementById('nav-user-container');

        function toggleUserMenu(open = null) {
            if (!userDropdown) return;
            const isOpen = open !== null ? !open : !userDropdown.classList.contains('hidden');
            if (isOpen) {
                userDropdown.classList.add('hidden');
                if (userChevron) userChevron.classList.remove('rotate-180');
                if (userToggleBtn) userToggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                userDropdown.classList.remove('hidden');
                if (userChevron) userChevron.classList.add('rotate-180');
                if (userToggleBtn) userToggleBtn.setAttribute('aria-expanded', 'true');
                toggleJelajahi(false);
                closeSearchPopup();
            }
        }

        function closeUserMenu() {
            if (userDropdown) {
                userDropdown.classList.add('hidden');
                if (userChevron) userChevron.classList.remove('rotate-180');
                if (userToggleBtn) userToggleBtn.setAttribute('aria-expanded', 'false');
            }
        }

        if (userToggleBtn) {
            userToggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleUserMenu();
            });
        }

        // --- 4. Mobile Drawer & Accordion ---
        const mobileToggleBtn = document.getElementById('nav-mobile-toggle-btn');
        const mobileDrawer = document.getElementById('nav-mobile-drawer');
        const mobileBackdrop = document.getElementById('nav-mobile-backdrop');
        const mobilePanel = document.getElementById('nav-mobile-panel');
        const mobileCloseBtn = document.getElementById('nav-mobile-close-btn');
        const hamburgerOpenIcon = document.getElementById('hamburger-icon-open');
        const hamburgerCloseIcon = document.getElementById('hamburger-icon-close');

        const mobileAccordionBtn = document.getElementById('mobile-jelajahi-accordion-btn');
        const mobileAccordionPanel = document.getElementById('mobile-jelajahi-accordion-panel');
        const mobileAccordionChevron = document.getElementById('mobile-jelajahi-chevron');
        let mobileCloseTimeout = null;
        let mobileOpenFrame = null;

        function openMobileDrawer() {
            if (!mobileDrawer) {
                return;
            }

            window.clearTimeout(mobileCloseTimeout);
            if (mobileOpenFrame !== null) {
                window.cancelAnimationFrame(mobileOpenFrame);
            }

            mobileDrawer.classList.remove('hidden');
            mobileDrawer.classList.add('opacity-0');
            mobileDrawer.setAttribute('aria-hidden', 'false');
            mobileDrawer.removeAttribute('inert');
            document.body.classList.add('overflow-hidden');
            if (mobileToggleBtn) {
                mobileToggleBtn.setAttribute('aria-expanded', 'true');
                mobileToggleBtn.setAttribute('aria-label', 'Tutup navigasi mobile');
            }
            if (hamburgerOpenIcon) hamburgerOpenIcon.classList.add('hidden');
            if (hamburgerCloseIcon) hamburgerCloseIcon.classList.remove('hidden');

            mobileOpenFrame = window.requestAnimationFrame(() => {
                if (mobileDrawer.getAttribute('aria-hidden') !== 'false') {
                    return;
                }

                mobileDrawer.classList.remove('opacity-0');
                mobileDrawer.classList.add('opacity-100');
                if (mobileBackdrop) {
                    mobileBackdrop.classList.remove('opacity-0');
                    mobileBackdrop.classList.add('opacity-100');
                }
                if (mobilePanel) {
                    mobilePanel.classList.remove('-translate-y-full', 'opacity-0');
                    mobilePanel.classList.add('translate-y-0', 'opacity-100');
                }
            });

            if (mobileCloseBtn) {
                mobileCloseBtn.focus();
            }
        }

        function closeMobileDrawer() {
            if (!mobileDrawer || mobileDrawer.getAttribute('aria-hidden') !== 'false') {
                return;
            }

            if (mobileOpenFrame !== null) {
                window.cancelAnimationFrame(mobileOpenFrame);
                mobileOpenFrame = null;
            }

            mobileDrawer.setAttribute('aria-hidden', 'true');
            mobileDrawer.setAttribute('inert', '');
            mobileDrawer.classList.remove('opacity-100');
            mobileDrawer.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            if (mobileToggleBtn) {
                mobileToggleBtn.setAttribute('aria-expanded', 'false');
                mobileToggleBtn.setAttribute('aria-label', 'Buka navigasi mobile');
            }
            if (hamburgerOpenIcon) hamburgerOpenIcon.classList.remove('hidden');
            if (hamburgerCloseIcon) hamburgerCloseIcon.classList.add('hidden');
            if (mobileBackdrop) {
                mobileBackdrop.classList.remove('opacity-100');
                mobileBackdrop.classList.add('opacity-0');
            }
            if (mobilePanel) {
                mobilePanel.classList.remove('translate-y-0', 'opacity-100');
                mobilePanel.classList.add('-translate-y-full', 'opacity-0');
            }

            window.clearTimeout(mobileCloseTimeout);
            const closeDelay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 300;
            mobileCloseTimeout = window.setTimeout(() => {
                if (mobileDrawer.getAttribute('aria-hidden') === 'true') {
                    mobileDrawer.classList.add('hidden');
                    if (mobileToggleBtn && window.innerWidth < 1024) mobileToggleBtn.focus();
                }
            }, closeDelay);
        }

        if (mobileToggleBtn) {
            mobileToggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (mobileDrawer?.getAttribute('aria-hidden') === 'false') {
                    closeMobileDrawer();
                } else {
                    openMobileDrawer();
                }
            });
        }

        if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileDrawer);
        if (mobileCloseBtn) mobileCloseBtn.addEventListener('click', closeMobileDrawer);

        if (mobileAccordionBtn && mobileAccordionPanel) {
            mobileAccordionBtn.addEventListener('click', function () {
                const isExpanded = mobileAccordionBtn.getAttribute('aria-expanded') === 'true';
                mobileAccordionBtn.setAttribute('aria-expanded', String(!isExpanded));
                mobileAccordionPanel.setAttribute('aria-hidden', String(isExpanded));

                if (isExpanded) {
                    mobileAccordionPanel.setAttribute('hidden', '');
                    mobileAccordionPanel.setAttribute('inert', '');
                    if (mobileAccordionChevron) mobileAccordionChevron.classList.remove('rotate-180');
                } else {
                    mobileAccordionPanel.removeAttribute('hidden');
                    mobileAccordionPanel.removeAttribute('inert');
                    if (mobileAccordionChevron) mobileAccordionChevron.classList.add('rotate-180');
                }
            });
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && mobileDrawer?.getAttribute('aria-hidden') === 'false') {
                closeMobileDrawer();
            }
        });

        // --- 5. Global Document Listeners (Click outside & Escape key) ---
        document.addEventListener('click', function (e) {
            if (jelajahiContainer && !jelajahiContainer.contains(e.target)) {
                toggleJelajahi(false);
            }
            if (searchContainer && !searchContainer.contains(e.target)) {
                closeSearchPopup();
            }
            if (userContainer && !userContainer.contains(e.target)) {
                closeUserMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Tab' && mobileDrawer?.getAttribute('aria-hidden') === 'false') {
                const focusableElements = mobileDrawer.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])');
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement?.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement?.focus();
                }
            }

            if (e.key === 'Escape') {
                toggleJelajahi(false);
                closeSearchPopup();
                closeUserMenu();
                closeMobileDrawer();
            }
        });
    });
</script>

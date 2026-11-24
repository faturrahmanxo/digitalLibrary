<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Pustaka &amp; Koleksi Riset &mdash; Litera</title>
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
    <style>
        /* Planetary Solar System & Disciplines Orbit Animation */
        @keyframes orbitClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes orbitCounterClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }
        @keyframes planetPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 20px rgba(0, 90, 157, 0.35)); }
            50% { transform: scale(1.04); filter: drop-shadow(0 0 35px rgba(2, 132, 199, 0.6)); }
        }
        .animate-orbit-slow {
            animation: orbitClockwise 36s linear infinite;
        }
        .animate-counter-slow {
            animation: orbitCounterClockwise 36s linear infinite;
        }
        .animate-orbit-slower-rev {
            animation: orbitCounterClockwise 52s linear infinite;
        }
        .animate-counter-slower-rev {
            animation: orbitClockwise 52s linear infinite;
        }
        .animate-planet-pulse {
            animation: planetPulse 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">

    <!-- Public Multi-Genre Litera Navigation Bar -->
    <x-navbar-public />

    <!-- ======================================================== -->
    <!-- FULL-WIDTH HERO SECTION (Edge-to-Edge Canvas Background) -->
    <!-- ======================================================== -->
    <section class="w-full bg-gradient-to-b from-canvas via-surface/40 to-canvas border-b border-line py-12 sm:py-16 md:py-20 relative overflow-hidden">
        
        <!-- Ambient Glowing Background Spheres -->
        <div class="absolute -top-12 left-1/4 w-96 h-96 bg-brand-600/5 dark:bg-brand-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute -bottom-12 right-1/4 w-96 h-96 bg-brand-400/10 dark:bg-brand-600/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Left Editorial Column: Eyebrow, Heading, Subtitle & Search Filter -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-5 text-center lg:text-left">
                    
                    <!-- Eyebrow Campus Badge -->
                    <div class="inline-flex items-center gap-2.5 justify-center lg:justify-start">
                        <span class="w-8 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                        <span class="text-[11px] sm:text-xs font-semibold tracking-[0.22em] text-brand-600 dark:text-brand-400 uppercase font-sans">
                            Eksplorasi Literatur Kampus
                        </span>
                    </div>

                    <!-- Main Editorial Title (Customized & Distinct) -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[48px] font-bold text-ink tracking-tight font-serif leading-[1.16]">
                        <span class="block">Katalog Pustaka &amp;</span>
                        <span class="font-normal italic bg-gradient-to-r from-brand-600 via-gold to-brand-600 dark:from-brand-400 dark:via-gold dark:to-brand-400 bg-clip-text text-transparent animate-gradient-flow inline-block">
                            Koleksi Riset Akademik.
                        </span>
                    </h1>

                    <p class="text-xs sm:text-sm text-text-muted max-w-xl mx-auto lg:mx-0 leading-relaxed font-sans">
                        Akses terkurasi menuju ribuan buku teks perkuliahan, karya ilmiah, dan literatur referensi universitas. Temukan nomor panggil, lokasi rak, dan ketersediaan koleksi secara instan.
                    </p>

                    <!-- Search Input & Filter Button Toolbar -->
                    <form method="GET" action="{{ route('catalog.index') }}" class="pt-2">
                        @if ($collectionType)<input type="hidden" name="collection_type" value="{{ $collectionType }}">@endif
                        @if ($categoryId)<input type="hidden" name="category_id" value="{{ $categoryId }}">@endif
                        @if ($author)<input type="hidden" name="author" value="{{ $author }}">@endif
                        @if ($isbn)<input type="hidden" name="isbn" value="{{ $isbn }}">@endif
                        @if ($publisher)<input type="hidden" name="publisher" value="{{ $publisher }}">@endif
                        @if ($year)<input type="hidden" name="year" value="{{ $year }}">@endif
                        @if ($availability)<input type="hidden" name="availability" value="{{ $availability }}">@endif
                        @if ($sort)<input type="hidden" name="sort" value="{{ $sort }}">@endif

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-2xl mx-auto lg:mx-0">
                            <!-- Search Input with Embedded 'Cari' Button -->
                            <div class="relative flex-1 flex items-center rounded-2xl bg-surface border-2 border-line focus-within:border-brand-600 dark:focus-within:border-brand-400 shadow-sm transition-all p-1.5">
                                <div class="pl-3.5 pr-2 text-text-muted">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Cari judul buku, pengarang, ISBN, atau topik riset..."
                                    class="w-full bg-transparent border-0 text-xs sm:text-sm text-ink placeholder:text-text-muted/70 focus:outline-none focus:ring-0 px-2 py-2"
                                >
                                <button
                                    type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm shrink-0 cursor-pointer"
                                >
                                    Cari
                                </button>
                            </div>

                            <!-- Filter Button (Opens Modal) -->
                            <button
                                type="button"
                                id="btn-open-filter-modal"
                                class="relative px-4 py-3 rounded-2xl border-2 border-line bg-surface hover:bg-surface-soft text-ink text-xs font-bold shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer hover:border-brand-600 dark:hover:border-brand-400 shrink-0 active:scale-95"
                            >
                                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Filter</span>
                                @php
                                    $activeFilterCount = (int)($categoryId !== null) + (int)($author !== '') + (int)($isbn !== '') + (int)($publisher !== '') + (int)($year !== null) + (int)($availability !== '') + (int)($collectionType !== '');
                                @endphp
                                @if ($activeFilterCount > 0)
                                    <span class="w-5 h-5 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center">
                                        {{ $activeFilterCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </form>

                    <!-- Metric Result Badge & Quick Reset -->
                    <div class="mt-4 flex flex-wrap items-center justify-center lg:justify-start gap-2 text-xs">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface border border-line font-medium text-text-muted shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                            <span><strong class="text-ink font-serif font-italiana font-bold text-sm">{{ number_format($books->total(), 0, ',', '.') }}</strong> judul buku ditemukan</span>
                        </span>

                        @if ($search || $categoryId || $author || $isbn || $publisher || $year || $availability || $collectionType)
                            <a href="{{ route('catalog.index') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline ml-2">
                                Reset Semua Filter &times;
                            </a>
                        @endif
                    </div>

                </div>

                <!-- Right Column: Planetary Library Core with Orbiting Academic Discipline Logos -->
                <div class="lg:col-span-5 xl:col-span-4 flex items-center justify-center lg:justify-end select-none pointer-events-none">
                    <div class="relative w-72 h-72 sm:w-84 sm:h-84 md:w-92 md:h-92 flex items-center justify-center">
                        
                        <!-- Outer Atmospheric Radial Glow -->
                        <div class="absolute inset-0 bg-radial from-brand-600/15 via-brand-600/10 to-transparent rounded-full blur-2xl"></div>

                        <!-- 1. OUTER ORBIT TRACK (52s Counter-Clockwise Rotation) -->
                        <div class="absolute w-[260px] h-[260px] sm:w-[290px] sm:h-[290px] md:w-[320px] md:h-[320px] rounded-full border border-dashed border-brand-600/25 dark:border-brand-400/30 animate-orbit-slower-rev">
                            
                            <!-- Satellite 1 (Top-Right ~45deg): Hukum & Timbangan Keadilan -->
                            <div class="absolute" style="top: 14.64%; left: 85.36%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slower-rev">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-amber-500/40 text-amber-500 shadow-lg flex items-center justify-center" title="Ilmu Hukum & Keadilan">
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 2 (Bottom-Right ~135deg): Ekonomi & Finansial (Trending Chart) -->
                            <div class="absolute" style="top: 85.36%; left: 85.36%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slower-rev">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-emerald-500/40 text-emerald-500 shadow-lg flex items-center justify-center" title="Ekonomi & Bisnis">
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 3 (Bottom-Left ~225deg): Sastra, Filsafat & Humaniora (Quill Pen) -->
                            <div class="absolute" style="top: 85.36%; left: 14.64%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slower-rev">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-lg flex items-center justify-center" title="Sastra & Humaniora">
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 4 (Top-Left ~315deg): Seni & Desain (Creative Sparkle / Star) -->
                            <div class="absolute" style="top: 14.64%; left: 14.64%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slower-rev">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-pink-500/40 text-pink-500 shadow-lg flex items-center justify-center" title="Seni & Desain Arsitektur">
                                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 2. INNER ORBIT TRACK (36s Clockwise Rotation) -->
                        <div class="absolute w-[170px] h-[170px] sm:w-[195px] sm:h-[195px] md:w-[215px] md:h-[215px] rounded-full border border-dashed border-brand-600/30 dark:border-brand-400/40 animate-orbit-slow">
                            
                            <!-- Satellite 5 (Top 0deg): Informatika & Pemrograman (Code </>) -->
                            <div class="absolute" style="top: 0%; left: 50%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slow">
                                    <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-lg flex items-center justify-center" title="Teknologi & Ilmu Komputer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 6 (Right 90deg): Sains Eksakta & Kimia / Fisika (Lab Flask) -->
                            <div class="absolute" style="top: 50%; left: 100%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slow">
                                    <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-600 dark:text-brand-400 shadow-lg flex items-center justify-center" title="Sains & Eksakta">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 7 (Bottom 180deg): Kedokteran & Ilmu Kesehatan (Heartbeat / Pulse) -->
                            <div class="absolute" style="top: 100%; left: 50%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slow">
                                    <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-rose-500/40 text-rose-500 shadow-lg flex items-center justify-center" title="Kedokteran & Kesehatan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Satellite 8 (Left 270deg): Matematika & Statistika (Pie Chart / Sigma) -->
                            <div class="absolute" style="top: 50%; left: 0%; transform: translate(-50%, -50%);">
                                <div class="animate-counter-slow">
                                    <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-lg flex items-center justify-center" title="Matematika & Statistika">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 3. CENTRAL PLANET BUKU (Knowledge Core) -->
                        <div class="relative z-20 animate-planet-pulse flex items-center justify-center">
                            <!-- Atmospheric Glowing Halo -->
                            <div class="absolute -inset-3 rounded-full bg-radial from-brand-600/40 via-brand-600/20 to-transparent blur-lg"></div>
                            
                            <!-- Planet Sphere Body -->
                            <div class="relative w-22 h-22 sm:w-26 sm:h-26 md:w-28 md:h-28 rounded-full bg-gradient-to-tr from-[#15241B] via-[#294638] to-[#92703D] p-1 shadow-2xl border-2 border-white/40 flex items-center justify-center overflow-hidden">
                                <!-- Subtle Planet Diagonal Texture -->
                                <div class="absolute inset-0 motif-diagonal-lines opacity-25 pointer-events-none"></div>
                                <div class="absolute -inset-1 rounded-full bg-gradient-to-b from-white/20 via-transparent to-black/35 pointer-events-none"></div>

                                <!-- Glowing Open Book Core Symbol -->
                                <div class="relative z-10 w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- MAIN CATALOG BOOK GRID (Identical Card Design as Beranda)-->
    <!-- ======================================================== -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        
        <!-- Format Koleksi Quick Switcher (Semua, Buku Fisik, E-book) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="inline-flex p-1 rounded-2xl bg-surface border border-line shadow-2xs self-start">
                <a
                    href="{{ route('catalog.index', array_filter(array_merge(request()->query(), ['collection_type' => '']))) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ empty($collectionType) ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    Semua Koleksi
                </a>
                <a
                    href="{{ route('catalog.index', array_merge(request()->query(), ['collection_type' => 'physical'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $collectionType === 'physical' ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    Buku Fisik
                </a>
                <a
                    href="{{ route('catalog.index', array_merge(request()->query(), ['collection_type' => 'ebook'])) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ in_array($collectionType, ['ebook', 'e-book'], true) ? 'bg-brand-600 text-white shadow-xs' : 'text-text-muted hover:text-ink' }}"
                >
                    E-book
                </a>
            </div>

            @if ($collectionType)
                <span class="text-xs font-medium text-text-muted">
                    Menampilkan format: <strong class="text-ink">{{ $collectionType === 'physical' ? 'Buku Fisik' : 'E-book' }}</strong>
                </span>
            @endif
        </div>

        @if ($books->isEmpty())
            <div class="p-16 text-center rounded-3xl border border-line bg-surface max-w-lg mx-auto shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-surface-soft text-text-muted flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Tidak Ada Koleksi yang Cocok</h3>
                <p class="text-xs text-text-muted mt-1.5 mb-5 max-w-sm mx-auto">
                    Koleksi yang Anda cari belum ditemukan. Silakan periksa kembali filter pencarian atau kata kunci Anda.
                </p>
                <a href="{{ route('catalog.index') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm">
                    Tampilkan Seluruh Koleksi
                </a>
            </div>
        @else
            <!-- 4-Column Book Cards Grid (Same Style & Unified Forest Green & Ivory Theme as Beranda) -->
            @php
                $cardGradient = 'linear-gradient(135deg, #15241B 0%, #294638 100%)';
            @endphp
            <div class="grid grid-cols-1 min-[400px]:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 sm:gap-6 mb-12">
                @foreach ($books as $index => $book)
                    @if ($book->collection_type === 'e-book' || $book->collection_type === 'novel')
                        <x-book-card-ebook
                            :book="$book"
                            :href="route('books.show', $book)"
                            :cover-label="$book->collection_type === 'novel' ? 'novel' : 'e-book'"
                            class="w-full"
                        />
                    @else
                        <a
                            href="{{ route('books.show', $book) }}"
                            class="group card-interactive flex flex-col justify-between cursor-pointer focus:outline-none"
                            title="{{ $book->title }}"
                        >
                            <!-- Minimalist Large Book Cover Container (Matching Beranda) -->
                            <div class="aspect-[3/4.2] w-full rounded-3xl overflow-hidden relative shadow-md border border-line flex items-center justify-center bg-surface-soft">
                                
                                @if ($book->cover_path)
                                    <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <!-- Fallback Artistic Graphic with Diagonal Pinstripe Motif & Unified Forest Green Gradient -->
                                    <div
                                        class="w-full h-full p-6 flex flex-col items-center justify-center text-white relative overflow-hidden group-hover:scale-105 transition-transform duration-300"
                                        style="background: {{ $cardGradient }};"
                                    >
                                        <div class="absolute inset-0 motif-diagonal-lines opacity-35 pointer-events-none"></div>
                                        <div class="relative z-10 w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-lg group-hover:scale-110 transition-transform">
                                            <span class="font-serif font-bold text-3xl sm:text-4xl text-white tracking-wider">
                                                {{ strtoupper(substr($book->title, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Category Badge at Top Left Inside Cover -->
                                <div class="absolute top-3.5 left-3.5 z-20 pointer-events-none">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-black/65 dark:bg-black/80 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase font-sans shadow-sm">
                                        {{ $book->category?->name ?? 'UMUM' }}
                                    </span>
                                </div>

                                <!-- Format Badge at Top Right Inside Cover -->
                                <div class="absolute top-3.5 right-3.5 z-20 pointer-events-none">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-brand-600/90 text-white text-[9px] font-extrabold tracking-wider uppercase font-sans shadow-xs">
                                        FISIK
                                    </span>
                                </div>

                                @if ($book->isPhysical() && $book->available_copies <= 0)
                                    <div class="absolute bottom-3 left-3 right-3 z-20 pointer-events-none text-center">
                                        <span class="inline-block w-full px-2 py-1 rounded-lg bg-rose-600/90 text-white text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                            Stok Habis
                                        </span>
                                    </div>
                                @endif

                            </div>

                            <!-- Metadata Below Card (Title, Author, Year - Same Style as Beranda) -->
                            <div class="pt-4 flex-1 flex flex-col">
                                <h3 class="text-sm sm:text-base font-bold text-ink font-sans tracking-tight line-clamp-2 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors" title="{{ $book->title }}">
                                    {{ $book->title }}
                                </h3>
                                <p class="text-xs text-text-muted uppercase tracking-wider truncate mt-1.5 font-sans font-medium">
                                    {{ $book->author }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-text-muted mt-2 pt-2 border-t border-line/60">
                                    <span class="font-year font-bold text-ink/90">{{ $book->publication_year }}</span>
                                    <span class="text-[11px] font-semibold text-brand-600 dark:text-brand-400">
                                        {{ $book->shelf?->code ? 'Rak ' . $book->shelf->code : ($book->available_copies > 0 ? $book->available_copies . ' Stok' : 'Habis') }}
                                    </span>
                                </div>
                            </div>

                        </a>
                    @endif
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-8 flex justify-center">
                {{ $books->links() }}
            </div>
        @endif

    </main>

    <!-- ======================================================== -->
    <!-- MODERN FILTER MODAL DIALOG (Gunadarma Style Filtering)   -->
    <!-- ======================================================== -->
    <div id="filter-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="filter-modal-backdrop" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
        
        <!-- Modal Content Card -->
        <div class="relative w-full max-w-2xl rounded-3xl bg-surface border border-line p-6 sm:p-8 shadow-2xl z-10 animate-fade-in-up">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-line mb-6">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-500/10 dark:bg-brand-400/15 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-ink">Filter Pencarian Katalog</h2>
                </div>
                <button
                    type="button"
                    id="btn-close-filter-modal"
                    class="p-2 rounded-xl text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                    aria-label="Tutup filter"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('catalog.index') }}" class="space-y-4">
                <!-- Keep Current Search Term If Available -->
                <input type="hidden" name="q" value="{{ $search }}">

                <!-- Row 1: Format Koleksi & Kategori -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Format Koleksi</label>
                        <select
                            name="collection_type"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all cursor-pointer"
                        >
                            <option value="">Semua Format (Fisik &amp; E-book)</option>
                            <option value="physical" @selected($collectionType === 'physical')>Buku Fisik (Tercetak di Rak)</option>
                            <option value="ebook" @selected(in_array($collectionType, ['ebook', 'e-book'], true))>E-book (Akses Digital)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Klasifikasi Kategori</label>
                        <select
                            name="category_id"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all cursor-pointer"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((int) $categoryId === $cat->id)>
                                    {{ $cat->name }} ({{ $cat->books_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Pengarang & Penerbit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Pengarang</label>
                        <input
                            type="text"
                            name="author"
                            value="{{ $author }}"
                            placeholder="Nama pengarang..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Penerbit</label>
                        <input
                            type="text"
                            name="publisher"
                            value="{{ $publisher }}"
                            placeholder="Nama penerbit..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all"
                        >
                    </div>
                </div>

                <!-- Row 3: ISBN, Tahun Terbit & Ketersediaan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">ISBN</label>
                        <input
                            type="text"
                            name="isbn"
                            value="{{ $isbn }}"
                            placeholder="978-xxx-..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Tahun Terbit</label>
                        <select
                            name="year"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all cursor-pointer"
                        >
                            <option value="">Semua Tahun</option>
                            @foreach ($years as $yr)
                                <option value="{{ $yr }}" @selected((int) $year === (int) $yr)>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-text-muted mb-1.5">Ketersediaan Rak Fisik</label>
                        <select
                            name="availability"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all cursor-pointer"
                        >
                            <option value="">Semua Status</option>
                            <option value="available" @selected($availability === 'available')>Tersedia untuk Dipinjam</option>
                            <option value="empty" @selected($availability === 'empty')>Sedang Dipinjam / Kosong</option>
                        </select>
                    </div>
                </div>

                <!-- Row 4: Urutan Tampilan -->
                <div>
                    <label class="block text-xs font-semibold text-text-muted mb-1.5">Urutan Tampilan</label>
                    <select
                        name="sort"
                        class="w-full px-3.5 py-2.5 rounded-2xl border border-line bg-surface text-ink text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400 transition-all cursor-pointer"
                    >
                        <option value="newest" @selected($sort === 'newest')>Terbaru Masuk</option>
                        <option value="popular" @selected($sort === 'popular')>Stok Terbanyak</option>
                        <option value="title_asc" @selected($sort === 'title_asc')>Judul A &ndash; Z</option>
                        <option value="title_desc" @selected($sort === 'title_desc')>Judul Z &ndash; A</option>
                        <option value="year_desc" @selected($sort === 'year_desc')>Tahun Rilis (Terbaru)</option>
                    </select>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-line mt-6">
                    <a
                        href="{{ route('catalog.index', $search ? ['q' => $search] : []) }}"
                        class="text-xs font-semibold text-text-muted hover:text-ink transition-colors"
                    >
                        Reset Filter
                    </a>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            onclick="closeFilterModal()"
                            class="px-4 py-2.5 rounded-xl border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer"
                        >
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <x-footer-public />

    <script>
        // Filter Modal Controls
        const filterModal = document.getElementById('filter-modal');
        const filterBackdrop = document.getElementById('filter-modal-backdrop');
        const openFilterBtn = document.getElementById('btn-open-filter-modal');
        const closeFilterBtn = document.getElementById('btn-close-filter-modal');

        function openFilterModal() {
            if (filterModal) {
                filterModal.classList.remove('hidden');
                filterModal.classList.add('flex');
            }
        }

        function closeFilterModal() {
            if (filterModal) {
                filterModal.classList.add('hidden');
                filterModal.classList.remove('flex');
            }
        }

        if (openFilterBtn) openFilterBtn.addEventListener('click', openFilterModal);
        if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeFilterModal);
        if (filterBackdrop) filterBackdrop.addEventListener('click', closeFilterModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeFilterModal();
        });

    </script>
</body>
</html>

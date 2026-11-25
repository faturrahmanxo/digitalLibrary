@props([
    'eyebrow' => 'Eksplorasi Literatur Kampus',
    'title' => 'Katalog Koleksi Pustaka',
    'highlight' => 'Koleksi Riset Akademik.',
    'description' => 'Akses terkurasi menuju ribuan buku fisik dan e-book perpustakaan kampus.',
    'search' => '',
    'collectionType' => '',
    'totalBooks' => 0,
    'activeFilterCount' => 0,
    'hasActiveFilters' => false,
])

<section class="w-full bg-gradient-to-b from-canvas via-surface/40 to-canvas border-b border-line py-10 sm:py-14 md:py-16 relative overflow-hidden">
    <!-- Ambient Glowing Background Spheres -->
    <div class="absolute -top-12 left-1/4 w-96 h-96 bg-brand-600/5 dark:bg-brand-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute -bottom-12 right-1/4 w-96 h-96 bg-brand-400/10 dark:bg-brand-600/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Editorial Column: Eyebrow, Heading, Subtitle & Search Filter -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-4 sm:space-y-5 text-center lg:text-left">
                
                <!-- Eyebrow Campus Badge -->
                <div class="inline-flex items-center gap-2.5 justify-center lg:justify-start">
                    <span class="w-8 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                    <span class="text-[11px] sm:text-xs font-semibold tracking-[0.22em] text-brand-600 dark:text-brand-400 uppercase font-sans">
                        {{ $eyebrow }}
                    </span>
                </div>

                <!-- Main Editorial Title -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[46px] font-bold text-ink tracking-tight font-serif leading-[1.16]">
                    <span class="block">{{ $title }}</span>
                    @if (filled($highlight))
                        <span class="font-normal italic bg-gradient-to-r from-brand-600 via-gold to-brand-600 dark:from-brand-400 dark:via-gold dark:to-brand-400 bg-clip-text text-transparent inline-block">
                            {{ $highlight }}
                        </span>
                    @endif
                </h1>

                <p class="text-xs sm:text-sm text-text-muted max-w-xl mx-auto lg:mx-0 leading-relaxed font-sans">
                    {{ $description }}
                </p>

                <!-- Search Input & Filter Button Toolbar -->
                <form method="GET" action="{{ route('catalog.index') }}" class="pt-1 sm:pt-2">
                    @if ($collectionType)<input type="hidden" name="collection_type" value="{{ $collectionType }}">@endif
                    @foreach (request()->except(['q', 'page', 'collection_type']) as $key => $val)
                        @if (filled($val))
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-2xl mx-auto lg:mx-0">
                        <!-- Search Input with Embedded 'Cari' Button -->
                        <div class="relative flex-1 flex items-center rounded-2xl bg-surface border-2 border-line focus-within:border-brand-600 dark:focus-within:border-brand-400 shadow-xs transition-all p-1.5">
                            <div class="pl-3.5 pr-2 text-text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Cari judul, pengarang, ISBN, atau topik riset..."
                                class="w-full bg-transparent border-0 text-xs sm:text-sm text-ink placeholder:text-text-muted/70 focus:outline-none focus:ring-0 px-2 py-2 font-sans"
                            >
                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs shrink-0 cursor-pointer"
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
                            @if ($activeFilterCount > 0)
                                <span class="w-5 h-5 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $activeFilterCount }}
                                </span>
                            @endif
                        </button>
                    </div>
                </form>

                <!-- Metric Result Badge & Quick Reset -->
                <div class="mt-3 sm:mt-4 flex flex-wrap items-center justify-center lg:justify-start gap-2 text-xs">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface border border-line font-medium text-text-muted shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-brand-600 dark:bg-brand-400"></span>
                        <span><strong class="text-ink font-serif font-italiana font-bold text-sm">{{ number_format($totalBooks, 0, ',', '.') }}</strong> judul buku ditemukan</span>
                    </span>

                    @if ($hasActiveFilters)
                        <a href="{{ route('catalog.index', $collectionType ? ['collection_type' => $collectionType] : []) }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline ml-2">
                            Reset Semua Filter &times;
                        </a>
                    @endif
                </div>

            </div>

            <!-- Right Column: Planetary Library Core Animation (Identical on Both Pages) -->
            <div class="lg:col-span-5 xl:col-span-4 flex items-center justify-center lg:justify-end select-none pointer-events-none">
                <div class="relative w-72 h-72 sm:w-80 sm:h-80 md:w-88 md:h-88 flex items-center justify-center">
                    
                    <!-- Outer Atmospheric Radial Glow -->
                    <div class="absolute inset-0 bg-radial from-brand-600/15 via-brand-600/10 to-transparent rounded-full blur-2xl"></div>

                    <!-- 1. OUTER ORBIT TRACK (52s Counter-Clockwise Rotation) -->
                    <div class="absolute w-[250px] h-[250px] sm:w-[280px] sm:h-[280px] md:w-[310px] md:h-[310px] rounded-full border border-dashed border-brand-600/25 dark:border-brand-400/30 animate-orbit-slower-rev">
                        <!-- Satellite 1: Hukum & Timbangan -->
                        <div class="absolute" style="top: 14.64%; left: 85.36%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slower-rev">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-amber-500/40 text-amber-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 2: Ekonomi & Bisnis -->
                        <div class="absolute" style="top: 85.36%; left: 85.36%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slower-rev">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-emerald-500/40 text-emerald-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 3: Sastra & Humaniora -->
                        <div class="absolute" style="top: 85.36%; left: 14.64%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slower-rev">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 4: Seni & Kreativitas -->
                        <div class="absolute" style="top: 14.64%; left: 14.64%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slower-rev">
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-pink-500/40 text-pink-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. INNER ORBIT TRACK (36s Clockwise Rotation) -->
                    <div class="absolute w-[165px] h-[165px] sm:w-[190px] sm:h-[190px] md:w-[210px] md:h-[210px] rounded-full border border-dashed border-brand-600/30 dark:border-brand-400/40 animate-orbit-slow">
                        <!-- Satellite 5: Informatika & Teknologi -->
                        <div class="absolute" style="top: 0%; left: 50%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slow">
                                <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 6: Sains Eksakta -->
                        <div class="absolute" style="top: 50%; left: 100%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slow">
                                <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-600 dark:text-brand-400 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 7: Kedokteran & Kesehatan -->
                        <div class="absolute" style="top: 100%; left: 50%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slow">
                                <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-rose-500/40 text-rose-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Satellite 8: Matematika & Analitika -->
                        <div class="absolute" style="top: 50%; left: 0%; transform: translate(-50%, -50%);">
                            <div class="animate-counter-slow">
                                <div class="w-7.5 h-7.5 sm:w-8.5 sm:h-8.5 rounded-full bg-surface/90 dark:bg-surface/95 backdrop-blur-md border border-brand-500/40 text-brand-500 shadow-md flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. CENTRAL PLANET CORE (Knowledge Core) -->
                    <div class="relative z-20 animate-planet-pulse flex items-center justify-center">
                        <div class="absolute -inset-3 rounded-full bg-radial from-brand-600/40 via-brand-600/20 to-transparent blur-lg"></div>
                        <div class="relative w-22 h-22 sm:w-26 sm:h-26 md:w-28 md:h-28 rounded-full bg-gradient-to-tr from-[#15241B] via-[#294638] to-[#92703D] p-1 shadow-2xl border-2 border-white/40 flex items-center justify-center overflow-hidden">
                            <div class="absolute inset-0 motif-diagonal-lines opacity-25 pointer-events-none"></div>
                            <div class="absolute -inset-1 rounded-full bg-gradient-to-b from-white/20 via-transparent to-black/35 pointer-events-none"></div>

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

<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Litera — Perpustakaan Digital Kampus</title>
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
        .font-brand-script {
            font-family: 'Cormorant Garamond', Georgia, serif;
            letter-spacing: 0.01em;
        }
        /* Entrance animation for 3D book and floating symbols */
        @keyframes heroEntrance {
            0% {
                opacity: 0;
                transform: translateY(28px) scale(0.92);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .animate-hero-entrance {
            animation: heroEntrance 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* 3D Fan-Deck Showcase Styling */
        .fan-deck-container {
            perspective: 1200px;
        }
        .fan-card {
            transform-style: preserve-3d;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fan-card-1 { transform: rotate(-14deg) translateY(24px) scale(0.9); z-index: 10; }
        .fan-card-2 { transform: rotate(-7deg) translateY(12px) scale(0.95); z-index: 20; }
        .fan-card-3 { transform: rotate(0deg) translateY(0px) scale(1.05); z-index: 30; }
        .fan-card-4 { transform: rotate(7deg) translateY(12px) scale(0.95); z-index: 20; }
        .fan-card-5 { transform: rotate(14deg) translateY(24px) scale(0.9); z-index: 10; }

        .fan-card:hover {
            transform: translateY(-16px) scale(1.08) rotate(0deg) !important;
            z-index: 50 !important;
            box-shadow: 0 25px 50px -12px rgba(20, 51, 93, 0.35);
        }

        @keyframes heroPageDrift {
            0%, 70%, 100% {
                transform: perspective(700px) rotateY(0deg);
            }
            85% {
                transform: perspective(700px) rotateY(-5deg);
            }
        }

        @keyframes heroBookFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-7px);
            }
        }

        @keyframes heroElementFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes heroWordFadeUp {
            from {
                opacity: 0;
                transform: translateY(0.35em);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-word-fade-up {
            animation: heroWordFadeUp 550ms ease-out both;
        }

        .hero-page-left {
            transform-box: fill-box;
            transform-origin: right center;
            animation: heroPageDrift 8.5s ease-in-out infinite;
        }

        .hero-page-right {
            transform-box: fill-box;
            transform-origin: left center;
            animation: heroPageDrift 9s ease-in-out infinite reverse;
        }

        .hero-book-float { animation: heroBookFloat 5.5s ease-in-out infinite; }
        .hero-float-1 { animation: heroElementFloat 6.5s ease-in-out infinite; }
        .hero-float-2 { animation: heroElementFloat 7.4s ease-in-out infinite 0.8s; }
        .hero-float-3 { animation: heroElementFloat 8.2s ease-in-out infinite 1.4s; }
        .hero-float-4 { animation: heroElementFloat 6.8s ease-in-out infinite 0.5s; }

        @media (prefers-reduced-motion: reduce) {
            .animate-hero-entrance,
            .animate-book-light,
            .hero-book-float,
            .hero-page-left,
            .hero-page-right,
            .hero-float-1,
            .hero-float-2,
            .hero-float-3,
            .hero-float-4,
            .hero-word-fade-up {
                animation: none !important;
            }
        }

    </style>
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">

    <!-- Public Multi-Genre Litera Navigation Bar -->
    <x-navbar-public />

    <!-- Hero Section with Animated Open Book Illustration -->
    <section class="relative overflow-hidden min-h-[calc(100vh-4.5rem)] flex items-center py-16 md:py-24 bg-gradient-to-b from-canvas via-surface/40 to-canvas">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-8 xl:gap-12 items-center">
                
                <!-- Left Hero Editorial Content (Aligned to Navbar Left) -->
                <div class="lg:col-span-8 xl:col-span-7 space-y-5 text-center lg:text-left">
                    <!-- Eyebrow Accent Line & Label -->
                    <div class="inline-flex items-center gap-2.5 justify-center lg:justify-start">
                        <span class="w-8 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                        <span class="text-[11px] sm:text-xs font-semibold tracking-[0.22em] text-brand-600 dark:text-brand-400 uppercase font-sans">
                            PERPUSTAKAAN DIGITAL
                        </span>
                    </div>

                    <h1 class="text-[30px] sm:text-[36px] md:text-[42px] lg:text-[52px] xl:text-[56px] font-bold text-ink tracking-tight font-serif leading-[1.2] max-w-xl lg:max-w-none mx-auto lg:mx-0">
                        <span class="block">Setiap Halaman,</span>
                        <span class="block pt-0.5">
                            <span class="grid" aria-live="off">
                                <span id="hero-animated-word" class="col-start-1 row-start-1 inline-block whitespace-nowrap bg-gradient-to-r from-brand-600 via-gold to-brand-600 dark:from-brand-400 dark:via-gold dark:to-brand-400 bg-clip-text text-transparent transition-[opacity,transform] duration-500 ease-out">
                                    Petualangan Baru.
                                </span>
                            </span>
                        </span>
                    </h1>

                    <p class="text-xs sm:text-sm text-text-muted max-w-xl mx-auto lg:mx-0 leading-relaxed font-sans">
                        Dari pengetahuan yang memperluas wawasan hingga cerita yang membawa imajinasi. Temukan bacaan favoritmu dan jelajahi dunia literasi bersama Litera.
                    </p>

                    <!-- Search Bar (Directs to the existing catalog search) -->
                    <form action="{{ route('catalog.index') }}" method="GET" class="max-w-xl mx-auto lg:mx-0 pt-2">
                        <div class="relative flex items-center rounded-2xl bg-surface border-2 border-line focus-within:border-brand-600 dark:focus-within:border-brand-400 shadow-lg transition-all p-1.5">
                            <div class="pl-3.5 pr-2 text-text-muted" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                id="hero-opac-input"
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                autocomplete="off"
                                spellcheck="false"
                                aria-label="Cari judul buku, novel, atau penulis"
                                placeholder="Cari judul buku, novel, atau penulis..."
                                class="w-full bg-transparent border-0 text-sm text-ink placeholder:text-text-muted/70 focus:outline-none focus:ring-0 px-2 py-2"
                            >
                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm shrink-0 cursor-pointer"
                            >
                                Telusuri
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Hero: 3D Open Book with Emerging Knowledge Symbols (Aligned to Navbar Right) -->
                <div class="lg:col-span-4 xl:col-span-5 relative flex flex-col items-center lg:items-end justify-center pt-2 pb-6 min-h-[440px] select-none lg:pl-4" aria-hidden="true">
                    
                    <!-- Atmospheric Aura & Emerging Particles Container (With Entrance Animation, Right Aligned) -->
                    <div class="relative w-full max-w-[400px] h-[390px] flex items-center justify-center lg:justify-end animate-hero-entrance lg:ml-auto">
                        
                        <div class="hero-float-1 pointer-events-none absolute left-[12%] top-[17%] z-30" aria-hidden="true">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-200/60 bg-surface/75 text-brand-600 shadow-lg shadow-brand-600/10 backdrop-blur-md dark:bg-surface/75 dark:text-brand-400">
                                <svg class="hero-decoration-icon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-5 3h4m-5.5-6.5a7 7 0 1111 0c-.9 1-1.5 1.9-1.5 3.5h-8c0-1.6-.6-2.5-1.5-3.5zM10 10a2.5 2.5 0 014-2"/>
                                </svg>
                            </div>
                        </div>

                        <div class="hero-float-2 pointer-events-none absolute left-[47%] top-[3%] z-30" aria-hidden="true">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-brand-200/60 bg-surface/75 text-brand-600 backdrop-blur-md dark:bg-surface/75 dark:text-brand-400">
                                <svg class="hero-decoration-icon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.5c3.4-.8 6.4-.2 9 1.7v12c-2.6-1.9-5.6-2.5-9-1.7v-12zm18 0c-3.4-.8-6.4-.2-9 1.7v12c2.6-1.9 5.6-2.5 9-1.7v-12z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="hero-float-3 pointer-events-none absolute right-[9%] top-[19%] z-30" aria-hidden="true">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-200/60 bg-surface/75 text-gold backdrop-blur-md dark:bg-surface/75">
                                <svg class="hero-decoration-icon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3zm6 11 .9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9L18 14z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="hero-float-4 pointer-events-none absolute left-[23%] top-[52%] z-30" aria-hidden="true">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border border-brand-200/50 bg-surface/75 text-brand-500 shadow-lg shadow-brand-500/10 backdrop-blur-md dark:bg-surface/75 dark:text-brand-400">
                                <svg class="hero-decoration-icon h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="m12 2.8 2.1 6.1 6.1 2.1-6.1 2.1-2.1 6.1-2.1-6.1-6.1-2.1 6.1-2.1L12 2.8z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- ======================================================== -->
                        <!-- Open Book Illustration -->
                        <!-- ======================================================== -->
                        <div class="hero-book-float absolute bottom-2 inset-x-0 flex flex-col items-center justify-center pointer-events-none z-20">
                            
                            <div class="relative w-[min(100%,22rem)] sm:w-[23rem] drop-shadow-md">
                                <svg viewBox="0 0 320 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" aria-hidden="true">
                                    <!-- Hardcover Backing (Botanical Forest Green) -->
                                    <path d="M15 158 C 80 162, 130 168, 160 178 C 190 168, 240 162, 305 158 L 305 68 C 240 72, 190 78, 160 88 C 130 78, 80 72, 15 68 Z" fill="#294638" stroke="#507B5C" stroke-width="2" />
                                    
                                    <!-- Gold Spine Accent -->
                                    <path d="M158 88 L 158 178 L 162 178 L 162 88 Z" fill="#92703D" />
                                    
                                    <g class="hero-page-left">
                                        <!-- Left Block Thickness -->
                                        <path d="M 18 152 C 80 156, 130 162, 158 172 L 158 166 C 130 156, 80 150, 18 146 Z" fill="#E8EDE4" />
                                        <!-- Left Page Surface -->
                                        <path d="M 20 62 C 85 66, 130 72, 158 82 L 158 166 C 130 156, 85 150, 20 146 Z" fill="url(#heroLeftPageGrad)" stroke="#D7DED3" stroke-width="1.5" />
                                        
                                        <line x1="38" y1="84" x2="135" y2="92" stroke="#294638" stroke-width="2.5" stroke-linecap="round" opacity="0.75" />
                                        <line x1="38" y1="96" x2="140" y2="103" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        <line x1="38" y1="106" x2="130" y2="113" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        <line x1="38" y1="116" x2="138" y2="123" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        <line x1="38" y1="126" x2="115" y2="132" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        
                                        <circle cx="50" cy="140" r="4" fill="#92703D" opacity="0.45" />
                                    </g>

                                    <g class="hero-page-right">
                                        <!-- Right Block Thickness -->
                                        <path d="M 302 152 C 240 156, 190 162, 162 172 L 162 166 C 190 156, 240 150, 302 146 Z" fill="#E8EDE4" />
                                        <!-- Right Page Surface -->
                                        <path d="M 300 62 C 235 66, 190 72, 162 82 L 162 166 C 190 156, 235 150, 300 146 Z" fill="url(#heroRightPageGrad)" stroke="#D7DED3" stroke-width="1.5" />
                                        
                                        <path d="M215 89l1.5 4.5L221 95l-4.5 1.5L215 101l-1.5-4.5L209 95l4.5-1.5L215 89z" fill="#92703D" opacity="0.8" />
                                        <line x1="180" y1="112" x2="278" y2="106" stroke="#294638" stroke-width="2.2" stroke-linecap="round" opacity="0.75" />
                                        <line x1="180" y1="124" x2="280" y2="118" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        <line x1="180" y1="134" x2="268" y2="129" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                        <line x1="180" y1="144" x2="258" y2="139" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" opacity="0.65" />
                                    </g>

                                    <!-- Gradients -->
                                    <defs>
                                        <linearGradient id="heroLeftPageGrad" x1="20" y1="62" x2="158" y2="166" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="#FFFFFF" />
                                            <stop offset="85%" stop-color="#F8FAFC" />
                                            <stop offset="100%" stop-color="#E8EDE4" />
                                        </linearGradient>
                                        <linearGradient id="heroRightPageGrad" x1="300" y1="62" x2="162" y2="166" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="#FFFFFF" />
                                            <stop offset="85%" stop-color="#F8FAFC" />
                                            <stop offset="100%" stop-color="#E8EDE4" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- Explore E-book/Keilmuan and Novel Categories -->
    <section id="e-resources" class="min-h-[calc(100vh-4.5rem)] flex flex-col justify-center py-16 lg:py-20 bg-gradient-to-b from-canvas via-surface/40 to-canvas relative overflow-hidden">
        
        <!-- Subtle Ambient Background Accents -->
        <div class="absolute top-1/4 left-8 w-72 h-72 bg-brand-600/5 dark:bg-brand-400/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 right-8 w-80 h-80 bg-brand-900/5 dark:bg-brand-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            
            <!-- Section Header (Academic Serif Title) -->
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-12">
                <div class="flex items-center justify-center gap-3 mb-3">
                    <span class="h-px w-8 bg-brand-600/30 dark:bg-brand-400/30"></span>
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-brand-600 dark:text-brand-400 font-sans">JELAJAHI KOLEKSI</span>
                    <span class="h-px w-8 bg-brand-600/30 dark:bg-brand-400/30"></span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-ink font-serif tracking-tight mt-1">
                    Dua Dunia, Satu Perpustakaan.
                </h2>
                <p class="text-xs sm:text-sm text-text-muted mt-3 max-w-2xl mx-auto leading-relaxed font-sans">
                    Jelajahi beragam pengetahuan dan temukan kisah yang menginspirasi, semuanya dalam satu ruang baca digital.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 max-w-5xl mx-auto">
                <a
                    href="{{ route('catalog.index', ['genre' => 'keilmuan-edukasi']) }}"
                    class="group card-interactive flex min-h-56 flex-col rounded-3xl border border-line bg-surface/90 p-6 sm:p-8 shadow-sm hover:border-brand-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600 dark:hover:border-brand-400 dark:focus-visible:outline-brand-400"
                >
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/10 text-brand-600 transition-transform duration-200 group-hover:scale-105 dark:bg-brand-400/15 dark:text-brand-400" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v14m0-14C10.7 5.8 8.6 5 6 5H3v14h3c2.6 0 4.7.8 6 2m0-14c1.3-1.2 3.4-2 6-2h3v14h-3c-2.6 0-4.7.8-6 2" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">E-book / Keilmuan</span>
                    <h3 class="mt-2 font-serif text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Perluas wawasan.</h3>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-text-muted">
                        Temukan bacaan digital tentang sains, teknologi, sejarah, bisnis, dan berbagai bidang ilmu.
                    </p>
                    <span class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-semibold text-brand-600 transition-colors group-hover:text-brand-700 dark:text-brand-400 dark:group-hover:text-ink">
                        Jelajahi Keilmuan
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('catalog.index', ['genre' => 'novel-fiksi']) }}"
                    class="group card-interactive flex min-h-56 flex-col rounded-3xl border border-line bg-surface/90 p-6 sm:p-8 shadow-sm hover:border-brand-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600 dark:hover:border-brand-400 dark:focus-visible:outline-brand-400"
                >
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/10 text-brand-600 transition-transform duration-200 group-hover:scale-105 dark:bg-brand-400/15 dark:text-brand-400" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 4.5A2.5 2.5 0 017.5 2H20v17H7.5A2.5 2.5 0 005 21.5v-17zm0 0V21.5M8 6h8m-8 4h8m-8 4h5" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">Novel</span>
                    <h3 class="mt-2 font-serif text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Temukan kisah baru.</h3>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-text-muted">
                        Jelajahi cerita dan dunia baru melalui pilihan novel Litera.
                    </p>
                    <span class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-semibold text-brand-600 transition-colors group-hover:text-brand-700 dark:text-brand-400 dark:group-hover:text-ink">
                        Jelajahi Novel
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </span>
                </a>
            </div>


        </div>
    </section>

    <!-- Section 1: Buku Fisik Terbaru -->
    <section id="buku-fisik" class="py-12 sm:py-16 bg-surface border-b border-line/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="mb-8 sm:mb-10">
                <div class="inline-flex items-center gap-2.5">
                    <span class="w-8 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 font-sans">Koleksi Fisik</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-ink font-serif tracking-tight mt-1.5">Buku Fisik Terbaru</h2>
                <p class="text-xs sm:text-sm text-text-muted mt-2 max-w-2xl leading-relaxed font-sans">
                    Koleksi buku fisik terbaru yang tersedia di rak perpustakaan dan siap dipinjam secara langsung oleh sivitas akademika.
                </p>
            </div>

            @if (($physicalBooks ?? collect())->isEmpty())
                <div class="p-12 text-center rounded-3xl border border-line bg-surface-soft max-w-md mx-auto">
                    <h3 class="text-base font-normal text-ink font-serif">Belum Ada Buku Fisik Terbaru</h3>
                    <p class="text-xs text-text-muted mt-1 mb-4 font-sans">Koleksi buku fisik terbaru akan segera hadir di perpustakaan.</p>
                    <a href="{{ route('catalog.index', ['collection_type' => 'physical']) }}" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold font-sans">
                        Jelajahi Buku Fisik
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5 sm:gap-x-5">
                    @foreach ($physicalBooks as $book)
                        <a
                            href="{{ route('books.show', $book) }}"
                            class="group/card flex flex-col h-full rounded-2xl bg-surface border border-line hover:border-brand-600 dark:hover:border-brand-400 p-3.5 sm:p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer"
                            title="{{ $book->title }}"
                        >
                            <!-- Cover Book (Aspect 3/4) -->
                            <div class="group/cover relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface-soft border border-line/60 shadow-2xs mb-3">
                                @if (filled($book->cover_path))
                                    <img
                                        src="{{ asset('storage/' . $book->cover_path) }}"
                                        alt="Sampul buku {{ $book->title }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover transition-transform duration-300 motion-safe:group-hover/card:scale-105"
                                    >
                                @else
                                    <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul buku tidak tersedia untuk {{ $book->title }}">
                                        <span class="font-serif text-4xl sm:text-5xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="flex flex-col flex-1 min-w-0">
                                <!-- Category Badge -->
                                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 truncate font-sans">
                                    {{ $book->category?->name ?? 'UMUM' }}
                                </span>

                                <!-- Title -->
                                <h3 class="font-bold text-sm text-ink group-hover/card:text-brand-600 dark:group-hover/card:text-brand-400 transition-colors line-clamp-2 leading-snug mt-1 font-sans" title="{{ $book->title }}">
                                    {{ $book->title }}
                                </h3>

                                <!-- Author & Year -->
                                <div class="flex items-center gap-1.5 text-xs text-text-muted mt-1.5 font-sans truncate">
                                    <span class="truncate font-medium">{{ $book->author ?: 'Litera' }}</span>
                                    @if ($book->publication_year)
                                        <span>·</span>
                                        <span class="font-year font-semibold shrink-0">{{ $book->publication_year }}</span>
                                    @endif
                                </div>

                                <!-- Status Stok / Ketersediaan -->
                                <div class="mt-2.5">
                                    @if (($book->available_copies ?? $book->stock_available ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[11px] font-semibold font-sans">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span class="truncate">Tersedia {{ $book->available_copies ?? $book->stock_available }} eks</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-700 dark:text-rose-400 text-[11px] font-semibold font-sans">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                            <span>Sedang Dipinjam</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Short Description (Max 2-3 lines) -->
                                <p class="text-[11px] text-text-muted line-clamp-2 leading-relaxed mt-2.5 font-sans">
                                    {{ $book->synopsis ?: ($book->description ?: ($book->category?->description ?: 'Koleksi buku fisik perpustakaan Litera untuk menunjang studi dan pengayaan literasi akademik.')) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- CTA Bawah -->
                <div class="mt-8 sm:mt-10 text-center">
                    <a href="{{ route('catalog.index', ['collection_type' => 'physical']) }}" class="group inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-surface hover:bg-surface-soft border border-line hover:border-brand-600 dark:hover:border-brand-400 text-xs font-bold text-ink hover:text-brand-600 dark:hover:text-brand-400 shadow-2xs hover:shadow-xs transition-all duration-200 font-sans">
                        <span>Jelajahi Buku Fisik</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1.5 transition-transform duration-200 ease-out text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif

        </div>
    </section>

    <!-- Section 2: E-Book Terbaru -->
    <section id="koleksi-ebook" class="py-12 sm:py-16 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="mb-8 sm:mb-10">
                <div class="inline-flex items-center gap-2.5">
                    <span class="w-8 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-full"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 font-sans">Pustaka Digital</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-ink font-serif tracking-tight mt-1.5">E-Book Terbaru</h2>
                <p class="text-xs sm:text-sm text-text-muted mt-2 max-w-2xl leading-relaxed font-sans">
                    Koleksi buku digital yang bisa dibaca secara online atau diakses langsung secara digital kapan pun dan di mana pun.
                </p>
            </div>

            @if (($ebooks ?? collect())->isEmpty())
                <div class="p-12 text-center rounded-3xl border border-line bg-surface-soft max-w-md mx-auto">
                    <h3 class="text-base font-normal text-ink font-serif">Belum Ada E-Book Terbaru</h3>
                    <p class="text-xs text-text-muted mt-1 mb-4 font-sans">Koleksi e-book digital terbaru akan tampil di sini.</p>
                    <a href="{{ route('catalog.index', ['collection_type' => 'ebook']) }}" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold font-sans">
                        Jelajahi E-Book
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-5 sm:gap-x-5">
                    @foreach ($ebooks as $book)
                        <a
                            href="{{ route('books.show', $book) }}"
                            class="group/card flex flex-col h-full rounded-2xl bg-surface border border-line hover:border-brand-600 dark:hover:border-brand-400 p-3.5 sm:p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer"
                            title="{{ $book->title }}"
                        >
                            <!-- Cover Book (Aspect 3/4) -->
                            <div class="group/cover relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface-soft border border-line/60 shadow-2xs mb-3">
                                @if (filled($book->cover_path))
                                    <img
                                        src="{{ asset('storage/' . $book->cover_path) }}"
                                        alt="Sampul {{ $book->collection_type === 'novel' ? 'novel' : 'e-book' }} {{ $book->title }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="h-full w-full object-cover transition-transform duration-300 motion-safe:group-hover/card:scale-105"
                                    >
                                @else
                                    <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul {{ $book->collection_type === 'novel' ? 'novel' : 'e-book' }} tidak tersedia untuk {{ $book->title }}">
                                        <span class="font-serif text-4xl sm:text-5xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="flex flex-col flex-1 min-w-0">
                                <!-- Category Badge -->
                                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 truncate font-sans">
                                    {{ $book->category?->name ?? 'UMUM' }}
                                </span>

                                <!-- Title -->
                                <h3 class="font-bold text-sm text-ink group-hover/card:text-brand-600 dark:group-hover/card:text-brand-400 transition-colors line-clamp-2 leading-snug mt-1 font-sans" title="{{ $book->title }}">
                                    {{ $book->title }}
                                </h3>

                                <!-- Author & Year -->
                                <div class="flex items-center gap-1.5 text-xs text-text-muted mt-1.5 font-sans truncate">
                                    <span class="truncate font-medium">{{ $book->author ?: 'Litera' }}</span>
                                    @if ($book->publication_year)
                                        <span>·</span>
                                        <span class="font-year font-semibold shrink-0">{{ $book->publication_year }}</span>
                                    @endif
                                </div>

                                <!-- Jenis Akses Singkat -->
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-brand-500/10 text-brand-700 dark:text-brand-300 text-[11px] font-semibold font-sans">
                                        <svg class="w-3 h-3 text-brand-600 dark:text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <span>{{ filled($book->read_url) || filled($book->digital_file_path) ? 'Baca Online' : 'Akses Digital' }}</span>
                                    </span>
                                </div>

                                <!-- Short Description (Max 2-3 lines) -->
                                <p class="text-[11px] text-text-muted line-clamp-2 leading-relaxed mt-2.5 font-sans">
                                    {{ $book->synopsis ?: ($book->description ?: ($book->category?->description ?: 'Koleksi e-book digital pilihan Litera untuk menunjang pembelajaran dan literasi sivitas akademika.')) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- CTA Bawah -->
                <div class="mt-8 sm:mt-10 text-center">
                    <a href="{{ route('catalog.index', ['collection_type' => 'ebook']) }}" class="group inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-surface hover:bg-surface-soft border border-line hover:border-brand-600 dark:hover:border-brand-400 text-xs font-bold text-ink hover:text-brand-600 dark:hover:text-brand-400 shadow-2xs hover:shadow-xs transition-all duration-200 font-sans">
                        <span>Lihat Semua E-Book</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1.5 transition-transform duration-200 ease-out text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endif

        </div>
    </section>

    <x-footer-public />

    <!-- Campus Member Borrow Modal -->
    <div id="landing-borrow-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <h3 class="text-base font-bold text-ink">Form Peminjaman Buku</h3>
                <button type="button" onclick="closeLandingBorrowModal()" class="text-text-muted hover:text-ink text-sm p-1">&times;</button>
            </div>

            <form id="landing-borrow-form" method="POST" action="" class="space-y-4 mt-4">
                @csrf
                <div>
                    <span class="text-xs text-text-muted block">Buku yang Dipilih:</span>
                    <p id="modal-book-title" class="text-sm font-bold text-ink mt-0.5"></p>
                </div>

                <!-- Duration Picker -->
                <div>
                    <label for="landing_duration_days" class="block text-xs font-semibold text-ink mb-1.5">
                        Pilihan Jangka Waktu Peminjaman:
                    </label>
                    <select
                        id="landing_duration_days"
                        name="duration_days"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"
                    >
                        <option value="3">3 Hari &mdash; Baca Singkat / Keperluan Ujian</option>
                        <option value="7" selected>7 Hari &mdash; Standar Sivitas Akademika</option>
                        <option value="14">14 Hari &mdash; 2 Minggu (Tugas Kuliah / Proyek)</option>
                        <option value="21">21 Hari &mdash; 3 Minggu (Riset &amp; Eksperimen)</option>
                        <option value="30">30 Hari &mdash; 1 Bulan (Penyusunan Skripsi / Dosen)</option>
                    </select>
                </div>

                <div class="p-3.5 rounded-2xl bg-surface-soft border border-line text-[11px] text-text-muted space-y-1">
                    <p>Peminjam: <strong class="text-ink">{{ auth()->user()?->name }}</strong> ({{ auth()->user()?->role_label }})</p>
                    <p>Setelah pengajuan selesai, bawa kartu sivitas akademika ke loket sirkulasi untuk mengambil fisik buku.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeLandingBorrowModal()" class="px-4 py-2 rounded-xl border border-line text-xs font-semibold text-text-muted hover:bg-surface-soft">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs">
                        Konfirmasi Pinjam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Guest Auth Required Modal -->
    <div id="landing-auth-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up text-center">
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-ink">Khusus Sivitas Akademika Kampus</h3>
            <p id="landing-auth-msg" class="text-xs text-text-muted mt-2 px-2 leading-relaxed">
                Fitur peminjaman buku fisik dan penyimpanan favorit hanya dapat diakses oleh mahasiswa, dosen, dan staf kampus yang terdaftar.
            </p>
            <div class="mt-6 flex flex-col gap-2.5">
                <a href="{{ route('login') }}" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs">
                    Masuk Akun Kampus
                </a>
                <a href="{{ route('register') }}" class="w-full py-2.5 rounded-xl border border-line bg-surface hover:bg-surface-soft text-ink text-xs font-semibold">
                    Daftar Akun Baru Sivitas
                </a>
                <button type="button" onclick="closeLandingAuthModal()" class="mt-1 text-xs text-text-muted hover:underline cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Unggah Mandiri Karya Ilmiah Sivitas -->
    <div id="modal-unggah-karya" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-3xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink font-serif">Unggah Mandiri Karya Ilmiah</h3>
                </div>
                <button type="button" onclick="closeModalUnggahKarya()" class="text-text-muted hover:text-ink text-sm p-1 cursor-pointer">&times;</button>
            </div>

            <form onsubmit="handleUnggahSubmit(event)" class="space-y-3.5 mt-4">
                <div>
                    <label class="block text-xs font-semibold text-ink mb-1">Judul Karya Ilmiah / Skripsi / Tesis *</label>
                    <input type="text" required placeholder="Contoh: Implementasi Deep Learning untuk Deteksi Pola Citra Medis" class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink mb-1">Jenjang / Program Studi *</label>
                        <select class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                            <option>S1 Informatika</option>
                            <option>S1 Sistem Informasi</option>
                            <option>S1 Teknik Elektro</option>
                            <option>S1 Manajemen Bisnis</option>
                            <option>S2 Ilmu Komputer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink mb-1">Tahun Sidang / Lulus *</label>
                        <input type="number" value="{{ date('Y') }}" class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink mb-1">Abstrak Singkat (Bahasa Indonesia) *</label>
                    <textarea rows="3" required placeholder="Tuliskan ringkasan latar belakang, metodologi, dan temuan utama riset..." class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink mb-1">Berkas Naskah PDF Lengkap *</label>
                    <input type="file" accept=".pdf" required class="w-full px-3 py-1.5 rounded-xl border border-line bg-surface-soft text-text-muted text-xs file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white">
                    <p class="text-[10px] text-text-muted mt-1">Format PDF, maksimal ukuran 25MB (mencakup halaman persetujuan pembimbing).</p>
                </div>

                <div class="p-3 rounded-2xl bg-surface-soft border border-line text-[11px] text-text-muted">
                    Penyerah: <strong class="text-ink">{{ auth()->user()?->name ?? 'Sivitas Kampus' }}</strong> ({{ auth()->user()?->email ?? 'Akun Terautentikasi' }})
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModalUnggahKarya()" class="px-4 py-2 rounded-xl border border-line text-xs font-semibold text-text-muted hover:bg-surface-soft cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs cursor-pointer">
                        Kirim Naskah Digital
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Usulan Koleksi Buku Baru -->
    <div id="modal-usulan-buku" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-3xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-ink font-serif">Usulan Koleksi Buku Baru</h3>
                </div>
                <button type="button" onclick="closeModalUsulanBuku()" class="text-text-muted hover:text-ink text-sm p-1 cursor-pointer">&times;</button>
            </div>

            <form onsubmit="handleUsulanSubmit(event)" class="space-y-3.5 mt-4">
                <div>
                    <label class="block text-xs font-semibold text-ink mb-1">Judul Buku yang Diusulkan *</label>
                    <input type="text" required placeholder="Contoh: Designing Data-Intensive Applications" class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink mb-1">Nama Pengarang / Penulis *</label>
                        <input type="text" required placeholder="Contoh: Martin Kleppmann" class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink mb-1">Penerbit / Perkiraan Tahun</label>
                        <input type="text" placeholder="Contoh: O'Reilly Media / 2023" class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink mb-1">Alasan Kebutuhan / Mata Kuliah Terkait *</label>
                    <textarea rows="3" required placeholder="Jelaskan kebutuhan akademik atau mata kuliah yang memerlukan buku ini sebagai referensi..." class="w-full px-3.5 py-2 rounded-xl border border-line bg-surface text-ink text-xs focus:ring-2 focus:ring-brand-600 dark:focus:ring-brand-400"></textarea>
                </div>

                <div class="p-3 rounded-2xl bg-surface-soft border border-line text-[11px] text-text-muted">
                    Pengusul: <strong class="text-ink">{{ auth()->user()?->name ?? 'Sivitas Kampus' }}</strong> ({{ auth()->user()?->role_label ?? 'Akun Terautentikasi' }})
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModalUsulanBuku()" class="px-4 py-2 rounded-xl border border-line text-xs font-semibold text-text-muted hover:bg-surface-soft cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs cursor-pointer">
                        Kirim Usulan Pengadaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const isLoggedIn = @json(Auth::check());

        // Academic Shortcuts Handler (Gunadarma model: 4 shortcuts)
        function handleAcademicShortcut(action) {
            if (!isLoggedIn) {
                let msg = 'Fitur ini memerlukan autentikasi akun sivitas akademika kampus. Silakan masuk akun kampus Anda.';
                if (action === 'baca-buku') {
                    msg = 'Layanan membaca e-paper & e-book digital kampus memerlukan autentikasi sivitas akademika kampus. Silakan masuk untuk membaca langsung.';
                } else if (action === 'unggah-karya') {
                    msg = 'Layanan unggah mandiri skripsi dan karya ilmiah repositori kampus memerlukan autentikasi akun resmi sivitas akademika.';
                } else if (action === 'usulan-buku') {
                    msg = 'Pengajuan usulan judul pengadaan koleksi buku baru perpustakaan hanya dapat dilakukan oleh sivitas akademika resmi.';
                }
                document.getElementById('landing-auth-msg').textContent = msg;
                document.getElementById('landing-auth-modal').classList.remove('hidden');
                return;
            }

            if (action === 'baca-buku') {
                window.location.href = "{{ route('catalog.index') }}";
            } else if (action === 'unggah-karya') {
                document.getElementById('modal-unggah-karya').classList.remove('hidden');
            } else if (action === 'usulan-buku') {
                document.getElementById('modal-usulan-buku').classList.remove('hidden');
            }
        }

        function closeModalUnggahKarya() {
            document.getElementById('modal-unggah-karya').classList.add('hidden');
        }

        function closeModalUsulanBuku() {
            document.getElementById('modal-usulan-buku').classList.add('hidden');
        }

        function handleUnggahSubmit(event) {
            event.preventDefault();
            alert('Terima kasih! Naskah karya ilmiah Anda berhasil diserahkan ke sistem repositori perpustakaan untuk proses validasi uji kemiripan Turnitin dan arsip digital.');
            event.target.reset();
            closeModalUnggahKarya();
        }

        function handleUsulanSubmit(event) {
            event.preventDefault();
            alert('Terima kasih! Rekomendasi usulan buku baru Anda telah berhasil dikirim ke bagian pengadaan perpustakaan kampus.');
            event.target.reset();
            closeModalUsulanBuku();
        }

        function handleBorrowClick(bookId, bookTitle, stockAvailable) {
            if (!isLoggedIn) {
                document.getElementById('landing-auth-msg').textContent = 'Peminjaman eksemplar buku fisik perpustakaan memerlukan autentikasi akun sivitas akademika kampus. Silakan masuk atau buat akun baru.';
                document.getElementById('landing-auth-modal').classList.remove('hidden');
                return;
            }
            if (stockAvailable < 1) {
                alert('Mohon maaf, stok buku ini sedang habis dipinjam sivitas lain.');
                return;
            }
            document.getElementById('modal-book-title').textContent = bookTitle;
            document.getElementById('landing-borrow-form').action = `/books/${bookId}/borrow`;
            document.getElementById('landing-borrow-modal').classList.remove('hidden');
        }

        function closeLandingBorrowModal() {
            document.getElementById('landing-borrow-modal').classList.add('hidden');
        }

        function closeLandingAuthModal() {
            document.getElementById('landing-auth-modal').classList.add('hidden');
        }

        function handleFavoriteClick(bookId, buttonEl) {
            if (!isLoggedIn) {
                document.getElementById('landing-auth-msg').textContent = 'Penyimpanan buku favorit ke daftar bacaan memerlukan akun sivitas akademika kampus. Silakan masuk atau daftar.';
                document.getElementById('landing-auth-modal').classList.remove('hidden');
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/books/${bookId}/favorite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(res => res.json())
            .then(data => {
                const svg = buttonEl.querySelector('svg');
                if (data.status === 'added') {
                    svg.classList.remove('fill-none', 'text-text-muted');
                    svg.classList.add('fill-red-500', 'text-red-500');
                    buttonEl.setAttribute('data-favorited', 'true');
                } else {
                    svg.classList.remove('fill-red-500', 'text-red-500');
                    svg.classList.add('fill-none', 'text-text-muted');
                    buttonEl.setAttribute('data-favorited', 'false');
                }
            })
            .catch(err => console.error(err));
        }

        const heroSearchInput = document.getElementById('hero-opac-input');
        const heroSearchPlaceholder = heroSearchInput?.getAttribute('placeholder') ?? '';

        if (
            heroSearchInput &&
            !heroSearchInput.value &&
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            let placeholderIndex = 0;
            let placeholderTimeout;

            const finishPlaceholderAnimation = () => {
                window.clearTimeout(placeholderTimeout);
                heroSearchInput.setAttribute('placeholder', heroSearchPlaceholder);
            };

            const typePlaceholder = () => {
                placeholderIndex += 1;
                heroSearchInput.setAttribute('placeholder', heroSearchPlaceholder.slice(0, placeholderIndex));

                if (placeholderIndex < heroSearchPlaceholder.length) {
                    placeholderTimeout = window.setTimeout(typePlaceholder, 90);
                }
            };

            heroSearchInput.setAttribute('placeholder', '');
            heroSearchInput.addEventListener('focus', finishPlaceholderAnimation, { once: true });
            heroSearchInput.addEventListener('input', finishPlaceholderAnimation, { once: true });
            window.addEventListener('pagehide', () => {
                window.clearTimeout(placeholderTimeout);
            }, { once: true });
            placeholderTimeout = window.setTimeout(typePlaceholder, 350);
        }

        const heroWordEl = document.getElementById('hero-animated-word');
        const heroWords = ['Petualangan Baru.', 'Pengetahuan Baru.', 'Cerita Baru.'];

        if (heroWordEl && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            let heroWordIndex = 0;

            const heroWordInterval = window.setInterval(() => {
                heroWordIndex = (heroWordIndex + 1) % heroWords.length;
                heroWordEl.classList.remove('hero-word-fade-up');
                heroWordEl.textContent = heroWords[heroWordIndex];
                window.requestAnimationFrame(() => heroWordEl.classList.add('hero-word-fade-up'));
            }, 3000);

            heroWordEl.addEventListener('animationend', (event) => {
                if (event.animationName === 'heroWordFadeUp') {
                    heroWordEl.classList.remove('hero-word-fade-up');
                }
            });

            window.addEventListener('pagehide', () => {
                window.clearInterval(heroWordInterval);
            }, { once: true });
        }

        // Smooth Anchor Navigation with Sticky Header Offset
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId && targetId !== '#') {
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        const navHeight = 72;
                        const elementPosition = targetEl.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - navHeight;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>

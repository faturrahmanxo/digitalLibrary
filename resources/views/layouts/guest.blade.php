<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ isset($title) ? $title . ' — ' : '' }}Litera</title>
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
    </style>
</head>
<body class="min-h-screen w-full bg-canvas text-ink antialiased p-0 m-0 overflow-x-hidden selection:bg-brand-500 selection:text-white transition-colors duration-300 flex flex-col">
    <!-- Dark / Light Mode Toggle Button in Guest/Login Layout with Expanding Ripple -->
    <div class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50">
        <button type="button" id="theme-toggle-btn" class="p-2.5 rounded-full border border-line bg-surface/90 backdrop-blur-md text-text-muted hover:text-ink active:scale-90 transition-all shadow-lg cursor-pointer overflow-hidden" aria-label="Ganti mode gelap/terang" title="Ganti mode gelap/terang">
            <div class="relative w-5 h-5 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-400 absolute transition-all duration-400 ease-out transform dark:rotate-0 dark:scale-100 dark:opacity-100 -rotate-90 scale-0 opacity-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg class="w-4 h-4 text-ink absolute transition-all duration-400 ease-out transform dark:rotate-90 dark:scale-0 dark:opacity-0 rotate-0 scale-100 opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </div>
        </button>
    </div>

    <main class="w-full min-h-screen flex-1 flex flex-col relative z-10 p-0 m-0">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
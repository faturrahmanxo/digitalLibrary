<footer class="mt-auto border-t border-line bg-surface-soft/70 px-4 py-9 text-center dark:bg-surface sm:py-11">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-4">
        <a href="{{ route('home') }}" class="inline-flex rounded-lg focus-visible:outline-2 focus-visible:outline-focus" aria-label="Litera — Beranda">
            <img
                src="{{ asset('images/logo-Litera.png') }}"
                alt="Litera"
                width="1326"
                height="529"
                class="h-8 w-auto object-contain dark:brightness-0 dark:invert"
            >
        </a>

        <p class="line-clamp-2 max-w-xl text-xs leading-relaxed text-text-muted sm:text-sm">
            Ruang digital untuk menjelajahi pengetahuan dan menikmati cerita dalam setiap halaman.
        </p>

        <nav class="flex flex-wrap items-center justify-center gap-x-7 gap-y-2 pt-1 text-xs font-semibold sm:text-sm" aria-label="Navigasi Footer">
            <a href="{{ route('home') }}" class="rounded-md text-ink transition-colors hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-focus">
                Beranda
            </a>
            <a href="{{ route('jelajahi') }}" class="rounded-md text-ink transition-colors hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-focus">
                Jelajahi
            </a>
            <a href="{{ route('info.profil') }}" class="rounded-md text-ink transition-colors hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-focus">
                Tentang Kami
            </a>
        </nav>

        <div class="mt-3 w-full border-t border-line/80 pt-5">
            <p class="text-[11px] text-text-muted sm:text-xs">
                &copy; {{ date('Y') }} Litera. All rights reserved.
            </p>
        </div>
    </div>
</footer>
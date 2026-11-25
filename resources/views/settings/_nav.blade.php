<div class="mb-6 border-b border-line pb-5">
    <h1 class="text-2xl font-bold text-ink">
        @if (request()->routeIs('settings.profile'))
            Pengaturan Profil
        @elseif (request()->routeIs('settings.security'))
            Pengaturan Keamanan
        @elseif (request()->routeIs('settings.appearance'))
            Pengaturan Tampilan
        @else
            Pengaturan Akun
        @endif
    </h1>
    <p class="text-xs sm:text-sm text-text-muted mt-1">
        @if (request()->routeIs('settings.profile'))
            Kelola informasi identitas, foto profil, dan akun pengelola Litera.
        @elseif (request()->routeIs('settings.security'))
            Kelola kata sandi dan pengaturan keamanan akun pengelola Litera.
        @elseif (request()->routeIs('settings.appearance'))
            Sesuaikan preferensi tema antarmuka dan visual sistem Litera.
        @else
            Kelola profil pengelola, keamanan autentikasi kata sandi, dan preferensi tampilan antarmuka.
        @endif
    </p>
</div>

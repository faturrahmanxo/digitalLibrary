<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-line pb-5">
    <div>
        <h1 class="text-2xl font-bold text-ink">Pengaturan Akun</h1>
        <p class="text-xs sm:text-sm text-text-muted mt-1">Kelola profil pengelola, keamanan autentikasi kata sandi, dan preferensi tampilan antarmuka.</p>
    </div>

    <!-- 3 Navigation Tabs with Glassmorphic Pills -->
    <div class="inline-flex items-center p-1 rounded-xl bg-surface-soft border border-line text-xs font-semibold shrink-0 shadow-2xs" role="tablist" aria-label="Menu Pengaturan Akun">
        <a href="{{ route('settings.profile') }}"
           id="tab-btn-profil"
           class="px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('settings.profile') ? 'bg-surface text-ink font-bold shadow-xs border border-line/60' : 'text-text-muted hover:text-ink hover:bg-surface/50' }}">
            <svg class="w-3.5 h-3.5 {{ request()->routeIs('settings.profile') ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profil</span>
        </a>
        <a href="{{ route('settings.security') }}"
           id="tab-btn-keamanan"
           class="px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('settings.security') ? 'bg-surface text-ink font-bold shadow-xs border border-line/60' : 'text-text-muted hover:text-ink hover:bg-surface/50' }}">
            <svg class="w-3.5 h-3.5 {{ request()->routeIs('settings.security') ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>Keamanan</span>
        </a>
        <a href="{{ route('settings.appearance') }}"
           id="tab-btn-tampilan"
           class="px-3.5 py-1.5 rounded-lg transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('settings.appearance') ? 'bg-surface text-ink font-bold shadow-xs border border-line/60' : 'text-text-muted hover:text-ink hover:bg-surface/50' }}">
            <svg class="w-3.5 h-3.5 {{ request()->routeIs('settings.appearance') ? 'text-brand-600 dark:text-brand-400' : 'text-text-muted' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
            <span>Tampilan</span>
        </a>
    </div>
</div>

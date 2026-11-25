<x-layouts.app title="Pengaturan Tampilan">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition-colors">Dashboard</a>
        <span class="text-text-muted">/</span>
        <span class="text-text-muted">Pengaturan Akun</span>
        <span class="text-text-muted">/</span>
        <span class="font-medium text-ink">Tampilan</span>
    </x-slot:breadcrumbs>

    <div class="max-w-4xl">
        @include('settings._nav')

        <div id="panel-tampilan" class="space-y-6">
            <!-- Card 1: Tema Antarmuka & Mode Warna -->
            <div class="bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-ink">Tema Antarmuka</h3>
                        <p class="text-xs text-text-muted mt-0.5">Pilih tampilan visual yang paling nyaman untuk Anda gunakan saat mengelola perpustakaan.</p>
                    </div>
                    <span id="current-theme-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 border border-brand-200/50">
                        Memuat...
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Option 1: Mode Terang (Light) -->
                    <button
                        type="button"
                        id="theme-opt-light"
                        onclick="setSettingsTheme('light')"
                        class="theme-card group relative p-4 rounded-xl border border-line bg-surface hover:bg-surface-soft text-left transition-all duration-200 cursor-pointer shadow-2xs hover:shadow-xs focus-visible:outline-2 focus-visible:outline-focus"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span class="theme-check w-5 h-5 rounded-full border border-line flex items-center justify-center transition-colors">
                                <svg class="w-3 h-3 text-white hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </div>
                        <p class="text-sm font-bold text-ink">Mode Terang</p>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">Nuansa Ivory &amp; Forest Green. Kontras tinggi, bersih, dan hangat.</p>
                        <!-- Visual Swatches -->
                        <div class="mt-3.5 flex items-center gap-1.5 pt-2 border-t border-line/60">
                            <span class="w-4 h-4 rounded-full bg-[#1b4332] shadow-2xs" title="Forest Green"></span>
                            <span class="w-4 h-4 rounded-full bg-[#fdfbf7] border border-line" title="Ivory Canvas"></span>
                            <span class="w-4 h-4 rounded-full bg-[#ffffff] border border-line" title="Surface White"></span>
                            <span class="text-[10px] text-text-muted ml-1">Ivory Klasik</span>
                        </div>
                    </button>

                    <!-- Option 2: Mode Gelap (Dark) -->
                    <button
                        type="button"
                        id="theme-opt-dark"
                        onclick="setSettingsTheme('dark')"
                        class="theme-card group relative p-4 rounded-xl border border-line bg-surface hover:bg-surface-soft text-left transition-all duration-200 cursor-pointer shadow-2xs hover:shadow-xs focus-visible:outline-2 focus-visible:outline-focus"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            </div>
                            <span class="theme-check w-5 h-5 rounded-full border border-line flex items-center justify-center transition-colors">
                                <svg class="w-3 h-3 text-white hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </div>
                        <p class="text-sm font-bold text-ink">Mode Gelap</p>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">Deep Emerald &amp; Midnight. Elegan, minim silau saat pencahayaan redup.</p>
                        <!-- Visual Swatches -->
                        <div class="mt-3.5 flex items-center gap-1.5 pt-2 border-t border-line/60">
                            <span class="w-4 h-4 rounded-full bg-[#0c1f16] shadow-2xs" title="Deep Emerald Canvas"></span>
                            <span class="w-4 h-4 rounded-full bg-[#133023] border border-line/40" title="Dark Surface"></span>
                            <span class="w-4 h-4 rounded-full bg-[#34d399] shadow-2xs" title="Emerald Accent"></span>
                            <span class="text-[10px] text-text-muted ml-1">Deep Emerald</span>
                        </div>
                    </button>

                    <!-- Option 3: Ikuti Sistem (System) -->
                    <button
                        type="button"
                        id="theme-opt-system"
                        onclick="setSettingsTheme('system')"
                        class="theme-card group relative p-4 rounded-xl border border-line bg-surface hover:bg-surface-soft text-left transition-all duration-200 cursor-pointer shadow-2xs hover:shadow-xs focus-visible:outline-2 focus-visible:outline-focus"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="theme-check w-5 h-5 rounded-full border border-line flex items-center justify-center transition-colors">
                                <svg class="w-3 h-3 text-white hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </div>
                        <p class="text-sm font-bold text-ink">Ikuti Sistem</p>
                        <p class="text-xs text-text-muted mt-1 leading-relaxed">Secara otomatis menyesuaikan preferensi terang atau gelap dari sistem operasi Anda.</p>
                        <!-- Visual Swatches -->
                        <div class="mt-3.5 flex items-center gap-1.5 pt-2 border-t border-line/60">
                            <span class="w-4 h-4 rounded-full bg-gradient-to-r from-amber-400 to-indigo-600 shadow-2xs" title="Auto Sync"></span>
                            <span class="text-[10px] text-text-muted ml-1">Otomatisasi OS</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateThemeCardsUI(theme) {
            const cards = {
                light: document.getElementById('theme-opt-light'),
                dark: document.getElementById('theme-opt-dark'),
                system: document.getElementById('theme-opt-system')
            };

            const badge = document.getElementById('current-theme-badge');

            Object.entries(cards).forEach(([key, card]) => {
                if (!card) return;
                const check = card.querySelector('.theme-check');
                const checkIcon = check?.querySelector('svg');

                if (key === theme) {
                    card.classList.add('border-brand-600', 'bg-brand-50/20', 'dark:bg-brand-900/20', 'ring-1', 'ring-brand-600');
                    card.classList.remove('border-line');
                    if (check) {
                        check.classList.add('bg-brand-600', 'border-brand-600');
                        check.classList.remove('border-line');
                    }
                    if (checkIcon) checkIcon.classList.remove('hidden');
                } else {
                    card.classList.remove('border-brand-600', 'bg-brand-50/20', 'dark:bg-brand-900/20', 'ring-1', 'ring-brand-600');
                    card.classList.add('border-line');
                    if (check) {
                        check.classList.remove('bg-brand-600', 'border-brand-600');
                        check.classList.add('border-line');
                    }
                    if (checkIcon) checkIcon.classList.add('hidden');
                }
            });

            if (badge) {
                if (theme === 'light') {
                    badge.textContent = 'Aktif: Mode Terang';
                } else if (theme === 'dark') {
                    badge.textContent = 'Aktif: Mode Gelap';
                } else {
                    badge.textContent = 'Aktif: Ikuti Sistem (' + (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'Gelap' : 'Terang') + ')';
                }
            }
        }

        function setSettingsTheme(theme) {
            if (theme === 'light') {
                localStorage.theme = 'light';
                document.documentElement.classList.remove('dark');
            } else if (theme === 'dark') {
                localStorage.theme = 'dark';
                document.documentElement.classList.add('dark');
            } else {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            updateThemeCardsUI(theme);
        }

        // Initialize active card state on load
        document.addEventListener('DOMContentLoaded', function () {
            const currentTheme = ('theme' in localStorage) ? localStorage.theme : 'system';
            updateThemeCardsUI(currentTheme);
        });
    </script>
    @endpush
</x-layouts.app>

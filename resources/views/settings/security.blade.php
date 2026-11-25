<x-layouts.app title="Pengaturan Keamanan">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition-colors">Dashboard</a>
        <span class="text-text-muted">/</span>
        <span class="text-text-muted">Pengaturan Akun</span>
        <span class="text-text-muted">/</span>
        <span class="font-medium text-ink">Keamanan</span>
    </x-slot:breadcrumbs>

    <div class="max-w-4xl">
        @include('settings._nav')

        @if (session('success'))
            <div role="status" class="mb-6 rounded-xl border border-success-fg/20 bg-success-bg px-4 py-3 text-sm text-success-fg dark:border-success-fg/30 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-xl border border-danger-fg/20 bg-danger-bg px-4 py-3 text-sm text-danger-fg dark:border-danger-fg/30 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div id="panel-keamanan" class="space-y-6">
            <!-- Card 1: Perbarui Kata Sandi -->
            <div class="bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs">
                <h3 class="text-base font-bold text-ink">Perbarui Kata Sandi</h3>
                <p class="text-xs text-text-muted mt-0.5">Pastikan akun Anda menggunakan kata sandi yang kuat dan unik.</p>

                <form action="{{ route('profile.password.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Kata Sandi Saat Ini -->
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-ink mb-1.5">Kata Sandi Saat Ini</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="w-full sm:max-w-md px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-2xs"
                            placeholder="••••••••"
                        >
                        @error('current_password')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Kata Sandi Baru -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-ink mb-1.5">Kata Sandi Baru</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-2xs"
                                placeholder="Minimal 8 karakter"
                            >
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Kata Sandi Baru -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-ink mb-1.5">Konfirmasi Kata Sandi Baru</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-2xs"
                                placeholder="Ulangi kata sandi baru"
                            >
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-surface-soft/60 border border-line/60 text-xs text-text-muted space-y-1">
                        <p class="font-semibold text-ink flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Panduan Kata Sandi Aman:</span>
                        </p>
                        <p>Gunakan kombinasi minimal 8 karakter dengan huruf kapital, huruf kecil, angka, dan karakter simbol khusus.</p>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-line">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm active:scale-98 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

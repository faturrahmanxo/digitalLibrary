<x-layouts.guest title="Daftar Akun">
    <!-- SCR-001: Register Page with Exact Matching Layout & Organic Split as Login -->
    <div class="relative w-full min-h-screen flex flex-col md:flex-row overflow-hidden bg-canvas dark:bg-surface transition-colors duration-300">
        
        <!-- Background Layer: Full Screen Library Bookshelf Image -->
        <div class="absolute inset-0 w-full h-full bg-cover bg-center transition-all duration-700" style="background-image: url('{{ asset('images/library-bookshelf.jpg') }}');">
            <!-- Atmospheric Scrim for Light & Dark Mode -->
            <div class="absolute inset-0 bg-[#1C2D23]/30 dark:bg-[#101A15]/80 backdrop-blur-[0.5px] transition-colors duration-300"></div>
        </div>

        <!-- Full-Screen SVG Organic Wave Layer for Desktop -->
        <div class="hidden md:block absolute inset-0 w-full h-full pointer-events-none z-10 text-canvas dark:text-surface transition-colors duration-300">
            <svg class="w-full h-full" viewBox="0 0 1000 1000" preserveAspectRatio="none">
                <path d="M 0 0 L 580 0 C 540 120, 640 220, 610 350 C 580 480, 470 600, 520 750 C 550 850, 510 930, 480 1000 L 0 1000 Z" fill="currentColor" />
            </svg>
        </div>

        <!-- Mobile Header Banner (< 768px) -->
        <div class="block md:hidden relative h-36 sm:h-44 w-full shrink-0 overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/library-bookshelf.jpg') }}');">
                <div class="absolute inset-0 bg-[#1C2D23]/35 dark:bg-[#101A15]/75"></div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-10 text-canvas dark:text-surface">
                <svg class="w-full h-full" viewBox="0 0 500 50" preserveAspectRatio="none">
                    <path d="M 0 50 L 500 50 L 500 20 C 350 45, 180 0, 0 30 Z" fill="currentColor" />
                </svg>
            </div>
            <div class="absolute top-4 left-4 inline-flex items-center rounded-full bg-canvas/90 px-3 py-1.5 shadow-sm backdrop-blur-md dark:bg-surface/90">
                <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-5 w-auto object-contain">
            </div>
        </div>

        <!-- Matching Form Canvas (Identical to Login) -->
        <div class="relative z-20 w-full md:w-[50%] lg:w-[45%] xl:w-[40%] min-h-screen flex flex-col justify-between p-6 sm:p-10 md:p-12 lg:p-14 overflow-y-auto auth-box font-sans">
            <div class="my-auto py-4 w-full max-w-sm mx-auto md:mx-0">
                
                <!-- Clean Litera Branding Lockup -->
                <div class="mb-5">
                    <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-10 w-auto object-contain">
                </div>

                <!-- Clean Simplified Title -->
                <div class="mb-5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight font-serif">Buat Akun Litera</h1>
                    <p class="mt-1 text-sm text-text-muted font-sans">Daftar untuk menjelajahi koleksi dan layanan Litera.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl bg-danger-bg border border-danger-fg/25 p-3.5 text-sm text-danger-fg">
                        <p class="font-semibold text-xs uppercase tracking-wider mb-1">Gagal Mendaftar:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" class="space-y-4" novalidate id="register-form" autocomplete="off">
                    @csrf

                    <!-- Full Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-ink mb-1.5">
                            Nama Lengkap <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Contoh: Muhammad Farhan"
                                class="w-full min-h-11 pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                            >
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-ink mb-1.5">
                            Email <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="off"
                                placeholder="nama@email.com"
                                class="w-full min-h-11 pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-ink mb-1.5">
                                Kata Sandi <span class="text-danger-fg" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Min. 6 karakter"
                                    class="w-full min-h-11 pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                                >
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-ink mb-1.5">
                                Konfirmasi <span class="text-danger-fg" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi sandi"
                                    class="w-full min-h-11 pl-10 pr-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Clean Full-Width Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            id="register-submit-btn"
                            class="w-full min-h-11 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-sm font-semibold shadow-md shadow-brand-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus transition-all cursor-pointer flex items-center justify-center disabled:opacity-50"
                        >
                            Buat Akun
                        </button>
                    </div>

                    <!-- Switch to Login -->
                    <p class="text-center text-xs text-text-muted pt-2">
                        Sudah memiliki akun terdaftar? 
                        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Masuk di sini</a>
                    </p>
                </form>
            </div>

            <!-- Clean Minimalist Footer -->
            <div class="pt-4 text-xs text-text-muted/80">
                <p>&copy; <span class="font-year font-bold">{{ date('Y') }}</span> Litera Perpustakaan Digital</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const regForm = document.getElementById('register-form');
            const submitBtn = document.getElementById('register-submit-btn');

            if (regForm && submitBtn) {
                regForm.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mendaftarkan akun...';
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
</x-layouts.guest>

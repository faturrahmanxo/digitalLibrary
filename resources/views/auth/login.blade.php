<x-layouts.guest title="Masuk">
    <!-- SCR-001: Simplified Modern Full-Screen Login with Litera Branding -->
    <div class="relative w-full min-h-screen flex flex-col md:flex-row overflow-hidden bg-canvas dark:bg-surface transition-colors duration-300">
        
        <!-- Background Layer: Full Screen Library Bookshelf Image (1.jpg) -->
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
        <div class="block md:hidden relative h-40 sm:h-48 w-full shrink-0 overflow-hidden">
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

        <!-- Simplified Form Canvas (Left Side on Desktop, Bottom on Mobile) -->
        <div class="relative z-20 w-full md:w-[50%] lg:w-[45%] xl:w-[40%] min-h-screen flex flex-col justify-between p-6 sm:p-10 md:p-12 lg:p-16 auth-box font-sans">
            <div class="my-auto py-4 w-full max-w-sm mx-auto md:mx-0">
                <!-- Clean Litera Branding Lockup -->
                <div class="mb-6">
                    <img src="{{ asset('images/logo-Litera.png') }}" alt="Litera" width="1326" height="529" class="h-10 w-auto object-contain">
                </div>

                <!-- Clean Simplified Title -->
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight font-serif">Masuk</h1>
                    <p class="mt-1 text-sm text-text-muted font-sans">Masukkan akun Anda untuk melanjutkan.</p>
                </div>

                @if (session('status'))
                    <div role="status" class="mb-5 rounded-xl bg-info-bg border border-info-fg/20 px-3.5 py-2.5 text-sm font-medium text-info-fg flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0 text-info-fg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl bg-danger-bg border border-danger-fg/25 p-3.5 text-sm text-danger-fg">
                        <p class="font-semibold text-xs uppercase tracking-wider mb-1">Gagal Masuk:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="space-y-4" novalidate id="login-form" autocomplete="off">
                    @csrf

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
                                autofocus
                                autocomplete="off"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @if($errors->has('email')) aria-describedby="email-error" @endif
                                placeholder="nama@perpustakaan.local"
                                class="w-full min-h-11 pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                            >
                        </div>
                        @error('email')
                            <p id="email-error" class="mt-1 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

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
                                placeholder="Masukkan kata sandi"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                @if($errors->has('password')) aria-describedby="password-error" @endif
                                class="w-full min-h-11 pl-10 pr-12 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm placeholder:text-text-muted/60 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all shadow-xs"
                            >
                            <button
                                type="button"
                                id="toggle-password-btn"
                                aria-label="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 px-3.5 flex items-center justify-center text-text-muted hover:text-ink focus-visible:outline-2 focus-visible:outline-focus rounded-r-xl cursor-pointer transition-colors"
                            >
                                <!-- Eye Open Icon -->
                                <svg id="eye-open-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye Slash Icon -->
                                <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Clean Full-Width Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            id="login-submit-btn"
                            data-testid="login-submit"
                            class="w-full min-h-11 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-sm font-semibold shadow-md shadow-brand-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus transition-all cursor-pointer flex items-center justify-center disabled:opacity-50"
                        >
                            Masuk
                        </button>
                    </div>

                    <!-- Switch to Register -->
                    <p class="text-center text-xs text-text-muted pt-2">
                        Belum memiliki akun Litera?
                        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Daftar di sini</a>
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
            const pwdInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password-btn');
            const loginForm = document.getElementById('login-form');
            const submitBtn = document.getElementById('login-submit-btn');

            const eyeOpen = document.getElementById('eye-open-icon');
            const eyeSlash = document.getElementById('eye-slash-icon');

            // Pastikan email & password harus diinput ulang dan tidak tertinggal setelah sesi keluar (logout)
            const isLoggedOut = {{ session('status') ? 'true' : 'false' }};
            if (isLoggedOut || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                if (pwdInput) pwdInput.value = '';
                const emailInput = document.getElementById('email');
                if (emailInput && isLoggedOut) emailInput.value = '';
            }

            if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', function () {
                    const isHidden = pwdInput.getAttribute('type') === 'password';
                    pwdInput.setAttribute('type', isHidden ? 'text' : 'password');
                    if (eyeOpen && eyeSlash) {
                        eyeOpen.classList.toggle('hidden', isHidden);
                        eyeSlash.classList.toggle('hidden', !isHidden);
                    }
                    toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                });
            }

            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Memproses...';
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
</x-layouts.guest>
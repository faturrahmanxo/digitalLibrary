<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Profil Saya — Litera</title>
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
</head>
<body class="min-h-full bg-canvas text-ink antialiased flex flex-col font-sans">
    <x-navbar-public />

    <main class="w-full flex-1 px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <div class="mx-auto w-full max-w-3xl">
            <header class="mb-8 sm:mb-10">
                <h1 class="font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">Profil Saya</h1>
                <p class="mt-2 text-sm text-text-muted sm:text-base">Kelola informasi dan keamanan akunmu.</p>
            </header>

            @if (session('success'))
                <div role="status" class="mb-6 rounded-xl border border-success-fg/20 bg-success-bg px-4 py-3 text-sm text-success-fg dark:border-success-fg/30">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mb-6 rounded-xl border border-danger-fg/20 bg-danger-bg px-4 py-3 text-sm text-danger-fg dark:border-danger-fg/30">
                    {{ session('error') }}
                </div>
            @endif

            <div class="divide-y divide-line rounded-2xl border border-line bg-surface px-5 shadow-xs sm:px-8">
                <section aria-labelledby="profile-photo-heading" class="py-6 sm:py-8">
                    <h2 id="profile-photo-heading" class="font-serif text-2xl font-semibold text-ink">Foto Profil</h2>
                    <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
                        <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-full bg-brand-100 text-brand-700 ring-1 ring-line dark:bg-brand-900/40 dark:text-[#F9E6A8]">
                            <img
                                id="profile-photo-preview"
                                src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : '' }}"
                                alt="Pratinjau foto profil"
                                @if (! $user->profile_photo_path) hidden @endif
                                class="h-full w-full object-cover"
                            >
                            <div
                                id="profile-avatar-fallback"
                                @if ($user->profile_photo_path) hidden @endif
                                class="flex h-full w-full items-center justify-center text-xl font-bold"
                                role="img"
                                aria-label="Inisial {{ $user->name }}"
                            >
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </div>

                        <div class="min-w-0 flex-1 text-center sm:text-left">
                            <p class="truncate font-semibold text-ink">{{ $user->name }}</p>
                            <p class="mt-1 text-sm text-text-muted">JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.</p>

                            <form
                                id="profile-photo-form"
                                method="POST"
                                action="{{ route('profile.photo.store') }}"
                                enctype="multipart/form-data"
                                data-loading-form
                                class="mt-4 flex flex-wrap items-center justify-center gap-2 sm:justify-start"
                            >
                                @csrf
                                <input
                                    id="profile-photo"
                                    name="photo"
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    required
                                    tabindex="-1"
                                    aria-hidden="true"
                                    class="sr-only"
                                >
                                <button
                                    id="choose-profile-photo"
                                    type="button"
                                    aria-controls="profile-photo"
                                    class="rounded-control border border-line bg-surface px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-focus"
                                >
                                    Ganti Foto
                                </button>
                                <button
                                    id="save-profile-photo"
                                    type="submit"
                                    disabled
                                    data-loading-text="Menyimpan..."
                                    class="rounded-control bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span data-submit-label>Simpan Foto</span>
                                </button>
                                @if ($user->profile_photo_path)
                                    <button
                                        type="submit"
                                        form="delete-profile-photo-form"
                                        class="rounded-control px-4 py-2 text-sm font-semibold text-danger-fg transition-colors hover:bg-danger-bg focus-visible:outline-2 focus-visible:outline-focus"
                                    >
                                        Hapus Foto
                                    </button>
                                @endif
                            </form>

                            <p id="profile-photo-client-error" class="mt-2 text-sm text-danger-fg" aria-live="polite"></p>
                            @error('photo')
                                <p class="mt-2 text-sm text-danger-fg">{{ $message }}</p>
                            @enderror

                            @if ($user->profile_photo_path)
                                <form id="delete-profile-photo-form" method="POST" action="{{ route('profile.photo.destroy') }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </div>
                </section>

                <section aria-labelledby="account-information-heading" class="py-6 sm:py-8">
                    <h2 id="account-information-heading" class="font-serif text-2xl font-semibold text-ink">Informasi Akun</h2>

                    <form id="profile-update-form" method="POST" action="{{ route('profile.update') }}" data-loading-form class="mt-5 space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-semibold text-ink">Nama Tampilan</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                maxlength="100"
                                autocomplete="name"
                                required
                                class="w-full rounded-control border border-line bg-canvas px-3.5 py-2.5 text-sm text-ink outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:focus:border-brand-400"
                                @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif
                            >
                            @error('name')
                                <p id="name-error" class="mt-1.5 text-sm text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="username" class="mb-1.5 block text-sm font-semibold text-ink">Username</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ old('username', $user->username) }}"
                                minlength="3"
                                maxlength="30"
                                pattern="[a-z0-9._]+"
                                autocapitalize="none"
                                spellcheck="false"
                                autocomplete="username"
                                required
                                class="w-full rounded-control border border-line bg-canvas px-3.5 py-2.5 text-sm text-ink outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:focus:border-brand-400"
                                aria-describedby="username-hint @if ($errors->has('username')) username-error @endif"
                                @if ($errors->has('username')) aria-invalid="true" @endif
                            >
                            <p id="username-hint" class="mt-1.5 text-xs text-text-muted">Gunakan 3-30 karakter: huruf kecil, angka, titik, atau garis bawah.</p>
                            @error('username')
                                <p id="username-error" class="mt-1.5 text-sm text-danger-fg">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ $user->email }}"
                                readonly
                                autocomplete="email"
                                class="w-full cursor-not-allowed rounded-control border border-line bg-surface-soft px-3.5 py-2.5 text-sm text-text-muted"
                            >
                            <p class="mt-1.5 text-xs text-text-muted">Email akun tidak dapat diubah.</p>
                        </div>

                        <button
                            type="submit"
                            data-loading-text="Menyimpan..."
                            class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:cursor-wait disabled:opacity-70"
                        >
                            <span data-submit-label>Simpan Perubahan</span>
                        </button>
                    </form>
                </section>

                <section aria-labelledby="account-security-heading" class="py-6 sm:py-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 id="account-security-heading" class="font-serif text-2xl font-semibold text-ink">Keamanan Akun</h2>
                            <p class="mt-1.5 text-sm text-text-muted">Perbarui kata sandi untuk menjaga keamanan akunmu.</p>
                        </div>
                        <button
                            id="toggle-password-form"
                            type="button"
                            aria-controls="password-fields"
                            aria-expanded="{{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }}"
                            class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-focus"
                        >
                            Ubah Kata Sandi
                        </button>
                    </div>

                    <div id="password-fields" @if (! $errors->has('current_password') && ! $errors->has('password')) hidden @endif class="mt-6">
                        <form id="password-update-form" method="POST" action="{{ route('profile.password.update') }}" data-loading-form class="space-y-5">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="current_password" class="mb-1.5 block text-sm font-semibold text-ink">Kata Sandi Saat Ini</label>
                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    class="w-full rounded-control border border-line bg-canvas px-3.5 py-2.5 text-sm text-ink outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:focus:border-brand-400"
                                >
                                @error('current_password')
                                    <p class="mt-1.5 text-sm text-danger-fg">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">Kata Sandi Baru</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="w-full rounded-control border border-line bg-canvas px-3.5 py-2.5 text-sm text-ink outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:focus:border-brand-400"
                                >
                                @error('password')
                                    <p class="mt-1.5 text-sm text-danger-fg">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-ink">Konfirmasi Kata Sandi Baru</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="w-full rounded-control border border-line bg-canvas px-3.5 py-2.5 text-sm text-ink outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:focus:border-brand-400"
                                >
                            </div>

                            <button
                                type="submit"
                                data-loading-text="Memperbarui..."
                                class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:cursor-wait disabled:opacity-70"
                            >
                                <span data-submit-label>Simpan Kata Sandi</span>
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <x-footer-public />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoInput = document.getElementById('profile-photo');
            const preview = document.getElementById('profile-photo-preview');
            const fallback = document.getElementById('profile-avatar-fallback');
            const photoError = document.getElementById('profile-photo-client-error');
            const savePhotoButton = document.getElementById('save-profile-photo');
            let previewUrl = null;

            document.getElementById('choose-profile-photo')?.addEventListener('click', function () {
                photoInput?.click();
            });

            photoInput?.addEventListener('change', function () {
                const file = photoInput.files?.[0];
                photoError.textContent = '';

                if (!file) {
                    savePhotoButton.disabled = true;
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
                    photoError.textContent = !allowedTypes.includes(file.type)
                        ? 'Pilih foto berformat JPG, JPEG, PNG, atau WebP.'
                        : 'Ukuran foto maksimal 2 MB.';
                    photoInput.value = '';
                    savePhotoButton.disabled = true;
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }

                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
                preview.hidden = false;
                fallback.hidden = true;
                savePhotoButton.disabled = false;
            });

            document.getElementById('toggle-password-form')?.addEventListener('click', function () {
                const passwordFields = document.getElementById('password-fields');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', String(!isExpanded));
                passwordFields.hidden = isExpanded;

                if (!isExpanded) {
                    document.getElementById('current_password').focus();
                }
            });

            document.querySelectorAll('[data-loading-form]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('button[type="submit"]:not([form])');
                    const label = button?.querySelector('[data-submit-label]');

                    if (button && label) {
                        button.disabled = true;
                        button.setAttribute('aria-busy', 'true');
                        label.textContent = button.dataset.loadingText || 'Menyimpan...';
                    }
                });
            });
        });
    </script>
</body>
</html>

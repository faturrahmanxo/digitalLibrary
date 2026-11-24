<x-layouts.app title="Pengaturan Profil">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition-colors">Dashboard</a>
        <span class="text-text-muted">/</span>
        <span class="text-text-muted">Pengaturan Akun</span>
        <span class="text-text-muted">/</span>
        <span class="font-medium text-ink">Profil</span>
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

        <div id="panel-profil" class="space-y-6">
            <!-- Card 1: Foto Profil Pengelola -->
            <div class="bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs">
                <h3 class="text-base font-bold text-ink">Foto Profil</h3>
                <p class="text-xs text-text-muted mt-0.5">Foto akan ditampilkan di header dan menu navigasi samping.</p>

                <div class="mt-5 flex flex-col sm:flex-row items-center gap-5">
                    <div class="relative w-20 h-20 shrink-0 rounded-full bg-brand-600/15 dark:bg-brand-400/20 border-2 border-brand-500/40 text-brand-700 dark:text-brand-300 font-bold text-2xl flex items-center justify-center overflow-hidden shadow-sm">
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>

                    <div class="flex-1 text-center sm:text-left space-y-3">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <form action="{{ route('profile.photo.store') }}" method="POST" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                                @csrf
                                <label for="profile_photo_input" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-line bg-surface hover:bg-surface-soft text-xs font-semibold text-ink cursor-pointer transition-colors shadow-2xs">
                                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Unggah Foto Baru</span>
                                </label>
                                <input id="profile_photo_input" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="sr-only" onchange="this.form.submit()">
                            </form>

                            @if ($user->profile_photo_path)
                                <form action="{{ route('profile.photo.destroy') }}" method="POST" class="inline-flex" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-900/20 hover:bg-rose-100/60 text-xs font-semibold text-rose-700 dark:text-rose-300 transition-colors shadow-2xs">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Hapus Foto</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <p class="text-[11px] text-text-muted">Format yang didukung: JPG, JPEG, PNG, WebP. Ukuran berkas maksimal 2 MB.</p>
                        @error('photo')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Identitas Akun -->
            <div class="bg-surface rounded-2xl border border-line p-5 sm:p-6 shadow-xs">
                <h3 class="text-base font-bold text-ink">Informasi Akun</h3>
                <p class="text-xs text-text-muted mt-0.5">Perbarui nama tampilan dan nama pengguna Anda.</p>

                <form action="{{ route('profile.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-xs font-semibold text-ink mb-1.5">Nama Tampilan</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                maxlength="100"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-2xs"
                                placeholder="Masukkan nama tampilan"
                            >
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-xs font-semibold text-ink mb-1.5">Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="{{ old('username', $user->username) }}"
                                required
                                minlength="3"
                                maxlength="30"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line bg-surface text-ink text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors shadow-2xs font-mono"
                                placeholder="username"
                            >
                            @error('username')
                                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Email (Readonly) -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-ink mb-1.5">Alamat Email</label>
                            <input
                                type="email"
                                id="email"
                                value="{{ $user->email }}"
                                disabled
                                class="w-full px-3.5 py-2.5 rounded-xl border border-line/60 bg-surface-soft text-text-muted text-sm cursor-not-allowed shadow-2xs"
                            >
                            <p class="text-[11px] text-text-muted mt-1">Alamat email bersifat permanen untuk identifikasi akun.</p>
                        </div>

                        <!-- Role / Peran -->
                        <div>
                            <label class="block text-xs font-semibold text-ink mb-1.5">Peran / Hak Akses</label>
                            <div class="px-3.5 py-2.5 rounded-xl border border-line/60 bg-surface-soft flex items-center justify-between shadow-2xs">
                                <span class="text-sm font-semibold text-ink">{{ $user->role_label }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' : ($user->isPetugas() ? 'bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 border border-brand-200/50' : 'bg-surface border border-line text-text-muted') }}">
                                    {{ $user->role }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-line">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-sm active:scale-98 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

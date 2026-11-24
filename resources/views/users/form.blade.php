<x-layouts.app :title="$isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('users.index') }}" class="hover:text-ink">Pengguna</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna' }}</span>
    </x-slot:breadcrumbs>

    <!-- SCR-004: User Create / Edit -->
    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui informasi akun dan peran akses pengguna internal.' : 'Buat akun baru untuk staf Admin atau Petugas perpustakaan.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('users.update', $userModel) : route('users.store') }}" class="bg-surface rounded-card border border-line p-6 space-y-5" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-1.5">
                    Nama Lengkap <span class="text-danger-fg" aria-hidden="true">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    value="{{ old('name', $userModel->name) }}"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    @if($errors->has('name')) aria-describedby="name-error" @endif
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                >
                @error('name')
                    <p id="name-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-ink mb-1.5">
                    Alamat Email <span class="text-danger-fg" aria-hidden="true">*</span>
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    value="{{ old('email', $userModel->email) }}"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @if($errors->has('email')) aria-describedby="email-error" @endif
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('email') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                >
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-ink mb-1.5">
                    Peran Akses <span class="text-danger-fg" aria-hidden="true">*</span>
                </label>
                <select
                    id="role"
                    name="role"
                    required
                    aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                    @if($errors->has('role')) aria-describedby="role-error" @endif
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('role') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                >
                    <option value="petugas" @selected(old('role', $userModel->role) === 'petugas')>Petugas (Kelola Buku, Anggota, Peminjaman; Kategori Baca-Saja)</option>
                    <option value="admin" @selected(old('role', $userModel->role) === 'admin')>Admin (Akses Penuh termasuk Pengguna & Kategori)</option>
                    <option value="anggota" @selected(old('role', $userModel->role) === 'anggota')>Anggota (Akses Katalog Buku & Riwayat Peminjaman Pribadi)</option>
                </select>
                @error('role')
                    <p id="role-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 border-t border-line grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-ink mb-1.5">
                        Kata Sandi @if(!$isEdit)<span class="text-danger-fg" aria-hidden="true">*</span>@endif
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        @if(!$isEdit) required @endif
                        autocomplete="new-password"
                        aria-describedby="password-help @error('password') password-error @enderror"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('password') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    <p id="password-help" class="mt-1 text-xs text-text-muted">
                        {{ $isEdit ? 'Biarkan kosong jika tidak ingin mengganti kata sandi.' : 'Minimal 8 karakter.' }}
                    </p>
                    @error('password')
                        <p id="password-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-1.5">
                        Konfirmasi Kata Sandi @if(!$isEdit)<span class="text-danger-fg" aria-hidden="true">*</span>@endif
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        @if(!$isEdit) required @endif
                        autocomplete="new-password"
                        class="w-full min-h-11 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                </div>
            </div>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ route('users.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 cursor-pointer">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

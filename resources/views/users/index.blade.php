<x-layouts.app title="Pengguna">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Pengguna</span>
    </x-slot:breadcrumbs>

    <!-- SCR-003: User List (Admin Only) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Pengguna</h1>
            <p class="mt-1 text-sm text-text-muted">Kelola akun internal Admin dan Petugas perpustakaan.</p>
        </div>
        <button
            type="button"
            id="btn-open-create-user-modal"
            class="min-h-11 inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer shadow-xs transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('users.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="q" class="sr-only">Cari nama atau email pengguna</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Cari nama atau email pengguna..." class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
        </div>
        <div class="sm:w-48">
            <label for="role" class="sr-only">Filter Peran</label>
            <select id="role" name="role" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
                <option value="">Semua Peran</option>
                <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                <option value="petugas" @selected($roleFilter === 'petugas')>Petugas</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="min-h-10 px-4 py-2 rounded-control bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">
                Terapkan
            </button>
            @if ($search !== '' || $roleFilter !== '')
                <a href="{{ route('users.index') }}" class="min-h-10 inline-flex items-center px-3 py-2 rounded-control border border-line text-sm font-medium text-text-muted hover:text-ink">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Users List Component -->
    <div class="sv lst sv-card shadow-xs">
        <div class="lst__head">
            <b>Daftar Pengguna</b>
            <span>{{ $users->total() }} akun</span>
        </div>

        @if ($users->isEmpty())
            <div class="p-10 text-center">
                <p class="text-base font-semibold text-ink">Tidak ada pengguna yang cocok.</p>
                <p class="mt-1 text-sm text-text-muted">Tambahkan pengguna baru atau ubah kata kunci pencarian.</p>
            </div>
        @else
            <ul class="lst__ul" aria-label="Daftar Pengguna">
                @foreach ($users as $userItem)
                    @php
                        $isSelf = auth()->id() === $userItem->id;
                        $isLastAdmin = $userItem->isAdmin() && $activeAdminCount <= 1;
                        $canArchive = ! $isSelf && ! $isLastAdmin;
                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $userItem->name) ?: 'US', 0, 2));

                        if ($userItem->isAdmin()) {
                            $avClass = 'lst__av';
                        } elseif ($userItem->isPetugas()) {
                            $avClass = 'lst__av lst__av--b';
                        } else {
                            $avClass = 'lst__av lst__av--d';
                        }
                    @endphp
                    <li class="lst__row" style="{{ $canArchive ? '' : '--lst-acts-w: 48px;' }}">
                        <!-- Hidden actions revealed on hover & keyboard :focus-within -->
                        <div class="lst__acts">
                            <button
                                type="button"
                                class="btn-edit-user"
                                data-user-id="{{ $userItem->id }}"
                                data-user-name="{{ $userItem->name }}"
                                data-user-email="{{ $userItem->email }}"
                                data-user-role="{{ $userItem->role }}"
                                data-action-url="{{ route('users.update', $userItem) }}"
                                aria-label="Edit pengguna {{ $userItem->name }}"
                                title="Edit Pengguna"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            @if ($canArchive)
                                <button
                                    type="button"
                                    class="lst__del"
                                    data-archive-action="{{ route('users.destroy', $userItem) }}"
                                    data-archive-title="Arsipkan Pengguna?"
                                    data-archive-desc="Akun {{ $userItem->name }} ({{ $userItem->email }}) tidak akan dapat masuk lagi, namun riwayat transaksi yang pernah dicatat tetap tersimpan."
                                    data-archive-label="Arsipkan Pengguna"
                                    aria-label="Arsipkan pengguna {{ $userItem->name }}"
                                    title="Arsipkan Pengguna"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 13h10l1-13M9 7V4h6v3"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Main Row Content -->
                        <div class="lst__main" tabindex="0">
                            <span class="{{ $avClass }}">
                                {{ $initials }}
                                <i class="lst__on {{ $userItem->deleted_at ? 'lst__on--away' : '' }}" title="{{ $userItem->role_label }}"></i>
                            </span>

                            <span class="lst__txt">
                                <b>
                                    {{ $userItem->name }}
                                    @if ($isSelf)
                                        <small class="inline font-normal text-text-muted ml-1.5">(Anda)</small>
                                    @endif
                                </b>
                                <small>{{ $userItem->email }}</small>
                            </span>

                            @if ($userItem->isAdmin())
                                <span class="lst__role font-semibold">Admin</span>
                            @elseif ($userItem->isPetugas())
                                <span class="lst__role">Petugas</span>
                            @else
                                <span class="lst__role text-text-muted">Anggota</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($users->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Create User Popup Modal -->
    <div id="create-user-modal" class="fixed inset-0 z-50 {{ $errors->any() && !old('_method') ? 'flex' : 'hidden' }} items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="create-user-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity cursor-pointer"></div>

        <!-- Modal Content Box -->
        <div class="relative z-10 w-full max-w-lg rounded-2xl bg-surface border border-line p-6 shadow-2xl animate-fade-in-up">
            <div class="flex items-center justify-between pb-4 border-b border-line">
                <div>
                    <h3 class="text-lg font-bold text-ink">Tambah Pengguna Baru</h3>
                    <p class="text-xs text-text-muted mt-0.5">Buat akun staf Admin, Petugas, atau Sivitas Anggota.</p>
                </div>
                <button type="button" id="btn-close-create-user" class="p-1 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft text-sm cursor-pointer">&times;</button>
            </div>

            <form id="create-user-form" method="POST" action="{{ route('users.store') }}" class="space-y-4 mt-4" novalidate>
                @csrf

                <div>
                    <label for="create_user_name" class="block text-xs font-semibold text-ink mb-1">
                        Nama Lengkap <span class="text-danger-fg">*</span>
                    </label>
                    <input
                        type="text"
                        id="create_user_name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="Contoh: Budi Santoso"
                        class="w-full min-h-10 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-danger-fg font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="create_user_email" class="block text-xs font-semibold text-ink mb-1">
                        Alamat Email <span class="text-danger-fg">*</span>
                    </label>
                    <input
                        type="email"
                        id="create_user_email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="nama@perpustakaan.local"
                        class="w-full min-h-10 px-3.5 py-2 rounded-control border {{ $errors->has('email') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-danger-fg font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="create_user_role" class="block text-xs font-semibold text-ink mb-1">
                        Peran Akses <span class="text-danger-fg">*</span>
                    </label>
                    <select
                        id="create_user_role"
                        name="role"
                        required
                        class="w-full min-h-10 px-3 py-2 rounded-control border {{ $errors->has('role') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                        <option value="petugas" @selected(old('role') === 'petugas')>Petugas (Kelola Buku, Anggota, Peminjaman)</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin (Akses Penuh Manajemen)</option>
                        <option value="anggota" @selected(old('role') === 'anggota')>Anggota (Sivitas Mahasiswa / Dosen)</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-danger-fg font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="create_user_password" class="block text-xs font-semibold text-ink mb-1">
                            Kata Sandi <span class="text-danger-fg">*</span>
                        </label>
                        <input
                            type="password"
                            id="create_user_password"
                            name="password"
                            required
                            placeholder="Min. 8 karakter"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border {{ $errors->has('password') ? 'border-danger-fg ring-1 ring-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-danger-fg font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="create_user_password_confirmation" class="block text-xs font-semibold text-ink mb-1">
                            Konfirmasi Sandi <span class="text-danger-fg">*</span>
                        </label>
                        <input
                            type="password"
                            id="create_user_password_confirmation"
                            name="password_confirmation"
                            required
                            placeholder="Ulangi sandi"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                    </div>
                </div>

                <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                    <button type="button" id="btn-cancel-create-user" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-create-user" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Popup Modal -->
    <div id="edit-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="edit-user-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity cursor-pointer"></div>

        <!-- Modal Content Box -->
        <div class="relative w-full max-w-lg rounded-card bg-surface border border-line p-6 shadow-xl z-10">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div>
                    <h3 class="text-base font-bold text-ink">Edit Data Pengguna</h3>
                    <p class="text-xs text-text-muted mt-0.5">Perbarui informasi profil dan peran akses pengguna perpustakaan.</p>
                </div>
                <button type="button" id="btn-close-edit-user" class="p-1 rounded-lg text-text-muted hover:text-ink hover:bg-surface-soft text-sm cursor-pointer">&times;</button>
            </div>

            <form id="edit-user-form" method="POST" action="" class="space-y-4 mt-4" novalidate>
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_user_name" class="block text-xs font-semibold text-ink mb-1">
                        Nama Lengkap <span class="text-danger-fg">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_user_name"
                        name="name"
                        required
                        placeholder="Contoh: Budi Santoso"
                        class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                </div>

                <div>
                    <label for="edit_user_email" class="block text-xs font-semibold text-ink mb-1">
                        Alamat Email <span class="text-danger-fg">*</span>
                    </label>
                    <input
                        type="email"
                        id="edit_user_email"
                        name="email"
                        required
                        placeholder="nama@perpustakaan.local"
                        class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                </div>

                <div>
                    <label for="edit_user_role" class="block text-xs font-semibold text-ink mb-1">
                        Peran Akses <span class="text-danger-fg">*</span>
                    </label>
                    <select
                        id="edit_user_role"
                        name="role"
                        required
                        class="w-full min-h-10 px-3 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                    >
                        <option value="petugas">Petugas (Kelola Buku, Anggota, Peminjaman)</option>
                        <option value="admin">Admin (Akses Penuh Manajemen)</option>
                        <option value="anggota">Anggota (Sivitas Mahasiswa / Dosen)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-line">
                    <div>
                        <label for="edit_user_password" class="block text-xs font-semibold text-ink mb-1">
                            Kata Sandi Baru <span class="text-[10px] text-text-muted font-normal">(opsional)</span>
                        </label>
                        <input
                            type="password"
                            id="edit_user_password"
                            name="password"
                            placeholder="Kosongkan jika tak diubah"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                    </div>
                    <div>
                        <label for="edit_user_password_confirmation" class="block text-xs font-semibold text-ink mb-1">
                            Ulangi Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="edit_user_password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi sandi baru"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                    </div>
                </div>

                <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                    <button type="button" id="btn-cancel-edit-user" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-edit-user" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Create User Modal
            const openUserBtn = document.getElementById('btn-open-create-user-modal');
            const userModal = document.getElementById('create-user-modal');
            const closeUserBtn = document.getElementById('btn-close-create-user');
            const cancelUserBtn = document.getElementById('btn-cancel-create-user');
            const userBackdrop = document.getElementById('create-user-backdrop');
            const userForm = document.getElementById('create-user-form');
            const userSubmit = document.getElementById('btn-submit-create-user');

            function openModal() {
                if (userModal) {
                    userModal.classList.remove('hidden');
                    userModal.classList.add('flex');
                    const firstInput = document.getElementById('create_user_name');
                    if (firstInput) setTimeout(() => firstInput.focus(), 50);
                }
            }

            function closeModal() {
                if (userModal) {
                    userModal.classList.add('hidden');
                    userModal.classList.remove('flex');
                }
            }

            if (openUserBtn) openUserBtn.addEventListener('click', openModal);
            if (closeUserBtn) closeUserBtn.addEventListener('click', closeModal);
            if (cancelUserBtn) cancelUserBtn.addEventListener('click', closeModal);
            if (userBackdrop) userBackdrop.addEventListener('click', closeModal);

            // Edit User Modal
            const editModal = document.getElementById('edit-user-modal');
            const closeEditBtn = document.getElementById('btn-close-edit-user');
            const cancelEditBtn = document.getElementById('btn-cancel-edit-user');
            const editBackdrop = document.getElementById('edit-user-backdrop');
            const editForm = document.getElementById('edit-user-form');
            const editSubmit = document.getElementById('btn-submit-edit-user');
            const editButtons = document.querySelectorAll('.btn-edit-user');

            const editName = document.getElementById('edit_user_name');
            const editEmail = document.getElementById('edit_user_email');
            const editRole = document.getElementById('edit_user_role');
            const editPassword = document.getElementById('edit_user_password');
            const editPasswordConfirm = document.getElementById('edit_user_password_confirmation');

            function openEditModal(button) {
                const actionUrl = button.getAttribute('data-action-url');
                const name = button.getAttribute('data-user-name') || '';
                const email = button.getAttribute('data-user-email') || '';
                const role = button.getAttribute('data-user-role') || 'petugas';

                if (editForm) editForm.action = actionUrl;
                if (editName) editName.value = name;
                if (editEmail) editEmail.value = email;
                if (editRole) editRole.value = role;
                if (editPassword) editPassword.value = '';
                if (editPasswordConfirm) editPasswordConfirm.value = '';

                if (editModal) {
                    editModal.classList.remove('hidden');
                    editModal.classList.add('flex');
                    if (editName) setTimeout(() => editName.focus(), 50);
                }
            }

            function closeEditModal() {
                if (editModal) {
                    editModal.classList.add('hidden');
                    editModal.classList.remove('flex');
                }
            }

            editButtons.forEach(btn => {
                btn.addEventListener('click', () => openEditModal(btn));
            });

            if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
            if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);
            if (editBackdrop) editBackdrop.addEventListener('click', closeEditModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (userModal && !userModal.classList.contains('hidden')) {
                        closeModal();
                    }
                    if (editModal && !editModal.classList.contains('hidden')) {
                        closeEditModal();
                    }
                }
            });

            @if ($errors->any())
                openModal();
            @endif

            if (userForm && userSubmit) {
                userForm.addEventListener('submit', function () {
                    userSubmit.disabled = true;
                    userSubmit.textContent = 'Menyimpan...';
                    userSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }

            if (editForm && editSubmit) {
                editForm.addEventListener('submit', function () {
                    editSubmit.disabled = true;
                    editSubmit.textContent = 'Menyimpan...';
                    editSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

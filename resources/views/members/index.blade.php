<x-layouts.app title="Anggota Perpustakaan">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Anggota</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <div role="alert" class="mb-6 rounded-card border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- SCR-010: Member List -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Anggota Perpustakaan</h1>
            <p class="mt-1 text-sm text-text-muted">Daftar anggota terdaftar yang berhak melakukan peminjaman buku.</p>
        </div>
        <button
            type="button"
            id="btn-open-create-member-popup"
            class="min-h-11 inline-flex items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
        >
            Tambah Anggota
        </button>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('members.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-xs">
        <div class="flex-1">
            <label for="q" class="sr-only">Cari kode atau nama anggota</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Cari kode anggota (mis. AGT-00042) atau nama..." class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors cursor-pointer">
                Cari
            </button>
            @if ($search !== '')
                <a href="{{ route('members.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-sm font-medium text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Members List Component -->
    <div class="sv lst sv-card shadow-xs">
        <div class="lst__head">
            <b>Daftar Anggota</b>
            <span>{{ $members->total() }} anggota</span>
        </div>

        @if ($members->isEmpty())
            <div class="p-10 text-center">
                @if ($search !== '')
                    <p class="text-base font-semibold text-ink">Tidak ada anggota yang cocok dengan pencarian.</p>
                    <a href="{{ route('members.index') }}" class="mt-4 inline-flex items-center justify-center min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft">
                        Bersihkan Pencarian
                    </a>
                @else
                    <p class="text-base font-semibold text-ink">Belum ada anggota. Tambahkan anggota sebelum mencatat peminjaman.</p>
                    <button type="button" onclick="document.getElementById('btn-open-create-member-popup')?.click()" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control bg-brand-600 text-xs font-semibold text-white hover:bg-brand-700 cursor-pointer shadow-xs">
                        Tambah Anggota
                    </button>
                @endif
            </div>
        @else
            <ul class="lst__ul" aria-label="Daftar Anggota Perpustakaan">
                @foreach ($members as $member)
                    @php
                        $avVariants = ['', 'lst__av--b', 'lst__av--c', 'lst__av--d'];
                        $avClass = $avVariants[$loop->index % 4];
                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $member->name) ?: 'AG', 0, 2));
                        $activeLoans = $member->active_borrowings_count ?? $member->activeBorrowings()->count();
                    @endphp
                    <li class="lst__row">
                        <!-- Hidden actions revealed on hover & keyboard :focus-within -->
                        <div class="lst__acts">
                            <a href="{{ route('members.show', $member) }}"
                               aria-label="Lihat detail {{ $member->name }}"
                               title="Lihat Detail">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button type="button"
                                    class="btn-edit-member"
                                    aria-label="Edit anggota {{ $member->name }}"
                                    title="Edit Anggota"
                                    data-member-id="{{ $member->id }}"
                                    data-member-name="{{ $member->name }}"
                                    data-member-email="{{ $member->email }}"
                                    data-member-phone="{{ $member->phone }}"
                                    data-member-address="{{ $member->address }}"
                                    data-action-url="{{ route('members.update', $member) }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </div>

                        <!-- Main Row Link -->
                        <a class="lst__main" href="{{ route('members.show', $member) }}">
                            <span class="lst__av {{ $avClass }}">
                                {{ $initials }}
                                <i class="lst__on {{ $activeLoans > 0 ? '' : 'lst__on--away' }}" title="{{ $activeLoans > 0 ? 'Sedang meminjam' : 'Tidak ada pinjaman aktif' }}"></i>
                            </span>

                            <span class="lst__txt">
                                <b>{{ $member->name }}</b>
                                <small class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-medium">{{ $member->member_code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $member->email }}</span>
                                    @if ($member->phone)
                                        <span>&bull;</span>
                                        <span>{{ $member->phone }}</span>
                                    @endif
                                </small>
                            </span>

                            @if ($activeLoans > 0)
                                <span class="lst__count" title="{{ $activeLoans }} Pinjaman Aktif">{{ $activeLoans }}</span>
                            @else
                                <span class="text-xs text-text-muted font-medium px-2 py-0.5 rounded-md bg-surface-soft">0 Pinjam</span>
                            @endif

                            <svg viewBox="0 0 24 24" aria-hidden="true" class="w-4 h-4 text-text-muted shrink-0"><path d="m9 6 6 6-6 6"/></svg>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($members->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $members->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- POPUP CREATE ANGGOTA (Latar Digelapin)                  -->
    <!-- ======================================================== -->
    <div id="create-member-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="create-member-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <!-- Darkened Backdrop -->
            <div id="create-member-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div>
                        <h3 id="create-member-title" class="text-lg font-bold text-ink">Tambah Anggota Perpustakaan</h3>
                        <p class="text-xs text-text-muted mt-0.5">Daftarkan anggota baru yang berhak meminjam koleksi.</p>
                    </div>
                    <button type="button" id="btn-close-create-member" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>

                <form id="create-member-form" method="POST" action="{{ route('members.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="create-member-name" class="block text-xs font-semibold text-ink mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="create-member-name"
                            name="name"
                            required
                            placeholder="Contoh: Rian Pratama"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                        <p id="err-member-name" class="hidden text-xs text-rose-500 font-medium mt-1">Nama lengkap wajib diisi.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="create-member-email" class="block text-xs font-semibold text-ink mb-1">
                                Email <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="create-member-email"
                                name="email"
                                required
                                placeholder="rian@example.test"
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                        </div>
                        <div>
                            <label for="create-member-phone" class="block text-xs font-semibold text-ink mb-1">
                                Nomor Telepon <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="create-member-phone"
                                name="phone"
                                required
                                placeholder="081234567890"
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="create-member-address" class="block text-xs font-semibold text-ink mb-1">
                            Alamat Domisili <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="create-member-address"
                            name="address"
                            rows="2"
                            required
                            placeholder="Alamat tempat tinggal lengkap..."
                            class="w-full px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            id="btn-cancel-create-member"
                            class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="btn-submit-create-member"
                            class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                        >
                            Simpan Anggota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- POPUP EDIT ANGGOTA (Latar Digelapin)                    -->
    <!-- ======================================================== -->
    <div id="edit-member-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="edit-member-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <!-- Darkened Backdrop -->
            <div id="edit-member-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div>
                        <h3 id="edit-member-title" class="text-lg font-bold text-ink">Edit Anggota Perpustakaan</h3>
                        <p class="text-xs text-text-muted mt-0.5">Perbarui biodata dan data kontak anggota.</p>
                    </div>
                    <button type="button" id="btn-close-edit-member" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>

                <form id="edit-member-form" method="POST" action="" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit-member-name" class="block text-xs font-semibold text-ink mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit-member-name"
                            name="name"
                            required
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="edit-member-email" class="block text-xs font-semibold text-ink mb-1">
                                Email <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="edit-member-email"
                                name="email"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                        </div>
                        <div>
                            <label for="edit-member-phone" class="block text-xs font-semibold text-ink mb-1">
                                Nomor Telepon <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit-member-phone"
                                name="phone"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="edit-member-address" class="block text-xs font-semibold text-ink mb-1">
                            Alamat Domisili <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="edit-member-address"
                            name="address"
                            rows="2"
                            required
                            class="w-full px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        ></textarea>
                    </div>

                    <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            id="btn-cancel-edit-member"
                            class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="btn-submit-edit-member"
                            class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                        >
                            Perbarui Anggota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Create Member Modal
            const openMemberBtn = document.getElementById('btn-open-create-member-popup');
            const memberModal = document.getElementById('create-member-popup');
            const closeMemberBtn = document.getElementById('btn-close-create-member');
            const cancelMemberBtn = document.getElementById('btn-cancel-create-member');
            const memberBackdrop = document.getElementById('create-member-backdrop');
            const memberForm = document.getElementById('create-member-form');
            const memberSubmit = document.getElementById('btn-submit-create-member');

            function openModal() {
                if (memberModal) memberModal.classList.remove('hidden');
            }

            function closeModal() {
                if (memberModal) memberModal.classList.add('hidden');
            }

            if (openMemberBtn) openMemberBtn.addEventListener('click', openModal);
            if (closeMemberBtn) closeMemberBtn.addEventListener('click', closeModal);
            if (cancelMemberBtn) cancelMemberBtn.addEventListener('click', closeModal);
            if (memberBackdrop) memberBackdrop.addEventListener('click', closeModal);

            // Edit Member Modal
            const editMemberModal = document.getElementById('edit-member-popup');
            const closeEditMemberBtn = document.getElementById('btn-close-edit-member');
            const cancelEditMemberBtn = document.getElementById('btn-cancel-edit-member');
            const editMemberBackdrop = document.getElementById('edit-member-backdrop');
            const editMemberForm = document.getElementById('edit-member-form');
            const editMemberName = document.getElementById('edit-member-name');
            const editMemberEmail = document.getElementById('edit-member-email');
            const editMemberPhone = document.getElementById('edit-member-phone');
            const editMemberAddress = document.getElementById('edit-member-address');
            const editMemberSubmit = document.getElementById('btn-submit-edit-member');

            function openEditModal(url, name, email, phone, address) {
                if (!editMemberModal) return;
                editMemberForm.action = url;
                editMemberName.value = name || '';
                editMemberEmail.value = email || '';
                editMemberPhone.value = phone || '';
                editMemberAddress.value = address || '';
                editMemberModal.classList.remove('hidden');
            }

            function closeEditModal() {
                if (editMemberModal) editMemberModal.classList.add('hidden');
            }

            document.querySelectorAll('.btn-edit-member').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = btn.getAttribute('data-action-url');
                    const name = btn.getAttribute('data-member-name');
                    const email = btn.getAttribute('data-member-email');
                    const phone = btn.getAttribute('data-member-phone');
                    const address = btn.getAttribute('data-member-address');
                    openEditModal(url, name, email, phone, address);
                });
            });

            if (closeEditMemberBtn) closeEditMemberBtn.addEventListener('click', closeEditModal);
            if (cancelEditMemberBtn) cancelEditMemberBtn.addEventListener('click', closeEditModal);
            if (editMemberBackdrop) editMemberBackdrop.addEventListener('click', closeEditModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (memberModal && !memberModal.classList.contains('hidden')) closeModal();
                    if (editMemberModal && !editMemberModal.classList.contains('hidden')) closeEditModal();
                }
            });

            if (memberForm && memberSubmit) {
                memberForm.addEventListener('submit', function (e) {
                    const name = document.getElementById('create-member-name');
                    const err = document.getElementById('err-member-name');

                    if (name && !name.value.trim()) {
                        e.preventDefault();
                        if (err) err.classList.remove('hidden');
                        name.classList.add('border-rose-500');
                        name.focus();
                        return;
                    }

                    memberSubmit.disabled = true;
                    memberSubmit.textContent = 'Menyimpan Anggota...';
                    memberSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }

            if (editMemberForm && editMemberSubmit) {
                editMemberForm.addEventListener('submit', function () {
                    editMemberSubmit.disabled = true;
                    editMemberSubmit.textContent = 'Memperbarui Anggota...';
                    editMemberSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

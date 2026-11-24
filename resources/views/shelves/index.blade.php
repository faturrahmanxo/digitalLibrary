<x-layouts.app title="Rak Buku — Manajemen Koleksi Fisik">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Rak Buku</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <div role="status" aria-live="polite" class="mb-6 rounded-card border border-success-fg/20 bg-success-bg p-4 text-success-fg flex items-start justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-danger-fg/20 bg-danger-bg p-4 text-danger-fg flex items-start justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Rak Buku</h1>
            <p class="mt-1 text-sm text-text-muted">
                Kelola penempatan dan lokasi rak fisik untuk koleksi buku di perpustakaan.
            </p>
        </div>

        @can('create', \App\Models\Shelf::class)
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    id="btn-open-create-shelf-modal"
                    class="min-h-11 inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Rak Buku</span>
                </button>
            </div>
        @endcan
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('shelves.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-xs">
        <div class="flex-1 relative">
            <label for="q" class="sr-only">Cari kode, nama, atau lokasi rak</label>
            <input
                type="search"
                id="q"
                name="q"
                value="{{ $search }}"
                placeholder="Cari kode rak (mis. R-01), nama rak, atau lokasi..."
                class="w-full min-h-10 pl-9 pr-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
            >
            <svg class="w-4 h-4 text-text-muted absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors shadow-xs cursor-pointer">
                Cari
            </button>
            @if ($search !== '')
                <a href="{{ route('shelves.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-xs font-semibold text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Shelves Table -->
    <div class="bg-surface rounded-card border border-line overflow-hidden shadow-xs mb-6">
        @if ($shelves->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-surface-soft border border-line text-text-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Belum ada data rak buku</h3>
                <p class="mt-1 text-xs text-text-muted">Tambahkan rak buku fisik untuk mengorganisasi lokasi penyimpanan koleksi.</p>
                @can('create', \App\Models\Shelf::class)
                    <button type="button" onclick="document.getElementById('btn-open-create-shelf-modal')?.click()" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control bg-brand-600 text-xs font-semibold text-white hover:bg-brand-700 cursor-pointer shadow-xs">
                        Tambah Rak Pertama
                    </button>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4 font-mono w-28">Kode Rak</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[180px]">Nama Rak</th>
                            <th scope="col" class="py-3.5 px-4">Lokasi Fisik</th>
                            <th scope="col" class="py-3.5 px-4">Keterangan</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Jumlah Buku</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($shelves as $shelf)
                            <tr class="hover:bg-surface-soft/50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-xs text-brand-700 dark:text-brand-400">
                                    {{ $shelf->code }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-ink">
                                    {{ $shelf->name }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-text-muted">
                                    {{ $shelf->location ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-text-muted line-clamp-1">
                                    {{ $shelf->notes ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-surface-soft text-ink border border-line tabular-nums">
                                        {{ $shelf->books_count ?? 0 }} judul
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    @can('update', $shelf)
                                        <button
                                            type="button"
                                            class="btn-edit-shelf inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                                            data-shelf-id="{{ $shelf->id }}"
                                            data-shelf-code="{{ $shelf->code }}"
                                            data-shelf-name="{{ $shelf->name }}"
                                            data-shelf-location="{{ $shelf->location }}"
                                            data-shelf-notes="{{ $shelf->notes }}"
                                            data-action-url="{{ route('shelves.update', $shelf) }}"
                                        >
                                            Edit
                                        </button>
                                    @endcan

                                    @can('delete', $shelf)
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-danger-fg hover:bg-danger-bg hover:border-danger-fg/30 transition-colors cursor-pointer"
                                            data-archive-action="{{ route('shelves.destroy', $shelf) }}"
                                            data-archive-title="Hapus Rak Buku?"
                                            data-archive-desc="Rak '{{ $shelf->name }}' ({{ $shelf->code }}) akan dihapus. Pastikan tidak ada buku yang masih terkait dengan rak ini."
                                            data-archive-label="Ya, Hapus Rak"
                                        >
                                            Hapus
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($shelves->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $shelves->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit Rak Buku) -->
    <div id="shelf-form-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="shelf-modal-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div id="shelf-modal-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <h3 id="shelf-modal-title" class="text-base font-bold text-ink">Tambah Rak Buku</h3>
                    <button type="button" id="btn-close-shelf-modal" class="text-text-muted hover:text-ink text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form id="shelf-modal-form" method="POST" action="{{ route('shelves.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div id="shelf-method-field"></div>

                    <div>
                        <label for="shelf_code" class="block text-xs font-semibold text-ink mb-1">
                            Kode Rak <span class="text-danger-fg">*</span>
                        </label>
                        <input
                            type="text"
                            id="shelf_code"
                            name="code"
                            required
                            placeholder="Contoh: RAK-A1"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm font-mono uppercase bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="shelf_name" class="block text-xs font-semibold text-ink mb-1">
                            Nama Rak <span class="text-danger-fg">*</span>
                        </label>
                        <input
                            type="text"
                            id="shelf_name"
                            name="name"
                            required
                            placeholder="Contoh: Rak Sejarah & Budaya"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="shelf_location" class="block text-xs font-semibold text-ink mb-1">
                            Lokasi Rak
                        </label>
                        <input
                            type="text"
                            id="shelf_location"
                            name="location"
                            placeholder="Contoh: Lantai 2, Sayap Barat"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="shelf_notes" class="block text-xs font-semibold text-ink mb-1">
                            Keterangan (Opsional)
                        </label>
                        <textarea
                            id="shelf_notes"
                            name="notes"
                            rows="2"
                            placeholder="Informasi kapasitas atau keterangan rak..."
                            class="w-full px-3 py-2 rounded-control border border-line text-xs bg-surface text-ink focus:outline-2 focus:outline-focus"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                        <button type="button" id="btn-cancel-shelf-modal" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-shelf-modal" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer">
                            Simpan Rak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('shelf-form-modal');
            const form = document.getElementById('shelf-modal-form');
            const title = document.getElementById('shelf-modal-title');
            const methodField = document.getElementById('shelf-method-field');
            const openCreateBtn = document.getElementById('btn-open-create-shelf-modal');
            const closeBtn = document.getElementById('btn-close-shelf-modal');
            const cancelBtn = document.getElementById('btn-cancel-shelf-modal');
            const backdrop = document.getElementById('shelf-modal-backdrop');

            const codeInput = document.getElementById('shelf_code');
            const nameInput = document.getElementById('shelf_name');
            const locInput = document.getElementById('shelf_location');
            const notesInput = document.getElementById('shelf_notes');

            const openCreate = () => {
                form.action = "{{ route('shelves.store') }}";
                title.textContent = 'Tambah Rak Buku Baru';
                methodField.innerHTML = '';
                codeInput.value = '';
                nameInput.value = '';
                locInput.value = '';
                notesInput.value = '';
                modal.classList.remove('hidden');
                codeInput.focus();
            };

            const closeModal = () => { if (modal) modal.classList.add('hidden'); };

            if (openCreateBtn) openCreateBtn.addEventListener('click', openCreate);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);

            document.querySelectorAll('.btn-edit-shelf').forEach(btn => {
                btn.addEventListener('click', function () {
                    form.action = this.dataset.actionUrl;
                    title.textContent = 'Edit Data Rak Buku';
                    methodField.innerHTML = '@method("PUT")';
                    codeInput.value = this.dataset.shelfCode || '';
                    nameInput.value = this.dataset.shelfName || '';
                    locInput.value = this.dataset.shelfLocation || '';
                    notesInput.value = this.dataset.shelfNotes || '';
                    modal.classList.remove('hidden');
                    nameInput.focus();
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>

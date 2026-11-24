<x-layouts.app title="Penerbit — Manajemen Buku">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Penerbit</span>
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
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Penerbit</h1>
            <p class="mt-1 text-sm text-text-muted">
                Daftar penerbit buku yang terdaftar di perpustakaan sebagai referensi bibliografi katalog.
            </p>
        </div>

        @can('create', \App\Models\Publisher::class)
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    id="btn-open-create-publisher-modal"
                    class="min-h-11 inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Penerbit</span>
                </button>
            </div>
        @endcan
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('publishers.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-xs">
        <div class="flex-1 relative">
            <label for="q" class="sr-only">Cari penerbit</label>
            <input
                type="search"
                id="q"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama penerbit, kota, atau website..."
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
                <a href="{{ route('publishers.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-xs font-semibold text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Publishers Table -->
    <div class="bg-surface rounded-card border border-line overflow-hidden shadow-xs mb-6">
        @if ($publishers->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-surface-soft border border-line text-text-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Belum ada data penerbit</h3>
                <p class="mt-1 text-xs text-text-muted">Penerbit dapat ditambahkan untuk melengkapi metadata katalog buku.</p>
                @can('create', \App\Models\Publisher::class)
                    <button type="button" onclick="document.getElementById('btn-open-create-publisher-modal')?.click()" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control bg-brand-600 text-xs font-semibold text-white hover:bg-brand-700 cursor-pointer shadow-xs">
                        Tambah Penerbit Pertama
                    </button>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4 min-w-[200px]">Nama Penerbit</th>
                            <th scope="col" class="py-3.5 px-4">Kota / Negara</th>
                            <th scope="col" class="py-3.5 px-4">Website</th>
                            <th scope="col" class="py-3.5 px-4">Keterangan</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Buku Terkait</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($publishers as $publisher)
                            <tr class="hover:bg-surface-soft/50 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-ink">
                                    {{ $publisher->name }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-text-muted">
                                    {{ $publisher->city ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if ($publisher->website)
                                        <a href="{{ $publisher->website }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:text-brand-700 underline truncate block max-w-[180px]">
                                            {{ preg_replace('#^https?://#', '', $publisher->website) }}
                                        </a>
                                    @else
                                        <span class="text-text-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-text-muted line-clamp-1">
                                    {{ $publisher->notes ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-surface-soft text-ink border border-line tabular-nums">
                                        {{ $publisher->books_count ?? 0 }} judul
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    @can('update', $publisher)
                                        <button
                                            type="button"
                                            class="btn-edit-publisher inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                                            data-publisher-id="{{ $publisher->id }}"
                                            data-publisher-name="{{ $publisher->name }}"
                                            data-publisher-city="{{ $publisher->city }}"
                                            data-publisher-website="{{ $publisher->website }}"
                                            data-publisher-notes="{{ $publisher->notes }}"
                                            data-action-url="{{ route('publishers.update', $publisher) }}"
                                        >
                                            Edit
                                        </button>
                                    @endcan

                                    @can('delete', $publisher)
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-danger-fg hover:bg-danger-bg hover:border-danger-fg/30 transition-colors cursor-pointer"
                                            data-archive-action="{{ route('publishers.destroy', $publisher) }}"
                                            data-archive-title="Hapus Penerbit?"
                                            data-archive-desc="Penerbit '{{ $publisher->name }}' akan dihapus. Penghapusan tidak dapat dilakukan jika masih digunakan pada katalog buku."
                                            data-archive-label="Ya, Hapus Penerbit"
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

            @if ($publishers->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $publishers->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit Penerbit) -->
    <div id="publisher-form-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="publisher-modal-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div id="publisher-modal-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <h3 id="publisher-modal-title" class="text-base font-bold text-ink">Tambah Penerbit</h3>
                    <button type="button" id="btn-close-publisher-modal" class="text-text-muted hover:text-ink text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form id="publisher-modal-form" method="POST" action="{{ route('publishers.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div id="publisher-method-field"></div>

                    <div>
                        <label for="publisher_name" class="block text-xs font-semibold text-ink mb-1">
                            Nama Penerbit <span class="text-danger-fg">*</span>
                        </label>
                        <input
                            type="text"
                            id="publisher_name"
                            name="name"
                            required
                            placeholder="Contoh: Balai Pustaka"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="publisher_city" class="block text-xs font-semibold text-ink mb-1">
                            Kota / Negara
                        </label>
                        <input
                            type="text"
                            id="publisher_city"
                            name="city"
                            placeholder="Contoh: Jakarta, Indonesia"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="publisher_website" class="block text-xs font-semibold text-ink mb-1">
                            Website Resmi (Opsional)
                        </label>
                        <input
                            type="url"
                            id="publisher_website"
                            name="website"
                            placeholder="https://balaipustaka.co.id"
                            class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                        >
                    </div>

                    <div>
                        <label for="publisher_notes" class="block text-xs font-semibold text-ink mb-1">
                            Keterangan (Opsional)
                        </label>
                        <textarea
                            id="publisher_notes"
                            name="notes"
                            rows="2"
                            placeholder="Catatan profil atau kontak penerbit..."
                            class="w-full px-3 py-2 rounded-control border border-line text-xs bg-surface text-ink focus:outline-2 focus:outline-focus"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                        <button type="button" id="btn-cancel-publisher-modal" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-publisher-modal" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer">
                            Simpan Penerbit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('publisher-form-modal');
            const form = document.getElementById('publisher-modal-form');
            const title = document.getElementById('publisher-modal-title');
            const methodField = document.getElementById('publisher-method-field');
            const openCreateBtn = document.getElementById('btn-open-create-publisher-modal');
            const closeBtn = document.getElementById('btn-close-publisher-modal');
            const cancelBtn = document.getElementById('btn-cancel-publisher-modal');
            const backdrop = document.getElementById('publisher-modal-backdrop');

            const nameInput = document.getElementById('publisher_name');
            const cityInput = document.getElementById('publisher_city');
            const webInput = document.getElementById('publisher_website');
            const notesInput = document.getElementById('publisher_notes');

            const openCreate = () => {
                form.action = "{{ route('publishers.store') }}";
                title.textContent = 'Tambah Penerbit Baru';
                methodField.innerHTML = '';
                nameInput.value = '';
                cityInput.value = '';
                webInput.value = '';
                notesInput.value = '';
                modal.classList.remove('hidden');
                nameInput.focus();
            };

            const closeModal = () => { if (modal) modal.classList.add('hidden'); };

            if (openCreateBtn) openCreateBtn.addEventListener('click', openCreate);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);

            document.querySelectorAll('.btn-edit-publisher').forEach(btn => {
                btn.addEventListener('click', function () {
                    form.action = this.dataset.actionUrl;
                    title.textContent = 'Edit Data Penerbit';
                    methodField.innerHTML = '@method("PUT")';
                    nameInput.value = this.dataset.publisherName || '';
                    cityInput.value = this.dataset.publisherCity || '';
                    webInput.value = this.dataset.publisherWebsite || '';
                    notesInput.value = this.dataset.publisherNotes || '';
                    modal.classList.remove('hidden');
                    nameInput.focus();
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>

<x-layouts.app title="Kategori Buku">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Kategori</span>
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

    <!-- SCR-005: Category List -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Kategori Buku</h1>
            <p class="mt-1 text-sm text-text-muted">
                @can('create', \App\Models\Category::class)
                    Kelola pengelompokan kategori untuk katalog buku perpustakaan.
                @else
                    Daftar kategori referensi untuk pengelompokan koleksi buku.
                @endcan
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @can('create', \App\Models\Category::class)
                <button
                    type="button"
                    id="btn-open-create-category-popup"
                    class="min-h-11 inline-flex items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors cursor-pointer shadow-xs"
                >
                    Tambah Kategori
                </button>
            @else
                <a href="{{ route('books.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-control border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-ink hover:bg-surface-soft transition-colors">
                    Buka Daftar Buku
                </a>
            @endcan
        </div>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('categories.index') }}" class="bg-surface rounded-card border border-line p-4 mb-4 flex flex-col sm:flex-row gap-3 shadow-xs">
        @if (!empty($genre))
            <input type="hidden" name="genre" value="{{ $genre }}">
        @endif
        <div class="flex-1">
            <label for="q" class="sr-only">Cari nama kategori</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Cari nama atau deskripsi kategori..." class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors cursor-pointer">
                Cari
            </button>
            @if ($search !== '' || !empty($genre))
                <a href="{{ route('categories.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-sm font-medium text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if (!empty($genre))
        <div class="mb-5 flex items-center gap-2 text-xs text-text-muted">
            <span>Filter Kategori:</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold bg-brand-50 text-brand-700 dark:bg-brand-600/25 dark:text-[#F9E6A8] border border-brand-600/20">
                {{ strtoupper($genre) }}
                <a href="{{ route('categories.index', array_filter(['q' => $search])) }}" class="hover:text-rose-500 font-bold ml-1 text-sm leading-none" title="Hapus filter">&times;</a>
            </span>
        </div>
    @endif

    <!-- Categories Table -->
    <div class="bg-surface rounded-card border border-line overflow-hidden shadow-xs">
        @if ($categories->isEmpty())
            <div class="p-10 text-center">
                <p class="text-base font-semibold text-ink">Belum ada kategori buku.</p>
                <p class="mt-1 text-sm text-text-muted">Kategori diperlukan untuk mengelompokkan judul buku di perpustakaan.</p>
                @can('create', \App\Models\Category::class)
                    <button type="button" onclick="document.getElementById('btn-open-create-category-popup')?.click()" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control bg-brand-600 text-xs font-semibold text-white hover:bg-brand-700 cursor-pointer shadow-xs">
                        Tambah Kategori Pertama
                    </button>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4">Nama Kategori</th>
                            <th scope="col" class="py-3.5 px-4">Deskripsi</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Jumlah Judul</th>
                            @can('create', \App\Models\Category::class)
                                <th scope="col" class="py-3.5 px-4 text-right">Tindakan</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-surface-soft/50">
                                <td class="py-3.5 px-4 font-semibold text-ink">{{ $category->name }}</td>
                                <td class="py-3.5 px-4 text-text-muted text-xs max-w-md truncate">{{ $category->description ?: '—' }}</td>
                                <td class="py-3.5 px-4 text-right tabular-nums text-text-muted">{{ $category->books_count ?? $category->books()->count() }} judul</td>
                                @can('create', \App\Models\Category::class)
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <button
                                            type="button"
                                            class="btn-edit-category text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer"
                                            data-category-id="{{ $category->id }}"
                                            data-category-name="{{ $category->name }}"
                                            data-category-desc="{{ $category->description }}"
                                            data-action-url="{{ route('categories.update', $category) }}"
                                        >
                                            Edit
                                        </button>
                                        <button type="button"
                                                data-archive-action="{{ route('categories.destroy', $category) }}"
                                                data-archive-title="Arsipkan Kategori?"
                                                data-archive-desc="Kategori '{{ $category->name }}' akan diarsipkan. Kategori tidak dapat diarsipkan bila masih memiliki buku aktif."
                                                data-archive-label="Arsipkan Kategori"
                                                class="text-xs font-medium text-danger-fg hover:underline cursor-pointer">
                                            Arsipkan
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $categories->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- POPUP CREATE KATEGORI (Latar Digelapin)                  -->
    <!-- ======================================================== -->
    @can('create', \App\Models\Category::class)
        <div id="create-category-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="create-cat-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <!-- Darkened Backdrop -->
                <div id="create-cat-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div>
                            <h3 id="create-cat-title" class="text-lg font-bold text-ink">Tambah Kategori Buku</h3>
                            <p class="text-xs text-text-muted mt-0.5">Kelompokkan koleksi buku dengan kategori baru.</p>
                        </div>
                        <button type="button" id="btn-close-create-cat" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                    <form id="create-category-form" method="POST" action="{{ route('categories.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="create-cat-name" class="block text-xs font-semibold text-ink mb-1.5">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="create-cat-name"
                                name="name"
                                required
                                placeholder="Contoh: Kecerdasan Buatan"
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                            <p id="err-create-cat-name" class="hidden text-xs text-rose-500 font-medium mt-1">Nama kategori wajib diisi.</p>
                        </div>

                        <div>
                            <label for="create-cat-desc" class="block text-xs font-semibold text-ink mb-1.5">
                                Deskripsi Kategori
                            </label>
                            <textarea
                                id="create-cat-desc"
                                name="description"
                                rows="3"
                                placeholder="Penjelasan singkat mengenai kelompok koleksi ini..."
                                class="w-full px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            ></textarea>
                        </div>

                        <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                id="btn-cancel-create-cat"
                                class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-submit-create-cat"
                                class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- POPUP EDIT KATEGORI (Latar Digelapin)                    -->
        <!-- ======================================================== -->
        <div id="edit-category-popup" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="edit-cat-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <!-- Darkened Backdrop -->
                <div id="edit-cat-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div>
                            <h3 id="edit-cat-title" class="text-lg font-bold text-ink">Edit Kategori Buku</h3>
                            <p class="text-xs text-text-muted mt-0.5">Perbarui nama dan deskripsi kategori.</p>
                        </div>
                        <button type="button" id="btn-close-edit-cat" class="px-3 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>

                    <form id="edit-category-form" method="POST" action="" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="edit-cat-name" class="block text-xs font-semibold text-ink mb-1.5">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="edit-cat-name"
                                name="name"
                                required
                                class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            >
                            <p id="err-edit-cat-name" class="hidden text-xs text-rose-500 font-medium mt-1">Nama kategori wajib diisi.</p>
                        </div>

                        <div>
                            <label for="edit-cat-desc" class="block text-xs font-semibold text-ink mb-1.5">
                                Deskripsi Kategori
                            </label>
                            <textarea
                                id="edit-cat-desc"
                                name="description"
                                rows="3"
                                class="w-full px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                            ></textarea>
                        </div>

                        <div class="pt-4 border-t border-line flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                id="btn-cancel-edit-cat"
                                class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                id="btn-submit-edit-cat"
                                class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Perbarui Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Create Category Modal Controls
            const openCatBtn = document.getElementById('btn-open-create-category-popup');
            const catModal = document.getElementById('create-category-popup');
            const closeCatBtn = document.getElementById('btn-close-create-cat');
            const cancelCatBtn = document.getElementById('btn-cancel-create-cat');
            const catBackdrop = document.getElementById('create-cat-backdrop');
            const catForm = document.getElementById('create-category-form');
            const catSubmit = document.getElementById('btn-submit-create-cat');

            function openModal() {
                if (catModal) catModal.classList.remove('hidden');
            }

            function closeModal() {
                if (catModal) catModal.classList.add('hidden');
            }

            if (openCatBtn) openCatBtn.addEventListener('click', openModal);
            if (closeCatBtn) closeCatBtn.addEventListener('click', closeModal);
            if (cancelCatBtn) cancelCatBtn.addEventListener('click', closeModal);
            if (catBackdrop) catBackdrop.addEventListener('click', closeModal);

            // Edit Category Modal Controls
            const editCatModal = document.getElementById('edit-category-popup');
            const closeEditCatBtn = document.getElementById('btn-close-edit-cat');
            const cancelEditCatBtn = document.getElementById('btn-cancel-edit-cat');
            const editCatBackdrop = document.getElementById('edit-cat-backdrop');
            const editCatForm = document.getElementById('edit-category-form');
            const editCatName = document.getElementById('edit-cat-name');
            const editCatDesc = document.getElementById('edit-cat-desc');
            const editCatSubmit = document.getElementById('btn-submit-edit-cat');

            function openEditModal(url, name, desc) {
                if (!editCatModal) return;
                editCatForm.action = url;
                editCatName.value = name || '';
                editCatDesc.value = desc || '';
                editCatModal.classList.remove('hidden');
            }

            function closeEditModal() {
                if (editCatModal) editCatModal.classList.add('hidden');
            }

            document.querySelectorAll('.btn-edit-category').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = btn.getAttribute('data-action-url');
                    const name = btn.getAttribute('data-category-name');
                    const desc = btn.getAttribute('data-category-desc');
                    openEditModal(url, name, desc);
                });
            });

            if (closeEditCatBtn) closeEditCatBtn.addEventListener('click', closeEditModal);
            if (cancelEditCatBtn) cancelEditCatBtn.addEventListener('click', closeEditModal);
            if (editCatBackdrop) editCatBackdrop.addEventListener('click', closeEditModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (catModal && !catModal.classList.contains('hidden')) closeModal();
                    if (editCatModal && !editCatModal.classList.contains('hidden')) closeEditModal();
                }
            });

            if (catForm && catSubmit) {
                catForm.addEventListener('submit', function (e) {
                    const name = document.getElementById('create-cat-name');
                    const err = document.getElementById('err-create-cat-name');

                    if (name && !name.value.trim()) {
                        e.preventDefault();
                        if (err) err.classList.remove('hidden');
                        name.classList.add('border-rose-500');
                        name.focus();
                        return;
                    }

                    catSubmit.disabled = true;
                    catSubmit.textContent = 'Menyimpan Kategori...';
                    catSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }

            if (editCatForm && editCatSubmit) {
                editCatForm.addEventListener('submit', function (e) {
                    const name = document.getElementById('edit-cat-name');
                    const err = document.getElementById('err-edit-cat-name');

                    if (name && !name.value.trim()) {
                        e.preventDefault();
                        if (err) err.classList.remove('hidden');
                        name.classList.add('border-rose-500');
                        name.focus();
                        return;
                    }

                    editCatSubmit.disabled = true;
                    editCatSubmit.textContent = 'Memperbarui Kategori...';
                    editCatSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

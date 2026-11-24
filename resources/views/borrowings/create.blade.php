<x-layouts.app title="Catat Peminjaman">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('borrowings.index') }}" class="hover:text-ink">Peminjaman</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Catat Peminjaman</span>
    </x-slot:breadcrumbs>

    <!-- SCR-014: Borrowing Create (Critical Screen) -->
    @php
        $oldItems = old('items', [
            ['book_id' => '', 'quantity' => 1],
        ]);
        if (! is_array($oldItems) || count($oldItems) === 0) {
            $oldItems = [['book_id' => '', 'quantity' => 1]];
        }
        $selectedMemberId = old('member_id', request()->query('member_id', ''));
        $currentRequestKey = old('request_key', $requestKey);
    @endphp

    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">Catat Peminjaman</h1>
            <p class="mt-1 text-sm text-text-muted">Pilih anggota aktif serta satu atau beberapa judul buku yang akan dipinjam. Stok tersedia akan diperbarui secara atomik.</p>
        </div>

        <form method="POST" action="{{ route('borrowings.store') }}" id="borrowing-create-form" class="space-y-6" novalidate>
            @csrf
            <input type="hidden" name="request_key" id="request_key" value="{{ $currentRequestKey }}">

            <!-- Panel 1: Anggota Peminjam -->
            <section aria-labelledby="member-panel-heading" class="bg-surface rounded-card border border-line p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-3">
                    <div>
                        <h2 id="member-panel-heading" class="text-base font-semibold text-ink">1. Anggota Peminjam</h2>
                        <p class="text-xs text-text-muted">Hanya anggota berstatus aktif yang dapat melakukan peminjaman.</p>
                    </div>
                    <a href="{{ route('members.create') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                        + Tambah Anggota Baru
                    </a>
                </div>

                @if ($members->isEmpty())
                    <div class="rounded-control border border-warning-fg/30 bg-warning-bg p-4 text-sm text-warning-fg">
                        Belum ada anggota. <a href="{{ route('members.create') }}" class="font-semibold underline">Tambahkan anggota</a> sebelum mencatat peminjaman.
                    </div>
                @else
                    <div>
                        <label for="member_id" class="block text-sm font-semibold text-ink mb-1.5">
                            Pilih Anggota <span class="text-danger-fg" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="member_id"
                            name="member_id"
                            required
                            aria-invalid="{{ $errors->has('member_id') ? 'true' : 'false' }}"
                            @if($errors->has('member_id')) aria-describedby="member-id-error" @endif
                            class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('member_id') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                        >
                            <option value="">-- Pilih Kode & Nama Anggota --</option>
                            @foreach ($members as $memberOption)
                                <option value="{{ $memberOption->id }}" @selected((string) $selectedMemberId === (string) $memberOption->id)>
                                    {{ $memberOption->member_code }} — {{ $memberOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <p id="member-id-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </section>

            <!-- Panel 2: Daftar Buku yang Dipinjam -->
            <section aria-labelledby="items-panel-heading" class="bg-surface rounded-card border border-line p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-line pb-3">
                    <div>
                        <h2 id="items-panel-heading" class="text-base font-semibold text-ink">2. Buku yang Dipinjam</h2>
                        <p class="text-xs text-text-muted">Pilih buku yang tersedia dan tentukan jumlah eksemplar per judul (tidak boleh memilih judul yang sama dua kali).</p>
                    </div>
                    <button
                        type="button"
                        id="add-item-row-btn"
                        data-testid="add-book-row"
                        class="min-h-10 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-control border border-line bg-surface text-xs font-semibold text-brand-600 hover:bg-surface-soft cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Buku</span>
                    </button>
                </div>

                <div id="client-duplicate-alert" role="alert" class="hidden rounded-control border border-danger-fg/25 bg-danger-bg p-3 text-xs font-medium text-danger-fg">
                    Buku ini sudah dipilih. Ubah jumlah pada baris sebelumnya.
                </div>

                @if ($books->isEmpty())
                    <div class="rounded-control border border-warning-fg/30 bg-warning-bg p-4 text-sm text-warning-fg">
                        Belum ada buku. <a href="{{ route('books.create') }}" class="font-semibold underline">Tambahkan buku pertama</a> untuk mulai mengelola koleksi.
                    </div>
                @endif

                <div id="borrowing-items-container" class="space-y-4">
                    @foreach (array_values($oldItems) as $idx => $oldItem)
                        @php
                            $bookError = $errors->first("items.{$idx}.book_id");
                            $qtyError = $errors->first("items.{$idx}.quantity");
                        @endphp
                        <div class="borrowing-item-row rounded-control border {{ ($bookError || $qtyError) ? 'border-danger-fg bg-danger-bg/20' : 'border-line bg-surface-soft/40' }} p-4" data-row-index="{{ $idx }}">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <div class="md:col-span-7">
                                    <label for="items_{{ $idx }}_book_id" class="block text-xs font-semibold text-ink mb-1">
                                        Judul Buku <span class="text-danger-fg" aria-hidden="true">*</span>
                                    </label>
                                    <select
                                        id="items_{{ $idx }}_book_id"
                                        name="items[{{ $idx }}][book_id]"
                                        required
                                        aria-invalid="{{ $bookError ? 'true' : 'false' }}"
                                        class="book-select w-full min-h-11 px-3 py-2 rounded-control border {{ $bookError ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                                    >
                                        <option value="" data-available="0">-- Pilih Judul Buku --</option>
                                        @foreach ($books as $bookOption)
                                            <option
                                                value="{{ $bookOption->id }}"
                                                data-available="{{ $bookOption->stock_available }}"
                                                data-total="{{ $bookOption->stock_total }}"
                                                @disabled($bookOption->stock_available <= 0 && (string) ($oldItem['book_id'] ?? '') !== (string) $bookOption->id)
                                                @selected((string) ($oldItem['book_id'] ?? '') === (string) $bookOption->id)
                                            >
                                                {{ $bookOption->title }} — {{ $bookOption->stock_available > 0 ? "Tersedia {$bookOption->stock_available} dari {$bookOption->stock_total} eksemplar" : 'Habis (0 eksemplar)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="stock-helper mt-1 text-xs text-text-muted">Pilih buku untuk melihat batas stok tersedia.</p>
                                    @if ($bookError)
                                        <p class="mt-1 text-xs font-medium text-danger-fg">{{ $bookError }}</p>
                                    @endif
                                </div>

                                <div class="md:col-span-3">
                                    <label for="items_{{ $idx }}_quantity" class="block text-xs font-semibold text-ink mb-1">
                                        Jumlah (Eksemplar) <span class="text-danger-fg" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        id="items_{{ $idx }}_quantity"
                                        name="items[{{ $idx }}][quantity]"
                                        type="number"
                                        min="1"
                                        step="1"
                                        required
                                        value="{{ $oldItem['quantity'] ?? 1 }}"
                                        aria-invalid="{{ $qtyError ? 'true' : 'false' }}"
                                        class="qty-input w-full min-h-11 px-3 py-2 rounded-control border {{ $qtyError ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus"
                                    >
                                    @if ($qtyError)
                                        <p class="mt-1 text-xs font-medium text-danger-fg">{{ $qtyError }}</p>
                                    @endif
                                </div>

                                <div class="md:col-span-2 flex md:justify-end md:pt-6">
                                    <button
                                        type="button"
                                        aria-label="Hapus Buku ke-{{ $idx + 1 }}"
                                        class="remove-item-btn min-h-11 w-full md:w-auto px-3 py-2 rounded-control border border-line bg-surface text-xs font-semibold text-danger-fg hover:bg-danger-bg disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                    >
                                        Hapus Baris
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Panel 3: Ringkasan & Metadata Server -->
            <section aria-labelledby="summary-panel-heading" class="bg-surface rounded-card border border-line p-6">
                <h2 id="summary-panel-heading" class="text-base font-semibold text-ink border-b border-line pb-3 mb-4">3. Ringkasan Peminjaman</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="p-3.5 rounded-control bg-surface-soft">
                        <p class="text-xs font-semibold text-text-muted">Dicatat Oleh (Sesi Login)</p>
                        <p class="mt-1 font-semibold text-ink">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-text-muted">Otomatis dari akun petugas aktif</p>
                    </div>

                    <div class="p-3.5 rounded-control bg-surface-soft">
                        <p class="text-xs font-semibold text-text-muted">Perkiraan Tanggal Pinjam</p>
                        <p class="mt-1 font-semibold text-ink tabular-nums">{{ $previewBorrowDate->format('d M Y') }}</p>
                        <p class="text-xs text-text-muted">Ditetapkan server saat disimpan</p>
                    </div>

                    <div class="p-3.5 rounded-control bg-surface-soft">
                        <label for="create-duration-select" class="block text-xs font-semibold text-text-muted mb-1">
                            Pilihan Waktu Pinjam
                        </label>
                        <select
                            id="create-duration-select"
                            name="duration_days"
                            class="w-full min-h-9 px-2.5 py-1.5 rounded-control border border-line text-xs bg-surface text-ink font-semibold focus:outline-2 focus:outline-focus cursor-pointer"
                        >
                            <option value="3">3 Hari (Peminjaman Singkat)</option>
                            <option value="7" selected>7 Hari (1 Minggu — Standar)</option>
                            <option value="14">14 Hari (2 Minggu)</option>
                            <option value="21">21 Hari (3 Minggu)</option>
                            <option value="30">30 Hari (1 Bulan)</option>
                        </select>
                    </div>

                    <div class="p-3.5 rounded-control bg-surface-soft">
                        <p class="text-xs font-semibold text-text-muted">Perkiraan Jatuh Tempo</p>
                        <p id="create-due-preview" class="mt-1 font-semibold text-brand-700 tabular-nums">{{ $previewDueDate->format('d M Y') }}</p>
                        <p id="create-due-note" class="text-xs text-text-muted">7 hari kalender setelah pinjam</p>
                    </div>

                    <div class="p-3.5 rounded-control bg-brand-50 border border-brand-600/20">
                        <p class="text-xs font-semibold text-brand-700">Ringkasan Item</p>
                        <p class="mt-1 font-bold text-ink tabular-nums">
                            <span id="summary-title-count">1</span> judul /
                            <span id="summary-copy-count">1</span> eksemplar
                        </p>
                        <p class="text-xs text-text-muted">Status awal: Dipinjam</p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <a href="{{ route('borrowings.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                        Batal
                    </a>
                    <button
                        type="submit"
                        id="borrow-submit-btn"
                        data-testid="borrow-submit"
                        class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer"
                    >
                        Simpan Peminjaman
                    </button>
                </div>
            </section>
        </form>
    </div>

    <template id="book-options-template">
        <option value="" data-available="0">-- Pilih Judul Buku --</option>
        @foreach ($books as $bookOption)
            <option
                value="{{ $bookOption->id }}"
                data-available="{{ $bookOption->stock_available }}"
                data-total="{{ $bookOption->stock_total }}"
                @disabled($bookOption->stock_available <= 0)
            >
                {{ $bookOption->title }} — {{ $bookOption->stock_available > 0 ? "Tersedia {$bookOption->stock_available} dari {$bookOption->stock_total} eksemplar" : 'Habis (0 eksemplar)' }}
            </option>
        @endforeach
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('borrowing-items-container');
            const addBtn = document.getElementById('add-item-row-btn');
            const optionsTemplate = document.getElementById('book-options-template');
            const titleCountEl = document.getElementById('summary-title-count');
            const copyCountEl = document.getElementById('summary-copy-count');
            const duplicateAlert = document.getElementById('client-duplicate-alert');
            const form = document.getElementById('borrowing-create-form');
            const submitBtn = document.getElementById('borrow-submit-btn');

            function reindexAndRefresh() {
                const rows = container.querySelectorAll('.borrowing-item-row');
                const selectedIds = new Set();
                let hasDuplicate = false;
                let validTitles = 0;
                let totalCopies = 0;

                rows.forEach(function (row, idx) {
                    row.setAttribute('data-row-index', idx);
                    const select = row.querySelector('.book-select');
                    const qtyInput = row.querySelector('.qty-input');
                    const removeBtn = row.querySelector('.remove-item-btn');
                    const helper = row.querySelector('.stock-helper');
                    const labels = row.querySelectorAll('label');

                    if (labels[0]) labels[0].setAttribute('for', `items_${idx}_book_id`);
                    if (labels[1]) labels[1].setAttribute('for', `items_${idx}_quantity`);

                    select.id = `items_${idx}_book_id`;
                    select.name = `items[${idx}][book_id]`;

                    qtyInput.id = `items_${idx}_quantity`;
                    qtyInput.name = `items[${idx}][quantity]`;

                    removeBtn.setAttribute('aria-label', `Hapus Buku ke-${idx + 1}`);
                    removeBtn.disabled = rows.length <= 1;

                    const selectedOption = select.options[select.selectedIndex];
                    const bookId = select.value;
                    const available = selectedOption ? parseInt(selectedOption.getAttribute('data-available') || '0', 10) : 0;

                    if (bookId) {
                        validTitles++;
                        if (selectedIds.has(bookId)) {
                            hasDuplicate = true;
                        }
                        selectedIds.add(bookId);
                        if (helper) {
                            helper.textContent = `Stok tersedia saat ini: ${available} eksemplar.`;
                        }
                    } else if (helper) {
                        helper.textContent = 'Pilih buku untuk melihat batas stok tersedia.';
                    }

                    const qty = parseInt(qtyInput.value || '0', 10);
                    if (!isNaN(qty) && qty > 0) {
                        totalCopies += qty;
                    }
                });

                if (titleCountEl) titleCountEl.textContent = validTitles > 0 ? validTitles : rows.length;
                if (copyCountEl) copyCountEl.textContent = totalCopies;

                if (duplicateAlert) {
                    if (hasDuplicate) {
                        duplicateAlert.classList.remove('hidden');
                    } else {
                        duplicateAlert.classList.add('hidden');
                    }
                }
            }

            if (addBtn && container && optionsTemplate) {
                addBtn.addEventListener('click', function () {
                    const idx = container.querySelectorAll('.borrowing-item-row').length;
                    const row = document.createElement('div');
                    row.className = 'borrowing-item-row rounded-control border border-line bg-surface-soft/40 p-4';
                    row.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                            <div class="md:col-span-7">
                                <label for="items_${idx}_book_id" class="block text-xs font-semibold text-ink mb-1">
                                    Judul Buku <span class="text-danger-fg" aria-hidden="true">*</span>
                                </label>
                                <select id="items_${idx}_book_id" name="items[${idx}][book_id]" required class="book-select w-full min-h-11 px-3 py-2 rounded-control border border-line bg-surface text-ink text-sm focus:outline-2 focus:outline-focus">
                                    ${optionsTemplate.innerHTML}
                                </select>
                                <p class="stock-helper mt-1 text-xs text-text-muted">Pilih buku untuk melihat batas stok tersedia.</p>
                            </div>
                            <div class="md:col-span-3">
                                <label for="items_${idx}_quantity" class="block text-xs font-semibold text-ink mb-1">
                                    Jumlah (Eksemplar) <span class="text-danger-fg" aria-hidden="true">*</span>
                                </label>
                                <input id="items_${idx}_quantity" name="items[${idx}][quantity]" type="number" min="1" step="1" required value="1" class="qty-input w-full min-h-11 px-3 py-2 rounded-control border border-line bg-surface text-ink text-sm tabular-nums focus:outline-2 focus:outline-focus">
                            </div>
                            <div class="md:col-span-2 flex md:justify-end md:pt-6">
                                <button type="button" aria-label="Hapus Buku ke-${idx + 1}" class="remove-item-btn min-h-11 w-full md:w-auto px-3 py-2 rounded-control border border-line bg-surface text-xs font-semibold text-danger-fg hover:bg-danger-bg disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                    Hapus Baris
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(row);
                    reindexAndRefresh();
                    const newSelect = row.querySelector('.book-select');
                    if (newSelect) newSelect.focus();
                });
            }

            if (container) {
                container.addEventListener('change', function (e) {
                    if (e.target.classList.contains('book-select') || e.target.classList.contains('qty-input')) {
                        reindexAndRefresh();
                    }
                });

                container.addEventListener('input', function (e) {
                    if (e.target.classList.contains('qty-input')) {
                        reindexAndRefresh();
                    }
                });

                container.addEventListener('click', function (e) {
                    const removeBtn = e.target.closest('.remove-item-btn');
                    if (removeBtn) {
                        const rows = container.querySelectorAll('.borrowing-item-row');
                        if (rows.length > 1) {
                            const row = removeBtn.closest('.borrowing-item-row');
                            if (row) row.remove();
                            reindexAndRefresh();
                        }
                    }
                });
            }

            function formatIndoDate(daysToAdd) {
                const d = new Date();
                d.setDate(d.getDate() + parseInt(daysToAdd, 10));
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
            }

            const createDuration = document.getElementById('create-duration-select');
            const createDuePreview = document.getElementById('create-due-preview');
            const createDueNote = document.getElementById('create-due-note');
            if (createDuration && createDuePreview) {
                createDuration.addEventListener('change', function () {
                    createDuePreview.textContent = formatIndoDate(this.value);
                    if (createDueNote) createDueNote.textContent = `${this.value} hari kalender setelah pinjam`;
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan…';
                });
            }

            reindexAndRefresh();
        });
    </script>
</x-layouts.app>

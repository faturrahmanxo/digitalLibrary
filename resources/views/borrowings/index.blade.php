<x-layouts.app :title="auth()->user()?->isAnggota() ? 'Peminjaman Saya' : 'Riwayat Peminjaman'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ auth()->user()?->isAnggota() ? 'Peminjaman Saya' : 'Riwayat Peminjaman' }}</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <div role="alert" class="mb-6 rounded-card border border-line bg-surface p-4 text-sm text-ink flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-card border border-line bg-surface p-4 text-sm text-ink flex items-center justify-between shadow-xs">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ auth()->user()?->isAnggota() ? 'Riwayat Peminjaman Saya' : 'Riwayat Peminjaman' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ auth()->user()?->isAnggota() ? 'Pantau riwayat sirkulasi dan catatan pengembalian buku pada akun Peminjaman Saya.' : 'Riwayat sirkulasi dan catatan pengembalian buku perpustakaan.' }}
            </p>
        </div>
    </div>

    <!-- Search & Status Filter -->
    <form method="GET" action="{{ route('borrowings.index') }}" class="bg-surface rounded-card border border-line p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-xs">
        <div class="flex-1">
            <label for="q" class="sr-only">Cari kode transaksi atau nama buku</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Cari kode transaksi..." class="w-full min-h-10 px-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
        </div>
        <div class="sm:w-52">
            <label for="status" class="sr-only">Filter Status</label>
            <select id="status" name="status" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus">
                <option value="">Semua Status</option>
                <option value="borrowed" @selected($statusFilter === 'borrowed')>Dipinjam (Aktif)</option>
                <option value="overdue" @selected($statusFilter === 'overdue')>Dipinjam — Terlambat</option>
                <option value="returned" @selected($statusFilter === 'returned')>Dikembalikan</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors cursor-pointer">
                Filter
            </button>
            @if ($search !== '' || $statusFilter !== '')
                <a href="{{ route('borrowings.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-sm font-medium text-text-muted hover:text-ink transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if (auth()->user()?->isAnggota())
        <!-- ============================================== -->
        <!-- ANGGOTA PEMINJAMAN CARDS (Reference Concept)   -->
        <!-- ============================================== -->
        @if ($borrowings->isEmpty())
            <div class="bg-surface rounded-card border border-line p-12 text-center shadow-xs">
                <p class="text-base font-semibold text-ink">Belum ada riwayat peminjaman.</p>
                <p class="mt-1 text-sm text-text-muted">Catatan sirkulasi peminjaman Anda akan ditampilkan di sini.</p>
            </div>
        @else
            <div class="space-y-4 mb-6">
                @foreach ($borrowings as $loan)
                    <div class="card-interactive bg-surface rounded-2xl border border-line p-5 shadow-xs hover:border-brand-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Book Art Box -->
                            <div class="w-12 h-16 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex flex-col items-center justify-center p-2 text-center shrink-0 shadow-xs">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-white/80">Koleksi</span>
                                <span class="text-[9px] font-mono font-bold mt-1 text-white">{{ $loan->total_quantity }} eks</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-bold text-text-muted">{{ $loan->transaction_code }}</span>
                                    @if ($loan->isBorrowed())
                                        @if ($loan->isOverdue())
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-soft text-text-muted border border-line">
                                                Terlambat Pengembalian
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-soft text-ink border border-line">
                                                Dipinjam (Aktif)
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-soft text-text-muted border border-line">
                                            Sudah Dikembalikan
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-ink">
                                    {{ $loan->items->first()?->book_title_snapshot ?? 'Koleksi Buku Litera' }}
                                    @if ($loan->items->count() > 1)
                                        <span class="text-xs font-normal text-text-muted">(+{{ $loan->items->count() - 1 }} judul lainnya)</span>
                                    @endif
                                </h3>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-text-muted">
                                    <span>Tgl Pinjam: <strong class="text-ink">{{ $loan->borrowed_at?->timezone('Asia/Jakarta')->format('d M Y') }}</strong></span>
                                    <span>Batas Jatuh Tempo: <strong class="{{ $loan->isOverdue() ? 'text-brand-600' : 'text-ink' }}">{{ $loan->due_at?->format('d M Y') }}</strong></span>
                                    @if ($loan->isReturned())
                                        <span>Dikembalikan: <strong class="text-ink">{{ $loan->returned_at?->timezone('Asia/Jakarta')->format('d M Y') }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="sm:text-right shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-line">
                            <a href="{{ route('borrowings.show', $loan) }}" class="inline-flex items-center px-4 py-2 rounded-control bg-surface border border-line text-xs font-semibold text-brand-600 hover:bg-surface-soft hover:border-brand-200 shadow-xs transition-colors">
                                Detail Transaksi
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($borrowings->hasPages())
                <div class="bg-surface rounded-card border border-line p-4">
                    {{ $borrowings->links() }}
                </div>
            @endif
        @endif

    @else
        <!-- ============================================== -->
        <!-- STAFF & ADMIN PEMINJAMAN DATA TABLE            -->
        <!-- ============================================== -->
        <div class="bg-surface rounded-card border border-line overflow-hidden shadow-xs">
            @if ($borrowings->isEmpty())
                <div class="p-10 text-center">
                    @if ($search !== '' || $statusFilter !== '')
                        <p class="text-base font-semibold text-ink">Tidak ada hasil yang cocok.</p>
                        <p class="mt-1 text-sm text-text-muted">Ubah filter status atau kata kunci pencarian Anda.</p>
                        <a href="{{ route('borrowings.index') }}" class="mt-4 inline-flex items-center justify-center min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft">
                            Bersihkan Filter
                        </a>
                    @else
                        <p class="text-base font-semibold text-ink">Belum ada riwayat peminjaman.</p>
                        <p class="mt-1 text-sm text-text-muted">Seluruh catatan sirkulasi dan transaksi peminjaman buku akan tercatat di sini.</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                                <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Kode Transaksi</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[160px]">Anggota Peminjam</th>
                                <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Petugas Pencatat</th>
                                <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Tanggal Pinjam</th>
                                <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Jatuh Tempo</th>
                                <th scope="col" class="py-3.5 px-4 text-right whitespace-nowrap">Total Buku</th>
                                <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Status</th>
                                <th scope="col" class="py-3.5 px-4 text-right whitespace-nowrap">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach ($borrowings as $loan)
                                <tr class="hover:bg-surface-soft/50">
                                    <td class="py-3.5 px-4 font-mono text-xs font-semibold text-ink whitespace-nowrap">
                                        <a href="{{ route('borrowings.show', $loan) }}" class="hover:text-brand-600">
                                            {{ $loan->transaction_code }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-ink">
                                        {{ $loan->member_name_snapshot }}
                                        @if ($loan->member)
                                            <span class="block text-xs font-mono text-text-muted">{{ $loan->member->member_code }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-text-muted text-xs">
                                        {{ $loan->isDigital() ? 'Mandiri (digital)' : ($loan->staff?->name ?? '—') }}
                                    </td>
                                    <td class="py-3.5 px-4 tabular-nums text-text-muted">{{ $loan->borrowed_at?->timezone('Asia/Jakarta')->format('d M Y') }}</td>
                                    <td class="py-3.5 px-4 tabular-nums {{ $loan->isOverdue() ? 'text-brand-600 font-semibold' : 'text-text-muted' }}">
                                        {{ $loan->due_at?->format('d M Y') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right tabular-nums font-semibold">{{ $loan->total_quantity }} eks</td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($loan->isBorrowed())
                                            @if ($loan->isOverdue())
                                                <x-status-badge variant="neutral">Terlambat</x-status-badge>
                                            @else
                                                <x-status-badge variant="info">Dipinjam</x-status-badge>
                                            @endif
                                        @else
                                            <x-status-badge variant="neutral">Dikembalikan</x-status-badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            @can('return', $loan)
                                                @if ($loan->isBorrowed())
                                                    <button
                                                        type="button"
                                                        class="btn-open-return-modal inline-flex items-center px-2.5 py-1 rounded-control bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                        data-loan-code="{{ $loan->transaction_code }}"
                                                        data-member-name="{{ $loan->member_name_snapshot }}"
                                                        data-quantity="{{ $loan->total_quantity }}"
                                                        data-action-url="{{ route('borrowings.return', $loan) }}"
                                                        title="Kembalikan Buku"
                                                    >
                                                        Kembalikan
                                                    </button>
                                                @endif
                                            @endcan
                                            <a href="{{ route('borrowings.show', $loan) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                                                Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($borrowings->hasPages())
                    <div class="p-4 border-t border-line">
                        {{ $borrowings->links() }}
                    </div>
                @endif
            @endif
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- POPUP KONFIRMASI PENGEMBALIAN BUKU                       -->
    <!-- ======================================================== -->
    <div id="quick-return-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="quick-return-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <!-- Darkened Backdrop -->
            <div id="quick-return-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

            <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                <div class="flex items-center justify-between pb-4 border-b border-line">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="quick-return-title" class="text-base font-bold text-ink">
                                Konfirmasi Pengembalian
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">Sirkulasi penerimaan buku perpustakaan.</p>
                        </div>
                    </div>
                    <button type="button" id="btn-close-quick-return" class="px-2.5 py-1.5 rounded-control text-xs font-medium text-text-muted hover:text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                        &times;
                    </button>
                </div>

                <form id="quick-return-form" method="POST" action="" class="space-y-4 mt-4">
                    @csrf

                    <div class="rounded-xl border border-line bg-surface-soft/40 p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">No. Transaksi</span>
                            <span id="return-info-code" class="font-mono font-bold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">Nama Peminjam</span>
                            <span id="return-info-member" class="font-semibold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted font-medium">Total Eksemplar</span>
                            <span id="return-info-quantity" class="font-bold text-brand-600 dark:text-brand-400">-</span>
                        </div>
                    </div>

                    <p class="text-xs text-text-muted leading-relaxed">
                        Dengan menekan tombol di bawah, status transaksi akan ditandai <strong>Sudah Dikembalikan</strong> dan stok buku fisik akan otomatis bertambah kembali ke sistem katalog.
                    </p>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                        <button type="button" id="btn-cancel-quick-return" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-quick-return" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                            Ya, Kembalikan Buku
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick Return Modal Logic
            const returnModal = document.getElementById('quick-return-modal');
            const returnBackdrop = document.getElementById('quick-return-backdrop');
            const returnCloseBtn = document.getElementById('btn-close-quick-return');
            const returnCancelBtn = document.getElementById('btn-cancel-quick-return');
            const returnForm = document.getElementById('quick-return-form');
            const returnSubmit = document.getElementById('btn-submit-quick-return');
            const returnInfoCode = document.getElementById('return-info-code');
            const returnInfoMember = document.getElementById('return-info-member');
            const returnInfoQuantity = document.getElementById('return-info-quantity');
            const returnButtons = document.querySelectorAll('.btn-open-return-modal');

            function openReturnModal(btn) {
                const actionUrl = btn.getAttribute('data-action-url');
                const loanCode = btn.getAttribute('data-loan-code') || '-';
                const memberName = btn.getAttribute('data-member-name') || '-';
                const quantity = btn.getAttribute('data-quantity') || '1';

                if (returnForm) returnForm.action = actionUrl;
                if (returnInfoCode) returnInfoCode.textContent = loanCode;
                if (returnInfoMember) returnInfoMember.textContent = memberName;
                if (returnInfoQuantity) returnInfoQuantity.textContent = quantity + ' eksemplar';

                if (returnModal) returnModal.classList.remove('hidden');
            }

            function closeReturnModal() {
                if (returnModal) returnModal.classList.add('hidden');
            }

            returnButtons.forEach(btn => {
                btn.addEventListener('click', () => openReturnModal(btn));
            });

            if (returnCloseBtn) returnCloseBtn.addEventListener('click', closeReturnModal);
            if (returnCancelBtn) returnCancelBtn.addEventListener('click', closeReturnModal);
            if (returnBackdrop) returnBackdrop.addEventListener('click', closeReturnModal);

            if (returnForm && returnSubmit) {
                returnForm.addEventListener('submit', function () {
                    returnSubmit.disabled = true;
                    returnSubmit.textContent = 'Memproses Pengembalian...';
                    returnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

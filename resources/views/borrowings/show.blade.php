<x-layouts.app :title="'Transaksi ' . $borrowing->transaction_code">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('borrowings.index') }}" class="hover:text-ink">Peminjaman</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Detail Peminjaman</span>
    </x-slot:breadcrumbs>

    <!-- SCR-015: Borrowing Detail / Return (Critical Screen) -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if ($borrowing->isBorrowed())
                    <x-status-badge variant="info">Dipinjam</x-status-badge>
                    @if ($borrowing->isOverdue())
                        <x-status-badge variant="warning">Terlambat</x-status-badge>
                    @endif
                @else
                    <x-status-badge variant="success">Dikembalikan</x-status-badge>
                @endif
                <span class="font-mono text-xs font-semibold text-text-muted">{{ $borrowing->transaction_code }}</span>
            </div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                Detail Peminjaman {{ $borrowing->transaction_code }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                Peminjam: <span class="font-semibold text-ink">{{ $borrowing->member_name_snapshot }}</span>
                @if ($borrowing->member)
                    ({{ $borrowing->member->member_code }})
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @can('return', $borrowing)
                @if ($borrowing->isBorrowed())
                    <button
                        type="button"
                        id="open-return-modal-btn"
                        data-testid="open-return-modal"
                        class="min-h-11 inline-flex items-center justify-center rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer shadow-xs transition-colors"
                    >
                        {{ $borrowing->isDigital() ? 'Kembalikan Akses Digital' : 'Kembalikan Semua Buku' }}
                    </button>
                @endif
            @endcan
            <a href="{{ route('borrowings.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink hover:bg-surface-soft transition-colors">
                Kembali ke Peminjaman
            </a>
        </div>
    </div>

    @if ($borrowing->isOverdue())
        <div role="status" class="mb-6 rounded-card border border-warning-fg/30 bg-warning-bg p-4 text-sm text-warning-fg flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold">Transaksi Melewati Tanggal Jatuh Tempo ({{ $borrowing->due_at?->format('d M Y') }})</p>
                <p class="text-xs mt-0.5">Pengembalian tetap dapat diproses untuk seluruh buku tanpa perhitungan denda pada MVP.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Summary Metadata Card -->
        <section aria-labelledby="loan-summary-heading" class="bg-surface rounded-card border border-line p-6 space-y-4">
            <h2 id="loan-summary-heading" class="text-base font-semibold text-ink border-b border-line pb-3">Informasi Transaksi</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Kode Transaksi</dt>
                    <dd class="mt-0.5 font-mono font-semibold text-ink">{{ $borrowing->transaction_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Anggota Peminjam</dt>
                    <dd class="mt-0.5 font-medium text-ink">
                        {{ $borrowing->member_name_snapshot }}
                        @if ($borrowing->member)
                            <span class="block text-xs font-mono text-text-muted">{{ $borrowing->member->member_code }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Dicatat Oleh</dt>
                    <dd class="mt-0.5 text-ink">
                        {{ $borrowing->isDigital() ? 'Peminjaman mandiri' : ($borrowing->staff?->name ?? '—') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Jenis Peminjaman</dt>
                    <dd class="mt-0.5 text-ink">{{ $borrowing->isDigital() ? 'Lisensi digital · 7 hari' : 'Inventaris fisik' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Tanggal Pinjam</dt>
                    <dd class="mt-0.5 tabular-nums text-ink">{{ $borrowing->borrowed_at?->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Jatuh Tempo</dt>
                    <dd class="mt-0.5 tabular-nums font-medium {{ $borrowing->isOverdue() ? 'text-warning-fg' : 'text-ink' }}">
                        {{ $borrowing->due_at?->format('d F Y') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Status</dt>
                    <dd class="mt-1 flex flex-wrap items-center gap-1.5">
                        @if ($borrowing->isBorrowed())
                            <x-status-badge variant="info">Dipinjam</x-status-badge>
                            @if ($borrowing->isOverdue())
                                <x-status-badge variant="warning">Terlambat</x-status-badge>
                            @endif
                        @else
                            <x-status-badge variant="success">Dikembalikan</x-status-badge>
                        @endif
                    </dd>
                </div>
                @if ($borrowing->isReturned())
                    <div class="pt-3 border-t border-line">
                        <dt class="text-xs font-semibold text-success-fg">Sudah Dikembalikan Pada</dt>
                        <dd class="mt-0.5 tabular-nums font-semibold text-ink">
                            {{ $borrowing->returned_at?->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB
                        </dd>
                        @if ($borrowing->returnedByUser)
                            <p class="mt-1 text-xs text-text-muted">Diterima oleh: {{ $borrowing->returnedByUser->name }}</p>
                        @endif
                    </div>
                @endif
            </dl>
        </section>

        <!-- Borrowed Books Table -->
        <section aria-labelledby="loan-items-heading" class="lg:col-span-2 bg-surface rounded-card border border-line overflow-hidden">
            <div class="p-5 border-b border-line flex items-center justify-between gap-2">
                <h2 id="loan-items-heading" class="text-base font-semibold text-ink">Rincian Buku yang Dipinjam</h2>
                <span class="text-xs font-semibold text-text-muted tabular-nums">
                    {{ $borrowing->items->count() }} judul / {{ $borrowing->total_quantity }} eksemplar
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">No.</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[180px]">Judul Buku (Snapshot Transaksi)</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Penulis</th>
                            <th scope="col" class="py-3.5 px-4 text-right whitespace-nowrap">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($borrowing->items as $idx => $item)
                            <tr class="hover:bg-surface-soft/50">
                                <td class="py-3.5 px-4 text-text-muted tabular-nums whitespace-nowrap">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4 font-semibold text-ink">
                                    {{ $item->book_title_snapshot }}
                                    @if ($item->volume_number_snapshot)
                                        <span class="block text-xs font-normal text-text-muted">
                                            Volume {{ $item->volume_number_snapshot }}{{ filled($item->volume_title_snapshot) ? ' · ' . $item->volume_title_snapshot : '' }}
                                        </span>
                                    @endif
                                    @if ($item->book && $item->book->trashed())
                                        <span class="ml-1.5 text-xs font-normal text-text-muted">(Buku Diarsipkan)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-text-muted whitespace-nowrap">
                                    {{ $item->book?->author ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-ink tabular-nums whitespace-nowrap">
                                    {{ $item->quantity }} eksemplar
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-surface-soft/70 border-t border-line text-sm font-semibold text-ink">
                            <td colspan="3" class="py-3.5 px-4 text-right whitespace-nowrap">Total Eksemplar</td>
                            <td class="py-3.5 px-4 text-right tabular-nums whitespace-nowrap">{{ $borrowing->total_quantity }} eksemplar</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    <!-- Return Confirmation Modal (SCR-015 Mandatory Spec) -->
    @if ($borrowing->isBorrowed())
        <div id="return-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="return-modal-title" aria-describedby="return-modal-desc" class="modal-animated fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200">
            <div class="modal-content-box bg-surface rounded-2xl border border-line shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-6 overflow-y-auto space-y-4">
                    <div>
                        <h2 id="return-modal-title" class="text-lg font-bold text-ink">Konfirmasi Pengembalian</h2>
                        <p id="return-modal-desc" class="mt-1 text-sm text-text-muted">
                            Kembalikan seluruh buku untuk anggota <strong class="text-ink">{{ $borrowing->member_name_snapshot }}</strong> pada transaksi <strong class="font-mono text-ink">{{ $borrowing->transaction_code }}</strong>?
                        </p>
                    </div>

                    <div class="rounded-control border border-line bg-surface-soft p-4 space-y-2 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-text-muted">Rincian Buku yang Dikembalikan:</p>
                        <ul class="divide-y divide-line">
                            @foreach ($borrowing->items as $item)
                                <li class="py-1.5 flex items-center justify-between gap-2">
                                    <span class="font-medium text-ink">{{ $item->book_title_snapshot }}</span>
                                    <span class="font-semibold text-ink tabular-nums shrink-0">{{ $item->quantity }} eksemplar</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="pt-2 border-t border-line flex items-center justify-between text-xs text-text-muted">
                            <span>Jatuh tempo: {{ $borrowing->due_at?->format('d M Y') }}</span>
                            <span class="font-semibold text-ink">Total: {{ $borrowing->total_quantity }} eksemplar</span>
                        </div>
                    </div>

                    <p class="text-xs text-text-muted">
                        Tanggal pengembalian dicatat otomatis dari waktu server.
                        {{ $borrowing->isDigital() ? 'Kuota lisensi digital dihitung kembali dari pinjaman aktif.' : 'Stok fisik tersedia dipulihkan tepat satu kali.' }}
                    </p>
                </div>

                <form method="POST" action="{{ route('borrowings.return', $borrowing) }}" id="return-confirm-form" class="p-4 bg-surface-soft border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    @csrf
                    <button
                        type="button"
                        id="close-return-modal-btn"
                        class="min-h-11 px-4 py-2 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="confirm-return-submit-btn"
                        data-testid="confirm-return-submit"
                        class="min-h-11 px-5 py-2 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer"
                    >
                        {{ $borrowing->isDigital() ? 'Ya, Kembalikan Akses' : 'Ya, Kembalikan Semua' }}
                    </button>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const openBtn = document.getElementById('open-return-modal-btn');
                const modal = document.getElementById('return-confirm-modal');
                const closeBtn = document.getElementById('close-return-modal-btn');
                const returnForm = document.getElementById('return-confirm-form');
                const submitBtn = document.getElementById('confirm-return-submit-btn');

                function openModal() {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    if (closeBtn) closeBtn.focus();
                }

                function closeModal() {
                    if (!modal || modal.classList.contains('hidden')) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    if (openBtn) openBtn.focus();
                }

                if (openBtn) openBtn.addEventListener('click', openModal);
                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                if (modal) {
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) closeModal();
                    });
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeModal();
                });

                if (returnForm && submitBtn) {
                    returnForm.addEventListener('submit', function () {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Mengembalikan…';
                    });
                }
            });
        </script>
    @endif
</x-layouts.app>

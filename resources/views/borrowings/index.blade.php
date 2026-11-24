<x-layouts.app :title="auth()->user()?->isAnggota() ? 'Peminjaman Saya' : 'Peminjaman Buku — Sirkulasi Perpustakaan'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">{{ auth()->user()?->isAnggota() ? 'Beranda' : 'Dashboard' }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ auth()->user()?->isAnggota() ? 'Peminjaman Saya' : 'Peminjaman Buku' }}</span>
    </x-slot:breadcrumbs>

    @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();
        $isAnggota = $authUser?->isAnggota();
        $members = $members ?? \App\Models\Member::orderBy('name')->get();
        $availableBooks = $availableBooks ?? \App\Models\Book::where('collection_type', 'physical')->where('stock_available', '>', 0)->orderBy('title')->get();

        $search = request('q', $search ?? '');
        $statusFilter = request('status', $statusFilter ?? '');
        $memberFilter = request('member_id', '');
        $hasActiveFilter = $search !== '' || $statusFilter !== '' || $memberFilter !== '';
    @endphp

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
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isAnggota ? 'Riwayat Peminjaman Saya' : 'Peminjaman Buku' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isAnggota ? 'Pantau status pengajuan buku, masa aktif peminjaman, serta jatuh tempo pengembalian.' : 'Kelola persetujuan, penyerahan eksemplar fisik, pencatatan pengembalian, dan peminjaman langsung walk-in.' }}
            </p>
        </div>

        @if (! $isAnggota)
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    id="btn-open-direct-borrow-modal"
                    class="min-h-11 inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Peminjaman Langsung (Walk-in)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- 18. SEARCH & FILTER BAR -->
    <form method="GET" action="{{ route('borrowings.index') }}" class="bg-surface rounded-card border border-line p-4 sm:p-5 mb-6 shadow-xs space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <label for="q" class="block text-xs font-semibold text-text-muted mb-1">Cari Transaksi</label>
                <div class="relative">
                    <input
                        type="search"
                        id="q"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari kode transaksi (TRX-...), nama anggota, atau judul buku..."
                        class="w-full min-h-10 pl-9 pr-3.5 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                    >
                    <svg class="w-4 h-4 text-text-muted absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Status Sirkulasi</label>
                <select id="status" name="status" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected($statusFilter === 'pending')>Pending (Menunggu Persetujuan)</option>
                    <option value="approved" @selected($statusFilter === 'approved')>Approved (Menunggu Pengambilan)</option>
                    <option value="borrowed" @selected($statusFilter === 'borrowed')>Borrowed (Sedang Dipinjam)</option>
                    <option value="overdue" @selected($statusFilter === 'overdue')>Terlambat (Overdue)</option>
                    <option value="returned" @selected($statusFilter === 'returned')>Returned (Sudah Dikembalikan)</option>
                    <option value="rejected" @selected($statusFilter === 'rejected')>Rejected (Ditolak)</option>
                    <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled (Dibatalkan)</option>
                </select>
            </div>

            @if (! $isAnggota)
                <div class="sm:col-span-3">
                    <label for="member_id" class="block text-xs font-semibold text-text-muted mb-1">Filter Anggota</label>
                    <select id="member_id" name="member_id" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                        <option value="">Semua Anggota</option>
                        @foreach ($members as $mem)
                            <option value="{{ $mem->id }}" @selected((string) $memberFilter === (string) $mem->id)>
                                {{ $mem->name }} ({{ $mem->member_code ?? 'AGT' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full min-h-10 px-4 py-2 rounded-control bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors shadow-xs cursor-pointer">
                        Filter Peminjaman
                    </button>
                </div>
            @endif
        </div>

        @if (! $isAnggota)
            <div class="flex items-center justify-between pt-2 border-t border-line">
                <span class="text-xs text-text-muted">
                    Menampilkan <strong class="text-ink">{{ $borrowings->total() }}</strong> catatan sirkulasi
                </span>
                <div class="flex items-center gap-2">
                    @if ($hasActiveFilter)
                        <a href="{{ route('borrowings.index') }}" class="min-h-10 inline-flex items-center px-4 py-2 rounded-control border border-line text-xs font-semibold text-text-muted hover:text-ink transition-colors">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors shadow-xs cursor-pointer">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        @endif
    </form>

    <!-- ======================================================== -->
    <!-- 18. AUTHORITATIVE BORROWING MANAGEMENT TABLE (STAFF)     -->
    <!-- ======================================================== -->
    <div class="bg-surface rounded-card border border-line overflow-hidden shadow-xs mb-6">
        @if ($borrowings->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-surface-soft border border-line text-text-muted flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Tidak ada transaksi sirkulasi yang ditemukan</h3>
                <p class="mt-1 text-xs text-text-muted">Belum ada data peminjaman atau filter pencarian tidak menghasilkan kecocokan.</p>
                @if ($hasActiveFilter)
                    <a href="{{ route('borrowings.index') }}" class="mt-4 inline-flex items-center justify-center min-h-10 px-5 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors">
                        Bersihkan Filter
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Kode Transaksi</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[180px]">Anggota</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[200px]">Judul Buku</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Tgl Pinjam / Pengajuan</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Jatuh Tempo</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Tgl Kembali</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap text-center">Status</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap">Petugas</th>
                            <th scope="col" class="py-3.5 px-4 whitespace-nowrap text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @foreach ($borrowings as $loan)
                            @php
                                $status = $loan->status ?? 'pending';
                                $isPending = $status === 'pending' || (method_exists($loan, 'isPending') && $loan->isPending());
                                $isApproved = $status === 'approved' || (method_exists($loan, 'isApproved') && $loan->isApproved());
                                $isBorrowed = $status === 'borrowed' || (method_exists($loan, 'isBorrowed') && $loan->isBorrowed());
                                $isReturned = $status === 'returned' || (method_exists($loan, 'isReturned') && $loan->isReturned());
                                $isRejected = $status === 'rejected' || (method_exists($loan, 'isRejected') && $loan->isRejected());
                                $isCancelled = $status === 'cancelled' || (method_exists($loan, 'isCancelled') && $loan->isCancelled());

                                $isOverdue = $isBorrowed && (
                                    method_exists($loan, 'isOverdue')
                                        ? $loan->isOverdue()
                                        : ($loan->due_at && $loan->due_at->isPast())
                                );

                                $firstItem = $loan->items?->first();
                                $bookTitle = $firstItem?->book_title_snapshot ?? $firstItem?->book?->title ?? $loan->book?->title ?? 'Buku Perpustakaan';
                                $itemCount = $loan->items ? $loan->items->count() : 1;
                                $memberName = $loan->member_name_snapshot ?? $loan->member?->name ?? $loan->user?->name ?? 'Anggota';
                                $memberCode = $loan->member?->member_code ?? null;
                            @endphp
                            <tr class="hover:bg-surface-soft/50 transition-colors">
                                <!-- Kode Transaksi -->
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-ink whitespace-nowrap">
                                    <a href="{{ route('borrowings.show', $loan) }}" class="hover:text-brand-600 transition-colors">
                                        {{ $loan->transaction_code }}
                                    </a>
                                    @if ($loan->is_direct_borrow)
                                        <span class="block text-[10px] font-sans font-semibold text-brand-700 dark:text-brand-400">Walk-in</span>
                                    @endif
                                </td>

                                <!-- Anggota -->
                                <td class="py-3.5 px-4 font-medium text-ink">
                                    <div class="line-clamp-1">{{ $memberName }}</div>
                                    @if ($memberCode)
                                        <span class="block text-xs font-mono text-text-muted">{{ $memberCode }}</span>
                                    @endif
                                </td>

                                <!-- Judul Buku -->
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-ink line-clamp-1" title="{{ $bookTitle }}">{{ $bookTitle }}</span>
                                    @if ($itemCount > 1)
                                        <span class="text-xs text-text-muted">(+{{ $itemCount - 1 }} judul lainnya)</span>
                                    @endif
                                </td>

                                <!-- Tanggal Pinjam / Pengajuan -->
                                <td class="py-3.5 px-4 tabular-nums text-xs text-text-muted whitespace-nowrap">
                                    {{ $loan->borrowed_at ? $loan->borrowed_at->timezone('Asia/Jakarta')->format('d M Y') : ($loan->created_at ? $loan->created_at->timezone('Asia/Jakarta')->format('d M Y') : '—') }}
                                </td>

                                <!-- Jatuh Tempo -->
                                <td class="py-3.5 px-4 tabular-nums text-xs whitespace-nowrap">
                                    @if ($loan->due_at || $loan->due_date)
                                        @php
                                            $dueDateObj = $loan->due_at ?? \Carbon\Carbon::parse($loan->due_date);
                                        @endphp
                                        <span class="{{ $isOverdue ? 'text-danger-fg font-bold' : 'text-text-muted' }}">
                                            {{ $dueDateObj->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-text-muted">—</span>
                                    @endif
                                </td>

                                <!-- Tanggal Pengembalian -->
                                <td class="py-3.5 px-4 tabular-nums text-xs text-text-muted whitespace-nowrap">
                                    {{ $loan->returned_at ? $loan->returned_at->timezone('Asia/Jakarta')->format('d M Y') : '—' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                    @if ($isPending)
                                        <x-status-badge variant="warning">Pending</x-status-badge>
                                    @elseif ($isApproved)
                                        <x-status-badge variant="info">Disetujui</x-status-badge>
                                    @elseif ($isBorrowed)
                                        @if ($isOverdue)
                                            <x-status-badge variant="danger">Terlambat</x-status-badge>
                                        @else
                                            <x-status-badge variant="info">Dipinjam</x-status-badge>
                                        @endif
                                    @elseif ($isReturned)
                                        <x-status-badge variant="success">Dikembalikan</x-status-badge>
                                    @elseif ($isRejected)
                                        <x-status-badge variant="danger">Ditolak</x-status-badge>
                                    @elseif ($isCancelled)
                                        <x-status-badge variant="neutral">Dibatalkan</x-status-badge>
                                    @else
                                        <x-status-badge variant="neutral">{{ ucfirst($status) }}</x-status-badge>
                                    @endif
                                </td>

                                <!-- Petugas -->
                                <td class="py-3.5 px-4 text-xs text-text-muted whitespace-nowrap">
                                    {{ $loan->staff?->name ?? $loan->approver?->name ?? '—' }}
                                </td>

                                <!-- 18. AKSI BERDASARKAN STATUS -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                                    @if (! $isAnggota)
                                        @if ($isPending)
                                            {{-- Pending: Setujui (Approve) & Tolak (Reject) --}}
                                            <button
                                                type="button"
                                                class="btn-action-approve inline-flex items-center px-2.5 py-1 rounded-control bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                data-action-url="{{ Route::has('borrowings.approve') ? route('borrowings.approve', $loan) : url('/borrowings/' . $loan->id . '/approve') }}"
                                                data-loan-code="{{ $loan->transaction_code }}"
                                                data-member-name="{{ $memberName }}"
                                                data-book-title="{{ $bookTitle }}"
                                                title="Setujui Peminjaman"
                                            >
                                                Setujui
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action-reject inline-flex items-center px-2.5 py-1 rounded-control border border-danger-fg/40 text-danger-fg hover:bg-danger-bg text-xs font-semibold cursor-pointer transition-colors"
                                                data-action-url="{{ Route::has('borrowings.reject') ? route('borrowings.reject', $loan) : url('/borrowings/' . $loan->id . '/reject') }}"
                                                data-loan-code="{{ $loan->transaction_code }}"
                                                data-member-name="{{ $memberName }}"
                                                title="Tolak Peminjaman"
                                            >
                                                Tolak
                                            </button>

                                        @elseif ($isApproved)
                                            {{-- Approved: Serahkan Buku (Handover) & Batalkan --}}
                                            <button
                                                type="button"
                                                class="btn-action-handover inline-flex items-center px-2.5 py-1 rounded-control bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                data-action-url="{{ Route::has('borrowings.handover') ? route('borrowings.handover', $loan) : url('/borrowings/' . $loan->id . '/handover') }}"
                                                data-loan-code="{{ $loan->transaction_code }}"
                                                data-member-name="{{ $memberName }}"
                                                data-book-title="{{ $bookTitle }}"
                                                title="Serahkan Buku kepada Anggota"
                                            >
                                                Serahkan Buku
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action-cancel inline-flex items-center px-2.5 py-1 rounded-control border border-line text-text-muted hover:text-ink hover:bg-surface-soft text-xs font-semibold cursor-pointer transition-colors"
                                                data-action-url="{{ Route::has('borrowings.cancel') ? route('borrowings.cancel', $loan) : url('/borrowings/' . $loan->id . '/cancel') }}"
                                                data-loan-code="{{ $loan->transaction_code }}"
                                                title="Batalkan Reservasi"
                                            >
                                                Batalkan
                                            </button>

                                        @elseif ($isBorrowed)
                                            {{-- Borrowed: Proses Pengembalian (Return) --}}
                                            <button
                                                type="button"
                                                class="btn-open-return-modal inline-flex items-center px-2.5 py-1 rounded-control bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold cursor-pointer shadow-xs transition-colors"
                                                data-action-url="{{ route('borrowings.return', $loan) }}"
                                                data-loan-code="{{ $loan->transaction_code }}"
                                                data-member-name="{{ $memberName }}"
                                                data-quantity="{{ $loan->total_quantity ?? 1 }}"
                                                title="Proses Pengembalian Buku"
                                            >
                                                Kembalikan
                                            </button>
                                        @endif
                                    @endif

                                    <!-- Detail Action (Available for all statuses) -->
                                    <a
                                        href="{{ route('borrowings.show', $loan) }}"
                                        class="inline-flex items-center px-2.5 py-1 rounded-control border border-line bg-surface text-xs font-semibold text-brand-600 hover:text-brand-700 hover:bg-surface-soft transition-colors"
                                        title="Lihat Detail Transaksi"
                                    >
                                        Detail
                                    </a>
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

    <!-- ======================================================== -->
    <!-- DIRECT BORROW MODAL (Walk-in Anggota di Perpustakaan)    -->
    <!-- ======================================================== -->
    @if (! $isAnggota)
        <div id="direct-borrow-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="direct-borrow-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div id="direct-borrow-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 id="direct-borrow-title" class="text-base font-bold text-ink">
                                    Peminjaman Langsung (Walk-in)
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5">Catat peminjaman buku fisik langsung di meja sirkulasi.</p>
                            </div>
                        </div>
                        <button type="button" id="btn-close-direct-borrow" class="text-text-muted hover:text-ink text-xl font-bold p-1 cursor-pointer">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('borrowings.store') }}" class="space-y-4 mt-4">
                        @csrf
                        <input type="hidden" name="is_direct_borrow" value="1">
                        <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                        <div>
                            <label for="direct_member_id" class="block text-xs font-semibold text-ink mb-1.5">
                                Pilih Anggota Peminjam <span class="text-danger-fg">*</span>
                            </label>
                            <select id="direct_member_id" name="member_id" required class="w-full min-h-11 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                                <option value="">-- Pilih Anggota Terdaftar --</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}">
                                        {{ $m->name }} ({{ $m->member_code ?? 'AGT' }}) - {{ $m->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="direct_book_id" class="block text-xs font-semibold text-ink mb-1.5">
                                Pilih Judul Buku Fisik <span class="text-danger-fg">*</span>
                            </label>
                            <select id="direct_book_id" name="items[0][book_id]" required class="w-full min-h-11 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink focus:outline-2 focus:outline-focus cursor-pointer">
                                <option value="">-- Pilih Buku yang Tersedia --</option>
                                @foreach ($availableBooks as $b)
                                    <option value="{{ $b->id }}" data-shelf="{{ $b->shelf?->code ?? '—' }}" data-stock="{{ $b->available_copies ?? $b->stock_available }}">
                                        {{ $b->title }} (Tersedia: {{ $b->available_copies ?? $b->stock_available }} eks)
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="items[0][quantity]" value="1">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="direct_duration" class="block text-xs font-semibold text-ink mb-1.5">Durasi Peminjaman</label>
                                <input type="number" id="direct_duration" name="duration_days" value="7" min="1" max="30" class="w-full min-h-10 px-3 py-2 rounded-control border border-line text-sm bg-surface text-ink tabular-nums focus:outline-2 focus:outline-focus">
                                <p class="mt-1 text-[11px] text-text-muted">Standar: 7 hari kalender</p>
                            </div>
                            <div class="p-2.5 rounded-control bg-surface-soft border border-line text-xs flex flex-col justify-center">
                                <span class="text-text-muted">Jatuh Tempo Otomatis:</span>
                                <strong class="text-ink font-semibold mt-0.5">{{ \Carbon\Carbon::now('Asia/Jakarta')->addDays(7)->format('d M Y') }}</strong>
                            </div>
                        </div>

                        <div>
                            <label for="direct_notes" class="block text-xs font-semibold text-ink mb-1.5">Catatan Tambahan (Opsional)</label>
                            <textarea id="direct_notes" name="notes" rows="2" placeholder="Catatan kondisi buku atau identitas penyerahan..." class="w-full px-3 py-2 rounded-control border border-line text-xs bg-surface text-ink focus:outline-2 focus:outline-focus"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                            <button type="button" id="btn-cancel-direct-borrow" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white shadow-xs transition-colors cursor-pointer">
                                Proses Peminjaman Langsung
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- CONFIRMATION ACTION MODAL (APPROVE / HANDOVER / CANCEL)  -->
        <!-- ======================================================== -->
        <div id="general-action-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="general-action-title">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div id="general-action-backdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity duration-200"></div>

                <div class="modal-content-box relative w-full max-w-md transform overflow-hidden rounded-2xl bg-surface border border-line p-6 text-left shadow-2xl transition-all">
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <h3 id="general-action-title" class="text-base font-bold text-ink">Konfirmasi Tindakan</h3>
                        <button type="button" id="btn-close-general-action" class="text-text-muted hover:text-ink text-xl font-bold cursor-pointer">&times;</button>
                    </div>

                    <form id="general-action-form" method="POST" action="" class="space-y-4 mt-4">
                        @csrf
                        <div class="rounded-xl border border-line bg-surface-soft/40 p-4 space-y-2 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-text-muted">No. Transaksi</span>
                                <span id="action-info-code" class="font-mono font-bold text-ink">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-text-muted">Peminjam</span>
                                <span id="action-info-member" class="font-semibold text-ink">-</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-text-muted">Judul Buku</span>
                                <span id="action-info-book" class="font-semibold text-ink line-clamp-1 max-w-[200px]">-</span>
                            </div>
                        </div>

                        <!-- Optional Rejection Reason Field -->
                        <div id="reject-reason-box" class="hidden">
                            <label for="rejection_reason" class="block text-xs font-semibold text-ink mb-1">
                                Alasan Penolakan <span class="text-danger-fg">*</span>
                            </label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" placeholder="Tuliskan alasan penolakan pengajuan..." class="w-full px-3 py-2 rounded-control border border-line text-xs bg-surface text-ink focus:outline-2 focus:outline-focus"></textarea>
                        </div>

                        <p id="general-action-desc" class="text-xs text-text-muted leading-relaxed"></p>

                        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                            <button type="button" id="btn-cancel-general-action" class="min-h-10 px-4 py-2 rounded-control border border-line text-xs font-semibold text-ink hover:bg-surface-soft transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" id="btn-submit-general-action" class="min-h-10 px-5 py-2 rounded-control bg-brand-600 text-white text-xs font-bold shadow-xs hover:bg-brand-700 transition-colors cursor-pointer">
                                Konfirmasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- QUICK RETURN CONFIRMATION MODAL                          -->
    <!-- ======================================================== -->
    <div id="quick-return-modal" class="modal-animated fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="quick-return-title">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
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
                            <p class="text-xs text-text-muted mt-0.5">Penerimaan fisik buku perpustakaan.</p>
                        </div>
                    </div>
                    <button type="button" id="btn-close-quick-return" class="text-text-muted hover:text-ink text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form id="quick-return-form" method="POST" action="" class="space-y-4 mt-4">
                    @csrf
                    <div class="rounded-xl border border-line bg-surface-soft/40 p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted">No. Transaksi</span>
                            <span id="return-info-code" class="font-mono font-bold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted">Nama Peminjam</span>
                            <span id="return-info-member" class="font-semibold text-ink">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-text-muted">Total Eksemplar</span>
                            <span id="return-info-quantity" class="font-bold text-brand-600 dark:text-brand-400">-</span>
                        </div>
                    </div>

                    <p class="text-xs text-text-muted leading-relaxed">
                        Dengan menekan tombol di bawah, status transaksi ditandai <strong>Sudah Dikembalikan</strong> dan stok eksemplar fisik buku akan otomatis kembali bertambah.
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
            // Direct Borrow Modal
            const directModal = document.getElementById('direct-borrow-modal');
            const openDirectBtn = document.getElementById('btn-open-direct-borrow-modal');
            const closeDirectBtn = document.getElementById('btn-close-direct-borrow');
            const cancelDirectBtn = document.getElementById('btn-cancel-direct-borrow');
            const directBackdrop = document.getElementById('direct-borrow-backdrop');

            if (openDirectBtn && directModal) {
                openDirectBtn.addEventListener('click', () => directModal.classList.remove('hidden'));
            }
            const closeDirect = () => { if (directModal) directModal.classList.add('hidden'); };
            if (closeDirectBtn) closeDirectBtn.addEventListener('click', closeDirect);
            if (cancelDirectBtn) cancelDirectBtn.addEventListener('click', closeDirect);
            if (directBackdrop) directBackdrop.addEventListener('click', closeDirect);

            // General Action Modal (Approve, Reject, Handover, Cancel)
            const actionModal = document.getElementById('general-action-modal');
            const actionForm = document.getElementById('general-action-form');
            const actionTitle = document.getElementById('general-action-title');
            const actionDesc = document.getElementById('general-action-desc');
            const actionSubmit = document.getElementById('btn-submit-general-action');
            const actionInfoCode = document.getElementById('action-info-code');
            const actionInfoMember = document.getElementById('action-info-member');
            const actionInfoBook = document.getElementById('action-info-book');
            const rejectReasonBox = document.getElementById('reject-reason-box');
            const closeActionBtn = document.getElementById('btn-close-general-action');
            const cancelActionBtn = document.getElementById('btn-cancel-general-action');
            const actionBackdrop = document.getElementById('general-action-backdrop');

            function openActionModal(opts) {
                if (!actionModal) return;
                actionForm.action = opts.url;
                actionTitle.textContent = opts.title;
                actionDesc.textContent = opts.desc;
                actionSubmit.textContent = opts.btnLabel;
                actionSubmit.className = 'min-h-10 px-5 py-2 rounded-control text-white text-xs font-bold shadow-xs transition-colors cursor-pointer ' + opts.btnClass;

                actionInfoCode.textContent = opts.code || '-';
                actionInfoMember.textContent = opts.member || '-';
                actionInfoBook.textContent = opts.book || '-';

                if (rejectReasonBox) {
                    rejectReasonBox.classList.toggle('hidden', !opts.isReject);
                    const rejectTextarea = document.getElementById('rejection_reason');
                    if (rejectTextarea) rejectTextarea.required = opts.isReject;
                }

                actionModal.classList.remove('hidden');
            }

            const closeAction = () => { if (actionModal) actionModal.classList.add('hidden'); };
            if (closeActionBtn) closeActionBtn.addEventListener('click', closeAction);
            if (cancelActionBtn) cancelActionBtn.addEventListener('click', closeAction);
            if (actionBackdrop) actionBackdrop.addEventListener('click', closeAction);

            // Wire Approve buttons
            document.querySelectorAll('.btn-action-approve').forEach(btn => {
                btn.addEventListener('click', function () {
                    openActionModal({
                        url: this.dataset.actionUrl,
                        code: this.dataset.loanCode,
                        member: this.dataset.memberName,
                        book: this.dataset.bookTitle,
                        title: 'Setujui Pengajuan Peminjaman',
                        desc: 'Menyetujui pengajuan ini akan mereservasi 1 eksemplar buku fisik. Anggota dapat mengambil buku di perpustakaan.',
                        btnLabel: 'Ya, Setujui',
                        btnClass: 'bg-brand-600 hover:bg-brand-700',
                        isReject: false
                    });
                });
            });

            // Wire Reject buttons
            document.querySelectorAll('.btn-action-reject').forEach(btn => {
                btn.addEventListener('click', function () {
                    openActionModal({
                        url: this.dataset.actionUrl,
                        code: this.dataset.loanCode,
                        member: this.dataset.memberName,
                        book: this.dataset.bookTitle,
                        title: 'Tolak Pengajuan Peminjaman',
                        desc: 'Pengajuan akan ditandai ditolak dan stok buku tidak terpengaruh.',
                        btnLabel: 'Tolak Pengajuan',
                        btnClass: 'bg-danger-fg hover:opacity-95',
                        isReject: true
                    });
                });
            });

            // Wire Handover buttons
            document.querySelectorAll('.btn-action-handover').forEach(btn => {
                btn.addEventListener('click', function () {
                    openActionModal({
                        url: this.dataset.actionUrl,
                        code: this.dataset.loanCode,
                        member: this.dataset.memberName,
                        book: this.dataset.bookTitle,
                        title: 'Serahkan Buku (Handover)',
                        desc: 'Konfirmasi bahwa buku fisik telah diserahkan kepada anggota. Masa aktif peminjaman 7 hari kalender dimulai dari saat ini.',
                        btnLabel: 'Konfirmasi Serahkan Buku',
                        btnClass: 'bg-emerald-600 hover:bg-emerald-700',
                        isReject: false
                    });
                });
            });

            // Wire Cancel buttons
            document.querySelectorAll('.btn-action-cancel').forEach(btn => {
                btn.addEventListener('click', function () {
                    openActionModal({
                        url: this.dataset.actionUrl,
                        code: this.dataset.loanCode,
                        title: 'Batalkan Reservasi Peminjaman',
                        desc: 'Reservasi buku yang disetujui akan dibatalkan, dan 1 eksemplar dikembalikan ke stok tersedia.',
                        btnLabel: 'Batalkan Reservasi',
                        btnClass: 'bg-danger-fg hover:opacity-95',
                        isReject: false
                    });
                });
            });

            // Return Modal Logic
            const returnModal = document.getElementById('quick-return-modal');
            const returnBackdrop = document.getElementById('quick-return-backdrop');
            const returnCloseBtn = document.getElementById('btn-close-quick-return');
            const returnCancelBtn = document.getElementById('btn-cancel-quick-return');
            const returnForm = document.getElementById('quick-return-form');
            const returnSubmit = document.getElementById('btn-submit-quick-return');
            const returnInfoCode = document.getElementById('return-info-code');
            const returnInfoMember = document.getElementById('return-info-member');
            const returnInfoQuantity = document.getElementById('return-info-quantity');

            document.querySelectorAll('.btn-open-return-modal').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (returnForm) returnForm.action = this.dataset.actionUrl;
                    if (returnInfoCode) returnInfoCode.textContent = this.dataset.loanCode || '-';
                    if (returnInfoMember) returnInfoMember.textContent = this.dataset.memberName || '-';
                    if (returnInfoQuantity) returnInfoQuantity.textContent = (this.dataset.quantity || '1') + ' eksemplar';
                    if (returnModal) returnModal.classList.remove('hidden');
                });
            });

            const closeReturn = () => { if (returnModal) returnModal.classList.add('hidden'); };
            if (returnCloseBtn) returnCloseBtn.addEventListener('click', closeReturn);
            if (returnCancelBtn) returnCancelBtn.addEventListener('click', closeReturn);
            if (returnBackdrop) returnBackdrop.addEventListener('click', closeReturn);

            if (returnForm && returnSubmit) {
                returnForm.addEventListener('submit', function () {
                    returnSubmit.disabled = true;
                    returnSubmit.textContent = 'Memproses...';
                    returnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>

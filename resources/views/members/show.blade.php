<x-layouts.app :title="$member->name">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('members.index') }}" class="hover:text-ink">Anggota</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Detail Anggota</span>
    </x-slot:breadcrumbs>

    <!-- SCR-011: Member Detail -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <p class="font-mono text-xs font-semibold text-brand-600">{{ $member->member_code }}</p>
            <h1 class="mt-0.5 text-2xl sm:text-[28px] font-bold text-ink leading-tight">{{ $member->name }}</h1>
            <p class="mt-1 text-sm text-text-muted">Profil anggota peminjam dan riwayat sirkulasi buku.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('members.edit', $member) }}" class="min-h-11 inline-flex items-center justify-center rounded-control bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                Edit Anggota
            </a>
            <button type="button"
                    data-archive-action="{{ route('members.destroy', $member) }}"
                    data-archive-title="Arsipkan Anggota?"
                    data-archive-desc="Arsipkan anggota {{ $member->name }} ({{ $member->member_code }})? Pengarsipan ditolak apabila anggota masih memiliki pinjaman aktif."
                    data-archive-label="Arsipkan Anggota"
                    class="min-h-11 inline-flex items-center justify-center rounded-control border border-danger-fg/30 bg-danger-bg px-4 py-2.5 text-sm font-semibold text-danger-fg hover:opacity-90 cursor-pointer">
                Arsipkan
            </button>
            <a href="{{ route('members.index') }}" class="min-h-11 inline-flex items-center justify-center rounded-control border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-ink hover:bg-surface-soft">
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Details -->
        <section aria-labelledby="member-profile-heading" class="bg-surface rounded-card border border-line p-6 space-y-4">
            <h2 id="member-profile-heading" class="text-base font-semibold text-ink border-b border-line pb-3">Data Identitas & Kontak</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Kode Anggota</dt>
                    <dd class="mt-0.5 font-mono font-semibold text-ink">{{ $member->member_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Nama Lengkap</dt>
                    <dd class="mt-0.5 font-medium text-ink">{{ $member->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Email</dt>
                    <dd class="mt-0.5 text-ink">{{ $member->email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Nomor Telepon</dt>
                    <dd class="mt-0.5 text-ink">{{ $member->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-text-muted">Alamat</dt>
                    <dd class="mt-0.5 text-ink whitespace-pre-line">{{ $member->address ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <!-- Borrowing History -->
        <section aria-labelledby="member-loans-heading" class="lg:col-span-2 bg-surface rounded-card border border-line overflow-hidden">
            <div class="p-5 border-b border-line flex items-center justify-between gap-2">
                <h2 id="member-loans-heading" class="text-base font-semibold text-ink">Riwayat Peminjaman Anggota</h2>
                <a href="{{ route('borrowings.create', ['member_id' => $member->id]) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                    + Catat Peminjaman
                </a>
            </div>

            @if ($member->borrowings->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm font-medium text-ink">Anggota ini belum memiliki riwayat peminjaman.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-soft border-b border-line text-xs font-semibold text-text-muted">
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Kode Transaksi</th>
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Tanggal Pinjam</th>
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Jatuh Tempo</th>
                                <th scope="col" class="py-3 px-4 text-right whitespace-nowrap">Jumlah</th>
                                <th scope="col" class="py-3 px-4 whitespace-nowrap">Status</th>
                                <th scope="col" class="py-3 px-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-sm">
                            @foreach ($member->borrowings as $loan)
                                <tr class="hover:bg-surface-soft/50">
                                    <td class="py-3 px-4 font-mono text-xs font-semibold text-ink whitespace-nowrap">{{ $loan->transaction_code }}</td>
                                    <td class="py-3 px-4 text-text-muted tabular-nums whitespace-nowrap">{{ $loan->borrowed_at?->timezone('Asia/Jakarta')->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-text-muted tabular-nums whitespace-nowrap">{{ $loan->due_at?->format('d M Y') }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums whitespace-nowrap">{{ $loan->total_quantity }} eksemplar</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if ($loan->isBorrowed())
                                                <x-status-badge variant="info">Dipinjam</x-status-badge>
                                                @if ($loan->isOverdue())
                                                    <x-status-badge variant="warning">Terlambat</x-status-badge>
                                                @endif
                                            @else
                                                <x-status-badge variant="success">Dikembalikan</x-status-badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('borrowings.show', $loan) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>

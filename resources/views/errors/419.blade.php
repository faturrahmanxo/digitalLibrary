<x-layouts.guest title="Sesi Berakhir (419)">
    <div class="bg-surface rounded-card border border-line p-8 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-warning-bg text-warning-fg flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-ink">Sesi Anda berakhir. Silakan masuk kembali untuk melanjutkan.</h1>
        <p class="text-sm text-text-muted">Demi keamanan transaksi, permintaan yang dikirim saat sesi kedaluwarsa tidak diproses.</p>
        <div class="pt-2">
            <a href="{{ route('login') }}" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">
                Ke Halaman Masuk
            </a>
        </div>
    </div>
</x-layouts.guest>

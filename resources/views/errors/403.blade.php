<x-layouts.guest title="Akses Ditolak (403)">
    <div class="bg-surface rounded-card border border-line p-8 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-danger-bg text-danger-fg flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-ink">Anda tidak memiliki izin untuk mengakses halaman ini.</h1>
        <p class="text-sm text-text-muted">Tindakan atau halaman ini hanya dapat diakses oleh peran yang berwenang.</p>
        <div class="pt-2">
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-layouts.guest>

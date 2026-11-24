<x-layouts.app :title="$isEdit ? 'Edit Rak Buku' : 'Tambah Rak Buku'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('shelves.index') }}" class="hover:text-ink">Rak Buku</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Rak' : 'Tambah Rak' }}</span>
    </x-slot:breadcrumbs>

    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Data Rak Buku' : 'Tambah Rak Buku Baru' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui informasi kode, nama, atau lokasi rak buku fisik.' : 'Tambahkan rak baru untuk penyimpanan koleksi buku fisik di perpustakaan.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('shelves.update', $shelf) : route('shelves.store') }}" class="bg-surface rounded-card border border-line p-6 sm:p-7 shadow-xs space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="code" class="block text-sm font-semibold text-ink mb-1.5">
                    Kode Rak <span class="text-danger-fg">*</span>
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    required
                    value="{{ old('code', $shelf->code) }}"
                    placeholder="Contoh: RAK-A1"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('code') ? 'border-danger-fg' : 'border-line' }} text-sm font-mono uppercase bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('code')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-1.5">
                    Nama Rak <span class="text-danger-fg">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    value="{{ old('name', $shelf->name) }}"
                    placeholder="Contoh: Rak Sejarah & Budaya"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('name')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location" class="block text-sm font-semibold text-ink mb-1.5">
                    Lokasi Fisik
                </label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    value="{{ old('location', $shelf->location) }}"
                    placeholder="Contoh: Lantai 2, Sayap Barat"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('location') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('location')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-ink mb-1.5">
                    Keterangan (Opsional)
                </label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Catatan kapasitas atau keterangan khusus rak..."
                    class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('notes') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >{{ old('notes', $shelf->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ route('shelves.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-sm font-semibold text-white shadow-xs cursor-pointer">
                    Simpan Rak
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

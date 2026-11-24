<x-layouts.app :title="$isEdit ? 'Edit Penerbit' : 'Tambah Penerbit'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publishers.index') }}" class="hover:text-ink">Penerbit</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Penerbit' : 'Tambah Penerbit' }}</span>
    </x-slot:breadcrumbs>

    <div class="max-w-2xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Data Penerbit' : 'Tambah Penerbit Baru' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui informasi kontak dan data profil penerbit buku.' : 'Tambahkan penerbit baru ke dalam direktori referensi katalog Litera.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('publishers.update', $publisher) : route('publishers.store') }}" class="bg-surface rounded-card border border-line p-6 sm:p-7 shadow-xs space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-1.5">
                    Nama Penerbit <span class="text-danger-fg">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    value="{{ old('name', $publisher->name) }}"
                    placeholder="Contoh: Balai Pustaka"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('name')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="city" class="block text-sm font-semibold text-ink mb-1.5">
                    Kota / Negara
                </label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    value="{{ old('city', $publisher->city) }}"
                    placeholder="Contoh: Jakarta, Indonesia"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('city') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('city')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="website" class="block text-sm font-semibold text-ink mb-1.5">
                    Website Resmi (Opsional)
                </label>
                <input
                    type="url"
                    id="website"
                    name="website"
                    value="{{ old('website', $publisher->website) }}"
                    placeholder="https://balaipustaka.co.id"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('website') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >
                @error('website')
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
                    placeholder="Catatan tambahan mengenai penerbit..."
                    class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('notes') ? 'border-danger-fg' : 'border-line' }} text-sm bg-surface text-ink focus:outline-2 focus:outline-focus"
                >{{ old('notes', $publisher->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ route('publishers.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2 rounded-control bg-brand-600 hover:bg-brand-700 text-sm font-semibold text-white shadow-xs cursor-pointer">
                    Simpan Penerbit
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

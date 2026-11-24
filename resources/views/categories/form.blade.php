<x-layouts.app :title="$isEdit ? 'Edit Kategori' : 'Tambah Kategori'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('categories.index') }}" class="hover:text-ink">Kategori</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}</span>
    </x-slot:breadcrumbs>

    <!-- SCR-006: Category Create / Edit (Admin Only) -->
    <div class="max-w-xl">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold text-ink leading-tight">
                {{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $isEdit ? 'Perbarui nama dan keterangan kategori buku.' : 'Tambahkan kategori baru untuk mengelompokkan koleksi buku.' }}
            </p>
        </div>

        <form method="POST" action="{{ $isEdit ? route('categories.update', $category) : route('categories.store') }}" class="bg-surface rounded-card border border-line p-6 space-y-5" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <p class="text-xs text-text-muted">Bidang dengan tanda <span class="text-danger-fg font-semibold">*</span> wajib diisi.</p>

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-1.5">
                    Nama Kategori <span class="text-danger-fg" aria-hidden="true">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    value="{{ old('name', $category->name) }}"
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    @if($errors->has('name')) aria-describedby="name-error" @endif
                    placeholder="Contoh: Sejarah Nusantara"
                    class="w-full min-h-11 px-3.5 py-2 rounded-control border {{ $errors->has('name') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                >
                @error('name')
                    <p id="name-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-ink mb-1.5">
                    Deskripsi (Opsional)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                    @if($errors->has('description')) aria-describedby="description-error" @endif
                    placeholder="Keterangan singkat cakupan buku dalam kategori ini..."
                    class="w-full px-3.5 py-2.5 rounded-control border {{ $errors->has('description') ? 'border-danger-fg' : 'border-line' }} bg-surface text-ink text-sm focus:outline-2 focus:outline-focus"
                >{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p id="description-error" class="mt-1.5 text-xs font-medium text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <a href="{{ route('categories.index') }}" class="min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-control border border-line bg-surface text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-control bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 cursor-pointer">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

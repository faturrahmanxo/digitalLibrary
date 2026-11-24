<x-layouts.app :title="($isEdit ? 'Edit' : 'Tambah') . ' Volume Novel'">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.index') }}" class="hover:text-ink">Buku</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.volumes.index', $book) }}" class="hover:text-ink">Volume {{ $book->title }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">{{ $isEdit ? 'Edit' : 'Tambah' }}</span>
    </x-slot:breadcrumbs>

    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-ink">{{ $isEdit ? 'Edit Volume' : 'Tambah Volume' }}</h1>
            <p class="mt-1 text-sm text-text-muted">{{ $book->title }} · Lisensi digital dikelola terpisah per volume.</p>
        </div>

        <form
            method="POST"
            action="{{ $isEdit ? route('books.volumes.update', [$book, $volume]) : route('books.volumes.store', $book) }}"
            enctype="multipart/form-data"
            class="space-y-5 rounded-card border border-line bg-surface p-5"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="volume_number" class="mb-1.5 block text-sm font-semibold text-ink">Nomor Volume</label>
                    <input
                        id="volume_number"
                        name="volume_number"
                        type="number"
                        min="1"
                        max="65535"
                        required
                        value="{{ old('volume_number', $volume->volume_number) }}"
                        class="min-h-11 w-full rounded-control border {{ $errors->has('volume_number') ? 'border-danger-fg' : 'border-line' }} bg-surface px-3.5 py-2 text-sm text-ink focus:outline-2 focus:outline-focus"
                    >
                    @error('volume_number')
                        <p class="mt-1 text-xs text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="title" class="mb-1.5 block text-sm font-semibold text-ink">Judul Volume (opsional)</label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $volume->title) }}"
                        class="min-h-11 w-full rounded-control border border-line bg-surface px-3.5 py-2 text-sm text-ink focus:outline-2 focus:outline-focus"
                    >
                    @error('title')
                        <p class="mt-1 text-xs text-danger-fg">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="digital_license_count" class="mb-1.5 block text-sm font-semibold text-ink">Jumlah Lisensi Volume</label>
                <input
                    id="digital_license_count"
                    name="digital_license_count"
                    type="number"
                    min="0"
                    max="1000000"
                    step="1"
                    required
                    value="{{ old('digital_license_count', $volume->digital_license_count) }}"
                    class="min-h-11 w-full rounded-control border {{ $errors->has('digital_license_count') ? 'border-danger-fg' : 'border-line' }} bg-surface px-3.5 py-2 text-sm tabular-nums text-ink focus:outline-2 focus:outline-focus"
                >
                @error('digital_license_count')
                    <p class="mt-1 text-xs text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="digital_file" class="mb-1.5 block text-sm font-semibold text-ink">File PDF / EPUB</label>
                <input
                    id="digital_file"
                    name="digital_file"
                    type="file"
                    accept=".pdf,.epub,application/pdf,application/epub+zip"
                    class="w-full text-xs text-text-muted file:mr-3 file:rounded-control file:border file:border-line file:bg-surface-soft file:px-3 file:py-2 file:text-xs file:font-semibold file:text-ink"
                >
                @if (filled($volume->digital_file_path))
                    <p class="mt-1 text-xs text-text-muted">File digital privat sudah tersimpan.</p>
                @endif
                @error('digital_file')
                    <p class="mt-1 text-xs text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3 border-t border-line pt-4">
                <label class="flex items-start gap-2.5 text-sm text-ink">
                    <input type="hidden" name="digital_lending_allowed" value="0">
                    <input
                        type="checkbox"
                        name="digital_lending_allowed"
                        value="1"
                        @checked(old('digital_lending_allowed', $volume->digital_lending_allowed))
                        class="mt-0.5 rounded border-line text-brand-600 focus:ring-focus"
                    >
                    <span>Izin distribusi dan model lisensi mengizinkan peminjaman digital.</span>
                </label>
                @error('digital_lending_allowed')
                    <p class="text-xs text-danger-fg">{{ $message }}</p>
                @enderror

                <label class="flex items-start gap-2.5 text-sm text-ink">
                    <input type="hidden" name="is_published" value="0">
                    <input
                        type="checkbox"
                        name="is_published"
                        value="1"
                        @checked(old('is_published', $volume->is_published))
                        class="mt-0.5 rounded border-line text-brand-600 focus:ring-focus"
                    >
                    <span>Terbitkan volume ke halaman publik.</span>
                </label>
                @error('is_published')
                    <p class="text-xs text-danger-fg">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('books.volumes.index', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-control border border-line px-4 py-2 text-sm font-semibold text-ink hover:bg-surface-soft">
                    Batal
                </a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-control bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    Simpan Volume
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

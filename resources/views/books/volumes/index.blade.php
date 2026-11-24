<x-layouts.app :title="'Volume Novel — ' . $book->title">
    <x-slot:breadcrumbs>
        <a href="{{ route('dashboard') }}" class="hover:text-ink">Dashboard</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.index') }}" class="hover:text-ink">Buku</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('books.manage', $book) }}" class="hover:text-ink">{{ $book->title }}</a>
        <span class="mx-1.5">/</span>
        <span class="font-medium text-ink">Volume</span>
    </x-slot:breadcrumbs>

    @if (session('success'))
        <p role="status" class="mb-5 rounded-control border border-line bg-surface p-3 text-sm text-ink">{{ session('success') }}</p>
    @endif

    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-ink">Volume Novel</h1>
            <p class="mt-1 text-sm text-text-muted">{{ $book->title }} · {{ $book->author }}</p>
        </div>
        <a href="{{ route('books.volumes.create', $book) }}" class="inline-flex min-h-10 items-center justify-center rounded-control bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            Tambah Volume
        </a>
    </div>

    <div class="overflow-hidden rounded-card border border-line bg-surface">
        @if ($volumes->isEmpty())
            <div class="p-8 text-center">
                <p class="font-semibold text-ink">Belum ada volume.</p>
                <p class="mt-1 text-sm text-text-muted">Tambahkan volume dan atur lisensi digitalnya secara terpisah.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-line bg-surface-soft text-xs text-text-muted">
                        <tr>
                            <th class="px-4 py-3">Volume</th>
                            <th class="px-4 py-3">Publikasi</th>
                            <th class="px-4 py-3">Lisensi</th>
                            <th class="px-4 py-3">File / Izin</th>
                            <th class="px-4 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($volumes as $volume)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-ink">Volume {{ $volume->volume_number }}</p>
                                    @if (filled($volume->title))
                                        <p class="mt-0.5 text-xs text-text-muted">{{ $volume->title }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-text-muted">{{ $volume->is_published ? 'Terbit' : 'Draf' }}</td>
                                <td class="px-4 py-3 tabular-nums text-ink">
                                    {{ $volume->digital_access_available }} tersedia / {{ $volume->digital_license_count }} total
                                    <span class="block text-xs text-text-muted">{{ $volume->active_digital_loan_count }} pinjaman aktif</span>
                                </td>
                                <td class="px-4 py-3 text-text-muted">
                                    {{ filled($volume->digital_file_path) ? 'File privat tersimpan' : 'File belum tersedia' }}
                                    <span class="block text-xs">{{ $volume->digital_lending_allowed ? 'Izin distribusi aktif' : 'Izin distribusi nonaktif' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('books.volumes.edit', [$book, $volume]) }}" class="rounded-control border border-line px-3 py-1.5 text-xs font-semibold text-ink hover:bg-surface-soft">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('books.volumes.destroy', [$book, $volume]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-control border border-line px-3 py-1.5 text-xs font-semibold text-text-muted hover:bg-surface-soft">
                                                Arsipkan
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.app>

<x-layouts.app title="Buku Favorit">
    <x-slot:breadcrumbs>
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-ink">Beranda Kampus</a>
            <span>/</span>
            <span class="text-ink font-semibold">Buku Favorit</span>
        </div>
    </x-slot:breadcrumbs>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-ink tracking-tight">Koleksi Favorit Saya</h1>
                <p class="text-sm text-text-muted mt-1">Daftar buku dan karya ilmiah yang Anda simpan untuk dibaca atau dipinjam.</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition-all">
                Jelajahi Katalog Kampus &rarr;
            </a>
        </div>

        @if ($books->isEmpty())
            <div class="rounded-3xl border border-line bg-surface p-12 text-center max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-950/40 text-brand-600 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ink">Belum Ada Buku Favorit</h3>
                <p class="text-xs text-text-muted mt-1 mb-6">
                    Temukan buku menarik di katalog dan klik ikon hati untuk menyimpannya ke daftar bacaan favorit Anda.
                </p>
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs">
                    Cari Buku Sekarang
                </a>
            </div>
        @else
            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($books as $book)
                    <div class="card-interactive rounded-2xl border border-line bg-surface p-5 flex flex-col justify-between shadow-xs group">
                        <div>
                            <!-- Cover Artwork -->
                            <div class="aspect-3/4 rounded-xl overflow-hidden bg-brand-50 dark:bg-brand-950/40 border border-line mb-4 relative flex items-center justify-center">
                                @if ($book->cover_path)
                                    <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="text-center p-3">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-brand-600 text-white mb-2">{{ $book->category?->name ?? 'Akademik' }}</span>
                                        <p class="text-xs font-bold text-ink line-clamp-2">{{ $book->title }}</p>
                                        <p class="text-[10px] text-text-muted mt-1">{{ $book->author }}</p>
                                    </div>
                                @endif

                                <!-- Remove Favorite Button -->
                                <form method="POST" action="{{ route('books.favorite', $book) }}" class="absolute top-2 right-2">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl bg-surface/90 hover:bg-surface backdrop-blur-md text-red-500 shadow-sm border border-line" title="Hapus dari favorit">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 mb-1">
                                {{ $book->category?->name ?? 'Umum' }}
                            </span>
                            <h3 class="text-sm font-bold text-ink line-clamp-2 leading-snug group-hover:text-brand-600 transition-colors">{{ $book->title }}</h3>
                            <p class="text-xs text-text-muted mt-1">{{ $book->author }} ({{ $book->publication_year }})</p>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4 border-t border-line mt-4 flex items-center gap-2">
                            <a href="{{ route('books.read', $book) }}" class="flex-1 py-2 rounded-xl border border-line bg-surface hover:bg-surface-soft text-ink text-xs font-semibold text-center transition-colors">
                                Baca Buku
                            </a>
                            <a href="{{ route('books.show', $book) }}" class="px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold text-center shadow-xs transition-colors">
                                Pinjam
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>

@props(['book', 'href' => null])

@php
    $href = $href ?? route('books.show', $book);
    $isPhysical = $book->isPhysical();
    $categoryName = $book->category?->name ?? ($isPhysical ? 'Koleksi Fisik' : 'Pustaka Digital');
    
    // Status text & style logic
    if ($isPhysical) {
        $copies = (int) ($book->available_copies ?? $book->stock_available ?? 0);
        if ($copies > 3) {
            $statusLabel = 'Tersedia (' . $copies . ')';
            $statusClass = 'text-emerald-700 dark:text-emerald-400 font-semibold';
            $dotClass = 'bg-emerald-500';
        } elseif ($copies > 0) {
            $statusLabel = 'Stok Terbatas (' . $copies . ')';
            $statusClass = 'text-amber-700 dark:text-amber-400 font-semibold';
            $dotClass = 'bg-amber-500';
        } else {
            $statusLabel = 'Sedang Dipinjam';
            $statusClass = 'text-rose-600 dark:text-rose-400 font-semibold';
            $dotClass = 'bg-rose-500';
        }
    } else {
        if ($book->read_url && ($book->download_url || $book->hasDownload())) {
            $statusLabel = 'Baca & Unduh';
        } elseif ($book->read_url) {
            $statusLabel = 'Baca Online';
        } elseif ($book->download_url || $book->hasDownload()) {
            $statusLabel = 'Unduh';
        } else {
            $statusLabel = 'Akses Digital';
        }
        $statusClass = 'text-brand-600 dark:text-brand-400 font-semibold';
        $dotClass = 'bg-brand-500';
    }

    $descriptionText = $book->synopsis ?: ($book->description ?: ($isPhysical ? 'Koleksi buku referensi perpustakaan yang dapat dipinjam di loket sirkulasi.' : 'Buku digital yang dapat diakses secara daring atau diunduh untuk kebutuhan riset.'));
@endphp

<a
    {{ $attributes->merge(['class' => 'group card-interactive bg-surface rounded-2xl border border-line p-3.5 sm:p-4 shadow-xs hover:border-brand-400 flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus cursor-pointer w-full']) }}
    href="{{ $href }}"
    title="{{ $book->title }}"
>
    <div>
        <!-- Cover Book (Aspect 4/5 - Unified Style) -->
        <div class="w-full aspect-[4/5] rounded-xl bg-gradient-to-tr from-brand-800 via-brand-600 to-brand-400 text-white p-3.5 sm:p-4 flex flex-col justify-between shadow-xs mb-3.5 relative overflow-hidden group-hover:scale-[1.03] transition-transform duration-300 ease-out {{ !filled($book->cover_path) ? 'book-cover-fallback' : '' }}" role="img" aria-label="Sampul {{ $book->collection_type === 'novel' ? 'novel' : ($isPhysical ? 'buku' : 'e-book') }} tidak tersedia untuk {{ $book->title }}">
            @if (filled($book->cover_path))
                <img src="{{ asset('storage/' . $book->cover_path) }}" alt="Sampul {{ $book->collection_type === 'novel' ? 'novel' : ($isPhysical ? 'buku' : 'e-book') }} {{ $book->title }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            @endif
            
            <!-- Category Badge Inside Cover (only if non-empty category) -->
            @if (filled($book->category?->name))
                <span class="relative z-10 text-[10px] font-bold uppercase tracking-wider bg-white/20 backdrop-blur-md px-2 py-0.5 rounded w-max border border-white/20 truncate max-w-full text-white">
                    {{ $categoryName }}
                </span>
            @else
                <span></span>
            @endif

            <div class="relative z-10">
                <p class="text-[11px] uppercase tracking-wider text-white/70 font-semibold mb-0.5">Litera</p>
                <p class="text-xs font-bold leading-snug line-clamp-2 text-white">
                    <span class="sr-only">LT</span>
                    {{ $book->title }}
                </p>
            </div>
        </div>

        <!-- Card Body Content -->
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-ink leading-snug line-clamp-1 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                {{ $book->title }}
            </h3>
            
            <p class="text-xs text-text-muted line-clamp-1 flex items-center gap-1.5">
                <span>{{ $book->author ?: 'Litera' }}</span>
                @if ($book->publication_year)
                    <span>·</span>
                    <span class="font-year font-bold text-xs sm:text-sm text-ink/90 tabular-nums">{{ $book->publication_year }}</span>
                @endif
            </p>

            <!-- Deskripsi Singkat 2-3 Baris -->
            <p class="text-[11px] text-text-muted line-clamp-2 leading-relaxed mt-1 font-sans">
                {{ $descriptionText }}
            </p>
        </div>
    </div>

    <!-- Bottom Row: Status / Availability & Detail Link -->
    <div class="pt-3 border-t border-line mt-3 flex items-center justify-between text-xs font-sans">
        <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} shrink-0"></span>
            <span class="text-[11px] {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <span class="font-semibold text-brand-600 dark:text-brand-400 group-hover:underline flex items-center gap-0.5">
            <span>Detail</span>
            <span>&rarr;</span>
        </span>
    </div>
</a>

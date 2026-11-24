@props(['book', 'href', 'coverLabel' => 'e-book', 'showAuthor' => false])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group/book-card flex h-full min-w-0 flex-col focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus']) }}
>
    <div class="group/cover book-cover-interactive card-interactive relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-surface-soft">
        @if (filled($book->category?->name))
            <span class="absolute left-2 top-2 z-10 max-w-[calc(100%-1rem)] truncate rounded-full bg-brand-400/90 px-2.5 py-1.5 text-[11px] font-medium leading-none text-brand-900 shadow-xs">
                {{ $book->category->name }}
            </span>
        @endif

        @if ($book->cover_path)
            <img
                src="{{ asset('storage/' . $book->cover_path) }}"
                alt="Sampul {{ $coverLabel }} {{ $book->title }}"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition-transform duration-200 motion-safe:group-hover/cover:scale-[1.03] motion-reduce:transform-none"
            >
        @else
            <div class="book-cover-fallback relative flex h-full w-full items-center justify-center overflow-hidden text-white" role="img" aria-label="Sampul {{ $coverLabel }} tidak tersedia untuk {{ $book->title }}">
                <span class="font-serif text-5xl font-bold tracking-wide text-white" aria-hidden="true">LT</span>
            </div>
        @endif
    </div>

    <div class="flex min-h-20 flex-1 flex-col pt-2.5">
        <h3 class="line-clamp-2 break-words text-sm font-semibold leading-snug text-ink transition-colors duration-200 group-hover/book-card:text-brand-600 dark:group-hover/book-card:text-brand-400">
            {{ $book->title }}
        </h3>
        @if ($showAuthor && filled($book->author))
            <p class="mt-1 truncate text-xs text-text-muted">{{ $book->author }}</p>
        @elseif ($book->publication_year)
            <p class="font-year mt-1 text-sm text-text-muted">{{ $book->publication_year }}</p>
        @endif
    </div>
</a>

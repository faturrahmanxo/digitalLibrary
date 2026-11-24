@props([
    'title' => null,
    'count' => null,
])

<div {{ $attributes->merge(['class' => 'sv lst sv-card']) }}>
    @if ($title || $count !== null)
        <div class="lst__head">
            @if ($title)
                <b>{{ $title }}</b>
            @endif
            @if ($count !== null)
                <span>{{ $count }}</span>
            @endif
        </div>
    @endif
    <ul class="lst__ul">
        {{ $slot }}
    </ul>
</div>

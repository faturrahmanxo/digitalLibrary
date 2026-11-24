@props([
    'avatarText' => null,
    'avatarVariant' => null, // null, 'b', 'c', 'd', 'brand'
    'status' => 'online', // 'online', 'away', 'busy', 'offline', null
    'title' => '',
    'subtitle' => null,
    'href' => null,
    'actsCount' => 2, // 2 or 3
])

@php
    $avClass = 'lst__av';
    if ($avatarVariant) {
        $avClass .= ' lst__av--' . $avatarVariant;
    }

    $statusClass = 'lst__on';
    if ($status === 'away') {
        $statusClass .= ' lst__on--away';
    } elseif ($status === 'busy') {
        $statusClass .= ' lst__on--busy';
    } elseif ($status === 'offline') {
        $statusClass .= ' lst__on--offline';
    }

    $rowClass = 'lst__row';
    if ($actsCount >= 3) {
        $rowClass .= ' lst__row--3acts';
    }
@endphp

<li {{ $attributes->merge(['class' => $rowClass]) }}>
    @if (isset($actions))
        <div class="lst__acts">
            {{ $actions }}
        </div>
    @endif

    <{{ $href ? 'a href="' . $href . '"' : 'div' }} class="lst__main">
        @if ($avatarText)
            <span class="{{ $avClass }}">
                {{ $avatarText }}
                @if ($status)
                    <i class="{{ $statusClass }}"></i>
                @endif
            </span>
        @endif

        <span class="lst__txt">
            <b>{!! $title !!}</b>
            @if ($subtitle)
                <small>{!! $subtitle !!}</small>
            @endif
        </span>

        @if (isset($badge))
            {{ $badge }}
        @endif
    </{{ $href ? 'a' : 'div' }}>
</li>

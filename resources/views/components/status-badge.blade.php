@props([
    'variant' => 'info', // info | success | warning | danger | neutral
])

@php
    $classes = match ($variant) {
        'success' => 'bg-success-bg text-success-fg border border-success-fg/20',
        'warning' => 'bg-warning-bg text-warning-fg border border-warning-fg/20',
        'danger' => 'bg-danger-bg text-danger-fg border border-danger-fg/20',
        'neutral' => 'bg-surface-soft text-text-muted border border-line',
        default => 'bg-info-bg text-info-fg border border-info-fg/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap shrink-0 leading-normal {$classes}"]) }}>
    {{ $slot }}
</span>

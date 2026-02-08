@props([
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger' => 'bg-red-100 text-red-700',
        'primary' => 'bg-moka-soft text-moka-primary',
        default => 'bg-[#F3ECE2] text-moka-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $slot }}
</span>

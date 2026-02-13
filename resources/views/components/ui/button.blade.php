@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $baseClasses = match ($variant) {
        'secondary' => 'moka-btn-secondary',
        'success' => 'moka-btn-success',
        'danger' => 'moka-btn-danger',
        default => 'moka-btn',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</button>
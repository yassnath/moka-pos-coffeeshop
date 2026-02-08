@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $baseClasses = match ($variant) {
        'secondary' => 'moka-btn-secondary',
        'success' => 'moka-btn-success',
        'danger' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-red-300 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 transition duration-200 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200 disabled:cursor-not-allowed disabled:opacity-60',
        default => 'moka-btn',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</button>

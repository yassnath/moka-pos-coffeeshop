@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-moka-primary bg-moka-soft text-start text-base font-medium text-moka-ink focus:bg-moka-soft focus:outline-none focus:text-moka-ink focus:border-moka-accent transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-moka-muted hover:border-moka-line hover:bg-moka-soft hover:text-moka-ink focus:border-moka-line focus:bg-moka-soft focus:text-moka-ink focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
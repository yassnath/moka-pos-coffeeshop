@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-moka-primary text-sm font-medium leading-5 text-moka-ink focus:outline-none focus:border-moka-accent transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-moka-muted hover:text-moka-ink hover:border-moka-line focus:outline-none focus:text-moka-ink focus:border-moka-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
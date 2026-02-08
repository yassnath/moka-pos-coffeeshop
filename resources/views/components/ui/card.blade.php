@props([
    'padding' => 'p-5',
    'glass' => false,
])

<section {{ $attributes->merge(['class' => ($glass ? 'glass-card ' : 'soft-card ').$padding]) }}>
    {{ $slot }}
</section>

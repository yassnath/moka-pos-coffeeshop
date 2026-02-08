@props([
    'name',
    'maxWidth' => '2xl',
])

@php
    $maxWidthValue = [
        'sm' => '24rem',
        'md' => '28rem',
        'lg' => '32rem',
        'xl' => '36rem',
        '2xl' => '42rem',
        '4xl' => '56rem',
    ][$maxWidth] ?? '42rem';
@endphp

<template x-teleport="body">
    <div
        x-cloak
        x-show="{{ $name }}"
        x-on:keydown.escape.window="{{ $name }} = false"
        class="fixed inset-0 z-[120]"
    >
        <div
            x-show="{{ $name }}"
            x-transition.opacity.duration.200ms
            x-on:click="{{ $name }} = false"
            class="absolute inset-0 moka-modal-overlay backdrop-blur-sm"
        ></div>

        <div
            x-show="{{ $name }}"
            x-transition.opacity.duration.200ms
            class="moka-modal-shell absolute w-[calc(100vw-2rem)] overflow-hidden"
            style="left: 50%; top: 50%; transform: translate(-50%, -50%); max-width: {{ $maxWidthValue }};"
        >
            {{ $slot }}
        </div>
    </div>
</template>

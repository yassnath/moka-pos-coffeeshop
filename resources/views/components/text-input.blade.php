@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'moka-input']) }}>

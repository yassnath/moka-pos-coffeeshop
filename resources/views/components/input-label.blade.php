@props(['value'])

<label {{ $attributes->merge(['class' => 'moka-label']) }}>
    {{ $value ?? $slot }}
</label>

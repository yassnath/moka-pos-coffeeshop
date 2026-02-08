@props([
    'label' => null,
    'hint' => null,
    'name' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="moka-label">{{ $label }}</label>
    @endif

    <input name="{{ $name }}" id="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'moka-input']) }}>

    @if($hint)
        <p class="mt-1 text-xs text-moka-muted">{{ $hint }}</p>
    @endif
</div>

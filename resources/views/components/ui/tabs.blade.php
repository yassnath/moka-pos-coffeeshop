@props([
    'active',
    'tabs' => [],
    'param' => 'view',
])

<div class="inline-flex rounded-full border border-moka-line bg-moka-card p-1">
    @foreach($tabs as $key => $label)
        <a
            href="{{ request()->fullUrlWithQuery([$param => $key]) }}"
            class="{{ $active === $key ? 'moka-chip moka-chip-active' : 'moka-chip' }} px-3 py-1.5 text-xs sm:text-sm"
        >
            {{ $label }}
        </a>
    @endforeach
</div>
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl moka-alert-success px-3 py-2 text-sm font-medium']) }}>
        {{ $status }}
    </div>
@endif
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'moka-btn-danger']) }}>
    {{ $slot }}
</button>
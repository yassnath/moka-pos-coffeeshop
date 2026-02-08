<button {{ $attributes->merge(['type' => 'submit', 'class' => 'moka-btn']) }}>
    {{ $slot }}
</button>

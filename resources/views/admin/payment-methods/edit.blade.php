<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Edit Metode Pembayaran</h1>
            <p class="text-sm text-moka-muted">Perbarui nama, kode, dan status metode pembayaran.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}">
            @include('admin.payment-methods._form', ['paymentMethod' => $paymentMethod, 'submitLabel' => 'Simpan Perubahan'])
        </form>
    </x-ui.card>
</x-app-layout>

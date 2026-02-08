<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Tambah Metode Pembayaran</h1>
            <p class="text-sm text-moka-muted">Tambahkan opsi pembayaran baru.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.payment-methods.store') }}">
            @include('admin.payment-methods._form', ['submitLabel' => 'Simpan Metode'])
        </form>
    </x-ui.card>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Tambah Produk</h1>
            <p class="text-sm text-moka-muted">Tambahkan menu baru agar otomatis tersedia di POS kasir.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @include('admin.products._form', ['submitLabel' => 'Simpan Produk'])
        </form>
    </x-ui.card>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Edit Produk</h1>
            <p class="text-sm text-moka-muted">Perbarui data menu, varian, stok, dan gambar produk.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @include('admin.products._form', ['product' => $product, 'submitLabel' => 'Simpan Perubahan'])
        </form>
    </x-ui.card>
</x-app-layout>

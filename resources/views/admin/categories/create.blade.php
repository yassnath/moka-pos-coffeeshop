<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Tambah Kategori</h1>
            <p class="text-sm text-moka-muted">Buat kategori baru untuk pengelompokan menu.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @include('admin.categories._form', ['submitLabel' => 'Simpan Kategori'])
        </form>
    </x-ui.card>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Edit Kategori</h1>
            <p class="text-sm text-moka-muted">Perbarui informasi kategori menu.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @include('admin.categories._form', ['category' => $category, 'submitLabel' => 'Simpan Perubahan'])
        </form>
    </x-ui.card>
</x-app-layout>

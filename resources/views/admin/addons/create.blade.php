<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Tambah Add-on</h1>
            <p class="text-sm text-moka-muted">Buat add-on baru untuk kebutuhan custom order.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.addons.store') }}">
            @include('admin.addons._form', ['submitLabel' => 'Simpan Add-on'])
        </form>
    </x-ui.card>
</x-app-layout>

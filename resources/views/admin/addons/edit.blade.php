<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Edit Add-on</h1>
            <p class="text-sm text-moka-muted">Perbarui detail add-on.</p>
        </div>
    </x-slot>

    <x-ui.card>
        <form method="POST" action="{{ route('admin.addons.update', $addon) }}">
            @include('admin.addons._form', ['addon' => $addon, 'submitLabel' => 'Simpan Perubahan'])
        </form>
    </x-ui.card>
</x-app-layout>

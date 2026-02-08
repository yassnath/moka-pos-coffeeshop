<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Add-on / Topping</h1>
            <p class="text-sm text-moka-muted">Kelola add-on aktif yang bisa dipilih di transaksi POS.</p>
        </div>
        <a href="{{ route('admin.addons.create') }}" class="moka-btn">Tambah Add-on</a>
    </x-slot>

    <x-ui.card padding="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="moka-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addons as $addon)
                        <tr>
                            <td class="font-semibold">{{ $addon->name }}</td>
                            <td class="text-money">Rp {{ number_format((float) $addon->price, 0, ',', '.') }}</td>
                            <td>
                                <x-ui.badge :variant="$addon->is_active ? 'success' : 'warning'">
                                    {{ $addon->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.addons.edit', $addon) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit</a>
                                <form action="{{ route('admin.addons.destroy', $addon) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus add-on ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-moka-muted">Belum ada add-on.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $addons->links() }}
    </div>
</x-app-layout>

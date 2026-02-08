<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Kategori Menu</h1>
            <p class="text-sm text-moka-muted">Kelola kategori produk yang akan tampil di POS kasir.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="moka-btn">Tambah Kategori</a>
    </x-slot>

    <x-ui.card padding="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="moka-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-semibold">{{ $category->name }}</td>
                            <td>
                                <x-ui.badge :variant="$category->is_active ? 'success' : 'warning'">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-sm text-moka-muted">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</x-app-layout>

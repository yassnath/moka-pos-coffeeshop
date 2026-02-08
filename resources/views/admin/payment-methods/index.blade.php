<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Metode Pembayaran</h1>
            <p class="text-sm text-moka-muted">Konfigurasi metode pembayaran aktif untuk checkout kasir.</p>
        </div>
        <a href="{{ route('admin.payment-methods.create') }}" class="moka-btn">Tambah Metode</a>
    </x-slot>

    <x-ui.card padding="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="moka-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentMethods as $paymentMethod)
                        <tr>
                            <td class="font-semibold">{{ $paymentMethod->name }}</td>
                            <td class="uppercase text-moka-muted">{{ $paymentMethod->code }}</td>
                            <td>
                                <x-ui.badge :variant="$paymentMethod->is_active ? 'success' : 'warning'">
                                    {{ $paymentMethod->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.payment-methods.edit', $paymentMethod) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit</a>
                                <form action="{{ route('admin.payment-methods.destroy', $paymentMethod) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus metode bayar ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-moka-muted">Belum ada metode pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $paymentMethods->links() }}
    </div>
</x-app-layout>

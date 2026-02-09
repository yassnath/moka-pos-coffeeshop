<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Riwayat Pesanan Waiter</h1>
            <p class="text-sm text-moka-muted">Pesanan yang kamu buat hari ini dan menunggu diproses kasir.</p>
        </div>
        <a href="{{ route('waiter.index') }}" class="moka-btn-secondary">Kembali ke Order</a>
    </x-slot>

    <x-ui.card padding="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="moka-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-semibold">
                                {{ $order->status === 'WAITING' ? 'Pesanan #'.$order->id : $order->invoice_no }}
                            </td>
                            <td>{{ optional($order->ordered_at)->format('d M Y H:i') }}</td>
                            <td>
                                <x-ui.badge :variant="$order->status === 'PAID' ? 'success' : ($order->status === 'WAITING' ? 'warning' : 'danger')">{{ $order->status }}</x-ui.badge>
                            </td>
                            <td>{{ $order->status === 'WAITING' ? '-' : $order->payment_method }}</td>
                            <td class="text-money">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($order->status === 'WAITING')
                                    <a href="{{ route('waiter.index', ['waiter_order' => $order->id]) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit Pesanan</a>
                                @else
                                    <a href="{{ route('waiter.show', $order) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-moka-muted">Belum ada pesanan hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Riwayat Transaksi & Open Bill</h1>
            <p class="text-sm text-moka-muted">Open bill aktif milikmu dan riwayat transaksi hari ini.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="moka-btn-secondary">Kembali ke POS</a>
    </x-slot>

    <x-ui.card padding="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="moka-table moka-table-mobile">
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
                            <td class="font-semibold">{{ $order->status === 'OPEN_BILL' ? 'Open Bill #'.$order->id : $order->invoice_no }}</td>
                            <td>{{ optional($order->ordered_at)->format('d M Y H:i') }}</td>
                            <td>
                                <x-ui.badge :variant="$order->status === 'PAID' ? 'success' : ($order->status === 'OPEN_BILL' ? 'warning' : 'danger')">{{ $order->status }}</x-ui.badge>
                            </td>
                            <td>{{ $order->status === 'OPEN_BILL' ? '-' : $order->payment_method }}</td>
                            <td class="text-money">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($order->status === 'OPEN_BILL')
                                    <a href="{{ route('pos.index', ['open_bill' => $order->id]) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Lanjut Bayar</a>
                                    <a href="{{ route('pos.show', $order) }}" class="ml-3 text-sm font-semibold text-moka-primary hover:text-moka-ink">Detail</a>
                                @else
                                    <a href="{{ route('pos.show', $order) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Detail</a>
                                    <a href="{{ route('orders.receipt', $order) }}" class="ml-3 text-sm font-semibold text-moka-primary hover:text-moka-ink">Cetak Ulang</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-moka-muted">Belum ada transaksi hari ini.</td>
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

<x-app-layout x-data="{ cancelOpen: false }">
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Detail Transaksi</h1>
            <p class="text-sm text-moka-muted">{{ $order->status === 'OPEN_BILL' ? 'Open Bill #'.$order->id : $order->invoice_no }} - {{ optional($order->ordered_at)->format('d M Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($order->status === 'PAID')
                <a href="{{ route('orders.receipt', $order) }}" class="moka-btn-secondary">Cetak Ulang</a>
            @endif
            @can('void', $order)
                <button type="button" class="moka-btn-danger" @click="cancelOpen = true">Batalkan</button>
            @endcan
        </div>
    </x-slot>

    <form x-ref="cancelForm" method="POST" action="{{ route('admin.orders.void', $order) }}" class="hidden">
        @csrf
    </form>

    <x-ui.modal name="cancelOpen" maxWidth="md">
        <div class="moka-modal-content">
            <div class="moka-modal-header">
                <div>
                    <h3 class="moka-modal-title">Batalkan Pesanan</h3>
                    <p class="moka-modal-subtitle">Batalkan pesanan ini sebelum diproses?</p>
                </div>
                <button type="button" class="moka-modal-close" @click="cancelOpen = false" aria-label="Tutup popup">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>
            <div class="moka-modal-footer">
                <button type="button" class="moka-btn-secondary" @click="cancelOpen = false">Tidak</button>
                <button type="button" class="moka-btn-danger" @click="$refs.cancelForm.submit()">Ya, Batalkan</button>
            </div>
        </div>
    </x-ui.modal>

    <div class="grid gap-4 xl:grid-cols-[1.3fr_1fr]">
        <x-ui.card padding="p-0 overflow-hidden">
            <div class="border-b border-moka-line px-5 py-4">
                <h2 class="font-display text-lg font-bold text-moka-ink">Item Pesanan</h2>
            </div>
            <div class="divide-y divide-moka-line">
                @forelse($order->items as $item)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-moka-ink">{{ $item->name_snapshot }}</p>
                                <p class="text-xs text-moka-muted text-money">{{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}</p>
                                @if($item->notes)
                                    <p class="mt-1 text-xs text-moka-muted">Catatan: {{ $item->notes }}</p>
                                @endif
                                @if($item->addons->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($item->addons as $addon)
                                            <x-ui.badge>{{ $addon->name_snapshot }} (+Rp {{ number_format((float) $addon->price, 0, ',', '.') }})</x-ui.badge>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <p class="font-semibold text-moka-ink text-money">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-moka-muted">Item transaksi kosong.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <dl class="space-y-2 text-sm text-moka-muted">
                <div class="flex items-center justify-between">
                    <dt>Kasir</dt>
                    <dd class="font-semibold text-moka-ink">{{ $order->user?->name ?? '-' }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Status</dt>
                    <dd>
                        <x-ui.badge :variant="$order->status === 'PAID' ? 'success' : (in_array($order->status, ['OPEN_BILL', 'WAITING'], true) ? 'warning' : 'danger')">{{ $order->status }}</x-ui.badge>
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Metode Bayar</dt>
                    <dd class="font-semibold text-moka-ink">{{ in_array($order->status, ['OPEN_BILL', 'WAITING'], true) ? '-' : $order->payment_method }}</dd>
                </div>
                <hr class="border-moka-line">
                <div class="flex items-center justify-between">
                    <dt>Subtotal</dt>
                    <dd class="text-money">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Diskon</dt>
                    <dd class="text-money">{{ $order->discount_type === 'percent' ? number_format((float) $order->discount_value, 2, ',', '.').'%' : 'Rp '.number_format((float) $order->discount_value, 0, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Pajak</dt>
                    <dd class="text-money">Rp {{ number_format((float) $order->tax, 0, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Service</dt>
                    <dd class="text-money">Rp {{ number_format((float) $order->service, 0, ',', '.') }}</dd>
                </div>
                <div class="flex items-center justify-between pt-2 text-base font-bold text-moka-ink">
                    <dt>Total</dt>
                    <dd class="text-money">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>
</x-app-layout>


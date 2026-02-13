<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Produk Menu</h1>
            <p class="text-sm text-moka-muted">Kelola menu, varian, stok, dan status aktif produk Bar.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="moka-btn">Tambah Produk</a>
    </x-slot>

    <div x-data="{
        viewMode: localStorage.getItem('admin_products_view_mode') || 'table',
        deleteOpen: false,
        deleteLabel: '',
        deleteForm: null,
        setView(mode) {
            this.viewMode = mode;
            localStorage.setItem('admin_products_view_mode', mode);
        },
        openDelete(form, label) {
            this.deleteForm = form;
            this.deleteLabel = label;
            this.deleteOpen = true;
        },
        confirmDelete() {
            if (this.deleteForm) {
                this.deleteForm.submit();
            }
        }
    }" class="space-y-4">
        <x-ui.card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-moka-ink">Tampilan daftar produk</p>
                    <p class="text-xs text-moka-muted">Pilih mode tabel atau kartu.</p>
                </div>

                <div class="inline-flex rounded-xl border border-moka-line bg-moka-card p-1">
                    <button type="button" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg transition" :class="viewMode === 'table' ? 'bg-moka-primary text-[#1A1408]' : 'text-moka-muted hover:bg-moka-soft'" @click="setView('table')" aria-label="Tampilan tabel">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                    <button type="button" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg transition" :class="viewMode === 'card' ? 'bg-moka-primary text-[#1A1408]' : 'text-moka-muted hover:bg-moka-soft'" @click="setView('card')" aria-label="Tampilan kartu">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="4" y="4" width="7" height="7" rx="1.5" stroke-width="1.8"></rect>
                            <rect x="13" y="4" width="7" height="7" rx="1.5" stroke-width="1.8"></rect>
                            <rect x="4" y="13" width="7" height="7" rx="1.5" stroke-width="1.8"></rect>
                            <rect x="13" y="13" width="7" height="7" rx="1.5" stroke-width="1.8"></rect>
                        </svg>
                    </button>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card x-show="viewMode === 'table'" padding="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="moka-table moka-table-mobile">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>SKU</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Modal</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <p class="font-semibold">{{ $product->name }}</p>
                                    @if($product->variants->isNotEmpty())
                                        <p class="text-xs text-moka-muted">{{ $product->variants->count() }} varian</p>
                                    @endif
                                </td>
                                <td class="uppercase text-moka-muted">{{ $product->sku }}</td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="text-money">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                                <td class="text-money">Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }}</td>
                                <td>{{ $product->track_stock ? $product->stock_qty : 'Non-stok' }}</td>
                                <td>
                                    <x-ui.badge :variant="$product->is_active ? 'success' : 'warning'">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" x-ref="deleteForm{{ $product->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700" @click.prevent="openDelete($refs.deleteForm{{ $product->id }}, @js($product->name))">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-sm text-moka-muted">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div x-show="viewMode === 'card'" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($products as $product)
                <x-ui.card>
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-display text-lg font-bold text-moka-ink">{{ $product->name }}</h3>
                            <p class="text-xs uppercase tracking-wide text-moka-muted">{{ $product->sku }}</p>
                        </div>
                        <x-ui.badge :variant="$product->is_active ? 'success' : 'warning'">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-ui.badge>
                    </div>

                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="mb-3 h-72 w-full rounded-xl object-cover object-center">
                    @else
                        <div class="mb-3 flex h-72 items-center justify-center rounded-xl border border-dashed border-moka-line bg-moka-soft/60 text-sm text-moka-muted">
                            Belum ada gambar
                        </div>
                    @endif

                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <x-ui.badge variant="primary">{{ $product->category?->name ?? '-' }}</x-ui.badge>
                        @if($product->variants->isNotEmpty())
                            <x-ui.badge>{{ $product->variants->count() }} Varian</x-ui.badge>
                        @endif
                        @if($product->track_stock)
                            <x-ui.badge variant="warning">Stok: {{ $product->stock_qty }}</x-ui.badge>
                        @else
                            <x-ui.badge>Non-stok</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-display text-xl font-bold text-moka-primary text-money">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                            <p class="text-xs text-moka-muted text-money">Modal: Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="moka-btn-secondary px-4">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" x-ref="deleteCardForm{{ $product->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="moka-btn-danger px-4" @click.prevent="openDelete($refs.deleteCardForm{{ $product->id }}, @js($product->name))">Hapus</button>
                            </form>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card class="sm:col-span-2 xl:col-span-3">
                    <p class="text-center text-sm text-moka-muted">Belum ada produk.</p>
                </x-ui.card>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <x-ui.modal name="deleteOpen" maxWidth="md">
        <div class="moka-modal-content">
            <div class="moka-modal-header">
                <div>
                    <h3 class="moka-modal-title">Konfirmasi Hapus</h3>
                    <p class="moka-modal-subtitle">Hapus <span class="font-semibold" x-text="deleteLabel"></span>?</p>
                </div>
                <button type="button" class="moka-modal-close" @click="deleteOpen = false" aria-label="Tutup popup">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>
            <div class="moka-modal-footer">
                <button type="button" class="moka-btn-secondary" @click="deleteOpen = false">Batal</button>
                <button type="button" class="moka-btn-danger" @click="confirmDelete()">Hapus</button>
            </div>
        </div>
    </x-ui.modal>
</x-app-layout>




@php
    $categoryPayload = $categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
    ])->values();

    $productPayload = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'is_active' => (bool) $product->is_active,
            'track_stock' => (bool) $product->track_stock,
            'stock_qty' => (int) $product->stock_qty,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'image_url' => $product->image_url,
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => is_null($variant->price) ? null : (float) $variant->price,
                'price_delta' => is_null($variant->price_delta) ? null : (float) $variant->price_delta,
            ])->values(),
        ];
    })->values();

    $addonPayload = $addons->map(fn ($addon) => [
        'id' => $addon->id,
        'name' => $addon->name,
        'price' => (float) $addon->price,
    ])->values();

    $paymentPayload = $paymentMethods->map(fn ($method) => [
        'id' => $method->id,
        'name' => $method->name,
        'code' => $method->code,
    ])->values();

    $openBillPayload = $openBills->map(fn ($openBill) => [
        'id' => $openBill->id,
        'total' => (float) $openBill->total,
        'updated_at' => optional($openBill->updated_at)?->toIso8601String(),
    ])->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir | {{ config('app.name', 'Moka POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
    <div
        x-data="posPage({
            categories: {{ Illuminate\Support\Js::from($categoryPayload) }},
            products: {{ Illuminate\Support\Js::from($productPayload) }},
            addons: {{ Illuminate\Support\Js::from($addonPayload) }},
            paymentMethods: {{ Illuminate\Support\Js::from($paymentPayload) }},
            checkoutUrl: @js(route('pos.checkout')),
            saveOpenBillUrl: @js(route('pos.open-bill.save')),
            historyUrl: @js(route('pos.history')),
            posIndexUrl: @js(route('pos.index')),
            openBills: {{ Illuminate\Support\Js::from($openBillPayload) }},
            resumeOpenBill: {{ Illuminate\Support\Js::from($resumeOpenBill) }},
        })"
        x-init="init()"
        class="page-shell h-screen overflow-hidden"
    >
        <div class="bg-blob -left-20 top-16 h-64 w-64 bg-moka-accent/35"></div>
        <div class="bg-blob -right-20 top-20 h-80 w-80 bg-moka-primary/20"></div>
        <div class="bg-blob bottom-20 left-1/2 h-64 w-64 bg-moka-accent/20"></div>

        <header class="sticky top-0 z-40 border-b border-moka-line bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 w-full max-w-[1600px] items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="inline-flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Moka POS" class="h-10 w-10 rounded-xl border border-moka-line object-cover">
                    <div>
                        <p class="font-display text-base font-bold text-moka-ink">Moka POS - Solvix Coffee</p>
                        <p class="text-xs text-moka-muted">Kasir Online</p>
                    </div>
                </div>

                <div class="hidden items-center gap-4 md:flex">
                    <div class="rounded-full border border-moka-line bg-white px-4 py-2 text-sm font-semibold text-moka-muted" x-text="clockLabel"></div>
                    <p class="text-sm text-moka-muted">Halo, <span class="font-semibold text-moka-ink">{{ auth()->user()->name }}</span></p>
                    <a href="{{ route('pos.history') }}" class="moka-btn-secondary px-4">Riwayat</a>
                    <button type="button" class="moka-btn-danger px-4" @click="logoutOpen = true">Logout</button>
                </div>

                <div class="flex items-center gap-2 md:hidden">
                    <a href="{{ route('pos.history') }}" class="moka-btn-secondary px-3">Riwayat</a>
                    <button type="button" class="moka-btn-danger px-3" @click="logoutOpen = true">Logout</button>
                </div>
            </div>
        </header>

        <form x-ref="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>

        <main class="mx-auto h-[calc(100vh-4rem)] w-full max-w-[1600px] flex-1 overflow-hidden px-4 pt-4 pb-20 sm:px-6 md:pb-3 lg:px-8">
            <div class="grid h-full min-h-0 grid-rows-1 gap-4 md:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[230px_minmax(0,1fr)_380px]">
                <aside class="soft-card hidden min-h-0 xl:flex xl:h-fit xl:flex-col xl:self-start">
                    <div class="border-b border-moka-line p-4">
                        <h2 class="font-display text-lg font-bold text-moka-ink">Kategori</h2>
                    </div>
                    <div class="max-h-[220px] space-y-2 overflow-y-auto px-4 py-3">
                        <button type="button" class="moka-chip w-full justify-start" :class="selectedCategory === null ? 'moka-chip-active' : ''" @click="selectedCategory = null">
                            Semua
                        </button>
                        <template x-for="category in categories" :key="category.id">
                            <button type="button" class="moka-chip w-full justify-start" :class="selectedCategory === category.id ? 'moka-chip-active' : ''" @click="selectedCategory = category.id" x-text="category.name"></button>
                        </template>
                    </div>
                    <div class="border-t border-moka-line p-3">
                        <a :href="historyUrl" class="moka-btn-secondary w-full justify-center">Riwayat Hari Ini</a>
                    </div>
                </aside>
                <section class="soft-card flex h-full min-h-0 flex-col overflow-hidden md:col-span-1">
                    <div class="border-b border-moka-line p-4">
                        <label for="search" class="moka-label">Cari menu / scan kode (tekan "/" untuk fokus)</label>
                        <input id="search" x-ref="searchInput" type="text" x-model.debounce.100ms="search" class="moka-input" placeholder="Contoh: latte / CF-LAT">
                    </div>

                    <div class="border-b border-moka-line p-3 xl:hidden">
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <button type="button" class="moka-chip whitespace-nowrap" :class="selectedCategory === null ? 'moka-chip-active' : ''" @click="selectedCategory = null">
                                Semua
                            </button>
                            <template x-for="category in categories" :key="category.id">
                                <button type="button" class="moka-chip whitespace-nowrap" :class="selectedCategory === category.id ? 'moka-chip-active' : ''" @click="selectedCategory = category.id" x-text="category.name"></button>
                            </template>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-hidden" x-show="mobileTab === 'menu' || !isMobile()">
                        <div class="h-full overflow-y-auto p-4 pb-6">
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <button
                                        type="button"
                                        class="group rounded-2xl border border-moka-line bg-white p-3 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-moka-primary/40 hover:shadow-md active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="isOutOfStock(product)"
                                        @click="selectProduct(product)"
                                    >
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url" :alt="product.name" class="mb-3 h-28 w-full rounded-xl object-cover">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <div class="mb-3 flex h-28 items-center justify-center rounded-xl bg-moka-soft/70 text-xs text-moka-muted">Tanpa Gambar</div>
                                        </template>

                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-display text-base font-bold text-moka-ink" x-text="product.name"></h3>
                                        </div>
                                        <div class="mt-1">
                                            <x-ui.badge variant="primary" class="shrink-0" x-text="product.category_name || '-'"></x-ui.badge>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between gap-2">
                                            <p class="text-xs uppercase tracking-wide text-moka-muted" x-text="product.sku"></p>
                                            <template x-if="product.track_stock">
                                                <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold" :class="product.stock_qty > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                                                    Stok: <span class="ml-1" x-text="product.stock_qty"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <p class="mt-2 font-display text-lg font-bold text-moka-primary text-money" x-text="formatCurrency(product.price)"></p>

                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <template x-if="product.variants.length > 0">
                                                <span class="inline-flex rounded-full bg-moka-soft px-2 py-1 text-[11px] font-semibold text-moka-primary">Varian</span>
                                            </template>
                                            <template x-if="addons.length > 0">
                                                <span class="inline-flex rounded-full bg-[#ede8ff] px-2 py-1 text-[11px] font-semibold text-[#5b46b4]">Addon</span>
                                            </template>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <div x-show="filteredProducts.length === 0" class="mt-10 rounded-2xl border border-dashed border-moka-line bg-white px-6 py-12 text-center text-sm text-moka-muted">
                                Produk tidak ditemukan. Coba kata kunci lain.
                            </div>
                        </div>
                    </div>
                </section>
                <section class="soft-card flex min-h-0 max-h-full flex-col overflow-hidden md:col-span-1 md:self-start xl:col-span-1" :class="mobileTab === 'cart' || !isMobile() ? '' : 'hidden md:flex'">
                    <div class="border-b border-moka-line p-4">
                        <h2 class="font-display text-xl font-bold text-moka-ink">Keranjang</h2>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <p class="text-xs text-moka-muted">
                                Open bill aktif:
                                <span class="font-semibold text-moka-ink" x-text="openBills.length"></span>
                            </p>
                            <button
                                type="button"
                                class="text-xs font-semibold text-moka-primary transition hover:text-moka-ink disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="openBills.length === 0"
                                @click="openOpenBillsModal()"
                            >
                                Lihat
                            </button>
                        </div>
                        <template x-if="editingOpenBillId">
                            <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">
                                Sedang edit Open Bill #<span x-text="editingOpenBillId"></span>
                            </p>
                        </template>
                    </div>

                    <div class="px-4 py-3">
                        <div
                            class="overflow-y-auto pr-1 transition-[max-height] duration-200 ease-out"
                            :class="cart.length === 0
                                ? 'max-h-[190px]'
                                : 'max-h-[56vh] md:max-h-[calc(100vh-15rem)]'"
                        >
                            <template x-if="cart.length === 0">
                                <div class="rounded-2xl border border-dashed border-moka-line bg-white px-4 py-10 text-center text-sm text-moka-muted">
                                    Keranjang masih kosong, pilih menu dulu.
                                </div>
                            </template>

                            <div class="space-y-3" x-show="cart.length > 0">
                                <template x-for="(item, index) in cart" :key="item.uid">
                                    <div class="rounded-xl border border-moka-line bg-white p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-semibold text-moka-ink" x-text="item.name"></p>
                                                <p class="text-xs text-moka-muted text-money" x-text="formatCurrency(item.price)"></p>
                                                <template x-if="item.addons.length > 0">
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        <template x-for="addon in item.addons" :key="addon.id">
                                                            <span class="rounded-full bg-moka-soft px-2 py-0.5 text-[11px] text-moka-primary" x-text="`${addon.name} (+${formatCurrency(addon.price)})`"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="item.notes">
                                                    <p class="mt-1 text-xs text-moka-muted">Catatan: <span x-text="item.notes"></span></p>
                                                </template>
                                            </div>
                                            <p class="text-sm font-semibold text-moka-ink text-money" x-text="formatCurrency(itemLineTotal(item))"></p>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="inline-flex items-center rounded-full border border-moka-line">
                                                <button type="button" class="inline-flex min-h-9 min-w-9 items-center justify-center text-moka-primary" @click="decrementQty(index)">-</button>
                                                <span class="min-w-8 text-center text-sm font-semibold text-moka-ink" x-text="item.qty"></span>
                                                <button type="button" class="inline-flex min-h-9 min-w-9 items-center justify-center text-moka-primary" @click="incrementQty(index)">+</button>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="text-xs font-semibold text-moka-primary hover:text-moka-ink" @click="openEditItem(index)">Edit</button>
                                                <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-700" @click="removeItem(index)">Hapus</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-moka-line bg-white/90">
                        <template x-if="checkoutError">
                            <p class="mx-3 mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="checkoutError"></p>
                        </template>
                        <template x-if="noticeMessage">
                            <p class="mx-3 mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700" x-text="noticeMessage"></p>
                        </template>

                        <div class="grid gap-2 px-3 py-2.5">
                            <button type="button" class="moka-btn-secondary w-full justify-center text-base" :disabled="cart.length === 0 || isSubmitting" @click="saveOpenBill()">
                                <span x-text="editingOpenBillId ? 'Update Open Bill' : 'Simpan Open Bill'"></span>
                            </button>
                            <button type="button" class="moka-btn w-full justify-center text-base" :disabled="cart.length === 0 || isSubmitting" @click="openPayment()">
                                Continue
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div class="fixed bottom-3 left-1/2 z-40 flex -translate-x-1/2 items-center gap-2 rounded-full border border-moka-line bg-white/95 p-2 shadow-lg md:hidden">
            <button type="button" class="moka-chip px-4 py-2 text-xs" :class="mobileTab === 'menu' ? 'moka-chip-active' : ''" @click="mobileTab = 'menu'">Menu</button>
            <button type="button" class="moka-chip px-4 py-2 text-xs" :class="mobileTab === 'cart' ? 'moka-chip-active' : ''" @click="mobileTab = 'cart'">
                Cart (<span x-text="cart.length"></span>)
            </button>
            <a :href="historyUrl" class="moka-chip px-4 py-2 text-xs">Riwayat</a>
        </div>
        <x-ui.modal name="openBillsOpen" maxWidth="xl">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Daftar Open Bill</h3>
                        <p class="moka-modal-subtitle">Pilih Open Bill untuk dilanjutkan pembayaran.</p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="openBillsOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 max-h-[55vh] space-y-2 overflow-y-auto pr-1">
                    <template x-if="openBills.length === 0">
                        <div class="rounded-xl border border-dashed border-moka-line bg-white px-4 py-8 text-center text-sm text-moka-muted">
                            Belum ada Open Bill aktif.
                        </div>
                    </template>

                    <template x-for="bill in openBills" :key="bill.id">
                        <div class="rounded-xl border border-moka-line bg-white p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-moka-ink">Open Bill #<span x-text="bill.id"></span></p>
                                    <p class="text-xs text-moka-muted" x-text="`Update: ${formatDateTime(bill.updated_at)}`"></p>
                                    <p class="mt-1 text-sm font-semibold text-moka-primary text-money" x-text="formatCurrency(bill.total)"></p>
                                </div>
                                <button type="button" class="moka-btn-secondary px-3 py-2 text-sm" @click="continueOpenBill(bill.id)">
                                    Buka
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="openBillsOpen = false">Tutup</button>
                </div>
            </div>
        </x-ui.modal>
        <x-ui.modal name="openBillSavedOpen" maxWidth="md">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Open Bill</h3>
                        <p class="moka-modal-subtitle" x-text="openBillSavedMessage"></p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="acknowledgeOpenBillSaved()" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn" @click="acknowledgeOpenBillSaved()">Oke</button>
                </div>
            </div>
        </x-ui.modal>
        <x-ui.modal name="customizeOpen" maxWidth="xl">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Sesuaikan Item</h3>
                        <p class="moka-modal-subtitle" x-text="selectedProduct ? selectedProduct.name : ''"></p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="customizeOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <template x-if="selectedProduct && selectedProduct.variants.length > 0">
                        <div>
                            <p class="moka-label">Pilih Varian</p>
                            <div class="grid gap-2 sm:grid-cols-3">
                                <template x-for="variant in selectedProduct.variants" :key="variant.id">
                                    <button type="button" class="rounded-xl border border-moka-line p-3 text-left transition hover:border-moka-primary/40" :class="selectedVariantId === variant.id ? 'border-moka-primary bg-moka-soft/60' : 'bg-white'" @click="selectedVariantId = variant.id">
                                        <p class="text-sm font-semibold text-moka-ink" x-text="variant.name"></p>
                                        <p class="text-xs text-moka-muted text-money" x-text="formatCurrency(resolveUnitPrice(selectedProduct, variant.id))"></p>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="addons.length > 0">
                        <div>
                            <p class="moka-label">Pilih Add-on</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <template x-for="addon in addons" :key="addon.id">
                                    <label class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-moka-line bg-white px-3 py-2">
                                        <input type="checkbox" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" :value="addon.id" @change="toggleAddon(addon.id)" :checked="selectedAddonIds.includes(addon.id)">
                                        <span class="text-sm text-moka-ink" x-text="`${addon.name} (+${formatCurrency(addon.price)})`"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                        <div>
                            <label class="moka-label">Catatan Item</label>
                            <input type="text" class="moka-input" x-model="itemNotes" placeholder="Contoh: less sugar">
                        </div>
                        <div>
                            <label class="moka-label">Qty</label>
                            <div class="inline-flex items-center rounded-full border border-moka-line bg-white">
                                <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center text-moka-primary" @click="itemQty = Math.max(1, itemQty - 1)">-</button>
                                <span class="min-w-10 text-center text-sm font-semibold text-moka-ink" x-text="itemQty"></span>
                                <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center text-moka-primary" @click="itemQty = itemQty + 1">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="customizeOpen = false">Batal</button>
                    <button type="button" class="moka-btn" @click="saveCustomize()">Simpan Item</button>
                </div>
            </div>
        </x-ui.modal>
        <x-ui.modal name="logoutOpen" maxWidth="md">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Konfirmasi Logout</h3>
                        <p class="moka-modal-subtitle">Yakin ingin keluar dari sesi kasir?</p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="logoutOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="logoutOpen = false">Batal</button>
                    <button type="button" class="moka-btn-danger" @click="$refs.logoutForm.submit()">Logout</button>
                </div>
            </div>
        </x-ui.modal>
        <x-ui.modal name="confirmPaymentOpen" maxWidth="md">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Konfirmasi</h3>
                        <p class="moka-modal-subtitle">Lanjut ke payment?</p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="confirmPaymentOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="confirmPaymentOpen = false">Cancel</button>
                    <button type="button" class="moka-btn" @click="continueToPayment()">Lanjut</button>
                </div>
            </div>
        </x-ui.modal>
        <x-ui.modal name="paymentOpen" maxWidth="2xl">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Pembayaran</h3>
                        <p class="moka-modal-subtitle">Pilih metode dan proses transaksi.</p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="paymentOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 grid gap-5 lg:grid-cols-[1.15fr_1fr]">
                    <div class="space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="moka-label">Diskon</label>
                                <select class="moka-select" x-model="discountType">
                                    <option value="none">Tidak ada</option>
                                    <option value="amount">Nominal</option>
                                    <option value="percent">Persen</option>
                                </select>
                            </div>
                            <div>
                                <label class="moka-label">Nilai Diskon</label>
                                <input type="number" step="0.01" min="0" class="moka-input" x-model="discountValue">
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="moka-label">Pajak</label>
                                <input
                                    type="text"
                                    class="moka-input"
                                    x-model="taxPercentInput"
                                    placeholder="10%"
                                    @blur="taxPercentInput = normalizeTaxPercentInput(taxPercentInput)"
                                >
                            </div>
                            <div>
                                <label class="moka-label">Service Charge</label>
                                <input type="number" step="0.01" min="0" class="moka-input" x-model="service">
                            </div>
                        </div>

                        <div>
                            <label class="moka-label">Catatan Order</label>
                            <textarea rows="2" class="w-full rounded-xl border-moka-line text-sm text-moka-ink focus:border-moka-primary focus:ring-moka-primary/20" x-model="orderNotes" placeholder="Catatan umum transaksi..."></textarea>
                        </div>

                        <dl class="rounded-xl border border-moka-line bg-white p-4 text-sm text-moka-muted">
                            <div class="flex items-center justify-between">
                                <dt>Subtotal</dt>
                                <dd class="text-money" x-text="formatCurrency(subtotal)"></dd>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <dt>Diskon</dt>
                                <dd class="text-money" x-text="formatCurrency(discountAmount)"></dd>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <dt>Pajak</dt>
                                <dd class="text-money" x-text="formatCurrency(taxAmount)"></dd>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <dt>Service</dt>
                                <dd class="text-money" x-text="formatCurrency(numberValue(service))"></dd>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-moka-line pt-3 text-xl font-bold text-moka-ink">
                                <dt>Total</dt>
                                <dd class="text-money" x-text="formatCurrency(total)"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="moka-label">Metode Pembayaran</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <template x-for="method in paymentMethods" :key="method.id">
                                    <button type="button" class="rounded-xl border border-moka-line bg-white px-4 py-3 text-left transition hover:border-moka-primary/40" :class="selectedPaymentMethodId === method.id ? 'border-moka-primary bg-moka-soft/60' : ''" @click="selectedPaymentMethodId = method.id">
                                        <p class="font-semibold text-moka-ink" x-text="method.name"></p>
                                        <p class="text-xs uppercase text-moka-muted" x-text="method.code"></p>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <template x-if="isCashMethod()">
                            <div class="space-y-3 rounded-xl border border-moka-line bg-white p-4">
                                <div>
                                    <label class="moka-label">Uang Diterima</label>
                                    <input type="number" step="0.01" min="0" class="moka-input" x-model="cashReceived">
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="moka-btn-secondary px-3" @click="quickCash(20000)">20k</button>
                                    <button type="button" class="moka-btn-secondary px-3" @click="quickCash(50000)">50k</button>
                                    <button type="button" class="moka-btn-secondary px-3" @click="quickCash(100000)">100k</button>
                                    <button type="button" class="moka-btn-secondary px-3" @click="quickCash(200000)">200k</button>
                                </div>

                                <div class="rounded-xl bg-moka-soft/50 px-3 py-2 text-sm text-moka-muted">
                                    Kembalian:
                                    <span class="font-bold text-moka-ink text-money" x-text="formatCurrency(cashChange)"></span>
                                </div>
                            </div>
                        </template>

                        <div class="moka-modal-pane">
                            <div class="flex items-center justify-between text-sm text-moka-muted">
                                <span>Total Bayar</span>
                                <span class="font-display text-2xl font-bold text-moka-ink text-money" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>

                        <template x-if="checkoutError">
                            <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="checkoutError"></p>
                        </template>
                    </div>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="paymentOpen = false">Batal</button>
                    <button type="button" class="moka-btn" :disabled="isSubmitting" @click="submitCheckout()">
                        <span x-show="!isSubmitting">Proses & Cetak</span>
                        <span x-show="isSubmitting">Memproses...</span>
                    </button>
                </div>
            </div>
        </x-ui.modal>

    </div>

    <script>
        function posPage(config) {
            return {
                categories: config.categories,
                products: config.products,
                addons: config.addons,
                paymentMethods: config.paymentMethods,
                checkoutUrl: config.checkoutUrl,
                saveOpenBillUrl: config.saveOpenBillUrl,
                historyUrl: config.historyUrl,
                posIndexUrl: config.posIndexUrl,
                openBills: config.openBills ?? [],
                resumeOpenBill: config.resumeOpenBill ?? null,
                search: '',
                selectedCategory: null,
                cart: [],
                discountType: 'none',
                discountValue: '',
                taxPercentInput: '10%',
                service: '',
                orderNotes: '',
                selectedPaymentMethodId: null,
                cashReceived: '',
                checkoutError: '',
                noticeMessage: '',
                customizeOpen: false,
                customizeMode: 'new',
                customizeIndex: null,
                selectedProduct: null,
                selectedVariantId: null,
                selectedAddonIds: [],
                itemNotes: '',
                itemQty: 1,
                logoutOpen: false,
                openBillsOpen: false,
                openBillSavedOpen: false,
                openBillSavedMessage: 'Open Bill berhasil ditambahkan.',
                confirmPaymentOpen: false,
                paymentOpen: false,
                isSubmitting: false,
                clockLabel: '',
                mobileTab: 'menu',
                editingOpenBillId: null,

                init() {
                    this.selectedPaymentMethodId = this.paymentMethods[0]?.id ?? null;
                    this.taxPercentInput = this.normalizeTaxPercentInput(this.taxPercentInput);
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    if (this.resumeOpenBill) {
                        this.hydrateFromOpenBill(this.resumeOpenBill);
                    }

                    window.addEventListener('keydown', (event) => {
                        const target = event.target;
                        const targetTag = target?.tagName?.toLowerCase();
                        const isTyping = targetTag === 'input' || targetTag === 'textarea' || target?.isContentEditable;

                        if (event.key === '/' && !isTyping) {
                            event.preventDefault();
                            this.$refs.searchInput?.focus();
                        }

                        if (event.key === 'Enter' && !event.ctrlKey && target === this.$refs.searchInput) {
                            event.preventDefault();
                            this.addFirstProduct();
                        }

                        if (event.key === 'Enter' && event.ctrlKey) {
                            event.preventDefault();
                            this.openPayment();
                        }

                        if (event.key === 'Escape') {
                            this.customizeOpen = false;
                            this.logoutOpen = false;
                            this.openBillsOpen = false;
                            this.openBillSavedOpen = false;
                            this.confirmPaymentOpen = false;
                            this.paymentOpen = false;
                        }
                    });
                },

                isMobile() {
                    return window.innerWidth < 768;
                },

                updateClock() {
                    this.clockLabel = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'short',
                        day: '2-digit',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    }).format(new Date());
                },

                formatDateTime(value) {
                    if (!value) {
                        return '-';
                    }

                    return new Intl.DateTimeFormat('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    }).format(new Date(value));
                },

                openOpenBillsModal() {
                    this.openBillsOpen = true;
                },

                continueOpenBill(openBillId) {
                    if (!openBillId) {
                        return;
                    }

                    this.openBillsOpen = false;
                    const url = new URL(this.posIndexUrl, window.location.origin);
                    url.searchParams.set('open_bill', openBillId);
                    window.location.href = url.toString();
                },

                get filteredProducts() {
                    const query = this.search.trim().toLowerCase();

                    return this.products.filter((product) => {
                        const matchedCategory = this.selectedCategory === null || product.category_id === this.selectedCategory;
                        const matchedSearch = query === '' || product.name.toLowerCase().includes(query) || product.sku.toLowerCase().includes(query);
                        return matchedCategory && matchedSearch;
                    });
                },

                get firstFilteredProduct() {
                    return this.filteredProducts[0] ?? null;
                },
                get selectedPaymentMethod() {
                    return this.paymentMethods.find((method) => method.id === this.selectedPaymentMethodId) ?? null;
                },

                get subtotal() {
                    return this.cart.reduce((carry, item) => carry + this.itemLineTotal(item), 0);
                },

                get discountAmount() {
                    const value = this.numberValue(this.discountValue);
                    if (this.discountType === 'percent') {
                        return this.subtotal * Math.min(100, Math.max(0, value)) / 100;
                    }
                    if (this.discountType === 'amount') {
                        return Math.min(this.subtotal, Math.max(0, value));
                    }
                    return 0;
                },

                get total() {
                    const base = Math.max(0, this.subtotal - this.discountAmount);
                    return Math.max(0, base + this.taxAmount + this.numberValue(this.service));
                },

                get taxAmount() {
                    const base = Math.max(0, this.subtotal - this.discountAmount);
                    return base * (this.taxPercentValue / 100);
                },

                get taxPercentValue() {
                    const normalized = String(this.taxPercentInput ?? '').replace(',', '.');
                    const numeric = Number.parseFloat(normalized.replace(/[^0-9.]/g, ''));
                    if (!Number.isFinite(numeric)) {
                        return 0;
                    }

                    return Math.min(100, Math.max(0, numeric));
                },

                get cashChange() {
                    return Math.max(0, this.numberValue(this.cashReceived) - this.total);
                },

                isCashMethod() {
                    const code = this.selectedPaymentMethod?.code ?? '';
                    return code.toLowerCase() === 'cash';
                },

                numberValue(value) {
                    const parsed = Number.parseFloat(value);
                    return Number.isFinite(parsed) ? parsed : 0;
                },

                normalizeTaxPercentInput(value) {
                    const normalized = String(value ?? '').replace(',', '.');
                    const parsed = Number.parseFloat(normalized.replace(/[^0-9.]/g, ''));
                    const safe = Number.isFinite(parsed) ? Math.min(100, Math.max(0, parsed)) : 10;
                    const rounded = Number(safe.toFixed(2));

                    return `${rounded}%`;
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                    }).format(value || 0);
                },

                isOutOfStock(product) {
                    if (!product.track_stock) {
                        return false;
                    }

                    return (product.stock_qty - this.reservedQty(product.id)) <= 0;
                },

                reservedQty(productId, exceptIndex = null) {
                    return this.cart.reduce((carry, item, index) => {
                        if (item.product_id !== productId) {
                            return carry;
                        }
                        if (exceptIndex !== null && index === exceptIndex) {
                            return carry;
                        }
                        return carry + item.qty;
                    }, 0);
                },

                addFirstProduct() {
                    if (!this.firstFilteredProduct) {
                        return;
                    }

                    this.selectProduct(this.firstFilteredProduct);
                },

                hydrateFromOpenBill(openBill) {
                    if (!openBill || !Array.isArray(openBill.items)) {
                        return;
                    }

                    this.editingOpenBillId = openBill.id ?? null;
                    this.discountType = openBill.discount_type ?? 'none';
                    this.discountValue = `${openBill.discount_value ?? 0}`;
                    this.taxPercentInput = this.normalizeTaxPercentInput(openBill.tax_percent ?? 10);
                    this.service = `${openBill.service ?? 0}`;
                    this.orderNotes = openBill.notes ?? '';
                    this.cart = openBill.items.map((item) => ({
                        uid: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        name: item.name_snapshot,
                        price: Number(item.price) || 0,
                        qty: Math.max(1, Number.parseInt(item.qty, 10) || 1),
                        addons: Array.isArray(item.addons) ? item.addons.map((addon) => ({
                            id: addon.id,
                            name: addon.name,
                            price: Number(addon.price) || 0,
                        })) : [],
                        notes: item.notes || '',
                    }));

                    this.noticeMessage = `Open Bill #${this.editingOpenBillId} berhasil dimuat.`;
                    this.checkoutError = '';
                },

                selectProduct(product) {
                    if (this.isOutOfStock(product)) {
                        this.checkoutError = `Stok ${product.name} sudah habis.`;
                        return;
                    }

                    this.checkoutError = '';

                    if (product.variants.length > 0) {
                        this.openCustomizeForNew(product);
                        return;
                    }

                    this.addCartItem({
                        product,
                        variantId: null,
                        addonIds: [],
                        notes: '',
                        qty: 1,
                    });
                },

                openCustomizeForNew(product) {
                    this.customizeMode = 'new';
                    this.customizeIndex = null;
                    this.selectedProduct = product;
                    this.selectedVariantId = product.variants[0]?.id ?? null;
                    this.selectedAddonIds = [];
                    this.itemNotes = '';
                    this.itemQty = 1;
                    this.customizeOpen = true;
                },

                openEditItem(index) {
                    const item = this.cart[index];
                    const product = this.products.find((productItem) => productItem.id === item.product_id);

                    if (!product) {
                        return;
                    }

                    this.customizeMode = 'edit';
                    this.customizeIndex = index;
                    this.selectedProduct = product;
                    this.selectedVariantId = item.variant_id;
                    this.selectedAddonIds = item.addons.map((addon) => addon.id);
                    this.itemNotes = item.notes ?? '';
                    this.itemQty = item.qty;
                    this.customizeOpen = true;
                },

                toggleAddon(addonId) {
                    if (this.selectedAddonIds.includes(addonId)) {
                        this.selectedAddonIds = this.selectedAddonIds.filter((id) => id !== addonId);
                        return;
                    }

                    this.selectedAddonIds = [...this.selectedAddonIds, addonId];
                },

                saveCustomize() {
                    if (!this.selectedProduct) {
                        return;
                    }

                    if (this.selectedProduct.track_stock) {
                        const reserved = this.reservedQty(this.selectedProduct.id, this.customizeMode === 'edit' ? this.customizeIndex : null);
                        if ((reserved + this.itemQty) > this.selectedProduct.stock_qty) {
                            this.checkoutError = `Stok ${this.selectedProduct.name} tidak cukup.`;
                            return;
                        }
                    }

                    const payload = {
                        product: this.selectedProduct,
                        variantId: this.selectedVariantId,
                        addonIds: this.selectedAddonIds,
                        notes: this.itemNotes,
                        qty: this.itemQty,
                    };

                    if (this.customizeMode === 'edit' && this.customizeIndex !== null) {
                        this.cart[this.customizeIndex] = this.makeCartItem(payload);
                    } else {
                        this.addCartItem(payload);
                    }

                    this.customizeOpen = false;
                },

                addCartItem(payload) {
                    const item = this.makeCartItem(payload);

                    const existing = this.cart.find((cartItem) => {
                        if (cartItem.product_id !== item.product_id || cartItem.variant_id !== item.variant_id) {
                            return false;
                        }

                        if ((cartItem.notes || '') !== (item.notes || '')) {
                            return false;
                        }

                        return JSON.stringify(cartItem.addons.map((addon) => addon.id).sort()) === JSON.stringify(item.addons.map((addon) => addon.id).sort());
                    });

                    if (existing) {
                        const product = this.products.find((productItem) => productItem.id === existing.product_id);
                        const newQty = existing.qty + item.qty;
                        if (product?.track_stock && newQty > product.stock_qty) {
                            this.checkoutError = `Stok ${product.name} tidak cukup.`;
                            return;
                        }
                        existing.qty = newQty;
                        return;
                    }

                    this.cart.push(item);
                },
                makeCartItem({ product, variantId, addonIds, notes, qty }) {
                    const selectedAddons = this.addons.filter((addon) => addonIds.includes(addon.id));
                    const unitPrice = this.resolveUnitPrice(product, variantId);
                    const variant = product.variants.find((variantItem) => variantItem.id === variantId);
                    const name = variant ? `${product.name} - ${variant.name}` : product.name;

                    return {
                        uid: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                        product_id: product.id,
                        variant_id: variantId,
                        name,
                        price: unitPrice,
                        qty: Math.max(1, Number.parseInt(qty, 10) || 1),
                        addons: selectedAddons,
                        notes: notes || '',
                    };
                },

                resolveUnitPrice(product, variantId) {
                    if (!variantId) {
                        return product.price;
                    }

                    const variant = product.variants.find((variantItem) => variantItem.id === variantId);
                    if (!variant) {
                        return product.price;
                    }

                    if (variant.price !== null) {
                        return variant.price;
                    }

                    if (variant.price_delta !== null) {
                        return product.price + variant.price_delta;
                    }

                    return product.price;
                },

                itemLineTotal(item) {
                    const addonTotal = item.addons.reduce((carry, addon) => carry + addon.price, 0);
                    return (item.price + addonTotal) * item.qty;
                },

                incrementQty(index) {
                    const item = this.cart[index];
                    const product = this.products.find((productItem) => productItem.id === item.product_id);
                    if (product?.track_stock && (this.reservedQty(product.id, index) + item.qty + 1) > product.stock_qty) {
                        this.checkoutError = `Stok ${product.name} tidak cukup.`;
                        return;
                    }
                    item.qty += 1;
                },

                decrementQty(index) {
                    if (this.cart[index].qty <= 1) {
                        this.removeItem(index);
                        return;
                    }
                    this.cart[index].qty -= 1;
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                },

                acknowledgeOpenBillSaved() {
                    this.openBillSavedOpen = false;
                    this.cart = [];
                    this.discountType = 'none';
                    this.discountValue = '';
                    this.taxPercentInput = this.normalizeTaxPercentInput(10);
                    this.service = '';
                    this.orderNotes = '';
                    this.cashReceived = '';
                    this.checkoutError = '';
                    this.noticeMessage = '';
                    this.editingOpenBillId = null;
                    this.mobileTab = this.isMobile() ? 'menu' : this.mobileTab;
                },

                async saveOpenBill() {
                    if (this.cart.length === 0) {
                        this.checkoutError = 'Keranjang masih kosong.';
                        return;
                    }

                    const wasUpdating = Boolean(this.editingOpenBillId);
                    this.checkoutError = '';
                    this.noticeMessage = '';
                    this.isSubmitting = true;

                    try {
                        const response = await fetch(this.saveOpenBillUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                open_bill_id: this.editingOpenBillId,
                                items: this.cart.map((item) => ({
                                    product_id: item.product_id,
                                    variant_id: item.variant_id,
                                    qty: item.qty,
                                    addons: item.addons.map((addon) => addon.id),
                                    notes: item.notes,
                                })),
                                discount_type: this.discountType,
                                discount_value: this.numberValue(this.discountValue),
                                tax_percent: this.taxPercentValue,
                                service: this.numberValue(this.service),
                                notes: this.orderNotes,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                this.checkoutError = Array.isArray(firstError) ? firstError[0] : 'Simpan open bill gagal.';
                            } else {
                                this.checkoutError = data.message || 'Simpan open bill gagal.';
                            }
                            return;
                        }

                        this.editingOpenBillId = data.open_bill_id;
                        this.noticeMessage = '';
                        const nowIso = new Date().toISOString();
                        const existingBill = this.openBills.find((bill) => bill.id === data.open_bill_id);

                        if (existingBill) {
                            existingBill.total = this.total;
                            existingBill.updated_at = nowIso;
                            this.openBills = [
                                existingBill,
                                ...this.openBills.filter((bill) => bill.id !== data.open_bill_id),
                            ];
                        } else {
                            this.openBills.unshift({
                                id: data.open_bill_id,
                                total: this.total,
                                updated_at: nowIso,
                            });
                        }

                        this.openBillSavedMessage = wasUpdating ? 'bill berhasil di update' : 'Open Bill berhasil ditambahkan';
                        this.openBillSavedOpen = true;
                    } catch (error) {
                        this.checkoutError = 'Terjadi kesalahan jaringan saat menyimpan open bill.';
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                openPayment() {
                    if (this.cart.length === 0) {
                        this.checkoutError = 'Keranjang masih kosong.';
                        return;
                    }

                    this.checkoutError = '';
                    this.noticeMessage = '';
                    this.confirmPaymentOpen = true;
                },

                continueToPayment() {
                    this.confirmPaymentOpen = false;
                    this.paymentOpen = true;
                    this.selectedPaymentMethodId = this.selectedPaymentMethodId || this.paymentMethods[0]?.id || null;
                },

                quickCash(amount) {
                    this.cashReceived = Math.max(amount, this.total);
                },

                async submitCheckout() {
                    if (this.cart.length === 0 || !this.selectedPaymentMethodId) {
                        this.checkoutError = 'Data pembayaran belum lengkap.';
                        return;
                    }

                    this.checkoutError = '';
                    this.isSubmitting = true;

                    try {
                        const response = await fetch(this.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                items: this.cart.map((item) => ({
                                    product_id: item.product_id,
                                    variant_id: item.variant_id,
                                    qty: item.qty,
                                    addons: item.addons.map((addon) => addon.id),
                                    notes: item.notes,
                                })),
                                discount_type: this.discountType,
                                discount_value: this.numberValue(this.discountValue),
                                tax_percent: this.taxPercentValue,
                                service: this.numberValue(this.service),
                                open_bill_id: this.editingOpenBillId,
                                payment_method_id: this.selectedPaymentMethodId,
                                cash_received: this.isCashMethod() ? this.numberValue(this.cashReceived) : null,
                                notes: this.orderNotes,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                const firstError = Object.values(data.errors)[0];
                                this.checkoutError = Array.isArray(firstError) ? firstError[0] : 'Checkout gagal.';
                            } else {
                                this.checkoutError = data.message || 'Checkout gagal.';
                            }
                            return;
                        }

                        window.location.href = data.redirect;
                    } catch (error) {
                        this.checkoutError = 'Terjadi kesalahan jaringan saat checkout.';
                    } finally {
                        this.isSubmitting = false;
                    }
                },
            };
        }
    </script>
</body>
</html>

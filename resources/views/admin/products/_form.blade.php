@php
    $initialVariants = old('variants');

    if ($initialVariants === null && isset($product)) {
        $initialVariants = $product->variants->map(fn ($variant) => [
            'name' => $variant->name,
            'price' => (string) $variant->price,
            'price_delta' => (string) $variant->price_delta,
            'is_active' => (bool) $variant->is_active,
        ])->values()->all();
    }

    if ($initialVariants === null) {
        $initialVariants = [];
    }
@endphp

@csrf
@isset($product)
    @method('PUT')
@endisset

<div x-data="productForm({{ Illuminate\Support\Js::from($initialVariants) }}, @js(isset($product) ? $product->image_url : null))" class="grid gap-6">
    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <x-input-label for="name" :value="'Nama Produk'" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name ?? '')" required />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="sku" :value="'SKU / Kode'" />
            <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full uppercase" :value="old('sku', $product->sku ?? '')" required />
            <x-input-error :messages="$errors->get('sku')" />
        </div>

        <div>
            <x-input-label for="category_id" :value="'Kategori'" />
            <select id="category_id" name="category_id" class="moka-select mt-1" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" />
        </div>

        <div>
            <x-input-label for="price" :value="'Harga Dasar'" />
            <x-text-input id="price" name="price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('price', isset($product) ? (float) $product->price : '')" required />
            <x-input-error :messages="$errors->get('price')" />
        </div>

        <div>
            <x-input-label for="cost_price" :value="'Harga Modal'" />
            <x-text-input id="cost_price" name="cost_price" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('cost_price', isset($product) ? (float) $product->cost_price : '')" required />
            <x-input-error :messages="$errors->get('cost_price')" />
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="track_stock" value="1" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" @checked(old('track_stock', $product->track_stock ?? false))>
            <span class="text-sm text-moka-muted">Produk menggunakan stok</span>
        </label>

        <div>
            <x-input-label for="stock_qty" :value="'Jumlah Stok'" />
            <x-text-input id="stock_qty" name="stock_qty" type="number" min="0" class="mt-1 block w-full" :value="old('stock_qty', $product->stock_qty ?? 0)" />
            <x-input-error :messages="$errors->get('stock_qty')" />
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" @checked(old('is_active', $product->is_active ?? true))>
            <span class="text-sm text-moka-muted">Produk aktif</span>
        </label>

        <div>
            <x-input-label for="image" :value="'Gambar Produk (Upload)'" />
            <input id="image" name="image" type="file" accept="image/*" class="moka-input mt-1 h-auto py-2 file:mr-3 file:rounded-full file:border-0 file:bg-moka-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-moka-primary" @change="previewImage($event)">
            <p class="mt-1 text-xs text-moka-muted">Format: JPG, JPEG, PNG, WEBP, SVG. File akan otomatis dioptimasi saat upload.</p>
            <x-input-error :messages="$errors->get('image')" />
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <template x-if="imagePreview">
            <img :src="imagePreview" alt="Preview Gambar" class="h-40 w-full rounded-xl border border-moka-line object-cover">
        </template>
        <template x-if="!imagePreview">
            <div class="flex h-40 items-center justify-center rounded-xl border border-dashed border-moka-line bg-moka-soft/60 text-sm text-moka-muted">
                Preview gambar produk
            </div>
        </template>
    </div>

    <x-ui.card class="bg-moka-soft/35">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-display text-lg font-bold text-moka-ink">Varian Produk</h3>
                <p class="text-sm text-moka-muted">Contoh: Small / Medium / Large.</p>
            </div>
            <button type="button" class="moka-btn-secondary px-4" @click="addVariant">Tambah Varian</button>
        </div>

        <div class="mt-4 grid gap-3">
            <template x-for="(variant, index) in variants" :key="index">
                <div class="rounded-xl border border-moka-line bg-white p-3">
                    <div class="grid gap-3 md:grid-cols-4">
                        <div>
                            <label class="moka-label">Nama</label>
                            <input type="text" class="moka-input" :name="`variants[${index}][name]`" x-model="variant.name" placeholder="Small">
                        </div>
                        <div>
                            <label class="moka-label">Harga Final</label>
                            <input type="number" min="0" step="0.01" class="moka-input" :name="`variants[${index}][price]`" x-model="variant.price" placeholder="opsional">
                        </div>
                        <div>
                            <label class="moka-label">Selisih Harga</label>
                            <input type="number" step="0.01" class="moka-input" :name="`variants[${index}][price_delta]`" x-model="variant.price_delta" placeholder="opsional">
                        </div>
                        <div class="flex items-end justify-between gap-3">
                            <label class="inline-flex items-center gap-2 pb-2">
                                <input type="checkbox" value="1" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" :name="`variants[${index}][is_active]`" x-model="variant.is_active">
                                <span class="text-sm text-moka-muted">Aktif</span>
                            </label>
                            <button type="button" class="text-sm font-semibold text-red-600 hover:text-red-700" @click="removeVariant(index)">Hapus</button>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="variants.length === 0" class="rounded-xl border border-dashed border-moka-line bg-white px-4 py-6 text-center text-sm text-moka-muted">
                Belum ada varian. Tambahkan jika produk punya ukuran/opsi.
            </div>
        </div>
    </x-ui.card>
</div>

<div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
    <a href="{{ route('admin.products.index') }}" class="moka-btn-secondary">Batal</a>
    <x-primary-button>{{ $submitLabel ?? 'Simpan' }}</x-primary-button>
</div>

<script>
    function productForm(initialVariants, initialImage) {
        return {
            variants: initialVariants.map((variant) => ({
                name: variant.name ?? '',
                price: variant.price ?? '',
                price_delta: variant.price_delta ?? '',
                is_active: Boolean(variant.is_active),
            })),
            imagePreview: initialImage,
            addVariant() {
                this.variants.push({
                    name: '',
                    price: '',
                    price_delta: '',
                    is_active: true,
                });
            },
            removeVariant(index) {
                this.variants.splice(index, 1);
            },
            previewImage(event) {
                const [file] = event.target.files;

                if (!file) {
                    return;
                }

                this.imagePreview = URL.createObjectURL(file);
            },
        };
    }
</script>

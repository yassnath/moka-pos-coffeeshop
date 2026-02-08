@csrf
@isset($paymentMethod)
    @method('PUT')
@endisset

<div class="grid gap-4">
    <div>
        <x-input-label for="name" :value="'Nama Metode'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $paymentMethod->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="code" :value="'Kode (cash/qris/debit/ewallet)'" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $paymentMethod->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" @checked(old('is_active', $paymentMethod->is_active ?? true))>
        <span class="text-sm text-moka-muted">Metode aktif</span>
    </label>
</div>

<div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
    <a href="{{ route('admin.payment-methods.index') }}" class="moka-btn-secondary">Batal</a>
    <x-primary-button>{{ $submitLabel ?? 'Simpan' }}</x-primary-button>
</div>

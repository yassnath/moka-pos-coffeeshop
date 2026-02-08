@csrf
@isset($category)
    @method('PUT')
@endisset

<div class="grid gap-4">
    <div>
        <x-input-label for="name" :value="'Nama Kategori'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40" @checked(old('is_active', $category->is_active ?? true))>
        <span class="text-sm text-moka-muted">Kategori aktif</span>
    </label>
</div>

<div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
    <a href="{{ route('admin.categories.index') }}" class="moka-btn-secondary">Batal</a>
    <x-primary-button>{{ $submitLabel ?? 'Simpan' }}</x-primary-button>
</div>

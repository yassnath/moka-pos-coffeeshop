@php
    $isEdit = isset($cashier);
@endphp

<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="'Nama Kasir'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $cashier->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" :value="'Email'" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $cashier->email ?? '')" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="biodata" :value="'Biodata'" />
        <textarea id="biodata" name="biodata" rows="3" class="mt-1 block w-full rounded-xl border-moka-line text-sm text-moka-ink focus:border-moka-primary focus:ring-moka-primary/20">{{ old('biodata', $cashier->biodata ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('biodata')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password" :value="'Password'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="!$isEdit" autocomplete="new-password" />
        @if($isEdit)
            <p class="moka-helper mt-1">Kosongkan jika tidak ingin mengubah password.</p>
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="'Konfirmasi Password'" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="!$isEdit" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
    </div>
</div>

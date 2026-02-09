@php
    $isEdit = isset($cashier);
@endphp

<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="'Nama Staff'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $cashier->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="role" :value="'Role'" />
        <select id="role" name="role" class="moka-select mt-1 block w-full" required>
            <option value="{{ \App\Models\User::ROLE_KASIR }}" @selected(old('role', $cashier->role ?? \App\Models\User::ROLE_KASIR) === \App\Models\User::ROLE_KASIR)>Kasir</option>
            <option value="{{ \App\Models\User::ROLE_WAITER }}" @selected(old('role', $cashier->role ?? \App\Models\User::ROLE_KASIR) === \App\Models\User::ROLE_WAITER)>Waiter</option>
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" :value="'Email'" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $cashier->email ?? '')" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
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

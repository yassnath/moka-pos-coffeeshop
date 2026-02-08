<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-moka-ink">Konfirmasi Password</h1>
        <p class="mt-1 text-sm text-moka-muted">Masukkan password akun untuk melanjutkan aksi ini.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <x-primary-button class="w-full justify-center">
            Konfirmasi
        </x-primary-button>
    </form>
</x-guest-layout>

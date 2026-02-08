<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-moka-ink">Daftar Akun</h1>
        <p class="mt-1 text-sm text-moka-muted">Buat akun pengguna baru untuk mengakses aplikasi.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="'Nama'" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="'Konfirmasi Password'" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm font-medium text-moka-primary transition hover:text-moka-ink" href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <x-primary-button class="w-full justify-center sm:w-auto">
                Daftar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

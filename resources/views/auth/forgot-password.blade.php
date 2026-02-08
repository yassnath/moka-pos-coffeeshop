<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-moka-ink">Lupa Password</h1>
        <p class="mt-1 text-sm text-moka-muted">Masukkan email untuk menerima link reset password.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <x-primary-button class="w-full justify-center">
            Kirim Link Reset
        </x-primary-button>
    </form>
</x-guest-layout>

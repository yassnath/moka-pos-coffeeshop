<section x-data="{ passwordConfirmOpen: false }">
    <header>
        <h2 class="font-display text-xl font-bold text-moka-ink">Ubah Password</h2>
        <p class="mt-1 text-sm text-moka-muted">Gunakan password panjang dan unik untuk keamanan akun.</p>
    </header>

    <form x-ref="passwordForm" method="post" action="{{ route('password.update') }}" class="mt-6 grid gap-4" @submit.prevent="passwordConfirmOpen = true">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="'Password Saat Ini'" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="'Password Baru'" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="'Konfirmasi Password Baru'" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="moka-btn">Simpan</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 1800)"
                    class="text-sm text-moka-muted"
                >Password diperbarui.</p>
            @endif
        </div>
    </form>

    <x-ui.modal name="passwordConfirmOpen" maxWidth="md">
        <div class="moka-modal-content">
            <div class="moka-modal-header">
                <div>
                    <h3 class="moka-modal-title">Konfirmasi Password</h3>
                    <p class="moka-modal-subtitle">Ubah password akun sekarang?</p>
                </div>
                <button type="button" class="moka-modal-close" @click="passwordConfirmOpen = false" aria-label="Tutup popup">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>

            <div class="moka-modal-footer">
                <button type="button" class="moka-btn-secondary" @click="passwordConfirmOpen = false">Batal</button>
                <button type="button" class="moka-btn" @click="$refs.passwordForm.submit()">Simpan</button>
            </div>
        </div>
    </x-ui.modal>
</section>

<section x-data="{ deleteAccountOpen: @js($errors->userDeletion->isNotEmpty()) }" class="space-y-5">
    <header>
        <h2 class="font-display text-xl font-bold text-moka-ink">Hapus Akun</h2>
        <p class="mt-1 text-sm text-moka-muted">
            Tindakan ini permanen. Semua data akun akan dihapus dan tidak bisa dipulihkan.
        </p>
    </header>

    <button type="button" class="moka-btn-danger" @click="deleteAccountOpen = true">
        Hapus Akun
    </button>

    <x-ui.modal name="deleteAccountOpen" maxWidth="md">
        <form method="post" action="{{ route('profile.destroy') }}" class="moka-modal-content">
            @csrf
            @method('delete')

            <div class="moka-modal-header">
                <div>
                    <h3 class="moka-modal-title">Yakin ingin menghapus akun?</h3>
                    <p class="moka-modal-subtitle">Masukkan password untuk konfirmasi penghapusan akun.</p>
                </div>
                <button type="button" class="moka-modal-close" @click="deleteAccountOpen = false" aria-label="Tutup popup">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>

            <div class="mt-4">
                <x-input-label for="delete_account_password" :value="'Password'" />
                <x-text-input id="delete_account_password" name="password" type="password" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="moka-modal-footer mt-6">
                <button type="button" class="moka-btn-secondary" @click="deleteAccountOpen = false">
                    Batal
                </button>

                <button type="submit" class="moka-btn-danger">
                    Hapus Permanen
                </button>
            </div>
        </form>
    </x-ui.modal>
</section>

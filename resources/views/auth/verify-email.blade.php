<x-guest-layout>
    <div x-data="{ logoutOpen: false }">
    <div class="space-y-4 text-sm text-moka-muted">
        <h1 class="font-display text-2xl font-bold text-moka-ink">Verifikasi Email</h1>
        <p>Terima kasih sudah mendaftar. Cek email untuk link verifikasi sebelum melanjutkan.</p>
        <p>Belum menerima email? Kamu bisa kirim ulang melalui tombol di bawah.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            Link verifikasi baru sudah dikirim ke email kamu.
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center sm:w-auto">
                Kirim Ulang Verifikasi
            </x-primary-button>
        </form>

        <form x-ref="logoutForm" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="button" class="moka-btn-danger w-full justify-center sm:w-auto" @click="logoutOpen = true">
                Logout
            </button>
        </form>
    </div>

    <x-ui.modal name="logoutOpen" maxWidth="md">
        <div class="moka-modal-content">
            <div class="moka-modal-header">
                <div>
                    <h3 class="moka-modal-title">Konfirmasi Logout</h3>
                    <p class="moka-modal-subtitle">Yakin ingin keluar dari akun ini?</p>
                </div>
                <button type="button" class="moka-modal-close" @click="logoutOpen = false" aria-label="Tutup popup">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </button>
            </div>

            <div class="moka-modal-footer">
                <button type="button" class="moka-btn-secondary" @click="logoutOpen = false">Batal</button>
                <button type="button" class="moka-btn-danger" @click="$refs.logoutForm.submit()">Logout</button>
            </div>
        </div>
    </x-ui.modal>
    </div>
</x-guest-layout>

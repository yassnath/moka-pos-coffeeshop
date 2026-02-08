<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Dashboard</h1>
            <p class="text-sm text-moka-muted">Mengarahkan ke halaman utama...</p>
        </div>
    </x-slot>

    <x-ui.card>
        <p class="text-sm text-moka-muted">Klik tombol di bawah jika redirect otomatis belum berjalan.</p>
        <a href="{{ url('/') }}" class="moka-btn mt-4">Buka Halaman Utama</a>
    </x-ui.card>

    <script>
        window.setTimeout(() => {
            window.location.href = @json(url('/'));
        }, 400);
    </script>
</x-app-layout>

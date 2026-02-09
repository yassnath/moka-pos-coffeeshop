<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-display text-2xl font-bold text-moka-ink">Kelola Kasir</h1>
            <p class="text-sm text-moka-muted">Tambah, ubah, dan hapus akun kasir.</p>
        </div>
        <a href="{{ route('admin.cashiers.create') }}" class="moka-btn">Tambah Kasir</a>
    </x-slot>

    <div x-data="{
        deleteOpen: false,
        deleteLabel: '',
        deleteForm: null,
        openDelete(form, label) {
            this.deleteForm = form;
            this.deleteLabel = label;
            this.deleteOpen = true;
        },
        confirmDelete() {
            if (this.deleteForm) {
                this.deleteForm.submit();
            }
        }
    }" class="space-y-4">
        <x-ui.card padding="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="moka-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Dibuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashiers as $cashier)
                            <tr>
                                <td class="font-semibold">{{ $cashier->name }}</td>
                                <td class="text-moka-muted">{{ $cashier->email }}</td>
                                <td>{{ optional($cashier->created_at)->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.cashiers.edit', $cashier) }}" class="text-sm font-semibold text-moka-primary hover:text-moka-ink">Edit</a>
                                    <form action="{{ route('admin.cashiers.destroy', $cashier) }}" method="POST" class="inline-block" x-ref="deleteForm{{ $cashier->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700" @click.prevent="openDelete($refs.deleteForm{{ $cashier->id }}, @js($cashier->name))">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-sm text-moka-muted">Belum ada kasir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div>
            {{ $cashiers->links() }}
        </div>

        <x-ui.modal name="deleteOpen" maxWidth="md">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title">Konfirmasi Hapus</h3>
                        <p class="moka-modal-subtitle">Hapus <span class="font-semibold" x-text="deleteLabel"></span>?</p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="deleteOpen = false" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>
                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="deleteOpen = false">Batal</button>
                    <button type="button" class="moka-btn-danger" @click="confirmDelete()">Hapus</button>
                </div>
            </div>
        </x-ui.modal>
    </div>
</x-app-layout>

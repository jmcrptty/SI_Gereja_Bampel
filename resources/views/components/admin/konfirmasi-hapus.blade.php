@props(['pesan' => 'Data yang dihapus tidak bisa dikembalikan.'])

{{-- Modal konfirmasi hapus. Komponen Livewire induk menyediakan $tampilHapus dan method hapus(). --}}
<flux:modal wire:model.self="tampilHapus" class="w-full max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Hapus data ini?</flux:heading>
            <flux:text class="mt-2">{{ $pesan }}</flux:text>
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="hapus">Hapus</flux:button>
        </div>
    </div>
</flux:modal>

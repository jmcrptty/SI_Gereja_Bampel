<?php

use App\Concerns\KompresFoto;
use App\Models\Pengurus;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Dewan Paroki')] class extends Component {
    use KompresFoto, WithFileUploads, WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $nama = '';
    public string $jabatan = '';
    public string $bidang = '';
    public string $periode = '';
    public string $urutan = '0';
    public $foto;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarPengurus()
    {
        return Pengurus::query()
            ->when($this->cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('jabatan', 'like', "%{$this->cari}%")
                ->orWhere('bidang', 'like', "%{$this->cari}%")))
            ->urut()
            ->paginate(20);
    }

    #[Computed]
    public function pengurusDiedit(): ?Pengurus
    {
        return $this->editId ? Pengurus::find($this->editId) : null;
    }

    public function tambah(): void
    {
        $this->resetForm();

        // Periode dan urutan diisi otomatis agar input pengurus berikutnya lebih cepat.
        $terakhir = Pengurus::query()->orderByDesc('urutan')->first();
        $this->periode = (string) $terakhir?->periode;
        $this->urutan = (string) (($terakhir?->urutan ?? 0) + 1);

        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $pengurus = Pengurus::findOrFail($id);

        $this->editId = $pengurus->id;
        $this->nama = $pengurus->nama;
        $this->jabatan = $pengurus->jabatan;
        $this->bidang = (string) $pengurus->bidang;
        $this->periode = $pengurus->periode;
        $this->urutan = (string) $pengurus->urutan;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'bidang' => ['nullable', 'string', 'max:255'],
            'periode' => ['required', 'string', 'max:20'],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
            'foto' => ['nullable', ...$this->aturanFoto()],
        ]);

        unset($data['foto']);
        $data['bidang'] = $data['bidang'] ?: null;

        $pengurus = $this->editId ? Pengurus::findOrFail($this->editId) : new Pengurus;

        if ($path = $this->prosesFoto($this->foto, $pengurus->foto, 'foto', 'pengurus')) {
            $data['foto'] = $path;
        }

        $pengurus->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Data pengurus disimpan.');
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Pengurus::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Data pengurus dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'jabatan', 'bidang', 'periode', 'urutan', 'foto', 'potong']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Dewan Paroki</flux:heading>
            <flux:subheading>Pengurus terkini. Ditampilkan di website sesuai nomor urutan.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Pengurus</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari nama, jabatan, atau bidang..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarPengurus" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Urutan</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Jabatan</flux:table.column>
            <flux:table.column>Bidang</flux:table.column>
            <flux:table.column>Periode</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarPengurus as $pengurus)
                <flux:table.row :key="$pengurus->id">
                    <flux:table.cell>{{ $pengurus->urutan }}</flux:table.cell>
                    <flux:table.cell variant="strong">
                        <div class="flex items-center gap-3">
                            <flux:avatar size="sm" :src="$pengurus->foto_url" :name="$pengurus->nama" />
                            {{ $pengurus->nama }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $pengurus->jabatan }}</flux:table.cell>
                    <flux:table.cell>{{ $pengurus->bidang ?? '–' }}</flux:table.cell>
                    <flux:table.cell>{{ $pengurus->periode }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $pengurus->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $pengurus->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">Belum ada pengurus.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Pengurus</flux:heading>

            <flux:input wire:model="nama" label="Nama" />

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="jabatan" label="Jabatan" placeholder="contoh: Ketua" />
                <flux:input wire:model="bidang" label="Bidang (opsional)" placeholder="contoh: Liturgi" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="periode" label="Periode" placeholder="contoh: 2024-2027" />
                <flux:input wire:model="urutan" type="number" min="0" label="Urutan tampil" />
            </div>

            <div class="max-w-xs">
                <x-admin.unggah-foto model="foto" label="Foto (opsional)" rasio="aspect-[4/5]" :rasio-potong="4 / 5"
                    :pratinjau="$foto?->isPreviewable() ? $foto->temporaryUrl() : $this->pengurusDiedit?->foto_url"
                    keterangan="Maks 3 MB. Foto potret (4:5) paling pas." />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    <x-admin.konfirmasi-hapus />
</section>

<?php

use App\Concerns\KompresFoto;
use App\Models\Pastor;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Pastor Paroki')] class extends Component {
    use KompresFoto, WithFileUploads;

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $nama = '';
    public string $jabatan = '';
    public string $tarekat = '';
    public string $asal = '';
    public string $tanggal_lahir = '';
    public string $tanggal_tahbisan = '';
    public string $mulai_bertugas = '';
    public string $motto = '';
    public string $cerita = '';
    public string $urutan = '0';
    public $foto;

    #[Computed]
    public function daftarPastor(): Collection
    {
        return Pastor::urut()->get();
    }

    #[Computed]
    public function pastorDiedit(): ?Pastor
    {
        return $this->editId ? Pastor::find($this->editId) : null;
    }

    public function tambah(): void
    {
        $this->resetForm();

        // Pastor pertama otomatis menjadi Pastor Paroki, berikutnya Pastor Rekan.
        $this->jabatan = Pastor::exists() ? 'Pastor Rekan' : 'Pastor Paroki';
        $this->urutan = (string) ((Pastor::max('urutan') ?? 0) + 1);

        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $pastor = Pastor::findOrFail($id);

        $this->editId = $pastor->id;
        $this->nama = $pastor->nama;
        $this->jabatan = $pastor->jabatan;
        $this->tarekat = (string) $pastor->tarekat;
        $this->asal = (string) $pastor->asal;
        $this->tanggal_lahir = (string) $pastor->tanggal_lahir?->format('Y-m-d');
        $this->tanggal_tahbisan = (string) $pastor->tanggal_tahbisan?->format('Y-m-d');
        $this->mulai_bertugas = (string) $pastor->mulai_bertugas;
        $this->motto = (string) $pastor->motto;
        $this->cerita = (string) $pastor->cerita;
        $this->urutan = (string) $pastor->urutan;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'tarekat' => ['nullable', 'string', 'max:255'],
            'asal' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'tanggal_tahbisan' => ['nullable', 'date', 'before_or_equal:today'],
            'mulai_bertugas' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'motto' => ['nullable', 'string', 'max:255'],
            'cerita' => ['nullable', 'string', 'max:20000'],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
            'foto' => ['nullable', ...$this->aturanFoto()],
        ]);

        unset($data['foto']);

        // Kolom opsional yang kosong disimpan sebagai null.
        foreach (['tarekat', 'asal', 'tanggal_lahir', 'tanggal_tahbisan', 'mulai_bertugas', 'motto', 'cerita'] as $kolom) {
            $data[$kolom] = filled($data[$kolom]) ? $data[$kolom] : null;
        }

        $pastor = $this->editId ? Pastor::findOrFail($this->editId) : new Pastor;

        if ($path = $this->prosesFoto($this->foto, $pastor->foto, 'foto', 'pastor')) {
            $data['foto'] = $path;
        }

        $pastor->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Data pastor disimpan.');
    }

    /**
     * Kosongkan foto pastor yang sedang diedit; file lama dihapus oleh HasFileCleanup.
     */
    public function hapusFoto(): void
    {
        Pastor::findOrFail($this->editId)->update(['foto' => null]);

        $this->reset(['foto', 'potong']);
        unset($this->pastorDiedit);

        Flux::toast(variant: 'success', text: 'Foto pastor dihapus.');
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Pastor::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Data pastor dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'jabatan', 'tarekat', 'asal', 'tanggal_lahir', 'tanggal_tahbisan',
            'mulai_bertugas', 'motto', 'cerita', 'urutan', 'foto', 'potong']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Pastor Paroki</flux:heading>
            <flux:subheading>Pastor yang sedang bertugas beserta kisah perkenalannya. Tampil di halaman "Pastor & Dewan Paroki".</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Pastor</flux:button>
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>Urutan</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Jabatan</flux:table.column>
            <flux:table.column>Bertugas sejak</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarPastor as $pastor)
                <flux:table.row :key="$pastor->id">
                    <flux:table.cell>{{ $pastor->urutan }}</flux:table.cell>
                    <flux:table.cell variant="strong">
                        <div class="flex items-center gap-3">
                            <flux:avatar size="sm" :src="$pastor->foto_kecil_url" :name="$pastor->nama" />
                            <div>
                                {{ $pastor->nama }}
                                @if ($pastor->tarekat)
                                    <span class="font-normal text-zinc-500">, {{ $pastor->tarekat }}</span>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $pastor->jabatan }}</flux:table.cell>
                    <flux:table.cell>{{ $pastor->mulai_bertugas ?? '–' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $pastor->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $pastor->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">Belum ada data pastor.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-2xl">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Pastor</flux:heading>

            <div class="grid gap-6 sm:grid-cols-[12rem_1fr]">
                <x-admin.unggah-foto model="foto" label="Foto (opsional)" rasio="aspect-[4/5]" :rasio-potong="4 / 5"
                    :pratinjau="$foto?->isPreviewable() ? $foto->temporaryUrl() : $this->pastorDiedit?->foto_url"
                    :hapus="$this->pastorDiedit?->foto ? 'hapusFoto' : null"
                    keterangan="Maks 3 MB. Foto potret (4:5)." />

                <div class="space-y-6">
                    <flux:input wire:model="nama" label="Nama" placeholder="contoh: P. Yohanes Kelanit" />

                    <div class="grid gap-6 sm:grid-cols-2">
                        <flux:input wire:model="jabatan" label="Jabatan" placeholder="contoh: Pastor Paroki" />
                        <flux:input wire:model="tarekat" label="Tarekat (opsional)" placeholder="contoh: MSC" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <flux:input wire:model="mulai_bertugas" type="number" min="1900" label="Bertugas sejak (tahun)" placeholder="contoh: 2023" />
                        <flux:input wire:model="urutan" type="number" min="0" label="Urutan tampil" />
                    </div>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <flux:input wire:model="asal" label="Daerah asal (opsional)" placeholder="contoh: Kei, Maluku" />
                <flux:input wire:model="tanggal_lahir" type="date" label="Tanggal lahir (opsional)" />
                <flux:input wire:model="tanggal_tahbisan" type="date" label="Tanggal tahbisan (opsional)" />
            </div>

            <flux:input wire:model="motto" label="Motto tahbisan (opsional)" placeholder="contoh: Bukan aku, tetapi Kristus yang hidup di dalam aku (Gal 2:20)" />

            <flux:textarea wire:model="cerita" label="Latar belakang & kisah (opsional)"
                description="Perkenalan, riwayat pendidikan, kisah panggilan, tempat tugas sebelumnya. Pisahkan paragraf dengan baris kosong."
                rows="10" />

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

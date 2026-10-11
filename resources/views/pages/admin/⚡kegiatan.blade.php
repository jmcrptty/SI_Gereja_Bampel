<?php

use App\Concerns\KompresFoto;
use App\Models\Kegiatan;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Kegiatan')] class extends Component {
    use KompresFoto, WithFileUploads, WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $judul = '';
    public string $tanggal = '';
    public string $jam = '';
    public string $tempat = '';
    public string $deskripsi = '';
    public bool $aktif = true;
    public $foto;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarKegiatan()
    {
        return Kegiatan::query()
            ->when($this->cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('judul', 'like', "%{$this->cari}%")
                ->orWhere('tempat', 'like', "%{$this->cari}%")))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);
    }

    #[Computed]
    public function kegiatanDiedit(): ?Kegiatan
    {
        return $this->editId ? Kegiatan::find($this->editId) : null;
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $kegiatan = Kegiatan::findOrFail($id);

        $this->editId = $kegiatan->id;
        $this->judul = $kegiatan->judul;
        $this->tanggal = $kegiatan->tanggal->format('Y-m-d');
        $this->jam = (string) $kegiatan->jam_format;
        $this->tempat = $kegiatan->tempat;
        $this->deskripsi = $kegiatan->deskripsi;
        $this->aktif = $kegiatan->aktif;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam' => ['nullable', 'date_format:H:i'],
            'tempat' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'aktif' => ['boolean'],
            'foto' => ['nullable', ...$this->aturanFoto()],
        ]);

        unset($data['foto']);
        $data['jam'] = $data['jam'] ?: null;

        $kegiatan = $this->editId ? Kegiatan::findOrFail($this->editId) : new Kegiatan;

        if ($path = $this->prosesFoto($this->foto, $kegiatan->foto, 'foto', 'kegiatan')) {
            $data['foto'] = $path;
        }

        $kegiatan->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Kegiatan disimpan.');
    }

    public function toggleAktif(int $id): void
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->update(['aktif' => ! $kegiatan->aktif]);
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Kegiatan::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Kegiatan dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'judul', 'tanggal', 'jam', 'tempat', 'deskripsi', 'aktif', 'foto', 'potong']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Kegiatan Paroki</flux:heading>
            <flux:subheading>Kegiatan yang lewat tanggal otomatis pindah ke "Kegiatan Sebelumnya" di website.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Kegiatan</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari judul atau tempat..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarKegiatan" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Kegiatan</flux:table.column>
            <flux:table.column>Tanggal</flux:table.column>
            <flux:table.column>Tempat</flux:table.column>
            <flux:table.column>Aktif</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarKegiatan as $kegiatan)
                <flux:table.row :key="$kegiatan->id">
                    <flux:table.cell variant="strong">
                        <div class="flex items-center gap-3">
                            @if ($kegiatan->foto_url)
                                <img src="{{ $kegiatan->foto_url }}" alt="" class="size-10 rounded object-cover">
                            @else
                                <div class="flex size-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                    <flux:icon.calendar class="size-5 text-zinc-400" />
                                </div>
                            @endif
                            {{ $kegiatan->judul }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $kegiatan->tanggal->translatedFormat('j F Y') }}
                        @if ($kegiatan->jam_format)
                            <span class="block text-xs text-zinc-500">{{ $kegiatan->jam_format }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $kegiatan->tempat }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$kegiatan->aktif" wire:click="toggleAktif({{ $kegiatan->id }})" />
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $kegiatan->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $kegiatan->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">Belum ada kegiatan.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Kegiatan</flux:heading>

            <flux:input wire:model="judul" label="Judul" />

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="tanggal" type="date" label="Tanggal" />
                <flux:input wire:model="jam" type="time" label="Jam (opsional)" />
            </div>

            <flux:input wire:model="tempat" label="Tempat" />
            <flux:textarea wire:model="deskripsi" label="Deskripsi" rows="5" />

            <x-admin.unggah-foto model="foto" label="Foto (opsional)" rasio="aspect-video" :rasio-potong="16 / 9"
                :pratinjau="$foto?->isPreviewable() ? $foto->temporaryUrl() : $this->kegiatanDiedit?->foto_url"
                keterangan="Maks 3 MB. Tanpa foto akan tampil ikon default." />

            <flux:switch wire:model="aktif" label="Aktif (tampil di website)" align="left" />

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

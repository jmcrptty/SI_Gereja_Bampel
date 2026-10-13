<?php

use App\Concerns\KompresFoto;
use App\Models\Galeri;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Galeri')] class extends Component {
    use KompresFoto, WithFileUploads, WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $judul = '';
    public string $tanggal = '';

    /** Banyak foto sekaligus saat tambah. */
    public array $fotos = [];

    /** Foto pengganti saat edit. */
    public $foto;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarFoto()
    {
        return Galeri::query()
            ->when($this->cari !== '', fn ($q) => $q->where('judul', 'like', "%{$this->cari}%"))
            ->terbaru()
            ->paginate(24);
    }

    #[Computed]
    public function fotoDiedit(): ?Galeri
    {
        return $this->editId ? Galeri::find($this->editId) : null;
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->tanggal = today()->format('Y-m-d');
        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $galeri = Galeri::findOrFail($id);

        $this->editId = $galeri->id;
        $this->judul = $galeri->judul;
        $this->tanggal = (string) $galeri->tanggal?->format('Y-m-d');

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        // Kompres banyak foto bisa melebihi max_execution_time 30 detik di shared hosting.
        set_time_limit(120);

        $data = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date'],
            ...$this->editId
                ? ['foto' => ['nullable', ...$this->aturanFoto()]]
                : ['fotos' => ['required', 'array', 'max:10'], 'fotos.*' => $this->aturanFoto()],
        ]);

        $data = ['judul' => $data['judul'], 'tanggal' => $data['tanggal'] ?: null];

        if ($this->editId) {
            $galeri = Galeri::findOrFail($this->editId);

            if ($path = $this->prosesFoto($this->foto, $galeri->foto, 'foto', 'galeri')) {
                $data['foto'] = $path;
            }

            $galeri->update($data);
            $pesan = 'Foto diperbarui.';
        } else {
            // Crop per foto memakai kunci fotos.0, fotos.1, ...
            foreach ($this->fotos as $i => $file) {
                Galeri::create([...$data, 'foto' => $this->prosesFoto($file, null, "fotos.{$i}", 'galeri')]);
            }
            $pesan = count($this->fotos).' foto ditambahkan.';
        }

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: $pesan);
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Galeri::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Foto dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'judul', 'tanggal', 'fotos', 'foto', 'potong']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Galeri</flux:heading>
            <flux:subheading>Bisa upload banyak foto sekaligus dengan judul yang sama.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Foto</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari judul foto..." class="mt-6 max-w-sm" />

    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @forelse ($this->daftarFoto as $galeri)
            <div wire:key="galeri-{{ $galeri->id }}" class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                <img src="{{ $galeri->foto_kecil_url }}" alt="{{ $galeri->judul }}" class="aspect-square w-full object-cover" loading="lazy">
                <div class="p-2">
                    <flux:text class="truncate text-sm font-medium">{{ $galeri->judul }}</flux:text>
                    <flux:text class="text-xs">{{ $galeri->tanggal?->translatedFormat('j F Y') ?? '–' }}</flux:text>
                    <div class="mt-1 flex justify-end">
                        <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="edit({{ $galeri->id }})" />
                        <flux:button size="xs" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $galeri->id }})" />
                    </div>
                </div>
            </div>
        @empty
            <flux:text class="col-span-full py-8 text-center">Belum ada foto.</flux:text>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->daftarFoto->links() }}
    </div>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit Foto' : 'Tambah Foto' }}</flux:heading>

            <flux:input wire:model="judul" label="Judul" placeholder="contoh: Misa Paskah 2026" />
            <flux:input wire:model="tanggal" type="date" label="Tanggal (opsional)" />

            @if ($editId)
                <x-admin.unggah-foto model="foto" label="Foto (klik untuk mengganti)" rasio="aspect-[4/3]"
                    :pratinjau="$foto?->isPreviewable() ? $foto->temporaryUrl() : $this->fotoDiedit?->foto_url" />
            @else
                <div>
                    <flux:input type="file" wire:model="fotos" label="Foto" accept="image/*" multiple />
                    <flux:text class="mt-1 text-xs">Bisa pilih banyak foto (maks 10 sekali upload), masing-masing maks 3 MB. Klik "Crop" pada foto untuk memotongnya.</flux:text>
                    <flux:error name="fotos.*" />
                </div>

                @if ($fotos)
                    {{-- Pratinjau per foto, masing-masing bisa di-crop (kunci fotos.0, fotos.1, ...) --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($fotos as $index => $file)
                            @continue (! $file->isPreviewable())
                            <x-admin.unggah-foto :model="'fotos.'.$index" :input="false" rasio="aspect-square" :keterangan="null"
                                :pratinjau="$file->temporaryUrl()" />
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    <x-admin.konfirmasi-hapus pesan="Foto akan dihapus permanen." />
</section>

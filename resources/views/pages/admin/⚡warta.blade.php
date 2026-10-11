<?php

use App\Models\Warta;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Warta')] class extends Component {
    use WithFileUploads, WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $judul = '';
    public string $tanggal = '';
    public string $ringkasan = '';
    public string $status = Warta::DRAFT;
    public $file_pdf;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarWarta()
    {
        return Warta::query()
            ->when($this->cari !== '', fn ($q) => $q->where('judul', 'like', "%{$this->cari}%"))
            ->terbaru()
            ->paginate(15);
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

        $warta = Warta::findOrFail($id);

        $this->editId = $warta->id;
        $this->judul = $warta->judul;
        $this->tanggal = $warta->tanggal->format('Y-m-d');
        $this->ringkasan = (string) $warta->ringkasan;
        $this->status = $warta->status;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'ringkasan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Warta::DRAFT, Warta::TERBIT])],
            'file_pdf' => [$this->editId ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        unset($data['file_pdf']);
        $data['ringkasan'] = $data['ringkasan'] ?: null;

        if ($this->file_pdf) {
            $data['file_pdf'] = $this->file_pdf->store('warta', 'public');
        }

        $warta = $this->editId ? Warta::findOrFail($this->editId) : new Warta;
        $warta->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Warta disimpan.');
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Warta::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Warta dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'judul', 'tanggal', 'ringkasan', 'status', 'file_pdf']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Warta</flux:heading>
            <flux:subheading>Warta paroki dalam bentuk PDF. Hanya yang berstatus terbit yang tampil di website.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Warta</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari judul warta..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarWarta" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Tanggal</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>PDF</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarWarta as $warta)
                <flux:table.row :key="$warta->id">
                    <flux:table.cell variant="strong">{{ $warta->judul }}</flux:table.cell>
                    <flux:table.cell>{{ $warta->tanggal->translatedFormat('j F Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($warta->status === \App\Models\Warta::TERBIT)
                            <flux:badge color="green" size="sm">Terbit</flux:badge>
                        @else
                            <flux:badge size="sm">Draft</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:link :href="$warta->pdf_url" target="_blank">Buka</flux:link>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $warta->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $warta->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">Belum ada warta.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Warta</flux:heading>

            <flux:input wire:model="judul" label="Judul" placeholder="contoh: Warta Paroki Minggu Biasa XXVIII" />
            <flux:input wire:model="tanggal" type="date" label="Tanggal" />
            <flux:textarea wire:model="ringkasan" label="Ringkasan (opsional)" rows="3" />

            <div>
                <flux:input type="file" wire:model="file_pdf" label="File PDF" accept="application/pdf" />
                <flux:text class="mt-1 text-xs">
                    Maks 5 MB.{{ $editId ? ' Kosongkan jika tidak ingin mengganti file.' : '' }}
                </flux:text>
            </div>

            <flux:select wire:model="status" label="Status">
                <flux:select.option :value="\App\Models\Warta::DRAFT">Draft (belum tampil)</flux:select.option>
                <flux:select.option :value="\App\Models\Warta::TERBIT">Terbit (tampil di website)</flux:select.option>
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    <x-admin.konfirmasi-hapus pesan="Warta beserta file PDF-nya akan dihapus permanen." />
</section>

<?php

use App\Models\Lingkungan;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Lingkungan')] class extends Component {
    use WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $nama = '';
    public string $wilayah = '';
    public string $ketua = '';
    public string $jumlah_kk = '';
    public string $jadwal_doa = '';
    public string $cakupan_jalan = '';
    public string $urutan = '0';

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarLingkungan()
    {
        return Lingkungan::query()
            ->when($this->cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('wilayah', 'like', "%{$this->cari}%")
                ->orWhere('ketua', 'like', "%{$this->cari}%")))
            ->urut()
            ->paginate(20);
    }

    /**
     * Nama wilayah yang sudah ada, untuk saran isian.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function daftarWilayah(): array
    {
        return Lingkungan::query()->whereNotNull('wilayah')->distinct()->orderBy('wilayah')->pluck('wilayah')->all();
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $lingkungan = Lingkungan::findOrFail($id);

        $this->editId = $lingkungan->id;
        $this->nama = $lingkungan->nama;
        $this->wilayah = (string) $lingkungan->wilayah;
        $this->ketua = (string) $lingkungan->ketua;
        $this->jumlah_kk = (string) $lingkungan->jumlah_kk;
        $this->jadwal_doa = (string) $lingkungan->jadwal_doa;
        $this->cakupan_jalan = (string) $lingkungan->cakupan_jalan;
        $this->urutan = (string) $lingkungan->urutan;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'wilayah' => ['nullable', 'string', 'max:255'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'jumlah_kk' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'jadwal_doa' => ['nullable', 'string', 'max:255'],
            'cakupan_jalan' => ['nullable', 'string', 'max:5000'],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $data = array_map(fn ($nilai) => $nilai === '' ? null : $nilai, $data);

        $lingkungan = $this->editId ? Lingkungan::findOrFail($this->editId) : new Lingkungan;
        $lingkungan->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Data lingkungan disimpan.');
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        Lingkungan::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Data lingkungan dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'wilayah', 'ketua', 'jumlah_kk', 'jadwal_doa', 'cakupan_jalan', 'urutan']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Lingkungan</flux:heading>
            <flux:subheading>Di website dikelompokkan per wilayah/stasi, lalu diurutkan sesuai nomor urutan.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Lingkungan</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari nama, wilayah, atau ketua..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarLingkungan" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Wilayah</flux:table.column>
            <flux:table.column>Lingkungan</flux:table.column>
            <flux:table.column>Ketua</flux:table.column>
            <flux:table.column>Jumlah KK</flux:table.column>
            <flux:table.column>Jadwal Doa</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarLingkungan as $lingkungan)
                <flux:table.row :key="$lingkungan->id">
                    <flux:table.cell>{{ $lingkungan->wilayah ?? '–' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $lingkungan->nama }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->ketua ?? '–' }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->jumlah_kk ?? '–' }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->jadwal_doa ?? '–' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $lingkungan->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $lingkungan->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">Belum ada lingkungan.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Lingkungan</flux:heading>

            <flux:input wire:model="nama" label="Nama lingkungan" />

            <div>
                <flux:input wire:model="wilayah" label="Wilayah / stasi" list="daftar-wilayah" />
                <datalist id="daftar-wilayah">
                    @foreach ($this->daftarWilayah as $namaWilayah)
                        <option value="{{ $namaWilayah }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="ketua" label="Nama ketua" description="Nama saja, tanpa nomor HP." />
                <flux:input wire:model="jumlah_kk" type="number" min="0" label="Jumlah KK" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="jadwal_doa" label="Jadwal doa" placeholder="contoh: Rabu, 19.00" />
                <flux:input wire:model="urutan" type="number" min="0" label="Urutan tampil" />
            </div>

            <flux:textarea wire:model="cakupan_jalan" label="Cakupan jalan" description="Satu jalan per baris." rows="4" />

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

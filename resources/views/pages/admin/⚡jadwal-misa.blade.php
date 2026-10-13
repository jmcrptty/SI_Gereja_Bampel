<?php

use App\Models\JadwalMisa;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Jadwal Misa')] class extends Component {
    use WithPagination;

    public string $cari = '';

    public bool $tampilForm = false;
    public bool $tampilHapus = false;
    public ?int $editId = null;
    public ?int $hapusId = null;

    public string $jenis = JadwalMisa::RUTIN;
    public string $nama_perayaan = '';
    public string $hari = '';
    public string $tanggal = '';
    public string $jam = '';
    public string $tempat = '';
    public bool $aktif = true;

    public function updatedCari(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function daftarMisa()
    {
        // Nama hari yang diawali kata pencarian (contoh: "ming" → 7) agar misa rutin juga bisa dicari.
        $hariCocok = array_keys(array_filter(
            JadwalMisa::HARI,
            fn (string $namaHari) => str_starts_with(mb_strtolower($namaHari), mb_strtolower(trim($this->cari))),
        ));

        return JadwalMisa::query()
            ->when($this->cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nama_perayaan', 'like', "%{$this->cari}%")
                ->orWhere('tempat', 'like', "%{$this->cari}%")
                ->when($hariCocok, fn ($q) => $q->orWhereIn('hari', $hariCocok))))
            ->orderBy('jenis')
            ->orderBy('hari')
            ->orderByDesc('tanggal')
            ->orderBy('jam')
            ->paginate(15);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->tampilForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $misa = JadwalMisa::findOrFail($id);

        $this->editId = $misa->id;
        $this->jenis = $misa->jenis;
        $this->nama_perayaan = (string) $misa->nama_perayaan;
        $this->hari = (string) $misa->hari;
        $this->tanggal = (string) $misa->tanggal?->format('Y-m-d');
        $this->jam = $misa->jam_format;
        $this->tempat = $misa->tempat;
        $this->aktif = $misa->aktif;

        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'jenis' => ['required', Rule::in([JadwalMisa::RUTIN, JadwalMisa::KHUSUS])],
            'hari' => ['nullable', 'required_if:jenis,'.JadwalMisa::RUTIN, 'integer', 'between:1,7'],
            'nama_perayaan' => ['nullable', 'required_if:jenis,'.JadwalMisa::KHUSUS, 'string', 'max:255'],
            'tanggal' => ['nullable', 'required_if:jenis,'.JadwalMisa::KHUSUS, 'date'],
            'jam' => ['required', 'date_format:H:i'],
            'tempat' => ['required', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]);

        $data = array_map(fn ($nilai) => $nilai === '' ? null : $nilai, $data);

        $misa = $this->editId ? JadwalMisa::findOrFail($this->editId) : new JadwalMisa;
        $misa->fill($data)->save();

        $this->tampilForm = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: 'Jadwal misa disimpan.');
    }

    public function toggleAktif(int $id): void
    {
        $misa = JadwalMisa::findOrFail($id);
        $misa->update(['aktif' => ! $misa->aktif]);
    }

    public function konfirmasiHapus(int $id): void
    {
        $this->hapusId = $id;
        $this->tampilHapus = true;
    }

    public function hapus(): void
    {
        JadwalMisa::findOrFail($this->hapusId)->delete();

        $this->tampilHapus = false;
        $this->hapusId = null;

        Flux::toast(variant: 'success', text: 'Jadwal misa dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'jenis', 'nama_perayaan', 'hari', 'tanggal', 'jam', 'tempat', 'aktif']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Jadwal Misa</flux:heading>
            <flux:subheading>Misa rutin mingguan dan misa hari raya.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Misa</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari hari, nama perayaan, atau tempat..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarMisa" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Jenis</flux:table.column>
            <flux:table.column>Hari / Perayaan</flux:table.column>
            <flux:table.column>Jam</flux:table.column>
            <flux:table.column>Tempat</flux:table.column>
            <flux:table.column>Aktif</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarMisa as $misa)
                <flux:table.row :key="$misa->id">
                    <flux:table.cell>
                        @if ($misa->jenis === \App\Models\JadwalMisa::KHUSUS)
                            <flux:badge color="amber" size="sm">Hari Raya</flux:badge>
                        @else
                            <flux:badge size="sm">Rutin</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        @if ($misa->jenis === \App\Models\JadwalMisa::KHUSUS)
                            {{ $misa->nama_perayaan }}
                            @if ($misa->tanggal?->lt(today()))
                                <flux:badge color="zinc" size="sm" class="ms-1">Sudah lewat</flux:badge>
                            @endif
                            <span class="block text-xs font-normal text-zinc-500">{{ $misa->tanggal?->translatedFormat('l, j F Y') }}</span>
                        @else
                            {{ $misa->nama_hari }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $misa->jam_format }}</flux:table.cell>
                    <flux:table.cell>{{ $misa->tempat }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$misa->aktif" wire:click="toggleAktif({{ $misa->id }})" />
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $misa->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $misa->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">Belum ada jadwal misa.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-lg">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Jadwal Misa</flux:heading>

            <flux:select wire:model.live="jenis" label="Jenis misa">
                <flux:select.option :value="\App\Models\JadwalMisa::RUTIN">Rutin mingguan</flux:select.option>
                <flux:select.option :value="\App\Models\JadwalMisa::KHUSUS">Hari raya / khusus</flux:select.option>
            </flux:select>

            @if ($jenis === \App\Models\JadwalMisa::RUTIN)
                <flux:select wire:model="hari" label="Hari">
                    <flux:select.option value="">Pilih hari...</flux:select.option>
                    @foreach (\App\Models\JadwalMisa::HARI as $nomor => $namaHari)
                        <flux:select.option :value="$nomor">{{ $namaHari }}</flux:select.option>
                    @endforeach
                </flux:select>
            @else
                <flux:input wire:model="nama_perayaan" label="Nama perayaan" placeholder="contoh: Malam Natal" />
                <flux:input wire:model="tanggal" type="date" label="Tanggal" />
            @endif

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:input wire:model="jam" type="time" label="Jam" />
                <flux:input wire:model="tempat" label="Tempat" placeholder="contoh: Gereja Paroki" />
            </div>

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

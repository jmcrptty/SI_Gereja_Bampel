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
    public string $ketua = '';
    public string $jumlah_kk = '';
    public string $jadwal_doa = '';
    public string $cakupan_jalan = '';
    public string $urutan = '0';

    /** @var array<int, array{0: float, 1: float}> Titik sudut arsiran [lat, lng], diisi dari peta. */
    public array $batas_wilayah = [];

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
                ->orWhere('ketua', 'like', "%{$this->cari}%")))
            ->urut()
            ->paginate(20);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->muatPeta();
        $this->tampilForm = true;
    }

    /**
     * Kirim arsiran yang sedang diedit dan arsiran lingkungan lain (sebagai acuan) ke peta di form.
     */
    private function muatPeta(): void
    {
        $lainnya = Lingkungan::punyaBatas()
            ->when($this->editId, fn ($q) => $q->whereKeyNot($this->editId))
            ->get(['nama', 'batas_wilayah'])
            ->map(fn (Lingkungan $l) => ['nama' => $l->nama, 'titik' => $l->batas_wilayah])
            ->all();

        $this->dispatch('muat-batas', batas: $this->batas_wilayah, lainnya: $lainnya);
    }

    public function edit(int $id): void
    {
        $this->resetForm();

        $lingkungan = Lingkungan::findOrFail($id);

        $this->editId = $lingkungan->id;
        $this->nama = $lingkungan->nama;
        $this->ketua = (string) $lingkungan->ketua;
        $this->jumlah_kk = (string) $lingkungan->jumlah_kk;
        $this->jadwal_doa = (string) $lingkungan->jadwal_doa;
        $this->cakupan_jalan = (string) $lingkungan->cakupan_jalan;
        $this->urutan = (string) $lingkungan->urutan;
        $this->batas_wilayah = $lingkungan->batas_wilayah ?? [];

        $this->muatPeta();
        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'jumlah_kk' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'jadwal_doa' => ['nullable', 'string', 'max:255'],
            'cakupan_jalan' => ['nullable', 'string', 'max:5000'],
            'urutan' => ['required', 'integer', 'min:0', 'max:65535'],
            'batas_wilayah' => ['array', 'max:300', function (string $atribut, array $nilai, Closure $gagal) {
                if ($nilai !== [] && count($nilai) < 3) {
                    $gagal('Arsiran butuh minimal 3 titik, atau kosongkan saja.');
                }
            }],
            'batas_wilayah.*' => ['array', 'size:2'],
            'batas_wilayah.*.0' => ['numeric', 'between:-90,90'],
            'batas_wilayah.*.1' => ['numeric', 'between:-180,180'],
        ]);

        $data = array_map(fn ($nilai) => $nilai === '' || $nilai === [] ? null : $nilai, $data);

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
        $this->reset(['editId', 'nama', 'ketua', 'jumlah_kk', 'jadwal_doa', 'cakupan_jalan', 'urutan', 'batas_wilayah']);
        $this->resetValidation();
    }
}; ?>

<section class="w-full">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Lingkungan</flux:heading>
            <flux:subheading>Diurutkan sesuai nomor urutan. Gambar arsiran batas tiap lingkungan agar tampil di peta website.</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="tambah">Tambah Lingkungan</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="cari" icon="magnifying-glass" placeholder="Cari nama lingkungan atau ketua..." class="mt-6 max-w-sm" />

    <flux:table :paginate="$this->daftarLingkungan" class="mt-4">
        <flux:table.columns>
            <flux:table.column>Urutan</flux:table.column>
            <flux:table.column>Lingkungan</flux:table.column>
            <flux:table.column>Ketua</flux:table.column>
            <flux:table.column>Jumlah KK</flux:table.column>
            <flux:table.column>Jadwal Doa</flux:table.column>
            <flux:table.column>Peta</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->daftarLingkungan as $lingkungan)
                <flux:table.row :key="$lingkungan->id">
                    <flux:table.cell>{{ $lingkungan->urutan }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $lingkungan->nama }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->ketua ?? '–' }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->jumlah_kk ?? '–' }}</flux:table.cell>
                    <flux:table.cell>{{ $lingkungan->jadwal_doa ?? '–' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($lingkungan->batas_wilayah)
                            <flux:badge size="sm" color="green" icon="map">Ada arsiran</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Belum</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $lingkungan->id }})" />
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="konfirmasiHapus({{ $lingkungan->id }})" />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center">Belum ada lingkungan.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="tampilForm" class="w-full max-w-6xl">
        <form wire:submit="simpan" class="space-y-6">
            <flux:heading size="lg">{{ $editId ? 'Edit' : 'Tambah' }} Lingkungan</flux:heading>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)]">
                {{-- Data lingkungan --}}
                <div class="space-y-6">
                    <flux:input wire:model="nama" label="Nama lingkungan" />
                    <flux:input wire:model="ketua" label="Nama ketua" description="Nama saja, tanpa nomor HP." />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="jumlah_kk" type="number" min="0" label="Jumlah KK" />
                        <flux:input wire:model="urutan" type="number" min="0" label="Urutan tampil" />
                    </div>

                    <flux:input wire:model="jadwal_doa" label="Jadwal doa" placeholder="contoh: Rabu, 19.00" />

                    <flux:textarea wire:model="cakupan_jalan" label="Cakupan jalan" description="Satu jalan per baris." rows="4" />
                </div>

                {{-- Editor arsiran batas lingkungan. Status (jumlah titik, luas) dikirim dari peta.js lewat event "peta-status". --}}
                <div data-peta-batas
                     x-data="{ jumlah: 0, terpilih: null, luas: null, bisaUrungkan: false, layarPenuh: false, bantuan: false }"
                     x-on:peta-status="Object.assign($data, $event.detail)"
                     x-on:fullscreenchange.document="layarPenuh = document.fullscreenElement === $el"
                     :class="layarPenuh && 'flex h-full flex-col bg-white p-3'">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-zinc-800">Arsiran batas lingkungan</p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                                  :class="jumlah >= 3 ? 'bg-green-100 text-green-800' : 'bg-zinc-100 text-zinc-600'">
                                <span x-text="jumlah"></span> titik<template x-if="luas !== null"><span> · ± <span x-text="luas < 10 ? luas.toFixed(2) : Math.round(luas).toLocaleString('id-ID')"></span> ha</span></template>
                            </span>
                        </div>
                        <flux:button size="xs" variant="ghost" icon="question-mark-circle" x-on:click="bantuan = ! bantuan">Cara menggambar</flux:button>
                    </div>

                    <div x-show="bantuan" x-cloak class="mb-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-900 ring-1 ring-amber-200">
                        <ul class="list-disc space-y-1 pl-5">
                            <li><strong>Klik di peta</strong> untuk menambah titik sudut, berurutan mengelilingi lingkungan.</li>
                            <li><strong>Geser titik</strong> untuk memindahkannya. Klik tanda <strong>+</strong> di tengah sisi untuk menyisipkan titik.</li>
                            <li><strong>Klik titik</strong> untuk memilihnya (kuning): klik di peta berikutnya menyisipkan titik setelahnya, atau tekan <em>Hapus titik</em>. Klik kanan / ketuk dua kali pada titik untuk langsung menghapus.</li>
                            <li>Ganti ke mode <strong>Satelit</strong> (tombol lapisan di kanan atas peta) agar rumah dan jalan terlihat jelas.</li>
                            <li>Arsiran abu-abu putus-putus = lingkungan lain, sebagai acuan agar batas tidak tumpang tindih.</li>
                        </ul>
                    </div>

                    <div class="mb-2 flex flex-wrap gap-1.5">
                        <flux:button size="sm" icon="arrow-uturn-left" x-on:click="$dispatch('batas-urungkan')" x-bind:disabled="! bisaUrungkan">Urungkan</flux:button>
                        <flux:button size="sm" icon="x-mark" x-on:click="$dispatch('batas-hapus-titik')" x-bind:disabled="terpilih === null">Hapus titik</flux:button>
                        <flux:button size="sm" icon="viewfinder-circle" x-on:click="$dispatch('batas-arahkan')">Arahkan ke arsiran</flux:button>
                        <flux:button size="sm" icon="map-pin" x-on:click="$dispatch('batas-lokasi')">Lokasi saya</flux:button>
                        <template x-if="document.fullscreenEnabled">
                            <flux:button size="sm" icon="arrows-pointing-out" x-on:click="layarPenuh ? document.exitFullscreen() : $root.requestFullscreen()">
                                <span x-text="layarPenuh ? 'Keluar layar penuh' : 'Layar penuh'"></span>
                            </flux:button>
                        </template>
                        <flux:button size="sm" variant="danger" icon="trash" class="ms-auto" x-on:click="confirm('Hapus semua titik arsiran?') && $dispatch('batas-kosongkan')" x-bind:disabled="jumlah === 0">Kosongkan</flux:button>
                    </div>

                    <div wire:ignore data-peta
                         class="isolate h-[26rem] overflow-hidden rounded-lg ring-1 ring-zinc-200 lg:h-[32rem]"
                         :class="layarPenuh && '!h-auto flex-1'"></div>

                    <p class="mt-2 text-xs text-zinc-500" x-show="jumlah > 0 && jumlah < 3" x-cloak>Tambahkan minimal <span x-text="3 - jumlah"></span> titik lagi agar arsiran terbentuk.</p>
                    <flux:error name="batas_wilayah" />
                </div>
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

@assets
    @vite('resources/js/peta.js')
@endassets

@script
<script>
    const wadah = $wire.$el.querySelector('[data-peta-batas]');
    const alat = window.PetaParoki.penggambar(wadah.querySelector('[data-peta]'), {
        pusat: @js(\App\Models\Lingkungan::PUSAT_PETA),
        // Disimpan di sisi browser dulu, baru dikirim ke server saat form disimpan.
        ubah: (titik) => $wire.$set('batas_wilayah', titik, false),
        status: (info) => wadah.dispatchEvent(new CustomEvent('peta-status', { detail: info })),
    });

    $wire.on('muat-batas', ({ batas, lainnya }) => alat.muat(batas, lainnya));
    wadah.addEventListener('batas-urungkan', () => alat.urungkan());
    wadah.addEventListener('batas-hapus-titik', () => alat.hapusTerpilih());
    wadah.addEventListener('batas-kosongkan', () => alat.kosongkan());
    wadah.addEventListener('batas-arahkan', () => alat.arahkan());
    wadah.addEventListener('batas-lokasi', () => alat.lokasiSaya());
</script>
@endscript

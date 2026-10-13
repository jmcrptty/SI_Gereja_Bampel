<?php

use App\Concerns\KompresFoto;
use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profil Paroki')] class extends Component {
    use KompresFoto, WithFileUploads;

    public string $nama_gereja = '';
    public string $pelindung = '';
    public string $kisah_pelindung = '';
    public string $sejarah_singkat = '';
    public string $sejarah = '';
    public string $visi = '';
    public string $misi = '';
    public string $alamat = '';
    public string $telepon = '';
    public string $whatsapp = '';
    public string $email = '';
    public string $link_maps = '';
    public string $facebook = '';
    public string $instagram = '';
    public string $youtube = '';
    public string $jam_sekretariat = '';
    public bool $tampil_card_misa = true;
    public bool $tampil_card_kegiatan = true;

    public $logo;
    public $foto_hero;
    public $foto_gereja;
    public $foto_pelindung;

    /** Kolom teks yang diisi dari / disimpan ke tabel settings. */
    private const KOLOM_TEKS = [
        'nama_gereja', 'pelindung', 'kisah_pelindung', 'sejarah_singkat', 'sejarah', 'visi', 'misi',
        'alamat', 'telepon', 'whatsapp', 'email', 'link_maps',
        'facebook', 'instagram', 'youtube', 'jam_sekretariat',
    ];

    /** Kolom foto beserta folder penyimpanannya. */
    private const KOLOM_FOTO = ['logo', 'foto_hero', 'foto_gereja', 'foto_pelindung'];

    public function mount(): void
    {
        $setting = Setting::current();

        foreach (self::KOLOM_TEKS as $kolom) {
            $this->{$kolom} = (string) $setting->{$kolom};
        }

        $this->tampil_card_misa = $setting->tampil_card_misa;
        $this->tampil_card_kegiatan = $setting->tampil_card_kegiatan;
    }

    public function simpan(): void
    {
        // Nama akun ("@paroki") diubah menjadi link lengkap sebelum divalidasi.
        foreach (['instagram', 'youtube', 'facebook'] as $medsos) {
            $this->{$medsos} = (string) Setting::linkMedsos($medsos, $this->{$medsos});
        }

        $data = $this->validate([
            'nama_gereja' => ['required', 'string', 'max:255'],
            'pelindung' => ['nullable', 'string', 'max:255'],
            'kisah_pelindung' => ['nullable', 'string', 'max:10000'],
            'sejarah_singkat' => ['nullable', 'string', 'max:2000'],
            'sejarah' => ['nullable', 'string'],
            'visi' => ['nullable', 'string', 'max:2000'],
            'misi' => ['nullable', 'string', 'max:5000'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'link_maps' => ['nullable', 'url', 'max:500'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'jam_sekretariat' => ['nullable', 'string', 'max:2000'],
            'tampil_card_misa' => ['boolean'],
            'tampil_card_kegiatan' => ['boolean'],
            'logo' => ['nullable', ...$this->aturanFoto()],
            'foto_hero' => ['nullable', ...$this->aturanFoto()],
            'foto_gereja' => ['nullable', ...$this->aturanFoto()],
            'foto_pelindung' => ['nullable', ...$this->aturanFoto()],
        ]);

        $setting = Setting::current();

        foreach (self::KOLOM_FOTO as $kolom) {
            unset($data[$kolom]);

            // Foto baru (dengan crop bila ada) atau crop ulang foto yang sudah tersimpan.
            if ($path = $this->prosesFoto($this->{$kolom}, $setting->{$kolom}, $kolom, 'profil')) {
                $data[$kolom] = $path;
            }
        }

        // String kosong disimpan sebagai NULL.
        $data = array_map(fn ($nilai) => $nilai === '' ? null : $nilai, $data);

        $setting->update($data);

        $this->reset([...self::KOLOM_FOTO, 'potong']);

        Flux::toast(variant: 'success', text: 'Profil paroki disimpan.');
    }

    /**
     * Kosongkan satu kolom foto; file lama dihapus oleh HasFileCleanup, website kembali memakai gambar default.
     */
    public function hapusFoto(string $kolom): void
    {
        abort_unless(in_array($kolom, self::KOLOM_FOTO, true), 404);

        Setting::current()->update([$kolom => null]);

        $this->reset($kolom);
        unset($this->potong[$kolom]);

        Flux::toast(variant: 'success', text: 'Foto dihapus.');
    }
}; ?>

<section class="w-full">
    @php
        $setting = \App\Models\Setting::current();
        // URL pratinjau: foto yang baru dipilih, atau foto yang sudah tersimpan.
        $pratinjau = fn (string $kolom) => $this->{$kolom}?->isPreviewable()
            ? $this->{$kolom}->temporaryUrl()
            : $setting->fileUrl($kolom);
        // Aksi tombol "Hapus foto", hanya bila foto sudah tersimpan.
        $hapus = fn (string $kolom) => $setting->{$kolom} ? "hapusFoto('{$kolom}')" : null;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Profil Paroki</flux:heading>
            <flux:subheading>Identitas, pelindung, sejarah, kontak, dan tampilan beranda.</flux:subheading>
        </div>
        <flux:button :href="route('profil')" target="_blank" icon="arrow-top-right-on-square" size="sm">Lihat halaman Profil</flux:button>
    </div>

    <form wire:submit="simpan" class="mt-8 grid gap-6 xl:grid-cols-3">
        {{-- Kolom utama --}}
        <div class="space-y-6 xl:col-span-2">
            <x-admin.kartu judul="Identitas Paroki" deskripsi="Nama dan foto yang tampil di navbar, beranda, dan halaman profil." icon="building-library">
                <flux:input wire:model="nama_gereja" label="Nama paroki" required />

                <div class="grid gap-5 sm:grid-cols-3">
                    <x-admin.unggah-foto model="logo" label="Logo" :pratinjau="$pratinjau('logo')" :hapus="$hapus('logo')" rasio="aspect-square" :rasio-potong="1" keterangan="Persegi, maks 3 MB." />
                    <x-admin.unggah-foto model="foto_hero" label="Foto hero (beranda)" :pratinjau="$pratinjau('foto_hero')" :hapus="$hapus('foto_hero')" rasio="aspect-square" :rasio-potong="16 / 9" keterangan="Latar layar pertama beranda." />
                    <x-admin.unggah-foto model="foto_gereja" label="Foto gereja" :pratinjau="$pratinjau('foto_gereja')" :hapus="$hapus('foto_gereja')" rasio="aspect-square" :rasio-potong="4 / 5" keterangan="Bagian Tentang Kami & Profil." />
                </div>
            </x-admin.kartu>

            <x-admin.kartu judul="Santo/Santa Pelindung" deskripsi="Tampil di halaman Profil, lengkap dengan foto dan kisahnya." icon="sparkles">
                <div class="grid gap-6 md:grid-cols-3">
                    <x-admin.unggah-foto model="foto_pelindung" label="Foto / gambar pelindung" :pratinjau="$pratinjau('foto_pelindung')" :hapus="$hapus('foto_pelindung')" rasio="aspect-[4/5]" :rasio-potong="4 / 5" />

                    <div class="space-y-6 md:col-span-2">
                        <flux:input wire:model="pelindung" label="Nama pelindung" placeholder="contoh: Santo Yoseph" />
                        <flux:textarea wire:model="kisah_pelindung" label="Kisah & latar belakang" description="Pisahkan paragraf dengan baris kosong." rows="9" />
                    </div>
                </div>
            </x-admin.kartu>

            <x-admin.kartu judul="Sejarah, Visi & Misi" deskripsi="Sejarah singkat tampil di beranda; sejarah lengkap, visi, dan misi di halaman Profil." icon="book-open">
                <flux:textarea wire:model="sejarah_singkat" label="Sejarah singkat (beranda)" rows="4" />
                <flux:textarea wire:model="sejarah" label="Sejarah lengkap" description="Pisahkan paragraf dengan baris kosong." rows="8" />

                <div class="grid gap-6 md:grid-cols-2">
                    <flux:textarea wire:model="visi" label="Visi" rows="5" />
                    <flux:textarea wire:model="misi" label="Misi" description="Satu poin misi per baris." rows="5" />
                </div>
            </x-admin.kartu>

            <x-admin.kartu judul="Kontak & Sekretariat" deskripsi="Tampil di beranda, footer, dan tombol WhatsApp melayang." icon="phone">
                <flux:textarea wire:model="alamat" label="Alamat" rows="2" />

                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input wire:model="telepon" label="Telepon" icon="phone" />
                    <flux:input wire:model="whatsapp" label="WhatsApp sekretariat" icon="chat-bubble-left-right" placeholder="contoh: 081234567890" />
                    <flux:input wire:model="email" type="email" label="Email" icon="envelope" />
                    <flux:input wire:model="link_maps" type="url" label="Link Google Maps" icon="map-pin" />
                </div>

                <flux:textarea wire:model="jam_sekretariat" label="Jam pelayanan sekretariat" description="Satu baris per hari, contoh: Senin–Jumat: 08.00–14.00 WIT" rows="4" />
            </x-admin.kartu>

            <x-admin.kartu judul="Media Sosial" deskripsi="Tampil sebagai ikon di footer semua halaman. Kosongkan yang tidak dipakai." icon="globe-alt">
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ([
                        ['instagram', 'Instagram', '@namaakun atau link Instagram', 'text-[#dd2a7b]'],
                        ['youtube', 'YouTube', '@namakanal atau link YouTube', 'text-[#ff0000]'],
                        ['facebook', 'Facebook', 'namahalaman atau link Facebook', 'text-[#1877f2]'],
                    ] as [$kolom, $label, $contoh, $warna])
                        <flux:field wire:key="medsos-{{ $kolom }}">
                            <flux:label class="flex items-center gap-2">
                                <x-ikon-medsos :jenis="$kolom" class="size-4 {{ $warna }}" /> {{ $label }}
                            </flux:label>
                            <flux:input wire:model="{{ $kolom }}" :placeholder="$contoh" />
                            <flux:error :name="$kolom" />
                        </flux:field>
                    @endforeach

                    <div class="flex items-start gap-3 rounded-xl bg-zinc-50 p-4 ring-1 ring-zinc-200">
                        <x-ikon-medsos jenis="whatsapp" class="mt-0.5 size-5 shrink-0 text-[#25d366]" />
                        <p class="text-sm text-zinc-600">
                            <span class="font-medium text-zinc-800">WhatsApp</span> memakai nomor
                            <span class="font-medium text-zinc-800">WhatsApp sekretariat</span> di kartu Kontak di atas.
                        </p>
                    </div>
                </div>

                {{-- Pratinjau ikon di footer (berdasarkan data tersimpan) --}}
                <div class="flex flex-wrap items-center gap-3 rounded-xl bg-coklat-900 px-5 py-4">
                    <span class="text-xs font-semibold uppercase tracking-widest text-krem-300">Pratinjau footer</span>
                    @forelse ($setting->media_sosial as $medsos)
                        <a href="{{ $medsos['url'] }}" target="_blank" rel="noopener" title="{{ $medsos['url'] }}"
                           class="flex size-9 items-center justify-center rounded-full bg-white/10 text-krem-100 ring-1 ring-white/15 hover:text-white">
                            <x-ikon-medsos :jenis="$medsos['jenis']" class="size-4" />
                        </a>
                    @empty
                        <span class="text-sm text-krem-200/70">Belum ada media sosial yang diisi.</span>
                    @endforelse
                </div>
                <flux:text class="text-xs">Cukup tulis nama akun (contoh: <strong>@parokibambupemali</strong>), otomatis diubah menjadi link saat disimpan.</flux:text>
            </x-admin.kartu>
        </div>

        {{-- Kolom samping (desktop: ikut saat di-scroll) --}}
        <div class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <x-admin.kartu judul="Card di Beranda" deskripsi="Atur card yang tampil di hero beranda." icon="rectangle-group">
                <flux:switch wire:model="tampil_card_misa" label="Card Jadwal Misa" description="Misa hari raya terdekat atau misa minggu ini." align="left" />
                <flux:switch wire:model="tampil_card_kegiatan" label="Card Kegiatan" description="Satu kegiatan terdekat." align="left" />
            </x-admin.kartu>

            <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-marun-600 to-marun-700 p-6 text-white shadow-lg">
                <p class="font-serif text-lg font-semibold">Simpan perubahan</p>
                <p class="mt-1 text-sm text-krem-200">Perubahan langsung tampil di website setelah disimpan.</p>
                <button type="submit" class="mt-4 flex w-full items-center justify-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-marun-700 shadow transition hover:bg-krem-50 disabled:opacity-60" wire:loading.attr="disabled" wire:target="simpan">
                    <span wire:loading.remove wire:target="simpan">Simpan</span>
                    <span wire:loading wire:target="simpan">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</section>

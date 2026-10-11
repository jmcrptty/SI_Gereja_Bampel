<?php

use App\Models\JadwalMisa;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::publik')] #[Title('Jadwal Misa')] class extends Component {
    #[Computed]
    public function misaHariRaya(): Collection
    {
        return JadwalMisa::aktif()->khusus()->akanDatang()->get();
    }

    /**
     * Misa rutin dikelompokkan per nomor hari (1 = Senin ... 7 = Minggu).
     */
    #[Computed]
    public function misaRutinPerHari(): \Illuminate\Support\Collection
    {
        return JadwalMisa::aktif()->rutin()->orderBy('hari')->orderBy('jam')->get()->groupBy('hari');
    }

    /**
     * Misa rutin terdekat dari sekarang (hari ini yang belum lewat, atau hari berikutnya).
     */
    #[Computed]
    public function misaBerikutnya(): ?JadwalMisa
    {
        $semua = JadwalMisa::aktif()->rutin()->urutDariHariIni()->get();
        $hariIni = today()->dayOfWeekIso;
        $jamSekarang = now()->format('H:i:s');

        return $semua->first(fn (JadwalMisa $m) => $m->hari !== $hariIni || $m->jam > $jamSekarang)
            ?? $semua->first();
    }

    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }
}; ?>

<div>
    <x-publik.hero-halaman eyebrow="Jadwal Misa" judul="Jadwal" aksen="Misa" :gambar="asset('images/hero-misa.svg')" posisi-gambar="object-[center_70%]"
        subjudul="Mari bersatu dalam perayaan Ekaristi. Berikut jadwal misa rutin setiap minggu dan misa hari raya di paroki kami.">
        @if ($misa = $this->misaBerikutnya)
            @php
                $selisih = ($misa->hari - today()->dayOfWeekIso + 7) % 7;
                $kapan = match (true) {
                    $selisih === 0 && $misa->jam > now()->format('H:i:s') => 'Hari ini',
                    $selisih === 1 => 'Besok',
                    default => $misa->nama_hari,
                };
            @endphp
            <div class="relative rounded-3xl bg-white/10 p-6 text-white shadow-2xl ring-1 ring-white/20 backdrop-blur-md sm:p-8">
                <div class="absolute -top-3 left-6 rounded-full bg-marun-600 px-3 py-1 text-xs font-semibold uppercase tracking-wider shadow">Misa berikutnya</div>
                <p class="mt-2 text-krem-200">{{ $kapan }}</p>
                <p class="font-serif text-6xl leading-none font-bold sm:text-7xl">{{ $misa->jam_format }}<span class="ml-2 text-2xl font-normal text-krem-300">WIT</span></p>
                <p class="mt-4 flex items-center gap-2 text-krem-100">
                    <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" /></svg>
                    {{ $misa->tempat }}
                </p>
            </div>
        @endif
    </x-publik.hero-halaman>

    {{-- Misa Hari Raya: kartu bergaya kalender --}}
    @if ($this->misaHariRaya->isNotEmpty())
        <section id="hari-raya" class="scroll-mt-24 bg-gradient-to-b from-krem-100 to-krem-50 py-14 lg:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Perayaan Khusus</p>
                </div>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-coklat-900 sm:text-4xl">Misa <span class="italic text-marun-700">Hari Raya</span></h2>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->misaHariRaya as $misa)
                        <div wire:key="hari-raya-{{ $misa->id }}" class="flex overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-krem-300/70">
                            <div class="flex w-24 shrink-0 flex-col items-center justify-center bg-gradient-to-b from-marun-600 to-marun-700 py-4 text-krem-50">
                                <span class="text-xs font-semibold uppercase tracking-wider">{{ $misa->tanggal->translatedFormat('M') }}</span>
                                <span class="font-serif text-4xl leading-none font-bold">{{ $misa->tanggal->format('j') }}</span>
                                <span class="mt-1 text-xs">{{ $misa->tanggal->format('Y') }}</span>
                            </div>
                            <div class="min-w-0 p-4">
                                <p class="font-serif text-lg leading-snug font-semibold text-coklat-900">{{ $misa->nama_perayaan }}</p>
                                <p class="mt-1 text-sm text-coklat-500">{{ $misa->tanggal->translatedFormat('l') }} · {{ $misa->tempat }}</p>
                                <p class="mt-2 inline-block rounded-full bg-krem-100 px-3 py-0.5 text-sm font-semibold text-marun-700">{{ $misa->jam_format }} WIT</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Misa Rutin: papan mingguan 7 kolom --}}
    <section class="bg-krem-50 py-14 lg:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="text-center">
                <div class="flex items-center justify-center gap-3">
                    <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Setiap Minggu</p>
                    <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                </div>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-coklat-900 sm:text-4xl">Misa <span class="italic text-marun-700">Rutin</span></h2>
            </div>

            @if ($this->misaRutinPerHari->isEmpty())
                <p class="mt-10 rounded-2xl border border-dashed border-coklat-400/50 p-8 text-center text-coklat-500">Jadwal misa rutin belum tersedia.</p>
            @else
                <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                    @foreach (\App\Models\JadwalMisa::HARI as $nomor => $namaHari)
                        @php($hariIni = $nomor === today()->dayOfWeekIso)
                        <div wire:key="hari-{{ $nomor }}" @class([
                            'flex flex-col rounded-2xl p-4 transition lg:min-h-48',
                            'bg-gradient-to-b from-marun-600 to-marun-700 text-krem-50 shadow-lg shadow-marun-700/20 lg:-translate-y-2' => $hariIni,
                            'bg-white text-coklat-900 ring-1 ring-krem-300/70' => ! $hariIni,
                        ])>
                            <div class="flex items-center justify-between gap-2 lg:flex-col lg:items-start">
                                <p class="font-serif text-lg font-semibold">{{ $namaHari }}</p>
                                @if ($hariIni)
                                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-[0.7rem] font-semibold uppercase tracking-wider">Hari ini</span>
                                @endif
                            </div>

                            <ul class="mt-3 space-y-2">
                                @forelse ($this->misaRutinPerHari->get($nomor, collect()) as $misa)
                                    <li wire:key="rutin-{{ $misa->id }}">
                                        <p @class(['text-xl font-bold', 'text-marun-700' => ! $hariIni])>{{ $misa->jam_format }}</p>
                                        <p @class(['text-xs', 'text-krem-200' => $hariIni, 'text-coklat-500' => ! $hariIni])>{{ $misa->tempat }}</p>
                                    </li>
                                @empty
                                    <li @class(['text-sm', 'text-krem-200' => $hariIni, 'text-coklat-400' => ! $hariIni])>Tidak ada misa</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Jam sekretariat --}}
    <section class="bg-krem-50 px-4 pb-16 sm:px-6 lg:pb-24">
        <div class="mx-auto grid max-w-6xl overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-krem-300/70 md:grid-cols-5">
            <div class="bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 p-8 text-white md:col-span-2">
                <h2 class="font-serif text-2xl font-semibold">Jam Pelayanan Sekretariat</h2>
                <p class="mt-3 text-krem-200">Untuk intensi misa, pendaftaran sakramen, dan informasi lainnya, silakan hubungi sekretariat paroki.</p>
                @if ($this->setting->whatsapp_url)
                    <a href="{{ $this->setting->whatsapp_url }}" target="_blank" rel="noopener" class="mt-6 inline-block rounded-full bg-krem-50 px-5 py-2.5 text-sm font-semibold text-coklat-900 hover:bg-white">Chat WhatsApp</a>
                @endif
            </div>
            <ul class="divide-y divide-krem-200 p-8 md:col-span-3">
                @forelse ($this->setting->daftar_jam_sekretariat as $baris)
                    <li class="py-3 text-lg text-coklat-900 first:pt-0 last:pb-0">{{ $baris }}</li>
                @empty
                    <li class="text-coklat-500">Silakan hubungi sekretariat.</li>
                @endforelse
            </ul>
        </div>
    </section>
</div>

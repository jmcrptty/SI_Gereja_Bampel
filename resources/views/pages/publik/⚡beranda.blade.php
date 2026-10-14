<?php

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Setting;
use App\Models\Warta;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::publik')] class extends Component {
    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }

    /**
     * @return array{judul: string, khusus: bool, items: \Illuminate\Support\Collection}
     */
    #[Computed]
    public function cardMisa(): array
    {
        return JadwalMisa::untukCardHero();
    }

    #[Computed]
    public function cardKegiatan(): ?Kegiatan
    {
        return Kegiatan::untukCardHero();
    }

    #[Computed]
    public function kegiatanTerdekat(): Collection
    {
        return Kegiatan::aktif()->akanDatang()->limit(4)->get();
    }

    #[Computed]
    public function wartaTerbaru(): Collection
    {
        return Warta::terbit()->terbaru()->limit(4)->get();
    }

    #[Computed]
    public function galeriTerbaru(): Collection
    {
        return Galeri::terbaru()->limit(6)->get();
    }
}; ?>

<div>
    {{-- 1. Hero: setinggi satu layar penuh di desktop dan HP; navbar transparan melayang di atasnya --}}
    <section class="relative isolate flex min-h-svh flex-col overflow-hidden bg-coklat-900">
        <img src="{{ $this->setting->fileUrl('foto_hero') ?? asset('images/hero-gereja.svg') }}"
             alt="{{ $this->setting->nama_gereja }}"
             class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
        {{-- Lapisan gelap agar teks putih tetap terbaca di atas foto apa pun --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-coklat-900/70 via-coklat-900/30 to-coklat-900/90"></div>

        {{-- Judul --}}
        {{-- pt-28: ruang untuk navbar (h-16) yang melayang di atas hero --}}
        <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col justify-center px-4 pt-28 pb-8 text-center sm:pt-32 md:text-left">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-krem-300 sm:text-sm">Selamat datang di</p>
            <h1 class="mx-auto mt-3 max-w-3xl font-serif text-4xl leading-tight font-bold text-white drop-shadow-lg sm:text-5xl md:mx-0 lg:text-6xl">
                {{ $this->setting->nama_gereja }}
            </h1>
            @if ($this->setting->pelindung)
                <p class="mt-4 text-base text-krem-100 drop-shadow sm:text-lg">Pelindung: {{ $this->setting->pelindung }}</p>
            @endif
        </div>

        {{-- Card Jadwal Misa & Kegiatan (sedikit transparan) --}}
        @if ($this->setting->tampil_card_misa || $this->setting->tampil_card_kegiatan)
            <div class="mx-auto w-full max-w-6xl px-4 pb-8 sm:pb-12">
                <div @class([
                    'grid grid-cols-1 gap-4',
                    'md:grid-cols-2' => $this->setting->tampil_card_misa && $this->setting->tampil_card_kegiatan,
                    'md:max-w-xl' => ! ($this->setting->tampil_card_misa && $this->setting->tampil_card_kegiatan),
                ])>
                    @if ($this->setting->tampil_card_misa)
                        <a href="{{ route('jadwal-misa') }}{{ $this->cardMisa['khusus'] ? '#hari-raya' : '' }}" wire:navigate
                           class="group block rounded-2xl bg-white/70 p-4 shadow-xl ring-1 ring-white/50 backdrop-blur-md transition hover:bg-white/85 sm:p-5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-marun-700 sm:text-sm">
                                    {{ $this->cardMisa['items']->isNotEmpty() ? $this->cardMisa['judul'] : 'Jadwal Misa' }}
                                </p>
                                <span class="text-sm text-marun-700 transition group-hover:translate-x-1">&rarr;</span>
                            </div>

                            @if ($this->cardMisa['items']->isNotEmpty())
                                <ul class="mt-2 divide-y divide-coklat-900/10">
                                    @foreach ($this->cardMisa['items'] as $misa)
                                        <li wire:key="card-misa-{{ $misa->id }}" class="flex items-center justify-between gap-4 py-1.5 text-sm sm:py-2">
                                            <span class="min-w-0 font-medium text-coklat-900">
                                                @if ($this->cardMisa['khusus'])
                                                    <span class="block truncate">{{ $misa->nama_perayaan }}</span>
                                                    <span class="block text-xs font-normal text-coklat-700">{{ $misa->tanggal->translatedFormat('l, j F Y') }}</span>
                                                @else
                                                    {{ $misa->nama_hari }}
                                                @endif
                                            </span>
                                            <span class="shrink-0 font-semibold text-coklat-900">{{ $misa->jam_format }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-coklat-900">Lihat semua jadwal</p>
                            @endif
                        </a>
                    @endif

                    @if ($this->setting->tampil_card_kegiatan)
                        @if ($kegiatan = $this->cardKegiatan)
                            <a href="{{ route('kegiatan.show', $kegiatan) }}" wire:navigate
                               class="group flex items-center gap-4 rounded-2xl bg-white/70 p-4 shadow-xl ring-1 ring-white/50 backdrop-blur-md transition hover:bg-white/85 sm:p-5">
                                @if ($kegiatan->foto_url)
                                    <img src="{{ $kegiatan->foto_kecil_url }}" alt="" class="size-16 shrink-0 rounded-xl object-cover sm:size-20" width="80" height="80">
                                @else
                                    <div class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-marun-700/10 text-marun-700 sm:size-20">
                                        <x-publik.ikon-kegiatan class="size-8 sm:size-10" />
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-marun-700 sm:text-sm">Kegiatan Terdekat</p>
                                    <p class="mt-1 truncate font-semibold text-coklat-900">{{ $kegiatan->judul }}</p>
                                    <p class="text-sm text-coklat-700">
                                        {{ $kegiatan->tanggal->translatedFormat('l, j F Y') }}@if ($kegiatan->jam_format) · {{ $kegiatan->jam_format }}@endif
                                    </p>
                                    <p class="truncate text-sm text-coklat-700">{{ $kegiatan->tempat }}</p>
                                </div>
                                <span class="shrink-0 self-start text-sm text-marun-700 transition group-hover:translate-x-1">&rarr;</span>
                            </a>
                        @else
                            <a href="{{ route('kegiatan') }}" wire:navigate
                               class="group block rounded-2xl bg-white/70 p-4 shadow-xl ring-1 ring-white/50 backdrop-blur-md transition hover:bg-white/85 sm:p-5">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-marun-700 sm:text-sm">Kegiatan</p>
                                    <span class="text-sm text-marun-700 transition group-hover:translate-x-1">&rarr;</span>
                                </div>
                                <p class="mt-2 text-coklat-900">Lihat semua kegiatan</p>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </section>

    {{--
        Di desktop (lg) setiap "layar" setinggi satu layar (min-h-svh) dengan isi di tengah;
        pt-16 memberi ruang untuk navbar yang melayang. Di HP tinggi mengikuti isi.
    --}}

    {{-- Layar 2: Sekilas Profil Paroki (Tentang Kami) --}}
    <section class="relative isolate flex items-center overflow-hidden bg-gradient-to-b from-krem-50 via-white to-krem-50 py-20 lg:min-h-svh lg:pt-24">
        {{-- Ornamen salib samar di latar --}}
        <svg class="pointer-events-none absolute right-6 bottom-10 -z-10 h-56 text-coklat-900/[0.05] lg:right-16 lg:h-80" viewBox="0 0 100 140" fill="currentColor" aria-hidden="true">
            <path d="M42 0h16v38h34v16H58v86H42V54H8V38h34z" />
        </svg>

        {{-- Tanpa max-w: foto menempel dekat tepi kiri layar (jarak tetap px-12/16) di semua lebar desktop --}}
        <div class="grid w-full items-center gap-y-16 px-4 sm:px-6 md:grid-cols-12 md:gap-x-10 lg:gap-x-16 lg:px-12 xl:px-16">
            {{--
                Foto berbentuk jendela gereja + bingkai emas + badge pelindung.
                Desktop: mengisi kolom kiri, maksimal 38rem agar tetap muat satu layar.
            --}}
            <div class="relative mx-auto w-full max-w-xs sm:max-w-sm md:col-span-5 md:mx-0 md:max-w-[38rem]">
                <div class="absolute inset-0 translate-x-3 translate-y-3 bentuk-jendela border-2 border-coklat-400/70" aria-hidden="true"></div>
                <img src="{{ $this->setting->fileUrl('foto_gereja') ?? asset('images/hero-gereja.svg') }}" alt="{{ $this->setting->nama_gereja }}"
                     class="relative aspect-[4/5] w-full bentuk-jendela object-cover shadow-2xl shadow-coklat-900/20"
                     loading="lazy" width="672" height="840">

                @if ($this->setting->pelindung)
                    <div class="absolute -bottom-8 left-1/2 flex w-max max-w-[85%] -translate-x-1/2 items-center gap-3 rounded-2xl bg-white/95 px-5 py-4 shadow-xl ring-1 ring-krem-200 backdrop-blur md:left-auto md:-right-6 md:translate-x-0">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-krem-100 text-marun-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.5 2h3v6h6v3h-6v11h-3V11h-6V8h6z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-widest text-marun-700">Pelindung</p>
                            <p class="font-serif text-lg leading-snug font-semibold text-coklat-900">{{ $this->setting->pelindung }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Teks --}}
            <div class="md:col-span-7 lg:max-w-2xl">
                <div class="flex items-center gap-3">
                    <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Sekilas Profil Paroki</p>
                </div>

                <h2 class="mt-4 font-serif text-5xl leading-none font-semibold text-coklat-900 sm:text-6xl lg:text-7xl">
                    Tentang <span class="italic text-marun-700">Kami</span>
                </h2>

                <p class="mt-6 line-clamp-[14] whitespace-pre-line leading-relaxed text-coklat-700 lg:text-lg">{{ $this->setting->sejarah_singkat ?: 'Profil paroki akan segera dilengkapi.' }}</p>

                @if ($this->setting->visi)
                    <figure class="mt-8 border-l-4 border-marun-600 pl-5">
                        <figcaption class="text-xs font-semibold uppercase tracking-widest text-coklat-500">Visi</figcaption>
                        <blockquote class="mt-1 line-clamp-4 font-serif text-lg leading-relaxed text-coklat-900 italic lg:text-xl">
                            {{ $this->setting->visi }}
                        </blockquote>
                    </figure>
                @endif

                <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-4">
                    <a href="{{ route('profil') }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-full bg-marun-700 px-7 py-3 text-sm font-medium text-white shadow-lg shadow-coklat-900/20 transition hover:bg-marun-600">
                        Selengkapnya <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="{{ route('dewan-paroki') }}" wire:navigate class="text-sm font-medium text-marun-700 underline-offset-4 hover:underline">Pastor & Dewan Paroki</a>
                    <a href="{{ route('lingkungan') }}" wire:navigate class="text-sm font-medium text-marun-700 underline-offset-4 hover:underline">Lingkungan</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Layar 3: Kabar Paroki = Kegiatan Terdekat + Warta Terbaru (palet krem, cokelat, marun) --}}
    {{--
        Gradasi vertikal: tepi atas & bawah = krem-50 (sama dengan section sebelum/sesudahnya),
        sehingga tidak ada garis batas warna antar section.
    --}}
    <section class="relative isolate overflow-hidden bg-gradient-to-b from-krem-50 via-krem-200 to-krem-50 py-16 lg:py-20">
        {{-- Cahaya lembut marun & cokelat, diletakkan di dalam agar tidak terpotong di tepi section --}}
        <div class="absolute top-1/4 -left-24 -z-10 size-96 rounded-full bg-marun-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="absolute bottom-1/4 -right-24 -z-10 size-[28rem] rounded-full bg-coklat-400/20 blur-3xl" aria-hidden="true"></div>
        {{-- Pola salib kecil sangat samar, memudar di tepi atas & bawah --}}
        <div class="absolute inset-0 -z-10 opacity-[0.06]" aria-hidden="true"
             style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 48 48'%3E%3Cpath fill='%233a2417' d='M23 16h2v6h6v2h-6v10h-2V24h-6v-2h6z'/%3E%3C/svg%3E&quot;); -webkit-mask-image: linear-gradient(to bottom, transparent, #000 20%, #000 80%, transparent); mask-image: linear-gradient(to bottom, transparent, #000 20%, #000 80%, transparent);"></div>

        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
            {{-- Judul --}}
            <div class="text-center">
                <div class="flex items-center justify-center gap-3">
                    <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Kabar Paroki</p>
                    <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                </div>
                <h2 class="mt-4 font-serif text-4xl leading-tight font-semibold text-coklat-900 sm:text-5xl">
                    Kegiatan & <span class="italic text-marun-700">Warta</span>
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-coklat-700">Ikuti kegiatan paroki yang akan datang dan baca warta terbaru setiap minggunya.</p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-12 lg:gap-10">
                {{-- Kegiatan Terdekat --}}
                <div class="lg:col-span-7">
                    <div class="flex items-end justify-between gap-4">
                        <h3 class="font-serif text-2xl font-semibold text-coklat-900">Kegiatan Terdekat</h3>
                        <a href="{{ route('kegiatan') }}" wire:navigate class="text-sm font-medium text-marun-700 underline-offset-4 hover:underline">Lihat semua &rarr;</a>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($this->kegiatanTerdekat as $kegiatan)
                            <a href="{{ route('kegiatan.show', $kegiatan) }}" wire:navigate wire:key="kegiatan-{{ $kegiatan->id }}"
                               class="group flex items-center gap-4 rounded-2xl bg-white/70 p-3 shadow-sm ring-1 ring-krem-300/70 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white hover:shadow-lg sm:gap-5 sm:p-4">
                                {{-- Blok tanggal ala kalender --}}
                                <div class="flex w-16 shrink-0 flex-col items-center rounded-xl bg-gradient-to-b from-marun-600 to-marun-700 py-2.5 text-krem-50 shadow-md sm:w-[4.5rem]">
                                    <span class="font-serif text-2xl leading-none font-bold sm:text-3xl">{{ $kegiatan->tanggal->format('j') }}</span>
                                    <span class="mt-1 text-[0.7rem] font-semibold uppercase tracking-wider">{{ $kegiatan->tanggal->translatedFormat('M Y') }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="font-serif text-lg leading-snug font-semibold text-coklat-900 group-hover:text-marun-700">{{ $kegiatan->judul }}</p>
                                    <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-sm text-coklat-500">
                                        <span>{{ $kegiatan->tanggal->translatedFormat('l') }}@if ($kegiatan->jam_format), {{ $kegiatan->jam_format }} WIT @endif</span>
                                        <span class="flex min-w-0 items-center gap-1">
                                            <svg class="size-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" /></svg>
                                            <span class="truncate">{{ $kegiatan->tempat }}</span>
                                        </span>
                                    </p>
                                </div>

                                @if ($kegiatan->foto_url)
                                    <img src="{{ $kegiatan->foto_kecil_url }}" alt="" class="hidden size-16 shrink-0 rounded-xl object-cover sm:block" loading="lazy" width="64" height="64">
                                @endif
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-coklat-400/50 bg-white/40 p-8 text-center text-coklat-500">
                                <x-publik.ikon-kegiatan class="mx-auto size-10 text-coklat-400" />
                                <p class="mt-2">Belum ada kegiatan terdekat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Warta Terbaru: panel terang senada dengan kartu kegiatan, aksen marun --}}
                <div class="lg:col-span-5">
                    <div class="relative h-full overflow-hidden rounded-3xl bg-white/60 p-6 text-coklat-900 shadow-sm ring-1 ring-krem-300/70 backdrop-blur sm:p-8">
                        {{-- Garis aksen marun di atas panel --}}
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-marun-700 via-marun-500 to-coklat-400" aria-hidden="true"></div>
                        <div class="absolute -top-20 -right-20 size-48 rounded-full bg-marun-500/10 blur-2xl" aria-hidden="true"></div>

                        <div class="relative flex items-end justify-between gap-4">
                            <h3 class="font-serif text-2xl font-semibold text-coklat-900">Warta Terbaru</h3>
                            <a href="{{ route('warta') }}" wire:navigate class="text-sm font-medium text-marun-700 underline-offset-4 hover:underline">Lihat semua &rarr;</a>
                        </div>

                        <div class="relative mt-4 divide-y divide-krem-300/70">
                            @forelse ($this->wartaTerbaru as $warta)
                                <a href="{{ $warta->pdf_url }}" target="_blank" rel="noopener" wire:key="warta-{{ $warta->id }}" class="group flex gap-4 py-4">
                                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-marun-700/10 text-marun-700 transition group-hover:bg-marun-700 group-hover:text-white">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium uppercase tracking-wider text-coklat-500">{{ $warta->tanggal->translatedFormat('j F Y') }}</p>
                                        <p class="mt-0.5 font-serif text-lg leading-snug font-semibold text-coklat-900 group-hover:text-marun-700">{{ $warta->judul }}</p>
                                        @if ($warta->ringkasan)
                                            <p class="mt-1 line-clamp-2 text-sm text-coklat-700">{{ $warta->ringkasan }}</p>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="py-10 text-center text-coklat-500">
                                    <svg class="mx-auto size-10 text-coklat-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <p class="mt-2">Belum ada warta.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Layar 4: Galeri + Kontak & Lokasi (tinggi mengikuti isi, tanpa dipaksa satu layar agar tidak ada ruang kosong) --}}
    <section class="py-12 lg:py-16">
        <div class="mx-auto w-full max-w-6xl space-y-14 px-4">
            {{-- Galeri --}}
            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Dokumentasi</p>
                        <h2 class="mt-2 font-serif text-3xl font-semibold text-coklat-900">Galeri</h2>
                    </div>
                    <a href="{{ route('galeri') }}" wire:navigate class="text-sm font-medium text-marun-700 hover:underline">Lihat semua &rarr;</a>
                </div>

                {{-- 3 kolom: 3 foto di atas, 3 di bawah --}}
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                    @forelse ($this->galeriTerbaru as $foto)
                        <img wire:key="galeri-{{ $foto->id }}" src="{{ $foto->foto_kecil_url }}" alt="{{ $foto->judul }}"
                             class="aspect-[4/3] w-full rounded-xl object-cover shadow-sm" loading="lazy" width="600" height="450">
                    @empty
                        <p class="col-span-full rounded-2xl border border-dashed border-coklat-400/50 p-6 text-center text-coklat-500">Belum ada foto.</p>
                    @endforelse
                </div>
            </div>

            {{-- Kontak & Lokasi --}}
            <div>
                <h2 class="font-serif text-2xl font-semibold text-coklat-900">Kontak & Lokasi</h2>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="space-y-4 rounded-2xl bg-white p-6 text-coklat-700 shadow-sm ring-1 ring-krem-200">
                        @if ($this->setting->alamat)
                            <div>
                                <p class="text-sm font-semibold text-coklat-500">Alamat</p>
                                <p class="whitespace-pre-line">{{ $this->setting->alamat }}</p>
                            </div>
                        @endif
                        @if ($this->setting->telepon)
                            <div>
                                <p class="text-sm font-semibold text-coklat-500">Telepon</p>
                                <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $this->setting->telepon) }}" class="hover:underline">{{ $this->setting->telepon }}</a></p>
                            </div>
                        @endif
                        @if ($this->setting->whatsapp_url)
                            <div>
                                <p class="text-sm font-semibold text-coklat-500">WhatsApp Sekretariat</p>
                                <p><a href="{{ $this->setting->whatsapp_url }}" target="_blank" rel="noopener" class="hover:underline">{{ $this->setting->whatsapp }}</a></p>
                            </div>
                        @endif
                        @if ($this->setting->link_maps)
                            <a href="{{ $this->setting->link_maps }}" target="_blank" rel="noopener"
                               class="inline-block rounded-full bg-marun-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-marun-600">Buka di Google Maps</a>
                        @endif
                    </div>

                    <div class="rounded-2xl border-2 border-marun-600 bg-krem-100 p-6">
                        <p class="text-lg font-semibold text-marun-700">Jam Pelayanan Sekretariat</p>
                        <ul class="mt-3 space-y-1 text-coklat-900">
                            @forelse ($this->setting->daftar_jam_sekretariat as $baris)
                                <li>{{ $baris }}</li>
                            @empty
                                <li>Silakan hubungi sekretariat.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

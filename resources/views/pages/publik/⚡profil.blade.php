<?php

use App\Models\Setting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::publik')] #[Title('Profil Paroki')] class extends Component {
    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }

    /**
     * Pecah teks menjadi paragraf (dipisah baris kosong).
     *
     * @return array<int, string>
     */
    public function paragraf(?string $teks): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', (string) $teks))));
    }

    /**
     * Misi dipecah per baris.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function daftarMisi(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->setting->misi))));
    }
}; ?>

<div>
    @php($setting = $this->setting)

    {{-- 1. Hero berfoto --}}
    <section class="relative isolate flex min-h-[70svh] items-end overflow-hidden bg-coklat-900">
        <img src="{{ $setting->fileUrl('foto_gereja') ?? asset('images/hero-gereja.svg') }}" alt="{{ $setting->nama_gereja }}"
             class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-coklat-900 via-coklat-900/60 to-coklat-900/40"></div>

        {{-- pb besar agar teks naik sedikit dari dasar hero --}}
        <div class="mx-auto w-full max-w-6xl px-4 pt-32 pb-20 sm:px-6 sm:pb-24 lg:pb-36">
            <nav class="text-sm text-krem-200" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-white">Beranda</a>
                <span class="mx-2 text-krem-300/60">/</span>
                <span class="text-white">Profil Paroki</span>
            </nav>

            <div class="mt-6 flex items-center gap-3">
                <span class="h-px w-10 bg-krem-300" aria-hidden="true"></span>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-krem-300 sm:text-sm">Profil Paroki</p>
            </div>
            <h1 class="mt-4 max-w-3xl font-serif text-4xl leading-tight font-bold text-white drop-shadow-lg sm:text-5xl lg:text-6xl">
                {{ $setting->nama_gereja }}
            </h1>
            @if ($setting->pelindung)
                <p class="mt-4 text-lg text-krem-100">Di bawah perlindungan <span class="font-serif italic text-krem-300">{{ $setting->pelindung }}</span></p>
            @endif
        </div>
    </section>

    {{-- 2. Sejarah + kartu Sekilas --}}
    <section class="relative isolate overflow-hidden bg-gradient-to-b from-krem-50 via-white to-krem-50 py-16 lg:py-24">
        <svg class="pointer-events-none absolute top-16 right-6 -z-10 h-56 text-coklat-900/[0.04] lg:h-80" viewBox="0 0 100 140" fill="currentColor" aria-hidden="true">
            <path d="M42 0h16v38h34v16H58v86H42V54H8V38h34z" />
        </svg>

        <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-7">
                <div class="flex items-center gap-3">
                    <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Perjalanan Iman</p>
                </div>
                <h2 class="mt-4 font-serif text-4xl font-semibold text-coklat-900 sm:text-5xl">Sejarah <span class="italic text-marun-700">Paroki</span></h2>

                <div class="mt-8 space-y-5 text-lg leading-relaxed text-coklat-700">
                    @forelse ($this->paragraf($setting->sejarah ?: $setting->sejarah_singkat) as $isi)
                        <p @class(['first-letter:float-left first-letter:mt-1 first-letter:mr-3 first-letter:font-serif first-letter:text-6xl first-letter:leading-none first-letter:font-bold first-letter:text-marun-700' => $loop->first])>{{ $isi }}</p>
                    @empty
                        <p>Sejarah paroki akan segera dilengkapi.</p>
                    @endforelse
                </div>
            </div>

            {{-- Kartu Sekilas --}}
            <aside class="lg:col-span-5">
                <div class="lg:sticky lg:top-24">
                    <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 shadow-coklat-900/10 ring-krem-200">
                        <img src="{{ $setting->fileUrl('foto_hero') ?? asset('images/hero-gereja.svg') }}" alt=""
                             class="aspect-[16/10] w-full object-cover" loading="lazy" width="640" height="400">
                        <dl class="divide-y divide-krem-200 p-6">
                            <div class="pb-4">
                                <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Nama Paroki</dt>
                                <dd class="mt-1 font-serif text-xl font-semibold text-coklat-900">{{ $setting->nama_gereja }}</dd>
                            </div>
                            @if ($setting->pelindung)
                                <div class="py-4">
                                    <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Pelindung</dt>
                                    <dd class="mt-1 text-coklat-900">{{ $setting->pelindung }}</dd>
                                </div>
                            @endif
                            @if ($setting->alamat)
                                <div class="py-4">
                                    <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Alamat</dt>
                                    <dd class="mt-1 whitespace-pre-line text-coklat-700">{{ $setting->alamat }}</dd>
                                </div>
                            @endif
                            @if ($setting->telepon || $setting->whatsapp)
                                <div class="pt-4">
                                    <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Kontak Sekretariat</dt>
                                    <dd class="mt-1 space-y-0.5 text-coklat-700">
                                        @if ($setting->telepon)
                                            <p>Telp. {{ $setting->telepon }}</p>
                                        @endif
                                        @if ($setting->whatsapp_url)
                                            <p><a href="{{ $setting->whatsapp_url }}" target="_blank" rel="noopener" class="text-marun-700 hover:underline">WhatsApp {{ $setting->whatsapp }}</a></p>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- 3. Visi & Misi --}}
    @if ($setting->visi || $this->daftarMisi)
        <section class="relative isolate overflow-hidden bg-gradient-to-b from-krem-50 via-krem-200 to-krem-50 py-16 lg:py-24">
            <div class="absolute top-1/4 -left-24 -z-10 size-96 rounded-full bg-marun-500/10 blur-3xl" aria-hidden="true"></div>

            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <div class="flex items-center justify-center gap-3">
                        <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Arah Pelayanan</p>
                        <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                    </div>
                    <h2 class="mt-4 font-serif text-4xl font-semibold text-coklat-900 sm:text-5xl">Visi & <span class="italic text-marun-700">Misi</span></h2>
                </div>

                @if ($setting->visi)
                    <figure class="mx-auto mt-12 max-w-3xl text-center">
                        <svg class="mx-auto size-10 text-marun-600/40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.5 6C6.5 6.7 4 9.3 4 13v5h6v-6H7c0-2.2 1.3-3.8 3-4.3zm10 0c-3 .7-5.5 3.3-5.5 7v5h6v-6h-3c0-2.2 1.3-3.8 3-4.3z" /></svg>
                        <blockquote class="mt-4 font-serif text-2xl leading-relaxed text-coklat-900 italic sm:text-3xl">{{ $setting->visi }}</blockquote>
                        <figcaption class="mt-4 text-xs font-semibold uppercase tracking-widest text-coklat-500">Visi</figcaption>
                    </figure>
                @endif

                @if ($this->daftarMisi)
                    <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($this->daftarMisi as $misi)
                            <div wire:key="misi-{{ $loop->index }}" class="flex gap-4 rounded-2xl bg-white/70 p-5 shadow-sm ring-1 ring-krem-300/70 backdrop-blur">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-marun-600 to-marun-700 font-serif text-lg font-bold text-krem-50">{{ $loop->iteration }}</span>
                                <p class="pt-1.5 text-coklat-700">{{ $misi }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- 4. Santo/Santa Pelindung --}}
    @if ($setting->pelindung)
        <section class="relative isolate overflow-hidden bg-gradient-to-b from-krem-50 via-white to-krem-50 py-16 lg:py-24">
            <div class="mx-auto grid max-w-6xl items-center gap-14 px-4 sm:px-6 md:grid-cols-12 lg:gap-20">
                {{-- Foto pelindung berbentuk jendela + halo --}}
                <div class="relative mx-auto w-full max-w-xs sm:max-w-sm md:col-span-5 md:max-w-none">
                    <div class="absolute -inset-6 -z-10 rounded-full bg-krem-300/40 blur-2xl" aria-hidden="true"></div>
                    <div class="bentuk-jendela absolute inset-0 -translate-x-3 translate-y-3 border-2 border-coklat-400/70" aria-hidden="true"></div>
                    <img src="{{ $setting->fileUrl('foto_pelindung') ?? asset('images/pelindung.svg') }}" alt="{{ $setting->pelindung }}"
                         class="bentuk-jendela relative aspect-[4/5] w-full object-cover shadow-2xl shadow-coklat-900/25" loading="lazy" width="640" height="800">
                </div>

                <div class="md:col-span-7">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Pelindung Paroki</p>
                    </div>
                    <h2 class="mt-4 font-serif text-4xl leading-tight font-semibold text-coklat-900 sm:text-5xl">{{ $setting->pelindung }}</h2>

                    <div class="mt-8 space-y-5 leading-relaxed text-coklat-700 lg:text-lg">
                        @forelse ($this->paragraf($setting->kisah_pelindung) as $isi)
                            <p>{{ $isi }}</p>
                        @empty
                            <p>Kisah {{ $setting->pelindung }} akan segera dilengkapi.</p>
                        @endforelse
                    </div>

                    <p class="mt-8 border-l-4 border-marun-600 pl-5 font-serif text-lg text-coklat-900 italic">
                        {{ $setting->pelindung }}, doakanlah kami.
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- 5. Ajakan berkunjung --}}
    <section class="px-4 pb-16 sm:px-6 lg:pb-24">
        <div class="relative mx-auto max-w-6xl overflow-hidden rounded-3xl bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 px-6 py-12 text-center text-white shadow-xl sm:px-12 lg:py-16">
            <div class="absolute -top-20 -right-20 size-64 rounded-full bg-marun-500/30 blur-3xl" aria-hidden="true"></div>
            <div class="relative">
                <h2 class="font-serif text-3xl font-semibold sm:text-4xl">Mari Bergabung Bersama Kami</h2>
                <p class="mx-auto mt-4 max-w-2xl text-krem-200">
                    {{ $setting->alamat ? 'Kami menantikan kehadiran Anda di '.$setting->nama_gereja.', '.str_replace("\n", ', ', $setting->alamat).'.' : 'Kami menantikan kehadiran Anda dalam setiap perayaan Ekaristi.' }}
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('jadwal-misa') }}" wire:navigate class="rounded-full bg-krem-50 px-6 py-3 text-sm font-semibold text-coklat-900 shadow transition hover:bg-white">Lihat Jadwal Misa</a>
                    @if ($setting->link_maps)
                        <a href="{{ $setting->link_maps }}" target="_blank" rel="noopener" class="rounded-full px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/40 transition hover:bg-white/10">Buka Google Maps</a>
                    @endif
                    <a href="{{ route('dewan-paroki') }}" wire:navigate class="rounded-full px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/40 transition hover:bg-white/10">Dewan Paroki</a>
                </div>
            </div>
        </div>
    </section>
</div>

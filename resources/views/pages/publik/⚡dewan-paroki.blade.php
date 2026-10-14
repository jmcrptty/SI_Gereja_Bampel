<?php

use App\Models\Pastor;
use App\Models\Pengurus;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::publik')] #[Title('Pastor & Dewan Paroki')] class extends Component {
    #[Computed]
    public function daftarPastor(): Collection
    {
        return Pastor::urut()->get();
    }

    #[Computed]
    public function daftarPengurus(): Collection
    {
        return Pengurus::urut()->get();
    }

    /** Inisial nama untuk pengganti foto, contoh: "Maria Goreti" → "MG". */
    public function inisial(string $nama): string
    {
        return \Illuminate\Support\Str::of($nama)->explode(' ')->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode('');
    }
}; ?>

<div>
    @php
        $pengurus = $this->daftarPengurus;
        // 3 urutan teratas tampil sebagai pengurus inti.
        $inti = $pengurus->take(3);
        $lainnya = $pengurus->slice(3);
        $periode = $pengurus->first()?->periode;
    @endphp

    <section class="bg-gradient-to-b from-krem-100 to-krem-50 pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Pastor & Dewan Paroki" judul="Pastor &" aksen="Dewan Paroki"
                subjudul="Para gembala dan pengurus yang melayani bersama dalam menggerakkan kehidupan menggereja umat." />

            {{-- Pastor paroki: perkenalan, latar belakang, dan kisah --}}
            @foreach ($this->daftarPastor as $pastor)
                <article wire:key="pastor-{{ $pastor->id }}" class="mt-14 grid items-start gap-10 md:grid-cols-12 lg:mt-20 lg:gap-16">
                    <div @class(['relative mx-auto w-full max-w-xs sm:max-w-sm md:col-span-5 md:max-w-none', 'md:order-last' => $loop->even])>
                        <div class="absolute -inset-6 -z-10 rounded-full bg-krem-300/40 blur-2xl" aria-hidden="true"></div>
                        <div class="bentuk-jendela absolute inset-0 -translate-x-3 translate-y-3 border-2 border-coklat-400/70" aria-hidden="true"></div>
                        @if ($pastor->foto_url)
                            <img src="{{ $pastor->foto_url }}" alt="{{ $pastor->nama }}"
                                 class="bentuk-jendela relative aspect-[4/5] w-full object-cover shadow-2xl shadow-coklat-900/25" loading="lazy" width="640" height="800">
                        @else
                            <div class="bentuk-jendela relative flex aspect-[4/5] w-full items-center justify-center bg-gradient-to-b from-coklat-700 to-coklat-900 shadow-2xl shadow-coklat-900/25">
                                <span class="font-serif text-7xl font-bold text-krem-200">{{ $this->inisial($pastor->nama) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-7">
                        <div class="flex items-center gap-3">
                            <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">{{ $pastor->jabatan }}</p>
                        </div>
                        <h2 class="mt-4 font-serif text-3xl leading-tight font-semibold text-coklat-900 sm:text-4xl">
                            {{ $pastor->nama }}@if ($pastor->tarekat)<span class="text-coklat-500">, {{ $pastor->tarekat }}</span>@endif
                        </h2>

                        @php
                            $fakta = array_filter([
                                'Asal' => $pastor->asal,
                                'Lahir' => $pastor->tanggal_lahir?->translatedFormat('j F Y'),
                                'Ditahbiskan' => $pastor->tanggal_tahbisan?->translatedFormat('j F Y'),
                                'Bertugas sejak' => $pastor->mulai_bertugas,
                            ]);
                        @endphp

                        @if ($fakta)
                            <dl class="mt-6 grid grid-cols-2 gap-4 rounded-2xl bg-white/70 p-5 ring-1 ring-krem-300/70 sm:grid-cols-4">
                                @foreach ($fakta as $label => $nilai)
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-coklat-500">{{ $label }}</dt>
                                        <dd class="mt-1 text-sm font-medium text-coklat-900">{{ $nilai }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        @if ($pastor->motto)
                            <p class="mt-6 border-l-4 border-marun-600 pl-5 font-serif text-lg text-coklat-900 italic">“{{ $pastor->motto }}”</p>
                        @endif

                        {{-- Kisah: 2 paragraf pertama tampil, sisanya dibuka dengan tombol agar halaman tidak terlalu panjang di HP. --}}
                        @if ($pastor->paragraf_cerita)
                            <div x-data="{ buka: false }" class="mt-6 space-y-5 leading-relaxed text-coklat-700 lg:text-lg">
                                @foreach ($pastor->paragraf_cerita as $isi)
                                    <p @if ($loop->index >= 2) x-show="buka" x-cloak x-transition.opacity @endif>{{ $isi }}</p>
                                @endforeach

                                @if (count($pastor->paragraf_cerita) > 2)
                                    <button type="button" x-on:click="buka = ! buka"
                                            class="text-sm font-semibold text-marun-700 underline-offset-4 hover:underline"
                                            x-text="buka ? 'Tutup kisah' : 'Baca kisah selengkapnya'">Baca kisah selengkapnya</button>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach

            {{-- Dewan paroki --}}
            <div class="mt-16 flex flex-col gap-6 border-t border-krem-300/70 pt-12 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Dewan Paroki</p>
                    </div>
                    <h2 class="mt-3 font-serif text-3xl leading-tight font-bold text-coklat-900 sm:text-4xl">Dewan <span class="italic text-marun-700">Paroki</span></h2>
                    <p class="mt-3 text-coklat-700">Para pengurus yang melayani bersama pastor paroki.</p>
                </div>

                @if ($periode)
                    <div class="rounded-2xl bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 px-8 py-5 text-center text-white shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-krem-300">Masa Bakti</p>
                        <p class="mt-1 font-serif text-3xl font-bold">{{ $periode }}</p>
                        <p class="text-sm text-krem-200">{{ $pengurus->count() }} pengurus</p>
                    </div>
                @endif
            </div>

            <div class="mt-12"></div>

            @if ($pengurus->isEmpty())
                <p class="rounded-2xl border border-dashed border-coklat-400/50 p-10 text-center text-coklat-500">Data pengurus belum tersedia.</p>
            @else
                {{-- Pengurus inti --}}
                <div class="text-center">
                    <div class="flex items-center justify-center gap-3">
                        <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">Pengurus Inti</p>
                        <span class="h-px w-10 bg-marun-600/50" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="mx-auto mt-10 grid max-w-5xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($inti as $orang)
                        <div wire:key="inti-{{ $orang->id }}" class="group text-center">
                            <div class="relative mx-auto w-full max-w-[15rem]">
                                <div class="bentuk-jendela absolute inset-0 translate-x-2 translate-y-2 border-2 border-coklat-400/60" aria-hidden="true"></div>
                                @if ($orang->foto_url)
                                    <img src="{{ $orang->foto_kecil_url }}" alt="{{ $orang->nama }}" class="bentuk-jendela relative aspect-[4/5] w-full object-cover shadow-xl" loading="lazy" width="480" height="600">
                                @else
                                    <div class="bentuk-jendela relative flex aspect-[4/5] w-full items-center justify-center bg-gradient-to-b from-coklat-700 to-coklat-900 shadow-xl">
                                        <span class="font-serif text-6xl font-bold text-krem-200">{{ $this->inisial($orang->nama) }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="mt-6 text-xs font-semibold uppercase tracking-widest text-marun-700">{{ $orang->jabatan }}</p>
                            <p class="mt-1 font-serif text-2xl font-semibold text-coklat-900">{{ $orang->nama }}</p>
                            @if ($orang->bidang)
                                <p class="text-sm text-coklat-500">{{ $orang->bidang }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pengurus lainnya: daftar ringkas --}}
                @if ($lainnya->isNotEmpty())
                    <div class="mt-16 rounded-3xl bg-white/70 p-6 shadow-sm ring-1 ring-krem-300/70 backdrop-blur sm:p-10">
                        <h2 class="font-serif text-2xl font-semibold text-coklat-900">Pengurus & Seksi</h2>
                        <div class="mt-6 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($lainnya as $orang)
                                <div wire:key="lain-{{ $orang->id }}" class="flex items-center gap-4">
                                    @if ($orang->foto_url)
                                        <img src="{{ $orang->foto_kecil_url }}" alt="{{ $orang->nama }}" class="size-14 shrink-0 rounded-full object-cover ring-2 ring-krem-300" loading="lazy" width="56" height="56">
                                    @else
                                        <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-krem-200 font-serif text-lg font-semibold text-marun-700">{{ $this->inisial($orang->nama) }}</div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold text-coklat-900">{{ $orang->nama }}</p>
                                        <p class="text-sm text-marun-700">{{ $orang->jabatan }}@if ($orang->bidang) · <span class="text-coklat-500">{{ $orang->bidang }}</span>@endif</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>

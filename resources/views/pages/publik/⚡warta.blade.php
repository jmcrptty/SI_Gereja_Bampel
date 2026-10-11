<?php

use App\Models\Warta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::publik')] #[Title('Warta')] class extends Component {
    use WithPagination;

    #[Computed]
    public function daftarWarta()
    {
        return Warta::terbit()->terbaru()->paginate(10);
    }

    #[Computed]
    public function jumlahWarta(): int
    {
        return Warta::terbit()->count();
    }
}; ?>

<div>
    @php
        $daftar = $this->daftarWarta;
        // Warta terbaru disorot hanya di halaman pertama.
        $sorotan = $daftar->onFirstPage() ? $daftar->first() : null;
        $arsip = $sorotan ? $daftar->slice(1) : $daftar;
    @endphp

    <section class="bg-gradient-to-b from-krem-100 via-krem-50 to-krem-50 pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Warta Paroki" judul="Warta" aksen="Paroki"
                subjudul="Pengumuman, kabar, dan informasi pelayanan paroki setiap minggu. Klik warta untuk membuka file PDF-nya.">
                <div class="flex items-center gap-4 rounded-2xl bg-white px-6 py-4 shadow-sm ring-1 ring-krem-300/70">
                    <p class="font-serif text-4xl font-bold text-marun-700">{{ $this->jumlahWarta }}</p>
                    <p class="text-sm leading-tight text-coklat-700">warta terbit<br><span class="text-coklat-500">setiap hari Minggu</span></p>
                </div>
            </x-publik.kepala-halaman>

            <div class="mt-10 lg:mt-12"></div>

            @if ($daftar->isEmpty())
                <p class="rounded-2xl border border-dashed border-coklat-400/50 p-10 text-center text-coklat-500">Belum ada warta.</p>
            @endif

            {{-- Sorotan: warta terbaru --}}
            @if ($sorotan)
                <a href="{{ $sorotan->pdf_url }}" target="_blank" rel="noopener"
                   class="group relative grid overflow-hidden rounded-3xl bg-white shadow-xl ring-1 shadow-coklat-900/10 ring-krem-300/70 md:grid-cols-5">
                    {{-- Sampul ala halaman warta --}}
                    <div class="relative flex min-h-56 flex-col justify-between bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 p-8 text-white md:col-span-2">
                        <div class="absolute inset-0 opacity-[0.07]" aria-hidden="true"
                             style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 48 48'%3E%3Cpath fill='%23fdf9f3' d='M23 16h2v6h6v2h-6v10h-2V24h-6v-2h6z'/%3E%3C/svg%3E&quot;);"></div>
                        <span class="relative w-fit rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider">Terbaru</span>
                        <div class="relative">
                            <p class="text-sm uppercase tracking-widest text-krem-300">{{ $sorotan->tanggal->translatedFormat('l') }}</p>
                            <p class="font-serif text-5xl font-bold">{{ $sorotan->tanggal->format('j') }}</p>
                            <p class="text-krem-200">{{ $sorotan->tanggal->translatedFormat('F Y') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center p-8 md:col-span-3 lg:p-10">
                        <h2 class="font-serif text-3xl leading-tight font-semibold text-coklat-900 group-hover:text-marun-700 lg:text-4xl">{{ $sorotan->judul }}</h2>
                        @if ($sorotan->ringkasan)
                            <p class="mt-4 text-lg leading-relaxed text-coklat-700">{{ $sorotan->ringkasan }}</p>
                        @endif
                        <span class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-marun-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-marun-700/20 transition group-hover:bg-marun-600">
                            Buka PDF <span aria-hidden="true">&rarr;</span>
                        </span>
                    </div>
                </a>
            @endif

            {{-- Arsip: daftar bergaya koran --}}
            @if ($arsip->isNotEmpty())
                <div class="mt-14">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
                        <h2 class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">{{ $sorotan ? 'Warta Sebelumnya' : 'Arsip Warta' }}</h2>
                    </div>

                    <div class="mt-6 divide-y divide-krem-300/70 border-y border-krem-300/70">
                        @foreach ($arsip as $warta)
                            <a href="{{ $warta->pdf_url }}" target="_blank" rel="noopener" wire:key="warta-{{ $warta->id }}"
                               class="group grid items-center gap-x-6 gap-y-1 py-5 transition hover:bg-white/60 sm:grid-cols-12 sm:px-4">
                                <p class="text-sm font-medium text-marun-700 sm:col-span-3">{{ $warta->tanggal->translatedFormat('j F Y') }}</p>
                                <div class="sm:col-span-8">
                                    <p class="font-serif text-xl font-semibold text-coklat-900 group-hover:text-marun-700">{{ $warta->judul }}</p>
                                    @if ($warta->ringkasan)
                                        <p class="mt-1 line-clamp-1 text-sm text-coklat-500">{{ $warta->ringkasan }}</p>
                                    @endif
                                </div>
                                <span class="hidden justify-self-end text-marun-700 transition group-hover:translate-x-1 sm:col-span-1 sm:block" aria-hidden="true">&rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-10">
                {{ $daftar->links() }}
            </div>
        </div>
    </section>
</div>

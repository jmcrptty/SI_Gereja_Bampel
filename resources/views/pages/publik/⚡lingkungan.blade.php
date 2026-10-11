<?php

use App\Models\Lingkungan;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::publik')] #[Title('Lingkungan')] class extends Component {
    /**
     * Lingkungan dikelompokkan per wilayah/stasi; yang tanpa wilayah ditaruh paling akhir.
     */
    #[Computed]
    public function perWilayah(): Collection
    {
        return Lingkungan::urut()->get()->toBase()
            ->groupBy(fn (Lingkungan $l) => $l->wilayah ?: 'Lainnya')
            ->pipe(fn (Collection $grup) => $grup->except('Lainnya')->union($grup->only('Lainnya')));
    }
}; ?>

<div x-data="{ aktif: 'semua' }">
    @php
        $grup = $this->perWilayah;
        $semua = $grup->flatten(1);
    @endphp

    <section class="bg-gradient-to-b from-krem-100 to-krem-50 pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Lingkungan" judul="Lingkungan" aksen="& Wilayah"
                subjudul="Komunitas basis umat untuk doa, persaudaraan, dan pelayanan sehari-hari. Temukan lingkungan Anda berdasarkan wilayah dan cakupan jalan.">
                <div class="grid grid-cols-3 divide-x divide-krem-300 rounded-2xl bg-white py-4 text-center shadow-sm ring-1 ring-krem-300/70">
                    @foreach ([['Wilayah', $grup->count()], ['Lingkungan', $semua->count()], ['KK', $semua->sum('jumlah_kk')]] as [$label, $nilai])
                        <div class="px-5 sm:px-7">
                            <p class="font-serif text-3xl font-bold text-marun-700">{{ $nilai }}</p>
                            <p class="text-xs text-coklat-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </x-publik.kepala-halaman>

            <div class="mt-10 lg:mt-12"></div>

            @if ($grup->isEmpty())
                <p class="rounded-2xl border border-dashed border-coklat-400/50 p-10 text-center text-coklat-500">Data lingkungan belum tersedia.</p>
            @else
                {{-- Tab filter per wilayah --}}
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0" role="tablist">
                    <button type="button" @click="aktif = 'semua'"
                            :class="aktif === 'semua' ? 'bg-marun-700 text-white shadow-md' : 'bg-white text-coklat-700 ring-1 ring-krem-300 hover:bg-krem-100'"
                            class="shrink-0 rounded-full px-5 py-2 text-sm font-semibold transition">Semua</button>
                    @foreach ($grup as $wilayah => $daftar)
                        <button type="button" wire:key="tab-{{ $loop->index }}" @click="aktif = '{{ $loop->index }}'"
                                :class="aktif === '{{ $loop->index }}' ? 'bg-marun-700 text-white shadow-md' : 'bg-white text-coklat-700 ring-1 ring-krem-300 hover:bg-krem-100'"
                                class="shrink-0 rounded-full px-5 py-2 text-sm font-semibold transition">{{ $wilayah }}</button>
                    @endforeach
                </div>

                <div class="mt-10 space-y-14">
                    @foreach ($grup as $wilayah => $daftar)
                        <section wire:key="wilayah-{{ $loop->index }}" x-show="aktif === 'semua' || aktif === '{{ $loop->index }}'" x-transition.opacity>
                            <div class="flex items-end justify-between gap-4 border-b border-krem-300 pb-3">
                                <h2 class="font-serif text-3xl font-semibold text-coklat-900">{{ $wilayah }}</h2>
                                <p class="text-sm text-coklat-500">{{ $daftar->count() }} lingkungan</p>
                            </div>

                            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                                @foreach ($daftar as $lingkungan)
                                    <article wire:key="lingkungan-{{ $lingkungan->id }}" class="relative flex flex-col overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-krem-300/70">
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-marun-700 via-marun-500 to-coklat-400" aria-hidden="true"></div>

                                        <h3 class="font-serif text-xl font-semibold text-coklat-900">{{ $lingkungan->nama }}</h3>

                                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                            @if ($lingkungan->ketua)
                                                <div class="col-span-2 rounded-xl bg-krem-50 px-3 py-2">
                                                    <dt class="text-xs text-coklat-500">Ketua</dt>
                                                    <dd class="font-medium text-coklat-900">{{ $lingkungan->ketua }}</dd>
                                                </div>
                                            @endif
                                            @if ($lingkungan->jumlah_kk)
                                                <div class="rounded-xl bg-krem-50 px-3 py-2">
                                                    <dt class="text-xs text-coklat-500">Jumlah KK</dt>
                                                    <dd class="font-medium text-coklat-900">{{ $lingkungan->jumlah_kk }}</dd>
                                                </div>
                                            @endif
                                            @if ($lingkungan->jadwal_doa)
                                                <div class="rounded-xl bg-krem-50 px-3 py-2">
                                                    <dt class="text-xs text-coklat-500">Doa</dt>
                                                    <dd class="font-medium text-coklat-900">{{ $lingkungan->jadwal_doa }}</dd>
                                                </div>
                                            @endif
                                        </dl>

                                        @if ($lingkungan->daftar_jalan)
                                            <p class="mt-5 text-xs font-semibold uppercase tracking-widest text-marun-700">Cakupan jalan</p>
                                            <ul class="mt-2 flex flex-wrap gap-2">
                                                @foreach ($lingkungan->daftar_jalan as $jalan)
                                                    <li class="rounded-full bg-krem-100 px-3 py-1 text-xs text-coklat-700">{{ $jalan }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif

            <div class="mt-14 flex flex-col items-center gap-4 rounded-3xl bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 p-8 text-center text-white sm:flex-row sm:text-left">
                <p class="flex-1 text-krem-100">Belum tahu termasuk lingkungan mana? Sekretariat paroki siap membantu Anda.</p>
                @if (($wa = \App\Models\Setting::current()->whatsapp_url))
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="shrink-0 rounded-full bg-krem-50 px-6 py-3 text-sm font-semibold text-coklat-900 hover:bg-white">Hubungi Sekretariat</a>
                @endif
            </div>
        </div>
    </section>
</div>

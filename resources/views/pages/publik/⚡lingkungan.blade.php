<?php

use App\Models\Lingkungan;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::publik')] #[Title('Lingkungan')] class extends Component {
    /** Warna arsiran & kartu, bergantian per lingkungan. */
    private const PALET = ['#8b1e2d', '#b45309', '#047857', '#1d4ed8', '#7c3aed', '#be185d', '#0e7490', '#4d7c0f', '#c2410c', '#4338ca'];

    #[Computed]
    public function daftarLingkungan(): Collection
    {
        return Lingkungan::urut()->get();
    }

    public function warna(int $urutan): string
    {
        return self::PALET[$urutan % count(self::PALET)];
    }

    /**
     * Arsiran lingkungan untuk peta, warnanya sama dengan garis warna di kartu.
     *
     * @return array<int, array{id: int, nama: string, warna: string, titik: array<int, array{0: float, 1: float}>}>
     */
    #[Computed]
    public function petaLingkungan(): array
    {
        return $this->daftarLingkungan
            ->values()
            ->map(fn (Lingkungan $l, int $i) => [
                'id' => $l->id,
                'nama' => $l->nama,
                'warna' => $this->warna($i),
                'titik' => $l->batas_wilayah,
            ])
            ->filter(fn (array $item) => filled($item['titik']))
            ->values()
            ->all();
    }
}; ?>

<div x-data="{ aktif: null }"
     x-on:lingkungan-dipilih="aktif = $event.detail.id; document.getElementById('kartu-lingkungan-' + aktif)?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' })">
    @php($semua = $this->daftarLingkungan)

    <section class="bg-gradient-to-b from-krem-100 to-krem-50 pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Lingkungan" judul="Lingkungan" aksen="Paroki"
                subjudul="Komunitas basis umat untuk doa, persaudaraan, dan pelayanan sehari-hari. Temukan lingkungan Anda di peta atau berdasarkan cakupan jalan.">
                <div class="grid grid-cols-2 divide-x divide-krem-300 rounded-2xl bg-white py-4 text-center shadow-sm ring-1 ring-krem-300/70">
                    @foreach ([['Lingkungan', $semua->count()], ['KK', $semua->sum('jumlah_kk')]] as [$label, $nilai])
                        <div class="px-6 sm:px-8">
                            <p class="font-serif text-3xl font-bold text-marun-700">{{ $nilai }}</p>
                            <p class="text-xs text-coklat-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </x-publik.kepala-halaman>

            <div class="mt-10 lg:mt-12"></div>

            @if ($semua->isEmpty())
                <p class="rounded-2xl border border-dashed border-coklat-400/50 p-10 text-center text-coklat-500">Data lingkungan belum tersedia.</p>
            @else
                {{-- Peta arsiran semua lingkungan; pilih kartu di bawah untuk menyorotnya. --}}
                @if ($this->petaLingkungan)
                    <x-publik.peta-lingkungan :daftar="$this->petaLingkungan" />
                @endif

                {{-- Kartu lingkungan menyamping (geser ke kanan/kiri) --}}
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-serif text-2xl font-semibold text-coklat-900 sm:text-3xl">Daftar Lingkungan</h2>
                        <p class="mt-1 text-sm text-coklat-500">
                            {{ $semua->count() }} lingkungan · geser untuk melihat semua{{ $this->petaLingkungan ? ', ketuk kartu untuk melihat arsirannya di peta' : '' }}.
                        </p>
                    </div>
                    <div class="hidden shrink-0 gap-2 sm:flex">
                        @foreach ([['-1', 'Sebelumnya', 'M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z'], ['1', 'Berikutnya', 'M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z']] as [$arah, $label, $ikon])
                            <button type="button" aria-label="{{ $label }}"
                                    x-on:click="$refs.baris.scrollBy({ left: {{ $arah }} * $refs.baris.clientWidth * 0.8, behavior: 'smooth' })"
                                    class="flex size-10 items-center justify-center rounded-full bg-white text-coklat-700 shadow-sm ring-1 ring-krem-300 transition hover:bg-krem-100">
                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $ikon }}" clip-rule="evenodd" /></svg>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div x-ref="baris" class="-mx-4 mt-5 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pt-1 pb-5 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                    @foreach ($semua as $lingkungan)
                        @php($punyaPeta = filled($lingkungan->batas_wilayah))
                        <article wire:key="lingkungan-{{ $lingkungan->id }}" id="kartu-lingkungan-{{ $lingkungan->id }}"
                                 @if ($punyaPeta)
                                     role="button" tabindex="0"
                                     x-on:click="aktif = {{ $lingkungan->id }}; $dispatch('sorot-lingkungan', { id: {{ $lingkungan->id }} })"
                                     x-on:keydown.enter="$el.click()"
                                 @endif
                                 :class="aktif === {{ $lingkungan->id }} ? 'ring-2 ring-marun-600 shadow-lg -translate-y-1' : 'ring-1 ring-krem-300/70 shadow-sm'"
                                 @class([
                                     'relative flex w-72 shrink-0 snap-start flex-col overflow-hidden rounded-2xl bg-white p-5 transition sm:w-80',
                                     'cursor-pointer hover:shadow-md' => $punyaPeta,
                                 ])>
                            <div class="absolute inset-x-0 top-0 h-1.5" style="background-color: {{ $this->warna($loop->index) }}" aria-hidden="true"></div>

                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-serif text-xl font-semibold text-coklat-900">{{ $lingkungan->nama }}</h3>
                                @if ($punyaPeta)
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold text-white" style="background-color: {{ $this->warna($loop->index) }}">
                                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" /></svg>
                                        Lihat di peta
                                    </span>
                                @endif
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
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
                                <p class="mt-4 text-xs font-semibold uppercase tracking-widest text-marun-700">Cakupan jalan</p>
                                <ul class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($lingkungan->daftar_jalan as $jalan)
                                        <li class="rounded-full bg-krem-100 px-2.5 py-1 text-xs text-coklat-700">{{ $jalan }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 flex flex-col items-center gap-4 rounded-3xl bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700 p-8 text-center text-white sm:flex-row sm:text-left">
                <p class="flex-1 text-krem-100">Belum tahu termasuk lingkungan mana? Sekretariat paroki siap membantu Anda.</p>
                @if (($wa = \App\Models\Setting::current()->whatsapp_url))
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="shrink-0 rounded-full bg-krem-50 px-6 py-3 text-sm font-semibold text-coklat-900 hover:bg-white">Hubungi Sekretariat</a>
                @endif
            </div>
        </div>
    </section>
</div>

<?php

use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::publik')] #[Title('Kegiatan')] class extends Component {
    use WithPagination;

    #[Computed]
    public function akanDatang(): Collection
    {
        return Kegiatan::aktif()->akanDatang()->get();
    }

    #[Computed]
    public function sebelumnya()
    {
        return Kegiatan::aktif()->sebelumnya()->paginate(9);
    }
}; ?>

<div>
    {{-- Akan Datang: linimasa vertikal --}}
    <section class="bg-gradient-to-b from-krem-100 to-krem-50 pt-10 pb-14 lg:pt-14 lg:pb-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Kegiatan Paroki" judul="Kegiatan" aksen="Paroki" tengah
                subjudul="Ikuti berbagai kegiatan pembinaan iman, pelayanan, dan kebersamaan umat. Setiap kegiatan terbuka bagi seluruh umat paroki." />

            <h2 class="mt-14 flex items-center gap-3 font-serif text-2xl font-semibold text-coklat-900 sm:text-3xl">
                <span class="size-3 rounded-full bg-marun-600" aria-hidden="true"></span>
                Akan Datang
            </h2>

            @if ($this->akanDatang->isEmpty())
                <p class="mt-8 rounded-2xl border border-dashed border-coklat-400/50 p-8 text-center text-coklat-500">Belum ada kegiatan yang akan datang.</p>
            @else
                <ol class="relative mt-10 space-y-8 border-l-2 border-krem-300 pl-8 sm:ml-12 sm:pl-12">
                    @foreach ($this->akanDatang as $kegiatan)
                        <li wire:key="akan-{{ $kegiatan->id }}" class="relative">
                            {{-- Penanda tanggal di garis --}}
                            <div class="absolute top-0 -left-[3.15rem] flex size-11 flex-col items-center justify-center rounded-full bg-gradient-to-b from-marun-600 to-marun-700 text-krem-50 shadow-md ring-4 ring-krem-50 sm:-left-[4.75rem] sm:size-16">
                                <span class="font-serif text-base leading-none font-bold sm:text-2xl">{{ $kegiatan->tanggal->format('j') }}</span>
                                <span class="text-[0.6rem] font-semibold uppercase sm:text-xs">{{ $kegiatan->tanggal->translatedFormat('M') }}</span>
                            </div>

                            <a href="{{ route('kegiatan.show', $kegiatan) }}" wire:navigate
                               class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-krem-300/70 transition hover:-translate-y-0.5 hover:shadow-lg sm:flex-row">
                                @if ($kegiatan->foto_url)
                                    <img src="{{ $kegiatan->foto_url }}" alt="" class="aspect-video w-full object-cover sm:aspect-auto sm:w-48" loading="lazy" width="384" height="256">
                                @endif
                                <div class="flex-1 p-5">
                                    <p class="text-sm font-medium text-marun-700">
                                        {{ $kegiatan->tanggal->translatedFormat('l, j F Y') }}@if ($kegiatan->jam_format) · {{ $kegiatan->jam_format }} WIT @endif
                                    </p>
                                    <h3 class="mt-1 font-serif text-xl font-semibold text-coklat-900 group-hover:text-marun-700">{{ $kegiatan->judul }}</h3>
                                    <p class="mt-1 text-sm text-coklat-500">{{ $kegiatan->tempat }}</p>
                                    <p class="mt-3 line-clamp-2 text-coklat-700">{{ $kegiatan->deskripsi }}</p>
                                    <span class="mt-3 inline-block text-sm font-semibold text-marun-700">Selengkapnya &rarr;</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>

    {{-- Kegiatan Sebelumnya: grid --}}
    <section id="sebelumnya" class="scroll-mt-24 bg-krem-50 pb-16 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="rounded-3xl bg-white/60 p-6 ring-1 ring-krem-300/70 sm:p-10">
                <div class="flex items-center gap-3">
                    <span class="h-px w-10 bg-coklat-400" aria-hidden="true"></span>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-coklat-500 sm:text-sm">Arsip</p>
                </div>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-coklat-900">Kegiatan Sebelumnya</h2>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($this->sebelumnya as $kegiatan)
                        <x-publik.kartu-kegiatan :kegiatan="$kegiatan" />
                    @empty
                        <p class="text-coklat-500 sm:col-span-2 lg:col-span-3">Belum ada kegiatan sebelumnya.</p>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $this->sebelumnya->links(data: ['scrollTo' => '#sebelumnya']) }}
                </div>
            </div>
        </div>
    </section>
</div>

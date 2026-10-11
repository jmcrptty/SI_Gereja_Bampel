<?php

use App\Models\Galeri;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::publik')] #[Title('Galeri')] class extends Component {
    use WithPagination;

    #[Computed]
    public function daftarFoto()
    {
        return Galeri::terbaru()->paginate(12);
    }
}; ?>

<div x-data="{ foto: null }" @keydown.escape.window="foto = null">
    <section class="bg-gradient-to-b from-krem-100 to-krem-50 pt-10 pb-16 lg:pt-14 lg:pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-publik.kepala-halaman eyebrow="Galeri" judul="Galeri" aksen="Paroki" tengah
                subjudul="Dokumentasi perayaan, kegiatan, dan kebersamaan umat. Klik foto untuk melihat ukuran penuh." />

            <div class="mt-10 lg:mt-12"></div>

            @if ($this->daftarFoto->isEmpty())
                <p class="rounded-2xl border border-dashed border-coklat-400/50 p-10 text-center text-coklat-500">Belum ada foto.</p>
            @else
                {{-- Masonry: tinggi foto bervariasi --}}
                <div class="columns-2 gap-4 sm:columns-3 lg:columns-4">
                    @foreach ($this->daftarFoto as $galeri)
                        @php($rasio = ['aspect-[4/5]', 'aspect-square', 'aspect-[4/3]', 'aspect-[3/4]'][$loop->index % 4])
                        <button type="button" wire:key="foto-{{ $galeri->id }}"
                                @click="foto = { src: @js($galeri->foto_url), judul: @js($galeri->judul), tanggal: @js($galeri->tanggal?->translatedFormat('j F Y')) }"
                                class="group relative mb-4 block w-full break-inside-avoid overflow-hidden rounded-2xl text-left shadow-sm ring-1 ring-krem-300/70">
                            <img src="{{ $galeri->foto_url }}" alt="{{ $galeri->judul }}"
                                 class="{{ $rasio }} w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" width="400" height="500">
                            <span class="absolute inset-0 bg-gradient-to-t from-coklat-900/80 via-coklat-900/0 to-transparent opacity-80 transition group-hover:opacity-100"></span>
                            <span class="absolute inset-x-0 bottom-0 p-3">
                                <span class="block font-serif font-semibold text-white">{{ $galeri->judul }}</span>
                                @if ($galeri->tanggal)
                                    <span class="block text-xs text-krem-200">{{ $galeri->tanggal->translatedFormat('j F Y') }}</span>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="mt-8">
                {{ $this->daftarFoto->links() }}
            </div>
        </div>
    </section>

    {{-- Tampilan foto besar --}}
    <div x-show="foto" x-cloak x-transition.opacity @click="foto = null"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-coklat-900/95 p-4">
        <img :src="foto?.src" :alt="foto?.judul" class="max-h-[80vh] max-w-full rounded-lg object-contain shadow-2xl" @click.stop>
        <p class="mt-4 text-center font-serif text-xl text-white" x-text="foto?.judul"></p>
        <p class="text-center text-sm text-krem-200" x-text="foto?.tanggal"></p>
        <button type="button" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Tutup">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div>

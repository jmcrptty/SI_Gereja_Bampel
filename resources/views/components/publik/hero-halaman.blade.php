@props([
    'eyebrow',
    'judul',
    'aksen' => null,
    'subjudul' => null,
    'gambar' => null,
    'posisiGambar' => 'object-center',
    'breadcrumb' => null,
])

{{--
    Hero halaman publik (gelap, navbar transparan melayang di atasnya).
    - gambar: URL foto latar; tanpa gambar dipakai gradasi cokelat-marun dengan pola salib.
    - slot: konten tambahan di kanan (kartu info, statistik, kolase); bila kosong judul memakai lebar penuh.
--}}
<section class="relative isolate overflow-hidden bg-coklat-900">
    @if ($gambar)
        <img src="{{ $gambar }}" alt="" class="absolute inset-0 -z-20 size-full object-cover {{ $posisiGambar }}" fetchpriority="high">
        {{-- Kiri gelap agar judul terbaca, kanan lebih terang agar foto/ilustrasi tetap terlihat --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-coklat-900/95 via-coklat-900/65 to-coklat-900/15"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-coklat-900/60 to-transparent"></div>
    @else
        <div class="absolute inset-0 -z-20 bg-gradient-to-br from-coklat-900 via-coklat-700 to-marun-700"></div>
        <div class="absolute -top-24 right-0 -z-10 size-[28rem] rounded-full bg-marun-500/30 blur-3xl" aria-hidden="true"></div>
        <div class="absolute inset-0 -z-10 opacity-[0.07]" aria-hidden="true"
             style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 48 48'%3E%3Cpath fill='%23fdf9f3' d='M23 16h2v6h6v2h-6v10h-2V24h-6v-2h6z'/%3E%3C/svg%3E&quot;);"></div>
    @endif

    <div @class([
        'mx-auto max-w-6xl px-4 pt-32 pb-16 sm:px-6 lg:pt-40 lg:pb-24',
        'grid items-center gap-12 lg:grid-cols-12' => $slot->isNotEmpty(),
    ])>
        <div @class(['lg:col-span-7' => $slot->isNotEmpty()])>
            <nav class="text-sm text-krem-200" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-white">Beranda</a>
                <span class="mx-2 text-krem-300/60">/</span>
                @if ($breadcrumb)
                    <a href="{{ $breadcrumb['url'] }}" wire:navigate class="hover:text-white">{{ $breadcrumb['label'] }}</a>
                    <span class="mx-2 text-krem-300/60">/</span>
                @endif
                <span class="text-white">{{ $eyebrow }}</span>
            </nav>

            <div class="mt-6 flex items-center gap-3">
                <span class="h-px w-10 bg-krem-300" aria-hidden="true"></span>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-krem-300 sm:text-sm">{{ $eyebrow }}</p>
            </div>

            <h1 class="mt-4 font-serif text-4xl leading-tight font-bold text-white drop-shadow-lg sm:text-5xl lg:text-6xl">
                {{ $judul }}@if ($aksen) <span class="italic text-krem-300">{{ $aksen }}</span>@endif
            </h1>

            @if ($subjudul)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-krem-100/90">{{ $subjudul }}</p>
            @endif
        </div>

        @if ($slot->isNotEmpty())
            <div class="lg:col-span-5">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>

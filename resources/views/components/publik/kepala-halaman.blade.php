@props([
    'eyebrow',
    'judul',
    'aksen' => null,
    'subjudul' => null,
    'tengah' => false,
])

{{--
    Judul ringkas untuk halaman tanpa hero: langsung di latar krem, menyatu dengan isi.
    - tengah: judul rata tengah.
    - slot: info tambahan di kanan judul (statistik, badge); diabaikan bila tengah.
--}}
<div @class([
    'flex flex-col gap-6',
    'items-center text-center' => $tengah,
    'lg:flex-row lg:items-end lg:justify-between' => ! $tengah,
])>
    <div @class(['max-w-2xl' => true])>
        <nav class="text-sm text-coklat-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-marun-700">Beranda</a>
            <span class="mx-2 text-coklat-400">/</span>
            <span class="text-coklat-900">{{ $eyebrow }}</span>
        </nav>

        <div @class(['mt-5 flex items-center gap-3', 'justify-center' => $tengah])>
            <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-marun-700 sm:text-sm">{{ $eyebrow }}</p>
            @if ($tengah)
                <span class="h-px w-10 bg-marun-600" aria-hidden="true"></span>
            @endif
        </div>

        <h1 class="mt-3 font-serif text-4xl leading-tight font-bold text-coklat-900 sm:text-5xl">
            {{ $judul }}@if ($aksen) <span class="italic text-marun-700">{{ $aksen }}</span>@endif
        </h1>

        @if ($subjudul)
            <p class="mt-4 text-lg leading-relaxed text-coklat-700">{{ $subjudul }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>

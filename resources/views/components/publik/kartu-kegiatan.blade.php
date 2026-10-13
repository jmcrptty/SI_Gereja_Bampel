@props(['kegiatan'])

<a href="{{ route('kegiatan.show', $kegiatan) }}" wire:navigate
   class="group flex flex-col overflow-hidden rounded-xl border border-krem-200 bg-white transition hover:shadow-md">
    @if ($kegiatan->foto_url)
        <img src="{{ $kegiatan->foto_kecil_url }}" alt="{{ $kegiatan->judul }}" class="aspect-video w-full object-cover" loading="lazy" width="640" height="360">
    @else
        <div class="flex aspect-video w-full items-center justify-center bg-krem-100 text-coklat-400">
            <x-publik.ikon-kegiatan class="size-14" />
        </div>
    @endif

    <div class="flex flex-1 flex-col p-4">
        <p class="text-sm font-medium text-marun-700">
            {{ $kegiatan->tanggal->translatedFormat('l, j F Y') }}@if ($kegiatan->jam_format) · {{ $kegiatan->jam_format }}@endif
        </p>
        <h3 class="mt-1 font-semibold text-coklat-900 group-hover:underline">{{ $kegiatan->judul }}</h3>
        <p class="mt-1 text-sm text-coklat-500">{{ $kegiatan->tempat }}</p>
    </div>
</a>

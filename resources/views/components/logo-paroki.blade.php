@props(['cincin' => false])

{{--
    Logo paroki yang sama di semua tempat (navbar website, sidebar dashboard, halaman login).
    Memakai logo upload (Admin > Profil Paroki); bila belum ada, lingkaran marun berikon salib.
    Ukuran diatur lewat class, contoh: <x-logo-paroki class="size-10" />
    - cincin: tambahkan lingkar krem tipis (untuk di atas latar gelap/foto).
--}}
@php($logoUrl = \App\Models\Setting::current()->fileUrl('logo'))

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="Logo {{ \App\Models\Setting::current()->nama_gereja }}"
         {{ $attributes->class(['shrink-0 rounded-full object-cover shadow', 'ring-2 ring-krem-300/70' => $cincin]) }}>
@else
    <span {{ $attributes->class(['flex shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-marun-500 to-marun-700 text-krem-50 shadow', 'ring-2 ring-krem-300/70' => $cincin]) }}>
        <x-app-logo-icon class="size-1/2" />
    </span>
@endif

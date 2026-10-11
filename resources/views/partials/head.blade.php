<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{--
    Editor crop foto (komponen Alpine "pemotongFoto"). Sengaja script biasa (bukan module Vite) di <head>
    agar terdaftar sebelum Livewire menyalakan Alpine; module Vite baru jalan setelah itu.
--}}
<script src="{{ asset('js/pemotong-foto.js') }}?v={{ filemtime(public_path('js/pemotong-foto.js')) }}"></script>

@fluxAppearance
{{-- Dashboard selalu tampil terang (krem), tidak mengikuti mode gelap sistem. Sidebar tetap cokelat lewat class "dark" miliknya sendiri. --}}
<script>window.Flux.applyAppearance('light')</script>

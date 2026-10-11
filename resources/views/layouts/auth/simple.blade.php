@php($setting = \App\Models\Setting::current())

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-krem-50 antialiased">
        <div class="grid min-h-svh lg:grid-cols-2">
            {{-- Panel kiri: foto gereja, logo & nama paroki, ayat (desktop) --}}
            <div class="relative hidden overflow-hidden bg-coklat-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
                <img src="{{ $setting->fileUrl('foto_hero') ?? asset('images/hero-gereja.svg') }}" alt="" class="absolute inset-0 size-full object-cover">
                {{-- Gelap di atas (logo terbaca) dan di bawah (ayat terbaca), sedikit rona marun --}}
                <div class="absolute inset-0 bg-gradient-to-b from-coklat-900/85 via-coklat-900/30 to-coklat-900"></div>
                <div class="absolute -right-24 -bottom-24 size-96 rounded-full bg-marun-500/25 blur-3xl"></div>

                <a href="{{ route('home') }}" class="relative flex items-center gap-4 text-white" wire:navigate>
                    <x-logo-paroki class="size-14" cincin />
                    <span class="leading-tight">
                        <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-krem-300">
                            <span class="h-px w-6 bg-krem-300"></span> Paroki
                        </span>
                        <span class="mt-1 block font-serif text-2xl font-semibold">{{ \Illuminate\Support\Str::after($setting->nama_gereja, 'Paroki ') }}</span>
                    </span>
                </a>

                <figure class="relative max-w-md text-white">
                    <svg class="size-10 text-krem-300/60" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.5 6C6.5 6.7 4 9.3 4 13v5h6v-6H7c0-2.2 1.3-3.8 3-4.3zm10 0c-3 .7-5.5 3.3-5.5 7v5h6v-6h-3c0-2.2 1.3-3.8 3-4.3z" /></svg>
                    <blockquote class="mt-3 font-serif text-2xl leading-relaxed italic">
                        Sebab di mana dua atau tiga orang berkumpul dalam nama-Ku, di situ Aku ada di tengah-tengah mereka.
                    </blockquote>
                    <figcaption class="mt-4 flex items-center gap-3 text-sm text-krem-300">
                        <span class="h-px w-10 bg-krem-300"></span> Matius 18:20
                    </figcaption>
                </figure>
            </div>

            {{-- Panel kanan: formulir --}}
            <div class="relative isolate flex items-center justify-center overflow-hidden bg-gradient-to-b from-krem-50 via-krem-100 to-krem-50 p-6 md:p-10">
                <div class="absolute -top-24 -right-24 -z-10 size-80 rounded-full bg-marun-500/10 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-24 -left-24 -z-10 size-80 rounded-full bg-coklat-400/20 blur-3xl" aria-hidden="true"></div>

                <div class="w-full max-w-sm">
                    {{-- Nama paroki (HP) --}}
                    <p class="mb-12 text-center font-serif text-lg font-semibold text-coklat-900 lg:hidden">{{ $setting->nama_gereja }}</p>

                    <div class="relative rounded-3xl bg-white px-8 pt-14 pb-8 shadow-xl ring-1 shadow-coklat-900/10 ring-krem-200">
                        <div class="absolute inset-x-0 top-0 h-1 overflow-hidden rounded-t-3xl bg-gradient-to-r from-marun-700 via-marun-500 to-coklat-400" aria-hidden="true"></div>

                        {{-- Logo paroki "menggantung" di tepi atas kartu --}}
                        <a href="{{ route('home') }}" class="absolute -top-9 left-1/2 -translate-x-1/2 rounded-full bg-white p-1.5 shadow-lg ring-1 ring-krem-200" wire:navigate aria-label="{{ $setting->nama_gereja }}">
                            <x-logo-paroki class="size-16" />
                        </a>

                        <div class="mb-6 flex items-center justify-center gap-3">
                            <span class="h-px w-8 bg-marun-600/50" aria-hidden="true"></span>
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-marun-700">Dashboard Admin</p>
                            <span class="h-px w-8 bg-marun-600/50" aria-hidden="true"></span>
                        </div>

                        <div class="flex flex-col gap-6">
                            {{ $slot }}
                        </div>
                    </div>

                    <p class="mt-6 text-center text-sm text-coklat-500">
                        <a href="{{ route('home') }}" class="hover:text-marun-700" wire:navigate>&larr; Kembali ke website</a>
                    </p>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

@php
    $setting = \App\Models\Setting::current();

    $menu = [
        ['route' => 'jadwal-misa', 'label' => 'Jadwal Misa'],
        ['route' => 'warta', 'label' => 'Warta'],
        ['route' => 'kegiatan', 'label' => 'Kegiatan'],
        ['route' => 'galeri', 'label' => 'Galeri'],
    ];

    $menuTentang = [
        ['route' => 'profil', 'label' => 'Profil Paroki'],
        ['route' => 'dewan-paroki', 'label' => 'Dewan Paroki'],
        ['route' => 'lingkungan', 'label' => 'Lingkungan'],
    ];

    $tentangAktif = request()->routeIs('profil', 'dewan-paroki', 'lingkungan');

    // Halaman yang diawali hero gelap: navbar melayang transparan lalu solid setelah di-scroll.
    // Halaman lain langsung ke isi, jadi navbar solid sejak awal.
    $navTransparan = request()->routeIs('home', 'profil', 'jadwal-misa', 'kegiatan.show');

    // Meta & Open Graph (pratinjau link di WhatsApp/Facebook). Halaman bisa mengirim $ogGambar & $ogDeskripsi lewat ->layout().
    $judulHalaman = filled($title ?? null) ? $title.' - '.$setting->nama_gereja : $setting->nama_gereja;
    $metaDeskripsi = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags((string) ($ogDeskripsi ?? $setting->sejarah_singkat)))),
        155,
    ) ?: 'Website informasi '.$setting->nama_gereja.', Merauke, Papua Selatan.';
    // Gambar harus URL absolut & raster (PNG/JPG/WebP); SVG tidak didukung WhatsApp/Facebook.
    $metaGambar = url($ogGambar ?? $setting->fileUrl('foto_hero') ?? $setting->fileUrl('foto_gereja') ?? asset('images/og-default.png'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $judulHalaman }}</title>
        <meta name="description" content="{{ $metaDeskripsi }}">
        <link rel="canonical" href="{{ url()->current() }}">

        {{-- Open Graph & Twitter Card --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $setting->nama_gereja }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:title" content="{{ $judulHalaman }}">
        <meta property="og:description" content="{{ $metaDeskripsi }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $metaGambar }}">
        <meta property="og:image:alt" content="{{ $setting->nama_gereja }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $judulHalaman }}">
        <meta name="twitter:description" content="{{ $metaDeskripsi }}">
        <meta name="twitter:image" content="{{ $metaGambar }}">

        @if ($setting->logo)
            <link rel="icon" href="{{ $setting->fileUrl('logo') }}" type="image/webp">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-krem-50 font-sans text-coklat-900 antialiased">
        {{-- Navbar --}}
        {{--
            data-transparan="true" → navbar transparan (teks putih); "false" → solid putih.
            Hanya di beranda: transparan selama belum di-scroll dan menu HP tertutup.
        --}}
        <header
            x-data="{ buka: false, terscroll: false, bisaTransparan: @js($navTransparan) }"
            x-init="terscroll = window.scrollY > 24"
            @scroll.window="terscroll = window.scrollY > 24"
            :data-transparan="(bisaTransparan && ! terscroll && ! buka).toString()"
            data-transparan="{{ $navTransparan ? 'true' : 'false' }}"
            @class([
                'group/nav inset-x-0 top-0 z-40 border-b border-krem-200 bg-white/95 text-coklat-900 shadow-sm backdrop-blur transition-colors duration-300',
                'data-[transparan=true]:border-transparent data-[transparan=true]:bg-transparent data-[transparan=true]:text-white data-[transparan=true]:shadow-none data-[transparan=true]:backdrop-blur-none',
                'fixed' => $navTransparan,
                'sticky' => ! $navTransparan,
            ])>
            {{-- Tinggi navbar tetap h-16 (4rem). --}}
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" wire:navigate>
                    <x-logo-paroki class="size-10" />
                    <span class="line-clamp-2 text-sm font-semibold leading-tight text-coklat-900 transition-colors group-data-[transparan=true]/nav:text-white group-data-[transparan=true]/nav:drop-shadow sm:text-base">{{ $setting->nama_gereja }}</span>
                </a>

                {{-- Menu desktop --}}
                <div class="hidden items-center gap-1 text-sm font-medium lg:flex">
                    <a href="{{ route('home') }}" wire:navigate
                       @class(['rounded-md px-3 py-2 hover:bg-krem-100 group-data-[transparan=true]/nav:hover:bg-white/15', 'text-marun-700 group-data-[transparan=true]/nav:text-krem-300' => request()->routeIs('home')])>Beranda</a>

                    <div x-data="{ tentang: false }" class="relative" @click.outside="tentang = false">
                        <button type="button" @click="tentang = ! tentang"
                                @class(['flex items-center gap-1 rounded-md px-3 py-2 hover:bg-krem-100 group-data-[transparan=true]/nav:hover:bg-white/15', 'text-marun-700 group-data-[transparan=true]/nav:text-krem-300' => $tentangAktif])>
                            Tentang Kami
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                        <div x-show="tentang" x-cloak x-transition class="absolute left-0 mt-1 w-48 rounded-lg border border-krem-200 bg-white py-1 text-coklat-900 shadow-lg">
                            @foreach ($menuTentang as $item)
                                <a href="{{ route($item['route']) }}" wire:navigate class="block px-4 py-2 hover:bg-krem-100">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($menu as $item)
                        <a href="{{ route($item['route']) }}" wire:navigate
                           @class(['rounded-md px-3 py-2 hover:bg-krem-100 group-data-[transparan=true]/nav:hover:bg-white/15', 'text-marun-700 group-data-[transparan=true]/nav:text-krem-300' => request()->routeIs($item['route'].'*')])>{{ $item['label'] }}</a>
                    @endforeach
                </div>

                {{-- Tombol menu HP --}}
                <button type="button" @click="buka = ! buka" class="rounded-md p-2 hover:bg-krem-100 group-data-[transparan=true]/nav:hover:bg-white/15 lg:hidden" aria-label="Buka menu">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
            </nav>

            {{-- Menu HP --}}
            <div x-show="buka" x-cloak x-transition class="border-t border-krem-200 bg-white px-4 py-2 text-sm font-medium text-coklat-900 lg:hidden">
                <a href="{{ route('home') }}" wire:navigate class="block rounded-md px-3 py-2 hover:bg-krem-100">Beranda</a>
                <p class="px-3 pt-3 pb-1 text-xs uppercase tracking-wide text-coklat-500">Tentang Kami</p>
                @foreach ($menuTentang as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate class="block rounded-md px-6 py-2 hover:bg-krem-100">{{ $item['label'] }}</a>
                @endforeach
                @foreach ($menu as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate class="block rounded-md px-3 py-2 hover:bg-krem-100">{{ $item['label'] }}</a>
                @endforeach
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bg-coklat-900 text-krem-100">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-base font-semibold text-white">{{ $setting->nama_gereja }}</p>
                    @if ($setting->alamat)
                        <p class="mt-2 whitespace-pre-line">{{ $setting->alamat }}</p>
                    @endif
                    @if ($setting->telepon)
                        <p class="mt-2">Telp. {{ $setting->telepon }}</p>
                    @endif
                    @if ($setting->whatsapp_url)
                        <p><a href="{{ $setting->whatsapp_url }}" target="_blank" rel="noopener" class="hover:text-white">WA {{ $setting->whatsapp }}</a></p>
                    @endif
                    @if ($setting->email)
                        <p><a href="mailto:{{ $setting->email }}" class="hover:text-white">{{ $setting->email }}</a></p>
                    @endif

                    {{-- Media sosial: Instagram, YouTube, WhatsApp selalu tampil; yang belum diisi di admin tampil redup "segera hadir". --}}
                    <p class="mt-6 text-xs font-semibold uppercase tracking-widest text-krem-300">Ikuti Kami</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($setting->media_sosial_footer as $medsos)
                            @if ($medsos['url'])
                                <a href="{{ $medsos['url'] }}" target="_blank" rel="noopener" aria-label="{{ $medsos['label'] }}" title="{{ $medsos['label'] }}"
                                   @class([
                                       'flex size-10 items-center justify-center rounded-full bg-white/10 text-krem-100 ring-1 ring-white/15 transition hover:-translate-y-0.5 hover:text-white',
                                       'hover:bg-gradient-to-br hover:from-[#f58529] hover:via-[#dd2a7b] hover:to-[#8134af]' => $medsos['jenis'] === 'instagram',
                                       'hover:bg-[#ff0000]' => $medsos['jenis'] === 'youtube',
                                       'hover:bg-[#25d366]' => $medsos['jenis'] === 'whatsapp',
                                       'hover:bg-[#1877f2]' => $medsos['jenis'] === 'facebook',
                                   ])>
                                    <x-ikon-medsos :jenis="$medsos['jenis']" class="size-5" />
                                </a>
                            @else
                                <span title="{{ $medsos['label'] }} segera hadir" aria-label="{{ $medsos['label'] }} segera hadir"
                                      class="flex size-10 cursor-default items-center justify-center rounded-full bg-white/5 text-krem-100/40 ring-1 ring-white/10">
                                    <x-ikon-medsos :jenis="$medsos['jenis']" class="size-5" />
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="font-semibold text-white">Jam Pelayanan Sekretariat</p>
                    <ul class="mt-2 space-y-1">
                        @forelse ($setting->daftar_jam_sekretariat as $baris)
                            <li>{{ $baris }}</li>
                        @empty
                            <li>Hubungi sekretariat.</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-white">Menu</p>
                    <ul class="mt-2 grid grid-cols-2 gap-1">
                        @foreach ([...$menuTentang, ...$menu] as $item)
                            <li><a href="{{ route($item['route']) }}" wire:navigate class="hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="border-t border-coklat-700 py-4 text-center text-xs text-krem-300">
                &copy; {{ now()->year }} {{ $setting->nama_gereja }}
            </div>
        </footer>

        {{-- Tombol WhatsApp sekretariat melayang --}}
        @if ($setting->whatsapp_url)
            <a href="{{ $setting->whatsapp_url }}" target="_blank" rel="noopener"
               class="fixed right-4 bottom-4 z-50 flex size-14 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition hover:bg-green-600"
               aria-label="Chat WhatsApp sekretariat">
                <svg class="size-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.5h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.07.15.2 2.1 3.2 5.08 4.49.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.38 9.38 0 0 1-1.44-5c0-5.19 4.23-9.41 9.43-9.41 2.52 0 4.88.98 6.66 2.76a9.35 9.35 0 0 1 2.76 6.66c0 5.19-4.23 9.41-9.43 9.41m8.02-17.43A11.27 11.27 0 0 0 12.05.75C5.8.75.7 5.84.7 12.1c0 2 .52 3.95 1.52 5.67L.6 23.25l5.6-1.47a11.32 11.32 0 0 0 5.84 1.6h.01c6.25 0 11.35-5.09 11.35-11.35 0-3.03-1.18-5.88-3.33-8.02" /></svg>
            </a>
        @endif
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        {{-- Sidebar selalu gelap (class "dark") berwarna cokelat tua, senada dengan footer halaman depan. --}}
        <flux:sidebar sticky collapsible="mobile" class="dark border-e border-coklat-700/60 bg-gradient-to-b from-coklat-900 to-[#24140c]">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            @php
                $menuKelola = [
                    ['route' => 'admin.profil-gereja', 'icon' => 'building-library', 'label' => 'Profil Paroki'],
                    ['route' => 'admin.jadwal-misa', 'icon' => 'calendar-days', 'label' => 'Jadwal Misa'],
                    ['route' => 'admin.warta', 'icon' => 'newspaper', 'label' => 'Warta'],
                    ['route' => 'admin.kegiatan', 'icon' => 'megaphone', 'label' => 'Kegiatan'],
                    ['route' => 'admin.dewan-paroki', 'icon' => 'user-group', 'label' => 'Dewan Paroki'],
                    ['route' => 'admin.lingkungan', 'icon' => 'map', 'label' => 'Lingkungan'],
                    ['route' => 'admin.galeri', 'icon' => 'photo', 'label' => 'Galeri'],
                ];
            @endphp

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    Dashboard
                </flux:sidebar.item>

                <flux:sidebar.group heading="Kelola" class="grid">
                    @foreach ($menuKelola as $menu)
                        <flux:sidebar.item :icon="$menu['icon']" :href="route($menu['route'])" :current="request()->routeIs($menu['route'])" wire:navigate>
                            {{ $menu['label'] }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Kartu ajakan melihat website --}}
            <a href="{{ route('home') }}" target="_blank" class="mx-2 hidden rounded-xl bg-gradient-to-br from-marun-600 to-marun-700 p-4 text-krem-50 shadow-lg transition hover:from-marun-500 lg:block">
                <p class="font-serif text-base font-semibold">Lihat Website</p>
                <p class="mt-1 text-xs text-krem-200">Cek hasil perubahan seperti yang dilihat umat &rarr;</p>
            </a>

            <flux:sidebar.nav class="lg:hidden">
                <flux:sidebar.item icon="globe-alt" :href="route('home')" target="_blank">
                    Lihat Website
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-zinc-200 bg-white/90 backdrop-blur lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

<?php

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Lingkungan;
use App\Models\Pengurus;
use App\Models\Warta;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    /**
     * @return array<int, array{label: string, nilai: int, icon: string, route: string}>
     */
    #[Computed]
    public function ringkasan(): array
    {
        return [
            ['label' => 'Misa aktif', 'nilai' => JadwalMisa::aktif()->count(), 'icon' => 'calendar-days', 'route' => 'admin.jadwal-misa'],
            ['label' => 'Kegiatan akan datang', 'nilai' => Kegiatan::aktif()->akanDatang()->count(), 'icon' => 'megaphone', 'route' => 'admin.kegiatan'],
            ['label' => 'Warta terbit bulan ini', 'nilai' => Warta::terbit()->whereBetween('tanggal', [today()->startOfMonth(), today()->endOfMonth()])->count(), 'icon' => 'newspaper', 'route' => 'admin.warta'],
            ['label' => 'Foto galeri', 'nilai' => Galeri::count(), 'icon' => 'photo', 'route' => 'admin.galeri'],
        ];
    }

    /**
     * 5 data terakhir yang diubah dari semua tabel kelola.
     *
     * @return Collection<int, array{jenis: string, judul: string, waktu: \DateTimeInterface, route: string}>
     */
    #[Computed]
    public function terakhirDiubah(): Collection
    {
        $sumber = [
            [JadwalMisa::class, 'Jadwal Misa', 'admin.jadwal-misa', fn (JadwalMisa $m) => $m->nama_perayaan ?? "Misa {$m->nama_hari} {$m->jam_format}"],
            [Warta::class, 'Warta', 'admin.warta', fn (Warta $w) => $w->judul],
            [Kegiatan::class, 'Kegiatan', 'admin.kegiatan', fn (Kegiatan $k) => $k->judul],
            [Pengurus::class, 'Dewan Paroki', 'admin.dewan-paroki', fn (Pengurus $p) => "{$p->nama} ({$p->jabatan})"],
            [Lingkungan::class, 'Lingkungan', 'admin.lingkungan', fn (Lingkungan $l) => $l->nama],
            [Galeri::class, 'Galeri', 'admin.galeri', fn (Galeri $g) => $g->judul],
        ];

        return collect($sumber)
            ->flatMap(fn (array $s) => $s[0]::query()->latest('updated_at')->limit(5)->get()
                ->map(fn ($model) => [
                    'jenis' => $s[1],
                    'judul' => $s[3]($model),
                    'waktu' => $model->updated_at,
                    'route' => $s[2],
                ]))
            ->sortByDesc('waktu')
            ->take(5)
            ->values();
    }

    #[Computed]
    public function kegiatanTerdekat(): \Illuminate\Database\Eloquent\Collection
    {
        return Kegiatan::aktif()->akanDatang()->limit(4)->get();
    }
}; ?>

<section class="w-full space-y-8">
    @php
        $jam = now()->hour;
        $salam = match (true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    @endphp

    {{-- Banner sapaan --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-krem-100 via-krem-50 to-white p-6 shadow-sm ring-1 ring-krem-200 sm:p-8">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-marun-700 via-marun-500 to-coklat-400" aria-hidden="true"></div>
        <div class="absolute -top-16 -right-16 size-64 rounded-full bg-marun-500/10 blur-3xl" aria-hidden="true"></div>
        <svg class="pointer-events-none absolute right-8 -bottom-6 h-40 text-marun-700/[0.07]" viewBox="0 0 100 140" fill="currentColor" aria-hidden="true">
            <path d="M42 0h16v38h34v16H58v86H42V54H8V38h34z" />
        </svg>

        <div class="relative">
            <p class="text-sm font-medium text-marun-700">{{ now()->translatedFormat('l, j F Y') }}</p>
            <h1 class="mt-1 font-serif text-3xl font-semibold text-coklat-900 sm:text-4xl">{{ $salam }}, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-xl text-coklat-700">Kelola informasi {{ \App\Models\Setting::current()->nama_gereja }} dari satu tempat.</p>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ([['admin.warta', 'newspaper', 'Unggah Warta'], ['admin.kegiatan', 'megaphone', 'Tambah Kegiatan'], ['admin.jadwal-misa', 'calendar-days', 'Atur Jadwal Misa'], ['admin.galeri', 'photo', 'Unggah Foto']] as [$route, $icon, $label])
                    <a href="{{ route($route) }}" wire:navigate wire:key="aksi-{{ $route }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                           'bg-marun-700 text-white shadow-md shadow-marun-700/20 hover:bg-marun-600' => $loop->first,
                           'bg-white text-coklat-900 ring-1 ring-krem-300 hover:bg-krem-100' => ! $loop->first,
                       ])>
                        <flux:icon :name="$icon" variant="micro" class="size-4" />
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->ringkasan as $item)
            <a href="{{ route($item['route']) }}" wire:navigate wire:key="ringkasan-{{ $item['route'] }}"
               class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-zinc-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-zinc-800 dark:ring-zinc-700">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-marun-700 via-marun-500 to-coklat-400" aria-hidden="true"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-serif text-4xl font-bold text-zinc-900 dark:text-white">{{ $item['nilai'] }}</p>
                        <flux:text class="mt-1">{{ $item['label'] }}</flux:text>
                    </div>
                    <div class="rounded-xl bg-marun-700/10 p-3 text-marun-700 transition group-hover:bg-marun-700 group-hover:text-white dark:text-krem-300">
                        <flux:icon :name="$item['icon']" class="size-6" />
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Terakhir diubah --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 xl:col-span-2 dark:bg-zinc-800 dark:ring-zinc-700">
            <flux:heading size="lg" class="font-serif !text-xl">Terakhir diubah</flux:heading>

            <flux:table class="mt-2">
            <flux:table.columns>
                <flux:table.column>Data</flux:table.column>
                <flux:table.column>Menu</flux:table.column>
                <flux:table.column>Waktu</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->terakhirDiubah as $index => $data)
                    <flux:table.row :key="$index">
                        <flux:table.cell variant="strong">{{ $data['judul'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:link :href="route($data['route'])" wire:navigate>{{ $data['jenis'] }}</flux:link>
                        </flux:table.cell>
                        <flux:table.cell>{{ $data['waktu']->diffForHumans() }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center">Belum ada data.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
            </flux:table>
        </div>

        {{-- Kegiatan terdekat --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-700">
            <div class="flex items-center justify-between gap-2">
                <flux:heading size="lg" class="font-serif !text-xl">Kegiatan terdekat</flux:heading>
                <flux:link :href="route('admin.kegiatan')" wire:navigate class="text-sm">Kelola</flux:link>
            </div>

            <ul class="mt-4 space-y-4">
                @forelse ($this->kegiatanTerdekat as $kegiatan)
                    <li wire:key="terdekat-{{ $kegiatan->id }}" class="flex items-center gap-3">
                        <span class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-b from-marun-600 to-marun-700 text-krem-50">
                            <span class="text-base leading-none font-bold">{{ $kegiatan->tanggal->format('j') }}</span>
                            <span class="text-[0.6rem] font-semibold uppercase">{{ $kegiatan->tanggal->translatedFormat('M') }}</span>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $kegiatan->judul }}</p>
                            <flux:text class="truncate text-xs">{{ $kegiatan->tempat }}@if ($kegiatan->jam_format) · {{ $kegiatan->jam_format }}@endif</flux:text>
                        </div>
                    </li>
                @empty
                    <li><flux:text>Belum ada kegiatan terdekat.</flux:text></li>
                @endforelse
            </ul>
        </div>
    </div>
</section>

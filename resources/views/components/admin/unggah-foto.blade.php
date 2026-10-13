@props([
    'model',
    'label' => null,
    'pratinjau' => null,
    'rasio' => 'aspect-video',
    'rasioPotong' => null,
    'keterangan' => 'Maks 3 MB. Otomatis dikompres ke WebP.',
    'input' => true,
    'hapus' => null,
])

{{--
    Kotak unggah foto dengan pratinjau dan editor crop.
    - model: nama properti Livewire (wire:model) sekaligus kunci crop di $potong (contoh: foto_hero, fotos.0).
    - pratinjau: URL foto sementara/tersimpan, atau null.
    - rasio: kelas aspect kotak pratinjau; rasioPotong: rasio awal editor crop (angka, null = rasio asli).
    - input: false untuk pratinjau tanpa pemilih file (mis. per foto pada upload banyak).
    - hapus: aksi Livewire untuk mengosongkan foto tersimpan (contoh: "hapusFoto('logo')"), null = tanpa tombol hapus.
    Crop diterapkan server saat form disimpan (lihat App\Concerns\KompresFoto).
--}}
<div wire:key="unggah-{{ $model }}-{{ md5((string) $pratinjau) }}"
     x-data="pemotongFoto({ kunci: @js($model), src: @js($pratinjau), rasio: @js($rasioPotong) })"
     @resize.window.debounce.150ms="mengedit && aturBingkai()"
     :class="mengedit && 'col-span-full'">

    @if ($label || $pratinjau)
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            @if ($label)
                <p class="text-sm font-medium text-zinc-800">{{ $label }}</p>
            @endif
            @if ($hapus)
                <button type="button" x-show="! mengedit" wire:click="{{ $hapus }}" wire:confirm="Hapus foto ini? Foto yang dihapus tidak bisa dikembalikan."
                        class="ms-auto inline-flex items-center gap-1 rounded-full bg-red-600/10 px-3 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-600 hover:text-white">
                    <flux:icon name="trash" variant="micro" class="size-3.5" />
                    Hapus foto
                </button>
            @endif
            @if ($pratinjau)
                <button type="button" x-show="! mengedit" @click="buka()"
                        class="{{ $hapus ? '' : 'ms-auto' }} inline-flex items-center gap-1 rounded-full bg-marun-700/10 px-3 py-1 text-xs font-semibold text-marun-700 transition hover:bg-marun-700 hover:text-white">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25 1.5 16.5m6.348-8.25A3 3 0 1 0 3.75 4.5a3 3 0 0 0 4.098 3.75Zm0 0L12 13.5m0 0 2.652 3.375m-2.652-3.375 2.652-3.375M12 13.5l-4.152 3.375m0 0A3 3 0 1 0 3.75 21a3 3 0 0 0 4.098-4.125Zm6.804-.125L22.5 7.5m-7.848 9.375A3 3 0 1 0 18.75 21a3 3 0 0 0-4.098-4.125Z" /></svg>
                    Crop
                </button>
            @endif
        </div>
    @endif

    {{-- Kotak pratinjau / pemilih file --}}
    <div x-show="! mengedit">
        @if ($input)
            <label class="group relative block cursor-pointer overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 transition hover:border-marun-600 {{ $rasio }}">
                @if ($pratinjau)
                    <img src="{{ $pratinjau }}" :src="hasil || src" alt="" class="size-full object-cover">
                    <span class="absolute inset-0 flex items-center justify-center bg-coklat-900/0 text-sm font-semibold text-white opacity-0 transition group-hover:bg-coklat-900/50 group-hover:opacity-100">Ganti foto</span>
                    <span x-show="hasil" x-cloak class="absolute top-2 left-2 rounded-full bg-marun-700 px-2 py-0.5 text-[0.65rem] font-semibold text-white shadow">Sudah di-crop</span>
                @else
                    <span class="flex size-full flex-col items-center justify-center gap-2 p-4 text-center text-zinc-500">
                        <flux:icon name="photo" class="size-8 text-zinc-400" />
                        <span class="text-sm">Klik untuk memilih foto</span>
                    </span>
                @endif

                <span wire:loading.flex wire:target="{{ $model }}" class="absolute inset-0 items-center justify-center bg-white/80 text-sm font-medium text-marun-700">Mengunggah...</span>

                <input type="file" wire:model="{{ $model }}" accept="image/*" class="sr-only">
            </label>
        @elseif ($pratinjau)
            <div class="relative overflow-hidden rounded-xl {{ $rasio }}">
                <img src="{{ $pratinjau }}" :src="hasil || src" alt="" class="size-full object-cover">
                <span x-show="hasil" x-cloak class="absolute top-1 left-1 rounded-full bg-marun-700 px-1.5 py-0.5 text-[0.6rem] font-semibold text-white">Crop</span>
            </div>
        @endif
    </div>

    {{-- Editor crop --}}
    <div x-show="mengedit" x-cloak class="rounded-xl bg-white p-4 ring-1 ring-zinc-200">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-zinc-500">Rasio:</span>
            <template x-for="pilihan in pilihanRasio" :key="pilihan.label">
                <button type="button" @click="pilihRasio(pilihan.nilai)" x-text="pilihan.label"
                        :class="rasio === pilihan.nilai ? 'bg-marun-700 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                        class="rounded-full px-3 py-1 text-xs font-semibold transition"></button>
            </template>
        </div>

        <div x-ref="wadah" class="mt-4 w-full">
            <p x-show="memuat" class="py-10 text-center text-sm text-zinc-500">Memuat foto...</p>

            <div x-show="! memuat"
                 class="relative mx-auto cursor-move touch-none overflow-hidden rounded-lg bg-zinc-900 select-none"
                 :style="`width:${bingkaiW}px;height:${bingkaiH}px`"
                 @pointerdown.prevent="mulaiSeret($event)"
                 @pointermove="gerakSeret($event)"
                 @pointerup="selesaiSeret()"
                 @pointercancel="selesaiSeret()"
                 @wheel.prevent="gulir($event)">
                <img :src="src" alt="" draggable="false" class="pointer-events-none absolute top-0 left-0 max-w-none" :style="gayaGambar()">

                {{-- Garis bantu sepertiga --}}
                <div class="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3">
                    @for ($i = 0; $i < 9; $i++)
                        <div class="border border-white/25"></div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM13.5 10.5h-6" /></svg>
            <input type="range" min="1" max="4" step="0.01" :value="zoom" @input="ubahZoom(parseFloat($event.target.value))"
                   class="h-1.5 flex-1 cursor-pointer appearance-none rounded-full bg-zinc-200 accent-marun-700" aria-label="Zoom">
            <svg class="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" /></svg>
        </div>
        <p class="mt-2 text-xs text-zinc-500">Geser foto untuk mengatur posisi, gunakan slider atau scroll untuk zoom.</p>

        <div class="mt-4 flex justify-end gap-2">
            <button type="button" @click="batal()" class="rounded-full px-4 py-2 text-sm font-medium text-zinc-700 ring-1 ring-zinc-300 hover:bg-zinc-50">Batal</button>
            <button type="button" @click="terapkan()" class="rounded-full bg-marun-700 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-marun-600">Terapkan</button>
        </div>
    </div>

    @if ($keterangan)
        <p x-show="! mengedit" class="mt-1.5 text-xs text-zinc-500">{{ $keterangan }}</p>
    @endif
    <flux:error :name="$model" />
</div>

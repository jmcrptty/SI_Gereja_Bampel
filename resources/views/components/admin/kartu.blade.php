@props(['judul', 'deskripsi' => null, 'icon' => null])

{{-- Kartu bagian form di dashboard admin. --}}
<section {{ $attributes->class('overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200') }}>
    <header class="flex items-start gap-4 border-b border-zinc-100 bg-gradient-to-r from-krem-50 to-white px-6 py-5">
        @if ($icon)
            <div class="rounded-xl bg-marun-700/10 p-2.5 text-marun-700">
                <flux:icon :name="$icon" class="size-5" />
            </div>
        @endif
        <div>
            <h2 class="font-serif text-xl font-semibold text-zinc-900">{{ $judul }}</h2>
            @if ($deskripsi)
                <p class="mt-0.5 text-sm text-zinc-500">{{ $deskripsi }}</p>
            @endif
        </div>
    </header>

    <div class="space-y-6 p-6">
        {{ $slot }}
    </div>
</section>

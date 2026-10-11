@props([
    'sidebar' => false,
])

{{-- Brand dashboard: logo paroki yang sama dengan navbar website + nama paroki. --}}
@php($setting = \App\Models\Setting::current())

@if ($sidebar)
    {{-- Brand sidebar dibuat sendiri (bukan flux:sidebar.brand) agar nama paroki yang panjang tampil dua baris, tidak terpotong. --}}
    <a {{ $attributes->class('flex min-w-0 items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-white/5') }}>
        <x-logo-paroki class="size-11" cincin />
        <span class="min-w-0 leading-tight">
            <span class="block text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-krem-300">Dashboard</span>
            <span class="line-clamp-2 font-serif text-[0.95rem] font-semibold text-white">{{ $setting->nama_gereja }}</span>
        </span>
    </a>
@else
    <flux:brand :name="$setting->nama_gereja" {{ $attributes }}>
        <x-slot name="logo" class="size-9 !rounded-full">
            <x-logo-paroki class="size-9" />
        </x-slot>
    </flux:brand>
@endif

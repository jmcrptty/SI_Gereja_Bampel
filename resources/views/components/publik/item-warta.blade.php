@props(['warta'])

<a href="{{ $warta->pdf_url }}" target="_blank" rel="noopener"
   class="flex items-start gap-4 rounded-xl border border-krem-200 bg-white p-4 transition hover:shadow-md">
    <div class="shrink-0 rounded-lg bg-marun-700/10 p-3 text-marun-700">
        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
    </div>
    <div class="min-w-0">
        <p class="text-sm text-coklat-500">{{ $warta->tanggal->translatedFormat('j F Y') }}</p>
        <h3 class="font-semibold text-coklat-900">{{ $warta->judul }}</h3>
        @if ($warta->ringkasan)
            <p class="mt-1 line-clamp-2 text-sm text-coklat-700">{{ $warta->ringkasan }}</p>
        @endif
        <p class="mt-2 text-sm font-medium text-marun-700">Buka PDF &rarr;</p>
    </div>
</a>

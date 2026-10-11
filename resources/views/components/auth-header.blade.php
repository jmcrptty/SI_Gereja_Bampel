@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="font-serif text-3xl font-semibold text-coklat-900">{{ $title }}</h1>
    <p class="mt-2 text-sm leading-relaxed text-coklat-500">{{ $description }}</p>
</div>

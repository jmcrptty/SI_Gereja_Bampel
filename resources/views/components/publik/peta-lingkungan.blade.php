@props(['daftar'])

{{--
    Peta arsiran lingkungan (Leaflet). Dipisah jadi komponen agar library peta hanya dimuat
    bila komponen ini di-render (ada lingkungan yang sudah diberi arsiran).
    - daftar: lihat petaLingkungan() di halaman publik Lingkungan.
    - Kartu lingkungan menyorot arsirannya dengan $dispatch('sorot-lingkungan', { id }).
    - Klik arsiran di peta mengirim event "lingkungan-dipilih" ke halaman agar kartunya ikut disorot.
--}}
<div class="mb-12 scroll-mt-28 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-krem-300/70" data-peta-wadah>
    <div class="p-5 sm:p-6">
        <h2 class="font-serif text-2xl font-semibold text-coklat-900">Peta Lingkungan</h2>
        <p class="mt-1 text-sm text-coklat-500">Ketuk arsiran atau kartu lingkungan untuk menyorotnya. Ganti tampilan peta (jalan, satelit, topografi) lewat tombol lapisan di kanan atas peta.</p>
    </div>
    <div wire:ignore class="isolate h-80 bg-krem-100 sm:h-[30rem]" data-peta></div>
</div>

@assets
    @vite('resources/js/peta.js')
@endassets

@script
<script>
    const wadah = $wire.$el.querySelector('[data-peta-wadah]');
    const peta = window.PetaParoki.publik(wadah.querySelector('[data-peta]'), {
        pusat: @js(\App\Models\Lingkungan::PUSAT_PETA),
        daftar: @js($daftar),
        pilih: (id) => $wire.$el.dispatchEvent(new CustomEvent('lingkungan-dipilih', { detail: { id } })),
    });

    $wire.$el.addEventListener('sorot-lingkungan', (e) => {
        wadah.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        peta.sorot(e.detail.id);
    });
</script>
@endscript

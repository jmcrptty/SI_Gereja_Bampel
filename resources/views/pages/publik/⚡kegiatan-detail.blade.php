<?php

use App\Models\Kegiatan;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::publik')] class extends Component {
    public Kegiatan $kegiatan;

    public function mount(Kegiatan $kegiatan): void
    {
        // Kegiatan nonaktif tidak pernah tampil di publik.
        abort_unless($kegiatan->aktif, 404);

        $this->kegiatan = $kegiatan;
    }

    #[Computed]
    public function kegiatanLain(): Collection
    {
        return Kegiatan::aktif()->akanDatang()->whereKeyNot($this->kegiatan->id)->limit(3)->get();
    }

    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }

    public function render()
    {
        // Pratinjau link (Open Graph) memakai foto & deskripsi kegiatan ini.
        return $this->view()
            ->layout('layouts::publik', [
                'ogGambar' => $this->kegiatan->foto_url,
                'ogDeskripsi' => $this->kegiatan->deskripsi,
            ])
            ->title($this->kegiatan->judul);
    }
}; ?>

<div>
    @php($sudahLewat = $kegiatan->tanggal->lt(today()))

    <x-publik.hero-halaman :eyebrow="$sudahLewat ? 'Sudah Berlangsung' : 'Akan Datang'" :judul="$kegiatan->judul"
        :breadcrumb="['url' => route('kegiatan'), 'label' => 'Kegiatan']"
        :gambar="$kegiatan->foto_url ?? asset('images/hero-gereja.svg')"
        :subjudul="$kegiatan->tanggal->translatedFormat('l, j F Y').($kegiatan->jam_format ? ' · Pukul '.$kegiatan->jam_format.' WIT' : '').' · '.$kegiatan->tempat" />

    <section class="bg-gradient-to-b from-krem-100 to-krem-50 py-14 lg:py-20">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-14">
            {{-- Deskripsi --}}
            <article class="lg:col-span-8">
                @if ($kegiatan->foto_url)
                    <img src="{{ $kegiatan->foto_url }}" alt="{{ $kegiatan->judul }}" class="mb-8 w-full rounded-3xl object-cover shadow-xl" width="1200" height="675">
                @endif

                <div class="space-y-5 text-lg leading-relaxed text-coklat-700">
                    @foreach (preg_split('/\R\s*\R/', trim($kegiatan->deskripsi)) as $isi)
                        <p @class(['whitespace-pre-line', 'first-letter:float-left first-letter:mt-1 first-letter:mr-3 first-letter:font-serif first-letter:text-6xl first-letter:leading-none first-letter:font-bold first-letter:text-marun-700' => $loop->first])>{{ $isi }}</p>
                    @endforeach
                </div>

                <a href="{{ route('kegiatan') }}" wire:navigate class="mt-10 inline-block text-sm font-semibold text-marun-700 hover:underline">&larr; Semua kegiatan</a>
            </article>

            {{-- Kartu info --}}
            <aside class="lg:col-span-4">
                <div class="space-y-6 lg:sticky lg:top-24">
                    <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 shadow-coklat-900/10 ring-krem-200">
                        <div class="flex items-center gap-4 bg-gradient-to-br from-marun-600 to-marun-700 p-6 text-krem-50">
                            <div class="text-center">
                                <p class="font-serif text-5xl leading-none font-bold">{{ $kegiatan->tanggal->format('j') }}</p>
                                <p class="text-xs font-semibold uppercase tracking-wider">{{ $kegiatan->tanggal->translatedFormat('M Y') }}</p>
                            </div>
                            <div class="border-l border-white/30 pl-4">
                                <p class="font-semibold">{{ $kegiatan->tanggal->translatedFormat('l') }}</p>
                                <p class="text-sm text-krem-200">{{ $sudahLewat ? 'Sudah berlangsung' : 'Akan datang' }}</p>
                            </div>
                        </div>
                        <dl class="divide-y divide-krem-200 p-6">
                            @if ($kegiatan->jam_format)
                                <div class="pb-4">
                                    <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Jam</dt>
                                    <dd class="mt-1 text-coklat-900">Pukul {{ $kegiatan->jam_format }} WIT</dd>
                                </div>
                            @endif
                            <div @class(['py-4' => $kegiatan->jam_format, 'pb-4' => ! $kegiatan->jam_format])>
                                <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Tempat</dt>
                                <dd class="mt-1 text-coklat-900">{{ $kegiatan->tempat }}</dd>
                            </div>
                            <div class="pt-4">
                                <dt class="text-xs font-semibold uppercase tracking-widest text-marun-700">Informasi</dt>
                                <dd class="mt-1 text-coklat-700">Hubungi sekretariat paroki untuk pertanyaan.</dd>
                                @if ($this->setting->whatsapp_url)
                                    <a href="{{ $this->setting->whatsapp_url }}" target="_blank" rel="noopener" class="mt-3 inline-block rounded-full bg-marun-700 px-5 py-2 text-sm font-semibold text-white hover:bg-marun-600">Tanya via WhatsApp</a>
                                @endif
                            </div>
                        </dl>
                    </div>

                    @if ($this->kegiatanLain->isNotEmpty())
                        <div class="rounded-3xl bg-white/60 p-6 ring-1 ring-krem-300/70">
                            <h2 class="font-serif text-xl font-semibold text-coklat-900">Kegiatan lainnya</h2>
                            <ul class="mt-4 space-y-4">
                                @foreach ($this->kegiatanLain as $lain)
                                    <li wire:key="lain-{{ $lain->id }}">
                                        <a href="{{ route('kegiatan.show', $lain) }}" wire:navigate class="group flex items-center gap-3">
                                            <span class="flex size-11 shrink-0 flex-col items-center justify-center rounded-xl bg-krem-100 text-marun-700">
                                                <span class="text-sm leading-none font-bold">{{ $lain->tanggal->format('j') }}</span>
                                                <span class="text-[0.6rem] font-semibold uppercase">{{ $lain->tanggal->translatedFormat('M') }}</span>
                                            </span>
                                            <span class="font-medium text-coklat-900 group-hover:text-marun-700">{{ $lain->judul }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</div>

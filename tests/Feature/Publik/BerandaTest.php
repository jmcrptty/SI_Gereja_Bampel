<?php

use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Setting;
use App\Models\Warta;
use Illuminate\Support\Carbon;

function misaRutin(array $atribut = []): JadwalMisa
{
    return JadwalMisa::create([...['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '07:00', 'tempat' => 'Gereja'], ...$atribut]);
}

function misaKhusus(array $atribut = []): JadwalMisa
{
    return JadwalMisa::create([...['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Hari Raya', 'tanggal' => today()->addDays(3), 'jam' => '19:00', 'tempat' => 'Gereja'], ...$atribut]);
}

test('hero misa card shows upcoming hari raya within 14 days and links to the anchor', function () {
    misaRutin();
    misaKhusus(['nama_perayaan' => 'Malam Natal']);
    misaKhusus(['nama_perayaan' => 'Masih Lama', 'tanggal' => today()->addDays(20)]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Jadwal Misa Hari Raya')
        ->assertSee('Malam Natal')
        ->assertDontSee('Masih Lama')
        ->assertSee(route('jadwal-misa').'#hari-raya', false);
});

test('hero misa card falls back to active rutin misa', function () {
    misaRutin(['hari' => 3, 'jam' => '06:00']);
    misaRutin(['hari' => 5, 'jam' => '17:30', 'aktif' => false]);
    misaKhusus(['nama_perayaan' => 'Nonaktif', 'aktif' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Jadwal Misa Minggu Ini')
        ->assertSee('06:00')
        ->assertDontSee('17:30')
        ->assertDontSee('Nonaktif');
});

test('hero rutin misa card moves masses that already passed today to the end', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-18 08:00')); // Minggu
    misaRutin(['hari' => 7, 'jam' => '06:30']);
    misaRutin(['hari' => 7, 'jam' => '09:00']);
    misaRutin(['hari' => 1, 'jam' => '06:00']);

    $card = JadwalMisa::untukCardHero();

    expect($card['items']->map(fn (JadwalMisa $m) => "{$m->nama_hari} {$m->jam_format}")->all())
        ->toBe(['Minggu 09:00', 'Senin 06:00', 'Minggu 06:30']);
});

test('hero misa card shows a link when there is no misa', function () {
    $this->get(route('home'))->assertSee('Lihat semua jadwal');
});

test('hero cards can be hidden from settings', function () {
    misaRutin();
    Setting::current()->update(['tampil_card_misa' => false, 'tampil_card_kegiatan' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Jadwal Misa Minggu Ini')
        ->assertDontSee('Lihat semua jadwal')
        ->assertDontSee('Lihat semua kegiatan');
});

test('hero kegiatan card shows the nearest active upcoming kegiatan', function () {
    Kegiatan::create(['judul' => 'Sudah Lewat', 'tanggal' => today()->subDay(), 'tempat' => '-', 'deskripsi' => '-']);
    Kegiatan::create(['judul' => 'Nonaktif', 'tanggal' => today(), 'tempat' => '-', 'deskripsi' => '-', 'aktif' => false]);
    $terdekat = Kegiatan::create(['judul' => 'Rekoleksi OMK', 'tanggal' => today()->addDay(), 'tempat' => '-', 'deskripsi' => '-']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Rekoleksi OMK')
        ->assertSee(route('kegiatan.show', $terdekat))
        ->assertDontSee('Sudah Lewat')
        ->assertDontSee('Nonaktif');
});

test('only published warta appear on the home page', function () {
    Warta::create(['judul' => 'Warta Terbit', 'tanggal' => today(), 'file_pdf' => 'a.pdf', 'status' => Warta::TERBIT]);
    Warta::create(['judul' => 'Warta Draft', 'tanggal' => today(), 'file_pdf' => 'b.pdf', 'status' => Warta::DRAFT]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Warta Terbit')
        ->assertDontSee('Warta Draft');
});

test('footer shows only the social media that are filled in', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Ikuti Kami')
        ->assertDontSee('segera hadir');

    Setting::current()->update(['instagram' => 'https://instagram.com/paroki', 'youtube' => 'https://youtube.com/@paroki']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Ikuti Kami')
        ->assertSee('aria-label="Instagram"', false)
        ->assertSee('https://youtube.com/@paroki')
        ->assertDontSee('aria-label="WhatsApp"', false)
        ->assertDontSee('aria-label="Facebook"', false);
});

test('whatsapp button appears only when a number is set', function () {
    $this->get(route('home'))->assertDontSee('wa.me');

    Setting::current()->update(['whatsapp' => '081234567890']);

    $this->get(route('home'))->assertSee('https://wa.me/6281234567890');
});

<?php

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Lingkungan;
use App\Models\Pengurus;
use App\Models\Setting;
use App\Models\Warta;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('every public page can be opened without login', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['home', 'profil', 'dewan-paroki', 'lingkungan', 'jadwal-misa', 'warta', 'kegiatan', 'galeri']);

test('public pages have open graph tags with an absolute image url', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="'.Setting::current()->nama_gereja.'">', false)
        ->assertSee('<meta property="og:image" content="'.url('images/og-default.png').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

test('kegiatan detail shares its own photo and description', function () {
    Kegiatan::create([
        'judul' => 'Bakti Sosial', 'tanggal' => today(), 'tempat' => 'Aula',
        'deskripsi' => 'Pembagian sembako untuk keluarga yang membutuhkan.', 'foto' => 'kegiatan/baksos.webp',
    ]);

    $this->get('/kegiatan/bakti-sosial')
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Bakti Sosial - ', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/kegiatan/baksos.webp').'">', false)
        ->assertSee('<meta property="og:description" content="Pembagian sembako untuk keluarga yang membutuhkan.">', false);
});

test('unknown pages show the indonesian 404 page', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman Tidak Ditemukan')
        ->assertSee('Kembali ke Beranda');
});

test('the 500 error page renders in indonesian', function () {
    expect(view('errors.500')->render())
        ->toContain('Terjadi Kesalahan')
        ->toContain(config('app.name'));
});

test('profil page shows sejarah, visi, and misi', function () {
    Setting::current()->update(['sejarah' => 'Didirikan tahun 1960.', 'visi' => 'Visi paroki', 'misi' => 'Misi paroki']);

    $this->get(route('profil'))
        ->assertOk()
        ->assertSee('Didirikan tahun 1960.')
        ->assertSee('Visi paroki')
        ->assertSee('Misi paroki');
});

test('profil page shows the pelindung story only when a pelindung is set', function () {
    $this->get(route('profil'))->assertOk()->assertDontSee('Pelindung Paroki');

    Setting::current()->update([
        'pelindung' => 'Santo Yosef',
        'kisah_pelindung' => "Paragraf pertama kisah.\n\nParagraf kedua kisah.",
    ]);

    $this->get(route('profil'))
        ->assertOk()
        ->assertSeeInOrder(['Pelindung Paroki', 'Santo Yosef', 'Paragraf pertama kisah.', 'Paragraf kedua kisah.'])
        ->assertSee('images/pelindung.svg');
});

test('dewan paroki lists pengurus by urutan', function () {
    Pengurus::create(['nama' => 'Bendahara Paroki', 'jabatan' => 'Bendahara', 'periode' => '2024-2027', 'urutan' => 2]);
    Pengurus::create(['nama' => 'Ketua Paroki', 'jabatan' => 'Ketua', 'periode' => '2024-2027', 'urutan' => 1]);

    $this->get(route('dewan-paroki'))->assertSeeInOrder(['Ketua Paroki', 'Bendahara Paroki']);
});

test('lingkungan are grouped by wilayah with the ungrouped ones last', function () {
    Lingkungan::create(['nama' => 'Tanpa Wilayah']);
    Lingkungan::create(['nama' => 'Santo Petrus', 'wilayah' => 'Stasi Bampel', 'cakupan_jalan' => "Jl. Ermasu\nJl. Mandala"]);

    $this->get(route('lingkungan'))
        ->assertOk()
        ->assertSeeInOrder(['Stasi Bampel', 'Santo Petrus', 'Jl. Ermasu', 'Jl. Mandala', 'Lainnya', 'Tanpa Wilayah']);
});

test('lingkungan wilayah count ignores the ungrouped "Lainnya" group', function () {
    Lingkungan::create(['nama' => 'Tanpa Wilayah']);
    Lingkungan::create(['nama' => 'Santo Petrus', 'wilayah' => 'Stasi Bampel']);
    Lingkungan::create(['nama' => 'Santo Paulus', 'wilayah' => 'Stasi Kuprik']);

    expect(Livewire::test('pages::publik.lingkungan')->instance()->jumlahWilayah)->toBe(2);
});

test('jadwal misa hides inactive and past misa', function () {
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Malam Natal', 'tanggal' => today()->addMonth(), 'jam' => '19:00', 'tempat' => 'Gereja']);
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Paskah Lalu', 'tanggal' => today()->subDay(), 'jam' => '07:00', 'tempat' => 'Gereja']);
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '06:30', 'tempat' => 'Gereja']);
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 6, 'jam' => '18:15', 'tempat' => 'Gereja', 'aktif' => false]);

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSee('id="hari-raya"', false)
        ->assertSeeInOrder(['Malam Natal', 'Minggu', '06:30'])
        ->assertDontSee('Paskah Lalu')
        ->assertDontSee('18:15');
});

test('next misa skips masses that already passed today', function () {
    $this->travelTo(now()->next('Sunday')->setTime(8, 0));
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '06:30', 'tempat' => 'Gereja']);
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '09:00', 'tempat' => 'Gereja']);

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSeeInOrder(['Misa berikutnya', 'Hari ini', '09:00', 'Setiap Minggu']);

    $this->travelTo(now()->setTime(10, 0));

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSeeInOrder(['Misa berikutnya', 'Minggu', '06:30', 'Setiap Minggu']);
});

test('next misa shows a hari raya misa when it comes before the next rutin misa', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-24 10:00'));
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '06:30', 'tempat' => 'Gereja']);
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Malam Natal', 'tanggal' => '2026-12-24', 'jam' => '19:00', 'tempat' => 'Gereja']);

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSeeInOrder(['Misa berikutnya', 'Malam Natal', 'Hari ini', '19:00', 'Setiap Minggu']);

    Carbon::setTestNow(Carbon::parse('2026-12-24 20:00'));

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSeeInOrder(['Misa berikutnya', 'Minggu', '06:30', 'Setiap Minggu']);
});

test('next misa shows the date of a hari raya misa further away', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-21 10:00'));
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Natal', 'tanggal' => '2026-12-25', 'jam' => '07:00', 'tempat' => 'Gereja']);

    $this->get(route('jadwal-misa'))
        ->assertOk()
        ->assertSeeInOrder(['Misa berikutnya', 'Natal', 'Jumat, 25 Desember 2026', '07:00']);
});

test('warta page shows only published warta', function () {
    Warta::create(['judul' => 'Warta Terbit', 'tanggal' => today(), 'file_pdf' => 'warta/a.pdf', 'status' => Warta::TERBIT]);
    Warta::create(['judul' => 'Warta Draft', 'tanggal' => today(), 'file_pdf' => 'warta/b.pdf', 'status' => Warta::DRAFT]);

    $this->get(route('warta'))
        ->assertOk()
        ->assertSee('Warta Terbit')
        ->assertSee('/storage/warta/a.pdf')
        ->assertDontSee('Warta Draft');
});

test('kegiatan page separates upcoming and past kegiatan and hides inactive ones', function () {
    Kegiatan::create(['judul' => 'Kegiatan Lalu', 'tanggal' => today()->subWeek(), 'tempat' => '-', 'deskripsi' => '-']);
    Kegiatan::create(['judul' => 'Kegiatan Nanti', 'tanggal' => today()->addWeek(), 'tempat' => '-', 'deskripsi' => '-']);
    Kegiatan::create(['judul' => 'Kegiatan Nonaktif', 'tanggal' => today()->addWeek(), 'tempat' => '-', 'deskripsi' => '-', 'aktif' => false]);

    $this->get(route('kegiatan'))
        ->assertOk()
        ->assertSeeInOrder(['Akan Datang', 'Kegiatan Nanti', 'Kegiatan Sebelumnya', 'Kegiatan Lalu'])
        ->assertDontSee('Kegiatan Nonaktif');
});

test('kegiatan detail is reachable by slug', function () {
    $kegiatan = Kegiatan::create(['judul' => 'Bakti Sosial', 'tanggal' => today(), 'tempat' => 'Aula', 'deskripsi' => 'Pembagian sembako.']);

    $this->get('/kegiatan/bakti-sosial')
        ->assertOk()
        ->assertSee('Bakti Sosial')
        ->assertSee('Pembagian sembako.');
});

test('inactive kegiatan detail returns 404', function () {
    $kegiatan = Kegiatan::create(['judul' => 'Rahasia', 'tanggal' => today(), 'tempat' => '-', 'deskripsi' => '-', 'aktif' => false]);

    $this->get(route('kegiatan.show', $kegiatan))->assertNotFound();
});

test('galeri paginates 12 photos per page', function () {
    foreach (range(1, 13) as $i) {
        Galeri::create(['judul' => "Foto {$i}", 'foto' => "galeri/{$i}.webp", 'tanggal' => today()->subDays($i)]);
    }

    $this->get(route('galeri'))
        ->assertOk()
        ->assertSee('Foto 12')
        ->assertDontSee('Foto 13');

    $this->get(route('galeri', ['page' => 2]))->assertSee('Foto 13');
});

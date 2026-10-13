<?php

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\User;
use App\Models\Warta;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('summary counts only active, upcoming, and published data', function () {
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '07:00', 'tempat' => 'Gereja', 'aktif' => true]);
    JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 6, 'jam' => '18:00', 'tempat' => 'Gereja', 'aktif' => false]);
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Natal', 'tanggal' => today()->addWeek(), 'jam' => '19:00', 'tempat' => 'Gereja', 'aktif' => true]);
    JadwalMisa::create(['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => 'Paskah', 'tanggal' => today()->subDay(), 'jam' => '07:00', 'tempat' => 'Gereja', 'aktif' => true]);

    Kegiatan::create(['judul' => 'Akan datang', 'tanggal' => today()->addDay(), 'tempat' => '-', 'deskripsi' => '-']);
    Kegiatan::create(['judul' => 'Sudah lewat', 'tanggal' => today()->subDay(), 'tempat' => '-', 'deskripsi' => '-']);
    Kegiatan::create(['judul' => 'Nonaktif', 'tanggal' => today()->addDay(), 'tempat' => '-', 'deskripsi' => '-', 'aktif' => false]);

    Warta::create(['judul' => 'Terbit', 'tanggal' => today(), 'file_pdf' => 'a.pdf', 'status' => Warta::TERBIT]);
    Warta::create(['judul' => 'Draft', 'tanggal' => today(), 'file_pdf' => 'b.pdf', 'status' => Warta::DRAFT]);
    Warta::create(['judul' => 'Bulan lalu', 'tanggal' => today()->subMonthNoOverflow()->startOfMonth(), 'file_pdf' => 'c.pdf', 'status' => Warta::TERBIT]);

    Galeri::create(['judul' => 'Foto', 'foto' => 'galeri/a.webp']);

    $ringkasan = collect(Livewire::test('pages::admin.dashboard')->instance()->ringkasan)->pluck('nilai', 'label');

    expect($ringkasan->all())->toBe([
        'Misa aktif' => 2,
        'Kegiatan akan datang' => 1,
        'Warta terbit bulan ini' => 1,
        'Foto galeri' => 1,
    ]);
});

test('latest changes show the five most recently updated items across tables', function () {
    foreach (range(1, 4) as $i) {
        $this->travel(1)->minutes();
        Galeri::create(['judul' => "Foto {$i}", 'foto' => "galeri/{$i}.webp"]);
    }

    $this->travel(1)->minutes();
    Warta::create(['judul' => 'Warta Terbaru', 'tanggal' => today(), 'file_pdf' => 'a.pdf']);

    $this->travel(1)->minutes();
    Kegiatan::create(['judul' => 'Kegiatan Terbaru', 'tanggal' => today(), 'tempat' => '-', 'deskripsi' => '-']);

    $terakhir = Livewire::test('pages::admin.dashboard')->instance()->terakhirDiubah;

    expect($terakhir->pluck('judul')->all())->toBe(['Kegiatan Terbaru', 'Warta Terbaru', 'Foto 4', 'Foto 3', 'Foto 2']);
});

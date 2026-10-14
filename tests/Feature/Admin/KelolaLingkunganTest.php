<?php

use App\Models\Lingkungan;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.lingkungan'))->assertOk()->assertSee('assets/peta-', false);
});

test('admin can create a lingkungan', function () {
    Livewire::test('pages::admin.lingkungan')
        ->call('tambah')
        ->set('nama', 'Santo Petrus')
        ->set('jumlah_kk', '25')
        ->set('cakupan_jalan', "Jl. Raya Mandala\nJl. Ermasu")
        ->call('simpan')
        ->assertHasNoErrors();

    expect(Lingkungan::sole())
        ->jumlah_kk->toBe(25)
        ->ketua->toBeNull()
        ->daftar_jalan->toBe(['Jl. Raya Mandala', 'Jl. Ermasu']);
});

test('nama is required and jumlah kk must be a number', function () {
    Livewire::test('pages::admin.lingkungan')
        ->call('tambah')
        ->set('jumlah_kk', 'banyak')
        ->call('simpan')
        ->assertHasErrors(['nama' => 'required', 'jumlah_kk' => 'integer']);
});

test('admin can save the map boundary of a lingkungan', function () {
    $titik = [[-8.49, 140.40], [-8.49, 140.41], [-8.50, 140.41]];

    Livewire::test('pages::admin.lingkungan')
        ->call('tambah')
        ->assertDispatched('muat-batas', batas: [], lainnya: [])
        ->set('nama', 'Santo Petrus')
        ->set('batas_wilayah', $titik)
        ->call('simpan')
        ->assertHasNoErrors();

    expect(Lingkungan::sole()->batas_wilayah)->toBe($titik);
});

test('editing sends the boundary and the other lingkungan boundaries to the map', function () {
    $lain = Lingkungan::create(['nama' => 'Santo Paulus', 'batas_wilayah' => [[-8.4, 140.3], [-8.4, 140.31], [-8.41, 140.31]]]);
    $lingkungan = Lingkungan::create(['nama' => 'Santo Petrus', 'batas_wilayah' => [[-8.49, 140.40], [-8.49, 140.41], [-8.50, 140.41]]]);

    Livewire::test('pages::admin.lingkungan')
        ->call('edit', $lingkungan->id)
        ->assertSet('batas_wilayah', $lingkungan->batas_wilayah)
        ->assertDispatched('muat-batas', batas: $lingkungan->batas_wilayah, lainnya: [['nama' => 'Santo Paulus', 'titik' => $lain->batas_wilayah]]);
});

test('map boundary needs at least 3 valid points, and an empty one is saved as null', function () {
    Livewire::test('pages::admin.lingkungan')
        ->call('tambah')
        ->set('nama', 'Santo Petrus')
        ->set('batas_wilayah', [[-8.49, 140.40], [-8.49, 140.41]])
        ->call('simpan')
        ->assertHasErrors('batas_wilayah')
        ->set('batas_wilayah', [[-8.49, 140.40], [-8.49, 140.41], [120, 140.41]])
        ->call('simpan')
        ->assertHasErrors('batas_wilayah.2.0')
        ->set('batas_wilayah', [])
        ->call('simpan')
        ->assertHasNoErrors();

    expect(Lingkungan::sole()->batas_wilayah)->toBeNull();
});

test('admin can delete a lingkungan', function () {
    $lingkungan = Lingkungan::create(['nama' => 'Santo Petrus']);

    Livewire::test('pages::admin.lingkungan')
        ->call('konfirmasiHapus', $lingkungan->id)
        ->call('hapus');

    expect(Lingkungan::count())->toBe(0);
});

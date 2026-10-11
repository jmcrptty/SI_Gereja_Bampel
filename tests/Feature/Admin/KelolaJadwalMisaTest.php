<?php

use App\Models\JadwalMisa;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests are redirected to the login page', function () {
    auth()->logout();

    $this->get(route('admin.jadwal-misa'))->assertRedirect(route('login'));
});

test('admin can open the page', function () {
    $this->get(route('admin.jadwal-misa'))->assertOk();
});

test('rutin misa requires hari', function () {
    Livewire::test('pages::admin.jadwal-misa')
        ->call('tambah')
        ->set('jenis', JadwalMisa::RUTIN)
        ->set('jam', '07:00')
        ->set('tempat', 'Gereja Paroki')
        ->call('simpan')
        ->assertHasErrors(['hari' => 'required_if']);
});

test('khusus misa requires nama perayaan and tanggal', function () {
    Livewire::test('pages::admin.jadwal-misa')
        ->call('tambah')
        ->set('jenis', JadwalMisa::KHUSUS)
        ->set('jam', '19:00')
        ->set('tempat', 'Gereja Paroki')
        ->call('simpan')
        ->assertHasErrors(['nama_perayaan' => 'required_if', 'tanggal' => 'required_if']);
});

test('admin can create a rutin misa', function () {
    Livewire::test('pages::admin.jadwal-misa')
        ->call('tambah')
        ->set('hari', '7')
        ->set('jam', '07:00')
        ->set('tempat', 'Gereja Paroki')
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSet('tampilForm', false);

    $misa = JadwalMisa::sole();

    expect($misa->jenis)->toBe(JadwalMisa::RUTIN)
        ->and($misa->hari)->toBe(7)
        ->and($misa->aktif)->toBeTrue()
        ->and($misa->created_by)->toBe(auth()->id());
});

test('admin can edit a misa', function () {
    $misa = JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '07:00', 'tempat' => 'Lama']);

    Livewire::test('pages::admin.jadwal-misa')
        ->call('edit', $misa->id)
        ->assertSet('jam', '07:00')
        ->set('tempat', 'Baru')
        ->call('simpan')
        ->assertHasNoErrors();

    expect($misa->fresh()->tempat)->toBe('Baru');
});

test('toggle aktif flips the status', function () {
    $misa = JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '07:00', 'tempat' => 'Gereja', 'aktif' => true]);

    Livewire::test('pages::admin.jadwal-misa')->call('toggleAktif', $misa->id);

    expect($misa->fresh()->aktif)->toBeFalse();
});

test('admin can delete a misa after confirming', function () {
    $misa = JadwalMisa::create(['jenis' => JadwalMisa::RUTIN, 'hari' => 7, 'jam' => '07:00', 'tempat' => 'Gereja']);

    Livewire::test('pages::admin.jadwal-misa')
        ->call('konfirmasiHapus', $misa->id)
        ->assertSet('tampilHapus', true)
        ->call('hapus');

    expect(JadwalMisa::count())->toBe(0);
});

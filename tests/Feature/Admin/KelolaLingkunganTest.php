<?php

use App\Models\Lingkungan;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.lingkungan'))->assertOk();
});

test('admin can create a lingkungan', function () {
    Livewire::test('pages::admin.lingkungan')
        ->call('tambah')
        ->set('nama', 'Santo Petrus')
        ->set('wilayah', 'Stasi Bampel')
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

test('admin can delete a lingkungan', function () {
    $lingkungan = Lingkungan::create(['nama' => 'Santo Petrus']);

    Livewire::test('pages::admin.lingkungan')
        ->call('konfirmasiHapus', $lingkungan->id)
        ->call('hapus');

    expect(Lingkungan::count())->toBe(0);
});

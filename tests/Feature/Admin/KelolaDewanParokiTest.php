<?php

use App\Models\Pengurus;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.dewan-paroki'))->assertOk();
});

test('new pengurus form continues periode and urutan from the last one', function () {
    Pengurus::create(['nama' => 'Yohanes', 'jabatan' => 'Ketua', 'periode' => '2024-2027', 'urutan' => 3]);

    Livewire::test('pages::admin.dewan-paroki')
        ->call('tambah')
        ->assertSet('periode', '2024-2027')
        ->assertSet('urutan', '4');
});

test('admin can create a pengurus', function () {
    Livewire::test('pages::admin.dewan-paroki')
        ->call('tambah')
        ->set('nama', 'Maria')
        ->set('jabatan', 'Sekretaris')
        ->set('periode', '2024-2027')
        ->call('simpan')
        ->assertHasNoErrors();

    expect(Pengurus::sole())
        ->nama->toBe('Maria')
        ->bidang->toBeNull()
        ->urutan->toBe(1);
});

test('nama, jabatan, periode, and urutan are required', function () {
    Livewire::test('pages::admin.dewan-paroki')
        ->call('tambah')
        ->set('urutan', '')
        ->call('simpan')
        ->assertHasErrors(['nama', 'jabatan', 'periode', 'urutan']);
});

test('admin can remove the pengurus photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pengurus/lama.webp', 'isi');
    $pengurus = Pengurus::create(['nama' => 'Yohanes', 'jabatan' => 'Ketua', 'periode' => '2024-2027', 'foto' => 'pengurus/lama.webp']);

    Livewire::test('pages::admin.dewan-paroki')
        ->call('edit', $pengurus->id)
        ->call('hapusFoto');

    expect($pengurus->fresh()->foto)->toBeNull();
    Storage::disk('public')->assertMissing('pengurus/lama.webp');
    $this->get(route('dewan-paroki'))->assertOk()->assertDontSee('pengurus/lama.webp');
});

test('admin can delete a pengurus', function () {
    $pengurus = Pengurus::create(['nama' => 'Yohanes', 'jabatan' => 'Ketua', 'periode' => '2024-2027']);

    Livewire::test('pages::admin.dewan-paroki')
        ->call('konfirmasiHapus', $pengurus->id)
        ->call('hapus');

    expect(Pengurus::count())->toBe(0);
});

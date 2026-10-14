<?php

use App\Models\Pastor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.pastor-paroki'))->assertOk();
});

test('guest cannot open the page', function () {
    auth()->logout();

    $this->get(route('admin.pastor-paroki'))->assertRedirect(route('login'));
});

test('first pastor defaults to pastor paroki, next to pastor rekan', function () {
    Livewire::test('pages::admin.pastor-paroki')
        ->call('tambah')
        ->assertSet('jabatan', 'Pastor Paroki')
        ->assertSet('urutan', '1');

    Pastor::create(['nama' => 'P. Yohanes', 'jabatan' => 'Pastor Paroki', 'urutan' => 1]);

    Livewire::test('pages::admin.pastor-paroki')
        ->call('tambah')
        ->assertSet('jabatan', 'Pastor Rekan')
        ->assertSet('urutan', '2');
});

test('admin can create a pastor with photo and story', function () {
    Storage::fake('public');

    Livewire::test('pages::admin.pastor-paroki')
        ->call('tambah')
        ->set('nama', 'P. Yohanes')
        ->set('tarekat', 'MSC')
        ->set('tanggal_tahbisan', '2010-08-15')
        ->set('mulai_bertugas', '2023')
        ->set('cerita', "Paragraf satu.\n\nParagraf dua.")
        ->set('foto', UploadedFile::fake()->image('pastor.jpg', 800, 1000))
        ->call('simpan')
        ->assertHasNoErrors();

    $pastor = Pastor::sole();

    expect($pastor)
        ->nama->toBe('P. Yohanes')
        ->jabatan->toBe('Pastor Paroki')
        ->asal->toBeNull()
        ->tanggal_lahir->toBeNull()
        ->mulai_bertugas->toBe(2023)
        ->paragraf_cerita->toBe(['Paragraf satu.', 'Paragraf dua.']);

    Storage::disk('public')->assertExists([$pastor->foto, Pastor::pathKecil($pastor->foto)]);
});

test('nama, jabatan, and urutan are required', function () {
    Livewire::test('pages::admin.pastor-paroki')
        ->call('tambah')
        ->set('jabatan', '')
        ->set('urutan', '')
        ->call('simpan')
        ->assertHasErrors(['nama', 'jabatan', 'urutan']);
});

test('tanggal tahbisan cannot be in the future', function () {
    Livewire::test('pages::admin.pastor-paroki')
        ->call('tambah')
        ->set('nama', 'P. Yohanes')
        ->set('tanggal_tahbisan', now()->addDay()->format('Y-m-d'))
        ->call('simpan')
        ->assertHasErrors(['tanggal_tahbisan']);
});

test('admin can remove the pastor photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pastor/lama.webp', 'isi');
    $pastor = Pastor::create(['nama' => 'P. Yohanes', 'jabatan' => 'Pastor Paroki', 'foto' => 'pastor/lama.webp']);

    Livewire::test('pages::admin.pastor-paroki')
        ->call('edit', $pastor->id)
        ->call('hapusFoto');

    expect($pastor->fresh()->foto)->toBeNull();
    Storage::disk('public')->assertMissing('pastor/lama.webp');
});

test('admin can delete a pastor', function () {
    $pastor = Pastor::create(['nama' => 'P. Yohanes', 'jabatan' => 'Pastor Paroki']);

    Livewire::test('pages::admin.pastor-paroki')
        ->call('konfirmasiHapus', $pastor->id)
        ->call('hapus');

    expect(Pastor::count())->toBe(0);
});

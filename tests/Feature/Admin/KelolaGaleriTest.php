<?php

use App\Models\Galeri;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.galeri'))->assertOk();
});

test('uploading several photos creates one row per photo', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->set('fotos', [
            UploadedFile::fake()->image('1.jpg'),
            UploadedFile::fake()->image('2.jpg'),
            UploadedFile::fake()->image('3.jpg'),
        ])
        ->call('simpan')
        ->assertHasNoErrors();

    $galeri = Galeri::all();

    expect($galeri)->toHaveCount(3)
        ->and($galeri->pluck('judul')->unique()->all())->toBe(['Misa Paskah']);
    $galeri->each(fn (Galeri $g) => Storage::disk('public')->assertExists($g->foto));
});

test('uploading a photo also stores a small version that is removed on delete', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->set('fotos', [UploadedFile::fake()->image('1.jpg', 2000, 1500)])
        ->call('simpan')
        ->assertHasNoErrors();

    $galeri = Galeri::sole();
    $kecil = Galeri::pathKecil($galeri->foto);

    expect($kecil)->toEndWith('_kecil.webp')
        ->and(getimagesizefromstring(Storage::disk('public')->get($kecil))[0])->toBe(600)
        ->and($galeri->foto_kecil_url)->toBe(Storage::disk('public')->url($kecil));
    Storage::disk('public')->assertExists($galeri->foto);

    Livewire::test('pages::admin.galeri')
        ->call('konfirmasiHapus', $galeri->id)
        ->call('hapus');

    Storage::disk('public')->assertMissing([$galeri->foto, $kecil]);
});

test('photos without a small version fall back to the full photo', function () {
    Storage::disk('public')->put('galeri/lama.webp', 'isi');
    $galeri = Galeri::create(['judul' => 'Lama', 'foto' => 'galeri/lama.webp']);

    expect($galeri->foto_kecil_url)->toBe($galeri->foto_url);
});

test('the thumbnail command creates small versions for existing photos', function () {
    Storage::disk('public')->put('galeri/lama.webp', UploadedFile::fake()->image('lama.jpg', 1200, 900)->getContent());
    Galeri::create(['judul' => 'Lama', 'foto' => 'galeri/lama.webp']);

    $this->artisan('foto:buat-thumbnail')->assertSuccessful();

    Storage::disk('public')->assertExists('galeri/lama_kecil.webp');
});

test('at least one photo is required when adding', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->call('simpan')
        ->assertHasErrors(['fotos' => 'required']);
});

test('more than 10 photos in one upload are rejected', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->set('fotos', array_map(fn ($i) => UploadedFile::fake()->image("{$i}.jpg", 10, 10), range(1, 11)))
        ->call('simpan')
        ->assertHasErrors(['fotos' => 'max']);

    expect(Galeri::count())->toBe(0);
});

test('non-image files are rejected', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->set('fotos', [UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])
        ->call('simpan')
        ->assertHasErrors(['fotos.0' => 'image']);
});

test('editing replaces the photo and deletes the old file', function () {
    Storage::disk('public')->put('galeri/lama.webp', 'isi');
    $galeri = Galeri::create(['judul' => 'Lama', 'foto' => 'galeri/lama.webp']);

    Livewire::test('pages::admin.galeri')
        ->call('edit', $galeri->id)
        ->set('judul', 'Baru')
        ->set('foto', UploadedFile::fake()->image('baru.jpg'))
        ->call('simpan')
        ->assertHasNoErrors();

    $galeri->refresh();

    expect($galeri->judul)->toBe('Baru')
        ->and($galeri->foto)->not->toBe('galeri/lama.webp');
    Storage::disk('public')->assertMissing('galeri/lama.webp');
});

test('replacing a photo also deletes the old small version', function () {
    Storage::disk('public')->put('galeri/lama.webp', 'isi');
    Storage::disk('public')->put('galeri/lama_kecil.webp', 'isi');
    $galeri = Galeri::create(['judul' => 'Lama', 'foto' => 'galeri/lama.webp']);

    $galeri->update(['foto' => 'galeri/baru.webp']);

    Storage::disk('public')->assertMissing(['galeri/lama.webp', 'galeri/lama_kecil.webp']);
});

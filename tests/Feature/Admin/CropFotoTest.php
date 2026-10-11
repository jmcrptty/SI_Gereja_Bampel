<?php

use App\Models\Galeri;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

/** Ukuran [lebar, tinggi] gambar yang tersimpan di disk public. */
function ukuranFoto(string $path): array
{
    return array_slice(getimagesizefromstring(Storage::disk('public')->get($path)), 0, 2);
}

test('new upload is cropped to the selected area', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('foto_hero', UploadedFile::fake()->image('hero.jpg', 1200, 800))
        ->set('potong.foto_hero', ['x' => 100, 'y' => 50, 'w' => 600, 'h' => 400])
        ->call('simpan')
        ->assertHasNoErrors();

    expect(ukuranFoto(Setting::current()->foto_hero))->toBe([600, 400]);
});

test('an already saved photo can be cropped without uploading again', function () {
    $komponen = Livewire::test('pages::admin.profil-gereja')
        ->set('foto_gereja', UploadedFile::fake()->image('gereja.jpg', 1000, 1000))
        ->call('simpan');

    $lama = Setting::current()->foto_gereja;

    $komponen->set('potong.foto_gereja', ['x' => 0, 'y' => 0, 'w' => 800, 'h' => 1000])
        ->call('simpan')
        ->assertHasNoErrors();

    $baru = Setting::current()->foto_gereja;

    expect($baru)->not->toBe($lama)
        ->and(ukuranFoto($baru))->toBe([800, 1000]);
    Storage::disk('public')->assertMissing($lama);
});

test('choosing a new file clears the previous crop', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('potong.logo', ['x' => 0, 'y' => 0, 'w' => 10, 'h' => 10])
        ->set('logo', UploadedFile::fake()->image('logo.jpg', 300, 300))
        ->assertSet('potong', [])
        ->call('simpan');

    expect(ukuranFoto(Setting::current()->logo))->toBe([300, 300]);
});

test('crop outside the image is clamped to the image bounds', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('foto_hero', UploadedFile::fake()->image('hero.jpg', 500, 400))
        ->set('potong.foto_hero', ['x' => 300, 'y' => -50, 'w' => 900, 'h' => 900])
        ->call('simpan')
        ->assertHasNoErrors();

    expect(ukuranFoto(Setting::current()->foto_hero))->toBe([200, 400]);
});

test('each photo in a multi upload can be cropped separately', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->set('fotos', [
            UploadedFile::fake()->image('1.jpg', 800, 600),
            UploadedFile::fake()->image('2.jpg', 800, 600),
        ])
        ->set('potong.fotos.1', ['x' => 0, 'y' => 0, 'w' => 600, 'h' => 600])
        ->call('simpan')
        ->assertHasNoErrors();

    $ukuran = Galeri::orderBy('id')->get()->map(fn (Galeri $g) => ukuranFoto($g->foto))->all();

    expect($ukuran)->toBe([[800, 600], [600, 600]]);
});

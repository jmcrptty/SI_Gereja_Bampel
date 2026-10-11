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

test('at least one photo is required when adding', function () {
    Livewire::test('pages::admin.galeri')
        ->call('tambah')
        ->set('judul', 'Misa Paskah')
        ->call('simpan')
        ->assertHasErrors(['fotos' => 'required']);
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

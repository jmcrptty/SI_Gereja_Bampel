<?php

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.kegiatan'))->assertOk();
});

test('admin can create a kegiatan with a photo', function () {
    Livewire::test('pages::admin.kegiatan')
        ->call('tambah')
        ->set('judul', 'Rekoleksi OMK')
        ->set('tanggal', today()->addWeek()->format('Y-m-d'))
        ->set('tempat', 'Aula Paroki')
        ->set('deskripsi', 'Rekoleksi untuk orang muda Katolik.')
        ->set('foto', UploadedFile::fake()->image('omk.jpg'))
        ->call('simpan')
        ->assertHasNoErrors();

    $kegiatan = Kegiatan::sole();

    expect($kegiatan->slug)->toBe('rekoleksi-omk')
        ->and($kegiatan->jam)->toBeNull()
        ->and($kegiatan->foto)->toEndWith('.webp');
    Storage::disk('public')->assertExists($kegiatan->foto);
});

test('judul, tanggal, tempat, and deskripsi are required', function () {
    Livewire::test('pages::admin.kegiatan')
        ->call('tambah')
        ->call('simpan')
        ->assertHasErrors(['judul', 'tanggal', 'tempat', 'deskripsi']);
});

test('toggle aktif flips the status', function () {
    $kegiatan = Kegiatan::create(['judul' => 'Bakti Sosial', 'tanggal' => today(), 'tempat' => 'Gereja', 'deskripsi' => '-']);

    Livewire::test('pages::admin.kegiatan')->call('toggleAktif', $kegiatan->id);

    expect($kegiatan->fresh()->aktif)->toBeFalse();
});

test('admin can delete a kegiatan', function () {
    $kegiatan = Kegiatan::create(['judul' => 'Bakti Sosial', 'tanggal' => today(), 'tempat' => 'Gereja', 'deskripsi' => '-']);

    Livewire::test('pages::admin.kegiatan')
        ->call('konfirmasiHapus', $kegiatan->id)
        ->call('hapus');

    expect(Kegiatan::count())->toBe(0);
});

<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests are redirected to the login page', function () {
    auth()->logout();

    $this->get(route('admin.profil-gereja'))->assertRedirect(route('login'));
});

test('admin can update the church profile', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('nama_gereja', 'Paroki Santo Yosef Bampel')
        ->set('whatsapp', '081234567890')
        ->set('tampil_card_misa', false)
        ->call('simpan')
        ->assertHasNoErrors();

    $setting = Setting::current();

    expect($setting->nama_gereja)->toBe('Paroki Santo Yosef Bampel')
        ->and($setting->whatsapp_url)->toBe('https://wa.me/6281234567890')
        ->and($setting->tampil_card_misa)->toBeFalse();
});

test('admin can save the pelindung story and photo', function () {
    Storage::fake('public');

    Livewire::test('pages::admin.profil-gereja')
        ->set('pelindung', 'Santo Yosef')
        ->set('kisah_pelindung', 'Suami Santa Perawan Maria.')
        ->set('foto_pelindung', UploadedFile::fake()->image('yosef.jpg'))
        ->call('simpan')
        ->assertHasNoErrors();

    $setting = Setting::current();

    expect($setting->kisah_pelindung)->toBe('Suami Santa Perawan Maria.')
        ->and($setting->foto_pelindung)->toStartWith('profil/')->toEndWith('.webp');
    Storage::disk('public')->assertExists($setting->foto_pelindung);
});

test('social media usernames are saved as full links', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('instagram', '@parokibambupemali')
        ->set('youtube', 'parokibambupemali')
        ->set('facebook', 'facebook.com/parokibp')
        ->call('simpan')
        ->assertHasNoErrors();

    expect(Setting::current())
        ->instagram->toBe('https://instagram.com/parokibambupemali')
        ->youtube->toBe('https://youtube.com/@parokibambupemali')
        ->facebook->toBe('https://facebook.com/parokibp');
});

test('nama gereja is required', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('nama_gereja', '')
        ->call('simpan')
        ->assertHasErrors(['nama_gereja' => 'required']);
});

test('uploaded photo is resized and stored as webp', function () {
    Storage::fake('public');

    Livewire::test('pages::admin.profil-gereja')
        ->set('foto_hero', UploadedFile::fake()->image('gereja.jpg', 2400, 1200))
        ->call('simpan')
        ->assertHasNoErrors();

    $path = Setting::current()->foto_hero;

    expect($path)->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
    expect(getimagesizefromstring(Storage::disk('public')->get($path))[0])->toBe(1600);
});

test('photo with resolution above 6000 px is rejected instead of exhausting memory', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('foto_hero', UploadedFile::fake()->image('besar.jpg', 6100, 10))
        ->call('simpan')
        ->assertHasErrors(['foto_hero' => 'dimensions']);
});

test('photo larger than 3 MB is rejected', function () {
    Livewire::test('pages::admin.profil-gereja')
        ->set('logo', UploadedFile::fake()->image('logo.jpg')->size(4000))
        ->call('simpan')
        ->assertHasErrors(['logo' => 'max']);
});

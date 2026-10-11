<?php

use App\Models\User;
use App\Models\Warta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

test('admin can open the page', function () {
    $this->get(route('admin.warta'))->assertOk();
});

test('admin can create a warta with a pdf', function () {
    Livewire::test('pages::admin.warta')
        ->call('tambah')
        ->set('judul', 'Warta Minggu Ini')
        ->set('status', Warta::TERBIT)
        ->set('file_pdf', UploadedFile::fake()->create('warta.pdf', 500, 'application/pdf'))
        ->call('simpan')
        ->assertHasNoErrors();

    $warta = Warta::sole();

    expect($warta->slug)->toBe('warta-minggu-ini')
        ->and($warta->status)->toBe(Warta::TERBIT)
        ->and($warta->tanggal->isToday())->toBeTrue();
    Storage::disk('public')->assertExists($warta->file_pdf);
});

test('pdf is required when creating', function () {
    Livewire::test('pages::admin.warta')
        ->call('tambah')
        ->set('judul', 'Warta')
        ->call('simpan')
        ->assertHasErrors(['file_pdf' => 'required']);
});

test('only pdf files up to 5 MB are accepted', function (UploadedFile $file, string $rule) {
    Livewire::test('pages::admin.warta')
        ->call('tambah')
        ->set('judul', 'Warta')
        ->set('file_pdf', $file)
        ->call('simpan')
        ->assertHasErrors(['file_pdf' => $rule]);
})->with([
    'not a pdf' => [fn () => UploadedFile::fake()->create('warta.docx', 100), 'mimes'],
    'too large' => [fn () => UploadedFile::fake()->create('warta.pdf', 6000, 'application/pdf'), 'max'],
]);

test('editing without a new pdf keeps the old file', function () {
    $warta = Warta::create(['judul' => 'Lama', 'tanggal' => today(), 'file_pdf' => 'warta/lama.pdf', 'status' => Warta::DRAFT]);

    Livewire::test('pages::admin.warta')
        ->call('edit', $warta->id)
        ->set('judul', 'Baru')
        ->call('simpan')
        ->assertHasNoErrors();

    expect($warta->fresh())
        ->judul->toBe('Baru')
        ->file_pdf->toBe('warta/lama.pdf');
});

test('deleting a warta removes its pdf', function () {
    Storage::disk('public')->put('warta/hapus.pdf', 'isi');
    $warta = Warta::create(['judul' => 'Hapus', 'tanggal' => today(), 'file_pdf' => 'warta/hapus.pdf']);

    Livewire::test('pages::admin.warta')
        ->call('konfirmasiHapus', $warta->id)
        ->call('hapus');

    expect(Warta::count())->toBe(0);
    Storage::disk('public')->assertMissing('warta/hapus.pdf');
});

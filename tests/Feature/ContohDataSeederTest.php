<?php

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Warta;
use Database\Seeders\ContohDataSeeder;
use Illuminate\Support\Facades\Storage;

test('sample seeder can run repeatedly without duplicating data', function () {
    Storage::fake('public');

    $this->seed(ContohDataSeeder::class);
    $this->seed(ContohDataSeeder::class);

    expect(JadwalMisa::count())->toBe(7)
        ->and(Kegiatan::count())->toBe(4)
        ->and(Warta::terbit()->count())->toBe(4)
        ->and(Galeri::count())->toBe(8);

    Galeri::all()->each(function (Galeri $galeri) {
        expect(getimagesizefromstring(Storage::disk('public')->get($galeri->foto))['mime'])->toBe('image/webp');
    });

    Warta::all()->each(function (Warta $warta) {
        Storage::disk('public')->assertExists($warta->file_pdf);
        expect(Storage::disk('public')->get($warta->file_pdf))->toStartWith('%PDF-');
    });
});

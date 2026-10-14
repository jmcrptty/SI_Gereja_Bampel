<?php

namespace App\Console\Commands;

use App\Concerns\KompresFoto;
use App\Models\Galeri;
use App\Models\Kegiatan;
use App\Models\Pastor;
use App\Models\Pengurus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

#[Signature('foto:buat-thumbnail')]
#[Description('Buat versi kecil (_kecil) untuk foto galeri, kegiatan, dan pengurus yang belum punya')]
class BuatThumbnailFoto extends Command
{
    use KompresFoto;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dibuat = 0;

        foreach ([Galeri::class, Kegiatan::class, Pastor::class, Pengurus::class] as $model) {
            $model::query()->whereNotNull('foto')->each(function ($data) use ($model, $disk, &$dibuat) {
                $pathKecil = $model::pathKecil($data->foto);

                if ($disk->exists($pathKecil) || ! $disk->exists($data->foto)) {
                    return;
                }

                try {
                    $gambar = ImageManager::usingDriver(Driver::class)->decodePath($disk->path($data->foto));
                    $this->simpanVersiKecil($gambar, $pathKecil);
                    $dibuat++;
                } catch (Throwable $e) {
                    $this->warn("Gagal: {$data->foto} ({$e->getMessage()})");
                }
            });
        }

        $this->info("{$dibuat} versi kecil dibuat.");

        return self::SUCCESS;
    }
}

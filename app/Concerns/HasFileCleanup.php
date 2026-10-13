<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Menghapus file lama di disk "public" saat file diganti atau data dihapus,
 * termasuk pasangan versi kecilnya (akhiran "_kecil", lihat KompresFoto).
 * Model yang memakai trait ini mendefinisikan: protected array $fileColumns = ['foto'];
 */
trait HasFileCleanup
{
    public static function bootHasFileCleanup(): void
    {
        static::updated(function ($model) {
            foreach ($model->fileColumns ?? [] as $kolom) {
                $lama = $model->getOriginal($kolom);
                if ($model->wasChanged($kolom) && $lama) {
                    Storage::disk('public')->delete([$lama, static::pathKecil($lama)]);
                }
            }
        });

        static::deleted(function ($model) {
            foreach ($model->fileColumns ?? [] as $kolom) {
                if ($model->{$kolom}) {
                    Storage::disk('public')->delete([$model->{$kolom}, static::pathKecil($model->{$kolom})]);
                }
            }
        });
    }

    /**
     * Path versi kecil sebuah file, contoh: galeri/abc.webp → galeri/abc_kecil.webp.
     */
    public static function pathKecil(string $path): string
    {
        $info = pathinfo($path);
        $folder = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'].'/';
        $ekstensi = isset($info['extension']) ? '.'.$info['extension'] : '';

        return $folder.$info['filename'].'_kecil'.$ekstensi;
    }

    /**
     * URL publik sebuah kolom file, atau null jika kosong.
     */
    public function fileUrl(string $kolom): ?string
    {
        return $this->{$kolom} ? Storage::disk('public')->url($this->{$kolom}) : null;
    }

    /**
     * URL versi kecil sebuah kolom foto; kembali ke foto penuh bila versi kecil belum ada (data lama).
     */
    public function fileUrlKecil(string $kolom): ?string
    {
        if (! $this->{$kolom}) {
            return null;
        }

        $kecil = static::pathKecil($this->{$kolom});

        return Storage::disk('public')->exists($kecil)
            ? Storage::disk('public')->url($kecil)
            : $this->fileUrl($kolom);
    }
}

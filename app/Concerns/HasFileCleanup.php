<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Menghapus file lama di disk "public" saat file diganti atau data dihapus.
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
                    Storage::disk('public')->delete($lama);
                }
            }
        });

        static::deleted(function ($model) {
            foreach ($model->fileColumns ?? [] as $kolom) {
                if ($model->{$kolom}) {
                    Storage::disk('public')->delete($model->{$kolom});
                }
            }
        });
    }

    /**
     * URL publik sebuah kolom file, atau null jika kosong.
     */
    public function fileUrl(string $kolom): ?string
    {
        return $this->{$kolom} ? Storage::disk('public')->url($this->{$kolom}) : null;
    }
}

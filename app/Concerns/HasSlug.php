<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Membuat slug unik otomatis dari kolom "judul" saat data dibuat.
 * Slug tidak berubah saat judul diedit, agar link yang sudah dibagikan tetap berfungsi.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (blank($model->slug)) {
                $model->slug = static::buatSlugUnik($model->judul, $model->getKey());
            }
        });
    }

    protected static function buatSlugUnik(string $judul, ?int $abaikanId = null): string
    {
        $dasar = Str::slug($judul) ?: 'item';
        $slug = $dasar;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($abaikanId, fn ($q) => $q->whereKeyNot($abaikanId))
            ->exists()) {
            $slug = $dasar.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

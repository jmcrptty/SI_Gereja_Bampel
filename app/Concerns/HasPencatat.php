<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mengisi kolom created_by / updated_by otomatis dengan admin yang sedang login.
 */
trait HasPencatat
{
    public static function bootHasPencatat(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

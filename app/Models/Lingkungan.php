<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lingkungan extends Model
{
    /** Titik tengah peta kota Merauke [lat, lng]. */
    public const PUSAT_PETA = [-8.4932, 140.4018];

    protected $fillable = ['nama', 'wilayah', 'ketua', 'jumlah_kk', 'jadwal_doa', 'cakupan_jalan', 'batas_wilayah', 'urutan'];

    protected function casts(): array
    {
        return [
            'batas_wilayah' => 'array',
            'jumlah_kk' => 'integer',
            'urutan' => 'integer',
        ];
    }

    /**
     * Lingkungan yang sudah punya arsiran batas wilayah di peta.
     */
    public function scopePunyaBatas(Builder $q): Builder
    {
        return $q->whereNotNull('batas_wilayah');
    }

    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderBy('urutan')->orderBy('nama');
    }

    /**
     * Cakupan jalan dipecah per baris.
     *
     * @return array<int, string>
     */
    public function getDaftarJalanAttribute(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->cakupan_jalan))));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lingkungan extends Model
{
    protected $fillable = ['nama', 'wilayah', 'ketua', 'jumlah_kk', 'jadwal_doa', 'cakupan_jalan', 'urutan'];

    protected function casts(): array
    {
        return [
            'jumlah_kk' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderBy('wilayah')->orderBy('urutan')->orderBy('nama');
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

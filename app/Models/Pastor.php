<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pastor extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'nama', 'jabatan', 'tarekat', 'foto', 'asal', 'tanggal_lahir', 'tanggal_tahbisan',
        'mulai_bertugas', 'motto', 'cerita', 'urutan',
    ];

    protected array $fileColumns = ['foto'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_tahbisan' => 'date',
            'mulai_bertugas' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderBy('urutan')->orderBy('nama');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->fileUrl('foto');
    }

    public function getFotoKecilUrlAttribute(): ?string
    {
        return $this->fileUrlKecil('foto');
    }

    /**
     * Cerita dipecah per paragraf (dipisah baris kosong).
     *
     * @return array<int, string>
     */
    public function getParagrafCeritaAttribute(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', (string) $this->cerita))));
    }
}

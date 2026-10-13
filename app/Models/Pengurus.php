<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    use HasFileCleanup;

    // Wajib: tanpa ini Laravel mencari tabel "penguruses".
    protected $table = 'pengurus';

    protected $fillable = ['nama', 'jabatan', 'bidang', 'foto', 'periode', 'urutan'];

    protected array $fileColumns = ['foto'];

    protected function casts(): array
    {
        return ['urutan' => 'integer'];
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
}

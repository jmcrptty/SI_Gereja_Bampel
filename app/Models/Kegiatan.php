<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use App\Concerns\HasPencatat;
use App\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFileCleanup, HasPencatat, HasSlug;

    protected $fillable = ['judul', 'slug', 'tanggal', 'jam', 'tempat', 'deskripsi', 'foto', 'aktif'];

    protected array $fileColumns = ['foto'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('aktif', true);
    }

    public function scopeAkanDatang(Builder $q): Builder
    {
        return $q->whereDate('tanggal', '>=', today())->orderBy('tanggal')->orderBy('jam');
    }

    public function scopeSebelumnya(Builder $q): Builder
    {
        return $q->whereDate('tanggal', '<', today())->orderByDesc('tanggal');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->fileUrl('foto');
    }

    public function getFotoKecilUrlAttribute(): ?string
    {
        return $this->fileUrlKecil('foto');
    }

    public function getJamFormatAttribute(): ?string
    {
        return $this->jam ? substr((string) $this->jam, 0, 5) : null;
    }

    /** Kegiatan terdekat untuk card hero. */
    public static function untukCardHero(): ?self
    {
        return static::aktif()->akanDatang()->first();
    }
}

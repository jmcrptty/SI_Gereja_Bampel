<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    use HasFileCleanup;

    protected $fillable = ['judul', 'foto', 'tanggal'];

    protected array $fileColumns = ['foto'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function scopeTerbaru(Builder $q): Builder
    {
        return $q->orderByDesc('tanggal')->orderByDesc('id');
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

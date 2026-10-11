<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use App\Concerns\HasPencatat;
use App\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Warta extends Model
{
    use HasFileCleanup, HasPencatat, HasSlug;

    public const DRAFT = 'draft';

    public const TERBIT = 'terbit';

    protected $fillable = ['judul', 'slug', 'tanggal', 'ringkasan', 'file_pdf', 'status'];

    protected array $fileColumns = ['file_pdf'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function scopeTerbit(Builder $q): Builder
    {
        return $q->where('status', self::TERBIT);
    }

    public function scopeTerbaru(Builder $q): Builder
    {
        return $q->orderByDesc('tanggal')->orderByDesc('id');
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->fileUrl('file_pdf');
    }
}

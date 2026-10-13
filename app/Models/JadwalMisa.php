<?php

namespace App\Models;

use App\Concerns\HasPencatat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class JadwalMisa extends Model
{
    use HasPencatat;

    public const RUTIN = 'rutin';

    public const KHUSUS = 'khusus';

    /** 1 = Senin ... 7 = Minggu (sama dengan Carbon::dayOfWeekIso) */
    public const HARI = [
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
        5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
    ];

    protected $fillable = ['jenis', 'nama_perayaan', 'hari', 'tanggal', 'jam', 'tempat', 'aktif'];

    protected function casts(): array
    {
        return [
            'hari' => 'integer',
            'tanggal' => 'date',
            'aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Kosongkan kolom yang tidak relevan sesuai jenis misa.
        static::saving(function (self $misa) {
            if ($misa->jenis === self::RUTIN) {
                $misa->tanggal = null;
                $misa->nama_perayaan = null;
            } else {
                $misa->hari = null;
            }
        });
    }

    // ---------- Scope ----------

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('aktif', true);
    }

    public function scopeRutin(Builder $q): Builder
    {
        return $q->where('jenis', self::RUTIN);
    }

    public function scopeKhusus(Builder $q): Builder
    {
        return $q->where('jenis', self::KHUSUS);
    }

    /** Misa khusus dari hari ini sampai $hari ke depan (null = tanpa batas). */
    public function scopeAkanDatang(Builder $q, ?int $hari = null): Builder
    {
        $q->whereDate('tanggal', '>=', today());

        if ($hari !== null) {
            $q->whereDate('tanggal', '<=', today()->addDays($hari));
        }

        return $q->orderBy('tanggal')->orderBy('jam');
    }

    /** Misa rutin diurutkan mulai dari hari ini, lalu berputar ke awal minggu. */
    public function scopeUrutDariHariIni(Builder $q): Builder
    {
        $hariIni = today()->dayOfWeekIso;

        return $q->orderByRaw('CASE WHEN hari >= ? THEN 0 ELSE 1 END', [$hariIni])
            ->orderBy('hari')
            ->orderBy('jam');
    }

    // ---------- Accessor ----------

    public function getNamaHariAttribute(): ?string
    {
        return self::HARI[$this->hari] ?? null;
    }

    public function getJamFormatAttribute(): string
    {
        return substr((string) $this->jam, 0, 5); // 07:00
    }

    // ---------- Query siap pakai untuk card hero ----------

    /**
     * Data card Jadwal Misa di hero.
     *
     * @return array{judul: string, khusus: bool, items: Collection}
     */
    public static function untukCardHero(): array
    {
        $khusus = static::aktif()->khusus()->akanDatang(14)->limit(4)->get();

        if ($khusus->isNotEmpty()) {
            return ['judul' => 'Jadwal Misa Hari Raya', 'khusus' => true, 'items' => $khusus];
        }

        // Misa hari ini yang jamnya sudah lewat dipindah ke urutan paling akhir (minggu depan).
        $hariIni = today()->dayOfWeekIso;
        $jamSekarang = now()->format('H:i:s');

        [$sudahLewat, $berikutnya] = static::aktif()->rutin()->urutDariHariIni()->get()
            ->partition(fn (self $misa) => $misa->hari === $hariIni && $misa->jam < $jamSekarang);

        return [
            'judul' => 'Jadwal Misa Minggu Ini',
            'khusus' => false,
            'items' => $berikutnya->concat($sudahLewat)->take(4)->values(),
        ];
    }
}

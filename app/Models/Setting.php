<?php

namespace App\Models;

use App\Concerns\HasFileCleanup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Profil gereja, kontak, dan pengaturan tampilan. Hanya ada 1 baris.
 * Pakai Setting::current() untuk mengambilnya (sudah di-cache).
 */
class Setting extends Model
{
    use HasFileCleanup;

    protected $fillable = [
        'nama_gereja', 'pelindung', 'foto_pelindung', 'kisah_pelindung', 'logo', 'foto_hero', 'foto_gereja',
        'sejarah_singkat', 'sejarah', 'visi', 'misi',
        'alamat', 'telepon', 'whatsapp', 'email', 'link_maps',
        'facebook', 'instagram', 'youtube', 'jam_sekretariat',
        'tampil_card_misa', 'tampil_card_kegiatan',
    ];

    /**
     * Yang di-cache hanya array atribut, karena cache Laravel menolak unserialize objek
     * (config cache.serializable_classes = false).
     */
    private const CACHE_KEY = 'setting.atribut';

    protected array $fileColumns = ['logo', 'foto_hero', 'foto_gereja', 'foto_pelindung'];

    protected function casts(): array
    {
        return [
            'tampil_card_misa' => 'boolean',
            'tampil_card_kegiatan' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        // refresh() agar nilai default kolom (misal tampil_card_misa) ikut terbaca saat baris baru dibuat.
        $atribut = Cache::rememberForever(self::CACHE_KEY, fn () => static::firstOrCreate([], [
            'nama_gereja' => 'Paroki St. Yoseph Bambu Pemali',
        ])->refresh()->getAttributes());

        return (new static)->newFromBuilder($atribut);
    }

    /**
     * Link WhatsApp siap pakai, contoh: https://wa.me/6281234567890
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $nomor = preg_replace('/\D/', '', $this->whatsapp);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62'.substr($nomor, 1);
        }

        return 'https://wa.me/'.$nomor;
    }

    /**
     * Ubah isian media sosial menjadi link lengkap. Menerima link penuh, "domain.com/akun", atau nama akun ("@akun").
     * Contoh: linkMedsos('instagram', '@paroki') → https://instagram.com/paroki
     */
    public static function linkMedsos(string $jenis, ?string $nilai): ?string
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $nilai)) {
            return $nilai;
        }

        if (str_contains($nilai, '.') && str_contains($nilai, '/')) {
            return 'https://'.ltrim($nilai, '/');
        }

        $akun = ltrim($nilai, '@/');

        return match ($jenis) {
            'instagram' => 'https://instagram.com/'.$akun,
            'youtube' => 'https://youtube.com/@'.$akun,
            'facebook' => 'https://facebook.com/'.$akun,
            default => $nilai,
        };
    }

    /**
     * Media sosial yang sudah diisi, untuk ikon di footer.
     *
     * @return array<int, array{jenis: string, label: string, url: string}>
     */
    public function getMediaSosialAttribute(): array
    {
        $daftar = [
            ['jenis' => 'instagram', 'label' => 'Instagram', 'url' => self::linkMedsos('instagram', $this->instagram)],
            ['jenis' => 'youtube', 'label' => 'YouTube', 'url' => self::linkMedsos('youtube', $this->youtube)],
            ['jenis' => 'whatsapp', 'label' => 'WhatsApp', 'url' => $this->whatsapp_url],
            ['jenis' => 'facebook', 'label' => 'Facebook', 'url' => self::linkMedsos('facebook', $this->facebook)],
        ];

        return array_values(array_filter($daftar, fn (array $item) => $item['url'] !== null));
    }

    /**
     * Jam sekretariat dipecah per baris.
     *
     * @return array<int, string>
     */
    public function getDaftarJamSekretariatAttribute(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->jam_sekretariat))));
    }
}

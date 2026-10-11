<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Menyimpan foto upload sebagai WebP (lebar maks 1600 px) di disk "public", dengan crop opsional.
 * Dipakai oleh komponen Livewire di dashboard admin.
 *
 * Data crop diisi dari editor crop di browser (komponen <x-admin.unggah-foto>) ke properti $potong,
 * dengan kunci = nama properti foto, contoh: $potong['foto_hero'] atau $potong['fotos'][0].
 * Nilainya koordinat dalam piksel asli gambar: ['x' => .., 'y' => .., 'w' => .., 'h' => ..].
 */
trait KompresFoto
{
    /** @var array<string, mixed> */
    public array $potong = [];

    /**
     * Hook trait Livewire (dipanggil setiap properti berubah):
     * saat file baru dipilih, crop lama untuk properti itu tidak berlaku lagi.
     */
    public function updatedKompresFoto(string $properti): void
    {
        if ($properti !== 'potong' && ! str_starts_with($properti, 'potong.')) {
            Arr::forget($this->potong, $properti);
        }
    }

    /**
     * Aturan validasi foto upload: gambar, maks 3 MB, resolusi maks 6000 px per sisi
     * (foto lebih besar butuh memori berlebih saat dikompres dengan GD).
     *
     * @return array<int, string>
     */
    protected function aturanFoto(): array
    {
        return ['image', 'max:3072', 'dimensions:max_width=6000,max_height=6000'];
    }

    /**
     * Proses satu kolom foto: foto baru (dengan crop bila ada), atau crop ulang foto lama.
     * Mengembalikan path baru, atau null bila tidak ada perubahan.
     */
    protected function prosesFoto(?UploadedFile $baru, ?string $lama, string $kunci, string $folder): ?string
    {
        $potong = $this->ambilPotong($kunci);

        if ($baru) {
            return $this->simpanFotoWebp($baru, $folder, $potong);
        }

        if ($lama && $potong && Storage::disk('public')->exists($lama)) {
            return $this->simpanFotoWebp(Storage::disk('public')->path($lama), $folder, $potong);
        }

        return null;
    }

    /**
     * Kompres foto (crop opsional) lalu simpan, kembalikan path relatif (contoh: kegiatan/abc.webp).
     *
     * @param  array{x: int, y: int, w: int, h: int}|null  $potong
     */
    protected function simpanFotoWebp(UploadedFile|string $foto, string $folder, ?array $potong = null): string
    {
        // GD memuat seluruh piksel ke memori (foto 4000x3000 ≈ 48 MB), jadi batas memori dinaikkan sementara.
        $this->naikkanBatasMemori('512M');

        $gambar = ImageManager::usingDriver(Driver::class)
            ->decodePath($foto instanceof UploadedFile ? $foto->getRealPath() : $foto);

        if ($potong) {
            // Kunci koordinat agar selalu di dalam gambar.
            $x = max(0, min($potong['x'], $gambar->width() - 1));
            $y = max(0, min($potong['y'], $gambar->height() - 1));
            $w = max(1, min($potong['w'], $gambar->width() - $x));
            $h = max(1, min($potong['h'], $gambar->height() - $y));

            $gambar->crop($w, $h, $x, $y);
        }

        $hasil = $gambar->scaleDown(width: 1600)->encode(new WebpEncoder(quality: 80));

        $path = $folder.'/'.Str::random(40).'.webp';

        Storage::disk('public')->put($path, $hasil->toString());

        return $path;
    }

    /**
     * Ambil data crop yang valid untuk satu kunci, atau null.
     *
     * @return array{x: int, y: int, w: int, h: int}|null
     */
    protected function ambilPotong(string $kunci): ?array
    {
        $data = Arr::get($this->potong, $kunci);

        if (! is_array($data) || ! isset($data['x'], $data['y'], $data['w'], $data['h'])) {
            return null;
        }

        $potong = array_map(fn ($nilai) => (int) round((float) $nilai), Arr::only($data, ['x', 'y', 'w', 'h']));

        return $potong['w'] > 0 && $potong['h'] > 0 ? $potong : null;
    }

    /**
     * Naikkan memory_limit hanya jika batas saat ini lebih kecil (atau tidak terbatas dibiarkan).
     */
    private function naikkanBatasMemori(string $target): void
    {
        $sekarang = ini_get('memory_limit');

        if ($sekarang === '-1' || $this->keByte($sekarang) >= $this->keByte($target)) {
            return;
        }

        ini_set('memory_limit', $target);
    }

    private function keByte(string $nilai): int
    {
        $angka = (int) $nilai;

        return match (strtoupper(substr(trim($nilai), -1))) {
            'G' => $angka * 1024 ** 3,
            'M' => $angka * 1024 ** 2,
            'K' => $angka * 1024,
            default => $angka,
        };
    }
}

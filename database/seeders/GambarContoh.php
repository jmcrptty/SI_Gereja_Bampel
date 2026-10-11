<?php

namespace Database\Seeders;

use GdImage;

/**
 * Membuat gambar ilustrasi bertema gereja (WebP) untuk data contoh galeri, memakai GD bawaan PHP.
 * Hanya untuk uji tampilan; ganti dengan foto asli lewat dashboard admin.
 */
class GambarContoh
{
    private const LEBAR = 1200;

    private const TINGGI = 900;

    /**
     * @param  'gereja'|'salib'|'lilin'|'jendela'|'umat'  $tema
     * @param  array{0: string, 1: string, 2: string}  $warna  [langit atas, langit bawah, siluet] dalam hex
     */
    public static function buat(string $tema, array $warna): string
    {
        $img = imagecreatetruecolor(self::LEBAR, self::TINGGI);
        imagealphablending($img, true);

        self::gradasi($img, $warna[0], $warna[1]);
        self::cahaya($img, (int) (self::LEBAR * 0.6), (int) (self::TINGGI * 0.62), 520, '#ffe2a8');

        $siluet = self::warna($img, $warna[2]);
        $emas = self::warna($img, '#fcd34d');

        match ($tema) {
            'gereja' => self::gereja($img, $siluet, $emas),
            'salib' => self::salib($img, $siluet),
            'lilin' => self::lilin($img, $emas),
            'jendela' => self::jendela($img, $siluet),
            'umat' => self::umat($img, $siluet),
        };

        ob_start();
        imagewebp($img, null, 80);

        return (string) ob_get_clean();
    }

    private static function gereja(GdImage $img, int $siluet, int $emas): void
    {
        // Menara + salib
        imagefilledrectangle($img, 640, 420, 760, 800, $siluet);
        imagefilledpolygon($img, [628, 425, 700, 300, 772, 425], $siluet);
        imagefilledrectangle($img, 694, 200, 706, 305, $siluet);
        imagefilledrectangle($img, 668, 228, 732, 240, $siluet);
        // Bangunan utama & sayap
        imagefilledrectangle($img, 330, 580, 660, 800, $siluet);
        imagefilledpolygon($img, [305, 590, 495, 460, 685, 590], $siluet);
        imagefilledrectangle($img, 750, 620, 930, 800, $siluet);
        imagefilledpolygon($img, [738, 628, 840, 565, 942, 628], $siluet);
        // Jendela menyala
        imagefilledellipse($img, 495, 550, 44, 44, $emas);
        foreach ([[400, 640], [560, 640], [785, 670], [865, 670]] as [$x, $y]) {
            imagefilledrectangle($img, $x, $y, $x + 34, $y + 64, $emas);
            imagefilledellipse($img, $x + 17, $y, 34, 34, $emas);
        }
        imagefilledrectangle($img, 680, 500, 720, 580, $emas);
        imagefilledellipse($img, 700, 500, 40, 40, $emas);
        // Tanah
        imagefilledrectangle($img, 0, 790, self::LEBAR, self::TINGGI, $siluet);
    }

    private static function salib(GdImage $img, int $siluet): void
    {
        // Sinar dari belakang salib
        $sinar = imagecolorallocatealpha($img, 255, 240, 200, 100);
        for ($sudut = 0; $sudut < 360; $sudut += 15) {
            $rad = deg2rad($sudut);
            imagefilledpolygon($img, [
                600, 380,
                (int) (600 + cos($rad) * 900), (int) (380 + sin($rad) * 900),
                (int) (600 + cos($rad + 0.08) * 900), (int) (380 + sin($rad + 0.08) * 900),
            ], $sinar);
        }
        // Bukit
        imagefilledellipse($img, 600, 1060, 1700, 560, $siluet);
        // Salib besar
        imagefilledrectangle($img, 580, 170, 620, 800, $siluet);
        imagefilledrectangle($img, 470, 300, 730, 340, $siluet);
    }

    private static function lilin(GdImage $img, int $emas): void
    {
        $krem = self::warna($img, '#f7efe2');
        $bayang = self::warna($img, '#e2cba9');

        foreach ([[380, 430, 70], [565, 330, 80], [760, 470, 70]] as [$x, $atas, $lebar]) {
            self::cahaya($img, $x + (int) ($lebar / 2), $atas - 40, 260, '#fcd34d');
            imagefilledrectangle($img, $x, $atas, $x + $lebar, 780, $krem);
            imagefilledrectangle($img, $x + $lebar - 14, $atas, $x + $lebar, 780, $bayang);
            imagefilledellipse($img, $x + (int) ($lebar / 2), $atas - 38, 34, 70, $emas);
            imagefilledellipse($img, $x + (int) ($lebar / 2), $atas - 28, 14, 30, self::warna($img, '#fff7d6'));
        }
        // Meja altar
        imagefilledrectangle($img, 200, 780, 1000, 820, self::warna($img, '#5c3d27'));
        imagefilledrectangle($img, 230, 820, 970, self::TINGGI, self::warna($img, '#3a2417'));
    }

    private static function jendela(GdImage $img, int $siluet): void
    {
        [$kiri, $kanan, $atas, $bawah] = [400, 800, 160, 800];
        $warnaKaca = ['#b13a3a', '#fcd34d', '#efe1cb', '#8c6647', '#962d2d', '#f4b860'];

        // Panel kaca warna-warni
        for ($y = $atas; $y < $bawah; $y += 80) {
            for ($x = $kiri; $x < $kanan; $x += 80) {
                $i = intdiv($x, 80) + intdiv($y, 80);
                imagefilledrectangle($img, $x, $y, $x + 80, $y + 80, self::warna($img, $warnaKaca[$i % count($warnaKaca)]));
            }
        }
        // Potong bagian atas menjadi lengkung: tutup sudut dengan warna latar dinding
        $dinding = $siluet;
        imagefilledrectangle($img, 0, 0, self::LEBAR, $atas + 200, $dinding);
        imagefilledrectangle($img, 0, 0, $kiri, self::TINGGI, $dinding);
        imagefilledrectangle($img, $kanan, 0, self::LEBAR, self::TINGGI, $dinding);
        imagefilledrectangle($img, 0, $bawah, self::LEBAR, self::TINGGI, $dinding);
        imagefilledarc($img, 600, $atas + 200, $kanan - $kiri, 400, 180, 360, self::warna($img, '#f4b860'), IMG_ARC_PIE);
        // Kerangka timah
        imagesetthickness($img, 8);
        for ($x = $kiri; $x <= $kanan; $x += 80) {
            imageline($img, $x, $atas + 200, $x, $bawah, $dinding);
        }
        for ($y = $atas + 200; $y <= $bawah; $y += 80) {
            imageline($img, $kiri, $y, $kanan, $y, $dinding);
        }
        imagearc($img, 600, $atas + 200, $kanan - $kiri, 400, 180, 360, $dinding);
        imageline($img, 600, $atas, 600, $bawah, $dinding);
        imagesetthickness($img, 1);
    }

    private static function umat(GdImage $img, int $siluet): void
    {
        // Barisan siluet umat dengan tinggi berbeda
        $orang = [[120, 1.0], [250, 0.85], [370, 1.1], [500, 0.75], [610, 1.05], [740, 0.9], [860, 1.15], [990, 0.8], [1100, 1.0]];
        foreach ($orang as [$x, $skala]) {
            $tinggi = (int) (300 * $skala);
            $kepala = (int) (70 * $skala);
            $atas = 900 - $tinggi;
            imagefilledellipse($img, $x, $atas, $kepala, $kepala, $siluet);
            imagefilledellipse($img, $x, $atas + $tinggi, (int) (170 * $skala), (int) ($tinggi * 1.4), $siluet);
        }
    }

    private static function gradasi(GdImage $img, string $dari, string $ke): void
    {
        [$r1, $g1, $b1] = self::rgb($dari);
        [$r2, $g2, $b2] = self::rgb($ke);

        for ($y = 0; $y < self::TINGGI; $y++) {
            $t = $y / (self::TINGGI - 1);
            $warna = imagecolorallocate($img, (int) ($r1 + ($r2 - $r1) * $t), (int) ($g1 + ($g2 - $g1) * $t), (int) ($b1 + ($b2 - $b1) * $t));
            imageline($img, 0, $y, self::LEBAR, $y, $warna);
        }
    }

    /** Lingkaran cahaya lembut berlapis. */
    private static function cahaya(GdImage $img, int $x, int $y, int $diameter, string $hex): void
    {
        [$r, $g, $b] = self::rgb($hex);
        for ($d = $diameter; $d > 0; $d -= (int) ($diameter / 12)) {
            imagefilledellipse($img, $x, $y, $d, $d, imagecolorallocatealpha($img, $r, $g, $b, 118));
        }
    }

    private static function warna(GdImage $img, string $hex): int
    {
        return imagecolorallocate($img, ...self::rgb($hex));
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }
}

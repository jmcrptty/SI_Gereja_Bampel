<?php

namespace Database\Seeders;

use App\Models\Galeri;
use App\Models\JadwalMisa;
use App\Models\Kegiatan;
use App\Models\Lingkungan;
use App\Models\Pengurus;
use App\Models\Setting;
use App\Models\Warta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Data contoh untuk uji tampilan. Hapus/ganti dengan data asli dari sekretariat.
 * Aman dijalankan berulang (tidak menggandakan data).
 * Jalankan: php artisan db:seed --class=ContohDataSeeder
 */
class ContohDataSeeder extends Seeder
{
    private const SEJARAH_SINGKAT = 'Paroki St. Yoseph Bambu Pemali adalah rumah bersama umat Katolik di Merauke, Papua Selatan. Berawal dari persekutuan kecil umat yang setia berkumpul untuk berdoa dan merayakan Ekaristi, komunitas ini terus bertumbuh menjadi paroki yang hidup, hangat, dan melayani.'
        ."\n\n".'Dengan semangat persaudaraan, kami mengajak setiap umat untuk ambil bagian dalam perayaan liturgi, kegiatan lingkungan, dan karya pelayanan bagi sesama. Pintu gereja selalu terbuka bagi siapa saja yang ingin mengenal Kristus dan bertumbuh bersama dalam iman.';

    /** Sejarah contoh (bukan sejarah asli). Ganti lewat Admin > Profil Paroki. */
    private const SEJARAH = 'Paroki St. Yoseph Bambu Pemali berawal dari sekelompok kecil umat Katolik yang berkumpul di rumah-rumah untuk berdoa bersama dan menyambut kunjungan imam yang datang secara berkala. Di tengah keterbatasan, iman umat tumbuh melalui doa lingkungan, pelajaran agama, dan semangat gotong royong.'
        ."\n\n".'Seiring bertambahnya jumlah umat, dibangunlah sebuah kapel sederhana dari kayu yang menjadi pusat perayaan Ekaristi. Kapel itu kemudian diperluas secara bertahap berkat swadaya umat dan dukungan para dermawan, hingga menjadi gedung gereja yang digunakan saat ini.'
        ."\n\n".'Kini Paroki St. Yoseph Bambu Pemali melayani umat dari berbagai lingkungan dan stasi. Selain perayaan liturgi, paroki aktif dalam pembinaan orang muda, katekese, serta karya sosial bagi sesama. Perjalanan ini adalah buah iman, pengorbanan, dan kesetiaan banyak generasi umat.';

    /** Kisah Santo Yoseph sebagai contoh pelindung. */
    private const KISAH_PELINDUNG = 'Santo Yoseph adalah suami Santa Perawan Maria dan bapa pengasuh Yesus. Ia berasal dari keturunan Raja Daud dan hidup sebagai tukang kayu di Nazaret. Kitab Suci menyebutnya sebagai "orang yang tulus hati" (Mat 1:19).'
        ."\n\n".'Ketika mengetahui Maria mengandung, Yoseph berniat menceraikannya diam-diam agar Maria tidak dipermalukan. Namun setelah malaikat Tuhan menyampaikan kehendak Allah dalam mimpi, ia taat dan menerima Maria sebagai istrinya. Ia pula yang membawa Maria dan Yesus mengungsi ke Mesir untuk menyelamatkan Sang Bayi dari Herodes.'
        ."\n\n".'Yoseph dikenal sebagai teladan kesetiaan, kerendahan hati, dan kerja keras. Gereja merayakan pestanya setiap 19 Maret, serta mengenangnya sebagai Santo Yoseph Pekerja setiap 1 Mei. Pada tahun 1870, Paus Pius IX menetapkannya sebagai Pelindung Gereja Semesta. Ia juga dihormati sebagai pelindung keluarga, para pekerja, dan orang yang menghadapi ajal.';

    public function run(): void
    {
        // Teks profil contoh; hanya mengisi kolom yang masih kosong.
        $setting = Setting::current();
        $setting->update(array_filter([
            'pelindung' => $setting->pelindung ? null : 'Santo Yoseph',
            'sejarah_singkat' => $setting->sejarah_singkat ? null : self::SEJARAH_SINGKAT,
            'visi' => $setting->visi ? null : 'Menjadi persekutuan umat beriman yang hidup, bersaudara, dan menjadi berkat bagi sesama.',
            'misi' => $setting->misi ? null : "Merayakan liturgi yang hidup dan bermakna.\nMembangun persaudaraan melalui komunitas lingkungan.\nMelayani sesama, terutama yang kecil dan menderita.",
            'sejarah' => $setting->sejarah ? null : self::SEJARAH,
            'kisah_pelindung' => $setting->kisah_pelindung ? null : self::KISAH_PELINDUNG,
        ]));

        foreach ([[7, '06:30'], [7, '09:00'], [7, '17:00'], [6, '18:00'], [3, '06:00']] as [$hari, $jam]) {
            JadwalMisa::firstOrCreate(
                ['jenis' => JadwalMisa::RUTIN, 'hari' => $hari, 'jam' => $jam],
                ['tempat' => 'Gereja Utama'],
            );
        }

        foreach ([['Malam Natal', '12-24', '19:00'], ['Hari Raya Natal', '12-25', '07:00']] as [$perayaan, $tanggal, $jam]) {
            JadwalMisa::firstOrCreate(
                ['jenis' => JadwalMisa::KHUSUS, 'nama_perayaan' => $perayaan],
                ['tanggal' => now()->year.'-'.$tanggal, 'jam' => $jam, 'tempat' => 'Gereja Utama'],
            );
        }

        $this->kegiatan();
        $this->warta();
        $this->galeri();

        foreach ([['Ketua Dewan Paroki', 1], ['Wakil Ketua', 2], ['Sekretaris', 3], ['Bendahara', 4]] as [$jabatan, $urutan]) {
            Pengurus::firstOrCreate(
                ['jabatan' => $jabatan, 'periode' => '2024-2027'],
                ['nama' => 'Nama '.$jabatan, 'urutan' => $urutan],
            );
        }

        Lingkungan::firstOrCreate(['nama' => 'Lingkungan Santo Yosef'], [
            'wilayah' => 'Wilayah I',
            'ketua' => 'Nama Ketua',
            'jumlah_kk' => 35,
            'jadwal_doa' => 'Rabu, 19.00',
            'cakupan_jalan' => "Jl. Contoh I\nJl. Contoh II (sisi kiri)",
            'urutan' => 1,
        ]);
    }

    private function kegiatan(): void
    {
        $daftar = [
            ['Rekoleksi Orang Muda Katolik', 10, '09:00', 'Aula Paroki', 'Rekoleksi untuk seluruh OMK paroki dengan tema "Muda, Beriman, dan Berbuah". Peserta diharapkan membawa Kitab Suci dan alat tulis.'],
            ['Latihan Koor Gabungan Lingkungan', 4, '16:00', 'Gereja Utama', 'Latihan bersama koor dari seluruh lingkungan untuk persiapan misa hari raya. Terbuka bagi umat yang ingin bergabung.'],
            ['Bakti Sosial dan Pembagian Sembako', 7, '08:00', 'Halaman Gereja', 'Karya amal paroki untuk keluarga yang membutuhkan. Umat dapat menyumbangkan bahan makanan melalui sekretariat paling lambat H-2.'],
            ['Pendalaman Iman Lingkungan', 14, '19:00', 'Rumah umat di tiap lingkungan', 'Pertemuan pendalaman Kitab Suci di setiap lingkungan. Materi dapat diambil di sekretariat paroki.'],
        ];

        foreach ($daftar as [$judul, $hariLagi, $jam, $tempat, $deskripsi]) {
            Kegiatan::firstOrCreate(['judul' => $judul], [
                'tanggal' => today()->addDays($hariLagi),
                'jam' => $jam,
                'tempat' => $tempat,
                'deskripsi' => $deskripsi,
            ]);
        }
    }

    private function warta(): void
    {
        $daftar = [
            ['Warta Paroki Minggu Biasa XXVIII', 0, 'Pengumuman jadwal misa, kegiatan lingkungan pekan ini, dan persiapan bulan Rosario.'],
            ['Warta Paroki Minggu Biasa XXVII', 7, 'Laporan kolekte, pendaftaran calon penerima Komuni Pertama, dan agenda rapat dewan paroki.'],
            ['Warta Paroki Minggu Biasa XXVI', 14, 'Ucapan terima kasih panitia bakti sosial dan jadwal latihan koor gabungan.'],
            ['Warta Paroki Minggu Biasa XXV', 21, 'Informasi pendaftaran pernikahan, jadwal katekese, dan doa bersama lingkungan.'],
        ];

        foreach ($daftar as [$judul, $mingguLalu, $ringkasan]) {
            $path = 'warta/contoh-'.Str::slug($judul).'.pdf';

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->pdfContoh($judul, $ringkasan));
            }

            Warta::firstOrCreate(['judul' => $judul], [
                'tanggal' => today()->subDays($mingguLalu)->startOfWeek()->addDays(6), // hari Minggu
                'ringkasan' => $ringkasan,
                'file_pdf' => $path,
                'status' => Warta::TERBIT,
            ]);
        }
    }

    private function galeri(): void
    {
        $daftar = [
            ['Misa Minggu Pagi', 'gereja', ['#f4b860', '#a8513a', '#3a2417']],
            ['Perayaan Paskah', 'salib', ['#fdf3d8', '#e2a94f', '#7a2222']],
            ['Doa Rosario Lingkungan', 'lilin', ['#3a2417', '#5c3d27', '#1c0f08']],
            ['Jendela Kaca Patri Gereja', 'jendela', ['#efe1cb', '#e2cba9', '#3a2417']],
            ['Bakti Sosial OMK', 'umat', ['#f7efe2', '#e2a94f', '#5c3d27']],
            ['Malam Natal', 'gereja', ['#24140c', '#5c3d27', '#120804']],
            ['Komuni Pertama', 'salib', ['#fdf9f3', '#efe1cb', '#962d2d']],
            ['Ziarah Umat Lingkungan', 'umat', ['#fcd9a8', '#b13a3a', '#3a2417']],
        ];

        foreach ($daftar as $i => [$judul, $tema, $warna]) {
            $path = 'galeri/contoh-'.Str::slug($judul).'.webp';

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, GambarContoh::buat($tema, $warna));
            }

            Galeri::firstOrCreate(['judul' => $judul], [
                'foto' => $path,
                'tanggal' => today()->subDays(($i + 1) * 9),
            ]);
        }
    }

    /**
     * PDF satu halaman sederhana berisi judul dan ringkasan (tanpa library tambahan).
     */
    private function pdfContoh(string $judul, string $ringkasan): string
    {
        $teks = fn (string $s) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);

        $isi = "BT /F1 20 Tf 60 760 Td ({$teks($judul)}) Tj ET\n"
            ."BT /F1 12 Tf 60 730 Td ({$teks($ringkasan)}) Tj ET\n"
            .'BT /F1 10 Tf 60 700 Td (Contoh warta untuk uji tampilan website.) Tj ET';

        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($isi)." >>\nstream\n{$isi}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offset = [];
        foreach ($objek as $i => $o) {
            $offset[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$o}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objek) + 1)."\n0000000000 65535 f \n";
        foreach ($offset as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objek) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}

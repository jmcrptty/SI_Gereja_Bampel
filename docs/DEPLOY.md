# Panduan Deploy ke Shared Hosting cPanel

Panduan memasang website **Paroki St. Yoseph Bambu Pemali** (Laravel 13 + Livewire 4) di shared hosting cPanel.
Ikuti berurutan. Contoh di bawah memakai akun cPanel `akunparoki` dan domain `parokibambupemali.org`; ganti sesuai milik Anda.

---

## Ringkasan langkah

1. Pastikan hosting memenuhi kebutuhan (PHP, ekstensi, batas upload).
2. Siapkan file di komputer: build aset (`npm run build`) dan paket kode.
3. Unggah kode ke folder **di luar** `public_html`, arahkan domain ke folder `public`.
4. Buat database MySQL di cPanel.
5. Isi `.env` untuk production.
6. Jalankan perintah instalasi (migrate, seed, storage link, optimize).
7. Pasang SSL, login admin, **ganti email & kata sandi admin**.

---

## 1. Kebutuhan hosting

| Komponen | Minimal | Keterangan |
| --- | --- | --- |
| PHP | **8.3** (disarankan sama dengan lokal: 8.5) | Atur di cPanel → *Select PHP Version* / *MultiPHP Manager* |
| MySQL / MariaDB | MySQL 8 atau MariaDB 10.6+ | |
| Ekstensi PHP | `gd` (dengan dukungan **WebP**), `pdo_mysql`, `mbstring`, `intl`, `fileinfo`, `bcmath`, `zip`, `openssl`, `ctype`, `tokenizer`, `xml`, `curl` | `gd` wajib untuk kompres & crop foto |
| Fungsi PHP | `symlink`, `proc_open` tidak diblokir | `symlink` dipakai `storage:link` |
| Terminal / SSH | Sangat disarankan | Tanpa terminal ada cara alternatif (lihat bagian 6b) |

**Pengaturan PHP** (cPanel → *Select PHP Version* → *Options*, atau *MultiPHP INI Editor*):

| Opsi | Nilai disarankan | Alasan |
| --- | --- | --- |
| `memory_limit` | `512M` | Kompres/crop foto beresolusi besar butuh memori |
| `upload_max_filesize` | `10M` | Foto maks 3 MB, PDF warta maks 5 MB |
| `post_max_size` | `12M` | Harus lebih besar dari `upload_max_filesize` |
| `max_execution_time` | `60` | Proses kompres foto |

> Cek dukungan WebP di GD: buat file sementara `info.php` berisi `<?php phpinfo();`, buka di browser, cari bagian **gd → WebP Support: enabled**. **Hapus file itu setelah dicek.**

---

## 2. Persiapan di komputer lokal

1. Build aset tampilan (hasilnya di `public/build`, **wajib ikut diunggah** karena hosting biasanya tidak punya Node.js):

   ```bash
   npm install
   npm run build
   ```

2. Pastikan semua test lulus:

   ```bash
   php artisan test --compact
   ```

3. Siapkan paket untuk diunggah. Pilih salah satu:

   - **A. Hosting punya Terminal/SSH (disarankan):** unggah kode **tanpa** folder `vendor` dan `node_modules`, lalu jalankan `composer install` di server (langkah 6).
   - **B. Tanpa Terminal:** buat salinan proyek di folder lain, jalankan di salinan itu:

     ```bash
     composer install --no-dev --optimize-autoloader
     ```

     lalu kompres salinan tersebut **termasuk** `vendor` dan `public/build`.

   Jangan ikut sertakan: `node_modules/`, `.env`, `storage/logs/*.log`, `tests/` (opsional), `.git/` (opsional), isi `storage/app/public` milik data contoh lokal.

---

## 3. Unggah kode & arahkan domain

Kode Laravel **tidak boleh** seluruhnya berada di `public_html`; hanya isi folder `public` yang boleh diakses publik.

1. Buat folder di luar `public_html`, misalnya `/home/akunparoki/paroki`.
2. Unggah & ekstrak paket ke folder tersebut (cPanel → *File Manager* → *Upload* → *Extract*).
3. Arahkan domain ke folder `public`:

   - **Cara utama (disarankan):** cPanel → *Domains* → pilih domain → *Manage* → ubah **Document Root** menjadi `/home/akunparoki/paroki/public`.
   - **Jika document root domain utama tidak bisa diubah:** salin **isi** folder `paroki/public` ke `public_html`, lalu ubah `public_html/index.php`:

     ```php
     // sebelumnya: __DIR__.'/../storage/...', '/../vendor/...', '/../bootstrap/...'
     if (file_exists($maintenance = __DIR__.'/../paroki/storage/framework/maintenance.php')) {
         require $maintenance;
     }

     require __DIR__.'/../paroki/vendor/autoload.php';

     $app = require_once __DIR__.'/../paroki/bootstrap/app.php';
     ```

     Pada cara ini, setiap kali `public/build` berubah, salin ulang ke `public_html/build`.

4. Izin folder: `storage` dan `bootstrap/cache` harus bisa ditulis (biasanya folder `755`, file `644` sudah cukup di cPanel).

---

## 4. Buat database

cPanel → *MySQL® Databases*:

1. **Create New Database**, contoh `paroki` → nama lengkap menjadi `akunparoki_paroki`.
2. **Add New User**, contoh `paroki` + kata sandi kuat → `akunparoki_paroki`.
3. **Add User To Database** → centang **ALL PRIVILEGES**.

Catat nama database, user, dan kata sandi untuk `.env`.

---

## 5. Isi file `.env`

Salin `.env.example` menjadi `.env` di folder `paroki`, lalu ubah minimal bagian berikut:

```dotenv
APP_NAME="Paroki St. Yoseph Bambu Pemali"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://parokibambupemali.org

APP_TIMEZONE=Asia/Jayapura
APP_LOCALE=id

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=akunparoki_paroki
DB_USERNAME=akunparoki_paroki
DB_PASSWORD=kata-sandi-database

FILESYSTEM_DISK=public
# Hanya bila website di subfolder (contoh domain.com/paroki):
# FILESYSTEM_PUBLIC_URL=/paroki/storage

# Email untuk "Lupa kata sandi" (buat akun email di cPanel → Email Accounts)
MAIL_MAILER=smtp
MAIL_HOST=mail.parokibambupemali.org
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=sekretariat@parokibambupemali.org
MAIL_PASSWORD=kata-sandi-email
MAIL_FROM_ADDRESS="sekretariat@parokibambupemali.org"
```

> **`APP_DEBUG` wajib `false`** di hosting. Jika `true`, detail error (termasuk isi `.env`) bisa terlihat publik.
> `APP_KEY` diisi otomatis oleh `php artisan key:generate` (langkah 6). Jangan menyalin `APP_KEY` dari komputer lokal lalu menggantinya belakangan; sesi login akan putus.

---

## 6a. Perintah instalasi (dengan Terminal/SSH)

cPanel → *Terminal* (atau SSH), lalu:

```bash
cd ~/paroki

# Hanya untuk Cara A (vendor belum diunggah):
composer install --no-dev --optimize-autoloader

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force        # akun admin + 1 baris profil paroki (aman diulang)
php artisan storage:link
php artisan optimize               # cache config, route, view
```

**Jangan** menjalankan `php artisan db:seed --class=ContohDataSeeder` di hosting; seeder itu hanya berisi data contoh untuk uji tampilan.

Perintah perusak data seperti `migrate:fresh` otomatis diblokir saat `APP_ENV=production`.

## 6b. Tanpa Terminal

Gunakan cPanel → *Cron Jobs* untuk menjalankan perintah **sekali**:

1. Tambah cron dengan jadwal "Once Per Minute", perintah:

   ```bash
   cd /home/akunparoki/paroki && php artisan key:generate --force && php artisan migrate --force && php artisan db:seed --force && php artisan storage:link && php artisan optimize >> storage/logs/instalasi.log 2>&1
   ```

2. Tunggu 1–2 menit, cek `storage/logs/instalasi.log` lewat File Manager.
3. **Hapus cron tersebut** setelah berhasil (agar tidak berjalan terus).

Jika `php` di cron bukan versi yang benar, gunakan path lengkap, contoh `/opt/cpanel/ea-php83/root/usr/bin/php` (lihat versi di *MultiPHP Manager*).

Jika `storage:link` gagal karena fungsi `symlink` diblokir, buat link lewat cron sekali jalan:

```bash
ln -s /home/akunparoki/paroki/storage/app/public /home/akunparoki/paroki/public/storage
```

(Untuk cara `public_html`, tujuan link-nya `/home/akunparoki/public_html/storage`.)

---

## 7. SSL & pemeriksaan akhir

1. cPanel → *SSL/TLS Status* → jalankan **AutoSSL** (Let's Encrypt) untuk domain.
2. Buka `https://parokibambupemali.org`, cek beranda, jadwal misa, warta, galeri.
3. Login di `https://parokibambupemali.org/login`:
   - Email: `admin@gerejabampel.test`
   - Kata sandi: `password`
4. **Segera** amankan akun admin lewat menu pengguna (pojok kiri bawah) → *Pengaturan*, **dengan urutan ini**:
   1. **Pastikan SMTP berfungsi**: logout, buka *Lupa kata sandi*, masukkan email admin saat ini, lalu cek di *Mail Delivery Reports* cPanel / log bahwa email benar-benar terkirim. Jika gagal, perbaiki `MAIL_*` di `.env` dulu.
   2. *Keamanan*: ganti ke **kata sandi baru** yang kuat.
   3. Baru setelah itu *Profil*: ganti nama & **email asli sekretariat** (dipakai untuk "Lupa kata sandi"), lalu buka kotak masuk email tersebut dan klik link verifikasinya.

   > **Kenapa harus berurutan?** Akun admin memakai verifikasi email. Begitu email diganti, status akun menjadi *belum terverifikasi* dan halaman **Keamanan terkunci** sampai link verifikasi di email baru diklik. Jika SMTP belum jalan, email verifikasi tidak pernah sampai dan kata sandi `password` tidak bisa diganti dari dashboard.
5. Isi **Admin → Profil Paroki** (nama, foto, sejarah, kontak, WhatsApp, media sosial), lalu jadwal misa, pengurus, lingkungan.
6. Uji upload satu foto dan satu PDF warta, lalu pastikan tampil di website.

**Cron & queue:** tidak diperlukan. Website ini tidak punya tugas terjadwal maupun antrean job.

---

## Memperbarui website (update)

1. **Backup dulu** (lihat bagian Backup).
2. Aktifkan mode perbaikan (pengunjung melihat halaman "Sedang Dalam Perbaikan"):

   ```bash
   cd ~/paroki && php artisan down
   ```

3. Unggah file yang berubah (atau `git pull`), termasuk `public/build` hasil `npm run build` terbaru.
4. Jalankan:

   ```bash
   composer install --no-dev --optimize-autoloader   # bila composer.json/lock berubah
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   php artisan up
   ```

---

## Backup (minimal sebulan sekali)

Yang wajib dicadangkan:

| Data | Cara |
| --- | --- |
| Database | cPanel → *phpMyAdmin* → pilih database → *Export* (format SQL) |
| Foto & PDF upload | Unduh folder `paroki/storage/app/public` (File Manager → *Compress* → unduh) |
| File `.env` | Unduh & simpan di tempat aman (berisi kata sandi) |

Alternatif: cPanel → *Backup* → *Download a Home Directory Backup* dan *Download a MySQL Database Backup*.

Simpan salinan backup di luar hosting (komputer sekretariat / Google Drive paroki).

---

## Mengatasi masalah

| Gejala | Penyebab umum | Solusi |
| --- | --- | --- |
| Halaman "Terjadi Kesalahan" (500) | Izin folder, `.env` salah, ekstensi PHP kurang | Lihat `storage/logs/laravel.log`; cek `storage` & `bootstrap/cache` bisa ditulis |
| Tampilan berantakan / "Vite manifest not found" | Folder `public/build` belum diunggah | Jalankan `npm run build` di lokal, unggah `public/build` |
| Foto & PDF tidak tampil | Link `public/storage` belum ada | `php artisan storage:link` atau buat symlink manual (6b) |
| Foto tidak tampil, website di subfolder | URL file salah | Isi `FILESYSTEM_PUBLIC_URL=/subfolder/storage` di `.env`, lalu `php artisan optimize` |
| "Allowed memory size ... exhausted" saat upload foto | `memory_limit` terlalu kecil | Naikkan ke `512M` (bagian 1) |
| Upload gagal untuk file besar | `upload_max_filesize` / `post_max_size` kecil | Naikkan sesuai tabel bagian 1 |
| "Sesi Berakhir" (419) terus-menerus | `APP_URL` tidak sama dengan alamat yang dibuka (http vs https / www) | Samakan `APP_URL`, lalu `php artisan optimize` |
| Email lupa kata sandi tidak terkirim | Pengaturan SMTP salah | Cek `MAIL_*` di `.env`, coba port 587 + `MAIL_SCHEME=null` |
| Perubahan `.env` tidak berpengaruh | Config masih di-cache | `php artisan optimize:clear` lalu `php artisan optimize` |

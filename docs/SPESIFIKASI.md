# Spesifikasi Sistem Informasi Gereja Bampel

Ringkasan hasil diskusi perencanaan (Oktober 2026). Ikuti dokumen ini saat menulis kode. Jika ada yang ingin diubah dari keputusan di sini, tanyakan dulu ke Jm.

## 1. Gambaran Umum

- Website informasi paroki Katolik di Merauke, Papua Selatan.
- Umat hanya **membaca** (tanpa login). Pengelola hanya **satu peran: Admin** (sekretariat). Tidak ada superadmin, tidak ada role/permission. Pastor tidak login; laporan diterima langsung dari sekretariat.
- Prinsip utama: **sederhana, ringan, mudah dirawat sekretariat.** Jangan menambah fitur atau tabel di luar daftar ini tanpa persetujuan.
- Prinsip database: **satu fitur = satu tabel.** Total 9 tabel utama.
- Pengguna mengakses lewat HP dengan internet yang bisa lambat → halaman publik harus ringan, gambar dikompres.

## 2. Stack

| Bagian | Pilihan |
| --- | --- |
| Framework | Laravel 13 (Livewire starter kit: Livewire 4, Flux, Fortify) |
| Dashboard admin | Livewire (+ komponen Flux) |
| Halaman publik | Livewire full-page component (`Route::livewire`), tanpa controller. Halaman publik hanya menampilkan data; hindari `wire:poll` dan interaksi berat |
| Database | MySQL (Laragon), nama database `SI_GEREJA_BAMPEL` |
| Penyimpanan file | Disk `public` (`php artisan storage:link`) |
| Zona waktu | `Asia/Jayapura` (WIT). Semua "hari ini" dihitung dengan zona ini |
| Bahasa | Indonesia (`APP_LOCALE=id`, nama hari/bulan Carbon berbahasa Indonesia) |
| Hosting nanti | Shared hosting cPanel |

## 3. Halaman Publik

### Navbar
- Beranda
- Tentang Kami ▾ (Profil Gereja, Pastor & Dewan Paroki, Lingkungan)
- Jadwal Misa
- Warta
- Kegiatan
- Galeri

Kontak **tidak** di navbar: tampil di bagian bawah Beranda dan di footer semua halaman. Ada **tombol WhatsApp sekretariat melayang** di pojok kanan bawah semua halaman (nomor dari `settings.whatsapp`, pakai accessor `whatsapp_url`).

### Isi Beranda (urutan scroll)
1. **Hero**: foto gereja (`settings.foto_hero`), nama gereja, card Jadwal Misa, card Kegiatan
2. **Sekilas Profil Gereja**: `foto_gereja` + `sejarah_singkat`, tombol *Selengkapnya* → /profil
3. **Kegiatan Terdekat**: 4 kegiatan, tombol *Lihat semua*
4. **Warta Terbaru**: 4 warta terbit terakhir, tombol *Lihat semua*
5. **Galeri**: 6 foto terbaru, tombol *Lihat semua*
6. **Kontak & Lokasi**: alamat, telepon, **kotak jam pelayanan sekretariat yang menonjol**, link Google Maps
7. **Footer**: kontak singkat, jam pelayanan sekretariat, link menu

### Daftar route publik

| Halaman | Route | Isi |
| --- | --- | --- |
| Beranda | `/` | Ringkasan semua bagian |
| Profil Gereja | `/profil` | Sejarah, visi, misi, pelindung (foto + kisah), alamat |
| Pastor & Dewan Paroki | `/dewan-paroki` | Perkenalan pastor (foto, data singkat, motto, kisah) di atas, lalu pengurus terkini, urut `urutan` |
| Lingkungan | `/lingkungan` | Peta arsiran lingkungan (Leaflet; mode Jalan/Satelit/Satelit + Label/Topografi; warna per lingkungan), lalu kartu lingkungan menyamping (geser) urut `urutan`, tanpa pengelompokan wilayah. Ketuk kartu ↔ sorot arsiran. Library peta hanya dimuat bila ada arsiran |
| Jadwal Misa | `/jadwal-misa` | Misa hari raya di atas (anchor `#hari-raya`), misa rutin per hari, jam sekretariat di bagian bawah |
| Warta | `/warta` | Daftar warta terbit, klik membuka PDF. Paginasi 10 |
| Kegiatan | `/kegiatan` | "Akan Datang" + "Kegiatan Sebelumnya" (paginasi 9) |
| Detail Kegiatan | `/kegiatan/{slug}` | Foto, tanggal, jam, tempat, deskripsi |
| Galeri | `/galeri` | Grid foto, paginasi 12 |

## 3b. Pola Kode

- **Tidak memakai controller.** Semua halaman (publik dan admin) adalah Livewire full-page component yang didaftarkan dengan `Route::livewire(...)`, mengikuti pola yang sudah ada di `routes/settings.php` starter kit.
- Halaman publik memakai layout publik sendiri (navbar + footer + tombol WhatsApp), terpisah dari layout dashboard admin.
- Paginasi di halaman publik memakai `WithPagination` Livewire.

## 4. Dashboard Admin

Semua di prefix `/admin`, middleware `auth`. **Registrasi dimatikan** (sudah dihapus dari `config/fortify.php`). Tidak ada menu kelola user; akun admin dibuat lewat `AdminSeeder`.

| Menu | Route | Tabel |
| --- | --- | --- |
| Login & Lupa Password | `/login`, `/forgot-password` | `users` (bawaan Fortify) |
| Dashboard Ringkasan | `/admin` | – |
| Profil Gereja, Kontak & Card Hero | `/admin/profil-gereja` | `settings` |
| Jadwal Misa | `/admin/jadwal-misa` | `jadwal_misas` |
| Warta | `/admin/warta` | `wartas` |
| Kegiatan Gereja | `/admin/kegiatan` | `kegiatans` |
| Pastor Paroki | `/admin/pastor-paroki` | `pastors` |
| Dewan Paroki | `/admin/dewan-paroki` | `pengurus` |
| Lingkungan | `/admin/lingkungan` | `lingkungans` |
| Galeri | `/admin/galeri` | `galeris` |
| Profil Saya | pakai halaman settings bawaan starter kit | `users` |

Pola setiap halaman kelola: tabel daftar + pencarian + tombol tambah/edit membuka **modal form** + hapus dengan konfirmasi. Jadwal Misa dan Kegiatan punya **toggle aktif langsung di tabel daftar** (sekali klik, tanpa buka form).

Dashboard Ringkasan: jumlah misa aktif, kegiatan akan datang, warta terbit bulan ini, total foto galeri, 5 data terakhir yang diubah.

## 5. Database (9 tabel) — SUDAH DIBUAT

Migration ada di `database/migrations/2026_10_11_00000{1-7}_*.php`. Relasi hanya `created_by` / `updated_by` → `users` (nullable, nullOnDelete).

- **users** — bawaan starter kit.
- **settings** (selalu 1 baris): nama_gereja, pelindung, foto_pelindung, kisah_pelindung (ditambahkan atas permintaan Jm, Okt 2026), logo, foto_hero, foto_gereja, sejarah_singkat, sejarah, visi, misi, alamat, telepon, whatsapp, email, link_maps, facebook, instagram, youtube, jam_sekretariat (teks, satu baris per hari), tampil_card_misa (bool), tampil_card_kegiatan (bool).
- **jadwal_misas**: jenis enum(rutin, khusus), nama_perayaan (wajib jika khusus), hari tinyint 1–7 (wajib jika rutin; 1=Senin … 7=Minggu, sama dengan `dayOfWeekIso`), tanggal (wajib jika khusus), jam, tempat, aktif, created_by, updated_by. **Tidak ada kolom keterangan** (sengaja dihapus).
- **wartas**: judul, slug unik, tanggal, ringkasan, file_pdf, status enum(draft, terbit), created_by, updated_by.
- **kegiatans**: judul, slug unik, tanggal, jam (nullable), tempat, deskripsi, foto (nullable → ikon default), aktif, created_by, updated_by.
- **pengurus**: nama, jabatan, bidang, foto, periode (string, contoh "2024-2027"), urutan. **Tidak ada arsip periode**; hanya pengurus terkini.
- **pastors** (ditambahkan atas permintaan Jm, 15 Okt 2026): nama, jabatan (Pastor Paroki/Pastor Rekan), tarekat, foto, asal, tanggal_lahir, tanggal_tahbisan, mulai_bertugas (tahun), motto, cerita (latar belakang & kisah, paragraf dipisah baris kosong), urutan. Hanya pastor yang sedang bertugas.
- **lingkungans**: nama, wilayah (kolom teks; sejak 15 Okt 2026 tidak dipakai lagi di form admin maupun halaman publik atas permintaan Jm, kolom dibiarkan), ketua (nama saja, tanpa nomor HP), jumlah_kk, jadwal_doa, cakupan_jalan (teks, satu jalan per baris), batas_wilayah (json nullable, titik sudut arsiran `[[lat, lng], ...]`, digambar admin di peta; ditambahkan atas permintaan Jm, 15 Okt 2026), urutan.
- **galeris**: judul, foto, tanggal. Satu baris = satu foto; upload banyak foto sekaligus membuat banyak baris.

## 6. Kode Backend yang SUDAH ADA

- **Trait** di `app/Concerns/`:
  - `HasPencatat` — isi `created_by`/`updated_by` otomatis dari user login; relasi `pembuat()`, `pengubah()`.
  - `HasSlug` — slug unik otomatis dari `judul`; route key = `slug`.
  - `HasFileCleanup` — hapus file lama di disk public saat file diganti/data dihapus; model mendefinisikan `protected array $fileColumns`; helper `fileUrl($kolom)`.
- **Model** di `app/Models/`: `Setting`, `JadwalMisa`, `Warta`, `Kegiatan`, `Pengurus` (`$table = 'pengurus'`), `Lingkungan`, `Galeri`.
  - `Setting::current()` — ambil baris settings (di-cache, cache dihapus saat disimpan). Accessor: `whatsapp_url`, `daftar_jam_sekretariat`.
  - `JadwalMisa`: konstanta `RUTIN`, `KHUSUS`, `HARI`; scope `aktif()`, `rutin()`, `khusus()`, `akanDatang(?int $hari)`, `urutDariHariIni()`; accessor `nama_hari`, `jam_format`; saat disimpan kolom yang tidak relevan dengan jenis dikosongkan otomatis; `JadwalMisa::untukCardHero()`.
  - `Warta`: scope `terbit()`, `terbaru()`; accessor `pdf_url`.
  - `Kegiatan`: scope `aktif()`, `akanDatang()`, `sebelumnya()`; accessor `foto_url`, `jam_format`; `Kegiatan::untukCardHero()`.
  - `Pengurus`, `Lingkungan`: scope `urut()`. `Lingkungan` accessor `daftar_jalan`.
  - `Galeri`: scope `terbaru()`; accessor `foto_url`.
- **Seeder**: `AdminSeeder` (admin@gerejabampel.test / password), `SettingSeeder` (1 baris settings), `ContohDataSeeder` (data contoh untuk uji tampilan, dijalankan terpisah). `DatabaseSeeder` memanggil Admin + Setting.
- **Konfigurasi**: `config/app.php` timezone dari `APP_TIMEZONE` (default Asia/Jayapura); `AppServiceProvider` set locale Carbon; registrasi Fortify dimatikan.

## 7. Aturan Logika

Data **nonaktif tidak pernah tampil di publik**. Data dengan tanggal yang sudah lewat otomatis tersembunyi dari hero.

### Card Jadwal Misa (Hero)
1. Tampil hanya jika `settings.tampil_card_misa = true`.
2. Cari misa khusus, aktif, tanggal antara hari ini s/d +14 hari, urut tanggal lalu jam, maksimal 4 → judul **"Jadwal Misa Hari Raya"**.
3. Jika kosong → misa rutin aktif, urut mulai dari hari ini → judul **"Jadwal Misa Minggu Ini"**.
4. Jika tetap kosong → tulisan *Lihat semua jadwal*.
5. Klik card → `/jadwal-misa` (ke `#hari-raya` jika yang tampil misa khusus).
6. **Tanpa foto**: hanya daftar teks (nama/hari · tanggal · jam). Sudah tersedia: `JadwalMisa::untukCardHero()`.

### Card Kegiatan (Hero)
1. Tampil hanya jika `settings.tampil_card_kegiatan = true`.
2. 1 kegiatan aktif terdekat dengan tanggal ≥ hari ini. Thumbnail foto kecil, atau ikon default bila kosong.
3. Klik → `/kegiatan/{slug}`. Jika kosong → *Lihat semua kegiatan*. Sudah tersedia: `Kegiatan::untukCardHero()`.

### Aturan status
| Status | Card Hero | Halaman publik |
| --- | --- | --- |
| Aktif | Tampil | Tampil |
| Nonaktif | Tidak tampil | Tidak tampil |

- Misa khusus yang lewat tanggal hilang dari publik meski masih aktif (datanya bisa diedit tanggalnya untuk dipakai tahun depan).
- Misa rutin yang ditiadakan sementara (misal saat Paskah) → admin nonaktifkan lewat toggle, lalu aktifkan lagi.
- Kegiatan yang lewat tanggal hilang dari hero, pindah ke "Kegiatan Sebelumnya" di halaman Kegiatan.
- Warta hanya tampil jika `status = terbit`.

### Validasi form
- Jadwal Misa: jenis rutin → `hari` wajib (1–7); jenis khusus → `tanggal` dan `nama_perayaan` wajib. Form Livewire menampilkan field sesuai jenis yang dipilih.
- Warta: `file_pdf` wajib saat tambah, mimes:pdf, maks 5 MB.
- Foto (kegiatan, pengurus, galeri, settings): image, maks 3 MB, lalu kompres ke WebP lebar maks 1600 px (pakai Intervention Image v4, driver GD — sudah di-install).
- Slug otomatis & unik (sudah ditangani `HasSlug`).

## 8. Privasi & Perawatan

- Nomor HP ketua lingkungan dan pengurus **tidak** ditampilkan di publik. Semua kontak diarahkan ke sekretariat.
- Backup database dan folder `storage` minimal sebulan sekali.
- Nanti buat panduan admin 1–2 halaman (upload warta, tambah misa hari raya, aktif/nonaktif data).

## 9. Ditunda (JANGAN dibuat sekarang)

Info sakramen, unduh formulir, cari lingkungan berdasarkan nama jalan, data umat per KK, donasi online, login umat, aplikasi mobile, notifikasi WA otomatis.

## 10. Status & Langkah Berikutnya

Sudah:
- [x] Migration 7 tabel, model, trait, seeder
- [x] Timezone, locale, registrasi dimatikan

Belum (kerjakan berurutan):
- [x] Ubah `.env`: `DB_CONNECTION=mysql`, `DB_DATABASE=SI_GEREJA_BAMPEL`, `DB_USERNAME=root`, `DB_PASSWORD=` (kosong), `APP_NAME="Gereja Bampel"`, `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jayapura`, `FILESYSTEM_DISK=public`
- [x] Buat database `SI_GEREJA_BAMPEL`, jalankan `php artisan migrate --seed`, `php artisan db:seed --class=ContohDataSeeder`, `php artisan storage:link`
- [x] Install Intervention Image untuk kompres foto ke WebP (v4, driver GD)
- [x] Route `/admin` + layout sidebar admin (pakai layout starter kit)
- [x] Komponen Livewire kelola: Settings, Jadwal Misa, Warta, Kegiatan, Pengurus, Lingkungan, Galeri, Dashboard Ringkasan
- [x] Halaman publik (Livewire full-page component) sesuai bagian 3
- [x] Hapus link/route "Register" yang masih muncul di view starter kit

Sudah juga (dikerjakan setelah checklist di atas):
- [x] Tag Open Graph & meta deskripsi di layout publik (gambar cadangan `public/images/og-default.png`)
- [x] Halaman error Bahasa Indonesia (401, 403, 404, 419, 429, 500, 503) di `resources/views/errors/`
- [x] `.env.example` diperbarui
- [x] `docs/DEPLOY.md` (panduan cPanel)
- [x] `AdminSeeder` memakai `firstOrCreate`

## 11. Perbaikan Tahap 2 (hasil review kode, Oktober 2026)

Kerjakan **berurutan**. Setiap poin: perbaiki → tambah/ubah test → jalankan `php artisan test` → centang. Jangan mengubah hal lain di luar poin yang sedang dikerjakan.

### A. Penting

- [x] **A1. Slug tidak boleh berubah saat judul diedit.**
  Masalah: `app/Concerns/HasSlug.php` membuat slug baru setiap `judul` berubah, jadi link kegiatan yang sudah dibagikan ke WhatsApp menjadi 404.
  Solusi: buat slug hanya saat `creating` (dan hanya bila `slug` masih kosong). Hapus kondisi `isDirty('judul')`.
  Test: buat kegiatan, ubah judulnya, pastikan slug tetap sama dan URL lama masih 200.

- [x] **A2. Thumbnail foto ringan untuk internet lambat.**
  Masalah: grid galeri, galeri beranda, kartu kegiatan, dan foto pengurus memakai foto penuh (lebar 1600 px).
  Solusi (tanpa kolom baru):
  - Di `app/Concerns/KompresFoto.php` (`simpanFotoWebp`), setelah menyimpan foto utama, simpan juga versi kecil lebar maks **600 px** dengan nama yang sama ditambah akhiran `_kecil` (contoh `galeri/abc.webp` → `galeri/abc_kecil.webp`).
  - Di `app/Concerns/HasFileCleanup.php`, saat file dihapus/diganti, hapus juga pasangan `_kecil`-nya.
  - Tambah helper `fileUrlKecil($kolom)` di `HasFileCleanup` yang mengembalikan URL versi kecil bila ada, jika tidak ada kembali ke foto penuh (agar data lama tetap tampil). Tambah accessor `foto_kecil_url` di `Galeri`, `Kegiatan`, `Pengurus`.
  - Pakai versi kecil di: grid `pages/publik/⚡galeri.blade.php`, galeri di `⚡beranda.blade.php`, kartu kegiatan (`components/publik/kartu-kegiatan.blade.php` dan card hero), foto di `⚡dewan-paroki.blade.php` publik, dan tabel/preview di halaman admin. Foto penuh tetap dipakai di lightbox galeri, hero, dan halaman detail kegiatan.
  - Opsional: perintah artisan `foto:buat-thumbnail` untuk membuat versi kecil dari foto yang sudah ada.
  Test: upload foto → kedua file ada di disk; hapus data → kedua file terhapus.

- [x] **A3. Tombol "Hapus foto".**
  Masalah: foto kegiatan, foto pengurus, serta logo/foto hero/foto gereja/foto pelindung di Profil Paroki hanya bisa diganti, tidak bisa dikosongkan.
  Solusi: tambah tombol "Hapus foto" (dengan konfirmasi) di form masing-masing yang mengisi kolom menjadi `null`. File lama otomatis terhapus lewat `HasFileCleanup`. Bisa dibuat sebagai opsi di komponen `<x-admin.unggah-foto>` agar dipakai ulang.
  Test: hapus foto → kolom null, file di disk terhapus, halaman publik memakai gambar/ikon default.

- [x] **A4. Upload galeri aman di shared hosting.**
  Masalah: sampai 20 foto dikompres dalam satu request; di hosting `max_execution_time` biasanya 30 detik sehingga bisa berhenti di tengah.
  Solusi: di `pages/admin/⚡galeri.blade.php` ubah batas `fotos` menjadi `max:10` (dan teks bantuannya), lalu panggil `set_time_limit(120)` di awal `simpan()`.
  Test: validasi menolak 11 foto.

### B. Bug kecil

- [x] **B1. Hitungan "Misa aktif" di dashboard.** Di `pages/admin/⚡dashboard.blade.php`, hitung `JadwalMisa::aktif()->rutin()->count()` + `JadwalMisa::aktif()->khusus()->whereDate('tanggal', '>=', today())->count()` (misa hari raya yang sudah lewat tidak dihitung). Test di `DashboardRingkasanTest`.

- [x] **B2. "Misa berikutnya" ikut mempertimbangkan misa hari raya.** Di `pages/publik/⚡jadwal-misa.blade.php` (`misaBerikutnya`), bandingkan misa rutin berikutnya dengan misa khusus aktif terdekat (tanggal + jam ≥ sekarang), tampilkan yang paling dekat. Sesuaikan teks "Hari ini / Besok / tanggal" untuk misa khusus. Test dengan `Carbon::setTestNow()`.

- [x] **B3. Card hero "Jadwal Misa Minggu Ini"** jangan menampilkan misa rutin hari ini yang jamnya sudah lewat. Ubah `JadwalMisa::untukCardHero()` di `app/Models/JadwalMisa.php`: misa hari ini dengan `jam` < sekarang dipindah ke urutan paling akhir (minggu depan) atau dilewati. Test dengan `Carbon::setTestNow()`.

- [x] **B4. Statistik "Wilayah" di halaman Lingkungan** jangan menghitung kelompok "Lainnya" (lingkungan tanpa wilayah). File: `pages/publik/⚡lingkungan.blade.php`.

### C. Kenyamanan admin & umat

- [x] **C1. Daftar Jadwal Misa di admin:** tampilkan badge "Sudah lewat" untuk misa khusus yang tanggalnya < hari ini. Pencarian juga menemukan misa rutin berdasarkan nama hari (ubah kata "Minggu", "Senin", dst. ke angka 1–7 lalu cari di kolom `hari`).

- [x] **C2. Nama file PDF warta yang mudah dibaca.** Simpan dengan `storeAs('warta', Str::slug($judul).'-'.Str::lower(Str::random(6)).'.pdf', 'public')` di `pages/admin/⚡warta.blade.php`. File lama tidak perlu diubah.

- [x] **C3. Ikon media sosial yang kosong disembunyikan** di footer `layouts/publik.blade.php` (hapus tampilan "segera hadir").

### D. Sisa pekerjaan sebelumnya

- [ ] **D1. Favicon paroki:** ganti `public/favicon.ico`, `public/favicon.svg`, `public/apple-touch-icon.png` dengan logo paroki (minta file logo ke Jm bila belum ada; jika belum ada, lewati dan beri catatan).
  *Catatan (12 Okt 2026): dilewati. Belum ada file logo resmi; kolom `settings.logo` saat ini berisi gambar Santo Yoseph (400×400 WebP), bukan logo. Layout publik sudah memakai logo upload sebagai ikon tab bila ada. Kirim file logo (sebaiknya SVG atau PNG persegi ≥ 512 px) untuk membuat ketiga file favicon.*
- [x] **D2. DEPLOY.md bagian 7:** tambahkan urutan aman setelah online: (1) pastikan SMTP berfungsi, (2) ganti kata sandi admin, (3) baru ganti email admin. Alasan: model `User` memakai verifikasi email, dan mengganti email membuat status belum terverifikasi sehingga halaman Keamanan terkunci sampai email verifikasi diklik.
- [x] **D3. Bersihkan sisa registrasi:** hapus `tests/Feature/Auth/RegistrationTest.php`, `resources/views/pages/auth/register.blade.php`, `app/Actions/Fortify/CreateNewUser.php`, serta baris `Fortify::registerView(...)` dan `Fortify::createUsersUsing(...)` di `app/Providers/FortifyServiceProvider.php`.
- [x] **D4. Akhir:** jalankan `php artisan test` dan `vendor/bin/pint`, pastikan semua lolos, lalu centang poin-poin di atas.

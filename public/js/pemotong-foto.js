/**
 * Editor crop foto ringan untuk dashboard admin (tanpa library tambahan).
 * Dimuat sebagai script biasa di resources/views/partials/head.blade.php (bukan lewat Vite),
 * supaya komponen Alpine terdaftar sebelum Livewire menyalakan Alpine.
 *
 * Pemakaian (lihat resources/views/components/admin/unggah-foto.blade.php):
 *   x-data="pemotongFoto({ kunci: 'foto_hero', src: '...', rasio: 16/9 })"
 *
 * Foto bisa digeser (drag) dan di-zoom di dalam bingkai ber-rasio tetap.
 * Hasilnya koordinat crop dalam piksel asli gambar, dikirim ke Livewire: $wire.set('potong.<kunci>', {x, y, w, h}).
 * Pratinjau hasil crop dibuat di browser (canvas) hanya untuk tampilan.
 */
const PILIHAN_RASIO = [
    { label: 'Asli', nilai: null },
    { label: '1:1', nilai: 1 },
    { label: '4:3', nilai: 4 / 3 },
    { label: '16:9', nilai: 16 / 9 },
    { label: '4:5', nilai: 4 / 5 },
];

const TINGGI_MAKS_BINGKAI = 420;

function daftarPemotongFoto(Alpine) {
    Alpine.data('pemotongFoto', ({ kunci, src, rasio = null }) => ({
        kunci,
        src,
        pilihanRasio: PILIHAN_RASIO,
        rasioAwal: rasio,
        rasio,

        mengedit: false,
        memuat: false,
        hasil: null, // dataURL pratinjau hasil crop

        // Ukuran asli gambar & bingkai
        natW: 0,
        natH: 0,
        bingkaiW: 0,
        bingkaiH: 0,

        // Tampilan: zoom (1 = memenuhi bingkai) dan posisi gambar di bingkai (px)
        zoom: 1,
        geserX: 0,
        geserY: 0,
        seret: null,

        buka() {
            if (! this.src) {
                return;
            }

            this.mengedit = true;
            this.memuat = true;

            const img = new Image();
            img.onload = () => {
                this.natW = img.naturalWidth;
                this.natH = img.naturalHeight;
                this.memuat = false;
                this.$nextTick(() => this.aturBingkai(true));
            };
            img.src = this.src;
        },

        batal() {
            this.mengedit = false;
        },

        pilihRasio(nilai) {
            this.rasio = nilai;
            this.$nextTick(() => this.aturBingkai(true));
        },

        /** Rasio efektif: pilihan pengguna, atau rasio asli gambar. */
        rasioEfektif() {
            return this.rasio ?? this.natW / this.natH;
        },

        /** Hitung ukuran bingkai mengikuti lebar wadah, batasi tingginya. */
        aturBingkai(reset = false) {
            const wadah = this.$refs.wadah;
            if (! wadah || ! this.natW) {
                return;
            }

            const r = this.rasioEfektif();
            let w = wadah.clientWidth;
            let h = w / r;

            if (h > TINGGI_MAKS_BINGKAI) {
                h = TINGGI_MAKS_BINGKAI;
                w = h * r;
            }

            this.bingkaiW = w;
            this.bingkaiH = h;

            if (reset) {
                this.zoom = 1;
                // Posisikan gambar di tengah
                this.geserX = (w - this.natW * this.skala()) / 2;
                this.geserY = (h - this.natH * this.skala()) / 2;
            }

            this.kunciPosisi();
        },

        /** Skala tampilan: gambar selalu menutupi bingkai, dikali zoom. */
        skala(zoom = this.zoom) {
            return Math.max(this.bingkaiW / this.natW, this.bingkaiH / this.natH) * zoom;
        },

        /** Jaga agar gambar selalu menutupi seluruh bingkai. */
        kunciPosisi() {
            const s = this.skala();
            const minX = this.bingkaiW - this.natW * s;
            const minY = this.bingkaiH - this.natH * s;

            this.geserX = Math.min(0, Math.max(minX, this.geserX));
            this.geserY = Math.min(0, Math.max(minY, this.geserY));
        },

        /** Ubah zoom dengan titik tengah bingkai tetap di tempat. */
        ubahZoom(nilaiBaru) {
            const lama = this.skala();
            const baru = this.skala(nilaiBaru);
            const cx = (this.bingkaiW / 2 - this.geserX) / lama;
            const cy = (this.bingkaiH / 2 - this.geserY) / lama;

            this.zoom = nilaiBaru;
            this.geserX = this.bingkaiW / 2 - cx * baru;
            this.geserY = this.bingkaiH / 2 - cy * baru;
            this.kunciPosisi();
        },

        gulir(event) {
            const nilai = Math.min(4, Math.max(1, this.zoom - event.deltaY * 0.002));
            this.ubahZoom(nilai);
        },

        mulaiSeret(event) {
            this.seret = { x: event.clientX, y: event.clientY, gx: this.geserX, gy: this.geserY };
            event.target.setPointerCapture?.(event.pointerId);
        },

        gerakSeret(event) {
            if (! this.seret) {
                return;
            }

            this.geserX = this.seret.gx + (event.clientX - this.seret.x);
            this.geserY = this.seret.gy + (event.clientY - this.seret.y);
            this.kunciPosisi();
        },

        selesaiSeret() {
            this.seret = null;
        },

        gayaGambar() {
            const s = this.skala();

            return `width:${this.natW * s}px;height:${this.natH * s}px;transform:translate(${this.geserX}px,${this.geserY}px)`;
        },

        /** Kirim koordinat crop (piksel asli) ke Livewire dan buat pratinjau. */
        terapkan() {
            const s = this.skala();
            const potong = {
                x: Math.round(-this.geserX / s),
                y: Math.round(-this.geserY / s),
                w: Math.round(this.bingkaiW / s),
                h: Math.round(this.bingkaiH / s),
            };

            this.$wire.set(`potong.${this.kunci}`, potong, false);
            this.buatPratinjau(potong);
            this.mengedit = false;
        },

        buatPratinjau(potong) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const lebar = Math.min(800, potong.w);
                const canvas = document.createElement('canvas');
                canvas.width = lebar;
                canvas.height = Math.round(lebar * (potong.h / potong.w));
                canvas.getContext('2d').drawImage(img, potong.x, potong.y, potong.w, potong.h, 0, 0, canvas.width, canvas.height);
                this.hasil = canvas.toDataURL('image/jpeg', 0.85);
            };
            img.src = this.src;
        },
    }));
}

// Daftarkan saat Alpine mulai; bila Alpine sudah berjalan, daftarkan langsung.
if (window.Alpine) {
    daftarPemotongFoto(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => daftarPemotongFoto(window.Alpine));
}

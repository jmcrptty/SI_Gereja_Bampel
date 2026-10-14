/**
 * Peta lingkungan (Leaflet). Hanya dimuat di halaman Lingkungan (publik & admin) lewat @assets.
 *
 * - window.PetaParoki.publik(el, opsi): semua arsiran lingkungan + sorot saat dipilih.
 * - window.PetaParoki.penggambar(el, opsi): editor arsiran untuk admin.
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/peta.css';

const ESRI = 'https://server.arcgisonline.com/ArcGIS/rest/services';
const ATRIBUSI_ESRI = 'Citra &copy; Esri, Maxar, Earthstar Geographics';

/** Pilihan mode peta. Semuanya gratis tanpa API key. */
function modePeta() {
    const jalan = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    });
    const citra = () => L.tileLayer(`${ESRI}/World_Imagery/MapServer/tile/{z}/{y}/{x}`, {
        maxZoom: 19,
        maxNativeZoom: 18,
        attribution: ATRIBUSI_ESRI,
    });
    const label = (layanan) => L.tileLayer(`${ESRI}/Reference/${layanan}/MapServer/tile/{z}/{y}/{x}`, {
        maxZoom: 19,
        maxNativeZoom: 18,
    });

    return {
        Jalan: jalan,
        Satelit: citra(),
        'Satelit + Label': L.layerGroup([citra(), label('World_Transportation'), label('World_Boundaries_and_Places')]),
        Topografi: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '&copy; OpenStreetMap, &copy; <a href="https://opentopomap.org">OpenTopoMap</a>',
        }),
    };
}

const KUNCI_MODE = 'peta-paroki-mode';

function bacaMode() {
    try {
        return localStorage.getItem(KUNCI_MODE);
    } catch {
        return null;
    }
}

function buat(el, pusat) {
    const peta = L.map(el, { scrollWheelZoom: false, zoomControl: false }).setView(pusat, 13);
    const mode = modePeta();

    // Mode terakhir yang dipilih pengunjung diingat di browser.
    (mode[bacaMode()] ?? mode.Jalan).addTo(peta);

    L.control.zoom({ position: 'topleft', zoomInTitle: 'Perbesar', zoomOutTitle: 'Perkecil' }).addTo(peta);
    L.control.layers(mode, null, { position: 'topright', collapsed: true }).addTo(peta);
    L.control.scale({ imperial: false }).addTo(peta);

    peta.on('baselayerchange', (e) => {
        try {
            localStorage.setItem(KUNCI_MODE, e.name);
        } catch {
            // Penyimpanan browser tidak tersedia: abaikan.
        }
    });

    // Zoom dengan scroll hanya setelah peta diklik, agar tidak mengganggu scroll halaman.
    peta.on('focus click', () => peta.scrollWheelZoom.enable());
    peta.on('blur mouseout', () => peta.scrollWheelZoom.disable());

    return peta;
}

const gayaBiasa = (warna) => ({ color: warna, weight: 2, fillColor: warna, fillOpacity: 0.25 });
const gayaSorot = (warna) => ({ color: warna, weight: 4, fillColor: warna, fillOpacity: 0.5 });

/**
 * @param {HTMLElement} el
 * @param {{ pusat: number[], daftar: Array<{id: number, nama: string, warna: string, titik: number[][]}>, pilih?: (id: number) => void }} opsi
 */
function publik(el, { pusat, daftar, pilih = () => {} }) {
    const peta = buat(el, pusat);
    const lapisan = new Map();
    let disorot = null;

    daftar.forEach((item) => {
        const poligon = L.polygon(item.titik, gayaBiasa(item.warna))
            .addTo(peta)
            .bindTooltip(escape(item.nama), { sticky: true, direction: 'top' })
            .on('click', () => {
                sorot(item.id, false);
                pilih(item.id);
            });

        lapisan.set(item.id, { poligon, warna: item.warna });
    });

    const semua = L.featureGroup([...lapisan.values()].map((l) => l.poligon));

    if (semua.getLayers().length) {
        peta.fitBounds(semua.getBounds(), { padding: [24, 24] });
    }

    function sorot(id, zoom = true) {
        if (disorot) {
            disorot.poligon.setStyle(gayaBiasa(disorot.warna));
        }

        disorot = lapisan.get(id) ?? null;

        if (! disorot) {
            return;
        }

        disorot.poligon.setStyle(gayaSorot(disorot.warna)).bringToFront();

        if (zoom) {
            peta.flyToBounds(disorot.poligon.getBounds(), { padding: [40, 40], maxZoom: 17, duration: 0.6 });
        }
    }

    return { peta, sorot };
}

/**
 * Editor arsiran:
 * - klik peta: tambah titik di akhir;
 * - geser titik: pindahkan;
 * - klik titik kecil di tengah sisi: sisipkan titik baru di situ;
 * - klik titik: pilih (lalu hapus lewat tombol), klik kanan / ketuk dua kali titik: hapus langsung.
 *
 * @param {HTMLElement} el
 * @param {{ pusat: number[], ubah: (titik: number[][]) => void, status: (info: {jumlah: number, terpilih: number|null, luas: number|null}) => void }} opsi
 */
function penggambar(el, { pusat, ubah, status }) {
    const WARNA = '#8b1e2d';
    const peta = buat(el, pusat);
    const latar = L.layerGroup().addTo(peta);
    const poligon = L.polygon([], { color: WARNA, weight: 3, fillColor: WARNA, fillOpacity: 0.3, interactive: false }).addTo(peta);
    const garisTutup = L.polyline([], { color: WARNA, weight: 2, dashArray: '6 6', interactive: false }).addTo(peta);
    const pegangan = L.layerGroup().addTo(peta);
    let titik = [];
    let riwayat = [];
    let terpilih = null;

    const ikonTitik = (nomor, aktif) => L.divIcon({
        className: '',
        html: `<div class="peta-titik${aktif ? ' peta-titik-aktif' : ''}">${nomor}</div>`,
        iconSize: [22, 22],
        iconAnchor: [11, 11],
    });
    const ikonTengah = L.divIcon({ className: '', html: '<div class="peta-titik-tengah">+</div>', iconSize: [16, 16], iconAnchor: [8, 8] });

    function simpanRiwayat() {
        riwayat.push(titik.map((t) => [...t]));
        riwayat = riwayat.slice(-50);
    }

    function gambar() {
        poligon.setLatLngs(titik.length >= 3 ? titik : []);
        // Selama belum 3 titik, tampilkan garis bantu agar admin tahu urutannya.
        garisTutup.setLatLngs(titik.length >= 2 && titik.length < 3 ? titik : []);
        pegangan.clearLayers();

        titik.forEach((t, i) => {
            const penanda = L.marker(t, { icon: ikonTitik(i + 1, i === terpilih), draggable: true, autoPan: true, zIndexOffset: 1000 })
                .on('dragstart', simpanRiwayat)
                .on('drag', (e) => {
                    titik[i] = [e.latlng.lat, e.latlng.lng];
                    poligon.setLatLngs(titik.length >= 3 ? titik : []);
                })
                .on('dragend', kirim)
                .on('click', () => {
                    terpilih = terpilih === i ? null : i;
                    kirim();
                })
                .on('contextmenu dblclick', (e) => {
                    L.DomEvent.stop(e);
                    hapusTitik(i);
                })
                .addTo(pegangan);

            penanda.bindTooltip(`Titik ${i + 1}`, { direction: 'top', offset: [0, -10] });
        });

        // Titik bantu di tengah setiap sisi untuk menyisipkan titik baru.
        if (titik.length >= 3) {
            titik.forEach((t, i) => {
                const berikut = titik[(i + 1) % titik.length];

                L.marker([(t[0] + berikut[0]) / 2, (t[1] + berikut[1]) / 2], { icon: ikonTengah, opacity: 0.85 })
                    .on('click', (e) => {
                        L.DomEvent.stop(e);
                        simpanRiwayat();
                        titik.splice(i + 1, 0, [e.latlng.lat, e.latlng.lng]);
                        terpilih = i + 1;
                        kirim();
                    })
                    .bindTooltip('Sisipkan titik', { direction: 'top' })
                    .addTo(pegangan);
            });
        }
    }

    function kirim() {
        gambar();
        ubah(titik.map(([lat, lng]) => [Number(lat.toFixed(6)), Number(lng.toFixed(6))]));
        status({ jumlah: titik.length, terpilih, luas: titik.length >= 3 ? luasHektar(titik) : null, bisaUrungkan: riwayat.length > 0 });
    }

    function hapusTitik(i) {
        simpanRiwayat();
        titik.splice(i, 1);
        terpilih = null;
        kirim();
    }

    peta.on('click', (e) => {
        simpanRiwayat();

        // Bila ada titik terpilih, titik baru disisipkan setelahnya; jika tidak, di akhir.
        const posisi = terpilih === null ? titik.length : terpilih + 1;
        titik.splice(posisi, 0, [e.latlng.lat, e.latlng.lng]);
        terpilih = terpilih === null ? null : posisi;
        kirim();
    });

    function arahkan() {
        if (titik.length) {
            peta.fitBounds(L.latLngBounds(titik), { padding: [30, 30], maxZoom: 17 });
        } else {
            peta.setView(pusat, 13);
        }
    }

    // Ukuran peta berubah (modal dibuka, layar penuh): hitung ulang agar potongan peta tidak abu-abu.
    new ResizeObserver(() => peta.invalidateSize()).observe(el);

    return {
        /**
         * Muat arsiran yang sedang diedit dan arsiran lingkungan lain (abu-abu, sebagai acuan).
         *
         * @param {number[][]} batas
         * @param {Array<{nama: string, titik: number[][]}>} lainnya
         */
        muat(batas, lainnya) {
            titik = (batas ?? []).map((t) => [...t]);
            riwayat = [];
            terpilih = null;
            latar.clearLayers();
            lainnya.forEach((l) => L.polygon(l.titik, { color: '#57534e', weight: 1.5, dashArray: '4 4', fillColor: '#78716c', fillOpacity: 0.15, interactive: false })
                .bindTooltip(escape(l.nama), { permanent: true, direction: 'center', className: 'peta-label-lain' })
                .addTo(latar));
            gambar();
            status({ jumlah: titik.length, terpilih, luas: titik.length >= 3 ? luasHektar(titik) : null, bisaUrungkan: false });

            // Peta di dalam modal: tunggu modal tampil, lalu arahkan ke arsiran.
            setTimeout(() => {
                peta.invalidateSize();
                arahkan();
            }, 250);
        },
        urungkan() {
            if (! riwayat.length) {
                return;
            }
            titik = riwayat.pop();
            terpilih = null;
            kirim();
        },
        hapusTerpilih() {
            if (terpilih !== null) {
                hapusTitik(terpilih);
            }
        },
        kosongkan() {
            simpanRiwayat();
            titik = [];
            terpilih = null;
            kirim();
        },
        arahkan,
        lokasiSaya() {
            peta.locate({ setView: true, maxZoom: 17 });
        },
    };
}

/**
 * Perkiraan luas poligon dalam hektar (proyeksi datar sederhana, cukup akurat untuk skala kota).
 *
 * @param {number[][]} titik
 */
function luasHektar(titik) {
    const latTengah = titik.reduce((jumlah, t) => jumlah + t[0], 0) / titik.length;
    const meterPerDerajatLng = 111320 * Math.cos((latTengah * Math.PI) / 180);
    const xy = titik.map(([lat, lng]) => [lng * meterPerDerajatLng, lat * 110540]);
    let luas = 0;

    xy.forEach(([x1, y1], i) => {
        const [x2, y2] = xy[(i + 1) % xy.length];
        luas += x1 * y2 - x2 * y1;
    });

    return Math.abs(luas) / 2 / 10000;
}

function escape(teks) {
    const div = document.createElement('div');
    div.textContent = teks ?? '';

    return div.innerHTML;
}

window.PetaParoki = { publik, penggambar };

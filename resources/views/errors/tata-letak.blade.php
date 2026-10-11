{{--
    Tata letak halaman error (Bahasa Indonesia).
    Sengaja TIDAK memakai database maupun Vite agar tetap tampil walau database mati
    atau aset build belum diunggah. Nama paroki diambil dari APP_NAME.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('judul') - {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: #3a2417; background: linear-gradient(180deg, #f7efe2 0%, #fdf9f3 60%, #fdf9f3 100%);
        }
        .kartu {
            position: relative; overflow: hidden; width: 100%; max-width: 560px; text-align: center;
            background: #fff; border-radius: 28px; padding: 48px 32px 40px;
            box-shadow: 0 20px 50px -20px rgba(58, 36, 23, .25); border: 1px solid #efe1cb;
        }
        .kartu::before {
            content: ''; position: absolute; inset: 0 0 auto 0; height: 4px;
            background: linear-gradient(90deg, #7a2222, #b13a3a, #b08a68);
        }
        .ikon {
            width: 64px; height: 64px; margin: 0 auto; border-radius: 9999px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(180deg, #b13a3a, #7a2222); color: #fdf9f3; box-shadow: 0 8px 20px -8px rgba(122, 34, 34, .6);
        }
        .kode {
            margin: 24px auto 0; font-family: 'Playfair Display', Georgia, 'Times New Roman', serif; font-weight: 700;
            font-size: 88px; line-height: 1; color: #7a2222; letter-spacing: -2px;
        }
        h1 { margin: 12px 0 0; font-family: 'Playfair Display', Georgia, serif; font-size: 28px; font-weight: 600; }
        p { margin: 12px auto 0; max-width: 420px; line-height: 1.6; color: #5c3d27; }
        .tombol { margin-top: 32px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .tombol a {
            display: inline-block; padding: 12px 24px; border-radius: 9999px; font-size: 14px; font-weight: 600; text-decoration: none;
        }
        .utama { background: #7a2222; color: #fff; box-shadow: 0 8px 20px -10px rgba(122, 34, 34, .7); }
        .utama:hover { background: #962d2d; }
        .kedua { color: #3a2417; border: 1px solid #e2cba9; }
        .kedua:hover { background: #f7efe2; }
        .kaki { margin-top: 32px; font-size: 13px; color: #8c6647; }
    </style>
</head>
<body>
    <main class="kartu">
        <div class="ikon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M10.5 2h3v6h6v3h-6v11h-3V11h-6V8h6z"/></svg>
        </div>

        <p class="kode">@yield('kode')</p>
        <h1>@yield('judul')</h1>
        <p>@yield('pesan')</p>

        <div class="tombol">
            <a href="{{ url('/') }}" class="utama">Kembali ke Beranda</a>
            @hasSection('tombol_kedua')
                @yield('tombol_kedua')
            @else
                <a href="javascript:history.back()" class="kedua">Halaman sebelumnya</a>
            @endif
        </div>

        <p class="kaki">{{ config('app.name') }}</p>
    </main>
</body>
</html>

@extends('errors.tata-letak')

@section('kode', '419')
@section('judul', 'Sesi Berakhir')
@section('pesan', 'Halaman sudah terlalu lama dibiarkan sehingga sesi Anda berakhir. Muat ulang halaman, lalu coba lagi.')

@section('tombol_kedua')
    <a href="javascript:location.reload()" class="kedua">Muat ulang</a>
@endsection

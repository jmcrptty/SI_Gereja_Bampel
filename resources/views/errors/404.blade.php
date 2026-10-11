@extends('errors.tata-letak')

@section('kode', '404')
@section('judul', 'Halaman Tidak Ditemukan')
@section('pesan', 'Maaf, halaman yang Anda cari tidak ada, sudah dipindahkan, atau alamatnya salah ketik. Silakan kembali ke beranda atau gunakan menu di website.')

@section('tombol_kedua')
    <a href="{{ url('/jadwal-misa') }}" class="kedua">Lihat Jadwal Misa</a>
@endsection

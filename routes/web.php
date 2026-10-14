<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::publik.beranda')->name('home');
Route::livewire('profil', 'pages::publik.profil')->name('profil');
Route::livewire('dewan-paroki', 'pages::publik.dewan-paroki')->name('dewan-paroki');
Route::livewire('lingkungan', 'pages::publik.lingkungan')->name('lingkungan');
Route::livewire('jadwal-misa', 'pages::publik.jadwal-misa')->name('jadwal-misa');
Route::livewire('warta', 'pages::publik.warta')->name('warta');
Route::livewire('kegiatan', 'pages::publik.kegiatan')->name('kegiatan');
Route::livewire('kegiatan/{kegiatan}', 'pages::publik.kegiatan-detail')->name('kegiatan.show');
Route::livewire('galeri', 'pages::publik.galeri')->name('galeri');

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');

    Route::name('admin.')->group(function () {
        Route::livewire('profil-gereja', 'pages::admin.profil-gereja')->name('profil-gereja');
        Route::livewire('jadwal-misa', 'pages::admin.jadwal-misa')->name('jadwal-misa');
        Route::livewire('warta', 'pages::admin.warta')->name('warta');
        Route::livewire('kegiatan', 'pages::admin.kegiatan')->name('kegiatan');
        Route::livewire('pastor-paroki', 'pages::admin.pastor-paroki')->name('pastor-paroki');
        Route::livewire('dewan-paroki', 'pages::admin.dewan-paroki')->name('dewan-paroki');
        Route::livewire('lingkungan', 'pages::admin.lingkungan')->name('lingkungan');
        Route::livewire('galeri', 'pages::admin.galeri')->name('galeri');
    });
});

require __DIR__.'/settings.php';

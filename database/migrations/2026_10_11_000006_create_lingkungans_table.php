<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lingkungans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('wilayah')->nullable();   // nama wilayah / stasi
            $table->string('ketua')->nullable();     // nama saja, tanpa nomor HP
            $table->unsignedSmallInteger('jumlah_kk')->nullable();
            $table->string('jadwal_doa')->nullable();
            $table->text('cakupan_jalan')->nullable(); // satu jalan per baris
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lingkungans');
    }
};

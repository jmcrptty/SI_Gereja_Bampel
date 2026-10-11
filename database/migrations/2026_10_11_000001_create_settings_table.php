<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil gereja, kontak, dan pengaturan tampilan (selalu 1 baris).
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gereja');
            $table->string('pelindung')->nullable();
            $table->string('logo')->nullable();
            $table->string('foto_hero')->nullable();
            $table->string('foto_gereja')->nullable();
            $table->text('sejarah_singkat')->nullable();
            $table->longText('sejarah')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('link_maps', 500)->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->text('jam_sekretariat')->nullable();
            $table->boolean('tampil_card_misa')->default(true);
            $table->boolean('tampil_card_kegiatan')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

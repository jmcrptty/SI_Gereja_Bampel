<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pastor yang sedang bertugas di paroki (pastor paroki & pastor rekan), beserta kisah perkenalannya.
     */
    public function up(): void
    {
        Schema::create('pastors', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan'); // contoh: Pastor Paroki, Pastor Rekan
            $table->string('tarekat')->nullable(); // contoh: MSC, OFM, Projo
            $table->string('foto')->nullable();
            $table->string('asal')->nullable(); // daerah asal, contoh: Kei, Maluku Tenggara
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_tahbisan')->nullable();
            $table->unsignedSmallInteger('mulai_bertugas')->nullable(); // tahun mulai bertugas di paroki
            $table->string('motto')->nullable(); // motto tahbisan
            $table->text('cerita')->nullable(); // latar belakang & kisah panggilan
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pastors');
    }
};

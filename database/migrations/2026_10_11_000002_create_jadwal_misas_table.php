<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_misas', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['rutin', 'khusus']);
            $table->string('nama_perayaan')->nullable();     // wajib jika khusus
            $table->unsignedTinyInteger('hari')->nullable(); // wajib jika rutin, 1 = Senin ... 7 = Minggu
            $table->date('tanggal')->nullable();             // wajib jika khusus
            $table->time('jam');
            $table->string('tempat');
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'aktif']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_misas');
    }
};

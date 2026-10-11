<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->date('tanggal');
            $table->time('jam')->nullable();
            $table->string('tempat');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['aktif', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};

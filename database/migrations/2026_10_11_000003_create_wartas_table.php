<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wartas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->date('tanggal');
            $table->text('ringkasan')->nullable();
            $table->string('file_pdf');
            $table->enum('status', ['draft', 'terbit'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wartas');
    }
};

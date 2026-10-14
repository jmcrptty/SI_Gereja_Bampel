<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batas wilayah lingkungan untuk arsiran di peta: daftar titik sudut [lat, lng].
     */
    public function up(): void
    {
        Schema::table('lingkungans', function (Blueprint $table) {
            $table->json('batas_wilayah')->nullable()->after('cakupan_jalan');
        });
    }

    public function down(): void
    {
        Schema::table('lingkungans', function (Blueprint $table) {
            $table->dropColumn('batas_wilayah');
        });
    }
};

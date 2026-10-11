<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto dan kisah santo/santa pelindung untuk halaman Profil Gereja.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('foto_pelindung')->nullable()->after('pelindung');
            $table->text('kisah_pelindung')->nullable()->after('foto_pelindung');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['foto_pelindung', 'kisah_pelindung']);
        });
    }
};

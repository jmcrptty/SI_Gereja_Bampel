<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Akun admin awal. Ganti password setelah login pertama.
     *
     * firstOrCreate: hanya membuat akun bila belum ada, sehingga menjalankan seeder ulang
     * (mis. di hosting) tidak mereset password atau nama yang sudah diganti admin.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gerejabampel.test'],
            [
                'name' => 'Admin Sekretariat',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan migrate --seed
     * Data contoh terpisah: php artisan db:seed --class=ContohDataSeeder
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
        ]);
    }
}

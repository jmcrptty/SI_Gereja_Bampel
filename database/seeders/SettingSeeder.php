<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::exists()) {
            return;
        }

        Setting::create([
            'nama_gereja' => 'Paroki St. Yoseph Bambu Pemali',
            'alamat' => 'Merauke, Papua Selatan',
            'jam_sekretariat' => "Senin - Jumat: 08.00 - 14.00 WIT\nSabtu: 08.00 - 12.00 WIT",
            'tampil_card_misa' => true,
            'tampil_card_kegiatan' => true,
        ]);
    }
}

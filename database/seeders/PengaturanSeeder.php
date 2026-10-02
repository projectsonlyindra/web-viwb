<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        Pengaturan::query()->updateOrCreate(
            ['kunci' => 'laporan_terbuka_ke_warga'],
            ['nilai' => 'false']
        );

        Pengaturan::query()->updateOrCreate(
            ['kunci' => 'tagihan_terbuka_ke_warga'],
            ['nilai' => 'false']
        );
    }
}

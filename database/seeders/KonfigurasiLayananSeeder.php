<?php

namespace Database\Seeders;

use App\Enums\JenisTagihan;
use App\Models\KonfigurasiLayanan;
use Illuminate\Database\Seeder;

class KonfigurasiLayananSeeder extends Seeder
{
    public function run(): void
    {
        KonfigurasiLayanan::query()->updateOrCreate(
            ['jenis' => JenisTagihan::KEAMANAN->value],
            [
                'nominal' => 0,
                'nominal_mobil' => 50000,
                'nominal_tanpa_mobil' => 30000,
                'cutoff_hari' => 10,
                'denda_harian' => 1000,
                'denda_maksimal' => 20000,
                'alert_tunggakan_bulan' => 3,
            ]
        );

        KonfigurasiLayanan::query()->updateOrCreate(
            ['jenis' => JenisTagihan::PAGUYUBAN->value],
            [
                'nominal' => 20000,
                'cutoff_hari' => 10,
                'denda_harian' => 500,
                'denda_maksimal' => 10000,
                'alert_tunggakan_bulan' => 3,
            ]
        );

        KonfigurasiLayanan::query()->updateOrCreate(
            ['jenis' => JenisTagihan::HIPPAM->value],
            [
                'nominal' => 30000,
                'cutoff_hari' => 10,
                'denda_harian' => 1000,
                'denda_maksimal' => 20000,
                'alert_tunggakan_bulan' => 3,
                'tarif_per_m3' => 3000,
                'minimal_m3' => 10,
                'minimal_nominal' => 30000,
            ]
        );

        KonfigurasiLayanan::query()->updateOrCreate(
            ['jenis' => JenisTagihan::KEBERSIHAN->value],
            [
                'nominal' => 15000,
                'cutoff_hari' => 10,
                'denda_harian' => 500,
                'denda_maksimal' => 10000,
                'alert_tunggakan_bulan' => 3,
            ]
        );
    }
}

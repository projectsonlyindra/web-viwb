<?php

namespace Database\Factories;

use App\Enums\JenisTagihan;
use App\Enums\StatusTagihan;
use App\Models\Tagihan;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tagihan>
 */
class TagihanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warga_id' => Warga::factory(),
            'jenis' => JenisTagihan::KEAMANAN,
            'periode' => now()->format('Y-m'),
            'nominal' => 30000,
            'status' => StatusTagihan::BELUM_BAYAR,
            'cutoff_hari' => 10,
            'denda_harian' => 1000,
            'denda_maksimal' => 20000,
            'tanggal_jatuh_tempo' => now()->startOfMonth()->addDays(9),
        ];
    }
}

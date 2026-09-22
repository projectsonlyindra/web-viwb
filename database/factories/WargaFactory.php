<?php

namespace Database\Factories;

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warga>
 */
class WargaFactory extends Factory
{
    private static int $unitSequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $blok = chr(65 + (self::$unitSequence % 5)); // A-E
        $nomor = str_pad((string) (self::$unitSequence % 99 + 1), 2, '0', STR_PAD_LEFT);
        self::$unitSequence++;

        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'nama' => $this->faker->name(),
            'no_wa' => '628'.$this->faker->numerify('##########'),
            'unit_id' => "{$blok}{$nomor}",
            'jenis_kendaraan' => JenisKendaraan::TIDAK_ADA,
            'status_warga' => StatusWarga::AKTIF,
            'ikut_hippam' => false,
            'ikut_kebersihan' => false,
        ];
    }
}

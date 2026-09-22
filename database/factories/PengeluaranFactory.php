<?php

namespace Database\Factories;

use App\Enums\StatusPengeluaran;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengeluaran>
 */
class PengeluaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori' => $this->faker->word(),
            'keterangan' => $this->faker->sentence(),
            'nominal' => $this->faker->numberBetween(10000, 1000000),
            'tanggal' => now()->toDateString(),
            'status' => StatusPengeluaran::DRAFT,
            'dibuat_oleh_id' => User::factory()->bendahara(),
        ];
    }
}

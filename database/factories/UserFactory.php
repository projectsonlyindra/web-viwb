<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::SUPERADMIN]);
    }

    public function ketuaRt(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::KETUA_RT]);
    }

    public function bendahara(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::BENDAHARA]);
    }

    public function timDivisi(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::TIM_DIVISI]);
    }

    /**
     * WARGA role linked to a Warga record (creates one if none given).
     */
    public function warga(?Warga $warga = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::WARGA,
            'warga_id' => $warga?->id ?? Warga::factory(),
        ]);
    }
}

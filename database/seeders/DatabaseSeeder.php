<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@viwb.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => Role::SUPERADMIN,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ketuart@viwb.test'],
            [
                'name' => 'Ketua RT',
                'password' => 'password',
                'role' => Role::KETUA_RT,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'bendahara@viwb.test'],
            [
                'name' => 'Bendahara',
                'password' => 'password',
                'role' => Role::BENDAHARA,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'warga@viwb.test'],
            [
                'name' => 'Warga Test',
                'password' => 'password',
                'role' => Role::WARGA,
            ]
        );

        $this->call(KonfigurasiLayananSeeder::class);
    }
}

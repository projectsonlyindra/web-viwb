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
            ]
        );
        $superadmin->role = Role::SUPERADMIN;
        $superadmin->save();

        $ketuaRt = User::query()->updateOrCreate(
            ['email' => 'ketuart@viwb.test'],
            [
                'name' => 'Ketua RT',
                'password' => 'password',
            ]
        );
        $ketuaRt->role = Role::KETUA_RT;
        $ketuaRt->save();

        $bendahara = User::query()->updateOrCreate(
            ['email' => 'bendahara@viwb.test'],
            [
                'name' => 'Bendahara',
                'password' => 'password',
            ]
        );
        $bendahara->role = Role::BENDAHARA;
        $bendahara->save();

        $warga = \App\Models\Warga::query()->updateOrCreate(
            ['unit_id' => 'A01'],
            [
                'nik' => '3515000000000001',
                'nama' => 'Budi Santoso',
                'no_wa' => '081234567890',
                'jenis_kendaraan' => \App\Enums\JenisKendaraan::MOBIL,
                'status_warga' => \App\Enums\StatusWarga::AKTIF,
                'ikut_hippam' => true,
                'ikut_kebersihan' => true,
            ]
        );

        $wargaUser = User::query()->updateOrCreate(
            ['email' => 'warga@viwb.test'],
            [
                'name' => 'Warga Test',
                'password' => 'password',
                'warga_id' => $warga->id,
            ]
        );
        $wargaUser->role = Role::WARGA;
        $wargaUser->save();

        $this->call([
            KonfigurasiLayananSeeder::class,
            PengaturanSeeder::class,
        ]);
    }
}

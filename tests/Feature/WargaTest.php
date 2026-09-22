<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bisa_melihat_daftar_warga(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        Warga::factory()->count(3)->create();

        $response = $this->actingAs($superadmin)->get(route('warga.index'));

        $response->assertOk();
    }

    public function test_warga_tidak_bisa_melihat_daftar_warga_lain(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('warga.index'));

        $response->assertForbidden();
    }

    public function test_superadmin_bisa_menambah_warga(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->post(route('warga.store'), [
            'nik' => '3578012345670099',
            'nama' => 'Warga Baru',
            'no_wa' => '628111111111',
            'unit_id' => 'C05',
            'jenis_kendaraan' => 'TIDAK_ADA',
            'status_warga' => 'AKTIF',
        ]);

        $response->assertRedirect(route('warga.index'));
        $this->assertDatabaseHas('warga', ['unit_id' => 'C05', 'nik' => '3578012345670099']);
    }

    public function test_ketua_rt_tidak_bisa_menambah_warga(): void
    {
        $ketuaRt = User::factory()->ketuaRt()->create();

        $response = $this->actingAs($ketuaRt)->post(route('warga.store'), [
            'nik' => '3578012345670099',
            'nama' => 'Warga Baru',
            'no_wa' => '628111111111',
            'unit_id' => 'C05',
            'jenis_kendaraan' => 'TIDAK_ADA',
            'status_warga' => 'AKTIF',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('warga', ['unit_id' => 'C05']);
    }

    public function test_unit_id_harus_sesuai_format_huruf_dan_dua_digit(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->post(route('warga.store'), [
            'nik' => '3578012345670099',
            'nama' => 'Warga Baru',
            'no_wa' => '628111111111',
            'unit_id' => 'blok-5',
            'jenis_kendaraan' => 'TIDAK_ADA',
            'status_warga' => 'AKTIF',
        ]);

        $response->assertSessionHasErrors('unit_id');
    }

    public function test_superadmin_bisa_menghapus_warga(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $warga = Warga::factory()->create();

        $response = $this->actingAs($superadmin)->delete(route('warga.destroy', $warga));

        $response->assertRedirect(route('warga.index'));
        $this->assertSoftDeleted($warga);
    }
}

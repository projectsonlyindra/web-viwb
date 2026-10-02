<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_tidak_bisa_melihat_laporan_keuangan(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('laporan.index'));

        $response->assertForbidden();
    }

    public function test_warga_tidak_bisa_ekspor_csv_laporan(): void
    {
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('laporan.export'));

        $response->assertForbidden();
    }

    public function test_pengurus_bisa_melihat_laporan_keuangan(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $ketuaRt = User::factory()->ketuaRt()->create();
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($bendahara)->get(route('laporan.index'))->assertOk();
        $this->actingAs($ketuaRt)->get(route('laporan.index'))->assertOk();
        $this->actingAs($superadmin)->get(route('laporan.index'))->assertOk();
    }

    public function test_laporan_dan_ekspor_aman_saat_warga_dihapus(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $warga = Warga::factory()->create();

        $tagihan = Tagihan::factory()->create([
            'warga_id' => $warga->id,
            'periode' => now()->format('Y-m'),
        ]);

        $warga->delete();

        $responseIndex = $this->actingAs($superadmin)->get(route('laporan.index'));
        $responseIndex->assertOk();

        $responseExport = $this->actingAs($superadmin)->get(route('laporan.export', ['periode' => now()->format('Y-m')]));
        $responseExport->assertOk();
    }
}

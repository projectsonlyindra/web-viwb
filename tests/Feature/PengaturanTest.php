<?php

namespace Tests\Feature;

use App\Models\KonfigurasiLayanan;
use App\Models\Pengaturan;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\KonfigurasiLayananSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(KonfigurasiLayananSeeder::class);
    }

    public function test_superadmin_dan_ketua_rt_bisa_mengakses_dan_mengubah_pengaturan(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)->get(route('pengaturan.index'))->assertOk();

        $response = $this->actingAs($superadmin)->put(route('pengaturan.transparansi'), [
            'laporan_terbuka_ke_warga' => true,
            'tagihan_terbuka_ke_warga' => true,
        ]);

        $response->assertRedirect(route('pengaturan.index'));
        $this->assertTrue(Pengaturan::get('laporan_terbuka_ke_warga'));
        $this->assertTrue(Pengaturan::get('tagihan_terbuka_ke_warga'));

        $layanan = KonfigurasiLayanan::query()->first();
        $responseLayanan = $this->actingAs($superadmin)->put(route('pengaturan.layanan', $layanan->id), [
            'nominal' => 75000,
            'nominal_mobil' => 100000,
            'nominal_tanpa_mobil' => 50000,
            'cutoff_hari' => 15,
            'denda_harian' => 2000,
            'denda_maksimal' => 40000,
            'alert_tunggakan_bulan' => 2,
        ]);

        $responseLayanan->assertRedirect(route('pengaturan.index'));
        $layanan->refresh();
        $this->assertSame(75000, $layanan->nominal);
        $this->assertSame(15, $layanan->cutoff_hari);
    }

    public function test_warga_tidak_bisa_mengakses_halaman_pengaturan(): void
    {
        $warga = User::factory()->warga()->create();

        $this->actingAs($warga)->get(route('pengaturan.index'))->assertForbidden();
        $this->actingAs($warga)->put(route('pengaturan.transparansi'), [
            'laporan_terbuka_ke_warga' => true,
            'tagihan_terbuka_ke_warga' => true,
        ])->assertForbidden();
    }

    public function test_warga_bisa_melihat_laporan_jika_setting_aktif(): void
    {
        Pengaturan::set('laporan_terbuka_ke_warga', true);
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('laporan.index'));
        $response->assertOk();
    }

    public function test_warga_ditolak_melihat_laporan_jika_setting_nonaktif(): void
    {
        Pengaturan::set('laporan_terbuka_ke_warga', false);
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('laporan.index'));
        $response->assertForbidden();
    }

    public function test_warga_tetap_tidak_bisa_ekspor_csv_meskipun_setting_laporan_aktif(): void
    {
        Pengaturan::set('laporan_terbuka_ke_warga', true);
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('laporan.export'));
        $response->assertForbidden();
    }

    public function test_warga_bisa_melihat_tagihan_lain_jika_setting_aktif(): void
    {
        Pengaturan::set('tagihan_terbuka_ke_warga', true);

        $wargaA = Warga::factory()->create(['unit_id' => 'A01']);
        $wargaB = Warga::factory()->create(['unit_id' => 'B02']);

        $userWargaA = User::factory()->warga()->create(['warga_id' => $wargaA->id]);

        $tagihanB = Tagihan::factory()->create([
            'warga_id' => $wargaB->id,
            'periode' => '2026-09',
        ]);

        $response = $this->actingAs($userWargaA)->get(route('tagihan.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tagihan/Index')
            ->has('tagihan.data', 1)
        );
    }

    public function test_warga_hanya_melihat_tagihan_sendiri_jika_setting_tagihan_nonaktif(): void
    {
        Pengaturan::set('tagihan_terbuka_ke_warga', false);

        $wargaA = Warga::factory()->create(['unit_id' => 'A01']);
        $wargaB = Warga::factory()->create(['unit_id' => 'B02']);

        $userWargaA = User::factory()->warga()->create(['warga_id' => $wargaA->id]);

        Tagihan::factory()->create([
            'warga_id' => $wargaB->id,
            'periode' => '2026-09',
        ]);

        $response = $this->actingAs($userWargaA)->get(route('tagihan.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tagihan/Index')
            ->has('tagihan.data', 0)
        );
    }

    public function test_warga_bisa_melihat_pengeluaran_jika_setting_laporan_aktif(): void
    {
        Pengaturan::set('laporan_terbuka_ke_warga', true);
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('pengeluaran.index'));
        $response->assertOk();
    }

    public function test_warga_ditolak_melihat_pengeluaran_jika_setting_laporan_nonaktif(): void
    {
        Pengaturan::set('laporan_terbuka_ke_warga', false);
        $warga = User::factory()->warga()->create();

        $response = $this->actingAs($warga)->get(route('pengeluaran.index'));
        $response->assertForbidden();
    }
}


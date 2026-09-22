<?php

namespace Tests\Feature;

use App\Enums\JenisKendaraan;
use App\Enums\StatusWarga;
use App\Models\Tagihan;
use App\Models\Warga;
use App\Services\TagihanService;
use Database\Seeders\KonfigurasiLayananSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagihanService $tagihanService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(KonfigurasiLayananSeeder::class);
        $this->tagihanService = app(TagihanService::class);
    }

    public function test_generate_membuat_keamanan_dan_paguyuban_untuk_semua_warga_aktif(): void
    {
        Warga::factory()->create(['jenis_kendaraan' => JenisKendaraan::TIDAK_ADA]);

        $hasil = $this->tagihanService->generateBulanan('2026-09');

        $this->assertSame(2, $hasil['dibuat']);
        $this->assertDatabaseHas('tagihan', ['jenis' => 'KEAMANAN', 'periode' => '2026-09']);
        $this->assertDatabaseHas('tagihan', ['jenis' => 'PAGUYUBAN', 'periode' => '2026-09']);
    }

    public function test_nominal_keamanan_mengikuti_jenis_kendaraan(): void
    {
        Warga::factory()->create(['jenis_kendaraan' => JenisKendaraan::MOBIL]);

        $this->tagihanService->generateBulanan('2026-09');

        $tagihan = Tagihan::query()->where('jenis', 'KEAMANAN')->first();

        // seeder: nominal_mobil = 50000
        $this->assertSame(50000, $tagihan->nominal);
    }

    public function test_hippam_dan_kebersihan_dilewati_jika_warga_tidak_ikut(): void
    {
        Warga::factory()->create(['ikut_hippam' => false, 'ikut_kebersihan' => false]);

        $this->tagihanService->generateBulanan('2026-09');

        $this->assertDatabaseMissing('tagihan', ['jenis' => 'HIPPAM']);
        $this->assertDatabaseMissing('tagihan', ['jenis' => 'KEBERSIHAN']);
    }

    public function test_hippam_dibuat_jika_warga_ikut(): void
    {
        Warga::factory()->create(['ikut_hippam' => true]);

        $hasil = $this->tagihanService->generateBulanan('2026-09');

        $this->assertSame(3, $hasil['dibuat']); // KEAMANAN + PAGUYUBAN + HIPPAM
        $this->assertDatabaseHas('tagihan', ['jenis' => 'HIPPAM', 'periode' => '2026-09']);
    }

    public function test_warga_tidak_aktif_dilewati(): void
    {
        Warga::factory()->create(['status_warga' => StatusWarga::PINDAH]);

        $hasil = $this->tagihanService->generateBulanan('2026-09');

        $this->assertSame(0, $hasil['dibuat']);
    }

    public function test_generate_ulang_periode_sama_bersifat_idempotent(): void
    {
        Warga::factory()->create();

        $this->tagihanService->generateBulanan('2026-09');
        $hasilKedua = $this->tagihanService->generateBulanan('2026-09');

        $this->assertSame(0, $hasilKedua['dibuat']);
        $this->assertSame(2, $hasilKedua['diperbarui']);
        $this->assertSame(2, Tagihan::query()->count());
    }

    public function test_tanggal_jatuh_tempo_sesuai_cutoff_hari_konfigurasi(): void
    {
        Warga::factory()->create();

        $this->tagihanService->generateBulanan('2026-09');

        $tagihan = Tagihan::query()->where('jenis', 'PAGUYUBAN')->first();

        // seeder: cutoff_hari PAGUYUBAN = 10
        $this->assertSame('2026-09-10', $tagihan->tanggal_jatuh_tempo->format('Y-m-d'));
    }
}

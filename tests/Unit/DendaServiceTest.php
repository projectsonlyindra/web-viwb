<?php

namespace Tests\Unit;

use App\Services\DendaService;
use Carbon\Carbon;
use Tests\TestCase;

class DendaServiceTest extends TestCase
{
    private DendaService $dendaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dendaService = new DendaService;
    }

    public function test_denda_nol_jika_belum_jatuh_tempo(): void
    {
        $jatuhTempo = Carbon::parse('2026-09-20');
        $today = Carbon::parse('2026-09-10');

        $denda = $this->dendaService->hitungDenda($jatuhTempo, 1000, 20000, $today);

        $this->assertSame(0, $denda);
    }

    public function test_denda_nol_pada_hari_jatuh_tempo(): void
    {
        $jatuhTempo = Carbon::parse('2026-09-10');
        $today = Carbon::parse('2026-09-10');

        $denda = $this->dendaService->hitungDenda($jatuhTempo, 1000, 20000, $today);

        $this->assertSame(0, $denda);
    }

    public function test_denda_dihitung_per_hari_telat(): void
    {
        $jatuhTempo = Carbon::parse('2026-09-10');
        $today = Carbon::parse('2026-09-13');

        $denda = $this->dendaService->hitungDenda($jatuhTempo, 1000, 20000, $today);

        $this->assertSame(3000, $denda);
    }

    public function test_denda_dibulatkan_ke_hari_penuh_walau_ada_komponen_jam(): void
    {
        $jatuhTempo = Carbon::parse('2026-09-10 00:00:00');
        $today = Carbon::parse('2026-09-12 17:45:00');

        $denda = $this->dendaService->hitungDenda($jatuhTempo, 1000, 20000, $today);

        $this->assertSame(2000, $denda);
    }

    public function test_denda_dibatasi_denda_maksimal(): void
    {
        $jatuhTempo = Carbon::parse('2026-01-01');
        $today = Carbon::parse('2026-09-12');

        $denda = $this->dendaService->hitungDenda($jatuhTempo, 1000, 20000, $today);

        $this->assertSame(20000, $denda);
    }

    public function test_total_tagihan_menjumlahkan_nominal_dan_denda(): void
    {
        Carbon::setTestNow('2026-09-13');

        $jatuhTempo = Carbon::parse('2026-09-10');

        $total = $this->dendaService->totalTagihan(30000, $jatuhTempo, 1000, 20000);

        $this->assertSame(33000, $total);

        Carbon::setTestNow();
    }
}

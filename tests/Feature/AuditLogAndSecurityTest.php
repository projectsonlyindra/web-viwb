<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\StatusTagihan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use App\Services\PembayaranService;
use App\Services\PengeluaranService;
use App\Services\WahaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuditLogAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_dicatat_saat_pembayaran_dikonfirmasi(): void
    {
        Log::spy();

        $warga = Warga::factory()->create();
        $tagihan = Tagihan::factory()->create(['warga_id' => $warga->id, 'nominal' => 50000]);
        $bendahara = User::factory()->bendahara()->create();

        $pembayaran = Pembayaran::query()->create([
            'warga_id' => $warga->id,
            'total_dibayar' => 50000,
            'status' => \App\Enums\StatusPembayaran::MENUNGGU_KONFIRMASI,
        ]);
        $pembayaran->item()->create([
            'tagihan_id' => $tagihan->id,
            'nominal' => 50000,
            'denda_dibayar' => 0,
        ]);

        app(PembayaranService::class)->confirm($pembayaran, $bendahara);

        Log::shouldHaveReceived('info')
            ->withArgs(fn ($message) => str_contains($message, 'Pembayaran ID:') && str_contains($message, 'dikonfirmasi'));
    }

    public function test_audit_log_dicatat_saat_pembayaran_ditolak(): void
    {
        Log::spy();

        $warga = Warga::factory()->create();
        $pembayaran = Pembayaran::query()->create([
            'warga_id' => $warga->id,
            'total_dibayar' => 50000,
            'status' => \App\Enums\StatusPembayaran::MENUNGGU_KONFIRMASI,
        ]);

        app(PembayaranService::class)->reject($pembayaran, 'Bukti transfer tidak valid');

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message) => str_contains($message, 'Pembayaran ID:') && str_contains($message, 'ditolak'));
    }

    public function test_waha_service_menolak_url_tidak_valid_sebagai_ssrf_guard(): void
    {
        config(['services.waha.dummy_mode' => false]);
        config(['services.waha.base_url' => 'ftp://malicious-host:21/api']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Konfigurasi WAHA base URL tidak valid.');

        app(WahaService::class)->sendText('08123456789', 'Tes pesan');
    }
}

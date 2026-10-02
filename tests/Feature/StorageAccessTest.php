<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_tidak_bisa_mengakses_berkas_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti-pembayaran/sample.jpg', 'dummy content');

        $response = $this->get('/storage/bukti-pembayaran/sample.jpg');

        $response->assertRedirect(route('login'));
    }

    public function test_warga_tidak_bisa_mengakses_bukti_bayar_warga_lain(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti-pembayaran/rahasia.jpg', 'bukti-lain');

        $wargaA = Warga::factory()->create();
        $userA = User::factory()->warga($wargaA)->create();

        $wargaB = Warga::factory()->create();
        $userB = User::factory()->warga($wargaB)->create();

        Pembayaran::query()->create([
            'warga_id' => $wargaB->id,
            'total_dibayar' => 50000,
            'bukti_url' => 'bukti-pembayaran/rahasia.jpg',
            'status' => \App\Enums\StatusPembayaran::MENUNGGU_KONFIRMASI,
        ]);

        $response = $this->actingAs($userA)->get('/storage/bukti-pembayaran/rahasia.jpg');

        $response->assertForbidden();
    }

    public function test_warga_bisa_mengakses_bukti_bayar_milik_sendiri(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti-pembayaran/milik-saya.jpg', 'bukti-saya');

        $warga = Warga::factory()->create();
        $user = User::factory()->warga($warga)->create();

        Pembayaran::query()->create([
            'warga_id' => $warga->id,
            'total_dibayar' => 50000,
            'bukti_url' => 'bukti-pembayaran/milik-saya.jpg',
            'status' => \App\Enums\StatusPembayaran::MENUNGGU_KONFIRMASI,
        ]);

        $response = $this->actingAs($user)->get('/storage/bukti-pembayaran/milik-saya.jpg');

        $response->assertOk();
    }

    public function test_pengurus_bisa_mengakses_seluruh_bukti_bayar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti-pembayaran/warga.jpg', 'content');

        $superadmin = User::factory()->superadmin()->create();
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($superadmin)->get('/storage/bukti-pembayaran/warga.jpg')->assertOk();
        $this->actingAs($bendahara)->get('/storage/bukti-pembayaran/warga.jpg')->assertOk();
    }
}

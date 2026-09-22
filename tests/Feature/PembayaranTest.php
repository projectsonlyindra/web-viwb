<?php

namespace Tests\Feature;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTagihan;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_bisa_mengajukan_pembayaran_untuk_tagihan_miliknya(): void
    {
        $warga = Warga::factory()->create();
        $user = User::factory()->warga($warga)->create();
        $tagihan = Tagihan::factory()->for($warga)->create([
            'nominal' => 30000,
            'status' => StatusTagihan::BELUM_BAYAR,
            'tanggal_jatuh_tempo' => now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->post(route('pembayaran.store'), [
            'tagihan_ids' => [$tagihan->id],
        ]);

        $response->assertRedirect(route('pembayaran.index'));
        $this->assertDatabaseHas('pembayaran', [
            'warga_id' => $warga->id,
            'total_dibayar' => 30000,
            'status' => StatusPembayaran::MENUNGGU_KONFIRMASI->value,
        ]);
    }

    public function test_warga_tidak_bisa_mengajukan_pembayaran_untuk_tagihan_warga_lain(): void
    {
        $wargaLain = Warga::factory()->create();
        $user = User::factory()->warga()->create();
        $tagihanOrangLain = Tagihan::factory()->for($wargaLain)->create();

        $this->actingAs($user)->post(route('pembayaran.store'), [
            'tagihan_ids' => [$tagihanOrangLain->id],
        ]);

        // tagihan_ids difilter berdasar warga_id milik user, jadi tidak ada item yang tercatat
        $this->assertDatabaseCount('pembayaran_item', 0);
    }

    public function test_konfirmasi_pembayaran_melunasi_semua_tagihan_terkait(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $warga = Warga::factory()->create();
        $user = User::factory()->warga($warga)->create();
        $tagihan = Tagihan::factory()->for($warga)->create([
            'status' => StatusTagihan::BELUM_BAYAR,
            'tanggal_jatuh_tempo' => now()->addDays(5),
        ]);

        $this->actingAs($user)->post(route('pembayaran.store'), ['tagihan_ids' => [$tagihan->id]]);
        $pembayaran = $warga->pembayaran()->first();

        $response = $this->actingAs($bendahara)->post(route('pembayaran.confirm', $pembayaran));

        $response->assertRedirect(route('pembayaran.index'));
        $this->assertSame(StatusPembayaran::DIKONFIRMASI->value, $pembayaran->fresh()->status->value);
        $this->assertSame(StatusTagihan::LUNAS->value, $tagihan->fresh()->status->value);
    }

    public function test_tolak_pembayaran_tidak_mengubah_status_tagihan(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $warga = Warga::factory()->create();
        $user = User::factory()->warga($warga)->create();
        $tagihan = Tagihan::factory()->for($warga)->create([
            'status' => StatusTagihan::BELUM_BAYAR,
            'tanggal_jatuh_tempo' => now()->addDays(5),
        ]);

        $this->actingAs($user)->post(route('pembayaran.store'), ['tagihan_ids' => [$tagihan->id]]);
        $pembayaran = $warga->pembayaran()->first();

        $this->actingAs($bendahara)->post(route('pembayaran.reject', $pembayaran), [
            'catatan' => 'Bukti tidak jelas',
        ]);

        $this->assertSame(StatusPembayaran::DITOLAK->value, $pembayaran->fresh()->status->value);
        $this->assertSame(StatusTagihan::BELUM_BAYAR->value, $tagihan->fresh()->status->value);
    }

    public function test_warga_tidak_bisa_konfirmasi_pembayaran(): void
    {
        $warga = Warga::factory()->create();
        $user = User::factory()->warga($warga)->create();
        $tagihan = Tagihan::factory()->for($warga)->create(['tanggal_jatuh_tempo' => now()->addDays(5)]);

        $this->actingAs($user)->post(route('pembayaran.store'), ['tagihan_ids' => [$tagihan->id]]);
        $pembayaran = $warga->pembayaran()->first();

        $response = $this->actingAs($user)->post(route('pembayaran.confirm', $pembayaran));

        $response->assertForbidden();
    }
}

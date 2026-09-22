<?php

namespace Tests\Feature;

use App\Enums\StatusPengeluaran;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'kategori' => 'Listrik',
        'keterangan' => 'Bayar listrik bulanan',
        'nominal' => 500000,
        'tanggal' => '2026-09-12',
    ];

    public function test_bendahara_entri_menghasilkan_status_draft(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $response = $this->actingAs($bendahara)->post(route('pengeluaran.store'), $this->payload);

        $response->assertRedirect(route('pengeluaran.index'));
        $this->assertDatabaseHas('pengeluaran', [
            'kategori' => 'Listrik',
            'status' => StatusPengeluaran::DRAFT->value,
            'disetujui_oleh_id' => null,
        ]);
    }

    public function test_superadmin_entri_langsung_approved(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)->post(route('pengeluaran.store'), $this->payload);

        $this->assertDatabaseHas('pengeluaran', [
            'kategori' => 'Listrik',
            'status' => StatusPengeluaran::APPROVED->value,
        ]);
    }

    public function test_ketua_rt_tidak_bisa_entri_pengeluaran(): void
    {
        $ketuaRt = User::factory()->ketuaRt()->create();

        $response = $this->actingAs($ketuaRt)->post(route('pengeluaran.store'), $this->payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('pengeluaran', ['kategori' => 'Listrik']);
    }

    public function test_ajukan_mengubah_draft_menjadi_menunggu_approval(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $pengeluaran = Pengeluaran::factory()->for($bendahara, 'dibuatOleh')->create([
            'status' => StatusPengeluaran::DRAFT,
        ]);

        $response = $this->actingAs($bendahara)->post(route('pengeluaran.submit', $pengeluaran));

        $response->assertRedirect(route('pengeluaran.index'));
        $this->assertSame(StatusPengeluaran::MENUNGGU_APPROVAL->value, $pengeluaran->fresh()->status->value);
    }

    public function test_ketua_rt_bisa_approve_pengeluaran_orang_lain(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $ketuaRt = User::factory()->ketuaRt()->create();
        $pengeluaran = Pengeluaran::factory()->for($bendahara, 'dibuatOleh')->create([
            'status' => StatusPengeluaran::MENUNGGU_APPROVAL,
        ]);

        $response = $this->actingAs($ketuaRt)->post(route('pengeluaran.approve', $pengeluaran));

        $response->assertRedirect(route('pengeluaran.index'));
        $fresh = $pengeluaran->fresh();
        $this->assertSame(StatusPengeluaran::APPROVED->value, $fresh->status->value);
        $this->assertSame($ketuaRt->id, $fresh->disetujui_oleh_id);
    }

    public function test_tidak_bisa_approve_pengeluaran_buatan_sendiri(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $pengeluaran = Pengeluaran::factory()->for($bendahara, 'dibuatOleh')->create([
            'status' => StatusPengeluaran::MENUNGGU_APPROVAL,
        ]);

        // BENDAHARA tidak termasuk role yang boleh approve sama sekali,
        // jadi ini juga menguji bahwa role dicek, bukan cuma kepemilikan.
        $response = $this->actingAs($bendahara)->post(route('pengeluaran.approve', $pengeluaran));

        $response->assertForbidden();
    }

    public function test_superadmin_tidak_bisa_approve_pengeluaran_buatan_sendiri(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $pengeluaran = Pengeluaran::factory()->for($superadmin, 'dibuatOleh')->create([
            'status' => StatusPengeluaran::MENUNGGU_APPROVAL,
        ]);

        $response = $this->actingAs($superadmin)->post(route('pengeluaran.approve', $pengeluaran));

        $response->assertForbidden();
    }

    public function test_reject_menyimpan_catatan_review(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $ketuaRt = User::factory()->ketuaRt()->create();
        $pengeluaran = Pengeluaran::factory()->for($bendahara, 'dibuatOleh')->create([
            'status' => StatusPengeluaran::MENUNGGU_APPROVAL,
        ]);

        $this->actingAs($ketuaRt)->post(route('pengeluaran.reject', $pengeluaran), [
            'catatan_review' => 'Nominal tidak sesuai nota',
        ]);

        $fresh = $pengeluaran->fresh();
        $this->assertSame(StatusPengeluaran::REJECTED->value, $fresh->status->value);
        $this->assertSame('Nominal tidak sesuai nota', $fresh->catatan_review);
    }
}

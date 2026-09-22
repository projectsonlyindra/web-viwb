<?php

namespace Tests\Feature;

use App\Enums\StatusBroadcastJob;
use App\Models\BroadcastJob;
use App\Models\BroadcastLog;
use App\Models\User;
use App\Models\Warga;
use App\Services\PengumumanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengumumanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ketua_rt_bisa_membuat_pengumuman(): void
    {
        $ketuaRt = User::factory()->ketuaRt()->create();
        Warga::factory()->create();

        $response = $this->actingAs($ketuaRt)->post(route('pengumuman.store'), [
            'judul' => 'Kerja Bakti',
            'isi' => 'Minggu depan jam 7 pagi',
        ]);

        $response->assertRedirect(route('pengumuman.index'));
        $this->assertDatabaseHas('pengumuman', ['judul' => 'Kerja Bakti']);
    }

    public function test_bendahara_tidak_bisa_membuat_pengumuman(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $response = $this->actingAs($bendahara)->post(route('pengumuman.store'), [
            'judul' => 'Kerja Bakti',
            'isi' => 'Minggu depan jam 7 pagi',
        ]);

        $response->assertForbidden();
    }

    public function test_membuat_pengumuman_mengantrekan_broadcast_job_untuk_semua_warga_aktif(): void
    {
        Warga::factory()->count(3)->create();
        Warga::factory()->create(['status_warga' => 'PINDAH']);

        app(PengumumanService::class)->create('Kerja Bakti', 'Minggu depan', null, null);

        $this->assertSame(3, BroadcastJob::query()->where('status', StatusBroadcastJob::PENDING->value)->count());
    }

    public function test_target_blok_memfilter_warga_berdasarkan_unit_id(): void
    {
        Warga::factory()->create(['unit_id' => 'A01']);
        Warga::factory()->create(['unit_id' => 'A02']);
        Warga::factory()->create(['unit_id' => 'B01']);

        app(PengumumanService::class)->create('Kerja Bakti', 'Minggu depan', 'A', null);

        $this->assertSame(2, BroadcastJob::query()->count());
    }

    public function test_process_queue_mengirim_job_pending_dan_mencatat_log(): void
    {
        $warga = Warga::factory()->create();
        app(PengumumanService::class)->create('Kerja Bakti', 'Minggu depan', null, null);

        $hasil = app(PengumumanService::class)->processQueue();

        $this->assertSame(1, $hasil['terkirim']);
        $this->assertSame(0, $hasil['gagal']);
        $this->assertDatabaseHas('broadcast_job', ['warga_id' => $warga->id, 'status' => StatusBroadcastJob::DONE->value]);
        $this->assertSame(1, BroadcastLog::query()->where('status', 'terkirim')->count());
    }
}

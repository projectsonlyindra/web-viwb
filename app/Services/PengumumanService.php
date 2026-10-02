<?php

namespace App\Services;

use App\Enums\StatusBroadcastJob;
use App\Enums\StatusWarga;
use App\Models\BroadcastJob;
use App\Models\BroadcastLog;
use App\Models\Pengumuman;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PengumumanService
{
    public function __construct(private WahaService $wahaService) {}

    public function getList(): Collection
    {
        return Pengumuman::query()->orderByDesc('created_at')->get();
    }

    /**
     * Buat pengumuman dan antrekan broadcast_job untuk tiap warga target.
     *
     * @param  string|null  $targetBlok  null = semua warga, "A" = filter blok A (unit_id LIKE 'A%')
     */
    public function create(string $judul, string $isi, ?string $targetBlok, ?string $lampiran): Pengumuman
    {
        $pengumuman = Pengumuman::query()->create([
            'judul' => $judul,
            'isi' => $isi,
            'target_blok' => $targetBlok,
            'lampiran' => $lampiran,
        ]);

        Warga::query()
            ->where('status_warga', StatusWarga::AKTIF->value)
            ->when($targetBlok, fn ($q, $blok) => $q->where('unit_id', 'like', "{$blok}%"))
            ->chunk(100, function ($wargas) use ($pengumuman) {
                $jobs = [];
                $now = now();
                foreach ($wargas as $warga) {
                    $jobs[] = [
                        'pengumuman_id' => $pengumuman->id,
                        'no_wa' => $warga->no_wa,
                        'warga_id' => $warga->id,
                        'pesan' => "{$pengumuman->judul}\n\n{$pengumuman->isi}",
                        'jenis' => 'pengumuman',
                        'status' => StatusBroadcastJob::PENDING->value,
                        'scheduled_at' => $now,
                        'created_at' => $now,
                    ];
                }

                if (! empty($jobs)) {
                    BroadcastJob::query()->insert($jobs);
                }
            });

        Log::info("Pengumuman dibuat: ID {$pengumuman->id}, Judul '{$pengumuman->judul}'.");

        return $pengumuman;
    }

    /**
     * Proses maksimal 10 broadcast_job berstatus PENDING.
     *
     * @return array{terkirim: int, gagal: int}
     */
    public function processQueue(): array
    {
        $terkirim = 0;
        $gagal = 0;

        BroadcastJob::query()
            ->where('status', StatusBroadcastJob::PENDING->value)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get()
            ->each(function (BroadcastJob $job) use (&$terkirim, &$gagal) {
                try {
                    $this->wahaService->sendText($job->no_wa, $job->pesan);

                    $job->update(['status' => StatusBroadcastJob::DONE, 'processed_at' => now()]);

                    BroadcastLog::query()->create([
                        'pengumuman_id' => $job->pengumuman_id,
                        'no_wa' => $job->no_wa,
                        'warga_id' => $job->warga_id,
                        'pesan' => $job->pesan,
                        'jenis' => $job->jenis,
                        'status' => 'terkirim',
                        'sent_at' => now(),
                    ]);

                    $terkirim++;
                } catch (\Throwable $e) {
                    $attempts = $job->attempts + 1;

                    $job->update([
                        'attempts' => $attempts,
                        'status' => $attempts >= 3 ? StatusBroadcastJob::FAILED : StatusBroadcastJob::PENDING,
                        'error_msg' => $e->getMessage(),
                    ]);

                    BroadcastLog::query()->create([
                        'pengumuman_id' => $job->pengumuman_id,
                        'no_wa' => $job->no_wa,
                        'warga_id' => $job->warga_id,
                        'pesan' => $job->pesan,
                        'jenis' => $job->jenis,
                        'status' => 'gagal',
                        'error_msg' => $e->getMessage(),
                        'sent_at' => now(),
                    ]);

                    $gagal++;
                }
            });

        return ['terkirim' => $terkirim, 'gagal' => $gagal];
    }
}

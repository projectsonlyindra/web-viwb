<?php

namespace App\Console\Commands;

use App\Services\PengumumanService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('broadcast:process')]
#[Description('Proses maksimal 10 broadcast_job berstatus PENDING')]
class ProcessBroadcastQueue extends Command
{
    public function handle(PengumumanService $pengumumanService)
    {
        $hasil = $pengumumanService->processQueue();

        $this->info("Terkirim: {$hasil['terkirim']}, Gagal: {$hasil['gagal']}.");
    }
}

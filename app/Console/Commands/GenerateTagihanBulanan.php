<?php

namespace App\Console\Commands;

use App\Services\TagihanService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tagihan:generate {periode? : Format YYYY-MM, default bulan berjalan}')]
#[Description('Generate tagihan bulanan untuk semua warga aktif')]
class GenerateTagihanBulanan extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TagihanService $tagihanService)
    {
        $periode = $this->argument('periode') ?? now()->format('Y-m');

        $this->info("Generating tagihan untuk periode {$periode}...");

        $hasil = $tagihanService->generateBulanan($periode);

        $this->info("Selesai. Dibuat: {$hasil['dibuat']}, Diperbarui: {$hasil['diperbarui']}.");
    }
}

<?php

namespace App\Services;

use Carbon\Carbon;

class DendaService
{
    public function hitungDenda(Carbon $tanggalJatuhTempo, int $dendaHarian, int $dendaMaksimal, ?Carbon $today = null): int
    {
        $today ??= now();

        if ($today->lte($tanggalJatuhTempo)) {
            return 0;
        }

        $hariTelat = (int) $tanggalJatuhTempo->diffInDays($today);

        return min($hariTelat * $dendaHarian, $dendaMaksimal);
    }

    public function totalTagihan(int $nominal, Carbon $tanggalJatuhTempo, int $dendaHarian, int $dendaMaksimal): int
    {
        return $nominal + $this->hitungDenda($tanggalJatuhTempo, $dendaHarian, $dendaMaksimal);
    }
}

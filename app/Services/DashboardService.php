<?php

namespace App\Services;

use App\Enums\StatusPengeluaran;
use App\Enums\StatusTagihan;
use App\Enums\StatusWarga;
use App\Models\KonfigurasiLayanan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Warga;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(): array
    {
        $periode = now()->format('Y-m');

        $tagihanBulanIni = Tagihan::query()->where('periode', $periode)->get();

        return [
            'periode' => $periode,
            'jumlah_warga_aktif' => Warga::query()->where('status_warga', StatusWarga::AKTIF->value)->count(),
            'jumlah_tagihan_bulan_ini' => $tagihanBulanIni->count(),
            'total_tagihan_bulan_ini' => (int) $tagihanBulanIni->sum('nominal'),
            'total_terbayar_bulan_ini' => (int) Pembayaran::query()
                ->where('status', 'DIKONFIRMASI')
                ->whereBetween('dikonfirmasi_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_dibayar'),
            'pengeluaran_menunggu_approval' => \App\Models\Pengeluaran::query()
                ->where('status', StatusPengeluaran::MENUNGGU_APPROVAL->value)
                ->count(),
            'alert_tunggakan' => $this->alertTunggakan(),
        ];
    }

    /**
     * Per jenis layanan, per warga: hitung berapa periode BELUM_BAYAR/SEBAGIAN.
     * Jika >= alert_tunggakan_bulan (dari konfigurasi_layanan), masuk daftar alert.
     */
    public function alertTunggakan(): array
    {
        $konfigurasi = KonfigurasiLayanan::query()->get()->keyBy(fn ($k) => $k->jenis->value);

        $tunggakan = Tagihan::query()
            ->whereIn('status', [StatusTagihan::BELUM_BAYAR->value, StatusTagihan::SEBAGIAN->value])
            ->select('warga_id', 'jenis', DB::raw('count(*) as jumlah_periode'))
            ->groupBy('warga_id', 'jenis')
            ->with('warga')
            ->get();

        return $tunggakan
            ->filter(function ($t) use ($konfigurasi) {
                $batas = $konfigurasi->get($t->jenis->value)?->alert_tunggakan_bulan ?? 3;

                return $t->jumlah_periode >= $batas;
            })
            ->map(fn ($t) => [
                'warga' => $t->warga->nama,
                'unit_id' => $t->warga->unit_id,
                'jenis' => $t->jenis->value,
                'jumlah_periode' => $t->jumlah_periode,
            ])
            ->values()
            ->all();
    }
}

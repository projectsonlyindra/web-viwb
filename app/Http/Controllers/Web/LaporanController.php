<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Tagihan;
use App\Services\DendaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(private DendaService $dendaService) {}

    public function index(Request $request): Response
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $awalBulan = Carbon::parse($periode.'-01')->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth()->endOfDay();

        $tagihan = Tagihan::query()->with('warga')->where('periode', $periode)->orderBy('warga_id')->get()
            ->map(function (Tagihan $t) {
                $t->denda = $this->dendaService->hitungDenda($t->tanggal_jatuh_tempo, $t->denda_harian, $t->denda_maksimal);
                $t->total = $t->nominal + $t->denda;

                return $t;
            });

        // Pendapatan = pembayaran yang benar-benar dikonfirmasi (uang masuk) pada bulan berjalan,
        // bukan sekadar tagihan berstatus lunas, supaya laporan mencerminkan kas riil.
        $pendapatan = Pembayaran::query()->with('warga')
            ->where('status', 'DIKONFIRMASI')
            ->whereBetween('dikonfirmasi_at', [$awalBulan, $akhirBulan])
            ->orderBy('dikonfirmasi_at')
            ->get();

        $pengeluaran = Pengeluaran::query()
            ->where('status', 'APPROVED')
            ->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->get();

        $totalPendapatan = (int) $pendapatan->sum('total_dibayar');
        $totalPengeluaran = (int) $pengeluaran->sum('nominal');

        return Inertia::render('Laporan/Index', [
            'periode' => $periode,
            'tagihan' => $tagihan,
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'totalTagihan' => (int) $tagihan->sum('total'),
            'totalLunas' => (int) $tagihan->where('status', 'LUNAS')->sum('total'),
            'totalPendapatan' => $totalPendapatan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $totalPendapatan - $totalPengeluaran,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        $tagihan = Tagihan::query()->with('warga')->where('periode', $periode)->orderBy('warga_id')->get();

        return response()->streamDownload(function () use ($tagihan) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Unit', 'Nama', 'Jenis', 'Periode', 'Nominal', 'Status', 'Jatuh Tempo']);

            foreach ($tagihan as $t) {
                fputcsv($handle, [
                    $t->warga->unit_id,
                    $t->warga->nama,
                    $t->jenis->value,
                    $t->periode,
                    $t->nominal,
                    $t->status->value,
                    $t->tanggal_jatuh_tempo->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, "laporan-tagihan-{$periode}.csv", ['Content-Type' => 'text/csv']);
    }
}

<?php

namespace App\Services;

use App\Enums\JenisKendaraan;
use App\Enums\JenisTagihan;
use App\Enums\Role;
use App\Enums\StatusTagihan;
use App\Enums\StatusWarga;
use App\Models\KonfigurasiLayanan;
use App\Models\Pengaturan;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Log;

class TagihanService
{
    public function __construct(private DendaService $dendaService) {}

    /**
     * Generate tagihan bulanan untuk semua warga AKTIF pada periode tertentu.
     * Aman dijalankan berulang kali (updateOrCreate berdasarkan [warga_id, jenis, periode]).
     *
     * @param  string  $periode  format "2025-06"
     * @return array{dibuat: int, diperbarui: int}
     */
    public function generateBulanan(string $periode): array
    {
        $konfigurasi = KonfigurasiLayanan::query()->get()->keyBy(fn (KonfigurasiLayanan $k) => $k->jenis->value);

        $dibuat = 0;
        $diperbarui = 0;

        Warga::query()
            ->where('status_warga', StatusWarga::AKTIF->value)
            ->chunk(100, function ($wargas) use ($konfigurasi, $periode, &$dibuat, &$diperbarui) {
                foreach ($wargas as $warga) {
                    // Keamanan: wajib untuk semua warga.
                    $keamanan = $konfigurasi->get(JenisTagihan::KEAMANAN->value);
                    if ($keamanan) {
                        $nominal = $warga->jenis_kendaraan === JenisKendaraan::MOBIL
                            ? $keamanan->nominal_mobil
                            : $keamanan->nominal_tanpa_mobil;

                        $this->buatTagihan($warga, JenisTagihan::KEAMANAN, $periode, $nominal, $keamanan, $dibuat, $diperbarui);
                    }

                    // Paguyuban: wajib untuk semua warga.
                    $paguyuban = $konfigurasi->get(JenisTagihan::PAGUYUBAN->value);
                    if ($paguyuban) {
                        $this->buatTagihan($warga, JenisTagihan::PAGUYUBAN, $periode, $paguyuban->nominal, $paguyuban, $dibuat, $diperbarui);
                    }

                    // HIPPAM: opsional, lewati jika warga tidak ikut.
                    $hippam = $konfigurasi->get(JenisTagihan::HIPPAM->value);
                    if ($hippam && $warga->ikut_hippam) {
                        $this->buatTagihan($warga, JenisTagihan::HIPPAM, $periode, $hippam->nominal, $hippam, $dibuat, $diperbarui);
                    }

                    // Kebersihan: opsional, lewati jika warga tidak ikut.
                    $kebersihan = $konfigurasi->get(JenisTagihan::KEBERSIHAN->value);
                    if ($kebersihan && $warga->ikut_kebersihan) {
                        $this->buatTagihan($warga, JenisTagihan::KEBERSIHAN, $periode, $kebersihan->nominal, $kebersihan, $dibuat, $diperbarui);
                    }
                }
            });

        Log::info("Generate tagihan bulanan: Periode {$periode}, Dibuat: {$dibuat}, Diperbarui: {$diperbarui}.");

        return ['dibuat' => $dibuat, 'diperbarui' => $diperbarui];
    }

    private function buatTagihan(
        Warga $warga,
        JenisTagihan $jenis,
        string $periode,
        int $nominal,
        KonfigurasiLayanan $konfigurasi,
        int &$dibuat,
        int &$diperbarui,
    ): void {
        $tanggalJatuhTempo = $this->tanggalJatuhTempo($periode, $konfigurasi->cutoff_hari);

        $tagihan = Tagihan::query()->where([
            'warga_id' => $warga->id,
            'jenis' => $jenis->value,
            'periode' => $periode,
        ])->first();

        $data = [
            'nominal' => $nominal,
            'cutoff_hari' => $konfigurasi->cutoff_hari,
            'denda_harian' => $konfigurasi->denda_harian,
            'denda_maksimal' => $konfigurasi->denda_maksimal,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
        ];

        if ($tagihan) {
            $tagihan->update($data);
            $diperbarui++;

            return;
        }

        Tagihan::query()->create([
            'warga_id' => $warga->id,
            'jenis' => $jenis->value,
            'periode' => $periode,
            ...$data,
        ]);
        $dibuat++;
    }

    private function tanggalJatuhTempo(string $periode, int $cutoffHari): Carbon
    {
        $awalBulan = Carbon::parse("{$periode}-01");

        return $awalBulan->copy()->day(min($cutoffHari, $awalBulan->daysInMonth));
    }

    /**
     * Ambil daftar tagihan dengan total denda dihitung on-the-fly, diberi scope
     * sesuai role: WARGA hanya lihat miliknya, TIM_DIVISI hanya lihat sesuai divisinya.
     */
    public function getList(User $user, array $filters = []): BaseCollection
    {
        $query = Tagihan::query()->with('warga');

        if ($user->role === Role::WARGA) {
            $tagihanTerbuka = (bool) Pengaturan::get('tagihan_terbuka_ke_warga', false);
            if (! $tagihanTerbuka) {
                $query->where('warga_id', $user->warga_id);
            }
        } elseif ($user->role === Role::TIM_DIVISI && $user->divisi) {
            $query->where('jenis', $user->divisi->value);
        }

        $query
            ->when($filters['periode'] ?? null, fn ($q, $periode) => $q->where('periode', $periode))
            ->when($filters['jenis'] ?? null, fn ($q, $jenis) => $q->where('jenis', $jenis))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        return $query->orderByDesc('periode')->get()->map(function (Tagihan $tagihan) {
            if ($tagihan->status === StatusTagihan::LUNAS) {
                $tagihan->denda = 0;
                $tagihan->total = $tagihan->nominal;
            } else {
                $tagihan->denda = $this->dendaService->hitungDenda(
                    $tagihan->tanggal_jatuh_tempo,
                    $tagihan->denda_harian,
                    $tagihan->denda_maksimal,
                );
                $tagihan->total = $tagihan->nominal + $tagihan->denda;
            }

            return $tagihan;
        });
    }
}

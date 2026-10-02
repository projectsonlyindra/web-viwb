<?php

namespace App\Http\Controllers\Web;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\KonfigurasiLayanan;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PengaturanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pengaturan::class);

        return Inertia::render('Pengaturan/Index', [
            'transparansi' => [
                'laporan_terbuka_ke_warga' => (bool) Pengaturan::get('laporan_terbuka_ke_warga', false),
                'tagihan_terbuka_ke_warga' => (bool) Pengaturan::get('tagihan_terbuka_ke_warga', false),
            ],
            'layanan' => KonfigurasiLayanan::query()->get(),
        ]);
    }

    public function updateTransparansi(Request $request): RedirectResponse
    {
        $this->authorize('update', Pengaturan::class);

        $validated = $request->validate([
            'laporan_terbuka_ke_warga' => ['required', 'boolean'],
            'tagihan_terbuka_ke_warga' => ['required', 'boolean'],
        ]);

        Pengaturan::set('laporan_terbuka_ke_warga', $validated['laporan_terbuka_ke_warga']);
        Pengaturan::set('tagihan_terbuka_ke_warga', $validated['tagihan_terbuka_ke_warga']);

        Log::info("Pengaturan transparansi diubah oleh User ID {$request->user()->id} ({$request->user()->name}): laporan={$validated['laporan_terbuka_ke_warga']}, tagihan={$validated['tagihan_terbuka_ke_warga']}");

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan transparansi paguyuban berhasil diperbarui');
    }

    public function updateLayanan(Request $request, KonfigurasiLayanan $layanan): RedirectResponse
    {
        $this->authorize('update', Pengaturan::class);

        $validated = $request->validate([
            'nominal' => ['required', 'integer', 'min:0'],
            'nominal_mobil' => ['nullable', 'integer', 'min:0'],
            'nominal_tanpa_mobil' => ['nullable', 'integer', 'min:0'],
            'cutoff_hari' => ['required', 'integer', 'min:1', 'max:28'],
            'denda_harian' => ['required', 'integer', 'min:0'],
            'denda_maksimal' => ['required', 'integer', 'min:0'],
            'alert_tunggakan_bulan' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $layanan->update($validated);

        Log::info("Pengaturan tarif layanan {$layanan->jenis->value} diubah oleh User ID {$request->user()->id} ({$request->user()->name}).");

        return redirect()->route('pengaturan.index')->with('success', "Tarif {$layanan->jenis->value} berhasil diperbarui");
    }
}

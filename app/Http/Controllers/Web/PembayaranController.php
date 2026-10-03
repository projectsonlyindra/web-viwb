<?php

namespace App\Http\Controllers\Web;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\DendaService;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PembayaranController extends Controller
{
    public function __construct(
        private PembayaranService $pembayaranService,
        private DendaService $dendaService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $tagihanBelumLunas = $user->role === Role::WARGA && $user->warga_id
            ? Tagihan::query()
                ->where('warga_id', $user->warga_id)
                ->whereIn('status', ['BELUM_BAYAR', 'SEBAGIAN'])
                ->orderBy('periode')
                ->get()
                ->map(function (Tagihan $t) {
                    $t->denda = $this->dendaService->hitungDenda($t->tanggal_jatuh_tempo, $t->denda_harian, $t->denda_maksimal);
                    $t->total = $t->nominal + $t->denda;

                    return $t;
                })
            : [];

        return Inertia::render('Pembayaran/Index', [
            'pembayaran' => $this->pembayaranService->getList($user, $request->only(['status']), 25),
            'filters' => $request->only(['status']),
            'tagihanBelumLunas' => $tagihanBelumLunas,
            'canConfirm' => in_array($user->role, [Role::SUPERADMIN, Role::BENDAHARA], true),
        ]);
    }

    public function store(StorePembayaranRequest $request): RedirectResponse
    {
        $wargaId = $request->user()->warga_id;
        if (! $wargaId) {
            return back()->withErrors(['tagihan_ids' => 'Akun Anda belum terhubung dengan data profil warga.']);
        }

        $this->pembayaranService->create(
            $wargaId,
            $request->input('tagihan_ids'),
            $request->file('bukti'),
            $request->input('catatan'),
        );

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diajukan, menunggu konfirmasi.');
    }

    public function confirm(Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorize('confirm', $pembayaran);

        $this->pembayaranService->confirm($pembayaran, auth()->user());

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran dikonfirmasi.');
    }

    public function reject(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $this->authorize('confirm', $pembayaran);

        $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $this->pembayaranService->reject($pembayaran, $request->input('catatan'));

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran ditolak.');
    }
}

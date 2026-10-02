<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\PengeluaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengeluaranController extends Controller
{
    public function __construct(private PengeluaranService $pengeluaranService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pengeluaran::class);

        $user = $request->user();

        return Inertia::render('Pengeluaran/Index', [
            'pengeluaran' => $this->pengeluaranService->getList($request->only(['status'])),
            'filters' => $request->only(['status']),
            'canCreate' => $user->can('create', Pengeluaran::class),
            'userId' => $user->id,
        ]);
    }

    public function store(StorePengeluaranRequest $request): RedirectResponse
    {
        $this->pengeluaranService->create($request->validated(), $request->user(), $request->file('bukti'));

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function submit(Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->authorize('create', Pengeluaran::class);

        $this->pengeluaranService->submitApproval($pengeluaran);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran diajukan untuk approval.');
    }

    public function approve(Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->authorize('approve', $pengeluaran);

        $this->pengeluaranService->approve($pengeluaran, auth()->user());

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran disetujui.');
    }

    public function reject(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->authorize('approve', $pengeluaran);

        $request->validate(['catatan_review' => ['required', 'string', 'max:1000']]);

        $this->pengeluaranService->reject($pengeluaran, auth()->user(), $request->input('catatan_review'));

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran ditolak.');
    }
}

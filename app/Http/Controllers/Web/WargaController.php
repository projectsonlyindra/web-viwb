<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWargaRequest;
use App\Http\Requests\UpdateWargaRequest;
use App\Models\Warga;
use App\Services\WargaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WargaController extends Controller
{
    public function __construct(private WargaService $wargaService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Warga::class);

        return Inertia::render('Warga/Index', [
            'warga' => $this->wargaService->getList($request->only(['blok', 'status'])),
            'filters' => $request->only(['blok', 'status']),
        ]);
    }

    public function store(StoreWargaRequest $request): RedirectResponse
    {
        $this->wargaService->create($request->validated());

        return redirect()->route('warga.index')->with('success', 'Warga berhasil ditambahkan');
    }

    public function update(UpdateWargaRequest $request, Warga $warga): RedirectResponse
    {
        $this->wargaService->update($warga, $request->validated());

        return redirect()->route('warga.index')->with('success', 'Warga berhasil diperbarui');
    }

    public function destroy(Warga $warga): RedirectResponse
    {
        $this->authorize('delete', $warga);

        $this->wargaService->delete($warga);

        return redirect()->route('warga.index')->with('success', 'Warga berhasil dihapus');
    }
}

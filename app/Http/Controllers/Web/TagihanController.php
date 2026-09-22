<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagihanController extends Controller
{
    public function __construct(private TagihanService $tagihanService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tagihan::class);

        return Inertia::render('Tagihan/Index', [
            'tagihan' => $this->tagihanService->getList($request->user(), $request->only(['periode', 'jenis', 'status'])),
            'filters' => $request->only(['periode', 'jenis', 'status']),
            'canGenerate' => $request->user()->can('create', Tagihan::class),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('create', Tagihan::class);

        $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $hasil = $this->tagihanService->generateBulanan($request->string('periode')->toString());

        return redirect()->route('tagihan.index')->with(
            'success',
            "Tagihan periode {$request->input('periode')} berhasil di-generate. Dibuat: {$hasil['dibuat']}, Diperbarui: {$hasil['diperbarui']}."
        );
    }
}

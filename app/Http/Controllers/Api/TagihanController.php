<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function __construct(private TagihanService $tagihanService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tagihan::class);

        return response()->json([
            'data' => $this->tagihanService->getList($request->user(), $request->only(['periode', 'jenis', 'status'])),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $this->authorize('create', Tagihan::class);

        $request->validate([
            'periode' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $hasil = $this->tagihanService->generateBulanan($request->string('periode')->toString());

        return response()->json(['data' => $hasil]);
    }
}

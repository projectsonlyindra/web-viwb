<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWargaRequest;
use App\Http\Requests\UpdateWargaRequest;
use App\Models\Warga;
use App\Services\WargaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WargaController extends Controller
{
    public function __construct(private WargaService $wargaService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Warga::class);

        return response()->json([
            'data' => $this->wargaService->getList($request->only(['blok', 'status'])),
        ]);
    }

    public function store(StoreWargaRequest $request): JsonResponse
    {
        $warga = $this->wargaService->create($request->validated());

        return response()->json(['data' => $warga], 201);
    }

    public function show(Warga $warga): JsonResponse
    {
        $this->authorize('view', $warga);

        return response()->json(['data' => $warga]);
    }

    public function update(UpdateWargaRequest $request, Warga $warga): JsonResponse
    {
        $warga = $this->wargaService->update($warga, $request->validated());

        return response()->json(['data' => $warga]);
    }

    public function destroy(Warga $warga): JsonResponse
    {
        $this->authorize('delete', $warga);

        $this->wargaService->delete($warga);

        return response()->json(null, 204);
    }
}

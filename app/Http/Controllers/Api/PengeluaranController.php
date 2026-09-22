<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\PengeluaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function __construct(private PengeluaranService $pengeluaranService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->pengeluaranService->getList($request->only(['status']))]);
    }

    public function store(StorePengeluaranRequest $request): JsonResponse
    {
        $pengeluaran = $this->pengeluaranService->create($request->validated(), $request->user(), $request->file('bukti'));

        return response()->json(['data' => $pengeluaran], 201);
    }

    public function submit(Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorize('create', Pengeluaran::class);

        return response()->json(['data' => $this->pengeluaranService->submitApproval($pengeluaran)]);
    }

    public function approve(Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorize('approve', $pengeluaran);

        return response()->json(['data' => $this->pengeluaranService->approve($pengeluaran, auth()->user())]);
    }

    public function reject(Request $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorize('approve', $pengeluaran);

        $request->validate(['catatan_review' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $this->pengeluaranService->reject($pengeluaran, auth()->user(), $request->input('catatan_review'))]);
    }
}

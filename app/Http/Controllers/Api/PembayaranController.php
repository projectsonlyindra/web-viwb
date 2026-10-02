<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Services\PembayaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $pembayaranService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->pembayaranService->getList($request->user(), $request->only(['status'])),
        ]);
    }

    public function store(StorePembayaranRequest $request): JsonResponse
    {
        $wargaId = $request->user()->warga_id;
        if (! $wargaId) {
            return response()->json([
                'message' => 'Akun Anda belum terhubung dengan data profil warga.',
            ], 422);
        }

        $pembayaran = $this->pembayaranService->create(
            $wargaId,
            $request->input('tagihan_ids'),
            $request->file('bukti'),
            $request->input('catatan'),
        );

        return response()->json(['data' => $pembayaran], 201);
    }

    public function confirm(Pembayaran $pembayaran): JsonResponse
    {
        $this->authorize('confirm', $pembayaran);

        return response()->json(['data' => $this->pembayaranService->confirm($pembayaran, auth()->user())]);
    }

    public function reject(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        $this->authorize('confirm', $pembayaran);

        $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $this->pembayaranService->reject($pembayaran, $request->input('catatan'))]);
    }
}

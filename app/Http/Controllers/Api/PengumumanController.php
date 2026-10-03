<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengumumanRequest;
use App\Services\PengumumanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function __construct(private PengumumanService $pengumumanService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->has('per_page') ? (int) $request->input('per_page') : 25;
        $pengumuman = $this->pengumumanService->getList($perPage);

        return response()->json($pengumuman);
    }

    public function store(StorePengumumanRequest $request): JsonResponse
    {
        $pengumuman = $this->pengumumanService->create(
            $request->input('judul'),
            $request->input('isi'),
            $request->input('target_blok'),
            null,
        );

        return response()->json(['data' => $pengumuman], 201);
    }
}

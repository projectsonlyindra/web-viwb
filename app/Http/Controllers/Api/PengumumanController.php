<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengumumanRequest;
use App\Services\PengumumanService;
use Illuminate\Http\JsonResponse;

class PengumumanController extends Controller
{
    public function __construct(private PengumumanService $pengumumanService) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->pengumumanService->getList()]);
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

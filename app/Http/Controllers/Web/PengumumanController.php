<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengumumanRequest;
use App\Models\Pengumuman;
use App\Services\PengumumanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengumumanController extends Controller
{
    public function __construct(private PengumumanService $pengumumanService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Pengumuman/Index', [
            'pengumuman' => $this->pengumumanService->getList(25),
            'canCreate' => $request->user()->can('create', Pengumuman::class),
        ]);
    }

    public function store(StorePengumumanRequest $request): RedirectResponse
    {
        $this->pengumumanService->create(
            $request->input('judul'),
            $request->input('isi'),
            $request->input('target_blok'),
            null,
        );

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat dan diantrekan untuk broadcast.');
    }
}

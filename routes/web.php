<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\LaporanController;
use App\Http\Controllers\Web\PembayaranController;
use App\Http\Controllers\Web\PengeluaranController;
use App\Http\Controllers\Web\PengumumanController;
use App\Http\Controllers\Web\TagihanController;
use App\Http\Controllers\Web\WargaController;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.local');

// Landing page = langsung halaman login (berisi juga pengumuman umum).
// Pengguna yang sudah login otomatis diarahkan ke /dashboard oleh middleware 'guest'.
Route::redirect('/', '/login');

Route::get('/dashboard', function (DashboardService $dashboardService) {
    return Inertia::render('Dashboard', $dashboardService->summary());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('warga', WargaController::class)->except(['create', 'edit', 'show']);

    Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::post('tagihan/generate', [TagihanController::class, 'generate'])->name('tagihan.generate');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
    Route::post('pembayaran/{pembayaran}/confirm', [PembayaranController::class, 'confirm'])->name('pembayaran.confirm');
    Route::post('pembayaran/{pembayaran}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject');

    Route::get('pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
    Route::post('pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
    Route::post('pengeluaran/{pengeluaran}/submit', [PengeluaranController::class, 'submit'])->name('pengeluaran.submit');
    Route::post('pengeluaran/{pengeluaran}/approve', [PengeluaranController::class, 'approve'])->name('pengeluaran.approve');
    Route::post('pengeluaran/{pengeluaran}/reject', [PengeluaranController::class, 'reject'])->name('pengeluaran.reject');

    Route::get('pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::post('pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
});

require __DIR__.'/auth.php';

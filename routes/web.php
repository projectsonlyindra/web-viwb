<?php

use App\Enums\Role;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\LaporanController;
use App\Http\Controllers\Web\PembayaranController;
use App\Http\Controllers\Web\PengaturanController;
use App\Http\Controllers\Web\PengeluaranController;
use App\Http\Controllers\Web\PengumumanController;
use App\Http\Controllers\Web\TagihanController;
use App\Http\Controllers\Web\WargaController;
use App\Models\Pembayaran;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/storage/{path}', function (Request $request, string $path) {
    abort_if(str_contains($path, '..'), 400, 'Format path tidak valid.');

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    abort_unless(in_array($extension, $allowedExtensions, true), 403, 'Format berkas tidak didukung.');

    abort_unless(Storage::disk('public')->exists($path), 404);

    $user = $request->user();

    if (in_array($user->role, [Role::SUPERADMIN, Role::KETUA_RT, Role::BENDAHARA], true)) {
        return Storage::disk('public')->response($path);
    }

    if ($user->role === Role::WARGA && $user->warga_id) {
        $punyaSaya = Pembayaran::query()
            ->where('warga_id', $user->warga_id)
            ->where('bukti_url', $path)
            ->exists();

        if ($punyaSaya) {
            return Storage::disk('public')->response($path);
        }
    }

    abort(403, 'Akses berkas tidak diizinkan.');
})->where('path', '.*')->middleware('auth')->name('storage.local');

// Landing page = langsung halaman login (berisi juga pengumuman umum).
// Pengguna yang sudah login otomatis diarahkan ke /dashboard oleh middleware 'guest'.
Route::redirect('/', '/login');

Route::get('/dashboard', function (Request $request, DashboardService $dashboardService) {
    return Inertia::render('Dashboard', $dashboardService->summaryForUser($request->user()));
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

    Route::middleware('role:SUPERADMIN,KETUA_RT,BENDAHARA')->group(function () {
        Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    });

    Route::middleware('role:SUPERADMIN,KETUA_RT')->group(function () {
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan/transparansi', [PengaturanController::class, 'updateTransparansi'])->name('pengaturan.transparansi');
        Route::put('pengaturan/layanan/{layanan}', [PengaturanController::class, 'updateLayanan'])->name('pengaturan.layanan');
    });
});

require __DIR__.'/auth.php';

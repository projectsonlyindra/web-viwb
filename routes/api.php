<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengumumanController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\WargaController;
use Illuminate\Support\Facades\Route;

$registerRoutes = function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        Route::apiResource('warga', WargaController::class);

        Route::get('tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::post('tagihan/generate', [TagihanController::class, 'generate'])
            ->middleware('throttle:10,1')
            ->name('tagihan.generate');

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
    });
};

Route::prefix('v1')->name('api.v1.')->group($registerRoutes);
Route::name('api.')->group($registerRoutes);

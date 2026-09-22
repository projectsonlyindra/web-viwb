<?php

use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengumumanController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\WargaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('warga', WargaController::class);

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
});

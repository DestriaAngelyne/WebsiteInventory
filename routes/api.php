<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PengaduanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('barang', BarangController::class);
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::post('/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject']);
        Route::post('/peminjaman/{peminjaman}/verifikasi', [PeminjamanController::class, 'verifikasi']);
        Route::post('/peminjaman/{peminjaman}/approve-perpanjangan', [PeminjamanController::class, 'approvePerpanjangan']);
        Route::post('/peminjaman/{peminjaman}/reject-perpanjangan', [PeminjamanController::class, 'rejectPerpanjangan']);
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/pengaduan', [PengaduanController::class, 'index']);
        Route::get('/pengaduan/{pengaduan}', [PengaduanController::class, 'show']);
        Route::post('/pengaduan/{pengaduan}/selesaikan', [PengaduanController::class, 'selesaikan']);
    });

    Route::get('/barang-tersedia', [BarangController::class, 'tersedia']);
    Route::get('/my-peminjaman', [PeminjamanController::class, 'myPeminjaman']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::post('/peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan']);
    Route::post('/peminjaman/{peminjaman}/batalkan', [PeminjamanController::class, 'batalkan']);
    Route::post('/peminjaman/{peminjaman}/minta-perpanjangan', [PeminjamanController::class, 'mintaPerpanjangan']);
    Route::get('/my-pengaduan', [PengaduanController::class, 'myPengaduan']);
    Route::post('/pengaduan', [PengaduanController::class, 'store']);
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\BiayaOperasionalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {

    // AUTH
    Route::get('/auth/me', [AuthController::class, 'me']);


    // PELANGGAN
    Route::middleware('role:ADMIN,KASIR')->group(function () {
        Route::get('/pelanggan', [PelangganController::class, 'index']);
        Route::post('/pelanggan', [PelangganController::class, 'store']);
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy']);
    });


    // LAYANAN
    Route::get('/layanan', [LayananController::class, 'index']);
    Route::get('/layanan/{id}', [LayananController::class, 'show']);

    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/layanan', [LayananController::class, 'store']);
        Route::delete('/layanan/{id}', [LayananController::class, 'destroy']);
    });

// TRANSAKSI
Route::middleware('role:ADMIN,KASIR,STAFF_LAUNDRY,OWNER')->group(function () {
    Route::get('/transaksi', [TransaksiController::class, 'index']);
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);
});

Route::middleware('role:ADMIN,KASIR')->group(function () {
    Route::post('/transaksi', [TransaksiController::class, 'store']);
    Route::post('/transaksi/{id}/pembayaran', [TransaksiController::class, 'pembayaran']);
});

Route::middleware('role:ADMIN,STAFF_LAUNDRY')->group(function () {
    Route::get('/proses-cucian', [TransaksiController::class, 'prosesCucian']);
    Route::post('/transaksi/{id}/proses', [TransaksiController::class, 'updateProses']);
});
   

// BIAYA OPERASIONAL
Route::middleware('role:ADMIN')->group(function () {
    Route::get('/biaya-operasional', [BiayaOperasionalController::class, 'index']);
    Route::post('/biaya-operasional', [BiayaOperasionalController::class, 'store']);
    Route::delete('/biaya-operasional/{id}', [BiayaOperasionalController::class, 'destroy']);
});

// LAPORAN
Route::middleware('role:ADMIN,OWNER')->group(function () {
    Route::get('/laporan/pendapatan', [LaporanController::class, 'pendapatan']);
    Route::get('/laporan/biaya', [LaporanController::class, 'biaya']);
    Route::get('/laporan/keuntungan', [LaporanController::class, 'keuntungan']);
});

    // MANAJEMEN PENGGUNA
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index']);
        Route::post('/pengguna', [PenggunaController::class, 'store']);
        Route::put('/pengguna/{id}/reset-password', [PenggunaController::class, 'resetPassword']);
        Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy']);
    });
});
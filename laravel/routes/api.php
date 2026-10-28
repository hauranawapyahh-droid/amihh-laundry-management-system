
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\BiayaOperasionalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PembayaranController;

// LOGIN
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('jwt')->group(function () {

    // AUTH
    Route::get('/auth/me', [AuthController::class, 'me']);

    // PELANGGAN
    Route::middleware('role:ADMIN,KASIR')->group(function () {
        Route::get('/pelanggan', [PelangganController::class, 'index']);
        Route::post('/pelanggan', [PelangganController::class, 'store']);
        Route::post('/pelanggan-umum', [PelangganController::class, 'pelangganUmum']);
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
        Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
        Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy']);
    });

    // LAYANAN
    Route::get('/layanan', [LayananController::class, 'index']);
    Route::get('/layanan/{id}', [LayananController::class, 'show']);

    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/layanan', [LayananController::class, 'store']);
        Route::put('/layanan/{id}', [LayananController::class, 'update']);
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

    // PEMBAYARAN: DAFTAR BUKTI MENUNGGU DAN VERIFIKASI
    Route::middleware('role:ADMIN,KASIR')->group(function () {
        Route::get('/pembayaran/menunggu', [PembayaranController::class, 'menunggu']);
        Route::post('/pembayaran/{id}/verifikasi', [PembayaranController::class, 'verifikasi']);
    });

    // BIAYA OPERASIONAL
    Route::middleware('role:ADMIN,OWNER')->group(function () {
        Route::get('/biaya-operasional', [BiayaOperasionalController::class, 'index']);
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/biaya-operasional', [BiayaOperasionalController::class, 'store']);
        Route::put('/biaya-operasional/{id}', [BiayaOperasionalController::class, 'update']);
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
        Route::put('/pengguna/{id}', [PenggunaController::class, 'update']);
        Route::put('/pengguna/{id}/reset-password', [PenggunaController::class, 'resetPassword']);
        Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy']);
    });

    // OWNER
    Route::middleware('role:OWNER,ADMIN')->prefix('owner')->group(function () {
        Route::get('/overview', [OwnerController::class, 'overview']);
        Route::get('/transaksi', [OwnerController::class, 'transaksi']);
        Route::get('/biaya', [OwnerController::class, 'biaya']);
    });

    // CUSTOMER
    Route::middleware('role:CUSTOMER')->prefix('customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard']);
        Route::put('/password', [CustomerController::class, 'ubahPassword']);

        Route::post(
            '/transaksi/{id}/bukti-pembayaran',
            [CustomerController::class, 'unggahBukti']
        );
    });
});

<?php

use App\Http\Controllers\Api\ApiAlatController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiPeminjamanController;
use App\Http\Controllers\Api\RfidVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIIS
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Endpoint Verifikasi Tap RFID dari Perangkat Hardware IoT ESP32
    Route::post('/rfid/verify', [RfidVerificationController::class, 'verify'])->name('api.rfid.verify');

    // 2. Autentikasi Flutter Mobile App
    Route::post('/auth/login', [ApiAuthController::class, 'login'])->name('api.auth.login');

    // 3. Endpoint Terautentikasi Mobile App (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Profil Pengguna & Logout
        Route::get('/auth/me', [ApiAuthController::class, 'me'])->name('api.auth.me');
        Route::post('/auth/logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout');

        // Modul Alat & Scan Barcode/QR
        Route::get('/alat', [ApiAlatController::class, 'index'])->name('api.alat.index');
        Route::get('/alat/{id}', [ApiAlatController::class, 'show'])->name('api.alat.show');
        Route::post('/alat', [ApiAlatController::class, 'store'])->name('api.alat.store');
        Route::patch('/unit-alat/{id}', [ApiAlatController::class, 'updateUnit'])->name('api.unit.update');
        Route::get('/alat/scan/{kode_alat}', [ApiAlatController::class, 'scan'])->name('api.alat.scan');
        Route::get('/kategori', [ApiAlatController::class, 'kategori'])->name('api.kategori.index');

        // Modul Peminjaman & Booking
        Route::get('/peminjaman', [ApiPeminjamanController::class, 'index'])->name('api.peminjaman.index');
        Route::post('/peminjaman', [ApiPeminjamanController::class, 'store'])->name('api.peminjaman.store');

        // Modul Approval & Pengembalian (Staff & Wakasek)
        Route::post('/peminjaman/{id}/approve', [ApiPeminjamanController::class, 'approve'])->name('api.peminjaman.approve');
        Route::post('/peminjaman/{id}/reject', [ApiPeminjamanController::class, 'reject'])->name('api.peminjaman.reject');
        Route::post('/peminjaman/{id}/return', [ApiPeminjamanController::class, 'returnItem'])->name('api.peminjaman.return');

        // Dashboard & Laporan (Staff, Wakasek, Kepsek)
        Route::get('/dashboard/stats', [ApiDashboardController::class, 'stats'])->name('api.dashboard.stats');
        Route::get('/dashboard/laporan', [ApiDashboardController::class, 'laporan'])->name('api.dashboard.laporan');
    });

});

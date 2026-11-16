<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IotController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KepalaSekolahController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIIS (Sistem Informasi Inventaris Sekolah)
|--------------------------------------------------------------------------
| Hierarki Role (RBAC):
| 1. super_admin    : Setting Data Master (User & RFID, Perangkat IoT ESP32, Kategori, Alat CRUD, Full Control)
| 2. staff_sarpras  : Tambah/Kelola Inventaris Alat & ACC / Verifikasi Peminjaman
| 3. wakasek_sarpras: Tambah/Kelola Inventaris Alat & ACC / Verifikasi Peminjaman
| 4. kepala_sekolah : View Only (Monitoring Dashboard Eksekutif & Laporan)
| 5. guru & siswa   : Peminjam (Katalog Alat Tersedia & Booking / Form Peminjaman)
|--------------------------------------------------------------------------
*/

// Root redirector berdasarkan role akun yang login
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'super_admin' => redirect()->route('sarpras.users.index'),
            'kepala_sekolah' => redirect()->route('kepsek.dashboard'),
            'staff_sarpras', 'wakasek_sarpras' => redirect()->route('sarpras.dashboard'),
            default => redirect()->route('peminjaman.index'),
        };
    }
    return redirect()->route('login');
});

// Autentikasi Pengguna
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Grup Route Terautentikasi (auth)
Route::middleware('auth')->group(function () {

    // --------------------------------------------------------------------------
    // RUTE BERSAMA (SHARED): Katalog Inventaris & Riwayat Peminjaman
    // - Siswa & Guru: Melihat katalog & status pinjaman sendiri
    // - Staff, Wakasek, Kepsek, Super Admin: Melihat seluruh katalog & seluruh pinjaman
    // --------------------------------------------------------------------------
    Route::get('/alat', [AlatController::class, 'index'])->name('alat.index');
    Route::get('/alat/{alat}', [AlatController::class, 'show'])->name('alat.show')->whereNumber('alat');
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');

    // --------------------------------------------------------------------------
    // LEVEL 1: SISWA & GURU (Peminjam Alat yang Tersedia)
    // --------------------------------------------------------------------------
    Route::middleware('role:siswa,guru')->group(function () {
        Route::get('/peminjaman-baru', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    });

    // --------------------------------------------------------------------------
    // LEVEL 2: STAFF SARPRAS & WAKASEK SARPRAS (Tambah Inventaris & ACC Peminjaman)
    // - Super Admin juga memiliki hak akses ini
    // --------------------------------------------------------------------------
    Route::middleware('role:super_admin,staff_sarpras,wakasek_sarpras')->group(function () {
        // Kelola Data Inventaris Alat (CRUD)
        Route::get('/alat/create', [AlatController::class, 'create'])->name('alat.create');
        Route::post('/alat', [AlatController::class, 'store'])->name('alat.store');
        Route::get('/alat/{alat}/edit', [AlatController::class, 'edit'])->name('alat.edit');
        Route::put('/alat/{alat}', [AlatController::class, 'update'])->name('alat.update');
        Route::delete('/alat/{alat}', [AlatController::class, 'destroy'])->name('alat.destroy');

        // Manajemen Rincian Unit Fisik (Unit 1 s/d N)
        Route::post('/alat/{alat}/units', [AlatController::class, 'addUnit'])->name('alat.units.add');
        Route::patch('/unit-alat/{unit}', [AlatController::class, 'updateUnitKondisi'])->name('unit-alat.update');
        Route::delete('/unit-alat/{unit}', [AlatController::class, 'destroyUnit'])->name('unit-alat.destroy');
        Route::get('/unit-alat/{unit}/qr', [AlatController::class, 'printUnitQr'])->name('unit-alat.qr');

        // Kategori Inventaris
        Route::resource('kategori', KategoriController::class)->except(['show']);
    });

    // ACC / Approval Peminjaman (Staff, Wakasek, Super Admin)
    Route::middleware('role:super_admin,staff_sarpras,wakasek_sarpras')->prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/dashboard', [ApprovalController::class, 'index'])->name('dashboard');
        Route::post('/peminjaman/{id}/approve', [ApprovalController::class, 'approve'])->name('peminjaman.approve');
        Route::post('/peminjaman/{id}/reject', [ApprovalController::class, 'reject'])->name('peminjaman.reject');
        Route::post('/peminjaman/{id}/borrow', [ApprovalController::class, 'markAsBorrowed'])->name('peminjaman.borrow');
        Route::post('/peminjaman/{id}/return', [ApprovalController::class, 'markAsReturned'])->name('peminjaman.return');

        // Cetak Stiker QR Code Alat
        Route::get('/alat/{alat}/qr', [AlatController::class, 'printQrLabel'])->name('alat.qr');

        // Cetak Laporan Resmi Peminjaman & Inventaris
        Route::get('/laporan/cetak', [ApprovalController::class, 'printReport'])->name('laporan.cetak');
    });

    // --------------------------------------------------------------------------
    // LEVEL 3: SUPER ADMIN (Setting Data Master: Akun User, Kartu RFID, & IoT ESP32)
    // --------------------------------------------------------------------------
    Route::middleware('role:super_admin')->prefix('sarpras')->name('sarpras.')->group(function () {
        // Manajemen Akun & Kartu RFID
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/rfid', [UserManagementController::class, 'updateRfid'])->name('users.rfid.update');

        // Monitoring Perangkat IoT ESP32 & RFID Master Control
        Route::get('/iot-devices', [IotController::class, 'devices'])->name('iot.devices');
        Route::post('/iot-devices', [IotController::class, 'storeDevice'])->name('iot.devices.store');
        Route::patch('/iot-devices/{device}/toggle', [IotController::class, 'toggleDeviceStatus'])->name('iot.devices.toggle');
        Route::get('/iot-logs', [IotController::class, 'logs'])->name('iot.logs');
        Route::get('/iot-simulator', [IotController::class, 'simulator'])->name('iot.simulator');
    });

    // --------------------------------------------------------------------------
    // LEVEL 4: KEPALA SEKOLAH (View Only: Dashboard Monitoring & Laporan Eksekutif)
    // - Super Admin juga dapat memantau dashboard eksekutif
    // --------------------------------------------------------------------------
    Route::middleware('role:kepala_sekolah,super_admin')->prefix('kepsek')->name('kepsek.')->group(function () {
        Route::get('/dashboard', [KepalaSekolahController::class, 'dashboard'])->name('dashboard');
    });

});

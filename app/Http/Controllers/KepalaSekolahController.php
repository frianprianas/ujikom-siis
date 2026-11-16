<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KepalaSekolahController extends Controller
{
    /**
     * Dashboard Monitoring Eksekutif Kepala Sekolah.
     */
    public function dashboard(Request $request): View
    {
        // 1. Statistik Ringkasan Inventaris
        $totalAlat = Alat::count();
        $totalUnitAlat = Alat::sum('stok_total');
        $unitTersedia = Alat::sum('stok_tersedia');
        $unitDipinjam = $totalUnitAlat - $unitTersedia;

        $alatKondisiBaik = Alat::where('kondisi', 'baik')->count();
        $alatRusakRingan = Alat::where('kondisi', 'rusak_ringan')->count();
        $alatRusakBerat = Alat::where('kondisi', 'rusak_berat')->count();

        // 2. Statistik Peminjaman
        $totalPeminjaman = Peminjaman::count();
        $menungguPersetujuan = Peminjaman::where('status', 'menunggu_persetujuan')->count();
        $sedangDipinjam = Peminjaman::where('status', 'sedang_dipinjam')->count();
        $selesaiKembali = Peminjaman::where('status', 'dikembalikan')->count();
        $terlambat = Peminjaman::where('status', 'terlambat')
            ->orWhere(function ($q) {
                $q->where('status', 'sedang_dipinjam')
                  ->where('tanggal_harus_kembali', '<', now()->toDateString());
            })->count();

        // 3. Rekap Pengguna
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();

        // 4. Data 10 Peminjaman Terkini
        $peminjamanTerkini = Peminjaman::with(['user', 'approvedBy', 'details.alat'])
            ->latest('tanggal_pengajuan')
            ->limit(10)
            ->get();

        // 5. Kategori beserta jumlah alat
        $kategoriStatistik = Kategori::withCount('alat')->get();

        return view('kepsek.dashboard', compact(
            'totalAlat',
            'totalUnitAlat',
            'unitTersedia',
            'unitDipinjam',
            'alatKondisiBaik',
            'alatRusakRingan',
            'alatRusakBerat',
            'totalPeminjaman',
            'menungguPersetujuan',
            'sedangDipinjam',
            'selesaiKembali',
            'terlambat',
            'totalSiswa',
            'totalGuru',
            'peminjamanTerkini',
            'kategoriStatistik'
        ));
    }
}


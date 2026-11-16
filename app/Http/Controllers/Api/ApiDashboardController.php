<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;

class ApiDashboardController extends Controller
{
    /**
     * Ringkasan statistik eksekutif untuk Kepala Sekolah pada Flutter Mobile.
     * GET /api/v1/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        $totalAlat = Alat::count();
        $totalUnitAlat = Alat::sum('stok_total');
        $unitTersedia = Alat::sum('stok_tersedia');
        $unitDipinjam = $totalUnitAlat - $unitTersedia;

        $alatBaik = Alat::where('kondisi', 'baik')->count();
        $alatRusakRingan = Alat::where('kondisi', 'rusak_ringan')->count();
        $alatRusakBerat = Alat::where('kondisi', 'rusak_berat')->count();

        $totalPeminjaman = Peminjaman::count();
        $menunggu = Peminjaman::where('status', 'menunggu_persetujuan')->count();
        $sedangDipinjam = Peminjaman::where('status', 'sedang_dipinjam')->count();
        $selesai = Peminjaman::where('status', 'dikembalikan')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_alat' => $totalAlat,
                'total_unit_alat' => $totalUnitAlat,
                'unit_tersedia' => $unitTersedia,
                'unit_dipinjam' => $unitDipinjam,
                'kondisi' => [
                    'baik' => $alatBaik,
                    'rusak_ringan' => $alatRusakRingan,
                    'rusak_berat' => $alatRusakBerat,
                ],
                'peminjaman' => [
                    'total' => $totalPeminjaman,
                    'menunggu' => $menunggu,
                    'sedang_dipinjam' => $sedangDipinjam,
                    'selesai' => $selesai,
                ],
            ],
        ]);
    }

    /**
     * Data Laporan Rekapitulasi Inventaris & Peminjaman untuk Mobile (Staff, Wakasek, Kepsek).
     * GET /api/v1/dashboard/laporan
     */
    public function laporan(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['staff_sarpras', 'wakasek_sarpras', 'kepala_sekolah'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses laporan ditolak untuk role peminjam.',
            ], 403);
        }

        $totalPeminjaman = Peminjaman::count();
        $totalDisetujui = Peminjaman::whereIn('status', ['disetujui', 'sedang_dipinjam', 'dikembalikan'])->count();
        $totalDitolak = Peminjaman::where('status', 'ditolak')->count();
        $totalDikembalikan = Peminjaman::where('status', 'dikembalikan')->count();

        $recentLoans = Peminjaman::with(['user', 'approvedBy', 'details.alat'])
            ->latest('updated_at')
            ->take(30)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan' => [
                    'total_peminjaman' => $totalPeminjaman,
                    'total_disetujui' => $totalDisetujui,
                    'total_ditolak' => $totalDitolak,
                    'total_dikembalikan' => $totalDikembalikan,
                ],
                'riwayat' => $recentLoans,
            ],
        ]);
    }
}


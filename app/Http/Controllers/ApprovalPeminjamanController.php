<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalPeminjamanController extends Controller
{
    /**
     * Dashboard Sarpras & Daftar Verifikasi Peminjaman.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = Peminjaman::with(['user', 'approvedBy', 'details.alat'])
            ->latest('tanggal_pengajuan');

        if ($status) {
            $query->where('status', $status);
        }

        $peminjamanList = $query->paginate(10)->withQueryString();

        // Hitung statistik ringkas untuk badge tab/kartu
        $counts = [
            'menunggu' => Peminjaman::where('status', 'menunggu_persetujuan')->count(),
            'disetujui' => Peminjaman::where('status', 'disetujui')->count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'sedang_dipinjam')->count(),
            'dikembalikan' => Peminjaman::where('status', 'dikembalikan')->count(),
            'ditolak' => Peminjaman::where('status', 'ditolak')->count(),
            'terlambat' => Peminjaman::where('status', 'terlambat')->count(),
        ];

        return view('sarpras.approval.index', compact('peminjamanList', 'status', 'counts'));
    }

    /**
     * Setujui Pengajuan Peminjaman oleh Staff atau Wakasek Sarpras.
     */
    public function approve(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu Persetujuan yang dapat disetujui.');
        }

        $request->validate([
            'catatan_approval' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $peminjaman) {
            // Cek ketersediaan stok kembali sebelum mengurangi
            foreach ($peminjaman->details as $detail) {
                $alat = $detail->alat;
                if ($alat->stok_tersedia < $detail->jumlah) {
                    return back()->with('error', "Stok alat '{$alat->nama_alat}' tidak mencukupi untuk disetujui.");
                }
            }

            // Kurangi stok tersedia
            foreach ($peminjaman->details as $detail) {
                $detail->alat->decrement('stok_tersedia', $detail->jumlah);
            }

            $peminjaman->update([
                'status' => 'disetujui',
                'approved_by_id' => Auth::id(),
                'tanggal_approval' => now(),
                'catatan_approval' => $request->catatan_approval,
            ]);

            return back()->with('success', "Pengajuan #{$peminjaman->id} berhasil disetujui oleh " . Auth::user()->nama . '.');
        });
    }

    /**
     * Tolak Pengajuan Peminjaman.
     */
    public function reject(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu Persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'catatan_approval' => ['required', 'string', 'max:500'],
        ], [
            'catatan_approval.required' => 'Wajib menyertakan alasan penolakan.',
        ]);

        $peminjaman->update([
            'status' => 'ditolak',
            'approved_by_id' => Auth::id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $request->catatan_approval,
        ]);

        return back()->with('success', "Pengajuan #{$peminjaman->id} telah ditolak.");
    }

    /**
     * Ubah status dari Disetujui menjadi Sedang Dipinjam (saat alat diambil oleh siswa/guru).
     */
    public function markAsBorrowed(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== 'disetujui') {
            return back()->with('error', 'Status peminjaman harus disetujui terlebih dahulu.');
        }

        $peminjaman->update(['status' => 'sedang_dipinjam']);

        return back()->with('success', "Status pengajuan #{$peminjaman->id} kini Sedang Dipinjam.");
    }

    /**
     * Proses Pengembalian Alat (Stok dikembalikan ke inventaris).
     */
    public function markAsReturned(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if (! in_array($peminjaman->status, ['sedang_dipinjam', 'terlambat'])) {
            return back()->with('error', 'Alat belum diambil atau sudah dikembalikan.');
        }

        return DB::transaction(function () use ($request, $peminjaman) {
            // Update kondisi kembali pada setiap detail item
            if ($request->has('kondisi_kembali')) {
                foreach ($request->input('kondisi_kembali', []) as $detailId => $kondisi) {
                    $peminjaman->details()->where('id', $detailId)->update([
                        'kondisi_saat_kembali' => $kondisi,
                    ]);
                }
            }

            // Kembalikan stok tersedia alat
            foreach ($peminjaman->details as $detail) {
                $detail->alat->increment('stok_tersedia', $detail->jumlah);
            }

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_aktual_kembali' => now(),
            ]);

            return back()->with('success', "Peminjaman #{$peminjaman->id} berhasil diselesaikan. Alat telah dikembalikan ke inventaris.");
        });
    }
}


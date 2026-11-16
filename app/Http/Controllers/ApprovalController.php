<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    /**
     * Tampilkan dashboard antrean approval untuk Staff & Wakasek Sarpras.
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
     * Menyetujui pengajuan peminjaman oleh Staff Sarpras ATAU Wakasek Sarpras.
     * Mengubah status menjadi 'disetujui', memotong stok_tersedia, dan mencatat approved_by_id.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu Persetujuan yang dapat disetujui.');
        }

        $request->validate([
            'catatan_approval' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $peminjaman) {
            // Cek ketersediaan stok aktual sebelum pengurangan
            foreach ($peminjaman->details as $detail) {
                $alat = $detail->alat;
                if ($alat->stok_tersedia < $detail->jumlah) {
                    return back()->with('error', "Stok alat '{$alat->nama_alat}' saat ini tidak mencukupi untuk disetujui.");
                }
            }

            // Kurangi stok tersedia alat
            foreach ($peminjaman->details as $detail) {
                $detail->alat->decrement('stok_tersedia', $detail->jumlah);
            }

            // Perbarui data peminjaman
            $peminjaman->update([
                'status' => 'disetujui',
                'approved_by_id' => Auth::id(),
                'tanggal_approval' => now(),
                'catatan_approval' => $request->input('catatan_approval'),
            ]);

            return back()->with('success', "Pengajuan #{$peminjaman->id} berhasil disetujui oleh " . Auth::user()->nama . '.');
        });
    }

    /**
     * Menolak pengajuan peminjaman oleh Staff Sarpras ATAU Wakasek Sarpras.
     * Mengubah status menjadi 'ditolak' dan mencatat catatan alasan penolakan.
     */
    public function reject(Request $request, $id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Hanya pengajuan berstatus Menunggu Persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'catatan_approval' => ['required', 'string', 'max:500'],
        ], [
            'catatan_approval.required' => 'Wajib menyertakan alasan penolakan peminjaman.',
        ]);

        $peminjaman->update([
            'status' => 'ditolak',
            'approved_by_id' => Auth::id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $request->input('catatan_approval'),
        ]);

        return back()->with('success', "Pengajuan #{$peminjaman->id} telah ditolak.");
    }

    /**
     * Konfirmasi pengambilan alat (Ubah status dari 'disetujui' menjadi 'sedang_dipinjam').
     */
    public function markAsBorrowed($id): RedirectResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'disetujui') {
            return back()->with('error', 'Status peminjaman harus sudah disetujui terlebih dahulu.');
        }

        $peminjaman->update(['status' => 'sedang_dipinjam']);

        return back()->with('success', "Status pengajuan #{$peminjaman->id} kini Sedang Dipinjam.");
    }

    /**
     * Konfirmasi pengembalian alat (Ubah status menjadi 'dikembalikan' & kembalikan stok).
     */
    public function markAsReturned(Request $request, $id): RedirectResponse
    {
        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if (! in_array($peminjaman->status, ['sedang_dipinjam', 'terlambat'])) {
            return back()->with('error', 'Alat belum diambil atau sudah pernah dikembalikan.');
        }

        return DB::transaction(function () use ($request, $peminjaman) {
            // Update kondisi kembali pada setiap item bila ada
            if ($request->has('kondisi_kembali')) {
                foreach ($request->input('kondisi_kembali', []) as $detailId => $kondisi) {
                    $peminjaman->details()->where('id', $detailId)->update([
                        'kondisi_saat_kembali' => $kondisi,
                    ]);
                }
            }

            // Kembalikan stok tersedia
            foreach ($peminjaman->details as $detail) {
                $detail->alat->increment('stok_tersedia', $detail->jumlah);
            }

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_aktual_kembali' => now(),
            ]);

            return back()->with('success', "Peminjaman #{$peminjaman->id} berhasil diselesaikan. Alat telah masuk kembali ke inventaris.");
        });
    }

    /**
     * Tampilkan cetak laporan resmi rekapitulasi inventaris & peminjaman.
     */
    public function printReport(Request $request): View
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $loans = Peminjaman::with(['user', 'approvedBy', 'details.alat'])
            ->whereDate('tanggal_pinjam', '>=', $startDate)
            ->whereDate('tanggal_pinjam', '<=', $endDate)
            ->orderBy('tanggal_pinjam')
            ->get();

        $alatList = \App\Models\Alat::with('kategori')->orderBy('nama_alat')->get();

        return view('sarpras.laporan.cetak', compact('loans', 'alatList', 'startDate', 'endDate'));
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\User;
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
        Peminjaman::cancelExpiredPendingLoans();

        $status = $request->query('status');

        $query = Peminjaman::with(['user', 'approvedBy', 'details.alat', 'details.unitAlat'])
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

            // Kurangi stok tersedia alat & sinkronkan unit fisik jika ada
            foreach ($peminjaman->details as $detail) {
                $detail->alat->decrement('stok_tersedia', $detail->jumlah);

                if (! $detail->unit_alat_id) {
                    $availableUnit = $detail->alat->units()->where('status', 'tersedia')->first();
                    if ($availableUnit) {
                        $detail->update(['unit_alat_id' => $availableUnit->id]);
                        $availableUnit->update(['status' => 'dipinjam']);
                    }
                }
            }

            $catatanInput = $request->input('catatan_approval');
            $catatanFinal = $peminjaman->catatan_approval;
            if ($catatanInput) {
                $catatanFinal = $catatanFinal ? "{$catatanFinal} | ACC: {$catatanInput}" : $catatanInput;
            }

            // Perbarui data peminjaman
            $peminjaman->update([
                'status' => 'disetujui',
                'approved_by_id' => Auth::id(),
                'tanggal_approval' => now(),
                'catatan_approval' => $catatanFinal,
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

        $catatanInput = $request->input('catatan_approval');
        $catatanFinal = $peminjaman->catatan_approval 
            ? "{$peminjaman->catatan_approval} | Alasan Penolakan: {$catatanInput}" 
            : $catatanInput;

        $peminjaman->update([
            'status' => 'ditolak',
            'approved_by_id' => Auth::id(),
            'tanggal_approval' => now(),
            'catatan_approval' => $catatanFinal,
        ]);

        return back()->with('success', "Pengajuan #{$peminjaman->id} telah ditolak.");
    }

    /**
     * Konfirmasi pengambilan alat (Ubah status dari 'disetujui' menjadi 'sedang_dipinjam').
     */
    public function markAsBorrowed($id): RedirectResponse
    {
        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if ($peminjaman->status !== 'disetujui') {
            return back()->with('error', 'Status peminjaman harus sudah disetujui terlebih dahulu.');
        }

        foreach ($peminjaman->details as $detail) {
            if ($detail->unit_alat_id) {
                $detail->unitAlat?->update(['status' => 'dipinjam']);
            }
        }

        $peminjaman->update(['status' => 'sedang_dipinjam']);

        return back()->with('success', "Status pengajuan #{$peminjaman->id} kini Sedang Dipinjam. Alat telah diserahkan fisik.");
    }

    /**
     * Konfirmasi pengembalian alat (Ubah status menjadi 'dikembalikan' & kembalikan stok).
     */
    public function markAsReturned(Request $request, $id): RedirectResponse
    {
        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if (! in_array($peminjaman->status, ['sedang_dipinjam', 'terlambat', 'disetujui'])) {
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
            } else {
                foreach ($peminjaman->details as $detail) {
                    if (! $detail->kondisi_saat_kembali) {
                        $detail->update(['kondisi_saat_kembali' => $detail->kondisi_saat_pinjam ?: 'baik']);
                    }
                }
            }

            // Kembalikan stok tersedia & pulihkan unit fisik
            foreach ($peminjaman->details as $detail) {
                $detail->alat->increment('stok_tersedia', $detail->jumlah);
                if ($detail->unit_alat_id) {
                    $kondisiAkhir = $detail->kondisi_saat_kembali ?: $detail->kondisi_saat_pinjam ?: 'baik';
                    $detail->unitAlat?->update([
                        'status' => 'tersedia',
                        'kondisi' => in_array($kondisiAkhir, ['baik', 'rusak_ringan', 'rusak_berat']) ? $kondisiAkhir : 'baik',
                    ]);
                }
            }

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_aktual_kembali' => now(),
            ]);

            return back()->with('success', "Peminjaman #{$peminjaman->id} berhasil diselesaikan. Alat telah masuk kembali ke inventaris.");
        });
    }

    /**
     * Tampilkan formulir pelayanan peminjaman langsung (tatap muka) di meja Sarpras.
     */
    public function createDirectLoan(Request $request): View
    {
        $selectedUserId = $request->query('user_id');
        $selectedAlatId = $request->query('alat_id');

        $peminjamList = User::whereIn('role', ['siswa', 'guru'])
            ->orderBy('role')
            ->orderBy('nama')
            ->get();

        // Ambil alat beserta unit-unit fisik spesifik yang berstatus 'tersedia'
        $alatTersedia = Alat::with(['kategori', 'units' => function ($q) {
                $q->where('status', 'tersedia')->where('kondisi', '!=', 'rusak_berat')->orderBy('kode_unit');
            }])
            ->where('stok_tersedia', '>', 0)
            ->orderBy('nama_alat')
            ->get();

        return view('sarpras.approval.direct_loan', compact('peminjamList', 'alatTersedia', 'selectedUserId', 'selectedAlatId'));
    }

    /**
     * Simpan peminjaman langsung di meja Sarpras dengan kode unit spesifik.
     */
    public function storeDirectLoan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_harus_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'integer', 'exists:alat,id'],
            'items.*.unit_alat_id' => ['nullable', 'integer', 'exists:unit_alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'user_id.required' => 'Pilih siswa atau guru peminjam yang datang ke Sarpras.',
            'items.min' => 'Pilih minimal 1 item alat / unit fisik yang diserahkan.',
            'tanggal_harus_kembali.after_or_equal' => 'Tanggal batas kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        $peminjam = User::findOrFail($validated['user_id']);

        return DB::transaction(function () use ($validated, $peminjam) {
            // 1. Verifikasi stok dan ketersediaan unit fisik
            foreach ($validated['items'] as $item) {
                if (!empty($item['unit_alat_id'])) {
                    $unit = \App\Models\UnitAlat::findOrFail($item['unit_alat_id']);
                    if ($unit->status !== 'tersedia') {
                        return back()->withInput()->with('error', "Unit fisik '{$unit->kode_unit}' sedang tidak tersedia.");
                    }
                } else {
                    $alat = Alat::findOrFail($item['alat_id']);
                    if ($alat->stok_tersedia < $item['jumlah']) {
                        return back()->withInput()->with('error', "Stok untuk alat '{$alat->nama_alat}' tidak mencukupi (Tersedia: {$alat->stok_tersedia} unit).");
                    }
                }
            }

            // 2. Buat Peminjaman dengan status LANGSUNG 'sedang_dipinjam'
            $peminjaman = Peminjaman::create([
                'user_id' => $peminjam->id,
                'tanggal_pengajuan' => now(),
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_harus_kembali' => $validated['tanggal_harus_kembali'],
                'status' => 'sedang_dipinjam',
                'approved_by_id' => Auth::id(),
                'tanggal_approval' => now(),
                'catatan_approval' => 'Layanan Tatap Muka Meja Sarpras' . (!empty($validated['keperluan']) ? " | Keperluan: {$validated['keperluan']}" : ''),
            ]);

            // 3. Buat Detail Peminjaman per unit fisik spesifik & potong stok
            $unitCodes = [];
            foreach ($validated['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                $unitAlatId = null;

                if (!empty($item['unit_alat_id'])) {
                    $unit = \App\Models\UnitAlat::findOrFail($item['unit_alat_id']);
                    $unitAlatId = $unit->id;
                    $unit->update(['status' => 'dipinjam']);
                    $unitCodes[] = $unit->kode_unit;
                } else {
                    // Cari unit fisik pertama yang tersedia
                    $availableUnit = $alat->units()->where('status', 'tersedia')->first();
                    if ($availableUnit) {
                        $unitAlatId = $availableUnit->id;
                        $availableUnit->update(['status' => 'dipinjam']);
                        $unitCodes[] = $availableUnit->kode_unit;
                    }
                }

                DetailPeminjaman::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alat->id,
                    'unit_alat_id' => $unitAlatId,
                    'jumlah' => $item['jumlah'] ?? 1,
                    'kondisi_saat_pinjam' => in_array($alat->kondisi, ['baik', 'rusak_ringan']) ? $alat->kondisi : 'baik',
                ]);

                $alat->decrement('stok_tersedia', $item['jumlah'] ?? 1);
            }

            $unitText = count($unitCodes) > 0 ? ' [Unit: ' . implode(', ', $unitCodes) . ']' : '';

            return redirect()->route('sarpras.dashboard')
                ->with('success', "Peminjaman langsung #{$peminjaman->id} berhasil dicatat. Barang fisik{$unitText} telah diserahkan di tempat kepada {$peminjam->nama}.");
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


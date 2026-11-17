<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiPeminjamanController extends Controller
{
    /**
     * Daftar peminjaman (Khusus peminjam atau semua untuk staf/wakasek/kepsek).
     * GET /api/v1/peminjaman
     */
    public function index(Request $request): JsonResponse
    {
        // Batalkan otomatis pemesanan yang melebihi batas 1 jam tanpa tindak lanjut
        Peminjaman::cancelExpiredPendingLoans();

        $user = $request->user();
        $status = $request->query('status');

        $query = Peminjaman::with(['user', 'approvedBy', 'details.alat.kategori', 'details.unitAlat'])
            ->latest('tanggal_pengajuan');

        if (in_array($user->role, ['siswa', 'guru'])) {
            $query->where('user_id', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $peminjamanList = $query->get();

        return response()->json([
            'success' => true,
            'data' => $peminjamanList,
        ]);
    }

    /**
     * Ajukan peminjaman baru dari Flutter mobile app.
     * POST /api/v1/peminjaman
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['siswa', 'guru'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa atau guru yang dapat mengajukan peminjaman.',
            ], 403);
        }

        $validated = $request->validate([
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_harus_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'integer', 'exists:alat,id'],
            'items.*.unit_alat_id' => ['nullable', 'integer', 'exists:unit_alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated, $user) {
            foreach ($validated['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                if ($alat->stok_tersedia < $item['jumlah']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok untuk '{$alat->nama_alat}' tidak mencukupi (Tersedia: {$alat->stok_tersedia} unit).",
                    ], 422);
                }

                if (!empty($item['unit_alat_id'])) {
                    $unit = \App\Models\UnitAlat::where('id', $item['unit_alat_id'])
                        ->where('alat_id', $alat->id)
                        ->first();

                    if (!$unit || $unit->status !== 'tersedia') {
                        return response()->json([
                            'success' => false,
                            'message' => "Unit fisik spesifik yang dipilih ({$unit?->kode_unit}) sedang tidak tersedia untuk dipinjam.",
                        ], 422);
                    }
                }
            }

            $keperluan = $validated['keperluan'] ?? '';
            $catatanText = trim("Keperluan: {$keperluan} [Batas Konfirmasi: Maks 1 Jam Tatap Muka di Sarpras]");

            $peminjaman = Peminjaman::create([
                'user_id' => $user->id,
                'tanggal_pengajuan' => now(),
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_harus_kembali' => $validated['tanggal_harus_kembali'],
                'catatan_approval' => $catatanText,
                'status' => 'menunggu_persetujuan',
            ]);

            foreach ($validated['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                $unitAlatId = null;

                if (!empty($item['unit_alat_id'])) {
                    $unit = \App\Models\UnitAlat::findOrFail($item['unit_alat_id']);
                    $unitAlatId = $unit->id;
                    $unit->update(['status' => 'dipinjam']);
                } else {
                    $availableUnit = $alat->units()->where('status', 'tersedia')->first();
                    if ($availableUnit) {
                        $unitAlatId = $availableUnit->id;
                        $availableUnit->update(['status' => 'dipinjam']);
                    }
                }

                DetailPeminjaman::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alat->id,
                    'unit_alat_id' => $unitAlatId,
                    'jumlah' => $item['jumlah'],
                    'kondisi_saat_pinjam' => in_array($alat->kondisi, ['baik', 'rusak_ringan']) ? $alat->kondisi : 'baik',
                ]);

                // Kurangi stok sementara selama pesanan aktif
                $alat->decrement('stok_tersedia', $item['jumlah']);
            }

            $peminjaman->load(['details.alat.kategori', 'details.unitAlat']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan peminjaman berhasil dibuat. Harap segera konfirmasi tatap muka ke ruang Sarpras dalam waktu maksimal 1 jam.',
                'data' => $peminjaman,
            ], 201);
        });
    }

    /**
     * Setujui peminjaman langsung dari Flutter (Staff & Wakasek Sarpras).
     * POST /api/v1/peminjaman/{id}/approve
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menyetujui peminjaman.',
            ], 403);
        }

        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan berstatus menunggu persetujuan yang dapat disetujui.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $peminjaman, $user) {
            foreach ($peminjaman->details as $detail) {
                if (! $detail->unit_alat_id) {
                    if ($detail->alat->stok_tersedia < $detail->jumlah) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok alat '{$detail->alat->nama_alat}' tidak mencukupi.",
                        ], 422);
                    }
                    $detail->alat->decrement('stok_tersedia', $detail->jumlah);
                    $availableUnit = $detail->alat->units()->where('status', 'tersedia')->first();
                    if ($availableUnit) {
                        $detail->update(['unit_alat_id' => $availableUnit->id]);
                        $availableUnit->update(['status' => 'dipinjam']);
                    }
                }
            }

            $peminjaman->update([
                'status' => 'disetujui',
                'approved_by_id' => $user->id,
                'tanggal_approval' => now(),
                'catatan_approval' => $request->input('catatan_approval'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pengajuan #{$peminjaman->id} berhasil disetujui.",
                'data' => $peminjaman->fresh(['details.alat', 'details.unitAlat', 'user']),
            ]);
        });
    }

    /**
     * Tolak peminjaman langsung dari Flutter (Staff & Wakasek Sarpras).
     * POST /api/v1/peminjaman/{id}/reject
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk menolak peminjaman.',
            ], 403);
        }

        $peminjaman = Peminjaman::with('details.alat', 'details.unitAlat')->findOrFail($id);

        $request->validate([
            'catatan_approval' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $peminjaman, $user) {
            // Pulihkan stok dan status unit fisik jika sempat dikunci saat pemesanan
            foreach ($peminjaman->details as $detail) {
                if ($detail->unit_alat_id) {
                    $detail->alat?->increment('stok_tersedia', $detail->jumlah);
                    $detail->unitAlat?->update(['status' => 'tersedia']);
                }
            }

            $peminjaman->update([
                'status' => 'ditolak',
                'approved_by_id' => $user->id,
                'tanggal_approval' => now(),
                'catatan_approval' => $request->input('catatan_approval'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pengajuan #{$peminjaman->id} telah ditolak dan unit fisik telah dikembalikan ke inventaris.",
                'data' => $peminjaman->fresh(['details.alat', 'details.unitAlat']),
            ]);
        });
    }

    /**
     * Konfirmasi pengembalian alat & pulihkan stok dari mobile app (Staff & Wakasek Sarpras).
     * POST /api/v1/peminjaman/{id}/return
     */
    public function returnItem(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk memproses pengembalian alat.',
            ], 403);
        }

        $peminjaman = Peminjaman::with('details.alat')->findOrFail($id);

        if (! in_array($peminjaman->status, ['sedang_dipinjam', 'terlambat', 'disetujui'])) {
            return response()->json([
                'success' => false,
                'message' => 'Alat belum diambil atau sudah pernah dikembalikan.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $peminjaman) {
            if ($request->has('kondisi_kembali')) {
                foreach ($request->input('kondisi_kembali', []) as $detailId => $kondisi) {
                    $peminjaman->details()->where('id', $detailId)->update([
                        'kondisi_saat_kembali' => $kondisi,
                    ]);
                }
            }

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

            return response()->json([
                'success' => true,
                'message' => "Peminjaman #{$peminjaman->id} berhasil dikembalikan. Stok alat telah dipulihkan.",
                'data' => $peminjaman->fresh(['details.alat', 'details.unitAlat', 'user']),
            ]);
        });
    }
}


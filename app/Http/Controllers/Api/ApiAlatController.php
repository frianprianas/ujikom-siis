<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\UnitAlat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAlatController extends Controller
{
    /**
     * Dapatkan daftar inventaris alat dengan filter search dan kategori.
     * GET /api/v1/alat
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');
        $kondisi = $request->query('kondisi');

        $query = Alat::with(['kategori', 'units']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_alat', 'like', "%{$search}%")
                  ->orWhere('kode_alat', 'like', "%{$search}%")
                  ->orWhere('lokasi_penyimpanan', 'like', "%{$search}%");
            });
        }

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($kondisi) {
            $query->where('kondisi', $kondisi);
        }

        $alatList = $query->orderBy('nama_alat')->get();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'alat' => $alatList,
                'kategori' => $kategoris,
            ],
        ]);
    }

    /**
     * Dapatkan rincian satu alat beserta unit-unit fisiknya (Unit 1 s/d N).
     * GET /api/v1/alat/{id}
     */
    public function show(int $id): JsonResponse
    {
        $alat = Alat::with(['kategori', 'units' => function ($q) {
            $q->orderBy('kode_unit', 'asc');
        }])->find($id);

        if (! $alat) {
            return response()->json([
                'success' => false,
                'message' => 'Alat inventaris tidak ditemukan.',
            ], 404);
        }

        if ($alat->units->isEmpty() && $alat->stok_total > 0) {
            $alat->generateUnits($alat->stok_total, $alat->kondisi, $alat->lokasi_penyimpanan);
            $alat->load('units');
        }

        return response()->json([
            'success' => true,
            'data' => $alat,
        ]);
    }

    /**
     * Scan Barcode / QR Code alat untuk mobile app (bisa kode master alat maupun kode unit fisik).
     * GET /api/v1/alat/scan/{kode_alat}
     */
    public function scan(string $kodeAlat): JsonResponse
    {
        $kode = trim($kodeAlat);
        $scannedUnit = null;

        // 1. Cari berdasarkan kode master alat (contoh: PROJ-001)
        $alat = Alat::with(['kategori', 'units'])->where('kode_alat', $kode)->first();

        // 2. Jika tidak ditemukan, cari berdasarkan kode unit fisik individual (contoh: PROJ-001-01) atau nomor seri
        if (! $alat) {
            $scannedUnit = \App\Models\UnitAlat::with('alat.kategori')
                ->where('kode_unit', $kode)
                ->orWhere('nomor_seri', $kode)
                ->first();

            if ($scannedUnit && $scannedUnit->alat) {
                $alat = $scannedUnit->alat;
                $alat->load(['kategori', 'units']);
            }
        }

        if (! $alat) {
            return response()->json([
                'success' => false,
                'message' => "Alat atau unit dengan kode '{$kodeAlat}' tidak ditemukan pada sistem inventaris.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $scannedUnit ? "Unit fisik '{$scannedUnit->kode_unit}' berhasil diidentifikasi." : 'Alat berhasil dipindai.',
            'data' => $alat,
            'unit' => $scannedUnit,
        ]);
    }

    /**
     * Tambah alat baru dari Flutter Mobile App (Khusus Staff & Wakasek Sarpras).
     * POST /api/v1/alat
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['staff_sarpras', 'wakasek_sarpras'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Staff dan Wakasek Sarpras yang berwenang menginput data inventaris.',
            ], 403);
        }

        $validated = $request->validate([
            'kategori_id' => ['required', 'exists:kategori,id'],
            'nama_alat' => ['required', 'string', 'max:150'],
            'kode_alat' => ['required', 'string', 'max:50', 'unique:alat,kode_alat'],
            'kondisi' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'stok_total' => ['required', 'integer', 'min:1'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['stok_tersedia'] = $validated['stok_total'];

        $alat = Alat::create($validated);
        $alat->generateUnits((int) $validated['stok_total'], $validated['kondisi'] ?? 'baik', $validated['lokasi_penyimpanan'] ?? null);
        $alat->load(['kategori', 'units']);

        return response()->json([
            'success' => true,
            'message' => "Alat '{$alat->nama_alat}' dan {$alat->stok_total} unit fisik berhasil ditambahkan ke inventaris.",
            'data' => $alat,
        ], 201);
    }

    /**
     * Update kondisi dan status 1 unit fisik dari Mobile App (Staff & Wakasek Sarpras).
     * PATCH /api/v1/unit-alat/{id}
     */
    public function updateUnit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['staff_sarpras', 'wakasek_sarpras', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Staff dan Wakasek Sarpras yang berwenang mengubah kondisi unit.',
            ], 403);
        }

        $unit = UnitAlat::find($id);
        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit fisik tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'kondisi' => ['required', 'in:baik,rusak_ringan,rusak_berat'],
            'status' => ['required', 'in:tersedia,dipinjam,perbaikan,hilang'],
            'nomor_seri' => ['nullable', 'string', 'max:100'],
            'lokasi_spesifik' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $unit->update($validated);
        $unit->alat->syncStockFromUnits();

        return response()->json([
            'success' => true,
            'message' => "Kondisi Unit {$unit->kode_unit} berhasil diperbarui.",
            'data' => $unit->fresh(),
        ]);
    }

    /**
     * Dapatkan daftar kategori inventaris untuk dropdown pilihan mobile.
     * GET /api/v1/kategori
     */
    public function kategori(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::orderBy('nama_kategori')->get(),
        ]);
    }
}


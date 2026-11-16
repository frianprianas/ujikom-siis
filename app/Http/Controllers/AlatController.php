<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlatRequest;
use App\Http\Requests\UpdateAlatRequest;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\UnitAlat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlatController extends Controller
{
    /**
     * Menampilkan daftar inventaris alat.
     * Dapat diakses oleh semua role dengan hak tampilan berbeda:
     * - Siswa & Guru: Melihat stok dan ketersediaan untuk meminjam.
     * - Staff Sarpras: Akses penuh untuk manajemen (tambah, edit, hapus).
     * - Wakasek & Kepala Sekolah: Monitoring inventaris.
     */
    public function index(Request $request): View
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

        $alatList = $query->latest()->paginate(12)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('alat.index', compact('alatList', 'kategoris', 'search', 'kategoriId', 'kondisi'));
    }

    /**
     * Tampilkan form pembuatan alat baru (Khusus staff_sarpras).
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('sarpras.alat.create', compact('kategoris'));
    }

    /**
     * Detail alat dan seluruh unit fisiknya (Unit 1 - N beserta kondisi masing-masing).
     * Dapat diakses oleh Siswa, Guru, Staff, Wakasek, Kepsek, dan Admin.
     */
    public function show(Alat $alat): View
    {
        $alat->load(['kategori', 'units' => function ($q) {
            $q->orderBy('kode_unit', 'asc');
        }]);

        // Jika master alat belum punya data unit fisik, generate otomatis sesuai stok total
        if ($alat->units->isEmpty() && $alat->stok_total > 0) {
            $alat->generateUnits($alat->stok_total, $alat->kondisi, $alat->lokasi_penyimpanan);
            $alat->load('units');
        }

        return view('alat.show', compact('alat'));
    }

    /**
     * Simpan data alat baru ke database (Khusus staff_sarpras).
     */
    public function store(StoreAlatRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Stok tersedia saat pertama kali dibuat sama dengan stok total
        $validated['stok_tersedia'] = $validated['stok_total'];

        $alat = Alat::create($validated);

        // Otomatis generate unit fisik berdasarkan stok awal (misal 10 unit Asus -> unit 001 s/d 010)
        $alat->generateUnits((int) $validated['stok_total'], $validated['kondisi'] ?? 'baik', $validated['lokasi_penyimpanan'] ?? null);

        return redirect()->route('alat.index')->with('success', "Data alat inventaris dan {$alat->stok_total} unit fisik berhasil ditambahkan.");
    }

    /**
     * Tampilkan form edit alat (Khusus staff_sarpras).
     */
    public function edit(Alat $alat): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('sarpras.alat.edit', compact('alat', 'kategoris'));
    }

    /**
     * Perbarui data alat (Khusus staff_sarpras).
     */
    public function update(UpdateAlatRequest $request, Alat $alat): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung selisih unit yang sedang dipinjam agar stok_tersedia tetap konsisten
        $unitDipinjam = $alat->stok_total - $alat->stok_tersedia;
        $validated['stok_tersedia'] = max(0, $validated['stok_total'] - $unitDipinjam);

        $alat->update($validated);

        return redirect()->route('alat.index')->with('success', 'Data alat inventaris berhasil diperbarui.');
    }

    /**
     * Hapus alat inventaris (Khusus staff_sarpras).
     */
    public function destroy(Alat $alat): RedirectResponse
    {
        // Cegah penghapusan jika ada peminjaman aktif yang melibatkan alat ini
        $sedangDipinjam = $alat->detailPeminjaman()
            ->whereHas('peminjaman', function ($q) {
                $q->whereIn('status', ['disetujui', 'sedang_dipinjam', 'terlambat']);
            })->exists();

        if ($sedangDipinjam) {
            return back()->with('error', 'Alat ini tidak dapat dihapus karena masih terdaftar dalam peminjaman aktif.');
        }

        $alat->delete();

        return redirect()->route('alat.index')->with('success', 'Data alat inventaris berhasil dihapus.');
    }

    /**
     * Tampilkan cetak stiker label QR Code alat inventaris.
     */
    public function printQrLabel(Alat $alat): View
    {
        return view('sarpras.alat.qr_label', compact('alat'));
    }

    /**
     * Tambah unit fisik baru untuk alat yang sudah ada (Staff, Wakasek, Admin).
     */
    public function addUnit(Request $request, Alat $alat): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah' => 'nullable|integer|min:1|max:50',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'lokasi_spesifik' => 'nullable|string|max:100',
            'catatan' => 'nullable|string|max:255',
        ]);

        $jumlah = (int) ($validated['jumlah'] ?? 1);
        $alat->generateUnits($jumlah, $validated['kondisi'], $validated['lokasi_spesifik'] ?? null);

        return back()->with('success', "Berhasil menambahkan {$jumlah} unit fisik baru untuk {$alat->nama_alat}.");
    }

    /**
     * Perbarui kondisi fisik, status, atau catatan dari unit tertentu (Staff, Wakasek, Admin).
     */
    public function updateUnitKondisi(Request $request, UnitAlat $unit): RedirectResponse
    {
        $validated = $request->validate([
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'status' => 'required|in:tersedia,dipinjam,perbaikan,hilang',
            'nomor_seri' => 'nullable|string|max:100',
            'lokasi_spesifik' => 'nullable|string|max:100',
            'catatan' => 'nullable|string|max:255',
        ]);

        $unit->update($validated);
        $unit->alat->syncStockFromUnits();

        return back()->with('success', "Status dan kondisi fisik Unit {$unit->kode_unit} berhasil diperbarui.");
    }

    /**
     * Hapus unit fisik tertentu (Staff, Wakasek, Admin).
     */
    public function destroyUnit(UnitAlat $unit): RedirectResponse
    {
        if ($unit->status === 'dipinjam') {
            return back()->with('error', "Unit {$unit->kode_unit} sedang dipinjam dan tidak dapat dihapus.");
        }

        $alat = $unit->alat;
        $unit->delete();
        $alat->syncStockFromUnits();

        return back()->with('success', "Unit {$unit->kode_unit} berhasil dihapus dari inventaris.");
    }

    /**
     * Cetak stiker label QR Code individual untuk 1 unit fisik.
     */
    public function printUnitQr(UnitAlat $unit): View
    {
        $unit->load('alat.kategori');
        return view('sarpras.alat.unit_qr_label', compact('unit'));
    }
}

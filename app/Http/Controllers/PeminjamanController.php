<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePeminjamanRequest;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar peminjaman:
     * - Jika Siswa/Guru: Hanya menampilkan peminjaman milik akun login.
     * - Jika Staff Sarpras / Wakasek Sarpras / Kepala Sekolah: Menampilkan semua data peminjaman di sekolah.
     */
    public function index(Request $request): View
    {
        Peminjaman::cancelExpiredPendingLoans();

        $user = Auth::user();
        $status = $request->query('status');

        $query = Peminjaman::with(['user', 'approvedBy', 'details.alat', 'details.unitAlat'])
            ->latest('tanggal_pengajuan');

        // Filter kepemilikan berdasarkan role pengguna
        if (in_array($user->role, ['siswa', 'guru'])) {
            $query->where('user_id', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $peminjamanList = $query->paginate(10)->withQueryString();

        return view('peminjaman.index', compact('peminjamanList', 'status'));
    }

    /**
     * Tampilkan form pembuatan pengajuan peminjaman baru (Khusus Siswa & Guru).
     */
    public function create(Request $request): View
    {
        $selectedAlatId = $request->query('alat_id');

        $alatTersedia = Alat::with('kategori')
            ->where('stok_tersedia', '>', 0)
            ->orderBy('nama_alat')
            ->get();

        return view('peminjaman.create', compact('alatTersedia', 'selectedAlatId'));
    }

    /**
     * Simpan pengajuan peminjaman baru dengan validasi StorePeminjamanRequest.
     * Status pengajuan otomatis diset menjadi 'menunggu_persetujuan'.
     */
    public function store(StorePeminjamanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $user = Auth::user();

            // Verifikasi kembali ketersediaan stok aktual sebelum pengajuan dibuat
            foreach ($validated['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                if ($alat->stok_tersedia < $item['jumlah']) {
                    return back()
                        ->withInput()
                        ->with('error', "Stok untuk alat '{$alat->nama_alat}' tidak mencukupi (Tersedia: {$alat->stok_tersedia} unit).");
                }
            }

            // 1. Buat Header Pengajuan Peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $user->id,
                'tanggal_pengajuan' => now(),
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_harus_kembali' => $validated['tanggal_harus_kembali'],
                'catatan_approval' => !empty($validated['keperluan']) ? 'Keperluan: ' . $validated['keperluan'] : null,
                'status' => 'menunggu_persetujuan',
            ]);

            // 2. Buat Detail Item Peminjaman
            foreach ($validated['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);

                DetailPeminjaman::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alat->id,
                    'jumlah' => $item['jumlah'],
                    'kondisi_saat_pinjam' => in_array($alat->kondisi, ['baik', 'rusak_ringan']) ? $alat->kondisi : 'rusak_ringan',
                ]);
            }

            return redirect()->route('peminjaman.index')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim. Menunggu verifikasi dari Staff/Wakasek Sarpras.');
        });
    }

    /**
     * Menampilkan detail lengkap suatu peminjaman.
     */
    public function show(Peminjaman $peminjaman): View
    {
        $user = Auth::user();

        // Otorisasi: Siswa/Guru hanya boleh melihat miliknya sendiri, staff/wakasek/kepsek boleh melihat semua
        if (in_array($user->role, ['siswa', 'guru']) && $peminjaman->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat data peminjaman ini.');
        }

        $peminjaman->load(['user', 'approvedBy', 'details.alat.kategori', 'details.unitAlat']);

        return view('peminjaman.show', compact('peminjaman'));
    }
}

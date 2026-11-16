<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use App\Models\IotLog;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidVerificationController extends Controller
{
    /**
     * Endpoint API Verifikasi Tap RFID dari ESP32 di Meja Sarpras.
     * POST /api/v1/rfid/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-Id');
        $apiKey = $request->input('api_key') ?? $request->header('X-Api-Key');
        $rfidUid = trim((string) $request->input('rfid_uid'));

        if (! $rfidUid) {
            return response()->json([
                'success' => false,
                'status' => 'bad_request',
                'message' => 'Parameter rfid_uid wajib dikirimkan oleh perangkat ESP32.',
            ], 422);
        }

        // 1. Verifikasi Keaslian & Status Perangkat IoT
        $device = IotDevice::where('device_id', $deviceId)->first();

        if (! $device || $device->api_key !== $apiKey || ! $device->isActive()) {
            IotLog::create([
                'device_id' => $deviceId ?? 'UNKNOWN_DEVICE',
                'user_id' => null,
                'rfid_uid' => $rfidUid,
                'tipe_akses' => 'auth_device',
                'status_aksi' => 'unauthorized',
                'keterangan' => 'Perangkat IoT tidak terdaftar, API Key salah, atau berstatus inactive.',
            ]);

            return response()->json([
                'success' => false,
                'status' => 'unauthorized',
                'message' => 'Autentikasi perangkat IoT gagal.',
            ], 401);
        }

        // 2. Cari Akun Pengguna berdasarkan rfid_uid
        $user = User::where('rfid_uid', $rfidUid)->first();

        if (! $user) {
            IotLog::create([
                'device_id' => $device->device_id,
                'user_id' => null,
                'rfid_uid' => $rfidUid,
                'tipe_akses' => 'tap_rfid',
                'status_aksi' => 'failed',
                'keterangan' => "Kartu RFID UID '{$rfidUid}' belum terdaftar pada akun peminjam manapun.",
            ]);

            return response()->json([
                'success' => false,
                'status' => 'user_not_found',
                'message' => 'Kartu RFID belum terdaftar di sistem SIIS.',
                'rfid_uid' => $rfidUid,
            ], 404);
        }

        $today = now()->toDateString();

        // 3. Periksa Apakah User Memiliki Jadwal Peminjaman yang Telah Disetujui (Berlaku Hari Ini)
        $peminjamanDisetujui = Peminjaman::with('details.alat')
            ->where('user_id', $user->id)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_pinjam', '<=', $today)
            ->whereDate('tanggal_harus_kembali', '>=', $today)
            ->orderBy('tanggal_pinjam')
            ->first();

        // Jika tidak ada yang strictly rentang tanggal hari ini, cek peminjaman disetujui terdekat
        if (! $peminjamanDisetujui) {
            $peminjamanDisetujui = Peminjaman::with('details.alat')
                ->where('user_id', $user->id)
                ->where('status', 'disetujui')
                ->orderBy('tanggal_pinjam')
                ->first();
        }

        if ($peminjamanDisetujui) {
            // Ubah status menjadi 'sedang_dipinjam' (alat diambil fisik di meja sarpras)
            $peminjamanDisetujui->update([
                'status' => 'sedang_dipinjam',
            ]);

            // Format daftar alat yang dipinjam
            $detailAlat = $peminjamanDisetujui->details->map(fn ($d) => [
                'nama_alat' => $d->alat->nama_alat ?? 'Alat Dihapus',
                'kode_alat' => $d->alat->kode_alat ?? '-',
                'jumlah' => $d->jumlah,
                'kondisi' => $d->kondisi_saat_pinjam,
            ]);

            IotLog::create([
                'device_id' => $device->device_id,
                'user_id' => $user->id,
                'rfid_uid' => $rfidUid,
                'tipe_akses' => 'pengambilan_alat',
                'status_aksi' => 'success',
                'keterangan' => "Peminjaman #{$peminjamanDisetujui->id} berhasil diverifikasi. Alat diserahkan kepada {$user->nama}.",
            ]);

            return response()->json([
                'success' => true,
                'status' => 'borrow_confirmed',
                'message' => "Verifikasi berhasil. Selamat menggunakan alat, {$user->nama}!",
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'role' => $user->role,
                    'nomor_identitas' => $user->nomor_identitas,
                    'kelas_atau_jabatan' => $user->kelas_atau_jabatan,
                ],
                'peminjaman' => [
                    'id' => $peminjamanDisetujui->id,
                    'status' => 'sedang_dipinjam',
                    'tanggal_pinjam' => $peminjamanDisetujui->tanggal_pinjam->format('Y-m-d'),
                    'tanggal_harus_kembali' => $peminjamanDisetujui->tanggal_harus_kembali->format('Y-m-d'),
                    'alat' => $detailAlat,
                ],
            ], 200);
        }

        // Skenario B: Pengguna sedang memiliki peminjaman aktif (menunggu pengembalian alat)
        $peminjamanAktif = Peminjaman::with('details.alat')
            ->where('user_id', $user->id)
            ->whereIn('status', ['sedang_dipinjam', 'terlambat'])
            ->orderBy('tanggal_harus_kembali')
            ->first();

        if ($peminjamanAktif) {
            $detailAlat = $peminjamanAktif->details->map(fn ($d) => [
                'nama_alat' => $d->alat->nama_alat ?? 'Alat Dihapus',
                'kode_alat' => $d->alat->kode_alat ?? '-',
                'jumlah' => $d->jumlah,
            ]);

            IotLog::create([
                'device_id' => $device->device_id,
                'user_id' => $user->id,
                'rfid_uid' => $rfidUid,
                'tipe_akses' => 'cek_pengembalian',
                'status_aksi' => 'success',
                'keterangan' => "Tap RFID peminjaman aktif #{$peminjamanAktif->id} terdeteksi untuk pengembalian.",
            ]);

            return response()->json([
                'success' => true,
                'status' => 'active_loan_detected',
                'message' => "Peminjaman aktif #{$peminjamanAktif->id} terdeteksi. Petugas siap memeriksa pengembalian alat.",
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'role' => $user->role,
                ],
                'peminjaman' => [
                    'id' => $peminjamanAktif->id,
                    'status' => $peminjamanAktif->status,
                    'tanggal_harus_kembali' => $peminjamanAktif->tanggal_harus_kembali->format('Y-m-d'),
                    'alat' => $detailAlat,
                ],
            ], 200);
        }

        // Skenario C: Kartu terdaftar, tetapi tidak ada jadwal peminjaman disetujui hari ini
        IotLog::create([
            'device_id' => $device->device_id,
            'user_id' => $user->id,
            'rfid_uid' => $rfidUid,
            'tipe_akses' => 'cek_jadwal',
            'status_aksi' => 'failed',
            'keterangan' => "Tap RFID atas nama {$user->nama}, tetapi tidak ada peminjaman berstatus 'disetujui' untuk diambil.",
        ]);

        return response()->json([
            'success' => false,
            'status' => 'no_approved_loan',
            'message' => "Halo {$user->nama}, Anda tidak memiliki pengajuan peminjaman yang disetujui untuk diambil hari ini.",
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'role' => $user->role,
                'nomor_identitas' => $user->nomor_identitas,
                'kelas_atau_jabatan' => $user->kelas_atau_jabatan,
            ],
        ], 200);
    }
}

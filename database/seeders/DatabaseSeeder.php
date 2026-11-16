<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\IotDevice;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna untuk Setiap Role SIIS
        $users = [
            [
                'nama' => 'Super Administrator (Master)',
                'email' => 'admin@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'nomor_identitas' => 'ADM2026001',
                'rfid_uid' => '00112233',
                'kelas_atau_jabatan' => 'Administrator SIIS',
            ],
            [
                'nama' => 'Hendra Staff Sarpras',
                'email' => 'sarpras@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'staff_sarpras',
                'nomor_identitas' => 'PEG202005001',
                'rfid_uid' => '11223344',
                'kelas_atau_jabatan' => 'Staff Pengelola Sarpras',
            ],
            [
                'nama' => 'Dra. Hj. Siti Rahma, M.Pd',
                'email' => 'wakasek@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'wakasek_sarpras',
                'nomor_identitas' => 'NIP197505122000032002',
                'rfid_uid' => '55667788',
                'kelas_atau_jabatan' => 'Wakil Kepala Sekolah Bidang Sarpras',
            ],
            [
                'nama' => 'Dr. H. Bambang Wijaya, M.M.',
                'email' => 'kepsek@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'kepala_sekolah',
                'nomor_identitas' => 'NIP196803151994031004',
                'rfid_uid' => '99001122',
                'kelas_atau_jabatan' => 'Kepala Sekolah (Monitoring Eksekutif)',
            ],
            [
                'nama' => 'Budi Santoso, S.Pd',
                'email' => 'guru@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nomor_identitas' => 'NIP198501012010011001',
                'rfid_uid' => 'E5F6G7H8',
                'kelas_atau_jabatan' => 'Guru Fisika',
            ],
            [
                'nama' => 'Ahmad Siswa',
                'email' => 'siswa@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026001',
                'rfid_uid' => 'A1B2C3D4',
                'kelas_atau_jabatan' => 'XII RPL 1',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['email' => $userData['email']], $userData);
        }

        // 2. Kategori Inventaris
        $kategoriElektronik = Kategori::updateOrCreate(
            ['nama_kategori' => 'Multimedia & Elektronik'],
            ['deskripsi' => 'Perangkat proyektor, pointer, kamera, sound system, kabel converter, dsb.']
        );

        $kategoriLab = Kategori::updateOrCreate(
            ['nama_kategori' => 'Laboratorium IPA & Fisika'],
            ['deskripsi' => 'Alat-alat praktikum sains seperti mikroskop, neraca ohaus, tabung reaksi, dsb.']
        );

        $kategoriOlahraga = Kategori::updateOrCreate(
            ['nama_kategori' => 'Peralatan Olahraga'],
            ['deskripsi' => 'Bola basket, bola voli, raket bulutangkis, matras senam, stopwatch, dsb.']
        );

        $kategoriIT = Kategori::updateOrCreate(
            ['nama_kategori' => 'Komputer & IT'],
            ['deskripsi' => 'Perangkat laptop praktikum, PC all-in-one, switch hub, router, dsb.']
        );

        // 3. Data Master Alat
        $alatList = [
            [
                'kategori_id' => $kategoriIT->id,
                'nama_alat' => 'Laptop Asus ExpertBook Core i5',
                'kode_alat' => 'LPT-ASUS-I5',
                'kondisi' => 'baik',
                'stok_total' => 10,
                'stok_tersedia' => 10,
                'lokasi_penyimpanan' => 'Lab Komputer 1 - Lemari A',
                'unit_samples' => [
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 01', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 02', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 03', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 04', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 05', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 06', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 07', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 08', 'catatan' => 'Lengkap dengan charger'],
                    ['kondisi' => 'rusak_ringan', 'lokasi' => 'Meja Lab 09', 'catatan' => 'Baterai drop, harus colok adaptor'],
                    ['kondisi' => 'baik', 'lokasi' => 'Meja Lab 10', 'catatan' => 'Lengkap dengan charger'],
                ],
            ],
            [
                'kategori_id' => $kategoriElektronik->id,
                'nama_alat' => 'LCD Proyektor Epson EB-E500',
                'kode_alat' => 'PRJ-EPS-001',
                'kondisi' => 'baik',
                'stok_total' => 5,
                'stok_tersedia' => 5,
                'lokasi_penyimpanan' => 'Gudang Sarpras Rak A1',
            ],
            [
                'kategori_id' => $kategoriElektronik->id,
                'nama_alat' => 'Wireless Presenter / Laser Pointer',
                'kode_alat' => 'PTR-LOG-002',
                'kondisi' => 'baik',
                'stok_total' => 8,
                'stok_tersedia' => 8,
                'lokasi_penyimpanan' => 'Gudang Sarpras Laci B2',
            ],
            [
                'kategori_id' => $kategoriLab->id,
                'nama_alat' => 'Mikroskop Binokuler Olympus CX23',
                'kode_alat' => 'MKS-OLY-001',
                'kondisi' => 'baik',
                'stok_total' => 10,
                'stok_tersedia' => 10,
                'lokasi_penyimpanan' => 'Lab Biologi Lemari 3',
            ],
            [
                'kategori_id' => $kategoriOlahraga->id,
                'nama_alat' => 'Bola Basket Molten GG7X',
                'kode_alat' => 'BBL-MLT-001',
                'kondisi' => 'baik',
                'stok_total' => 12,
                'stok_tersedia' => 12,
                'lokasi_penyimpanan' => 'Ruang Olahraga Keranjang 1',
            ],
        ];

        foreach ($alatList as $item) {
            $unitSamples = $item['unit_samples'] ?? null;
            unset($item['unit_samples']);

            $alat = Alat::updateOrCreate(['kode_alat' => $item['kode_alat']], $item);

            // Buat fisik unit_alat jika belum ada
            if ($alat->units()->count() === 0) {
                if ($unitSamples) {
                    foreach ($unitSamples as $idx => $sample) {
                        $alat->units()->create([
                            'kode_unit' => sprintf('%s-%03d', $alat->kode_alat, $idx + 1),
                            'nomor_seri' => 'SN-' . strtoupper(substr(md5($alat->kode_alat . ($idx + 1)), 0, 8)),
                            'kondisi' => $sample['kondisi'],
                            'status' => 'tersedia',
                            'lokasi_spesifik' => $sample['lokasi'],
                            'catatan' => $sample['catatan'],
                        ]);
                    }
                    $alat->syncStockFromUnits();
                } else {
                    $alat->generateUnits($alat->stok_total);
                }
            }
        }

        // 4. Perangkat IoT ESP32 Default
        IotDevice::updateOrCreate(
            ['device_id' => 'ESP32-SARPRAS-01'],
            [
                'nama_device' => 'RFID Scanner Meja Sarpras Lt. 1',
                'api_key' => 'siis-rfid-secret-key-2026',
                'status' => 'active',
            ]
        );
    }
}

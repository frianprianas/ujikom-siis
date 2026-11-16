<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\IotDevice;
use App\Models\Kategori;
use App\Models\Peminjaman;
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
            // -------------------- GURU --------------------
            [
                'nama' => 'Budi Santoso, S.Pd',
                'email' => 'guru@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nomor_identitas' => 'NIP198501012010011001',
                'rfid_uid' => 'E5F6G7H8',
                'kelas_atau_jabatan' => 'Guru Fisika & Pembina KIR',
            ],
            [
                'nama' => 'Dra. Ratna Sartika, M.Pd',
                'email' => 'ratna.sartika@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nomor_identitas' => 'NIP198203152008012005',
                'rfid_uid' => 'D4E5F6A1',
                'kelas_atau_jabatan' => 'Guru Matematika & Kepala Lab Sains',
            ],
            [
                'nama' => 'Hendra Kurnia, S.Kom',
                'email' => 'hendra.kurnia@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nomor_identitas' => 'NIP199009182015021003',
                'rfid_uid' => 'B3C4D5E6',
                'kelas_atau_jabatan' => 'Guru Rekayasa Perangkat Lunak (RPL)',
            ],
            // -------------------- SISWA --------------------
            [
                'nama' => 'Ahmad Siswa',
                'email' => 'siswa@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026001',
                'rfid_uid' => 'A1B2C3D4',
                'kelas_atau_jabatan' => 'XII RPL 1',
            ],
            [
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026002',
                'rfid_uid' => 'A2B3C4D5',
                'kelas_atau_jabatan' => 'XII RPL 2',
            ],
            [
                'nama' => 'Dimas Pratama',
                'email' => 'dimas.pratama@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026003',
                'rfid_uid' => 'A3B4C5D6',
                'kelas_atau_jabatan' => 'XI TKJ 1',
            ],
            [
                'nama' => 'Putri Ayu Lestari',
                'email' => 'putri.ayu@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026004',
                'rfid_uid' => 'A4B5C6D7',
                'kelas_atau_jabatan' => 'XI DKV 2',
            ],
            [
                'nama' => 'Fajar Ramadhan',
                'email' => 'fajar.ramadhan@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026005',
                'rfid_uid' => 'A5B6C7D8',
                'kelas_atau_jabatan' => 'X PPLG 1',
            ],
            [
                'nama' => 'Rina Amelia',
                'email' => 'rina.amelia@siis.sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nomor_identitas' => 'NIS2026006',
                'rfid_uid' => 'A6B7C8D9',
                'kelas_atau_jabatan' => 'XII Multimedia 1',
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

        // 5. Data Sampel Peminjaman (Siklus Peminjaman & Pengembalian Berjalan)
        if (Peminjaman::count() === 0) {
            $staff = User::where('role', 'staff_sarpras')->first();
            $siswaAhmad = User::where('email', 'siswa@siis.sch.id')->first();
            $siswaSiti = User::where('email', 'siti.nurhaliza@siis.sch.id')->first();
            $siswaDimas = User::where('email', 'dimas.pratama@siis.sch.id')->first();
            $guruBudi = User::where('email', 'guru@siis.sch.id')->first();
            $guruRatna = User::where('email', 'ratna.sartika@siis.sch.id')->first();

            $proyektor = Alat::where('kode_alat', 'PRJ-EPS-001')->first();
            $mikroskop = Alat::where('kode_alat', 'MKS-OLY-001')->first();
            $pointer = Alat::where('kode_alat', 'PTR-LOG-002')->first();
            $laptop = Alat::where('kode_alat', 'LPT-ASUS-I5')->first();
            $bola = Alat::where('kode_alat', 'BBL-MLT-001')->first();

            // Kasus 1: Menunggu Persetujuan (Siswa Ahmad ingin pinjam Proyektor)
            if ($siswaAhmad && $proyektor) {
                $p1 = Peminjaman::create([
                    'user_id' => $siswaAhmad->id,
                    'tanggal_pengajuan' => now()->subHours(2),
                    'tanggal_pinjam' => now()->toDateString(),
                    'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
                    'status' => 'menunggu_persetujuan',
                    'catatan_approval' => 'Keperluan: Presentasi tugas project akhir Web & Mobile di Aula',
                ]);
                $p1->details()->create([
                    'alat_id' => $proyektor->id,
                    'jumlah' => 1,
                    'kondisi_saat_pinjam' => 'baik',
                ]);
            }

            // Kasus 2: Disetujui / Siap Diambil di Sarpras (Guru Ratna pinjam Mikroskop)
            if ($guruRatna && $mikroskop && $staff) {
                $unitMikroskop = $mikroskop->units()->where('status', 'tersedia')->first();
                $p2 = Peminjaman::create([
                    'user_id' => $guruRatna->id,
                    'tanggal_pengajuan' => now()->subHours(5),
                    'tanggal_pinjam' => now()->toDateString(),
                    'tanggal_harus_kembali' => now()->addDays(3)->toDateString(),
                    'status' => 'disetujui',
                    'approved_by_id' => $staff->id,
                    'tanggal_approval' => now()->subHours(4),
                    'catatan_approval' => 'Keperluan: Praktikum Biologi Sel X RPL | ACC: Disetujui, ambil di Lab Lt. 2',
                ]);
                $p2->details()->create([
                    'alat_id' => $mikroskop->id,
                    'unit_alat_id' => $unitMikroskop?->id,
                    'jumlah' => 1,
                    'kondisi_saat_pinjam' => 'baik',
                ]);
                $mikroskop->decrement('stok_tersedia', 1);
            }

            // Kasus 3: Sedang Dipinjam / Barang di Tangan Peminjam (Siswa Siti pinjam Pointer)
            if ($siswaSiti && $pointer && $staff) {
                $unitPointer = $pointer->units()->where('status', 'tersedia')->first();
                if ($unitPointer) {
                    $unitPointer->update(['status' => 'dipinjam']);
                }
                $p3 = Peminjaman::create([
                    'user_id' => $siswaSiti->id,
                    'tanggal_pengajuan' => now()->subDay(),
                    'tanggal_pinjam' => now()->subDay()->toDateString(),
                    'tanggal_harus_kembali' => now()->addDays(1)->toDateString(),
                    'status' => 'sedang_dipinjam',
                    'approved_by_id' => $staff->id,
                    'tanggal_approval' => now()->subDay(),
                    'catatan_approval' => 'Keperluan: Presentasi sidang proposal kejuruan RPL (Diserahkan langsung di Sarpras)',
                ]);
                $p3->details()->create([
                    'alat_id' => $pointer->id,
                    'unit_alat_id' => $unitPointer?->id,
                    'jumlah' => 1,
                    'kondisi_saat_pinjam' => 'baik',
                ]);
                $pointer->decrement('stok_tersedia', 1);
            }

            // Kasus 4: Sudah Selesai Dikembalikan (Guru Budi pinjam Laptop Asus)
            if ($guruBudi && $laptop && $staff) {
                $unitLaptop = $laptop->units()->first();
                $p4 = Peminjaman::create([
                    'user_id' => $guruBudi->id,
                    'tanggal_pengajuan' => now()->subDays(3),
                    'tanggal_pinjam' => now()->subDays(3)->toDateString(),
                    'tanggal_harus_kembali' => now()->subDay()->toDateString(),
                    'tanggal_aktual_kembali' => now()->subHours(3),
                    'status' => 'dikembalikan',
                    'approved_by_id' => $staff->id,
                    'tanggal_approval' => now()->subDays(3),
                    'catatan_approval' => 'Keperluan: Mengajar materi olimpiade fisika komputasi',
                ]);
                $p4->details()->create([
                    'alat_id' => $laptop->id,
                    'unit_alat_id' => $unitLaptop?->id,
                    'jumlah' => 1,
                    'kondisi_saat_pinjam' => 'baik',
                    'kondisi_saat_kembali' => 'baik',
                ]);
            }

            // Kasus 5: Terlambat Mengembalikan (Siswa Dimas pinjam Bola Basket)
            if ($siswaDimas && $bola && $staff) {
                $unitBola = $bola->units()->where('status', 'tersedia')->first();
                if ($unitBola) {
                    $unitBola->update(['status' => 'dipinjam']);
                }
                $p5 = Peminjaman::create([
                    'user_id' => $siswaDimas->id,
                    'tanggal_pengajuan' => now()->subDays(5),
                    'tanggal_pinjam' => now()->subDays(5)->toDateString(),
                    'tanggal_harus_kembali' => now()->subDays(2)->toDateString(),
                    'status' => 'terlambat',
                    'approved_by_id' => $staff->id,
                    'tanggal_approval' => now()->subDays(5),
                    'catatan_approval' => 'Keperluan: Latihan pertandingan basket antar kelas',
                ]);
                $p5->details()->create([
                    'alat_id' => $bola->id,
                    'unit_alat_id' => $unitBola?->id,
                    'jumlah' => 1,
                    'kondisi_saat_pinjam' => 'baik',
                ]);
                $bola->decrement('stok_tersedia', 1);
            }
        }
    }
}

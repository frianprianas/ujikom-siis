<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiisCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIIS Portal');
    }

    public function test_user_can_login_and_redirects_according_to_role(): void
    {
        $response = $this->post('/login', [
            'email' => 'siswa@siis.sch.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $this->assertAuthenticated();
    }

    public function test_siswa_cannot_access_sarpras_dashboard(): void
    {
        $siswa = User::where('role', 'siswa')->first();

        $response = $this->actingAs($siswa)->get(route('sarpras.dashboard'));
        $response->assertStatus(403);
    }

    public function test_store_alat_validation_and_authorization(): void
    {
        $staff = User::where('role', 'staff_sarpras')->first();
        $siswa = User::where('role', 'siswa')->first();
        $kategori = Kategori::first();

        // 1. Siswa dilarang menambah alat (403 via Form Request authorize)
        $responseSiswa = $this->actingAs($siswa)->post(route('alat.store'), [
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon DSLR',
            'kode_alat' => 'KMR-001',
            'kondisi' => 'baik',
            'stok_total' => 3,
        ]);
        $responseSiswa->assertStatus(403);

        // 2. Staff Sarpras berhasil menambah alat
        $responseStaff = $this->actingAs($staff)->post(route('alat.store'), [
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon DSLR EOS',
            'kode_alat' => 'KMR-CAN-001',
            'kondisi' => 'baik',
            'stok_total' => 4,
            'lokasi_penyimpanan' => 'Lemari Multimedia',
        ]);
        $responseStaff->assertRedirect(route('alat.index'));
        $this->assertDatabaseHas('alat', [
            'kode_alat' => 'KMR-CAN-001',
            'stok_total' => 4,
            'stok_tersedia' => 4,
        ]);

        // 3. Validasi kode_alat unik gagal
        $responseDuplikat = $this->actingAs($staff)->post(route('alat.store'), [
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Duplikat',
            'kode_alat' => 'KMR-CAN-001',
            'kondisi' => 'baik',
            'stok_total' => 2,
        ]);
        $responseDuplikat->assertSessionHasErrors(['kode_alat']);
    }

    public function test_siswa_can_create_peminjaman_with_validation(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $alat = Alat::where('stok_tersedia', '>', 0)->first();

        // 1. Validasi tanggal tidak boleh sebelum hari ini
        $responseSalahTanggal = $this->actingAs($siswa)->post(route('peminjaman.store'), [
            'tanggal_pinjam' => now()->subDay()->toDateString(),
            'tanggal_harus_kembali' => now()->toDateString(),
            'items' => [
                ['alat_id' => $alat->id, 'jumlah' => 1],
            ],
        ]);
        $responseSalahTanggal->assertSessionHasErrors(['tanggal_pinjam']);

        // 2. Berhasil mengajukan peminjaman
        $response = $this->actingAs($siswa)->post(route('peminjaman.store'), [
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(3)->toDateString(),
            'items' => [
                [
                    'alat_id' => $alat->id,
                    'jumlah' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseHas('peminjaman', [
            'user_id' => $siswa->id,
            'status' => 'menunggu_persetujuan',
        ]);
    }

    public function test_sarpras_can_approve_peminjaman_and_deducts_stock(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $staff = User::where('role', 'staff_sarpras')->first();
        $alat = Alat::first();
        $initialStock = $alat->stok_tersedia;

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'tanggal_pengajuan' => now(),
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'menunggu_persetujuan',
        ]);

        $peminjaman->details()->create([
            'alat_id' => $alat->id,
            'jumlah' => 2,
            'kondisi_saat_pinjam' => 'baik',
        ]);

        $response = $this->actingAs($staff)->post(route('sarpras.peminjaman.approve', $peminjaman->id), [
            'catatan_approval' => 'Disetujui untuk kegiatan praktikum',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'disetujui',
            'approved_by_id' => $staff->id,
        ]);

        $this->assertEquals($initialStock - 2, $alat->fresh()->stok_tersedia);
    }

    public function test_sarpras_can_reject_peminjaman_with_reason(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $staff = User::where('role', 'staff_sarpras')->first();

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'tanggal_pengajuan' => now(),
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'menunggu_persetujuan',
        ]);

        $response = $this->actingAs($staff)->post(route('sarpras.peminjaman.reject', $peminjaman->id), [
            'catatan_approval' => 'Jadwal lab bertabrakan dengan ujian',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'ditolak',
            'catatan_approval' => 'Jadwal lab bertabrakan dengan ujian',
        ]);
    }

    public function test_sarpras_can_process_returned_tools_and_restores_stock(): void
    {
        $staff = User::where('role', 'staff_sarpras')->first();
        $alat = Alat::first();
        $initialStock = $alat->stok_tersedia;

        $peminjaman = Peminjaman::create([
            'user_id' => User::where('role', 'siswa')->first()->id,
            'tanggal_pengajuan' => now(),
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'sedang_dipinjam',
        ]);

        $detail = $peminjaman->details()->create([
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'kondisi_saat_pinjam' => 'baik',
        ]);

        $response = $this->actingAs($staff)->post(route('sarpras.peminjaman.return', $peminjaman->id), [
            'kondisi_kembali' => [
                $detail->id => 'baik',
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'dikembalikan',
        ]);

        $this->assertEquals($initialStock + 1, $alat->fresh()->stok_tersedia);
    }

    public function test_kepala_sekolah_can_access_executive_dashboard(): void
    {
        $kepsek = User::where('role', 'kepala_sekolah')->first();

        $response = $this->actingAs($kepsek)->get(route('kepsek.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Monitoring Sarana');
    }

    public function test_alat_catalog_screen_renders_for_siswa_and_staff(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $responseSiswa = $this->actingAs($siswa)->get(route('alat.index'));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertSee('Katalog Inventaris Alat Sekolah');
        $responseSiswa->assertSee('Pilih / Masukkan ke Keranjang');

        $staff = User::where('role', 'staff_sarpras')->first();
        $responseStaff = $this->actingAs($staff)->get(route('alat.index'));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('+ Tambah Alat Baru');
    }

    public function test_peminjaman_create_screen_renders_with_available_tools(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $alat = Alat::first();

        $response = $this->actingAs($siswa)->get(route('peminjaman.create', ['alat_id' => $alat->id]));
        $response->assertStatus(200);
        $response->assertSee('Formulir Pengajuan Peminjaman Alat');
        $response->assertSee('Ringkasan Pengajuan Peminjaman');
    }

    public function test_api_login_and_scan_endpoint_works(): void
    {
        // 1. Test Login via API
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'siswa@siis.sch.id',
            'password' => 'password',
        ]);
        $response->assertStatus(200);
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // 2. Test Scan Alat by Kode
        $alat = Alat::first();
        $scanResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/alat/scan/{$alat->kode_alat}");

        $scanResponse->assertStatus(200);
        $scanResponse->assertJsonPath('data.kode_alat', $alat->kode_alat);
        $scanResponse->assertJsonPath('data.nama_alat', $alat->nama_alat);
    }

    public function test_rfid_endpoint_requires_registered_device(): void
    {
        $response = $this->postJson('/api/v1/rfid/verify', [
            'device_id' => 'INVALID_DEVICE',
            'api_key' => 'wrong_key',
            'rfid_uid' => 'A1B2C3D4',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'unauthorized',
        ]);
    }

    public function test_rfid_endpoint_identifies_registered_student(): void
    {
        $response = $this->postJson('/api/v1/rfid/verify', [
            'device_id' => 'ESP32-SARPRAS-01',
            'api_key' => 'siis-rfid-secret-key-2026',
            'rfid_uid' => 'A1B2C3D4',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'status' => 'no_approved_loan',
        ]);
        $response->assertJsonPath('user.nama', 'Ahmad Siswa');
    }

    public function test_rfid_endpoint_confirms_borrowing_when_approved(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $staff = User::where('role', 'staff_sarpras')->first();
        $alat = Alat::first();

        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'tanggal_pengajuan' => now(),
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'disetujui',
            'approved_by_id' => $staff->id,
            'tanggal_approval' => now(),
        ]);

        $peminjaman->details()->create([
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'kondisi_saat_pinjam' => 'baik',
        ]);

        $response = $this->postJson('/api/v1/rfid/verify', [
            'device_id' => 'ESP32-SARPRAS-01',
            'api_key' => 'siis-rfid-secret-key-2026',
            'rfid_uid' => $siswa->rfid_uid,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'borrow_confirmed',
        ]);

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'sedang_dipinjam',
        ]);
    }

    public function test_super_admin_has_full_master_data_access(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        // Master Data routes
        $this->actingAs($admin)->get(route('sarpras.users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('sarpras.iot.devices'))->assertStatus(200);
        $this->actingAs($admin)->get(route('sarpras.iot.logs'))->assertStatus(200);
        $this->actingAs($admin)->get(route('sarpras.iot.simulator'))->assertStatus(200);

        // Sarpras & Executive routes
        $this->actingAs($admin)->get(route('alat.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('sarpras.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('kepsek.dashboard'))->assertStatus(200);
    }

    public function test_staff_and_wakasek_can_manage_inventaris_and_acc_but_cannot_access_master_data(): void
    {
        $staff = User::where('role', 'staff_sarpras')->first();
        $wakasek = User::where('role', 'wakasek_sarpras')->first();

        foreach ([$staff, $wakasek] as $user) {
            // Bisa tambah inventaris & ACC peminjaman
            $this->actingAs($user)->get(route('alat.create'))->assertStatus(200);
            $this->actingAs($user)->get(route('sarpras.dashboard'))->assertStatus(200);

            // DILARANG akses Master Data (khusus super admin)
            $this->actingAs($user)->get(route('sarpras.users.index'))->assertStatus(403);
            $this->actingAs($user)->get(route('sarpras.iot.devices'))->assertStatus(403);
            $this->actingAs($user)->get(route('sarpras.iot.logs'))->assertStatus(403);
            $this->actingAs($user)->get(route('sarpras.iot.simulator'))->assertStatus(403);
        }
    }

    public function test_kepala_sekolah_is_view_only(): void
    {
        $kepsek = User::where('role', 'kepala_sekolah')->first();
        $kategori = Kategori::first();

        // BISA: View katalog, list peminjaman, dan dashboard eksekutif
        $this->actingAs($kepsek)->get(route('alat.index'))->assertStatus(200);
        $this->actingAs($kepsek)->get(route('peminjaman.index'))->assertStatus(200);
        $this->actingAs($kepsek)->get(route('kepsek.dashboard'))->assertStatus(200);

        // TIDAK BISA (403): Tambah inventaris
        $this->actingAs($kepsek)->get(route('alat.create'))->assertStatus(403);
        $this->actingAs($kepsek)->post(route('alat.store'), [
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Proyektor Uji',
            'kode_alat' => 'UJI-001',
            'kondisi' => 'baik',
            'stok_total' => 1,
        ])->assertStatus(403);

        // TIDAK BISA (403): Mengajukan peminjaman (khusus siswa/guru)
        $this->actingAs($kepsek)->get(route('peminjaman.create'))->assertStatus(403);

        // TIDAK BISA (403): Approval / Sarpras dashboard
        $this->actingAs($kepsek)->get(route('sarpras.dashboard'))->assertStatus(403);

        // TIDAK BISA (403): Master data
        $this->actingAs($kepsek)->get(route('sarpras.users.index'))->assertStatus(403);
        $this->actingAs($kepsek)->get(route('sarpras.iot.devices'))->assertStatus(403);
    }

    public function test_siswa_and_guru_can_borrow_tools_but_cannot_manage_inventory_or_approve(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $guru = User::where('role', 'guru')->first();

        foreach ([$siswa, $guru] as $user) {
            // BISA: Form pengajuan pinjam alat
            $this->actingAs($user)->get(route('peminjaman.create'))->assertStatus(200);

            // TIDAK BISA (403): Tambah inventaris
            $this->actingAs($user)->get(route('alat.create'))->assertStatus(403);

            // TIDAK BISA (403): Approval / Sarpras dashboard
            $this->actingAs($user)->get(route('sarpras.dashboard'))->assertStatus(403);

            // TIDAK BISA (403): Master Data
            $this->actingAs($user)->get(route('sarpras.users.index'))->assertStatus(403);

            // TIDAK BISA (403): Dashboard Eksekutif Kepsek
            $this->actingAs($user)->get(route('kepsek.dashboard'))->assertStatus(403);
        }
    }

    public function test_admin_is_blocked_from_mobile_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@siis.sch.id',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Akun Super Admin & Setting Data Master hanya dapat diakses melalui Aplikasi Web SIIS.',
        ]);
    }

    public function test_mobile_api_roles_and_endpoints(): void
    {
        $staff = User::where('role', 'staff_sarpras')->first();
        $wakasek = User::where('role', 'wakasek_sarpras')->first();
        $kepsek = User::where('role', 'kepala_sekolah')->first();
        $siswa = User::where('role', 'siswa')->first();
        $kategori = Kategori::first();

        // 1. Staff & Wakasek BISA input barang via Mobile API
        foreach ([$staff, $wakasek] as $approver) {
            \Laravel\Sanctum\Sanctum::actingAs($approver);
            $kodeAlat = 'TEST-API-' . $approver->role . '-' . rand(100, 999);

            $res = $this->postJson('/api/v1/alat', [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Proyektor API ' . $approver->role,
                'kode_alat' => $kodeAlat,
                'kondisi' => 'baik',
                'stok_total' => 5,
                'lokasi_penyimpanan' => 'Gudang Mobile',
            ]);

            $res->assertStatus(201);
            $this->assertDatabaseHas('alat', ['kode_alat' => $kodeAlat, 'stok_tersedia' => 5]);
        }

        // 2. Siswa & Kepsek DILARANG input barang via Mobile API (403)
        foreach ([$siswa, $kepsek] as $nonStaff) {
            \Laravel\Sanctum\Sanctum::actingAs($nonStaff);
            $res = $this->postJson('/api/v1/alat', [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Dilarang Input',
                'kode_alat' => 'FORBIDDEN-' . rand(100, 999),
                'kondisi' => 'baik',
                'stok_total' => 1,
            ]);

            $res->assertStatus(403);
        }

        // 3. Staff & Wakasek BISA tandai pengembalian barang via Mobile API
        $alat = Alat::first();
        $peminjaman = Peminjaman::create([
            'user_id' => $siswa->id,
            'tanggal_pengajuan' => now(),
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_harus_kembali' => now()->addDays(2)->toDateString(),
            'status' => 'sedang_dipinjam',
            'approved_by_id' => $staff->id,
            'tanggal_approval' => now(),
        ]);
        $peminjaman->details()->create([
            'alat_id' => $alat->id,
            'jumlah' => 2,
            'kondisi_saat_pinjam' => 'baik',
        ]);
        $stokAwal = $alat->fresh()->stok_tersedia;

        \Laravel\Sanctum\Sanctum::actingAs($staff);
        $resReturn = $this->postJson("/api/v1/peminjaman/{$peminjaman->id}/return");

        $resReturn->assertStatus(200);
        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'dikembalikan',
        ]);
        $this->assertEquals($stokAwal + 2, $alat->fresh()->stok_tersedia);

        // 4. Laporan Mobile: Staff, Wakasek, Kepsek BISA; Siswa DILARANG (403)
        \Laravel\Sanctum\Sanctum::actingAs($kepsek);
        $this->getJson('/api/v1/dashboard/laporan')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'ringkasan' => ['total_peminjaman', 'total_disetujui', 'total_ditolak', 'total_dikembalikan'],
                    'riwayat',
                ],
            ]);

        \Laravel\Sanctum\Sanctum::actingAs($siswa);
        $this->getJson('/api/v1/dashboard/laporan')
            ->assertStatus(403);
    }

    /**
     * Test identifikasi fisik individual: 1 alat punya N unit fisik dengan ID berurutan dan kondisi masing-masing.
     * Contoh: Laptop Asus 10 unit -> Unit 1 s/d 10.
     */
    public function test_master_alat_generates_sequential_units(): void
    {
        $user = User::where('role', 'siswa')->first();
        $asus = Alat::where('kode_alat', 'LPT-ASUS-I5')->first();

        $this->assertNotNull($asus);
        $this->assertEquals(10, $asus->units()->count());

        // Verifikasi kode unit berurutan LPT-ASUS-I5-001 s/d LPT-ASUS-I5-010
        $firstUnit = $asus->units()->first();
        $this->assertEquals('LPT-ASUS-I5-001', $firstUnit->kode_unit);

        $lastUnit = $asus->units()->reorder('kode_unit', 'desc')->first();
        $this->assertEquals('LPT-ASUS-I5-010', $lastUnit->kode_unit);

        // Halaman rincian unit dapat diakses oleh Siswa
        $response = $this->actingAs($user)->get(route('alat.show', $asus));
        $response->assertStatus(200);
        $response->assertSee('LPT-ASUS-I5-001');
        $response->assertSee('LPT-ASUS-I5-010');
        $response->assertSee('Rincian Fisik: Unit 1 s/d Unit 10');
    }

    /**
     * Test staff dapat mengupdate kondisi fisik per unit dan stok master otomatis tersinkronisasi.
     */
    public function test_staff_can_update_unit_condition_and_sync_master_stock(): void
    {
        $staff = User::where('role', 'staff_sarpras')->first();
        $siswa = User::where('role', 'siswa')->first();
        $asus = Alat::where('kode_alat', 'LPT-ASUS-I5')->first();

        $unit9 = $asus->units()->where('kode_unit', 'LPT-ASUS-I5-009')->first();

        // 1. Siswa dilarang mengupdate kondisi unit (403)
        $this->actingAs($siswa)->patch(route('unit-alat.update', $unit9), [
            'kondisi' => 'rusak_berat',
            'status' => 'perbaikan',
        ])->assertStatus(403);

        // 2. Staff Sarpras berhasil update kondisi unit 9
        $response = $this->actingAs($staff)->patch(route('unit-alat.update', $unit9), [
            'kondisi' => 'rusak_berat',
            'status' => 'perbaikan',
            'catatan' => 'Motherboard korslet, dikirim ke servis center',
        ]);
        $response->assertSessionHas('success');

        $unit9->refresh();
        $this->assertEquals('rusak_berat', $unit9->kondisi);
        $this->assertEquals('perbaikan', $unit9->status);

        // Stok master otomatis berkurang karena unit berstatus perbaikan
        $asus->refresh();
        $this->assertEquals(9, $asus->stok_tersedia);
    }

    /**
     * Test API endpoint GET /api/v1/alat/{id} mengembalikan daftar unit fisik dan kondisi individual.
     */
    public function test_api_v1_alat_show_returns_unit_breakdown(): void
    {
        $siswa = User::where('role', 'siswa')->first();
        $asus = Alat::where('kode_alat', 'LPT-ASUS-I5')->first();

        \Laravel\Sanctum\Sanctum::actingAs($siswa);
        $response = $this->getJson("/api/v1/alat/{$asus->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.kode_alat', 'LPT-ASUS-I5');
        $response->assertJsonCount(10, 'data.units');
        $response->assertJsonPath('data.units.0.kode_unit', 'LPT-ASUS-I5-001');
    }
}

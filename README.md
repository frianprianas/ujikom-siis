# SIIS (Sistem Informasi Inventaris Sekolah)

**SIIS** adalah sistem manajemen inventaris, peminjaman alat, dan kontrol aset sekolah berbasis web dan mobile yang terintegrasi langsung dengan mikrokontroler **IoT ESP32 + RFID RC522** dan **Aplikasi Mobile Flutter**.

---

## 🛠️ Tech Stack

- **Backend Web**: Laravel Framework (versi terbaru) + MySQL (XAMPP Lokal).
- **Frontend Web**: Laravel Blade Templates + Tailwind CSS + Alpine.js + Lucide Icons.
- **REST API Mobile**: Laravel Sanctum Token Authentication.
- **Mobile Client**: Flutter (Android & iOS) + Dio + Provider + Mobile Scanner.
- **IoT Firmware**: ESP32 C++ (Arduino IDE) + MFRC522 RFID Reader (SPI Protocol).

---

## 👥 Role & Hak Akses (RBAC) Hierarkis

| Level | Role | Akun Demo | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Level 1** | **Super Admin** | `admin@siis.sch.id` | `password` | **Full Master Data & Control**: Kelola akun pengguna, pasang UID kartu RFID siswa/pegawai, registrasi alat IoT ESP32, audit logs RFID, simulator tap, CRUD inventaris & kategori, ACC peminjaman, serta akses dashboard monitoring eksekutif. |
| **Level 2** | **Staff Sarpras** | `sarpras@siis.sch.id` | `password` | **Inventaris & ACC**: Tambah/edit/hapus inventaris alat, kelola kategori, verifikasi permohonan pinjam (Setuju/Tolak), proses serah terima (barang dipinjam) & pengembalian, cetak stiker QR code alat, cetak laporan sarpras. *(Tidak dapat akses master user & device IoT)*. |
| **Level 2** | **Wakasek Sarpras** | `wakasek@siis.sch.id` | `password` | **Inventaris & ACC**: Memiliki wewenang yang sama dengan Staff Sarpras untuk menambah/mengelola inventaris alat dan melakukan ACC / approval peminjaman sekolah. |
| **Level 3** | **Kepala Sekolah** | `kepsek@siis.sch.id` | `password` | **View-Only / Executive Monitoring**: Hanya memantau dashboard statistik, KPI peminjaman, sirkulasi aset, rekapitulasi inventaris, dan cetak laporan resmi. *(Dilarang menambah/edit alat, dilarang ACC, dilarang mengajukan pinjaman)*. |
| **Level 4** | **Siswa** | `siswa@siis.sch.id` | `password` | **Peminjam**: Jelajah katalog alat tersedia, form pemesanan (booking keranjang dengan validasi stok), histori peminjaman, tap kartu RFID saat ambil alat di pos sarpras. |
| **Level 4** | **Guru** | `guru@siis.sch.id` | `password` | **Peminjam**: Jelajah katalog alat praktikum/mengajar, pengajuan peminjaman alat, monitoring status pinjaman mandiri. |

---

## 🚀 Panduan Menjalankan Sistem

### 1. Menjalankan Backend Web Laravel
Pastikan Apache dan MySQL di XAMPP telah berjalan, lalu jalankan:

```powershell
# Jalankan web server Laravel
php artisan serve
```
Akses di browser: **`http://localhost:8000`**

### 2. Akun Demo & Quick Login
Pada halaman login (`http://localhost:8000/login`), tersedia tombol **"Pilih Akun Demo"** untuk masuk secara instan tanpa perlu mengetik kredensial secara manual.

### 3. Otomatisasi Cek Keterlambatan Pengembalian
Untuk memeriksa peminjaman yang melewati tanggal batas kembali:
```powershell
php artisan siis:check-overdue
```

### 4. Menjalankan Test Suite (Pengujian Otomatis)
```powershell
php artisan test
```

---

## 📡 Integrasi IoT RFID ESP32

1. **Hardware Setup**:
   - Board: ESP32 Dev Module
   - RFID: MFRC522 (SDA/SS -> GPIO 21, SCK -> GPIO 18, MOSI -> GPIO 23, MISO -> GPIO 19, RST -> GPIO 22, VCC -> 3.3V)
   - Kode Firmware Arduino: [`resources/iot/esp32_rfid_siis.ino`](resources/iot/esp32_rfid_siis.ino)

2. **Web Simulator Tap RFID**:
   - Jika Anda belum menyalakan hardware fisik ESP32, staf dapat mencoba simulasi tap kartu langsung dari browser pada menu:
   - **`http://localhost:8000/sarpras/iot-simulator`**

3. **Audit Log RFID**:
   - Seluruh histori tap kartu (berhasil, ditolak, kartu tidak terdaftar) tercatat rapi di:
   - **`http://localhost:8000/sarpras/iot-logs`**

---

## 🏷️ Stiker Label QR Code & Cetak Laporan Resmi

- **Stiker Label QR Code**: Klik ikon QR code pada setiap alat di halaman katalog (`/alat`) untuk mencetak stiker barcode/QR yang siap ditempel pada fisik barang.
- **Cetak Laporan Resmi A4**: Dilengkapi Kop Surat Sekolah dan tanda tangan resmi Kepala Sekolah & Wakasek Sarpras di menu:
  - **`http://localhost:8000/sarpras/laporan/cetak`**

---

## 📱 Aplikasi Mobile Flutter (`siis_mobile`)

Aplikasi Flutter terletak di dalam folder [`siis_mobile`](file:///c:/Users/SERVER-03/Documents/siis/siis_mobile) dan terhubung ke REST API Laravel (`/api/v1/*`):
- **Dynamic Role Navigation**: Menu otomatis menyesuaikan role login (`siswa`, `guru`, `staff_sarpras`, `wakasek_sarpras`, `kepala_sekolah`).
- **Fitur Scan QR / Barcode**: Menggunakan kamera HP (`mobile_scanner`) untuk memindai kode stiker alat dan menampilkan rincian kondisi serta ketersediaan stok fisik secara instan.
- **Approval Mobile**: Staf Sarpras & Wakasek dapat menyetujui atau menolak pengajuan pinjam langsung dari ponsel dengan 1-tap.
- **Booking Online**: Siswa & Guru dapat memesan alat kapan saja dari aplikasi dengan dynamic cart & quantity stepper.
- **Executive Dashboard**: Kepala Sekolah dapat memantau grafik dan rekapitulasi inventaris secara langsung dari aplikasi ponsel.

### Cara Menjalankan Flutter App:
```bash
# 1. Masuk ke folder flutter
cd siis_mobile

# 2. Unduh dependensi
flutter pub get

# 3. Sesuaikan baseUrl API di lib/config/api_constants.dart jika diperlukan:
# - Android Emulator: http://10.0.2.2:8000/api/v1
# - HP Fisik (WiFi LAN yang sama): http://192.168.x.x:8000/api/v1

# 4. Jalankan aplikasi
flutter run
```

### Koleksi API Postman:
Tersedia file koleksi siap import di root proyek: [`siis_api_collection.json`](file:///c:/Users/SERVER-03/Documents/siis/siis_api_collection.json).

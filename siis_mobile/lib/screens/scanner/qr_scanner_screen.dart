import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../models/alat_model.dart';
import '../../models/unit_alat_model.dart';
import '../../providers/alat_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';

class QrScannerScreen extends StatefulWidget {
  const QrScannerScreen({super.key});

  @override
  State<QrScannerScreen> createState() => _QrScannerScreenState();
}

class _QrScannerScreenState extends State<QrScannerScreen> {
  late final MobileScannerController _cameraController;
  bool _isProcessing = false;
  final TextEditingController _manualInputController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _cameraController = MobileScannerController(
      detectionSpeed: DetectionSpeed.noDuplicates,
      facing: CameraFacing.back,
      torchEnabled: false,
    );
  }

  @override
  void dispose() {
    _cameraController.dispose();
    _manualInputController.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) async {
    if (_isProcessing) return;

    final List<Barcode> barcodes = capture.barcodes;
    for (final barcode in barcodes) {
      final code = barcode.rawValue;
      if (code != null && code.trim().isNotEmpty) {
        setState(() => _isProcessing = true);
        await _processScannedCode(code);
        break;
      }
    }
  }

  Future<void> _processScannedCode(String rawCode) async {
    // Bersihkan URL jika QR Code berisi URL lengkap, ambil kode di ujungnya
    var cleanCode = rawCode.trim();
    if (cleanCode.contains('/')) {
      cleanCode = cleanCode.split('/').last.trim();
    }

    final alatProvider = context.read<AlatProvider>();
    final result = await alatProvider.scanAlat(cleanCode);

    if (!mounted) return;

    if (result['success'] == true) {
      final alat = result['alat'] as AlatModel;
      final unit = result['unit'] as UnitAlatModel?;
      _showScanResultSheet(alat, scannedUnit: unit);
    } else {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.error_outline, color: Colors.red),
              SizedBox(width: 8),
              Expanded(
                child: Text('Alat Tidak Ditemukan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          content: Text(result['message'] ?? "Kode '$cleanCode' tidak terdaftar dalam sistem SIIS."),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                setState(() => _isProcessing = false);
                _showManualInputDialog();
              },
              child: const Text('Input Manual', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold)),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                setState(() => _isProcessing = false);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
              ),
              child: const Text('Pindai Lagi', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
    }
  }

  void _showScanResultSheet(AlatModel alat, {UnitAlatModel? scannedUnit}) {
    final authProvider = context.read<AuthProvider>();
    final peminjamanProvider = context.read<PeminjamanProvider>();

    showModalBottomSheet(
      context: context,
      isDismissible: false,
      enableDrag: false,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (ctx) {
        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.check_circle, color: Color(0xFF059669), size: 24),
                      const SizedBox(width: 8),
                      Text(
                        scannedUnit != null ? 'Unit Fisik Teridentifikasi' : 'Alat Teridentifikasi',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () {
                      Navigator.pop(ctx);
                      setState(() => _isProcessing = false);
                    },
                  ),
                ],
              ),
              const SizedBox(height: 12),
              const Divider(color: Color(0xFFE2E8F0)),
              const SizedBox(height: 8),

              // Jika unit spesifik terdeteksi
              if (scannedUnit != null) ...[
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.qr_code_2, size: 18, color: Color(0xFF059669)),
                          const SizedBox(width: 6),
                          Text(
                            'Unit: ${scannedUnit.kodeUnit}',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                          ),
                          const Spacer(),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: scannedUnit.isTersedia() ? const Color(0xFFD1FAE5) : const Color(0xFFFEE2E2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              scannedUnit.status.toUpperCase(),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: scannedUnit.isTersedia() ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                              ),
                            ),
                          ),
                        ],
                      ),
                      if (scannedUnit.nomorSeri != null && scannedUnit.nomorSeri!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          'No. Seri: ${scannedUnit.nomorSeri}',
                          style: const TextStyle(fontSize: 12, color: Color(0xFF047857), fontWeight: FontWeight.w600),
                        ),
                      ],
                    ],
                  ),
                ),
              ],

              Text(
                alat.namaAlat,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
              ),
              const SizedBox(height: 4),
              Text(
                'Kode: ${alat.kodeAlat} • Kategori: ${alat.kategori?.namaKategori ?? '-'}',
                style: const TextStyle(fontSize: 13, color: Color(0xFF047857), fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 14),

              Row(
                children: [
                  _badge(
                    'Kondisi: ${alat.kondisiLabel}',
                    alat.kondisi == 'baik' ? const Color(0xFFD1FAE5) : const Color(0xFFFEF3C7),
                    alat.kondisi == 'baik' ? const Color(0xFF065F46) : const Color(0xFF92400E),
                  ),
                  const SizedBox(width: 8),
                  _badge(
                    alat.isAvailable ? 'Stok: ${alat.stokTersedia} unit' : 'Stok Habis',
                    alat.isAvailable ? const Color(0xFFECFDF5) : const Color(0xFFFEE2E2),
                    alat.isAvailable ? const Color(0xFF064E3B) : const Color(0xFF991B1B),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  const Icon(Icons.location_on_outlined, size: 16, color: Color(0xFF059669)),
                  const SizedBox(width: 6),
                  Text('Lokasi: ${alat.lokasiPenyimpanan ?? '-'}', style: const TextStyle(color: Color(0xFF475569), fontWeight: FontWeight.w500)),
                ],
              ),
              const SizedBox(height: 24),

              if (authProvider.isPeminjam && (scannedUnit != null ? scannedUnit.isTersedia() : alat.isAvailable))
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF059669), Color(0xFF10B981)],
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF059669).withOpacity(0.3),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ElevatedButton.icon(
                    onPressed: () {
                      peminjamanProvider.addToCart(alat, jumlah: 1, unitAlat: scannedUnit);
                      Navigator.pop(ctx);
                      setState(() => _isProcessing = false);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            scannedUnit != null
                                ? 'Unit ${scannedUnit.kodeUnit} berhasil ditambahkan ke keranjang.'
                                : '${alat.namaAlat} berhasil ditambahkan ke keranjang.',
                          ),
                          backgroundColor: const Color(0xFF059669),
                        ),
                      );
                    },
                    icon: const Icon(Icons.add_shopping_cart, color: Colors.white),
                    label: Text(
                      scannedUnit != null ? 'Pilih Unit (${scannedUnit.kodeUnit})' : 'Tambahkan ke Keranjang Pinjam',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.transparent,
                      shadowColor: Colors.transparent,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _badge(String label, Color bg, Color text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: text.withOpacity(0.2)),
      ),
      child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: text)),
    );
  }

  void _showManualInputDialog() {
    _manualInputController.clear();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Input Kode Manual', style: TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF064E3B))),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan kode master alat atau kode unit fisik:',
              style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: _manualInputController,
              autofocus: true,
              textCapitalization: TextCapitalization.characters,
              decoration: InputDecoration(
                hintText: 'Misal: PROJ-001 atau PROJ-001-01',
                hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            onPressed: () {
              final code = _manualInputController.text.trim();
              if (code.isNotEmpty) {
                Navigator.pop(ctx);
                setState(() => _isProcessing = true);
                _processScannedCode(code);
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Cari Alat', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState(MobileScannerException error) {
    final bool isPermissionDenied = error.errorCode == MobileScannerErrorCode.permissionDenied;
    final String errorTitle = isPermissionDenied
        ? 'Izin Akses Kamera Diperlukan'
        : 'Kamera Tidak Tersedia / Error';
    final String errorMsg = isPermissionDenied
        ? 'Aktifkan izin kamera pada perangkat untuk memindai QR Code alat secara otomatis.'
        : 'Kamera tidak dapat diaktifkan (${error.errorDetails?.message ?? error.errorCode.message}). Anda tetap dapat memasukkan kode alat atau unit secara manual di bawah.';

    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                isPermissionDenied ? Icons.no_photography_outlined : Icons.videocam_off_outlined,
                size: 56,
                color: const Color(0xFF10B981),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              errorTitle,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              errorMsg,
              style: const TextStyle(
                color: Colors.white70,
                fontSize: 13,
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                _cameraController.start();
              },
              icon: const Icon(Icons.refresh, size: 18),
              label: const Text('Coba Buka Kamera Lagi'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _showManualInputDialog,
              icon: const Icon(Icons.keyboard, size: 18),
              label: const Text('Ketik Kode Manual'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: const BorderSide(color: Colors.white38),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Pindai QR / Barcode Alat'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on),
            tooltip: 'Senter',
            onPressed: () => _cameraController.toggleTorch(),
          ),
          IconButton(
            icon: const Icon(Icons.flip_camera_ios),
            tooltip: 'Ganti Kamera',
            onPressed: () => _cameraController.switchCamera(),
          ),
          IconButton(
            icon: const Icon(Icons.keyboard),
            tooltip: 'Ketik Kode Manual',
            onPressed: _showManualInputDialog,
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _cameraController,
            onDetect: _onDetect,
            errorBuilder: (context, error, child) {
              return _buildErrorState(error);
            },
          ),

          // Scanner Target Frame Overlay with Emerald Accent
          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(color: const Color(0xFF10B981), width: 3),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Icon(Icons.crop_free, color: Color(0xFF10B981), size: 36),
                      Icon(Icons.crop_free, color: Color(0xFF10B981), size: 36),
                    ],
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Icon(Icons.crop_free, color: Color(0xFF10B981), size: 36),
                      Icon(Icons.crop_free, color: Color(0xFF10B981), size: 36),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Instructions and manual input shortcut at bottom
          Positioned(
            bottom: 30,
            left: 20,
            right: 20,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.75),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
                  ),
                  child: const Text(
                    'Arahkan kamera ke stiker QR Code yang tertera pada alat inventaris sekolah.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w500),
                  ),
                ),
                const SizedBox(height: 10),
                TextButton.icon(
                  onPressed: _showManualInputDialog,
                  icon: const Icon(Icons.keyboard, color: Color(0xFF10B981), size: 18),
                  label: const Text(
                    'Atau Ketik Kode Alat / Unit Secara Manual',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                  style: TextButton.styleFrom(
                    backgroundColor: Colors.black54,
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                      side: const BorderSide(color: Colors.white24),
                    ),
                  ),
                ),
              ],
            ),
          ),

          if (_isProcessing)
            Container(
              color: Colors.black54,
              child: const Center(
                child: CircularProgressIndicator(color: Color(0xFF10B981)),
              ),
            ),
        ],
      ),
    );
  }
}

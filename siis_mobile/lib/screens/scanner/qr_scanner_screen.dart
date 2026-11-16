import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../models/alat_model.dart';
import '../../providers/alat_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';

class QrScannerScreen extends StatefulWidget {
  const QrScannerScreen({super.key});

  @override
  State<QrScannerScreen> createState() => _QrScannerScreenState();
}

class _QrScannerScreenState extends State<QrScannerScreen> {
  final MobileScannerController _cameraController = MobileScannerController();
  bool _isProcessing = false;
  final TextEditingController _manualInputController = TextEditingController();

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
      if (code != null && code.isNotEmpty) {
        setState(() => _isProcessing = true);
        await _processScannedCode(code);
        break;
      }
    }
  }

  Future<void> _processScannedCode(String rawCode) async {
    final alatProvider = context.read<AlatProvider>();
    final result = await alatProvider.scanAlat(rawCode);

    if (!mounted) return;

    if (result['success'] == true) {
      final alat = result['alat'] as AlatModel;
      _showScanResultSheet(alat);
    } else {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.error_outline, color: Colors.red),
              SizedBox(width: 8),
              Text('Alat Tidak Ditemukan'),
            ],
          ),
          content: Text(result['message'] ?? 'Kode alat tidak terdaftar dalam sistem SIIS.'),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                setState(() => _isProcessing = false);
              },
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
              child: const Text('Pindai Lagi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
    }
  }

  void _showScanResultSheet(AlatModel alat) {
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
                  const Row(
                    children: [
                      Icon(Icons.check_circle, color: Color(0xFF059669), size: 24),
                      SizedBox(width: 8),
                      Text(
                        'Alat Teridentifikasi',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
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

              if (authProvider.isPeminjam && alat.isAvailable)
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
                      peminjamanProvider.addToCart(alat, jumlah: 1);
                      Navigator.pop(ctx);
                      setState(() => _isProcessing = false);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('${alat.namaAlat} berhasil ditambahkan ke keranjang.'),
                          backgroundColor: const Color(0xFF059669),
                        ),
                      );
                    },
                    icon: const Icon(Icons.add_shopping_cart, color: Colors.white),
                    label: const Text('Tambahkan ke Keranjang Pinjam', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
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
        content: TextField(
          controller: _manualInputController,
          decoration: InputDecoration(
            hintText: 'Contoh: PROJ-001 / MIC-001',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B)))),
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
            child: const Text('Cari', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
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

          // Instructions at bottom
          Positioned(
            bottom: 40,
            left: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.75),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
              ),
              child: const Text(
                'Arahkan kamera ke stiker QR Code yang tertera pada alat inventaris sekolah.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w500),
              ),
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

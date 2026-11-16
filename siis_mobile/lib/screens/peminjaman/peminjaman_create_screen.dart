import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../providers/alat_provider.dart';
import '../../providers/peminjaman_provider.dart';

class PeminjamanCreateScreen extends StatefulWidget {
  const PeminjamanCreateScreen({super.key});

  @override
  State<PeminjamanCreateScreen> createState() => _PeminjamanCreateScreenState();
}

class _PeminjamanCreateScreenState extends State<PeminjamanCreateScreen> {
  DateTime? _tanggalPinjam;
  DateTime? _tanggalHarusKembali;
  final _keperluanController = TextEditingController();
  final DateFormat _dateFormat = DateFormat('yyyy-MM-dd');
  final DateFormat _displayFormat = DateFormat('dd MMMM yyyy');

  @override
  void initState() {
    super.initState();
    _tanggalPinjam = DateTime.now();
    _tanggalHarusKembali = DateTime.now().add(const Duration(days: 3));
  }

  @override
  void dispose() {
    _keperluanController.dispose();
    super.dispose();
  }

  Future<void> _selectDatePinjam() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tanggalPinjam ?? DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
    );
    if (picked != null) {
      setState(() {
        _tanggalPinjam = picked;
        if (_tanggalHarusKembali != null && _tanggalHarusKembali!.isBefore(picked)) {
          _tanggalHarusKembali = picked.add(const Duration(days: 1));
        }
      });
    }
  }

  Future<void> _selectDateKembali() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tanggalHarusKembali ?? (_tanggalPinjam?.add(const Duration(days: 1)) ?? DateTime.now()),
      firstDate: _tanggalPinjam ?? DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
    );
    if (picked != null) {
      setState(() {
        _tanggalHarusKembali = picked;
      });
    }
  }

  void _showAddToolDialog() {
    final alatProvider = context.read<AlatProvider>();
    final peminjamanProvider = context.read<PeminjamanProvider>();

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        final availableTools = alatProvider.alatList.where((a) => a.isAvailable).toList();

        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 48,
                  height: 4,
                  decoration: BoxDecoration(
                    color: const Color(0xFFA7F3D0),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Pilih Alat untuk Dipinjam',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
              ),
              const SizedBox(height: 12),
              if (availableTools.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 24),
                  child: Center(
                    child: Text(
                      'Tidak ada alat yang stoknya tersedia saat ini.',
                      style: TextStyle(color: Color(0xFF64748B)),
                    ),
                  ),
                )
              else
                Expanded(
                  child: ListView.separated(
                    itemCount: availableTools.length,
                    separatorBuilder: (_, __) => const Divider(color: Color(0xFFF1F5F9)),
                    itemBuilder: (context, i) {
                      final alat = availableTools[i];
                      return ListTile(
                        contentPadding: EdgeInsets.zero,
                        title: Text(alat.namaAlat, style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF064E3B))),
                        subtitle: Text('${alat.kodeAlat} • Stok: ${alat.stokTersedia} unit', style: const TextStyle(color: Color(0xFF64748B))),
                        trailing: ElevatedButton(
                          onPressed: () {
                            peminjamanProvider.addToCart(alat, jumlah: 1);
                            Navigator.pop(ctx);
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF059669),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: const Text('Pilih', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      );
                    },
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _handleSubmit() async {
    if (_tanggalPinjam == null || _tanggalHarusKembali == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih tanggal pinjam dan tanggal kembali terlebih dahulu.')),
      );
      return;
    }

    final peminjamanProvider = context.read<PeminjamanProvider>();
    if (peminjamanProvider.isCartEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih minimal satu alat untuk diajukan peminjaman.')),
      );
      return;
    }

    final result = await peminjamanProvider.submitPeminjaman(
      tanggalPinjam: _dateFormat.format(_tanggalPinjam!),
      tanggalHarusKembali: _dateFormat.format(_tanggalHarusKembali!),
      keperluan: _keperluanController.text.trim(),
    );

    if (!mounted) return;

    if (result['success'] == true) {
      _keperluanController.clear();
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.check_circle, color: Color(0xFF059669)),
              SizedBox(width: 8),
              Text('Pengajuan Terkirim', style: TextStyle(color: Color(0xFF064E3B), fontWeight: FontWeight.bold)),
            ],
          ),
          content: const Text(
            'Pengajuan peminjaman Anda berhasil diajukan dan sedang menunggu persetujuan dari Staff / Wakasek Sarpras.',
            style: TextStyle(color: Color(0xFF334155)),
          ),
          actions: [
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('OK', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Gagal membuat pengajuan.'),
          backgroundColor: Colors.red.shade700,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final peminjamanProvider = context.watch<PeminjamanProvider>();

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Form Pengajuan Pinjam'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Section 1: Dates
            Card(
              elevation: 1,
              shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
                side: const BorderSide(color: Color(0xFFA7F3D0)),
              ),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Jadwal Peminjaman',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                    ),
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        // Tanggal Pinjam
                        Expanded(
                          child: InkWell(
                            onTap: _selectDatePinjam,
                            borderRadius: BorderRadius.circular(14),
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0FDF4),
                                border: Border.all(color: const Color(0xFFA7F3D0)),
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Tanggal Pinjam', style: TextStyle(fontSize: 11, color: Color(0xFF047857), fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 4),
                                  Text(
                                    _tanggalPinjam != null ? _displayFormat.format(_tanggalPinjam!) : 'Pilih Tanggal',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF064E3B)),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),

                        // Tanggal Kembali
                        Expanded(
                          child: InkWell(
                            onTap: _selectDateKembali,
                            borderRadius: BorderRadius.circular(14),
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0FDF4),
                                border: Border.all(color: const Color(0xFFA7F3D0)),
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Tanggal Kembali', style: TextStyle(fontSize: 11, color: Color(0xFF047857), fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 4),
                                  Text(
                                    _tanggalHarusKembali != null
                                        ? _displayFormat.format(_tanggalHarusKembali!)
                                        : 'Pilih Tanggal',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF064E3B)),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Section 2: Dynamic Cart / Item Selector
            Card(
              elevation: 1,
              shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
                side: const BorderSide(color: Color(0xFFA7F3D0)),
              ),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Daftar Alat Dipinjam',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                        ),
                        TextButton.icon(
                          onPressed: _showAddToolDialog,
                          icon: const Icon(Icons.add, size: 18, color: Color(0xFF059669)),
                          label: const Text('Pilih Alat', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    if (peminjamanProvider.cart.isEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 28),
                        width: double.infinity,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0FDF4),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: Column(
                          children: [
                            const Icon(Icons.shopping_bag_outlined, size: 36, color: Color(0xFF6EE7B7)),
                            const SizedBox(height: 8),
                            const Text(
                              'Belum ada alat yang dipilih',
                              style: TextStyle(color: Color(0xFF065F46), fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                            const SizedBox(height: 10),
                            ElevatedButton(
                              onPressed: _showAddToolDialog,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFFD1FAE5),
                                foregroundColor: const Color(0xFF065F46),
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              child: const Text('Buka Daftar Alat', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      )
                    else
                      ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: peminjamanProvider.cart.length,
                        separatorBuilder: (_, __) => const Divider(color: Color(0xFFE2E8F0)),
                        itemBuilder: (context, idx) {
                          final item = peminjamanProvider.cart[idx];

                          return Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      item.alat.namaAlat,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF064E3B)),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      '${item.alat.kodeAlat} • Tersedia: ${item.alat.stokTersedia}',
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                    ),
                                  ],
                                ),
                              ),

                              // Stepper (+ / -)
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.remove_circle_outline, size: 20, color: Color(0xFF059669)),
                                    onPressed: () {
                                      peminjamanProvider.updateQuantity(item.alat.id, item.jumlah - 1);
                                    },
                                  ),
                                  Text(
                                    '${item.jumlah}',
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF064E3B)),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.add_circle_outline, size: 20, color: Color(0xFF059669)),
                                    onPressed: item.jumlah < item.alat.stokTersedia
                                        ? () {
                                            peminjamanProvider.updateQuantity(item.alat.id, item.jumlah + 1);
                                          }
                                        : null,
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline, size: 20, color: Colors.red),
                                    onPressed: () {
                                      peminjamanProvider.removeFromCart(item.alat.id);
                                    },
                                  ),
                                ],
                              ),
                            ],
                          );
                        },
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Section 3: Notes / Keperluan
            Card(
              elevation: 1,
              shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
                side: const BorderSide(color: Color(0xFFA7F3D0)),
              ),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Keperluan Peminjaman',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: _keperluanController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Contoh: Praktikum IPA Fisika Kelas 10A materi Gelombang.',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                        filled: true,
                        fillColor: const Color(0xFFF0FDF4),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: const BorderSide(color: Color(0xFFA7F3D0)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: const BorderSide(color: Color(0xFF059669), width: 1.5),
                        ),
                        contentPadding: const EdgeInsets.all(14),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Summary & Submit Bar
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF064E3B), Color(0xFF047857)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF064E3B).withOpacity(0.3),
                    blurRadius: 12,
                    offset: const Offset(0, 5),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Total Pinjaman:', style: TextStyle(fontSize: 12, color: Color(0xFFA7F3D0), fontWeight: FontWeight.bold)),
                      Text(
                        '${peminjamanProvider.cart.length} jenis • ${peminjamanProvider.totalCartItems} unit',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white),
                      ),
                    ],
                  ),
                  ElevatedButton(
                    onPressed: peminjamanProvider.isCartEmpty || peminjamanProvider.isLoading
                        ? null
                        : _handleSubmit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF10B981),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: peminjamanProvider.isLoading
                        ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Kirim Pengajuan', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

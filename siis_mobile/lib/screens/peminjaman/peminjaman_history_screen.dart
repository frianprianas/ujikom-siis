import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/peminjaman_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';

class PeminjamanHistoryScreen extends StatefulWidget {
  const PeminjamanHistoryScreen({super.key});

  @override
  State<PeminjamanHistoryScreen> createState() => _PeminjamanHistoryScreenState();
}

class _PeminjamanHistoryScreenState extends State<PeminjamanHistoryScreen> {
  String? _selectedFilter;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PeminjamanProvider>().fetchPeminjaman();
    });
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'menunggu_persetujuan':
        return const Color(0xFFD97706); // Amber
      case 'disetujui':
        return const Color(0xFF059669); // Emerald
      case 'sedang_dipinjam':
        return const Color(0xFF0D9488); // Teal
      case 'dikembalikan':
        return const Color(0xFF16A34A); // Green
      case 'ditolak':
      case 'terlambat':
        return const Color(0xFFDC2626); // Red
      default:
        return Colors.grey;
    }
  }

  Color _getStatusBg(String status) {
    switch (status) {
      case 'menunggu_persetujuan':
        return const Color(0xFFFEF3C7);
      case 'disetujui':
        return const Color(0xFFD1FAE5);
      case 'sedang_dipinjam':
        return const Color(0xFFCCFBF1);
      case 'dikembalikan':
        return const Color(0xFFDCFCE7);
      case 'ditolak':
      case 'terlambat':
        return const Color(0xFFFEE2E2);
      default:
        return const Color(0xFFF1F5F9);
    }
  }

  void _showDetailModal(BuildContext context, PeminjamanModel loan) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
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
              const SizedBox(height: 18),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Peminjaman #${loan.id}',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: _getStatusBg(loan.status),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      loan.statusLabel,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: _getStatusColor(loan.status),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(color: Color(0xFFE2E8F0)),
              const SizedBox(height: 8),

              if (loan.user != null) ...[
                Text('Peminjam: ${loan.user!.nama} (${loan.user!.role})',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF064E3B))),
                const SizedBox(height: 6),
              ],
              Text('Tanggal Pengajuan: ${loan.tanggalPengajuan.split('T').first}',
                  style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
              Text('Periode Pinjam: ${loan.tanggalPinjam} s/d ${loan.tanggalHarusKembali}',
                  style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
              if (loan.tanggalApproval != null)
                Text('Disetujui Oleh: ${loan.approvedBy?.nama ?? '-'}',
                    style: const TextStyle(fontSize: 13, color: Color(0xFF64748B))),
              if (loan.catatanApproval != null) ...[
                const SizedBox(height: 6),
                Text('Catatan: ${loan.catatanApproval}',
                    style: const TextStyle(fontSize: 13, fontStyle: FontStyle.italic, color: Color(0xFF047857))),
              ],

              const SizedBox(height: 16),
              const Text('Alat yang Dipinjam:', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF064E3B))),
              const SizedBox(height: 8),
              ...loan.details.map((detail) {
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(detail.alat?.namaAlat ?? 'Alat #${detail.alatId}',
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
                            if (detail.unitAlat != null) ...[
                              const SizedBox(height: 3),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFECFDF5),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFFA7F3D0)),
                                ),
                                child: Text(
                                  'Unit: ${detail.unitAlat!.kodeUnit}${detail.unitAlat!.nomorSeri != null ? ' (${detail.unitAlat!.nomorSeri})' : ''}',
                                  style: const TextStyle(fontSize: 11, fontFamily: 'monospace', fontWeight: FontWeight.bold, color: Color(0xFF065F46)),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      Text('${detail.jumlah} unit',
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                    ],
                  ),
                );
              }),

              if (loan.status == 'dikembalikan') ...[
                const SizedBox(height: 16),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFDCFCE7),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF86EFAC)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.assignment_turned_in, size: 18, color: Color(0xFF16A34A)),
                          SizedBox(width: 8),
                          Text(
                            'Keterangan: Barang Sudah Dikembalikan',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF14532D),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Waktu Dikembalikan: ${loan.tanggalAktualKembali != null ? loan.tanggalAktualKembali!.split('T').first : 'Telah Masuk Inventaris'}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF166534)),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Pemeriksaan fisik sarpras selesai & stok inventaris otomatis dipulihkan.',
                        style: TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: Color(0xFF15803D)),
                      ),
                    ],
                  ),
                ),
              ],

              if (context.read<AuthProvider>().canApprove &&
                  (loan.status == 'sedang_dipinjam' || loan.status == 'disetujui' || loan.status == 'terlambat')) ...[
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (dCtx) => AlertDialog(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                          title: const Row(
                            children: [
                              Icon(Icons.inventory, color: Color(0xFF059669), size: 20),
                              SizedBox(width: 8),
                              Text('Konfirmasi Pengembalian',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF064E3B))),
                            ],
                          ),
                          content: Text(
                            'Apakah seluruh barang peminjaman #${loan.id} milik ${loan.user?.nama ?? 'peminjam'} sudah diterima kembali di sarpras dalam kondisi baik?',
                            style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
                          ),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(dCtx, false), child: const Text('Batal')),
                            ElevatedButton(
                              onPressed: () => Navigator.pop(dCtx, true),
                              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                              child: const Text('Ya, Barang Sudah Kembali', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      );

                      if (confirm == true) {
                        final res = await context.read<PeminjamanProvider>().returnPeminjaman(loan.id);
                        if (!context.mounted) return;
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(res['message'] ?? 'Barang berhasil ditandai telah dikembalikan.'),
                            backgroundColor: const Color(0xFF059669),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      }
                    },
                    icon: const Icon(Icons.check_circle_outline, color: Colors.white, size: 18),
                    label: const Text('Tandai Barang Sudah Dikembalikan',
                        style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 13)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      elevation: 2,
                    ),
                  ),
                ),
              ],
              const SizedBox(height: 20),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final peminjamanProvider = context.watch<PeminjamanProvider>();
    final authProvider = context.watch<AuthProvider>();

    final items = peminjamanProvider.peminjamanList.where((p) {
      if (_selectedFilter == null) return true;
      return p.status == _selectedFilter;
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: Text(authProvider.isPeminjam ? 'Riwayat Peminjaman' : 'Daftar Semua Peminjaman'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filter Chips
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _chip('Semua Status', null),
                  const SizedBox(width: 8),
                  _chip('Menunggu', 'menunggu_persetujuan'),
                  const SizedBox(width: 8),
                  _chip('Disetujui', 'disetujui'),
                  const SizedBox(width: 8),
                  _chip('Dipinjam', 'sedang_dipinjam'),
                  const SizedBox(width: 8),
                  _chip('Dikembalikan', 'dikembalikan'),
                ],
              ),
            ),
          ),

          // List
          Expanded(
            child: peminjamanProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
                : items.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.assignment_outlined, size: 56, color: Color(0xFFA7F3D0)),
                            const SizedBox(height: 12),
                            const Text(
                              'Belum ada riwayat peminjaman.',
                              style: TextStyle(color: Color(0xFF065F46), fontSize: 15, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        color: const Color(0xFF059669),
                        onRefresh: () => peminjamanProvider.fetchPeminjaman(showLoading: false),
                        child: ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: items.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (context, idx) {
                            final loan = items[idx];

                            return Card(
                              elevation: 1,
                              shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(20),
                                side: const BorderSide(color: Color(0xFFA7F3D0)),
                              ),
                              color: Colors.white,
                              child: InkWell(
                                borderRadius: BorderRadius.circular(20),
                                onTap: () => _showDetailModal(context, loan),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Header card: ID & Status badge
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            'Peminjaman #${loan.id}',
                                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF064E3B)),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: _getStatusBg(loan.status),
                                              borderRadius: BorderRadius.circular(20),
                                            ),
                                            child: Text(
                                              loan.statusLabel,
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                                color: _getStatusColor(loan.status),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 8),

                                      // Borrower name (if staff or kepsek)
                                      if (!authProvider.isPeminjam && loan.user != null) ...[
                                        Row(
                                          children: [
                                            const Icon(Icons.person_outline, size: 15, color: Color(0xFF059669)),
                                            const SizedBox(width: 4),
                                            Text(
                                              '${loan.user!.nama} (${loan.user!.roleLabel})',
                                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),
                                      ],

                                      // Period
                                      Row(
                                        children: [
                                          const Icon(Icons.calendar_today_outlined, size: 14, color: Color(0xFF059669)),
                                          const SizedBox(width: 6),
                                          Text(
                                            '${loan.tanggalPinjam} s/d ${loan.tanggalHarusKembali}',
                                            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 10),
                                      const Divider(height: 1, color: Color(0xFFF1F5F9)),
                                      const SizedBox(height: 8),

                                      // Summary of items
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            '${loan.details.length} jenis alat (${loan.totalItemCount} unit)',
                                            style: const TextStyle(fontSize: 13, color: Color(0xFF064E3B), fontWeight: FontWeight.w600),
                                          ),
                                          const Icon(Icons.chevron_right, size: 20, color: Color(0xFF059669)),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _chip(String label, String? value) {
    final isSelected = _selectedFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: const Color(0xFF059669),
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : const Color(0xFF064E3B),
        fontWeight: FontWeight.bold,
        fontSize: 12,
      ),
      backgroundColor: const Color(0xFFECFDF5),
      side: const BorderSide(color: Color(0xFFA7F3D0)),
      onSelected: (_) => setState(() => _selectedFilter = value),
    );
  }
}

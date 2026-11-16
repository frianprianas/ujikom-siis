import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/peminjaman_model.dart';
import '../../providers/peminjaman_provider.dart';

class ApprovalListScreen extends StatefulWidget {
  const ApprovalListScreen({super.key});

  @override
  State<ApprovalListScreen> createState() => _ApprovalListScreenState();
}

class _ApprovalListScreenState extends State<ApprovalListScreen> {
  final _reasonController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PeminjamanProvider>().fetchPeminjaman(status: 'menunggu_persetujuan');
    });
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  void _showApproveDialog(BuildContext context, PeminjamanModel loan) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Setujui Pengajuan #${loan.id}?', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF064E3B))),
        content: Text(
          'Apakah Anda yakin menyetujui peminjaman oleh ${loan.user?.nama ?? 'Pemohon'}?\n'
          'Stok alat akan otomatis dialokasikan untuk peminjam ini.',
          style: const TextStyle(color: Color(0xFF334155)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final res = await context.read<PeminjamanProvider>().approvePeminjaman(loan.id);
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Status berhasil diperbarui.'),
                  backgroundColor: res['success'] == true ? const Color(0xFF059669) : Colors.red,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Ya, Setujui', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _showRejectDialog(BuildContext context, PeminjamanModel loan) {
    _reasonController.clear();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Tolak Pengajuan #${loan.id}', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF991B1B))),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tuliskan alasan penolakan untuk peminjam:', style: TextStyle(color: Color(0xFF334155))),
            const SizedBox(height: 12),
            TextField(
              controller: _reasonController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'Contoh: Alat sedang dalam perawatan rutin.',
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
            onPressed: () async {
              if (_reasonController.text.trim().isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Alasan penolakan wajib diisi.')),
                );
                return;
              }
              Navigator.pop(ctx);
              final res = await context.read<PeminjamanProvider>().rejectPeminjaman(
                    loan.id,
                    catatan: _reasonController.text.trim(),
                  );
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Pengajuan ditolak.'),
                  backgroundColor: Colors.red.shade700,
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red.shade700,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Tolak Pengajuan', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final peminjamanProvider = context.watch<PeminjamanProvider>();
    final pendingLoans = peminjamanProvider.peminjamanList
        .where((p) => p.status == 'menunggu_persetujuan')
        .toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Persetujuan Peminjaman'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
      ),
      body: peminjamanProvider.isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : pendingLoans.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD1FAE5),
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: const Icon(Icons.task_alt, size: 56, color: Color(0xFF059669)),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'Semua Beres!',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Tidak ada permohonan peminjaman yang menunggu persetujuan.',
                        style: TextStyle(color: Color(0xFF047857), fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFF059669),
                  onRefresh: () => peminjamanProvider.fetchPeminjaman(
                    status: 'menunggu_persetujuan',
                    showLoading: false,
                  ),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: pendingLoans.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 14),
                    itemBuilder: (context, idx) {
                      final loan = pendingLoans[idx];

                      return Card(
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
                              // Borrower Header
                              Row(
                                children: [
                                  CircleAvatar(
                                    backgroundColor: const Color(0xFFECFDF5),
                                    foregroundColor: const Color(0xFF065F46),
                                    child: Text(
                                      (loan.user?.nama ?? 'U').substring(0, 1).toUpperCase(),
                                      style: const TextStyle(fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          loan.user?.nama ?? 'Peminjam',
                                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF064E3B)),
                                        ),
                                        Text(
                                          '${loan.user?.roleLabel ?? ''} • ${loan.user?.kelasAtauJabatan ?? ''}',
                                          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFEF3C7),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Text(
                                      'Menunggu',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFFB45309),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              const Divider(color: Color(0xFFF1F5F9)),
                              const SizedBox(height: 8),

                              // Date Range
                              Row(
                                children: [
                                  const Icon(Icons.date_range, size: 16, color: Color(0xFF059669)),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Periode: ${loan.tanggalPinjam} s/d ${loan.tanggalHarusKembali}',
                                    style: const TextStyle(fontSize: 13, color: Color(0xFF334155), fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                              if (loan.catatanApproval != null) ...[
                                const SizedBox(height: 4),
                                Text(
                                  loan.catatanApproval!,
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF047857), fontStyle: FontStyle.italic),
                                ),
                              ],
                              const SizedBox(height: 10),

                              // Detail items
                              const Text('Item Diajukan:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF064E3B))),
                              const SizedBox(height: 6),
                              ...loan.details.map((d) {
                                return Padding(
                                  padding: const EdgeInsets.only(bottom: 4),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text('• ${d.alat?.namaAlat ?? 'Alat'}', style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                                            if (d.unitAlat != null) ...[
                                              const SizedBox(height: 2),
                                              Text(
                                                '  Unit: ${d.unitAlat!.kodeUnit}',
                                                style: const TextStyle(fontSize: 11, fontFamily: 'monospace', fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                                              ),
                                            ],
                                          ],
                                        ),
                                      ),
                                      Text('${d.jumlah} unit', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                                    ],
                                  ),
                                );
                              }),

                              const SizedBox(height: 16),

                              // Action Buttons (Approve & Reject)
                              Row(
                                children: [
                                  Expanded(
                                    child: OutlinedButton(
                                      onPressed: () => _showRejectDialog(context, loan),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: Colors.red.shade700,
                                        side: BorderSide(color: Colors.red.shade200),
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                      child: const Text('Tolak', style: TextStyle(fontWeight: FontWeight.bold)),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Container(
                                      decoration: BoxDecoration(
                                        gradient: const LinearGradient(
                                          colors: [Color(0xFF059669), Color(0xFF10B981)],
                                        ),
                                        borderRadius: BorderRadius.circular(12),
                                        boxShadow: [
                                          BoxShadow(
                                            color: const Color(0xFF059669).withOpacity(0.3),
                                            blurRadius: 8,
                                            offset: const Offset(0, 3),
                                          ),
                                        ],
                                      ),
                                      child: ElevatedButton(
                                        onPressed: () => _showApproveDialog(context, loan),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.transparent,
                                          shadowColor: Colors.transparent,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                        child: const Text('Setujui', style: TextStyle(fontWeight: FontWeight.bold)),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}

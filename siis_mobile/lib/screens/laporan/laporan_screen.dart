import 'package:flutter/material.dart';
import '../../services/dashboard_service.dart';

class LaporanScreen extends StatefulWidget {
  const LaporanScreen({super.key});

  @override
  State<LaporanScreen> createState() => _LaporanScreenState();
}

class _LaporanScreenState extends State<LaporanScreen> {
  final DashboardService _dashboardService = DashboardService();
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _laporanData;

  @override
  void initState() {
    super.initState();
    _fetchLaporan();
  }

  Future<void> _fetchLaporan() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await _dashboardService.getLaporan();

    setState(() {
      _isLoading = false;
      if (res['success'] == true) {
        _laporanData = res['data'] as Map<String, dynamic>?;
      } else {
        _errorMessage = res['message'] as String?;
      }
    });
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'menunggu_persetujuan':
        return const Color(0xFFD97706);
      case 'disetujui':
        return const Color(0xFF059669);
      case 'sedang_dipinjam':
        return const Color(0xFF0D9488);
      case 'dikembalikan':
        return const Color(0xFF16A34A);
      case 'ditolak':
      case 'terlambat':
        return const Color(0xFFDC2626);
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

  @override
  Widget build(BuildContext context) {
    final ringkasan = _laporanData?['ringkasan'] as Map<String, dynamic>? ?? {};
    final riwayat = _laporanData?['riwayat'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Laporan Inventaris & Peminjaman'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF059669)),
            onPressed: _fetchLaporan,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 48, color: Colors.red),
                      const SizedBox(height: 12),
                      Text(_errorMessage!),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _fetchLaporan,
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                        child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFF059669),
                  onRefresh: _fetchLaporan,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Header Banner
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF064E3B), Color(0xFF059669)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(24),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF064E3B).withOpacity(0.2),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.assessment_outlined, size: 38, color: Color(0xFFA7F3D0)),
                              SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Rekapitulasi Sirkulasi Sarpras',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w800,
                                        color: Colors.white,
                                      ),
                                    ),
                                    SizedBox(height: 4),
                                    Text(
                                      'Ringkasan data permohonan, approval, dan pengembalian alat sekolah',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Color(0xFFA7F3D0),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Summary Metric Cards
                        const Text(
                          'Ringkasan Aktivitas',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF064E3B),
                          ),
                        ),
                        const SizedBox(height: 12),

                        GridView.count(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          crossAxisCount: 2,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 1.5,
                          children: [
                            _buildStatCard(
                              'Total Peminjaman',
                              '${ringkasan['total_peminjaman'] ?? 0}',
                              Icons.assignment,
                              const Color(0xFF059669),
                              const Color(0xFFECFDF5),
                            ),
                            _buildStatCard(
                              'Disetujui',
                              '${ringkasan['total_disetujui'] ?? 0}',
                              Icons.check_circle_outline,
                              const Color(0xFF0D9488),
                              const Color(0xFFF0FDFA),
                            ),
                            _buildStatCard(
                              'Dikembalikan',
                              '${ringkasan['total_dikembalikan'] ?? 0}',
                              Icons.assignment_turned_in_outlined,
                              const Color(0xFF16A34A),
                              const Color(0xFFDCFCE7),
                            ),
                            _buildStatCard(
                              'Ditolak',
                              '${ringkasan['total_ditolak'] ?? 0}',
                              Icons.cancel_outlined,
                              const Color(0xFFDC2626),
                              const Color(0xFFFEE2E2),
                            ),
                          ],
                        ),

                        const SizedBox(height: 24),

                        // Recent Transaction Log
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Riwayat Sirkulasi Terkini',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF064E3B),
                              ),
                            ),
                            Text(
                              'Update Real-time',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF059669),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        if (riwayat.isEmpty)
                          Container(
                            padding: const EdgeInsets.all(24),
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: const Color(0xFFA7F3D0)),
                            ),
                            child: const Text('Belum ada transaksi peminjaman.', style: TextStyle(color: Color(0xFF64748B))),
                          )
                        else
                          ...riwayat.map((item) {
                            final user = item['user'] as Map<String, dynamic>?;
                            final status = item['status'] as String? ?? 'menunggu_persetujuan';
                            final details = item['details'] as List<dynamic>? ?? [];

                            return Card(
                              elevation: 1,
                              margin: const EdgeInsets.only(bottom: 10),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                                side: const BorderSide(color: Color(0xFFA7F3D0)),
                              ),
                              color: Colors.white,
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          user?['nama'] ?? 'Peminjam',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF064E3B)),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: _getStatusBg(status),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            status.toUpperCase().replaceAll('_', ' '),
                                            style: TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: _getStatusColor(status),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      'Periode: ${item['tanggal_pinjam']} s/d ${item['tanggal_harus_kembali']}',
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                    ),
                                    if (item['tanggal_aktual_kembali'] != null)
                                      Text(
                                        'Dikembalikan: ${item['tanggal_aktual_kembali'].toString().split('T').first}',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF16A34A)),
                                      ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Barang: ${details.length} item dipinjam',
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF065F46), fontWeight: FontWeight.w600),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color, Color bgColor) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA7F3D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF064E3B).withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 18, color: color),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF64748B),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}

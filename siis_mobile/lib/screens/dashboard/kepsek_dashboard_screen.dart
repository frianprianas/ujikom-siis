import 'package:flutter/material.dart';
import '../../models/dashboard_stats_model.dart';
import '../../services/dashboard_service.dart';

class KepsekDashboardScreen extends StatefulWidget {
  const KepsekDashboardScreen({super.key});

  @override
  State<KepsekDashboardScreen> createState() => _KepsekDashboardScreenState();
}

class _KepsekDashboardScreenState extends State<KepsekDashboardScreen> {
  final DashboardService _dashboardService = DashboardService();
  DashboardStatsModel? _stats;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await _dashboardService.getStats();

    setState(() {
      _isLoading = false;
      if (res['success'] == true) {
        _stats = res['data'] as DashboardStatsModel;
      } else {
        _errorMessage = res['message'] as String?;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Dashboard Monitoring Eksekutif'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF059669)),
            onPressed: _loadStats,
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
                        onPressed: _loadStats,
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                        child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFF059669),
                  onRefresh: _loadStats,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Welcome Banner with Green Gradient
                        Container(
                          padding: const EdgeInsets.all(22),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF064E3B), Color(0xFF059669)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(24),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF064E3B).withOpacity(0.3),
                                blurRadius: 14,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Icon(Icons.school, color: Colors.white, size: 24),
                                  SizedBox(width: 8),
                                  Text(
                                    'SIIS Executive Portal',
                                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 17),
                                  ),
                                ],
                              ),
                              SizedBox(height: 10),
                              Text(
                                'Pantauan Real-time Inventaris & Peminjaman Sarana Prasarana Sekolah',
                                style: TextStyle(color: Color(0xFFA7F3D0), fontSize: 13, height: 1.4),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Section 1: Overview Alat
                        const Text(
                          'Ringkasan Alat & Stok',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _statCard(
                                title: 'Total Jenis Alat',
                                value: '${_stats?.totalAlat ?? 0}',
                                subtitle: '${_stats?.totalUnitAlat ?? 0} unit fisik',
                                icon: Icons.precision_manufacturing,
                                color: const Color(0xFF059669),
                                bg: const Color(0xFFECFDF5),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _statCard(
                                title: 'Unit Tersedia',
                                value: '${_stats?.unitTersedia ?? 0}',
                                subtitle: 'Siap dipinjam',
                                icon: Icons.check_circle,
                                color: const Color(0xFF10B981),
                                bg: const Color(0xFFD1FAE5),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _statCard(
                                title: 'Sedang Dipinjam',
                                value: '${_stats?.unitDipinjam ?? 0}',
                                subtitle: 'Sedang dipakai siswa/guru',
                                icon: Icons.schedule,
                                color: const Color(0xFF0D9488),
                                bg: const Color(0xFFCCFBF1),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _statCard(
                                title: 'Kondisi Baik',
                                value: '${_stats?.kondisiBaik ?? 0}',
                                subtitle: '${_stats?.kondisiRusakRingan ?? 0} rusak ringan',
                                icon: Icons.health_and_safety,
                                color: const Color(0xFF047857),
                                bg: const Color(0xFFECFDF5),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 24),

                        // Section 2: Ringkasan Peminjaman
                        const Text(
                          'Statistik Peminjaman',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF064E3B)),
                        ),
                        const SizedBox(height: 12),

                        Card(
                          elevation: 1,
                          shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                            side: const BorderSide(color: Color(0xFFA7F3D0)),
                          ),
                          color: Colors.white,
                          child: Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                              children: [
                                _metricRow('Total Keseluruhan Pengajuan', '${_stats?.peminjamanTotal ?? 0}', const Color(0xFF059669)),
                                const Divider(height: 24, color: Color(0xFFF1F5F9)),
                                _metricRow('Menunggu Persetujuan Sarpras', '${_stats?.peminjamanMenunggu ?? 0}', const Color(0xFFD97706)),
                                const Divider(height: 24, color: Color(0xFFF1F5F9)),
                                _metricRow('Sedang Dipinjam Saat Ini', '${_stats?.peminjamanSedangDipinjam ?? 0}', const Color(0xFF0D9488)),
                                const Divider(height: 24, color: Color(0xFFF1F5F9)),
                                _metricRow('Peminjaman Telah Selesai', '${_stats?.peminjamanSelesai ?? 0}', const Color(0xFF16A34A)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _statCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
    required Color bg,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA7F3D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF064E3B).withOpacity(0.03),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: bg,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: color.withOpacity(0.2)),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(height: 12),
          Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: Color(0xFF064E3B))),
          const SizedBox(height: 2),
          Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF334155))),
          const SizedBox(height: 2),
          Text(subtitle, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
        ],
      ),
    );
  }

  Widget _metricRow(String title, String value, Color color) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(title, style: const TextStyle(fontSize: 14, color: Color(0xFF334155), fontWeight: FontWeight.w500)),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
          decoration: BoxDecoration(color: color.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
          child: Text(
            value,
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color),
          ),
        ),
      ],
    );
  }
}

class DashboardStatsModel {
  final int totalAlat;
  final int totalUnitAlat;
  final int unitTersedia;
  final int unitDipinjam;
  final int kondisiBaik;
  final int kondisiRusakRingan;
  final int kondisiRusakBerat;
  final int peminjamanTotal;
  final int peminjamanMenunggu;
  final int peminjamanSedangDipinjam;
  final int peminjamanSelesai;

  DashboardStatsModel({
    required this.totalAlat,
    required this.totalUnitAlat,
    required this.unitTersedia,
    required this.unitDipinjam,
    required this.kondisiBaik,
    required this.kondisiRusakRingan,
    required this.kondisiRusakBerat,
    required this.peminjamanTotal,
    required this.peminjamanMenunggu,
    required this.peminjamanSedangDipinjam,
    required this.peminjamanSelesai,
  });

  factory DashboardStatsModel.fromJson(Map<String, dynamic> json) {
    final kondisi = json['kondisi'] as Map<String, dynamic>? ?? {};
    final peminjaman = json['peminjaman'] as Map<String, dynamic>? ?? {};

    return DashboardStatsModel(
      totalAlat: json['total_alat'] as int? ?? 0,
      totalUnitAlat: json['total_unit_alat'] as int? ?? 0,
      unitTersedia: json['unit_tersedia'] as int? ?? 0,
      unitDipinjam: json['unit_dipinjam'] as int? ?? 0,
      kondisiBaik: kondisi['baik'] as int? ?? 0,
      kondisiRusakRingan: kondisi['rusak_ringan'] as int? ?? 0,
      kondisiRusakBerat: kondisi['rusak_berat'] as int? ?? 0,
      peminjamanTotal: peminjaman['total'] as int? ?? 0,
      peminjamanMenunggu: peminjaman['menunggu'] as int? ?? 0,
      peminjamanSedangDipinjam: peminjaman['sedang_dipinjam'] as int? ?? 0,
      peminjamanSelesai: peminjaman['selesai'] as int? ?? 0,
    );
  }
}


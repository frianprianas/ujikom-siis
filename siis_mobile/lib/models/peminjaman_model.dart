import 'alat_model.dart';
import 'unit_alat_model.dart';
import 'user_model.dart';

class DetailPeminjamanModel {
  final int id;
  final int peminjamanId;
  final int alatId;
  final int? unitAlatId;
  final int jumlah;
  final String? kondisiSaatPinjam;
  final String? kondisiSaatKembali;
  final AlatModel? alat;
  final UnitAlatModel? unitAlat;

  DetailPeminjamanModel({
    required this.id,
    required this.peminjamanId,
    required this.alatId,
    this.unitAlatId,
    required this.jumlah,
    this.kondisiSaatPinjam,
    this.kondisiSaatKembali,
    this.alat,
    this.unitAlat,
  });

  factory DetailPeminjamanModel.fromJson(Map<String, dynamic> json) {
    return DetailPeminjamanModel(
      id: json['id'] as int,
      peminjamanId: json['peminjaman_id'] as int,
      alatId: json['alat_id'] as int,
      unitAlatId: json['unit_alat_id'] as int?,
      jumlah: json['jumlah'] as int? ?? 1,
      kondisiSaatPinjam: json['kondisi_saat_pinjam'] as String?,
      kondisiSaatKembali: json['kondisi_saat_kembali'] as String?,
      alat: json['alat'] != null
          ? AlatModel.fromJson(json['alat'] as Map<String, dynamic>)
          : null,
      unitAlat: json['unit_alat'] != null
          ? UnitAlatModel.fromJson(json['unit_alat'] as Map<String, dynamic>)
          : (json['unitAlat'] != null
              ? UnitAlatModel.fromJson(json['unitAlat'] as Map<String, dynamic>)
              : null),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'peminjaman_id': peminjamanId,
      'alat_id': alatId,
      'unit_alat_id': unitAlatId,
      'jumlah': jumlah,
      'kondisi_saat_pinjam': kondisiSaatPinjam,
      'kondisi_saat_kembali': kondisiSaatKembali,
      'alat': alat?.toJson(),
      'unit_alat': unitAlat?.toJson(),
    };
  }
}

class PeminjamanModel {
  final int id;
  final int userId;
  final String tanggalPengajuan;
  final String tanggalPinjam;
  final String tanggalHarusKembali;
  final String? tanggalAktualKembali;
  final String status;
  final int? approvedById;
  final String? tanggalApproval;
  final String? catatanApproval;
  final UserModel? user;
  final UserModel? approvedBy;
  final List<DetailPeminjamanModel> details;

  PeminjamanModel({
    required this.id,
    required this.userId,
    required this.tanggalPengajuan,
    required this.tanggalPinjam,
    required this.tanggalHarusKembali,
    this.tanggalAktualKembali,
    required this.status,
    this.approvedById,
    this.tanggalApproval,
    this.catatanApproval,
    this.user,
    this.approvedBy,
    this.details = const [],
  });

  factory PeminjamanModel.fromJson(Map<String, dynamic> json) {
    var detailsJson = json['details'] as List<dynamic>? ?? [];
    List<DetailPeminjamanModel> detailList = detailsJson
        .map((d) => DetailPeminjamanModel.fromJson(d as Map<String, dynamic>))
        .toList();

    return PeminjamanModel(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      tanggalPengajuan: json['tanggal_pengajuan'] as String? ?? '',
      tanggalPinjam: json['tanggal_pinjam'] as String? ?? '',
      tanggalHarusKembali: json['tanggal_harus_kembali'] as String? ?? '',
      tanggalAktualKembali: json['tanggal_aktual_kembali'] as String?,
      status: json['status'] as String? ?? 'menunggu_persetujuan',
      approvedById: json['approved_by_id'] as int?,
      tanggalApproval: json['tanggal_approval'] as String?,
      catatanApproval: json['catatan_approval'] as String?,
      user: json['user'] != null
          ? UserModel.fromJson(json['user'] as Map<String, dynamic>)
          : null,
      approvedBy: json['approved_by'] != null
          ? UserModel.fromJson(json['approved_by'] as Map<String, dynamic>)
          : null,
      details: detailList,
    );
  }

  String get statusLabel {
    switch (status) {
      case 'menunggu_persetujuan':
        return 'Menunggu Persetujuan';
      case 'disetujui':
        return 'Disetujui (Siap Diambil)';
      case 'ditolak':
        if (catatanApproval != null && catatanApproval!.contains('Batal Otomatis')) {
          return 'Batal Otomatis (1 Jam)';
        }
        return 'Ditolak';
      case 'sedang_dipinjam':
        return 'Sedang Dipinjam';
      case 'dikembalikan':
        return 'Sudah Dikembalikan';
      case 'terlambat':
        return 'Terlambat';
      default:
        return status;
    }
  }

  int get totalItemCount {
    return details.fold(0, (sum, item) => sum + item.jumlah);
  }
}


class UnitAlatModel {
  final int id;
  final int alatId;
  final String kodeUnit;
  final String? nomorSeri;
  final String kondisi;
  final String status;
  final String? lokasiSpesifik;
  final String? catatan;

  UnitAlatModel({
    required this.id,
    required this.alatId,
    required this.kodeUnit,
    this.nomorSeri,
    required this.kondisi,
    required this.status,
    this.lokasiSpesifik,
    this.catatan,
  });

  factory UnitAlatModel.fromJson(Map<String, dynamic> json) {
    return UnitAlatModel(
      id: json['id'] as int,
      alatId: json['alat_id'] as int? ?? 0,
      kodeUnit: json['kode_unit'] as String? ?? '',
      nomorSeri: json['nomor_seri'] as String?,
      kondisi: json['kondisi'] as String? ?? 'baik',
      status: json['status'] as String? ?? 'tersedia',
      lokasiSpesifik: json['lokasi_spesifik'] as String?,
      catatan: json['catatan'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'alat_id': alatId,
      'kode_unit': kodeUnit,
      'nomor_seri': nomorSeri,
      'kondisi': kondisi,
      'status': status,
      'lokasi_spesifik': lokasiSpesifik,
      'catatan': catatan,
    };
  }

  String get kondisiLabel {
    switch (kondisi) {
      case 'baik':
        return 'Baik';
      case 'rusak_ringan':
        return 'Rusak Ringan';
      case 'rusak_berat':
        return 'Rusak Berat';
      default:
        return kondisi;
    }
  }

  String get statusLabel {
    switch (status) {
      case 'tersedia':
        return 'Tersedia';
      case 'dipinjam':
        return 'Dipinjam';
      case 'perbaikan':
        return 'Perbaikan';
      case 'hilang':
        return 'Hilang';
      default:
        return status;
    }
  }

  bool isTersedia() => status == 'tersedia' && kondisi != 'rusak_berat';
  bool get isAvailable => isTersedia();
}


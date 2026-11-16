import 'kategori_model.dart';
import 'unit_alat_model.dart';

class AlatModel {
  final int id;
  final int kategoriId;
  final String namaAlat;
  final String kodeAlat;
  final String kondisi;
  final int stokTotal;
  final int stokTersedia;
  final String? lokasiPenyimpanan;
  final KategoriModel? kategori;
  final List<UnitAlatModel> units;

  AlatModel({
    required this.id,
    required this.kategoriId,
    required this.namaAlat,
    required this.kodeAlat,
    required this.kondisi,
    required this.stokTotal,
    required this.stokTersedia,
    this.lokasiPenyimpanan,
    this.kategori,
    this.units = const [],
  });

  factory AlatModel.fromJson(Map<String, dynamic> json) {
    var rawUnits = json['units'] as List<dynamic>? ?? [];
    List<UnitAlatModel> unitList = rawUnits
        .map((u) => UnitAlatModel.fromJson(u as Map<String, dynamic>))
        .toList();

    return AlatModel(
      id: json['id'] as int,
      kategoriId: json['kategori_id'] as int,
      namaAlat: json['nama_alat'] as String? ?? '',
      kodeAlat: json['kode_alat'] as String? ?? '',
      kondisi: json['kondisi'] as String? ?? 'baik',
      stokTotal: json['stok_total'] as int? ?? 0,
      stokTersedia: json['stok_tersedia'] as int? ?? 0,
      lokasiPenyimpanan: json['lokasi_penyimpanan'] as String?,
      kategori: json['kategori'] != null
          ? KategoriModel.fromJson(json['kategori'] as Map<String, dynamic>)
          : null,
      units: unitList,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'kategori_id': kategoriId,
      'nama_alat': namaAlat,
      'kode_alat': kodeAlat,
      'kondisi': kondisi,
      'stok_total': stokTotal,
      'stok_tersedia': stokTersedia,
      'lokasi_penyimpanan': lokasiPenyimpanan,
      'kategori': kategori?.toJson(),
    };
  }

  bool get isAvailable => stokTersedia > 0;

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
}


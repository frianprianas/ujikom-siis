class KategoriModel {
  final int id;
  final String namaKategori;
  final String? deskripsi;

  KategoriModel({
    required this.id,
    required this.namaKategori,
    this.deskripsi,
  });

  factory KategoriModel.fromJson(Map<String, dynamic> json) {
    return KategoriModel(
      id: json['id'] as int,
      namaKategori: json['nama_kategori'] as String? ?? '',
      deskripsi: json['deskripsi'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_kategori': namaKategori,
      'deskripsi': deskripsi,
    };
  }
}


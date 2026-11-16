class UserModel {
  final int id;
  final String nama;
  final String email;
  final String role;
  final String? nomorIdentitas;
  final String? rfidUid;
  final String? kelasAtauJabatan;

  UserModel({
    required this.id,
    required this.nama,
    required this.email,
    required this.role,
    this.nomorIdentitas,
    this.rfidUid,
    this.kelasAtauJabatan,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      nama: json['nama'] as String? ?? '',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? 'siswa',
      nomorIdentitas: json['nomor_identitas'] as String?,
      rfidUid: json['rfid_uid'] as String?,
      kelasAtauJabatan: json['kelas_atau_jabatan'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama': nama,
      'email': email,
      'role': role,
      'nomor_identitas': nomorIdentitas,
      'rfid_uid': rfidUid,
      'kelas_atau_jabatan': kelasAtauJabatan,
    };
  }

  bool get isSuperAdmin => role == 'super_admin';
  bool get isPeminjam => role == 'siswa' || role == 'guru';
  bool get canApprove => role == 'super_admin' || role == 'staff_sarpras' || role == 'wakasek_sarpras';
  bool get canManageInventaris => role == 'super_admin' || role == 'staff_sarpras' || role == 'wakasek_sarpras';
  bool get canManageMaster => role == 'super_admin';
  bool get isKepalaSekolah => role == 'kepala_sekolah';

  String get roleLabel {
    switch (role) {
      case 'super_admin':
        return 'Super Admin';
      case 'siswa':
        return 'Siswa';
      case 'guru':
        return 'Guru / Pendidik';
      case 'staff_sarpras':
        return 'Staff Sarpras';
      case 'wakasek_sarpras':
        return 'Wakasek Sarpras';
      case 'kepala_sekolah':
        return 'Kepala Sekolah';
      default:
        return role;
    }
  }
}


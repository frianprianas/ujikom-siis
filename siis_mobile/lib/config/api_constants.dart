class ApiConstants {
  // Pilihan URL Server Backend SIIS:
  // 1. VPS Cloud / Ujikom (Aktif): 'http://202.10.40.177:8080/api/v1'
  // 2. Android Emulator (Localhost PC): 'http://10.0.2.2:8000/api/v1' (atau port 8080)
  // 3. HP Fisik via Wi-Fi LAN: 'http://<IP_KOMPUTER_ANDA>:8000/api/v1'
  static const String baseUrl = 'http://202.10.40.177:8080/api/v1';

  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';

  static const String alat = '/alat';
  static const String scanAlat = '/alat/scan';

  static const String peminjaman = '/peminjaman';
  static const String dashboardStats = '/dashboard/stats';
  static const String kategori = '/kategori';
  static const String laporan = '/dashboard/laporan';
}


class ApiConstants {
  // Ganti IP dengan IP LAN mesin server Laravel (misal: 192.168.1.100:8000) atau 10.0.2.2 untuk Android Emulator
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';

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


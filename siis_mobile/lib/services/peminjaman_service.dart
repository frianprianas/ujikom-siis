import 'package:dio/dio.dart';
import '../config/api_constants.dart';
import '../models/peminjaman_model.dart';
import 'api_service.dart';

class PeminjamanService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getPeminjamanList({String? status}) async {
    try {
      final response = await _apiService.dio.get(
        ApiConstants.peminjaman,
        queryParameters: {
          if (status != null && status.isNotEmpty) 'status': status,
        },
      );

      final List<dynamic> data = response.data['data'] ?? [];
      final list = data.map((p) => PeminjamanModel.fromJson(p)).toList();

      return {
        'success': true,
        'data': list,
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal memuat data peminjaman.',
        'data': <PeminjamanModel>[],
      };
    }
  }

  Future<Map<String, dynamic>> storePeminjaman({
    required String tanggalPinjam,
    required String tanggalHarusKembali,
    String? keperluan,
    required List<Map<String, dynamic>> items,
  }) async {
    try {
      final response = await _apiService.dio.post(
        ApiConstants.peminjaman,
        data: {
          'tanggal_pinjam': tanggalPinjam,
          'tanggal_harus_kembali': tanggalHarusKembali,
          if (keperluan != null && keperluan.isNotEmpty) 'keperluan': keperluan,
          'items': items,
        },
      );

      return {
        'success': true,
        'message': response.data['message'] ?? 'Pengajuan peminjaman berhasil disimpan.',
        'data': PeminjamanModel.fromJson(response.data['data']),
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal membuat pengajuan peminjaman.',
      };
    }
  }

  Future<Map<String, dynamic>> approve(int id, {String? catatan}) async {
    try {
      final response = await _apiService.dio.post(
        '${ApiConstants.peminjaman}/$id/approve',
        data: {
          if (catatan != null) 'catatan_approval': catatan,
        },
      );

      return {
        'success': true,
        'message': response.data['message'] ?? 'Peminjaman berhasil disetujui.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal menyetujui peminjaman.',
      };
    }
  }

  Future<Map<String, dynamic>> reject(int id, {required String catatan}) async {
    try {
      final response = await _apiService.dio.post(
        '${ApiConstants.peminjaman}/$id/reject',
        data: {
          'catatan_approval': catatan,
        },
      );

      return {
        'success': true,
        'message': response.data['message'] ?? 'Peminjaman berhasil ditolak.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal menolak peminjaman.',
      };
    }
  }

  Future<Map<String, dynamic>> markAsReturned(int id, {Map<String, dynamic>? kondisiKembali}) async {
    try {
      final response = await _apiService.dio.post(
        '${ApiConstants.peminjaman}/$id/return',
        data: {
          if (kondisiKembali != null) 'kondisi_kembali': kondisiKembali,
        },
      );

      return {
        'success': true,
        'message': response.data['message'] ?? 'Alat berhasil dikembalikan ke inventaris.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal memproses pengembalian alat.',
      };
    }
  }
}


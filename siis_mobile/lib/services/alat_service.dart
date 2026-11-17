import 'package:dio/dio.dart';
import '../config/api_constants.dart';
import '../models/alat_model.dart';
import '../models/kategori_model.dart';
import '../models/unit_alat_model.dart';
import 'api_service.dart';

class AlatService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getAlatList({
    String? search,
    int? kategoriId,
    String? kondisi,
  }) async {
    try {
      final response = await _apiService.dio.get(
        ApiConstants.alat,
        queryParameters: {
          if (search != null && search.isNotEmpty) 'search': search,
          if (kategoriId != null) 'kategori_id': kategoriId,
          if (kondisi != null && kondisi.isNotEmpty) 'kondisi': kondisi,
        },
      );

      final data = response.data['data'];
      final List<dynamic> alatRaw = data['alat'] ?? [];
      final List<dynamic> kategoriRaw = data['kategori'] ?? [];

      final alatList = alatRaw.map((a) => AlatModel.fromJson(a)).toList();
      final kategoriList = kategoriRaw.map((k) => KategoriModel.fromJson(k)).toList();

      return {
        'success': true,
        'alat': alatList,
        'kategori': kategoriList,
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal memuat katalog alat.',
        'alat': <AlatModel>[],
        'kategori': <KategoriModel>[],
      };
    }
  }

  Future<Map<String, dynamic>> scanAlat(String kodeAlat) async {
    try {
      final cleanKode = kodeAlat.trim();
      final response = await _apiService.dio.get('${ApiConstants.scanAlat}/$cleanKode');

      if (response.data['success'] == true) {
        return {
          'success': true,
          'message': response.data['message'],
          'alat': AlatModel.fromJson(response.data['data']),
          'unit': response.data['unit'] != null
              ? UnitAlatModel.fromJson(response.data['unit'])
              : null,
        };
      }

      return {
        'success': false,
        'message': response.data['message'] ?? 'Alat tidak ditemukan.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? "Alat dengan kode '$kodeAlat' tidak ditemukan.",
      };
    }
  }

  Future<Map<String, dynamic>> createAlat({
    required int kategoriId,
    required String namaAlat,
    required String kodeAlat,
    required String kondisi,
    required int stokTotal,
    String? lokasiPenyimpanan,
  }) async {
    try {
      final response = await _apiService.dio.post(
        ApiConstants.alat,
        data: {
          'kategori_id': kategoriId,
          'nama_alat': namaAlat,
          'kode_alat': kodeAlat,
          'kondisi': kondisi,
          'stok_total': stokTotal,
          if (lokasiPenyimpanan != null && lokasiPenyimpanan.isNotEmpty)
            'lokasi_penyimpanan': lokasiPenyimpanan,
        },
      );

      return {
        'success': true,
        'message': response.data['message'] ?? 'Alat berhasil ditambahkan ke inventaris.',
        'alat': AlatModel.fromJson(response.data['data']),
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal menambahkan alat.',
      };
    }
  }

  Future<List<KategoriModel>> getKategoriList() async {
    try {
      final response = await _apiService.dio.get(ApiConstants.kategori);
      final List<dynamic> data = response.data['data'] ?? [];
      return data.map((k) => KategoriModel.fromJson(k)).toList();
    } catch (_) {
      return [];
    }
  }
}


import 'package:dio/dio.dart';
import '../config/api_constants.dart';
import '../models/dashboard_stats_model.dart';
import 'api_service.dart';

class DashboardService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> getStats() async {
    try {
      final response = await _apiService.dio.get(ApiConstants.dashboardStats);
      if (response.data['success'] == true) {
        return {
          'success': true,
          'data': DashboardStatsModel.fromJson(response.data['data']),
        };
      }
      return {
        'success': false,
        'message': 'Gagal memuat data statistik.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal memuat data dashboard.',
      };
    }
  }

  Future<Map<String, dynamic>> getLaporan() async {
    try {
      final response = await _apiService.dio.get(ApiConstants.laporan);
      if (response.data['success'] == true) {
        return {
          'success': true,
          'data': response.data['data'],
        };
      }
      return {
        'success': false,
        'message': 'Gagal memuat data laporan.',
      };
    } on DioException catch (e) {
      return {
        'success': false,
        'message': e.response?.data?['message'] ?? 'Gagal memuat data laporan.',
      };
    }
  }
}


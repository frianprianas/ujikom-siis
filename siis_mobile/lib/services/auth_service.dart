import 'package:dio/dio.dart';
import '../config/api_constants.dart';
import '../models/user_model.dart';
import 'api_service.dart';

class AuthService {
  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _apiService.dio.post(
        ApiConstants.login,
        data: {
          'email': email,
          'password': password,
          'device_name': 'SIIS Mobile App',
        },
      );

      final data = response.data['data'];
      final token = data['token'] as String;
      final user = UserModel.fromJson(data['user']);

      await _apiService.secureStorage.write(key: 'auth_token', value: token);

      return {
        'success': true,
        'token': token,
        'user': user,
      };
    } on DioException catch (e) {
      final message = e.response?.data?['message'] ?? 'Gagal melakukan login. Silakan periksa koneksi Anda.';
      return {
        'success': false,
        'message': message,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Terjadi kesalahan sistem: $e',
      };
    }
  }

  Future<UserModel?> getProfile() async {
    try {
      final response = await _apiService.dio.get(ApiConstants.me);
      if (response.statusCode == 200 && response.data['success'] == true) {
        return UserModel.fromJson(response.data['data']);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.dio.post(ApiConstants.logout);
    } catch (_) {
      // Ignore network errors on logout
    } finally {
      await _apiService.secureStorage.delete(key: 'auth_token');
    }
  }

  Future<bool> hasToken() async {
    final token = await _apiService.secureStorage.read(key: 'auth_token');
    return token != null && token.isNotEmpty;
  }
}


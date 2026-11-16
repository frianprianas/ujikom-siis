import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService _authService = AuthService();

  UserModel? _user;
  bool _isLoading = false;
  bool _isInitialized = false;
  String? _errorMessage;

  UserModel? get user => _user;
  bool get isLoading => _isLoading;
  bool get isInitialized => _isInitialized;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _user != null;

  String get role => _user?.role ?? '';
  bool get isPeminjam => _user?.isPeminjam ?? false;
  bool get canApprove => _user?.canApprove ?? false;
  bool get isKepalaSekolah => _user?.isKepalaSekolah ?? false;

  Future<void> initializeAuth() async {
    _isLoading = true;
    notifyListeners();

    final hasToken = await _authService.hasToken();
    if (hasToken) {
      _user = await _authService.getProfile();
    } else {
      _user = null;
    }

    _isInitialized = true;
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final result = await _authService.login(email, password);

    _isLoading = false;
    if (result['success'] == true) {
      _user = result['user'] as UserModel;
      notifyListeners();
      return true;
    } else {
      _errorMessage = result['message'] as String?;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    await _authService.logout();
    _user = null;

    _isLoading = false;
    notifyListeners();
  }
}


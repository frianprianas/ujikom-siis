import 'package:flutter/material.dart';
import '../models/alat_model.dart';
import '../models/kategori_model.dart';
import '../services/alat_service.dart';

class AlatProvider extends ChangeNotifier {
  final AlatService _alatService = AlatService();

  List<AlatModel> _alatList = [];
  List<KategoriModel> _kategoriList = [];
  bool _isLoading = false;
  String? _errorMessage;

  String _searchQuery = '';
  int? _selectedKategoriId;
  String? _selectedKondisi;

  List<AlatModel> get alatList => _alatList;
  List<KategoriModel> get kategoriList => _kategoriList;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get searchQuery => _searchQuery;
  int? get selectedKategoriId => _selectedKategoriId;
  String? get selectedKondisi => _selectedKondisi;

  Future<void> fetchAlat({bool showLoading = true}) async {
    if (showLoading) {
      _isLoading = true;
      notifyListeners();
    }

    final res = await _alatService.getAlatList(
      search: _searchQuery,
      kategoriId: _selectedKategoriId,
      kondisi: _selectedKondisi,
    );

    _isLoading = false;
    if (res['success'] == true) {
      _alatList = res['alat'] as List<AlatModel>;
      _kategoriList = res['kategori'] as List<KategoriModel>;
      _errorMessage = null;
    } else {
      _errorMessage = res['message'] as String?;
    }

    notifyListeners();
  }

  void setSearchQuery(String q) {
    _searchQuery = q;
    fetchAlat(showLoading: false);
  }

  void setKategori(int? kategoriId) {
    _selectedKategoriId = kategoriId;
    fetchAlat(showLoading: true);
  }

  void setKondisi(String? kondisi) {
    _selectedKondisi = kondisi;
    fetchAlat(showLoading: true);
  }

  void resetFilters() {
    _searchQuery = '';
    _selectedKategoriId = null;
    _selectedKondisi = null;
    fetchAlat(showLoading: true);
  }

  Future<Map<String, dynamic>> scanAlat(String kode) async {
    return await _alatService.scanAlat(kode);
  }

  Future<Map<String, dynamic>> createAlat({
    required int kategoriId,
    required String namaAlat,
    required String kodeAlat,
    required String kondisi,
    required int stokTotal,
    String? lokasiPenyimpanan,
  }) async {
    _isLoading = true;
    notifyListeners();

    final res = await _alatService.createAlat(
      kategoriId: kategoriId,
      namaAlat: namaAlat,
      kodeAlat: kodeAlat,
      kondisi: kondisi,
      stokTotal: stokTotal,
      lokasiPenyimpanan: lokasiPenyimpanan,
    );

    _isLoading = false;
    if (res['success'] == true) {
      await fetchAlat(showLoading: false);
    } else {
      _errorMessage = res['message'] as String?;
    }

    notifyListeners();
    return res;
  }
}


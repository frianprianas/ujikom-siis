import 'package:flutter/material.dart';
import '../models/alat_model.dart';
import '../models/peminjaman_model.dart';
import '../models/unit_alat_model.dart';
import '../services/peminjaman_service.dart';

class CartItem {
  final AlatModel alat;
  final UnitAlatModel? unitAlat;
  int jumlah;

  CartItem({required this.alat, this.unitAlat, this.jumlah = 1});
}

class PeminjamanProvider extends ChangeNotifier {
  final PeminjamanService _peminjamanService = PeminjamanService();

  List<PeminjamanModel> _peminjamanList = [];
  bool _isLoading = false;
  String? _errorMessage;
  String? _selectedStatus;

  // Keranjang / Dynamic item selector untuk pengajuan peminjaman
  final List<CartItem> _cart = [];

  List<PeminjamanModel> get peminjamanList => _peminjamanList;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get selectedStatus => _selectedStatus;
  List<CartItem> get cart => _cart;

  int get totalCartItems => _cart.fold(0, (sum, item) => sum + item.jumlah);
  bool get isCartEmpty => _cart.isEmpty;

  // Cart Management
  void addToCart(AlatModel alat, {UnitAlatModel? unitAlat, int jumlah = 1}) {
    if (unitAlat != null) {
      final exists = _cart.any((item) => item.unitAlat?.id == unitAlat.id);
      if (exists) return;
      _cart.add(CartItem(alat: alat, unitAlat: unitAlat, jumlah: 1));
    } else {
      final existingIndex = _cart.indexWhere((item) => item.alat.id == alat.id && item.unitAlat == null);
      if (existingIndex >= 0) {
        final newTotal = _cart[existingIndex].jumlah + jumlah;
        if (newTotal <= alat.stokTersedia) {
          _cart[existingIndex].jumlah = newTotal;
        }
      } else {
        if (jumlah <= alat.stokTersedia) {
          _cart.add(CartItem(alat: alat, jumlah: jumlah));
        }
      }
    }
    notifyListeners();
  }

  void updateQuantity(int alatId, int newQuantity, {int? unitAlatId}) {
    final index = _cart.indexWhere((item) => item.alat.id == alatId && item.unitAlat?.id == unitAlatId);
    if (index >= 0) {
      if (newQuantity <= 0) {
        _cart.removeAt(index);
      } else if (newQuantity <= _cart[index].alat.stokTersedia) {
        _cart[index].jumlah = newQuantity;
      }
      notifyListeners();
    }
  }

  void removeFromCart(int alatId, {int? unitAlatId}) {
    if (unitAlatId != null) {
      _cart.removeWhere((item) => item.unitAlat?.id == unitAlatId);
    } else {
      _cart.removeWhere((item) => item.alat.id == alatId);
    }
    notifyListeners();
  }

  void clearCart() {
    _cart.clear();
    notifyListeners();
  }

  // Fetch List
  Future<void> fetchPeminjaman({String? status, bool showLoading = true}) async {
    _selectedStatus = status;
    if (showLoading) {
      _isLoading = true;
      notifyListeners();
    }

    final res = await _peminjamanService.getPeminjamanList(status: status);

    _isLoading = false;
    if (res['success'] == true) {
      _peminjamanList = res['data'] as List<PeminjamanModel>;
      _errorMessage = null;
    } else {
      _errorMessage = res['message'] as String?;
    }

    notifyListeners();
  }

  // Submit Booking
  Future<Map<String, dynamic>> submitPeminjaman({
    required String tanggalPinjam,
    required String tanggalHarusKembali,
    String? keperluan,
  }) async {
    if (_cart.isEmpty) {
      return {
        'success': false,
        'message': 'Keranjang peminjaman masih kosong.',
      };
    }

    _isLoading = true;
    notifyListeners();

    final items = _cart
        .map((c) => {
              'alat_id': c.alat.id,
              'unit_alat_id': c.unitAlat?.id,
              'jumlah': c.jumlah,
            })
        .toList();

    final res = await _peminjamanService.storePeminjaman(
      tanggalPinjam: tanggalPinjam,
      tanggalHarusKembali: tanggalHarusKembali,
      keperluan: keperluan,
      items: items,
    );

    _isLoading = false;
    if (res['success'] == true) {
      clearCart();
      await fetchPeminjaman(showLoading: false);
    }

    notifyListeners();
    return res;
  }

  // Approvals (Staff & Wakasek Sarpras)
  Future<Map<String, dynamic>> approvePeminjaman(int id, {String? catatan}) async {
    final res = await _peminjamanService.approve(id, catatan: catatan);
    if (res['success'] == true) {
      await fetchPeminjaman(showLoading: false);
    }
    return res;
  }

  Future<Map<String, dynamic>> rejectPeminjaman(int id, {required String catatan}) async {
    final res = await _peminjamanService.reject(id, catatan: catatan);
    if (res['success'] == true) {
      await fetchPeminjaman(showLoading: false);
    }
    return res;
  }

  Future<Map<String, dynamic>> returnPeminjaman(int id, {Map<String, dynamic>? kondisiKembali}) async {
    final res = await _peminjamanService.markAsReturned(id, kondisiKembali: kondisiKembali);
    if (res['success'] == true) {
      await fetchPeminjaman(showLoading: false);
    }
    return res;
  }
}


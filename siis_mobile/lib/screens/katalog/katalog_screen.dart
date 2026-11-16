import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/alat_model.dart';
import '../../providers/alat_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../scanner/qr_scanner_screen.dart';

class KatalogScreen extends StatefulWidget {
  const KatalogScreen({super.key});

  @override
  State<KatalogScreen> createState() => _KatalogScreenState();
}

class _KatalogScreenState extends State<KatalogScreen> {
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AlatProvider>().fetchAlat();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _showAlatDetail(BuildContext context, AlatModel alat) {
    final auth = context.read<AuthProvider>();
    final peminjaman = context.read<PeminjamanProvider>();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            top: 24,
            left: 24,
            right: 24,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 28,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 48,
                  height: 4,
                  decoration: BoxDecoration(
                    color: const Color(0xFFA7F3D0),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          alat.namaAlat,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF064E3B),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Text(
                            'Kode: ${alat.kodeAlat}',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF047857),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: alat.isAvailable ? const Color(0xFFD1FAE5) : const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: alat.isAvailable ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA),
                      ),
                    ),
                    child: Text(
                      alat.isAvailable ? 'Tersedia' : 'Habis',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: alat.isAvailable ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(color: Color(0xFFE2E8F0)),
              const SizedBox(height: 8),

              // Detail List
              _buildDetailRow(Icons.category_outlined, 'Kategori', alat.kategori?.namaKategori ?? '-'),
              const SizedBox(height: 10),
              _buildDetailRow(Icons.health_and_safety_outlined, 'Kondisi Fisik', alat.kondisiLabel),
              const SizedBox(height: 10),
              _buildDetailRow(Icons.location_on_outlined, 'Lokasi Penyimpanan', alat.lokasiPenyimpanan ?? '-'),
              const SizedBox(height: 10),
              _buildDetailRow(
                Icons.inventory_2_outlined,
                'Stok Tersedia / Total',
                '${alat.stokTersedia} / ${alat.stokTotal} unit',
              ),

              if (alat.units.isNotEmpty) ...[
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Rincian Fisik (${alat.units.length} Unit)',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF064E3B),
                      ),
                    ),
                    Text(
                      '${alat.units.where((u) => u.kondisi == "baik").length} Baik • ${alat.units.where((u) => u.kondisi == "rusak_ringan").length} Rusak Ringan',
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF047857),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Container(
                  constraints: const BoxConstraints(maxHeight: 180),
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: alat.units.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 6),
                    itemBuilder: (ctx, idx) {
                      final u = alat.units[idx];
                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          children: [
                            Text('#${idx + 1}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF94A3B8))),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(u.kodeUnit, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, fontFamily: 'monospace', color: Color(0xFF0F172A))),
                                  if (u.catatan != null && u.catatan!.isNotEmpty)
                                    Text(u.catatan!, style: const TextStyle(fontSize: 10, fontStyle: FontStyle.italic, color: Color(0xFF64748B))),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: u.kondisi == 'baik' ? const Color(0xFFECFDF5) : const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: u.kondisi == 'baik' ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A)),
                              ),
                              child: Text(
                                u.kondisiLabel,
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: u.kondisi == 'baik' ? const Color(0xFF065F46) : const Color(0xFF92400E),
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ],

              const SizedBox(height: 20),

              if (auth.isPeminjam && alat.isAvailable)
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF059669), Color(0xFF10B981)],
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF059669).withOpacity(0.3),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ElevatedButton.icon(
                    onPressed: () {
                      peminjaman.addToCart(alat, jumlah: 1);
                      Navigator.pop(ctx);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('${alat.namaAlat} ditambahkan ke daftar pinjaman.'),
                          backgroundColor: const Color(0xFF059669),
                          behavior: SnackBarBehavior.floating,
                        ),
                      );
                    },
                    icon: const Icon(Icons.add_shopping_cart, color: Colors.white),
                    label: const Text('Masukkan ke Daftar Pinjam', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.transparent,
                      shadowColor: Colors.transparent,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                  ),
                ),

              if (auth.isKepalaSekolah)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFCBD5E1)),
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.visibility_outlined, size: 16, color: Color(0xFF64748B)),
                      SizedBox(width: 8),
                      Text(
                        'Mode Tinjauan Eksekutif (View Only)',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                      ),
                    ],
                  ),
                ),

              if (auth.canApprove)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.inventory_2, size: 16, color: Color(0xFF059669)),
                      const SizedBox(width: 8),
                      Text(
                        'Total: ${alat.stokTotal} unit | Tersedia: ${alat.stokTersedia} unit',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF064E3B)),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 18, color: const Color(0xFF059669)),
        const SizedBox(width: 10),
        Text(
          '$label: ',
          style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF064E3B)),
          ),
        ),
      ],
    );
  }

  void _showAddAlatModal(BuildContext context) {
    final alatProvider = context.read<AlatProvider>();
    final formKey = GlobalKey<FormState>();

    final namaController = TextEditingController();
    final kodeController = TextEditingController();
    final stokController = TextEditingController(text: '1');
    final lokasiController = TextEditingController();

    int? selectedKategoriId = alatProvider.kategoriList.isNotEmpty ? alatProvider.kategoriList.first.id : null;
    String selectedKondisi = 'baik';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                top: 24,
                left: 24,
                right: 24,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 28,
              ),
              child: SingleChildScrollView(
                child: Form(
                  key: formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Center(
                        child: Container(
                          width: 48,
                          height: 4,
                          decoration: BoxDecoration(
                            color: const Color(0xFFA7F3D0),
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Row(
                        children: [
                          Icon(Icons.add_box_outlined, color: Color(0xFF059669), size: 22),
                          SizedBox(width: 8),
                          Text(
                            'Input Barang Baru',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF064E3B),
                            ),
                          ),
                        ],
                      ),
                      const Text(
                        'Tambahkan inventaris alat sekolah ke database',
                        style: TextStyle(fontSize: 12, color: Color(0xFF047857)),
                      ),
                      const SizedBox(height: 18),

                      TextFormField(
                        controller: namaController,
                        decoration: InputDecoration(
                          labelText: 'Nama Alat / Barang *',
                          labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                          hintText: 'Misal: Proyektor Epson EB-X500',
                          filled: true,
                          fillColor: const Color(0xFFF0FDF4),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFF059669))),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama alat wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),

                      TextFormField(
                        controller: kodeController,
                        decoration: InputDecoration(
                          labelText: 'Kode Alat (Unik) *',
                          labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                          hintText: 'Misal: PRJ-EPS-005',
                          filled: true,
                          fillColor: const Color(0xFFF0FDF4),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFF059669))),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Kode alat wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),

                      DropdownButtonFormField<int>(
                        value: selectedKategoriId,
                        decoration: InputDecoration(
                          labelText: 'Kategori Alat *',
                          labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                          filled: true,
                          fillColor: const Color(0xFFF0FDF4),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                        ),
                        items: alatProvider.kategoriList.map((k) {
                          return DropdownMenuItem<int>(
                            value: k.id,
                            child: Text(k.namaKategori, style: const TextStyle(fontSize: 13)),
                          );
                        }).toList(),
                        onChanged: (val) => setModalState(() => selectedKategoriId = val),
                        validator: (v) => v == null ? 'Pilih kategori alat' : null,
                      ),
                      const SizedBox(height: 12),

                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              value: selectedKondisi,
                              decoration: InputDecoration(
                                labelText: 'Kondisi *',
                                labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                                filled: true,
                                fillColor: const Color(0xFFF0FDF4),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              ),
                              items: const [
                                DropdownMenuItem(value: 'baik', child: Text('Baik', style: TextStyle(fontSize: 13))),
                                DropdownMenuItem(value: 'rusak_ringan', child: Text('Rusak Ringan', style: TextStyle(fontSize: 13))),
                                DropdownMenuItem(value: 'rusak_berat', child: Text('Rusak Berat', style: TextStyle(fontSize: 13))),
                              ],
                              onChanged: (val) => setModalState(() => selectedKondisi = val ?? 'baik'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: TextFormField(
                              controller: stokController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                labelText: 'Total Unit *',
                                labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                                filled: true,
                                fillColor: const Color(0xFFF0FDF4),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                              ),
                              validator: (v) {
                                if (v == null || v.trim().isEmpty) return 'Wajib diisi';
                                final n = int.tryParse(v);
                                if (n == null || n < 1) return 'Min. 1 unit';
                                return null;
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      TextFormField(
                        controller: lokasiController,
                        decoration: InputDecoration(
                          labelText: 'Lokasi Penyimpanan',
                          labelStyle: const TextStyle(color: Color(0xFF065F46), fontSize: 13),
                          hintText: 'Misal: Ruang Sarpras Rak C',
                          filled: true,
                          fillColor: const Color(0xFFF0FDF4),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFA7F3D0))),
                        ),
                      ),
                      const SizedBox(height: 20),

                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF059669), Color(0xFF0D9488)],
                          ),
                          borderRadius: BorderRadius.circular(14),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF059669).withOpacity(0.3),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: ElevatedButton.icon(
                          onPressed: () async {
                            if (!formKey.currentState!.validate()) return;
                            if (selectedKategoriId == null) return;

                            final res = await alatProvider.createAlat(
                              kategoriId: selectedKategoriId!,
                              namaAlat: namaController.text.trim(),
                              kodeAlat: kodeController.text.trim(),
                              kondisi: selectedKondisi,
                              stokTotal: int.parse(stokController.text.trim()),
                              lokasiPenyimpanan: lokasiController.text.trim(),
                            );

                            if (!ctx.mounted) return;
                            Navigator.pop(ctx);

                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res['message'] ?? 'Alat berhasil ditambahkan.'),
                                backgroundColor: res['success'] == true ? const Color(0xFF059669) : Colors.red,
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          },
                          icon: const Icon(Icons.save, color: Colors.white),
                          label: const Text('Simpan Inventaris Baru', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final alatProvider = context.watch<AlatProvider>();
    final authProvider = context.watch<AuthProvider>();
    final peminjamanProvider = context.watch<PeminjamanProvider>();

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: Text(authProvider.isKepalaSekolah
            ? 'Katalog (Tinjauan Eksekutif)'
            : 'Katalog Inventaris'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF064E3B),
        elevation: 0,
        actions: [
          if (authProvider.canApprove)
            IconButton(
              icon: const Icon(Icons.add_circle, color: Color(0xFF059669)),
              tooltip: 'Input Barang Baru',
              onPressed: () => _showAddAlatModal(context),
            ),
          IconButton(
            icon: const Icon(Icons.qr_code_scanner, color: Color(0xFF059669)),
            tooltip: 'Pindai QR Alat',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const QrScannerScreen()),
              );
            },
          ),
        ],
      ),
      floatingActionButton: authProvider.canApprove
          ? FloatingActionButton.extended(
              onPressed: () => _showAddAlatModal(context),
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add),
              label: const Text('Input Barang', style: TextStyle(fontWeight: FontWeight.bold)),
            )
          : null,
      body: Column(
        children: [
          // Search & Filter Header
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Column(
              children: [
                // Search Field
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Cari alat, kode, atau lokasi...',
                    hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                    prefixIcon: const Icon(Icons.search, size: 20, color: Color(0xFF059669)),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18, color: Color(0xFF059669)),
                            onPressed: () {
                              _searchController.clear();
                              alatProvider.setSearchQuery('');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF0FDF4),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: Color(0xFFA7F3D0)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: Color(0xFF059669), width: 1.5),
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  onChanged: (val) {
                    alatProvider.setSearchQuery(val);
                  },
                ),
                const SizedBox(height: 10),

                // Category Chips
                if (alatProvider.kategoriList.isNotEmpty)
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        ChoiceChip(
                          label: const Text('Semua Kategori'),
                          selected: alatProvider.selectedKategoriId == null,
                          selectedColor: const Color(0xFF059669),
                          labelStyle: TextStyle(
                            color: alatProvider.selectedKategoriId == null ? Colors.white : const Color(0xFF064E3B),
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                          backgroundColor: const Color(0xFFECFDF5),
                          side: const BorderSide(color: Color(0xFFA7F3D0)),
                          onSelected: (_) => alatProvider.setKategori(null),
                        ),
                        const SizedBox(width: 8),
                        ...alatProvider.kategoriList.map((k) {
                          final isSelected = alatProvider.selectedKategoriId == k.id;
                          return Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: ChoiceChip(
                              label: Text(k.namaKategori),
                              selected: isSelected,
                              selectedColor: const Color(0xFF059669),
                              labelStyle: TextStyle(
                                color: isSelected ? Colors.white : const Color(0xFF064E3B),
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                              backgroundColor: const Color(0xFFECFDF5),
                              side: const BorderSide(color: Color(0xFFA7F3D0)),
                              onSelected: (_) => alatProvider.setKategori(k.id),
                            ),
                          );
                        }),
                      ],
                    ),
                  ),
              ],
            ),
          ),

          // Main List
          Expanded(
            child: alatProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
                : alatProvider.alatList.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.inventory_2_outlined, size: 56, color: Color(0xFFA7F3D0)),
                            const SizedBox(height: 12),
                            const Text(
                              'Tidak ada alat yang ditemukan.',
                              style: TextStyle(color: Color(0xFF065F46), fontSize: 15, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        color: const Color(0xFF059669),
                        onRefresh: () => alatProvider.fetchAlat(showLoading: false),
                        child: ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: alatProvider.alatList.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (context, index) {
                            final alat = alatProvider.alatList[index];
                            final inCart = peminjamanProvider.cart.any((item) => item.alat.id == alat.id);

                            return Card(
                              elevation: 1,
                              shadowColor: const Color(0xFF064E3B).withOpacity(0.04),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(20),
                                side: const BorderSide(color: Color(0xFFA7F3D0)),
                              ),
                              color: Colors.white,
                              child: InkWell(
                                borderRadius: BorderRadius.circular(20),
                                onTap: () => _showAlatDetail(context, alat),
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Icon container with light green background
                                      Container(
                                        width: 52,
                                        height: 52,
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFECFDF5),
                                          borderRadius: BorderRadius.circular(14),
                                          border: Border.all(color: const Color(0xFFA7F3D0)),
                                        ),
                                        child: const Icon(
                                          Icons.precision_manufacturing_rounded,
                                          color: Color(0xFF059669),
                                          size: 26,
                                        ),
                                      ),
                                      const SizedBox(width: 14),

                                      // Information
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              alat.namaAlat,
                                              style: const TextStyle(
                                                fontSize: 16,
                                                fontWeight: FontWeight.w800,
                                                color: Color(0xFF064E3B),
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            Row(
                                              children: [
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: const Color(0xFFECFDF5),
                                                    borderRadius: BorderRadius.circular(6),
                                                    border: Border.all(color: const Color(0xFFA7F3D0)),
                                                  ),
                                                  child: Text(
                                                    alat.kodeAlat,
                                                    style: const TextStyle(
                                                      fontSize: 11,
                                                      fontWeight: FontWeight.bold,
                                                      color: Color(0xFF047857),
                                                    ),
                                                  ),
                                                ),
                                                const SizedBox(width: 6),
                                                Text(
                                                  alat.kategori?.namaKategori ?? '',
                                                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 8),
                                            Row(
                                              children: [
                                                const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFF059669)),
                                                const SizedBox(width: 4),
                                                Expanded(
                                                  child: Text(
                                                    alat.lokasiPenyimpanan ?? '-',
                                                    style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                              ],
                                            ),
                                            if (alat.units.isNotEmpty) ...[
                                              const SizedBox(height: 6),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: const Color(0xFFF0FDF4),
                                                  borderRadius: BorderRadius.circular(6),
                                                  border: Border.all(color: const Color(0xFFA7F3D0)),
                                                ),
                                                child: Text(
                                                  '${alat.units.length} Unit Fisik (${alat.units.where((u) => u.kondisi == "baik").length} Baik)',
                                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF065F46)),
                                                ),
                                              ),
                                            ],
                                          ],
                                        ),
                                      ),

                                      // Stock & Action
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.end,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: alat.isAvailable
                                                  ? const Color(0xFFD1FAE5)
                                                  : const Color(0xFFFEE2E2),
                                              borderRadius: BorderRadius.circular(12),
                                              border: Border.all(
                                                color: alat.isAvailable
                                                    ? const Color(0xFFA7F3D0)
                                                    : const Color(0xFFFECACA),
                                              ),
                                            ),
                                            child: Text(
                                              '${alat.stokTersedia} unit',
                                              style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                                color: alat.isAvailable
                                                    ? const Color(0xFF065F46)
                                                    : const Color(0xFFB91C1C),
                                              ),
                                            ),
                                          ),
                                          const SizedBox(height: 12),
                                          if (authProvider.isPeminjam && alat.isAvailable)
                                            IconButton(
                                              icon: Icon(
                                                inCart ? Icons.check_circle : Icons.add_circle_outline,
                                                color: inCart ? const Color(0xFF059669) : const Color(0xFF10B981),
                                              ),
                                              tooltip: inCart ? 'Sudah di Keranjang' : 'Tambah ke Pinjaman',
                                              onPressed: () {
                                                peminjamanProvider.addToCart(alat, jumlah: 1);
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  SnackBar(
                                                    content: Text('${alat.namaAlat} ditambahkan ke pinjaman.'),
                                                    duration: const Duration(seconds: 1),
                                                    backgroundColor: const Color(0xFF059669),
                                                    behavior: SnackBarBehavior.floating,
                                                  ),
                                                );
                                              },
                                            ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

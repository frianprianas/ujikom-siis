import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../approval/approval_list_screen.dart';
import '../dashboard/kepsek_dashboard_screen.dart';
import '../katalog/katalog_screen.dart';
import '../laporan/laporan_screen.dart';
import '../peminjaman/peminjaman_create_screen.dart';
import '../peminjaman/peminjaman_history_screen.dart';
import '../profile/profile_screen.dart';
import '../scanner/qr_scanner_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final peminjamanProvider = context.watch<PeminjamanProvider>();
    final user = authProvider.user;

    // Define tabs and screens based on user role
    List<Widget> screens = [];
    List<BottomNavigationBarItem> navItems = [];

    if (user != null && user.isPeminjam) {
      // Role: Siswa & Guru
      screens = const [
        KatalogScreen(),
        PeminjamanCreateScreen(),
        PeminjamanHistoryScreen(),
        ProfileScreen(),
      ];

      navItems = [
        const BottomNavigationBarItem(
          icon: Icon(Icons.inventory_2_outlined),
          activeIcon: Icon(Icons.inventory_2),
          label: 'Katalog',
        ),
        BottomNavigationBarItem(
          icon: Badge(
            isLabelVisible: peminjamanProvider.cart.isNotEmpty,
            label: Text('${peminjamanProvider.cart.length}'),
            backgroundColor: const Color(0xFF059669),
            child: const Icon(Icons.add_shopping_cart_outlined),
          ),
          activeIcon: Badge(
            isLabelVisible: peminjamanProvider.cart.isNotEmpty,
            label: Text('${peminjamanProvider.cart.length}'),
            backgroundColor: const Color(0xFF059669),
            child: const Icon(Icons.add_shopping_cart),
          ),
          label: 'Ajukan Pinjam',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.history_outlined),
          activeIcon: Icon(Icons.history),
          label: 'Riwayat',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person),
          label: 'Profil',
        ),
      ];
    } else if (user != null && user.canApprove) {
      // Role: Staff Sarpras & Wakasek Sarpras
      screens = const [
        ApprovalListScreen(),
        KatalogScreen(),
        PeminjamanHistoryScreen(),
        LaporanScreen(),
        ProfileScreen(),
      ];

      navItems = const [
        BottomNavigationBarItem(
          icon: Icon(Icons.approval_outlined),
          activeIcon: Icon(Icons.approval),
          label: 'ACC Pinjam',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.inventory_2_outlined),
          activeIcon: Icon(Icons.inventory_2),
          label: 'Barang & Input',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.assignment_turned_in_outlined),
          activeIcon: Icon(Icons.assignment_turned_in),
          label: 'Pengembalian',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.assessment_outlined),
          activeIcon: Icon(Icons.assessment),
          label: 'Laporan',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person),
          label: 'Profil',
        ),
      ];
    } else {
      // Role: Kepala Sekolah (View Only & Laporan)
      screens = const [
        KepsekDashboardScreen(),
        KatalogScreen(),
        LaporanScreen(),
        ProfileScreen(),
      ];

      navItems = const [
        BottomNavigationBarItem(
          icon: Icon(Icons.dashboard_outlined),
          activeIcon: Icon(Icons.dashboard),
          label: 'Dashboard',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.inventory_2_outlined),
          activeIcon: Icon(Icons.inventory_2),
          label: 'Katalog',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.assessment_outlined),
          activeIcon: Icon(Icons.assessment),
          label: 'Laporan',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person),
          label: 'Profil',
        ),
      ];
    }

    if (_currentIndex >= screens.length) {
      _currentIndex = 0;
    }

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      floatingActionButton: Container(
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: const LinearGradient(
            colors: [Color(0xFF059669), Color(0xFF10B981)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF059669).withOpacity(0.35),
              blurRadius: 12,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: FloatingActionButton(
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const QrScannerScreen()),
            );
          },
          backgroundColor: Colors.transparent,
          elevation: 0,
          foregroundColor: Colors.white,
          tooltip: 'Pindai QR / Barcode Alat',
          child: const Icon(Icons.qr_code_scanner, size: 26),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: 6,
        elevation: 8,
        color: Colors.white,
        padding: EdgeInsets.zero,
        child: SafeArea(
          child: SizedBox(
            height: 64,
            child: Row(
              children: [
                // Left tabs
                ...List.generate(
                  (navItems.length / 2).ceil(),
                  (i) => Expanded(child: _navButton(i, navItems[i])),
                ),

                // Notch space for center FAB
                const SizedBox(width: 48),

                // Right tabs
                ...List.generate(
                  navItems.length - (navItems.length / 2).ceil(),
                  (i) {
                    final realIndex = (navItems.length / 2).ceil() + i;
                    return Expanded(child: _navButton(realIndex, navItems[realIndex]));
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _navButton(int index, BottomNavigationBarItem item) {
    final isSelected = _currentIndex == index;
    final color = isSelected ? const Color(0xFF059669) : const Color(0xFF64748B);

    return InkWell(
      onTap: () => setState(() => _currentIndex = index),
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 2, vertical: 4),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: MainAxisSize.min,
          children: [
            isSelected ? (item.activeIcon) : (item.icon),
            const SizedBox(height: 2),
            Text(
              item.label ?? '',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

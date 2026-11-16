import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController(text: 'password');
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _fillDemo(String email) {
    setState(() {
      _emailController.text = email;
      _passwordController.text = 'password';
    });
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final emailInput = _emailController.text.trim().toLowerCase();
    if (emailInput == 'admin@siis.sch.id') {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.shield_outlined, color: Color(0xFFD97706)),
              SizedBox(width: 8),
              Text('Akses Khusus Web', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF064E3B))),
            ],
          ),
          content: const Text(
            'Akun Super Admin & Pengaturan Data Master hanya dapat diakses melalui Aplikasi Web SIIS (http://localhost:8000).\n\nUntuk aplikasi mobile, silakan masuk menggunakan akun Staff, Wakasek, Guru, Siswa, atau Kepala Sekolah.',
            style: TextStyle(fontSize: 13, color: Color(0xFF334155)),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Mengerti', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final success = await authProvider.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (!mounted) return;

    if (!success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Gagal masuk. Periksa kembali email dan password.'),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFD1FAE5), Color(0xFFF0FDF4), Colors.white],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Official Trademark Logo
                    ClipRRect(
                      borderRadius: BorderRadius.circular(24),
                      child: Image.asset(
                        'assets/images/logo.png',
                        width: 88,
                        height: 88,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => Container(
                          width: 88,
                          height: 88,
                          decoration: BoxDecoration(
                            color: const Color(0xFF059669),
                            borderRadius: BorderRadius.circular(24),
                          ),
                          child: const Icon(Icons.inventory_2_rounded, color: Colors.white, size: 44),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'SIIS',
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF064E3B),
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Inventaris Sekolah Baknus',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF059669),
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Sistem Informasi Inventaris & Peminjaman Sarpras',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF64748B),
                      ),
                    ),
                        color: Color(0xFF047857),
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Login Card
                    Card(
                      elevation: 4,
                      shadowColor: const Color(0xFF064E3B).withOpacity(0.08),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                        side: const BorderSide(color: Color(0xFFA7F3D0)),
                      ),
                      color: Colors.white.withOpacity(0.95),
                      child: Padding(
                        padding: const EdgeInsets.all(26),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const Text(
                                'Masuk ke Akun Anda',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF064E3B),
                                ),
                              ),
                              const SizedBox(height: 20),

                              // Email
                              TextFormField(
                                controller: _emailController,
                                keyboardType: TextInputType.emailAddress,
                                decoration: InputDecoration(
                                  labelText: 'Alamat Email',
                                  labelStyle: const TextStyle(color: Color(0xFF065F46)),
                                  hintText: 'nama@siis.sch.id',
                                  prefixIcon: const Icon(Icons.email_outlined, size: 20, color: Color(0xFF059669)),
                                  filled: true,
                                  fillColor: const Color(0xFFF0FDF4),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: const BorderSide(color: Color(0xFFA7F3D0)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: const BorderSide(color: Color(0xFF059669), width: 2),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                ),
                                validator: (val) {
                                  if (val == null || val.trim().isEmpty) {
                                    return 'Email tidak boleh kosong';
                                  }
                                  if (!val.contains('@')) {
                                    return 'Format email tidak valid';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 16),

                              // Password
                              TextFormField(
                                controller: _passwordController,
                                obscureText: _obscurePassword,
                                decoration: InputDecoration(
                                  labelText: 'Kata Sandi',
                                  labelStyle: const TextStyle(color: Color(0xFF065F46)),
                                  prefixIcon: const Icon(Icons.lock_outline, size: 20, color: Color(0xFF059669)),
                                  suffixIcon: IconButton(
                                    icon: Icon(
                                      _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                      size: 20,
                                      color: const Color(0xFF059669),
                                    ),
                                    onPressed: () {
                                      setState(() {
                                        _obscurePassword = !_obscurePassword;
                                      });
                                    },
                                  ),
                                  filled: true,
                                  fillColor: const Color(0xFFF0FDF4),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: const BorderSide(color: Color(0xFFA7F3D0)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                    borderSide: const BorderSide(color: Color(0xFF059669), width: 2),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                ),
                                validator: (val) {
                                  if (val == null || val.isEmpty) {
                                    return 'Kata sandi tidak boleh kosong';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 24),

                              // Submit Button with Gradient
                              Container(
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFF059669), Color(0xFF10B981)],
                                    begin: Alignment.centerLeft,
                                    end: Alignment.centerRight,
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
                                child: ElevatedButton(
                                  onPressed: authProvider.isLoading ? null : _handleLogin,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 14),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                  ),
                                  child: authProvider.isLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2.5,
                                          ),
                                        )
                                      : const Text(
                                          'Masuk Sekarang',
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w700,
                                          ),
                                        ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Demo Quick Switcher Chips
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF064E3B).withOpacity(0.04),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.touch_app_outlined, size: 16, color: Color(0xFF059669)),
                              SizedBox(width: 6),
                              Text(
                                'Pilih Akun Demo (Uji Coba Cepat):',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF064E3B),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: [
                              ActionChip(
                                avatar: const Icon(Icons.school, size: 16, color: Color(0xFF059669)),
                                label: const Text('Siswa (Peminjam)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF064E3B))),
                                backgroundColor: const Color(0xFFECFDF5),
                                side: const BorderSide(color: Color(0xFFA7F3D0)),
                                onPressed: () => _fillDemo('siswa@siis.sch.id'),
                              ),
                              ActionChip(
                                avatar: const Icon(Icons.co_present, size: 16, color: Color(0xFF0D9488)),
                                label: const Text('Guru (Peminjam)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF134E4A))),
                                backgroundColor: const Color(0xFFF0FDFA),
                                side: const BorderSide(color: Color(0xFF99F6E4)),
                                onPressed: () => _fillDemo('guru@siis.sch.id'),
                              ),
                              ActionChip(
                                avatar: const Icon(Icons.build, size: 16, color: Color(0xFFD97706)),
                                label: const Text('Staff Sarpras (ACC & Input)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF78350F))),
                                backgroundColor: const Color(0xFFFFFBEB),
                                side: const BorderSide(color: Color(0xFFFDE68A)),
                                onPressed: () => _fillDemo('sarpras@siis.sch.id'),
                              ),
                              ActionChip(
                                avatar: const Icon(Icons.verified_user, size: 16, color: Color(0xFF0891B2)),
                                label: const Text('Wakasek (ACC & Input)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF164E63))),
                                backgroundColor: const Color(0xFFECFEFF),
                                side: const BorderSide(color: Color(0xFFA5F3FC)),
                                onPressed: () => _fillDemo('wakasek@siis.sch.id'),
                              ),
                              ActionChip(
                                avatar: const Icon(Icons.analytics, size: 16, color: Color(0xFF7C3AED)),
                                label: const Text('Kepala Sekolah (View Only)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF4C1D95))),
                                backgroundColor: const Color(0xFFF5F3FF),
                                side: const BorderSide(color: Color(0xFFDDD6FE)),
                                onPressed: () => _fillDemo('kepsek@siis.sch.id'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

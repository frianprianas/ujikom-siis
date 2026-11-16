<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIIS (Sistem Informasi Inventaris Sekolah)</title>
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-100 via-teal-50 to-white min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white/90 backdrop-blur-md rounded-3xl shadow-xl shadow-emerald-950/5 p-8 border border-emerald-100" 
         x-data="{
            fillDemo(email) {
                document.getElementById('email').value = email;
                document.getElementById('password').value = 'password';
            }
         }">
        
        <!-- Header & Trademark Logo -->
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIIS" class="w-24 h-24 mx-auto rounded-3xl object-contain mb-3 shadow-md border border-emerald-100 bg-white p-1">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">SIIS Portal</h1>
            <p class="text-xs font-bold text-emerald-700 tracking-wider uppercase mt-0.5">Inventaris Sekolah Baknus</p>
            <p class="text-xs text-slate-500 mt-1">Sistem Informasi Inventaris & Peminjaman Sarpras</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('info'))
            <div class="mb-5 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                {{ session('info') }}
            </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Pengguna</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-emerald-600">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white/70 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition"
                           placeholder="nama@siis.sch.id">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-emerald-600">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white/70 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Ingat Saya</span>
                </label>
                <span class="text-slate-400 text-[11px]">Default Password: <code class="font-mono text-emerald-800 font-semibold">password</code></span>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center space-x-2 text-sm mt-3">
                <span>Masuk ke Sistem</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <!-- Quick Switch Demo Account -->
        <div class="mt-8 pt-6 border-t border-emerald-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center mb-3">Pilih Akun Demo (Klik untuk Isi Cepat):</p>
            <div class="mb-2 text-xs">
                <button type="button" @click="fillDemo('admin@siis.sch.id')" class="w-full p-2.5 rounded-xl bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-950 border border-emerald-300 hover:from-emerald-200 hover:to-teal-200 text-center font-bold transition flex items-center justify-center space-x-2 shadow-sm">
                    <span>👑</span> <span>Super Admin (Master Data, RFID, IoT & Full Access)</span>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" @click="fillDemo('sarpras@siis.sch.id')" class="p-2.5 rounded-xl bg-emerald-100/60 text-emerald-950 border border-emerald-300 hover:bg-emerald-200/70 text-left font-semibold transition flex items-center space-x-2">
                    <span>🛠️</span> <span>Staff Sarpras (ACC & Alat)</span>
                </button>
                <button type="button" @click="fillDemo('wakasek@siis.sch.id')" class="p-2.5 rounded-xl bg-teal-100/60 text-teal-950 border border-teal-300 hover:bg-teal-200/70 text-left font-semibold transition flex items-center space-x-2">
                    <span>📋</span> <span>Wakasek (ACC & Alat)</span>
                </button>
                <button type="button" @click="fillDemo('siswa@siis.sch.id')" class="p-2.5 rounded-xl bg-emerald-50/80 text-emerald-900 border border-emerald-200 hover:bg-emerald-100 text-left font-semibold transition flex items-center space-x-2">
                    <span>👨‍🎓</span> <span>Siswa (Peminjam)</span>
                </button>
                <button type="button" @click="fillDemo('guru@siis.sch.id')" class="p-2.5 rounded-xl bg-teal-50/80 text-teal-900 border border-teal-200 hover:bg-teal-100 text-left font-semibold transition flex items-center space-x-2">
                    <span>👨‍🏫</span> <span>Guru (Peminjam)</span>
                </button>
            </div>
            <div class="mt-2 text-xs">
                <button type="button" @click="fillDemo('kepsek@siis.sch.id')" class="w-full p-2.5 rounded-xl bg-slate-100/80 text-slate-800 border border-slate-200 hover:bg-slate-200 text-center font-semibold transition flex items-center justify-center space-x-2">
                    <span>🏛️</span> <span>Kepala Sekolah (Executive View-Only)</span>
                </button>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

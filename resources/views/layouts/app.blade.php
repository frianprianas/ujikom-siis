<!DOCTYPE html>
<html lang="id" class="h-full bg-emerald-50/30">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIIS') - Sistem Informasi Inventaris Sekolah</title>
    
    <!-- Favicon & Trademark App Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom subtle scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f0fdf4; }
        ::-webkit-scrollbar-thumb { background: #a7f3d0; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #34d399; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased font-sans bg-gradient-to-br from-emerald-50/70 via-teal-50/30 to-white min-h-screen" x-data="{ sidebarOpen: false }">

    <div class="min-h-full flex">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-emerald-950/60 z-40 lg:hidden backdrop-blur-sm"></div>

        <!-- Left Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-emerald-950 via-slate-900 to-emerald-950 text-emerald-100 flex flex-col transition-transform duration-300 ease-in-out border-r border-emerald-900/60 shadow-xl lg:shadow-none">
            
            <!-- Sidebar Header / Brand -->
            <div class="h-16 flex items-center justify-between px-6 bg-emerald-950/80 border-b border-emerald-900/80">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SIIS" class="w-10 h-10 rounded-xl object-contain bg-white p-0.5 shadow-sm group-hover:scale-105 transition-transform shrink-0">
                    <div>
                        <span class="text-lg font-black text-white tracking-tight leading-tight block">SIIS</span>
                        <span class="text-[11px] font-semibold text-emerald-300 block tracking-wide">Inventaris Sekolah Baknus</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-emerald-300 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
                @auth
                    <!-- Section: Menu Utama -->
                    <div>
                        <span class="px-3 text-[10px] font-bold text-emerald-400/90 uppercase tracking-wider block mb-2">Menu Utama</span>
                        <div class="space-y-1">
                            <a href="{{ route('alat.index') }}"
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('alat.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm shadow-emerald-500/30' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
                                <span>Katalog & Inventaris</span>
                            </a>

                            <a href="{{ route('peminjaman.index') }}"
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('peminjaman.index') || request()->routeIs('peminjaman.show') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm shadow-emerald-500/30' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i>
                                <span>{{ in_array(auth()->user()->role, ['siswa', 'guru']) ? 'Peminjaman Saya' : 'Daftar Peminjaman' }}</span>
                            </a>

                            @if(in_array(auth()->user()->role, ['siswa', 'guru']))
                                <a href="{{ route('peminjaman.create') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('peminjaman.create') ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-sm shadow-emerald-400/30' : 'hover:bg-emerald-900/50 hover:text-emerald-300 text-emerald-200' }}">
                                    <i data-lucide="plus-circle" class="w-4 h-4 shrink-0"></i>
                                    <span>+ Ajukan Pinjam Alat</span>
                                </a>
                            @endif

                            <a href="/mobile-simulator.html" target="_blank"
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition hover:bg-emerald-900/60 hover:text-white text-emerald-200 bg-emerald-900/30 border border-emerald-800/60 mt-1">
                                <i data-lucide="smartphone" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Simulator Mobile (Flat UI)</span>
                                <i data-lucide="external-link" class="w-3 h-3 text-emerald-400/70 ml-auto"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Section: Pengelolaan Sarpras (Staff, Wakasek, Super Admin) -->
                    @if(auth()->user()->canApprove())
                        <div>
                            <span class="px-3 text-[10px] font-bold text-emerald-400/90 uppercase tracking-wider block mb-2">Modul Sarpras</span>
                            <div class="space-y-1">
                                <a href="{{ route('sarpras.peminjaman.direct.create') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('sarpras.peminjaman.direct.*') ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-200 bg-emerald-900/30 border border-emerald-800/70' }}">
                                    <i data-lucide="handshake" class="w-4 h-4 shrink-0 text-emerald-300"></i>
                                    <span>+ Pinjam Langsung (Tatap Muka)</span>
                                </a>

                                <a href="{{ route('sarpras.dashboard') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('sarpras.dashboard') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <div class="flex items-center space-x-3">
                                        <i data-lucide="check-check" class="w-4 h-4 shrink-0"></i>
                                        <span>Approval & Verifikasi</span>
                                    </div>
                                </a>

                                <a href="{{ route('kategori.index') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('kategori.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <i data-lucide="folder-tree" class="w-4 h-4 shrink-0"></i>
                                    <span>Kategori Inventaris</span>
                                </a>

                                <a href="{{ route('sarpras.laporan.cetak') }}" target="_blank"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition hover:bg-emerald-900/50 hover:text-white text-emerald-100/80">
                                    <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>
                                    <span>Cetak Laporan</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Section: Data Master & IoT (Super Admin Only) -->
                    @if(auth()->user()->isSuperAdmin())
                        <div>
                            <span class="px-3 text-[10px] font-bold text-teal-300 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
                                <i data-lucide="shield" class="w-3 h-3 text-teal-400"></i>
                                Data Master & IoT
                            </span>
                            <div class="space-y-1">
                                <a href="{{ route('sarpras.users.index') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('sarpras.users.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                                    <span>Akun & Kartu RFID</span>
                                </a>

                                <a href="{{ route('sarpras.iot.devices') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('sarpras.iot.devices') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <i data-lucide="cpu" class="w-4 h-4 shrink-0"></i>
                                    <span>Perangkat IoT ESP32</span>
                                </a>

                                <a href="{{ route('sarpras.iot.logs') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('sarpras.iot.logs') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <i data-lucide="radio" class="w-4 h-4 shrink-0"></i>
                                    <span>Audit Log RFID</span>
                                </a>

                                <a href="{{ route('sarpras.iot.simulator') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('sarpras.iot.simulator') ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-teal-300 text-emerald-100/80' }}">
                                    <i data-lucide="play-circle" class="w-4 h-4 shrink-0"></i>
                                    <span>Simulator Tap RFID</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Section: Eksekutif (Kepala Sekolah & Super Admin) -->
                    @if(auth()->user()->isKepalaSekolah() || auth()->user()->isSuperAdmin())
                        <div>
                            <span class="px-3 text-[10px] font-bold text-emerald-400/90 uppercase tracking-wider block mb-2">Pimpinan Sekolah</span>
                            <div class="space-y-1">
                                <a href="{{ route('kepsek.dashboard') }}"
                                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('kepsek.dashboard') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'hover:bg-emerald-900/50 hover:text-white text-emerald-100/80' }}">
                                    <i data-lucide="pie-chart" class="w-4 h-4 shrink-0"></i>
                                    <span>Executive Monitoring</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @endauth
            </nav>

            <!-- Sidebar Footer (User Info & Logout) -->
            @auth
                <div class="p-4 border-t border-emerald-900/80 bg-emerald-950/60">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-emerald-900 border border-emerald-700/80 flex items-center justify-center font-bold text-white shrink-0">
                                {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-semibold text-white block truncate">{{ auth()->user()->nama }}</span>
                                <span class="text-[10px] font-medium text-emerald-300 block truncate">
                                    {{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}
                                </span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="p-2 text-emerald-300 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" title="Logout">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

        </aside>

        <!-- Main Wrapper Container (Beside Sidebar) -->
        <div class="flex-1 flex flex-col lg:pl-72 min-w-0">
            
            <!-- Top Sticky Header -->
            <header class="h-16 bg-white/90 backdrop-blur-md border-b border-emerald-100 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-sm shadow-emerald-950/5">
                <!-- Left Title & Mobile Toggle -->
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = true" class="lg:hidden text-emerald-800 hover:text-emerald-950 p-2 rounded-lg hover:bg-emerald-50 focus:outline-none">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-emerald-950 leading-tight">@yield('title', 'Sistem Informasi Inventaris')</h2>
                        <span class="text-xs text-emerald-700/70 hidden sm:inline">SIIS &bull; Inventaris Sekolah Baknus</span>
                    </div>
                </div>

                <!-- Right Quick Info & Badges -->
                <div class="flex items-center space-x-3">
                    @auth
                        <!-- Role Badge -->
                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-emerald-500"></span>
                            {{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}
                        </span>

                        <!-- RFID Card Indicator if assigned -->
                        @if(auth()->user()->rfid_uid)
                            <div class="hidden md:flex items-center space-x-1.5 text-[11px] font-mono text-emerald-800 bg-emerald-50/80 px-2.5 py-1 rounded-lg border border-emerald-200">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>RFID: {{ auth()->user()->rfid_uid }}</span>
                            </div>
                        @endif
                    @endauth
                </div>
            </header>

            <!-- Main Page Content Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <!-- Toast Alerts -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm" x-data="{ show: true }" x-show="show">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm" x-data="{ show: true }" x-show="show">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 mt-0.5 shrink-0"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                        <div class="flex items-center space-x-2 font-semibold text-sm mb-1.5">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                            <span>Terdapat kendala validasi input formulir:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 pl-2">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white/80 border-t border-emerald-100 py-4 px-6 text-center text-xs text-emerald-800/60">
                SIIS &copy; {{ date('Y') }} - Sistem Informasi Inventaris Sekolah • Terintegrasi IoT RFID ESP32
            </footer>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>

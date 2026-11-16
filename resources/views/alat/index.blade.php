@extends('layouts.app')

@section('title', 'Katalog & Inventaris Alat')

@section('content')
<div class="space-y-6" x-data="{
    viewMode: 'grid', // 'grid' atau 'table'
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: '', kategori_id: '', nama_alat: '', kode_alat: '', kondisi: 'baik', stok_total: 1, stok_tersedia: 1, lokasi_penyimpanan: '' },
    openEdit(alat) {
        this.editData = { ...alat };
        this.editModalOpen = true;
    }
}">

    <!-- Page Title & Top Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Inventaris Alat Sekolah</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar sarana, proyektor, peralatan lab, dan perlengkapan sekolah yang siap dipinjam.</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Toggle Grid / Table View -->
            <div class="flex items-center bg-emerald-100/60 p-1 rounded-xl text-xs font-semibold text-emerald-900 border border-emerald-200/60">
                <button type="button" @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-white text-emerald-800 shadow-sm' : 'text-emerald-700 hover:text-emerald-950'"
                        class="px-3 py-1.5 rounded-lg flex items-center space-x-1.5 transition">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Katalog Grid</span>
                </button>
                <button type="button" @click="viewMode = 'table'"
                        :class="viewMode === 'table' ? 'bg-white text-emerald-800 shadow-sm' : 'text-emerald-700 hover:text-emerald-950'"
                        class="px-3 py-1.5 rounded-lg flex items-center space-x-1.5 transition">
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Tabel Ringkas</span>
                </button>
            </div>

            <!-- Tombol Tambah Alat (Super Admin, Staff Sarpras, Wakasek Sarpras) -->
            @if(auth()->user()->canManageInventaris())
                <button type="button" @click="addModalOpen = true"
                        class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-sm shadow-emerald-500/20 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>+ Tambah Alat Baru</span>
                </button>
            @endif

            <!-- Tombol Ajukan Peminjaman untuk Siswa/Guru -->
            @if(auth()->user()->isPeminjam())
                <a href="{{ route('peminjaman.create') }}"
                   class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-sm shadow-emerald-500/20 transition">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Buka Formulir Pinjam</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Search Bar & Filter Dropdowns Toolbar -->
    <div class="bg-white/90 backdrop-blur-md p-5 rounded-3xl shadow-sm border border-emerald-100">
        <form method="GET" action="{{ route('alat.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Bar -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider mb-1">Cari Alat / Kode</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-600">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama alat, kode unik, atau rak penyimpanan..."
                           class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-emerald-50/20">
                </div>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label class="block text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider mb-1">Filter Kategori</label>
                <select name="kategori_id" onchange="this.form.submit()" class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kondisi -->
            <div>
                <label class="block text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider mb-1">Filter Kondisi Fisik</label>
                <div class="flex space-x-2">
                    <select name="kondisi" onchange="this.form.submit()" class="w-full text-sm rounded-xl border border-slate-200 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Kondisi</option>
                        <option value="baik" {{ ($kondisi ?? '') === 'baik' ? 'selected' : '' }}>Kondisi Baik</option>
                        <option value="rusak_ringan" {{ ($kondisi ?? '') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ ($kondisi ?? '') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>

                    @if($search || $kategoriId || !empty($kondisi))
                        <a href="{{ route('alat.index') }}" class="p-2.5 text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition shrink-0" title="Reset filter">
                            <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- TAMPILAN 1: GRID / CARD LAYOUT (FLAT UI) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($alatList as $alat)
            <div class="bg-white rounded-2xl border border-slate-200 flex flex-col justify-between hover:border-emerald-400 transition group p-4">
                <div>
                    <!-- Top Category & Condition Header -->
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 truncate flex items-center gap-1">
                            <i data-lucide="folder" class="w-3 h-3 text-slate-400"></i>
                            <span>{{ $alat->kategori->nama_kategori ?? 'Umum' }}</span>
                        </span>

                        @if($alat->kondisi === 'baik')
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600"></i>
                                <span>Baik</span>
                            </span>
                        @elseif($alat->kondisi === 'rusak_ringan')
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1">
                                <i data-lucide="alert-triangle" class="w-3 h-3 text-amber-600"></i>
                                <span>Rusak Ringan</span>
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-1">
                                <i data-lucide="alert-octagon" class="w-3 h-3 text-rose-600"></i>
                                <span>Rusak Berat</span>
                            </span>
                        @endif
                    </div>

                    <!-- Item Name & Code -->
                    <a href="{{ route('alat.show', $alat) }}" class="block group-hover:text-emerald-700 transition">
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">
                            {{ $alat->nama_alat }}
                        </h3>
                    </a>
                    <div class="mt-1 font-mono text-xs text-emerald-800 font-bold bg-slate-50 px-2 py-0.5 rounded border border-slate-200 inline-flex items-center gap-1">
                        <i data-lucide="hash" class="w-3 h-3 text-emerald-600"></i>
                        <span>{{ $alat->kode_alat }}</span>
                    </div>

                    <!-- Location -->
                    <div class="mt-2.5 flex items-center space-x-1.5 text-xs text-slate-500">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <span class="truncate">{{ $alat->lokasi_penyimpanan ?: 'Gudang Sarpras' }}</span>
                    </div>

                    <!-- Rincian Unit Fisik & Kondisi Tiap Unit (1 s/d N) -->
                    <a href="{{ route('alat.show', $alat) }}"
                       class="mt-3 block p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 transition group/unit">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                            <span class="flex items-center space-x-1.5">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>{{ $alat->units->count() }} Unit Teridentifikasi</span>
                            </span>
                            <span class="text-[10px] text-emerald-700 font-bold flex items-center space-x-0.5 group-hover/unit:translate-x-0.5 transition">
                                <span>Rincian</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px]">
                            <span class="text-emerald-800 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-2.5 h-2.5 text-emerald-600"></i>
                                <span>{{ $alat->units_baik_count }} Baik</span>
                            </span>
                            @if($alat->units_rusak_ringan_count > 0)
                                <span class="text-amber-800 font-bold bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-2.5 h-2.5 text-amber-600"></i>
                                    <span>{{ $alat->units_rusak_ringan_count }} Rusak Ringan</span>
                                </span>
                            @endif
                            @if($alat->units_rusak_berat_count > 0)
                                <span class="text-rose-800 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200 flex items-center gap-1">
                                    <i data-lucide="alert-octagon" class="w-2.5 h-2.5 text-rose-600"></i>
                                    <span>{{ $alat->units_rusak_berat_count }} Rusak Berat</span>
                                </span>
                            @endif
                        </div>
                    </a>

                    <!-- Stock Indicator with Badge -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Stok:</span>
                        </span>
                        @if($alat->stok_tersedia > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                Tersedia: {{ $alat->stok_tersedia }} / {{ $alat->stok_total }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                Habis (0 / {{ $alat->stok_total }})
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Bottom Action Buttons based on Role -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    @if(auth()->user()->canManageInventaris())
                        <!-- Super Admin, Staff Sarpras, Wakasek Sarpras Controls: Edit & Hapus -->
                        <div class="flex items-center space-x-1.5">
                            <a href="{{ route('sarpras.alat.qr', $alat) }}" target="_blank"
                               class="p-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-emerald-800 bg-slate-50 hover:bg-emerald-50 border border-slate-200 transition" title="Cetak Stiker QR Code">
                                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                            </a>
                            <button type="button" @click="openEdit({{ Js::from($alat) }})"
                                    class="flex-1 inline-flex items-center justify-center space-x-1 px-3 py-2 rounded-xl text-xs font-bold text-slate-800 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-slate-500"></i>
                                <span>Edit</span>
                            </button>
                            <form action="{{ route('alat.destroy', $alat) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini dari inventaris?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition" title="Hapus Alat">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    @elseif(auth()->user()->isPeminjam())
                        <!-- Siswa & Guru Controls: Pilih / Masukkan ke Keranjang Pinjam -->
                        @if($alat->stok_tersedia > 0)
                            <a href="{{ route('peminjaman.create', ['alat_id' => $alat->id]) }}"
                               class="w-full inline-flex items-center justify-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                <span>Pilih / Ajukan Pinjam</span>
                            </a>
                        @else
                            <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center space-x-1 px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 bg-slate-100 cursor-not-allowed border border-slate-200">
                                <i data-lucide="slash" class="w-3.5 h-3.5"></i>
                                <span>Stok Sedang Habis</span>
                            </button>
                        @endif
                    @else
                        <!-- Kepala Sekolah (View Only) -->
                        <div class="text-center text-xs font-bold text-slate-600 py-1.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Aset Terdaftar (Monitoring)</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 bg-white rounded-2xl border border-slate-200 text-center text-slate-400 p-8">
                <i data-lucide="package-x" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                <h3 class="text-base font-bold text-slate-700">Tidak ada alat inventaris yang cocok</h3>
                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau ubah filter kategori/kondisi.</p>
            </div>
        @endforelse
    </div>

    <!-- TAMPILAN 2: TABEL RESPONSIP -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama & Kode Alat</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Kondisi</th>
                        <th class="px-6 py-4">Stok Ketersediaan</th>
                        <th class="px-6 py-4">Lokasi Rak</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alatList as $alat)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('alat.show', $alat) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition block">
                                    {{ $alat->nama_alat }}
                                </a>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="font-mono text-xs text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/50">
                                        {{ $alat->kode_alat }}
                                    </span>
                                    <a href="{{ route('alat.show', $alat) }}" class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-100/50 px-2 py-0.5 rounded-full inline-flex items-center space-x-1">
                                        <i data-lucide="layers" class="w-3 h-3"></i>
                                        <span>{{ $alat->units->count() }} Unit Fisik</span>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ $alat->kategori->nama_kategori ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($alat->kondisi === 'baik')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Baik</span>
                                @elseif($alat->kondisi === 'rusak_ringan')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Rusak Ringan</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Rusak Berat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($alat->stok_tersedia > 0)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tersedia: {{ $alat->stok_tersedia }} / {{ $alat->stok_total }} unit
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Habis (0 / {{ $alat->stok_total }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $alat->lokasi_penyimpanan ?: 'Gudang Sarpras' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('alat.show', $alat) }}"
                                   class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition inline-flex items-center space-x-1" title="Lihat Rincian Unit Fisik">
                                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    <span>Unit ({{ $alat->units->count() }})</span>
                                </a>
                                @if(auth()->user()->canManageInventaris())
                                    <a href="{{ route('sarpras.alat.qr', $alat) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition inline-block" title="Cetak Label Stiker QR">
                                        <i data-lucide="qr-code" class="w-3.5 h-3.5 inline"></i>
                                    </a>
                                    <button type="button" @click="openEdit({{ Js::from($alat) }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                                        Edit
                                    </button>
                                    <form action="{{ route('alat.destroy', $alat) }}" method="POST" class="inline" onsubmit="return confirm('Hapus alat inventaris ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @elseif(auth()->user()->isPeminjam())
                                    @if($alat->stok_tersedia > 0)
                                        <a href="{{ route('peminjaman.create', ['alat_id' => $alat->id]) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm transition">
                                            + Pilih Alat
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic font-medium">Habis</span>
                                    @endif
                                @else
                                    <span class="text-xs text-emerald-800/60 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">View Only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data alat inventaris yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($alatList->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200">
            {{ $alatList->links() }}
        </div>
    @endif

    <!-- MODAL TAMBAH ALAT (Super Admin, Staff Sarpras, Wakasek Sarpras) -->
    @if(auth()->user()->canManageInventaris())
        <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-emerald-100" @click.away="addModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <h3 class="text-lg font-extrabold text-slate-900">Tambah Alat Inventaris Baru</h3>
                    <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form action="{{ route('alat.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_alat" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500" placeholder="Misal: LCD Proyektor EPSON EB-X400">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode Alat (Unik) <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_alat" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500" placeholder="Misal: PRJ-EPS-005">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                            <select name="kategori_id" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="" disabled selected>Pilih Kategori</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                            <select name="kondisi" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Stok Total Unit <span class="text-rose-500">*</span></label>
                            <input type="number" name="stok_total" min="1" value="1" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                            <input type="text" name="lokasi_penyimpanan" class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500" placeholder="Misal: Rak A-2 Gudang Sarpras">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t border-emerald-100">
                        <button type="button" @click="addModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm shadow-emerald-500/20">Simpan Alat</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDIT ALAT -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-emerald-100" @click.away="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <h3 class="text-lg font-extrabold text-slate-900">Edit Data Alat Inventaris</h3>
                    <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form :action="`{{ url('alat') }}/${editData.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_alat" x-model="editData.nama_alat" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kode Alat <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_alat" x-model="editData.kode_alat" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                            <select name="kategori_id" x-model="editData.kategori_id" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500">
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                            <select name="kondisi" x-model="editData.kondisi" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Stok Total Unit <span class="text-rose-500">*</span></label>
                            <input type="number" name="stok_total" x-model="editData.stok_total" min="1" required class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                            <input type="text" name="lokasi_penyimpanan" x-model="editData.lokasi_penyimpanan" class="w-full text-sm border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t border-emerald-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm shadow-emerald-500/20">Perbarui Alat</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection


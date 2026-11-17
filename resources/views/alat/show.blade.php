@extends('layouts.app')

@section('title', 'Rincian Unit - ' . $alat->nama_alat)

@section('content')
<div class="space-y-6" x-data="{
    filterKondisi: 'all',
    filterStatus: 'all',
    searchUnit: '',
    addUnitModalOpen: false,
    editUnitModalOpen: false,
    qrModalOpen: false,
    qrData: { title: '', code: '', url: '', printUrl: '' },
    openQr(title, code, printUrl) {
        this.qrData = {
            title: title,
            code: code,
            url: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(code),
            printUrl: printUrl
        };
        this.qrModalOpen = true;
    },
    selectedUnit: { id: '', kode_unit: '', nomor_seri: '', kondisi: 'baik', status: 'tersedia', lokasi_spesifik: '', catatan: '' },
    openEditUnit(unit) {
        this.selectedUnit = { ...unit };
        this.editUnitModalOpen = true;
    },
    matchesFilter(unit) {
        const matchKondisi = this.filterKondisi === 'all' || unit.kondisi === this.filterKondisi;
        const matchStatus = this.filterStatus === 'all' || unit.status === this.filterStatus;
        const term = this.searchUnit.toLowerCase().trim();
        const matchSearch = !term || 
            unit.kode_unit.toLowerCase().includes(term) || 
            (unit.nomor_seri && unit.nomor_seri.toLowerCase().includes(term)) ||
            (unit.catatan && unit.catatan.toLowerCase().includes(term)) ||
            (unit.lokasi_spesifik && unit.lokasi_spesifik.toLowerCase().includes(term));
        return matchKondisi && matchStatus && matchSearch;
    }
}">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs font-semibold text-emerald-800/70 mb-1.5">
                <a href="{{ route('alat.index') }}" class="hover:text-emerald-950 transition flex items-center space-x-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Katalog Inventaris</span>
                </a>
                <span>&bull;</span>
                <span class="text-slate-400 truncate">{{ $alat->kategori->nama_kategori ?? 'Aset' }}</span>
                <span>&bull;</span>
                <span class="text-emerald-900 font-bold truncate">{{ $alat->nama_alat }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>{{ $alat->nama_alat }}</span>
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-100/70 text-emerald-900 border border-emerald-300/60">
                    {{ $alat->kode_alat }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Identifikasi fisik individual per unit. Setiap unit memiliki kode unik (ID fisik), nomor seri, serta pencatatan kondisi mandiri.
            </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            @if(auth()->user()->canManageInventaris())
                <button type="button" @click="addUnitModalOpen = true"
                        class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-4 py-2.5 rounded-xl font-medium text-xs shadow-sm shadow-emerald-500/20 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>+ Tambah Unit Baru</span>
                </button>
                <button type="button" @click="openQr('Master: {{ addslashes($alat->nama_alat) }}', '{{ $alat->kode_alat }}', '{{ route('sarpras.alat.qr', $alat) }}')"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-white hover:bg-emerald-50 text-emerald-800 font-semibold text-xs rounded-xl border border-emerald-200 transition shadow-sm">
                    <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600"></i>
                    <span>Lihat / Cetak QR Master</span>
                </button>
            @endif

            @if(auth()->user()->isPeminjam() && $alat->stok_tersedia > 0)
                <a href="{{ route('peminjaman.create', ['alat_id' => $alat->id]) }}"
                   class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Pinjam Alat Ini</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Master Asset Summary Card (Flat Design) -->
    <div class="bg-slate-900 rounded-2xl p-6 text-white border border-slate-800 relative">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 items-center">
            <div class="md:col-span-2 space-y-2">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-400 bg-slate-800 border border-slate-700 px-3 py-1 rounded-lg">
                    <i data-lucide="folder" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $alat->kategori->nama_kategori ?? 'Umum' }}</span>
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-white leading-tight flex items-center gap-2">
                    <i data-lucide="laptop" class="w-6 h-6 text-emerald-400 shrink-0"></i>
                    <span>{{ $alat->nama_alat }}</span>
                </h2>
                <div class="flex items-center space-x-2 text-xs text-slate-300">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Lokasi Default: <strong class="text-white">{{ $alat->lokasi_penyimpanan ?: 'Gudang Sarpras' }}</strong></span>
                </div>
            </div>

            <!-- Total Units & Availability -->
            <div class="flex items-center justify-between md:justify-end md:col-span-2 gap-3">
                <div class="bg-slate-800 px-4 py-3 rounded-xl border border-slate-700 text-center min-w-[110px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider flex items-center justify-center gap-1 mb-0.5">
                        <i data-lucide="boxes" class="w-3 h-3 text-emerald-400"></i> Total Unit
                    </span>
                    <span class="text-2xl font-extrabold text-white">{{ $alat->units->count() }}</span>
                    <span class="text-[10px] text-slate-400 block font-medium">Terdata</span>
                </div>
                <div class="bg-slate-800 px-4 py-3 rounded-xl border border-emerald-500/30 text-center min-w-[110px]">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider flex items-center justify-center gap-1 mb-0.5">
                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-400"></i> Siap Pinjam
                    </span>
                    <span class="text-2xl font-extrabold text-emerald-400">{{ $alat->units_tersedia_count }}</span>
                    <span class="text-[10px] text-emerald-300/70 block font-medium">Tersedia</span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI / Breakdown Kondisi Fisik Cards (Flat Minimalist) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Unit -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="font-bold text-[11px]">Total Unit</span>
                <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
            </div>
            <div class="text-xl font-black text-slate-900">{{ $alat->units->count() }}</div>
            <span class="text-[10px] text-slate-400 font-medium">Fisik individual</span>
        </div>

        <!-- Tersedia -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-emerald-800 mb-1">
                <span class="font-bold text-[11px]">Tersedia</span>
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="text-xl font-black text-emerald-700">{{ $alat->units_tersedia_count }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">Siap dipinjam</span>
        </div>

        <!-- Sedang Dipinjam -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-blue-800 mb-1">
                <span class="font-bold text-[11px]">Dipinjam</span>
                <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div class="text-xl font-black text-blue-700">{{ $alat->units_dipinjam_count }}</div>
            <span class="text-[10px] text-blue-600 font-medium">Aktif dipinjam</span>
        </div>

        <!-- Kondisi Baik -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-emerald-800 mb-1">
                <span class="font-bold text-[11px]">Kondisi Baik</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="text-xl font-black text-emerald-700">{{ $alat->units_baik_count }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">Prima / Normal</span>
        </div>

        <!-- Rusak Ringan -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-amber-800 mb-1">
                <span class="font-bold text-[11px]">Rusak Ringan</span>
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
            </div>
            <div class="text-xl font-black text-amber-600">{{ $alat->units_rusak_ringan_count }}</div>
            <span class="text-[10px] text-amber-600 font-medium">Fungsi terbatas</span>
        </div>

        <!-- Rusak Berat -->
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between text-xs text-rose-800 mb-1">
                <span class="font-bold text-[11px]">Rusak Berat</span>
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600"></i>
            </div>
            <div class="text-xl font-black text-rose-600">{{ $alat->units_rusak_berat_count }}</div>
            <span class="text-[10px] text-rose-600 font-medium">Perlu perbaikan</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar untuk Unit (Flat UI) -->
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" x-model="searchUnit" placeholder="Cari Kode Unit (#001), No Seri, Rak, atau Catatan..."
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-emerald-500 bg-slate-50 font-medium">
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <!-- Filter Kondisi -->
            <select x-model="filterKondisi" class="text-xs rounded-lg border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:border-emerald-500 font-medium text-slate-700">
                <option value="all">Semua Kondisi Fisik</option>
                <option value="baik">Kondisi Baik</option>
                <option value="rusak_ringan">Rusak Ringan</option>
                <option value="rusak_berat">Rusak Berat</option>
            </select>

            <!-- Filter Status -->
            <select x-model="filterStatus" class="text-xs rounded-lg border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:border-emerald-500 font-medium text-slate-700">
                <option value="all">Semua Status Ketersediaan</option>
                <option value="tersedia">Tersedia</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="perbaikan">Perbaikan</option>
                <option value="hilang">Hilang</option>
            </select>
        </div>
    </div>

    <!-- Daftar Unit Fisik (Unit 1 s/d N) - Flat Minimalist Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Rincian Fisik: Unit 1 s/d Unit {{ $alat->units->count() }}</h3>
                    <p class="text-[10px] text-slate-500">Daftar ID fisik individual, nomor seri pabrik, kondisi terkini, dan riwayat lokasi.</p>
                </div>
            </div>
            <span class="text-xs font-mono font-bold text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-slate-200 flex items-center gap-1.5">
                <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>{{ $alat->units->count() }} Unit Terdaftar</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-center w-12">No</th>
                        <th class="px-5 py-3">Kode Unit (ID Fisik)</th>
                        <th class="px-5 py-3">Nomor Seri</th>
                        <th class="px-5 py-3">Kondisi Fisik</th>
                        <th class="px-5 py-3">Status Ketersediaan</th>
                        <th class="px-5 py-3">Lokasi Rak</th>
                        <th class="px-5 py-3">Catatan / Keterangan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alat->units as $index => $unit)
                        <tr class="hover:bg-slate-50 transition group"
                            x-show="matchesFilter({{ Js::from($unit) }})">
                            <!-- No Urut -->
                            <td class="px-5 py-3.5 text-center font-bold text-slate-400">
                                #{{ $index + 1 }}
                            </td>

                            <!-- Kode Unit -->
                            <td class="px-5 py-3.5">
                                <div class="font-mono font-bold text-slate-900 text-xs bg-slate-50 px-2 py-0.5 rounded border border-slate-200 inline-flex items-center space-x-1.5">
                                    <i data-lucide="cpu" class="w-3 h-3 text-emerald-600"></i>
                                    <span>{{ $unit->kode_unit }}</span>
                                </div>
                            </td>

                            <!-- Nomor Seri -->
                            <td class="px-5 py-3.5 font-mono text-slate-600">
                                <span class="inline-flex items-center gap-1 text-[11px]">
                                    <i data-lucide="fingerprint" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $unit->nomor_seri ?: '-' }}</span>
                                </span>
                            </td>

                            <!-- Kondisi Fisik -->
                            <td class="px-5 py-3.5">
                                @if($unit->kondisi === 'baik')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600"></i>
                                        <span>Baik</span>
                                    </span>
                                @elseif($unit->kondisi === 'rusak_ringan')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i data-lucide="alert-triangle" class="w-3 h-3 text-amber-600"></i>
                                        <span>Rusak Ringan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                        <i data-lucide="alert-octagon" class="w-3 h-3 text-rose-600"></i>
                                        <span>Rusak Berat</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status Ketersediaan -->
                            <td class="px-5 py-3.5">
                                @if($unit->status === 'tersedia')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                        <span>Tersedia</span>
                                    </span>
                                @elseif($unit->status === 'dipinjam')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        <i data-lucide="clock" class="w-3 h-3 text-blue-600"></i>
                                        <span>Dipinjam</span>
                                    </span>
                                @elseif($unit->status === 'perbaikan')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i data-lucide="wrench" class="w-3 h-3 text-amber-600"></i>
                                        <span>Perbaikan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                        <i data-lucide="x" class="w-3 h-3 text-slate-600"></i>
                                        <span>Hilang</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Lokasi Spesifik -->
                            <td class="px-5 py-3.5 text-slate-600">
                                <span class="inline-flex items-center gap-1 text-[11px]">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $unit->lokasi_spesifik ?: ($alat->lokasi_penyimpanan ?: 'Gudang Sarpras') }}</span>
                                </span>
                            </td>

                            <!-- Catatan -->
                            <td class="px-5 py-3.5">
                                @if($unit->catatan)
                                    <span class="text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 inline-flex items-center gap-1 max-w-xs truncate" title="{{ $unit->catatan }}">
                                        <i data-lucide="message-square-text" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span class="truncate">{{ $unit->catatan }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-3.5 text-right space-x-1 whitespace-nowrap">
                                <button type="button" @click="openQr('Unit: {{ $unit->kode_unit }} ({{ addslashes($alat->nama_alat) }})', '{{ $unit->kode_unit }}', '{{ route('unit-alat.qr', $unit) }}')"
                                   class="p-1.5 rounded-lg text-slate-700 hover:text-emerald-800 bg-slate-50 hover:bg-emerald-50 border border-slate-200 transition inline-flex items-center" title="Lihat & Cetak Stiker QR Unit">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-emerald-600"></i>
                                </button>

                                @if(auth()->user()->canManageInventaris())
                                    <button type="button" @click="openEditUnit({{ Js::from($unit) }})"
                                            class="px-2.5 py-1 rounded-lg font-bold text-slate-800 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition inline-flex items-center gap-1">
                                        <i data-lucide="sliders" class="w-3 h-3 text-slate-500"></i>
                                        <span>Ubah Kondisi</span>
                                    </button>

                                    @if($unit->status !== 'dipinjam')
                                        <form action="{{ route('unit-alat.destroy', $unit) }}" method="POST" class="inline" onsubmit="return confirm('Hapus Unit {{ $unit->kode_unit }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition" title="Hapus Unit">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                Belum ada rincian unit fisik untuk alat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL EDIT KONDISI UNIT (Staff, Wakasek, Admin) -->
    @if(auth()->user()->canManageInventaris())
        <div x-show="editUnitModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-4 border border-emerald-100" @click.away="editUnitModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Ubah Status & Kondisi Unit</h3>
                        <p class="text-xs font-mono font-bold text-emerald-700" x-text="selectedUnit.kode_unit"></p>
                    </div>
                    <button type="button" @click="editUnitModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'{{ url('/unit-alat') }}/' + selectedUnit.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <!-- Kondisi Fisik -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Fisik Unit</label>
                        <select name="kondisi" x-model="selectedUnit.kondisi" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="baik">Kondisi Baik (Prima)</option>
                            <option value="rusak_ringan">Rusak Ringan (Bisa Digunakan Terbatas)</option>
                            <option value="rusak_berat">Rusak Berat (Tidak Layak Pakai)</option>
                        </select>
                    </div>

                    <!-- Status Ketersediaan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Ketersediaan</label>
                        <select name="status" x-model="selectedUnit.status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="tersedia">Tersedia (Bisa Dipinjam)</option>
                            <option value="dipinjam">Sedang Dipinjam</option>
                            <option value="perbaikan">Dalam Perbaikan (Maintenance)</option>
                            <option value="hilang">Hilang</option>
                        </select>
                    </div>

                    <!-- Nomor Seri (Serial Number) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri (Serial Number Pabrik)</label>
                        <input type="text" name="nomor_seri" x-model="selectedUnit.nomor_seri" placeholder="Contoh: SN-839219320"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Lokasi Spesifik -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Rak / Penempatan Spesifik</label>
                        <input type="text" name="lokasi_spesifik" x-model="selectedUnit.lokasi_spesifik" placeholder="Contoh: Lab Komputer 1, Rak A3"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Catatan Fisik / Kerusakan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Khusus Unit</label>
                        <textarea name="catatan" x-model="selectedUnit.catatan" rows="2" placeholder="Contoh: Baterai drop, harus colok adaptor terus menerus."
                                  class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-emerald-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="editUnitModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL TAMBAH UNIT BARU (Staff, Wakasek, Admin) -->
        <div x-show="addUnitModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-4 border border-emerald-100" @click.away="addUnitModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">+ Tambah Unit Fisik Baru</h3>
                        <p class="text-xs text-slate-500">ID unit akan dibuat berurutan (misal Unit #{{ $alat->units->count() + 1 }} dst.)</p>
                    </div>
                    <button type="button" @click="addUnitModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('alat.units.add', $alat) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Jumlah Unit Baru -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Unit yang Ditambahkan</label>
                        <input type="number" name="jumlah" value="1" min="1" max="50" required
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                        <span class="text-[10px] text-slate-400 mt-1 block">Sistem akan men-generate kode otomatis sesuai urutan master.</span>
                    </div>

                    <!-- Kondisi Awal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Unit Baru</label>
                        <select name="kondisi" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="baik">Kondisi Baik (Baru / Normal)</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>

                    <!-- Lokasi Penyimpanan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Rak / Lemari</label>
                        <input type="text" name="lokasi_spesifik" value="{{ $alat->lokasi_penyimpanan }}" placeholder="Contoh: Rak Komputer A"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-3 border-t border-emerald-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="addUnitModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md transition">
                            + Generate Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Preview QR Code Barcode Langsung di Layar -->
    <div x-show="qrModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center border border-slate-100" @click.away="qrModalOpen = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-900 text-left">Barcode / QR Code Alat</span>
                </div>
                <button type="button" @click="qrModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 inline-block">
                <img :src="qrData.url" :alt="qrData.code" class="w-48 h-48 mx-auto rounded-xl shadow-sm bg-white p-2">
            </div>

            <div>
                <p class="font-mono text-base font-extrabold text-emerald-900 tracking-wider" x-text="qrData.code"></p>
                <p class="text-xs text-slate-600 mt-1 font-medium" x-text="qrData.title"></p>
                <p class="text-[11px] text-slate-400 mt-1">Dapat dipindai langsung menggunakan kamera Scanner di aplikasi Mobile SIIS.</p>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <a :href="qrData.printUrl" target="_blank"
                   class="flex-1 inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-500/20 transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak Stiker Fisik</span>
                </a>
                <button type="button" @click="qrModalOpen = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection


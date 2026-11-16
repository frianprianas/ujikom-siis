@extends('layouts.app')

@section('title', 'Executive Monitoring Dashboard - Kepala Sekolah')

@section('content')
<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Ruang Eksekutif</span>
                <span class="text-xs text-slate-400">Monitoring Realtime</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Dashboard Monitoring Sarana & Prasarana</h1>
            <p class="text-sm text-slate-500">Laporan terpadu pemanfaatan aset, ketersediaan inventaris, dan kepatuhan pengembalian alat.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="inline-flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / Ekspor Laporan</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1 -->
        <div class="bg-white/90 backdrop-blur-sm p-5 rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                <i data-lucide="boxes" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Total Unit Inventaris</span>
                <div class="flex items-baseline space-x-2 mt-0.5">
                    <span class="text-2xl font-extrabold text-slate-900">{{ $totalUnitAlat }}</span>
                    <span class="text-xs text-slate-400">({{ $totalAlat }} item)</span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white/90 backdrop-blur-sm p-5 rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 border border-teal-100 flex items-center justify-center shrink-0">
                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-teal-900/60 uppercase tracking-wider block">Sedang Dipinjam</span>
                <div class="flex items-baseline space-x-2 mt-0.5">
                    <span class="text-2xl font-extrabold text-teal-700">{{ $unitDipinjam }}</span>
                    <span class="text-xs text-slate-400">unit alat</span>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white/90 backdrop-blur-sm p-5 rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100/70 text-emerald-800 border border-emerald-200 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Unit Siap Digunakan</span>
                <div class="flex items-baseline space-x-2 mt-0.5">
                    <span class="text-2xl font-extrabold text-emerald-700">{{ $unitTersedia }}</span>
                    <span class="text-xs text-slate-400">tersedia di rak</span>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white/90 backdrop-blur-sm p-5 rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0">
                <i data-lucide="clock-alert" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-rose-900/60 uppercase tracking-wider block">Peminjaman Terlambat</span>
                <div class="flex items-baseline space-x-2 mt-0.5">
                    <span class="text-2xl font-extrabold text-rose-600">{{ $terlambat }}</span>
                    <span class="text-xs text-slate-400">transaksi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats: Kondisi Fisik & Status Pengajuan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Kondisi Fisik Alat -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="activity" class="w-4 h-4 text-brand-600"></i>
                <span>Kelayakan Kondisi Fisik Aset</span>
            </h2>
            <div class="grid grid-cols-3 gap-3">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                    <span class="text-xs text-emerald-700 font-semibold uppercase block">Kondisi Baik</span>
                    <span class="text-2xl font-bold text-emerald-900 mt-1 block">{{ $alatKondisiBaik }}</span>
                    <span class="text-[11px] text-emerald-600">Alat Siap Pakai</span>
                </div>
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-center">
                    <span class="text-xs text-amber-700 font-semibold uppercase block">Rusak Ringan</span>
                    <span class="text-2xl font-bold text-amber-900 mt-1 block">{{ $alatRusakRingan }}</span>
                    <span class="text-[11px] text-amber-600">Perlu Servis</span>
                </div>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-center">
                    <span class="text-xs text-rose-700 font-semibold uppercase block">Rusak Berat</span>
                    <span class="text-2xl font-bold text-rose-900 mt-1 block">{{ $alatRusakBerat }}</span>
                    <span class="text-[11px] text-rose-600">Tidak Beroperasi</span>
                </div>
            </div>
        </div>

        <!-- Rekapitulasi Alur Peminjaman -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-600"></i>
                <span>Rekapitulasi Sirkulasi Alat</span>
            </h2>
            <div class="grid grid-cols-3 gap-3">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                    <span class="text-xs text-slate-500 font-semibold uppercase block">Menunggu Approval</span>
                    <span class="text-2xl font-bold text-slate-800 mt-1 block">{{ $menungguPersetujuan }}</span>
                    <span class="text-[11px] text-slate-400">Pengajuan Baru</span>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100 text-center">
                    <span class="text-xs text-indigo-700 font-semibold uppercase block">Sedang Dipinjam</span>
                    <span class="text-2xl font-bold text-indigo-900 mt-1 block">{{ $sedangDipinjam }}</span>
                    <span class="text-[11px] text-indigo-600">Dipegang Siswa/Guru</span>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                    <span class="text-xs text-emerald-700 font-semibold uppercase block">Selesai Kembali</span>
                    <span class="text-2xl font-bold text-emerald-900 mt-1 block">{{ $selesaiKembali }}</span>
                    <span class="text-[11px] text-emerald-600">Riwayat Beres</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terkini Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                <i data-lucide="history" class="w-4 h-4 text-brand-600"></i>
                <span>10 Aktivitas Peminjaman Terkini</span>
            </h2>
            <span class="text-xs text-slate-400">Total Akumulasi: {{ $totalPeminjaman }} Peminjaman</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">ID</th>
                        <th class="px-6 py-3.5">Peminjam</th>
                        <th class="px-6 py-3.5">Alat Dipinjam</th>
                        <th class="px-6 py-3.5">Tgl Pinjam / Kembali</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Approval Sarpras</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($peminjamanTerkini as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-700">#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $item->user->nama }}</div>
                                <div class="text-[11px] text-slate-400">{{ strtoupper($item->user->role) }} {{ $item->user->kelas_atau_jabatan ? '• ' . $item->user->kelas_atau_jabatan : '' }}</div>
                            </td>
                            <td class="px-6 py-3.5 text-xs">
                                @foreach($item->details as $d)
                                    <div>{{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }} unit)</div>
                                @endforeach
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $item->tanggal_pinjam->format('d/m/Y') }} s/d {{ $item->tanggal_harus_kembali->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3.5">
                                @php
                                    $badgeColors = [
                                        'menunggu_persetujuan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sedang_dipinjam' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'dikembalikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'terlambat' => 'bg-red-50 text-red-700 border-red-200 font-bold',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeColors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500">
                                {{ $item->approvedBy ? $item->approvedBy->nama : 'Belum diverifikasi' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">Belum ada riwayat aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection


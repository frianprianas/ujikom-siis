@extends('layouts.app')

@section('title', in_array(auth()->user()->role, ['siswa', 'guru']) ? 'Peminjaman Saya' : 'Daftar Peminjaman')

@section('content')
<div class="space-y-6">

    <!-- Page Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900 p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 mb-3">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                {{ in_array(auth()->user()->role, ['siswa', 'guru']) ? 'Portal Peminjam' : 'Monitoring Sirkulasi Alat' }}
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ in_array(auth()->user()->role, ['siswa', 'guru']) ? 'Daftar Peminjaman Saya' : 'Daftar Semua Peminjaman' }}
            </h1>
            <p class="text-sm text-emerald-100/80 mt-1 max-w-xl">
                Pantau status pengajuan, jadwal batas pengembalian, status ACC, dan histori barang yang telah dikembalikan.
            </p>
        </div>
        @if(in_array(auth()->user()->role, ['siswa', 'guru']))
            <div class="relative z-10 shrink-0">
                <a href="{{ route('peminjaman.create') }}"
                   class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white px-5 py-3 rounded-2xl font-semibold text-sm shadow-lg shadow-emerald-900/30 transition transform active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>+ Ajukan Pinjam Alat</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Peminjaman Table Card -->
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-emerald-50/50 border-b border-emerald-100 text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider">
                        <th class="px-6 py-4">No. Pengajuan</th>
                        @if(!in_array(auth()->user()->role, ['siswa', 'guru']))
                            <th class="px-6 py-4">Peminjam</th>
                        @endif
                        <th class="px-6 py-4">Alat & Jumlah</th>
                        <th class="px-6 py-4">Tgl Pinjam / Batas Kembali</th>
                        <th class="px-6 py-4">Status & Pengembalian</th>
                        <th class="px-6 py-4">Approval</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse($peminjamanList as $item)
                        <tr class="hover:bg-emerald-50/40 transition">
                            <td class="px-6 py-4 font-mono font-bold text-emerald-950">
                                #{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                <div class="text-[11px] text-slate-400 font-sans font-normal">{{ $item->tanggal_pengajuan->format('d M Y, H:i') }}</div>
                            </td>
                            @if(!in_array(auth()->user()->role, ['siswa', 'guru']))
                                <td class="px-6 py-4">
                                    <div class="font-bold text-emerald-950">{{ $item->user->nama }}</div>
                                    <div class="text-xs text-slate-500 uppercase font-semibold text-[10px]">{{ $item->user->role }} {{ $item->user->kelas_atau_jabatan ? '• ' . $item->user->kelas_atau_jabatan : '' }}</div>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($item->details as $d)
                                        <li class="text-slate-800 font-medium flex items-center space-x-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs">{{ $d->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                            <span class="text-[11px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-1.5 py-0.2 rounded font-bold">{{ $d->jumlah }} unit</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div><span class="text-slate-400">Pinjam:</span> <span class="font-semibold text-emerald-950">{{ $item->tanggal_pinjam->format('d/m/Y') }}</span></div>
                                <div class="mt-0.5"><span class="text-slate-400">Batas:</span> <span class="font-semibold text-rose-600">{{ $item->tanggal_harus_kembali->format('d/m/Y') }}</span></div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeColors = [
                                        'menunggu_persetujuan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sedang_dipinjam' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'dikembalikan' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                        'terlambat' => 'bg-red-50 text-red-700 border-red-200 font-bold',
                                    ];
                                @endphp
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                    @if($item->status === 'dikembalikan')
                                        <div class="text-[11px] text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                            Kembali: {{ $item->tanggal_aktual_kembali?->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                @if($item->approvedBy)
                                    <div class="font-semibold text-emerald-950">{{ $item->approvedBy->nama }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $item->tanggal_approval?->format('d M Y, H:i') }}</div>
                                @else
                                    <span class="text-slate-400 italic">Menunggu verifikasi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('peminjaman.show', $item) }}" class="inline-flex items-center space-x-1 text-emerald-800 hover:text-emerald-950 font-semibold text-xs bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !in_array(auth()->user()->role, ['siswa', 'guru']) ? 7 : 6 }}" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-emerald-200 mb-2"></i>
                                <p>Belum ada riwayat peminjaman.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjamanList->hasPages())
            <div class="p-4 border-t border-emerald-100 bg-emerald-50/20">
                {{ $peminjamanList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

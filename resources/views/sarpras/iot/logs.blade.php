@extends('layouts.app')

@section('title', 'Audit Log RFID IoT')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Audit Log Transaksi Scan RFID</h1>
            <p class="text-sm text-slate-500 mt-1">Rekam jejak setiap kali kartu RFID didekatkan ke sensor scanner ESP32 meja Sarpras.</p>
        </div>
        <div>
            <a href="{{ route('sarpras.iot.simulator') }}" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-sm transition">
                <i data-lucide="play" class="w-4 h-4"></i>
                <span>Uji Coba Simulator Tap</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('sarpras.iot.logs') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari UID kartu, nama peminjam, atau device ID..."
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div class="flex space-x-2">
                <select name="status" onchange="this.form.submit()" class="text-sm rounded-xl border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Semua Status Aksi</option>
                    <option value="success" {{ $status === 'success' ? 'selected' : '' }}>Success (Berhasil)</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed (Gagal / Belum Disetujui)</option>
                    <option value="unauthorized" {{ $status === 'unauthorized' ? 'selected' : '' }}>Unauthorized (Perangkat Ilegal)</option>
                </select>
                @if($search || $status)
                    <a href="{{ route('sarpras.iot.logs') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Waktu Tap</th>
                        <th class="px-6 py-4">Perangkat Scanner</th>
                        <th class="px-6 py-4">Pengguna / UID Kartu</th>
                        <th class="px-6 py-4">Tipe Akses</th>
                        <th class="px-6 py-4">Status Aksi</th>
                        <th class="px-6 py-4">Keterangan / Respons</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 text-xs font-mono text-slate-600 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-800">
                                {{ $log->device_id }}
                            </td>
                            <td class="px-6 py-4">
                                @if($log->user)
                                    <div class="font-semibold text-slate-900">{{ $log->user->nama }}</div>
                                    <div class="text-xs text-slate-400 font-mono">UID: {{ $log->rfid_uid }} ({{ strtoupper($log->user->role) }})</div>
                                @else
                                    <span class="font-mono text-xs text-rose-600 bg-rose-50 px-2 py-0.5 rounded font-bold">UID: {{ $log->rfid_uid }}</span>
                                    <div class="text-[11px] text-slate-400">Tidak Terdaftar</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700 capitalize">
                                {{ str_replace('_', ' ', $log->tipe_akses) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($log->status_aksi === 'success')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                        Success
                                    </span>
                                @elseif($log->status_aksi === 'failed')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                        Failed
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                        Unauthorized
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 max-w-sm">
                                {{ $log->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Belum ada catatan log aktivitas scan RFID.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection


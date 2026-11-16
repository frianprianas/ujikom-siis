@extends('layouts.app')

@section('title', 'Perangkat IoT ESP32')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Perangkat IoT ESP32</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola scanner RFID meja Sarpras, kunci API autentikasi, dan status koneksi.</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('sarpras.iot.simulator') }}" class="inline-flex items-center space-x-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 px-4 py-2.5 rounded-xl font-medium text-sm transition">
                <i data-lucide="cpu" class="w-4 h-4"></i>
                <span>Buka Simulator Tap RFID</span>
            </a>
            <button type="button" @click="addModalOpen = true" class="inline-flex items-center space-x-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-sm transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Daftarkan Device Baru</span>
            </button>
        </div>
    </div>

    <!-- Table of Devices -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Device ID</th>
                        <th class="px-6 py-4">Nama Perangkat</th>
                        <th class="px-6 py-4">API Key Rahasia</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Aktivitas Scan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $d)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                {{ $d->device_id }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $d->nama_device }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                <span class="bg-slate-100 px-2 py-1 rounded select-all">{{ $d->api_key }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($d->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $d->logs_count }} kali tap
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('sarpras.iot.devices.toggle', $d) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $d->status === 'active' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }} transition">
                                        {{ $d->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Belum ada perangkat IoT yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Device -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.away="addModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900">Daftarkan Perangkat ESP32 Baru</h3>
            <form action="{{ route('sarpras.iot.devices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Device ID (Unik) <span class="text-rose-500">*</span></label>
                    <input type="text" name="device_id" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: ESP32-LAB-01">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nama / Lokasi Scanner <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_device" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: RFID Scanner Lab Komputer 2">
                </div>
                <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    API Key rahasia akan otomatis digenerate untuk autentikasi mikrokontroler.
                </p>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection


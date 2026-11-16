@extends('layouts.app')

@section('title', 'Detail Peminjaman #' . str_pad($peminjaman->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Pengajuan Peminjaman</h1>
                <span class="font-mono text-sm font-semibold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg">#{{ str_pad($peminjaman->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Diajukan pada: {{ $peminjaman->tanggal_pengajuan->format('d F Y, H:i') }} WIB</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ url()->previous() }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Status & Overview Card -->
    <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Status Pengajuan</span>
                <span class="inline-flex mt-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider
                    @if($peminjaman->status === 'menunggu_persetujuan') bg-amber-50 text-amber-700 border border-amber-200
                    @elseif($peminjaman->status === 'disetujui') bg-emerald-50 text-emerald-800 border border-emerald-200
                    @elseif($peminjaman->status === 'ditolak') bg-rose-50 text-rose-700 border border-rose-200
                    @elseif($peminjaman->status === 'sedang_dipinjam') bg-teal-50 text-teal-800 border border-teal-200
                    @elseif($peminjaman->status === 'dikembalikan') bg-emerald-100/70 text-emerald-900 border border-emerald-300
                    @else bg-red-50 text-red-700 border border-red-200 @endif">
                    {{ str_replace('_', ' ', $peminjaman->status) }}
                </span>
            </div>

            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Tanggal Peminjaman</span>
                <div class="mt-1 text-sm font-bold text-slate-800">{{ $peminjaman->tanggal_pinjam->format('d M Y') }}</div>
            </div>

            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Batas Pengembalian</span>
                <div class="mt-1 text-sm font-bold text-rose-600">{{ $peminjaman->tanggal_harus_kembali->format('d M Y') }}</div>
            </div>

            <div>
                <span class="text-xs font-bold text-emerald-900/60 uppercase tracking-wider block">Aktual Kembali</span>
                <div class="mt-1 text-sm font-bold text-slate-700">
                    {{ $peminjaman->tanggal_aktual_kembali ? $peminjaman->tanggal_aktual_kembali->format('d M Y, H:i') : '-' }}
                </div>
            </div>
        </div>
    </div>

    @if($peminjaman->status === 'dikembalikan')
        <div class="p-4 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start space-x-3 shadow-sm">
            <i data-lucide="check-check" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0"></i>
            <div>
                <div class="font-bold text-sm">Keterangan: Barang Sudah Dikembalikan ke Sarpras</div>
                <div class="text-xs text-emerald-700 mt-0.5">
                    Seluruh unit alat telah diperiksa dan diterima kembali pada <strong>{{ $peminjaman->tanggal_aktual_kembali?->format('d F Y, H:i') }} WIB</strong>. Stok inventaris telah otomatis dipulihkan ke gudang/rak penyimpanan.
                </div>
            </div>
        </div>
    @endif

    <!-- Peminjam & Verifikator Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Peminjam Card -->
        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
            <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="user" class="w-4 h-4 text-emerald-600"></i>
                <span>Informasi Peminjam</span>
            </h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">Nama Lengkap:</span>
                    <span class="font-bold text-slate-800">{{ $peminjaman->user->nama }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">Role / Peran:</span>
                    <span class="font-bold uppercase text-xs px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">{{ $peminjaman->user->role }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">NIS / NIP:</span>
                    <span class="font-mono text-slate-800">{{ $peminjaman->user->nomor_identitas ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">Kelas / Jabatan:</span>
                    <span class="text-slate-800">{{ $peminjaman->user->kelas_atau_jabatan ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">UID RFID Terdaftar:</span>
                    <span class="font-mono text-xs bg-emerald-50 px-2 py-0.5 rounded-lg font-bold text-emerald-800 border border-emerald-200">{{ $peminjaman->user->rfid_uid ?? 'Tidak Ada' }}</span>
                </div>
            </div>
        </div>

        <!-- Approval Card -->
        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
            <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Verifikasi Sarpras</span>
            </h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">Petugas Approval:</span>
                    <span class="font-bold text-slate-800">{{ $peminjaman->approvedBy?->nama ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-emerald-50">
                    <span class="text-slate-500">Waktu Approval:</span>
                    <span class="text-slate-700">{{ $peminjaman->tanggal_approval ? $peminjaman->tanggal_approval->format('d M Y, H:i') : '-' }}</span>
                </div>
                <div class="py-1">
                    <span class="text-slate-500 block mb-1">Catatan Verifikator:</span>
                    <div class="bg-emerald-50/50 p-3 rounded-2xl border border-emerald-100 text-xs text-emerald-900 italic">
                        {{ $peminjaman->catatan_approval ?? 'Tidak ada catatan.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Item Alat Card -->
    <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
        <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider mb-4 flex items-center space-x-2">
            <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
            <span>Daftar Item Alat Dipinjam</span>
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                        <th class="py-3 px-4">Kode & Nama Alat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Jumlah</th>
                        <th class="py-3 px-4">Kondisi Awal</th>
                        <th class="py-3 px-4">Kondisi Pengembalian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($peminjaman->details as $d)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $d->alat->nama_alat }}</div>
                                <div class="text-xs font-mono text-slate-400">{{ $d->alat->kode_alat }} | Lokasi: {{ $d->alat->lokasi_penyimpanan ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $d->alat->kategori->nama_kategori ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $d->jumlah }} unit
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                    {{ $d->kondisi_saat_pinjam }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($d->kondisi_saat_kembali)
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 uppercase">
                                        {{ $d->kondisi_saat_kembali }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs italic">Belum dikembalikan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection


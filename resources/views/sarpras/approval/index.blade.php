@extends('layouts.app')

@section('title', 'Approval & Verifikasi Sarpras')

@section('content')
<div class="space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    returnModalOpen: false,
    selectedPeminjaman: null,
    actionUrl: '',

    openApprove(id, url) {
        this.selectedPeminjaman = id;
        this.actionUrl = url;
        this.approveModalOpen = true;
    },
    openReject(id, url) {
        this.selectedPeminjaman = id;
        this.actionUrl = url;
        this.rejectModalOpen = true;
    },
    openReturn(id, url) {
        this.selectedPeminjaman = id;
        this.actionUrl = url;
        this.returnModalOpen = true;
    }
}">

    <!-- Page Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900 p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 mb-3">
                <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                Wewenang Staff & Wakasek Sarpras
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Approval & Verifikasi Peminjaman</h1>
            <p class="text-sm text-emerald-100/80 mt-1 max-w-xl">
                Tinjau permohonan pinjam alat sekolah, berikan persetujuan atau penolakan, serta konfirmasi serah-terima dan pengembalian barang fisik.
            </p>
        </div>
        <div class="relative z-10 shrink-0 flex flex-wrap items-center gap-3">
            <a href="{{ route('sarpras.peminjaman.direct.create') }}"
               class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-emerald-950 font-bold px-4 py-2.5 rounded-2xl text-sm shadow-lg shadow-emerald-950/20 transition transform active:scale-95">
                <i data-lucide="handshake" class="w-4 h-4"></i>
                <span>+ Pinjam Langsung (Tatap Muka)</span>
            </a>
            <a href="{{ route('sarpras.laporan.cetak') }}" target="_blank"
               class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2.5 rounded-2xl font-semibold text-sm transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Rekap Sarpras</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-emerald-100 pb-3 text-xs font-semibold">
        <a href="{{ route('sarpras.dashboard') }}"
           class="px-3.5 py-2 rounded-xl transition {{ empty($status) ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm' : 'bg-white text-emerald-950 hover:bg-emerald-50 border border-emerald-100' }}">
            Semua Pengajuan
        </a>
        <a href="{{ route('sarpras.dashboard', ['status' => 'menunggu_persetujuan']) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 {{ $status === 'menunggu_persetujuan' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-amber-900 hover:bg-amber-50 border border-amber-200' }}">
            <span>Menunggu Persetujuan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'menunggu_persetujuan' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $counts['menunggu'] }}</span>
        </a>
        <a href="{{ route('sarpras.dashboard', ['status' => 'disetujui']) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 {{ $status === 'disetujui' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-emerald-900 hover:bg-emerald-50 border border-emerald-200' }}">
            <span>Disetujui (Siap Ambil)</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'disetujui' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $counts['disetujui'] }}</span>
        </a>
        <a href="{{ route('sarpras.dashboard', ['status' => 'sedang_dipinjam']) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 {{ $status === 'sedang_dipinjam' ? 'bg-teal-700 text-white shadow-sm' : 'bg-white text-teal-900 hover:bg-teal-50 border border-teal-200' }}">
            <span>Sedang Dipinjam</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'sedang_dipinjam' ? 'bg-white/20 text-white' : 'bg-teal-100 text-teal-800' }}">{{ $counts['sedang_dipinjam'] }}</span>
        </a>
        <a href="{{ route('sarpras.dashboard', ['status' => 'dikembalikan']) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5 {{ $status === 'dikembalikan' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-900 hover:bg-emerald-50 border border-emerald-200' }}">
            <span>Selesai Dikembalikan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'dikembalikan' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $counts['dikembalikan'] }}</span>
        </a>
        <a href="{{ route('sarpras.dashboard', ['status' => 'ditolak']) }}"
           class="px-3.5 py-2 rounded-xl transition {{ $status === 'ditolak' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-900 hover:bg-rose-50 border border-rose-200' }}">
            Ditolak ({{ $counts['ditolak'] }})
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-emerald-50/50 border-b border-emerald-100 text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider">
                        <th class="px-6 py-4">No & Tanggal</th>
                        <th class="px-6 py-4">Peminjam</th>
                        <th class="px-6 py-4">Daftar Alat</th>
                        <th class="px-6 py-4">Jadwal Pinjam</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Tindakan / Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse($peminjamanList as $p)
                        <tr class="hover:bg-emerald-50/40 transition">
                            <td class="px-6 py-4 font-mono font-bold text-emerald-950">
                                #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                <div class="text-[11px] text-slate-400 font-sans font-normal">{{ $p->tanggal_pengajuan->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-emerald-950">{{ $p->user->nama }}</div>
                                <div class="text-xs text-slate-500">
                                    <span class="uppercase font-semibold text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-1.5 py-0.5 rounded">{{ $p->user->role }}</span>
                                    {{ $p->user->kelas_atau_jabatan ? '• ' . $p->user->kelas_atau_jabatan : '' }}
                                </div>
                                <div class="text-[11px] font-mono text-emerald-800/70 mt-0.5">RFID: {{ $p->user->rfid_uid ?? 'Belum ada kartu' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($p->details as $d)
                                        <li class="text-slate-800 text-xs font-medium">
                                            {{ $d->alat->nama_alat ?? 'Alat Dihapus' }}
                                            <span class="bg-emerald-50 text-emerald-800 border border-emerald-200 px-1.5 py-0.2 rounded font-bold">{{ $d->jumlah }} unit</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div><span class="text-slate-400">Mulai:</span> <span class="font-semibold text-emerald-950">{{ $p->tanggal_pinjam->format('d/m/Y') }}</span></div>
                                <div class="mt-0.5"><span class="text-slate-400">Batas:</span> <span class="font-semibold text-rose-600">{{ $p->tanggal_harus_kembali->format('d/m/Y') }}</span></div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeColors = [
                                        'menunggu_persetujuan' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sedang_dipinjam' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'dikembalikan' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'terlambat' => 'bg-red-50 text-red-700 border-red-200 font-bold',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeColors[$p->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ strtoupper(str_replace('_', ' ', $p->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5">
                                <a href="{{ route('peminjaman.show', $p) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                                    Detail
                                </a>

                                <!-- Skenario 1: Menunggu Persetujuan -> Setujui / Tolak -->
                                @if($p->status === 'menunggu_persetujuan')
                                    <button type="button" @click="openApprove('{{ $p->id }}', '{{ route('sarpras.peminjaman.approve', $p) }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm transition">
                                        Setujui
                                    </button>
                                    <button type="button" @click="openReject('{{ $p->id }}', '{{ route('sarpras.peminjaman.reject', $p) }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                        Tolak
                                    </button>
                                @endif

                                <!-- Skenario 2: Disetujui -> Konfirmasi Diambil / Tap RFID -->
                                @if($p->status === 'disetujui')
                                    <form method="POST" action="{{ route('sarpras.peminjaman.borrow', $p) }}" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Konfirmasi bahwa alat sudah diambil fisik oleh peminjam?')"
                                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition" title="Bisa otomatis via tap RFID meja">
                                            Ambil Alat
                                        </button>
                                    </form>
                                @endif

                                <!-- Skenario 3: Sedang Dipinjam -> Konfirmasi Pengembalian -->
                                @if(in_array($p->status, ['sedang_dipinjam', 'terlambat']))
                                    <button type="button" @click="openReturn('{{ $p->id }}', '{{ route('sarpras.peminjaman.return', $p) }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                                        Kembalikan Alat
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data peminjaman pada filter ini.
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

    <!-- Modal Setujui -->
    <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 space-y-4" @click.away="approveModalOpen = false">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                Setujui Pengajuan Peminjaman
            </h3>
            <p class="text-xs text-slate-500">Stok inventaris masing-masing alat akan otomatis dipotong saat Anda menyetujui pengajuan ini.</p>
            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-emerald-900 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan_approval" rows="3" class="w-full text-sm border border-emerald-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Misal: Harap dikembalikan sebelum jam 15:00..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-emerald-100">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md transition">Konfirmasi Setuju</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tolak -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 space-y-4" @click.away="rejectModalOpen = false">
            <h3 class="text-base font-bold text-rose-950 flex items-center gap-2">
                <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
                Tolak Pengajuan Peminjaman
            </h3>
            <p class="text-xs text-slate-500">Silakan masukkan alasan penolakan agar peminjam mendapatkan penjelasan yang jelas.</p>
            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-rose-900 uppercase mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="catatan_approval" rows="3" required class="w-full text-sm border border-rose-200 rounded-xl p-3 focus:ring-2 focus:ring-rose-500 focus:outline-none" placeholder="Misal: Alat sedang dalam jadwal pemeliharaan lab..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-rose-100">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-md transition">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pengembalian Alat -->
    <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 space-y-4" @click.away="returnModalOpen = false">
            <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                <i data-lucide="archive" class="w-5 h-5 text-emerald-600"></i>
                Konfirmasi Pengembalian Alat
            </h3>
            <p class="text-xs text-slate-500">Stok inventaris akan dikembalikan ke posisi semula di gudang/rak penyimpanan.</p>
            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 p-3 rounded-2xl">
                    Pastikan fisik alat telah dicek oleh petugas Sarpras dan dalam kondisi lengkap & baik.
                </p>
                <div class="flex justify-end space-x-2 pt-2 border-t border-emerald-100">
                    <button type="button" @click="returnModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md transition">Konfirmasi Alat Selesai Kembali</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

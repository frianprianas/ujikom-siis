<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Sarpras - SIIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; color: black !important; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-8 text-slate-800">

    <!-- Top Action (No Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <a href="{{ url()->previous() }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Dokumen Resmi (Ctrl + P)</span>
            </button>
        </div>
    </div>

    <!-- Halaman Dokumen A4 -->
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-xl border border-slate-200 print:border-none print:shadow-none print:p-0 space-y-6">
        
        <!-- KOP SURAT SEKOLAH & TRADEMARK LOGO -->
        <div class="flex items-center justify-between border-b-4 border-double border-slate-900 pb-4 gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIIS Baknus" class="w-20 h-20 object-contain shrink-0">
            <div class="text-center flex-1">
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-600">SMK BAKTI NUSANTARA</h2>
                <h1 class="text-xl font-black uppercase tracking-wider text-slate-900 mt-0.5">SISTEM INFORMASI INVENTARIS SEKOLAH (SIIS)</h1>
                <h3 class="text-sm font-extrabold uppercase text-emerald-800">Inventaris Sekolah Baknus — Bagian Sarana & Prasarana</h3>
                <p class="text-xs text-slate-500 mt-1">Dokumen Resmi Inventaris & Rekapitulasi Aset Sarpras • Terintegrasi SIIS</p>
            </div>
            <div class="w-20 hidden md:block"></div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="text-center space-y-1">
            <h2 class="text-base font-bold uppercase tracking-wide underline">LAPORAN REKAPITULASI PEMINJAMAN SARANA & PRASARANA</h2>
            <p class="text-xs text-slate-600">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        </div>

        <!-- TABEL DATA REKAPITULASI -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs border border-slate-900">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-900 font-bold uppercase">
                        <th class="p-2 border border-slate-900 text-center w-10">No</th>
                        <th class="p-2 border border-slate-900">Nama Peminjam</th>
                        <th class="p-2 border border-slate-900">Role / Kelas</th>
                        <th class="p-2 border border-slate-900">Alat & Kuantitas</th>
                        <th class="p-2 border border-slate-900 text-center">Tgl Pinjam - Kembali</th>
                        <th class="p-2 border border-slate-900 text-center">Status</th>
                        <th class="p-2 border border-slate-900">Verifikator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                    @forelse($loans as $index => $loan)
                        <tr>
                            <td class="p-2 border border-slate-900 text-center">{{ $index + 1 }}</td>
                            <td class="p-2 border border-slate-900 font-semibold">{{ $loan->user->nama }}</td>
                            <td class="p-2 border border-slate-900">{{ strtoupper($loan->user->role) }} ({{ $loan->user->kelas_atau_jabatan ?? '-' }})</td>
                            <td class="p-2 border border-slate-900">
                                @foreach($loan->details as $d)
                                    <div>• {{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }} unit)</div>
                                @endforeach
                            </td>
                            <td class="p-2 border border-slate-900 text-center whitespace-nowrap">
                                {{ $loan->tanggal_pinjam->format('d/m/Y') }} - {{ $loan->tanggal_harus_kembali->format('d/m/Y') }}
                            </td>
                            <td class="p-2 border border-slate-900 text-center font-bold uppercase">
                                {{ str_replace('_', ' ', $loan->status) }}
                            </td>
                            <td class="p-2 border border-slate-900">{{ $loan->approvedBy ? $loan->approvedBy->nama : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center border border-slate-900 italic">Tidak ada catatan transaksi peminjaman pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TANDA TANGAN PEJABAT RESMI -->
        <div class="pt-8 grid grid-cols-2 text-center text-xs">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Sekolah</p>
                <div class="h-20"></div>
                <p class="font-bold underline">Dr. H. Bambang Wijaya, M.M.</p>
                <p class="text-slate-500">NIP. 196803151994031004</p>
            </div>
            <div>
                <p>Kota Sekolah, {{ now()->format('d F Y') }}</p>
                <p class="font-bold">Wakil Kepala Sekolah Bidang Sarpras</p>
                <div class="h-20"></div>
                <p class="font-bold underline">Dra. Hj. Siti Rahma, M.Pd</p>
                <p class="text-slate-500">NIP. 197505122000032002</p>
            </div>
        </div>

    </div>

</body>
</html>


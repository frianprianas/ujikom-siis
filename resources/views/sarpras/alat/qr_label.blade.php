<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker Label QR Code - {{ $alat->kode_alat }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .label-card { box-shadow: none !important; border: 2px solid #000 !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6 flex flex-col items-center">

    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print w-full max-w-xl mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <a href="{{ route('alat.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center space-x-1">
            <span>&larr; Kembali ke Katalog</span>
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-xl shadow-md transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Label Stiker (Ctrl + P)</span>
            </button>
        </div>
    </div>

    <!-- Label Stiker Aset Inventaris (Ukuran Standar Label Stiker Fisik) -->
    <div class="label-card w-full max-w-md bg-white rounded-2xl p-6 border-2 border-slate-800 shadow-xl space-y-4">
        <!-- Header Stiker -->
        <div class="flex items-center justify-between border-b-2 border-slate-800 pb-3">
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SIIS" class="w-10 h-10 rounded-lg object-contain border border-slate-300 p-0.5">
                <div>
                    <h3 class="font-extrabold text-sm uppercase tracking-wider text-slate-900 leading-none">SIIS INVENTARIS</h3>
                    <span class="text-[10px] font-semibold text-emerald-800 uppercase tracking-widest block mt-0.5">Inventaris Sekolah Baknus</span>
                </div>
            </div>
            <span class="text-[10px] font-mono bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded font-bold uppercase border border-emerald-300">ASET TERVERIFIKASI</span>
        </div>

        <!-- Body: QR Code & Detail -->
        <div class="flex items-center space-x-4">
            <!-- QR Code Image -->
            <div class="p-2 border border-slate-300 rounded-xl bg-white shrink-0">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($alat->kode_alat) }}"
                     alt="QR Code {{ $alat->kode_alat }}"
                     class="w-28 h-28 object-contain">
            </div>

            <!-- Details -->
            <div class="space-y-1.5 flex-1 min-w-0 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Alat:</span>
                    <strong class="text-sm font-black text-slate-900 leading-tight block truncate">{{ $alat->nama_alat }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kode Inventaris:</span>
                    <span class="font-mono font-black text-blue-600 text-xs bg-blue-50 px-2 py-0.5 rounded inline-block">{{ $alat->kode_alat }}</span>
                </div>
                <div class="grid grid-cols-2 gap-1 text-[11px] pt-1">
                    <div>
                        <span class="text-slate-400 text-[10px] block">Kategori:</span>
                        <span class="font-semibold text-slate-700 truncate block">{{ $alat->kategori->nama_kategori ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block">Kondisi Fisik:</span>
                        <span class="font-bold uppercase text-emerald-700">{{ $alat->kondisi }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Stiker -->
        <div class="border-t border-slate-200 pt-2 flex items-center justify-between text-[10px] text-slate-500">
            <span>Lokasi: <strong>{{ $alat->lokasi_penyimpanan ?: 'Sarpras' }}</strong></span>
            <span class="italic">Scan menggunakan aplikasi SIIS Mobile</span>
        </div>
    </div>

</body>
</html>


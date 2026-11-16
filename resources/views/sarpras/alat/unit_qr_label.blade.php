<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker Label QR Unit - {{ $unit->kode_unit }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .label-card { box-shadow: none !important; border: 2px solid #000 !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6 flex flex-col items-center justify-center">

    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print w-full max-w-md mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-emerald-100">
        <a href="{{ route('alat.show', $unit->alat_id) }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 flex items-center space-x-1">
            <span>&larr; Kembali ke Rincian Unit</span>
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold text-xs rounded-xl shadow-md transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Label Unit (Ctrl + P)</span>
            </button>
        </div>
    </div>

    <!-- Label Stiker Unit Fisik Individual -->
    <div class="label-card w-full max-w-md bg-white rounded-2xl p-6 border-2 border-slate-900 shadow-xl space-y-4">
        <!-- Header Stiker -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SIIS" class="w-10 h-10 rounded-lg object-contain border border-slate-300 p-0.5">
                <div>
                    <h3 class="font-extrabold text-sm uppercase tracking-wider text-slate-900 leading-none">SIIS INVENTARIS</h3>
                    <span class="text-[10px] font-semibold text-emerald-800 uppercase tracking-widest block mt-0.5">Inventaris Sekolah Baknus</span>
                </div>
            </div>
            <span class="text-[10px] font-mono bg-emerald-50 text-emerald-800 px-2.5 py-1 rounded font-bold uppercase border border-emerald-300">
                UNIT INDIVIDUAL
            </span>
        </div>

        <!-- Body: QR Code & Detail -->
        <div class="flex items-center space-x-4">
            <!-- QR Code Image -->
            <div class="p-2 border border-slate-300 rounded-xl bg-white shrink-0">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($unit->kode_unit) }}"
                     alt="QR Code {{ $unit->kode_unit }}"
                     class="w-28 h-28 object-contain">
            </div>

            <!-- Details -->
            <div class="space-y-1.5 flex-1 min-w-0 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">ID / Kode Unit:</span>
                    <strong class="text-sm font-mono font-bold text-emerald-800 block truncate">{{ $unit->kode_unit }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alat / Spek:</span>
                    <span class="font-bold text-slate-900 block truncate">{{ $unit->alat->nama_alat }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kategori:</span>
                    <span class="text-slate-600 truncate block">{{ $unit->alat->kategori->nama_kategori ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kondisi & Status:</span>
                    <span class="inline-block font-semibold text-[11px] {{ $unit->kondisi === 'baik' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $unit->kondisi)) }} &bull; {{ ucfirst($unit->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer Stiker -->
        <div class="pt-3 border-t-2 border-slate-900 flex items-center justify-between text-[10px] text-slate-500 font-mono">
            <span>Rak: {{ $unit->lokasi_spesifik ?: ($unit->alat->lokasi_penyimpanan ?: 'Gudang') }}</span>
            <span>SN: {{ $unit->nomor_seri ?: 'N/A' }}</span>
        </div>
    </div>

</body>
</html>


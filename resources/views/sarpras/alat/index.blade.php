@extends('layouts.app')

@section('title', 'Master Data Alat & Inventaris')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: '', kategori_id: '', nama_alat: '', kode_alat: '', kondisi: 'baik', stok_total: 1, stok_tersedia: 1, lokasi_penyimpanan: '' },
    openEdit(alat) {
        this.editData = { ...alat };
        this.editModalOpen = true;
    }
}">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventaris Alat Sekolah</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data seluruh aset, stok ketersediaan, kondisi fisik, dan lokasi rak sarpras.</p>
        </div>
        <div>
            <button type="button" @click="addModalOpen = true" class="inline-flex items-center space-x-2 bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm shadow-sm transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Tambah Alat Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('alat.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama alat, kode inventaris, atau lokasi..."
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div class="flex space-x-2">
                <select name="kategori_id" onchange="this.form.submit()" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
                @if($search || $kategoriId)
                    <a href="{{ route('alat.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama & Kode Alat</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Kondisi</th>
                        <th class="px-6 py-4">Stok (Tersedia / Total)</th>
                        <th class="px-6 py-4">Lokasi Penyimpanan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alatList as $alat)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $alat->nama_alat }}</div>
                                <div class="font-mono text-xs text-brand-600 bg-brand-50 px-2 py-0.5 rounded inline-block mt-0.5">{{ $alat->kode_alat }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ $alat->kategori->nama_kategori ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($alat->kondisi === 'baik')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Baik</span>
                                @elseif($alat->kondisi === 'rusak_ringan')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Rusak Ringan</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Rusak Berat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-sm {{ $alat->stok_tersedia > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $alat->stok_tersedia }}
                                    </span>
                                    <span class="text-xs text-slate-400">/ {{ $alat->stok_total }} unit</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                    @php $pct = $alat->stok_total > 0 ? ($alat->stok_tersedia / $alat->stok_total) * 100 : 0; @endphp
                                    <div class="bg-brand-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $alat->lokasi_penyimpanan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <button type="button" @click="openEdit({{ Js::from($alat) }})"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    Edit
                                </button>
                                <form action="{{ route('alat.destroy', $alat) }}" method="POST" class="inline" onsubmit="return confirm('Hapus alat inventaris ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data alat inventaris yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alatList->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $alatList->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Alat -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4" @click.away="addModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900">Tambah Alat Inventaris Baru</h3>
            <form action="{{ route('alat.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_alat" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: LCD Proyektor EPSON">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kode Alat (Unik) <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_alat" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: PRJ-002">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <select name="kategori_id" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="" disabled selected>Pilih Kategori</option>
                            @foreach($kategoris as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                        <select name="kondisi" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Total <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_total" min="1" value="1" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Tersedia <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_tersedia" min="0" value="1" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi_penyimpanan" class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: Rak A-2 Gudang Sarpras">
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Simpan Alat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Alat -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4" @click.away="editModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900">Edit Data Alat</h3>
            <form :action="`{{ url('alat') }}/${editData.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_alat" x-model="editData.nama_alat" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kode Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_alat" x-model="editData.kode_alat" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <select name="kategori_id" x-model="editData.kategori_id" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            @foreach($kategoris as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                        <select name="kondisi" x-model="editData.kondisi" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Total <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_total" x-model="editData.stok_total" min="1" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Tersedia <span class="text-rose-500">*</span></label>
                        <input type="number" name="stok_tersedia" x-model="editData.stok_tersedia" min="0" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi_penyimpanan" x-model="editData.lokasi_penyimpanan" class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection


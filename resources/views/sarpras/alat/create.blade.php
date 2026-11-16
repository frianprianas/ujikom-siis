@extends('layouts.app')

@section('title', 'Tambah Alat Inventaris')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Alat Inventaris Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Daftarkan aset atau perlengkapan baru ke dalam sistem sarpras.</p>
        </div>
        <a href="{{ route('alat.index') }}" class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-3.5 py-2 rounded-xl hover:bg-slate-50">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('alat.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_alat" value="{{ old('nama_alat') }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: LCD Proyektor EPSON EB-X400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kode Alat (Unik) <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_alat" value="{{ old('kode_alat') }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: PRJ-EPS-005">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                    <select name="kondisi" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="baik" {{ old('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Total <span class="text-rose-500">*</span></label>
                    <input type="number" name="stok_total" min="1" value="{{ old('stok_total', 1) }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan') }}" class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500" placeholder="Misal: Gudang Sarpras Lt. 1 Rak B-3">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-2">
                <a href="{{ route('alat.index') }}" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Simpan Data Alat</button>
            </div>
        </form>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Edit Alat - ' . $alat->nama_alat)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Alat Inventaris</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui rincian, kondisi, atau kuantitas total unit alat.</p>
        </div>
        <a href="{{ route('alat.index') }}" class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-3.5 py-2 rounded-xl hover:bg-slate-50">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('alat.update', $alat) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nama Alat <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_alat" value="{{ old('nama_alat', $alat->nama_alat) }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kode Alat (Unik) <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_alat" value="{{ old('kode_alat', $alat->kode_alat) }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id', $alat->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kondisi Fisik <span class="text-rose-500">*</span></label>
                    <select name="kondisi" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="baik" {{ old('kondisi', $alat->kondisi) == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi', $alat->kondisi) == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi', $alat->kondisi) == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Stok Total <span class="text-rose-500">*</span></label>
                    <input type="number" name="stok_total" min="1" value="{{ old('stok_total', $alat->stok_total) }}" required class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan', $alat->lokasi_penyimpanan) }}" class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-2">
                <a href="{{ route('alat.index') }}" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Perbarui Alat</button>
            </div>
        </form>
    </div>
</div>
@endsection


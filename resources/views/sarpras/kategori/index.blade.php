@extends('layouts.app')

@section('title', 'Manajemen Kategori Inventaris')

@section('content')
<div class="space-y-6" x-data="{
    addModalOpen: false,
    editModalOpen: false,
    editData: { id: '', nama_kategori: '', deskripsi: '' },
    openEdit(item) {
        this.editData = { ...item };
        this.editModalOpen = true;
    }
}">

    <!-- Page Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900 p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 mb-3">
                <i data-lucide="folder-tree" class="w-3.5 h-3.5"></i>
                Modul Sarana & Prasarana
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kategori Inventaris</h1>
            <p class="text-sm text-emerald-100/80 mt-1 max-w-xl">
                Kelompokkan alat dan fasilitas sekolah ke dalam kategori terstruktur untuk kemudahan pencarian dan pendataan.
            </p>
        </div>
        <div class="relative z-10 shrink-0">
            <button type="button" @click="addModalOpen = true"
                    class="inline-flex items-center space-x-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white px-5 py-3 rounded-2xl font-semibold text-sm shadow-lg shadow-emerald-900/30 transition transform active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Tambah Kategori Baru</span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white/95 rounded-3xl shadow-sm border border-emerald-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-100/70 bg-emerald-50/30 flex items-center justify-between">
            <h3 class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
                Daftar Kategori Tersedia ({{ $kategoriList->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-emerald-50/50 border-b border-emerald-100 text-[11px] font-bold text-emerald-900/70 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama Kategori</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">Jumlah Alat</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse($kategoriList as $kategori)
                        <tr class="hover:bg-emerald-50/40 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0 border border-emerald-200">
                                        {{ strtoupper(substr($kategori->nama_kategori, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-emerald-950 block">{{ $kategori->nama_kategori }}</span>
                                        <span class="text-[11px] text-slate-400">ID: #{{ $kategori->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs max-w-md">
                                {{ $kategori->deskripsi ?? 'Tidak ada deskripsi' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <i data-lucide="package" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    {{ $kategori->alat_count }} Alat Terdaftar
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5">
                                <button type="button" @click="openEdit({{ Js::from($kategori) }})"
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                                    Edit
                                </button>
                                <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Data alat yang terhubung akan terpengaruh.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Belum ada kategori yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kategoriList->hasPages())
            <div class="p-4 border-t border-emerald-100 bg-emerald-50/20">
                {{ $kategoriList->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Kategori -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 space-y-4" @click.away="addModalOpen = false">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                    <i data-lucide="folder-plus" class="w-5 h-5 text-emerald-600"></i>
                    Tambah Kategori Baru
                </h3>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('kategori.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-emerald-900 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kategori" required class="w-full text-sm border border-emerald-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Misal: Elektronik & Multimedia">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-emerald-900 mb-1">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" rows="3" class="w-full text-sm border border-emerald-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Keterangan singkat mengenai kategori..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-emerald-100">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/20 transition">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kategori -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-emerald-950/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 space-y-4" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                    <i data-lucide="edit" class="w-5 h-5 text-emerald-600"></i>
                    Edit Data Kategori
                </h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form :action="`{{ url('kategori') }}/${editData.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold uppercase text-emerald-900 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kategori" x-model="editData.nama_kategori" required class="w-full text-sm border border-emerald-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-emerald-900 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" x-model="editData.deskripsi" rows="3" class="w-full text-sm border border-emerald-200 rounded-xl p-3 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-emerald-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/20 transition">Perbarui Kategori</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

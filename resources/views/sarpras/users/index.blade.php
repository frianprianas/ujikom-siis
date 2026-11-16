@extends('layouts.app')

@section('title', 'Manajemen Akun & Kartu RFID')

@section('content')
<div class="space-y-6" x-data="{
    rfidModalOpen: false,
    selectedUser: { id: '', nama: '', role: '', rfid_uid: '', nomor_identitas: '', kelas_atau_jabatan: '' },
    openRfidModal(user) {
        this.selectedUser = { ...user };
        this.rfidModalOpen = true;
    }
}">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Akun Peminjam & Kartu RFID</h1>
            <p class="text-sm text-slate-500 mt-1">Daftarkan dan perbarui UID kartu RFID siswa atau guru untuk otentikasi tap di meja Sarpras.</p>
        </div>
        <div>
            <a href="{{ route('sarpras.iot.simulator') }}" class="inline-flex items-center space-x-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 px-4 py-2.5 rounded-xl font-medium text-sm transition">
                <i data-lucide="cpu" class="w-4 h-4"></i>
                <span>Uji Coba di Simulator</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('sarpras.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, NIS/NIP, atau UID kartu RFID..."
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div class="flex space-x-2">
                <select name="role" onchange="this.form.submit()" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 bg-white">
                    <option value="">Semua Peran / Role</option>
                    <option value="siswa" {{ $role === 'siswa' ? 'selected' : '' }}>Siswa</option>
                    <option value="guru" {{ $role === 'guru' ? 'selected' : '' }}>Guru</option>
                    <option value="staff_sarpras" {{ $role === 'staff_sarpras' ? 'selected' : '' }}>Staff Sarpras</option>
                    <option value="wakasek_sarpras" {{ $role === 'wakasek_sarpras' ? 'selected' : '' }}>Wakasek Sarpras</option>
                    <option value="kepala_sekolah" {{ $role === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                </select>
                @if($search || $role)
                    <a href="{{ route('sarpras.users.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition" title="Reset filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Users -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama & Email</th>
                        <th class="px-6 py-4">Peran</th>
                        <th class="px-6 py-4">Nomor Identitas (NIS/NIP)</th>
                        <th class="px-6 py-4">Kelas / Jabatan</th>
                        <th class="px-6 py-4">Status Kartu RFID</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $u->nama }}</div>
                                <div class="text-xs text-slate-400">{{ $u->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                    @if($u->role === 'siswa') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($u->role === 'guru') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($u->role === 'staff_sarpras') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($u->role === 'wakasek_sarpras') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @else bg-purple-50 text-purple-700 border border-purple-200 @endif">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 font-semibold">
                                {{ $u->nomor_identitas ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $u->kelas_atau_jabatan ?: '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($u->rfid_uid)
                                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>{{ $u->rfid_uid }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center space-x-1 text-xs text-slate-400 italic">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        <span>Belum Didaftarkan</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" @click="openRfidModal({{ Js::from($u) }})"
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition">
                                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                                    <span>Atur RFID</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Atur Kartu RFID -->
    <div x-show="rfidModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="rfidModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Atur Kartu RFID Peminjam</h3>
                <button @click="rfidModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="`{{ url('sarpras/users') }}/${selectedUser.id}/rfid`" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <div>Nama: <strong class="text-slate-900" x-text="selectedUser.nama"></strong></div>
                    <div class="mt-1">Peran: <strong class="uppercase text-brand-600" x-text="selectedUser.role"></strong></div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">UID Kartu RFID (Hex) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="rfid_uid" x-model="selectedUser.rfid_uid" required
                               placeholder="Misal: A1B2C3D4"
                               class="w-full pl-9 pr-4 py-2.5 font-mono text-sm uppercase rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Dapat discan langsung di meja sarpras atau diketik manual.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Nomor Identitas (NIS/NIP)</label>
                    <input type="text" name="nomor_identitas" x-model="selectedUser.nomor_identitas"
                           class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Kelas / Jabatan</label>
                    <input type="text" name="kelas_atau_jabatan" x-model="selectedUser.kelas_atau_jabatan"
                           class="w-full text-sm border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="rfidModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-medium text-white bg-brand-600 hover:bg-brand-700">Simpan Kartu</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

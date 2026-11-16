@extends('layouts.app')

@section('title', 'Pelayanan Peminjaman Langsung (Meja Sarpras)')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
         peminjamList: {{ Js::from($peminjamList) }},
         availableAlat: {{ Js::from($alatTersedia) }},
         
         // Form Fields
         selectedUserId: '{{ $selectedUserId ?? '' }}',
         userSearch: '',
         tanggalPinjam: '{{ old('tanggal_pinjam', date('Y-m-d')) }}',
         tanggalKembali: '{{ old('tanggal_harus_kembali', date('Y-m-d', strtotime('+3 days'))) }}',
         keperluan: '{{ old('keperluan', '') }}',

         // Selected Items / Keranjang Serah Terima
         cart: [],

         // Item & Unit Picker State
         selectedAlatId: '{{ $selectedAlatId ?? '' }}',
         selectedUnitId: '',

         get selectedUser() {
             return this.peminjamList.find(u => u.id == this.selectedUserId) || null;
         },

         get currentAlat() {
             return this.availableAlat.find(a => a.id == this.selectedAlatId) || null;
         },

         get availableUnitsForSelectedAlat() {
             if (!this.currentAlat || !this.currentAlat.units) return [];
             // Filter unit yang belum dimasukkan ke keranjang
             const chosenUnitIds = this.cart.map(c => c.unit_alat_id);
             return this.currentAlat.units.filter(u => !chosenUnitIds.includes(u.id));
         },

         addUnitToCart(unit = null) {
             const targetUnit = unit || this.availableUnitsForSelectedAlat.find(u => u.id == this.selectedUnitId);
             if (!targetUnit || !this.currentAlat) return;

             // Cek duplikasi
             if (this.cart.some(c => c.unit_alat_id == targetUnit.id)) {
                 alert(`Unit ${targetUnit.kode_unit} sudah ada di dalam daftar.`);
                 return;
             }

             this.cart.push({
                 alat_id: this.currentAlat.id,
                 unit_alat_id: targetUnit.id,
                 nama_alat: this.currentAlat.nama_alat,
                 kode_alat: this.currentAlat.kode_alat,
                 kategori: this.currentAlat.kategori ? this.currentAlat.kategori.nama_kategori : '-',
                 kode_unit: targetUnit.kode_unit,
                 nomor_seri: targetUnit.nomor_seri || '-',
                 kondisi: targetUnit.kondisi,
                 lokasi: targetUnit.lokasi_spesifik || this.currentAlat.lokasi_penyimpanan || 'Ruang Sarpras',
                 catatan: targetUnit.catatan || ''
             });

             this.selectedUnitId = '';
         },

         addGenericAlatToCart() {
             if (!this.currentAlat) return;
             // Jika alat memiliki unit fisik, ambil unit pertama yang tersedia
             if (this.availableUnitsForSelectedAlat.length > 0) {
                 this.addUnitToCart(this.availableUnitsForSelectedAlat[0]);
                 return;
             }

             // Fallback jika alat tidak memiliki unit fisik
             this.cart.push({
                 alat_id: this.currentAlat.id,
                 unit_alat_id: null,
                 nama_alat: this.currentAlat.nama_alat,
                 kode_alat: this.currentAlat.kode_alat,
                 kategori: this.currentAlat.kategori ? this.currentAlat.kategori.nama_kategori : '-',
                 kode_unit: this.currentAlat.kode_alat + ' (Standar)',
                 nomor_seri: '-',
                 kondisi: this.currentAlat.kondisi,
                 lokasi: this.currentAlat.lokasi_penyimpanan || 'Ruang Sarpras',
                 catatan: ''
             });
         },

         removeFromCart(index) {
             this.cart.splice(index, 1);
         },

         get totalUnitAlat() {
             return this.cart.length;
         },

         get isFormValid() {
             return this.selectedUserId !== '' && 
                    this.tanggalPinjam !== '' && 
                    this.tanggalKembali !== '' && 
                    this.tanggalKembali >= this.tanggalPinjam && 
                    this.cart.length > 0;
         }
     }">

    <!-- Page Header Banner -->
    <div class="rounded-3xl bg-gradient-to-r from-teal-800 via-emerald-800 to-teal-900 p-6 sm:p-8 text-white shadow-xl shadow-teal-950/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-teal-500/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-400/20 text-teal-200 border border-teal-300/30 mb-3">
                <i data-lucide="handshake" class="w-3.5 h-3.5"></i>
                Layanan Tatap Muka Meja Sarpras
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Pelayanan Peminjaman Langsung</h1>
            <p class="text-sm text-teal-100/80 mt-1 max-w-xl">
                Catat data peminjam di tempat, pilih kode spesifik unit alat fisik yang diserahkan, dan proses pinjam langsung.
            </p>
        </div>
        <div class="relative z-10 shrink-0">
            <a href="{{ route('sarpras.dashboard') }}"
               class="inline-flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2.5 rounded-2xl font-semibold text-sm transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Main Form Container -->
    <form method="POST" action="{{ route('sarpras.peminjaman.direct.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Pemilihan Peminjam (Siswa / Guru di Meja Layanan) -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-50 pb-3">
                <h2 class="text-sm font-bold text-emerald-950 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">1</span>
                    <span>Identitas Peminjam (Siswa / Guru di Ruang Sarpras)</span>
                </h2>
                <span class="text-xs text-rose-500 font-semibold">* Wajib Dipilih</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">
                        Pilih Akun Peminjam <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" x-model="selectedUserId" required
                            class="w-full text-sm rounded-xl border border-slate-300 p-2.5 focus:ring-2 focus:ring-teal-500 bg-white">
                        <option value="">-- Pilih Siswa atau Guru --</option>
                        <optgroup label="Siswa">
                            <template x-for="u in peminjamList.filter(x => x.role === 'siswa')" :key="u.id">
                                <option :value="u.id" x-text="`${u.nama} (${u.kelas_atau_jabatan || 'Siswa'}) - NIS: ${u.nomor_identitas || '-'}`"></option>
                            </template>
                        </optgroup>
                        <optgroup label="Guru">
                            <template x-for="u in peminjamList.filter(x => x.role === 'guru')" :key="u.id">
                                <option :value="u.id" x-text="`${u.nama} (${u.kelas_atau_jabatan || 'Guru'}) - NIP: ${u.nomor_identitas || '-'}`"></option>
                            </template>
                        </optgroup>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Pilih nama siswa atau guru yang sedang dilayani di meja Sarpras.</span>
                </div>

                <!-- Preview Kartu Identitas Peminjam -->
                <div>
                    <div x-show="selectedUser" class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50/50 border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900" x-text="selectedUser ? selectedUser.role.toUpperCase() : ''"></span>
                            <span class="font-mono text-xs font-semibold text-teal-800" x-text="selectedUser ? 'RFID: ' + (selectedUser.rfid_uid || 'Belum Ada') : ''"></span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm mt-1.5" x-text="selectedUser ? selectedUser.nama : ''"></h4>
                        <div class="text-xs text-slate-600 mt-0.5">
                            <span x-text="selectedUser ? (selectedUser.role === 'guru' ? 'NIP: ' : 'NIS: ') + (selectedUser.nomor_identitas || '-') : ''"></span>
                            <span>•</span>
                            <span x-text="selectedUser ? selectedUser.kelas_atau_jabatan : ''"></span>
                        </div>
                    </div>
                    <div x-show="!selectedUser" class="h-full flex items-center justify-center p-4 rounded-2xl border-2 border-dashed border-slate-200 text-slate-400 text-xs text-center">
                        Pilih peminjam di sebelah kiri untuk melihat ringkasan identitas.
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Pemilihan Alat & Kode Unit Fisik Spesifik -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-50 pb-3">
                <h2 class="text-sm font-bold text-emerald-950 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">2</span>
                    <span>Pilih Alat & Kode Spesifik Unit Fisik yang Diserahkan</span>
                </h2>
                <span class="text-xs text-teal-700 font-semibold bg-teal-50 border border-teal-200 px-2.5 py-0.5 rounded-full">Unit Level Tracking</span>
            </div>

            <!-- Langkah Pemilihan: Pilih Alat Terlebih Dahulu, lalu Pilih Kode Unit Fisiknya -->
            <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Dropdown Model Alat -->
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">
                            A. Pilih Nama / Model Alat
                        </label>
                        <select x-model="selectedAlatId" @change="selectedUnitId = ''" class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white">
                            <option value="">-- Pilih Alat Tersedia --</option>
                            <template x-for="a in availableAlat" :key="a.id">
                                <option :value="a.id" x-text="`${a.nama_alat} [${a.kode_alat}] - Tersedia: ${a.stok_tersedia} unit`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Dropdown Unit Fisik Spesifik -->
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-bold uppercase text-teal-900 mb-1">
                            B. Kode Spesifik Unit Fisik
                        </label>
                        <select x-model="selectedUnitId" :disabled="!currentAlat || availableUnitsForSelectedAlat.length === 0"
                                class="w-full text-sm rounded-xl border border-teal-300 p-2.5 bg-white font-mono disabled:opacity-40">
                            <option value="">-- Pilih Kode Unit Fisik --</option>
                            <template x-for="u in availableUnitsForSelectedAlat" :key="u.id">
                                <option :value="u.id" x-text="`${u.kode_unit} (${u.nomor_seri ? u.nomor_seri + ' - ' : ''}Kondisi: ${u.kondisi})`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Tombol Tambahkan Unit -->
                    <div class="sm:col-span-2">
                        <button type="button" @click="addUnitToCart()" :disabled="!selectedUnitId && availableUnitsForSelectedAlat.length > 0"
                                class="w-full p-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-40 text-white font-bold text-xs shadow-sm transition">
                            + Serahkan Unit
                        </button>
                    </div>
                </div>

                <!-- Info Cepat Pilihan Unit Fisik -->
                <div x-show="currentAlat && availableUnitsForSelectedAlat.length > 0" class="pt-2 border-t border-emerald-100">
                    <span class="text-[11px] font-bold text-slate-600 uppercase block mb-1.5">Pilihan Cepat Unit Fisik Tersedia (Klik untuk Menyerahkan):</span>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="u in availableUnitsForSelectedAlat" :key="u.id">
                            <button type="button" @click="addUnitToCart(u)"
                                    class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-teal-50 border border-teal-200 hover:border-teal-400 text-xs font-mono text-teal-900 flex items-center space-x-1.5 shadow-2xs transition">
                                <i data-lucide="tag" class="w-3 h-3 text-teal-600"></i>
                                <span class="font-bold" x-text="u.kode_unit"></span>
                                <span class="text-[10px] text-slate-500" x-text="u.lokasi_spesifik ? '• ' + u.lokasi_spesifik : ''"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Daftar Unit yang Diserahkan (Keranjang Serah Terima) -->
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Daftar Fisik Barang yang Diserahkan ke Peminjam:</span>
                
                <div x-show="cart.length === 0" class="py-8 text-center border-2 border-dashed border-emerald-200 rounded-2xl p-6 text-slate-400 bg-white/40">
                    <i data-lucide="tag" class="w-8 h-8 mx-auto text-teal-300 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700">Belum ada unit fisik yang dimasukkan</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih nama alat dan kode unit fisik di atas untuk menambahkan ke daftar serah terima.</p>
                </div>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="p-3.5 rounded-2xl border border-teal-200 bg-teal-50/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-start sm:items-center space-x-3">
                            <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0" x-text="index + 1"></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-bold text-slate-900 text-sm" x-text="item.nama_alat"></h4>
                                    <!-- Badge Kode Unit Spesifik -->
                                    <span class="px-2 py-0.5 rounded-lg bg-teal-100 text-teal-900 border border-teal-300 font-mono text-xs font-extrabold flex items-center gap-1">
                                        <i data-lucide="barcode" class="w-3.5 h-3.5 text-teal-700"></i>
                                        <span x-text="'Kode Unit: ' + item.kode_unit"></span>
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-mono mt-1 flex flex-wrap items-center gap-2">
                                    <span x-text="'Model: ' + item.kode_alat"></span>
                                    <span>•</span>
                                    <span x-text="'SN: ' + item.nomor_seri"></span>
                                    <span>•</span>
                                    <span x-text="'Kondisi: ' + item.kondisi.toUpperCase()"></span>
                                    <span x-show="item.lokasi">•</span>
                                    <span x-show="item.lokasi" x-text="'Lokasi: ' + item.lokasi"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 self-end sm:self-center">
                            <span class="text-xs font-bold text-teal-800 bg-teal-100 px-2 py-1 rounded-lg">1 Unit Fisik</span>
                            <button type="button" @click="removeFromCart(index)" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus dari daftar">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" :name="`items[${index}][alat_id]`" :value="item.alat_id">
                            <input type="hidden" :name="`items[${index}][unit_alat_id]`" :value="item.unit_alat_id">
                            <input type="hidden" :name="`items[${index}][jumlah]`" value="1">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Jadwal Waktu & Keperluan -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-50 pb-3">
                <h2 class="text-sm font-bold text-emerald-950 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">3</span>
                    <span>Jadwal Pengembalian & Catatan Pelayanan</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Tanggal Mulai Pinjam</label>
                    <input type="date" name="tanggal_pinjam" x-model="tanggalPinjam" required
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Batas Waktu Pengembalian <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_harus_kembali" x-model="tanggalKembali" :min="tanggalPinjam" required
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Keperluan / Keterangan Peminjaman (Opsional)</label>
                    <input type="text" name="keperluan" x-model="keperluan" placeholder="Misal: Praktikum presentasi fisika di Ruang 204"
                           class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white">
                </div>
            </div>
        </div>

        <!-- 4. Tombol Submit & Ringkasan -->
        <div class="bg-gradient-to-r from-emerald-950 via-teal-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border border-teal-900">
            <div>
                <span class="text-xs font-semibold text-teal-300 uppercase tracking-wider block">Ringkasan Serah Terima</span>
                <div class="text-sm text-slate-300 mt-1">
                    Total: <strong class="text-emerald-400 font-extrabold text-base" x-text="totalUnitAlat"></strong> unit fisik spesifik diserahkan langsung.
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('sarpras.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-teal-800 text-xs font-semibold text-teal-200 hover:bg-teal-900/50 transition">
                    Batal
                </a>
                <button type="submit" :disabled="!isFormValid"
                        :class="!isFormValid ? 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700' : 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-lg shadow-teal-500/25'"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs transition flex items-center space-x-2">
                    <i data-lucide="handshake" class="w-4 h-4"></i>
                    <span>Proses Pinjam & Serahkan Barang Sekarang</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

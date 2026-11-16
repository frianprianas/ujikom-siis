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

         // Item Picker State
         selectedPickerAlatId: '{{ $selectedAlatId ?? '' }}',
         pickerJumlah: 1,

         init() {
             if (this.selectedPickerAlatId) {
                 const found = this.availableAlat.find(a => a.id == this.selectedPickerAlatId);
                 if (found) {
                     this.addToCart(found.id, 1);
                 }
             }
         },

         get selectedUser() {
             return this.peminjamList.find(u => u.id == this.selectedUserId) || null;
         },

         get filteredPeminjam() {
             if (!this.userSearch.trim()) return this.peminjamList;
             const q = this.userSearch.toLowerCase();
             return this.peminjamList.filter(u => 
                 u.nama.toLowerCase().includes(q) || 
                 (u.nomor_identitas && u.nomor_identitas.toLowerCase().includes(q)) ||
                 (u.rfid_uid && u.rfid_uid.toLowerCase().includes(q)) ||
                 (u.kelas_atau_jabatan && u.kelas_atau_jabatan.toLowerCase().includes(q))
             );
         },

         get currentPickerAlat() {
             return this.availableAlat.find(a => a.id == this.selectedPickerAlatId) || null;
         },

         addToCart(alatId, qty = 1) {
             const targetId = alatId || this.selectedPickerAlatId;
             if (!targetId) return;

             const alat = this.availableAlat.find(a => a.id == targetId);
             if (!alat) return;

             const existing = this.cart.find(i => i.alat_id == targetId);
             const maxStock = alat.stok_tersedia;

             if (existing) {
                 if (existing.jumlah + qty <= maxStock) {
                     existing.jumlah += qty;
                 } else {
                     existing.jumlah = maxStock;
                     alert(`Batas stok tersedia untuk '${alat.nama_alat}' adalah ${maxStock} unit.`);
                 }
             } else {
                 const amount = Math.min(qty, maxStock);
                 this.cart.push({
                     alat_id: alat.id,
                     nama_alat: alat.nama_alat,
                     kode_alat: alat.kode_alat,
                     kategori: alat.kategori ? alat.kategori.nama_kategori : '-',
                     kondisi: alat.kondisi,
                     lokasi: alat.lokasi_penyimpanan || 'Gudang Sarpras',
                     max_stok: maxStock,
                     jumlah: amount
                 });
             }

             this.selectedPickerAlatId = '';
             this.pickerJumlah = 1;
         },

         removeFromCart(index) {
             this.cart.splice(index, 1);
         },

         updateQty(index, delta) {
             const item = this.cart[index];
             const newQty = item.jumlah + delta;
             if (newQty >= 1 && newQty <= item.max_stok) {
                 item.jumlah = newQty;
             }
         },

         get totalUnitAlat() {
             return this.cart.reduce((sum, item) => sum + parseInt(item.jumlah || 0), 0);
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
                Catat data peminjam yang datang langsung ke ruang Sarpras, pilih alat yang dibutuhkan, dan serahkan fisik barang seketika.
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

        <!-- 1. Pemilihan Peminjam (Siswa / Guru yang datang tatap muka) -->
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
                    <span class="text-[11px] text-slate-400 mt-1 block">Pilih nama siswa atau guru yang sedang berdiri di hadapan Anda.</span>
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

        <!-- 2. Pemilihan Alat & Jumlah Unit -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-50 pb-3">
                <h2 class="text-sm font-bold text-emerald-950 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black">2</span>
                    <span>Pilih Alat Inventaris yang Diserahkan Fisik</span>
                </h2>
                <span class="text-xs text-slate-500 font-medium">Stok akan langsung dipotong</span>
            </div>

            <!-- Picker Bar -->
            <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-8">
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Pilih Alat Tersedia di Gudang</label>
                        <select x-model="selectedPickerAlatId" class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white">
                            <option value="">-- Pilih Alat Tersedia --</option>
                            <template x-for="a in availableAlat" :key="a.id">
                                <option :value="a.id" x-text="`${a.nama_alat} [${a.kode_alat}] - Tersedia: ${a.stok_tersedia} unit`"></option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1">Jumlah</label>
                        <input type="number" x-model.number="pickerJumlah" min="1" :max="currentPickerAlat ? currentPickerAlat.stok_tersedia : 1"
                               class="w-full text-sm rounded-xl border border-slate-300 p-2.5 bg-white text-center font-bold">
                    </div>

                    <div class="sm:col-span-2">
                        <button type="button" @click="addToCart()" :disabled="!selectedPickerAlatId"
                                class="w-full p-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-40 text-white font-bold text-xs shadow-sm transition">
                            + Masukkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Keranjang Alat -->
            <div class="space-y-2">
                <div x-show="cart.length === 0" class="py-8 text-center border-2 border-dashed border-emerald-200 rounded-2xl p-6 text-slate-400 bg-white/40">
                    <i data-lucide="package-search" class="w-8 h-8 mx-auto text-emerald-300 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700">Belum ada alat yang dimasukkan ke daftar serah terima</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih alat dari menu di atas lalu klik tombol "+ Masukkan".</p>
                </div>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="p-3.5 rounded-2xl border border-emerald-100 bg-white flex items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs" x-text="index + 1"></span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm" x-text="item.nama_alat"></h4>
                                <div class="text-[11px] text-slate-500 font-mono">
                                    <span class="text-emerald-700 font-semibold" x-text="item.kode_alat"></span>
                                    <span>• Lokasi: <span x-text="item.lokasi"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="flex items-center border border-emerald-200 rounded-xl overflow-hidden bg-emerald-50/40">
                                <button type="button" @click="updateQty(index, -1)" :disabled="item.jumlah <= 1"
                                        class="px-2.5 py-1 text-slate-600 hover:text-emerald-800 disabled:opacity-30">
                                    -
                                </button>
                                <span class="w-8 text-center font-bold text-xs text-emerald-950" x-text="item.jumlah"></span>
                                <button type="button" @click="updateQty(index, 1)" :disabled="item.jumlah >= item.max_stok"
                                        class="px-2.5 py-1 text-slate-600 hover:text-emerald-800 disabled:opacity-30">
                                    +
                                </button>
                            </div>
                            <span class="text-xs text-slate-500">unit</span>

                            <button type="button" @click="removeFromCart(index)" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" :name="`items[${index}][alat_id]`" :value="item.alat_id">
                            <input type="hidden" :name="`items[${index}][jumlah]`" :value="item.jumlah">
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
                    Total: <strong class="text-white" x-text="cart.length"></strong> jenis alat |
                    <strong class="text-emerald-400" x-text="totalUnitAlat"></strong> unit fisik diserahkan.
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

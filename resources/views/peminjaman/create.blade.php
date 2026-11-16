@extends('layouts.app')

@section('title', 'Pemesanan & Peminjaman Alat')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
         availableAlat: {{ Js::from($alatTersedia) }},
         initialAlatId: '{{ $selectedAlatId ?? '' }}',
         
         // Form Fields
         tanggalPinjam: '{{ old('tanggal_pinjam', date('Y-m-d')) }}',
         tanggalKembali: '{{ old('tanggal_harus_kembali', date('Y-m-d', strtotime('+3 days'))) }}',
         keperluan: '{{ old('keperluan', '') }}',

         // Selected Items / Keranjang
         cart: [],

         // Item Picker State
         selectedPickerAlatId: '',
         pickerJumlah: 1,

         init() {
             // Jika ada alat_id dari query string katalog, otomatis masukkan ke keranjang
             if (this.initialAlatId) {
                 const found = this.availableAlat.find(a => a.id == this.initialAlatId);
                 if (found) {
                     this.addToCart(found.id, 1);
                 }
             }
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
                     lokasi: alat.lokasi_penyimpanan || 'Sarpras',
                     max_stok: maxStock,
                     jumlah: amount
                 });
             }

             // Reset picker
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

         get totalJenisAlat() {
             return this.cart.length;
         },

         get totalUnitAlat() {
             return this.cart.reduce((sum, item) => sum + parseInt(item.jumlah || 0), 0);
         },

         get isFormValid() {
             return this.tanggalPinjam !== '' && 
                    this.tanggalKembali !== '' && 
                    this.tanggalKembali >= this.tanggalPinjam && 
                    this.cart.length > 0;
         }
     }">

    <!-- Page Header & Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('alat.index') }}" class="hover:text-brand-600 transition">Katalog Alat</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Formulir Booking</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Formulir Pengajuan Peminjaman Alat</h1>
            <p class="text-sm text-slate-500">Pilih alat inventaris, jadwalkan tanggal, dan ajukan ke staf sarpras sekolah.</p>
        </div>

        <a href="{{ route('alat.index') }}" class="inline-flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Katalog</span>
        </a>
    </div>

    <!-- Main Booking Form Container -->
    <form method="POST" action="{{ route('peminjaman.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Identitas Peminjam (Auto-filled Card) -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
            <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Identitas Akun Peminjam</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                    <span class="text-emerald-800/60 block mb-0.5 font-medium">Nama Lengkap</span>
                    <span class="text-sm font-bold text-slate-900">{{ auth()->user()->nama }}</span>
                </div>
                <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                    <span class="text-emerald-800/60 block mb-0.5 font-medium">Peran / Role</span>
                    <span class="text-sm font-bold uppercase text-emerald-800">{{ auth()->user()->role }}</span>
                </div>
                <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                    <span class="text-emerald-800/60 block mb-0.5 font-medium">Nomor Identitas (NIS/NIP)</span>
                    <span class="text-sm font-mono font-semibold text-slate-800">{{ auth()->user()->nomor_identitas ?: '-' }}</span>
                </div>
                <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                    <span class="text-emerald-800/60 block mb-0.5 font-medium">Kartu RFID Terdaftar</span>
                    <span class="text-sm font-mono font-semibold text-emerald-700">{{ auth()->user()->rfid_uid ?: 'Belum terdaftar' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Jadwal Peminjaman & Keperluan -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6">
            <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                <span>Jadwal Waktu & Keperluan</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">
                        Tanggal Pinjam <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_pinjam" x-model="tanggalPinjam" min="{{ date('Y-m-d') }}" required
                           class="w-full text-sm rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-emerald-500 bg-white">
                    <span class="text-[11px] text-slate-400 mt-1 block">Rencana mulai pengambilan alat di sarpras.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">
                        Rencana Tanggal Pengembalian <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_harus_kembali" x-model="tanggalKembali" :min="tanggalPinjam" required
                           class="w-full text-sm rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-emerald-500 bg-white">
                    <span class="text-[11px] text-slate-400 mt-1 block">Batas maksimal alat harus sudah dikembalikan ke sarpras.</span>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">
                        Catatan / Keperluan Peminjaman (Opsional)
                    </label>
                    <textarea name="keperluan" x-model="keperluan" rows="2"
                              class="w-full text-sm rounded-xl border border-slate-200 p-2.5 focus:ring-2 focus:ring-emerald-500"
                              placeholder="Misal: Untuk kegiatan praktikum Biologi bab mikroskop di Lab 2..."></textarea>
                </div>
            </div>
        </div>

        <!-- 3. Dynamic Item Selector (Keranjang Pinjam) -->
        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-sm shadow-emerald-950/5 border border-emerald-100 p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold text-emerald-900/70 uppercase tracking-wider flex items-center space-x-2">
                    <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
                    <span>Daftar Item Alat Yang Ingin Dipinjam</span>
                </h2>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-xl">
                    Tersedia <span x-text="availableAlat.length"></span> Alat
                </span>
            </div>

            <!-- Box Pemilih Item Baru -->
            <div class="bg-emerald-50/40 p-4 rounded-2xl border border-emerald-100 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Dropdown Pilih Alat -->
                    <div class="sm:col-span-8">
                        <label class="block text-[11px] font-bold text-emerald-900/70 uppercase mb-1">Pilih Alat Dari Inventaris</label>
                        <select x-model="selectedPickerAlatId"
                                class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Alat Yang Tersedia --</option>
                            <template x-for="alat in availableAlat" :key="alat.id">
                                <option :value="alat.id" x-text="`${alat.nama_alat} (${alat.kode_alat}) — Stok: ${alat.stok_tersedia}`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Input Kuantitas -->
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-emerald-900/70 uppercase mb-1">Jumlah</label>
                        <input type="number" x-model.number="pickerJumlah" min="1" :max="currentPickerAlat ? currentPickerAlat.stok_tersedia : 1"
                               class="w-full text-sm border border-slate-200 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-emerald-500 text-center font-bold">
                    </div>

                    <!-- Tombol Tambah ke Keranjang -->
                    <div class="sm:col-span-2">
                        <button type="button" @click="addToCart(selectedPickerAlatId, pickerJumlah)"
                                :disabled="!selectedPickerAlatId || pickerJumlah < 1"
                                :class="!selectedPickerAlatId ? 'bg-slate-200 cursor-not-allowed text-slate-400' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-sm shadow-emerald-600/20'"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>+ Masukkan</span>
                        </button>
                    </div>
                </div>

                <!-- Info Box Preview Alat yang dipilih saat ini -->
                <template x-if="currentPickerAlat">
                    <div class="p-3 bg-emerald-100/60 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="info" class="w-4 h-4 text-emerald-700 shrink-0"></i>
                            <span>Kategori: <strong x-text="currentPickerAlat.kategori ? currentPickerAlat.kategori.nama_kategori : '-'"></strong> | Lokasi: <strong x-text="currentPickerAlat.lokasi_penyimpanan || 'Gudang Sarpras'"></strong></span>
                        </div>
                        <div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-white text-emerald-800 border border-emerald-200" x-text="`Stok Maksimal: ${currentPickerAlat.stok_tersedia} unit`"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tabel / Daftar Item Dalam Keranjang Pinjaman -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Item Yang Dipilih (<span x-text="cart.length"></span> Item):</h3>

                <!-- Tampilan Kosong -->
                <div x-show="cart.length === 0" class="py-10 text-center border-2 border-dashed border-emerald-200 rounded-2xl p-6 text-slate-400 bg-white/40">
                    <i data-lucide="shopping-cart" class="w-10 h-10 mx-auto text-emerald-300 mb-2"></i>
                    <p class="text-sm font-semibold text-slate-700">Keranjang pinjaman masih kosong</p>
                    <p class="text-xs text-slate-400 mt-0.5">Silakan pilih alat dari dropdown di atas lalu klik tombol "+ Masukkan".</p>
                </div>

                <!-- Daftar Kartu Item Keranjang -->
                <div x-show="cart.length > 0" class="space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-4 rounded-2xl border border-emerald-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-emerald-300 transition shadow-sm shadow-emerald-950/5">
                            <div class="flex items-start space-x-3">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0" x-text="index + 1"></span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-tight" x-text="item.nama_alat"></h4>
                                    <div class="flex items-center space-x-2 text-xs text-slate-500 mt-1">
                                        <span class="font-mono text-emerald-700 font-semibold" x-text="item.kode_alat"></span>
                                        <span>•</span>
                                        <span x-text="item.kategori"></span>
                                        <span>•</span>
                                        <span>Kondisi: <strong class="uppercase text-slate-700" x-text="item.kondisi"></strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stepper Kuantitas & Hapus -->
                            <div class="flex items-center space-x-3 self-end sm:self-center">
                                <!-- Stepper Input -->
                                <div class="flex items-center border border-emerald-200 rounded-xl overflow-hidden bg-emerald-50/40">
                                    <button type="button" @click="updateQty(index, -1)"
                                            :disabled="item.jumlah <= 1"
                                            class="p-2 text-slate-600 hover:text-emerald-800 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <span class="w-10 text-center font-bold text-xs text-emerald-950" x-text="item.jumlah"></span>
                                    <button type="button" @click="updateQty(index, 1)"
                                            :disabled="item.jumlah >= item.max_stok"
                                            class="p-2 text-slate-600 hover:text-emerald-800 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <span class="text-xs text-slate-500 font-medium">unit</span>

                                <!-- Tombol Hapus -->
                                <button type="button" @click="removeFromCart(index)"
                                        class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition" title="Hapus dari daftar">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" :name="`items[${index}][alat_id]`" :value="item.alat_id">
                            <input type="hidden" :name="`items[${index}][jumlah]`" :value="item.jumlah">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 4. Ringkasan & Submit Bar -->
        <div class="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 rounded-3xl p-6 text-white shadow-xl shadow-emerald-950/10 space-y-4 border border-emerald-900">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-300/80 block">Ringkasan Pengajuan Peminjaman</span>
                    <div class="flex items-center space-x-4 mt-1">
                        <div>
                            <span class="text-2xl font-extrabold text-white" x-text="totalJenisAlat"></span>
                            <span class="text-xs text-emerald-200/70 ml-1">Jenis Alat</span>
                        </div>
                        <span class="text-emerald-800 font-light text-xl">|</span>
                        <div>
                            <span class="text-2xl font-extrabold text-emerald-400" x-text="totalUnitAlat"></span>
                            <span class="text-xs text-emerald-200/70 ml-1">Total Unit</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('alat.index') }}" class="px-5 py-2.5 rounded-xl border border-emerald-800 text-xs font-semibold text-emerald-200 hover:bg-emerald-900/60 transition">
                        Batal
                    </a>

                    <button type="submit"
                            :disabled="!isFormValid"
                            :class="!isFormValid ? 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700' : 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white shadow-lg shadow-emerald-500/25'"
                            class="px-6 py-2.5 rounded-xl font-bold text-xs transition flex items-center space-x-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Pengajuan Peminjaman</span>
                    </button>
                </div>
            </div>

            <!-- Validasi Warning Info -->
            <div x-show="!isFormValid" class="text-xs text-amber-200 bg-amber-500/10 border border-amber-500/20 p-3 rounded-2xl flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-amber-400"></i>
                <span>Pastikan tanggal pinjam, batas kembali telah terisi benar, dan minimal 1 item alat dimasukkan ke keranjang sebelum mengirim.</span>
            </div>
        </div>

    </form>

</div>
@endsection

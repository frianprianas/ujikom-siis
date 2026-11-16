@extends('layouts.app')

@section('title', 'Simulator Tap RFID ESP32')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    devices: {{ Js::from($devices) }},
    users: {{ Js::from($users) }},
    selectedDeviceId: 'ESP32-SARPRAS-01',
    apiKey: 'siis-rfid-secret-key-2026',
    rfidUid: 'A1B2C3D4',
    isLoading: false,
    responseResult: null,

    onDeviceChange() {
        const dev = this.devices.find(d => d.device_id === this.selectedDeviceId);
        if (dev) {
            this.apiKey = dev.api_key;
        }
    },

    pickUserCard(uid) {
        this.rfidUid = uid;
    },

    async simulateTap() {
        if (!this.rfidUid) {
            alert('Masukkan UID kartu RFID.');
            return;
        }

        this.isLoading = true;
        this.responseResult = null;

        try {
            const res = await fetch('{{ route('api.rfid.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_id: this.selectedDeviceId,
                    api_key: this.apiKey,
                    rfid_uid: this.rfidUid
                })
            });

            const data = await res.json();
            this.responseResult = {
                status_code: res.status,
                ok: res.ok,
                data: data
            };
        } catch (e) {
            this.responseResult = {
                status_code: 500,
                ok: false,
                data: { message: 'Koneksi ke endpoint gagal: ' + e.message }
            };
        } finally {
            this.isLoading = false;
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">IoT Hardware Simulator</span>
                <span class="text-xs text-slate-400">Endpoint: POST /api/v1/rfid/verify</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Simulasi Tap Kartu RFID Sarpras</h1>
            <p class="text-sm text-slate-500">Uji coba interaksi mikrokontroler ESP32 dan RFID RC522 langsung dari peramban web tanpa harus menyalakan hardware fisik.</p>
        </div>
        <div>
            <a href="{{ route('sarpras.iot.logs') }}" class="text-xs font-semibold text-slate-700 bg-white border border-slate-200 px-3.5 py-2 rounded-xl hover:bg-slate-50 transition">
                &larr; Lihat Audit Log
            </a>
        </div>
    </div>

    <!-- Simulator Form Box -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Pilihan Device -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">Perangkat Scanner ESP32</label>
                <select x-model="selectedDeviceId" @change="onDeviceChange()" class="w-full text-sm border border-slate-300 rounded-xl p-2.5 bg-white focus:ring-2 focus:ring-brand-500">
                    <template x-for="d in devices" :key="d.id">
                        <option :value="d.device_id" x-text="`${d.nama_device} (${d.device_id})`"></option>
                    </template>
                </select>
            </div>

            <!-- API Key -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">API Key Rahasia Perangkat</label>
                <input type="text" x-model="apiKey" class="w-full text-sm font-mono border border-slate-300 rounded-xl p-2.5 bg-slate-50">
            </div>
        </div>

        <!-- Quick Pick Kartu Pengguna -->
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-700 mb-2">Pilih Kartu RFID Siswa/Guru Untuk Di-Tap:</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                <template x-for="u in users" :key="u.id">
                    <button type="button" @click="pickUserCard(u.rfid_uid)"
                            :class="rfidUid === u.rfid_uid ? 'border-brand-500 bg-brand-50 text-brand-900 ring-2 ring-brand-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                            class="p-3 rounded-xl border text-left transition flex flex-col justify-between">
                        <div>
                            <span class="font-semibold block" x-text="u.nama"></span>
                            <span class="text-[11px] text-slate-500" x-text="`${u.role.toUpperCase()} • ${u.nomor_identitas || '-'}`"></span>
                        </div>
                        <div class="mt-2 font-mono text-[11px] font-bold text-brand-600 bg-white/70 px-2 py-0.5 rounded w-fit" x-text="`UID: ${u.rfid_uid}`"></div>
                    </button>
                </template>

                <!-- Tombol Kartu Tidak Dikenal -->
                <button type="button" @click="pickUserCard('99AABBCC')"
                        :class="rfidUid === '99AABBCC' ? 'border-rose-500 bg-rose-50 text-rose-900 ring-2 ring-rose-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        class="p-3 rounded-xl border text-left transition flex flex-col justify-between">
                    <div>
                        <span class="font-semibold block text-rose-700">Kartu Ilegal / Asing</span>
                        <span class="text-[11px] text-slate-500">Uji Coba Kartu Tidak Terdaftar</span>
                    </div>
                    <div class="mt-2 font-mono text-[11px] font-bold text-rose-600 bg-white/70 px-2 py-0.5 rounded w-fit">UID: 99AABBCC</div>
                </button>
            </div>
        </div>

        <!-- Input UID Manual & Tombol Tap -->
        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:flex-1">
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1">UID Kartu RFID Yang Ditempelkan (Hex)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </span>
                    <input type="text" x-model="rfidUid" placeholder="Misal: A1B2C3D4"
                           class="w-full pl-9 pr-4 py-2.5 font-mono text-sm uppercase rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <div class="w-full sm:w-auto pt-5">
                <button type="button" @click="simulateTap()" :disabled="isLoading"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm shadow-md shadow-indigo-500/20 transition flex items-center justify-center space-x-2">
                    <i data-lucide="radio" class="w-4 h-4"></i>
                    <span x-text="isLoading ? 'Memproses Sinyal...' : 'Tempelkan Kartu (Simulate Tap)'"></span>
                </button>
            </div>
        </div>

        <!-- Response Monitor Display -->
        <div x-show="responseResult" x-cloak class="pt-4 border-t border-slate-200 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Respons Server SIIS:</h3>
                <span class="font-mono text-xs px-2.5 py-0.5 rounded font-bold"
                      :class="responseResult && responseResult.ok ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                      x-text="`HTTP Status: ${responseResult ? responseResult.status_code : ''}`"></span>
            </div>

            <!-- Pesan Interaktif -->
            <div class="p-4 rounded-xl border text-sm"
                 :class="responseResult && responseResult.data.success ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900'">
                <div class="flex items-start space-x-3">
                    <i data-lucide="bell-ring" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <div class="font-bold text-base" x-text="responseResult ? responseResult.data.message : ''"></div>
                        <template x-if="responseResult && responseResult.data.peminjaman">
                            <div class="mt-2 text-xs space-y-1">
                                <div><strong>ID Peminjaman:</strong> #<span x-text="responseResult.data.peminjaman.id"></span></div>
                                <div><strong>Status Terbaru:</strong> <span class="uppercase font-bold" x-text="responseResult.data.peminjaman.status"></span></div>
                                <div><strong>Batas Waktu Pengembalian:</strong> <span x-text="responseResult.data.peminjaman.tanggal_harus_kembali"></span></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Raw JSON -->
            <div>
                <span class="text-[11px] text-slate-400 font-semibold uppercase block mb-1">Payload JSON Respons:</span>
                <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-xs font-mono overflow-x-auto leading-relaxed"
                     x-text="JSON.stringify(responseResult ? responseResult.data : {}, null, 2)"></pre>
            </div>
        </div>

    </div>

</div>
@endsection


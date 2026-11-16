<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlatRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki wewenang untuk request ini.
     * Khusus staff_sarpras.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'staff_sarpras';
    }

    /**
     * Dapatkan aturan validasi untuk penambahan data alat.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'nama_alat' => ['required', 'string', 'max:150'],
            'kode_alat' => ['required', 'string', 'max:100', 'unique:alat,kode_alat'],
            'kondisi' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'stok_total' => ['required', 'integer', 'min:1'],
            'lokasi_penyimpanan' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Pesan kustom validasi bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Kategori alat wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid di database.',
            'nama_alat.required' => 'Nama alat inventaris wajib diisi.',
            'nama_alat.max' => 'Nama alat tidak boleh lebih dari 150 karakter.',
            'kode_alat.required' => 'Kode unik alat inventaris wajib diisi.',
            'kode_alat.unique' => 'Kode alat ini sudah terdaftar pada inventaris lain.',
            'kondisi.required' => 'Kondisi fisik alat wajib ditentukan.',
            'kondisi.in' => 'Pilihan kondisi harus salah satu dari: baik, rusak_ringan, atau rusak_berat.',
            'stok_total.required' => 'Jumlah stok total alat wajib diisi.',
            'stok_total.integer' => 'Stok total harus berupa angka bilangan bulat.',
            'stok_total.min' => 'Stok total minimal berjumlah 1 unit.',
            'lokasi_penyimpanan.max' => 'Lokasi penyimpanan maksimal 150 karakter.',
        ];
    }
}

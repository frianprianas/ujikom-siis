<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeminjamanRequest extends FormRequest
{
    /**
     * Tentukan apakah user berwenang membuat pengajuan peminjaman.
     * Hanya role siswa dan guru.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['siswa', 'guru']);
    }

    /**
     * Dapatkan aturan validasi untuk pemesanan peminjaman alat.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_harus_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'integer', 'exists:alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
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
            'tanggal_pinjam.required' => 'Tanggal rencana pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
            'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lampau (minimal hari ini).',
            'tanggal_harus_kembali.required' => 'Tanggal batas pengembalian wajib diisi.',
            'tanggal_harus_kembali.date' => 'Format tanggal batas pengembalian tidak valid.',
            'tanggal_harus_kembali.after_or_equal' => 'Tanggal batas kembali tidak boleh mendahului tanggal pinjam.',
            'items.required' => 'Daftar item alat yang dipinjam wajib disertakan.',
            'items.array' => 'Format daftar item alat tidak valid.',
            'items.min' => 'Pilih minimal 1 item alat yang ingin dipinjam.',
            'items.*.alat_id.required' => 'ID alat wajib dipilih.',
            'items.*.alat_id.exists' => 'Alat yang dipilih tidak terdaftar pada inventaris.',
            'items.*.jumlah.required' => 'Jumlah unit yang dipinjam wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah unit harus berupa angka bilangan bulat.',
            'items.*.jumlah.min' => 'Jumlah unit peminjaman minimal 1 unit.',
        ];
    }
}

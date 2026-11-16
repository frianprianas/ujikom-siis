<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'nomor_identitas',
        'rfid_uid',
        'kelas_atau_jabatan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Alias agar kompatibel bila pemanggilan $user->name digunakan di template / library.
     */
    public function getNameAttribute(): string
    {
        return $this->nama;
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helper Methods (RBAC)
    |--------------------------------------------------------------------------
    */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isStaffSarpras(): bool
    {
        return $this->role === 'staff_sarpras';
    }

    public function isWakasekSarpras(): bool
    {
        return $this->role === 'wakasek_sarpras';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    /**
     * Role peminjam alat (Siswa & Guru).
     */
    public function isPeminjam(): bool
    {
        return in_array($this->role, ['siswa', 'guru']);
    }

    /**
     * Wewenang untuk ACC / menyetujui, menolak, dan verifikasi kembali alat
     * (Super Admin, Staff Sarpras, Wakasek Sarpras).
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['super_admin', 'staff_sarpras', 'wakasek_sarpras']);
    }

    /**
     * Wewenang untuk menambah, mengubah, menghapus data inventaris & kategori
     * (Super Admin, Staff Sarpras, Wakasek Sarpras).
     */
    public function canManageInventaris(): bool
    {
        return in_array($this->role, ['super_admin', 'staff_sarpras', 'wakasek_sarpras']);
    }

    /**
     * Wewenang setting data master: Akun & RFID pengguna, IoT ESP32, konfigurasi sistem
     * (Super Admin).
     */
    public function canManageMaster(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Role yang hanya memiliki hak akses view/monitoring laporan
     * (Kepala Sekolah).
     */
    public function isViewOnly(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'user_id');
    }

    public function peminjamanDisetujui(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'approved_by_id');
    }

    public function iotLogs(): HasMany
    {
        return $this->hasMany(IotLog::class, 'user_id');
    }
}

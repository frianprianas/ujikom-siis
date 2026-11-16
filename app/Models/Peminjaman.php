<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'tanggal_pengajuan',
        'tanggal_pinjam',
        'tanggal_harus_kembali',
        'tanggal_aktual_kembali',
        'status',
        'approved_by_id',
        'tanggal_approval',
        'catatan_approval',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            'tanggal_pinjam' => 'date',
            'tanggal_harus_kembali' => 'date',
            'tanggal_aktual_kembali' => 'datetime',
            'tanggal_approval' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helper Checks
    |--------------------------------------------------------------------------
    */
    public function isMenungguPersetujuan(): bool
    {
        return $this->status === 'menunggu_persetujuan';
    }

    public function isDisetujui(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isSedangDipinjam(): bool
    {
        return $this->status === 'sedang_dipinjam';
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    public function alat(): BelongsToMany
    {
        return $this->belongsToMany(Alat::class, 'detail_peminjaman', 'peminjaman_id', 'alat_id')
            ->withPivot(['id', 'jumlah', 'kondisi_saat_pinjam', 'kondisi_saat_kembali'])
            ->withTimestamps();
    }
}


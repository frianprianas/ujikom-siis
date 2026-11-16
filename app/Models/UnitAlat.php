<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitAlat extends Model
{
    use HasFactory;

    protected $table = 'unit_alat';

    protected $fillable = [
        'alat_id',
        'kode_unit',
        'nomor_seri',
        'kondisi',
        'status',
        'lokasi_spesifik',
        'catatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'unit_alat_id');
    }

    public function isTersedia(): bool
    {
        return $this->status === 'tersedia' && $this->kondisi !== 'rusak_berat';
    }

    public function isBaik(): bool
    {
        return $this->kondisi === 'baik';
    }

    public function isRusakRingan(): bool
    {
        return $this->kondisi === 'rusak_ringan';
    }

    public function isRusakBerat(): bool
    {
        return $this->kondisi === 'rusak_berat';
    }

    public function getKondisiBadgeAttribute(): array
    {
        return match ($this->kondisi) {
            'baik' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'label' => 'Baik'],
            'rusak_ringan' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label' => 'Rusak Ringan'],
            'rusak_berat' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'label' => 'Rusak Berat'],
            default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => $this->kondisi],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'tersedia' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'label' => 'Tersedia'],
            'dipinjam' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-800', 'border' => 'border-teal-200', 'label' => 'Sedang Dipinjam'],
            'perbaikan' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label' => 'Dalam Perbaikan'],
            'hilang' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200', 'label' => 'Hilang'],
            default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => $this->status],
        };
    }
}


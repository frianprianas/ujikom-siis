<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = [
        'kategori_id',
        'nama_alat',
        'kode_alat',
        'kondisi',
        'stok_total',
        'stok_tersedia',
        'lokasi_penyimpanan',
    ];

    protected function casts(): array
    {
        return [
            'stok_total' => 'integer',
            'stok_tersedia' => 'integer',
        ];
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('stok_tersedia', '>', 0);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(UnitAlat::class, 'alat_id')->orderBy('kode_unit');
    }

    /**
     * Sinkronisasi angka stok_total dan stok_tersedia berdasarkan data fisik di tabel unit_alat.
     */
    public function syncStockFromUnits(): void
    {
        $total = $this->units()->count();
        $tersedia = $this->units()
            ->where('status', 'tersedia')
            ->where('kondisi', '!=', 'rusak_berat')
            ->count();

        $this->update([
            'stok_total' => $total,
            'stok_tersedia' => $tersedia,
        ]);
    }

    /**
     * Otomatis membuat unit alat fisik berurutan (misal: PRJ-001-01, PRJ-001-02, ...).
     */
    public function generateUnits(int $count, string $kondisi = 'baik', ?string $lokasi = null): void
    {
        $currentMax = $this->units()->count();
        $baseCode = $this->kode_alat;

        for ($i = 1; $i <= $count; $i++) {
            $unitIndex = $currentMax + $i;
            $kodeUnit = sprintf('%s-%03d', $baseCode, $unitIndex);

            // Cek agar kode_unit tidak duplikat
            if (UnitAlat::where('kode_unit', $kodeUnit)->exists()) {
                $kodeUnit = sprintf('%s-%s-%03d', $baseCode, date('y'), $unitIndex);
            }

            $this->units()->create([
                'kode_unit' => $kodeUnit,
                'kondisi' => $kondisi,
                'status' => 'tersedia',
                'lokasi_spesifik' => $lokasi ?: $this->lokasi_penyimpanan,
            ]);
        }

        $this->syncStockFromUnits();
    }

    public function getUnitsBaikCountAttribute(): int
    {
        return $this->units()->where('kondisi', 'baik')->count();
    }

    public function getUnitsRusakRinganCountAttribute(): int
    {
        return $this->units()->where('kondisi', 'rusak_ringan')->count();
    }

    public function getUnitsRusakBeratCountAttribute(): int
    {
        return $this->units()->where('kondisi', 'rusak_berat')->count();
    }

    public function getUnitsTersediaCountAttribute(): int
    {
        return $this->units()->where('status', 'tersedia')->count();
    }

    public function getUnitsDipinjamCountAttribute(): int
    {
        return $this->units()->where('status', 'dipinjam')->count();
    }
}

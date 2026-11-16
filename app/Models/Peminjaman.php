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

    /**
     * Batalkan otomatis pemesanan/peminjaman 'menunggu_persetujuan' yang tidak ditindaklanjuti
     * ke petugas Sarpras melebihi batas waktu maksimal 1 jam (60 menit).
     * Pulihkan stok alat dan kembalikan status unit_alat menjadi 'tersedia'.
     */
    public static function cancelExpiredPendingLoans(): int
    {
        $cutoff = now()->subHour();

        $expiredLoans = self::with(['details.alat', 'details.unitAlat'])
            ->where('status', 'menunggu_persetujuan')
            ->where('tanggal_pengajuan', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($expiredLoans as $loan) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($loan) {
                foreach ($loan->details as $detail) {
                    // Pulihkan stok alat
                    $detail->alat?->increment('stok_tersedia', $detail->jumlah);

                    // Pulihkan unit fisik jika ada
                    if ($detail->unit_alat_id && $detail->unitAlat) {
                        $detail->unitAlat->update(['status' => 'tersedia']);
                    }
                }

                $cancelMsg = '[Batal Otomatis: Melebihi batas waktu 1 jam tanpa tindak lanjut ke ruang Sarpras]';
                $oldNote = $loan->catatan_approval;
                $newNote = $oldNote ? "{$oldNote} | {$cancelMsg}" : $cancelMsg;

                $loan->update([
                    'status' => 'ditolak',
                    'catatan_approval' => $newNote,
                ]);
            });
            $count++;
        }

        return $count;
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


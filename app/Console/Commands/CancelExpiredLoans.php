<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use Illuminate\Console\Command;

class CancelExpiredLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peminjaman:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan otomatis pengajuan peminjaman menunggu yang melebihi batas waktu 1 jam';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa pengajuan peminjaman yang kedaluwarsa (> 1 jam)...');

        $cancelled = Peminjaman::cancelExpiredPendingLoans();

        if ($cancelled > 0) {
            $this->warn("Berhasil membatalkan {$cancelled} peminjaman kedaluwarsa dan mengembalikan unit ke inventaris.");
        } else {
            $this->info('Tidak ada pengajuan peminjaman yang kedaluwarsa.');
        }

        return self::SUCCESS;
    }
}

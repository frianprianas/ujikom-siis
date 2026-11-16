<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use Illuminate\Console\Command;

class CheckOverdueLoansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siis:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa peminjaman yang melewati batas waktu pengembalian dan ubah statusnya menjadi terlambat';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $overdueLoans = Peminjaman::where('status', 'sedang_dipinjam')
            ->whereDate('tanggal_harus_kembali', '<', $today)
            ->get();

        $count = $overdueLoans->count();

        foreach ($overdueLoans as $loan) {
            $loan->update(['status' => 'terlambat']);
        }

        $this->info("Pemeriksaan selesai: {$count} peminjaman diperbarui ke status 'terlambat'.");

        return Command::SUCCESS;
    }
}

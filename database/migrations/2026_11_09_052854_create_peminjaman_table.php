<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_harus_kembali');
            $table->dateTime('tanggal_aktual_kembali')->nullable();
            $table->enum('status', [
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'sedang_dipinjam',
                'dikembalikan',
                'terlambat',
            ])->default('menunggu_persetujuan');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal_approval')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};

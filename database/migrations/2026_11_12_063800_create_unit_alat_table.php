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
        Schema::create('unit_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained('alat')->cascadeOnDelete();
            $table->string('kode_unit', 100)->unique(); // Identitas unik per unit (contoh: LPT-ASUS-001, LPT-ASUS-002)
            $table->string('nomor_seri', 100)->nullable(); // Nomor seri pabrik jika ada
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'perbaikan', 'hilang'])->default('tersedia');
            $table->string('lokasi_spesifik', 150)->nullable(); // Misal: Meja Lab 01, Rak 3B
            $table->text('catatan')->nullable(); // Keterangan spesifik unit (misal: Baterai baru)
            $table->timestamps();
        });

        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->foreignId('unit_alat_id')->nullable()->after('alat_id')->constrained('unit_alat')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->dropForeign(['unit_alat_id']);
            $table->dropColumn('unit_alat_id');
        });

        Schema::dropIfExists('unit_alat');
    }
};


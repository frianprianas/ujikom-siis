<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'staff_sarpras', 'wakasek_sarpras', 'kepala_sekolah', 'guru', 'siswa') NOT NULL DEFAULT 'siswa'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('staff_sarpras', 'wakasek_sarpras', 'kepala_sekolah', 'guru', 'siswa') NOT NULL DEFAULT 'siswa'");
        }
    }
};

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
        Schema::create('iot_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rfid_uid', 50);
            $table->string('tipe_akses', 50);
            $table->enum('status_aksi', ['success', 'failed', 'unauthorized']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_presensi')) {
            Schema::create('riwayat_presensi', function (Blueprint $table) {
                $table->id();
                $table->string('periode', 7); // format: 2026-03
                $table->string('nama_file', 255)->nullable();
                $table->string('uploaded_by', 255)->nullable(); // nama admin
                $table->timestamp('uploaded_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_presensi');
    }
};

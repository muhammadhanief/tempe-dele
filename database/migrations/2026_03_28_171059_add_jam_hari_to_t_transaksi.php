<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('t_transaksi', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('date');
            }
            if (!Schema::hasColumn('t_transaksi', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }
            if (!Schema::hasColumn('t_transaksi', 'jam_mulai_disetujui')) {
                $table->time('jam_mulai_disetujui')->nullable()->after('jam_selesai');
            }
            if (!Schema::hasColumn('t_transaksi', 'jam_selesai_disetujui')) {
                $table->time('jam_selesai_disetujui')->nullable()->after('jam_mulai_disetujui');
            }
            if (!Schema::hasColumn('t_transaksi', 'hari')) {
                $table->tinyInteger('hari')->default(0)->after('jam_selesai_disetujui'); // 0=kerja, 1=libur
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'jam_mulai_disetujui', 'jam_selesai_disetujui', 'hari']);
        });
    }
};

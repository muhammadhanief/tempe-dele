<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom uraian dari VARCHAR(255) menjadi TEXT agar dapat menampung narasi panjang
        DB::statement("ALTER TABLE t_transaksi MODIFY COLUMN uraian TEXT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE t_transaksi MODIFY COLUMN uraian VARCHAR(255) NULL");
    }
};

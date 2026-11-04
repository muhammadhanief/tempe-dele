<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hash bcrypt panjangnya 60 karakter, sedangkan kolom lama hanya VARCHAR(50).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE m_pegawai MODIFY COLUMN password VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Tidak dikembalikan ke 50 karakter karena hash yang sudah tersimpan akan terpotong.
    }
};

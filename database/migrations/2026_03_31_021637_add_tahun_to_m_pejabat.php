<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_pejabat', function (Blueprint $table) {
            if (!Schema::hasColumn('m_pejabat', 'tahun')) {
                $table->year('tahun')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('m_pejabat', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};

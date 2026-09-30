<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('m_rates', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('id_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('m_rates', function (Blueprint $table) {
            $table->dropColumn('golongan');
        });
    }
};

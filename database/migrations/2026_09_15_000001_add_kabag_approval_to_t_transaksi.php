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
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('t_transaksi', 'note_kabag')) {
                $table->text('note_kabag')->nullable()->after('note');
            }
            if (!Schema::hasColumn('t_transaksi', 'approved_kabag_at')) {
                $table->dateTime('approved_kabag_at')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('t_transaksi', 'note_kabag')) {
                $table->dropColumn('note_kabag');
            }
            if (Schema::hasColumn('t_transaksi', 'approved_kabag_at')) {
                $table->dropColumn('approved_kabag_at');
            }
        });
    }
};

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
            if (!Schema::hasColumn('t_transaksi', 'user_edited')) {
                $table->string('user_edited', 100)->nullable()->after('note_kabag');
            }
            if (!Schema::hasColumn('t_transaksi', 'tanggal_edited')) {
                $table->dateTime('tanggal_edited')->nullable()->after('user_edited');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('t_transaksi', 'tanggal_edited')) {
                $table->dropColumn('tanggal_edited');
            }
            if (Schema::hasColumn('t_transaksi', 'user_edited')) {
                $table->dropColumn('user_edited');
            }
        });
    }
};

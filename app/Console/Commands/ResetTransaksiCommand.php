<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetTransaksiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lembur:reset-transaksi {--force : Lewati konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengosongkan seluruh data transaksi lembur (testing/dummy) tanpa menyentuh data master pegawai, pejabat, tim, tarif, dan presensi.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            $confirm = $this->confirm(
                'Apakah Anda yakin ingin mengosongkan seluruh data transaksi lembur & dokumen hasil generate? (Data master pegawai, pejabat, dan tim TIDAK akan terhapus)',
                false
            );

            if (!$confirm) {
                $this->info('Aksi dibatalkan. Data tetap aman.');
                return 0;
            }
        }

        $this->info('Memulai pembersihan data transaksi lembur...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $tables = [
                't_transaksi',
                't_dokumen',
                't_dokumen_pejabat',
                't_laporan',
                't_rekapitulasi',
                't_akumulasi',
                't_riwayat_presensi',
            ];

            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("  - Tabel <comment>{$table}</comment> berhasil dikosongkan.");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info('✓ Seluruh data transaksi lembur dan dokumen hasil generate berhasil dibersihkan.');
            $this->info('✓ Data master (m_pegawai, m_pejabat, m_tim, m_rates, m_hari_libur, t_anggota_tim) tetap 100% utuh.');
            $this->info('✓ Database siap diisi data riil operasional kantor.');

            return 0;
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Gagal membersihkan data transaksi: ' . $e->getMessage());
            return 1;
        }
    }
}

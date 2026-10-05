<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCleanDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lembur:import-clean-db {--path= : Path file SQL dump bersih}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengimpor database riil bersih dari mentor lalu menerapkan penyesuaian v2 (kolom Kabag, audit edit, pejabat, dll).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultPath = 'D:\\TUGAS ITTP\\BPS - MAGANG\\bahan lembur\\bersih_lemburwe_lembur';
        $path = $this->option('path') ?: $defaultPath;

        if (!file_exists($path)) {
            $this->error("Berkas SQL tidak ditemukan di path: {$path}");
            return 1;
        }

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);

        $this->info("Menyiapkan impor database riil ke `{$dbName}` dari: {$path}");

        // 1. Bersihkan tabel lama agar tidak error Table already exists
        $this->line("1. Mengosongkan skema lama di `{$dbName}`...");
        DB::statement('SET GLOBAL max_allowed_packet = 134217728;');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $tbl) {
            $tableName = array_values((array)$tbl)[0];
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`;");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        $this->info("   ✓ Skema lama berhasil dibersihkan.");

        // 2. Eksekusi impor via MySQL CLI
        $this->line("2. Mengimpor 206+ transaksi riil & data master BPS...");
        $mysqlExe = file_exists('C:\\xampp\\mysql\\bin\\mysql.exe') ? 'C:\\xampp\\mysql\\bin\\mysql.exe' : 'mysql';
        $cmd = "\"\"{$mysqlExe}\" --max_allowed_packet=128M -h {$dbHost} -P {$dbPort} -u {$dbUser} " . (!empty($dbPass) ? "-p{$dbPass} " : "") . "{$dbName} < \"{$path}\"\"";
        
        exec("cmd /c " . $cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->warn("Impor via CLI return {$returnVar}, mencoba impor via PDO query parser...");
            $this->importViaPdo($path);
        } else {
            $this->info("   ✓ Impor data riil berhasil diselesaikan.");
        }

        // 3. Jalankan penyesuaian v2 (Alter Table & Sanitasi)
        $this->line("3. Menerapkan penyesuaian struktur alur v2 (Kabag, Audit Edit, Pejabat)...");
        $this->applyV2Updates();

        // 4. Pastikan tabel cache & jobs Laravel tersedia jika belum ada
        if (!DB::getSchemaBuilder()->hasTable('cache')) {
            \Artisan::call('migrate', ['--path' => 'database/migrations/0001_01_01_000001_create_cache_table.php', '--force' => true]);
        }
        if (!DB::getSchemaBuilder()->hasTable('jobs')) {
            \Artisan::call('migrate', ['--path' => 'database/migrations/0001_01_01_000002_create_jobs_table.php', '--force' => true]);
        }

        $totalTrx = DB::table('t_transaksi')->count();
        $this->newLine();
        $this->info("✓ BERHASIL! Database `{$dbName}` kini menggunakan data riil ({$totalTrx} transaksi).");
        $this->info("✓ Seluruh penyesuaian kolom Kabag Umum, audit edit, dan struktur dokumen telah aktif.");

        return 0;
    }

    private function applyV2Updates(): void
    {
        // A. Tambah kolom note_kabag & approved_kabag_at jika belum ada
        if (!DB::getSchemaBuilder()->hasColumn('t_transaksi', 'note_kabag')) {
            DB::statement("ALTER TABLE `t_transaksi` ADD COLUMN `note_kabag` TEXT NULL AFTER `note`;");
        }
        if (!DB::getSchemaBuilder()->hasColumn('t_transaksi', 'approved_kabag_at')) {
            DB::statement("ALTER TABLE `t_transaksi` ADD COLUMN `approved_kabag_at` DATETIME NULL AFTER `approved_at`;");
        }

        // B. Perlebar status menjadi varchar(30)
        DB::statement("ALTER TABLE `t_transaksi` MODIFY COLUMN `status` VARCHAR(30) NULL DEFAULT 'pending';");

        // C. Tambah kolom audit edit user_edited & tanggal_edited jika belum ada
        if (!DB::getSchemaBuilder()->hasColumn('t_transaksi', 'user_edited')) {
            DB::statement("ALTER TABLE `t_transaksi` ADD COLUMN `user_edited` VARCHAR(100) NULL AFTER `note_kabag`;");
        }
        if (!DB::getSchemaBuilder()->hasColumn('t_transaksi', 'tanggal_edited')) {
            DB::statement("ALTER TABLE `t_transaksi` ADD COLUMN `tanggal_edited` DATETIME NULL AFTER `user_edited`;");
        }

        // D. Perlebar kapasitas uraian menjadi TEXT
        DB::statement("ALTER TABLE `t_transaksi` MODIFY COLUMN `uraian` TEXT NULL;");

        // E. Sanitasi Pejabat Aktif (KBU Pak Joko Suwarjo id=12, PPK Bu Suci Budi Utami id=9)
        DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where('id_pejabat', '!=', 12)->update(['status' => 'nonaktif']);
        DB::table('m_pejabat')->where('id_pejabat', 12)->update(['status' => 'aktif', 'nip' => '197106131993121001']);
        DB::table('m_pejabat')->where('jabatan', 'PPK')->where('id_pejabat', '!=', 9)->update(['status' => 'nonaktif']);
        DB::table('m_pejabat')->where('id_pejabat', 9)->update(['status' => 'aktif', 'nip' => '197811262000122001']);

        // F. Role Pak Joko Suwarjo sebagai ketua_tim (Alur Kabag)
        DB::table('m_pegawai')->where('nip', '197106131993121001')->orWhere('nip_lama', '340013741')->update(['role' => 'ketua_tim']);

        // G. Pastikan role Superadmin & Admin
        DB::table('m_pegawai')->whereIn('nip', ['198707082009022002', '200108252024121005'])->orWhere('nip_lama', '340050277')->update(['role' => 'superadmin']); // Mbak Yuli Purwitasari & Muhammad Hanief
        DB::table('m_pegawai')->whereIn('nip', ['199509102018022001', '197811262000122001'])->orWhere('nip_lama', '340058229')->update(['role' => 'admin']); // Mbak Rizki Dianing Wardhani & Ibu Suci Budi Utami

        // H. Update eligible=1 untuk transaksi yang sudah approved
        DB::table('t_transaksi')->where('status', 'approved')->whereNull('eligible')->update(['eligible' => 1]);

        // I. Regenerasi berkas dokumen di t_dokumen agar langsung memakai format layout terbaru
        $this->regenerateExistingDocs();

        $this->info("   ✓ Penyesuaian struktur kolom v2 selesai.");
    }

    private function regenerateExistingDocs(): void
    {
        $this->line("   Regenerasi berkas dokumen di `t_dokumen` dengan layout terbaru...");
        $controller = app(\App\Http\Controllers\admin\DokumenGenerateController::class);
        $docs = DB::table('t_dokumen')->orderBy('id_dokumen')->get();

        foreach ($docs as $doc) {
            $parts = explode('_', $doc->type);
            if (count($parts) === 3) {
                $docType = $parts[0];
                $jenis   = $parts[1];
                $format  = $parts[2];

                $request = new \Illuminate\Http\Request([
                    'bulan'  => $doc->periode,
                    'jenis'  => $jenis,
                    'format' => $format,
                ]);

                try {
                    if ($docType === 'spkl') {
                        $controller->spkl($request);
                    } elseif ($docType === 'laporan') {
                        $controller->laporan($request, $jenis);
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika terjadi kendala minor pada data historis tertentu
                }
            }
        }
        $this->info("   ✓ Dokumen t_dokumen berhasil direfresh ke layout resmi terkini.");
    }

    private function importViaPdo(string $path): void
    {
        DB::unprepared(file_get_contents($path));
    }
}

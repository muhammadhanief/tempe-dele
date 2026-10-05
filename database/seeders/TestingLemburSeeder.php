<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestingLemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder data uji coba lembur dengan uraian kegiatan riil BPS (SE2026, Sakernas, SPJ, dll).
     */
    public function run(): void
    {
        $this->command->info('Menyiapkan data uji coba lembur (Testing Data)...');

        // Cari beberapa pegawai sampel yang ada di m_pegawai
        $aning     = DB::table('m_pegawai')->where('nip', '197412171998032004')->orWhere('nip_lama', '340015664')->first();
        $asna      = DB::table('m_pegawai')->where('nip', '199007302014102001')->orWhere('nip_lama', '340056926')->first();
        $saniman   = DB::table('m_pegawai')->where('nip', '196911261989031001')->orWhere('nip_lama', '340012313')->first();
        $istiqomah = DB::table('m_pegawai')->where('nip', '197509231998032001')->orWhere('nip_lama', '340015623')->first();
        $herry     = DB::table('m_pegawai')->where('nip', '197205242006041002')->orWhere('nip_lama', '340018283')->first();
        $pristiana = DB::table('m_pegawai')->where('nip', '198907112010122003')->orWhere('nip_lama', '340054208')->first();
        $rizka     = DB::table('m_pegawai')->where('email', 'like', '%-pppk@bps.go.id')->first();

        // Fallback jika salah satu tidak ditemukan
        $fallbackPegawai = DB::table('m_pegawai')->limit(6)->get();
        $p1 = $aning ?: ($fallbackPegawai[0] ?? null);
        $p2 = $asna ?: ($fallbackPegawai[1] ?? null);
        $p3 = $saniman ?: ($fallbackPegawai[2] ?? null);
        $p4 = $istiqomah ?: ($fallbackPegawai[3] ?? null);
        $p5 = $herry ?: ($fallbackPegawai[4] ?? null);
        $p6 = $pristiana ?: ($fallbackPegawai[5] ?? null);

        $now = now();
        $bulanUji = $now->format('Y-m'); // Bulan berjalan atau bisa disesuaikan
        $tahun = (int) $now->format('Y');
        $bln   = (int) $now->format('m');

        $sampleData = [];

        // 1. Pegawai 1 (Aning) - Lembur di 2 tanggal: tgl 1 & tgl 6
        if ($p1) {
            $sampleData[] = [
                'submitted_by_NIP'    => $p1->nip,
                'date'                => sprintf('%04d-%02d-01', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:30:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:30:00',
                'hari'                => 0,
                'uraian'              => 'Melakukan Tugas Humas dan Protokol Pendampingan Pendataan SE2026 Rumah Dinas Jabatan Sekretaris Daerah Provinsi Jawa Tengah',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-01 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-01 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-01 20:30:00', $tahun, $bln),
            ];
            $sampleData[] = [
                'submitted_by_NIP'    => $p1->nip,
                'date'                => sprintf('%04d-%02d-06', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:00:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:00:00',
                'hari'                => 0,
                'uraian'              => 'Melakukan koordinasi dan menyusun rundown kegiatan Kepala BPS Provinsi Jawa Tengah dalam rangka supervisi pendataan SE2026 ke BPS Kab. Magelang, Kab Purworejo, dan Kab Kebumen',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-06 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-06 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-06 20:30:00', $tahun, $bln),
            ];
        }

        // 2. Pegawai 2 (Asna) - Lembur di 3 tanggal: tgl 9, 14, 15
        if ($p2) {
            $sampleData[] = [
                'submitted_by_NIP'    => $p2->nip,
                'date'                => sprintf('%04d-%02d-09', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:30:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:30:00',
                'hari'                => 0,
                'uraian'              => 'Melakukan pemeriksaan, perbaikan data, dan penghitungan indikator hasil Sakernas Mei 2026 Tindak Lanjut Rekonsiliasi Tahap I',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-09 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-09 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-09 20:30:00', $tahun, $bln),
            ];
            $sampleData[] = [
                'submitted_by_NIP'    => $p2->nip,
                'date'                => sprintf('%04d-%02d-14', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:00:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:00:00',
                'hari'                => 0,
                'uraian'              => 'Melakukan pengolahan mikrodata Sakernas pemenuhan data SDGs',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-14 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-14 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-14 20:30:00', $tahun, $bln),
            ];
            $sampleData[] = [
                'submitted_by_NIP'    => $p2->nip,
                'date'                => sprintf('%04d-%02d-15', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:30:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:30:00',
                'hari'                => 0,
                'uraian'              => 'Melakukan pemeriksaan, perbaikan data, dan penghitungan indikator hasil Sakernas Mei 2026 Tindak Lanjut Rekonsiliasi Tahap II',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-15 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-15 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-15 20:30:00', $tahun, $bln),
            ];
        }

        // 3. Pegawai 3 (Saniman) - Lembur tgl 20
        if ($p3) {
            $sampleData[] = [
                'submitted_by_NIP'    => $p3->nip,
                'date'                => sprintf('%04d-%02d-20', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:00:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:00:00',
                'hari'                => 0,
                'uraian'              => 'Penyusunan berkas SPJ Keuangan dan Administrasi Pengadaan Sarana Prasarana Kantor Bagian Umum',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-20 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-20 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-20 20:30:00', $tahun, $bln),
            ];
        }

        // 4. Pegawai 4 (Istiqomah) - Status: Menunggu Kabag Umum (untuk uji flow persetujuan)
        if ($p4) {
            $sampleData[] = [
                'submitted_by_NIP'    => $p4->nip,
                'date'                => sprintf('%04d-%02d-22', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '18:30:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '18:30:00',
                'hari'                => 0,
                'uraian'              => 'Finalisasi Laporan Evaluasi Kebutuhan Data Statistik Sektoral BPS Provinsi Jawa Tengah',
                'status'              => 'menunggu_kabag',
                'eligible'            => null,
                'submitted_at'        => sprintf('%04d-%02d-22 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-22 18:40:00', $tahun, $bln),
                'approved_kabag_at'   => null,
            ];
        }

        // 5. Pegawai 5 (Herry) - Status: Pending (untuk uji flow ketua tim)
        if ($p5) {
            $sampleData[] = [
                'submitted_by_NIP'    => $p5->nip,
                'date'                => sprintf('%04d-%02d-24', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:30:00',
                'jam_mulai_disetujui' => null,
                'jam_selesai_disetujui'=> null,
                'hari'                => 0,
                'uraian'              => 'Pengolahan dan Analisis Tabulasi Data Survei Sosial Ekonomi Nasional (Susenas)',
                'status'              => 'pending',
                'eligible'            => null,
                'submitted_at'        => sprintf('%04d-%02d-24 16:10:00', $tahun, $bln),
                'approved_at'         => null,
                'approved_kabag_at'   => null,
            ];
        }

        // 6. PPPK (Rizka Argi) - Lembur tgl 25 (untuk uji kategori PPPK)
        if ($rizka) {
            $sampleData[] = [
                'submitted_by_NIP'    => $rizka->nip,
                'date'                => sprintf('%04d-%02d-25', $tahun, $bln),
                'jam_mulai'           => '16:30:00',
                'jam_selesai'         => '19:00:00',
                'jam_mulai_disetujui' => '16:30:00',
                'jam_selesai_disetujui'=> '19:00:00',
                'hari'                => 0,
                'uraian'              => 'Peliputan dan Publikasi Konten Media Sosial Rilis Berita Resmi Statistik (BRS)',
                'status'              => 'approved',
                'eligible'            => 1,
                'submitted_at'        => sprintf('%04d-%02d-25 16:00:00', $tahun, $bln),
                'approved_at'         => sprintf('%04d-%02d-25 20:00:00', $tahun, $bln),
                'approved_kabag_at'   => sprintf('%04d-%02d-25 20:30:00', $tahun, $bln),
            ];
        }

        DB::table('t_transaksi')->insert($sampleData);

        $this->command->info('✓ ' . count($sampleData) . ' transaksi uji coba berhasil ditambahkan untuk periode ' . $bulanUji . '.');
    }
}

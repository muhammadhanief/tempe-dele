<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\KoreksiLembur;

class DashboardController extends Controller
{
    use KoreksiLembur;

    public function index()
    {
        $tahunIni  = Carbon::now()->year;
        $hariIni   = Carbon::today();

        // --- Statistik pengajuan tahun berjalan ---
        $stats = [
            'total'     => DB::table('t_transaksi')->whereYear('date', $tahunIni)->count(),
            'disetujui' => DB::table('t_transaksi')->whereYear('date', $tahunIni)->where('status', 'approved')->count(),
            'diproses'  => DB::table('t_transaksi')->whereYear('date', $tahunIni)->whereIn('status', ['pending', 'menunggu_kabag'])->count(),
            'ditolak'   => DB::table('t_transaksi')->whereYear('date', $tahunIni)->where('status', 'rejected')->count(),
        ];

        // --- Lembur hari ini ---
        $lemburHariIni = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as tim', 't.tim_kode_tim', '=', 'tim.kode_tim')
            ->whereDate('t.date', $hariIni)
            ->select(
                'p.nama as nama_pegawai',
                'tim.nama_tim',
                't.jam_mulai',
                't.jam_selesai',
                't.jam_mulai_disetujui',
                't.jam_selesai_disetujui',
                't.status'
            )
            ->orderBy('t.submitted_at', 'desc')
            ->get();

        // --- Dokumen bulan ini ---
        $bulanPeriode    = now()->format('Y-m');
        $dokumenBulanIni = DB::table('t_dokumen')
            ->where('periode', $bulanPeriode)
            ->whereNotNull('file_blob')
            ->get();

        $types = [
            ['label' => 'SPKL PNS',     'doc' => $dokumenBulanIni->firstWhere('type', 'spkl_pns_pdf')],
            ['label' => 'SPKL PPPK',    'doc' => $dokumenBulanIni->firstWhere('type', 'spkl_pppk_pdf')],
            ['label' => 'Laporan PNS',  'doc' => $dokumenBulanIni->firstWhere('type', 'laporan_pns_pdf')],
            ['label' => 'Laporan PPPK', 'doc' => $dokumenBulanIni->firstWhere('type', 'laporan_pppk_pdf')],
        ];

        // --- Ringkasan tahun berjalan ---
        $transaksiDisetujui = DB::table('t_transaksi')
            ->whereYear('date', $tahunIni)
            ->where('status', 'approved')
            ->select('submitted_by_NIP', 'jam_mulai_disetujui', 'jam_selesai_disetujui')
            ->get();

        $totalMenit = $transaksiDisetujui->sum(function ($t) {
            if (!$t->jam_mulai_disetujui || !$t->jam_selesai_disetujui) return 0;
            return Carbon::parse($t->jam_selesai_disetujui)->diffInMinutes(Carbon::parse($t->jam_mulai_disetujui));
        });

        $karyawanAktif = $transaksiDisetujui->pluck('submitted_by_NIP')->unique()->count();
        $totalJam      = round($totalMenit / 60, 1);
        $rataRataJam   = $karyawanAktif > 0 ? round($totalJam / $karyawanAktif, 1) : 0;

        $periodeIni = Carbon::now()->format('Y-m');

        $presensiLengkap  = DB::table('t_riwayat_presensi')->where('periode', $periodeIni)->exists();
        $spklGenerated    = $dokumenBulanIni->whereIn('type', ['spkl_pns_pdf', 'spkl_pppk_pdf'])->isNotEmpty();
        $laporanGenerated = $dokumenBulanIni->whereIn('type', ['laporan_pns_pdf', 'laporan_pppk_pdf'])->isNotEmpty();

        $ringkasan = [
            'total_jam'         => $totalJam,
            'karyawan_aktif'    => $karyawanAktif,
            'rata_rata_jam'     => $rataRataJam,
            'presensi_lengkap'  => $presensiLengkap,
            'spkl_generated'    => $spklGenerated,
            'laporan_generated' => $laporanGenerated,
        ];

        // --- Notifikasi otomatis ---
        $notifikasi = collect();

        if (!$presensiLengkap) {
            $notifikasi->push([
                'pesan' => 'Presensi ' . Carbon::now()->translatedFormat('F Y') . ' belum diinput.',
                'level' => 'danger',
                'waktu' => 'Hari ini',
            ]);
        }

        if (!$spklGenerated) {
            $notifikasi->push([
                'pesan' => 'SPKL ' . Carbon::now()->translatedFormat('F Y') . ' belum di-generate.',
                'level' => 'warning',
                'waktu' => 'Batas: ' . Carbon::now()->endOfMonth()->translatedFormat('d M Y'),
            ]);
        }

        if (!$laporanGenerated) {
            $notifikasi->push([
                'pesan' => 'Laporan lembur ' . Carbon::now()->translatedFormat('F Y') . ' belum di-generate.',
                'level' => 'warning',
                'waktu' => 'Batas: ' . Carbon::now()->endOfMonth()->translatedFormat('d M Y'),
            ]);
        }

        return view('admin.dashboard', compact(
            'stats',
            'lemburHariIni',
            'types',
            'ringkasan',
            'notifikasi'
        ));
    }

    public function getPending()
    {
        $tahunIni = now()->year;

        $pending = DB::table('t_transaksi')
            ->join('m_pegawai', 't_transaksi.submitted_by_NIP', '=', 'm_pegawai.nip')
            ->whereYear('t_transaksi.date', $tahunIni)
            ->whereIn('t_transaksi.status', ['pending', 'menunggu_kabag'])
            ->select(
                't_transaksi.id_transaksi',
                'm_pegawai.nama',
                't_transaksi.date',
                't_transaksi.deskripsi',
                't_transaksi.status'
            )
            ->orderBy('t_transaksi.date', 'desc')
            ->get();

        return response()->json($pending);
    }

    public function approve($id)
    {
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . ($transaksi->jam_mulai_disetujui ?? $transaksi->jam_mulai));
        $jamSelesaiDisetujui = ($transaksi->jam_selesai_disetujui ?? $transaksi->jam_selesai)
            ? Carbon::parse($transaksi->date . ' ' . ($transaksi->jam_selesai_disetujui ?? $transaksi->jam_selesai))
            : null;

        if ($jamSelesaiDisetujui && $jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        // Batasi jam selesai agar tidak melebihi jam kepulangan fisik presensi jika data presensi sudah ada
        if ($jamSelesaiDisetujui) {
            $pegawaiNipLama = DB::table('m_pegawai')->where('nip', $transaksi->submitted_by_NIP)->value('nip_lama');
            $presensi = DB::table('t_presensi')
                ->where('niplama', $pegawaiNipLama)
                ->whereDate('tanggal', $transaksi->date)
                ->first();

            if ($presensi && $presensi->jam_selesai) {
                $jamPulangPresensi = Carbon::parse($transaksi->date . ' ' . Carbon::parse($presensi->jam_selesai)->format('H:i:s'));
                if ($jamPulangPresensi->lessThan($jamMulaiDisetujui)) {
                    $jamPulangPresensi->addDay();
                }
                if ($jamSelesaiDisetujui->greaterThan($jamPulangPresensi)) {
                    $jamSelesaiDisetujui = $jamPulangPresensi;
                }
            }
        }

        DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->update([
                'status'                => 'approved',
                'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                'jam_selesai_disetujui' => $jamSelesaiDisetujui?->format('H:i:s'),
                'note'                  => null,
                'eligible'              => null,
                'approved_at'           => now()->toDateString(),
                'approved_kabag_at'     => now(),
                'user_edited'           => session('user')['nama'] ?? session('user')['nip'],
                'tanggal_edited'        => now(),
            ]);

        $this->koreksiUntukTransaksi($id);

        return response()->json(['success' => true]);
    }
}

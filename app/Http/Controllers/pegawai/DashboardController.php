<?php

namespace App\Http\Controllers\pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $nip       = session('user')['nip'];
        $nipLama   = session('user')['nip_lama'] ?? null;
        $tahunIni  = Carbon::now()->year;
        $hariIni   = Carbon::today();
        $sekarang  = Carbon::now();

        $nipFilter = function($q) use ($nip, $nipLama) {
            $q->where('submitted_by_NIP', $nip);
            if ($nipLama) $q->orWhere('submitted_by_NIP', $nipLama);
        };

        // --- Statistik pengajuan tahun berjalan milik pegawai ---
        $stats = [
            'total'     => DB::table('t_transaksi')->where($nipFilter)->whereYear('date', $tahunIni)->count(),
            'disetujui' => DB::table('t_transaksi')->where($nipFilter)->whereYear('date', $tahunIni)->where('status', 'approved')->count(),
            'diproses'  => DB::table('t_transaksi')->where($nipFilter)->whereYear('date', $tahunIni)->whereIn('status', ['pending', 'menunggu_kabag'])->count(),
            'ditolak'   => DB::table('t_transaksi')->where($nipFilter)->whereYear('date', $tahunIni)->where('status', 'rejected')->count(),
        ];

        // --- Pengajuan terbaru tahun berjalan ---
        $pengajuanTerbaru = DB::table('t_transaksi')
            ->where($nipFilter)
            ->whereYear('date', $tahunIni)
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        // --- Jadwal lembur yang akan datang ---
        $jadwalMendatang = DB::table('t_transaksi')
            ->where($nipFilter)
            ->where('status', 'approved')
            ->whereDate('date', '>=', $hariIni)
            ->orderBy('date', 'asc')
            ->limit(3)
            ->get();

        // --- Notifikasi pribadi ---
        $notifikasi = collect();

        // Ada pengajuan yang ditolak tahun ini
        $ditolakTahunIni = DB::table('t_transaksi')
            ->where($nipFilter)
            ->whereYear('date', $tahunIni)
            ->where('status', 'rejected')
            ->count();

        if ($ditolakTahunIni > 0) {
            $notifikasi->push([
                'pesan' => "{$ditolakTahunIni} pengajuan lembur kamu ditolak tahun ini.",
                'level' => 'danger',
                'waktu' => 'Tahun ' . $tahunIni,
            ]);
        }

        // Ada pengajuan pending / menunggu kabag
        $pendingCount = DB::table('t_transaksi')
            ->where($nipFilter)
            ->whereIn('status', ['pending', 'menunggu_kabag'])
            ->whereYear('date', $tahunIni)
            ->count();

        if ($pendingCount > 0) {
            $notifikasi->push([
                'pesan' => "{$pendingCount} pengajuan lembur masih menunggu persetujuan.",
                'level' => 'warning',
                'waktu' => 'Menunggu review atasan',
            ]);
        }

        // Ada jadwal lembur besok
        $lemburBesok = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nip)
            ->where('status', 'approved')
            ->whereDate('date', $hariIni->copy()->addDay())
            ->first();

        if ($lemburBesok) {
            $notifikasi->push([
                'pesan' => 'Kamu memiliki jadwal lembur besok, ' . Carbon::parse($lemburBesok->date)->translatedFormat('d F Y') . '.',
                'level' => 'info',
                'waktu' => $lemburBesok->jam_mulai_disetujui
                    ? substr($lemburBesok->jam_mulai_disetujui, 0, 5) . ' - ' . substr($lemburBesok->jam_selesai_disetujui, 0, 5)
                    : '-',
            ]);
        }

        return view('dashboard', compact(
            'stats',
            'pengajuanTerbaru',
            'jadwalMendatang',
            'notifikasi'
        ));
    }
}

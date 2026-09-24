<?php

namespace App\Http\Controllers\pimpinan;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Statistik semua tim
        $stats = [
            'total'     => DB::table('t_transaksi')->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->count(),
            'disetujui' => DB::table('t_transaksi')->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'approved')->count(),
            'diproses'  => DB::table('t_transaksi')->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'pending')->count(),
            'ditolak'   => DB::table('t_transaksi')->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'rejected')->count(),
        ];

        // 5 pengajuan terbaru bulan ini, semua tim
        $pengajuan = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->whereMonth('t.date', $bulanIni)
            ->whereYear('t.date', $tahunIni)
            ->select('t.*', 'p.nama as nama_pegawai')
            ->orderBy('t.submitted_at', 'desc')
            ->limit(5)
            ->get();

        // Lembur hari ini yang approved, semua tim
        $lemburHariIni = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->whereDate('t.date', today())
            ->where('t.status', 'approved')
            ->select('p.nama as nama_pegawai', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui')
            ->get();

        return view('pimpinan.dashboard', compact('stats', 'pengajuan', 'lemburHariIni'));
    }
}

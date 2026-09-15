<?php

namespace App\Http\Controllers\ketuatim;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $nipKetua = session('user')['nip'];
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // --- Statistik pengajuan tim bulan ini ---
        $stats = [
            'total'     => DB::table('t_transaksi')->where('approver_employee_id', $nipKetua)->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->count(),
            'disetujui' => DB::table('t_transaksi')->where('approver_employee_id', $nipKetua)->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'approved')->count(),
            'diproses'  => DB::table('t_transaksi')->where('approver_employee_id', $nipKetua)->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'pending')->count(),
            'ditolak'   => DB::table('t_transaksi')->where('approver_employee_id', $nipKetua)->whereMonth('date', $bulanIni)->whereYear('date', $tahunIni)->where('status', 'rejected')->count(),
        ];

        // --- pengajuan terbaru dari anggota tim ---
        $pengajuan = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.approver_employee_id', $nipKetua)
            ->whereMonth('t.date', $bulanIni)
            ->whereYear('t.date', $tahunIni)
            ->select('t.*', 'p.nama as nama_pegawai')
            ->orderBy('t.submitted_at', 'desc')
            ->get();

        // --- Lembur hari ini yang approved ---
        $lemburHariIni = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.approver_employee_id', $nipKetua)
            ->whereDate('t.date', today())
            ->where('t.status', 'approved')
            ->select('p.nama as nama_pegawai', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui')
            ->get();

        return view('ketua-tim.dashboard', compact('stats', 'pengajuan', 'lemburHariIni'));
    }

    public function getPending()
    {
        $nipKetua = session('user')['nip'];
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $pending = DB::table('t_transaksi')
            ->join('m_pegawai', 't_transaksi.submitted_by_NIP', '=', 'm_pegawai.nip')
            ->whereMonth('t_transaksi.date', $bulanIni)
            ->whereYear('t_transaksi.date', $tahunIni)
            ->where('t_transaksi.status', 'pending')
            ->where('t_transaksi.approver_employee_id', $nipKetua) // filter tim ketua
            ->select(
                't_transaksi.id_transaksi',
                'm_pegawai.nama',
                't_transaksi.date',
            )
            ->orderBy('t_transaksi.date', 'asc')
            ->get();

        return response()->json($pending);
    }

    public function approve($id)
    {
        $nipKetua  = session('user')['nip'];

        // Pastikan transaksi ini memang milik tim ketua tsb
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->where('approver_employee_id', $nipKetua)
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $jamMulai   = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
        $jamSelesai = $transaksi->jam_selesai
            ? Carbon::parse($transaksi->date . ' ' . $transaksi->jam_selesai)
            : null;

        if ($jamSelesai && $jamSelesai->lessThan($jamMulai)) {
            $jamSelesai->addDay();
        }

        DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->update([
                'status'                => 'approved',
                'jam_mulai_disetujui'   => $jamMulai->format('H:i:s'),
                'jam_selesai_disetujui' => $jamSelesai?->format('H:i:s'),
                'approved_at'           => now()->toDateString(),
            ]);

        return response()->json(['success' => true]);
    }
}
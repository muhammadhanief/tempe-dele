<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.status', 'approved')
            ->where('eligible', 1)
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select(
                'p.nama', 'p.nip',
                't.uraian', 't.date',
                't.jam_mulai_disetujui', 't.jam_selesai_disetujui',
                'mt.kode_tim', 'mt.nama_tim'
            )
            ->orderBy('t.date', 'asc')
            ->orderBy('p.nama', 'asc');

        // Filter tim
        if ($request->filled('tim')) {
            $query->where('t.tim_kode_tim', $request->tim);
        }

        // Filter pegawai
        if ($request->filled('pegawai')) {
            $query->where('t.submitted_by_NIP', $request->pegawai);
        }

        $laporan = $query->paginate(20)->appends($request->query());

        // Hitung kode (durasi jam)
        $laporan->getCollection()->transform(function ($item) {
            $mulai   = strtotime($item->jam_mulai_disetujui);
            $selesai = strtotime($item->jam_selesai_disetujui);
            $item->kode = (int) floor(($selesai - $mulai) / 3600);
            return $item;
        });

        // Untuk dropdown tim & pegawai
        $tim = DB::table('m_tim')->select('kode_tim', 'nama_tim')->get();

        return view('admin.laporan', compact('laporan', 'bulan', 'tim'));
    }
}

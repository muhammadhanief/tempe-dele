<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AkumulasiController extends Controller
{
    public function index(Request $request)
{
    $bulan = $request->get('bulan', now()->format('Y-m'));
    [$tahun, $bln] = explode('-', $bulan);

    $query = DB::table('t_transaksi as t')
        ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
        ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
        ->leftJoin('m_rates as r', function ($join) {
            $join->on(DB::raw("SUBSTRING_INDEX(p.golongan, '/', 1)"), '=', 'r.golongan')
                 ->on('t.hari', '=', 'r.day_type');
        })
        ->where('t.status', 'approved')
        ->where('t.eligible', 1)
        ->whereYear('t.date', $tahun)
        ->whereMonth('t.date', $bln)
        ->select(
            'p.nama', 'p.nip', 'p.golongan',
            DB::raw('SUM(TIMESTAMPDIFF(HOUR, t.jam_mulai_disetujui, t.jam_selesai_disetujui)) as jam_disetujui'),
            DB::raw('SUM(TIMESTAMPDIFF(HOUR, t.jam_mulai, t.jam_selesai)) as jam_diajukan'),
            DB::raw('SUM(COALESCE(r.uang_lembur, 0) * TIMESTAMPDIFF(HOUR, t.jam_mulai_disetujui, t.jam_selesai_disetujui)) as total_uang_lembur'),
            DB::raw('SUM(COALESCE(r.uang_makan, 0)) as total_uang_makan'),
            DB::raw('MAX(COALESCE(r.pajak, 0)) as pajak_pct')
        )
        ->groupBy('p.nip', 'p.nama', 'p.golongan')
        ->orderBy('p.nama');

    if ($request->filled('tim')) {
        $query->where('t.tim_kode_tim', $request->tim);
    }

    if ($request->filled('pegawai')) {
        $query->where('t.submitted_by_NIP', $request->pegawai);
    }

    $akumulasi = $query->paginate(20)->appends($request->query());

    $akumulasi->getCollection()->transform(function ($item) {
        $jumlah = $item->total_uang_lembur + $item->total_uang_makan;
        $pajak  = round($jumlah * $item->pajak_pct); // m_rates.pajak berupa pecahan (0.05 = 5%)

        $item->total_jumlah = $jumlah;
        $item->total_pajak  = $pajak;
        $item->total_terima = $jumlah - $pajak;

        return $item;
    });

    $tim = DB::table('m_tim')->select('kode_tim', 'nama_tim')->get();

    return view('admin.akumulasi', compact('akumulasi', 'bulan', 'tim'));
}

    public function download(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AkumulasiExport($request->all()),
            'akumulasi_' . $bulan . '.xlsx'
        );
    }
}

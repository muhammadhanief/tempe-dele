<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DokumenViewController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan');
        $isFiltered = !empty($bulan);

        $dokumenList = DB::table('t_dokumen')->orderBy('periode', 'desc')->get();

        $periodeTransaksi = DB::table('t_transaksi')
            ->where('status', 'approved')
            ->where('eligible', 1)
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as periode")
            ->distinct()
            ->pluck('periode');

        $periodeDokumen = $dokumenList->pluck('periode');
        $allPeriode = $periodeTransaksi->merge($periodeDokumen)
            ->unique()
            ->sortDesc()
            ->values();

        $periodeList = $isFiltered
            ? $allPeriode->filter(fn($p) => $p === $bulan)
            : $allPeriode;

        $perPage  = 12;
        $page     = $request->get('page', 1);
        $items    = $periodeList->forPage($page, $perPage);

        $periodeList = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $periodeList->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $pejabat = DB::table('m_pejabat')->where('status', 'aktif')->get();
        $ppk = $pejabat->firstWhere('jabatan', 'PPK');
        $kbps = $pejabat->firstWhere('jabatan', 'Kepala BPS');
        $kbu = $pejabat->firstWhere('jabatan', 'Kepala Bagian Umum');

        return view('admin.dokumen', compact('periodeList', 'dokumenList', 'ppk', 'kbps', 'kbu', 'bulan', 'isFiltered'));
    }

    public function hapus(Request $request)
    {
        $bulan = $request->get('bulan');
        DB::table('t_dokumen')->where('periode', $bulan)->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function hapusSatu($id)
    {
        DB::table('t_dokumen')->where('id_dokumen', $id)->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function view($id)
    {
        $dokumen = DB::table('t_dokumen')->where('id_dokumen', $id)->first();
        if (!$dokumen || !$dokumen->file_blob) abort(404);

        $timestamp = $dokumen->generated_at
            ? \Carbon\Carbon::parse($dokumen->generated_at)->format('Ymd_His')
            : now()->format('Ymd_His');

        $isXlsx = str_ends_with($dokumen->type, '_xlsx');

        $namaFile = match($dokumen->type) {
            'spkl_pns_pdf'      => "SPKL_PNS_{$dokumen->periode}_{$timestamp}.pdf",
            'spkl_pns_xlsx'     => "SPKL_PNS_{$dokumen->periode}_{$timestamp}.xlsx",
            'spkl_pppk_pdf'     => "SPKL_PPPK_{$dokumen->periode}_{$timestamp}.pdf",
            'spkl_pppk_xlsx'    => "SPKL_PPPK_{$dokumen->periode}_{$timestamp}.xlsx",
            'laporan_pns_pdf'   => "Laporan_PNS_{$dokumen->periode}_{$timestamp}.pdf",
            'laporan_pns_xlsx'  => "Laporan_PNS_{$dokumen->periode}_{$timestamp}.xlsx",
            'laporan_pppk_pdf'  => "Laporan_PPPK_{$dokumen->periode}_{$timestamp}.pdf",
            'laporan_pppk_xlsx' => "Laporan_PPPK_{$dokumen->periode}_{$timestamp}.xlsx",
            default             => "Dokumen_{$dokumen->periode}_{$timestamp}" . ($isXlsx ? '.xlsx' : '.pdf'),
        };

        $contentType = $isXlsx
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'application/pdf';

        $disposition = $isXlsx ? 'attachment' : 'inline';

        return response($dokumen->file_blob, 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => "{$disposition}; filename=\"{$namaFile}\"",
        ]);
    }
}

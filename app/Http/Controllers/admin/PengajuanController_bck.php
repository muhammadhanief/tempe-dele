<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
       /*file asli $bulan = $request->get('bulan');*/
       $bulan = $request->get('bulan', now()->format('Y-m'));
    
        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->select([
                't.*',
                'p.nama as nama_pegawai',
                'p.nip as nip_pegawai',
                'p.nip_lama',
                'mt.nama_tim',
                DB::raw('EXISTS(
                    SELECT 1 FROM t_presensi pr
                    WHERE pr.niplama = p.nip_lama
                    AND DATE(pr.tanggal) = t.date
                ) as has_presensi')
            ]);
    
        if ($bulan) {
            try {
                /*ini aslinya $periode = Carbon::createFromFormat('Y-m-d', $bulan . '-01');ak nambah 2 baris ini*/
                 $periode = Carbon::parse($bulan . '-01');
                 $bulan = $periode->format('Y-m');
    
                $query->whereYear('t.date', $periode->year)
                    ->whereMonth('t.date', $periode->month);
            } catch (\Exception $e) {
                /*$bulan = null; ini jg ak benerin*/
                $bulan = now()->format('Y-m');
            }
        }
    
        $pengajuan = $query
            ->orderBy('t.date', 'desc')
            ->paginate(10)
            ->withQueryString();
    
        $hariLibur = DB::table('m_hari_libur')->orderBy('tanggal', 'asc')->get();
    
        return view('admin.pengajuan', compact('pengajuan', 'hariLibur', 'bulan'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'required',
            'jam_selesai_disetujui' => 'required',
            'status'                => 'required|in:approved,rejected',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.id_transaksi', $id)
            ->select('t.*', 'p.nip_lama')
            ->first();

        $noteKetua = trim($request->note ?? '');

        $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . $request->jam_mulai_disetujui);
        $jamSelesaiDisetujui = Carbon::parse($transaksi->date . ' ' . $request->jam_selesai_disetujui);

        if ($jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update([
            'status'                => $request->status,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
            'note'                  => $noteKetua !== '' ? $noteKetua : null,
            'eligible'              => $request->status === 'approved' ? null : null,
            'approved_at'           => now()->toDateString(),
        ]);

        return response()->json(['success' => true]);
    }

    public function presensi($id)
    {
        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.id_transaksi', $id)
            ->select('t.date', 'p.nip_lama', 'p.nama', 'p.nip')
            ->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $presensi = DB::table('t_presensi')
            ->whereDate('tanggal', $transaksi->date)
            ->where('niplama', $transaksi->nip_lama)
            ->first();

        return response()->json([
            'nama'       => $transaksi->nama,
            'nip'        => $transaksi->nip,
            'tanggal'    => \Carbon\Carbon::parse($transaksi->date)->translatedFormat('l, d F Y'),
            'status'     => $presensi->status ?? null,
            'jam_masuk'  => $presensi ? \Carbon\Carbon::parse($presensi->jam_mulai)->format('H:i') : null,
            'jam_pulang' => $presensi ? \Carbon\Carbon::parse($presensi->jam_selesai)->format('H:i') : null,
        ]);
    }

    public function semuaPegawai()
    {
        $pegawai = DB::table('m_pegawai')
            ->select('nama', 'nip')
            ->orderBy('nama')
            ->get();

        return response()->json($pegawai);
    }

}
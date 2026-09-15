<?php

namespace App\Http\Controllers\ketuatim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $nipKetua = session('user')['nip'];
        $nipLamaKetua = session('user')['nip_lama'] ?? null;

        // Jika user adalah Kabag Umum, redirect langsung ke halaman Persetujuan Kabag Umum
        $isKabagUmum = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->where('status', 'aktif')
            ->where(function ($q) use ($nipKetua, $nipLamaKetua) {
                if ($nipKetua) $q->where('nip', $nipKetua);
                if ($nipLamaKetua) $q->orWhere('nip_lama', $nipLamaKetua);
            })->exists();

        if ($isKabagUmum) {
            return redirect()->route('kabag-umum.pengajuan');
        }

        $bulan = $request->get('bulan', now()->format('Y-m'));

        try {
            $periode = Carbon::parse($bulan . '-01');
        } catch (\Exception $e) {
            $periode = Carbon::now();
            $bulan = $periode->format('Y-m');
        }

        $tim = DB::table('m_tim')->where('nipbaru_ketua', $nipKetua)->first();

        $pengajuan = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.approver_employee_id', $nipKetua)
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
            ])
            ->whereYear('t.date', $periode->year)
            ->whereMonth('t.date', $periode->month)
            ->orderBy('t.date', 'desc')
            // ->paginate(10);
            ->paginate(10)->appends($request->query());

        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('ketua-tim.pengajuan', compact('pengajuan', 'hariLibur', 'bulan'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'required',
            'jam_selesai_disetujui' => 'required',
            'status'                => 'required|in:approved,rejected',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->first();

        $noteKetua = trim($request->note ?? '');

        $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . $request->jam_mulai_disetujui);
        $jamSelesaiDisetujui = Carbon::parse($transaksi->date . ' ' . $request->jam_selesai_disetujui);

        if ($jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        // Cek apakah tim adalah Tim Bagian Umum atau approver adalah Kabag Umum
        $isTimBagianUmum = false;
        if (!empty($transaksi->tim_kode_tim)) {
            $tim = DB::table('m_tim')->where('kode_tim', $transaksi->tim_kode_tim)->first();
            if ($tim && (str_contains(strtolower($tim->nama_tim), 'bagian umum') || $tim->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                $isTimBagianUmum = true;
            }
        }

        $nipSess = session('user')['nip'] ?? null;
        $nipLamaSess = session('user')['nip_lama'] ?? null;
        $isApproverKabag = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->where('status', 'aktif')
            ->where(function($q) use ($nipSess, $nipLamaSess) {
                if ($nipSess) $q->where('nip', $nipSess);
                if ($nipLamaSess) $q->orWhere('nip_lama', $nipLamaSess);
            })->exists();

        $finalStatus = $request->status;
        $approvedKabagAt = null;

        if ($request->status === 'approved') {
            if ($isTimBagianUmum || $isApproverKabag) {
                // Tim Bagian Umum langsung disetujui final
                $finalStatus = 'approved';
                $approvedKabagAt = now();
            } else {
                // Tim Lain naik ke persetujuan Kabag Umum
                $finalStatus = 'menunggu_kabag';
            }
        }

        $updateData = [
            'status'                => $finalStatus,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
            'note'                  => $noteKetua !== '' ? $noteKetua : null,
            'eligible'              => null,
            'approved_at'           => now()->toDateString(),
        ];

        if ($approvedKabagAt) {
            $updateData['approved_kabag_at'] = $approvedKabagAt;
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        return response()->json([
            'success'               => true,
            'status'                => $finalStatus,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i'),
            'message'               => $finalStatus === 'menunggu_kabag' 
                ? 'Pengajuan berhasil disetujui Ketua Tim dan diteruskan ke Kabag Umum' 
                : ($finalStatus === 'approved' ? 'Pengajuan berhasil disetujui' : 'Pengajuan berhasil ditolak')
        ]);
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

        // Cari presensi berdasarkan niplama dan tanggal
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

    public function anggotaTim()
    {
        $nipKetua = session('user')['nip'];

        $anggota = DB::table('t_anggota_tim as at')
            ->join('m_pegawai as p', 'at.pegawai_id_pegawai', '=', 'p.id_pegawai')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('mt.nipbaru_ketua', $nipKetua)
            ->select('p.nama', 'p.nip')
            ->get();

        return response()->json($anggota);
    }
}
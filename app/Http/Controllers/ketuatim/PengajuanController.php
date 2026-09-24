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

        $bulan  = $request->get('bulan', now()->format('Y-m'));
        $search = trim((string) $request->get('nip'));
        $status = $request->get('status', 'all');
        $sort   = in_array(strtolower($request->get('sort', 'priority')), ['priority', 'desc', 'asc'])
            ? strtolower($request->get('sort', 'priority'))
            : 'priority';

        try {
            $periode = Carbon::parse($bulan . '-01');
        } catch (\Exception $e) {
            $periode = Carbon::now();
            $bulan = $periode->format('Y-m');
        }

        $tim = DB::table('m_tim')
            ->where('nipbaru_ketua', $nipKetua)
            ->orWhere('niplama_ketua', $nipLamaKetua)
            ->first();

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.approver_employee_id', $nipKetua)
            ->select([
                't.*',
                'p.nama as nama_pegawai',
                'p.nip as nip_pegawai',
                'p.nip_lama',
                'mt.nama_tim',
                'mt.nama_ketua',
                DB::raw('EXISTS(
                    SELECT 1 FROM t_presensi pr
                    WHERE pr.niplama = p.nip_lama
                    AND DATE(pr.tanggal) = t.date
                ) as has_presensi')
            ])
            ->whereYear('t.date', $periode->year)
            ->whereMonth('t.date', $periode->month);

        if ($search !== '') {
            $query->where('p.nip', $search);
        }

        if ($status && $status !== 'all') {
            $query->where('t.status', $status);
        }

        if ($sort === 'asc') {
            $query->orderBy('t.date', 'asc')->orderBy('t.id_transaksi', 'asc');
        } elseif ($sort === 'desc') {
            $query->orderBy('t.date', 'desc')->orderBy('t.id_transaksi', 'desc');
        } else { // priority
            $query->orderByRaw("CASE WHEN t.status = 'pending' THEN 0 WHEN t.status = 'menunggu_kabag' THEN 1 WHEN t.status = 'approved' THEN 2 ELSE 3 END")
                ->orderBy('t.date', 'desc')
                ->orderBy('t.id_transaksi', 'desc');
        }

        $pengajuan = $query
            ->paginate(10)
            ->withQueryString();

        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('ketua-tim.pengajuan', compact('pengajuan', 'hariLibur', 'bulan', 'tim', 'search', 'status', 'sort'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'nullable',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected,menunggu_kabag',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // KUNCI STATUS: Jika sudah diproses (menunggu_kabag, approved, atau rejected), status keputusan tidak boleh dibalik
        if (in_array($transaksi->status, ['menunggu_kabag', 'approved', 'rejected'])) {
            if ($transaksi->status === 'rejected' && $request->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status Ditolak sudah terkunci. Anda hanya dapat mengoreksi catatan penolakan.'
                ], 422);
            }
            if (in_array($transaksi->status, ['menunggu_kabag', 'approved']) && $request->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan yang sudah diproses atau disetujui tidak dapat dibatalkan menjadi Ditolak. Anda hanya dapat mengoreksi jam disetujui atau catatan.'
                ], 422);
            }
        }

        $noteKetua = trim($request->note ?? '');

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

        $finalStatus = $transaksi->status;
        $approvedKabagAt = null;

        if ($transaksi->status === 'pending') {
            if ($request->status === 'approved') {
                if ($isTimBagianUmum || $isApproverKabag) {
                    $finalStatus = 'approved';
                    $approvedKabagAt = now();
                } else {
                    $finalStatus = 'menunggu_kabag';
                }
            } else {
                $finalStatus = 'rejected';
            }
        }

        $updateData = [
            'status'   => $finalStatus,
            'note'     => $noteKetua !== '' ? $noteKetua : null,
            'eligible' => null,
        ];

        if ($finalStatus === 'rejected') {
            $updateData['jam_mulai_disetujui']   = null;
            $updateData['jam_selesai_disetujui'] = null;
            if (empty($transaksi->approved_at)) {
                $updateData['approved_at'] = now()->toDateString();
            }
        } else {
            // approved atau menunggu_kabag
            if ($request->jam_mulai_disetujui && $request->jam_selesai_disetujui) {
                $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . $request->jam_mulai_disetujui);
                $jamSelesaiDisetujui = Carbon::parse($transaksi->date . ' ' . $request->jam_selesai_disetujui);

                if ($jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
                    $jamSelesaiDisetujui->addDay();
                }

                $updateData['jam_mulai_disetujui']   = $jamMulaiDisetujui->format('H:i:s');
                $updateData['jam_selesai_disetujui'] = $jamSelesaiDisetujui->format('H:i:s');
            }

            if (empty($transaksi->approved_at)) {
                $updateData['approved_at'] = now()->toDateString();
            }

            if ($approvedKabagAt) {
                $updateData['approved_kabag_at'] = $approvedKabagAt;
            } elseif ($finalStatus === 'approved' && ($isTimBagianUmum || $isApproverKabag) && empty($transaksi->approved_kabag_at)) {
                $updateData['approved_kabag_at'] = now();
            }
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        $jamMulaiResp = isset($updateData['jam_mulai_disetujui']) && $updateData['jam_mulai_disetujui']
            ? substr($updateData['jam_mulai_disetujui'], 0, 5)
            : ($transaksi->jam_mulai_disetujui ? substr($transaksi->jam_mulai_disetujui, 0, 5) : null);

        $jamSelesaiResp = isset($updateData['jam_selesai_disetujui']) && $updateData['jam_selesai_disetujui']
            ? substr($updateData['jam_selesai_disetujui'], 0, 5)
            : ($transaksi->jam_selesai_disetujui ? substr($transaksi->jam_selesai_disetujui, 0, 5) : null);

        return response()->json([
            'success'               => true,
            'status'                => $finalStatus,
            'jam_mulai_disetujui'   => $jamMulaiResp,
            'jam_selesai_disetujui' => $jamSelesaiResp,
            'note'                  => $noteKetua,
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
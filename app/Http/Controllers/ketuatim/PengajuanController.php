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

        $bulanParam = $request->get('bulan');
        $currentYear = (int) now()->year;
        $selectedYear = $currentYear;
        $selectedMonth = null; // null = semua bulan tahun berjalan

        if ($bulanParam && $bulanParam !== 'all') {
            if (preg_match('/^(\d{4})-all$/i', $bulanParam, $matches)) {
                $selectedYear = (int) $matches[1];
                $selectedMonth = null;
                $bulan = $selectedYear . '-all';
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', $bulanParam, $matches)) {
                $selectedYear = (int) $matches[1];
                $selectedMonth = (int) $matches[2];
                $bulan = sprintf('%04d-%02d', $selectedYear, $selectedMonth);
            } else {
                $bulan = 'all';
            }
        } else {
            $bulan = 'all';
        }

        $search = trim((string) $request->get('nip'));
        $status = $request->get('status', 'all');
        $sort   = in_array(strtolower($request->get('sort', 'desc')), ['desc', 'asc', 'priority'])
            ? strtolower($request->get('sort', 'desc'))
            : 'desc';

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
                ) as has_presensi'),
                DB::raw('(
                    SELECT DATE_FORMAT(pr.jam_selesai, "%H:%i") FROM t_presensi pr
                    WHERE pr.niplama = p.nip_lama
                    AND DATE(pr.tanggal) = t.date
                    LIMIT 1
                ) as jam_selesai_presensi')
            ])
            ->whereYear('t.date', $selectedYear);

        if ($selectedMonth !== null) {
            $query->whereMonth('t.date', $selectedMonth);
        }

        if ($search !== '') {
            $query->where('p.nip', $search);
        }

        if ($status && $status !== 'all') {
            $query->where('t.status', $status);
        }

        if ($sort === 'asc') {
            $query->orderBy('t.date', 'asc')->orderBy('t.id_transaksi', 'asc');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE WHEN t.status = 'pending' THEN 0 WHEN t.status = 'menunggu_kabag' THEN 1 WHEN t.status = 'approved' THEN 2 ELSE 3 END")
                ->orderBy('t.date', 'desc')
                ->orderBy('t.id_transaksi', 'desc');
        } else { // desc (default: terbaru ke terlama)
            $query->orderBy('t.date', 'desc')->orderBy('t.id_transaksi', 'desc');
        }

        $pengajuan = $query
            ->paginate(10)
            ->withQueryString();

        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('ketua-tim.pengajuan', compact('pengajuan', 'hariLibur', 'bulan', 'search', 'status', 'tim', 'sort', 'selectedYear', 'selectedMonth'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'nullable',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected,menunggu_kabag',
            'note'                  => 'nullable|string',
            'uraian'                => 'nullable|string|max:2000',
        ]);

        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.id_transaksi', $id)
            ->select('t.*', 'p.nip_lama')
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

        if ($finalStatus !== 'rejected' && $transaksi->jam_mulai && $transaksi->jam_selesai) {
            $mulaiAwal = substr($transaksi->jam_mulai, 0, 5);
            $selesaiAwal = substr($transaksi->jam_selesai, 0, 5);
            $mulaiBaru = $request->jam_mulai_disetujui ? substr($request->jam_mulai_disetujui, 0, 5) : null;
            $selesaiBaru = $request->jam_selesai_disetujui ? substr($request->jam_selesai_disetujui, 0, 5) : null;

            if (($mulaiBaru !== $mulaiAwal || $selesaiBaru !== $selesaiAwal) && empty($noteKetua)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catatan wajib diisi jika jam lembur yang disetujui berbeda dari jam pengajuan.'
                ], 422);
            }
        }

        $updateData = [
            'status'         => $finalStatus,
            'note'           => $noteKetua !== '' ? $noteKetua : null,
            'eligible'       => null,
            'user_edited'    => session('user')['nama'] ?? session('user')['nip'],
            'tanggal_edited' => now(),
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

                // Validasi: Jam selesai disetujui tidak boleh melebihi jam kepulangan presensi (jika data presensi tersedia)
                $presensi = DB::table('t_presensi')
                    ->where('niplama', $transaksi->nip_lama)
                    ->whereDate('tanggal', $transaksi->date)
                    ->first();

                if ($presensi && $presensi->jam_selesai) {
                    $jamSelesaiPresensi = Carbon::parse($transaksi->date . ' ' . Carbon::parse($presensi->jam_selesai)->format('H:i:s'));
                    if ($jamSelesaiPresensi->lessThan($jamMulaiDisetujui)) {
                        $jamSelesaiPresensi->addDay();
                    }
                    if ($jamSelesaiDisetujui->greaterThan($jamSelesaiPresensi)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Jam selesai disetujui (' . $jamSelesaiDisetujui->format('H:i') . ') tidak boleh melebihi jam kepulangan presensi pegawai (' . $jamSelesaiPresensi->format('H:i') . ').'
                        ], 422);
                    }
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

        // Cek pengubahan uraian kegiatan (syarat: presensi sudah ada)
        if ($request->filled('uraian') && trim($request->uraian) !== trim($transaksi->uraian ?? '')) {
            $hasPresensi = DB::table('t_presensi')
                ->where('niplama', $transaksi->nip_lama)
                ->whereDate('tanggal', $transaksi->date)
                ->exists();

            if (!$hasPresensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uraian kegiatan hanya dapat diubah jika data presensi pegawai sudah tersedia.'
                ], 422);
            }

            $updateData['uraian'] = trim($request->uraian);
            $updateData['user_edited'] = session('user')['nama'] ?? session('user')['nip'];
            $updateData['tanggal_edited'] = now();
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
            'uraian'                => $updateData['uraian'] ?? $transaksi->uraian,
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
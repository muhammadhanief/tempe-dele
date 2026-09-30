<?php

namespace App\Http\Controllers\ketuatim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\KoreksiLembur;

class KabagUmumPengajuanController extends Controller
{
    use KoreksiLembur;
    /**
     * Memastikan user yang mengakses adalah Kabag Umum, Ketua Tim Bagian Umum, atau Admin.
     */
    private function checkAccess(): bool
    {
        $role = session('user')
            ? DB::table('m_pegawai')->where('nip', session('user')['nip'])->value('role')
            : null;

        if (in_array($role, ['admin', 'superadmin', 'pimpinan'])) {
            return true;
        }

        $nipSess = session('user')['nip'] ?? null;
        $nipLamaSess = session('user')['nip_lama'] ?? null;

        // Cek pejabat aktif sebagai Kepala Bagian Umum
        $isPejabatKabag = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->where('status', 'aktif')
            ->where(function ($q) use ($nipSess, $nipLamaSess) {
                if ($nipSess) $q->where('nip', $nipSess);
                if ($nipLamaSess) $q->orWhere('nip_lama', $nipLamaSess);
            })->exists();

        if ($isPejabatKabag) {
            return true;
        }

        // Cek apakah ketua dari Tim Bagian Umum
        $isKetuaTimUmum = DB::table('m_tim')
            ->where(function ($q) use ($nipSess, $nipLamaSess) {
                if ($nipSess) $q->where('nipbaru_ketua', $nipSess);
                if ($nipLamaSess) $q->orWhere('niplama_ketua', $nipLamaSess);
            })
            ->where(function ($q) {
                $q->where('nama_tim', 'like', '%Bagian Umum%')
                  ->orWhere('kode_tim', 'QrBzgE3O3lEqVPjy');
            })
            ->exists();

        return $isKetuaTimUmum;
    }

    public function index(Request $request)
    {
        if (!$this->checkAccess()) {
            abort(403, 'Akses khusus Kepala Bagian Umum.');
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

        $statusFilter = $request->get('status', 'all');

        // Filter pengajuan: Seluruh Tim Kerja BPS (Monitoring Seluruh Satker & Approval Tim Lain)
        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
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

        // Hitung statistik untuk periode yang dipilih
        $statsBase = clone $query;
        $allTransactions = $statsBase->get();
        $stats = [
            'total'          => $allTransactions->count(),
            'menunggu_kabag' => $allTransactions->where('status', 'menunggu_kabag')->count(),
            'approved'       => $allTransactions->where('status', 'approved')->count(),
            'rejected'       => $allTransactions->where('status', 'rejected')->count(),
            'cancelled'      => $allTransactions->where('status', 'cancelled')->count(),
        ];

        // Terapkan filter status jika dipilih
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('t.status', $statusFilter);
        }

        // Opsi sorting tanggal: desc (default: terbaru), asc (terlama), priority
        $sort = in_array(strtolower($request->get('sort', 'desc')), ['desc', 'asc', 'priority'])
            ? strtolower($request->get('sort', 'desc'))
            : 'desc';

        if ($sort === 'asc') {
            $query->orderBy('t.date', 'asc')->orderBy('t.id_transaksi', 'asc');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE WHEN t.status = 'menunggu_kabag' THEN 0 WHEN t.status = 'pending' THEN 1 WHEN t.status = 'approved' THEN 2 ELSE 3 END")
                ->orderBy('t.date', 'desc')
                ->orderBy('t.id_transaksi', 'desc');
        } else { // desc (default: terbaru ke terlama)
            $query->orderBy('t.date', 'desc')->orderBy('t.id_transaksi', 'desc');
        }

        $pengajuan = $query
            ->paginate(10)
            ->appends($request->query());

        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('kabag-umum.pengajuan', compact('pengajuan', 'hariLibur', 'bulan', 'statusFilter', 'stats', 'sort', 'selectedYear', 'selectedMonth'));
    }

    public function approve(Request $request, $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status'                => 'required|in:approved,rejected',
            'jam_mulai_disetujui'   => 'nullable',
            'jam_selesai_disetujui' => 'nullable',
            'note_kabag'            => 'nullable|string',
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

        // Pengajuan berstatus pending belum boleh diproses oleh Kabag Umum
        if ($transaksi->status === 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan ini masih menunggu persetujuan Ketua Tim.'], 422);
        }

        // KUNCI STATUS: Jika sudah disetujui final atau ditolak, status keputusannya tidak boleh dibalik
        if (in_array($transaksi->status, ['approved', 'rejected']) && $request->status !== $transaksi->status) {
            return response()->json([
                'success' => false,
                'message' => 'Status keputusan sudah final dan terkunci (' . ($transaksi->status === 'approved' ? 'Disetujui Final' : 'Ditolak') . '). Anda hanya dapat mengoreksi jam disetujui atau catatan.'
            ], 422);
        }

        $noteKabag = trim($request->note_kabag ?? '');

        // Menentukan status akhir (tetap sama jika sudah final, atau sesuai pilihan jika menunggu_kabag)
        $finalStatus = in_array($transaksi->status, ['approved', 'rejected']) ? $transaksi->status : $request->status;

        $updateData = [
            'status'            => $finalStatus,
            'note_kabag'        => $noteKabag !== '' ? $noteKabag : null,
            'approved_kabag_at' => now(),
        ];

        if ($finalStatus === 'approved') {
            if (empty($transaksi->approved_at)) {
                $updateData['approved_at'] = now()->toDateString();
            }

            // Gunakan jam yang disetujui ketua tim sebagai basis, atau jam yang diinput Kabag Umum jika diubah
            $jamMulai = $request->filled('jam_mulai_disetujui') 
                ? $request->jam_mulai_disetujui 
                : ($transaksi->jam_mulai_disetujui ?? $transaksi->jam_mulai);

            $jamSelesai = $request->filled('jam_selesai_disetujui') 
                ? $request->jam_selesai_disetujui 
                : ($transaksi->jam_selesai_disetujui ?? $transaksi->jam_selesai);

            if ($jamMulai) {
                $dtMulai = Carbon::parse($transaksi->date . ' ' . $jamMulai);
                $updateData['jam_mulai_disetujui'] = $dtMulai->format('H:i:s');
            }

            if ($jamSelesai) {
                $dtSelesai = Carbon::parse($transaksi->date . ' ' . $jamSelesai);
                if (isset($dtMulai) && $dtSelesai->lessThan($dtMulai)) {
                    $dtSelesai->addDay();
                }

                // Validasi: Jam selesai disetujui tidak boleh melebihi jam kepulangan presensi (jika data presensi tersedia)
                $presensi = DB::table('t_presensi')
                    ->where('niplama', $transaksi->nip_lama)
                    ->whereDate('tanggal', $transaksi->date)
                    ->first();

                if ($presensi && $presensi->jam_selesai) {
                    $jamSelesaiPresensi = Carbon::parse($transaksi->date . ' ' . Carbon::parse($presensi->jam_selesai)->format('H:i:s'));
                    if (isset($dtMulai) && $jamSelesaiPresensi->lessThan($dtMulai)) {
                        $jamSelesaiPresensi->addDay();
                    }
                    if ($dtSelesai->greaterThan($jamSelesaiPresensi)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Jam selesai disetujui (' . $dtSelesai->format('H:i') . ') tidak boleh melebihi jam kepulangan presensi pegawai (' . $jamSelesaiPresensi->format('H:i') . ').'
                        ], 422);
                    }
                }

                $updateData['jam_selesai_disetujui'] = $dtSelesai->format('H:i:s');
            }
        } else {
            $updateData['jam_mulai_disetujui']   = null;
            $updateData['jam_selesai_disetujui'] = null;
        }

        // Cek apakah pengajuan dari Tim Bagian Umum
        $isTimBagianUmum = false;
        if (!empty($transaksi->tim_kode_tim)) {
            $tim = DB::table('m_tim')->where('kode_tim', $transaksi->tim_kode_tim)->first();
            if ($tim && (str_contains(strtolower($tim->nama_tim), 'bagian umum') || $tim->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                $isTimBagianUmum = true;
            }
        }

        // Cek pengubahan uraian kegiatan (khusus untuk tim Bagian Umum & syarat presensi sudah ada)
        if ($request->filled('uraian') && trim($request->uraian) !== trim($transaksi->uraian ?? '')) {
            $hasPresensi = DB::table('t_presensi')
                ->where('niplama', $transaksi->nip_lama)
                ->whereDate('tanggal', $transaksi->date)
                ->exists();

            if ($isTimBagianUmum && $hasPresensi) {
                $updateData['uraian'] = trim($request->uraian);
                $updateData['user_edited'] = session('user')['nama'] ?? session('user')['nip'];
                $updateData['tanggal_edited'] = now();
            }
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        if ($finalStatus === 'approved') {
            $this->koreksiUntukTransaksi($id);
        }

        $jamMulaiResp = isset($updateData['jam_mulai_disetujui']) && $updateData['jam_mulai_disetujui']
            ? substr($updateData['jam_mulai_disetujui'], 0, 5)
            : ($transaksi->jam_mulai_disetujui ? substr($transaksi->jam_mulai_disetujui, 0, 5) : null);

        $jamSelesaiResp = isset($updateData['jam_selesai_disetujui']) && $updateData['jam_selesai_disetujui']
            ? substr($updateData['jam_selesai_disetujui'], 0, 5)
            : ($transaksi->jam_selesai_disetujui ? substr($transaksi->jam_selesai_disetujui, 0, 5) : null);

        if ($finalStatus === 'rejected') {
            $jamMulaiResp = null;
            $jamSelesaiResp = null;
        }

        return response()->json([
            'success'               => true,
            'status'                => $finalStatus,
            'jam_mulai_disetujui'   => $jamMulaiResp,
            'jam_selesai_disetujui' => $jamSelesaiResp,
            'note_kabag'            => $noteKabag,
            'uraian'                => $updateData['uraian'] ?? $transaksi->uraian,
            'message'               => $finalStatus === 'approved' 
                ? 'Pengajuan lembur berhasil disetujui (Final).' 
                : 'Pengajuan lembur berhasil ditolak.'
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

        $presensi = DB::table('t_presensi')
            ->whereDate('tanggal', $transaksi->date)
            ->where('niplama', $transaksi->nip_lama)
            ->first();

        return response()->json([
            'nama'       => $transaksi->nama,
            'nip'        => $transaksi->nip,
            'tanggal'    => Carbon::parse($transaksi->date)->translatedFormat('l, d F Y'),
            'status'     => $presensi->status ?? null,
            'jam_masuk'  => $presensi ? Carbon::parse($presensi->jam_mulai)->format('H:i') : null,
            'jam_pulang' => $presensi ? Carbon::parse($presensi->jam_selesai)->format('H:i') : null,
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

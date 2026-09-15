<?php

namespace App\Http\Controllers\ketuatim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KabagUmumPengajuanController extends Controller
{
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

        $bulan = $request->get('bulan', now()->format('Y-m'));
        $statusFilter = $request->get('status', 'all');

        try {
            $periode = Carbon::parse($bulan . '-01');
        } catch (\Exception $e) {
            $periode = Carbon::now();
            $bulan = $periode->format('Y-m');
        }

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
                ) as has_presensi')
            ])
            ->whereYear('t.date', $periode->year)
            ->whereMonth('t.date', $periode->month);

        // Hitung statistik untuk bulan yang dipilih
        $statsBase = clone $query;
        $allTransactions = $statsBase->get();
        $stats = [
            'total'          => $allTransactions->count(),
            'menunggu_kabag' => $allTransactions->where('status', 'menunggu_kabag')->count(),
            'approved'       => $allTransactions->where('status', 'approved')->count(),
            'rejected'       => $allTransactions->where('status', 'rejected')->count(),
        ];

        // Terapkan filter status jika dipilih
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('t.status', $statusFilter);
        }

        // Urutkan: prioritas 'menunggu_kabag' paling atas, lalu tanggal terbaru
        $pengajuan = $query
            ->orderByRaw("CASE WHEN t.status = 'menunggu_kabag' THEN 0 WHEN t.status = 'pending' THEN 1 WHEN t.status = 'approved' THEN 2 ELSE 3 END")
            ->orderBy('t.date', 'desc')
            ->paginate(10)
            ->appends($request->query());

        $hariLibur = DB::table('m_hari_libur')
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('kabag-umum.pengajuan', compact('pengajuan', 'hariLibur', 'bulan', 'statusFilter', 'stats'));
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
        ]);

        $transaksi = DB::table('t_transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $noteKabag = trim($request->note_kabag ?? '');

        $updateData = [
            'status'            => $request->status,
            'note_kabag'        => $noteKabag !== '' ? $noteKabag : null,
            'approved_kabag_at' => now(),
        ];

        if ($request->status === 'approved') {
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
                $updateData['jam_selesai_disetujui'] = $dtSelesai->format('H:i:s');
            }
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => $request->status === 'approved' 
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

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\KoreksiLembur;

class PengajuanController extends Controller
{
    use KoreksiLembur;

    public function index(Request $request)
    {
        $bulan  = $request->get('bulan', now()->format('Y-m'));
        $search = trim((string) $request->get('nip'));
        $status = $request->get('status', 'all');
        $sort   = in_array(strtolower($request->get('sort', 'priority')), ['priority', 'desc', 'asc'])
            ? strtolower($request->get('sort', 'priority'))
            : 'priority';

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
            ]);

        if ($bulan) {
            try {
                $periode = Carbon::parse($bulan . '-01');

                $bulan = $periode->format('Y-m');

                $query->whereYear('t.date', $periode->year)
                    ->whereMonth('t.date', $periode->month);
            } catch (\Exception $e) {
                $bulan = now()->format('Y-m');
            }
        }

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
            $query->orderByRaw("CASE WHEN t.status = 'menunggu_kabag' THEN 0 WHEN t.status = 'pending' THEN 1 WHEN t.status = 'approved' THEN 2 ELSE 3 END")
                ->orderBy('t.date', 'desc')
                ->orderBy('t.id_transaksi', 'desc');
        }

        $pengajuan = $query
            ->paginate(10)
            ->withQueryString();

        $hariLibur = DB::table('m_hari_libur')->orderBy('tanggal', 'asc')->get();

        return view('admin.pengajuan', compact('pengajuan', 'hariLibur', 'bulan', 'search', 'status', 'sort'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'nullable',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected,cancelled',
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

        $noteKetua = trim($request->note ?? '');

        if ($request->status === 'cancelled') {
            if (empty($noteKetua)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan pembatalan wajib diisi pada kolom catatan.'
                ], 422);
            }

            $catatanFinal = str_starts_with($noteKetua, '[Dibatalkan Admin]') ? $noteKetua : '[Dibatalkan Admin] ' . $noteKetua;

            $updateData = [
                'status'                => 'cancelled',
                'jam_mulai_disetujui'   => null,
                'jam_selesai_disetujui' => null,
                'note'                  => $catatanFinal,
                'eligible'              => null,
                'approved_at'           => null,
                'approved_kabag_at'     => null,
                'user_edited'           => session('user')['nama'] ?? session('user')['nip'],
                'tanggal_edited'        => now(),
            ];
        } elseif ($request->status === 'approved') {
            $jamMulaiInput = $request->jam_mulai_disetujui ?? ($transaksi->jam_mulai_disetujui ? substr($transaksi->jam_mulai_disetujui, 0, 5) : substr($transaksi->jam_mulai, 0, 5));
            $jamSelesaiInput = $request->jam_selesai_disetujui ?? ($transaksi->jam_selesai_disetujui ? substr($transaksi->jam_selesai_disetujui, 0, 5) : substr($transaksi->jam_selesai, 0, 5));

            if (!$jamMulaiInput || !$jamSelesaiInput) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam mulai dan jam selesai disetujui wajib diisi.'
                ], 422);
            }

            $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . $jamMulaiInput);
            $jamSelesaiDisetujui = Carbon::parse($transaksi->date . ' ' . $jamSelesaiInput);

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

            $updateData = [
                'status'                => 'approved',
                'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
                'note'                  => $noteKetua !== '' ? $noteKetua : null,
                'eligible'              => null,
                'approved_at'           => now()->toDateString(),
                'approved_kabag_at'     => now(),
                'user_edited'           => session('user')['nama'] ?? session('user')['nip'],
                'tanggal_edited'        => now(),
            ];
        } else {
            $updateData = [
                'status'                => 'rejected',
                'jam_mulai_disetujui'   => null,
                'jam_selesai_disetujui' => null,
                'note'                  => $noteKetua !== '' ? $noteKetua : null,
                'eligible'              => null,
                'approved_at'           => now()->toDateString(),
                'approved_kabag_at'     => null,
                'user_edited'           => session('user')['nama'] ?? session('user')['nip'],
                'tanggal_edited'        => now(),
            ];
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
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        if ($request->status === 'approved') {
            $this->koreksiUntukTransaksi($id);
        }

        return response()->json([
            'success' => true,
            'uraian'  => $updateData['uraian'] ?? $transaksi->uraian,
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

    public function cancel(Request $request, $id)
    {
        $alasanRaw = $request->alasan ?? $request->alasan_batal ?? $request->note ?? '';
        $alasan = trim((string) $alasanRaw);

        if ($alasan === '') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alasan pembatalan wajib diisi.'], 422);
            }
            return back()->with('error', 'Alasan pembatalan wajib diisi.');
        }

        if (mb_strlen($alasan) > 500) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alasan pembatalan maksimal 500 karakter.'], 422);
            }
            return back()->with('error', 'Alasan pembatalan maksimal 500 karakter.');
        }

        $transaksi = DB::table('t_transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $userActor = session('user')['nama'] ?? session('user')['nip'];
        $catatanFinal = str_starts_with($alasan, '[Dibatalkan Admin]') ? $alasan : '[Dibatalkan Admin] ' . $alasan;

        DB::table('t_transaksi')->where('id_transaksi', $id)->update([
            'status'                => 'cancelled',
            'note'                  => $catatanFinal,
            'jam_mulai_disetujui'   => null,
            'jam_selesai_disetujui' => null,
            'eligible'              => null,
            'approved_at'           => null,
            'approved_kabag_at'     => null,
            'user_edited'           => $userActor,
            'tanggal_edited'        => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => 'cancelled',
                'note'    => $catatanFinal,
                'message' => 'Pengajuan lembur berhasil dibatalkan.'
            ]);
        }

        return back()->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }

}


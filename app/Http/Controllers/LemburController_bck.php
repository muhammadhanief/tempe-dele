<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LemburController extends Controller
{
    public function index(Request $request)
    {
        $nipUser  = session('user')['nip'];
        $namaUser = session('user')['nama'];
        $bulan = $request->query('bulan', now()->format('Y-m'));

        $role = DB::table('m_pegawai')->where('nip', $nipUser)->value('role');

        // Koreksi otomatis berdasarkan presensi setiap kali halaman dibuka
        $this->koreksiDariPresensi($nipUser);

        $transaksi = DB::table('t_transaksi as t')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->leftJoin('m_dokumentasi as md', 't.dokumentasi_id_dokumentasi', '=', 'md.id_dokumentasi')
            ->where('t.submitted_by_NIP', $nipUser)
            ->select('t.*', 'mt.nama_tim', 'mt.nama_ketua', 'md.file_path as file_dokumentasi')
            ->orderBy('t.date', 'desc')
            ->paginate(10);

        $idPegawai = session('id_pegawai');

        $ketuaTim = DB::table('t_anggota_tim as at')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('at.pegawai_id_pegawai', $idPegawai)
            ->where('mt.status', 'aktif')
            ->whereNotNull('mt.niplama_ketua')
            ->where('mt.niplama_ketua', '!=', $nipUser) // exclude diri sendiri sebagai ketua, sesuai logic asli
            ->select(
                'mt.niplama_ketua as nip',
                'mt.nama_ketua as nama',
                'mt.nama_tim as tim',
                'mt.kode_tim'
            )
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        if ($role === 'ketua_tim') {
            $timSendiri = DB::table('m_tim')
                ->where('niplama_ketua', $nipUser)
                ->where('status', 'aktif')
                ->select('kode_tim', 'nama_tim')
                ->first();

            if ($timSendiri) {
                $ketuaTim[] = [
                    'nip'      => $nipUser,
                    'nama'     => $namaUser,
                    'tim'      => $timSendiri->nama_tim,
                    'kode_tim' => $timSendiri->kode_tim,
                ];
            } else {
                $ketuaTim[] = [
                    'nip'      => $nipUser,
                    'nama'     => $namaUser,
                    'tim'      => 'Tim Developer',
                    'kode_tim' => 'DEV',
                ];
            }
        }

        $hariLibur = DB::table('m_hari_libur')->pluck('tanggal')->toArray();
        $view = $role === 'ketua_tim' ? 'ketua-tim.lembur' : 'lembur';
        return view($view, compact('ketuaTim', 'transaksi', 'hariLibur', 'bulan'));
    }

    private function koreksiDariPresensi(string $nipUser): void
    {
        $transaksis = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.submitted_by_NIP', $nipUser)
            ->where('t.status', 'approved')
            ->whereNull('t.eligible')
            ->select('t.*', 'p.nip_lama')
            ->get();

        foreach ($transaksis as $transaksi) {

            $presensi = DB::table('t_presensi')
                ->whereDate('tanggal', $transaksi->date)
                ->where('niplama', $transaksi->nip_lama)
                ->first();

            if (!$presensi || !$presensi->jam_selesai) {
                continue;
            }

            $tanggalCarbon     = Carbon::parse($transaksi->date);
            $isWeekend = $tanggalCarbon->isWeekend();
            $isLiburNasional = DB::table('m_hari_libur')
                ->whereDate('tanggal', $tanggalCarbon->toDateString())
                ->exists();
            $maxJam = ($isWeekend || $isLiburNasional) ? 6 : 4;
            $jamMulaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
            $batasMaksimal     = $jamMulaiPengajuan->copy()->addHours($maxJam);

            $jamSelesaiPresensi = Carbon::parse($presensi->jam_selesai);
            if ($jamSelesaiPresensi->lessThan($jamMulaiPengajuan)) {
                $jamSelesaiPresensi->addDay();
            }

            $jamSelesaiFinal = $jamSelesaiPresensi->lessThan($batasMaksimal)
                ? $jamSelesaiPresensi
                : $batasMaksimal;

            $durasi = $jamMulaiPengajuan->diffInHours($jamSelesaiFinal);
            if ($durasi < 2) {
                DB::table('t_transaksi')
                    ->where('id_transaksi', $transaksi->id_transaksi)
                    ->update([
                        'status'                => 'rejected',
                        'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                        'note'                  => 'Durasi lembur kurang dari 2 jam berdasarkan data presensi.',
                        'eligible'              => null,
                    ]);
                continue;
            }

            // Cek pelanggaran
            $pelanggaranList = [];

            $statusPresensi = strtolower(trim($presensi->status));
            if (!in_array($statusPresensi, ['wfo', 'wfol'])) {
                $pelanggaranList[] = 'status bekerja tidak sesuai kebijakan';
            }

            $jamMasuk    = Carbon::parse($presensi->jam_mulai);
            $batasLambat = Carbon::parse($transaksi->date . ' 07:31:00');
            if (!($isWeekend || $isLiburNasional) && !$jamMasuk->lessThan($batasLambat)) {
                $pelanggaranList[] = 'presensi kantor terlambat';
            }

            if (!empty($pelanggaranList)) {
                $catatan   = implode(' dan ', $pelanggaranList);
                $noteFinal = "Lembur disetujui namun tidak masuk proses bisnis karena: {$catatan}.";
                $eligible  = 0;
            } else {
                $noteFinal = $transaksi->note ?? null;
                $eligible  = 1;
            }

            DB::table('t_transaksi')
                ->where('id_transaksi', $transaksi->id_transaksi)
                ->update([
                    'jam_selesai_disetujui' => $jamSelesaiFinal->format('H:i:s'),
                    'note'                  => $noteFinal,
                    'eligible'              => $eligible,
                ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'approver_id' => 'required|string',
            'kode_tim'    => 'required|string',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'nullable',
            'uraian'      => 'required|string|max:255',
            'signature'   => 'required|string',
        ], [
            'uraian.required' => 'Uraian kegiatan wajib diisi.',
        ]);

        $nip     = session('user')['nip'];
        $tanggal = Carbon::createFromFormat('Y-m-d', $validated['tanggal']);

        $tahun             = $tanggal->year;
        $isWeekend       = $tanggal->isWeekend();
        $isLiburNasional = DB::table('m_hari_libur')  // ganti t_ → m_
            ->whereDate('tanggal', $tanggal->toDateString())
            ->exists();
        $isHariLibur = $isWeekend || $isLiburNasional;
        $hari        = $isHariLibur ? 1 : 0;

        $jamMulai   = Carbon::parse($validated['jam_mulai']);
        $jamSelesai = !empty($validated['jam_selesai'])
            ? Carbon::parse($validated['jam_selesai'])
            : null;

        $status = 'pending';
        $note   = null;

        if ($jamSelesai) {
            if ($jamSelesai->lessThan($jamMulai)) {
                $jamSelesai->addDay();
            }

            $durasi = $jamMulai->diffInHours($jamSelesai);

            if ($durasi < 2) {
                $status = 'rejected';
                $note   = 'Durasi lembur kurang dari 2 jam.';
            }
        }

        $idTransaksi = DB::table('t_transaksi')->insertGetId([
            'submitted_by_NIP'      => $nip,
            'date'                  => $tanggal->toDateString(),
            'jam_mulai'             => $jamMulai->format('H:i:s'),
            'jam_selesai'           => $jamSelesai?->format('H:i:s'),
            'jam_mulai_disetujui'   => null,
            'jam_selesai_disetujui' => null,
            'uraian'                => $validated['uraian'],
            'approver_employee_id'  => $validated['approver_id'],
            'tim_kode_tim'          => $validated['kode_tim'],
            'status'                => $status,
            'note'                  => $note,
            'submitted_at'          => now(),
            'hari'                  => $hari,
        ]);

        $signatureRaw = str_replace('data:image/png;base64,', '', $validated['signature']);
        $signatureRaw = str_replace(' ', '+', $signatureRaw);
        $fileName     = 'signatures/' . $idTransaksi . '_' . $nip . '.png';

        Storage::disk('public')->put($fileName, base64_decode($signatureRaw));

        DB::table('t_transaksi')
            ->where('id_transaksi', $idTransaksi)
            ->update(['signature_path' => $fileName]);

        $message = $status === 'rejected'
            ? 'Pengajuan tersimpan namun otomatis ditolak karena durasi lembur kurang dari 2 jam.'
            : 'Pengajuan lembur berhasil dikirim.';

        return back()->with($status === 'rejected' ? 'error' : 'success', $message);
    }

    public function timPegawai()
    {
        $idPegawai = session('id_pegawai');

        $tim = DB::table('t_anggota_tim as at')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('at.pegawai_id_pegawai', $idPegawai)
            ->select('mt.kode_tim', 'mt.nama_tim')
            ->get();

        return response()->json($tim);
    }

    public function storeDoc(Request $request, $id_transaksi)
    {
        $request->validate([
            'file_path' => 'required|url|max:255',
        ]);

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', session('user')['nip'])
            ->where('status', 'approved')
            ->firstOrFail();

        $idDok = DB::table('m_dokumentasi')->insertGetId([
            'transaksi_id' => $id_transaksi,
            'date'         => $transaksi->date,
            'file_path'    => $request->file_path,
        ]);

        DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->update(['dokumentasi_id_dokumentasi' => $idDok]);

        return back()->with('success', 'Dokumentasi berhasil disimpan.');
    }

    public function destroyDoc($id_transaksi)
    {
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', session('user')['nip'])
            ->firstOrFail();

        if ($transaksi->dokumentasi_id_dokumentasi) {
            DB::table('m_dokumentasi')
                ->where('id_dokumentasi', $transaksi->dokumentasi_id_dokumentasi)
                ->delete();

            DB::table('t_transaksi')
                ->where('id_transaksi', $id_transaksi)
                ->update(['dokumentasi_id_dokumentasi' => null]);
        }

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'required',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.id_transaksi', $id)
            ->select('t.*', 'p.nip_lama')
            ->first();

        $noteKetua = trim($request->note ?? '');
        $noteFinal = null;
        $eligible  = null;

        $jamMulaiDisetujui = Carbon::parse($transaksi->date . ' ' . $request->jam_mulai_disetujui);

        $jamSelesaiDisetujui = $request->jam_selesai_disetujui
            ? Carbon::parse($transaksi->date . ' ' . $request->jam_selesai_disetujui)
            : null;

        if ($jamSelesaiDisetujui && $jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        if ($request->status === 'approved') {

            $tanggalCarbon     = Carbon::parse($transaksi->date);
            $isWeekend = $tanggalCarbon->isWeekend();
            $isLiburNasional = DB::table('m_hari_libur')
                ->whereDate('tanggal', $tanggalCarbon->toDateString())
                ->exists();
            $maxJam = ($isWeekend || $isLiburNasional) ? 6 : 4;
            $jamMulaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
            $batasMaksimal     = $jamMulaiPengajuan->copy()->addHours($maxJam);

            $presensi = DB::table('t_presensi')
                ->whereDate('tanggal', $transaksi->date)
                ->where('niplama', $transaksi->nip_lama)
                ->first();

            if ($presensi && $presensi->jam_selesai) {

                $jamSelesaiPresensi = Carbon::parse($presensi->jam_selesai);
                if ($jamSelesaiPresensi->lessThan($jamMulaiPengajuan)) {
                    $jamSelesaiPresensi->addDay();
                }

                $jamSelesaiDisetujui = $jamSelesaiPresensi->lessThan($batasMaksimal)
                    ? $jamSelesaiPresensi
                    : $batasMaksimal;

                $durasi = $jamMulaiPengajuan->diffInHours($jamSelesaiDisetujui);
                if ($durasi < 2) {
                    DB::table('t_transaksi')->where('id_transaksi', $id)->update([
                        'status'                => 'rejected',
                        'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                        'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
                        'note'                  => 'Durasi lembur kurang dari 2 jam berdasarkan data presensi.',
                        'eligible'              => null,
                        'approved_at'           => now()->toDateString(),
                    ]);
                    return response()->json(['success' => true]);
                }

                // Cek pelanggaran
                $pelanggaranList = [];

                $statusPresensi = strtolower(trim($presensi->status));
                if (!in_array($statusPresensi, ['wfo', 'wfol'])) {
                    $pelanggaranList[] = 'status bekerja tidak sesuai kebijakan';
                }

                $jamMasuk    = Carbon::parse($presensi->jam_mulai);
                $batasLambat = Carbon::parse($transaksi->date . ' 07:31:00');
                if (!($isWeekend || $isLiburNasional) && !$jamMasuk->lessThan($batasLambat)) {
                    $pelanggaranList[] = 'presensi kantor terlambat';
                }

                if (!empty($pelanggaranList)) {
                    $catatan   = implode(' dan ', $pelanggaranList);
                    $noteFinal = "Lembur disetujui namun tidak masuk proses bisnis karena: {$catatan}.";
                    $eligible  = 0;
                } else {
                    $noteFinal = $noteKetua !== '' ? $noteKetua : null;
                    $eligible  = 1;
                }

            } else {
                // Presensi belum ada, koreksi nanti via koreksiDariPresensi()
                $noteFinal = $noteKetua !== '' ? $noteKetua : null;
                $eligible  = null;
            }

        } else {
            $noteFinal = $noteKetua !== '' ? $noteKetua : null;
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update([
            'status'                => $request->status,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui?->format('H:i:s'),
            'note'                  => $noteFinal,
            'eligible'              => $eligible,
            'approved_at'           => now()->toDateString(),
        ]);

        return response()->json(['success' => true]);
    }
}
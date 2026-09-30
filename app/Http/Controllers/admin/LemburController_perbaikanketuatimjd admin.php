<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Traits\KoreksiLembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Exports\LemburExport;
use Maatwebsite\Excel\Facades\Excel;

class LemburController extends Controller
{
    use KoreksiLembur;

    public function index(Request $request)
    {
        $bulan   = $request->query('bulan');
        $tanggal = $request->query('tanggal');
        $tim     = $request->query('tim');
        $nip     = $request->query('nip');
        $nipUser = session('user')['nip'];

        // Koreksi otomatis presensi untuk akun admin sendiri saja
        $this->koreksiDariPresensi($nipUser);

        $perPage = in_array((int) $request->get('perPage', 10), [10, 25, 50, 100])
            ? (int) $request->get('perPage', 10)
            : 10;

        $query = DB::table('t_transaksi as t')
        ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
        ->leftJoin('m_pegawai as pg', 't.submitted_by_NIP', '=', 'pg.nip')
        ->leftJoin('m_dokumentasi as md', 't.dokumentasi_id_dokumentasi', '=', 'md.id_dokumentasi')
        ->select(
            't.*',
            'mt.nama_tim',
            'mt.nama_ketua',
            'pg.nama as nama_pegawai',
            'md.file_path as file_dokumentasi'
        );

        if ($tanggal) {
            $query->whereDate('t.date', $tanggal);
        } elseif ($bulan) {
            $periode      = Carbon::parse($bulan . '-01');
            $startOfMonth = $periode->copy()->startOfMonth()->toDateString();
            $endOfMonth   = $periode->copy()->endOfMonth()->toDateString();

            $query->whereBetween('t.date', [$startOfMonth, $endOfMonth]);
        }

        if ($tim) {
            $query->where('t.tim_kode_tim', $tim);
        }

        if ($nip) {
            $query->where('t.submitted_by_NIP', $nip);
        }

        $query->orderBy('t.date', 'desc');

        $transaksi = $query->paginate($perPage)->appends($request->query());

        $idPegawai = session('id_pegawai');

        $ketuaTim = DB::table('t_anggota_tim as at')
            ->join('m_tim as mt', 'at.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('at.pegawai_id_pegawai', $idPegawai)
            ->where('mt.status', 'aktif')
            ->whereNotNull('mt.nipbaru_ketua')
            ->where('mt.nipbaru_ketua', '!=', $nipUser)
            ->select(
                'mt.nipbaru_ketua as nip',
                'mt.nama_ketua as nama',
                'mt.nama_tim as tim',
                'mt.kode_tim'
            )
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        $timSendiri = DB::table('m_tim')
            ->where('nipbaru_ketua', $nipUser)
            ->where('status', 'aktif')
            ->select('kode_tim', 'nama_tim')
            ->first();

        if ($timSendiri) {
            $ketuaTim[] = [
                'nip'      => $nipUser,
                'nama'     => session('user')['nama'],
                'tim'      => $timSendiri->nama_tim,
                'kode_tim' => $timSendiri->kode_tim,
            ];
        }

        $hariLibur = DB::table('m_hari_libur')
            ->pluck('tanggal')
            ->toArray();

        return view('admin.lembur', compact(
            'transaksi',
            'ketuaTim',
            'bulan',
            'hariLibur'
        ));
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

        $isWeekend = $tanggal->isWeekend();

        $isLiburNasional = DB::table('m_hari_libur')
            ->whereDate('tanggal', $tanggal->toDateString())
            ->exists();

        $hari = ($isWeekend || $isLiburNasional) ? 1 : 0;

        $jamMulai = Carbon::parse($validated['jam_mulai']);

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

        $signatureRaw = str_replace(
            'data:image/png;base64,',
            '',
            $validated['signature']
        );

        $signatureRaw = str_replace(' ', '+', $signatureRaw);

        $fileName = 'signatures/' . $idTransaksi . '_' . $nip . '.png';

        Storage::disk('public')->put(
            $fileName,
            base64_decode($signatureRaw)
        );

        DB::table('t_transaksi')
            ->where('id_transaksi', $idTransaksi)
            ->update([
                'signature_path' => $fileName,
            ]);

        $message = $status === 'rejected'
            ? 'Pengajuan tersimpan namun otomatis ditolak karena durasi lembur kurang dari 2 jam.'
            : 'Pengajuan lembur berhasil dikirim.';

        return back()->with(
            $status === 'rejected' ? 'error' : 'success',
            $message
        );
    }

    public function quickApprove($id)
    {
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->first();

        $jamMulaiDisetujui   = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_mulai);
        $jamSelesaiDisetujui = $transaksi->jam_selesai
            ? Carbon::parse($transaksi->date . ' ' . $transaksi->jam_selesai)
            : null;

        if ($jamSelesaiDisetujui && $jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)) {
            $jamSelesaiDisetujui->addDay();
        }

        DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->update([
                'status'                => 'approved',
                'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                'jam_selesai_disetujui' => $jamSelesaiDisetujui?->format('H:i:s'),
                'note'                  => null,
                'eligible'              => null,
                'approved_at'           => now()->toDateString(),
            ]);

        return response()->json(['success' => true]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'jam_mulai_disetujui'   => 'required',
            'jam_selesai_disetujui' => 'nullable',
            'status'                => 'required|in:approved,rejected',
            'note'                  => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->first();

        $noteKetua = trim($request->note ?? '');

        $jamMulaiDisetujui = Carbon::parse(
            $transaksi->date . ' ' . $request->jam_mulai_disetujui
        );

        $jamSelesaiDisetujui = $request->jam_selesai_disetujui
            ? Carbon::parse(
                $transaksi->date . ' ' . $request->jam_selesai_disetujui
            )
            : null;

        if (
            $jamSelesaiDisetujui &&
            $jamSelesaiDisetujui->lessThan($jamMulaiDisetujui)
        ) {
            $jamSelesaiDisetujui->addDay();
        }

        DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->update([
                'status'                => $request->status,
                'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
                'jam_selesai_disetujui' => $jamSelesaiDisetujui?->format('H:i:s'),
                'note'                  => $noteKetua !== '' ? $noteKetua : null,
                'eligible'              => $request->status === 'approved' ? null : null,
                'approved_at'           => now()->toDateString(),
            ]);

        return response()->json([
            'success' => true,
        ]);
    }

    private function koreksiDariPresensi(string $nipUser): void
    {
        $tanggalList = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nipUser)
            ->where('status', 'approved')
            ->whereNull('eligible')
            ->pluck('date')
            ->toArray();

        $this->koreksiUntukTanggal($tanggalList);
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

    public function allPegawai()
    {
        $pegawai = DB::table('m_pegawai')
            ->select('nip', 'nama')
            ->orderBy('nama')
            ->get();

        return response()->json($pegawai);
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->query('bulan');
        $tim   = $request->query('tim') ?: null;
        $nip   = $request->query('nip') ?: null;

        $namaBulan = Carbon::parse($bulan . '-01')
            ->translatedFormat('F_Y');

        $filename = "Lembur_{$namaBulan}.xlsx";

        return Excel::download(
            new LemburExport($bulan, $tim, $nip),
            $filename
        );
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
            ->update([
                'dokumentasi_id_dokumentasi' => $idDok,
            ]);

        return back()->with(
            'success',
            'Dokumentasi berhasil disimpan.'
        );
    }

    public function destroyDoc($id_transaksi)
    {
        $transaksi = DB::table('t_transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('submitted_by_NIP', session('user')['nip'])
            ->firstOrFail();

        if ($transaksi->dokumentasi_id_dokumentasi) {

            DB::table('m_dokumentasi')
                ->where(
                    'id_dokumentasi',
                    $transaksi->dokumentasi_id_dokumentasi
                )
                ->delete();

            DB::table('t_transaksi')
                ->where('id_transaksi', $id_transaksi)
                ->update([
                    'dokumentasi_id_dokumentasi' => null,
                ]);
        }

        return back()->with(
            'success',
            'Dokumentasi berhasil dihapus.'
        );
    }

    public function updateUraian(Request $request, $id)
    {
        $request->validate([
            'uraian' => 'required|string|max:500',
        ]);

        DB::table('t_transaksi')
            ->where('id_transaksi', $id)
            ->update([
                'uraian' => $request->uraian,
            ]);

        return back()->with(
            'success',
            'Uraian berhasil diperbarui.'
        );
    }
}

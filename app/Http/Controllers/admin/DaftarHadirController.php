<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DaftarHadirController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.status', 'approved')
            ->where(function ($q) {
                $q->where('t.eligible', 1)->orWhereNull('t.eligible');
            })
            ->whereDate('t.date', $tanggal)
            ->select(
                't.date', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui',
                DB::raw("(SELECT DATE_FORMAT(pr.jam_selesai, '%H:%i') FROM t_presensi pr WHERE pr.niplama = p.nip_lama AND DATE(pr.tanggal) = DATE(t.date) ORDER BY pr.id_presensi DESC LIMIT 1) as jam_pulang"),
                'p.nama', 'p.nip', 'p.nip_lama',
                'mt.kode_tim', 'mt.nama_tim',
                't.signature_path'
            )
            ->orderBy('p.nama');

        if ($request->filled('tim')) {
            $query->where('t.tim_kode_tim', $request->tim);
        }

        if ($request->filled('nip')) {
            $query->where('p.nip', $request->nip);
        }

        $daftarHadir = $query->get();
        $tim = DB::table('m_tim')->select('kode_tim', 'nama_tim')->get();

        return view('admin.daftar_hadir', compact('daftarHadir', 'tanggal', 'tim'));
    }

    public function download(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $jenis   = $request->get('jenis', 'pns');

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.status', 'approved')
            ->where(function ($q) {
                $q->where('t.eligible', 1)->orWhereNull('t.eligible');
            })
            ->whereDate('t.date', $tanggal)
            ->select(
                't.date', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui',
                DB::raw("(SELECT DATE_FORMAT(pr.jam_selesai, '%H:%i') FROM t_presensi pr WHERE pr.niplama = p.nip_lama AND DATE(pr.tanggal) = DATE(t.date) ORDER BY pr.id_presensi DESC LIMIT 1) as jam_pulang"),
                'p.nama', 'p.nip', 'p.nip_lama',
                'mt.kode_tim', 'mt.nama_tim',
                't.signature_path'
            )
            ->orderBy('p.nama');

        if ($jenis === 'pns') {
            $query->where(function ($q) {
                $q->whereNull('p.email')
                  ->orWhere('p.email', '')
                  ->orWhere('p.email', 'not like', '%-pppk@bps.go.id');
            });
        } else {
            $query->where('p.email', 'like', '%-pppk@bps.go.id');
        }

        if ($request->filled('tim')) $query->where('t.tim_kode_tim', $request->tim);
        if ($request->filled('nip')) $query->where('p.nip', $request->nip);

        $daftarHadir  = $query->get();
        $tanggalLabel = \Carbon\Carbon::parse($tanggal)->translatedFormat('d/m/Y');
        $namaTim      = $request->filled('tim')
            ? DB::table('m_tim')->where('kode_tim', $request->tim)->value('nama_tim') ?? ''
            : '';
        $kbu = null;
        if ($request->filled('kbu') || $request->filled('kbu_id') || $request->filled('id_kbu')) {
            $val = $request->get('kbu') ?: ($request->get('kbu_id') ?: $request->get('id_kbu'));
            $kbu = DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where(function($q) use ($val) {
                $q->where('id_pejabat', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->orWhere('nama', $val);
            })->first();
            if (!$kbu) {
                $peg = DB::table('m_pegawai')->where('id_pegawai', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->first();
                if ($peg) {
                    $kbu = (object) [
                        'nama'     => $peg->nama,
                        'jabatan'  => 'Kepala Bagian Umum',
                        'nip'      => $peg->nip,
                        'nip_lama' => $peg->nip_lama,
                    ];
                }
            }
        }
        if (!$kbu) {
            $kbu = DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where('status', 'aktif')->orderByDesc('tahun')->first();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'dokumen.daftar_hadir',
            compact('daftarHadir', 'tanggalLabel', 'namaTim', 'kbu')
        )->setPaper('a4', 'portrait');

        $labelJenis = $jenis === 'pns' ? 'PNS' : 'PPPK';

        return $pdf->download('Daftar_Hadir_' . $labelJenis . '_' . $tanggal . '.pdf');
    }

    /**
     * Tampilan cetak Daftar Hadir dengan layout A4 dan pemicu window.print() otomatis
     */
    public function print(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $jenis   = $request->get('jenis', 'pns');

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.status', 'approved')
            ->where(function ($q) {
                $q->where('t.eligible', 1)->orWhereNull('t.eligible');
            })
            ->whereDate('t.date', $tanggal)
            ->select(
                't.date', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui',
                DB::raw("(SELECT DATE_FORMAT(pr.jam_selesai, '%H:%i') FROM t_presensi pr WHERE pr.niplama = p.nip_lama AND DATE(pr.tanggal) = DATE(t.date) ORDER BY pr.id_presensi DESC LIMIT 1) as jam_pulang"),
                'p.nama', 'p.nip', 'p.nip_lama',
                'mt.kode_tim', 'mt.nama_tim',
                't.signature_path'
            )
            ->orderBy('p.nama');

        if ($jenis === 'pns') {
            $query->where(function ($q) {
                $q->whereNull('p.email')
                  ->orWhere('p.email', '')
                  ->orWhere('p.email', 'not like', '%-pppk@bps.go.id');
            });
        } elseif ($jenis === 'pppk') {
            $query->where('p.email', 'like', '%-pppk@bps.go.id');
        }

        if ($request->filled('tim')) $query->where('t.tim_kode_tim', $request->tim);
        if ($request->filled('nip')) $query->where('p.nip', $request->nip);

        $daftarHadir  = $query->get();
        $tanggalLabel = \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
        $namaTim      = $request->filled('tim')
            ? DB::table('m_tim')->where('kode_tim', $request->tim)->value('nama_tim') ?? ''
            : '';

        // Siapkan signature dalam format data URI Base64 agar dapat di-render langsung di browser print
        foreach ($daftarHadir as $item) {
            $item->signature_src = null;
            if ($item->signature_path) {
                $fullPath = storage_path('app/public/' . $item->signature_path);
                if (file_exists($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $base64 = base64_encode(file_get_contents($fullPath));
                    $item->signature_src = "data:{$mime};base64,{$base64}";
                }
            }
        }

        $kbu = null;
        if ($request->filled('kbu') || $request->filled('kbu_id') || $request->filled('id_kbu')) {
            $val = $request->get('kbu') ?: ($request->get('kbu_id') ?: $request->get('id_kbu'));
            $kbu = DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where(function($q) use ($val) {
                $q->where('id_pejabat', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->orWhere('nama', $val);
            })->first();
            if (!$kbu) {
                $peg = DB::table('m_pegawai')->where('id_pegawai', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->first();
                if ($peg) {
                    $kbu = (object) [
                        'nama'     => $peg->nama,
                        'jabatan'  => 'Kepala Bagian Umum',
                        'nip'      => $peg->nip,
                        'nip_lama' => $peg->nip_lama,
                    ];
                }
            }
        }
        if (!$kbu) {
            $kbu = DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where('status', 'aktif')->orderByDesc('tahun')->first();
        }

        $labelJenis = strtoupper($jenis);

        return view('dokumen.daftar_hadir_print', compact(
            'daftarHadir', 'tanggalLabel', 'namaTim', 'kbu', 'labelJenis', 'tanggal'
        ));
    }
}

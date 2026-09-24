<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;

class DokumenGenerateController extends Controller
{
    private function getPejabat()
    {
        $pejabat = DB::table('m_pejabat')->where('status', 'aktif')->get();
        $ppk  = $pejabat->firstWhere('jabatan', 'PPK');
        $kbps = $pejabat->firstWhere('jabatan', 'Kepala BPS');
        $kbu  = $pejabat->firstWhere('jabatan', 'Kepala Bagian Umum');
        return [$ppk, $kbps, $kbu];
    }

    private function getNomorSurat(string $bulan): string
    {
        $dt = Carbon::parse($bulan . '-01');
        $urutan = DB::table('t_dokumen')
            ->whereYear('generated_at', $dt->year)
            ->whereMonth('generated_at', $dt->month)
            ->count() + 1;
        $nomorUrut = str_pad($urutan, 5, '0', STR_PAD_LEFT);
        return "558.1/{$nomorUrut}/RT.512/{$dt->year}";
    }

    private function getLiburNasional(int $tahun): array
    {
        $fallback = [
            "$tahun-01-01",
            "$tahun-05-01",
            "$tahun-06-01",
            "$tahun-08-17",
            "$tahun-12-25",
        ];

        try {
            $response = Http::timeout(5)->get('https://api-harilibur.vercel.app/api', [
                'year' => $tahun,
            ]);

            if (!$response->ok()) {
                return $fallback;
            }

            $result = collect($response->json())
                ->where('is_national_holiday', true)
                ->pluck('holiday_date')
                ->toArray();

            return !empty($result) ? $result : $fallback;

        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function hariKerjaPertama(int $bulan, int $tahun): Carbon
    {
        $liburNasional = $this->getLiburNasional($tahun);
        $tanggal       = Carbon::create($tahun, $bulan, 1);

        while (true) {
            $isWeekend = $tanggal->isWeekend();
            $isHoliday = in_array($tanggal->format('Y-m-d'), $liburNasional);

            if (!$isWeekend && !$isHoliday) {
                break;
            }

            $tanggal->addDay();
        }

        return $tanggal;
    }

    public function spkl(Request $request)
    {
        $bulan  = $request->get('bulan', now()->format('Y-m'));
        $jenis  = $request->get('jenis', 'pns');
        $format = $request->get('format', 'pdf');
        $type   = 'spkl_' . $jenis . '_' . $format;

        [$tahun, $bln] = explode('-', $bulan);
        $dt = Carbon::parse($bulan . '-01');

        $existing = DB::table('t_dokumen')
            ->where('periode', $bulan)
            ->where('type', $type)
            ->first();
        if ($existing) {
            return redirect()->route('admin.dokumen')->with('info', 'Dokumen sudah pernah digenerate.');
        }

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where('eligible', 1)
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select('p.nama', 'p.nip', 'p.nip_lama', 't.date', 't.uraian')
            ->orderBy('p.nama');

        if ($jenis === 'pns') {
            $query->where('p.email', 'not like', '%-pppk@bps.go.id');
        } else {
            $query->where('p.email', 'like', '%-pppk@bps.go.id');
        }

        $pegawai = $query->get()->groupBy('nip')->map(function ($rows) {
            $first   = $rows->first();
            $tanggal = $rows->pluck('date')
                ->map(fn($d) => (int) date('j', strtotime($d)))
                ->sort()->values()->implode(', ');
            $uraian  = $rows->pluck('uraian')->unique()->filter()
                ->map(fn($u) => '- ' . $u)->implode("\n");
            return (object) [
                'nama'           => $first->nama,
                'nip_lama'       => $first->nip_lama,
                'tanggal_lembur' => $tanggal,
                'uraian'         => $uraian,
            ];
        })->values();

        [$ppk, , $kbu] = $this->getPejabat();
        $nomorSurat = $request->get('nomor_surat', $this->getNomorSurat($bulan));
        $bulanLabel = $dt->translatedFormat('F');
        $tahun      = $dt->year;
        $tanggalTtd = $this->hariKerjaPertama((int) $bln, (int) $tahun)
                        ->translatedFormat('d F Y');

        if ($format === 'xlsx') {
            $fileBlob = Excel::raw(
                new \App\Exports\SpklExport(compact('pegawai', 'ppk', 'kbu', 'nomorSurat', 'bulanLabel', 'tahun', 'tanggalTtd')),
                \Maatwebsite\Excel\Excel::XLSX
            );
            DB::table('t_dokumen')->insert([
                'type'         => $type,
                'periode'      => $bulan,
                'generated_at' => now(),
                'file_blob'    => $fileBlob,
            ]);
            return redirect()->route('admin.dokumen')->with('success', 'SPKL XLSX berhasil digenerate.');
        }
        
        ini_set('memory_limit', '512M');

        $pdf = Pdf::loadView('dokumen.spkl', compact(
            'pegawai', 'ppk', 'kbu', 'nomorSurat', 'bulanLabel', 'tahun', 'tanggalTtd'
        ))->setPaper('a4', 'portrait');
        
        $pdf->getDomPDF()->add_info('Title', "SPKL_" . strtoupper($jenis) . "_" . $bulan);

        DB::table('t_dokumen')->insert([
            'type'         => $type,
            'periode'      => $bulan,
            'generated_at' => now(),
            'file_blob'    => $pdf->output(),
        ]);

        return redirect()->route('admin.dokumen')->with('success', 'SPKL berhasil digenerate.');
    }

    public function laporan(Request $request, string $jenis)
    {
        $bulan  = $request->get('bulan', now()->format('Y-m'));
        $format = $request->get('format', 'pdf');
        $type   = 'laporan_' . $jenis . '_' . $format;

        [$tahun, $bln] = explode('-', $bulan);
        $dt = Carbon::parse($bulan . '-01');

        $existing = DB::table('t_dokumen')
            ->where('periode', $bulan)
            ->where('type', $type)
            ->first();
        if ($existing) {
            return redirect()->route('admin.dokumen')->with('info', 'Dokumen sudah pernah digenerate.');
        }

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where('eligible', 1)
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select('p.nama', 'p.nip', 'p.nip_lama', 't.date', 't.uraian')
            ->orderBy('p.nama')
            ->orderBy('t.date');

        if ($jenis === 'pns') {
            $query->where('p.email', 'not like', '%-pppk@bps.go.id');
        } else {
            $query->where('p.email', 'like', '%-pppk@bps.go.id');
        }

        $pegawai = $query->get()->groupBy('nip')->map(function ($rows) {
            $first   = $rows->first();
            $tanggal = $rows->pluck('date')
                ->map(fn($d) => (int) date('j', strtotime($d)))
                ->sort()->values()->implode(', ');
            $uraian  = $rows->pluck('uraian')->unique()->filter()
                ->map(fn($u) => '- ' . $u)->implode("\n");
            return (object) [
                'nama'     => $first->nama,
                'nip_lama' => $first->nip_lama,
                'tanggal'  => $tanggal,
                'uraian'   => $uraian,
            ];
        })->values();

        [$ppk, , $kbu] = $this->getPejabat();
        $bulanLabel = $dt->translatedFormat('F');
        $tahun      = $dt->year;

        if ($format === 'xlsx') {
            $fileBlob = Excel::raw(
                new \App\Exports\LaporanExport(['bulan' => $bulan, 'jenis' => $jenis]),
                \Maatwebsite\Excel\Excel::XLSX
            );
            DB::table('t_dokumen')->insert([
                'type'         => $type,
                'periode'      => $bulan,
                'generated_at' => now(),
                'file_blob'    => $fileBlob,
            ]);
            return redirect()->route('admin.dokumen')->with('success', 'Laporan XLSX berhasil digenerate.');
        }

        $pdf = Pdf::loadView('dokumen.laporan', compact(
            'pegawai', 'kbu', 'bulanLabel', 'tahun', 'jenis'
        ))->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->add_info('Title', "Laporan_" . strtoupper($jenis) . "_" . $bulan);
        
        DB::table('t_dokumen')->insert([
            'type'         => $type,
            'periode'      => $bulan,
            'generated_at' => now(),
            'file_blob'    => $pdf->output(),
        ]);

        return redirect()->route('admin.dokumen')->with('success', 'Laporan berhasil digenerate.');
    }

    public function download(Request $request, string $type)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));

        $dokumen = DB::table('t_dokumen')
            ->where('periode', $bulan)
            ->where('type', $type)
            ->first();

        if (!$dokumen) {
            return back()->with('error', 'Dokumen belum digenerate.');
        }

        return response($dokumen->file_blob, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $type . '_' . $bulan . '.pdf"',
        ]);
    }
}

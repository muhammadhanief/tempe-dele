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
    private function getPejabat(?Request $request = null, ?int $tahun = null)
    {
        // 1. PPK: jika dioper via request, gunakan parameter; jika tidak, ambil yang aktif
        $ppk = null;
        if ($request && ($request->filled('ppk') || $request->filled('ppk_id') || $request->filled('id_ppk'))) {
            $val = $request->get('ppk') ?: ($request->get('ppk_id') ?: $request->get('id_ppk'));
            $ppk = DB::table('m_pejabat')->where('jabatan', 'PPK')->where(function($q) use ($val) {
                $q->where('id_pejabat', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->orWhere('nama', $val);
            })->first();
            if (!$ppk) {
                $peg = DB::table('m_pegawai')->where('id_pegawai', $val)->orWhere('nip', $val)->orWhere('nip_lama', $val)->first();
                if ($peg) {
                    $ppk = (object) [
                        'nama'     => $peg->nama,
                        'jabatan'  => 'PPK',
                        'nip'      => $peg->nip,
                        'nip_lama' => $peg->nip_lama,
                    ];
                }
            }
        }
        if (!$ppk) {
            $ppkQuery = DB::table('m_pejabat')->where('jabatan', 'PPK')->where('status', 'aktif');
            if ($tahun) {
                $ppk = (clone $ppkQuery)->where('tahun', $tahun)->first() ?: $ppkQuery->orderByDesc('tahun')->first();
            } else {
                $ppk = $ppkQuery->orderByDesc('tahun')->first();
            }
        }

        // 2. Kepala BPS
        $kbps = DB::table('m_pejabat')->where('jabatan', 'Kepala BPS')->where('status', 'aktif')->orderByDesc('tahun')->first();

        // 3. Kepala Bagian Umum: jika dioper via request, gunakan parameter; jika tidak, ambil yang aktif
        $kbu = null;
        if ($request && ($request->filled('kbu') || $request->filled('kbu_id') || $request->filled('id_kbu'))) {
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
            $kbuQuery = DB::table('m_pejabat')->where('jabatan', 'Kepala Bagian Umum')->where('status', 'aktif');
            if ($tahun) {
                $kbu = (clone $kbuQuery)->where('tahun', $tahun)->first() ?: $kbuQuery->orderByDesc('tahun')->first();
            } else {
                $kbu = $kbuQuery->orderByDesc('tahun')->first();
            }
        }

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

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where(function ($q) {
                $q->where('t.eligible', 1)->orWhereNull('t.eligible');
            })
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select('p.nama', 'p.nip', 'p.nip_lama', 't.date', 't.uraian')
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

        $pegawai = $query->get()->groupBy('nip')->map(function ($rows) {
            $first = $rows->first();

            // Tanggal lembur unik dan diurutkan secara numerik menaik
            $tanggalList = $rows->pluck('date')
                ->map(fn($d) => (int) date('j', strtotime($d)))
                ->unique()
                ->sort()
                ->values();

            $tanggal = $tanggalList->implode(', ');

            // Format uraian agar rapi, unik, dan tidak ada duplikasi bullet
            $uraianItems = collect();
            foreach ($rows as $row) {
                if (!empty($row->uraian)) {
                    $parts = preg_split('/[;\n\r]+/', $row->uraian);
                    foreach ($parts as $u) {
                        $clean = trim($u);
                        $clean = ltrim($clean, "-•* \t\n\r\0\x0B");
                        if (!empty($clean)) {
                            $uraianItems->push($clean);
                        }
                    }
                }
            }
            $uraian = $uraianItems->unique()->values()->map(fn($u) => '- ' . $u)->implode("\n");

            // NIP Baru (18 digit) dengan fallback ke NIP Lama jika kosong
            $nipBaru = !empty($first->nip) ? $first->nip : $first->nip_lama;

            return (object) [
                'nama'           => $first->nama,
                'nip'            => $nipBaru,
                'nip_baru'       => $nipBaru,
                'nip_lama'       => $first->nip_lama,
                'tanggal_lembur' => $tanggal,
                'uraian'         => $uraian,
                'min_tanggal'    => $tanggalList->first() ?? 999,
            ];
        })
        ->sortBy([
            ['min_tanggal', 'asc'],
            ['nama', 'asc'],
        ])
        ->values();

        [$ppk, , $kbu] = $this->getPejabat($request, (int) $tahun);
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

            if ($existing) {
                DB::table('t_dokumen')->where('id_dokumen', $existing->id_dokumen)->update([
                    'generated_at' => now(),
                    'file_blob'    => $fileBlob,
                ]);
                return redirect()->route('admin.dokumen')->with('success', 'SPKL XLSX berhasil diperbarui.');
            }

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

        if ($existing) {
            DB::table('t_dokumen')->where('id_dokumen', $existing->id_dokumen)->update([
                'generated_at' => now(),
                'file_blob'    => $pdf->output(),
            ]);
            return redirect()->route('admin.dokumen')->with('success', 'SPKL berhasil diperbarui.');
        }

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

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where(function ($q) {
                $q->where('t.eligible', 1)->orWhereNull('t.eligible');
            })
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select('p.nama', 'p.nip', 'p.nip_lama', 't.date', 't.uraian')
            ->orderBy('t.date', 'asc')
            ->orderBy('p.nama', 'asc');

        if ($jenis === 'pns') {
            $query->where(function ($q) {
                $q->whereNull('p.email')
                  ->orWhere('p.email', '')
                  ->orWhere('p.email', 'not like', '%-pppk@bps.go.id');
            });
        } else {
            $query->where('p.email', 'like', '%-pppk@bps.go.id');
        }

        $pegawai = $query->get()->groupBy(function ($item) {
            return $item->date . '_' . $item->nip;
        })->map(function ($rows) {
            $first = $rows->first();
            $tanggal = (int) date('j', strtotime($first->date)); // Angka tanggal (1, 2, 6, dst)

            $uraianItems = collect();
            foreach ($rows as $row) {
                if (!empty($row->uraian)) {
                    $parts = preg_split('/[;\n\r]+/', $row->uraian);
                    foreach ($parts as $u) {
                        $clean = trim($u);
                        $clean = ltrim($clean, "-•* \t\n\r\0\x0B");
                        if (!empty($clean)) {
                            $uraianItems->push($clean);
                        }
                    }
                }
            }
            $uraianItems = $uraianItems->unique()->values();

            if ($uraianItems->count() > 1) {
                $uraianFormatted = $uraianItems->map(fn($u) => '- ' . $u)->implode("\n");
            } elseif ($uraianItems->count() === 1) {
                $uraianFormatted = $uraianItems->first();
            } else {
                $uraianFormatted = '-';
            }

            // NIP Baru 18 digit resmi, fallback ke NIP lama jika kosong
            $nipDisplay = !empty($first->nip) ? $first->nip : $first->nip_lama;

            return (object) [
                'nama'           => $first->nama,
                'nip'            => $first->nip,
                'nip_baru'       => $first->nip,
                'nip_lama'       => $first->nip_lama,
                'nip_display'    => $nipDisplay,
                'nama_nip'       => $first->nama . ' / ' . $nipDisplay,
                'date'           => $first->date,
                'tanggal'        => $tanggal,
                'tanggal_lembur' => $tanggal,
                'uraian'         => $uraianFormatted,
            ];
        })
        ->sortBy([
            ['date', 'asc'],
            ['nama', 'asc'],
        ])
        ->values();

        [$ppk, , $kbu] = $this->getPejabat($request, (int) $tahun);
        $bulanLabel = $dt->translatedFormat('F');
        $tahun      = $dt->year;

        if ($format === 'xlsx') {
            $fileBlob = Excel::raw(
                new \App\Exports\LaporanExport(['bulan' => $bulan, 'jenis' => $jenis]),
                \Maatwebsite\Excel\Excel::XLSX
            );

            if ($existing) {
                DB::table('t_dokumen')->where('id_dokumen', $existing->id_dokumen)->update([
                    'generated_at' => now(),
                    'file_blob'    => $fileBlob,
                ]);
                return redirect()->route('admin.dokumen')->with('success', 'Laporan XLSX berhasil diperbarui.');
            }

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
        
        if ($existing) {
            DB::table('t_dokumen')->where('id_dokumen', $existing->id_dokumen)->update([
                'generated_at' => now(),
                'file_blob'    => $pdf->output(),
            ]);
            return redirect()->route('admin.dokumen')->with('success', 'Laporan berhasil diperbarui.');
        }

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

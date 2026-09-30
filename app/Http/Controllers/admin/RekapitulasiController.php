<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Cookie;
use App\Traits\KoreksiLembur;

class RekapitulasiController extends Controller
{
    use KoreksiLembur;

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $nip   = $request->get('nip_lama');

        [$tahun, $bln] = explode('-', $bulan);
        $monthDate = $tahun . '-' . $bln . '-01';

        // 0. Sinkronisasi otomatis kelayakan (eligible) untuk seluruh pengajuan approved di bulan ini
        $this->koreksiUntukBulan((int) $tahun, (int) $bln);

        // 1. Ambil transaksi approved + eligible di bulan tsb
        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where('t.eligible', 1)
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->when($nip, fn($q) => $q->where('p.nip_lama', $nip))
            ->select(
                'p.id_pegawai',
                'p.nama',
                'p.nip_lama',
                't.date',
                't.hari',
                't.jam_mulai_disetujui',
                't.jam_selesai_disetujui'
            )
            ->orderBy('p.nama')
            ->orderBy('t.date')
            ->get();

        // 2. Hitung rekapitulasi per pegawai
        $rekapMap = $transaksi->groupBy('nip_lama')->map(function ($rows) use ($monthDate, $tahun, $bln) {
            $first      = $rows->first();
            $nama       = $first->nama;
            $nip        = $first->nip_lama;
            $id_pegawai = $first->id_pegawai;

            // hb: hari bekerja max 4 jam, hl: hari libur/weekend max 6 jam
            $hb = [2 => 0, 3 => 0, 4 => 0];
            $hl = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];

            $tanggalList = [];

            foreach ($rows as $row) {
                if (!$row->jam_mulai_disetujui || !$row->jam_selesai_disetujui) continue;

                // Pakai Carbon agar aman melewati tengah malam
                $mulai   = \Carbon\Carbon::parse($row->date . ' ' . $row->jam_mulai_disetujui);
                $selesai = \Carbon\Carbon::parse($row->date . ' ' . $row->jam_selesai_disetujui);

                if ($selesai->lessThan($mulai)) {
                    $selesai->addDay();
                }

                $durasi = (int) floor($mulai->diffInMinutes($selesai) / 60);
                if ($durasi <= 0) continue;

                $tgl = (int) date('j', strtotime($row->date));
                $tanggalList[] = $tgl;

                if ($row->hari == 0) {
                    if (array_key_exists($durasi, $hb)) {
                        $hb[$durasi]++;
                    }
                } else {
                    if (array_key_exists($durasi, $hl)) {
                        $hl[$durasi]++;
                    }
                }
            }

            // Hitung total jam
            $jumlahHb = 0;
            foreach ($hb as $jam => $count) {
                $jumlahHb += $jam * $count;
            }

            $jumlahHl = 0;
            foreach ($hl as $jam => $count) {
                $jumlahHl += $jam * $count;
            }

            $tanggalStr = implode(', ', array_unique($tanggalList));

            return [
                'id_pegawai'       => $id_pegawai,
                'nama'             => $nama,
                'nip'              => $nip,
                'month'            => $monthDate,
                'hb2'              => $hb[2],
                'hb3'              => $hb[3],
                'hb4'              => $hb[4],
                'hl2'              => $hl[2],
                'hl3'              => $hl[3],
                'hl4'              => $hl[4],
                'hl5'              => $hl[5],
                'hl6'              => $hl[6],
                'jumlah_hb'        => $jumlahHb,
                'jumlah_hl'        => $jumlahHl,
                '_tanggal_display' => $tanggalStr,
            ];
        })->values();

        // 3. Upsert ke t_rekapitulasi
        if (!$nip) {
            DB::table('t_rekapitulasi')
                ->where('month', $monthDate)
                ->delete();

            $insertData = $rekapMap->map(fn($r) => [
                'pegawai_id_pegawai' => $r['id_pegawai'],
                'month'              => $monthDate,
                'hb2'                => $r['hb2'],
                'hb3'                => $r['hb3'],
                'hb4'                => $r['hb4'],
                'hl2'                => $r['hl2'],
                'hl3'                => $r['hl3'],
                'hl4'                => $r['hl4'],
                'hl5'                => $r['hl5'],
                'hl6'                => $r['hl6'],
                'jumlah_hb'          => $r['jumlah_hb'],
                'jumlah_hl'          => $r['jumlah_hl'],
                'tanggal'            => null,
            ])->toArray();

            if (!empty($insertData)) {
                DB::table('t_rekapitulasi')->insert($insertData);
            }
        }

        // 4. Paginasi
        $perPage = 10;
        $page    = $request->get('page', 1);
        $items   = $rekapMap->forPage($page, $perPage);

        $rekapitulasi = new LengthAwarePaginator(
            $items,
            $rekapMap->count(),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.spkl', compact('rekapitulasi', 'bulan'));
    }

    public function downloadExcel(Request $request)
    {
        $bulan    = $request->get('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);
        $this->koreksiUntukBulan((int) $tahun, (int) $bln);

        $filename = 'Rekapitulasi_Lembur_' . $bulan . '.xlsx';

        $response = Excel::download(
            new \App\Exports\RekapitulasiExport(['bulan' => $bulan]),
            $filename
        );

        session(['export_done' => true]);

        return $response;
    }

    public function exportStatus()
    {
        $done = session()->pull('export_done', false);
        return response()->json(['done' => $done]);
    }
}

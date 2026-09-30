<?php

namespace App\Http\Controllers\pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\KoreksiLembur;

class RekapitulasiController extends Controller
{
    use KoreksiLembur;

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        // Sinkronisasi otomatis kelayakan (eligible) untuk seluruh pengajuan approved di bulan ini
        $this->koreksiUntukBulan((int) $tahun, (int) $bln);

        $nip = session('user')['nip'];

        $transaksi = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nip)
            ->where('status', 'approved')
            ->where('eligible', 1)
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bln)
            ->orderBy('date')
            ->get();

        $rows = $transaksi->map(function ($t) {
            $mulai   = Carbon::parse($t->date . ' ' . $t->jam_mulai_disetujui);
            $selesai = Carbon::parse($t->date . ' ' . $t->jam_selesai_disetujui);

            if ($selesai->lessThan($mulai)) {
                $selesai->addDay();
            }

            $durasi = (int) floor($mulai->diffInMinutes($selesai) / 60);

            return [
                'tanggal'    => $t->date,
                'jenis_hari' => $t->hari == 0 ? 'Bekerja' : 'Libur',
                'hari'       => $t->hari,
                'jam'        => $durasi,
                'jam_label'  => substr($t->jam_mulai_disetujui, 0, 5) . ' - ' . substr($t->jam_selesai_disetujui, 0, 5),
            ];
        });

        $total = ['hb2'=>0,'hb3'=>0,'hb4'=>0,'hl2'=>0,'hl3'=>0,'hl4'=>0,'hl5'=>0,'hl6'=>0];
        foreach ($rows as $r) {
            $key = ($r['hari'] == 0 ? 'hb' : 'hl') . $r['jam'];
            if (isset($total[$key])) $total[$key]++;
        }

        $jumlahHb = ($total['hb2']*2) + ($total['hb3']*3) + ($total['hb4']*4);
        $jumlahHl = ($total['hl2']*2) + ($total['hl3']*3) + ($total['hl4']*4) + ($total['hl5']*5) + ($total['hl6']*6);

        return view('rekapitulasi', compact('rows', 'total', 'jumlahHb', 'jumlahHl', 'bulan'));
    }
}

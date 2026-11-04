<?php

namespace App\Http\Controllers\pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\KoreksiLembur;

class AkumulasiController extends Controller
{
    use KoreksiLembur;

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        [$tahun, $bln] = explode('-', $bulan);

        $this->koreksiUntukBulan((int) $tahun, (int) $bln);

        $nip = session('user')['nip'];

        $golongan = DB::table('m_pegawai')->where('nip', $nip)->value('golongan');
        $golonganDasar = $golongan ? explode('/', $golongan)[0] : null;

        $rates = DB::table('m_rates')
            ->where('golongan', $golonganDasar)
            ->get()
            ->keyBy('day_type');

        $transaksi = DB::table('t_transaksi')
            ->where('submitted_by_NIP', $nip)
            ->where('status', 'approved')
            ->where('eligible', 1)
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bln)
            ->orderBy('date')
            ->get();

        $rows = $transaksi->map(function ($t) use ($rates, $golongan) {
            $rate = $rates->get($t->hari);

            $jamDiajukan  = $this->hitungJam($t->date, $t->jam_mulai, $t->jam_selesai);
            $jamDisetujui = $this->hitungJam($t->date, $t->jam_mulai_disetujui, $t->jam_selesai_disetujui);

            $uangLembur = ($rate->uang_lembur ?? 0) * $jamDisetujui;
            $uangMakan  = $rate->uang_makan ?? 0;
            $jumlah     = $uangLembur + $uangMakan;
            $pajak      = (int) round($jumlah * (float) ($rate->pajak ?? 0));

            return (object) [
                'tanggal'       => $t->date,
                'jenis_hari'    => $t->hari == 0 ? 'Bekerja' : 'Libur',
                'jam_diajukan'  => $jamDiajukan,
                'jam_disetujui' => $jamDisetujui,
                'golongan'      => $golongan ?: '-',
                'uang_lembur'   => $uangLembur,
                'uang_makan'    => $uangMakan,
                'jumlah'        => $jumlah,
                'pajak'         => $pajak,
                'terima'        => $jumlah - $pajak,
            ];
        });

        $totalTerima = $rows->sum('terima');

        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $akumulasi = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('akumulasi', compact('akumulasi', 'totalTerima', 'bulan'));
    }

    private function hitungJam($tanggal, $mulai, $selesai): int
    {
        if (!$mulai || !$selesai) {
            return 0;
        }

        $awal  = Carbon::parse($tanggal . ' ' . $mulai);
        $akhir = Carbon::parse($tanggal . ' ' . $selesai);

        if ($akhir->lessThan($awal)) {
            $akhir->addDay();
        }

        return (int) floor($awal->diffInMinutes($akhir) / 60);
    }
}

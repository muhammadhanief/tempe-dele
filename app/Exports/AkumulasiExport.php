<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AkumulasiExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function collection()
    {
        $bulan = $this->params['bulan'] ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $query = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->leftJoin('m_rates as r', function ($join) {
                $join->on(
                    DB::raw("SUBSTRING_INDEX(p.golongan, '/', 1)"), '=', 'r.golongan'
                )->on('t.hari', '=', 'r.day_type');
            })
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->where('t.status', 'approved')
            ->where('t.eligible', 1)
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select(
                'p.nama', 'p.nip', 'p.golongan',
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, t.jam_mulai_disetujui, t.jam_selesai_disetujui)) as jam_disetujui'),
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, t.jam_mulai, t.jam_selesai)) as jam_diajukan'),
                DB::raw('SUM(COALESCE(r.uang_lembur, 0) * TIMESTAMPDIFF(HOUR, t.jam_mulai_disetujui, t.jam_selesai_disetujui)) as total_uang_lembur'),
                DB::raw('SUM(COALESCE(r.uang_makan, 0)) as total_uang_makan'),
                DB::raw('MAX(COALESCE(r.pajak, 0)) as pajak_pct')
            )
            ->groupBy('p.nip', 'p.nama', 'p.golongan')
            ->orderBy('p.nama');

        if (!empty($this->params['tim']))     $query->where('t.tim_kode_tim', $this->params['tim']);
        if (!empty($this->params['pegawai'])) $query->where('t.submitted_by_NIP', $this->params['pegawai']);

        return $query->get()->map(function ($item, $i) {
            $jumlah = $item->total_uang_lembur + $item->total_uang_makan;
            $pajak  = round($jumlah * $item->pajak_pct); // m_rates.pajak berupa pecahan (0.05 = 5%)
            $terima = $jumlah - $pajak;

            return [
                'no'            => $i + 1,
                'nama'          => $item->nama,
                'nip'           => "\t" . $item->nip,
                'golongan'      => $item->golongan ?? '-',
                'jam_diajukan'  => $item->jam_diajukan ?? 0,
                'jam_disetujui' => $item->jam_disetujui ?? 0,
                'uang_lembur'   => $item->total_uang_lembur ?? 0,
                'uang_makan'    => $item->total_uang_makan ?? 0,
                'jumlah'        => $jumlah,
                'pajak'         => $pajak,
                'terima'        => $terima,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'NIP', 'Golongan',
            'Jam Diajukan', 'Jam Disetujui',
            'Uang Lembur', 'Uang Makan', 'Jumlah', 'PPh 21', 'Terima'
        ];
    }

    public function title(): string
    {
        return 'Akumulasi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, 
        ];
    }
}

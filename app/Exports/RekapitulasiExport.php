<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use \App\Traits\KoreksiLembur;

    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function collection()
    {
        $bulan = $this->params['bulan'] ?? now()->format('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $this->koreksiUntukBulan((int) $tahun, (int) $bln);

        $transaksi = DB::table('t_transaksi as t')
            ->join('m_pegawai as p', 't.submitted_by_NIP', '=', 'p.nip')
            ->where('t.status', 'approved')
            ->where('t.eligible', 1) 
            ->whereYear('t.date', $tahun)
            ->whereMonth('t.date', $bln)
            ->select('p.nip_lama', 't.date', 't.hari', 't.jam_mulai_disetujui', 't.jam_selesai_disetujui')
            ->orderBy('p.nip_lama')
            ->orderBy('t.date')
            ->get();

        return $transaksi->groupBy('nip_lama')->map(function ($rows) {
            $hb = array_fill(1, 12, 0);
            $hl = array_fill(1, 16, 0);

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

                if ($row->hari == 0) {
                    if (isset($hb[$durasi])) $hb[$durasi]++;
                } else {
                    if (isset($hl[$durasi])) $hl[$durasi]++;
                }
            }

            $result = ['NIP Lama' => "\t" . $rows->first()->nip_lama];

            for ($i = 1; $i <= 12; $i++) {
                $result['HB' . $i] = $hb[$i] ?: '';
            }
            for ($i = 1; $i <= 16; $i++) {
                $result['HL' . $i] = $hl[$i] ?: '';
            }

            return $result;
        })->values();
    }

    public function headings(): array
    {
        $headings = ['NIP Lama'];

        for ($i = 1; $i <= 12; $i++) $headings[] = 'HB' . $i;
        for ($i = 1; $i <= 16; $i++) $headings[] = 'HL' . $i;

        return $headings;
    }

    public function title(): string
    {
        return 'Rekapitulasi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

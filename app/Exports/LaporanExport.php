<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanExport implements FromCollection, WithTitle, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function collection()
    {
        $bulan = $this->params['bulan'] ?? now()->format('Y-m');
        $jenis = $this->params['jenis'] ?? 'pns';
        [$tahun, $bln] = explode('-', $bulan);

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

        $no = 0;
        return $query->get()->groupBy(function ($item) {
            return $item->date . '_' . $item->nip;
        })->map(function ($rows) use (&$no) {
            $no++;
            $first = $rows->first();
            $tanggal = (int) date('j', strtotime($first->date));

            $uraianItems = collect();
            foreach ($rows as $row) {
                if (!empty($row->uraian)) {
                    $parts = explode(';', $row->uraian);
                    foreach ($parts as $p) {
                        $clean = trim($p);
                        $clean = ltrim($clean, "- \t\n\r\0\x0B");
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

            $nipDisplay = $first->nip ?: $first->nip_lama;

            return [
                $no,
                $first->nama . ' / ' . $nipDisplay,
                $tanggal,
                $uraianFormatted,
            ];
        })->values();
    }

    public function title(): string
    {
        return 'Laporan';
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        $bulan = $this->params['bulan'] ?? now()->format('Y-m');
        $jenis = strtoupper($this->params['jenis'] ?? 'pns');
        $dt    = Carbon::parse($bulan . '-01');
        $judul = 'LAPORAN HASIL KERJA LEMBUR ' . $jenis . ' BULAN ' . strtoupper($dt->translatedFormat('F Y'));

        return [
            AfterSheet::class => function (AfterSheet $event) use ($judul) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Insert 3 baris di atas: judul, kosong, header
                $sheet->insertNewRowBefore(1, 3);

                // Baris 1: Judul
                $sheet->setCellValue('A1', $judul);
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: kosong
                $sheet->getRowDimension(2)->setRowHeight(15);

                // Baris 3: Header kolom
                $sheet->setCellValue('A3', 'No');
                $sheet->setCellValue('B3', 'Nama Pegawai / NIP');
                $sheet->setCellValue('C3', 'Tanggal');
                $sheet->setCellValue('D3', 'Uraian Kegiatan');
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // Data rows mulai dari baris 4
                $dataLastRow = $lastRow + 3;
                if ($dataLastRow >= 4) {
                    $sheet->getStyle("A4:D{$dataLastRow}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
                    ]);
                    $sheet->getStyle("A4:A{$dataLastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B4:B{$dataLastRow}")->getAlignment()
                        ->setWrapText(true);
                    $sheet->getStyle("C4:C{$dataLastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D4:D{$dataLastRow}")->getAlignment()
                        ->setWrapText(true);
                }

                // Lebar kolom
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(6);
                $sheet->getColumnDimension('B')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(12);
                $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(65);
            },
        ];
    }
}

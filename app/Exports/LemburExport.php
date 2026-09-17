<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LemburExport implements FromCollection, WithColumnWidths, WithEvents
{
    protected $bulan;
    protected $tim;
    protected $nip;
    protected $status;
    protected $data;
    protected $namaBulan;

    public function __construct($bulan, $tim = null, $nip = null, $status = null)
    {
        $this->bulan  = $bulan;
        $this->tim    = $tim;
        $this->nip    = $nip;
        $this->status = $status;

        $periode         = Carbon::parse($bulan . '-01');
        $this->namaBulan = $periode->translatedFormat('F Y');
        $startOfMonth    = $periode->copy()->startOfMonth()->toDateString();
        $endOfMonth      = $periode->copy()->endOfMonth()->toDateString();

        $query = DB::table('t_transaksi as t')
            ->leftJoin('m_tim as mt', 't.tim_kode_tim', '=', 'mt.kode_tim')
            ->leftJoin('m_pegawai as kp', 't.approver_employee_id', '=', 'kp.nip')
            ->leftJoin('m_pegawai as pg', 't.submitted_by_NIP', '=', 'pg.nip')
            ->select('t.*', 'mt.nama_tim', 'kp.nama as nama_ketua', 'pg.nama as nama_pegawai')
            ->whereBetween('t.date', [$startOfMonth, $endOfMonth])
            ->orderBy('t.date', 'asc');

        if ($tim) $query->where('t.tim_kode_tim', $tim);
        if ($nip) $query->where('t.submitted_by_NIP', $nip);
        if ($status) $query->where('t.status', $status);

        $this->data = $query->get();
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->data as $i => $d) {
            $jamDiajukan = ($d->jam_mulai && $d->jam_selesai)
                ? substr($d->jam_mulai, 0, 5) . ' - ' . substr($d->jam_selesai, 0, 5)
                : ($d->jam_mulai ? substr($d->jam_mulai, 0, 5) . ' - menunggu' : '-');

            $jamDisetujui = ($d->jam_mulai_disetujui && $d->jam_selesai_disetujui)
                ? substr($d->jam_mulai_disetujui, 0, 5) . ' - ' . substr($d->jam_selesai_disetujui, 0, 5)
                : '-';

            $status = match($d->status) {
                'pending'  => 'Diproses',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default    => '-',
            };

            $rows->push([
                $i + 1,
                Carbon::parse($d->date)->translatedFormat('d F Y'),
                $d->submitted_by_NIP, // akan diformat teks di AfterSheet
                $d->nama_pegawai ?? '-',
                $jamDiajukan,
                $jamDisetujui,
                $d->uraian ?? '-',
                $d->nama_ketua ?? '-',
                $d->nama_tim ?? '-',
                $status,
            ]);
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 22,
            'D' => 25,
            'E' => 18,
            'F' => 18,
            'G' => 35,
            'H' => 25,
            'I' => 25,
            'J' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $dataRows = count($this->data);

                // Data dari collection() mulai di baris 1
                // Sisipkan 3 baris di atas (judul1, judul2, kosong)
                $sheet->insertNewRowBefore(1, 3);

                // Sekarang data mulai baris 4, heading di baris 4, data di 5+
                // Tapi kita perlu heading dulu — tulis manual di baris 4
                $headings = ['No', 'Tanggal', 'NIP', 'Nama Pegawai', 'Jam Diajukan', 'Jam Disetujui', 'Uraian Kegiatan', 'Ketua Tim', 'Nama Tim', 'Status'];
                foreach ($headings as $i => $h) {
                    $col = chr(65 + $i); // A, B, C, ...
                    $sheet->setCellValue("{$col}4", $h);
                }

                $lastRow = $dataRows + 4;

                // Judul baris 1 & 2
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'REKAPITULASI PENGAJUAN LEMBUR');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'Bulan ' . $this->namaBulan);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style heading baris 4
                $sheet->getStyle('A4:J4')->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Border data
                $sheet->getStyle("A5:J{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // NIP sebagai teks
                for ($row = 5; $row <= $lastRow; $row++) {
                    $sheet->getCell("C{$row}")
                        ->setValueExplicit(
                            $sheet->getCell("C{$row}")->getValue(),
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                }

                // Warna status
                for ($row = 5; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell("J{$row}")->getValue();
                    $color  = match($status) {
                        'Disetujui' => 'D1FAE5',
                        'Ditolak'   => 'FEE2E2',
                        'Diproses'  => 'FEF3C7',
                        default     => 'FFFFFF',
                    };
                    $sheet->getStyle("J{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }
            },
        ];
    }
}

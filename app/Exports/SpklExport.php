<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpklExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'SPKL';
    }

    public function array(): array
    {
        $pegawai    = $this->data['pegawai'];
        $nomorSurat = $this->data['nomorSurat'];
        $bulanLabel = $this->data['bulanLabel'];
        $tahun      = $this->data['tahun'];
        $ppk        = $this->data['ppk'];
        $kbu        = $this->data['kbu'];
        $tanggalTtd = $this->data['tanggalTtd'];

        $rows = [];

        // Baris 1-4: area logo (kosong, logo akan ditaruh via drawing)
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];

        // Baris 5: judul
        $rows[] = ['SURAT PERINTAH KERJA LEMBUR', '', '', ''];

        // Baris 6: nomor surat
        $rows[] = ['Nomor: ' . $nomorSurat, '', '', ''];

        // Baris 7: kosong
        $rows[] = ['', '', '', ''];

        // Baris 8: pembuka
        $rows[] = [
            'Sehubungan dengan adanya penyelesaian pekerjaan yang dilakukan di luar jam kerja (lembur) pada bulan '
            . $bulanLabel . ' Tahun ' . $tahun
            . ', dengan ini kami memerintahkan pegawai tersebut di bawah ini untuk menyelesaikan pekerjaan yang dimaksud.',
            '', '', ''
        ];

        // Baris 9: kosong
        $rows[] = ['', '', '', ''];

        // Baris 10: header tabel
        $rows[] = ['No', 'Nama Pegawai/NIP', 'Bulan ' . $bulanLabel, 'Uraian Kegiatan'];

        // Baris 11+: data pegawai
        foreach ($pegawai as $i => $p) {
            $rows[] = [
                $i + 1,
                $p->nama . "\n" . ($p->nip ?? $p->nip_lama),
                $p->tanggal_lembur,
                $p->uraian,
            ];
        }

        // Spasi sebelum TTD
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];

        // TTD
        $rows[] = ['Pejabat Pembuat Komitmen', '', 'Mengetahui, ' . $tanggalTtd, ''];
        $rows[] = ['BPS Provinsi Jawa Tengah', '', 'a.n. Kepala BPS Provinsi Jawa Tengah', ''];
        $rows[] = ['', '', 'Kepala Bagian Umum', ''];
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];
        $rows[] = ['', '', '', ''];
        $rows[] = [$ppk->nama ?? '', '', $kbu->nama ?? '', ''];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 25,
            'D' => 50,
        ];
    }

    public function styles($sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Landscape A4
                $sheet->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );
                $sheet->getPageSetup()->setPaperSize(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                );
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Logo — taruh di A1, tinggi 4 baris
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo BPS');
                $drawing->setDescription('Logo BPS Provinsi Jawa Tengah');
                $drawing->setPath(public_path('images/LOGO BPS PROVINSI JATENG.png'));
                $drawing->setCoordinates('A1');
                $drawing->setHeight(80);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(3);
                $drawing->setWorksheet($sheet);

                // Tinggi baris 1-4 untuk area logo
                foreach (range(1, 4) as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // Baris 5: judul — merge A-D, bold, center, underline
                $sheet->mergeCells('A5:D5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => [
                        'bold'      => true,
                        'size'      => 13,
                        'underline' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Baris 6: nomor surat — merge A-D, center
                $sheet->mergeCells('A6:D6');
                $sheet->getStyle('A6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Baris 8: pembuka — merge A-D, justify, wrap
                $sheet->mergeCells('A8:D8');
                $sheet->getStyle('A8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_JUSTIFY,
                        'wrapText'   => true,
                    ],
                ]);
                $sheet->getRowDimension(8)->setRowHeight(-1);

                // Baris 10: header tabel
                $sheet->getStyle('A10:D10')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F0F0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Baris 11+: data pegawai
                $dataStart = 11;
                $dataEnd   = $dataStart + count($this->data['pegawai']) - 1;

                $sheet->getStyle("A{$dataStart}:D{$dataEnd}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'wrapText'  => true,
                        'vertical'  => Alignment::VERTICAL_TOP,
                    ],
                ]);

                // Kolom No: center
                $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Kolom tanggal: center
                $sheet->getStyle("C{$dataStart}:C{$dataEnd}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // TTD
                $ttdStart = $dataEnd + 3;

                $sheet->mergeCells("A{$ttdStart}:B{$ttdStart}");
                $sheet->mergeCells("C{$ttdStart}:D{$ttdStart}");
                $sheet->mergeCells('A' . ($ttdStart + 1) . ':B' . ($ttdStart + 1));
                $sheet->mergeCells('C' . ($ttdStart + 1) . ':D' . ($ttdStart + 1));
                $sheet->mergeCells('A' . ($ttdStart + 2) . ':B' . ($ttdStart + 2));
                $sheet->mergeCells('C' . ($ttdStart + 2) . ':D' . ($ttdStart + 2));
                $sheet->mergeCells('A' . ($ttdStart + 6) . ':B' . ($ttdStart + 6));
                $sheet->mergeCells('C' . ($ttdStart + 6) . ':D' . ($ttdStart + 6));

                foreach (range($ttdStart, $ttdStart + 6) as $r) {
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // Nama pejabat bold
                $sheet->getStyle('A' . ($ttdStart + 6))->getFont()->setBold(true);
                $sheet->getStyle('C' . ($ttdStart + 6))->getFont()->setBold(true);
            },
        ];
    }
}

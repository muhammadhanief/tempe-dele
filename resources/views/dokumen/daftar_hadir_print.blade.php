<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daftar Hadir Lembur - {{ $labelJenis }} - {{ $tanggalLabel }}</title>
    <style>
        /* ========================================================= */
        /* ATURAN HALAMAN KERTAS A4 PORTRAIT DEFAULT                 */
        /* ========================================================= */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #111827;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
        }

        /* Bilah Aksi Floating (Hanya Muncul di Layar / Non-Print) */
        .no-print-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .no-print-bar .info-text {
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-print-bar .badge-a4 {
            background-color: #fef3c7;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid #fde68a;
        }

        .btn-action-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background-color: #faa938;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .btn-print:hover {
            background-color: #fd9a10;
        }

        .btn-close {
            background-color: #ffffff;
            color: #64748b;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background-color: #f8fafc;
            color: #0f172a;
        }

        /* Lembar Dokumen A4 */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm 20mm;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 4px;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 11pt;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #334155;
            text-transform: uppercase;
        }

        .doc-meta {
            font-size: 10pt;
            color: #475569;
            margin-top: 4px;
        }

        /* Tabel Bergaris Tegas Standar BPS */
        table.table-hadir {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        table.table-hadir th,
        table.table-hadir td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.table-hadir thead th {
            background-color: #e2e8f0;
            color: #000000;
            font-weight: 700;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tbody tr {
            page-break-inside: avoid;
        }

        .col-center {
            text-align: center;
        }

        .col-left {
            text-align: left;
        }

        .nama-pegawai {
            font-weight: 600;
            color: #000000;
        }

        .nip-pegawai {
            font-size: 8.5pt;
            color: #374151;
        }

        .ttd-cell {
            height: 48px;
            text-align: center;
            padding: 2px !important;
        }

        .ttd-img {
            max-height: 42px;
            max-width: 110px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        /* Kotak Tanda Tangan Mengetahui KBU */
        .ttd-container {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .ttd-box-right {
            width: 250px;
            float: right;
            text-align: center;
            font-size: 10pt;
            line-height: 1.4;
        }

        .ttd-space {
            height: 65px;
        }

        .ttd-pejabat-nama {
            font-weight: 700;
            text-decoration: underline;
            color: #000000;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Aturan Khusus Mode Print Printer / PDF */
        @media print {
            body {
                background: transparent !important;
            }

            .no-print {
                display: none !important;
            }

            .paper-sheet {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- BILAH AKSI INTERAKTIF (Hanya di layar komputer) --}}
    <div class="no-print no-print-bar">
        <div class="info-text">
            <svg style="width: 18px; height: 18px; color: #d97706;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Pratinjau Cetak Daftar Hadir Lembur ({{ $labelJenis }})</span>
            <span class="badge-a4">Default Kertas A4 Portrait</span>
        </div>

        <div class="btn-action-group">
            <button type="button" class="btn-print" onclick="window.print()">
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Sekarang</span>
            </button>
            <button type="button" class="btn-close" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>

    {{-- LEMBAR KERTAS DOKUMEN A4 --}}
    <div class="paper-sheet">

        {{-- HEADER DOKUMEN --}}
        <div class="doc-header">
            <h1 class="doc-title">Daftar Hadir Lembur</h1>
            @if($namaTim)
                <h2 class="doc-subtitle">{{ $namaTim }}</h2>
            @else
                <h2 class="doc-subtitle">BPS Provinsi Jawa Tengah</h2>
            @endif
            <div class="doc-meta">
                Tanggal: <strong>{{ $tanggalLabel }}</strong> &nbsp;|&nbsp; Kategori: <strong>Pegawai {{ $labelJenis }}</strong>
            </div>
        </div>

        {{-- TABEL DAFTAR HADIR --}}
        <table class="table-hadir">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 15%;">Tanggal</th>
                    <th rowspan="2" style="width: 6%;">NO</th>
                    <th rowspan="2" style="width: 38%;">Nama / NIP</th>
                    <th colspan="2" style="width: 21%;">Jam</th>
                    <th rowspan="2" style="width: 20%;">Tanda Tangan</th>
                </tr>
                <tr>
                    <th style="width: 10.5%;">Datang</th>
                    <th style="width: 10.5%;">Pulang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarHadir as $i => $d)
                    <tr>
                        <td class="col-center">
                            {{ $i === 0 ? $tanggalLabel : '' }}
                        </td>
                        <td class="col-center">{{ $i + 1 }}</td>
                        <td class="col-left">
                            <span class="nama-pegawai">{{ $d->nama }}</span><br>
                            <span class="nip-pegawai">NIP: {{ $d->nip ?? ($d->nip_lama ?? '-') }}</span>
                        </td>
                        <td class="col-center">
                            {{ $d->jam_mulai_disetujui ? substr($d->jam_mulai_disetujui, 0, 5) : '-' }}
                        </td>
                        <td class="col-center">
                            {{ $d->jam_pulang ?: '-' }}
                        </td>
                        <td class="ttd-cell">
                            @if(!empty($d->signature_src))
                                <img src="{{ $d->signature_src }}" alt="TTD" class="ttd-img">
                            @else
                                <span style="color: #94a3b8; font-size: 8.5pt;">(Telah Hadir)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="col-center" style="padding: 16px; color: #64748b;">
                            Tidak ada data kehadiran lembur untuk tanggal dan kategori ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TANDA TANGAN MENGETAHUI KEPALA BAGIAN UMUM --}}
        <div class="ttd-container clearfix">
            <div class="ttd-box-right">
                Mengetahui,<br>
                Kepala Bagian Umum
                <div class="ttd-space"></div>
                <div class="ttd-pejabat-nama">{{ $kbu->nama ?? 'Kepala Bagian Umum' }}</div>
                @if(!empty($kbu->nip) || !empty($kbu->nip_lama))
                    <div style="font-size: 9pt; color: #374151;">
                        NIP: {{ $kbu->nip ?? $kbu->nip_lama }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- OTOMATIS PICU DIALOG PRINT BROWSER DENGAN DEFAULT A4 --}}
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 450);
        });
    </script>
</body>
</html>

{{-- resources/views/dokumen/laporan.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: A4 portrait;
        margin: 15mm 15mm 20mm 15mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        margin: 0;
        padding: 0;
        color: #000;
        line-height: 1.35;
    }

    .title {
        text-align: center;
        font-size: 12.5pt;
        font-weight: bold;
        margin-bottom: 18px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        page-break-inside: auto;
    }

    th, td {
        border: 1px solid black;
        padding: 6px 8px;
        vertical-align: top;
    }

    th {
        text-align: center;
        background-color: #f0f0f0;
        font-weight: bold;
        font-size: 9.5pt;
    }

    thead {
        display: table-header-group;
    }

    /* Membiarkan baris uraian kegiatan mengalir alami menyambung ke halaman berikutnya */
    tr {
        page-break-inside: auto;
    }

    .col-center {
        text-align: center;
    }

    .col-nama {
        font-weight: bold;
        font-size: 9.5pt;
        color: #000;
    }

    .col-nip {
        font-size: 8.5pt;
        color: #374151;
        margin-top: 2px;
    }

    .col-uraian {
        white-space: pre-line;
        word-wrap: break-word;
        word-break: break-word;
        font-size: 9.5pt;
    }

    .ttd-wrapper {
        page-break-inside: avoid;
        margin-top: 25px;
    }

    .ttd-box {
        text-align: center;
        float: right;
        width: 45%;
        page-break-inside: avoid;
        line-height: 1.4;
    }

    .ttd-space {
        height: 65px;
    }

    .nama-pejabat {
        font-weight: bold;
        text-decoration: underline;
    }

    .clearfix::after {
        content: '';
        display: block;
        clear: both;
    }
</style>
</head>
<body>

<div class="title">
    LAPORAN HASIL KERJA LEMBUR BULAN {{ strtoupper($bulanLabel) }} TAHUN {{ $tahun }}
</div>

<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 32%;">Nama Pegawai / NIP</th>
            <th style="width: 12%;">Tanggal</th>
            <th>Uraian Kegiatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pegawai as $i => $p)
        <tr>
            <td class="col-center">{{ $i + 1 }}</td>
            <td>
                <div class="col-nama">{{ $p->nama }}</div>
                <div class="col-nip">{{ $p->nip_display ?? ($p->nip ?? $p->nip_lama) }}</div>
            </td>
            <td class="col-center" style="font-weight: bold;">{{ $p->tanggal }}</td>
            <td class="col-uraian">{{ $p->uraian }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="ttd-wrapper clearfix">
    <div class="ttd-box">
        Mengetahui,<br>
        Kepala Bagian Umum<br>
        <div class="ttd-space"></div>
        <div class="nama-pejabat">{{ $kbu->nama ?? 'Kepala Bagian Umum' }}</div>
        @if(!empty($kbu->nip) || !empty($kbu->nip_lama))
            <div style="font-size: 9pt; color: #374151;">
                NIP. {{ $kbu->nip ?? $kbu->nip_lama }}
            </div>
        @endif
    </div>
</div>

</body>
</html>

{{-- resources/views/dokumen/spkl.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        margin: 0;
        padding: 20px 28px;
        color: #000;
    }

    .header {
        text-align: center;
        margin-bottom: 18px;
    }

    .title {
        font-size: 14pt;
        font-weight: bold;
        text-decoration: underline;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .nomor {
        margin-bottom: 15px;
    }

    .pembuka {
        text-align: justify;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
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
    }

    .text-center {
        text-align: center;
    }

    thead {
        display: table-header-group;
    }

    tbody tr {
        page-break-inside: avoid;
    }

    .ttd-table {
        width: 100%;
        border: none;
        margin-top: 28px;
    }

    .ttd-table td {
        border: none;
        vertical-align: top;
        width: 50%;
    }

    .ttd-box {
        text-align: center;
        line-height: 1.5;
    }

    .ttd-space {
        height: 70px;
    }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER GAMBAR --}}
    @php
        $logoPath = '/home/lemburwe/public_html/images/LOGO BPS PROVINSI JATENG.png';
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoBase64;
    @endphp
    
    <img
        src="{{ $logoSrc }}"
        alt="Header BPS Provinsi Jawa Tengah"
        style="width: 80%; display: block; margin-bottom: 6px;"
    >

    <div style="width: 100%; border-top: 2px solid black; margin: 6px 0;"></div>

    {{-- JUDUL SURAT --}}
    <div class="header">
        <div class="title">SURAT PERINTAH KERJA LEMBUR</div>
        <div class="nomor">Nomor: {{ $nomorSurat }}</div>
    </div>

    {{-- PEMBUKA --}}
    <div class="pembuka">
        &nbsp;&nbsp;&nbsp;&nbsp;Sehubungan dengan adanya penyelesaian pekerjaan yang dilakukan di luar jam kerja (lembur) pada bulan {{ $bulanLabel }} Tahun {{ $tahun }}, dengan ini kami memerintahkan pegawai tersebut di bawah ini untuk menyelesaikan pekerjaan yang dimaksud.
    </div>

    {{-- TABEL --}}
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:30%">Nama Pegawai/NIP</th>
                <th style="width:20%">Bulan {{ $bulanLabel }}</th>
                <th>Uraian Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pegawai as $i => $p)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    {{ $p->nama }}<br>
                    <small>{{ $p->nip_lama }}</small>
                </td>
                <td class="text-center">{{ $p->tanggal_lembur }}</td>
                <td style="white-space: pre-line;">{{ $p->uraian }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td class="ttd-box" style="padding-top: 15px;">
                <div style="height: 60px;">
                    Pejabat Pembuat Komitmen<br>
                    BPS Provinsi Jawa Tengah
                </div>
                <div class="ttd-space"></div>
                <span class="nama-pejabat">{{ $ppk->nama ?? '' }}</span><br>
            </td>
            <td class="ttd-box">
                Mengetahui, {{ $tanggalTtd }}<br>
                a.n. Kepala BPS Provinsi Jawa Tengah<br>
                Kepala Bagian Umum
                <div class="ttd-space"></div>
                <span class="nama-pejabat">{{ $kbu->nama ?? '' }}</span>
            </td>
        </tr>
    </table>

</div>
</body>
</html>

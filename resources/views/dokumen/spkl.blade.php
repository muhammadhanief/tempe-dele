{{-- resources/views/dokumen/spkl.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: A4 portrait;
        margin: 12mm 15mm 15mm 15mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9.5pt;
        line-height: 1.3;
        margin: 0;
        padding: 0;
        color: #000;
    }

    .header {
        text-align: center;
        margin-bottom: 8px;
    }

    .title {
        font-size: 13pt;
        font-weight: bold;
        text-decoration: underline;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .nomor {
        margin-bottom: 8px;
        font-size: 9.5pt;
    }

    .pembuka {
        text-align: justify;
        text-indent: 28px;
        margin-bottom: 10px;
        line-height: 1.35;
        font-size: 9.5pt;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    th, td {
        border: 1px solid black;
        padding: 4px 6px;
        vertical-align: top;
        font-size: 9pt;
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

    /* Membiarkan baris uraian kegiatan mengalir alami menyambung ke halaman berikutnya */
    tbody tr {
        page-break-inside: auto;
    }

    .ttd-table {
        width: 100%;
        border: none;
        margin-top: 15px;
        page-break-inside: avoid;
    }

    .ttd-table td {
        border: none;
        vertical-align: top;
        width: 50%;
        page-break-inside: avoid;
        font-size: 9.5pt;
    }

    .ttd-box {
        text-align: center;
        line-height: 1.35;
        page-break-inside: avoid;
    }

    .ttd-space {
        height: 50px;
    }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER GAMBAR --}}
    @php
        $logoSrc = null;
        if (extension_loaded('gd')) {
            $logoCandidates = [
                public_path('images/LOGO BPS PROVINSI JATENG.png'),
                base_path('public/images/LOGO BPS PROVINSI JATENG.png'),
                base_path('../public_html/images/LOGO BPS PROVINSI JATENG.png'),
                '/home/lemburwe/public_html/images/LOGO BPS PROVINSI JATENG.png',
            ];
            foreach ($logoCandidates as $cand) {
                if (!empty($cand) && file_exists($cand)) {
                    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($cand));
                    break;
                }
            }
        }
    @endphp
    
    @if($logoSrc)
        <img
            src="{{ $logoSrc }}"
            alt="Header BPS Provinsi Jawa Tengah"
            style="width: 70%; display: block; margin-bottom: 4px;"
        >
        <div style="width: 100%; border-top: 2px solid black; margin: 4px 0 8px 0;"></div>
    @else
        <div style="text-align: left; margin-bottom: 8px; border-bottom: 2px solid black; padding-bottom: 6px;">
            <div style="font-size: 13pt; font-weight: bold; letter-spacing: 0.5px;">BADAN PUSAT STATISTIK</div>
            <div style="font-size: 11pt; font-weight: bold; color: #1e293b;">PROVINSI JAWA TENGAH</div>
        </div>
    @endif

    {{-- JUDUL SURAT --}}
    <div class="header">
        <div class="title">SURAT PERINTAH KERJA LEMBUR</div>
        <div class="nomor">Nomor: {{ $nomorSurat }}</div>
    </div>

    {{-- PEMBUKA --}}
    <div class="pembuka">
        Sehubungan dengan adanya penyelesaian pekerjaan yang dilakukan di luar jam kerja (lembur) pada bulan {{ $bulanLabel }} Tahun {{ $tahun }}, dengan ini kami memerintahkan pegawai tersebut di bawah ini untuk menyelesaikan pekerjaan yang dimaksud.
    </div>

    {{-- TABEL --}}
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:30%">Nama Pegawai/NIP</th>
                <th style="width:18%">Bulan {{ $bulanLabel }}</th>
                <th>Uraian Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pegawai as $i => $p)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>
                    {{ $p->nama }}<br>
                    <small style="color: #374151;">{{ $p->nip ?? $p->nip_lama }}</small>
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
                <div style="height: 50px;">
                    Pejabat Pembuat Komitmen<br>
                    BPS Provinsi Jawa Tengah
                </div>
                <div class="ttd-space"></div>
                <span class="nama-pejabat" style="font-weight: bold; text-decoration: underline;">{{ $ppk->nama ?? '' }}</span><br>
                @if(!empty($ppk->nip) || !empty($ppk->nip_lama))
                    <div style="font-size: 8.5pt; color: #374151;">
                        NIP. {{ $ppk->nip ?? $ppk->nip_lama }}
                    </div>
                @endif
            </td>
            <td class="ttd-box">
                Mengetahui, {{ $tanggalTtd }}<br>
                a.n. Kepala BPS Provinsi Jawa Tengah<br>
                Kepala Bagian Umum
                <div class="ttd-space"></div>
                <span class="nama-pejabat" style="font-weight: bold; text-decoration: underline;">{{ $kbu->nama ?? '' }}</span><br>
                @if(!empty($kbu->nip) || !empty($kbu->nip_lama))
                    <div style="font-size: 8.5pt; color: #374151;">
                        NIP. {{ $kbu->nip ?? $kbu->nip_lama }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

</div>
</body>
</html>

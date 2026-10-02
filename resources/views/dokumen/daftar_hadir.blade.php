<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: sans-serif;
        font-size: 11px;
    }

    h2 {
        text-align: center;
        margin-bottom: 2px;
    }

    p.sub {
        text-align: right;
        margin-top: 0;
        margin-bottom: 16px;
    }
    thead {
        display: table-header-group;
    }

    tbody tr {
        page-break-inside: avoid;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 6px 8px;
        text-align: center;
    }

    th {
        background: #d0d0d0;
        font-weight: 600;
    }

    td.left {
        text-align: left;
    }

    .ttd {
        height: 50px;
    }

    .ttd-box {
        text-align: center;
        margin-top: 30px;
        float: right;
        width: 40%;
    }

    .ttd-space {
        height: 70px;
    }

    .clearfix::after {
        content: '';
        display: block;
        clear: both;
    }
</style>
</head>
<body>

<h2>DAFTAR HADIR LEMBUR</h2>
<h2>{{ $namaTim }}</h2>

<table>
    <thead>
        <tr>
            <th rowspan="2">Tanggal</th>
            <th rowspan="2">NO</th>
            <th rowspan="2">Nama</th>
            <th colspan="2">Jam</th>
            <th rowspan="2">Tanda Tangan</th>
        </tr>
        <tr>
            <th>Datang</th>
            <th>Pulang</th>
        </tr>
    </thead>
    <tbody>
        @forelse($daftarHadir as $i => $d)
        <tr>
            <td>{{ $i == 0 ? $tanggalLabel : '' }}</td>
            <td>{{ $i + 1 }}</td>
            <td class="left">
                {{ $d->nama }}<br>
                <small>{{ $d->nip }}</small>
            </td>
            <td>{{ $d->jam_mulai_disetujui ? substr($d->jam_mulai_disetujui, 0, 5) : '-' }}</td>
            <td>{{ $d->jam_pulang ?: '-' }}</td>
            <td class="ttd">
                @if($d->signature_path && file_exists(storage_path('app/public/' . $d->signature_path)))
                    <img src="{{ storage_path('app/public/' . $d->signature_path) }}"
                        style="height: 45px; width: auto; display: block; margin: 0 auto;">
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6">Tidak ada data.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="clearfix">
    <div class="ttd-box">
        Mengetahui,<br>
        Kepala Bagian Umum<br>
        <div class="ttd-space"></div>
        <strong>{{ $kbu->nama ?? '' }}</strong><br>
        {{-- NIP {{ $kbu->nip_lama ?? '' }} --}}
    </div>
</div>

</body>
</html>

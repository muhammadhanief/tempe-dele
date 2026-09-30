@extends('layouts.app')

@section('title', 'Daftar Pengajuan Lembur - ' . ($tim->nama_tim ?? ''))

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">

        {{-- Period Picker --}}
            <div class="relative w-full sm:w-auto shrink-0" id="periodPicker">
                <button type="button" id="periodBtn"
                    class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors">

                    <span class="inline-flex items-center gap-2 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0">
                            <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                        </svg>

                        <span id="periodLabel" class="leading-none truncate">
                            @if(empty($selectedMonth))
                                Semua Bulan {{ $selectedYear }}
                            @else
                                {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('M Y') }}
                            @endif
                        </span>
                    </span>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <input type="hidden" id="periodValue" name="period" value="{{ $bulan }}">

                <div id="periodPanel"
                    class="hidden absolute z-50 mt-2 left-0 w-full sm:w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white shadow-lg p-3">

                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="yearPrev"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>

                        <span id="yearLabel" class="text-sm font-medium text-gray-900">{{ $selectedYear }}</span>

                        <button type="button" id="yearNext"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-2.5">
                        <button type="button" id="btnAllMonthsOfYear"
                            class="w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center {{ empty($selectedMonth) ? 'bg-[#faa938] text-white border-[#faa938]' : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]' }}">
                            Semua Bulan (<span id="allMonthsYearLabel">{{ $selectedYear }}</span>)
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                    <div class="flex items-center justify-between mt-3">
                        <button type="button" id="btnThisMonth"
                            class="text-sm font-medium text-gray-500 hover:text-[#faa938]">
                            Bulan ini
                        </button>

                        <button type="button" id="btnClosePanel"
                            class="px-3 py-1 text-sm font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938]">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

        {{-- Filter Pegawai --}}
        <div class="relative w-full sm:w-[22rem]">
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                onclick="toggleDropdownPegawai()" oninput="filterDropdownPegawai()" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">

            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                    <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                </svg>
            </div>

            <div id="dropdownPegawai"
                class="absolute z-40 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listPegawai"></ul>
            </div>
        </div>

        {{-- Filter Status --}}
        <div class="relative w-full sm:w-48 shrink-0">
            <select id="filterStatus" onchange="onFilterStatusChange(this.value)"
                class="h-10 w-full appearance-none rounded-xl border border-gray-200 bg-white pl-3.5 pr-8 text-sm font-medium text-gray-700 shadow-2xs hover:border-[#faa938] focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
                <option value="all" {{ (($status ?? 'all') === 'all') ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ (($status ?? '') === 'pending') ? 'selected' : '' }}>Menunggu</option>
                <option value="menunggu_kabag" {{ (($status ?? '') === 'menunggu_kabag') ? 'selected' : '' }}>Menunggu Kabag</option>
                <option value="approved" {{ (($status ?? '') === 'approved') ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ (($status ?? '') === 'rejected') ? 'selected' : '' }}>Ditolak</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        {{-- Reset filter --}}
        <button type="button" id="btnResetFilter"
            class="hidden h-10 w-full sm:w-auto px-4 text-sm font-medium border border-gray-200 bg-white text-gray-500 rounded-xl sm:rounded-full hover:border-[#faa938] hover:text-[#faa938] transition-colors">
            Reset
        </button>
    </div>

    {{-- Petunjuk Geser di Layar HP (Mobile) --}}
    <div class="sm:hidden flex items-center gap-1.5 px-1 mb-2 text-[11px] font-medium text-slate-500">
        <svg class="h-3.5 w-3.5 text-amber-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <span>Geser tabel ke kiri / kanan untuk melihat kolom lengkap</span>
    </div>

    {{-- Tabel: mobile/tablet/desktop tetap tabel, hanya horizontal scroll --}}
    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[1080px] table-auto rounded-xl">
            <thead>
                <tr class="bg-gray-100">
                    <th class="w-12 rounded-tl-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">No</th>
                    <th class="w-48 px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama Pegawai</th>
                    <th class="w-36 px-3 py-3 text-center text-xs font-semibold text-gray-900">
                        <div class="inline-flex items-center justify-center gap-1.5 w-full">
                            <span>Tanggal Lembur</span>
                            <div class="relative inline-block text-left">
                                <select id="headerSortTanggal" onchange="onHeaderSortChange(this.value)"
                                    class="appearance-none bg-white hover:bg-gray-50 rounded-md pl-1.5 pr-4 py-0.5 text-[11px] font-medium text-gray-700 cursor-pointer border border-gray-300 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-1 focus:ring-[#faa938]/40 transition-all">
                                    <option value="desc" {{ (($sort ?? 'desc') === 'desc') ? 'selected' : '' }}>Terbaru</option>
                                    <option value="asc" {{ (($sort ?? 'desc') === 'asc') ? 'selected' : '' }}>Terlama</option>
                                    <option value="priority" {{ (($sort ?? 'desc') === 'priority') ? 'selected' : '' }}>Prioritas</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-1 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th class="w-28 px-3 py-3 text-center text-xs font-semibold text-gray-900">Jam Diajukan</th>
                    <th class="w-28 px-3 py-3 text-center text-xs font-semibold text-gray-900">Jam Disetujui</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Uraian Kegiatan</th>
                    <th class="w-40 px-3 py-3 text-center text-xs font-semibold text-gray-900">Data Presensi</th>
                    <th class="w-32 px-3 py-3 text-center text-xs font-semibold text-gray-900">Status</th>
                    <th class="w-24 rounded-tr-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300" id="tabelPengajuan">
                @forelse($pengajuan as $i => $p)
                    @php
                        // Untuk pengajuan baru/pending, jam persetujuan SELALU default ke jam yang diajukan pegawai
                        $jamMulaiDefault = ($p->status === 'pending' || empty($p->jam_mulai_disetujui))
                            ? ($p->jam_mulai ? substr($p->jam_mulai, 0, 5) : '')
                            : substr($p->jam_mulai_disetujui, 0, 5);

                        $jamSelesaiDefault = ($p->status === 'pending' || empty($p->jam_selesai_disetujui))
                            ? ($p->jam_selesai ? substr($p->jam_selesai, 0, 5) : '')
                            : substr($p->jam_selesai_disetujui, 0, 5);
                    @endphp

                    <tr class="bg-white transition-all duration-200 hover:bg-gray-50"
                        data-nama="{{ strtolower($p->nama_pegawai) }}"
                        data-nip="{{ $p->nip_pegawai }}"
                        data-tanggal="{{ $p->date }}">

                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            {{ $pengajuan->firstItem() + $i }}
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[180px] whitespace-normal break-words">
                                {{ $p->nama_pegawai }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $p->nip_pegawai }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-left text-xs text-gray-900">
                            {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d F Y') }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-center text-xs text-gray-900">
                            {{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : '-' }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-center text-xs text-gray-900" id="jam-disetujui-{{ $p->id_transaksi }}">
                            @if($p->jam_mulai_disetujui && $p->jam_selesai_disetujui)
                                {{ substr($p->jam_mulai_disetujui, 0, 5) }} - {{ substr($p->jam_selesai_disetujui, 0, 5) }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[280px] whitespace-normal break-words" id="uraian-display-{{ $p->id_transaksi }}">
                                {{ $p->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="openModalPresensi({{ $p->id_transaksi }})"
                                class="inline-flex items-center justify-center gap-1 text-xs font-medium
                                {{ $p->has_presensi ? 'text-green-600 underline' : 'text-slate-500' }}">
                                {{ $p->has_presensi ? 'Informasi tersedia' : 'Tidak ada informasi' }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/>
                                </svg>
                            </button>
                        </td>

                        <td class="px-3 py-3 text-center" id="status-{{ $p->id_transaksi }}">
                            @if($p->status === 'pending')
                                <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Menunggu</span>
                            @elseif($p->status === 'menunggu_kabag')
                                <span class="whitespace-nowrap rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">Menunggu Kabag</span>
                            @elseif($p->status === 'approved')
                                <span class="whitespace-nowrap rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Disetujui</span>
                            @elseif($p->status === 'rejected')
                                <span class="whitespace-nowrap rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-600">Ditolak</span>
                            @elseif($p->status === 'cancelled')
                                <span class="whitespace-nowrap rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 border border-gray-300">Dibatalkan</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center" id="aksi-{{ $p->id_transaksi }}">
                            @if($p->status === 'pending')
                                <button type="button"
                                    onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ $jamMulaiDefault }}', '{{ $jamSelesaiDefault }}', {{ json_encode($p->note ?? '') }}, 'pending', {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all shadow-sm bg-[#faa938] text-slate-950 hover:bg-[#fd9a10] hover:shadow cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Proses</span>
                                </button>
                            @elseif($p->status === 'cancelled')
                                <span class="text-slate-500 text-xs italic">Dibatalkan</span>
                            @else
                                <button type="button"
                                    onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ $jamMulaiDefault }}', '{{ $jamSelesaiDefault }}', {{ json_encode($p->note ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                    <span>Koreksi</span>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500 font-medium">
                            Belum ada pengajuan lembur yang sesuai dengan kriteria yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pengajuan->hasPages())
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex items-center gap-2 rounded bg-white p-1">

                @if($pengajuan->onFirstPage())
                    <span class="cursor-not-allowed rounded border p-1 text-gray-300">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $pengajuan->previousPageUrl() }}">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="whitespace-nowrap text-sm text-gray-500">
                    Page {{ $pengajuan->currentPage() }} of {{ $pengajuan->lastPage() }}
                </p>

                @if($pengajuan->hasMorePages())
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $pengajuan->nextPageUrl() }}">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="cursor-not-allowed rounded border p-1 text-gray-300">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                @endif

            </nav>
        </div>
    @endif
</div>

{{-- Modal Presensi --}}
<div id="modalPresensi" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalPresensi()"></div>

    <div class="relative flex min-h-full items-center justify-center px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">

            <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Informasi Presensi</h2>
                    <p class="mt-0.5 text-xs text-gray-400" id="presensiSubtitle">-</p>
                </div>

                <button type="button" onclick="closeModalPresensi()"
                    class="text-xl leading-none text-gray-400 hover:text-gray-600">
                    &times;
                </button>
            </div>

            <div class="space-y-4 px-5 py-5 sm:px-6" id="presensiBody">
                <p class="py-4 text-center text-sm text-gray-400">Memuat data...</p>
            </div>

        </div>
    </div>
</div>

{{-- Modal Keputusan --}}
<div id="modalKeputusan" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" onclick="closeModalKeputusan()"></div>

    <div class="relative flex min-h-screen items-center justify-center p-3 sm:p-4 my-auto">
        <div class="w-full max-w-lg flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden my-auto border border-gray-100">

            {{-- Modal Header --}}
            <div class="shrink-0 flex items-center justify-between border-b px-4 sm:px-6 py-3 bg-white">
                <div>
                    <h2 class="text-base font-bold text-gray-900" id="modalTitle">Keputusan Lembur</h2>
                    <p class="text-xs text-gray-500" id="modalSubtitle">Pilih keputusan persetujuan atau penolakan lembur pegawai.</p>
                </div>

                <button type="button" onclick="closeModalKeputusan()"
                    class="text-xl leading-none text-gray-400 hover:text-gray-700 p-1">
                    &times;
                </button>
            </div>

            {{-- Modal Body (Compact & Langsung Terlihat Semua Tanpa Scroll) --}}
            <div class="p-4 sm:p-5 space-y-3">
                {{-- Status Terkunci Banner (Jika status bukan pending) --}}
                <div id="wrapperStatusLocked" class="hidden">
                    <div id="statusLockedBanner" class="flex items-center gap-2 rounded-xl p-2.5 border text-xs font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="statusLockedText">Status terkunci.</span>
                    </div>
                </div>

                {{-- Warning durasi --}}
                <div id="warningDurasi" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-700">
                    ⚠️ <span id="warningText"></span>
                </div>

                {{-- 1. Jam Disetujui (2 Kolom Rapi) --}}
                <div id="wrapperJamDisetujui" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">Jam Mulai Disetujui</label>
                        <input id="kJamMulai" type="time"
                            class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm outline-none focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">Jam Selesai Disetujui</label>
                        <input id="kJamSelesai" type="time"
                            class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm outline-none focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30">
                        <p id="kJamSelesaiPresensiHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-blue-700 font-medium">
                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Maks: <strong id="kJamSelesaiPresensiVal">-</strong> (presensi)</span>
                        </p>
                    </div>
                </div>

                {{-- 2. Uraian Kegiatan (Compact Rows=2 dengan Counter) --}}
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-700">Uraian Kegiatan</label>
                        <div class="flex items-center gap-1.5">
                            <span id="kUraianCount" class="text-[10px] text-gray-400">0 / 2000</span>
                            <span id="kUraianBadge" class="rounded-full px-1.5 py-0.2 text-[10px] font-medium"></span>
                        </div>
                    </div>
                    <textarea id="kUraian" rows="2" maxlength="2000"
                        class="w-full resize-y rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm outline-none transition-all focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30"
                        placeholder="Uraian kegiatan lembur..."></textarea>
                    <p id="kUraianLockedHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-amber-700">
                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Uraian terkunci karena belum ada presensi.</span>
                    </p>
                </div>

                {{-- 3. Catatan (Compact Rows=2) --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">
                        Catatan <span class="text-gray-400 font-normal" id="kCatatanHint">(Opsional)</span>
                    </label>
                    <textarea id="kCatatan" rows="2"
                        class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm outline-none focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30"
                        placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                {{-- 4. Pilihan Keputusan: DI BAWAH (ALUR NATURAL SETELAH REVIEW DATA) --}}
                <div id="wrapperPilihanKeputusan" class="space-y-1 pt-1 border-t border-gray-100">
                    <label class="block text-xs font-semibold text-gray-700">Keputusan Akhir:</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" onclick="setKeputusan('approved')" id="kBtnSetujui"
                            class="flex items-center justify-center gap-1.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 px-3 py-2 text-xs sm:text-sm font-bold text-emerald-800 transition-all hover:bg-emerald-100">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Setujui</span>
                        </button>

                        <button type="button" onclick="setKeputusan('rejected')" id="kBtnTolak"
                            class="flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Tolak</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="shrink-0 flex items-center justify-end gap-2 border-t px-4 py-2.5 sm:px-6 sm:py-3 bg-gray-50">
                <button type="button" onclick="closeModalKeputusan()"
                    class="rounded-lg border border-gray-300 px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-100 bg-white">
                    Batal
                </button>

                <button type="button" onclick="simpanKeputusan()" id="btnSimpan"
                    class="rounded-lg bg-[#faa938] px-4 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-bold text-slate-950 transition-all hover:bg-[#fd9a10] shadow-sm">
                    Simpan Keputusan
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
function makeBtn(text, className, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.className = className;
    button.addEventListener('click', function (event) {
        event.stopPropagation();
        onClick();
    });
    return button;
}

function pad2(value) {
    return String(value).padStart(2, '0');
}

// =====================
// STATE FILTER
// =====================
const urlParams = new URLSearchParams(window.location.search);
const activeNip = urlParams.get('nip') || '';
const activeStatus = urlParams.get('status') || '';
const activeSort = urlParams.get('sort') || '';
const activeBulan = urlParams.get('bulan') || '';
let cachedAnggota = [];

// =====================
// FETCH ANGGOTA TIM
// =====================
fetch('/ketua-tim/pengajuan/anggota')
    .then(response => response.json())
    .then(data => {
        cachedAnggota = Array.isArray(data) ? data : [];
        if (activeNip) {
            const emp = cachedAnggota.find(e => e.nip === activeNip);
            if (emp) {
                document.getElementById('searchPegawai').value = `${emp.nama} (${emp.nip})`;
            }
        }
        updateResetBtn();
        renderDropdownPegawai('');
    })
    .catch(() => {
        cachedAnggota = [];
    });

function renderDropdownPegawai(filter = '') {
    const list = document.getElementById('listPegawai');
    if (!list) return;

    list.innerHTML = '';

    const liSemua = document.createElement('li');
    liSemua.className = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
    liSemua.textContent = 'Semua anggota';
    liSemua.onclick = () => pilihPegawai(null);
    list.appendChild(liSemua);

    const keyword = filter.toLowerCase();

    cachedAnggota
        .filter(emp => `${emp.nama ?? ''} ${emp.nip ?? ''}`.toLowerCase().includes(keyword))
        .forEach(emp => {
            const li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
            li.textContent = `${emp.nama} (${emp.nip})`;
            li.onclick = () => pilihPegawai(emp);
            list.appendChild(li);
        });
}

window.toggleDropdownPegawai = function () {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');

    if (!dropdown || !search) return;

    dropdown.classList.toggle('hidden');
    renderDropdownPegawai(search.value);
};

window.filterDropdownPegawai = function () {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');

    if (!dropdown || !search) return;

    renderDropdownPegawai(search.value);
    dropdown.classList.remove('hidden');
};

function pilihPegawai(emp) {
    const params = new URLSearchParams(window.location.search);
    const pVal = document.getElementById('periodValue').value;
    if (pVal && pVal !== 'all') {
        params.set('bulan', pVal);
    } else {
        params.delete('bulan');
    }

    if (emp) {
        params.set('nip', emp.nip);
    } else {
        params.delete('nip');
    }
    params.delete('page');

    window.location.href = `?${params.toString()}`;
}

// =====================
// FILTER STATUS & SORT TANGGAL
// =====================
window.onFilterStatusChange = function(statusVal) {
    const params = new URLSearchParams(window.location.search);
    if (statusVal && statusVal !== 'all') {
        params.set('status', statusVal);
    } else {
        params.delete('status');
    }
    params.delete('page');
    window.location.href = `?${params.toString()}`;
};

window.onHeaderSortChange = function(sortVal) {
    const params = new URLSearchParams(window.location.search);
    if (sortVal && sortVal !== 'desc') {
        params.set('sort', sortVal);
    } else {
        params.delete('sort');
    }
    params.delete('page');
    window.location.href = `?${params.toString()}`;
};

function updateResetBtn() {
    const btn = document.getElementById('btnResetFilter');
    if (!btn) return;

    const hasBulanFilter = (activeBulan && activeBulan !== 'all' && !activeBulan.endsWith('-all'));
    (activeNip || (activeStatus && activeStatus !== 'all') || (activeSort && activeSort !== 'desc') || hasBulanFilter)
        ? btn.classList.remove('hidden')
        : btn.classList.add('hidden');
}

updateResetBtn();

document.getElementById('btnResetFilter')?.addEventListener('click', () => {
    window.location.href = window.location.pathname;
});

    // =====================
    // PERIOD PICKER
    // =====================
    (function () {
        const el = (id) => document.getElementById(id);

        const now = new Date();
        function pad2(n) { return String(n).padStart(2, '0'); }

        const picker = el('periodPicker');
        const btn = el('periodBtn');
        const panel = el('periodPanel');
        const grid = el('monthGrid');
        const navLabel = el('yearLabel');
        const periodLabel = el('periodLabel');
        const periodValue = el('periodValue');
        const btnPrev = el('yearPrev');
        const btnNext = el('yearNext');
        const btnThisMonth = el('btnThisMonth');
        const btnClose = el('btnClosePanel');

        if (!picker || !btn || !panel) return;

        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        let view = 'month';
        let selYear  = {{ $selectedYear }};
        let selMonth = {{ $selectedMonth !== null ? ($selectedMonth - 1) : 'null' }};
        let viewYear = selYear;

        function updateDisplayOnly(y, m) {
            if (m === null) {
                periodLabel.textContent = `Semua Bulan ${y}`;
                periodValue.value = (y === now.getFullYear()) ? 'all' : `${y}-all`;
            } else {
                periodLabel.textContent = `${monthShort[m]} ${y}`;
                periodValue.value = `${y}-${pad2(m + 1)}`;
            }
        }

        function setPeriod(y, m) {
            selYear = y;
            selMonth = m;
            updateDisplayOnly(y, m);

            const params = new URLSearchParams(window.location.search);
            if (m === null) {
                if (y === now.getFullYear()) {
                    params.set('bulan', 'all');
                } else {
                    params.set('bulan', `${y}-all`);
                }
            } else {
                params.set('bulan', `${y}-${pad2(m + 1)}`);
            }
            params.delete('page');

            window.location.href = `?${params.toString()}`;
        }

        function openPanel() {
            viewYear = selYear;
            renderMonth();
            panel.classList.remove('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';

            const allMonthsBtn = el('btnAllMonthsOfYear');
            const allMonthsLabel = el('allMonthsYearLabel');
            if (allMonthsLabel) allMonthsLabel.textContent = String(viewYear);
            if (allMonthsBtn) {
                const isAllMonthsSelected = (selMonth === null && viewYear === selYear);
                allMonthsBtn.className = 'w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center ' + (
                    isAllMonthsSelected
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]'
                );
                allMonthsBtn.onclick = (e) => {
                    e.stopPropagation();
                    setPeriod(viewYear, null);
                    closePanel();
                };
            }

            grid.innerHTML = '';

            monthNames.forEach((name, m) => {
                const isSel = (m === selMonth && viewYear === selYear);
                const isNow = (m === now.getMonth() && viewYear === now.getFullYear());

                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSel
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white'
                            : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
                );

                grid.appendChild(makeBtn(name.slice(0, 3), cls, () => {
                    setPeriod(viewYear, m);
                    closePanel();
                }));
            });
        }

        function renderYear() {
            view = 'year';

            const startYear = Math.floor(viewYear / 12) * 12;

            navLabel.textContent = `${startYear} - ${startYear + 11}`;
            navLabel.className = 'text-sm font-medium text-gray-400 select-none cursor-default';
            grid.innerHTML = '';

            for (let y = startYear; y < startYear + 12; y++) {
                const isSel = (y === selYear);
                const isNow = (y === now.getFullYear());
                const _y = y;

                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSel
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white'
                            : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
                );

                grid.appendChild(makeBtn(String(_y), cls, () => {
                    viewYear = _y;
                    renderMonth();
                }));
            }
        }

        function navigate(dir) {
            if (view === 'month') {
                viewYear += dir;
                renderMonth();
            } else {
                viewYear += dir * 12;
                renderYear();
            }
        }

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.contains('hidden') ? openPanel() : closePanel();
        });

        navLabel.addEventListener('click', (e) => {
            e.stopPropagation();
            if (view === 'month') renderYear();
        });

        btnPrev?.addEventListener('click', (e) => {
            e.stopPropagation();
            navigate(-1);
        });

        btnNext?.addEventListener('click', (e) => {
            e.stopPropagation();
            navigate(1);
        });

        btnThisMonth?.addEventListener('click', (e) => {
            e.stopPropagation();
            setPeriod(now.getFullYear(), now.getMonth());
            closePanel();
        });

        btnClose?.addEventListener('click', (e) => {
            e.stopPropagation();
            closePanel();
        });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) closePanel();
        });

        updateDisplayOnly(selYear, selMonth);
    })();

// =====================
// MODAL PRESENSI
// =====================
window.openModalPresensi = function(id) {
    const modal = document.getElementById('modalPresensi');
    const subtitle = document.getElementById('presensiSubtitle');
    const body = document.getElementById('presensiBody');

    subtitle.textContent = '-';
    body.innerHTML = '<p class="py-4 text-center text-sm text-gray-400">Memuat data...</p>';

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    fetch(`/ketua-tim/pengajuan/${id}/presensi`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        subtitle.textContent = `${data.nama} · ${data.nip}`;

        body.innerHTML = `
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Tanggal</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.tanggal}</div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Status Kehadiran</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                    ${data.status ?? '<span class="text-gray-400">Tidak ada data presensi</span>'}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Jam Kedatangan</label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.jam_masuk ?? '-'}</div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Jam Kepulangan</label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.jam_pulang ?? '-'}</div>
                </div>
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = '<p class="py-4 text-center text-sm text-red-400">Gagal memuat data presensi.</p>';
    });
};

window.closeModalPresensi = function() {
    document.getElementById('modalPresensi').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

// =====================
// MODAL KEPUTUSAN
// =====================
let currentId = null;
let keputusan = null;
let isStatusLocked = false;
let currentUraian = '';
let currentHasPresensi = false;
let currentJamSelesaiPresensi = '';

window.openModalKeputusan = function(id, jamMulai, jamSelesai, catatan, status, uraian, hasPresensi, jamSelesaiPresensi) {
    currentId = id;
    const currentStatus = status || 'pending';
    isStatusLocked = (currentStatus !== 'pending');
    keputusan = isStatusLocked ? currentStatus : null;
    currentUraian = (uraian !== undefined && uraian !== null) ? uraian : '';
    currentHasPresensi = Boolean(hasPresensi);
    currentJamSelesaiPresensi = (jamSelesaiPresensi !== undefined && jamSelesaiPresensi !== null) ? String(jamSelesaiPresensi).trim() : '';

    const elJamMulai = document.getElementById('kJamMulai');
    const elJamSelesai = document.getElementById('kJamSelesai');
    const elPresensiHint = document.getElementById('kJamSelesaiPresensiHint');
    const elPresensiVal = document.getElementById('kJamSelesaiPresensiVal');

    elJamMulai.value = jamMulai || '';
    elJamSelesai.value = jamSelesai || '';
    document.getElementById('kCatatan').value = catatan || '';

    if (currentJamSelesaiPresensi) {
        elJamSelesai.setAttribute('max', currentJamSelesaiPresensi);
        if (elPresensiHint && elPresensiVal) {
            elPresensiVal.textContent = currentJamSelesaiPresensi;
            elPresensiHint.classList.remove('hidden');
        }
    } else {
        elJamSelesai.removeAttribute('max');
        if (elPresensiHint) {
            elPresensiHint.classList.add('hidden');
        }
    }

    const elUraian = document.getElementById('kUraian');
    const elUraianBadge = document.getElementById('kUraianBadge');
    const elUraianHint = document.getElementById('kUraianLockedHint');

    elUraian.value = currentUraian;
    const elUraianCount = document.getElementById('kUraianCount');
    if (elUraianCount) elUraianCount.textContent = `${currentUraian.length} / 2000`;

    if (currentHasPresensi) {
        elUraian.readOnly = false;
        elUraian.className = 'w-full resize-y rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none transition-all focus:border-[#faa938] focus:ring-2 focus:ring-[#faa938]/20';
        elUraianBadge.textContent = 'Presensi Ada (Dapat Diedit)';
        elUraianBadge.className = 'rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 border border-emerald-200';
        elUraianHint.classList.add('hidden');
    } else {
        elUraian.readOnly = true;
        elUraian.className = 'w-full resize-y rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 outline-none cursor-not-allowed';
        elUraianBadge.textContent = 'Presensi Belum Ada';
        elUraianBadge.className = 'rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 border border-amber-200';
        elUraianHint.classList.remove('hidden');
    }

    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const wrapperPilihan = document.getElementById('wrapperPilihanKeputusan');
    const wrapperLocked = document.getElementById('wrapperStatusLocked');
    const bannerLocked = document.getElementById('statusLockedBanner');
    const lockedText = document.getElementById('statusLockedText');
    const wrapperJam = document.getElementById('wrapperJamDisetujui');
    const catatanHint = document.getElementById('kCatatanHint');
    const btnSimpan = document.getElementById('btnSimpan');

    resetBtnKeputusan();

    if (currentStatus === 'menunggu_kabag') {
        modalTitle.textContent = 'Koreksi Jam & Catatan';
        modalSubtitle.textContent = 'Status Menunggu Kabag terkunci. Anda dapat mengoreksi jam disetujui atau catatan.';
        wrapperPilihan.classList.add('hidden');
        wrapperLocked.classList.remove('hidden');
        bannerLocked.className = 'flex items-center gap-2 rounded-xl bg-blue-50 p-3 border border-blue-200 text-xs text-blue-800 font-medium';
        lockedText.innerHTML = 'Status <b>Menunggu Kabag</b> terkunci. Pengajuan telah diteruskan ke Kabag Umum. Anda hanya dapat mengoreksi jam disetujui dan catatan.';
        wrapperJam.classList.remove('hidden');
        catatanHint.textContent = '(Opsional)';
        btnSimpan.textContent = 'Simpan Koreksi';
    } else if (currentStatus === 'approved') {
        modalTitle.textContent = 'Koreksi Jam & Catatan';
        modalSubtitle.textContent = 'Status Disetujui Final terkunci. Anda dapat mengoreksi jam disetujui atau catatan.';
        wrapperPilihan.classList.add('hidden');
        wrapperLocked.classList.remove('hidden');
        bannerLocked.className = 'flex items-center gap-2 rounded-xl bg-emerald-50 p-3 border border-emerald-200 text-xs text-emerald-800 font-medium';
        lockedText.innerHTML = 'Status <b>Disetujui Final</b> terkunci. Anda hanya dapat mengoreksi jam disetujui dan catatan.';
        wrapperJam.classList.remove('hidden');
        catatanHint.textContent = '(Opsional)';
        btnSimpan.textContent = 'Simpan Koreksi';
    } else if (currentStatus === 'rejected') {
        modalTitle.textContent = 'Koreksi Catatan Penolakan';
        modalSubtitle.textContent = 'Status Ditolak terkunci. Anda dapat mengoreksi catatan alasan penolakan.';
        wrapperPilihan.classList.add('hidden');
        wrapperLocked.classList.remove('hidden');
        bannerLocked.className = 'flex items-center gap-2 rounded-xl bg-rose-50 p-3 border border-rose-200 text-xs text-rose-800 font-medium';
        lockedText.innerHTML = 'Status <b>Ditolak</b> terkunci. Anda dapat mengoreksi catatan alasan penolakan.';
        wrapperJam.classList.add('hidden');
        catatanHint.textContent = '(Wajib)';
        btnSimpan.textContent = 'Simpan Koreksi';
    } else {
        // Pending
        modalTitle.textContent = 'Keputusan Lembur';
        modalSubtitle.textContent = 'Pilih keputusan persetujuan atau penolakan lembur pegawai.';
        wrapperPilihan.classList.remove('hidden');
        wrapperLocked.classList.add('hidden');
        wrapperJam.classList.remove('hidden');
        catatanHint.textContent = '(Opsional)';
        btnSimpan.textContent = 'Simpan Keputusan';
        setKeputusan('approved');
    }

    cekWarningDurasi();

    document.getElementById('modalKeputusan').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModalKeputusan = function() {
    document.getElementById('modalKeputusan').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    currentId = null;
    keputusan = null;
    isStatusLocked = false;
    currentUraian = '';
    currentHasPresensi = false;
};

window.setKeputusan = function(value) {
    if (isStatusLocked) return;

    keputusan = value;
    resetBtnKeputusan();

    const wrapperJam = document.getElementById('wrapperJamDisetujui');
    const catatanHint = document.getElementById('kCatatanHint');

    if (value === 'rejected') {
        document.getElementById('kBtnTolak').className = 'flex items-center justify-center gap-1.5 rounded-xl border-2 border-rose-500 bg-rose-50 px-3 py-2 text-xs sm:text-sm font-bold text-rose-700 transition-all shadow-xs';
        wrapperJam.classList.add('hidden');
        catatanHint.textContent = '(Wajib jika menolak)';
    } else {
        document.getElementById('kBtnSetujui').className = 'flex items-center justify-center gap-1.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 px-3 py-2 text-xs sm:text-sm font-bold text-emerald-800 transition-all shadow-xs';
        wrapperJam.classList.remove('hidden');
        catatanHint.textContent = '(Opsional)';
    }
};

function resetBtnKeputusan() {
    document.getElementById('kBtnTolak').className = 'flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700';
    document.getElementById('kBtnSetujui').className = 'flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700';
}

function cekWarningDurasi() {
    const mulai = document.getElementById('kJamMulai').value;
    const selesai = document.getElementById('kJamSelesai').value;
    const warning = document.getElementById('warningDurasi');
    const text = document.getElementById('warningText');

    if (!mulai || !selesai) {
        warning.classList.add('hidden');
        return;
    }

    const [jm, mm] = mulai.split(':').map(Number);
    const [js, ms] = selesai.split(':').map(Number);
    const totalMenit = (js * 60 + ms) - (jm * 60 + mm);

    if (totalMenit <= 0) {
        warning.classList.add('hidden');
        return;
    }

    const jam = Math.floor(totalMenit / 60);

    if (jam > 6) {
        text.textContent = `Durasi ${jam} jam melebihi batas maksimal lembur hari libur (6 jam).`;
        warning.classList.remove('hidden');
    } else if (jam > 4) {
        text.textContent = `Durasi ${jam} jam melebihi batas maksimal lembur hari kerja (4 jam).`;
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

document.getElementById('kJamMulai')?.addEventListener('change', cekWarningDurasi);
document.getElementById('kJamSelesai')?.addEventListener('change', cekWarningDurasi);
document.getElementById('kUraian')?.addEventListener('input', function() {
    const elCount = document.getElementById('kUraianCount');
    if (elCount) elCount.textContent = `${this.value.length} / 2000`;
});

window.simpanKeputusan = function() {
    if (!keputusan) {
        alert('Pilih keputusan terlebih dahulu.');
        return;
    }

    const jamMulai = document.getElementById('kJamMulai').value;
    const jamSelesai = document.getElementById('kJamSelesai').value;
    const catatan = document.getElementById('kCatatan').value;
    const uraian = document.getElementById('kUraian').value;

    if (keputusan !== 'rejected' && (!jamMulai || !jamSelesai)) {
        alert('Jam mulai dan jam selesai wajib diisi.');
        return;
    }

    if (keputusan !== 'rejected' && currentJamSelesaiPresensi && jamSelesai > currentJamSelesaiPresensi) {
        alert(`Jam selesai disetujui (${jamSelesai}) tidak boleh melebihi jam kepulangan presensi pegawai (${currentJamSelesaiPresensi}).`);
        document.getElementById('kJamSelesai').focus();
        return;
    }

    if (keputusan === 'rejected' && !catatan.trim()) {
        alert('Catatan/alasan penolakan wajib diisi jika menolak.');
        return;
    }

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch(`/ketua-tim/pengajuan/${currentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            status: keputusan,
            jam_mulai_disetujui: jamMulai || null,
            jam_selesai_disetujui: jamSelesai || null,
            note: catatan,
            uraian: uraian,
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Gagal menyimpan, coba lagi.');
        }
        return data;
    })
    .then(data => {
        const approvedMulai = data.jam_mulai_disetujui ? data.jam_mulai_disetujui.substring(0, 5) : '';
        const approvedSelesai = data.jam_selesai_disetujui ? data.jam_selesai_disetujui.substring(0, 5) : '';

        const finalStatus = data.status || keputusan;
        let badgeClass = 'bg-amber-100 text-amber-700';
        let badgeText = 'Menunggu';

        if (finalStatus === 'menunggu_kabag') {
            badgeClass = 'bg-blue-100 text-blue-700';
            badgeText = 'Menunggu Kabag';
        } else if (finalStatus === 'approved') {
            badgeClass = 'bg-green-100 text-green-700';
            badgeText = 'Disetujui';
        } else if (finalStatus === 'rejected') {
            badgeClass = 'bg-red-100 text-red-600';
            badgeText = 'Ditolak';
        }

        const statusElement = document.getElementById(`status-${currentId}`);
        if (statusElement) {
            statusElement.innerHTML = `<span class="${badgeClass} whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium">${badgeText}</span>`;
        }

        if (data.uraian !== undefined) {
            currentUraian = data.uraian;
            const uraianElement = document.getElementById(`uraian-display-${currentId}`);
            if (uraianElement) {
                uraianElement.textContent = data.uraian || '-';
            }
        }

        const aksiElement = document.getElementById(`aksi-${currentId}`);
        if (aksiElement) {
            const safeNote = JSON.stringify(data.note || catatan || '');
            const safeUraian = JSON.stringify(currentUraian);
            const presensiFlag = currentHasPresensi ? 1 : 0;
            aksiElement.innerHTML = `
                <button type="button" onclick='openModalKeputusan(${currentId}, "${approvedMulai}", "${approvedSelesai}", ${safeNote}, "${finalStatus}", ${safeUraian}, ${presensiFlag}, "${currentJamSelesaiPresensi}")'
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    <span>Koreksi</span>
                </button>
            `;
        }

        const jamDisetujuiElement = document.getElementById(`jam-disetujui-${currentId}`);
        if (jamDisetujuiElement) {
            jamDisetujuiElement.textContent = (finalStatus !== 'rejected' && approvedMulai && approvedSelesai)
                ? `${approvedMulai} - ${approvedSelesai}`
                : '-';
        }

        closeModalKeputusan();
    })
    .catch(error => {
        alert(error.message || 'Gagal menyimpan, coba lagi.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = isStatusLocked ? 'Simpan Koreksi' : 'Simpan Keputusan';
    });
};
</script>
@endpush

@endsection
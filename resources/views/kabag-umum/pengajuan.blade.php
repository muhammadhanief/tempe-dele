@extends('layouts.app')

@section('title', 'Persetujuan Lembur - Kabag Umum')

@section('content')

<div class="w-full max-w-full px-2 sm:px-3 my-4">

    {{-- Banner Header --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-5 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#faa938]/20 px-3 py-1 text-xs font-semibold text-[#faa938]">
                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Wewenang Kepala Bagian Umum
                </span>
                <h1 class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-white">
                    Persetujuan & Monitoring Lembur Satker
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-300">
                    Persetujuan akhir lembur lintas tim kerja dan monitoring seluruh kegiatan lembur pegawai BPS.
                </p>
            </div>

            {{-- Stat counter cards --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="rounded-xl bg-white/10 px-4 py-2.5 text-center backdrop-blur-sm border border-white/10">
                    <div class="text-xs text-slate-300 font-medium">Perlu Persetujuan</div>
                    <div class="text-xl font-bold text-amber-400">{{ $stats['menunggu_kabag'] ?? 0 }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-2.5 text-center backdrop-blur-sm border border-white/10">
                    <div class="text-xs text-slate-300 font-medium">Disetujui Final</div>
                    <div class="text-xl font-bold text-green-400">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-2.5 text-center backdrop-blur-sm border border-white/10">
                    <div class="text-xs text-slate-300 font-medium">Ditolak</div>
                    <div class="text-xl font-bold text-red-400">{{ $stats['rejected'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar Filter & Pencarian --}}
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Filter Periode Bulan --}}
            <div class="relative shrink-0">
                <button type="button" id="periodBtn"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-sm hover:border-[#faa938] hover:text-[#faa938] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="periodLabel">
                        {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-40 shrink-0">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <div id="periodPanel"
                    class="hidden absolute z-50 mt-2 left-0 w-72 rounded-2xl border border-gray-200 bg-white shadow-xl p-3.5">
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="yearPrev"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>
                        <span id="yearLabel" class="text-sm font-bold text-gray-900">2026</span>
                        <button type="button" id="yearNext"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                    <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-gray-100">
                        <button type="button" id="btnThisMonth"
                            class="text-xs font-semibold text-gray-600 hover:text-[#faa938] transition-colors">
                            Bulan ini
                        </button>
                        <button type="button" id="btnClosePanel"
                            class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Status Tabs --}}
            <div class="inline-flex h-10 items-center rounded-xl bg-gray-100/90 p-1 text-xs font-semibold overflow-x-auto max-w-full shrink-0">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}"
                    class="inline-flex items-center h-8 rounded-lg px-3 transition-all whitespace-nowrap {{ ($statusFilter === 'all') ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Semua ({{ $stats['total'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'menunggu_kabag', 'page' => 1]) }}"
                    class="inline-flex items-center h-8 rounded-lg px-3 transition-all whitespace-nowrap {{ ($statusFilter === 'menunggu_kabag') ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Menunggu Kabag ({{ $stats['menunggu_kabag'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}"
                    class="inline-flex items-center h-8 rounded-lg px-3 transition-all whitespace-nowrap {{ ($statusFilter === 'approved') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Disetujui ({{ $stats['approved'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected', 'page' => 1]) }}"
                    class="inline-flex items-center h-8 rounded-lg px-3 transition-all whitespace-nowrap {{ ($statusFilter === 'rejected') ? 'bg-white text-rose-600 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Ditolak ({{ $stats['rejected'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}"
                    class="inline-flex items-center h-8 rounded-lg px-3 transition-all whitespace-nowrap {{ ($statusFilter === 'cancelled') ? 'bg-white text-gray-700 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    Dibatalkan ({{ $stats['cancelled'] ?? 0 }})
                </a>
            </div>
        </div>

        {{-- Filter Pencarian Pegawai --}}
        <div class="relative w-full lg:w-72 xl:w-80 shrink-0">
            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai / NIP..."
                oninput="filterTableRows()" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-8 text-xs text-gray-700 shadow-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all placeholder:text-gray-400">
            <button type="button" id="clearSearchBtn" onclick="clearSearchInput()"
                class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                title="Hapus pencarian">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Tabel Pengajuan --}}
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm border border-gray-200">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left">
                    <th class="w-9 px-2 py-3 text-center text-xs font-semibold text-gray-700">No</th>
                    <th class="w-44 px-2.5 py-3 text-xs font-semibold text-gray-700">Nama Pegawai</th>
                    <th class="w-40 px-2.5 py-3 text-xs font-semibold text-gray-700">Tim & Ketua</th>
                    <th class="w-32 px-2 py-3 text-center text-xs font-semibold text-gray-700">
                        <div class="inline-flex items-center justify-center gap-1 w-full">
                            <span>Tanggal</span>
                            <div class="relative inline-block text-left">
                                <select id="headerSortTanggal" onchange="onHeaderSortChange(this.value)"
                                    class="appearance-none bg-white hover:bg-gray-50 rounded-md pl-1.5 pr-4 py-0.5 text-[10px] font-medium text-gray-700 cursor-pointer border border-gray-300 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-1 focus:ring-[#faa938]/40 transition-all">
                                    <option value="priority" {{ (($sort ?? 'priority') === 'priority') ? 'selected' : '' }}>Prioritas</option>
                                    <option value="desc" {{ (($sort ?? 'priority') === 'desc') ? 'selected' : '' }}>Terbaru</option>
                                    <option value="asc" {{ (($sort ?? 'priority') === 'asc') ? 'selected' : '' }}>Terlama</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-1 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th class="w-24 px-1.5 py-3 text-center text-xs font-semibold text-gray-700">Diajukan</th>
                    <th class="w-24 px-1.5 py-3 text-center text-xs font-semibold text-gray-700">Disetujui</th>
                    <th class="px-2.5 py-3 text-xs font-semibold text-gray-700 min-w-[120px]">Uraian Tugas</th>
                    <th class="w-36 px-2.5 py-3 text-xs font-semibold text-gray-700">Catatan</th>
                    <th class="w-28 px-2 py-3 text-center text-xs font-semibold text-gray-700">Status</th>
                    <th class="w-20 px-2 py-3 text-center text-xs font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100" id="tabelPengajuan">
                @forelse($pengajuan as $i => $p)
                    @php
                        $jamPengajuanMulai = $p->jam_mulai ? substr($p->jam_mulai, 0, 5) : '16:00';
                        $jamPengajuanSelesai = $p->jam_selesai ? substr($p->jam_selesai, 0, 5) : '18:00';

                        $jamMulaiDef = ($p->status === 'pending' || empty($p->jam_mulai_disetujui))
                            ? $jamPengajuanMulai
                            : substr($p->jam_mulai_disetujui, 0, 5);

                        $jamSelesaiDef = ($p->status === 'pending' || empty($p->jam_selesai_disetujui))
                            ? $jamPengajuanSelesai
                            : substr($p->jam_selesai_disetujui, 0, 5);

                        if ($p->jam_selesai && $jamSelesaiDef > $jamPengajuanSelesai) {
                            $jamSelesaiDef = $jamPengajuanSelesai;
                        }

                        $isBagianUmum = !empty($p->nama_tim) && (str_contains(strtolower($p->nama_tim), 'bagian umum') || ($p->tim_kode_tim ?? '') === 'QrBzgE3O3lEqVPjy');
                    @endphp
                    <tr class="bg-white transition-colors hover:bg-slate-50/80"
                        data-search="{{ strtolower($p->nama_pegawai . ' ' . $p->nip_pegawai . ' ' . $p->nama_tim) }}">

                        <td class="px-2 py-2.5 text-center text-xs text-gray-600 font-medium">
                            {{ $pengajuan->firstItem() + $i }}
                        </td>

                        <td class="px-2.5 py-2.5 text-xs">
                            <div class="font-semibold text-gray-900 max-w-[160px] break-words">{{ $p->nama_pegawai }}</div>
                            <div class="text-[11px] text-gray-500 font-mono mt-0.5">{{ $p->nip_pegawai }}</div>
                        </td>

                        <td class="px-2.5 py-2.5 text-xs">
                            <div class="font-medium text-indigo-900 max-w-[150px] break-words">{{ $p->nama_tim ?? '-' }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Ketua: {{ $p->nama_ketua ?? '-' }}</div>
                        </td>

                        <td class="px-2 py-2.5 text-center text-xs text-gray-800 whitespace-nowrap font-medium">
                            {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d M Y') }}
                        </td>

                        <td class="px-1.5 py-2.5 text-center text-xs text-gray-700 whitespace-nowrap font-mono">
                            {{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : '-' }}
                        </td>

                        <td class="px-1.5 py-2.5 text-center text-xs text-gray-900 whitespace-nowrap font-mono font-semibold" id="jam-disetujui-{{ $p->id_transaksi }}">
                            @if($p->jam_mulai_disetujui && $p->jam_selesai_disetujui && $p->status !== 'rejected')
                                {{ substr($p->jam_mulai_disetujui, 0, 5) }} - {{ substr($p->jam_selesai_disetujui, 0, 5) }}
                            @else
                                <span class="text-gray-400 font-normal">-</span>
                            @endif
                        </td>

                        <td class="px-2.5 py-2.5 text-xs text-gray-700">
                            <div class="max-w-[170px] break-words line-clamp-2" title="{{ $p->uraian ?? '' }}" id="uraian-display-{{ $p->id_transaksi }}">
                                {{ $p->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-2.5 py-2.5 text-xs text-gray-600">
                            <div class="space-y-1 max-w-[150px]">
                                @if(!empty($p->note))
                                    <div>
                                         <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-slate-500">Ketua Tim:</span>
                                        <div class="text-[11px] text-slate-700 break-words italic">{{ $p->note }}</div>
                                    </div>
                                @endif
                                @if(!empty($p->note_kabag))
                                    <div id="note-kabag-display-{{ $p->id_transaksi }}">
                                        <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-blue-600">Kabag Umum:</span>
                                        <div class="text-[11px] text-blue-900 break-words italic">{{ $p->note_kabag }}</div>
                                    </div>
                                @else
                                    <div id="note-kabag-display-{{ $p->id_transaksi }}"></div>
                                @endif
                                @if(empty($p->note) && empty($p->note_kabag))
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-2 py-2.5 text-center" id="status-{{ $p->id_transaksi }}">
                            @if($p->status === 'menunggu_kabag')
                                <span class="whitespace-nowrap rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 border border-blue-200">
                                    Menunggu Kabag
                                </span>
                            @elseif($p->status === 'pending')
                                <span class="whitespace-nowrap rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 border border-amber-200">
                                    Menunggu Ketua
                                </span>
                            @elseif($p->status === 'approved')
                                <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-200">
                                    Disetujui Final
                                </span>
                            @elseif($p->status === 'rejected')
                                <span class="whitespace-nowrap rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 border border-rose-200">
                                    Ditolak
                                </span>
                            @elseif($p->status === 'cancelled')
                                <span class="whitespace-nowrap rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 border border-gray-300">
                                    Dibatalkan
                                </span>
                            @endif
                        </td>

                        <td class="px-2 py-2.5 text-center" id="aksi-kabag-{{ $p->id_transaksi }}">
                            @if($p->status === 'menunggu_kabag')
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all shadow-sm bg-[#faa938] text-slate-950 hover:bg-[#fd9a10] hover:shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Proses</span>
                                </button>
                            @elseif(in_array($p->status, ['approved', 'rejected']))
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                    <span>Koreksi</span>
                                </button>
                            @elseif($p->status === 'cancelled')
                                <span class="text-gray-400 text-xs italic">Dibatalkan</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-sm text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="font-medium text-gray-600">Tidak ada data pengajuan lembur tim lain.</p>
                                <p class="text-xs text-gray-400 mt-1">Pastikan Ketua Tim sudah menyetujui lembur anggotanya terlebih dahulu.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                <tr id="noSearchMatchRow" class="hidden">
                    <td colspan="10" class="px-4 py-10 text-center text-xs text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="font-medium text-gray-600">Tidak ada pegawai yang cocok dengan pencarian.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pengajuan->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $pengajuan->links() }}
        </div>
    @endif
</div>

{{-- Modal Keputusan Kabag Umum --}}
<div id="modalKabag" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModalKabag()"></div>

    <div class="relative flex min-h-screen items-center justify-center p-3 sm:p-4 my-auto">
        <div class="relative w-full max-w-lg flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden border border-gray-100 my-auto">

            {{-- Modal Header --}}
            <div class="shrink-0 bg-gradient-to-r from-slate-900 to-indigo-950 px-4 sm:px-6 py-3 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold" id="mModalTitle">Persetujuan Akhir Kabag Umum</h3>
                    <p class="text-xs text-slate-300 mt-0.5" id="mModalSubtitle">Berikan persetujuan atau penolakan final atas lembur pegawai</p>
                </div>
                <button type="button" onclick="closeModalKabag()" class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            {{-- Modal Body (Compact & Langsung Terlihat Semua Tanpa Scroll) --}}
            <div class="p-4 sm:p-5 space-y-2.5">
                {{-- Info Ringkas Pegawai & Tim --}}
                <div class="rounded-xl bg-slate-50 p-2.5 border border-slate-200 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Pegawai: <strong class="text-gray-900 font-semibold" id="mNamaPegawai">-</strong></span>
                        <span class="text-indigo-700 font-medium" id="mNamaTim">-</span>
                    </div>
                    <div id="mWrapperNoteKetua" class="pt-1 border-t border-slate-200 hidden">
                        <span class="text-gray-500 font-semibold block text-[11px]">Catatan Ketua Tim:</span>
                        <span class="text-slate-700 italic bg-white px-2 py-1 rounded border border-slate-200 block text-xs" id="mNoteKetua">-</span>
                    </div>
                </div>

                {{-- Status Terkunci Banner (Jika status sudah approved / rejected) --}}
                <div id="mWrapperStatusLocked" class="hidden">
                    <div id="mStatusLockedBanner" class="flex items-center gap-2 rounded-xl p-2.5 border text-xs font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="mStatusLockedText">Status terkunci.</span>
                    </div>
                </div>

                {{-- 1. Jam Disetujui (2 Kolom Rapi) --}}
                <div id="mWrapperJamDisetujui" class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai Disetujui</label>
                        <input id="mJamMulai" type="time"
                            class="w-full rounded-xl border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai Disetujui</label>
                        <input id="mJamSelesai" type="time"
                            class="w-full rounded-xl border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 outline-none">
                        <p id="mJamSelesaiPresensiHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-blue-700 font-medium">
                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Maks: <strong id="mJamSelesaiPresensiVal">-</strong> (presensi)</span>
                        </p>
                    </div>
                </div>

                {{-- 2. Uraian Kegiatan --}}
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-700">Uraian Kegiatan</label>
                        <div class="flex items-center gap-1.5">
                            <span id="mUraianCount" class="text-[10px] text-gray-400">0 / 2000</span>
                            <span id="mUraianBadge" class="rounded-full px-1.5 py-0.2 text-[10px] font-medium"></span>
                        </div>
                    </div>
                    <textarea id="mUraian" rows="2" maxlength="2000"
                        class="w-full resize-y rounded-xl border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 outline-none transition-all"
                        placeholder="Uraian kegiatan lembur..."></textarea>
                    <p id="mUraianHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-amber-700">
                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="mUraianHintText"></span>
                    </p>
                </div>

                {{-- 3. Catatan Kabag Umum --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Catatan / Arahan Kabag Umum <span class="text-gray-400 font-normal" id="mNoteWajibHint">(Wajib jika menolak)</span>
                    </label>
                    <textarea id="mNoteKabag" rows="2"
                        class="w-full rounded-xl border border-gray-300 px-2.5 py-1.5 text-xs sm:text-sm focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 outline-none"
                        placeholder="Tuliskan catatan, arahan, atau alasan penolakan..."></textarea>
                </div>

                {{-- 4. Pilihan Keputusan: DI BAWAH (ALUR NATURAL SETELAH REVIEW DATA) --}}
                <div id="mWrapperPilihanKeputusan" class="space-y-1 pt-1 border-t border-gray-100">
                    <label class="block text-xs font-semibold text-gray-700">Keputusan Akhir:</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" onclick="setKeputusanKabag('approved')" id="btnPilihSetuju"
                            class="flex items-center justify-center gap-1.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 px-3 py-2 text-xs sm:text-sm font-bold text-emerald-800 transition-all hover:bg-emerald-100">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Setujui Final</span>
                        </button>

                        <button type="button" onclick="setKeputusanKabag('rejected')" id="btnPilihTolak"
                            class="flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tolak</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="shrink-0 bg-gray-50 px-4 sm:px-6 py-2.5 sm:py-3 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeModalKabag()"
                    class="rounded-xl border border-gray-300 bg-white px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" onclick="simpanKeputusanKabag()" id="btnSimpanKabag"
                    class="rounded-xl bg-[#faa938] px-4 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-bold text-slate-950 hover:bg-[#fd9a10] shadow-sm">
                    Simpan Keputusan
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Modal Presensi --}}
<div id="modalPresensi" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeModalPresensi()"></div>
    <div class="relative flex min-h-full items-center justify-center p-3 sm:p-4 my-auto">
        <div class="relative w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl border border-gray-100 my-auto">
            <div class="shrink-0 flex items-center justify-between border-b px-5 py-4 bg-slate-50">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Informasi Kehadiran Presensi</h3>
                    <p class="text-xs text-gray-500 mt-0.5" id="presensiSubtitle">-</p>
                </div>
                <button type="button" onclick="closeModalPresensi()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-5 flex-1 overflow-y-auto" id="presensiBody">
                <p class="py-4 text-center text-sm text-gray-400">Memuat data presensi...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentKabagId = null;
let selectedKeputusanKabag = 'approved';
let isStatusLocked = false;
let currentUraianKabag = '';
let currentIsBagianUmum = false;
let currentHasPresensiKabag = false;
let currentJamSelesaiPresensiKabag = '';

// =====================
// MODAL KEPUTUSAN KABAG
// =====================
window.openModalKabag = function(id, nama, tim, jamMulai, jamSelesai, noteKetua, noteKabag, currentStatus, uraian, isBagianUmum, hasPresensi, jamSelesaiPresensi) {
    currentKabagId = id;
    currentUraianKabag = (uraian !== undefined && uraian !== null) ? uraian : '';
    currentIsBagianUmum = Boolean(isBagianUmum);
    currentHasPresensiKabag = Boolean(hasPresensi);
    currentJamSelesaiPresensiKabag = (jamSelesaiPresensi !== undefined && jamSelesaiPresensi !== null) ? String(jamSelesaiPresensi).trim() : '';

    const elJamMulai = document.getElementById('mJamMulai');
    const elJamSelesai = document.getElementById('mJamSelesai');
    const elPresensiHint = document.getElementById('mJamSelesaiPresensiHint');
    const elPresensiVal = document.getElementById('mJamSelesaiPresensiVal');

    document.getElementById('mNamaPegawai').textContent = nama;
    document.getElementById('mNamaTim').textContent = tim;
    elJamMulai.value = jamMulai || '';
    elJamSelesai.value = jamSelesai || '';
    document.getElementById('mNoteKabag').value = noteKabag || '';

    if (currentJamSelesaiPresensiKabag) {
        elJamSelesai.setAttribute('max', currentJamSelesaiPresensiKabag);
        if (elPresensiHint && elPresensiVal) {
            elPresensiVal.textContent = currentJamSelesaiPresensiKabag;
            elPresensiHint.classList.remove('hidden');
        }
    } else {
        elJamSelesai.removeAttribute('max');
        if (elPresensiHint) {
            elPresensiHint.classList.add('hidden');
        }
    }

    const elUraian = document.getElementById('mUraian');
    const elUraianBadge = document.getElementById('mUraianBadge');
    const elUraianHint = document.getElementById('mUraianHint');
    const elUraianHintText = document.getElementById('mUraianHintText');

    elUraian.value = currentUraianKabag;
    const elUraianCount = document.getElementById('mUraianCount');
    if (elUraianCount) elUraianCount.textContent = `${currentUraianKabag.length} / 2000`;

    if (currentIsBagianUmum) {
        if (currentHasPresensiKabag) {
            elUraian.readOnly = false;
            elUraian.className = 'w-full resize-y rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#faa938] focus:ring-2 focus:ring-[#faa938]/20 outline-none transition-all';
            elUraianBadge.textContent = 'Tim Bagian Umum (Dapat Diedit)';
            elUraianBadge.className = 'rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 border border-emerald-200';
            elUraianHint.classList.add('hidden');
        } else {
            elUraian.readOnly = true;
            elUraian.className = 'w-full resize-y rounded-xl border border-gray-200 bg-gray-50 text-gray-500 px-3 py-2 text-sm outline-none cursor-not-allowed';
            elUraianBadge.textContent = 'Presensi Belum Ada';
            elUraianBadge.className = 'rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 border border-amber-200';
            elUraianHintText.textContent = 'Uraian tidak dapat diedit karena data presensi belum tersedia.';
            elUraianHint.classList.remove('hidden');
        }
    } else {
        elUraian.readOnly = true;
        elUraian.className = 'w-full resize-y rounded-xl border border-gray-200 bg-gray-50 text-gray-500 px-3 py-2 text-sm outline-none cursor-not-allowed';
        elUraianBadge.textContent = 'Hanya Baca (Tim Lain)';
        elUraianBadge.className = 'rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700 border border-slate-200';
        elUraianHintText.textContent = 'Uraian kegiatan tim lain hanya dapat diubah oleh Ketua Tim terkait.';
        elUraianHint.classList.remove('hidden');
    }

    const noteKetuaEl = document.getElementById('mNoteKetua');
    const wrapperKetua = document.getElementById('mWrapperNoteKetua');
    if (noteKetua && noteKetua.trim() !== '') {
        noteKetuaEl.textContent = noteKetua;
        wrapperKetua.classList.remove('hidden');
    } else {
        wrapperKetua.classList.add('hidden');
    }

    const modalTitle = document.getElementById('mModalTitle');
    const modalSubtitle = document.getElementById('mModalSubtitle');
    const wrapperPilihan = document.getElementById('mWrapperPilihanKeputusan');
    const wrapperLocked = document.getElementById('mWrapperStatusLocked');
    const bannerLocked = document.getElementById('mStatusLockedBanner');
    const lockedText = document.getElementById('mStatusLockedText');
    const wrapperJam = document.getElementById('mWrapperJamDisetujui');
    const noteWajibHint = document.getElementById('mNoteWajibHint');
    const btnSimpan = document.getElementById('btnSimpanKabag');

    if (currentStatus === 'approved') {
        isStatusLocked = true;
        selectedKeputusanKabag = 'approved';
        modalTitle.textContent = 'Koreksi Jam & Catatan';
        modalSubtitle.textContent = 'Status Disetujui Final terkunci. Anda dapat mengoreksi jam disetujui atau catatan.';
        wrapperPilihan.classList.add('hidden');
        wrapperLocked.classList.remove('hidden');
        bannerLocked.className = 'flex items-center gap-2 rounded-xl bg-emerald-50 p-3 border border-emerald-200 text-xs text-emerald-800 font-medium';
        lockedText.innerHTML = 'Status <b>Disetujui Final</b> terkunci. Anda hanya dapat mengoreksi jam disetujui dan catatan arahan.';
        wrapperJam.classList.remove('hidden');
        noteWajibHint.textContent = '(Opsional)';
        btnSimpan.textContent = 'Simpan Koreksi';
    } else if (currentStatus === 'rejected') {
        isStatusLocked = true;
        selectedKeputusanKabag = 'rejected';
        modalTitle.textContent = 'Koreksi Catatan Penolakan';
        modalSubtitle.textContent = 'Status Ditolak terkunci. Anda dapat mengoreksi catatan alasan penolakan.';
        wrapperPilihan.classList.add('hidden');
        wrapperLocked.classList.remove('hidden');
        bannerLocked.className = 'flex items-center gap-2 rounded-xl bg-rose-50 p-3 border border-rose-200 text-xs text-rose-800 font-medium';
        lockedText.innerHTML = 'Status <b>Ditolak</b> terkunci. Anda dapat mengoreksi catatan alasan penolakan.';
        wrapperJam.classList.add('hidden');
        noteWajibHint.textContent = '(Wajib)';
        btnSimpan.textContent = 'Simpan Koreksi';
    } else {
        // Status menunggu_kabag (Proses baru)
        isStatusLocked = false;
        selectedKeputusanKabag = 'approved';
        modalTitle.textContent = 'Persetujuan Akhir Kabag Umum';
        modalSubtitle.textContent = 'Berikan persetujuan atau penolakan final atas lembur pegawai';
        wrapperPilihan.classList.remove('hidden');
        wrapperLocked.classList.add('hidden');
        wrapperJam.classList.remove('hidden');
        noteWajibHint.textContent = '(Wajib jika menolak)';
        btnSimpan.textContent = 'Simpan Keputusan';
        setKeputusanKabag('approved');
    }

    document.getElementById('modalKabag').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModalKabag = function() {
    document.getElementById('modalKabag').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentKabagId = null;
    isStatusLocked = false;
    currentUraianKabag = '';
    currentIsBagianUmum = false;
    currentHasPresensiKabag = false;
    currentJamSelesaiPresensiKabag = '';
};

window.setKeputusanKabag = function(val) {
    if (isStatusLocked) return;

    selectedKeputusanKabag = val;
    const btnSetuju = document.getElementById('btnPilihSetuju');
    const btnTolak = document.getElementById('btnPilihTolak');
    const wrapperJam = document.getElementById('mWrapperJamDisetujui');

    if (val === 'approved') {
        btnSetuju.className = 'flex items-center justify-center gap-1.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 px-3 py-2 text-xs sm:text-sm font-bold text-emerald-800 transition-all shadow-xs';
        btnTolak.className = 'flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700';
        wrapperJam.classList.remove('hidden');
    } else {
        btnTolak.className = 'flex items-center justify-center gap-1.5 rounded-xl border-2 border-rose-500 bg-rose-50 px-3 py-2 text-xs sm:text-sm font-bold text-rose-700 transition-all shadow-xs';
        btnSetuju.className = 'flex items-center justify-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-600 transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700';
        wrapperJam.classList.add('hidden');
    }
};

document.getElementById('mUraian')?.addEventListener('input', function() {
    const elCount = document.getElementById('mUraianCount');
    if (elCount) elCount.textContent = `${this.value.length} / 2000`;
});

window.simpanKeputusanKabag = function() {
    if (!currentKabagId) return;

    const jamMulai = document.getElementById('mJamMulai').value;
    const jamSelesai = document.getElementById('mJamSelesai').value;
    const noteKabag = document.getElementById('mNoteKabag').value;
    const uraian = document.getElementById('mUraian').value;

    if (selectedKeputusanKabag === 'approved' && currentJamSelesaiPresensiKabag && jamSelesai > currentJamSelesaiPresensiKabag) {
        alert(`Jam selesai disetujui (${jamSelesai}) tidak boleh melebihi jam kepulangan presensi pegawai (${currentJamSelesaiPresensiKabag}).`);
        document.getElementById('mJamSelesai').focus();
        return;
    }

    if (selectedKeputusanKabag === 'rejected' && (!noteKabag || noteKabag.trim() === '')) {
        alert('Mohon tuliskan alasan penolakan pada kolom Catatan Kabag Umum.');
        document.getElementById('mNoteKabag').focus();
        return;
    }

    const btn = document.getElementById('btnSimpanKabag');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch(`/kabag-umum/pengajuan/${currentKabagId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            status: selectedKeputusanKabag,
            jam_mulai_disetujui: jamMulai,
            jam_selesai_disetujui: jamSelesai,
            note_kabag: noteKabag,
            uraian: uraian,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Gagal menyimpan keputusan.');
            return;
        }

        const finalStatus = data.status || selectedKeputusanKabag;
        const approvedMulai = data.jam_mulai_disetujui || (finalStatus !== 'rejected' && jamMulai ? jamMulai.substring(0, 5) : '');
        const approvedSelesai = data.jam_selesai_disetujui || (finalStatus !== 'rejected' && jamSelesai ? jamSelesai.substring(0, 5) : '');

        // Update status badge di tabel
        const statusEl = document.getElementById(`status-${currentKabagId}`);
        if (statusEl) {
            if (finalStatus === 'approved') {
                statusEl.innerHTML = `<span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">Disetujui Final</span>`;
            } else {
                statusEl.innerHTML = `<span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 border border-rose-200">Ditolak</span>`;
            }
        }

        // Update jam disetujui di tabel
        const jamEl = document.getElementById(`jam-disetujui-${currentKabagId}`);
        if (jamEl) {
            if (finalStatus === 'rejected' || !approvedMulai || !approvedSelesai) {
                jamEl.innerHTML = `<span class="text-gray-400 font-normal">-</span>`;
            } else {
                jamEl.textContent = `${approvedMulai} - ${approvedSelesai}`;
            }
        }

        // Update uraian di tabel jika ada
        if (data.uraian !== undefined) {
            currentUraianKabag = data.uraian;
            const uraianEl = document.getElementById(`uraian-display-${currentKabagId}`);
            if (uraianEl) {
                uraianEl.textContent = data.uraian || '-';
            }
        }

        // Update catatan Kabag di tabel
        const noteEl = document.getElementById(`note-kabag-display-${currentKabagId}`);
        if (noteEl && noteKabag.trim() !== '') {
            noteEl.innerHTML = `
                <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-blue-600">Kabag Umum:</span>
                <div class="text-[11px] text-blue-900 break-words italic">${noteKabag}</div>
            `;
        }

        // Update tombol aksi di tabel menjadi Koreksi
        const aksiEl = document.getElementById(`aksi-kabag-${currentKabagId}`);
        if (aksiEl) {
            const namaPegawai = document.getElementById('mNamaPegawai')?.textContent || '';
            const namaTim = document.getElementById('mNamaTim')?.textContent || '';
            const safeNama = JSON.stringify(namaPegawai);
            const safeTim = JSON.stringify(namaTim);
            const safeNoteKetua = JSON.stringify(document.getElementById('mNoteKetua')?.textContent || '');
            const safeNoteKabag = JSON.stringify(noteKabag);
            const safeUraian = JSON.stringify(currentUraianKabag);
            const isBagianUmumFlag = currentIsBagianUmum ? 1 : 0;
            const presensiFlag = currentHasPresensiKabag ? 1 : 0;

            aksiEl.innerHTML = `
                <button type="button"
                    onclick='openModalKabag(${currentKabagId}, ${safeNama}, ${safeTim}, "${approvedMulai}", "${approvedSelesai}", ${safeNoteKetua}, ${safeNoteKabag}, "${finalStatus}", ${safeUraian}, ${isBagianUmumFlag}, ${presensiFlag}, "${currentJamSelesaiPresensiKabag}")'
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    <span>Koreksi</span>
                </button>
            `;
        }

        closeModalKabag();
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = isStatusLocked ? 'Simpan Koreksi' : 'Simpan Keputusan';
    });
};

// =====================
// MODAL PRESENSI
// =====================
window.openModalPresensi = function(id) {
    const modal = document.getElementById('modalPresensi');
    const body = document.getElementById('presensiBody');
    const subtitle = document.getElementById('presensiSubtitle');

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    body.innerHTML = '<p class="py-6 text-center text-sm text-gray-400">Memuat data presensi...</p>';

    fetch(`/kabag-umum/pengajuan/${id}/presensi`)
        .then(r => r.json())
        .then(d => {
            subtitle.textContent = `${d.nama} (${d.nip}) — ${d.tanggal}`;
            body.innerHTML = `
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Status Kehadiran</span>
                        <span class="font-bold text-gray-900">${d.status || 'Tidak ada keterangan'}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Jam Masuk</span>
                        <span class="font-mono font-semibold text-emerald-600">${d.jam_masuk || '-'}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Jam Pulang</span>
                        <span class="font-mono font-semibold text-blue-600">${d.jam_pulang || '-'}</span>
                    </div>
                </div>
            `;
        })
        .catch(() => {
            body.innerHTML = '<p class="py-4 text-center text-sm text-red-500">Gagal memuat presensi.</p>';
        });
};

window.closeModalPresensi = function() {
    document.getElementById('modalPresensi').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

// =====================
// SORT TANGGAL DROPDOWN
// =====================
window.onHeaderSortChange = function(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

// =====================
// FILTER SEARCH PEGAWAI
// =====================
window.filterTableRows = function() {
    const input = document.getElementById('searchPegawai');
    const clearBtn = document.getElementById('clearSearchBtn');
    const query = input.value.toLowerCase().trim();

    if (clearBtn) {
        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    const rows = document.querySelectorAll('#tabelPengajuan tr[data-search]');
    let matchCount = 0;
    rows.forEach(r => {
        const text = r.getAttribute('data-search');
        if (text.includes(query)) {
            r.classList.remove('hidden');
            matchCount++;
        } else {
            r.classList.add('hidden');
        }
    });

    const noResultRow = document.getElementById('noSearchMatchRow');
    if (noResultRow) {
        if (matchCount === 0 && query.length > 0) {
            noResultRow.classList.remove('hidden');
        } else {
            noResultRow.classList.add('hidden');
        }
    }
};

window.clearSearchInput = function() {
    const input = document.getElementById('searchPegawai');
    if (input) {
        input.value = '';
        filterTableRows();
        input.focus();
    }
};

// =====================
// PERIOD PICKER LOGIC
// =====================
(function() {
    const periodBtn = document.getElementById('periodBtn');
    const periodPanel = document.getElementById('periodPanel');
    const btnClose = document.getElementById('btnClosePanel');
    const yearLabel = document.getElementById('yearLabel');
    const yearPrev = document.getElementById('yearPrev');
    const yearNext = document.getElementById('yearNext');
    const monthGrid = document.getElementById('monthGrid');
    const btnThisMonth = document.getElementById('btnThisMonth');

    let currentBulanStr = "{{ $bulan }}"; // YYYY-MM
    let [currY, currM] = currentBulanStr.split('-').map(Number);
    let viewYear = currY;

    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    function renderMonths() {
        yearLabel.textContent = viewYear;
        monthGrid.innerHTML = '';
        monthNames.forEach((name, idx) => {
            const m = idx + 1;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = name;
            const isSelected = (viewYear === currY && m === currM);
            btn.className = `py-2 rounded-lg text-xs font-semibold transition-colors ${isSelected ? 'bg-[#faa938] text-slate-950 shadow-sm' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'}`;
            btn.onclick = () => {
                const targetM = String(m).padStart(2, '0');
                const url = new URL(window.location.href);
                url.searchParams.set('bulan', `${viewYear}-${targetM}`);
                window.location.href = url.toString();
            };
            monthGrid.appendChild(btn);
        });
    }

    if (periodBtn && periodPanel) {
        periodBtn.onclick = (e) => {
            e.stopPropagation();
            periodPanel.classList.toggle('hidden');
            renderMonths();
        };

        if (btnClose) btnClose.onclick = () => periodPanel.classList.add('hidden');
        if (yearPrev) yearPrev.onclick = () => { viewYear--; renderMonths(); };
        if (yearNext) yearNext.onclick = () => { viewYear++; renderMonths(); };

        if (btnThisMonth) {
            btnThisMonth.onclick = () => {
                const now = new Date();
                const target = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2, '0')}`;
                const url = new URL(window.location.href);
                url.searchParams.set('bulan', target);
                window.location.href = url.toString();
            };
        }

        document.addEventListener('click', (e) => {
            if (!periodPanel.contains(e.target) && !periodBtn.contains(e.target)) {
                periodPanel.classList.add('hidden');
            }
        });
    }
})();
</script>
@endpush

@endsection

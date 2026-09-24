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
                        $jamMulaiDef = $p->jam_mulai_disetujui ? substr($p->jam_mulai_disetujui, 0, 5) : ($p->jam_mulai ? substr($p->jam_mulai, 0, 5) : '16:00');
                        $jamSelesaiDef = $p->jam_selesai_disetujui ? substr($p->jam_selesai_disetujui, 0, 5) : ($p->jam_selesai ? substr($p->jam_selesai, 0, 5) : '18:00');
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
                            @if($p->jam_mulai_disetujui && $p->jam_selesai_disetujui)
                                {{ substr($p->jam_mulai_disetujui, 0, 5) }} - {{ substr($p->jam_selesai_disetujui, 0, 5) }}
                            @else
                                <span class="text-gray-400 font-normal">-</span>
                            @endif
                        </td>

                        <td class="px-2.5 py-2.5 text-xs text-gray-700">
                            <div class="max-w-[170px] break-words line-clamp-2" title="{{ $p->uraian ?? '' }}">
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
                            @endif
                        </td>

                        <td class="px-2 py-2.5 text-center">
                            @if($p->status === 'menunggu_kabag')
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', '{{ addslashes($p->note ?? '') }}', '{{ addslashes($p->note_kabag ?? '') }}', '{{ $p->status }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all shadow-sm bg-[#faa938] text-slate-950 hover:bg-[#fd9a10] hover:shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Proses</span>
                                </button>
                            @elseif(in_array($p->status, ['approved', 'rejected']))
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', '{{ addslashes($p->note ?? '') }}', '{{ addslashes($p->note_kabag ?? '') }}', '{{ $p->status }}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                    <span>Koreksi</span>
                                </button>
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
<div id="modalKabag" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModalKabag()"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden border border-gray-100">

            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-4 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold" id="mModalTitle">Persetujuan Akhir Kabag Umum</h3>
                    <p class="text-xs text-slate-300 mt-0.5" id="mModalSubtitle">Berikan persetujuan atau penolakan final atas lembur pegawai</p>
                </div>
                <button type="button" onclick="closeModalKabag()" class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-4">
                {{-- Info Ringkas --}}
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nama Pegawai:</span>
                        <span class="font-semibold text-gray-900" id="mNamaPegawai">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tim Kerja:</span>
                        <span class="font-medium text-indigo-700" id="mNamaTim">-</span>
                    </div>
                    <div id="mWrapperNoteKetua" class="pt-1 border-t border-slate-200">
                        <span class="text-gray-500 font-semibold block mb-0.5">Catatan Ketua Tim:</span>
                        <span class="text-slate-700 italic bg-white p-2 rounded-lg border border-slate-200 block" id="mNoteKetua">-</span>
                    </div>
                </div>

                {{-- Status Terkunci Banner (Jika status sudah approved / rejected) --}}
                <div id="mWrapperStatusLocked" class="hidden">
                    <div id="mStatusLockedBanner" class="flex items-center gap-2 rounded-xl p-3 border text-xs font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="mStatusLockedText">Status terkunci.</span>
                    </div>
                </div>

                {{-- Jam Disetujui --}}
                <div id="mWrapperJamDisetujui" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai Disetujui</label>
                        <input id="mJamMulai" type="time"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-[#faa938] focus:ring-2 focus:ring-[#faa938]/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai Disetujui</label>
                        <input id="mJamSelesai" type="time"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-[#faa938] focus:ring-2 focus:ring-[#faa938]/20 outline-none">
                    </div>
                </div>

                {{-- Catatan Kabag Umum --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Catatan / Arahan Kabag Umum <span class="text-gray-400 font-normal" id="mNoteWajibHint">(Wajib jika menolak)</span>
                    </label>
                    <textarea id="mNoteKabag" rows="3"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-[#faa938] focus:ring-2 focus:ring-[#faa938]/20 outline-none"
                        placeholder="Tuliskan catatan, arahan, atau alasan penolakan..."></textarea>
                </div>

                {{-- Pilihan Keputusan (Hanya tampil saat status menunggu_kabag) --}}
                <div id="mWrapperPilihanKeputusan">
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Keputusan Akhir</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="setKeputusanKabag('approved')" id="btnPilihSetuju"
                            class="flex items-center justify-center gap-2 rounded-xl border-2 border-emerald-300 bg-emerald-50/50 p-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Setujui Final
                        </button>

                        <button type="button" onclick="setKeputusanKabag('rejected')" id="btnPilihTolak"
                            class="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-3 text-sm font-semibold text-gray-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Tolak
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" onclick="closeModalKabag()"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" onclick="simpanKeputusanKabag()" id="btnSimpanKabag"
                    class="rounded-xl bg-[#faa938] px-5 py-2 text-sm font-bold text-slate-950 hover:bg-[#fd9a10] shadow-sm">
                    Simpan Keputusan
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Modal Presensi --}}
<div id="modalPresensi" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModalPresensi()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between border-b px-5 py-4 bg-slate-50">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Informasi Kehadiran Presensi</h3>
                    <p class="text-xs text-gray-500 mt-0.5" id="presensiSubtitle">-</p>
                </div>
                <button type="button" onclick="closeModalPresensi()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-5" id="presensiBody">
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

// =====================
// MODAL KEPUTUSAN KABAG
// =====================
window.openModalKabag = function(id, nama, tim, jamMulai, jamSelesai, noteKetua, noteKabag, currentStatus) {
    currentKabagId = id;

    document.getElementById('mNamaPegawai').textContent = nama;
    document.getElementById('mNamaTim').textContent = tim;
    document.getElementById('mJamMulai').value = jamMulai || '';
    document.getElementById('mJamSelesai').value = jamSelesai || '';
    document.getElementById('mNoteKabag').value = noteKabag || '';

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
};

window.setKeputusanKabag = function(val) {
    if (isStatusLocked) return;

    selectedKeputusanKabag = val;
    const btnSetuju = document.getElementById('btnPilihSetuju');
    const btnTolak = document.getElementById('btnPilihTolak');
    const wrapperJam = document.getElementById('mWrapperJamDisetujui');

    if (val === 'approved') {
        btnSetuju.className = 'flex items-center justify-center gap-2 rounded-xl border-2 border-emerald-500 bg-emerald-100 p-3 text-sm font-bold text-emerald-800 shadow-sm transition-all';
        btnTolak.className = 'flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-3 text-sm font-semibold text-gray-500 hover:border-rose-300 hover:bg-rose-50 transition-all';
        wrapperJam.classList.remove('hidden');
    } else {
        btnTolak.className = 'flex items-center justify-center gap-2 rounded-xl border-2 border-rose-500 bg-rose-100 p-3 text-sm font-bold text-rose-800 shadow-sm transition-all';
        btnSetuju.className = 'flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white p-3 text-sm font-semibold text-gray-500 hover:border-emerald-300 hover:bg-emerald-50 transition-all';
        wrapperJam.classList.add('hidden');
    }
};

window.simpanKeputusanKabag = function() {
    if (!currentKabagId) return;

    const jamMulai = document.getElementById('mJamMulai').value;
    const jamSelesai = document.getElementById('mJamSelesai').value;
    const noteKabag = document.getElementById('mNoteKabag').value;

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
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Gagal menyimpan keputusan.');
            return;
        }

        // Update status badge di tabel
        const statusEl = document.getElementById(`status-${currentKabagId}`);
        if (statusEl) {
            if (selectedKeputusanKabag === 'approved') {
                statusEl.innerHTML = `<span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">Disetujui Final</span>`;
            } else {
                statusEl.innerHTML = `<span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 border border-rose-200">Ditolak</span>`;
            }
        }

        // Update jam disetujui
        const jamEl = document.getElementById(`jam-disetujui-${currentKabagId}`);
        if (jamEl && jamMulai && jamSelesai) {
            jamEl.textContent = `${jamMulai.substring(0, 5)} - ${jamSelesai.substring(0, 5)}`;
        }

        // Update catatan Kabag di tabel
        const noteEl = document.getElementById(`note-kabag-display-${currentKabagId}`);
        if (noteEl && noteKabag.trim() !== '') {
            noteEl.innerHTML = `
                <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-blue-600">Kabag Umum:</span>
                <div class="text-[11px] text-blue-900 break-words italic">${noteKabag}</div>
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
        btn.textContent = 'Simpan Keputusan';
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

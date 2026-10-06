@extends('layouts.app')

@section('title', 'Daftar Pengajuan Lembur')

@section('content')

<div class="w-full max-w-7xl mx-auto flex flex-col px-4 sm:px-6 lg:px-8">

    {{-- ===================== TOOLBAR ===================== --}}
    @php
        $currentStatus = $status ?? 'all';
        $statusMap = [
            'all' => [
                'label' => 'Semua Status',
                'dot' => 'bg-slate-400',
            ],
            'menunggu_kabag' => [
                'label' => 'Menunggu Kabag',
                'dot' => 'bg-blue-600',
            ],
            'pending' => [
                'label' => 'Menunggu Ketua',
                'dot' => 'bg-amber-500',
            ],
            'approved' => [
                'label' => 'Disetujui Final',
                'dot' => 'bg-emerald-500',
            ],
            'rejected' => [
                'label' => 'Ditolak',
                'dot' => 'bg-rose-500',
            ],
        ];
        $activeStatusConfig = $statusMap[$currentStatus] ?? $statusMap['all'];
        $hasActiveFilter = ($bulan !== 'all' && !empty($bulan)) || !empty($search) || ($currentStatus !== 'all' && !empty($currentStatus));
    @endphp

    <div class="w-full my-4 sm:my-5">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2.5 sm:gap-3">

            {{-- Baris 1 Mobile: Grid 2 Kolom (Bulan 50% & Status 50%) | Desktop: Item Langsung --}}
            <div class="grid grid-cols-2 gap-2 sm:contents">
                {{-- Period Picker --}}
                <div class="relative w-full sm:w-auto shrink-0 sm:order-1" id="periodPicker">
                    <button type="button" id="periodBtn"
                        class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-1.5 sm:gap-2 px-3 sm:px-4 text-xs sm:text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors cursor-pointer select-none">

                        <span class="inline-flex items-center gap-1.5 sm:gap-2 min-w-0 truncate pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-3.5 h-3.5 fill-current text-[#faa938] shrink-0">
                                <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                            </svg>

                            <span id="periodLabel" class="leading-none truncate text-xs sm:text-sm font-medium text-gray-800">
                                @if(empty($selectedMonth))
                                    Semua Bulan {{ $selectedYear }}
                                @else
                                    {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('M Y') }}
                                @endif
                            </span>
                        </span>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-2.5 h-2.5 fill-current text-gray-400 shrink-0 pointer-events-none">
                            <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                        </svg>
                    </button>

                    <input type="hidden" id="periodValue" name="period" value="{{ $bulan }}">

                    <div id="periodPanel"
                        class="hidden absolute z-50 mt-1.5 left-0 w-72 max-w-[calc(100vw-2rem)] rounded-2xl border border-gray-200 bg-white shadow-xl p-3.5">

                        <div class="flex items-center justify-between mb-3">
                            <button type="button" id="yearPrev"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                                </svg>
                            </button>

                            <span id="yearLabel" class="text-sm font-semibold text-gray-900">{{ $selectedYear }}</span>

                            <button type="button" id="yearNext"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="mb-2.5">
                            <button type="button" id="btnAllMonthsOfYearAdmin"
                                class="w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ empty($selectedMonth) ? 'bg-[#faa938] text-white border-[#faa938]' : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]' }}">
                                Semua Bulan (<span id="allMonthsYearLabelAdmin">{{ $selectedYear }}</span>)
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                            <button type="button" id="btnThisMonth"
                                class="text-xs font-semibold text-gray-500 hover:text-[#faa938] transition-colors cursor-pointer">
                                Bulan ini
                            </button>

                            <button type="button" id="btnClosePanel"
                                class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Custom Filter Status Dropdown --}}
                <div class="relative w-full sm:w-52 shrink-0 sm:order-3" id="wrapStatusFilter">
                    <button type="button" id="btnStatusDropdown" onclick="toggleStatusDropdown(event)"
                        class="inline-flex w-full h-10 items-center justify-between gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs sm:text-sm font-medium text-gray-700 shadow-2xs hover:border-[#faa938] active:bg-gray-50 transition-all cursor-pointer select-none">
                        <span class="inline-flex items-center gap-1.5 sm:gap-2 min-w-0 truncate pointer-events-none">
                            @if($currentStatus === 'menunggu_kabag')
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                                </span>
                            @else
                                <span class="h-2 w-2 rounded-full {{ $activeStatusConfig['dot'] }} shrink-0"></span>
                            @endif
                            <span class="truncate font-medium text-gray-800">{{ $activeStatusConfig['label'] }}</span>
                        </span>

                        <svg id="iconChevronStatus" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-2.5 w-2.5 fill-current text-gray-400 shrink-0 transition-transform duration-200 pointer-events-none">
                            <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                        </svg>
                    </button>

                    <input type="hidden" id="filterStatus" value="{{ $currentStatus }}">

                    {{-- Menu Dropdown Status Custom --}}
                    <div id="menuStatusDropdown"
                        class="hidden absolute right-0 sm:left-0 sm:right-auto top-full mt-1.5 z-50 w-56 sm:w-60 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden py-1 divide-y divide-gray-50/80">
                        @foreach($statusMap as $val => $cfg)
                            @php $isSelected = ($currentStatus === $val); @endphp
                            <button type="button" onclick="selectStatusOption('{{ $val }}')"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs sm:text-sm text-left transition-colors cursor-pointer group {{ $isSelected ? 'bg-amber-50/70 font-semibold text-amber-900' : 'text-gray-700 hover:bg-gray-50' }}">
                                <span class="inline-flex items-center gap-2.5 pointer-events-none">
                                    @if($val === 'menunggu_kabag')
                                        <span class="relative flex h-2 w-2 shrink-0">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                                        </span>
                                    @else
                                        <span class="h-2 w-2 rounded-full {{ $cfg['dot'] }} shrink-0"></span>
                                    @endif
                                    <span class="{{ $isSelected ? 'font-semibold text-gray-900' : 'text-gray-700 group-hover:text-gray-900' }}">
                                        {{ $cfg['label'] }}
                                    </span>
                                </span>

                                @if($isSelected)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Baris 2 Mobile: Search Pegawai + Tombol Hari Libur | Desktop: Item Langsung --}}
            <div class="flex items-center gap-2 sm:contents">
                {{-- Filter Pegawai --}}
                <div class="relative flex-1 sm:min-w-[240px] sm:order-2" id="wrapSearchPegawai">
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                        onclick="openDropdownPegawai()" onfocus="openDropdownPegawai()" oninput="filterDropdownPegawai()" autocomplete="off"
                        class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-9 pr-10 text-xs sm:text-sm text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all placeholder:text-gray-400"/>

                    <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                        <button type="button" id="btnClearPegawai" onclick="pilihPegawai(null)" class="hidden p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100 transition-colors" title="Hapus filter pegawai">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <button type="button" onclick="toggleDropdownPegawai()" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none p-0.5" title="Buka daftar pegawai">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-2.5 w-2.5 fill-current">
                                <path d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                            </svg>
                        </button>
                    </div>

                    <div id="dropdownPegawai"
                        class="hidden absolute z-40 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                        <ul id="listPegawai"></ul>
                    </div>
                </div>

                {{-- Tombol Hari Libur --}}
                <button type="button" onclick="openModalHariLibur()"
                    class="h-10 w-10 shrink-0 inline-flex items-center justify-center bg-[#faa938] text-white rounded-xl hover:bg-[#fd9a10] active:scale-95 transition-all shadow-2xs sm:order-6"
                    title="Kelola Hari Libur">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>

            {{-- Reset Filter (hanya muncul jika ada filter yang aktif) --}}
            @if($hasActiveFilter)
                <a href="{{ url()->current() }}"
                    class="h-10 w-full sm:w-auto px-3.5 inline-flex items-center justify-center gap-1.5 text-xs sm:text-sm font-medium border border-rose-200 bg-rose-50/70 text-rose-600 rounded-xl hover:bg-rose-100 hover:border-rose-300 transition-colors sm:order-4 cursor-pointer"
                    title="Reset semua filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Filter</span>
                </a>
            @endif

            <div class="hidden sm:block flex-1 sm:order-5"></div>
        </div>
    </div>

    {{-- Petunjuk Geser & Scrollbar di Layar HP (Mobile) --}}
    <div class="sm:hidden table-scroll-hint flex flex-col gap-1.5 px-1 mb-2.5 text-[11px] font-medium text-slate-500">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-[#faa938] shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Geser tabel ke kiri / kanan</span>
            </span>
            <span class="table-scroll-pct text-[10px] font-mono text-slate-400 shrink-0">Geser »</span>
        </div>
        <div class="table-scroll-track w-full h-1.5 bg-slate-200/90 rounded-full overflow-hidden cursor-pointer relative">
            <div class="table-scroll-thumb absolute top-0 left-0 h-full bg-[#faa938] rounded-full" style="width: 30%; transform: translateX(0px);"></div>
        </div>
    </div>

    {{-- ===================== TABEL ===================== --}}
    <div class="w-full overflow-x-auto rounded-xl ring-1 ring-gray-200 bg-white">
        <table class="min-w-[980px] lg:min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 rounded-tl-xl w-8">No</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Nama Pegawai</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Tim & Ketua</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">
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
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Jam Diajukan</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Jam Disetujui</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Uraian Kegiatan</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Data Presensi</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 rounded-tr-xl">Keputusan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200" id="tabelPengajuan">
                @forelse($pengajuan as $i => $p)
                    <tr class="bg-white transition-all duration-500 hover:bg-gray-50"
                        data-nama="{{ strtolower($p->nama_pegawai) }}"
                        data-nip="{{ $p->nip_pegawai }}"
                        data-tanggal="{{ $p->date }}">

                        <td class="px-2 py-2 text-xs text-gray-900 text-center">
                            {{ $pengajuan->firstItem() + $i }}
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900">
                            <div class="font-medium">{{ $p->nama_pegawai }}</div>
                            <div class="text-xs text-slate-500">{{ $p->nip_pegawai }}</div>
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900 text-left">
                            <div class="font-medium text-gray-900 max-w-[150px] break-words">{{ $p->nama_tim ?? '-' }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Ketua: {{ $p->nama_ketua ?? '-' }}</div>
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900 text-left whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d F Y') }}
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900 text-center whitespace-nowrap">
                            {{ $p->jam_mulai ? substr($p->jam_mulai,0,5).' - '.substr($p->jam_selesai,0,5) : '-' }}
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900 text-center whitespace-nowrap" id="jam-disetujui-{{ $p->id_transaksi }}">
                            @if($p->jam_mulai_disetujui && $p->jam_selesai_disetujui)
                                {{ substr($p->jam_mulai_disetujui,0,5) }} - {{ substr($p->jam_selesai_disetujui,0,5) }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-2 py-2 text-xs text-gray-900">
                            <div class="max-w-[260px]" id="uraian-display-{{ $p->id_transaksi }}">
                                {{ $p->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-2 py-2 text-center">
                            <button type="button" onclick="openModalPresensi({{ $p->id_transaksi }})"
                                class="inline-flex items-center justify-center gap-1 text-xs font-medium cursor-pointer
                                {{ $p->has_presensi ? 'text-green-600 underline' : 'text-slate-500' }}">
                                {{ $p->has_presensi ? 'Informasi tersedia' : 'Tidak ada informasi' }}

                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </button>
                        </td>

                        <td class="px-2 py-2 text-center" id="status-{{ $p->id_transaksi }}">
                            @if($p->status === 'approved')
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <span class="bg-green-100 rounded-full px-2 text-xs text-green-700 py-0.5">Disetujui</span>
                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai_disetujui ?? $p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai_disetujui ?? $p->jam_selesai,0,5) }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', 'approved', '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}')"
                                        class="text-slate-500 cursor-pointer hover:text-slate-800" title="Koreksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            @elseif($p->status === 'menunggu_kabag')
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <span class="bg-blue-100 rounded-full px-2 text-xs text-blue-700 py-0.5">Menunggu Kabag</span>
                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai_disetujui ?? $p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai_disetujui ?? $p->jam_selesai,0,5) }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', 'approved', '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}')"
                                        class="text-blue-600 cursor-pointer hover:text-blue-800" title="Proses / Setujui">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            @elseif($p->status === 'rejected')
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <span class="bg-red-100 rounded-full px-2 text-xs text-red-700 py-0.5">Ditolak</span>
                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai_disetujui ?? $p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai_disetujui ?? $p->jam_selesai,0,5) }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', 'rejected', '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}')"
                                        class="text-slate-500 cursor-pointer hover:text-slate-800" title="Lihat / Koreksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            @elseif($p->status === 'cancelled')
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <span class="bg-gray-100 rounded-full px-2 text-xs text-gray-700 py-0.5 border border-gray-300">Dibatalkan</span>
                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai_disetujui ?? $p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai_disetujui ?? $p->jam_selesai,0,5) }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', 'cancelled', '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}')"
                                        class="text-slate-500 cursor-pointer hover:text-slate-800" title="Lihat / Ubah Keputusan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-2">
                                    <span class="bg-amber-100 rounded-full px-2 text-xs text-amber-800 py-0.5 font-medium">Menunggu Ketua</span>

                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->uraian ?? '') }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', 'pending')"
                                        class="text-amber-600 cursor-pointer hover:text-amber-800" title="Proses">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500 font-medium">
                            Tidak ada data pengajuan lembur yang sesuai dengan filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===================== PAGINATION ===================== --}}
    @if($pengajuan->hasPages())
        <div class="flex justify-center mt-6 px-4">
            <nav class="inline-flex items-center p-1 rounded bg-white space-x-2">
                @if($pengajuan->onFirstPage())
                    <span class="p-1 rounded border text-gray-300 cursor-not-allowed">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="p-1 rounded border hover:bg-[#faa938] hover:text-white hover:border-[#faa938]"
                        href="{{ $pengajuan->previousPageUrl() }}">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                    Page {{ $pengajuan->currentPage() }} of {{ $pengajuan->lastPage() }}
                </p>

                @if($pengajuan->hasMorePages())
                    <a class="p-1 rounded border hover:bg-[#faa938] hover:text-white hover:border-[#faa938]"
                        href="{{ $pengajuan->nextPageUrl() }}">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="p-1 rounded border text-gray-300 cursor-not-allowed">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                @endif
            </nav>
        </div>
    @endif

</div>

{{-- ===================== MODAL PRESENSI ===================== --}}
<div id="modalPresensi" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalPresensi()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto sm:overflow-hidden">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="h-1 w-10 rounded-full bg-gray-200"></div>
            </div>

            <div class="sticky sm:static top-0 z-10 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-4 rounded-t-2xl">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Informasi Presensi</h2>
                    <p class="text-xs text-gray-400 mt-0.5" id="presensiSubtitle">-</p>
                </div>

                <button type="button" onclick="closeModalPresensi()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                    &times;
                </button>
            </div>

            <div class="px-4 sm:px-6 py-5 space-y-5" id="presensiBody">
                <p class="text-sm text-gray-400 text-center py-4">Memuat data...</p>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL KEPUTUSAN ===================== --}}
<div id="modalKeputusan" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" onclick="closeModalKeputusan()"></div>

    <div class="relative flex min-h-screen items-center justify-center p-3 sm:p-4 my-auto">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden my-auto border border-gray-100">

            <div class="shrink-0 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Keputusan Lembur</h2>
                    <p class="text-xs text-gray-500">Pilih keputusan persetujuan, penolakan, atau pembatalan lembur.</p>
                </div>

                <button type="button" onclick="closeModalKeputusan()"
                    class="text-gray-400 hover:text-gray-700 text-xl leading-none p-1">
                    &times;
                </button>
            </div>

            <div class="p-4 sm:p-5 space-y-3">
                <div id="warningDurasi"
                    class="hidden px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
                    ⚠️ <span id="warningText"></span>
                </div>

                {{-- 1. Jam Disetujui (2 Kolom Rapi) --}}
                <div id="wrapperJamDisetujui" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai Disetujui</label>
                        <input id="kJamMulai" type="time"
                            class="border rounded-lg px-2.5 py-1.5 text-xs sm:text-sm w-full outline-none border-gray-300 focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai Disetujui</label>
                        <input id="kJamSelesai" type="time"
                            class="border rounded-lg px-2.5 py-1.5 text-xs sm:text-sm w-full outline-none border-gray-300 focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30" />
                        <p id="kJamSelesaiPresensiHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-blue-700 font-medium">
                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Maks: <strong id="kJamSelesaiPresensiVal">-</strong> (presensi)</span>
                        </p>
                    </div>
                </div>

                {{-- 2. Uraian Kegiatan --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700">
                            Uraian Kegiatan
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span id="kUraianCount" class="text-[10px] text-gray-400">0 / 2000</span>
                            <span id="kUraianBadge" class="rounded-full px-1.5 py-0.2 text-[10px] font-medium"></span>
                        </div>
                    </div>
                    <textarea id="kUraian" rows="2" maxlength="2000"
                        class="border rounded-lg px-2.5 py-1.5 text-xs sm:text-sm w-full outline-none border-gray-300 focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 resize-y transition-all"
                        placeholder="Uraian kegiatan lembur..."></textarea>
                    <p id="kUraianLockedHint" class="mt-1 hidden flex items-center gap-1 text-[11px] text-amber-700">
                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Uraian tidak dapat diedit karena data presensi belum tersedia.</span>
                    </p>
                </div>

                {{-- 3. Catatan --}}
                <div>
                    <label id="labelCatatan" class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
                    <textarea id="kCatatan" rows="2"
                        class="border rounded-lg px-2.5 py-1.5 text-xs sm:text-sm w-full outline-none border-gray-300 focus:border-[#faa938] focus:ring-1 focus:ring-[#faa938]/30 resize-none"
                        placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                {{-- 4. Pilihan Keputusan: DI BAWAH (ALUR NATURAL SETELAH REVIEW DATA) --}}
                <div class="space-y-1 pt-1 border-t border-gray-100">
                    <label class="block text-xs font-semibold text-gray-700">Pilihan Keputusan:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setKeputusan('approved')" id="kBtnSetujui"
                            class="px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 transition-all flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Setujui</span>
                        </button>

                        <button type="button" onclick="setKeputusan('rejected')" id="kBtnTolak"
                            class="px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600 transition-all flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Tolak</span>
                        </button>

                        <button type="button" onclick="setKeputusan('cancelled')" id="kBtnBatal"
                            class="px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-amber-50 hover:border-amber-400 hover:text-amber-800 transition-all flex items-center justify-center gap-1"
                            title="Batalkan pengajuan (duplikat / salah tanggal)">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Batalkan</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="shrink-0 flex items-center justify-end gap-2 px-4 sm:px-6 py-2.5 sm:py-3 border-t bg-gray-50">
                <button type="button" onclick="closeModalKeputusan()"
                    class="rounded-lg border border-gray-300 px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-100 bg-white">
                    Batal
                </button>

                <button type="button" onclick="simpanKeputusan()" id="btnSimpan"
                    class="rounded-lg bg-[#faa938] px-4 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-bold text-slate-950 transition-all hover:bg-[#fd9a10] shadow-sm">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL HARI LIBUR ===================== --}}
<div id="modalHariLibur" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalHariLibur()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="h-1 w-10 rounded-full bg-gray-200"></div>
            </div>

            <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-4 rounded-t-2xl">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Kelola Hari Libur</h2>

                <button type="button" onclick="closeModalHariLibur()"
                    class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            {{-- Kalender Range Picker --}}
            <div class="px-4 sm:px-6 py-4 border-b">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" id="hlPrev"
                        class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <span id="hlNavLabel" class="text-sm font-medium text-gray-900"></span>

                    <button type="button" id="hlNext"
                        class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                <div id="hlGrid"></div>

                <div id="hlRangeInfo" class="hidden mt-3 text-xs text-gray-500 text-center"></div>

                <form id="formHariLibur" method="POST" action="{{ route('admin.hari-libur.store') }}"
                    class="mt-3 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-end">
                    @csrf

                    <input type="hidden" name="tanggal_mulai" id="hlInputMulai" />
                    <input type="hidden" name="tanggal_selesai" id="hlInputSelesai" />

                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="hlKeterangan"
                            placeholder="contoh: Cuti Bersama Idul Fitri"
                            class="border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-300 focus:ring-1 focus:ring-[#faa938]" />
                    </div>

                    <button type="button" onclick="submitHariLibur()"
                        class="h-10 px-4 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white transition-all shrink-0 disabled:opacity-40 w-full sm:w-auto"
                        id="hlBtnTambah" disabled>
                        Tambah
                    </button>
                </form>
            </div>

            {{-- Daftar Hari Libur --}}
            <div class="px-4 sm:px-6 py-4 max-h-60 overflow-y-auto space-y-2">
                @php
                    $grouped = $hariLibur->groupBy('grup_id');
                @endphp

                @forelse($grouped as $grupId => $items)
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 last:border-0">
                        <div class="min-w-0">
                            @if($items->count() > 1)
                                <div class="text-sm text-gray-800 truncate">
                                    {{ \Carbon\Carbon::parse($items->first()->tanggal)->translatedFormat('d F Y') }}
                                    —
                                    {{ \Carbon\Carbon::parse($items->last()->tanggal)->translatedFormat('d F Y') }}
                                </div>
                            @else
                                <div class="text-sm text-gray-800 truncate">
                                    {{ \Carbon\Carbon::parse($items->first()->tanggal)->translatedFormat('d F Y') }}
                                </div>
                            @endif

                            <div class="text-xs text-gray-400 truncate">
                                {{ $items->first()->keterangan ?? '-' }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.hari-libur.destroy', $items->first()->id) }}" class="shrink-0">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">
                        Belum ada hari libur yang ditambahkan.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// =====================
// HELPER
// =====================
function makeBtn(text, cls, onClick) {
    const b = document.createElement('button');
    b.type = 'button';
    b.textContent = text;
    b.className = cls;
    b.addEventListener('click', (e) => {
        e.stopPropagation();
        onClick();
    });
    return b;
}

// =====================
// STATE FILTER
// =====================
const urlParams = new URLSearchParams(window.location.search);
const activeNip = urlParams.get('nip') || '';
const activeStatus = urlParams.get('status') || '';
const activeSort = urlParams.get('sort') || '';
const activeBulan = urlParams.get('bulan') || '';
let cachedPegawai = [];

// =====================
// FETCH SEMUA PEGAWAI
// =====================
fetch('/admin/pengajuan/pegawai')
    .then(r => r.json())
    .then(data => {
        cachedPegawai = Array.isArray(data) ? data : [];

        if (activeNip) {
            const emp = cachedPegawai.find(e => e.nip === activeNip);

            if (emp) {
                document.getElementById('searchPegawai').value = `${emp.nama} (${emp.nip})`;
                document.getElementById('btnClearPegawai')?.classList.remove('hidden');
            }
        }

        updateResetBtn();
        renderDropdownPegawai('');
    });

function renderDropdownPegawai(filter = '') {
    const list = document.getElementById('listPegawai');
    if (!list) return;
    list.innerHTML = '';

    const liSemua = document.createElement('li');
    liSemua.className = 'cursor-pointer px-4 py-2.5 text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-between ' + (!activeNip ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
    liSemua.innerHTML = '<span>Semua pegawai</span>' + (!activeNip ? '<svg class="w-4 h-4 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
    liSemua.onclick = () => pilihPegawai(null);
    list.appendChild(liSemua);

    const keyword = (filter || '').toLowerCase().trim();
    const filtered = cachedPegawai.filter(emp => {
        if (!keyword) return true;
        return `${emp.nama ?? ''} ${emp.nip ?? ''}`.toLowerCase().includes(keyword);
    });

    if (filtered.length === 0) {
        const liEmpty = document.createElement('li');
        liEmpty.className = 'px-4 py-3 text-xs text-gray-400 text-center';
        liEmpty.textContent = 'Pegawai tidak ditemukan';
        list.appendChild(liEmpty);
        return;
    }

    filtered.forEach(emp => {
        const isSelected = activeNip === emp.nip;
        const li = document.createElement('li');
        li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between ' + (isSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
        li.innerHTML = `<span>${emp.nama} (${emp.nip})</span>` + (isSelected ? '<svg class="w-4 h-4 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
        li.onclick = () => pilihPegawai(emp);
        list.appendChild(li);
    });
}

window.openDropdownPegawai = function () {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');
    if (!dropdown || !search) return;

    const menuStatus = document.getElementById('menuStatusDropdown');
    if (menuStatus) menuStatus.classList.add('hidden');
    const chevronStatus = document.getElementById('iconChevronStatus');
    if (chevronStatus) chevronStatus.classList.remove('rotate-180');

    renderDropdownPegawai(''); // Always render full list when opened
    dropdown.classList.remove('hidden');
    setTimeout(() => search.select(), 10);
};

window.toggleDropdownPegawai = function () {
    const dropdown = document.getElementById('dropdownPegawai');
    if (!dropdown) return;

    if (dropdown.classList.contains('hidden')) {
        openDropdownPegawai();
    } else {
        dropdown.classList.add('hidden');
    }
};

window.filterDropdownPegawai = function () {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');
    if (!dropdown || !search) return;

    renderDropdownPegawai(search.value);
    dropdown.classList.remove('hidden');

    const btnClear = document.getElementById('btnClearPegawai');
    if (btnClear) {
        search.value.trim() ? btnClear.classList.remove('hidden') : (activeNip ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden'));
    }
};

// Close dropdown when clicked outside and restore active value if untouched
document.addEventListener('click', (e) => {
    const wrap = document.getElementById('wrapSearchPegawai');
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');
    if (wrap && dropdown && search && !wrap.contains(e.target)) {
        dropdown.classList.add('hidden');
        if (activeNip) {
            const emp = cachedPegawai.find(emp => emp.nip === activeNip);
            if (emp) search.value = `${emp.nama} (${emp.nip})`;
        } else {
            search.value = '';
        }
    }
});

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
window.toggleStatusDropdown = function(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('menuStatusDropdown');
    const chevron = document.getElementById('iconChevronStatus');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    // Tutup panel lain jika terbuka
    const periodPanel = document.getElementById('periodPanel');
    if (periodPanel) periodPanel.classList.add('hidden');
    const dropdownPegawai = document.getElementById('dropdownPegawai');
    if (dropdownPegawai) dropdownPegawai.classList.add('hidden');

    if (isHidden) {
        menu.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
};

window.selectStatusOption = function(statusVal) {
    const menu = document.getElementById('menuStatusDropdown');
    const chevron = document.getElementById('iconChevronStatus');
    if (menu) menu.classList.add('hidden');
    if (chevron) chevron.classList.remove('rotate-180');
    window.onFilterStatusChange(statusVal);
};

// Tutup dropdown status jika klik di luar
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('wrapStatusFilter');
    const menu = document.getElementById('menuStatusDropdown');
    const chevron = document.getElementById('iconChevronStatus');
    if (wrap && menu && !wrap.contains(e.target)) {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
});

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

            const menuStatus = document.getElementById('menuStatusDropdown');
            if (menuStatus) menuStatus.classList.add('hidden');
            const chevronStatus = document.getElementById('iconChevronStatus');
            if (chevronStatus) chevronStatus.classList.remove('rotate-180');
            const dropPegawai = document.getElementById('dropdownPegawai');
            if (dropPegawai) dropPegawai.classList.add('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';

            const allMonthsBtn = el('btnAllMonthsOfYearAdmin');
            const allMonthsLabel = el('allMonthsYearLabelAdmin');
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
// RESET FILTER
// =====================
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
// MODAL PRESENSI
// =====================
window.openModalPresensi = function(id) {
    document.getElementById('presensiSubtitle').textContent = '-';
    document.getElementById('presensiBody').innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Memuat data...</p>';
    document.getElementById('modalPresensi').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    fetch(`/admin/pengajuan/${id}/presensi`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('presensiSubtitle').textContent = `${data.nama} · ${data.nip}`;

        document.getElementById('presensiBody').innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <div class="border rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 border-gray-200">${data.tanggal}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Kehadiran</label>
                <div class="border rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 border-gray-200">
                    ${data.status ?? '<span class="text-gray-400">Tidak ada data presensi</span>'}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Kedatangan</label>
                    <div class="border rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 border-gray-200">${data.jam_masuk ?? '-'}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Kepulangan</label>
                    <div class="border rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-50 border-gray-200">${data.jam_pulang ?? '-'}</div>
                </div>
            </div>
        `;
    })
    .catch(() => {
        document.getElementById('presensiBody').innerHTML = '<p class="text-sm text-red-400 text-center py-4">Gagal memuat data presensi.</p>';
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
let currentUraian = '';
let currentHasPresensi = false;
let currentJamSelesaiPresensi = '';
let currentJamPengajuanMulaiAdmin = '';
let currentJamPengajuanSelesaiAdmin = '';

function updateCatatanAdminWajibHint() {
    const labelCatatan = document.getElementById('labelCatatan');
    if (!labelCatatan) return;

    if (keputusan === 'cancelled') {
        labelCatatan.innerHTML = 'Catatan / Alasan Pembatalan <span class="text-rose-500 font-semibold">*wajib</span>';
        return;
    }
    if (keputusan === 'rejected') {
        labelCatatan.textContent = 'Catatan / Alasan Penolakan';
        return;
    }

    const mulai = document.getElementById('kJamMulai').value;
    const selesai = document.getElementById('kJamSelesai').value;
    const isJamDiubah = (currentJamPengajuanMulaiAdmin && mulai !== currentJamPengajuanMulaiAdmin) ||
                        (currentJamPengajuanSelesaiAdmin && selesai !== currentJamPengajuanSelesaiAdmin);

    if (isJamDiubah) {
        labelCatatan.innerHTML = 'Catatan <span class="text-rose-600 font-semibold text-xs">*wajib (jam disetujui berbeda)</span>';
    } else {
        labelCatatan.textContent = 'Catatan (Opsional)';
    }
}

window.openModalKeputusan = function(id, jamMulai, jamSelesai, catatan, uraian, hasPresensi, jamSelesaiPresensi, initialStatus, jamPengajuanMulai, jamPengajuanSelesai) {
    currentId = id;
    keputusan = (initialStatus === 'approved' || initialStatus === 'rejected' || initialStatus === 'cancelled') ? initialStatus : null;
    currentUraian = (uraian !== undefined && uraian !== null) ? uraian : '';
    currentHasPresensi = Boolean(hasPresensi);
    currentJamSelesaiPresensi = (jamSelesaiPresensi !== undefined && jamSelesaiPresensi !== null) ? String(jamSelesaiPresensi).trim() : '';
    currentJamPengajuanMulaiAdmin = (jamPengajuanMulai !== undefined && jamPengajuanMulai !== null && String(jamPengajuanMulai).trim() !== '') ? String(jamPengajuanMulai).trim() : (jamMulai || '');
    currentJamPengajuanSelesaiAdmin = (jamPengajuanSelesai !== undefined && jamPengajuanSelesai !== null && String(jamPengajuanSelesai).trim() !== '') ? String(jamPengajuanSelesai).trim() : (jamSelesai || '');

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
        elUraian.className = 'border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-300 focus:ring-1 focus:ring-gray-400 resize-y transition-all bg-white';
        elUraianBadge.textContent = 'Presensi Ada (Dapat Diedit)';
        elUraianBadge.className = 'rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 border border-emerald-200';
        elUraianHint.classList.add('hidden');
    } else {
        elUraian.readOnly = true;
        elUraian.className = 'border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-200 bg-gray-50 text-gray-500 resize-y cursor-not-allowed';
        elUraianBadge.textContent = 'Presensi Belum Ada';
        elUraianBadge.className = 'rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800 border border-amber-200';
        elUraianHint.classList.remove('hidden');
    }

    if (initialStatus === 'approved') {
        setKeputusan('approved');
    } else if (initialStatus === 'rejected') {
        setKeputusan('rejected');
    } else if (initialStatus === 'cancelled') {
        setKeputusan('cancelled');
    } else {
        setKeputusan('approved');
    }
    cekWarningDurasi();
    updateCatatanAdminWajibHint();

    document.getElementById('modalKeputusan').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModalKeputusan = function() {
    document.getElementById('modalKeputusan').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    currentId = null;
    keputusan = null;
    currentUraian = '';
    currentHasPresensi = false;
    currentJamSelesaiPresensi = '';
    currentJamPengajuanMulaiAdmin = '';
    currentJamPengajuanSelesaiAdmin = '';
};

window.setKeputusan = function(val) {
    keputusan = val;
    resetBtnKeputusan();

    const wrapperJam = document.getElementById('wrapperJamDisetujui');
    const labelCatatan = document.getElementById('labelCatatan');

    if (val === 'rejected') {
        document.getElementById('kBtnTolak').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-bold rounded-xl border-2 border-rose-500 bg-rose-50 text-rose-700 transition-all flex items-center justify-center gap-1 shadow-xs';
        if (wrapperJam) wrapperJam.classList.remove('hidden');
    } else if (val === 'cancelled') {
        document.getElementById('kBtnBatal').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-bold rounded-xl border-2 border-amber-500 bg-amber-50 text-amber-800 transition-all flex items-center justify-center gap-1 shadow-xs';
        if (wrapperJam) wrapperJam.classList.add('hidden');
    } else {
        document.getElementById('kBtnSetujui').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-bold rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-800 transition-all flex items-center justify-center gap-1 shadow-xs';
        if (wrapperJam) wrapperJam.classList.remove('hidden');
    }
    updateCatatanAdminWajibHint();
};

function resetBtnKeputusan() {
    document.getElementById('kBtnTolak').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600 transition-all flex items-center justify-center gap-1';
    document.getElementById('kBtnBatal').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-amber-50 hover:border-amber-400 hover:text-amber-800 transition-all flex items-center justify-center gap-1';
    document.getElementById('kBtnSetujui').className = 'px-2.5 py-1.5 text-xs sm:text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 transition-all flex items-center justify-center gap-1';
    const wrapperJam = document.getElementById('wrapperJamDisetujui');
    if (wrapperJam) wrapperJam.classList.remove('hidden');
    const labelCatatan = document.getElementById('labelCatatan');
    if (labelCatatan) labelCatatan.textContent = 'Catatan';
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
    const jam = Math.floor(((js * 60 + ms) - (jm * 60 + mm)) / 60);

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

document.getElementById('kJamMulai')?.addEventListener('change', () => { cekWarningDurasi(); updateCatatanAdminWajibHint(); });
document.getElementById('kJamMulai')?.addEventListener('input', updateCatatanAdminWajibHint);
document.getElementById('kJamSelesai')?.addEventListener('change', () => { cekWarningDurasi(); updateCatatanAdminWajibHint(); });
document.getElementById('kJamSelesai')?.addEventListener('input', updateCatatanAdminWajibHint);
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

    if (keputusan === 'cancelled') {
        if (!catatan.trim()) {
            alert('Alasan pembatalan wajib diisi pada kotak catatan.');
            document.getElementById('kCatatan').focus();
            return;
        }
    } else if (keputusan !== 'rejected') {
        if (!jamMulai || !jamSelesai) {
            alert('Jam mulai dan jam selesai wajib diisi.');
            return;
        }

        const isJamDiubah = (currentJamPengajuanMulaiAdmin && jamMulai !== currentJamPengajuanMulaiAdmin) ||
                            (currentJamPengajuanSelesaiAdmin && jamSelesai !== currentJamPengajuanSelesaiAdmin);
        if (isJamDiubah && !catatan.trim()) {
            alert('Catatan wajib diisi jika jam lembur yang disetujui berbeda dari jam pengajuan.');
            document.getElementById('kCatatan').focus();
            return;
        }

        if (currentJamSelesaiPresensi && jamSelesai > currentJamSelesaiPresensi) {
            alert(`Jam selesai disetujui (${jamSelesai}) tidak boleh melebihi jam kepulangan presensi pegawai (${currentJamSelesaiPresensi}).`);
            document.getElementById('kJamSelesai').focus();
            return;
        }
    }

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch(`/admin/pengajuan/${currentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            status: keputusan,
            jam_mulai_disetujui: jamMulai,
            jam_selesai_disetujui: jamSelesai,
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
        const statusEl = document.querySelector(`#status-${currentId}`);

        if (statusEl) {
            let badgeClass = 'bg-green-100 text-green-700';
            let badgeText = 'Disetujui';
            let btnTitle = 'Koreksi';
            let actionStatus = 'approved';

            if (keputusan === 'rejected') {
                badgeClass = 'bg-red-100 text-red-700';
                badgeText = 'Ditolak';
                btnTitle = 'Lihat / Koreksi';
                actionStatus = 'rejected';
            } else if (keputusan === 'cancelled') {
                badgeClass = 'bg-gray-100 text-gray-700 border border-gray-300';
                badgeText = 'Dibatalkan';
                btnTitle = 'Lihat / Ubah Keputusan';
                actionStatus = 'cancelled';
            }

            statusEl.innerHTML = `
                <div class="inline-flex items-center gap-1.5 justify-center">
                    <span class="${badgeClass} rounded-full px-2 text-xs py-0.5">${badgeText}</span>
                    <button type="button"
                        onclick="openModalKeputusan(${currentId}, '${jamMulai}', '${jamSelesai}', ${JSON.stringify(catatan)}, ${JSON.stringify(uraian)}, ${currentHasPresensi ? 1 : 0}, '${currentJamSelesaiPresensi}', '${actionStatus}', '${currentJamPengajuanMulaiAdmin}', '${currentJamPengajuanSelesaiAdmin}')"
                        class="text-gray-400 cursor-pointer hover:text-gray-600" title="${btnTitle}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </div>`;
        }

        const jamEl = document.querySelector(`#jam-disetujui-${currentId}`);
        if (jamEl) {
            jamEl.textContent = (keputusan === 'rejected' || keputusan === 'cancelled') ? '-' : `${jamMulai} - ${jamSelesai}`;
        }

        if (data.uraian !== undefined) {
            currentUraian = data.uraian;
            const uraianEl = document.querySelector(`#uraian-display-${currentId}`);
            if (uraianEl) {
                uraianEl.textContent = data.uraian || '-';
            }
        }

        closeModalKeputusan();
    })
    .catch(error => alert(error.message || 'Gagal menyimpan, coba lagi.'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    });
};

// =====================
// MODAL HARI LIBUR
// =====================
window.openModalHariLibur = function() {
    document.getElementById('modalHariLibur').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModalHariLibur = function() {
    document.getElementById('modalHariLibur').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

// =====================
// KALENDER RANGE PICKER HARI LIBUR
// =====================
(function() {
    const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const dayNames = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    const existingLibur = @json($hariLibur->pluck('tanggal')->toArray());

    const now = new Date();

    let viewYear = now.getFullYear();
    let viewMonth = now.getMonth();
    let state = {
        start: null,
        end: null
    };

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function toDate(str) {
        const [y, m, d] = str.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    function toStr(date) {
        return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
    }

    function getEffectiveRange(hoverStr) {
        if (!state.start) return {
            s: null,
            e: null
        };

        const end = state.end || hoverStr;

        if (!end) return {
            s: state.start,
            e: null
        };

        const sd = toDate(state.start);
        const ed = toDate(end);

        return sd <= ed
            ? { s: toStr(sd), e: toStr(ed) }
            : { s: toStr(ed), e: toStr(sd) };
    }

    function applyHighlight(hoverStr) {
        const { s, e } = getEffectiveRange(hoverStr);
        const grid = document.getElementById('hlGrid');

        if (!grid) return;

        grid.querySelectorAll('[data-date]').forEach(btn => {
            const d = btn.dataset.date;
            const isExisting = existingLibur.includes(d);
            const isS = d === s;
            const isE = d === e;
            const inR = s && e && toDate(d) >= toDate(s) && toDate(d) <= toDate(e);

            btn.className = 'text-sm py-1 transition cursor-pointer w-full rounded-lg ';

            if (isS || isE) {
                btn.className += 'bg-[#faa938] text-white';
            } else if (inR) {
                btn.className += 'bg-[#faa938]/20 text-[#faa938]';
            } else if (isExisting) {
                btn.className += 'bg-red-100 text-red-500';
            } else {
                btn.className += 'text-gray-700 hover:text-[#faa938]';
            }
        });
    }

    function renderHlGrid() {
        const grid = document.getElementById('hlGrid');
        const navLabel = document.getElementById('hlNavLabel');

        if (!grid || !navLabel) return;

        navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        grid.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'grid grid-cols-7 mb-1';

        dayNames.forEach(d => {
            const span = document.createElement('span');
            span.className = 'text-center text-xs text-gray-400 py-1';
            span.textContent = d;
            header.appendChild(span);
        });

        grid.appendChild(header);

        const dayGrid = document.createElement('div');
        dayGrid.className = 'grid grid-cols-7 gap-y-1';

        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInPrev = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = firstDay - 1; i >= 0; i--) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = daysInPrev - i;
            btn.className = 'text-sm py-1 text-gray-200 cursor-default w-full';
            dayGrid.appendChild(btn);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${viewYear}-${pad2(viewMonth + 1)}-${pad2(d)}`;
            const btn = document.createElement('button');

            btn.type = 'button';
            btn.textContent = d;
            btn.dataset.date = dateStr;
            btn.className = 'text-sm py-1 transition cursor-pointer w-full rounded-lg text-gray-700 hover:text-[#faa938]';

            btn.addEventListener('click', () => {
                if (!state.start || state.end) {
                    state.start = dateStr;
                    state.end = null;
                } else {
                    state.end = dateStr;

                    if (toDate(state.start) > toDate(state.end)) {
                        [state.start, state.end] = [state.end, state.start];
                    }
                }

                updateRangeInfo();
                applyHighlight(null);
            });

            dayGrid.appendChild(btn);
        }

        dayGrid.addEventListener('mouseover', (e) => {
            if (!state.start || state.end) return;

            const btn = e.target.closest('[data-date]');

            if (btn) applyHighlight(btn.dataset.date);
        });

        dayGrid.addEventListener('mouseleave', () => {
            if (!state.start || state.end) return;

            applyHighlight(null);
        });

        grid.appendChild(dayGrid);
        applyHighlight(null);
    }

    function updateRangeInfo() {
        const info = document.getElementById('hlRangeInfo');
        const btnAdd = document.getElementById('hlBtnTambah');
        const inputMulai = document.getElementById('hlInputMulai');
        const inputSelesai = document.getElementById('hlInputSelesai');

        if (state.start && state.end) {
            inputMulai.value = state.start;
            inputSelesai.value = state.end;
            info.textContent = state.start === state.end ? state.start : `${state.start} s/d ${state.end}`;
            info.classList.remove('hidden');
            btnAdd.disabled = false;
        } else if (state.start) {
            info.textContent = `Pilih tanggal akhir... (mulai: ${state.start})`;
            info.classList.remove('hidden');
            btnAdd.disabled = true;
        } else {
            info.classList.add('hidden');
            btnAdd.disabled = true;
        }
    }

    window.submitHariLibur = function() {
        if (!state.start || !state.end) {
            alert('Pilih tanggal terlebih dahulu.');
            return;
        }

        document.getElementById('formHariLibur').submit();
    };

    document.getElementById('hlPrev')?.addEventListener('click', () => {
        viewMonth--;

        if (viewMonth < 0) {
            viewMonth = 11;
            viewYear--;
        }

        renderHlGrid();
    });

    document.getElementById('hlNext')?.addEventListener('click', () => {
        viewMonth++;

        if (viewMonth > 11) {
            viewMonth = 0;
            viewYear++;
        }

        renderHlGrid();
    });

    window.openModalHariLibur = function() {
        state = {
            start: null,
            end: null
        };

        updateRangeInfo();
        renderHlGrid();

        document.getElementById('modalHariLibur').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalHariLibur = function() {
        document.getElementById('modalHariLibur').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };
})();
</script>
@endpush

@endsection

@extends('layouts.app')

@section('title', 'Pengajuan Lembur')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Flash success / error --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-800">
                Pengajuan Lembur Pribadi
            </h1>
            <p class="hidden sm:block text-xs text-slate-500 mt-0.5">
                Kelola dan pantau riwayat pengajuan kegiatan lembur mandiri Anda.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            {{-- Toggle Mode Tampilan (Tabel vs Timeline) --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/80 shadow-2xs shrink-0">
                <button type="button" id="btnViewTable" onclick="switchViewMode('table')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-white text-slate-800 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Tabel</span>
                </button>
                <button type="button" id="btnViewTimeline" onclick="switchViewMode('timeline')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-800 transition-all">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Timeline</span>
                </button>
            </div>

            {{-- Tombol Ajukan Lembur --}}
            <a href="javascript:void(0)" id="btnAjukan"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow-sm transition-all shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Ajukan Lembur</span>
            </a>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">

        {{-- Filter Tanggal --}}
        <div class="relative" id="datePicker">
            <button type="button" id="dateBtn"
                class="inline-flex h-10 items-center justify-between gap-2.5 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all whitespace-nowrap">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3.5 w-3.5 fill-current text-[#faa938] shrink-0">
                        <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                    </svg>
                    <span id="dateLabel" class="whitespace-nowrap leading-none">Semua Tanggal</span>
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current opacity-40 shrink-0">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <input type="hidden" id="dateValue" value="">

            {{-- Date Panel --}}
            <div id="datePanel"
                class="absolute left-0 z-50 mt-2 hidden w-72 rounded-2xl border border-gray-200 bg-white p-3.5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <button type="button" id="datePrev"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <span id="dateNavLabel"
                        class="cursor-pointer select-none text-xs font-bold text-gray-900 hover:text-[#faa938]">
                    </span>

                    <button type="button" id="dateNext"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                <div id="dateGrid"></div>

                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2.5">
                    <button type="button" id="btnToday"
                        class="text-xs font-semibold text-gray-600 hover:text-[#faa938] transition-colors">
                        Hari ini
                    </button>

                    <button type="button" id="btnDateClose"
                        class="rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Bulan --}}
        <select id="filterBulan" onchange="gantiFilter('bulan', this.value)"
            class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer whitespace-nowrap">
            <option value="">Semua Bulan</option>
            @php $tahunBulan = now()->format('Y'); \Carbon\Carbon::setLocale('id'); @endphp
            @for($m = 1; $m <= 12; $m++)
                @php
                    $valBulan  = \Carbon\Carbon::create($tahunBulan, $m, 1)->format('Y-m');
                    $namaBulan = \Carbon\Carbon::create($tahunBulan, $m, 1)->translatedFormat('F');
                @endphp
                <option value="{{ $valBulan }}" @selected($bulan ?? $valBulan)>{{ ucfirst($namaBulan) }} {{ $tahunBulan }}</option>
            @endfor
        </select>

        {{-- Jumlah per Halaman --}}
        <select id="perHalaman" onchange="gantiFilter('perPage', this.value)"
            class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer whitespace-nowrap">
            <option value="10"  @selected($perPage ?? 10) = 10)>10 / hal</option>
            <option value="25"  @selected($perPage ?? 10) = 25)>25 / hal</option>
            <option value="50"  @selected($perPage ?? 10) = 50)>50 / hal</option>
            <option value="100" @selected($perPage ?? 10) = 100)>100 / hal</option>
        </select>

        {{-- Filter Tim --}}
        <div class="relative w-full sm:w-64" id="wrapSearchTim">
            <input type="text" id="searchTim" placeholder="Cari nama tim..."
                onclick="openDropdownTim()" onfocus="openDropdownTim()" oninput="filterDropdownTim()" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-3.5 pr-12 text-xs font-medium text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all">

            <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                <button type="button" id="btnClearTim" onclick="pilihTim(null)" class="hidden p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100 transition-colors" title="Hapus filter tim">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <button type="button" onclick="toggleDropdownTim()" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none p-0.5" title="Buka daftar tim">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3">
                        <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                    </svg>
                </button>
            </div>

            <div id="dropdownTim"
                class="absolute z-40 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listTim"></ul>
            </div>
        </div>

        {{-- Reset Filter --}}
        <button type="button" id="btnResetFilter"
            class="hidden h-10 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-500 shadow-2xs hover:border-red-300 hover:text-red-500 transition-colors whitespace-nowrap">
            Reset
        </button>

    </div>

    {{-- Container 1: Tampilan Tabel --}}
    <div id="viewContainerTable">
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

        {{-- Card Tabel --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full {{ $transaksi->isEmpty() ? 'min-w-[840px] sm:min-w-full' : 'min-w-[1280px]' }} table-fixed divide-y divide-gray-200">
                    <thead class="bg-gray-50/90 border-b border-gray-200">
                        <tr>
                            <th class="w-32 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize rounded-tl-2xl">Tanggal</th>
                            <th class="w-32 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize">Jam Diajukan</th>
                            <th class="w-32 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize">Jam Disetujui</th>
                            <th class="w-64 px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize">Uraian Kegiatan</th>
                            <th class="w-40 px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize">Ketua Tim</th>
                            <th class="w-44 px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize">Nama Tim</th>
                            <th class="w-32 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize">Status</th>
                            <th class="w-44 px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize">Catatan</th>
                            <th class="w-32 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize">Dokumentasi</th>
                            <th class="w-28 px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize rounded-tr-2xl">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white" id="tabelLembur">
                        @forelse($transaksi as $t)
                                <tr class="transition-colors hover:bg-slate-50/80"
                                    data-tanggal="{{ $t->date }}"
                                    data-tim="{{ $t->tim_kode_tim }}">

                                    <td class="whitespace-nowrap px-4 py-3.5 text-xs font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($t->date)->translatedFormat('d F Y') }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5 text-center text-xs font-mono text-gray-700">
                                        @if($t->jam_mulai && $t->jam_selesai)
                                            {{ substr($t->jam_mulai, 0, 5) }} - {{ substr($t->jam_selesai, 0, 5) }}
                                        @elseif($t->jam_mulai)
                                            {{ substr($t->jam_mulai, 0, 5) }} - <span class="italic text-gray-400">menunggu</span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5 text-center text-xs">
                                        @php
                                            $isAdjusted = $t->status === 'approved'
                                                && $t->jam_mulai_disetujui
                                                && $t->jam_selesai_disetujui
                                                && (substr($t->jam_mulai_disetujui, 0, 5) !== substr($t->jam_mulai, 0, 5) || substr($t->jam_selesai_disetujui, 0, 5) !== substr($t->jam_selesai, 0, 5));
                                        @endphp

                                        @if($t->status === 'approved')
                                            @if($t->jam_mulai_disetujui && $t->jam_selesai_disetujui)
                                                <div class="inline-flex flex-col items-center gap-1">
                                                    <span class="font-mono text-xs font-semibold text-emerald-700">
                                                        {{ substr($t->jam_mulai_disetujui, 0, 5) }} - {{ substr($t->jam_selesai_disetujui, 0, 5) }}
                                                    </span>
                                                    @if($isAdjusted)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="Jam disetujui disesuaikan dari jam pengajuan">
                                                            Disesuaikan
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 font-mono">-</span>
                                            @endif
                                        @elseif($t->status === 'rejected')
                                            <span class="text-xs text-rose-500 italic">Ditolak</span>
                                        @elseif($t->status === 'cancelled')
                                            <span class="text-xs text-gray-400 italic">Dibatalkan</span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Menunggu</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-800">
                                        <div class="max-w-[280px] whitespace-normal break-words leading-relaxed">
                                            {{ $t->uraian ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-700">
                                        <div class="max-w-[140px] whitespace-normal break-words font-medium">
                                            {{ $t->nama_ketua ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-600">
                                        <div class="max-w-[160px] whitespace-normal break-words">
                                            {{ $t->nama_tim ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-center text-xs">
                                        @if($t->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu Ketua
                                            </span>
                                        @elseif($t->status === 'menunggu_kabag')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                Menunggu Kabag
                                            </span>
                                        @elseif($t->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Disetujui
                                            </span>
                                        @elseif($t->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                Ditolak
                                            </span>
                                        @elseif($t->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-300">
                                                Dibatalkan Admin
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-600">
                                        <div class="max-w-[180px] space-y-1 text-left">
                                            @if(!empty($t->note))
                                                <div>
                                                    <span class="text-[10px] font-bold uppercase text-slate-400">Ketua Tim:</span>
                                                    <div class="text-[11px] text-gray-700 italic break-words">{{ $t->note }}</div>
                                                </div>
                                            @endif
                                            @if(!empty($t->note_kabag))
                                                <div>
                                                    <span class="text-[10px] font-bold uppercase text-blue-600">Kabag Umum:</span>
                                                    <div class="text-[11px] text-blue-900 italic break-words">{{ $t->note_kabag }}</div>
                                                </div>
                                            @endif
                                            @if(empty($t->note) && empty($t->note_kabag))
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-center text-xs">
                                        @if($t->status === 'approved')
                                            @if($t->file_dokumentasi)
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                                        class="inline-flex items-center gap-1 font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        Lihat
                                                    </a>

                                                    <form action="{{ route('pegawai.lembur.destroyDoc', $t->id_transaksi) }}" method="POST"
                                                        onsubmit="return confirm('Hapus dokumentasi ini?')">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Hapus dokumentasi">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <button type="button"
                                                    onclick="openModalDok(this)"
                                                    data-action="{{ route('pegawai.lembur.storeDoc', $t->id_transaksi) }}"
                                                    class="inline-flex items-center gap-1 font-medium text-[#faa938] hover:text-[#fd9a10] transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                                    </svg>
                                                    Dokumentasi
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-center text-xs whitespace-nowrap">
                                        @php
                                            $canEdit = ($t->status === 'pending') || ($t->status === 'menunggu_kabag' && empty($t->approved_at));
                                        @endphp
                                        @if($canEdit)
                                            <button type="button"
                                                onclick="openModalEdit(this)"
                                                data-action="{{ route('pegawai.lembur.update', $t->id_transaksi) }}"
                                                data-tanggal="{{ \Carbon\Carbon::parse($t->date)->translatedFormat('l, d F Y') }}"
                                                data-jam-mulai="{{ $t->jam_mulai ? substr($t->jam_mulai, 0, 5) : '' }}"
                                                data-jam-selesai="{{ $t->jam_selesai ? substr($t->jam_selesai, 0, 5) : '' }}"
                                                data-uraian="{{ $t->uraian ?? '' }}"
                                                data-approver="{{ $t->approver_employee_id ?? '' }}"
                                                data-kode-tim="{{ $t->tim_kode_tim ?? '' }}"
                                                data-ketua="{{ $t->nama_ketua ?? '-' }}"
                                                data-tim="{{ $t->nama_tim ?? '-' }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 transition-all shadow-2xs cursor-pointer"
                                                title="Ubah ketua tim, jam, atau uraian kegiatan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                                </svg>
                                                <span>Ubah</span>
                                            </button>
                                        @else
                                            <span class="text-gray-300 font-medium select-none" title="Sudah diproses / terkunci">-</span>
                                        @endif
                                    </td>
                                </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-[#faa938] mb-3 border border-amber-100/80 shadow-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 mb-1">Belum Ada Pengajuan Lembur</h3>
                                        <p class="text-xs text-gray-500 mb-4 text-center leading-relaxed">
                                            Anda belum memiliki riwayat pengajuan kegiatan lembur mandiri pada periode ini.
                                        </p>
                                        <button type="button" onclick="openModalAjukan()"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#faa938] px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            <span>Ajukan Lembur Sekarang</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="emptyFilterRow" class="hidden">
                            <td colspan="10" class="px-4 py-12 text-center text-xs text-gray-500 font-medium">
                                Tidak ada data pengajuan lembur yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Container 2: Tampilan Visual Timeline (Paket Visual Premium Polish) --}}
    <div id="viewContainerTimeline" class="hidden">
        @if($transaksi->isEmpty())
            <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-12 sm:p-16 text-center shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-[#faa938] mb-3 border border-amber-100/80 shadow-xs">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Belum Ada Pengajuan Lembur</h3>
                <p class="text-xs text-gray-500 mb-4 text-center leading-relaxed">Anda belum memiliki riwayat pengajuan kegiatan lembur mandiri pada periode ini.</p>
                <button type="button" onclick="openModalAjukan()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#faa938] px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Ajukan Lembur Sekarang</span>
                </button>
            </div>
        @else
            <div class="max-w-4xl mx-auto py-2">
                <div class="relative" id="timelineList">
                    @foreach($transaksi as $t)
                        @php
                            $carbonDate = \Carbon\Carbon::parse($t->date);
                            $jamMulai = $t->jam_mulai ? substr($t->jam_mulai, 0, 5) : '-';
                            $jamSelesai = $t->jam_selesai ? substr($t->jam_selesai, 0, 5) : '-';
                            $jamMulaiAcc = $t->jam_mulai_disetujui ? substr($t->jam_mulai_disetujui, 0, 5) : null;
                            $jamSelesaiAcc = $t->jam_selesai_disetujui ? substr($t->jam_selesai_disetujui, 0, 5) : null;
                            $isAdjusted = $t->status === 'approved' && $jamMulaiAcc && $jamSelesaiAcc && ($jamMulaiAcc !== $jamMulai || $jamSelesaiAcc !== $jamSelesai);
                        @endphp

                        <div class="flex items-start gap-3 sm:gap-5 group timeline-item pb-5 sm:pb-6 last:pb-2" data-tanggal="{{ $t->date }}" data-tim="{{ $t->tim_kode_tim }}">
                            {{-- 1. Date Box --}}
                            <div class="w-16 sm:w-20 shrink-0 flex flex-col items-center justify-center rounded-2xl bg-white border border-slate-300 py-2.5 px-1.5 shadow-2xs group-hover:border-slate-400 transition-all duration-200">
                                <span class="text-xl sm:text-2xl font-black font-mono text-slate-800 leading-none tracking-tight">
                                    {{ $carbonDate->format('d') }}
                                </span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono mt-1">
                                    {{ $carbonDate->translatedFormat('M') }}
                                </span>
                            </div>

                            {{-- 2. Kolom Bulatan Status (Dot Orange, Merah, Hijau) + Garis Aksis Vertikal Menyambung --}}
                            <div class="relative flex flex-col items-center shrink-0 w-6 self-stretch pt-3.5">
                                {{-- Garis Aksis Vertikal Menyambung Antar-Node --}}
                                <div class="timeline-stem absolute left-1/2 -translate-x-1/2 bg-slate-300 z-0"
                                     style="width: 2px; {{ $transaksi->count() <= 1 ? 'display: none;' : ($loop->first ? 'top: 21px; bottom: 0;' : ($loop->last ? 'top: 0; height: 21px;' : 'top: 0; bottom: 0;')) }}"></div>

                                {{-- Bulatan Dot Warna Status (Orange / Red / Green) --}}
                                <div class="relative z-10 flex items-center justify-center">
                                    @if($t->status === 'approved')
                                        <span class="block h-3.5 w-3.5 rounded-full bg-emerald-500 ring-4 ring-white shadow-2xs"></span>
                                    @elseif($t->status === 'menunggu_kabag')
                                        <span class="block h-3.5 w-3.5 rounded-full bg-blue-600 ring-4 ring-white shadow-2xs animate-pulse"></span>
                                    @elseif($t->status === 'pending')
                                        <span class="block h-3.5 w-3.5 rounded-full bg-amber-500 ring-4 ring-white shadow-2xs animate-pulse"></span>
                                    @elseif($t->status === 'rejected')
                                        <span class="block h-3.5 w-3.5 rounded-full bg-rose-500 ring-4 ring-white shadow-2xs"></span>
                                    @else
                                        <span class="block h-3.5 w-3.5 rounded-full bg-slate-400 ring-4 ring-white shadow-2xs"></span>
                                    @endif
                                </div>
                            </div>

                            {{-- 3. Kartu Timeline Clean & Elegan --}}
                            <div class="flex-1 min-w-0">
                                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:shadow-md transition-all duration-200 space-y-3">
                                    {{-- Header: Status Pill (Kiri) + Jam Diajukan (Kanan) --}}
                                    <div class="flex items-center justify-between gap-2 pb-1">
                                        <div>
                                            @if($t->status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    Diproses Ketua Tim
                                                </span>
                                            @elseif($t->status === 'menunggu_kabag')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                                    Menunggu Kabag Umum
                                                </span>
                                            @elseif($t->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Disetujui Final
                                                </span>
                                            @elseif($t->status === 'rejected')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                    Ditolak
                                                </span>
                                            @elseif($t->status === 'cancelled')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                    Dibatalkan Admin
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Jam Diajukan Badge Rapi --}}
                                        <div class="text-xs font-mono text-slate-500">
                                            <span class="text-slate-400 font-sans text-[11px] mr-1">Diajukan:</span>
                                            <span class="font-bold text-slate-800">{{ $jamMulai }} - {{ $jamSelesai }}</span>
                                        </div>
                                    </div>

                                    {{-- Judul Uraian Kegiatan --}}
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug break-words">
                                            {{ $t->uraian ?? 'Pengajuan Kegiatan Lembur' }}
                                        </h3>
                                    </div>

                                    {{-- Highlighting Jam Disetujui (Subtle fill, NO harsh box border) --}}
                                    @if($t->status === 'approved' && $jamMulaiAcc && $jamSelesaiAcc)
                                        <div class="px-3.5 py-2 rounded-xl bg-emerald-50/80 text-xs flex items-center justify-between text-emerald-900">
                                            <span class="font-medium">Jam Lembur Disetujui:</span>
                                            <span class="font-mono font-bold text-emerald-800 bg-white/80 px-2.5 py-0.5 rounded shadow-2xs">
                                                {{ $jamMulaiAcc }} - {{ $jamSelesaiAcc }}
                                            </span>
                                        </div>
                                        @if($isAdjusted)
                                            <div class="text-[11px] text-amber-800 font-medium px-1">
                                                ⚡ <strong>Disesuaikan:</strong> Jam diajukan ({{ $jamMulai }} - {{ $jamSelesai }}) disesuaikan dengan presensi fisik / operasional.
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Meta Info: Ketua Tim & Nama Tim (Single Clean Row) --}}
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 pt-0.5">
                                        <div class="text-slate-600">
                                            <span class="text-slate-400">Ketua Tim:</span>
                                            <span class="font-semibold text-slate-800 ml-1">{{ $t->nama_ketua ?? '-' }}</span>
                                        </div>

                                        <span class="text-[11px] font-medium text-slate-600 bg-slate-100/90 px-2.5 py-0.5 rounded-full">
                                            {{ $t->nama_tim ?? 'Tim Kerja' }}
                                        </span>
                                    </div>

                                    {{-- Catatan Persetujuan (Clean Quote Bar, NO box borders) --}}
                                    @if(!empty($t->note) || !empty($t->note_kabag))
                                        <div class="space-y-1.5 pt-1">
                                            @if(!empty($t->note))
                                                <div class="border-l-2 border-amber-400 pl-3 py-0.5 text-xs text-slate-600 bg-amber-50/40 rounded-r-lg">
                                                    <span class="font-semibold text-amber-800">Catatan Ketua:</span>
                                                    <span class="italic text-slate-700 ml-1">"{{ $t->note }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($t->note_kabag))
                                                <div class="border-l-2 border-blue-400 pl-3 py-0.5 text-xs text-slate-600 bg-blue-50/40 rounded-r-lg">
                                                    <span class="font-semibold text-blue-800">Catatan Kabag:</span>
                                                    <span class="italic text-slate-800 ml-1">"{{ $t->note_kabag }}"</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Baris 5: Footer Card (Dokumentasi & Aksi Edit) --}}
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs">
                                        <div>
                                            @if($t->status === 'approved')
                                                @if($t->file_dokumentasi)
                                                    <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                                        class="inline-flex items-center gap-1 font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                                        <span>Lihat Dokumentasi ↗</span>
                                                    </a>
                                                @else
                                                    <button type="button" onclick="openModalDok(this)" data-action="{{ route('pegawai.lembur.storeDoc', $t->id_transaksi) }}"
                                                        class="inline-flex items-center gap-1 font-medium text-amber-600 hover:text-amber-800 transition-colors">
                                                        <span>+ Unggah Dokumentasi</span>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>

                                        <div>
                                            @php
                                                $canEdit = ($t->status === 'pending') || ($t->status === 'menunggu_kabag' && empty($t->approved_at));
                                            @endphp
                                            @if($canEdit)
                                                <button type="button"
                                                    onclick="openModalEdit(this)"
                                                    data-action="{{ route('pegawai.lembur.update', $t->id_transaksi) }}"
                                                    data-tanggal="{{ \Carbon\Carbon::parse($t->date)->translatedFormat('l, d F Y') }}"
                                                    data-jam-mulai="{{ $t->jam_mulai ? substr($t->jam_mulai, 0, 5) : '' }}"
                                                    data-jam-selesai="{{ $t->jam_selesai ? substr($t->jam_selesai, 0, 5) : '' }}"
                                                    data-uraian="{{ $t->uraian ?? '' }}"
                                                    data-approver="{{ $t->approver_employee_id ?? '' }}"
                                                    data-kode-tim="{{ $t->tim_kode_tim ?? '' }}"
                                                    data-ketua="{{ $t->nama_ketua ?? '-' }}"
                                                    data-tim="{{ $t->nama_tim ?? '-' }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 transition-all shadow-2xs cursor-pointer"
                                                    title="Ubah ketua tim, jam, atau uraian kegiatan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                                    </svg>
                                                    <span>Ubah</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Pesan Kosong Saat Filter Timeline Aktif --}}
                    <div id="emptyFilterTimeline" class="hidden overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-10 text-center shadow-xs">
                        <p class="text-xs text-gray-500 font-medium">
                            Tidak ada data pengajuan lembur yang sesuai dengan filter pencarian.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($transaksi->hasPages())
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xs">

                @if($transaksi->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-transparent p-1.5 text-gray-300">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="rounded-lg border border-transparent p-1.5 text-gray-700 hover:border-gray-200 hover:bg-gray-50 transition-colors"
                        href="{{ $transaksi->previousPageUrl() }}">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="whitespace-nowrap px-2 text-xs font-medium text-gray-600">
                    Halaman {{ $transaksi->currentPage() }} dari {{ $transaksi->lastPage() }}
                </p>

                @if($transaksi->hasMorePages())
                    <a class="rounded-lg border border-transparent p-1.5 text-gray-700 hover:border-gray-200 hover:bg-gray-50 transition-colors"
                        href="{{ $transaksi->nextPageUrl() }}">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-transparent p-1.5 text-gray-300">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                @endif

            </nav>
        </div>
    @endif

</div>

{{-- Modal Dokumentasi --}}
<div id="modalDok" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalDok()"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-xl">

            <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold text-gray-900">Tambah Dokumentasi</h2>
                <button type="button" onclick="closeModalDok()"
                    class="text-xl leading-none text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>

            <form id="formDok" method="POST" class="space-y-4 px-5 py-5 sm:px-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-600">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $errorDok)
                                <li>{{ $errorDok }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Link Google Drive</label>
                    <input type="url" name="file_path" required
                        placeholder="https://drive.google.com/..."
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModalDok()"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50 sm:w-auto">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black hover:bg-[#fd9a10] hover:text-white sm:w-auto">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal Edit Lembur (Hanya Jam & Uraian) --}}
<div id="modalEditLembur" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" onclick="closeModalEdit()"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center px-4 py-6 sm:py-8">
            <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-black/5">

                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Ubah Pengajuan Lembur</h2>
                            <p class="text-xs text-gray-500">Perbarui ketua tim, jam, atau uraian kegiatan sebelum disetujui</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModalEdit()"
                        class="text-xl leading-none text-gray-400 hover:text-gray-600 transition-colors">
                        &times;
                    </button>
                </div>

                <form id="formEditLembur" method="POST" class="space-y-4 px-5 py-5 sm:px-6">
                    @csrf
                    @method('PUT')

                    {{-- Info Ringkas Tanggal Lembur (Read-Only) --}}
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3 text-xs text-gray-600">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-500">Tanggal Lembur:</span>
                            <span id="editTanggalLabel" class="font-semibold text-gray-800"></span>
                        </div>
                    </div>

                    {{-- Pilihan Ketua Tim & Tim --}}
                    <div>
                        <label for="edit_approver_id" class="mb-1.5 block text-xs font-semibold text-gray-700">Ketua Tim / Tim</label>
                        <input type="hidden" name="kode_tim" id="edit_kode_tim">
                        <select id="edit_approver_id" name="approver_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            <option value="">Pilih Ketua Tim</option>
                            @forelse($ketuaTim as $ketua)
                                <option value="{{ $ketua['nip'] }}" data-kode="{{ $ketua['kode_tim'] }}">
                                    {{ $ketua['nama'] }} ({{ $ketua['tim'] }})
                                </option>
                            @empty
                                <option disabled>Kamu tidak terdaftar di tim manapun</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Form Jam Mulai & Jam Selesai --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit_jam_mulai" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam Mulai</label>
                            <input type="time" id="edit_jam_mulai" name="jam_mulai" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        </div>

                        <div>
                            <label for="edit_jam_selesai" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam Selesai</label>
                            <input type="time" id="edit_jam_selesai" name="jam_selesai" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        </div>
                    </div>

                    {{-- Preview Durasi --}}
                    <p id="editPreviewDurasi" class="hidden text-xs text-gray-500">
                        Estimasi: <span id="editDurasiLabel" class="font-semibold text-gray-800"></span>
                    </p>

                    {{-- Form Uraian Kegiatan --}}
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label for="edit_uraian" class="block text-xs font-semibold text-gray-700">Uraian Kegiatan</label>
                            <span id="editUraianCount" class="text-[11px] text-gray-400">0 / 2000</span>
                        </div>
                        <textarea id="edit_uraian" name="uraian" rows="4" maxlength="2000" required
                            placeholder="Contoh: Menyelesaikan rekonsiliasi data..."
                            class="w-full resize-y rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"></textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" onclick="closeModalEdit()"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 sm:w-auto">
                            Batal
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black hover:bg-[#fd9a10] hover:text-white transition-all shadow-sm sm:w-auto">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- Modal Ajukan Lembur --}}
<div id="modalAjukan" class="fixed inset-0 z-50 hidden">
    <div id="modalOverlay" class="fixed inset-0 bg-black/40"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center px-4 py-6 sm:py-8">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl">

                <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-gray-900 sm:text-lg">Ajukan Lembur</h2>
                    <button type="button" id="btnCloseModal"
                        class="text-xl leading-none text-gray-500 hover:text-gray-700">
                        &times;
                    </button>
                </div>

                <form id="formAjukan" action="{{ route('lembur.store') }}" method="POST"
                    class="space-y-5 px-5 py-5 sm:px-6">
                    @csrf

                    <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage') }}">

                    <input type="hidden" name="kode_tim" id="kode_tim">

                    <div>
                        <label for="approver_id" class="mb-2 block text-sm font-medium text-gray-700">Ketua Tim</label>
                        <select id="approver_id" name="approver_id" required
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            <option value="">Pilih Ketua Tim</option>
                            @forelse($ketuaTim as $ketua)
                                <option value="{{ $ketua['nip'] }}" data-kode="{{ $ketua['kode_tim'] }}">
                                    {{ $ketua['nama'] }} ({{ $ketua['tim'] }})
                                </option>
                            @empty
                                <option disabled>Kamu tidak terdaftar di tim manapun</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <p id="infoHari" class="mt-1 hidden text-xs text-gray-400"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jam_mulai" class="mb-2 block text-sm font-medium text-gray-700">Jam Mulai</label>
                            <input type="time" id="jam_mulai" name="jam_mulai" required
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            <p id="infoJamMulai" class="mt-1 hidden text-xs text-black">Default hari kerja: 16:01</p>
                        </div>

                        <div id="wrapperJamSelesai">
                            <label for="jam_selesai" class="mb-2 block text-sm font-medium text-gray-700">Jam Selesai</label>
                            <input type="time" id="jam_selesai" name="jam_selesai" required
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        </div>
                    </div>

                    <p id="previewDurasi" class="hidden text-xs text-gray-500">
                        Estimasi: <span id="durasiLabel" class="font-semibold text-gray-800"></span>
                    </p>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="uraian" class="block text-sm font-medium text-gray-700">Uraian Kegiatan</label>
                            <span id="uraianCount" class="text-xs text-gray-400">0 / 2000</span>
                        </div>
                        <textarea id="uraian" name="uraian" rows="4" maxlength="2000" required
                            placeholder="Contoh: Penyusunan laporan bulanan..."
                            class="w-full resize-y rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Tanda Tangan</label>

                        <div class="overflow-hidden rounded-md border border-gray-300 bg-gray-50">
                            <canvas id="signatureCanvas" class="h-40 w-full touch-none"></canvas>
                        </div>

                        <div class="mt-2 flex justify-end">
                            <button type="button" id="btnClearSignature"
                                class="text-xs text-gray-500 underline hover:text-red-500">
                                Hapus Tanda Tangan
                            </button>
                        </div>

                        <input type="hidden" name="signature" id="signatureData">

                        <p id="signatureError" class="mt-1 hidden text-xs text-red-500">
                            Tanda tangan wajib diisi.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" id="btnCancel"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 sm:w-auto">
                            Batal
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black hover:bg-[#fd9a10] hover:text-white sm:w-auto">
                            Kirim
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const now = new Date();

    let selectedDate = null;
    let selectedTim = null;
    let cachedTim = [];

    function pad2(value) {
        return String(value).padStart(2, '0');
    }

    function updateResetBtn() {
        const btn = document.getElementById('btnResetFilter');
        if (!btn) return;

        if (selectedDate || selectedTim) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    window.gantiFilter = function (key, value) {
        const url = new URL(window.location.href);
        const params = url.searchParams;

        if (value === '') {
            params.delete(key);
        } else {
            params.set(key, value);
        }

        params.delete('page');

        window.location.href = url.pathname + '?' + params.toString();
    };

    function filterTabel() {
        let visibleCount = 0;
        const rows = document.querySelectorAll('#tabelLembur tr[data-tanggal]');
        rows.forEach(row => {
            const cocokTanggal = !selectedDate || row.dataset.tanggal === selectedDate;
            const cocokTim = !selectedTim || row.dataset.tim === selectedTim;
            const match = cocokTanggal && cocokTim;

            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        const timelineItems = document.querySelectorAll('#viewContainerTimeline .timeline-item[data-tanggal]');
        let visibleTimelineCount = 0;
        timelineItems.forEach(item => {
            const cocokTanggal = !selectedDate || item.dataset.tanggal === selectedDate;
            const cocokTim = !selectedTim || item.dataset.tim === selectedTim;
            const isMatch = cocokTanggal && cocokTim;
            item.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleTimelineCount++;
        });

        const emptyRow = document.getElementById('emptyFilterRow');
        if (emptyRow) {
            emptyRow.classList.toggle('hidden', rows.length === 0 || visibleCount > 0);
        }

        const emptyTimeline = document.getElementById('emptyFilterTimeline');
        if (emptyTimeline) {
            emptyTimeline.classList.toggle('hidden', timelineItems.length === 0 || visibleTimelineCount > 0);
        }

        updateTimelineStems();
    }

    function updateTimelineStems() {
        const visibleItems = Array.from(document.querySelectorAll('#viewContainerTimeline .timeline-item[data-tanggal]'))
            .filter(item => item.style.display !== 'none');

        visibleItems.forEach((item, index) => {
            const stem = item.querySelector('.timeline-stem');
            if (!stem) return;
            if (visibleItems.length <= 1) {
                stem.style.display = 'none';
            } else {
                stem.style.display = 'block';
                if (index === 0) {
                    stem.style.top = '21px';
                    stem.style.bottom = '0';
                    stem.style.height = 'auto';
                } else if (index === visibleItems.length - 1) {
                    stem.style.top = '0';
                    stem.style.bottom = 'auto';
                    stem.style.height = '21px';
                } else {
                    stem.style.top = '0';
                    stem.style.bottom = '0';
                    stem.style.height = 'auto';
                }
            }
        });
    }

    function makeBtn(text, className, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = text;
        button.className = className;
        button.addEventListener('click', event => {
            event.stopPropagation();
            onClick();
        });
        return button;
    }

    // =====================
    // FILTER TIM
    // =====================
    function renderDropdownTim(filter = '') {
        const list = document.getElementById('listTim');
        if (!list) return;

        list.innerHTML = '';

        const isSemuaSelected = !selectedTim;
        const liSemua = document.createElement('li');
        liSemua.className = 'cursor-pointer px-4 py-2.5 text-xs text-gray-500 hover:bg-gray-50 flex items-center justify-between ' + (isSemuaSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
        liSemua.innerHTML = '<span>Semua tim</span>' + (isSemuaSelected ? '<svg class="w-3.5 h-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
        liSemua.onclick = () => pilihTim(null);
        list.appendChild(liSemua);

        const keyword = (filter || '').toLowerCase().trim();

        const filtered = cachedTim.filter(tim => String(tim.nama_tim ?? '').toLowerCase().includes(keyword));

        if (filtered.length === 0) {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 text-xs text-gray-400 text-center';
            li.textContent = 'Tim tidak ditemukan';
            list.appendChild(li);
            return;
        }

        filtered.forEach(tim => {
            const isSelected = selectedTim === tim.kode_tim;
            const li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center justify-between ' + (isSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
            li.innerHTML = `<span>${tim.nama_tim ?? '-'}</span>` + (isSelected ? '<svg class="w-3.5 h-3.5 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
            li.onclick = () => pilihTim(tim);
            list.appendChild(li);
        });
    }

    window.openDropdownTim = function () {
        const dropdown = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');
        if (!dropdown || !search) return;

        renderDropdownTim(''); // Always render full list when opened
        dropdown.classList.remove('hidden');
        setTimeout(() => search.select(), 10);
    };

    window.toggleDropdownTim = function () {
        const dropdown = document.getElementById('dropdownTim');
        if (!dropdown) return;

        if (dropdown.classList.contains('hidden')) {
            openDropdownTim();
        } else {
            dropdown.classList.add('hidden');
        }
    };

    window.filterDropdownTim = function () {
        const dropdown = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');

        if (!dropdown || !search) return;

        renderDropdownTim(search.value);
        dropdown.classList.remove('hidden');

        const btnClear = document.getElementById('btnClearTim');
        if (btnClear) {
            search.value.trim() ? btnClear.classList.remove('hidden') : (selectedTim ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden'));
        }
    };

    function pilihTim(tim) {
        selectedTim = tim ? tim.kode_tim : null;

        const input = document.getElementById('searchTim');
        const dropdown = document.getElementById('dropdownTim');
        const btnClear = document.getElementById('btnClearTim');

        if (input) input.value = tim ? tim.nama_tim : '';
        if (dropdown) dropdown.classList.add('hidden');
        if (btnClear) {
            selectedTim ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden');
        }

        filterTabel();
        updateResetBtn();
    }

    // Click outside to close and restore active label
    document.addEventListener('click', (e) => {
        const wrap = document.getElementById('wrapSearchTim');
        const dropdown = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');
        if (wrap && dropdown && search && !wrap.contains(e.target)) {
            dropdown.classList.add('hidden');
            if (selectedTim) {
                const found = cachedTim.find(t => t.kode_tim === selectedTim);
                if (found) search.value = found.nama_tim;
            } else {
                search.value = '';
            }
        }
    });

    fetch('/lembur/tim')
        .then(response => response.json())
        .then(data => {
            cachedTim = Array.isArray(data) ? data : [];
            renderDropdownTim('');
        })
        .catch(() => {
            cachedTim = [];
        });

    // =====================
    // DATE PICKER
    // =====================
    (function initDatePicker() {
        const picker = document.getElementById('datePicker');
        const btn = document.getElementById('dateBtn');
        const panel = document.getElementById('datePanel');
        const grid = document.getElementById('dateGrid');
        const navLabel = document.getElementById('dateNavLabel');
        const dateLabel = document.getElementById('dateLabel');
        const dateValue = document.getElementById('dateValue');
        const btnPrev = document.getElementById('datePrev');
        const btnNext = document.getElementById('dateNext');
        const btnToday = document.getElementById('btnToday');
        const btnClose = document.getElementById('btnDateClose');

        if (!picker || !btn || !panel || !grid || !navLabel) return;

        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const dayNames = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

        let view = 'day';
        let viewYear = now.getFullYear();
        let viewMonth = now.getMonth();
        let selYear = null;
        let selMonth = null;
        let selDay = null;

        function setDate(year, month, day) {
            selYear = year;
            selMonth = month;
            selDay = day;

            selectedDate = `${year}-${pad2(month + 1)}-${pad2(day)}`;

            dateLabel.textContent = `${day} ${monthShort[month]} ${year}`;
            dateValue.value = selectedDate;

            filterTabel();
            updateResetBtn();
        }

        function openPanel() {
            if (selYear !== null && selMonth !== null) {
                viewYear = selYear;
                viewMonth = selMonth;
            } else {
                viewYear = now.getFullYear();
                viewMonth = now.getMonth();
            }

            renderDay();
            panel.classList.remove('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        function renderDay() {
            view = 'day';
            navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
            navLabel.className = 'cursor-pointer select-none text-sm font-medium text-gray-900 hover:text-[#faa938]';
            grid.innerHTML = '';

            const header = document.createElement('div');
            header.className = 'mb-1 grid grid-cols-7';

            dayNames.forEach(day => {
                const span = document.createElement('span');
                span.className = 'py-1 text-center text-xs text-gray-400';
                span.textContent = day;
                header.appendChild(span);
            });

            grid.appendChild(header);

            const dayGrid = document.createElement('div');
            dayGrid.className = 'grid grid-cols-7 gap-y-1';

            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const firstDay = new Date(viewYear, viewMonth, 1).getDay();
            const daysInPrev = new Date(viewYear, viewMonth, 0).getDate();
            const base = 'rounded-lg border py-1 text-sm transition-all ';

            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrev - i;

                dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', () => {
                    let month = viewMonth - 1;
                    let year = viewYear;

                    if (month < 0) {
                        month = 11;
                        year--;
                    }

                    setDate(year, month, day);
                    closePanel();
                }));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const isSelected = day === selDay && viewMonth === selMonth && viewYear === selYear;
                const isToday = day === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isToday
                        ? 'border-transparent bg-[#faa938]/20 text-[#faa938]'
                        : 'border-transparent text-gray-700 hover:border-[#faa938] hover:text-[#faa938]';

                dayGrid.appendChild(makeBtn(day, base + className, () => {
                    setDate(viewYear, viewMonth, day);
                    closePanel();
                }));
            }

            const total = firstDay + daysInMonth;
            const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);

            for (let day = 1; day <= remaining; day++) {
                dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', () => {
                    let month = viewMonth + 1;
                    let year = viewYear;

                    if (month > 11) {
                        month = 0;
                        year++;
                    }

                    setDate(year, month, day);
                    closePanel();
                }));
            }

            grid.appendChild(dayGrid);
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'cursor-pointer select-none text-sm font-medium text-gray-900 hover:text-[#faa938]';
            grid.innerHTML = '';

            const monthGrid = document.createElement('div');
            monthGrid.className = 'grid grid-cols-3 gap-2';

            monthNames.forEach((name, month) => {
                const isSelected = month === selMonth && viewYear === selYear;
                const isNow = month === now.getMonth() && viewYear === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isNow
                        ? 'border-[#faa938] bg-white text-[#faa938]'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';

                monthGrid.appendChild(makeBtn(name.slice(0, 3), 'rounded-lg border px-2 py-2 text-sm transition-all ' + className, () => {
                    viewMonth = month;
                    renderDay();
                }));
            });

            grid.appendChild(monthGrid);
        }

        function renderYear() {
            view = 'year';

            const startYear = Math.floor(viewYear / 12) * 12;

            navLabel.textContent = `${startYear} - ${startYear + 11}`;
            navLabel.className = 'cursor-default select-none text-sm font-medium text-gray-400';

            grid.innerHTML = '';

            const yearGrid = document.createElement('div');
            yearGrid.className = 'grid grid-cols-3 gap-2';

            for (let year = startYear; year < startYear + 12; year++) {
                const isSelected = year === selYear;
                const isNow = year === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isNow
                        ? 'border-[#faa938] bg-white text-[#faa938]'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';

                yearGrid.appendChild(makeBtn(year, 'rounded-lg border px-2 py-2 text-sm transition-all ' + className, () => {
                    viewYear = year;
                    renderMonth();
                }));
            }

            grid.appendChild(yearGrid);
        }

        btn.addEventListener('click', event => {
            event.stopPropagation();

            if (panel.classList.contains('hidden')) {
                openPanel();
            } else {
                closePanel();
            }
        });

        navLabel.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                renderMonth();
            } else if (view === 'month') {
                renderYear();
            }
        });

        btnPrev?.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                viewMonth--;

                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear--;
                }

                renderDay();
            } else if (view === 'month') {
                viewYear--;
                renderMonth();
            } else if (view === 'year') {
                viewYear -= 12;
                renderYear();
            }
        });

        btnNext?.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                viewMonth++;

                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear++;
                }

                renderDay();
            } else if (view === 'month') {
                viewYear++;
                renderMonth();
            } else if (view === 'year') {
                viewYear += 12;
                renderYear();
            }
        });

        btnToday?.addEventListener('click', event => {
            event.stopPropagation();

            viewYear = now.getFullYear();
            viewMonth = now.getMonth();

            setDate(now.getFullYear(), now.getMonth(), now.getDate());
            closePanel();
        });

        btnClose?.addEventListener('click', event => {
            event.stopPropagation();
            closePanel();
        });

        document.addEventListener('click', event => {
            if (!picker.contains(event.target)) {
                closePanel();
            }

            const dropdownTim = document.getElementById('dropdownTim');
            const searchTim = document.getElementById('searchTim');

            if (dropdownTim && searchTim && !dropdownTim.contains(event.target) && !searchTim.contains(event.target)) {
                dropdownTim.classList.add('hidden');
            }
        });
    })();

    // =====================
    // RESET FILTER
    // =====================
    document.getElementById('btnResetFilter')?.addEventListener('click', () => {
        selectedDate = null;
        selectedTim = null;

        document.getElementById('dateLabel').textContent = 'Semua Tanggal';
        document.getElementById('dateValue').value = '';
        document.getElementById('searchTim').value = '';

        filterTabel();
        updateResetBtn();
    });

    // =====================
    // MODAL DOKUMENTASI
    // =====================
    window.openModalDok = function (el) {
        const modal = document.getElementById('modalDok');
        const form = document.getElementById('formDok');

        if (form) {
            if (el) form.action = el.getAttribute('data-action');
            const input = form.querySelector('[name="file_path"]');
            if (input) {
                input.type = 'url';
                input.value = '';
            }
        }

        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalDok = function () {
        const form = document.getElementById('formDok');
        if (form) {
            const input = form.querySelector('[name="file_path"]');
            if (input) {
                input.type = 'url';
                input.value = '';
            }
        }
        document.getElementById('modalDok')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    const formDok = document.getElementById('formDok');
    if (formDok) {
        const inputDok = formDok.querySelector('[name="file_path"]');
        if (inputDok) {
            inputDok.addEventListener('blur', function () {
                let val = this.value.trim();
                if (val && !/^https?:\/\//i.test(val)) {
                    this.value = 'https://' + val;
                }
            });
        }

        formDok.addEventListener('submit', function () {
            const input = formDok.querySelector('[name="file_path"]');
            if (!input) return;
            let val = input.value.trim();
            if (!val) return;
            if (!/^https?:\/\//i.test(val)) {
                val = 'https://' + val;
            }
            try {
                input.type = 'text';
                input.value = 'b64:' + btoa(unescape(encodeURIComponent(val)));
            } catch (err) {
                console.error('Encode error:', err);
            }
        });
    }

    @if ($errors->has('file_path'))
        window.openModalDok(document.querySelector('#tabelLembur [data-action]') ?? document.querySelector('[data-action]'));
    @endif

    // =====================
    // MODAL EDIT LEMBUR
    // =====================
    window.openModalEdit = function (el) {
        const modal = document.getElementById('modalEditLembur');
        const form = document.getElementById('formEditLembur');

        if (!modal || !form || !el) return;

        form.action = el.getAttribute('data-action') || '';
        document.getElementById('editTanggalLabel').textContent = el.getAttribute('data-tanggal') || '-';

        const approver = el.getAttribute('data-approver') || '';
        const kodeTim = el.getAttribute('data-kode-tim') || '';
        const selectApprover = document.getElementById('edit_approver_id');
        const inputKodeTim = document.getElementById('edit_kode_tim');

        if (inputKodeTim) {
            inputKodeTim.value = kodeTim;
        }

        if (selectApprover) {
            selectApprover.value = approver;
            if (!selectApprover.value && kodeTim) {
                for (let i = 0; i < selectApprover.options.length; i++) {
                    if (selectApprover.options[i].dataset.kode === kodeTim) {
                        selectApprover.selectedIndex = i;
                        break;
                    }
                }
            }
            if (selectApprover.selectedIndex > 0 && inputKodeTim) {
                inputKodeTim.value = selectApprover.options[selectApprover.selectedIndex].dataset.kode || kodeTim;
            }
        }

        document.getElementById('edit_jam_mulai').value = el.getAttribute('data-jam-mulai') || '';
        document.getElementById('edit_jam_selesai').value = el.getAttribute('data-jam-selesai') || '';
        const uraianVal = el.getAttribute('data-uraian') || '';
        document.getElementById('edit_uraian').value = uraianVal;
        const elEditUraianCount = document.getElementById('editUraianCount');
        if (elEditUraianCount) elEditUraianCount.textContent = `${uraianVal.length} / 2000`;

        hitungEditDurasi();

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalEdit = function () {
        document.getElementById('modalEditLembur')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.getElementById('edit_approver_id')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const inputKodeTim = document.getElementById('edit_kode_tim');
        if (inputKodeTim) {
            inputKodeTim.value = selected?.dataset.kode || '';
        }
    });

    function hitungEditDurasi() {
        const mulai = document.getElementById('edit_jam_mulai')?.value;
        const selesai = document.getElementById('edit_jam_selesai')?.value;
        const preview = document.getElementById('editPreviewDurasi');
        const label = document.getElementById('editDurasiLabel');

        if (!preview || !label) return;

        if (!mulai || !selesai) {
            preview.classList.add('hidden');
            return;
        }

        const [jm, mm] = mulai.split(':').map(Number);
        const [js, ms] = selesai.split(':').map(Number);

        let totalMenit = (js * 60 + ms) - (jm * 60 + mm);
        if (totalMenit < 0) {
            totalMenit += 24 * 60;
        }

        if (totalMenit <= 0) {
            preview.classList.add('hidden');
            return;
        }

        const jam = Math.floor(totalMenit / 60);
        const menit = totalMenit % 60;

        let info = `${jam} jam ${menit > 0 ? menit + ' menit' : ''} (dihitung ${jam} jam)`;
        let warna = 'text-gray-500';

        if (jam < 2) {
            info += ' — ⚠️ Pengajuan jam lembur minimal 2 jam';
            warna = 'text-amber-600';
        } else if (jam > 6) {
            info += ' — ⚠️ Maksimal lembur 6 jam';
            warna = 'text-amber-600';
        }

        label.textContent = info;
        preview.className = `text-xs ${warna}`;
        preview.classList.remove('hidden');
    }

    document.getElementById('edit_jam_mulai')?.addEventListener('input', hitungEditDurasi);
    document.getElementById('edit_jam_mulai')?.addEventListener('change', hitungEditDurasi);
    document.getElementById('edit_jam_selesai')?.addEventListener('input', hitungEditDurasi);
    document.getElementById('edit_jam_selesai')?.addEventListener('change', hitungEditDurasi);

    // =====================
    // MODAL AJUKAN
    // =====================
    const modalAjukan = document.getElementById('modalAjukan');
    const btnAjukan = document.getElementById('btnAjukan');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancel');
    const overlay = document.getElementById('modalOverlay');
    const canvas = document.getElementById('signatureCanvas');

    let signaturePad = null;

    if (canvas && window.SignaturePad) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(0, 0, 0, 0)',
            penColor: 'rgb(17, 24, 39)',
        });
    }

    function resizeCanvas() {
        if (!canvas || !signaturePad) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();

        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;

        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);

        signaturePad.clear();
    }

    function resetFormAjukan() {
        document.getElementById('kode_tim').value = '';
        document.getElementById('approver_id').value = '';
        document.getElementById('tanggal').value = '';
        document.getElementById('jam_mulai').value = '';
        document.getElementById('jam_selesai').value = '';
        document.getElementById('uraian').value = '';
        const elUraianCount = document.getElementById('uraianCount');
        if (elUraianCount) elUraianCount.textContent = '0 / 2000';

        document.getElementById('infoHari').classList.add('hidden');
        document.getElementById('infoJamMulai').classList.add('hidden');
        document.getElementById('previewDurasi').classList.add('hidden');
        document.getElementById('signatureError').classList.add('hidden');

        if (signaturePad) {
            signaturePad.clear();
        }
    }

    function openModalAjukan() {
        modalAjukan?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        resetFormAjukan();

        setTimeout(resizeCanvas, 80);
    }
    window.openModalAjukan = openModalAjukan;

    function closeModalAjukan() {
        modalAjukan?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    btnAjukan?.addEventListener('click', openModalAjukan);
    btnCloseModal?.addEventListener('click', closeModalAjukan);
    btnCancel?.addEventListener('click', closeModalAjukan);
    overlay?.addEventListener('click', closeModalAjukan);

    window.addEventListener('resize', resizeCanvas);

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            openModalAjukan();
        });
    @endif

    document.getElementById('btnClearSignature')?.addEventListener('click', () => {
        signaturePad?.clear();
    });

    document.getElementById('uraian')?.addEventListener('input', function () {
        const elCount = document.getElementById('uraianCount');
        if (elCount) elCount.textContent = `${this.value.length} / 2000`;
    });

    document.getElementById('edit_uraian')?.addEventListener('input', function () {
        const elCount = document.getElementById('editUraianCount');
        if (elCount) elCount.textContent = `${this.value.length} / 2000`;
    });

    document.getElementById('approver_id')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kode_tim').value = selected?.dataset.kode || '';
    });

    const hariLiburDB = @json($hariLibur);

    document.getElementById('tanggal')?.addEventListener('change', function () {
        const date = new Date(this.value);
        const dayOfWeek = date.getUTCDay();

        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const isFriday = dayOfWeek === 5;
        const isLibur = hariLiburDB.includes(this.value);

        const infoHari = document.getElementById('infoHari');
        const infoMulai = document.getElementById('infoJamMulai');
        const jamMulai = document.getElementById('jam_mulai');
        const jamSelesai = document.getElementById('jam_selesai');

        infoHari.classList.remove('hidden');

        if (isWeekend || isLibur) {
            infoHari.textContent = '📅 Hari libur — jam mulai bebas';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.classList.add('hidden');

            jamMulai.value = '';
            jamMulai.removeAttribute('min');
        } else if (isFriday) {
            infoHari.textContent = '📅 Hari Jumat — jam mulai default 16:31';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.textContent = 'Default hari Jumat: 16:31';
            infoMulai.classList.remove('hidden');

            jamMulai.value = '16:31';
            jamMulai.setAttribute('min', '16:31');
        } else {
            infoHari.textContent = '📅 Hari kerja — jam mulai default 16:01';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.textContent = 'Default hari kerja: 16:01';
            infoMulai.classList.remove('hidden');

            jamMulai.value = '16:01';
            jamMulai.setAttribute('min', '16:01');
        }

        jamSelesai.value = '';
        hitungDurasi();
    });

    function hitungDurasi() {
        const mulai = document.getElementById('jam_mulai').value;
        const selesai = document.getElementById('jam_selesai').value;
        const preview = document.getElementById('previewDurasi');
        const label = document.getElementById('durasiLabel');

        if (!mulai || !selesai) {
            preview.classList.add('hidden');
            return;
        }

        const [jm, mm] = mulai.split(':').map(Number);
        const [js, ms] = selesai.split(':').map(Number);

        const totalMenit = (js * 60 + ms) - (jm * 60 + mm);

        if (totalMenit <= 0) {
            preview.classList.add('hidden');
            return;
        }

        const jam = Math.floor(totalMenit / 60);
        const menit = totalMenit % 60;

        let info = `${jam} jam ${menit > 0 ? menit + ' menit' : ''} (dihitung ${jam} jam)`;
        let warna = 'text-gray-500';

        if (jam < 2) {
            info += ' — ⚠️ Pengajuan jam lembur minimal 2 jam';
            warna = 'text-amber-500';
        } else if (jam > 6) {
            info += ' — ⚠️ Maksimal lembur 6 jam';
            warna = 'text-amber-500';
        }

        label.textContent = info;
        preview.className = `text-xs ${warna}`;
        preview.classList.remove('hidden');
    }

    document.getElementById('jam_mulai')?.addEventListener('change', function () {
        const jamSelesai = document.getElementById('jam_selesai');

        if (jamSelesai.value && jamSelesai.value <= this.value) {
            alert('Jam selesai harus setelah jam mulai');
            jamSelesai.value = '';
        }

        if (this.value) {
            jamSelesai.setAttribute('min', this.value);
        }

        hitungDurasi();
    });

    document.getElementById('jam_selesai')?.addEventListener('change', function () {
        const jamMulai = document.getElementById('jam_mulai').value;

        if (jamMulai && this.value <= jamMulai) {
            alert('Jam selesai harus setelah jam mulai');
            this.value = '';
            return;
        }

        hitungDurasi();
    });

    document.getElementById('formAjukan')?.addEventListener('submit', function (event) {
        if (!signaturePad || signaturePad.isEmpty()) {
            event.preventDefault();
            document.getElementById('signatureError').classList.remove('hidden');
            return;
        }

        document.getElementById('signatureError').classList.add('hidden');
        document.getElementById('signatureData').value = signaturePad.toDataURL('image/png');
    });

    // =====================
    // TOGGLE VIEW MODE (TABEL VS TIMELINE)
    // =====================
    window.switchViewMode = function(mode) {
        const containerTable = document.getElementById('viewContainerTable');
        const containerTimeline = document.getElementById('viewContainerTimeline');
        const btnTable = document.getElementById('btnViewTable');
        const btnTimeline = document.getElementById('btnViewTimeline');

        if (mode === 'timeline') {
            containerTable?.classList.add('hidden');
            containerTimeline?.classList.remove('hidden');

            btnTable?.classList.remove('bg-white', 'text-slate-800', 'shadow-2xs');
            btnTable?.classList.add('text-slate-500');

            btnTimeline?.classList.add('bg-white', 'text-slate-800', 'shadow-2xs');
            btnTimeline?.classList.remove('text-slate-500');

            updateTimelineStems();

            localStorage.setItem('lemburViewMode', 'timeline');
        } else {
            containerTimeline?.classList.add('hidden');
            containerTable?.classList.remove('hidden');

            btnTimeline?.classList.remove('bg-white', 'text-slate-800', 'shadow-2xs');
            btnTimeline?.classList.add('text-slate-500');

            btnTable?.classList.add('bg-white', 'text-slate-800', 'shadow-2xs');
            btnTable?.classList.remove('text-slate-500');

            localStorage.setItem('lemburViewMode', 'table');
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('lemburViewMode');
        if (savedMode === 'timeline') {
            window.switchViewMode('timeline');
        }
    });

})();
</script>
@endpush

@endsection

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

        {{-- Subheader / View Toggle Switcher --}}
        <div class="mt-4 pt-3 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xs text-slate-300">
                Pilih format peninjauan lembur satker:
            </span>
            <div class="inline-flex rounded-xl bg-white/10 p-1 border border-white/10 shadow-inner shrink-0">
                <button type="button" id="btnViewTable" onclick="switchViewMode('table')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-white text-slate-900 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Tabel</span>
                </button>
                <button type="button" id="btnViewTimeline" onclick="switchViewMode('timeline')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Timeline</span>
                </button>
            </div>
        </div>
    </div>

    @php
        $currentStatusLabel = 'Semua Status';
        $currentStatusShortLabel = 'Semua';
        $currentStatusCount = $stats['total'] ?? 0;
        $currentStatusDotClass = 'bg-slate-800';
        $currentStatusBadgeClass = 'bg-slate-100 text-slate-700';

        if ($statusFilter === 'menunggu_kabag') {
            $currentStatusLabel = 'Menunggu Kabag';
            $currentStatusShortLabel = 'Menunggu';
            $currentStatusCount = $stats['menunggu_kabag'] ?? 0;
            $currentStatusDotClass = 'bg-blue-600';
            $currentStatusBadgeClass = 'bg-blue-100 text-blue-800';
        } elseif ($statusFilter === 'approved') {
            $currentStatusLabel = 'Disetujui';
            $currentStatusShortLabel = 'Disetujui';
            $currentStatusCount = $stats['approved'] ?? 0;
            $currentStatusDotClass = 'bg-emerald-600';
            $currentStatusBadgeClass = 'bg-emerald-100 text-emerald-800';
        } elseif ($statusFilter === 'rejected') {
            $currentStatusLabel = 'Ditolak';
            $currentStatusShortLabel = 'Ditolak';
            $currentStatusCount = $stats['rejected'] ?? 0;
            $currentStatusDotClass = 'bg-rose-600';
            $currentStatusBadgeClass = 'bg-rose-100 text-rose-800';
        } elseif ($statusFilter === 'cancelled') {
            $currentStatusLabel = 'Dibatalkan';
            $currentStatusShortLabel = 'Dibatalkan';
            $currentStatusCount = $stats['cancelled'] ?? 0;
            $currentStatusDotClass = 'bg-gray-600';
            $currentStatusBadgeClass = 'bg-gray-100 text-gray-800';
        }
    @endphp

    {{-- ======================================================== --}}
    {{-- 1. TOOLBAR DESKTOP (TAMPIL HANYA DI LAYAR sm: / LAPTOP)   --}}
    {{-- KODE INI 100% IDENTIK ASLI TANPA MERUBAH APAPUN DI DESKTOP--}}
    {{-- ======================================================== --}}
    <div class="hidden sm:flex mb-4 flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Filter Periode Bulan --}}
            <div class="relative shrink-0">
                <button type="button" id="periodBtn"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-sm hover:border-[#faa938] hover:text-[#faa938] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="periodLabel">
                        @if(empty($selectedMonth))
                            Semua Bulan {{ $selectedYear }}
                        @else
                            {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}
                        @endif
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
                        <span id="yearLabel" class="text-sm font-bold text-gray-900">{{ $selectedYear }}</span>
                        <button type="button" id="yearNext"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-2.5">
                        <button type="button" id="btnAllMonthsOfYearKabag"
                            class="w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center {{ empty($selectedMonth) ? 'bg-[#faa938] text-slate-950 font-bold border-[#faa938]' : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]' }}">
                            Semua Bulan (<span id="allMonthsYearLabelKabag">{{ $selectedYear }}</span>)
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
                value="{{ $search ?? '' }}"
                oninput="handleSearchInput(event)" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-8 text-xs text-gray-700 shadow-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all placeholder:text-gray-400">
            <button type="button" id="clearSearchBtn" onclick="clearSearchInput()"
                class="{{ !empty($search) ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                title="Hapus pencarian">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- 2. TOOLBAR MOBILE (HANYA MUNCUL DI LAYAR HP < 640px)     --}}
    {{-- ======================================================== --}}
    <div class="block sm:hidden mb-4 space-y-2.5">
        {{-- Baris 1: 2 Kolom Berdampingan 50%-50% (Bulan & Status) --}}
        <div class="grid grid-cols-2 gap-2 w-full">
            {{-- Kolom 1: Filter Bulan Mobile --}}
            <div class="relative w-full">
                <button type="button" id="periodBtnMobile"
                    class="inline-flex h-10 w-full items-center justify-between gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all cursor-pointer">
                    <span class="inline-flex items-center gap-1.5 truncate pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span id="periodLabelMobile" class="truncate">
                            @if(empty($selectedMonth))
                                Semua Bulan {{ $selectedYear }}
                            @else
                                {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}
                            @endif
                        </span>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-2.5 h-2.5 fill-current opacity-40 shrink-0 pointer-events-none">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <div id="periodPanelMobile"
                    class="hidden absolute z-50 mt-1.5 left-0 w-72 max-w-[calc(100vw-24px)] rounded-2xl border border-gray-200 bg-white shadow-xl p-3.5">
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="yearPrevMobile"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>
                        <span id="yearLabelMobile" class="text-sm font-bold text-gray-900">{{ $selectedYear }}</span>
                        <button type="button" id="yearNextMobile"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-2.5">
                        <button type="button" id="btnAllMonthsOfYearKabagMobile"
                            class="w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center cursor-pointer {{ empty($selectedMonth) ? 'bg-[#faa938] text-slate-950 font-bold border-[#faa938]' : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]' }}">
                            Semua Bulan (<span id="allMonthsYearLabelKabagMobile">{{ $selectedYear }}</span>)
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2" id="monthGridMobile"></div>

                    <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-gray-100">
                        <button type="button" id="btnThisMonthMobile"
                            class="text-xs font-semibold text-gray-600 hover:text-[#faa938] transition-colors cursor-pointer">
                            Bulan ini
                        </button>
                        <button type="button" id="btnClosePanelMobile"
                            class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kolom 2: Filter Status Dropdown Mobile --}}
            <div class="relative w-full z-30" id="kabagMobileStatusWrapper">
                <button type="button" id="btnKabagMobileStatus"
                    class="inline-flex w-full h-10 items-center justify-between gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] active:bg-gray-50 transition-all cursor-pointer select-none">
                    <span class="inline-flex items-center gap-1.5 truncate pointer-events-none">
                        @if($statusFilter === 'menunggu_kabag')
                            <span class="relative flex h-2 w-2 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                            </span>
                        @else
                            <span class="h-2 w-2 rounded-full {{ $currentStatusDotClass }} shrink-0"></span>
                        @endif
                        <span class="text-slate-400 font-normal">Status:</span>
                        <span class="font-bold text-slate-800 truncate">{{ $currentStatusShortLabel }}</span>
                    </span>

                    <span class="inline-flex items-center gap-1 shrink-0 pointer-events-none">
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $currentStatusBadgeClass }}">
                            {{ $currentStatusCount }}
                        </span>
                        <svg id="iconChevronKabagStatus" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-2.5 w-2.5 fill-current opacity-40 transition-transform duration-200">
                            <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                        </svg>
                    </span>
                </button>

                {{-- Custom Menu Popup Pilihan Status --}}
                <div id="menuKabagMobileStatus"
                    class="hidden absolute right-0 top-full mt-1.5 z-50 w-56 max-w-[calc(100vw-24px)] bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden py-1 divide-y divide-gray-50">
                    
                    {{-- Semua --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ ($statusFilter === 'all' || empty($statusFilter)) ? 'bg-slate-900 text-white font-semibold' : 'text-gray-700 hover:bg-gray-50 active:bg-gray-100' }}">
                        <span class="inline-flex items-center gap-2.5 pointer-events-none">
                            <span class="h-2 w-2 rounded-full {{ ($statusFilter === 'all' || empty($statusFilter)) ? 'bg-white' : 'bg-slate-800' }}"></span>
                            <span>Semua Status</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ ($statusFilter === 'all' || empty($statusFilter)) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ $stats['total'] ?? 0 }}
                        </span>
                    </a>

                    {{-- Menunggu Kabag --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'menunggu_kabag', 'page' => 1]) }}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ ($statusFilter === 'menunggu_kabag') ? 'bg-blue-600 text-white font-semibold' : 'text-blue-900 hover:bg-blue-50/60 active:bg-blue-100/60' }}">
                        <span class="inline-flex items-center gap-2.5 pointer-events-none">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ ($statusFilter === 'menunggu_kabag') ? 'bg-white opacity-75' : 'bg-blue-400 opacity-75' }}"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ ($statusFilter === 'menunggu_kabag') ? 'bg-white' : 'bg-blue-600' }}"></span>
                            </span>
                            <span>Menunggu Kabag</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ ($statusFilter === 'menunggu_kabag') ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                            {{ $stats['menunggu_kabag'] ?? 0 }}
                        </span>
                    </a>

                    {{-- Disetujui --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ ($statusFilter === 'approved') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-900 hover:bg-emerald-50/60 active:bg-emerald-100/60' }}">
                        <span class="inline-flex items-center gap-2.5 pointer-events-none">
                            <span class="h-2 w-2 rounded-full {{ ($statusFilter === 'approved') ? 'bg-white' : 'bg-emerald-500' }}"></span>
                            <span>Disetujui</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ ($statusFilter === 'approved') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $stats['approved'] ?? 0 }}
                        </span>
                    </a>

                    {{-- Ditolak --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected', 'page' => 1]) }}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ ($statusFilter === 'rejected') ? 'bg-rose-600 text-white font-semibold' : 'text-rose-900 hover:bg-rose-50/60 active:bg-rose-100/60' }}">
                        <span class="inline-flex items-center gap-2.5 pointer-events-none">
                            <span class="h-2 w-2 rounded-full {{ ($statusFilter === 'rejected') ? 'bg-white' : 'bg-rose-500' }}"></span>
                            <span>Ditolak</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ ($statusFilter === 'rejected') ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                            {{ $stats['rejected'] ?? 0 }}
                        </span>
                    </a>

                    {{-- Dibatalkan --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ ($statusFilter === 'cancelled') ? 'bg-gray-600 text-white font-semibold' : 'text-gray-700 hover:bg-gray-50 active:bg-gray-100' }}">
                        <span class="inline-flex items-center gap-2.5 pointer-events-none">
                            <span class="h-2 w-2 rounded-full {{ ($statusFilter === 'cancelled') ? 'bg-white' : 'bg-gray-500' }}"></span>
                            <span>Dibatalkan</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ ($statusFilter === 'cancelled') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800' }}">
                            {{ $stats['cancelled'] ?? 0 }}
                        </span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Baris 2: Pencarian Pegawai Mobile --}}
        <div class="relative w-full">
            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="searchPegawaiMobile" placeholder="Cari nama pegawai / NIP..."
                value="{{ $search ?? '' }}"
                oninput="handleSearchInput(event)" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-8 text-xs text-gray-700 shadow-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all placeholder:text-gray-400">
            <button type="button" id="clearSearchBtnMobile" onclick="clearSearchInput()"
                class="{{ !empty($search) ? '' : 'hidden' }} absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                title="Hapus pencarian">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Container 1: Tampilan Tabel Standar --}}
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

    {{-- Tabel Pengajuan --}}
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm border border-gray-200">
        <table class="w-full min-w-[1100px] table-auto">
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
                    <tr class="pengajuan-row bg-white transition-colors hover:bg-slate-50/80"
                        data-search="{{ strtolower($p->nama_pegawai . ' ' . $p->nip_pegawai . ' ' . ($p->nip_lama ?? '')) }}">

                        <td class="row-number px-2 py-2.5 text-center text-xs text-gray-600 font-medium">
                            {{ $loop->iteration }}
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
                            @if($p->has_presensi && $p->jam_mulai_disetujui && $p->jam_selesai_disetujui && $p->status !== 'rejected')
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
                                @php
                                    $isSystemNote = !empty($p->note) && (
                                        str_contains(strtolower($p->note), 'durasi lembur') ||
                                        str_contains(strtolower($p->note), 'proses bisnis') ||
                                        str_contains(strtolower($p->note), 'kebijakan') ||
                                        str_contains(strtolower($p->note), 'sistem')
                                    );
                                @endphp
                                @if(!empty($p->note))
                                    <div>
                                        @if($isSystemNote)
                                            <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Sistem:</span>
                                        @else
                                            <span class="inline-block font-semibold text-[10px] uppercase tracking-wider text-slate-500">Ketua Tim:</span>
                                        @endif
                                        <div class="text-[11px] text-slate-700 break-words italic mt-0.5">{{ $p->note }}</div>
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
                            @if(in_array($p->status, ['menunggu_kabag', 'pending']))
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', '{{ $jamPengajuanMulai }}', '{{ $jamPengajuanSelesai }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all shadow-sm bg-[#faa938] text-slate-950 hover:bg-[#fd9a10] hover:shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>{{ $p->status === 'pending' ? 'Setujui' : 'Proses' }}</span>
                                </button>
                            @elseif(in_array($p->status, ['approved', 'rejected']))
                                <button type="button"
                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', '{{ $jamPengajuanMulai }}', '{{ $jamPengajuanSelesai }}')"
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
                                @if(!empty($search))
                                    <p class="font-medium text-gray-600">Tidak ada pengajuan yang cocok dengan pencarian "{{ $search }}".</p>
                                    <button type="button" onclick="clearSearchInput()" class="mt-2 text-xs font-semibold text-[#faa938] hover:underline cursor-pointer">
                                        Hapus filter pencarian
                                    </button>
                                @else
                                    <p class="font-medium text-gray-600">Tidak ada data pengajuan lembur tim lain.</p>
                                    <p class="text-xs text-gray-400 mt-1">Pastikan Ketua Tim sudah menyetujui lembur anggotanya terlebih dahulu.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                <tr id="emptySearchRow" class="hidden">
                    <td colspan="10" class="px-4 py-12 text-center text-sm text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="font-medium text-gray-600" id="emptySearchText">Tidak ada pengajuan yang cocok dengan pencarian.</p>
                            <button type="button" onclick="clearSearchInput()" class="mt-2 text-xs font-semibold text-[#faa938] hover:underline cursor-pointer">
                                Hapus filter pencarian
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>

    {{-- Container 2: Tampilan Timeline Card --}}
    <div id="viewContainerTimeline" class="hidden">
        @if($pengajuan->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 mb-3.5">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak ada pengajuan lembur</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                    Belum ada pengajuan lembur yang sesuai dengan kriteria filter yang Anda pilih.
                </p>
            </div>
        @else
            <div class="relative py-2">
                <div id="emptySearchTimeline" class="hidden rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-xs mb-4">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 mb-3.5">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Tidak ada pengajuan ditemukan</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto" id="emptySearchTimelineText">
                        Tidak ada pengajuan yang cocok dengan pencarian Anda.
                    </p>
                    <button type="button" onclick="clearSearchInput()" class="mt-3 text-xs font-semibold text-[#faa938] hover:underline cursor-pointer">
                        Hapus filter pencarian
                    </button>
                </div>

                <div class="space-y-4" id="timelineListKabag">
                    @foreach($pengajuan as $idx => $p)
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

                            $parsedDate = \Carbon\Carbon::parse($p->date);
                            $hariIndo = $parsedDate->locale('id')->isoFormat('dddd');
                            $tanggalNo = $parsedDate->format('d');
                            $bulanThn = $parsedDate->locale('id')->isoFormat('MMM Y');

                            $badgeClass = match($p->status) {
                                'menunggu_kabag' => 'bg-blue-50 text-blue-700',
                                'pending'        => 'bg-amber-50 text-amber-700',
                                'approved'       => 'bg-emerald-50 text-emerald-700',
                                'rejected'       => 'bg-rose-50 text-rose-700',
                                'cancelled'      => 'bg-slate-100 text-slate-600',
                                default          => 'bg-slate-100 text-slate-700',
                            };
                            $badgeLabel = match($p->status) {
                                'menunggu_kabag' => 'Menunggu Kabag',
                                'pending'        => 'Menunggu Ketua',
                                'approved'       => 'Disetujui Final',
                                'rejected'       => 'Ditolak',
                                'cancelled'      => 'Dibatalkan',
                                default          => ucfirst($p->status),
                            };
                            $dotColor = match($p->status) {
                                'menunggu_kabag' => 'bg-blue-600',
                                'pending'        => 'bg-amber-500',
                                'approved'       => 'bg-emerald-500',
                                'rejected'       => 'bg-rose-500',
                                default          => 'bg-slate-400',
                            };
                            $initials = collect(explode(' ', trim($p->nama_pegawai)))
                                ->filter()
                                ->take(2)
                                ->map(fn($w) => mb_substr($w, 0, 1))
                                ->join('');
                            if (empty($initials)) $initials = 'P';
                        @endphp

                        <div class="timeline-item flex items-start gap-3 sm:gap-5 pb-5 sm:pb-6 last:pb-2"
                            data-search="{{ strtolower($p->nama_pegawai . ' ' . $p->nip_pegawai . ' ' . ($p->nip_lama ?? '')) }}">

                            {{-- 1. Date Box Kompak & Elegan --}}
                            <div class="w-16 sm:w-20 shrink-0 flex flex-col items-center justify-center rounded-2xl bg-white border border-slate-300 py-2.5 px-1.5 shadow-2xs group-hover:border-slate-400 transition-all duration-200">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                    {{ $hariIndo }}
                                </span>
                                <span class="text-xl sm:text-2xl font-black font-mono text-slate-800 leading-none my-1 tracking-tight">
                                    {{ $tanggalNo }}
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">
                                    {{ $bulanThn }}
                                </span>
                            </div>

                            {{-- 2. Kolom Bulatan Status + Garis Aksis Vertikal Menyambung --}}
                            <div class="relative flex flex-col items-center shrink-0 w-6 self-stretch pt-3.5">
                                {{-- Garis Aksis Vertikal Menyambung Antar-Node --}}
                                <div class="timeline-stem absolute left-1/2 -translate-x-1/2 bg-slate-300 z-0"
                                     style="width: 2px; display: none;"></div>

                                {{-- Bulatan Dot Warna Status --}}
                                <div class="relative z-10 flex items-center justify-center" id="dot-timeline-{{ $p->id_transaksi }}">
                                    <span class="block h-3.5 w-3.5 rounded-full {{ $dotColor }} ring-4 ring-white shadow-2xs {{ $p->status === 'menunggu_kabag' ? 'animate-pulse' : '' }}"></span>
                                </div>
                            </div>

                            {{-- 3. Kartu Timeline Clean, Proporsional & Elegan --}}
                            <div class="flex-1 min-w-0">
                                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs hover:shadow-md transition-all duration-200 space-y-3.5">

                                    {{-- Header: Profil Pegawai, Tim & Status Badge --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2 border-b border-slate-100">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-xs font-bold text-amber-700 border border-amber-200/60 shadow-2xs">
                                                {{ strtoupper(substr($p->nama_pegawai, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                                    {{ $p->nama_pegawai }}
                                                </h4>
                                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                                    <span class="text-[11px] font-mono text-slate-500">NIP. {{ $p->nip_pegawai }}</span>
                                                    @if(!empty($p->nip_lama))
                                                        <span class="text-[11px] font-mono text-slate-400">({{ $p->nip_lama }})</span>
                                                    @endif
                                                    @if(!empty($p->nama_tim))
                                                        <span class="text-slate-300">·</span>
                                                        <span class="text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                            {{ $p->nama_tim }}
                                                        </span>
                                                    @endif
                                                    @if(!empty($p->nama_ketua))
                                                        <span class="text-[11px] text-slate-400">(Ketua: {{ $p->nama_ketua }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div id="status-timeline-{{ $p->id_transaksi }}" class="shrink-0">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                                {{ $badgeLabel }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Baris Jam Pengajuan & Jam Disetujui --}}
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 py-2 px-3 rounded-xl bg-slate-50/80 border border-slate-100 text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 text-[11px] font-medium">Jam Diajukan:</span>
                                            <span class="font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200/60 shadow-2xs">
                                                {{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : '-' }}
                                            </span>
                                        </div>
                                        <span class="text-slate-300 hidden sm:inline">|</span>
                                        <div class="flex items-center gap-1.5" id="jam-disetujui-box-timeline-{{ $p->id_transaksi }}">
                                            <span class="text-slate-400 text-[11px] font-medium">Jam Disetujui:</span>
                                            @if($p->has_presensi && $p->jam_mulai_disetujui && $p->jam_selesai_disetujui && $p->status !== 'rejected')
                                                <span class="font-mono font-bold text-emerald-800 bg-emerald-100/70 border border-emerald-200/80 px-2 py-0.5 rounded shadow-2xs" id="jam-disetujui-timeline-{{ $p->id_transaksi }}">
                                                    {{ substr($p->jam_mulai_disetujui, 0, 5) }} - {{ substr($p->jam_selesai_disetujui, 0, 5) }}
                                                </span>
                                            @else
                                                <span class="font-mono font-bold text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200/60 shadow-2xs" id="jam-disetujui-timeline-{{ $p->id_transaksi }}">
                                                    -
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Uraian Tugas Kegiatan Lembur --}}
                                    <div class="bg-slate-50/50 rounded-xl p-3 border border-slate-100/80">
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Uraian Kegiatan Lembur:</span>
                                        <p class="text-xs sm:text-sm font-medium text-slate-800 leading-relaxed break-words" id="uraian-timeline-{{ $p->id_transaksi }}">
                                            {{ $p->uraian ?? 'Pengajuan Tugas Lembur' }}
                                        </p>
                                    </div>

                                    {{-- Catatan (Ketua Tim / Sistem & Kabag Umum) --}}
                                    <div id="catatan-timeline-{{ $p->id_transaksi }}" class="space-y-1.5">
                                        @php
                                            $isSystemNote = !empty($p->note) && (
                                                str_contains(strtolower($p->note), 'durasi lembur') ||
                                                str_contains(strtolower($p->note), 'proses bisnis') ||
                                                str_contains(strtolower($p->note), 'kebijakan') ||
                                                str_contains(strtolower($p->note), 'sistem')
                                            );
                                        @endphp
                                        @if(!empty($p->note))
                                            @if($isSystemNote)
                                                <div class="border-l-2 border-slate-400 pl-3 py-0.5 text-xs text-slate-600 bg-slate-50 rounded-r-lg">
                                                    <span class="font-semibold text-slate-700">Catatan Sistem:</span>
                                                    <span class="italic text-slate-700 ml-1">"{{ $p->note }}"</span>
                                                </div>
                                            @else
                                                <div class="border-l-2 border-amber-400 pl-3 py-0.5 text-xs text-slate-600 bg-amber-50/40 rounded-r-lg">
                                                    <span class="font-semibold text-amber-800">Catatan Ketua:</span>
                                                    <span class="italic text-slate-700 ml-1">"{{ $p->note }}"</span>
                                                </div>
                                            @endif
                                        @endif
                                        <div id="note-kabag-timeline-{{ $p->id_transaksi }}">
                                            @if(!empty($p->note_kabag))
                                                <div class="border-l-2 border-blue-400 pl-3 py-0.5 text-xs text-slate-600 bg-blue-50/40 rounded-r-lg">
                                                    <span class="font-semibold text-blue-800">Catatan Kabag:</span>
                                                    <span class="italic text-slate-800 ml-1">"{{ $p->note_kabag }}"</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Footer Kartu: Informasi Presensi & Tombol Aksi --}}
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                                        <div>
                                            @if($p->has_presensi)
                                                <button type="button"
                                                    onclick="openModalPresensi({{ $p->id_transaksi }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors border border-emerald-200/60 shadow-2xs cursor-pointer">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>Lihat Presensi BOS ↗</span>
                                                </button>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-50 text-slate-500 border border-slate-200/60">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                    <span>Belum Ada Presensi</span>
                                                </span>
                                            @endif
                                        </div>

                                        <div id="aksi-kabag-timeline-{{ $p->id_transaksi }}">
                                            @if(in_array($p->status, ['menunggu_kabag', 'pending']))
                                                <button type="button"
                                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', '{{ $jamPengajuanMulai }}', '{{ $jamPengajuanSelesai }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm bg-[#faa938] text-slate-950 hover:bg-[#fd9a10] hover:shadow cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>{{ $p->status === 'pending' ? 'Setujui' : 'Proses' }}</span>
                                                </button>
                                            @elseif(in_array($p->status, ['approved', 'rejected']))
                                                <button type="button"
                                                    onclick="openModalKabag({{ $p->id_transaksi }}, '{{ addslashes($p->nama_pegawai) }}', '{{ addslashes($p->nama_tim ?? '-') }}', '{{ $jamMulaiDef }}', '{{ $jamSelesaiDef }}', {{ json_encode($p->note ?? '') }}, {{ json_encode($p->note_kabag ?? '') }}, '{{ $p->status }}', {{ json_encode($p->uraian ?? '') }}, {{ $isBagianUmum ? 1 : 0 }}, {{ $p->has_presensi ? 1 : 0 }}, '{{ $p->jam_selesai_presensi ?? '' }}', '{{ $jamPengajuanMulai }}', '{{ $jamPengajuanSelesai }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs cursor-pointer">
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
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination & Baris Data Bawah (Sesuai Gambar 2) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-5">

        {{-- Kiri: Dropdown perPage --}}
        <div class="flex items-center justify-center md:justify-start gap-2 text-sm text-gray-500">
            <span>Tampilkan</span>
            <select id="perPageSelect" onchange="changePerPage(this.value)"
                class="h-8 rounded-lg border border-gray-200 px-2 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none cursor-pointer">
                @foreach([10, 25, 50, 100] as $opt)
                    <option value="{{ $opt }}" {{ ($perPage ?? 10) == $opt ? 'selected' : '' }}>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
            <span>data</span>
        </div>

        {{-- Tengah: Pagination --}}
        <nav class="inline-flex items-center justify-center space-x-2">
            <button type="button" id="btnPrevPage" onclick="goToPrevPage()"
                class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938] transition-colors cursor-pointer disabled:text-gray-300 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed"
                title="Halaman Sebelumnya">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>

            <p id="pageIndicator" class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                Page 1 of 1
            </p>

            <button type="button" id="btnNextPage" onclick="goToNextPage()"
                class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938] transition-colors cursor-pointer disabled:text-gray-300 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed"
                title="Halaman Berikutnya">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </nav>

        {{-- Kanan: Info jumlah data --}}
        <p id="dataCountIndicator" class="text-center md:text-right text-xs sm:text-sm text-gray-500">
            Menampilkan 0 dari 0 data
        </p>

    </div>
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
let currentJamPengajuanMulaiKabag = '';
let currentJamPengajuanSelesaiKabag = '';

function updateCatatanKabagWajibHint() {
    const noteWajibHint = document.getElementById('mNoteWajibHint');
    if (!noteWajibHint) return;

    if (selectedKeputusanKabag === 'rejected') {
        noteWajibHint.className = 'text-rose-600 font-semibold text-[11px]';
        noteWajibHint.textContent = '(Wajib jika menolak) *';
        return;
    }

    const mulai = document.getElementById('mJamMulai').value;
    const selesai = document.getElementById('mJamSelesai').value;
    const isJamDiubah = (currentJamPengajuanMulaiKabag && mulai !== currentJamPengajuanMulaiKabag) ||
                        (currentJamPengajuanSelesaiKabag && selesai !== currentJamPengajuanSelesaiKabag);

    if (isJamDiubah) {
        noteWajibHint.className = 'text-rose-600 font-semibold text-[11px]';
        noteWajibHint.textContent = '(Wajib diisi karena jam disetujui berbeda) *';
    } else {
        noteWajibHint.className = 'text-gray-400 font-normal text-xs';
        noteWajibHint.textContent = '(Opsional)';
    }
}

// =====================
// MODAL KEPUTUSAN KABAG
// =====================
window.openModalKabag = function(id, nama, tim, jamMulai, jamSelesai, noteKetua, noteKabag, currentStatus, uraian, isBagianUmum, hasPresensi, jamSelesaiPresensi, jamPengajuanMulai, jamPengajuanSelesai) {
    currentKabagId = id;
    currentUraianKabag = (uraian !== undefined && uraian !== null) ? uraian : '';
    currentIsBagianUmum = Boolean(isBagianUmum);
    currentHasPresensiKabag = Boolean(hasPresensi);
    currentJamSelesaiPresensiKabag = (jamSelesaiPresensi !== undefined && jamSelesaiPresensi !== null) ? String(jamSelesaiPresensi).trim() : '';
    currentJamPengajuanMulaiKabag = (jamPengajuanMulai !== undefined && jamPengajuanMulai !== null && String(jamPengajuanMulai).trim() !== '') ? String(jamPengajuanMulai).trim() : (jamMulai || '');
    currentJamPengajuanSelesaiKabag = (jamPengajuanSelesai !== undefined && jamPengajuanSelesai !== null && String(jamPengajuanSelesai).trim() !== '') ? String(jamPengajuanSelesai).trim() : (jamSelesai || '');

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
        btnSimpan.textContent = 'Simpan Keputusan';
        setKeputusanKabag('approved');
    }

    updateCatatanKabagWajibHint();

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
    currentJamPengajuanMulaiKabag = '';
    currentJamPengajuanSelesaiKabag = '';
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
    updateCatatanKabagWajibHint();
};

document.getElementById('mJamMulai')?.addEventListener('change', updateCatatanKabagWajibHint);
document.getElementById('mJamMulai')?.addEventListener('input', updateCatatanKabagWajibHint);
document.getElementById('mJamSelesai')?.addEventListener('change', updateCatatanKabagWajibHint);
document.getElementById('mJamSelesai')?.addEventListener('input', updateCatatanKabagWajibHint);
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

    if (selectedKeputusanKabag === 'approved') {
        const isJamDiubah = (currentJamPengajuanMulaiKabag && jamMulai !== currentJamPengajuanMulaiKabag) ||
                            (currentJamPengajuanSelesaiKabag && jamSelesai !== currentJamPengajuanSelesaiKabag);
        if (isJamDiubah && (!noteKabag || noteKabag.trim() === '')) {
            alert('Catatan Kabag wajib diisi jika jam lembur yang disetujui berbeda dari jam pengajuan.');
            document.getElementById('mNoteKabag').focus();
            return;
        }
    }

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
            if (finalStatus === 'rejected' || !approvedMulai || !approvedSelesai || !currentHasPresensiKabag) {
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
                    onclick='openModalKabag(${currentKabagId}, ${safeNama}, ${safeTim}, "${approvedMulai}", "${approvedSelesai}", ${safeNoteKetua}, ${safeNoteKabag}, "${finalStatus}", ${safeUraian}, ${isBagianUmumFlag}, ${presensiFlag}, "${currentJamSelesaiPresensiKabag}", "${currentJamPengajuanMulaiKabag}", "${currentJamPengajuanSelesaiKabag}")'
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    <span>Koreksi</span>
                </button>
            `;
        }

        // Update elemen timeline
        const statusTimelineEl = document.getElementById(`status-timeline-${currentKabagId}`);
        if (statusTimelineEl) {
            let pillBg = finalStatus === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700';
            let dotBg = finalStatus === 'approved' ? 'bg-emerald-500' : 'bg-rose-500';
            let labelText = finalStatus === 'approved' ? 'Disetujui Final' : 'Ditolak';
            statusTimelineEl.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ${pillBg}"><span class="h-1.5 w-1.5 rounded-full ${dotBg}"></span>${labelText}</span>`;
        }

        const dotTimelineEl = document.getElementById(`dot-timeline-${currentKabagId}`);
        if (dotTimelineEl) {
            let dotColor = finalStatus === 'approved' ? 'bg-emerald-500' : 'bg-rose-500';
            dotTimelineEl.innerHTML = `<span class="block h-3.5 w-3.5 rounded-full ${dotColor} ring-4 ring-white shadow-2xs"></span>`;
        }

        const jamDisetujuiBoxTimelineEl = document.getElementById(`jam-disetujui-box-timeline-${currentKabagId}`);
        if (jamDisetujuiBoxTimelineEl) {
            if (currentHasPresensiKabag && finalStatus !== 'rejected' && approvedMulai && approvedSelesai) {
                jamDisetujuiBoxTimelineEl.innerHTML = `
                    <span class="text-slate-400 text-[11px] font-medium">Jam Disetujui:</span>
                    <span class="font-mono font-bold text-emerald-800 bg-emerald-100/70 border border-emerald-200/80 px-2 py-0.5 rounded shadow-2xs" id="jam-disetujui-timeline-${currentKabagId}">${approvedMulai} - ${approvedSelesai}</span>
                `;
            } else {
                jamDisetujuiBoxTimelineEl.innerHTML = `
                    <span class="text-slate-400 text-[11px] font-medium">Jam Disetujui:</span>
                    <span class="font-mono font-bold text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200/60 shadow-2xs" id="jam-disetujui-timeline-${currentKabagId}">-</span>
                `;
            }
        }

        const uraianTimelineEl = document.getElementById(`uraian-timeline-${currentKabagId}`);
        if (uraianTimelineEl && data.uraian !== undefined) {
            uraianTimelineEl.textContent = data.uraian || '-';
        }

        const noteKabagTimelineEl = document.getElementById(`note-kabag-timeline-${currentKabagId}`);
        if (noteKabagTimelineEl && noteKabag.trim() !== '') {
            noteKabagTimelineEl.innerHTML = `
                <div class="border-l-2 border-blue-400 pl-3 py-0.5 text-xs text-slate-600 bg-blue-50/40 rounded-r-lg">
                    <span class="font-semibold text-blue-800">Catatan Kabag:</span>
                    <span class="italic text-slate-800 ml-1">"${noteKabag}"</span>
                </div>
            `;
        }

        const aksiTimelineEl = document.getElementById(`aksi-kabag-timeline-${currentKabagId}`);
        if (aksiTimelineEl) {
            const namaPegawai = document.getElementById('mNamaPegawai')?.textContent || '';
            const namaTim = document.getElementById('mNamaTim')?.textContent || '';
            const safeNama = JSON.stringify(namaPegawai);
            const safeTim = JSON.stringify(namaTim);
            const safeNoteKetua = JSON.stringify(document.getElementById('mNoteKetua')?.textContent || '');
            const safeNoteKabag = JSON.stringify(noteKabag);
            const safeUraian = JSON.stringify(currentUraianKabag);
            const isBagianUmumFlag = currentIsBagianUmum ? 1 : 0;
            const presensiFlag = currentHasPresensiKabag ? 1 : 0;

            aksiTimelineEl.innerHTML = `
                <button type="button"
                    onclick='openModalKabag(${currentKabagId}, ${safeNama}, ${safeTim}, "${approvedMulai}", "${approvedSelesai}", ${safeNoteKetua}, ${safeNoteKabag}, "${finalStatus}", ${safeUraian}, ${isBagianUmumFlag}, ${presensiFlag}, "${currentJamSelesaiPresensiKabag}", "${currentJamPengajuanMulaiKabag}", "${currentJamPengajuanSelesaiKabag}")'
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium transition-all border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs cursor-pointer">
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
            subtitle.textContent = `${d.nama} (${d.nip}) - ${d.tanggal}`;
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
    if (val && val !== 'desc') {
        url.searchParams.set('sort', val);
    } else {
        url.searchParams.delete('sort');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

// ========================================================
// INSTANT IN-MEMORY SEARCH & CLIENT PAGINATION ENGINE (SMOOTH 0MS)
// ========================================================
let currentPage = 1;
let currentPerPage = {{ $perPage ?? 10 }};
let currentSearchQuery = '{{ addslashes($search ?? '') }}'.toLowerCase().trim();

window.renderTableAndPagination = function() {
    const tableRows = Array.from(document.querySelectorAll('#tabelPengajuan tr.pengajuan-row'));
    const timelineItems = Array.from(document.querySelectorAll('#timelineListKabag .timeline-item'));

    // Filter baris yang cocok berdasarkan data-search
    const matchedTableRows = tableRows.filter(row => {
        if (!currentSearchQuery) return true;
        const searchData = row.getAttribute('data-search') || '';
        return searchData.includes(currentSearchQuery);
    });

    const matchedTimelineItems = timelineItems.filter(item => {
        if (!currentSearchQuery) return true;
        const searchData = item.getAttribute('data-search') || '';
        return searchData.includes(currentSearchQuery);
    });

    const totalMatched = matchedTableRows.length;
    const totalPages = Math.max(1, Math.ceil(totalMatched / currentPerPage));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIndex = (currentPage - 1) * currentPerPage;
    const endIndex = startIndex + currentPerPage;

    // 1. Tampilkan baris tabel yang sesuai halaman aktif, sembunyikan sisanya
    tableRows.forEach(row => row.classList.add('hidden'));
    matchedTableRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.classList.remove('hidden');
            const noCell = row.querySelector('.row-number');
            if (noCell) {
                noCell.textContent = idx + 1;
            }
        } else {
            row.classList.add('hidden');
        }
    });

    // 2. Tampilkan item timeline yang sesuai halaman aktif
    timelineItems.forEach(item => item.classList.add('hidden'));
    matchedTimelineItems.forEach((item, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });

    if (typeof updateTimelineStems === 'function') {
        updateTimelineStems();
    }

    // 3. Status pesan kosong pencarian di tabel
    const emptyTableSearch = document.getElementById('emptySearchRow');
    if (emptyTableSearch) {
        if (totalMatched === 0 && tableRows.length > 0) {
            emptyTableSearch.classList.remove('hidden');
            const emptyText = document.getElementById('emptySearchText');
            if (emptyText) {
                emptyText.textContent = currentSearchQuery 
                    ? `Tidak ada pengajuan yang cocok dengan pencarian "${currentSearchQuery}".`
                    : 'Tidak ada data pengajuan lembur.';
            }
        } else {
            emptyTableSearch.classList.add('hidden');
        }
    }

    // Status pesan kosong pencarian di timeline
    const emptyTimelineSearch = document.getElementById('emptySearchTimeline');
    if (emptyTimelineSearch) {
        if (totalMatched === 0 && timelineItems.length > 0) {
            emptyTimelineSearch.classList.remove('hidden');
            const emptyText = document.getElementById('emptySearchTimelineText');
            if (emptyText) {
                emptyText.textContent = currentSearchQuery 
                    ? `Tidak ada pengajuan yang cocok dengan pencarian "${currentSearchQuery}".`
                    : 'Tidak ada data pengajuan lembur.';
            }
        } else {
            emptyTimelineSearch.classList.add('hidden');
        }
    }

    // 4. Update Indikator Halaman & Data Bawah (Sesuai Gambar 2)
    const pageIndicator = document.getElementById('pageIndicator');
    if (pageIndicator) {
        pageIndicator.textContent = `Page ${totalMatched === 0 ? 0 : currentPage} of ${totalPages}`;
    }

    const dataCountIndicator = document.getElementById('dataCountIndicator');
    if (dataCountIndicator) {
        if (totalMatched === 0) {
            dataCountIndicator.textContent = 'Menampilkan 0 dari 0 data';
        } else {
            const startDisplay = startIndex + 1;
            const endDisplay = Math.min(endIndex, totalMatched);
            dataCountIndicator.textContent = `Menampilkan ${startDisplay}-${endDisplay} dari ${totalMatched} data`;
        }
    }

    // 5. Update Status Tombol Prev & Next
    const btnPrev = document.getElementById('btnPrevPage');
    if (btnPrev) {
        const canPrev = currentPage > 1 && totalMatched > 0;
        btnPrev.disabled = !canPrev;
        if (canPrev) {
            btnPrev.className = 'p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938] transition-colors cursor-pointer';
        } else {
            btnPrev.className = 'p-1 rounded border text-gray-300 border-gray-200 bg-gray-50 cursor-not-allowed';
        }
    }

    const btnNext = document.getElementById('btnNextPage');
    if (btnNext) {
        const canNext = currentPage < totalPages && totalMatched > 0;
        btnNext.disabled = !canNext;
        if (canNext) {
            btnNext.className = 'p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938] transition-colors cursor-pointer';
        } else {
            btnNext.className = 'p-1 rounded border text-gray-300 border-gray-200 bg-gray-50 cursor-not-allowed';
        }
    }

    // 6. Sinkronisasi parameter URL secara senyap tanpa reload
    try {
        const url = new URL(window.location.href);
        if (currentSearchQuery) {
            url.searchParams.set('search', currentSearchQuery);
        } else {
            url.searchParams.delete('search');
            url.searchParams.delete('q');
        }
        if (currentPage > 1) {
            url.searchParams.set('page', currentPage);
        } else {
            url.searchParams.delete('page');
        }
        if (currentPerPage !== 10) {
            url.searchParams.set('perPage', currentPerPage);
        } else {
            url.searchParams.delete('perPage');
        }
        window.history.replaceState({}, '', url.toString());
    } catch (e) {
        console.warn('URL state sync failed', e);
    }
};

window.goToPrevPage = function() {
    if (currentPage > 1) {
        currentPage--;
        renderTableAndPagination();
    }
};

window.goToNextPage = function() {
    currentPage++;
    renderTableAndPagination();
};

window.changePerPage = function(val) {
    currentPerPage = parseInt(val) || 10;
    currentPage = 1;
    renderTableAndPagination();
};

window.handleSearchInput = function(e) {
    const val = e.target.value;
    const clearDesktop = document.getElementById('clearSearchBtn');
    const clearMobile  = document.getElementById('clearSearchBtnMobile');

    if (val.trim().length > 0) {
        if (clearDesktop) clearDesktop.classList.remove('hidden');
        if (clearMobile)  clearMobile.classList.remove('hidden');
    } else {
        if (clearDesktop) clearDesktop.classList.add('hidden');
        if (clearMobile)  clearMobile.classList.add('hidden');
    }

    // Sinkronisasi nilai antara input desktop & mobile
    const inputDesktop = document.getElementById('searchPegawai');
    const inputMobile  = document.getElementById('searchPegawaiMobile');
    if (e.target === inputDesktop && inputMobile) {
        inputMobile.value = val;
    } else if (e.target === inputMobile && inputDesktop) {
        inputDesktop.value = val;
    }

    currentSearchQuery = val.trim().toLowerCase();
    currentPage = 1; // Kembali ke halaman 1 saat filter pencarian berubah
    renderTableAndPagination(); // Eksekusi instan 0ms tanpa reload!
};

window.clearSearchInput = function() {
    const inputDesktop = document.getElementById('searchPegawai');
    const inputMobile  = document.getElementById('searchPegawaiMobile');
    if (inputDesktop) inputDesktop.value = '';
    if (inputMobile)  inputMobile.value = '';

    const clearDesktop = document.getElementById('clearSearchBtn');
    const clearMobile  = document.getElementById('clearSearchBtnMobile');
    if (clearDesktop) clearDesktop.classList.add('hidden');
    if (clearMobile)  clearMobile.classList.add('hidden');

    currentSearchQuery = '';
    currentPage = 1;
    renderTableAndPagination();

    if (window.innerWidth >= 640 && inputDesktop) {
        inputDesktop.focus();
    } else if (inputMobile) {
        inputMobile.focus();
    }
};

// =====================
// PERIOD PICKER LOGIC (DESKTOP & MOBILE ISOLATED)
// =====================
(function() {
    function initPeriodPicker(btnId, panelId, closeId, yLabelId, yPrevId, yNextId, gridId, thisMonthId, allMonthsId, allMonthsLabelId) {
        const periodBtn    = document.getElementById(btnId);
        const periodPanel  = document.getElementById(panelId);
        const btnClose     = document.getElementById(closeId);
        const yearLabel    = document.getElementById(yLabelId);
        const yearPrev     = document.getElementById(yPrevId);
        const yearNext     = document.getElementById(yNextId);
        const monthGrid    = document.getElementById(gridId);
        const btnThisMonth = document.getElementById(thisMonthId);
        const btnAllMonths = document.getElementById(allMonthsId);
        const allMonthsYearLabel = document.getElementById(allMonthsLabelId);

        if (!periodBtn || !periodPanel) return;

        let selYear  = {{ $selectedYear }};
        let selMonth = {{ $selectedMonth !== null ? $selectedMonth : 'null' }};
        let viewYear = selYear;

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        function renderMonths() {
            if (yearLabel) yearLabel.textContent = viewYear;
            if (allMonthsYearLabel) allMonthsYearLabel.textContent = viewYear;
            if (btnAllMonths) {
                const isAllSelected = (selMonth === null && viewYear === selYear);
                btnAllMonths.className = `w-full py-1.5 px-3 text-xs font-semibold rounded-lg border transition text-center ${isAllSelected ? 'bg-[#faa938] text-slate-950 font-bold border-[#faa938]' : 'border-gray-200 text-gray-700 bg-white hover:border-[#faa938] hover:text-[#faa938]'}`;
                btnAllMonths.onclick = () => {
                    const now = new Date();
                    const url = new URL(window.location.href);
                    if (viewYear === now.getFullYear()) {
                        url.searchParams.set('bulan', 'all');
                    } else {
                        url.searchParams.set('bulan', `${viewYear}-all`);
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                };
            }

            if (monthGrid) {
                monthGrid.innerHTML = '';
                monthNames.forEach((name, idx) => {
                    const m = idx + 1;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = name;
                    const isSelected = (viewYear === selYear && m === selMonth);
                    btn.className = `py-2 rounded-lg text-xs font-semibold transition-colors ${isSelected ? 'bg-[#faa938] text-slate-950 font-bold shadow-sm' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'}`;
                    btn.onclick = () => {
                        const targetM = String(m).padStart(2, '0');
                        const url = new URL(window.location.href);
                        url.searchParams.set('bulan', `${viewYear}-${targetM}`);
                        url.searchParams.delete('page');
                        window.location.href = url.toString();
                    };
                    monthGrid.appendChild(btn);
                });
            }
        }

        periodBtn.onclick = (e) => {
            e.stopPropagation();
            // Jika mobile, tutup dropdown status mobile jika sedang terbuka
            const menuMobile = document.getElementById('menuKabagMobileStatus');
            const iconChevron = document.getElementById('iconChevronKabagStatus');
            if (menuMobile) {
                menuMobile.classList.add('hidden');
                iconChevron?.classList.remove('rotate-180');
            }
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
                url.searchParams.delete('page');
                window.location.href = url.toString();
            };
        }

        document.addEventListener('click', (e) => {
            if (!periodPanel.contains(e.target) && !periodBtn.contains(e.target)) {
                periodPanel.classList.add('hidden');
            }
        });
    }

    // Init Desktop Period Picker (100% Identik Asli)
    initPeriodPicker('periodBtn', 'periodPanel', 'btnClosePanel', 'yearLabel', 'yearPrev', 'yearNext', 'monthGrid', 'btnThisMonth', 'btnAllMonthsOfYearKabag', 'allMonthsYearLabelKabag');

    // Init Mobile Period Picker
    initPeriodPicker('periodBtnMobile', 'periodPanelMobile', 'btnClosePanelMobile', 'yearLabelMobile', 'yearPrevMobile', 'yearNextMobile', 'monthGridMobile', 'btnThisMonthMobile', 'btnAllMonthsOfYearKabagMobile', 'allMonthsYearLabelKabagMobile');

    // Setup Mobile Status Custom Dropdown
    const btnKabagMobileStatus     = document.getElementById('btnKabagMobileStatus');
    const menuKabagMobileStatus    = document.getElementById('menuKabagMobileStatus');
    const iconChevronKabagStatus   = document.getElementById('iconChevronKabagStatus');
    const wrapperKabagMobileStatus = document.getElementById('kabagMobileStatusWrapper');

    if (btnKabagMobileStatus && menuKabagMobileStatus) {
        btnKabagMobileStatus.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Tutup panel kalender mobile jika sedang terbuka
            const panelMobile = document.getElementById('periodPanelMobile');
            if (panelMobile) panelMobile.classList.add('hidden');

            const isHidden = menuKabagMobileStatus.classList.contains('hidden');
            if (isHidden) {
                menuKabagMobileStatus.classList.remove('hidden');
                iconChevronKabagStatus?.classList.add('rotate-180');
            } else {
                menuKabagMobileStatus.classList.add('hidden');
                iconChevronKabagStatus?.classList.remove('rotate-180');
            }
        });

        document.addEventListener('click', function (e) {
            if (wrapperKabagMobileStatus && !wrapperKabagMobileStatus.contains(e.target)) {
                menuKabagMobileStatus.classList.add('hidden');
                iconChevronKabagStatus?.classList.remove('rotate-180');
            }
        });
    }
})();

// =====================
// TOGGLE VIEW MODE (TABEL VS TIMELINE) KABAG UMUM
// =====================
window.switchViewMode = function(mode) {
    const containerTable = document.getElementById('viewContainerTable');
    const containerTimeline = document.getElementById('viewContainerTimeline');
    const btnTable = document.getElementById('btnViewTable');
    const btnTimeline = document.getElementById('btnViewTimeline');

    if (mode === 'timeline') {
        containerTable?.classList.add('hidden');
        containerTimeline?.classList.remove('hidden');

        btnTable?.classList.remove('bg-white', 'text-slate-900', 'shadow-2xs');
        btnTable?.classList.add('text-slate-300');

        btnTimeline?.classList.add('bg-white', 'text-slate-900', 'shadow-2xs');
        btnTimeline?.classList.remove('text-slate-300');

        updateTimelineStems();
        localStorage.setItem('kabagUmumPengajuanViewMode', 'timeline');
    } else {
        containerTimeline?.classList.add('hidden');
        containerTable?.classList.remove('hidden');

        btnTimeline?.classList.remove('bg-white', 'text-slate-900', 'shadow-2xs');
        btnTimeline?.classList.add('text-slate-300');

        btnTable?.classList.add('bg-white', 'text-slate-900', 'shadow-2xs');
        btnTable?.classList.remove('text-slate-300');

        localStorage.setItem('kabagUmumPengajuanViewMode', 'table');
    }
};

function updateTimelineStems() {
    const visibleItems = Array.from(document.querySelectorAll('#timelineListKabag .timeline-item')).filter(el => !el.classList.contains('hidden'));
    if (!visibleItems.length) return;

    visibleItems.forEach((item, idx) => {
        const stem = item.querySelector('.timeline-stem');
        if (!stem) return;

        if (visibleItems.length === 1) {
            stem.style.display = 'none';
        } else if (idx === 0) {
            stem.style.display = 'block';
            stem.style.top = '21px';
            stem.style.bottom = '0';
            stem.style.height = '';
        } else if (idx === visibleItems.length - 1) {
            stem.style.display = 'block';
            stem.style.top = '0';
            stem.style.height = '21px';
            stem.style.bottom = '';
        } else {
            stem.style.display = 'block';
            stem.style.top = '0';
            stem.style.bottom = '0';
            stem.style.height = '';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const savedMode = localStorage.getItem('kabagUmumPengajuanViewMode') || 'table';
    if (savedMode === 'timeline') {
        window.switchViewMode('timeline');
    }

    // Inisialisasi awal pagination & filter pencarian dari query string URL jika ada
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const pParam = parseInt(urlParams.get('page'));
        if (!isNaN(pParam) && pParam > 0) currentPage = pParam;

        const perPageParam = parseInt(urlParams.get('perPage'));
        if ([10, 25, 50, 100].includes(perPageParam)) {
            currentPerPage = perPageParam;
            const selectEl = document.getElementById('perPageSelect');
            if (selectEl) selectEl.value = currentPerPage;
        }

        const searchParam = urlParams.get('search') || urlParams.get('q') || '';
        if (searchParam) {
            currentSearchQuery = searchParam.toLowerCase().trim();
            const inputDesktop = document.getElementById('searchPegawai');
            const inputMobile  = document.getElementById('searchPegawaiMobile');
            if (inputDesktop) inputDesktop.value = searchParam;
            if (inputMobile)  inputMobile.value = searchParam;
            const clearDesktop = document.getElementById('clearSearchBtn');
            const clearMobile  = document.getElementById('clearSearchBtnMobile');
            if (clearDesktop) clearDesktop.classList.remove('hidden');
            if (clearMobile)  clearMobile.classList.remove('hidden');
        }
    } catch (e) {
        console.warn('URL params parsing error', e);
    }

    window.renderTableAndPagination();
});

window.addEventListener('resize', updateTimelineStems);
</script>
@endpush

@endsection

@extends('layouts.app')

@section('title', 'Pengajuan Lembur')

@section('content')

<div class="w-full max-w-7xl mx-auto flex flex-col px-4 sm:px-6 lg:px-8">

    {{-- Flash success --}}
    @if(session('success'))
        <div class="mt-4 px-4 py-3 bg-green-100 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-3.5 sm:mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-800">
                Monitoring & Pengajuan Lembur
            </h1>
            <p class="hidden sm:block text-xs text-slate-500 mt-0.5">
                Kelola, monitor status verifikasi, dan rekapitulasi data lembur seluruh pegawai.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 shrink-0">
            {{-- Unduh Rekapitulasi --}}
            <a id="btnExport"
                href="{{ route('admin.lembur.export', ['bulan' => now()->format('Y-m'), 'tim' => request('tim'), 'nip' => request('nip'), 'status' => request('status'), 'sort' => request('sort')]) }}"
                title="Unduh Rekapitulasi"
                class="inline-flex h-9 sm:h-10 items-center justify-center gap-1.5 sm:gap-2 rounded-xl border border-gray-200 bg-white px-3 sm:px-3.5 text-xs sm:text-sm font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                <span>Unduh Excel</span>
            </a>

            {{-- Tombol Ajukan --}}
            <a href="javascript:void(0)" id="btnAjukan"
                class="inline-flex h-9 sm:h-10 items-center justify-center gap-1.5 sm:gap-2 rounded-xl bg-[#faa938] px-3.5 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow-sm transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Ajukan Lembur</span>
            </a>
        </div>
    </div>

    <div class="overflow-visible">

        {{-- Toolbar --}}
        {{-- Toolbar --}}
        <div class="mb-3.5 sm:mb-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2.5">

            <div class="flex items-center gap-2 w-full sm:w-auto">
                {{-- Filter Periode --}}
                <div class="relative flex-1 sm:flex-initial" id="datePicker">
                    <button type="button" id="dateBtn"
                        class="inline-flex w-full sm:w-auto h-10 items-center justify-between gap-2.5 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all whitespace-nowrap cursor-pointer">
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

                    <div id="datePanel"
                        class="hidden absolute z-50 mt-2 left-0 w-72 max-w-[calc(100vw-2rem)] rounded-2xl border border-gray-200 bg-white shadow-xl p-3.5">
                        <div class="flex items-center justify-between mb-3">
                            <button type="button" id="datePrev"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                                </svg>
                            </button>

                            <span id="dateNavLabel"
                                class="text-xs font-bold text-gray-900 cursor-pointer hover:text-[#faa938] select-none">
                            </span>

                            <button type="button" id="dateNext"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                                </svg>
                            </button>
                        </div>

                        <div id="dateGrid"></div>

                        <div class="flex items-center justify-between mt-3 border-t border-gray-100 pt-2.5">
                            <button type="button" id="btnToday"
                                class="text-xs font-semibold text-gray-600 hover:text-[#faa938] transition-colors">
                                Hari ini
                            </button>
                            <button type="button" id="btnDateClose"
                                class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Toggle Filter Mobile (Accordion) --}}
                @php
                    $hasActiveSearchFilter = !empty(request('nip')) || !empty(request('search')) || !empty(request('tim'));
                    $activeFilterCount = (!empty(request('nip')) ? 1 : 0) + (!empty(request('search')) || !empty(request('tim')) ? 1 : 0);
                @endphp
                <button type="button" id="btnToggleMobileFilter" onclick="toggleMobileFilter()"
                    class="sm:hidden inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all shrink-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Cari</span>
                    @if($hasActiveSearchFilter)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#faa938] text-white leading-none">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                    <svg id="iconChevronFilter" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-2.5 w-2.5 fill-current opacity-40 transition-transform duration-200 {{ $hasActiveSearchFilter ? 'rotate-180' : '' }}">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>
            </div>

            {{-- Collapsible Container Pegawai & Tim --}}
            <div id="mobileFilterCollapse" class="{{ $hasActiveSearchFilter ? 'flex' : 'hidden' }} sm:!flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                {{-- Filter Pegawai --}}
                <div class="relative w-full sm:w-64" id="wrapSearchPegawai">
                    <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                        onclick="openDropdown()" onfocus="openDropdown()" oninput="filterDropdown()" autocomplete="off"
                        class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-3.5 pr-12 text-xs font-medium text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all"/>

                    <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                        <button type="button" id="btnClearPegawai" onclick="pilihPegawai(null)" class="hidden p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100 transition-colors cursor-pointer" title="Hapus filter pegawai">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <button type="button" onclick="toggleDropdown()" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none p-0.5 cursor-pointer" title="Buka daftar pegawai">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3">
                                <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                            </svg>
                        </button>
                    </div>

                    <div id="dropdownPegawai"
                        class="hidden absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                        <ul id="listPegawai"></ul>
                    </div>
                </div>

                {{-- Filter Tim --}}
                <div class="relative w-full sm:w-60" id="wrapSearchTim">
                    <input type="text" id="searchTim" placeholder="Cari nama tim..."
                        onclick="openDropdownTim()" onfocus="openDropdownTim()" oninput="filterDropdownTim()" autocomplete="off"
                        value="{{ request('search') }}"
                        class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-3.5 pr-12 text-xs font-medium text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all"/>

                    <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                        <button type="button" id="btnClearTim" onclick="pilihTim(null)" class="hidden p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100 transition-colors cursor-pointer" title="Hapus filter tim">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <button type="button" onclick="toggleDropdownTim()" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none p-0.5 cursor-pointer" title="Buka daftar tim">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3">
                                <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                            </svg>
                        </button>
                    </div>

                    <div id="dropdownTim"
                        class="hidden absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                        <ul id="listTim"></ul>
                    </div>
                </div>
            </div>

        </div>

        @php
            $currentStatusLabel = 'Semua Status';
            $currentStatusCount = $statusCounts['all'] ?? 0;
            $currentStatusBadgeClass = 'bg-slate-100 text-slate-700';
            $currentStatusDotClass = 'bg-slate-800';

            if ($status === 'menunggu_kabag') {
                $currentStatusLabel = 'Menunggu Kabag';
                $currentStatusCount = $statusCounts['menunggu_kabag'] ?? 0;
                $currentStatusBadgeClass = 'bg-blue-100 text-blue-800';
                $currentStatusDotClass = 'bg-blue-600';
            } elseif ($status === 'pending') {
                $currentStatusLabel = 'Menunggu Ketua';
                $currentStatusCount = $statusCounts['pending'] ?? 0;
                $currentStatusBadgeClass = 'bg-amber-100 text-amber-800';
                $currentStatusDotClass = 'bg-amber-500';
            } elseif ($status === 'approved') {
                $currentStatusLabel = 'Disetujui';
                $currentStatusCount = $statusCounts['approved'] ?? 0;
                $currentStatusBadgeClass = 'bg-emerald-100 text-emerald-800';
                $currentStatusDotClass = 'bg-emerald-600';
            } elseif ($status === 'rejected') {
                $currentStatusLabel = 'Ditolak';
                $currentStatusCount = $statusCounts['rejected'] ?? 0;
                $currentStatusBadgeClass = 'bg-rose-100 text-rose-800';
                $currentStatusDotClass = 'bg-rose-600';
            } elseif ($status === 'cancelled') {
                $currentStatusLabel = 'Dibatalkan';
                $currentStatusCount = $statusCounts['cancelled'] ?? 0;
                $currentStatusBadgeClass = 'bg-gray-100 text-gray-800';
                $currentStatusDotClass = 'bg-gray-600';
            }
        @endphp

        {{-- Status Filter Mobile: Dropdown Kompak & Elegan (Khusus Layar HP < 640px) --}}
        <div class="sm:hidden relative w-full mb-3 z-30" id="mobileStatusWrapper">
            <button type="button" id="btnMobileStatus"
                class="inline-flex w-full h-10 items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] active:bg-gray-50 transition-all cursor-pointer select-none">
                <span class="inline-flex items-center gap-2 truncate pointer-events-none">
                    <span class="h-2.5 w-2.5 rounded-full {{ $currentStatusDotClass }} shrink-0"></span>
                    <span class="text-slate-400 font-normal">Status:</span>
                    <span class="font-bold text-slate-800 truncate">{{ $currentStatusLabel }}</span>
                </span>

                <span class="inline-flex items-center gap-2 shrink-0 pointer-events-none">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $currentStatusBadgeClass }}">
                        {{ $currentStatusCount }}
                    </span>
                    <svg id="iconChevronStatus" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-2.5 w-2.5 fill-current opacity-40 transition-transform duration-200">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </span>
            </button>

            {{-- Custom Menu Popup Pilihan Status dengan Tipografi Cantik & Modern --}}
            <div id="menuMobileStatus"
                class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden py-1 divide-y divide-gray-50">
                
                {{-- Semua Status --}}
                <button type="button" onclick="selectStatus('')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ empty($status) ? 'bg-slate-900 text-white font-semibold' : 'text-gray-700 hover:bg-gray-50 active:bg-gray-100' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="h-2 w-2 rounded-full {{ empty($status) ? 'bg-white' : 'bg-slate-800' }}"></span>
                        <span>Semua Status</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ empty($status) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                        {{ $statusCounts['all'] ?? 0 }}
                    </span>
                </button>

                {{-- Menunggu Kabag --}}
                <button type="button" onclick="selectStatus('menunggu_kabag')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $status === 'menunggu_kabag' ? 'bg-blue-600 text-white font-semibold' : 'text-blue-900 hover:bg-blue-50/60 active:bg-blue-100/60' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'menunggu_kabag' ? 'bg-white opacity-75' : 'bg-blue-400 opacity-75' }}"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'menunggu_kabag' ? 'bg-white' : 'bg-blue-600' }}"></span>
                        </span>
                        <span>Menunggu Kabag</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ $status === 'menunggu_kabag' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                        {{ $statusCounts['menunggu_kabag'] ?? 0 }}
                    </span>
                </button>

                {{-- Menunggu Ketua --}}
                <button type="button" onclick="selectStatus('pending')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $status === 'pending' ? 'bg-amber-500 text-white font-semibold' : 'text-amber-900 hover:bg-amber-50/60 active:bg-amber-100/60' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'pending' ? 'bg-white opacity-75' : 'bg-amber-400 opacity-75' }}"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                        </span>
                        <span>Menunggu Ketua</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                        {{ $statusCounts['pending'] ?? 0 }}
                    </span>
                </button>

                {{-- Disetujui --}}
                <button type="button" onclick="selectStatus('approved')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $status === 'approved' ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-900 hover:bg-emerald-50/60 active:bg-emerald-100/60' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="h-2 w-2 rounded-full {{ $status === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                        <span>Disetujui</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $statusCounts['approved'] ?? 0 }}
                    </span>
                </button>

                {{-- Ditolak --}}
                <button type="button" onclick="selectStatus('rejected')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $status === 'rejected' ? 'bg-rose-600 text-white font-semibold' : 'text-rose-900 hover:bg-rose-50/60 active:bg-rose-100/60' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="h-2 w-2 rounded-full {{ $status === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                        <span>Ditolak</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                        {{ $statusCounts['rejected'] ?? 0 }}
                    </span>
                </button>

                {{-- Dibatalkan --}}
                <button type="button" onclick="selectStatus('cancelled')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-xs text-left transition-colors cursor-pointer {{ $status === 'cancelled' ? 'bg-gray-600 text-white font-semibold' : 'text-gray-700 hover:bg-gray-50 active:bg-gray-100' }}">
                    <span class="inline-flex items-center gap-2.5 pointer-events-none">
                        <span class="h-2 w-2 rounded-full {{ $status === 'cancelled' ? 'bg-white' : 'bg-gray-500' }}"></span>
                        <span>Dibatalkan</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold pointer-events-none {{ $status === 'cancelled' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800' }}">
                        {{ $statusCounts['cancelled'] ?? 0 }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Desktop Status Filter Tabs / Monitoring Pills (HANYA tampil di Desktop sm:flex) --}}
        <div class="hidden sm:flex flex-wrap items-center gap-2 mb-3.5 sm:mb-4">
            {{-- Semua Status --}}
            <button type="button" onclick="selectStatus('')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ empty($status) ? 'bg-slate-900 text-white border-slate-900 shadow-sm shadow-slate-900/20 ring-2 ring-slate-900/10' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                <span>Semua Status</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ empty($status) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                    {{ $statusCounts['all'] ?? 0 }}
                </span>
            </button>

            {{-- Menunggu Kabag --}}
            <button type="button" onclick="selectStatus('menunggu_kabag')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ $status === 'menunggu_kabag' ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-600/25 ring-2 ring-blue-600/20' : 'bg-white text-blue-700 border-blue-200 hover:bg-blue-50/80 hover:border-blue-300' }}">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'menunggu_kabag' ? 'bg-white opacity-75' : 'bg-blue-400 opacity-75' }}"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'menunggu_kabag' ? 'bg-white' : 'bg-blue-600' }}"></span>
                </span>
                <span>Menunggu Kabag</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'menunggu_kabag' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                    {{ $statusCounts['menunggu_kabag'] ?? 0 }}
                </span>
            </button>

            {{-- Menunggu Ketua --}}
            <button type="button" onclick="selectStatus('pending')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ $status === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-sm shadow-amber-500/25 ring-2 ring-amber-500/20' : 'bg-white text-amber-700 border-amber-200 hover:bg-amber-50/80 hover:border-amber-300' }}">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'pending' ? 'bg-white opacity-75' : 'bg-amber-400 opacity-75' }}"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                </span>
                <span>Menunggu Ketua</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ $statusCounts['pending'] ?? 0 }}
                </span>
            </button>

            {{-- Disetujui --}}
            <button type="button" onclick="selectStatus('approved')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ $status === 'approved' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm shadow-emerald-600/25 ring-2 ring-emerald-600/20' : 'bg-white text-emerald-700 border-emerald-200 hover:bg-emerald-50/80 hover:border-emerald-300' }}">
                <span class="h-2 w-2 rounded-full {{ $status === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>Disetujui</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $statusCounts['approved'] ?? 0 }}
                </span>
            </button>

            {{-- Ditolak --}}
            <button type="button" onclick="selectStatus('rejected')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ $status === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-sm shadow-rose-600/25 ring-2 ring-rose-600/20' : 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50/80 hover:border-rose-300' }}">
                <span class="h-2 w-2 rounded-full {{ $status === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                <span>Ditolak</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                    {{ $statusCounts['rejected'] ?? 0 }}
                </span>
            </button>

            {{-- Dibatalkan --}}
            <button type="button" onclick="selectStatus('cancelled')"
                class="inline-flex shrink-0 items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap cursor-pointer {{ $status === 'cancelled' ? 'bg-gray-600 text-white border-gray-600 shadow-sm shadow-gray-600/25 ring-2 ring-gray-600/20' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50/80 hover:border-gray-300' }}">
                <span class="h-2 w-2 rounded-full {{ $status === 'cancelled' ? 'bg-white' : 'bg-gray-500' }}"></span>
                <span>Dibatalkan</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'cancelled' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800' }}">
                    {{ $statusCounts['cancelled'] ?? 0 }}
                </span>
            </button>
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

        {{-- Tabel --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1240px] table-auto divide-y divide-gray-200">
                    <thead class="bg-gray-50/90 border-b border-gray-200">
                        <tr>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize rounded-tl-2xl w-32">
                                <div class="inline-flex items-center justify-center gap-1.5 w-full">
                                    <span>Tanggal</span>
                                    <div class="relative inline-block text-left">
                                        <select id="headerSortTanggal" onchange="onHeaderSortChange(this.value)"
                                            class="appearance-none bg-white hover:bg-gray-50 rounded-md pl-1.5 pr-4 py-0.5 text-[11px] font-medium text-gray-700 cursor-pointer border border-gray-300 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-1 focus:ring-[#faa938]/40 transition-all">
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
                            <th class="px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize w-44">Pegawai</th>
                            <th class="px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize w-40">Tim &amp; Ketua</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize w-32">Jam Diajukan</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize w-32">Jam Disetujui</th>
                            <th class="px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize">Uraian Kegiatan</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize w-36">Data Presensi</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize w-36">Status</th>
                            <th class="px-3.5 py-3.5 text-left text-xs font-semibold text-gray-700 capitalize w-32">Catatan</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize w-24">Dokumentasi</th>
                            <th class="px-3.5 py-3.5 text-center text-xs font-semibold text-gray-700 capitalize rounded-tr-2xl w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white" id="tabelLembur">
                        @forelse($transaksi as $t)
                            <tr class="transition-colors hover:bg-slate-50/80"
                                data-tanggal="{{ $t->date }}"
                                data-tim="{{ $t->tim_kode_tim }}"
                                data-nip="{{ $t->submitted_by_NIP }}">

                                <td class="px-3.5 py-3.5 text-xs text-gray-700 whitespace-nowrap text-center align-middle">
                                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($t->date)->translatedFormat('d M Y') }}</span>
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-left align-middle">
                                    <div class="font-semibold text-gray-900 leading-snug">{{ $t->nama_pegawai ?? '-' }}</div>
                                    <div class="text-[11px] font-mono text-gray-500 mt-0.5">{{ $t->submitted_by_NIP }}</div>
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-left align-middle">
                                    <div class="font-medium text-gray-800 leading-snug max-w-[150px] break-words">{{ $t->nama_tim ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Ketua: <span class="text-gray-700">{{ $t->nama_ketua ?? '-' }}</span></div>
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-center whitespace-nowrap align-middle">
                                    @if($t->jam_mulai && $t->jam_selesai)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[11px] font-medium text-gray-700 bg-gray-50 border border-gray-200/80">
                                            {{ substr($t->jam_mulai, 0, 5) }} &ndash; {{ substr($t->jam_selesai, 0, 5) }}
                                        </span>
                                    @elseif($t->jam_mulai)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[11px] text-gray-600 bg-gray-50 border border-gray-200/80">
                                            {{ substr($t->jam_mulai, 0, 5) }} &ndash; <span class="text-gray-400 font-sans italic text-[10px] ml-1">menunggu</span>
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-mono">-</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-center whitespace-nowrap align-middle">
                                    @if($t->jam_mulai_disetujui && $t->jam_selesai_disetujui)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono text-[11px] font-medium text-emerald-800 bg-emerald-50 border border-emerald-200/80">
                                            {{ substr($t->jam_mulai_disetujui, 0, 5) }} &ndash; {{ substr($t->jam_selesai_disetujui, 0, 5) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-mono">-</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-gray-800 group relative align-middle">
                                    {{-- Mode tampil --}}
                                    <div class="flex items-start gap-1.5" id="view-{{ $t->id_transaksi }}">
                                        <div class="flex-1 max-w-[240px] whitespace-normal break-words leading-relaxed text-gray-700">
                                            {{ $t->uraian ?? '-' }}
                                        </div>
                                        <button type="button"
                                            onclick="startEditUraian({{ $t->id_transaksi }})"
                                            class="flex-shrink-0 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-amber-500 rounded p-0.5 transition-all duration-150 cursor-pointer"
                                            title="Edit uraian cepat">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Mode edit --}}
                                    <div class="hidden" id="edit-{{ $t->id_transaksi }}">
                                        <textarea
                                            id="textarea-{{ $t->id_transaksi }}"
                                            rows="3"
                                            maxlength="2000"
                                            class="w-full text-xs border border-[#faa938] rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#faa938] resize-y text-gray-800"
                                            placeholder="Isi uraian kegiatan...">{{ $t->uraian }}</textarea>
                                        <div class="flex gap-1.5 mt-1 justify-end">
                                            <button type="button"
                                                onclick="cancelEditUraian({{ $t->id_transaksi }})"
                                                class="text-xs px-2 py-0.5 rounded border border-gray-300 text-gray-500 hover:bg-gray-50">
                                                Batal
                                            </button>
                                            <button type="button"
                                                onclick="submitEditUraian({{ $t->id_transaksi }})"
                                                class="text-xs px-2 py-0.5 rounded border border-[#faa938] text-orange-500 hover:bg-amber-50">
                                                Simpan
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom Data Presensi (Lihat Presensi) --}}
                                <td class="px-3.5 py-3.5 text-center whitespace-nowrap align-middle">
                                    @if($t->has_presensi)
                                        <button type="button"
                                            onclick="openModalPresensiAdmin({{ $t->id_transaksi }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100 hover:border-emerald-300 transition-all cursor-pointer shadow-2xs group"
                                            title="Presensi Pulang: {{ $t->jam_selesai_presensi ?? '-' }} • Klik untuk rincian">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Lihat Presensi</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] text-gray-400 bg-gray-50 border border-gray-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                            <span>Belum Presensi</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-center whitespace-nowrap align-middle" id="status-cell-{{ $t->id_transaksi }}">
                                    @if($t->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>Menunggu Ketua</span>
                                        </span>
                                    @elseif($t->status === 'menunggu_kabag')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            <span>Menunggu Kabag</span>
                                        </span>
                                    @elseif($t->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>
                                    @elseif($t->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                    @elseif($t->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            <span>Dibatalkan</span>
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-gray-600 text-left max-w-[140px] break-words align-middle" id="note-cell-{{ $t->id_transaksi }}">
                                    @if(!empty($t->note))
                                        <span class="italic text-gray-600 leading-relaxed">{{ $t->note }}</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-xs text-center whitespace-nowrap align-middle">
                                    @if($t->status === 'approved')
                                        @if($t->file_dokumentasi)
                                            <div class="inline-flex items-center justify-center gap-1.5">
                                                <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200/80 hover:bg-blue-100 hover:text-blue-800 transition-colors shadow-2xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    <span>Lihat</span>
                                                </a>
                                                @if($t->submitted_by_NIP === session('user')['nip'])
                                                    <form action="{{ route('admin.lembur.destroyDoc', $t->id_transaksi) }}" method="POST"
                                                        onsubmit="return confirm('Hapus dokumentasi ini?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 text-gray-400 hover:text-red-600 rounded transition-colors" title="Hapus dokumentasi">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            @if($t->submitted_by_NIP === session('user')['nip'])
                                                <button type="button"
                                                    onclick="openModalDok({{ $t->id_transaksi }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200/80 hover:bg-amber-100 transition-colors shadow-2xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                                    </svg>
                                                    <span>Unggah</span>
                                                </button>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-3.5 text-center text-xs whitespace-nowrap align-middle" id="aksi-cell-{{ $t->id_transaksi }}">
                                    @if($t->status !== 'cancelled')
                                        <button type="button"
                                            onclick="openModalAksiAdmin({{ $t->id_transaksi }}, {{ json_encode($t->nama_pegawai ?? '-') }}, '{{ \Carbon\Carbon::parse($t->date)->translatedFormat('d M Y') }}', {{ json_encode($t->uraian ?? '') }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-2xs transition-all cursor-pointer group"
                                            title="Kelola & Aksi Pengajuan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500 group-hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                            </svg>
                                            <span>Aksi</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-gray-400 bg-gray-50 border border-gray-200/60 italic">Dibatalkan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-[#faa938] mb-3 border border-amber-100/80 shadow-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 mb-1">Tidak Ada Pengajuan Lembur</h3>
                                        <p class="text-xs text-gray-500 mb-3 text-center leading-relaxed">
                                            Tidak ada data pengajuan lembur yang sesuai dengan kriteria filter saat ini.
                                        </p>
                                        @if(request()->hasAny(['tanggal', 'bulan', 'tim', 'nip', 'status']))
                                            <a href="{{ route('admin.lembur') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-2xs transition-all">
                                                Reset Semua Filter
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-5">

            {{-- Kiri: Dropdown perPage --}}
            <div class="flex items-center justify-center md:justify-start gap-2 text-sm text-gray-500">
                <span>Tampilkan</span>
                <select onchange="changePerPage(this.value)"
                    class="h-8 rounded-lg border border-gray-200 px-2 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none">
                    @foreach([10, 25, 50, 100] as $opt)
                        <option value="{{ $opt }}" {{ request('perPage', 10) == $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
                <span>data</span>
            </div>

            {{-- Tengah: Pagination --}}
            <nav class="inline-flex items-center justify-center space-x-2">
                @if($transaksi->onFirstPage())
                    <span class="p-1 rounded border text-gray-300 cursor-not-allowed">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]"
                        href="{{ $transaksi->previousPageUrl() }}">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                    Page {{ $transaksi->currentPage() }} of {{ $transaksi->lastPage() }}
                </p>

                @if($transaksi->hasMorePages())
                    <a class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]"
                        href="{{ $transaksi->nextPageUrl() }}">
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

            {{-- Kanan: Info jumlah data --}}
            <p class="text-center md:text-right text-xs sm:text-sm text-gray-500">
                Menampilkan {{ $transaksi->firstItem() ?? 0 }}-{{ $transaksi->lastItem() ?? 0 }}
                dari {{ $transaksi->total() }} data
            </p>

        </div>

    </div>
</div>

{{-- Form edit uraian  --}}
<form id="formEditUraian" action="" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="uraian" id="inputUraian">
</form>

{{-- MODAL DOKUMENTASI --}}
<div id="modalDok" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalDok()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>

            <div class="flex items-center justify-between border-b px-4 sm:px-6 py-4 sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-gray-900">Tambah Dokumentasi</h2>
                <button type="button" onclick="closeModalDok()"
                    class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            <form id="formDok" method="POST" class="px-4 sm:px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link Google Drive</label>
                    <input type="url" name="file_path" required
                        placeholder="https://drive.google.com/..."
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-1">
                    <button type="button" onclick="closeModalDok()"
                        class="w-full sm:w-auto px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL AKSI & KELOLA PENGAJUAN (ADMIN) --}}
<div id="modalAksiAdmin" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-xs" onclick="closeModalAksiAdmin()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>

            <div class="flex items-center justify-between border-b px-4 sm:px-6 py-4 sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2.5">
                    <div class="p-1.5 bg-amber-50 text-[#faa938] border border-amber-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-gray-900">Kelola Pengajuan Lembur</h2>
                        <p class="text-xs text-gray-500 mt-0.5" id="aksiSubtitleAdmin">-</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalAksiAdmin()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none cursor-pointer">
                    &times;
                </button>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-4">
                <input type="hidden" id="aksiIdTransaksi" value="">
                <input type="hidden" id="batalIdTransaksi" value="">

                {{-- Tab Navigasi Aksi --}}
                <div class="flex border-b border-gray-200 gap-2">
                    <button type="button" id="tabBtnEditUraian" onclick="switchAksiTab('edit')"
                        class="pb-2.5 px-3 text-xs font-semibold border-b-2 border-[#faa938] text-gray-900 transition-colors cursor-pointer flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Uraian</span>
                    </button>
                    <button type="button" id="tabBtnBatalPengajuan" onclick="switchAksiTab('batal')"
                        class="pb-2.5 px-3 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-rose-600 transition-colors cursor-pointer flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Batalkan Pengajuan</span>
                    </button>
                </div>

                {{-- PANEL 1: EDIT URAIAN --}}
                <div id="panelEditUraian" class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="aksiUraianInput" class="block text-xs font-semibold text-gray-800">
                                Uraian Kegiatan Lembur
                            </label>
                            <span id="aksiUraianCounter" class="text-[11px] text-gray-400">0 / 2000</span>
                        </div>
                        <textarea id="aksiUraianInput" rows="4" maxlength="2000"
                            class="w-full rounded-xl border border-gray-300 p-3 text-xs text-gray-900 placeholder-gray-400 focus:border-[#faa938] focus:outline-none focus:ring-1 focus:ring-[#faa938]/40 resize-y transition-all"
                            placeholder="Tulis uraian kegiatan lembur..."></textarea>
                        <p id="aksiUraianError" class="text-xs text-rose-600 mt-1 hidden"></p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2 border-t">
                        <button type="button" onclick="closeModalAksiAdmin()"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all cursor-pointer">
                            Tutup
                        </button>
                        <button type="button" onclick="submitAksiEditUraian()" id="btnSubmitEditUraian"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-950 bg-[#faa938] hover:bg-[#fd9a10] rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simpan Uraian</span>
                        </button>
                    </div>
                </div>

                {{-- PANEL 2: BATALKAN PENGAJUAN --}}
                <div id="panelBatalPengajuan" class="space-y-4 hidden">
                    <form id="formBatalAdmin" onsubmit="submitBatalAdmin(event)" class="space-y-4">
                        @csrf
                        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl space-y-1.5 text-xs text-rose-900">
                            <p class="font-medium text-rose-800">
                                Fitur ini digunakan jika pengajuan lembur salah tanggal atau dobel input:
                            </p>
                            <p class="text-[11px] text-rose-700/90 leading-relaxed">
                                Pengajuan akan diubah statusnya menjadi <b>Dibatalkan</b> dan tidak akan masuk dalam perhitungan lembur/keuangan. Riwayat pengajuan tetap tersimpan untuk transparansi.
                            </p>
                        </div>

                        <div>
                            <label for="batalAlasan" class="block text-xs font-semibold text-gray-800 mb-1.5">
                                Alasan Pembatalan <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="batalAlasan" name="alasan" rows="3" required
                                placeholder="Contoh: Dobel input pengajuan lembur / Salah input tanggal pengajuan"
                                class="w-full rounded-xl border border-gray-300 p-3 text-xs text-gray-900 placeholder-gray-400 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all"></textarea>
                            <p id="batalAlasanError" class="text-xs text-rose-600 mt-1 hidden"></p>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2 border-t">
                            <button type="button" onclick="closeModalAksiAdmin()"
                                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all cursor-pointer">
                                Tutup
                            </button>
                            <button type="submit" id="btnSubmitBatalAdmin"
                                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-rose-600/20 cursor-pointer">
                                <span>Ya, Batalkan Pengajuan</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL PRESENSI ADMIN --}}
<div id="modalPresensiAdmin" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-xs" onclick="closeModalPresensiAdmin()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto sm:overflow-hidden">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="h-1 w-10 rounded-full bg-gray-200"></div>
            </div>

            <div class="sticky sm:static top-0 z-10 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-4 rounded-t-2xl">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Informasi Presensi Pegawai</h2>
                    <p class="text-xs text-gray-400 mt-0.5" id="presensiSubtitleAdmin">-</p>
                </div>

                <button type="button" onclick="closeModalPresensiAdmin()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                    &times;
                </button>
            </div>

            <div class="px-4 sm:px-6 py-5 space-y-4" id="presensiBodyAdmin">
                <p class="text-sm text-gray-400 text-center py-4">Memuat data presensi...</p>
            </div>
        </div>
    </div>
</div>

{{-- MODAL AJUKAN LEMBUR --}}
<div id="modalAjukan" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div id="modalOverlay" class="absolute inset-0 bg-black/40"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-end sm:items-start justify-center p-0 sm:p-4 sm:py-8">

            <div class="relative w-full sm:max-w-3xl bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[94vh] sm:max-h-none overflow-y-auto sm:overflow-hidden">

                {{-- Mobile handle --}}
                <div class="flex justify-center pt-3 pb-1 sm:hidden">
                    <div class="h-1 w-10 rounded-full bg-gray-200"></div>
                </div>

                <div class="sticky sm:static top-0 z-10 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-4">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                        Ajukan Lembur
                    </h2>

                    <button type="button" id="btnCloseModal"
                        class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                        &times;
                    </button>
                </div>

                <form id="formAjukan" action="{{ route('admin.lembur.store') }}" method="POST"
                    class="px-4 sm:px-6 py-5 space-y-5">
                    @csrf

                    @if($errors->any())
                        <div class="px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Hidden kode_tim — diisi JS saat pilih ketua --}}
                    <input type="hidden" name="kode_tim" id="kode_tim">

                    {{-- Ketua Tim --}}
                    <div>
                        <label for="approver_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Ketua Tim
                        </label>

                        <select id="approver_id" name="approver_id" required
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">
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

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal
                        </label>

                        <input type="date" id="tanggal" name="tanggal"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">

                        <p id="infoHari" class="mt-1 text-xs text-gray-400 hidden"></p>
                    </div>

                    {{-- Jam Mulai & Selesai --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="jam_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                                Jam Mulai
                            </label>

                            <input type="time" id="jam_mulai" name="jam_mulai" required
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">

                            <p id="infoJamMulai" class="mt-1 text-xs text-black hidden">
                                Default hari kerja: 16:01
                            </p>
                        </div>

                        <div id="wrapperJamSelesai">
                            <label for="jam_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                                Jam Selesai
                            </label>

                            <input type="time" id="jam_selesai" name="jam_selesai"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">

                            <p id="infoJamSelesai" class="hidden mt-1 text-xs text-gray-400">
                                Jam selesai ditentukan otomatis oleh sistem sesuai jam pulang kantor.
                            </p>
                        </div>
                    </div>

                    {{-- Preview durasi --}}
                    <p id="previewDurasi" class="text-xs text-gray-500 hidden">
                        Estimasi:
                        <span id="durasiLabel" class="font-semibold text-gray-800"></span>
                    </p>

                    {{-- Uraian --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="uraian" class="block text-sm font-medium text-gray-700">
                                Uraian Kegiatan
                            </label>
                            <span id="uraianCount" class="text-xs text-gray-400">0 / 2000</span>
                        </div>

                        <textarea id="uraian" name="uraian" rows="4" maxlength="2000" required
                            placeholder="Contoh: Penyusunan laporan bulanan..."
                            class="w-full resize-y rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
                    </div>

                    {{-- Tanda Tangan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanda Tangan
                        </label>

                        <div class="border border-gray-300 rounded-md overflow-hidden bg-gray-50">
                            <canvas id="signatureCanvas"
                                class="w-full h-[180px] sm:h-[210px] touch-none"
                                height="210">
                            </canvas>
                        </div>

                        <div class="flex justify-end mt-2">
                            <button type="button" id="btnClearSignature"
                                class="text-xs text-gray-500 hover:text-red-500 underline">
                                Hapus Tanda Tangan
                            </button>
                        </div>

                        <input type="hidden" name="signature" id="signatureData">

                        <p id="signatureError" class="mt-1 text-xs text-red-500 hidden">
                            Tanda tangan wajib diisi.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                        <button type="button" id="btnCancel"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>

                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white">
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

    // =====================
    // HELPER
    // =====================
    const now = new Date();

    function pad2(n) { return String(n).padStart(2, '0'); }

    function makeBtn(text, cls, onClick) {
        const b = document.createElement('button');
        b.type        = 'button';
        b.textContent = text;
        b.className   = cls;
        b.addEventListener('click', (e) => { e.stopPropagation(); onClick(); });
        return b;
    }

    // Tanda tangan
    const canvas = document.getElementById('signatureCanvas');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(0, 0, 0, 0)',
        penColor: 'rgb(17, 24, 39)'
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }
    window.addEventListener('resize', resizeCanvas);

    document.getElementById('btnClearSignature').addEventListener('click', () => {
        signaturePad.clear();
    });

    document.getElementById('formAjukan').addEventListener('submit', function (e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            document.getElementById('signatureError').classList.remove('hidden');
            return;
        }
        document.getElementById('signatureError').classList.add('hidden');
        document.getElementById('signatureData').value = signaturePad.toDataURL('image/png');
    });

    // =====================
    // MODAL AJUKAN
    // =====================
    const modal     = document.getElementById('modalAjukan');
    const btnAjukan = document.getElementById('btnAjukan');
    const btnClose  = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancel');
    const overlay   = document.getElementById('modalOverlay');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('wrapperJamSelesai').classList.remove('hidden');
        document.getElementById('jam_selesai').value = '';
        document.getElementById('jam_mulai').value   = '';
        document.getElementById('tanggal').value     = '';
        document.getElementById('infoHari').classList.add('hidden');
        document.getElementById('infoJamMulai').classList.add('hidden');
        setTimeout(resizeCanvas, 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => openModal());
    @endif

    btnAjukan.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    document.getElementById('approver_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kode_tim').value = selected.dataset.kode || '';
    });

    const hariLiburDB = @json($hariLibur ?? []);

    document.getElementById('tanggal').addEventListener('change', function () {
        const date      = new Date(this.value);
        const dayOfWeek = date.getUTCDay();
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const isFriday  = (dayOfWeek === 5);
        const isLibur   = hariLiburDB.includes(this.value);

        const infoHari        = document.getElementById('infoHari');
        const infoMulai       = document.getElementById('infoJamMulai');
        const jamMulai        = document.getElementById('jam_mulai');
        const wrapper         = document.getElementById('wrapperJamSelesai');
        const jamSelesaiInput = document.getElementById('jam_selesai');

        infoHari.classList.remove('hidden');

        if (isWeekend || isLibur) {
            infoHari.textContent = '📅 Hari libur — jam mulai bebas';
            infoHari.className   = 'mt-1 text-xs text-black';
            infoMulai.classList.add('hidden');
            jamMulai.value = '';
            jamMulai.removeAttribute('min');
        } else if (isFriday) {
            infoHari.textContent  = '📅 Hari Jumat — jam mulai default 16:31';
            infoHari.className    = 'mt-1 text-xs text-black';
            infoMulai.textContent = 'Default hari Jumat: 16:31';
            infoMulai.classList.remove('hidden');
            jamMulai.value = '16:31';
            jamMulai.setAttribute('min', '16:31');
        } else {
            infoHari.textContent  = '📅 Hari kerja — jam mulai default 16:01';
            infoHari.className    = 'mt-1 text-xs text-black';
            infoMulai.textContent = 'Default hari kerja: 16:01';
            infoMulai.classList.remove('hidden');
            jamMulai.value = '16:01';
            jamMulai.setAttribute('min', '16:01');
        }

        wrapper.classList.remove('hidden');
        jamSelesaiInput.value = '';
        hitungDurasi();
    });

    function hitungDurasi() {
        const mulai   = document.getElementById('jam_mulai').value;
        const selesai = document.getElementById('jam_selesai').value;
        const preview = document.getElementById('previewDurasi');
        const label   = document.getElementById('durasiLabel');

        if (!mulai || !selesai) { preview.classList.add('hidden'); return; }

        const [jm, mm]   = mulai.split(':').map(Number);
        const [js, ms]   = selesai.split(':').map(Number);
        const totalMenit = (js * 60 + ms) - (jm * 60 + mm);

        if (totalMenit <= 0) { preview.classList.add('hidden'); return; }

        const jam   = Math.floor(totalMenit / 60);
        const menit = totalMenit % 60;

        let info  = `${jam} jam ${menit > 0 ? menit + ' menit' : ''} (dihitung ${jam} jam)`;
        let warna = 'text-gray-500';

        if (jam < 2) {
            info  += ' — ⚠️ Pengajuan jam lembur minimal 2 jam';
            warna  = 'text-amber-500';
        } else if (jam >= 6) {
            info  += ' — ⚠️ Maksimal lembur 6 jam';
            warna  = 'text-amber-500';
        }

        label.textContent = info;
        preview.className = `text-xs ${warna}`;
        preview.classList.remove('hidden');
    }

    document.getElementById('jam_mulai').addEventListener('change', function () {
        const jamSelesai = document.getElementById('jam_selesai');
        if (jamSelesai.value && jamSelesai.value <= this.value) {
            alert('Jam selesai harus setelah jam mulai');
            jamSelesai.value = '';
        }
        if (this.value) jamSelesai.setAttribute('min', this.value);
        hitungDurasi();
    });

    document.getElementById('jam_selesai').addEventListener('change', function () {
        const jamMulai = document.getElementById('jam_mulai').value;
        if (jamMulai && this.value <= jamMulai) {
            alert('Jam selesai harus setelah jam mulai');
            this.value = '';
            return;
        }
        hitungDurasi();
    });

    // =====================
    // STATE FILTER
    // =====================
    let selectedNip     = @json(request('nip')) || null;
    let selectedTimId   = @json(request('tim')) || null;
    let selectedTanggal = @json(request('tanggal')) || null;
    let selectedStatus  = @json(request('status')) || null;
    let selectedSort    = @json(request('sort', 'priority')) || 'priority';
    let cachedTim       = [];
    let cachedPegawai   = [];

    // =====================
    // APPLY FILTER (redirect ke URL)
    // =====================
    function applyFilter() {
        const params = new URLSearchParams();
        if (selectedTimId)   params.set('tim',     selectedTimId);
        if (selectedNip)     params.set('nip',     selectedNip);
        if (selectedTanggal) params.set('tanggal', selectedTanggal);
        if (selectedStatus)  params.set('status',  selectedStatus);
        if (selectedSort && selectedSort !== 'priority') params.set('sort', selectedSort);
        window.location.href = '?' + params.toString();
    }

    window.selectStatus = function (status) {
        selectedStatus = status || null;
        applyFilter();
    };

    window.onHeaderSortChange = function (sortVal) {
        selectedSort = sortVal;
        applyFilter();
    };

    // =====================
    // UPDATE EXPORT LINK
    // =====================
    function updateExportLink() {
        const tim    = selectedTimId   ?? '';
        const nip    = selectedNip     ?? '';
        const status = selectedStatus  ?? '';
        const sort   = selectedSort    ?? 'priority';
        const bulan  = selectedTanggal
            ? selectedTanggal.slice(0, 7)
            : `${now.getFullYear()}-${pad2(now.getMonth() + 1)}`;

        document.getElementById('btnExport').href =
            `/admin/lembur/export?bulan=${bulan}&tim=${tim}&nip=${nip}&status=${status}&sort=${sort}`;
    }

    // =====================
    // FETCH DATA
    // =====================
    async function fetchTim() {
        const res = await fetch('/admin/presensi/tim');
        cachedTim = await res.json();
        populateDropdownTim('', cachedTim);
    }

    async function fetchPegawai(kodeTim = '') {
        const url     = kodeTim ? `/admin/presensi/pegawai?kode_tim=${kodeTim}` : '/admin/presensi/pegawai';
        const res     = await fetch(url);
        cachedPegawai = await res.json();
        populateDropdown('', cachedPegawai);
    }

    // =====================
    // TUTUP SEMUA DROPDOWN
    // =====================
    function tutupSemuaDropdown() {
        document.getElementById('dropdownPegawai')?.classList.add('hidden');
        document.getElementById('dropdownTim')?.classList.add('hidden');
    }

    // =====================
    // DROPDOWN TIM
    // =====================
    function populateDropdownTim(filter = '', data = []) {
        const list = document.getElementById('listTim');
        if (!list) return;
        list.innerHTML = '';

        const liSemua = document.createElement('li');
        liSemua.className   = 'cursor-pointer px-4 py-2.5 text-xs text-gray-500 hover:bg-gray-50 flex items-center justify-between ' + (!selectedTimId ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
        liSemua.innerHTML   = '<span>Semua tim</span>' + (!selectedTimId ? '<svg class="w-3.5 h-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
        liSemua.addEventListener('mousedown', (e) => { e.preventDefault(); pilihTim(null); });
        list.appendChild(liSemua);

        const keyword = (filter || '').toLowerCase().trim();
        const filtered = data.filter(t => {
            if (!keyword) return true;
            return (t.nama_tim || '').toLowerCase().includes(keyword) || (t.kode_tim || '').toLowerCase().includes(keyword);
        });

        if (filtered.length === 0) {
            const liEmpty = document.createElement('li');
            liEmpty.className = 'px-4 py-3 text-xs text-gray-400 text-center';
            liEmpty.textContent = 'Tim tidak ditemukan';
            list.appendChild(liEmpty);
            return;
        }

        filtered.forEach(tim => {
            const isSelected = selectedTimId === tim.kode_tim;
            const li = document.createElement('li');
            li.className   = 'cursor-pointer px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center justify-between ' + (isSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
            li.innerHTML   = `<span>${tim.nama_tim}</span>` + (isSelected ? '<svg class="w-3.5 h-3.5 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
            li.addEventListener('mousedown', (e) => { e.preventDefault(); pilihTim(tim); });
            list.appendChild(li);
        });
    }

    window.openDropdownTim = function () {
        const dd     = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');
        if (!dd || !search) return;

        tutupSemuaDropdown();
        populateDropdownTim('', cachedTim); // Always full list!
        dd.classList.remove('hidden');
        setTimeout(() => search.select(), 10);
    };

    window.toggleDropdownTim = function () {
        const dd = document.getElementById('dropdownTim');
        if (!dd) return;

        if (dd.classList.contains('hidden')) {
            openDropdownTim();
        } else {
            dd.classList.add('hidden');
        }
    };

    window.filterDropdownTim = function () {
        const search = document.getElementById('searchTim');
        populateDropdownTim(search.value, cachedTim);
        document.getElementById('dropdownTim')?.classList.remove('hidden');

        const btnClear = document.getElementById('btnClearTim');
        if (btnClear) {
            search.value.trim() ? btnClear.classList.remove('hidden') : (selectedTimId ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden'));
        }
    };

    function pilihTim(tim) {
        selectedTimId = tim ? tim.kode_tim : null;
        const search = document.getElementById('searchTim');
        if (search) search.value = tim ? tim.nama_tim : '';
        document.getElementById('dropdownTim')?.classList.add('hidden');

        const btnClear = document.getElementById('btnClearTim');
        if (btnClear) {
            selectedTimId ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden');
        }

        selectedNip = null;
        const searchPeg = document.getElementById('searchPegawai');
        if (searchPeg) searchPeg.value = '';
        document.getElementById('btnClearPegawai')?.classList.add('hidden');

        if (tim) fetchPegawai(tim.kode_tim);
        else     fetchPegawai();
        applyFilter();
    }

    // =====================
    // DROPDOWN PEGAWAI
    // =====================
    function populateDropdown(filter = '', data = []) {
        const list = document.getElementById('listPegawai');
        if (!list) return;
        list.innerHTML = '';

        const liSemua = document.createElement('li');
        liSemua.className   = 'cursor-pointer px-4 py-2.5 text-xs text-gray-500 hover:bg-gray-50 flex items-center justify-between ' + (!selectedNip ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
        liSemua.innerHTML   = '<span>Semua pegawai</span>' + (!selectedNip ? '<svg class="w-3.5 h-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
        liSemua.addEventListener('mousedown', (e) => { e.preventDefault(); pilihPegawai(null); });
        list.appendChild(liSemua);

        const keyword = (filter || '').toLowerCase().trim();
        const filtered = data.filter(e => {
            if (!keyword) return true;
            return `${e.nama || ''} ${e.nip || ''}`.toLowerCase().includes(keyword);
        });

        if (filtered.length === 0) {
            const liEmpty = document.createElement('li');
            liEmpty.className = 'px-4 py-3 text-xs text-gray-400 text-center';
            liEmpty.textContent = 'Pegawai tidak ditemukan';
            list.appendChild(liEmpty);
            return;
        }

        filtered.forEach(emp => {
            const isSelected = selectedNip === emp.nip;
            const li = document.createElement('li');
            li.className   = 'cursor-pointer px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center justify-between ' + (isSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
            li.innerHTML   = `<span>${emp.nama} (${emp.nip})</span>` + (isSelected ? '<svg class="w-3.5 h-3.5 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
            li.addEventListener('mousedown', (e) => { e.preventDefault(); pilihPegawai(emp); });
            list.appendChild(li);
        });
    }

    window.openDropdown = function () {
        const dd     = document.getElementById('dropdownPegawai');
        const search = document.getElementById('searchPegawai');
        if (!dd || !search) return;

        tutupSemuaDropdown();
        populateDropdown('', cachedPegawai); // Always full list!
        dd.classList.remove('hidden');
        setTimeout(() => search.select(), 10);
    };

    window.toggleDropdown = function () {
        const dd = document.getElementById('dropdownPegawai');
        if (!dd) return;

        if (dd.classList.contains('hidden')) {
            openDropdown();
        } else {
            dd.classList.add('hidden');
        }
    };

    window.filterDropdown = function () {
        const search = document.getElementById('searchPegawai');
        populateDropdown(search.value, cachedPegawai);
        document.getElementById('dropdownPegawai')?.classList.remove('hidden');

        const btnClear = document.getElementById('btnClearPegawai');
        if (btnClear) {
            search.value.trim() ? btnClear.classList.remove('hidden') : (selectedNip ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden'));
        }
    };

    function pilihPegawai(emp) {
        selectedNip = emp ? emp.nip : null;
        const search = document.getElementById('searchPegawai');
        if (search) search.value = emp ? `${emp.nama} (${emp.nip})` : '';
        document.getElementById('dropdownPegawai')?.classList.add('hidden');

        const btnClear = document.getElementById('btnClearPegawai');
        if (btnClear) {
            selectedNip ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden');
        }

        applyFilter();
    }

    // Close dropdowns when clicked outside and restore active selection text if untouched
    document.addEventListener('click', (e) => {
        const wrapPeg = document.getElementById('wrapSearchPegawai');
        const ddPeg = document.getElementById('dropdownPegawai');
        const searchPeg = document.getElementById('searchPegawai');
        if (wrapPeg && ddPeg && searchPeg && !wrapPeg.contains(e.target)) {
            ddPeg.classList.add('hidden');
            if (selectedNip) {
                const emp = cachedPegawai.find(emp => emp.nip === selectedNip);
                if (emp) searchPeg.value = `${emp.nama} (${emp.nip})`;
            } else {
                searchPeg.value = '';
            }
        }

        const wrapTim = document.getElementById('wrapSearchTim');
        const ddTim = document.getElementById('dropdownTim');
        const searchTim = document.getElementById('searchTim');
        if (wrapTim && ddTim && searchTim && !wrapTim.contains(e.target)) {
            ddTim.classList.add('hidden');
            if (selectedTimId) {
                const tim = cachedTim.find(tim => tim.kode_tim === selectedTimId);
                if (tim) searchTim.value = tim.nama_tim;
            } else {
                searchTim.value = '';
            }
        }
    });

    // =====================
    // DATE PICKER
    // =====================
    (function () {
        const el = (id) => document.getElementById(id);

        const picker    = el('datePicker');
        const btn       = el('dateBtn');
        const panel     = el('datePanel');
        const grid      = el('dateGrid');
        const navLabel  = el('dateNavLabel');
        const dateLabel = el('dateLabel');
        const dateValue = el('dateValue');
        const btnPrev   = el('datePrev');
        const btnNext   = el('dateNext');
        const btnToday  = el('btnToday');
        const btnClose  = el('btnDateClose');

        if (!picker || !btn || !panel) return;

        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const dayNames   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

        let view      = 'day';
        let viewYear  = now.getFullYear();
        let viewMonth = now.getMonth();
        let selYear   = null;
        let selMonth  = null;
        let selDay    = null;

        function setDate(y, m, d) {
            selYear  = y;
            selMonth = m;
            selDay   = d;

            const tanggalStr      = `${y}-${pad2(m + 1)}-${pad2(d)}`;
            dateLabel.textContent = `${d} ${monthShort[m]} ${y}`;
            dateValue.value       = tanggalStr;
            selectedTanggal       = tanggalStr;

            updateExportLink();
            applyFilter();
        }

        function openPanel() {
            viewYear  = now.getFullYear();
            viewMonth = now.getMonth();
            renderDay();
            panel.classList.remove('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        function renderDay() {
            view = 'day';
            navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
            navLabel.className   = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
            grid.innerHTML = '';

            const header = document.createElement('div');
            header.className = 'grid grid-cols-7 mb-1';
            dayNames.forEach(d => {
                const span = document.createElement('span');
                span.className   = 'text-center text-xs text-gray-400 py-1';
                span.textContent = d;
                header.appendChild(span);
            });
            grid.appendChild(header);

            const dayGrid     = document.createElement('div');
            dayGrid.className = 'grid grid-cols-7 gap-y-1';
            const base        = 'text-sm rounded-lg py-1 transition border ';

            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const firstDay    = new Date(viewYear, viewMonth, 1).getDay();
            const daysInPrev  = new Date(viewYear, viewMonth, 0).getDate();

            // Trailing days from previous month
            for (let i = firstDay - 1; i >= 0; i--) {
                const d = daysInPrev - i;
                dayGrid.appendChild(makeBtn(d, base + 'border-transparent text-gray-300', () => {
                    let m = viewMonth - 1, y = viewYear;
                    if (m < 0) { m = 11; y--; }
                    setDate(y, m, d);
                    viewYear  = y;
                    viewMonth = m;
                    renderDay();
                }));
            }

            // Days in current month
            for (let d = 1; d <= daysInMonth; d++) {
                const isSelected = (selDay === d && selMonth === viewMonth && selYear === viewYear);
                const isToday    = (d === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear());
                const cls = isSelected
                    ? 'bg-[#faa938] text-white border-[#faa938]'
                    : isToday
                        ? 'bg-[#faa938]/20 text-[#faa938] border-transparent'
                        : 'border-transparent text-gray-700 hover:border-[#faa938] hover:text-[#faa938]';
                const _d = d;
                dayGrid.appendChild(makeBtn(_d, base + cls, () => {
                    setDate(viewYear, viewMonth, _d);
                    closePanel();
                }));
            }

            // Leading days for next month
            const total     = firstDay + daysInMonth;
            const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);
            for (let d = 1; d <= remaining; d++) {
                const _d = d;
                dayGrid.appendChild(makeBtn(_d, base + 'border-transparent text-gray-300', () => {
                    let m = viewMonth + 1, y = viewYear;
                    if (m > 11) { m = 0; y++; }
                    setDate(y, m, _d);
                    viewYear  = y;
                    viewMonth = m;
                    renderDay();
                }));
            }

            grid.appendChild(dayGrid);
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className   = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
            grid.innerHTML = '';

            const g = document.createElement('div');
            g.className = 'grid grid-cols-3 gap-2';

            monthNames.forEach((name, m) => {
                const isSelected = (m === selMonth && viewYear === selYear);
                const isNow      = (m === now.getMonth() && viewYear === now.getFullYear());
                const cls = isSelected
                    ? 'bg-[#faa938] text-white border-[#faa938]'
                    : isNow
                        ? 'border-[#faa938] text-[#faa938] bg-white'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';
                g.appendChild(makeBtn(name.slice(0, 3), 'px-2 py-2 text-sm rounded-lg border transition ' + cls, () => {
                    viewMonth = m;
                    renderDay();
                }));
            });

            grid.appendChild(g);
        }

        function renderYear() {
            view = 'year';
            const startYear = Math.floor(viewYear / 12) * 12;
            navLabel.textContent = `${startYear} - ${startYear + 11}`;
            navLabel.className   = 'text-sm font-medium text-gray-400 select-none cursor-default';
            grid.innerHTML = '';

            const g = document.createElement('div');
            g.className = 'grid grid-cols-3 gap-2';

            for (let y = startYear; y < startYear + 12; y++) {
                const isSelected = (y === selYear);
                const isNow      = (y === now.getFullYear());
                const cls = isSelected
                    ? 'bg-[#faa938] text-white border-[#faa938]'
                    : isNow
                        ? 'border-[#faa938] text-[#faa938] bg-white'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';
                const _y = y;
                g.appendChild(makeBtn(_y, 'px-2 py-2 text-sm rounded-lg border transition ' + cls, () => {
                    viewYear = _y;
                    renderMonth();
                }));
            }

            grid.appendChild(g);
        }

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.contains('hidden') ? openPanel() : closePanel();
        });

        navLabel.addEventListener('click', (e) => {
            e.stopPropagation();
            if (view === 'day')        renderMonth();
            else if (view === 'month') renderYear();
        });

        btnPrev?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (view === 'day')        { viewMonth--; if (viewMonth < 0)  { viewMonth = 11; viewYear--; } renderDay(); }
            else if (view === 'month') { viewYear--;  renderMonth(); }
            else if (view === 'year')  { viewYear -= 12; renderYear(); }
        });

        btnNext?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (view === 'day')        { viewMonth++; if (viewMonth > 11) { viewMonth = 0; viewYear++; } renderDay(); }
            else if (view === 'month') { viewYear++;  renderMonth(); }
            else if (view === 'year')  { viewYear += 12; renderYear(); }
        });

        btnToday?.addEventListener('click', (e) => {
            e.stopPropagation();
            viewYear  = now.getFullYear();
            viewMonth = now.getMonth();
            setDate(now.getFullYear(), now.getMonth(), now.getDate());
            closePanel();
        });

        btnClose?.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) closePanel();
        });
    })();

    // =====================
    // MODAL DOKUMENTASI
    // =====================
    window.openModalDok = function (idTransaksi) {
        const base = "{{ url('admin/lembur') }}";
        const form = document.getElementById('formDok');
        if (form) {
            form.action = `${base}/${idTransaksi}/dokumentasi`;
            const input = form.querySelector('[name="file_path"]');
            if (input) {
                input.type = 'url';
                input.value = '';
            }
        }
        document.getElementById('modalDok')?.classList.remove('hidden');
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

    const formDokAdmin = document.getElementById('formDok');
    if (formDokAdmin) {
        const inputDok = formDokAdmin.querySelector('[name="file_path"]');
        if (inputDok) {
            inputDok.addEventListener('blur', function () {
                let val = this.value.trim();
                if (val && !/^https?:\/\//i.test(val)) {
                    this.value = 'https://' + val;
                }
            });
        }

        formDokAdmin.addEventListener('submit', function () {
            const input = formDokAdmin.querySelector('[name="file_path"]');
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

    // =====================
    // MODAL AKSI & KELOLA PENGAJUAN (ADMIN)
    // =====================
    window.switchAksiTab = function (tab) {
        const tabEdit = document.getElementById('tabBtnEditUraian');
        const tabBatal = document.getElementById('tabBtnBatalPengajuan');
        const panelEdit = document.getElementById('panelEditUraian');
        const panelBatal = document.getElementById('panelBatalPengajuan');

        if (tab === 'edit') {
            tabEdit.className = 'pb-2.5 px-3 text-xs font-semibold border-b-2 border-[#faa938] text-gray-900 transition-colors cursor-pointer flex items-center gap-1.5';
            tabBatal.className = 'pb-2.5 px-3 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-rose-600 transition-colors cursor-pointer flex items-center gap-1.5';
            panelEdit.classList.remove('hidden');
            panelBatal.classList.add('hidden');
        } else {
            tabBatal.className = 'pb-2.5 px-3 text-xs font-semibold border-b-2 border-rose-500 text-rose-600 transition-colors cursor-pointer flex items-center gap-1.5';
            tabEdit.className = 'pb-2.5 px-3 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-900 transition-colors cursor-pointer flex items-center gap-1.5';
            panelBatal.classList.remove('hidden');
            panelEdit.classList.add('hidden');
            setTimeout(() => document.getElementById('batalAlasan').focus(), 50);
        }
    };

    window.openModalAksiAdmin = function (idTransaksi, namaPegawai, tanggal, uraian) {
        document.getElementById('aksiIdTransaksi').value = idTransaksi;
        document.getElementById('batalIdTransaksi').value = idTransaksi;
        document.getElementById('aksiSubtitleAdmin').textContent = `${namaPegawai || '-'} • ${tanggal || '-'}`;

        const uraianInput = document.getElementById('aksiUraianInput');
        uraianInput.value = uraian || '';
        document.getElementById('aksiUraianCounter').textContent = `${(uraian || '').length} / 2000`;
        document.getElementById('aksiUraianError').classList.add('hidden');

        document.getElementById('batalAlasan').value = '';
        document.getElementById('batalAlasanError').classList.add('hidden');

        const btnSubmitBatal = document.getElementById('btnSubmitBatalAdmin');
        btnSubmitBatal.disabled = false;
        btnSubmitBatal.innerHTML = '<span>Ya, Batalkan Pengajuan</span>';

        const btnSubmitEdit = document.getElementById('btnSubmitEditUraian');
        btnSubmitEdit.disabled = false;
        btnSubmitEdit.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span>Simpan Uraian</span>
        `;

        switchAksiTab('edit');

        document.getElementById('modalAksiAdmin').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalAksiAdmin = function () {
        document.getElementById('modalAksiAdmin').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // Alias backward-compatibility
    window.openModalBatalAdmin = function (idTransaksi, namaPegawai, tanggal) {
        openModalAksiAdmin(idTransaksi, namaPegawai, tanggal, '');
        switchAksiTab('batal');
    };
    window.closeModalBatalAdmin = window.closeModalAksiAdmin;

    window.submitAksiEditUraian = function () {
        const id = document.getElementById('aksiIdTransaksi').value;
        const uraian = document.getElementById('aksiUraianInput').value.trim();
        const errorEl = document.getElementById('aksiUraianError');

        if (!uraian) {
            errorEl.textContent = 'Uraian kegiatan wajib diisi.';
            errorEl.classList.remove('hidden');
            return;
        }

        const base = "{{ url('admin/lembur') }}";
        const form = document.getElementById('formEditUraian');
        form.action = `${base}/${id}/uraian`;
        document.getElementById('inputUraian').value = uraian;
        form.submit();
    };

    document.getElementById('aksiUraianInput')?.addEventListener('input', function () {
        const counter = document.getElementById('aksiUraianCounter');
        if (counter) counter.textContent = `${this.value.length} / 2000`;
    });

    window.submitBatalAdmin = async function (e) {
        e.preventDefault();
        const id = document.getElementById('batalIdTransaksi').value;
        const alasan = document.getElementById('batalAlasan').value.trim();
        const errorEl = document.getElementById('batalAlasanError');
        const btnSubmit = document.getElementById('btnSubmitBatalAdmin');

        if (!alasan) {
            errorEl.textContent = 'Alasan pembatalan wajib diisi.';
            errorEl.classList.remove('hidden');
            return;
        }

        errorEl.classList.add('hidden');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <svg class="animate-spin h-3.5 w-3.5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memproses...</span>
        `;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('#formBatalAdmin input[name="_token"]')?.value;

            const res = await fetch(`/admin/lembur/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ alasan: alasan, alasan_batal: alasan })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal membatalkan pengajuan');
            }

            const statusCell = document.getElementById(`status-cell-${id}`);
            if (statusCell) {
                statusCell.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span><span>Dibatalkan</span></span>`;
            }

            const noteCell = document.getElementById(`note-cell-${id}`);
            if (noteCell) {
                noteCell.innerHTML = `<span class="italic text-gray-600 leading-relaxed">${data.note || `[Dibatalkan Admin] ${alasan}`}</span>`;
            }

            const aksiCell = document.getElementById(`aksi-cell-${id}`);
            if (aksiCell) {
                aksiCell.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-gray-400 bg-gray-50 border border-gray-200/60 italic">Dibatalkan</span>`;
            }

            closeModalAksiAdmin();

            const alertBox = document.createElement('div');
            alertBox.className = 'fixed bottom-5 right-5 z-50 bg-emerald-600 text-white text-xs px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300';
            alertBox.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>${data.message || 'Pengajuan lembur berhasil dibatalkan.'}</span>
            `;
            document.body.appendChild(alertBox);
            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 300);
            }, 3000);

        } catch (err) {
            errorEl.textContent = err.message || 'Terjadi kesalahan sistem.';
            errorEl.classList.remove('hidden');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>Ya, Batalkan Pengajuan</span>';
        }
    };

    // =====================
    // MODAL PRESENSI (ADMIN)
    // =====================
    window.openModalPresensiAdmin = function (idTransaksi) {
        const modal = document.getElementById('modalPresensiAdmin');
        const subtitle = document.getElementById('presensiSubtitleAdmin');
        const body = document.getElementById('presensiBodyAdmin');

        subtitle.textContent = 'Memuat data...';
        body.innerHTML = `
            <div class="flex items-center justify-center py-6 text-gray-400 gap-2">
                <svg class="animate-spin h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs">Memuat informasi presensi...</span>
            </div>
        `;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        fetch(`/admin/pengajuan/${idTransaksi}/presensi`)
            .then(res => res.json())
            .then(data => {
                subtitle.textContent = `${data.nama} — ${data.nip}`;

                if (!data.status && !data.jam_masuk && !data.jam_pulang) {
                    body.innerHTML = `
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-center">
                            <p class="text-xs font-semibold text-amber-800">Tidak ada data presensi</p>
                            <p class="text-[11px] text-amber-600 mt-0.5">Pegawai belum tercatat hadir pada tanggal ${data.tanggal ?? '-'}.</p>
                        </div>
                    `;
                    return;
                }

                body.innerHTML = `
                    <div class="space-y-3">
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs space-y-1">
                            <div class="flex justify-between text-gray-500">
                                <span>Tanggal Presensi:</span>
                                <span class="font-semibold text-gray-900">${data.tanggal ?? '-'}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Status Kehadiran:</span>
                                <span class="font-semibold uppercase text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full text-[10px]">${data.status ?? '-'}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 border border-gray-200 rounded-xl bg-white text-center">
                                <span class="text-[11px] text-gray-400 font-medium">Jam Masuk</span>
                                <div class="text-base font-bold text-gray-900 mt-0.5 font-mono">${data.jam_masuk ?? '-'}</div>
                            </div>
                            <div class="p-3 border border-gray-200 rounded-xl bg-white text-center">
                                <span class="text-[11px] text-gray-400 font-medium">Jam Pulang</span>
                                <div class="text-base font-bold text-gray-900 mt-0.5 font-mono">${data.jam_pulang ?? '-'}</div>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = `
                    <p class="text-xs text-rose-500 text-center py-4">Gagal memuat data presensi.</p>
                `;
            });
    };

    window.closeModalPresensiAdmin = function () {
        document.getElementById('modalPresensiAdmin').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // =====================
    // INIT
    // =====================
    document.addEventListener('DOMContentLoaded', function () {
        fetchTim();
        fetchPegawai();
        updateExportLink();

        // Setup Mobile Status Custom Dropdown
        const btnMobileStatus     = document.getElementById('btnMobileStatus');
        const menuMobileStatus    = document.getElementById('menuMobileStatus');
        const iconChevronStatus   = document.getElementById('iconChevronStatus');
        const wrapperMobileStatus = document.getElementById('mobileStatusWrapper');

        if (btnMobileStatus && menuMobileStatus) {
            btnMobileStatus.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isHidden = menuMobileStatus.classList.contains('hidden');
                if (isHidden) {
                    menuMobileStatus.classList.remove('hidden');
                    iconChevronStatus?.classList.add('rotate-180');
                } else {
                    menuMobileStatus.classList.add('hidden');
                    iconChevronStatus?.classList.remove('rotate-180');
                }
            });

            document.addEventListener('click', function (e) {
                if (wrapperMobileStatus && !wrapperMobileStatus.contains(e.target)) {
                    menuMobileStatus.classList.add('hidden');
                    iconChevronStatus?.classList.remove('rotate-180');
                }
            });
        }

        // Restore state dari URL
        const params       = new URLSearchParams(window.location.search);
        const timParam     = params.get('tim')     ?? '';
        const nipParam     = params.get('nip')     ?? '';
        const tanggalParam = params.get('tanggal') ?? '';
        const statusParam  = params.get('status')  ?? '';
        const sortParam    = params.get('sort')    ?? '';

        if (statusParam) {
            selectedStatus = statusParam;
        }

        if (sortParam) {
            selectedSort = sortParam;
        }

        if (timParam) {
            selectedTimId = timParam;
            fetch('/admin/presensi/tim')
                .then(r => r.json())
                .then(data => {
                    const found = data.find(t => t.kode_tim == timParam);
                    if (found) document.getElementById('searchTim').value = found.nama_tim;
                });
        }

        if (nipParam) {
            selectedNip = nipParam;
            fetch('/admin/presensi/pegawai')
                .then(r => r.json())
                .then(data => {
                    const found = data.find(e => e.nip == nipParam);
                    if (found) document.getElementById('searchPegawai').value = `${found.nama} - ${found.nip}`;
                });
        }

        if (tanggalParam) {
            selectedTanggal = tanggalParam;
            const parts = tanggalParam.split('-');
            if (parts.length === 3) {
                const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                const y = parseInt(parts[0]);
                const m = parseInt(parts[1]) - 1;
                const d = parseInt(parts[2]);
                const dateLabel = document.getElementById('dateLabel');
                if (dateLabel) dateLabel.textContent = `${d} ${monthShort[m]} ${y}`;
            }
            updateExportLink();
        }

        // Tutup dropdown saat klik di luar
        document.addEventListener('click', function (e) {
            const wrapperTim = document.getElementById('searchTim')?.closest('.relative');
            if (wrapperTim && !wrapperTim.contains(e.target))
                document.getElementById('dropdownTim').classList.add('hidden');

            const wrapperPegawai = document.getElementById('searchPegawai')?.closest('.relative');
            if (wrapperPegawai && !wrapperPegawai.contains(e.target))
                document.getElementById('dropdownPegawai').classList.add('hidden');
        });
    });
})();

    document.getElementById('uraian')?.addEventListener('input', function () {
        const elCount = document.getElementById('uraianCount');
        if (elCount) elCount.textContent = `${this.value.length} / 2000`;
    });

    //Ubah Uraian Lembur
    window.startEditUraian = function (id) {
        document.getElementById('view-' + id).classList.add('hidden');
        document.getElementById('edit-' + id).classList.remove('hidden');
        document.getElementById('edit-' + id).querySelector('textarea').focus();
    };

    window.cancelEditUraian = function (id) {
        document.getElementById('edit-' + id).classList.add('hidden');
        document.getElementById('view-' + id).classList.remove('hidden');
    };

    window.submitEditUraian = function (id) {
        const uraian = document.getElementById('textarea-' + id).value;
        const base   = "{{ url('admin/lembur') }}";
        const form   = document.getElementById('formEditUraian');
        form.action  = `${base}/${id}/uraian`;
        document.getElementById('inputUraian').value = uraian;

    form.submit();
};

    // Toggle Mobile Filter Accordion
    window.toggleMobileFilter = function () {
        const collapse = document.getElementById('mobileFilterCollapse');
        const chevron  = document.getElementById('iconChevronFilter');
        if (!collapse) return;

        const isHidden = collapse.classList.contains('hidden');
        if (isHidden) {
            collapse.classList.remove('hidden');
            collapse.classList.add('flex');
            chevron?.classList.add('rotate-180');
        } else {
            collapse.classList.add('hidden');
            collapse.classList.remove('flex');
            chevron?.classList.remove('rotate-180');
        }
    };

    //Paginaation
    function changePerPage(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('perPage', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
</script>
@endpush

@endsection
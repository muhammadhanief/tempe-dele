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

    <div class="overflow-visible">

        {{-- Toolbar --}}
        <div class="max-w-7xl mx-auto w-full my-4 sm:my-5">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">

                {{-- Filter Periode --}}
                <div class="relative w-full sm:w-auto shrink-0" id="datePicker">
                    <button type="button" id="dateBtn"
                        class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors">
                        <span class="inline-flex items-center gap-2 min-w-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0">
                                <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7 64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                            </svg>
                            <span id="dateLabel" class="leading-none truncate">Semua Tanggal</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                            <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                        </svg>
                    </button>

                    <input type="hidden" id="dateValue" value="">

                    <div id="datePanel"
                        class="hidden absolute z-50 mt-2 left-0 w-full sm:w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white shadow-lg p-3">
                        <div class="flex items-center justify-between mb-3">
                            <button type="button" id="datePrev"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                                </svg>
                            </button>

                            <span id="dateNavLabel"
                                class="text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none">
                            </span>

                            <button type="button" id="dateNext"
                                class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                    <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                                </svg>
                            </button>
                        </div>

                        <div id="dateGrid"></div>

                        <div class="flex items-center justify-between mt-3">
                            <button type="button" id="btnToday"
                                class="text-sm font-medium text-gray-500 hover:text-[#faa938]">
                                Hari ini
                            </button>
                            <button type="button" id="btnDateClose"
                                class="px-3 py-1 text-sm font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938]">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filter Pegawai --}}
                <div class="relative w-full lg:flex-1 lg:min-w-[260px]">
                    <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                        onclick="toggleDropdown()" oninput="filterDropdown()" autocomplete="off"
                        class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                            <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                        </svg>
                    </div>
                    <div id="dropdownPegawai"
                        class="hidden absolute z-20 mt-1 w-full max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                        <ul id="listPegawai"></ul>
                    </div>
                </div>

                {{-- Filter Tim --}}
                <div class="relative w-full lg:w-60 shrink-0">
                    <input type="text" id="searchTim" placeholder="Cari nama tim..."
                        onclick="toggleDropdownTim()" oninput="filterDropdownTim()" autocomplete="off"
                        value="{{ request('search') }}"
                        class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                            <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                        </svg>
                    </div>
                    <div id="dropdownTim"
                        class="hidden absolute z-20 mt-1 w-full max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                        <ul id="listTim"></ul>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 w-full lg:w-auto lg:pl-3 lg:border-l lg:border-gray-200">

                    {{-- Unduh Rekapitulasi --}}
                    <a id="btnExport"
                        href="{{ route('admin.lembur.export', ['bulan' => now()->format('Y-m'), 'tim' => request('tim'), 'nip' => request('nip'), 'status' => request('status'), 'sort' => request('sort')]) }}"
                        title="Unduh Rekapitulasi"
                        class="inline-flex h-10 flex-1 lg:flex-none lg:w-10 items-center justify-center gap-2 rounded-xl lg:rounded-full border border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        <span class="text-sm font-medium lg:hidden">Unduh</span>
                    </a>

                    {{-- Tombol Ajukan --}}
                    <a href="javascript:void(0)" id="btnAjukan"
                        class="inline-flex h-10 flex-1 lg:flex-none lg:w-10 items-center justify-center gap-2 rounded-xl lg:rounded-full bg-[#faa938] text-white hover:brightness-95 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span class="text-sm font-medium lg:hidden">Ajukan</span>
                    </a>

                </div>
            </div>
        </div>

        {{-- Status Filter Tabs / Monitoring Pills --}}
        <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1 scrollbar-none">
            {{-- Semua Status --}}
            <button type="button" onclick="selectStatus('')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap {{ empty($status) ? 'bg-slate-900 text-white border-slate-900 shadow-sm shadow-slate-900/20 ring-2 ring-slate-900/10' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                <span>Semua Status</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ empty($status) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                    {{ $statusCounts['all'] ?? 0 }}
                </span>
            </button>

            {{-- Menunggu Ketua --}}
            <button type="button" onclick="selectStatus('pending')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-sm shadow-amber-500/25 ring-2 ring-amber-500/20' : 'bg-white text-amber-700 border-amber-200 hover:bg-amber-50/80 hover:border-amber-300' }}">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'pending' ? 'bg-white opacity-75' : 'bg-amber-400 opacity-75' }}"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                </span>
                <span>Menunggu Ketua</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ $statusCounts['pending'] ?? 0 }}
                </span>
            </button>

            {{-- Menunggu Kabag --}}
            <button type="button" onclick="selectStatus('menunggu_kabag')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap {{ $status === 'menunggu_kabag' ? 'bg-blue-600 text-white border-blue-600 shadow-sm shadow-blue-600/25 ring-2 ring-blue-600/20' : 'bg-white text-blue-700 border-blue-200 hover:bg-blue-50/80 hover:border-blue-300' }}">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $status === 'menunggu_kabag' ? 'bg-white opacity-75' : 'bg-blue-400 opacity-75' }}"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $status === 'menunggu_kabag' ? 'bg-white' : 'bg-blue-600' }}"></span>
                </span>
                <span>Menunggu Kabag</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'menunggu_kabag' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                    {{ $statusCounts['menunggu_kabag'] ?? 0 }}
                </span>
            </button>

            {{-- Disetujui --}}
            <button type="button" onclick="selectStatus('approved')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap {{ $status === 'approved' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm shadow-emerald-600/25 ring-2 ring-emerald-600/20' : 'bg-white text-emerald-700 border-emerald-200 hover:bg-emerald-50/80 hover:border-emerald-300' }}">
                <span class="h-2 w-2 rounded-full {{ $status === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>Disetujui</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $statusCounts['approved'] ?? 0 }}
                </span>
            </button>

            {{-- Ditolak --}}
            <button type="button" onclick="selectStatus('rejected')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 border whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-sm shadow-rose-600/25 ring-2 ring-rose-600/20' : 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50/80 hover:border-rose-300' }}">
                <span class="h-2 w-2 rounded-full {{ $status === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                <span>Ditolak</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                    {{ $statusCounts['rejected'] ?? 0 }}
                </span>
            </button>
        </div>

        {{-- Tabel --}}
        <div class="w-full overflow-x-auto rounded-xl ring-1 ring-gray-200 bg-white">
            <table class="min-w-[1180px] lg:min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize rounded-tl-xl w-28 cursor-pointer select-none hover:bg-gray-200 transition-colors group"
                            onclick="toggleSortTanggal()"
                            title="Urutkan: {{ ($sort ?? 'desc') === 'asc' ? 'Terlama (Klik untuk Terbaru)' : 'Terbaru (Klik untuk Terlama)' }}">
                            <div class="inline-flex items-center justify-center gap-1.5 w-full">
                                <span>Tanggal</span>
                                @if(($sort ?? 'desc') === 'asc')
                                    <span class="inline-flex items-center justify-center p-0.5 rounded bg-[#faa938] text-white shadow-xs" title="Terlama ke Terbaru">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center p-0.5 rounded bg-[#faa938] text-white shadow-xs" title="Terbaru ke Terlama">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-24">Pegawai</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-24">Jam Diajukan</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-24">Jam Disetujui</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize">Uraian Kegiatan</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-36">Tim & Ketua</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-32">Status</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize w-20">Catatan</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 capitalize rounded-tr-xl w-24">Dokumentasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300" id="tabelLembur">
                    @forelse($transaksi as $t)
                        <tr class="bg-white transition-all duration-500 hover:bg-gray-50"
                            data-tanggal="{{ $t->date }}"
                            data-tim="{{ $t->tim_kode_tim }}"
                            data-nip="{{ $t->submitted_by_NIP }}">

                            <td class="px-2 py-2 text-xs text-gray-900">
                                {{ \Carbon\Carbon::parse($t->date)->translatedFormat('d F Y') }}
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 text-left">
                                {{ $t->nama_pegawai ?? '-' }}
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 text-center">
                                @if($t->jam_mulai && $t->jam_selesai)
                                    {{ substr($t->jam_mulai, 0, 5) }} - {{ substr($t->jam_selesai, 0, 5) }}
                                @elseif($t->jam_mulai)
                                    {{ substr($t->jam_mulai, 0, 5) }} - <span class="text-gray-400 italic">menunggu</span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 text-center">
                                @if($t->jam_mulai_disetujui && $t->jam_selesai_disetujui)
                                    {{ substr($t->jam_mulai_disetujui, 0, 5) }} - {{ substr($t->jam_selesai_disetujui, 0, 5) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 group relative">
                                {{-- Mode tampil --}}
                                <div class="flex items-start gap-1" id="view-{{ $t->id_transaksi }}">
                                    <span class="flex-1">{{ $t->uraian ?? '-' }}</span>
                                    <button type="button"
                                        onclick="startEditUraian({{ $t->id_transaksi }})"
                                        class="flex-shrink-0 invisible group-hover:visible text-gray-300 hover:text-amber-500 rounded-full p-1.5 transition-all duration-150"
                                        title="Edit uraian">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                        class="w-full text-xs border border-[#faa938] rounded-md px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#faa938] resize-none text-gray-800"
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

                            <td class="px-2.5 py-2 text-xs text-gray-900 text-left">
                                <div class="font-medium text-gray-900 max-w-[150px] break-words">{{ $t->nama_tim ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">Ketua: {{ $t->nama_ketua ?? '-' }}</div>
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 text-center">
                                @if($t->status === 'pending')
                                    <span class="bg-amber-100 rounded-full px-2.5 text-xs text-amber-700 py-0.5 whitespace-nowrap font-medium">Menunggu Ketua</span>
                                @elseif($t->status === 'menunggu_kabag')
                                    <span class="bg-blue-100 rounded-full px-2.5 text-xs text-blue-700 py-0.5 whitespace-nowrap font-medium">Menunggu Kabag</span>
                                @elseif($t->status === 'approved')
                                    <span class="bg-emerald-100 rounded-full px-2.5 text-xs text-emerald-700 py-0.5 whitespace-nowrap font-medium">Disetujui</span>
                                @elseif($t->status === 'rejected')
                                    <span class="bg-rose-100 rounded-full px-2.5 text-xs text-rose-700 py-0.5 whitespace-nowrap font-medium">Ditolak</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <td class="px-2 py-2 text-xs text-gray-900 text-left">
                                {{ $t->note ?? '-' }}
                            </td>

                            <td class="px-2 py-2 text-xs text-center">
                                @if($t->status === 'approved')
                                    @if($t->file_dokumentasi)
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-700 underline">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Lihat
                                            </a>
                                            @if($t->submitted_by_NIP === session('user')['nip'])
                                                <form action="{{ route('admin.lembur.destroyDoc', $t->id_transaksi) }}" method="POST"
                                                    onsubmit="return confirm('Hapus dokumentasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        @if($t->submitted_by_NIP === session('user')['nip'])
                                            <button type="button"
                                                onclick="openModalDok({{ $t->id_transaksi }})"
                                                class="text-[#faa938] hover:text-[#fd9a10] text-xs underline">
                                                + Tambah
                                            </button>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-10 text-center">
                                <div class="flex flex-col items-center justify-center gap-1.5 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-500">Tidak ada pengajuan lembur yang sesuai.</span>
                                    @if(request()->hasAny(['tanggal', 'bulan', 'tim', 'nip', 'status']))
                                        <a href="{{ route('admin.lembur') }}" class="text-xs text-[#faa938] hover:underline font-semibold mt-1">
                                            Reset semua filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                        <label for="uraian" class="block text-sm font-medium text-gray-700 mb-2">
                            Uraian Kegiatan
                        </label>

                        <textarea id="uraian" name="uraian" rows="3" required
                            placeholder="Contoh: Penyusunan laporan bulanan..."
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
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
    let selectedSort    = @json(request('sort', 'desc')) || 'desc';
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
        if (selectedSort && selectedSort !== 'desc') params.set('sort', selectedSort);
        window.location.href = '?' + params.toString();
    }

    window.selectStatus = function (status) {
        selectedStatus = status || null;
        applyFilter();
    };

    window.toggleSortTanggal = function () {
        selectedSort = (selectedSort === 'asc') ? 'desc' : 'asc';
        applyFilter();
    };

    // =====================
    // UPDATE EXPORT LINK
    // =====================
    function updateExportLink() {
        const tim    = selectedTimId   ?? '';
        const nip    = selectedNip     ?? '';
        const status = selectedStatus  ?? '';
        const sort   = selectedSort    ?? 'desc';
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
        list.innerHTML = '';

        const liSemua = document.createElement('li');
        liSemua.className   = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
        liSemua.textContent = 'Semua tim';
        liSemua.addEventListener('mousedown', (e) => { e.preventDefault(); pilihTim(null); });
        list.appendChild(liSemua);

        data
            .filter(t => t.nama_tim.toLowerCase().includes(filter.toLowerCase()))
            .forEach(tim => {
                const li = document.createElement('li');
                li.className   = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                li.textContent = tim.nama_tim;
                li.addEventListener('mousedown', (e) => { e.preventDefault(); pilihTim(tim); });
                list.appendChild(li);
            });
    }

    window.toggleDropdownTim = function () {
        const dd       = document.getElementById('dropdownTim');
        const isHidden = dd.classList.contains('hidden');
        tutupSemuaDropdown();
        if (isHidden) { populateDropdownTim('', cachedTim); dd.classList.remove('hidden'); }
    };

    window.filterDropdownTim = function () {
        populateDropdownTim(document.getElementById('searchTim').value, cachedTim);
        document.getElementById('dropdownTim').classList.remove('hidden');
    };

    function pilihTim(tim) {
        selectedTimId = tim ? tim.kode_tim : null;
        document.getElementById('searchTim').value    = tim ? tim.nama_tim : '';
        document.getElementById('dropdownTim').classList.add('hidden');
        selectedNip = null;
        document.getElementById('searchPegawai').value = '';
        if (tim) fetchPegawai(tim.kode_tim);
        else     fetchPegawai();
        applyFilter();
    }

    // =====================
    // DROPDOWN PEGAWAI
    // =====================
    function populateDropdown(filter = '', data = []) {
        const list = document.getElementById('listPegawai');
        list.innerHTML = '';

        const liSemua = document.createElement('li');
        liSemua.className   = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
        liSemua.textContent = 'Semua pegawai';
        liSemua.addEventListener('mousedown', (e) => { e.preventDefault(); pilihPegawai(null); });
        list.appendChild(liSemua);

        data
            .filter(e => `${e.nama} ${e.nip}`.toLowerCase().includes(filter.toLowerCase()))
            .forEach(emp => {
                const li = document.createElement('li');
                li.className   = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                li.textContent = `${emp.nama} - ${emp.nip}`;
                li.addEventListener('mousedown', (e) => { e.preventDefault(); pilihPegawai(emp); });
                list.appendChild(li);
            });
    }

    window.toggleDropdown = function () {
        const dd       = document.getElementById('dropdownPegawai');
        const isHidden = dd.classList.contains('hidden');
        tutupSemuaDropdown();
        if (isHidden) { populateDropdown('', cachedPegawai); dd.classList.remove('hidden'); }
    };

    window.filterDropdown = function () {
        populateDropdown(document.getElementById('searchPegawai').value, cachedPegawai);
        document.getElementById('dropdownPegawai').classList.remove('hidden');
    };

    function pilihPegawai(emp) {
        selectedNip = emp ? emp.nip : null;
        document.getElementById('searchPegawai').value = emp ? `${emp.nama} - ${emp.nip}` : '';
        document.getElementById('dropdownPegawai').classList.add('hidden');
        applyFilter();
    }

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
        document.getElementById('formDok').action = `${base}/${idTransaksi}/dokumentasi`;
        document.getElementById('modalDok').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalDok = function () {
        document.getElementById('modalDok').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // =====================
    // INIT
    // =====================
    document.addEventListener('DOMContentLoaded', function () {
        fetchTim();
        fetchPegawai();
        updateExportLink();

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
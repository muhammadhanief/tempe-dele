@extends('layouts.app')

@section('title', 'Daftar Pengajuan Lembur')

@section('content')

<div class="w-full max-w-7xl mx-auto flex flex-col px-4 sm:px-6 lg:px-8">

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="w-full my-4 sm:my-5">
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">

            {{-- Period Picker --}}
            <div class="relative w-full sm:w-auto shrink-0" id="periodPicker">
                <button type="button" id="periodBtn"
                    class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors">

                    <span class="inline-flex items-center gap-2 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0">
                            <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                        </svg>

                        <span id="periodLabel" class="leading-none truncate">
                            {{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('M Y') }}
                        </span>
                    </span>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <input type="hidden" id="periodValue" name="period" value="">

                <div id="periodPanel"
                    class="hidden absolute z-50 mt-2 left-0 w-full sm:w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white shadow-lg p-3">

                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="yearPrev"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>

                        <span id="yearLabel" class="text-sm font-medium text-gray-900">2026</span>

                        <button type="button" id="yearNext"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
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
            <div class="relative w-full lg:flex-1 lg:min-w-[260px]">
                <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                    onclick="toggleDropdownPegawai()" oninput="filterDropdownPegawai()" autocomplete="off"
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

            {{-- Reset Filter --}}
            <button type="button" id="btnResetFilter"
                class="hidden h-10 w-full sm:w-auto px-4 text-sm font-medium border border-gray-200 bg-white text-gray-500 rounded-xl sm:rounded-full hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                Reset
            </button>

            <div class="hidden sm:block flex-1"></div>

            {{-- Tombol Hari Libur --}}
            <button type="button" onclick="openModalHariLibur()"
                class="h-10 w-full sm:w-10 inline-flex items-center justify-center gap-2 bg-[#faa938] text-white rounded-xl sm:rounded-full hover:bg-[#fd9a10] transition-colors"
                title="Kelola Hari Libur">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>

                <span class="sm:hidden text-sm font-medium">Kelola Hari Libur</span>
            </button>
        </div>
    </div>

    {{-- ===================== TABEL ===================== --}}
    <div class="w-full overflow-x-auto rounded-xl ring-1 ring-gray-200 bg-white">
        <table class="min-w-[980px] lg:min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900 rounded-tl-xl w-8">No</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Nama Pegawai</th>
                    <th class="px-2 py-2 text-center text-xs font-semibold text-gray-900">Tanggal Lembur</th>
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
                            <div class="text-xs text-gray-400">{{ $p->nip_pegawai }}</div>
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
                            <div class="max-w-[260px]">
                                {{ $p->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-2 py-2 text-center">
                            <button type="button" onclick="openModalPresensi({{ $p->id_transaksi }})"
                                class="inline-flex items-center justify-center gap-1 text-xs font-medium cursor-pointer
                                {{ $p->has_presensi ? 'text-green-600 underline' : 'text-gray-400' }}">
                                {{ $p->has_presensi ? 'Informasi tersedia' : 'Tidak ada informasi' }}

                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </button>
                        </td>

                        <td class="px-2 py-2 text-center" id="status-{{ $p->id_transaksi }}">
                            @if($p->status === 'approved')
                                <span class="bg-green-100 rounded-full px-2 text-xs text-green-700 py-0.5">Disetujui</span>
                            @elseif($p->status === 'menunggu_kabag')
                                <span class="bg-blue-100 rounded-full px-2 text-xs text-blue-700 py-0.5">Menunggu Kabag</span>
                            @elseif($p->status === 'rejected')
                                <span class="bg-red-100 rounded-full px-2 text-xs text-red-700 py-0.5">Ditolak</span>
                            @else
                                <div class="inline-flex items-center gap-2">
                                    <span class="bg-amber-100 rounded-full px-2 text-xs text-amber-700 py-0.5">Menunggu Ketua</span>

                                    <button type="button"
                                        onclick="openModalKeputusan({{ $p->id_transaksi }}, '{{ substr($p->jam_mulai,0,5) }}', '{{ substr($p->jam_selesai,0,5) }}')"
                                        class="text-amber-400 cursor-pointer hover:text-amber-500">
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
                        <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-400">
                            Tidak ada pengajuan lembur yang menunggu keputusan.
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
<div id="modalKeputusan" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalKeputusan()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto sm:overflow-hidden">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="h-1 w-10 rounded-full bg-gray-200"></div>
            </div>

            <div class="sticky sm:static top-0 z-10 flex items-center justify-between border-b bg-white px-4 sm:px-6 py-4 rounded-t-2xl">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Keputusan Lembur</h2>

                <button type="button" onclick="closeModalKeputusan()"
                    class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            <div id="warningDurasi"
                class="hidden mx-4 sm:mx-6 mt-4 px-4 py-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
                ⚠️ <span id="warningText"></span>
            </div>

            <div class="px-4 sm:px-6 py-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai Disetujui</label>
                        <input id="kJamMulai" type="time"
                            class="border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-300 focus:ring-1 focus:ring-gray-400" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai Disetujui</label>
                        <input id="kJamSelesai" type="time"
                            class="border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-300 focus:ring-1 focus:ring-gray-400" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea id="kCatatan" rows="3"
                        class="border rounded-lg px-3 py-2 text-sm w-full outline-none border-gray-300 focus:ring-1 focus:ring-gray-400 resize-none"
                        placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keputusan</label>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="setKeputusan('rejected')" id="kBtnTolak"
                            class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all">
                            Tolak
                        </button>

                        <button type="button" onclick="setKeputusan('approved')" id="kBtnSetujui"
                            class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-all">
                            Setujui
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 px-4 sm:px-6 py-4 border-t">
                <button type="button" onclick="closeModalKeputusan()"
                    class="w-full sm:w-auto px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>

                <button type="button" onclick="simpanKeputusan()" id="btnSimpan"
                    class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white transition-all">
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
let cachedPegawai = [];

// =====================
// FETCH SEMUA PEGAWAI
// =====================
fetch('/admin/pengajuan/pegawai')
    .then(r => r.json())
    .then(data => {
        cachedPegawai = data;

        if (activeNip) {
            const emp = cachedPegawai.find(e => e.nip === activeNip);

            if (emp) {
                document.getElementById('searchPegawai').value = `${emp.nama} — ${emp.nip}`;
            }
        }

        updateResetBtn();
        renderDropdownPegawai('');
    });

function renderDropdownPegawai(filter) {
    const list = document.getElementById('listPegawai');
    list.innerHTML = '';

    const liSemua = document.createElement('li');
    liSemua.className = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
    liSemua.textContent = 'Semua pegawai';
    liSemua.onclick = () => pilihPegawai(null);
    list.appendChild(liSemua);

    cachedPegawai
        .filter(e => `${e.nama} ${e.nip}`.toLowerCase().includes(filter.toLowerCase()))
        .forEach(emp => {
            const li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
            li.textContent = `${emp.nama} — ${emp.nip}`;
            li.onclick = () => pilihPegawai(emp);
            list.appendChild(li);
        });
}

window.toggleDropdownPegawai = function () {
    document.getElementById('dropdownPegawai').classList.toggle('hidden');
    renderDropdownPegawai('');
};

window.filterDropdownPegawai = function () {
    const search = document.getElementById('searchPegawai').value;
    renderDropdownPegawai(search);
    document.getElementById('dropdownPegawai').classList.remove('hidden');
};

function pilihPegawai(emp) {
    const params = new URLSearchParams();
    params.set('bulan', document.getElementById('periodValue').value);

    if (emp) {
        params.set('nip', emp.nip);
    }

    window.location.href = `?${params.toString()}`;
}

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
        let selYear  = {{ \Carbon\Carbon::parse($bulan . '-01')->year }};
        let selMonth = {{ \Carbon\Carbon::parse($bulan . '-01')->month - 1 }};
        let viewYear = selYear;

        function updateDisplayOnly(y, m) {
            periodLabel.textContent = `${monthShort[m]} ${y}`;
            periodValue.value = `${y}-${pad2(m + 1)}`;
        }

        function setPeriod(y, m) {
            selYear = y;
            selMonth = m;
            updateDisplayOnly(y, m);

            const params = new URLSearchParams();
            params.set('bulan', `${y}-${pad2(m + 1)}`);

            if (activeNip) {
                params.set('nip', activeNip);
            }

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

    (activeNip)
        ? btn.classList.remove('hidden')
        : btn.classList.add('hidden');
}

updateResetBtn();

document.getElementById('btnResetFilter')?.addEventListener('click', () => {
    const params = new URLSearchParams();
    params.set('bulan', document.getElementById('periodValue').value);

    window.location.href = `?${params.toString()}`;
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

window.openModalKeputusan = function(id, jamMulai, jamSelesai) {
    currentId = id;
    keputusan = null;

    document.getElementById('kJamMulai').value = jamMulai || '';
    document.getElementById('kJamSelesai').value = jamSelesai || '';
    document.getElementById('kCatatan').value = '';

    resetBtnKeputusan();
    cekWarningDurasi();

    document.getElementById('modalKeputusan').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeModalKeputusan = function() {
    document.getElementById('modalKeputusan').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    currentId = null;
    keputusan = null;
};

window.setKeputusan = function(val) {
    keputusan = val;
    resetBtnKeputusan();

    if (val === 'rejected') {
        document.getElementById('kBtnTolak').className = 'px-4 py-2 text-sm font-semibold rounded-lg border border-red-400 bg-red-50 text-red-600 transition-all';
    } else {
        document.getElementById('kBtnSetujui').className = 'px-4 py-2 text-sm font-semibold rounded-lg border border-green-400 bg-green-50 text-green-700 transition-all';
    }
};

function resetBtnKeputusan() {
    document.getElementById('kBtnTolak').className = 'px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all';
    document.getElementById('kBtnSetujui').className = 'px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-all';
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

document.getElementById('kJamMulai')?.addEventListener('change', cekWarningDurasi);
document.getElementById('kJamSelesai')?.addEventListener('change', cekWarningDurasi);

window.simpanKeputusan = function() {
    if (!keputusan) {
        alert('Pilih keputusan terlebih dahulu.');
        return;
    }

    const jamMulai = document.getElementById('kJamMulai').value;
    const jamSelesai = document.getElementById('kJamSelesai').value;
    const catatan = document.getElementById('kCatatan').value;

    if (!jamMulai || !jamSelesai) {
        alert('Jam mulai dan jam selesai wajib diisi.');
        return;
    }

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch(`/admin/pengajuan/${currentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            status: keputusan,
            jam_mulai_disetujui: jamMulai,
            jam_selesai_disetujui: jamSelesai,
            note: catatan,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const statusEl = document.querySelector(`#status-${currentId}`);

            if (statusEl) {
                if (keputusan === 'approved') {
                    statusEl.innerHTML = '<span class="bg-green-100 rounded-full px-2 text-xs text-green-700 py-0.5">Disetujui</span>';
                } else {
                    statusEl.innerHTML = '<span class="bg-red-100 rounded-full px-2 text-xs text-red-700 py-0.5">Ditolak</span>';
                }
            }

            const jamEl = document.querySelector(`#jam-disetujui-${currentId}`);

            if (jamEl) {
                jamEl.textContent = `${jamMulai} - ${jamSelesai}`;
            }

            closeModalKeputusan();
        }
    })
    .catch(() => alert('Gagal menyimpan, coba lagi.'))
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

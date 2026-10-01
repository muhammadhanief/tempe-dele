@extends('layouts.app')
@section('hideLoading', true)

@section('title', 'Generate Dokumen')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-4 sm:my-5">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        {{-- Period Picker --}}
        <div class="relative w-full sm:w-auto shrink-0 flex items-center gap-1.5" id="periodPicker">
            <button type="button" id="periodBtn"
                class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors cursor-pointer">

                <span class="inline-flex items-center gap-2 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0 text-slate-500">
                        <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                    </svg>

                    <span id="periodLabel" class="leading-none truncate">
                        {{ $isFiltered ? \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('M Y') : 'Filter Bulan' }}
                    </span>
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            @if($isFiltered)
                <a href="{{ route('admin.dokumen') }}"
                   class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-colors shrink-0 cursor-pointer"
                   title="Reset filter (tampilkan semua bulan)">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif

            <input type="hidden" id="periodValue" name="period" value="">

            <div id="periodPanel"
                class="hidden absolute z-50 mt-2 left-0 w-full sm:w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white shadow-lg p-3">

                <div class="flex items-center justify-between mb-3">
                    <button type="button" id="yearPrev"
                        class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <span id="yearLabel" class="text-sm font-medium text-gray-900 cursor-pointer select-none">2026</span>

                    <button type="button" id="yearNext"
                        class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <button type="button" id="btnAllMonths"
                            class="text-xs sm:text-sm font-medium text-gray-500 hover:text-[#faa938] cursor-pointer transition-colors">
                            Semua Bulan
                        </button>
                        <span class="text-gray-300">·</span>
                        <button type="button" id="btnThisMonth"
                            class="text-xs sm:text-sm font-medium text-gray-500 hover:text-[#faa938] cursor-pointer transition-colors">
                            Bulan ini
                        </button>
                    </div>

                    <button type="button" id="btnClosePanel"
                        class="px-3 py-1 text-xs sm:text-sm font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938] cursor-pointer transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <div class="hidden sm:block flex-1"></div>

        {{-- Generate Button --}}
        <a href="javascript:void(0)" onclick="openModalGenerate()" title="Generate Dokumen"
            class="inline-flex h-10 w-full sm:w-10 items-center justify-center gap-2 rounded-xl sm:rounded-full border border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-600 transition-colors">

            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>

            <span class="sm:hidden text-sm font-medium">Generate Dokumen</span>
        </a>

    </div>
</div>

{{-- ===================== TABEL ===================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">
    <div class="overflow-x-auto rounded-xl ring-1 ring-gray-200 bg-white">
        <table class="min-w-[780px] sm:min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 sm:px-4 py-2.5 text-left text-xs font-semibold text-gray-900 rounded-tl-xl whitespace-nowrap">
                        Periode
                    </th>
                    <th class="px-3 sm:px-4 py-2.5 text-left text-xs font-semibold text-gray-900 whitespace-nowrap">
                        Status
                    </th>
                    <th class="px-3 sm:px-4 py-2.5 text-left text-xs font-semibold text-gray-900 whitespace-nowrap">
                        SPKL
                    </th>
                    <th class="px-3 sm:px-4 py-2.5 text-left text-xs font-semibold text-gray-900 rounded-tr-xl whitespace-nowrap">
                        Laporan
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($periodeList as $periode)
                    @foreach(['pns', 'pppk'] as $jenis)
                        @php
                            $spklPdf  = $dokumenList->where('type', 'spkl_'    . $jenis . '_pdf') ->where('periode', $periode)->first();
                            $spklXlsx = $dokumenList->where('type', 'spkl_'    . $jenis . '_xlsx')->where('periode', $periode)->first();
                            $lapPdf   = $dokumenList->where('type', 'laporan_' . $jenis . '_pdf') ->where('periode', $periode)->first();
                            $lapXlsx  = $dokumenList->where('type', 'laporan_' . $jenis . '_xlsx')->where('periode', $periode)->first();
                        @endphp

                        <tr class="bg-white hover:bg-gray-50 {{ $loop->last ? '' : 'border-b border-dashed border-gray-200' }}">

                            @if($loop->first)
                                <td class="px-3 sm:px-4 py-3 text-xs text-gray-900 font-medium align-middle border-r border-gray-100 whitespace-nowrap" rowspan="2">
                                    {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}
                                </td>
                            @endif

                            <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 font-medium border-r border-gray-100 whitespace-nowrap">
                                {{ strtoupper($jenis) }}
                            </td>

                            {{-- SPKL --}}
                            <td class="px-3 sm:px-4 py-3 text-xs border-r border-gray-100">
                                <div class="space-y-1.5">

                                    @if($spklPdf)
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fa-regular fa-file-pdf text-red-400 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.view', $spklPdf->id_dokumen) }}" target="_blank"
                                                class="text-gray-700 hover:underline truncate max-w-[240px] lg:max-w-[320px] inline-block">
                                                SPKL_{{ strtoupper($jenis) }}_{{ $periode }}.pdf
                                            </a>

                                            <form method="POST" action="{{ route('admin.dokumen.hapus-satu', $spklPdf->id_dokumen) }}"
                                                onsubmit="return confirm('Hapus file ini?')" class="ml-auto shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-300 hover:text-gray-500 text-base leading-none">
                                                    ×
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-file-pdf text-gray-200 shrink-0"></i>

                                            <button type="button" onclick="openModalNomor('{{ $periode }}', '{{ $jenis }}', 'pdf')"
                                                class="text-gray-300 hover:text-[#faa938] transition-colors whitespace-nowrap">
                                                Generate PDF
                                            </button>
                                        </div>
                                    @endif

                                    @if($spklXlsx)
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fa-regular fa-file-excel text-green-400 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.view', $spklXlsx->id_dokumen) }}" target="_blank"
                                                class="text-gray-700 hover:underline truncate max-w-[240px] lg:max-w-[320px] inline-block">
                                                SPKL_{{ strtoupper($jenis) }}_{{ $periode }}.xlsx
                                            </a>

                                            <form method="POST" action="{{ route('admin.dokumen.hapus-satu', $spklXlsx->id_dokumen) }}"
                                                onsubmit="return confirm('Hapus file ini?')" class="ml-auto shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-300 hover:text-gray-500 text-base leading-none">
                                                    ×
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-file-excel text-gray-200 shrink-0"></i>

                                            <button type="button" onclick="openModalNomor('{{ $periode }}', '{{ $jenis }}', 'xlsx')"
                                                class="text-gray-300 hover:text-[#faa938] transition-colors whitespace-nowrap">
                                                Generate XLSX
                                            </button>
                                        </div>
                                    @endif

                                </div>
                            </td>

                            {{-- LAPORAN --}}
                            <td class="px-3 sm:px-4 py-3 text-xs">
                                <div class="space-y-1.5">

                                    @if($lapPdf)
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fa-regular fa-file-pdf text-red-400 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.view', $lapPdf->id_dokumen) }}" target="_blank"
                                                class="text-gray-700 hover:underline truncate max-w-[240px] lg:max-w-[320px] inline-block">
                                                Laporan_{{ strtoupper($jenis) }}_{{ $periode }}.pdf
                                            </a>

                                            <form method="POST" action="{{ route('admin.dokumen.hapus-satu', $lapPdf->id_dokumen) }}"
                                                onsubmit="return confirm('Hapus file ini?')" class="ml-auto shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-300 hover:text-gray-500 text-base leading-none">
                                                    ×
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-file-pdf text-gray-200 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.generate.laporan', ['jenis' => $jenis, 'bulan' => $periode, 'format' => 'pdf']) }}"
                                                class="text-gray-300 hover:text-[#faa938] transition-colors whitespace-nowrap">
                                                Generate PDF
                                            </a>
                                        </div>
                                    @endif

                                    @if($lapXlsx)
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i class="fa-regular fa-file-excel text-green-400 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.view', $lapXlsx->id_dokumen) }}" target="_blank"
                                                class="text-gray-700 hover:underline truncate max-w-[240px] lg:max-w-[320px] inline-block">
                                                Laporan_{{ strtoupper($jenis) }}_{{ $periode }}.xlsx
                                            </a>

                                            <form method="POST" action="{{ route('admin.dokumen.hapus-satu', $lapXlsx->id_dokumen) }}"
                                                onsubmit="return confirm('Hapus file ini?')" class="ml-auto shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-300 hover:text-gray-500 text-base leading-none">
                                                    ×
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-file-excel text-gray-200 shrink-0"></i>

                                            <a href="{{ route('admin.dokumen.generate.laporan', ['jenis' => $jenis, 'bulan' => $periode, 'format' => 'xlsx']) }}"
                                                class="text-gray-300 hover:text-[#faa938] transition-colors whitespace-nowrap">
                                                Generate XLSX
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-400">
                            Tidak ada dokumen atau transaksi lembur untuk periode yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===================== PAGINATION ===================== --}}
@if($periodeList->hasPages())
    <div class="flex justify-center mt-6 px-4">
        <nav class="inline-flex items-center p-1 rounded bg-white space-x-2">

            @if($periodeList->onFirstPage())
                <span class="p-1 rounded border text-gray-300 cursor-not-allowed">
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </span>
            @else
                <a href="{{ $periodeList->previousPageUrl() }}"
                    class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]">
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </a>
            @endif

            <p class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                Page {{ $periodeList->currentPage() }} of {{ $periodeList->lastPage() }}
            </p>

            @if($periodeList->hasMorePages())
                <a href="{{ $periodeList->nextPageUrl() }}"
                    class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]">
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

{{-- ===================== MODAL NOMOR SURAT ===================== --}}
<div id="modalNomor" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalNomor()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>

            <div class="flex items-center justify-between border-b px-4 sm:px-6 py-4 sticky top-0 bg-white z-10">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Generate SPKL</h2>

                <button type="button" onclick="closeModalNomor()"
                    class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            <form id="formGenerateSpkl" method="GET" action="{{ route('admin.dokumen.generate.spkl') }}"
                class="px-4 sm:px-6 py-5 space-y-4">

                <input type="hidden" name="bulan" id="inputBulanSpkl">
                <input type="hidden" name="jenis" id="inputJenisSpkl">
                <input type="hidden" name="format" id="inputFormatSpkl">

                <div class="text-sm text-gray-500 bg-gray-50 rounded-lg px-4 py-2 leading-relaxed">
                    Periode:
                    <span id="labelPeriodeSpkl" class="font-medium text-gray-800">—</span>
                    <span class="mx-1">·</span>
                    <span id="labelJenisSpkl" class="font-medium text-gray-800">—</span>
                    <span class="mx-1">·</span>
                    <span id="labelFormatSpkl" class="font-medium text-gray-800">—</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label>

                    <input type="text" name="nomor_surat" required
                        placeholder="558.1/00001/RT.512/2026"
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">

                    <p class="text-xs text-gray-400 mt-1">
                        Contoh: 558.1/00001/RT.512/2026
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" onclick="closeModalNomor()"
                        class="w-full sm:w-auto px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white">
                        Generate
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL GENERATE ===================== --}}
<div id="modalGenerate" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalGenerate()"></div>

    <div class="relative flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] overflow-y-auto">

            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>

            <div class="flex items-center justify-between border-b px-4 sm:px-6 py-4 sticky top-0 bg-white z-10">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Generate Dokumen</h2>

                <button type="button" onclick="closeModalGenerate()"
                    class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            <div class="px-4 sm:px-6 py-5 space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="selectBulan"
                            class="w-full sm:flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">
                            <option value="">Bulan</option>
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $n)
                                <option value="{{ $i + 1 }}">{{ $n }}</option>
                            @endforeach
                        </select>

                        <input type="number" id="inputTahun" placeholder="2026" min="2000" max="2099"
                            class="w-full sm:w-28 rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Dokumen</label>

                    <select id="selectDok"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-900">
                        <option value="">Pilih jenis dokumen...</option>
                        <option value="spkl">SPKL</option>
                        <option value="laporan">Laporan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pegawai</label>

                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modalJenis" value="pns" checked class="accent-[#faa938]">
                            <span class="text-sm text-gray-700">PNS</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modalJenis" value="pppk" class="accent-[#faa938]">
                            <span class="text-sm text-gray-700">PPPK</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>

                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modalFormat" value="pdf" checked class="accent-[#faa938]">
                            <span class="text-sm text-gray-700">PDF</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modalFormat" value="xlsx" class="accent-[#faa938]">
                            <span class="text-sm text-gray-700">XLSX</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2">
                    <button type="button" onclick="closeModalGenerate()"
                        class="w-full sm:w-auto px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>

                    <button type="button" onclick="nextStep()"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-black bg-[#faa938] rounded-lg hover:bg-[#fd9a10] hover:text-white">
                        Lanjut
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const now = new Date();

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

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
    // PERIOD PICKER
    // =====================
    (function () {
        const el = (id) => document.getElementById(id);

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
        const btnAllMonths = el('btnAllMonths');
        const btnClose = el('btnClosePanel');

        if (!picker || !btn || !panel) return;

        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        const isFiltered = {{ $isFiltered ? 'true' : 'false' }};
        let view = 'month';
        let selYear  = {{ $isFiltered ? (int)\Carbon\Carbon::parse($bulan . '-01')->year : 'now.getFullYear()' }};
        let selMonth = {{ $isFiltered ? ((int)\Carbon\Carbon::parse($bulan . '-01')->month - 1) : '-1' }};
        let viewYear = selYear;

        function updateDisplayOnly(y, m) {
            if (!isFiltered || m === -1) {
                periodLabel.textContent = 'Filter Bulan';
                periodValue.value = '';
            } else {
                periodLabel.textContent = `${monthShort[m]} ${y}`;
                periodValue.value = `${y}-${pad2(m + 1)}`;
            }
        }

        function setPeriod(y, m) {
            selYear = y;
            selMonth = m;
            window.location.href = `{{ route('admin.dokumen') }}?bulan=${y}-${pad2(m + 1)}`;
        }

        function openPanel() {
            viewYear = isFiltered ? selYear : now.getFullYear();
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
                const isSel = isFiltered && (m === selMonth && viewYear === selYear);
                const isNow = (m === now.getMonth() && viewYear === now.getFullYear());

                const cls = 'px-2 py-2 text-sm rounded-lg border transition cursor-pointer ' + (
                    isSel
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white hover:bg-amber-50'
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
                const isSel = isFiltered && (y === selYear);
                const isNow = (y === now.getFullYear());
                const _y = y;

                const cls = 'px-2 py-2 text-sm rounded-lg border transition cursor-pointer ' + (
                    isSel
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white hover:bg-amber-50'
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

        btnAllMonths?.addEventListener('click', (e) => {
            e.stopPropagation();
            window.location.href = `{{ route('admin.dokumen') }}`;
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
    // MODAL GENERATE
    // =====================
    const bulanNama = [
        '',
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    window.openModalGenerate = function () {
        document.getElementById('modalGenerate').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalGenerate = function () {
        document.getElementById('modalGenerate').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    window.nextStep = function () {
        const bulan = document.getElementById('selectBulan').value;
        const tahun = document.getElementById('inputTahun').value;
        const dok = document.getElementById('selectDok').value;
        const jenis = document.querySelector('input[name="modalJenis"]:checked').value;
        const format = document.querySelector('input[name="modalFormat"]:checked').value;

        if (!bulan || !tahun) {
            alert('Pilih bulan dan isi tahun dulu!');
            return;
        }

        if (!dok) {
            alert('Pilih jenis dokumen!');
            return;
        }

        const periodeValue = tahun + '-' + String(bulan).padStart(2, '0');

        closeModalGenerate();

        if (dok === 'spkl') {
            openModalNomor(periodeValue, jenis, format);
        } else {
            window.location.href = `{{ url('admin/dokumen/generate/laporan') }}/${jenis}?bulan=${periodeValue}&format=${format}`;
        }
    };

    // =====================
    // MODAL NOMOR SURAT
    // =====================
    window.openModalNomor = function (periode, jenis, format) {
        const parts = periode.split('-');

        document.getElementById('inputBulanSpkl').value = periode;
        document.getElementById('inputJenisSpkl').value = jenis;
        document.getElementById('inputFormatSpkl').value = format;

        document.getElementById('labelPeriodeSpkl').textContent = bulanNama[+parts[1]] + ' ' + parts[0];
        document.getElementById('labelJenisSpkl').textContent = jenis.toUpperCase();
        document.getElementById('labelFormatSpkl').textContent = format.toUpperCase();

        document.getElementById('modalNomor').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalNomor = function () {
        document.getElementById('modalNomor').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

})();
</script>

@endsection

@extends('layouts.app')
@section('hideLoading', true)

@section('title', 'Rekapitulasi Lembur')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-4 sm:my-5">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        {{-- Filter Periode --}}
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
        <div class="relative w-full sm:flex-1 sm:min-w-[260px]" id="wrapSearchPegawai">
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                onclick="openDropdown()" onfocus="openDropdown()" oninput="filterDropdown()" autocomplete="off"
                class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-4 pr-12 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all"/>

            <div class="absolute inset-y-0 right-2.5 flex items-center gap-1">
                <button type="button" id="btnClearPegawai" onclick="pilihPegawai(null)" class="hidden p-1 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-100 transition-colors" title="Hapus filter pegawai">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <button type="button" onclick="toggleDropdown()" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none p-0.5" title="Buka daftar pegawai">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3">
                        <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                    </svg>
                </button>
            </div>

            <div id="dropdownPegawai"
                class="hidden absolute z-30 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
                <ul id="listPegawai"></ul>
            </div>
        </div>

        {{-- Filter Kategori (Semua / PNS / PPPK) --}}
        <div class="relative w-full sm:w-auto shrink-0">
            <select id="filterJenis" onchange="updateURL()"
                class="w-full sm:w-auto h-10 px-3.5 pr-8 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:border-[#faa938] focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
                <option value="" {{ empty($jenis) ? 'selected' : '' }}>Semua Pegawai</option>
                <option value="pns" {{ ($jenis ?? '') === 'pns' ? 'selected' : '' }}>PNS</option>
                <option value="pppk" {{ ($jenis ?? '') === 'pppk' ? 'selected' : '' }}>PPPK</option>
            </select>
        </div>

        {{-- Dropdown Unduh Rekapitulasi (Semua / PNS / PPPK) --}}
        <div class="relative w-full sm:w-auto shrink-0" id="wrapDownloadDropdown">
            <button type="button" id="btnDownloadDropdown" onclick="toggleDownloadMenu(event)"
                class="inline-flex h-10 w-full sm:w-auto items-center justify-between sm:justify-center gap-2 px-4 rounded-xl bg-[#faa938] text-white hover:brightness-95 transition-all shadow-sm text-sm font-medium">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    <span>Unduh Rekap</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-80 shrink-0">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <div id="downloadMenu"
                class="hidden absolute right-0 z-50 mt-1.5 w-full sm:w-56 rounded-xl border border-gray-200 bg-white shadow-xl py-1.5 text-sm text-gray-700">
                <div class="px-3 py-1.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 mb-1">
                    Pilihan Format Unduh
                </div>
                <a href="{{ route('admin.rekapitulasi.export', ['bulan' => $bulan, 'nip_lama' => request('nip_lama')]) }}"
                    class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-amber-50 hover:text-amber-800 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                    <span class="font-medium">Unduh Semua Pegawai</span>
                </a>
                <a href="{{ route('admin.rekapitulasi.export', ['bulan' => $bulan, 'nip_lama' => request('nip_lama'), 'jenis' => 'pns']) }}"
                    class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                    <span>Unduh Rekap PNS</span>
                </a>
                <a href="{{ route('admin.rekapitulasi.export', ['bulan' => $bulan, 'nip_lama' => request('nip_lama'), 'jenis' => 'pppk']) }}"
                    class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-purple-50 hover:text-purple-800 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-purple-500 shrink-0"></span>
                    <span>Unduh Rekap PPPK</span>
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ===================== TABEL ===================== --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">
    <div class="overflow-auto max-h-[65vh] sm:max-h-[500px] rounded-xl border border-gray-200 bg-white">
        <table class="min-w-[1200px] table-auto">
            <thead class="sticky top-0 z-20">
                <tr class="bg-gray-100">
                    <th class="sticky left-0 z-30 bg-gray-100 p-3 sm:p-4 text-left text-xs font-semibold text-gray-900 whitespace-nowrap">
                        NIP Lama
                    </th>

                    @for ($i = 1; $i <= 12; $i++)
                        <th class="p-3 sm:p-4 text-center text-xs font-semibold text-gray-900 whitespace-nowrap">
                            HB {{ $i }}
                        </th>
                    @endfor

                    @for ($i = 1; $i <= 16; $i++)
                        <th class="p-3 sm:p-4 text-center text-xs font-semibold text-gray-900 whitespace-nowrap">
                            HL {{ $i }}
                        </th>
                    @endfor
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($rekapitulasi as $r)
                    <tr class="bg-white hover:bg-gray-50 transition-all duration-300">
                        <td class="sticky left-0 z-10 bg-white p-3 sm:p-4 text-xs text-gray-900 whitespace-nowrap">
                            {{ $r['nip'] }}
                        </td>

                        @for ($i = 1; $i <= 12; $i++)
                            <td class="p-3 sm:p-4 text-xs text-gray-900 text-center whitespace-nowrap">
                                {{ $r['hb'.$i] ?? '-' }}
                            </td>
                        @endfor

                        @for ($i = 1; $i <= 16; $i++)
                            <td class="p-3 sm:p-4 text-xs text-gray-900 text-center whitespace-nowrap">
                                {{ $r['hl'.$i] ?? '-' }}
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="29" class="p-8 text-center text-sm text-gray-400">
                            Tidak ada data lembur untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===================== PAGINATION ===================== --}}
@if($rekapitulasi->hasPages())
    <div class="flex justify-center mt-6 px-4">
        <nav class="inline-flex items-center p-1 rounded bg-white space-x-2">
            @if($rekapitulasi->onFirstPage())
                <span class="p-1 rounded border text-gray-300 cursor-not-allowed">
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </span>
            @else
                <a href="{{ $rekapitulasi->previousPageUrl() }}"
                    class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]">
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </a>
            @endif

            <p class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                Page {{ $rekapitulasi->currentPage() }} of {{ $rekapitulasi->lastPage() }}
            </p>

            @if($rekapitulasi->hasMorePages())
                <a href="{{ $rekapitulasi->nextPageUrl() }}"
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

<script>
    // =====================
    // GLOBAL STATE
    // =====================
    const now = new Date();

    let selectedNip = null;
    let cachedPegawai = [];
    let selYear = {{ \Carbon\Carbon::parse($bulan . '-01')->year }};
    let selMonth = {{ \Carbon\Carbon::parse($bulan . '-01')->month - 1 }};

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
    // UPDATE URL
    // =====================
    function updateURL() {
        const params = new URLSearchParams();

        params.set('bulan', `${selYear}-${pad2(selMonth + 1)}`);

        if (selectedNip) {
            params.set('nip_lama', selectedNip);
        }

        const selJenis = document.getElementById('filterJenis')?.value;
        if (selJenis) {
            params.set('jenis', selJenis);
        }

        window.location.href = '?' + params.toString();
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
        const dateLabel = el('periodLabel');
        const btnPrev = el('yearPrev');
        const btnNext = el('yearNext');
        const btnThis = el('btnThisMonth');
        const btnClose = el('btnClosePanel');

        if (!picker || !btn || !panel) return;

        const monthNames = [
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

        const monthShort = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];

        let viewYear = selYear;
        let view = 'month';

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
            grid.innerHTML = '';

            monthNames.forEach((name, m) => {
                const isSelected = (m === selMonth && viewYear === selYear);
                const isNow = (m === now.getMonth() && viewYear === now.getFullYear());

                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSelected
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white'
                            : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
                );

                grid.appendChild(makeBtn(name.slice(0, 3), cls, () => {
                    selYear = viewYear;
                    selMonth = m;
                    dateLabel.textContent = `${monthShort[m]} ${viewYear}`;
                    closePanel();
                    updateURL();
                }));
            });
        }

        function renderYear() {
            view = 'year';

            const startYear = Math.floor(viewYear / 12) * 12;

            navLabel.textContent = `${startYear} - ${startYear + 11}`;
            navLabel.className = 'text-sm font-medium text-gray-400 select-none cursor-default';
            grid.innerHTML = '';

            const g = document.createElement('div');
            g.className = 'grid grid-cols-3 gap-2';

            for (let y = startYear; y < startYear + 12; y++) {
                const isSelected = (y === selYear);
                const isNow = (y === now.getFullYear());

                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSelected
                        ? 'bg-[#faa938] text-white border-[#faa938]'
                        : isNow
                            ? 'border-[#faa938] text-[#faa938] bg-white'
                            : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
                );

                const _y = y;

                g.appendChild(makeBtn(String(_y), cls, () => {
                    viewYear = _y;
                    renderMonth();
                }));
            }

            grid.appendChild(g);
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

        function openPanel() {
            viewYear = selYear;
            renderMonth();
            panel.classList.remove('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        dateLabel.textContent = `${monthShort[selMonth]} ${selYear}`;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.contains('hidden') ? openPanel() : closePanel();
        });

        navLabel.addEventListener('click', (e) => {
            e.stopPropagation();
            view === 'month' ? renderYear() : renderMonth();
        });

        btnPrev?.addEventListener('click', (e) => {
            e.stopPropagation();
            navigate(-1);
        });

        btnNext?.addEventListener('click', (e) => {
            e.stopPropagation();
            navigate(1);
        });

        btnThis?.addEventListener('click', (e) => {
            e.stopPropagation();
            selYear = now.getFullYear();
            selMonth = now.getMonth();
            closePanel();
            updateURL();
        });

        btnClose?.addEventListener('click', (e) => {
            e.stopPropagation();
            closePanel();
        });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) closePanel();
        });
    })();

    // =====================
    // DROPDOWN PEGAWAI
    // =====================
    function populateDropdown(filter = '') {
        const list = document.getElementById('listPegawai');
        list.innerHTML = '';

        const isSemua = !selectedNip;
        const liSemua = document.createElement('li');
        liSemua.className = 'cursor-pointer px-4 py-2.5 text-xs text-gray-500 hover:bg-gray-50 flex items-center justify-between ' + (isSemua ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
        liSemua.innerHTML = '<span>Semua pegawai</span>' + (isSemua ? '<svg class="w-3.5 h-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
        liSemua.addEventListener('click', () => pilihPegawai(null));
        list.appendChild(liSemua);

        const keyword = (filter || '').toLowerCase().trim();
        const filtered = cachedPegawai.filter(e =>
            `${e.nama} ${e.nip}`.toLowerCase().includes(keyword)
        );

        if (filtered.length === 0) {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 text-xs text-gray-400 text-center';
            li.textContent = 'Pegawai tidak ditemukan';
            list.appendChild(li);
            return;
        }

        filtered.forEach(emp => {
            const isSelected = selectedNip === emp.nip_lama;
            const li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center justify-between ' + (isSelected ? 'bg-amber-50/70 font-semibold text-amber-700' : '');
            li.innerHTML = `<span>${emp.nama} - ${emp.nip}</span>` + (isSelected ? '<svg class="w-3.5 h-3.5 text-[#faa938] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '');
            li.addEventListener('click', () => pilihPegawai(emp));
            list.appendChild(li);
        });
    }

    function pilihPegawai(emp) {
        selectedNip = emp ? emp.nip_lama : null;
        const search = document.getElementById('searchPegawai');
        const dropdown = document.getElementById('dropdownPegawai');
        const btnClear = document.getElementById('btnClearPegawai');

        if (search) search.value = emp ? `${emp.nama} - ${emp.nip}` : '';
        if (dropdown) dropdown.classList.add('hidden');
        if (btnClear) {
            selectedNip ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden');
        }
        updateURL();
    }

    window.openDropdown = function () {
        const dropdown = document.getElementById('dropdownPegawai');
        const search = document.getElementById('searchPegawai');
        if (!dropdown || !search) return;

        populateDropdown('');
        dropdown.classList.remove('hidden');
        setTimeout(() => search.select(), 10);
    };

    window.toggleDropdown = function () {
        const dropdown = document.getElementById('dropdownPegawai');
        if (!dropdown) return;

        if (dropdown.classList.contains('hidden')) {
            openDropdown();
        } else {
            dropdown.classList.add('hidden');
        }
    };

    window.filterDropdown = function () {
        const search = document.getElementById('searchPegawai');
        populateDropdown(search.value);
        document.getElementById('dropdownPegawai')?.classList.remove('hidden');

        const btnClear = document.getElementById('btnClearPegawai');
        if (btnClear) {
            search.value.trim() ? btnClear.classList.remove('hidden') : (selectedNip ? btnClear.classList.remove('hidden') : btnClear.classList.add('hidden'));
        }
    };

    // =====================
    // INIT
    // =====================
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const nipParam = params.get('nip_lama') ?? '';

        fetch('/admin/presensi/pegawai')
            .then(r => r.json())
            .then(data => {
                cachedPegawai = data;
                populateDropdown('');

                if (nipParam) {
                    selectedNip = nipParam;
                    const found = data.find(e => e.nip_lama == nipParam);

                    if (found) {
                        document.getElementById('searchPegawai').value = `${found.nama} - ${found.nip}`;
                        document.getElementById('btnClearPegawai')?.classList.remove('hidden');
                    }
                }
            });

        document.addEventListener('click', function (e) {
            const wrap = document.getElementById('wrapSearchPegawai');
            const dropdown = document.getElementById('dropdownPegawai');
            const search = document.getElementById('searchPegawai');

            if (wrap && dropdown && search && !wrap.contains(e.target)) {
                dropdown.classList.add('hidden');
                if (selectedNip) {
                    const found = cachedPegawai.find(e => e.nip_lama == selectedNip);
                    if (found) search.value = `${found.nama} - ${found.nip}`;
                } else {
                    search.value = '';
                }
            }

            const wrapDl = document.getElementById('wrapDownloadDropdown');
            const menuDl = document.getElementById('downloadMenu');
            if (wrapDl && menuDl && !wrapDl.contains(e.target)) {
                menuDl.classList.add('hidden');
            }
        });
    });

    window.toggleDownloadMenu = function (e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('downloadMenu');
        if (menu) menu.classList.toggle('hidden');
    };
</script>

@endsection

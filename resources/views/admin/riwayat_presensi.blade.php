@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')

<div class="flex items-center gap-3 max-w-6xl mx-auto px-8 my-5">

        {{-- Filter Periode --}}
        <div class="relative shrink-0" id="periodPicker">
            <button type="button" id="periodBtn"
                class="inline-flex items-center h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current">
                    <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                </svg>
                <span id="periodLabel" class="leading-none">Mar 2026</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>
            <input type="hidden" id="periodValue" name="period" value="">
            <div id="periodPanel" class="hidden absolute z-50 mt-2 w-72 rounded-xl border border-gray-200 bg-white shadow-lg p-3">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" id="yearPrev" class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>
                    <span id="yearLabel" class="text-sm font-medium text-gray-900">2026</span>
                    <button type="button" id="yearNext" class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>
                <div class="flex items-center justify-between mt-3">
                    <button type="button" id="btnThisMonth" class="text-sm font-medium text-gray-500 hover:text-[#faa938]">Bulan ini</button>
                    <button type="button" id="btnClosePanel" class="px-3 py-1 text-sm font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938]">Tutup</button>
                </div>
            </div>
        </div>
</div>

    {{-- Petunjuk Geser di Layar HP (Mobile) --}}
    <div class="sm:hidden flex items-center gap-1.5 px-4 mb-2 text-[11px] font-medium text-slate-500 max-w-6xl mx-auto">
        <svg class="h-3.5 w-3.5 text-amber-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <span>Geser tabel ke kiri / kanan untuk melihat kolom lengkap</span>
    </div>

<div class="overflow-x-auto max-w-6xl mx-auto px-4 sm:px-8 my-5">
    {{-- Tabel --}}
    <table class="w-full min-w-[700px] rounded-xl border-separate border-spacing-0">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-3 text-center text-sm font-semibold text-gray-900 rounded-tl-xl">
                    Periode
                </th>
                <th class="p-3 text-center text-sm font-semibold text-gray-900">
                    Nama File
                </th>
                <th class="p-3 text-center text-sm font-semibold text-gray-900">
                    User Upload
                </th>
                <th class="p-3 text-center text-sm font-semibold text-gray-900 rounded-tr-xl">
                    Tanggal Upload
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
            @forelse($daftarHadir as $d)
            <tr class="bg-white hover:bg-gray-50">

                <!-- Periode -->
                <td class="p-2 text-sm text-gray-900 text-center">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $d->periode)->translatedFormat('F Y') }}
                </td>

                <!-- Nama File -->
                <td class="p-2 text-sm text-gray-900">
                    {{ $d->nama_file ?? '-' }}
                </td>

                <!-- User Upload -->
                <td class="p-2 text-sm text-gray-900 text-center">
                    {{ $d->uploaded_by ?? '-' }}
                </td>

                <!-- Tanggal Upload -->
                <td class="p-2 text-sm text-gray-900 text-center">
                    {{ \Carbon\Carbon::parse($d->uploaded_at)->translatedFormat('d M Y H:i') }}
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="4" class="p-8 text-center text-sm text-gray-400">
                    Tidak ada data riwayat upload.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

<!-- Pagination -->
    <div class="flex justify-center mt-6">
        <nav class="inline-flex items-center p-1 rounded bg-white space-x-2">
            <a href="#" class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </a>
            <p class="text-gray-500">Page 1 of 10</p>
            <a href="#" class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </a>
        </nav>
    </div>
    <!-- /Pagination -->

<script>
    // =====================
    // GLOBAL STATE
    // =====================
    const now = new Date();
    let current = new Date(now.getFullYear(), now.getMonth(), 1);
    let selectedEmployeeId = null;
    let selectedTeamId = null;
    let cachedTim = [];
    let cachedPegawai = [];
    const gridEl = document.getElementById('presensiGrid');

    function pad2(n) { return String(n).padStart(2, '0'); }

    function makeBtn(text, cls, onClick) {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = text;
        b.className = cls;
        b.addEventListener('click', (e) => { e.stopPropagation(); onClick(); });
        return b;
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
        const url = kodeTim
            ? `/admin/presensi/pegawai?kode_tim=${kodeTim}`
            : '/admin/presensi/pegawai';
        const res = await fetch(url);
        cachedPegawai = await res.json();
        populateDropdown('', cachedPegawai);
    }

    // =====================
    // DROPDOWN TIM
    // =====================
    function populateDropdownTim(filter = '', data = []) {
        const list = document.getElementById('listTim');
        list.innerHTML = '';
        data
            .filter(t => t.nama_tim.toLowerCase().includes(filter.toLowerCase()))
            .forEach(tim => {
                const li = document.createElement('li');
                li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                li.textContent = tim.nama_tim;
                li.onclick = () => pilihTim(tim);
                list.appendChild(li);
            });
    }

    window.toggleDropdownTim = function () {
        document.getElementById('dropdownTim').classList.toggle('hidden');
        populateDropdownTim('', cachedTim);
    };

    window.filterDropdownTim = function () {
        const search = document.getElementById('searchTim').value;
        populateDropdownTim(search, cachedTim);
        document.getElementById('dropdownTim').classList.remove('hidden');
    };

    window.pilihTim = function (tim) {
        selectedTeamId = tim.kode_tim;
        document.getElementById('searchTim').value = tim.nama_tim;
        document.getElementById('dropdownTim').classList.add('hidden');
        selectedEmployeeId = null;
        document.getElementById('searchPegawai').value = '';
        fetchPegawai(tim.kode_tim);
    };

    // =====================
    // DROPDOWN PEGAWAI
    // =====================
    function populateDropdown(filter = '', data = []) {
        const list = document.getElementById('listPegawai');
        list.innerHTML = '';
        data
            .filter(e => `${e.nama} ${e.nip}`.toLowerCase().includes(filter.toLowerCase()))
            .forEach(emp => {
                const li = document.createElement('li');
                li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                li.textContent = `${emp.nama} - ${emp.nip}`;
                li.onclick = () => pilihPegawai(emp);
                list.appendChild(li);
            });
    }

    window.toggleDropdown = function () {
        document.getElementById('dropdownPegawai').classList.toggle('hidden');
        populateDropdown('', cachedPegawai);
    };

    window.filterDropdown = function () {
        const search = document.getElementById('searchPegawai').value;
        populateDropdown(search, cachedPegawai);
        document.getElementById('dropdownPegawai').classList.remove('hidden');
    };

    window.pilihPegawai = function (emp) {
        selectedEmployeeId = emp.id_pegawai;
        document.getElementById('searchPegawai').value = `${emp.nama} - ${emp.nip}`;
        document.getElementById('dropdownPegawai').classList.add('hidden');
    };

    // =====================
    // PERIOD PICKER
    // =====================
    (function () {
        const el = (id) => document.getElementById(id);

        const picker       = el('periodPicker');
        const btn          = el('periodBtn');
        const panel        = el('periodPanel');
        const grid         = el('monthGrid');
        const navLabel     = el('yearLabel');
        const periodLabel  = el('periodLabel');
        const periodValue  = el('periodValue');
        const btnPrev      = el('yearPrev');
        const btnNext      = el('yearNext');
        const btnThisMonth = el('btnThisMonth');
        const btnClose     = el('btnClosePanel');

        if (!picker || !btn || !panel) return;

        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        let view     = 'month';
        let viewYear = now.getFullYear();
        let selYear  = now.getFullYear();
        let selMonth = now.getMonth();

        function setPeriod(y, m) {
            selYear = y; selMonth = m;
            periodLabel.textContent = `${monthShort[m]} ${y}`;
            periodValue.value = `${y}-${pad2(m + 1)}`;
            // TODO: trigger fetch/filter
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
            grid.innerHTML = '';

            monthNames.forEach((name, m) => {
                const isSelected = (m === selMonth && viewYear === selYear);
                const isNow      = (m === now.getMonth() && viewYear === now.getFullYear());
                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSelected ? 'bg-[#faa938] text-white border-[#faa938]'
                    : isNow    ? 'border-[#faa938] text-[#faa938] bg-white'
                    :            'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
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
                const isSelected = (y === selYear);
                const isNow      = (y === now.getFullYear());
                const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                    isSelected ? 'bg-[#faa938] text-white border-[#faa938]'
                    : isNow    ? 'border-[#faa938] text-[#faa938] bg-white'
                    :            'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
                );
                const _y = y;
                grid.appendChild(makeBtn(_y, cls, () => { viewYear = _y; renderMonth(); }));
            }
        }

        function navigate(dir) {
            if (view === 'month') { viewYear += dir; renderMonth(); }
            else if (view === 'year') { viewYear += dir * 12; renderYear(); }
        }

        function openPanel() {
            viewYear = selYear;
            renderMonth();
            panel.classList.remove('hidden');
        }

        function closePanel() { panel.classList.add('hidden'); }

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.contains('hidden') ? openPanel() : closePanel();
        });

        navLabel.addEventListener('click', (e) => {
            e.stopPropagation();
            if (view === 'month') renderYear();
        });

        btnPrev?.addEventListener('click', (e) => { e.stopPropagation(); navigate(-1); });
        btnNext?.addEventListener('click', (e) => { e.stopPropagation(); navigate(1); });

        btnThisMonth?.addEventListener('click', (e) => {
            e.stopPropagation();
            setPeriod(now.getFullYear(), now.getMonth());
            closePanel();
        });

        btnClose?.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) closePanel();
        });

        setPeriod(selYear, selMonth);
    })();

    // =====================
    // INIT
    // =====================
    document.addEventListener('DOMContentLoaded', function () {
        fetchTim();
        fetchPegawai();

        document.addEventListener('click', function (e) {
            const wrapperTim = document.getElementById('searchTim')?.closest('.relative');
            if (wrapperTim && !wrapperTim.contains(e.target))
                document.getElementById('dropdownTim').classList.add('hidden');

            const wrapperPegawai = document.getElementById('searchPegawai')?.closest('.relative');
            if (wrapperPegawai && !wrapperPegawai.contains(e.target))
                document.getElementById('dropdownPegawai').classList.add('hidden');
        });
    });
</script>

@endsection

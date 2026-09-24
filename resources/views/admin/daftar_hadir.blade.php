@extends('layouts.app')
@section('hideLoading', true)

@section('title', 'Daftar Hadir')

@section('content')

<div class="max-w-7xl mx-auto w-full px-2 sm:px-4 lg:px-6 space-y-4 my-2">

    {{-- ===================== HEADER PAGE ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-amber-50 text-[#faa938] border border-amber-200/60 shadow-2xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">Daftar Hadir Lembur</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi tanda tangan dan waktu kehadiran pegawai lembur harian.</p>
                </div>
            </div>
        </div>

        {{-- Tombol Download PDF --}}
        <div class="relative shrink-0" id="downloadPicker">
            <button type="button" id="downloadBtn"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-xs font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all"
                title="Unduh Daftar Hadir">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh PDF</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-2.5 h-2.5 fill-current opacity-70">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <div id="downloadPanel"
                class="hidden absolute right-0 mt-2 w-52 rounded-2xl border border-gray-200 bg-white shadow-xl overflow-hidden z-50 p-1.5">
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 mb-1">
                    Pilih Kategori Pegawai
                </div>
                <a href="{{ route('admin.daftar_hadir.download', ['tanggal' => $tanggal, 'tim' => request('tim'), 'nip' => request('nip'), 'jenis' => 'pns']) }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-700 hover:bg-amber-50 hover:text-[#faa938] transition-colors">
                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                    <span>Daftar Hadir (PNS)</span>
                </a>
                <a href="{{ route('admin.daftar_hadir.download', ['tanggal' => $tanggal, 'tim' => request('tim'), 'nip' => request('nip'), 'jenis' => 'pppk']) }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-700 hover:bg-amber-50 hover:text-[#faa938] transition-colors">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Daftar Hadir (PPPK)</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ===================== TOOLBAR FILTER & PENCARIAN ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 sm:p-3.5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Filter Tanggal --}}
            <div class="relative shrink-0" id="datePicker">
                <button type="button" id="dateBtn"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="dateLabel" class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-2.5 h-2.5 fill-current opacity-40 shrink-0">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <input type="hidden" id="dateValue" name="date" value="{{ $tanggal }}">

                <div id="datePanel"
                    class="hidden absolute z-50 mt-2 left-0 w-72 rounded-2xl border border-gray-200 bg-white shadow-xl p-3.5">
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="datePrev"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>

                        <span id="dateNavLabel"
                            class="text-sm font-bold text-gray-900 cursor-pointer hover:text-[#faa938] select-none">
                        </span>

                        <button type="button" id="dateNext"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                            </svg>
                        </button>
                    </div>

                    <div id="dateGrid"></div>

                    <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-gray-100">
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

            {{-- Filter Tim --}}
            <div class="relative shrink-0">
                <select id="timFilter" onchange="onTimFilterChange(this.value)"
                    class="h-10 appearance-none rounded-xl border border-gray-200 bg-white pl-3.5 pr-8 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
                    <option value="">Semua Tim ({{ count($daftarHadir) }})</option>
                    @foreach($tim as $t)
                        <option value="{{ $t->kode_tim }}" {{ request('tim') === $t->kode_tim ? 'selected' : '' }}>
                            {{ $t->nama_tim }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Live Search Box --}}
        <div class="relative w-full sm:w-64 lg:w-72 shrink-0">
            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai / NIP..."
                oninput="filterRows()" autocomplete="off"
                class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-9 pr-8 text-xs text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all placeholder:text-gray-400">
            <button type="button" id="clearSearchBtn" onclick="clearSearch()"
                class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                title="Hapus pencarian">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ===================== TABEL DAFTAR HADIR ===================== --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-xs">
        <table class="w-full table-auto border-collapse" id="tabelDaftarHadir">
            <thead>
                <tr class="bg-gray-50/90 text-gray-700 text-xs font-semibold border-b border-gray-200">
                    <th rowspan="2" class="w-28 px-3 py-3 text-center align-middle border-r border-gray-200 whitespace-nowrap">
                        Tanggal
                    </th>
                    <th rowspan="2" class="w-12 px-2 py-3 text-center align-middle border-r border-gray-200 whitespace-nowrap">
                        No
                    </th>
                    <th rowspan="2" class="px-4 py-3 text-left align-middle border-r border-gray-200 min-w-[220px]">
                        Nama / NIP
                    </th>
                    <th colspan="2" class="w-48 px-3 py-2 text-center border-b border-r border-gray-200 bg-gray-100/60 font-semibold tracking-wide">
                        Jam Lembur
                    </th>
                    <th rowspan="2" class="w-44 px-3 py-3 text-center align-middle whitespace-nowrap">
                        Tanda Tangan
                    </th>
                </tr>
                <tr class="bg-gray-50/60 text-[11px] font-semibold text-gray-600 border-b border-gray-200">
                    <th class="w-24 px-3 py-2 text-center border-r border-gray-200 whitespace-nowrap">
                        Datang
                    </th>
                    <th class="w-24 px-3 py-2 text-center border-r border-gray-200 whitespace-nowrap">
                        Pulang
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                @forelse($daftarHadir as $i => $d)
                    <tr class="bg-white transition-colors hover:bg-slate-50/80"
                        data-search="{{ strtolower($d->nama . ' ' . $d->nip . ' ' . ($d->nama_tim ?? '')) }}"
                        data-tim="{{ $d->kode_tim ?? '' }}">
                        <td class="px-3 py-3 text-center border-r border-gray-100 align-middle whitespace-nowrap">
                            @if($i == 0 || $d->date != $daftarHadir[$i-1]->date)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200/80 font-medium">
                                    {{ \Carbon\Carbon::parse($d->date)->translatedFormat('d M Y') }}
                                </span>
                            @else
                                <span class="text-gray-300 font-mono">- &quot; -</span>
                            @endif
                        </td>

                        <td class="px-2 py-3 text-center text-gray-500 font-medium border-r border-gray-100 align-middle">
                            {{ $i + 1 }}
                        </td>

                        <td class="px-4 py-3 border-r border-gray-100 align-middle">
                            <div class="font-semibold text-gray-900">{{ $d->nama }}</div>
                            <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $d->nip }}</div>
                            @if(!empty($d->nama_tim))
                                <span class="inline-block mt-1 text-[10px] font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                    {{ $d->nama_tim }}
                                </span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-center border-r border-gray-100 align-middle font-mono font-medium text-emerald-700 whitespace-nowrap">
                            {{ $d->jam_mulai_disetujui ? substr($d->jam_mulai_disetujui, 0, 5) : '-' }}
                        </td>

                        <td class="px-3 py-3 text-center border-r border-gray-100 align-middle font-mono font-medium text-blue-700 whitespace-nowrap">
                            {{ $d->jam_selesai_disetujui ? substr($d->jam_selesai_disetujui, 0, 5) : '-' }}
                        </td>

                        <td class="px-3 py-3 text-center align-middle">
                            @if($d->signature_path)
                                <div class="inline-block p-1 bg-gray-50 rounded-lg border border-gray-200 shadow-2xs">
                                    <img src="{{ Storage::url($d->signature_path) }}"
                                        alt="TTD {{ $d->nama }}"
                                        class="h-10 w-auto mx-auto object-contain">
                                </div>
                            @else
                                <span class="text-xs text-gray-300 italic">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRow">
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="mx-auto max-w-sm flex flex-col items-center justify-center">
                                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-[#faa938] mb-3.5 shadow-2xs">
                                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">Tidak Ada Daftar Hadir</h3>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                    Belum ada pengajuan lembur yang disetujui pada tanggal <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</span>.
                                </p>
                                <button type="button" onclick="setToday()"
                                    class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#faa938]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Lihat Hari Ini</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse

                <tr id="noMatchRow" class="hidden">
                    <td colspan="6" class="px-4 py-12 text-center text-xs text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="font-medium text-gray-700">Tidak ada pegawai yang sesuai dengan pencarian.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('scripts')
<script>
const now = new Date();

function pad2(n) {
    return String(n).padStart(2, '0');
}

// =====================
// APPLY FILTER
// =====================
function applyFilter() {
    const tanggal = document.getElementById('dateValue').value;
    const params = new URLSearchParams(window.location.search);

    params.set('tanggal', tanggal);

    window.location.href = '?' + params.toString();
}

window.onTimFilterChange = function(kodeTim) {
    const params = new URLSearchParams(window.location.search);
    if (kodeTim) {
        params.set('tim', kodeTim);
    } else {
        params.delete('tim');
    }
    window.location.href = '?' + params.toString();
};

window.setToday = function() {
    const params = new URLSearchParams(window.location.search);
    const today = `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`;
    params.set('tanggal', today);
    window.location.href = '?' + params.toString();
};

// =====================
// LIVE SEARCH FILTER
// =====================
window.filterRows = function() {
    const input = document.getElementById('searchPegawai');
    const clearBtn = document.getElementById('clearSearchBtn');
    const query = input ? input.value.toLowerCase().trim() : '';

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const rows = document.querySelectorAll('#tabelDaftarHadir tbody tr[data-search]');
    let matchCount = 0;
    rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        if (text.includes(query)) {
            r.classList.remove('hidden');
            matchCount++;
        } else {
            r.classList.add('hidden');
        }
    });

    const noMatchRow = document.getElementById('noMatchRow');
    if (noMatchRow) {
        if (matchCount === 0 && query.length > 0 && rows.length > 0) {
            noMatchRow.classList.remove('hidden');
        } else {
            noMatchRow.classList.add('hidden');
        }
    }
};

window.clearSearch = function() {
    const input = document.getElementById('searchPegawai');
    if (input) {
        input.value = '';
        filterRows();
        input.focus();
    }
};

// =====================
// DATE PICKER
// =====================
(function () {
    const el = (id) => document.getElementById(id);

    const picker = el('datePicker');
    const btn = el('dateBtn');
    const panel = el('datePanel');
    const grid = el('dateGrid');
    const navLabel = el('dateNavLabel');
    const dateLabel = el('dateLabel');
    const dateValue = el('dateValue');
    const btnPrev = el('datePrev');
    const btnNext = el('dateNext');
    const btnToday = el('btnToday');
    const btnClose = el('btnDateClose');

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

    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    let initVal = dateValue.value || `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`;
    let [iy, im, id] = initVal.split('-').map(Number);

    let view = 'day';
    let viewYear = iy;
    let viewMonth = im - 1;
    let selYear = iy;
    let selMonth = im - 1;
    let selDay = id;

    dateLabel.textContent = `${selDay} ${monthShort[selMonth]} ${selYear}`;

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

    function setDate(y, m, d, triggerFilter = true) {
        selYear = y;
        selMonth = m;
        selDay = d;

        dateLabel.textContent = `${d} ${monthShort[m]} ${y}`;
        dateValue.value = `${y}-${pad2(m + 1)}-${pad2(d)}`;

        if (triggerFilter) {
            applyFilter();
        }
    }

    function renderDay() {
        view = 'day';
        navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
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

        const base = 'text-sm rounded-lg py-1 transition border ';
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInPrev = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = firstDay - 1; i >= 0; i--) {
            const d = daysInPrev - i;

            dayGrid.appendChild(makeBtn(d, base + 'border-transparent text-gray-300', () => {
                let m = viewMonth - 1;
                let y = viewYear;

                if (m < 0) {
                    m = 11;
                    y--;
                }

                viewYear = y;
                viewMonth = m;

                setDate(y, m, d);
                closePanel();
            }));
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const isSelected = (d === selDay && viewMonth === selMonth && viewYear === selYear);
            const isToday = (d === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear());

            const cls = isSelected
                ? 'bg-[#faa938] text-white border-[#faa938]'
                : isToday
                    ? 'border-[#faa938] text-[#faa938] bg-white'
                    : 'border-transparent text-gray-700 hover:border-[#faa938] hover:text-[#faa938]';

            const _d = d;

            dayGrid.appendChild(makeBtn(_d, base + cls, () => {
                setDate(viewYear, viewMonth, _d);
                closePanel();
            }));
        }

        const total = firstDay + daysInMonth;
        const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);

        for (let d = 1; d <= remaining; d++) {
            const _d = d;

            dayGrid.appendChild(makeBtn(_d, base + 'border-transparent text-gray-300', () => {
                let m = viewMonth + 1;
                let y = viewYear;

                if (m > 11) {
                    m = 0;
                    y++;
                }

                viewYear = y;
                viewMonth = m;

                setDate(y, m, _d);
                closePanel();
            }));
        }

        grid.appendChild(dayGrid);
    }

    function renderMonth() {
        view = 'month';
        navLabel.textContent = String(viewYear);
        navLabel.className = 'text-sm font-medium text-gray-900 cursor-pointer hover:text-[#faa938] select-none';
        grid.innerHTML = '';

        const g = document.createElement('div');
        g.className = 'grid grid-cols-3 gap-2';

        monthNames.forEach((name, m) => {
            const isSelected = (m === selMonth && viewYear === selYear);
            const isNow = (m === now.getMonth() && viewYear === now.getFullYear());

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
        navLabel.className = 'text-sm font-medium text-gray-400 select-none cursor-default';
        grid.innerHTML = '';

        const g = document.createElement('div');
        g.className = 'grid grid-cols-3 gap-2';

        for (let y = startYear; y < startYear + 12; y++) {
            const isSelected = (y === selYear);
            const isNow = (y === now.getFullYear());

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

    function navigate(dir) {
        if (view === 'day') {
            viewMonth += dir;

            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }

            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }

            renderDay();
        } else if (view === 'month') {
            viewYear += dir;
            renderMonth();
        } else {
            viewYear += dir * 12;
            renderYear();
        }
    }

    function openPanel() {
        viewYear = selYear;
        viewMonth = selMonth;
        renderDay();
        panel.classList.remove('hidden');
    }

    function closePanel() {
        panel.classList.add('hidden');
    }

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.contains('hidden') ? openPanel() : closePanel();
    });

    navLabel.addEventListener('click', (e) => {
        e.stopPropagation();

        if (view === 'day') {
            renderMonth();
        } else if (view === 'month') {
            renderYear();
        }
    });

    btnPrev?.addEventListener('click', (e) => {
        e.stopPropagation();
        navigate(-1);
    });

    btnNext?.addEventListener('click', (e) => {
        e.stopPropagation();
        navigate(1);
    });

    btnToday?.addEventListener('click', (e) => {
        e.stopPropagation();

        viewYear = now.getFullYear();
        viewMonth = now.getMonth();

        setDate(now.getFullYear(), now.getMonth(), now.getDate());
        closePanel();
    });

    btnClose?.addEventListener('click', (e) => {
        e.stopPropagation();
        closePanel();
    });

    document.addEventListener('click', (e) => {
        if (!picker.contains(e.target)) {
            closePanel();
        }
    });
})();

// =====================
// DOWNLOAD DROPDOWN
// =====================
document.addEventListener('DOMContentLoaded', function () {
    const downloadBtn = document.getElementById('downloadBtn');
    const downloadPanel = document.getElementById('downloadPanel');
    const downloadPicker = document.getElementById('downloadPicker');

    if (!downloadBtn || !downloadPanel || !downloadPicker) return;

    downloadBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        downloadPanel.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!downloadPicker.contains(e.target)) {
            downloadPanel.classList.add('hidden');
        }
    });
});
</script>
@endpush

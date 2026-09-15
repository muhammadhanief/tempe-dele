@extends('layouts.app')
@section('hideLoading', true)

@section('title', 'Daftar Hadir')

@section('content')

<div class="flex flex-col min-h-screen">

    {{-- ===================== FILTER BAR ===================== --}}
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 my-4 sm:my-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">

            {{-- Filter Tanggal --}}
            <div class="relative w-full sm:w-auto shrink-0" id="datePicker">
                <button type="button" id="dateBtn"
                    class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors">

                    <span class="inline-flex items-center gap-2 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0">
                            <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                        </svg>

                        <span id="dateLabel" class="leading-none truncate">
                            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
                        </span>
                    </span>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                    </svg>
                </button>

                <input type="hidden" id="dateValue" name="date" value="{{ $tanggal }}">

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

            <div class="hidden sm:block flex-1"></div>

            {{-- Tombol Download --}}
            <div class="relative w-full sm:w-auto" id="downloadPicker">
                <button type="button" id="downloadBtn"
                    class="inline-flex h-10 w-full sm:w-10 items-center justify-center gap-2 rounded-xl sm:rounded-full bg-[#faa938] text-white hover:brightness-95 transition-all"
                    title="Unduh Daftar Hadir">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>

                    <span class="sm:hidden text-sm font-medium">
                        Unduh Daftar Hadir
                    </span>
                </button>

                <div id="downloadPanel"
                    class="hidden absolute right-0 mt-2 w-full sm:w-36 rounded-xl border border-gray-200 bg-white shadow-lg overflow-hidden z-50">

                    <a href="{{ route('admin.daftar_hadir.download', ['tanggal' => $tanggal, 'tim' => request('tim'), 'nip' => request('nip'), 'jenis' => 'pns']) }}"
                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                        PNS
                    </a>

                    <a href="{{ route('admin.daftar_hadir.download', ['tanggal' => $tanggal, 'tim' => request('tim'), 'nip' => request('nip'), 'jenis' => 'pppk']) }}"
                        class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                        PPPK
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ===================== TABEL ===================== --}}
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="min-w-[760px] lg:min-w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-gray-100">
                        <th rowspan="2"
                            class="px-2 py-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 rounded-tl-xl whitespace-nowrap">
                            Tanggal
                        </th>
                        <th rowspan="2"
                            class="px-2 py-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 whitespace-nowrap">
                            No
                        </th>
                        <th rowspan="2"
                            class="px-2 py-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 whitespace-nowrap">
                            Nama / NIP
                        </th>
                        <th colspan="2"
                            class="px-2 py-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 whitespace-nowrap">
                            Jam
                        </th>
                        <th rowspan="2"
                            class="px-2 py-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 rounded-tr-xl whitespace-nowrap">
                            Tanda Tangan
                        </th>
                    </tr>

                    <tr class="bg-gray-100">
                        <th class="p-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 whitespace-nowrap">
                            Datang
                        </th>
                        <th class="p-2 text-center text-xs sm:text-sm font-semibold text-gray-900 border border-gray-100 whitespace-nowrap">
                            Pulang
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($daftarHadir as $i => $d)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="p-3 sm:p-4 text-xs sm:text-sm text-gray-900 text-center border border-gray-100 whitespace-nowrap">
                                @if($i == 0 || $d->date != $daftarHadir[$i-1]->date)
                                    {{ \Carbon\Carbon::parse($d->date)->translatedFormat('d/m/Y') }}
                                @endif
                            </td>

                            <td class="p-3 sm:p-4 text-xs sm:text-sm text-gray-900 text-center border border-gray-100 whitespace-nowrap">
                                {{ $i + 1 }}
                            </td>

                            <td class="p-3 sm:p-4 text-xs sm:text-sm text-gray-900 border border-gray-100">
                                <div class="font-medium whitespace-nowrap">
                                    {{ $d->nama }}
                                </div>
                                <div class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ $d->nip }}
                                </div>
                            </td>

                            <td class="p-3 sm:p-4 text-xs sm:text-sm text-gray-900 text-center border border-gray-100 whitespace-nowrap">
                                {{ $d->jam_mulai_disetujui ? substr($d->jam_mulai_disetujui, 0, 5) : '-' }}
                            </td>

                            <td class="p-3 sm:p-4 text-xs sm:text-sm text-gray-900 text-center border border-gray-100 whitespace-nowrap">
                                {{ $d->jam_selesai_disetujui ? substr($d->jam_selesai_disetujui, 0, 5) : '-' }}
                            </td>

                            <td class="p-3 sm:p-4 border border-gray-100 h-12 text-center">
                                @if($d->signature_path)
                                    <img src="{{ Storage::url($d->signature_path) }}"
                                        alt="TTD {{ $d->nama }}"
                                        class="h-10 w-auto mx-auto object-contain">
                                @else
                                    <span class="text-xs text-gray-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-sm text-gray-400">
                                Tidak ada data untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

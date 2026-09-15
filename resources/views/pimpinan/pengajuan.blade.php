@extends('layouts.app')

@section('title', 'Daftar Pengajuan Lembur')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">

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
        <div class="relative w-full sm:w-[22rem]">
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                onclick="toggleDropdownPegawai()" oninput="filterDropdownPegawai()" autocomplete="off"
                class="h-10 w-full rounded-full border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">

            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                    <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                </svg>
            </div>

            <div id="dropdownPegawai"
                class="absolute z-40 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listPegawai"></ul>
            </div>
        </div>

        {{-- Reset filter --}}
        <button type="button" id="btnResetFilter"
            class="hidden h-10 rounded-full border border-gray-200 bg-white px-4 text-sm font-medium text-gray-500 transition-colors hover:border-red-300 hover:text-red-400">
            Reset
        </button>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[1100px] table-auto rounded-xl">
            <thead>
                <tr class="bg-gray-100">
                    <th class="w-12 rounded-tl-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">No</th>
                    <th class="w-48 px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama Pegawai</th>
                    <th class="w-32 px-3 py-3 text-center text-xs font-semibold text-gray-900">Tanggal Lembur</th>
                    <th class="w-28 px-3 py-3 text-center text-xs font-semibold text-gray-900">Jam Diajukan</th>
                    <th class="w-36 px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama Tim</th>
                    <th class="w-40 px-3 py-3 text-center text-xs font-semibold text-gray-900">Ketua Tim</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Uraian Kegiatan</th>
                    <th class="w-28 px-3 py-3 text-center text-xs font-semibold text-gray-900">Data Presensi</th>
                    <th class="w-28 rounded-tr-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300" id="tabelPengajuan">
                @forelse($pengajuan as $i => $p)
                    <tr class="bg-white transition-all duration-200 hover:bg-gray-50"
                        data-nama="{{ strtolower($p->nama_pegawai) }}"
                        data-nip="{{ $p->nip_pegawai }}">

                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            {{ $pengajuan->firstItem() + $i }}
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[180px] whitespace-normal break-words">{{ $p->nama_pegawai }}</div>
                            <div class="text-xs text-gray-400">{{ $p->nip_pegawai }}</div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-center text-xs text-gray-900">
                            {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d F Y') }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-center text-xs text-gray-900">
                            {{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : '-' }}
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[140px] whitespace-normal break-words">
                                {{ $p->nama_tim ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[160px] whitespace-normal break-words">
                                {{ $p->nama_ketua ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[280px] whitespace-normal break-words">
                                {{ $p->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="openModalPresensi({{ $p->id_transaksi }})"
                                class="inline-flex items-center justify-center gap-1 text-xs font-medium
                                {{ $p->has_presensi ? 'text-green-600 underline' : 'text-gray-400' }}">
                                {{ $p->has_presensi ? 'Informasi tersedia' : 'Tidak ada informasi' }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/>
                                </svg>
                            </button>
                        </td>

                        <td class="px-3 py-3 text-center">
                            @if($p->status === 'pending')
                                <span class="whitespace-nowrap rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Menunggu</span>
                            @elseif($p->status === 'approved')
                                <span class="whitespace-nowrap rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Disetujui</span>
                            @elseif($p->status === 'rejected')
                                <span class="whitespace-nowrap rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-600">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-8 text-center text-sm text-gray-400">
                            Belum ada pengajuan lembur.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pengajuan->hasPages())
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex items-center gap-2 rounded bg-white p-1">

                @if($pengajuan->onFirstPage())
                    <span class="cursor-not-allowed rounded border p-1 text-gray-300">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $pengajuan->previousPageUrl() }}">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="whitespace-nowrap text-sm text-gray-500">
                    Page {{ $pengajuan->currentPage() }} of {{ $pengajuan->lastPage() }}
                </p>

                @if($pengajuan->hasMorePages())
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $pengajuan->nextPageUrl() }}">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="cursor-not-allowed rounded border p-1 text-gray-300">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                @endif
            </nav>
        </div>
    @endif
</div>

{{-- Modal Presensi --}}
<div id="modalPresensi" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalPresensi()"></div>
    <div class="relative flex min-h-full items-center justify-center px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Informasi Presensi</h2>
                    <p class="mt-0.5 text-xs text-gray-400" id="presensiSubtitle">-</p>
                </div>
                <button type="button" onclick="closeModalPresensi()"
                    class="text-xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="space-y-4 px-5 py-5 sm:px-6" id="presensiBody">
                <p class="py-4 text-center text-sm text-gray-400">Memuat data...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function makeBtn(text, className, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.className = className;
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        onClick();
    });
    return button;
}

let selectedNip = null;
let cachedPegawai = [];

// Fetch semua pegawai
fetch('{{ route('pimpinan.pengajuan.pegawai') }}')
    .then(r => r.json())
    .then(data => {
        cachedPegawai = Array.isArray(data) ? data : [];
        renderDropdownPegawai('');
    })
    .catch(() => { cachedPegawai = []; });

function renderDropdownPegawai(filter = '') {
    const list = document.getElementById('listPegawai');
    if (!list) return;
    list.innerHTML = '';

    const liSemua = document.createElement('li');
    liSemua.className = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
    liSemua.textContent = 'Semua pegawai';
    liSemua.onclick = () => pilihPegawai(null);
    list.appendChild(liSemua);

    const keyword = filter.toLowerCase();
    cachedPegawai
        .filter(emp => `${emp.nama ?? ''} ${emp.nip ?? ''}`.toLowerCase().includes(keyword))
        .forEach(emp => {
            const li = document.createElement('li');
            li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
            li.textContent = `${emp.nama} — ${emp.nip}`;
            li.onclick = () => pilihPegawai(emp);
            list.appendChild(li);
        });
}

window.toggleDropdownPegawai = function() {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');
    if (!dropdown || !search) return;
    dropdown.classList.toggle('hidden');
    renderDropdownPegawai(search.value);
};

window.filterDropdownPegawai = function() {
    const dropdown = document.getElementById('dropdownPegawai');
    const search = document.getElementById('searchPegawai');
    if (!dropdown || !search) return;
    renderDropdownPegawai(search.value);
    dropdown.classList.remove('hidden');
};

function pilihPegawai(emp) {
    selectedNip = emp ? emp.nip : null;
    const search = document.getElementById('searchPegawai');
    const dropdown = document.getElementById('dropdownPegawai');
    if (search) search.value = emp ? `${emp.nama} — ${emp.nip}` : '';
    if (dropdown) dropdown.classList.add('hidden');
    filterTabel();
    updateResetBtn();
}

function filterTabel() {
    document.querySelectorAll('#tabelPengajuan tr[data-nip]').forEach(row => {
        const cocokNip = !selectedNip || row.dataset.nip === selectedNip;
        row.style.display = cocokNip ? '' : 'none';
    });
}

function updateResetBtn() {
    const btn = document.getElementById('btnResetFilter');
    if (!btn) return;
    selectedNip ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}

document.getElementById('btnResetFilter')?.addEventListener('click', () => {
    selectedNip = null;
    document.getElementById('searchPegawai').value = '';
    filterTabel();
    updateResetBtn();
});

// Period Picker
(function() {
    const el = (id) => document.getElementById(id);
    const now = new Date();
    function pad2(n) { return String(n).padStart(2, '0'); }

    const picker      = el('periodPicker');
    const btn         = el('periodBtn');
    const panel       = el('periodPanel');
    const grid        = el('monthGrid');
    const navLabel    = el('yearLabel');
    const periodLabel = el('periodLabel');
    const periodValue = el('periodValue');
    const btnPrev     = el('yearPrev');
    const btnNext     = el('yearNext');
    const btnThisMonth = el('btnThisMonth');
    const btnClose    = el('btnClosePanel');

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
        selYear = y; selMonth = m;
        updateDisplayOnly(y, m);
        window.location.href = `?bulan=${y}-${pad2(m + 1)}`;
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
                isSel ? 'bg-[#faa938] text-white border-[#faa938]'
                : isNow ? 'border-[#faa938] text-[#faa938] bg-white'
                : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
            );
            grid.appendChild(makeBtn(name.slice(0, 3), cls, () => { setPeriod(viewYear, m); closePanel(); }));
        });
    }

    function renderYear() {
        view = 'year';
        const startYear = Math.floor(viewYear / 12) * 12;
        navLabel.textContent = `${startYear} - ${startYear + 11}`;
        navLabel.className = 'text-sm font-medium text-gray-400 select-none cursor-default';
        grid.innerHTML = '';
        for (let y = startYear; y < startYear + 12; y++) {
            const _y = y;
            const cls = 'px-2 py-2 text-sm rounded-lg border transition ' + (
                _y === selYear ? 'bg-[#faa938] text-white border-[#faa938]'
                : _y === now.getFullYear() ? 'border-[#faa938] text-[#faa938] bg-white'
                : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]'
            );
            grid.appendChild(makeBtn(String(_y), cls, () => { viewYear = _y; renderMonth(); }));
        }
    }

    function navigate(dir) {
        if (view === 'month') { viewYear += dir; renderMonth(); }
        else { viewYear += dir * 12; renderYear(); }
    }

    const openPanel  = () => { viewYear = selYear; renderMonth(); panel.classList.remove('hidden'); };
    const closePanel = () => panel.classList.add('hidden');

    btn.addEventListener('click', (e) => { e.stopPropagation(); panel.classList.contains('hidden') ? openPanel() : closePanel(); });
    navLabel.addEventListener('click', (e) => { e.stopPropagation(); if (view === 'month') renderYear(); });
    btnPrev?.addEventListener('click', (e) => { e.stopPropagation(); navigate(-1); });
    btnNext?.addEventListener('click', (e) => { e.stopPropagation(); navigate(1); });
    btnThisMonth?.addEventListener('click', (e) => { e.stopPropagation(); setPeriod(now.getFullYear(), now.getMonth()); closePanel(); });
    btnClose?.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });
    document.addEventListener('click', (e) => { if (!picker.contains(e.target)) closePanel(); });

    updateDisplayOnly(selYear, selMonth);
})();

// Modal Presensi
window.openModalPresensi = function(id) {
    const modal    = document.getElementById('modalPresensi');
    const subtitle = document.getElementById('presensiSubtitle');
    const body     = document.getElementById('presensiBody');

    subtitle.textContent = '-';
    body.innerHTML = '<p class="py-4 text-center text-sm text-gray-400">Memuat data...</p>';
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    fetch(`{{ url('/pimpinan/pengajuan') }}/${id}/presensi`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        subtitle.textContent = `${data.nama} · ${data.nip}`;
        body.innerHTML = `
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Tanggal</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.tanggal}</div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Status Kehadiran</label>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                    ${data.status ?? '<span class="text-gray-400">Tidak ada data presensi</span>'}
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Jam Kedatangan</label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.jam_masuk ?? '-'}</div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Jam Kepulangan</label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">${data.jam_pulang ?? '-'}</div>
                </div>
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = '<p class="py-4 text-center text-sm text-red-400">Gagal memuat data presensi.</p>';
    });
};

window.closeModalPresensi = function() {
    document.getElementById('modalPresensi').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};
</script>
@endpush

@endsection

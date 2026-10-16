@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 my-4 sm:my-6">

    {{-- Top Action Bar / Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-5 border-b border-gray-100">
        <div class="flex items-center gap-3">
            {{-- Tombol Back Icon --}}
            <a href="{{ route('admin.presensi') }}"
               class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 hover:text-[#faa938] hover:border-[#faa938] hover:bg-amber-50/40 shadow-sm transition-all"
               title="Kembali ke Presensi Pegawai">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-800 tracking-tight">Riwayat Presensi</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-[#faa938] border border-amber-200">
                        Upload Log
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Daftar riwayat berkas spreadsheet presensi pegawai yang telah diunggah ke sistem.
                </p>
            </div>
        </div>

        {{-- Navigasi Aksi: Kembali ke Presensi & Tombol Close (x) --}}
        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <a href="{{ route('admin.presensi') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-[#faa938] hover:text-[#faa938] shadow-sm transition-all"
               title="Kembali ke Halaman Presensi">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Presensi</span>
            </a>
            <a href="{{ route('admin.presensi') }}"
               class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100 shadow-sm transition-all"
               title="Tutup (Kembali ke Presensi)">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Filter Periode & Info --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        {{-- Filter Periode --}}
        <div class="relative shrink-0" id="periodPicker">
            <button type="button" id="periodBtn"
                class="inline-flex items-center h-10 gap-2 px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current">
                    <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                </svg>
                <span id="periodLabel" class="leading-none">Semua Periode</span>
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
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                    <button type="button" id="btnAllPeriod" class="text-xs font-medium text-gray-500 hover:text-[#faa938]">Semua</button>
                    <button type="button" id="btnThisMonth" class="text-xs font-medium text-gray-500 hover:text-[#faa938]">Bulan ini</button>
                    <button type="button" id="btnClosePanel" class="px-2.5 py-1 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:border-[#faa938] hover:text-[#faa938]">Tutup</button>
                </div>
            </div>
        </div>

        <div class="text-xs text-gray-400">
            Total Riwayat: <span id="totalRiwayatCount" class="font-semibold text-gray-700">{{ count($daftarHadir) }}</span> berkas
        </div>
    </div>

    {{-- Petunjuk Geser & Scrollbar di Layar HP (Mobile) --}}
    <div class="sm:hidden table-scroll-hint flex flex-col gap-1.5 mb-2.5 text-[11px] font-medium text-slate-500">
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

    {{-- Tabel Container --}}
    <div id="tableContainer" class="overflow-x-auto my-3 bg-white rounded-xl shadow-sm border border-gray-200">
        <table class="w-full min-w-[700px] border-collapse">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200">
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Periode
                    </th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Nama Berkas
                    </th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        User Pengunggah
                    </th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Waktu Unggah
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white" id="riwayatTableBody">
                @forelse($daftarHadir as $d)
                <tr class="riwayat-row hover:bg-amber-50/20 transition-colors" data-periode="{{ $d->periode }}">
                    <!-- Periode -->
                    <td class="py-3 px-4 text-sm text-gray-900 text-center font-medium whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $d->periode)->translatedFormat('F Y') }}
                        </span>
                    </td>

                    <!-- Nama File -->
                    <td class="py-3 px-4 text-sm text-gray-900 font-medium">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="truncate max-w-md" title="{{ $d->nama_file }}">{{ $d->nama_file ?? '-' }}</span>
                        </div>
                    </td>

                    <!-- User Upload -->
                    <td class="py-3 px-4 text-sm text-gray-700 text-center whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $d->uploaded_by ?? '-' }}
                        </span>
                    </td>

                    <!-- Tanggal Upload -->
                    <td class="py-3 px-4 text-sm text-gray-500 text-center whitespace-nowrap font-mono text-xs">
                        {{ \Carbon\Carbon::parse($d->uploaded_at)->translatedFormat('d M Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr id="riwayatEmptyRow">
                    <td colspan="4" class="p-8 text-center text-sm text-gray-400">
                        Tidak ada data riwayat upload.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    // =====================
    // PERIOD PICKER & FILTER
    // =====================
    (function () {
        const now = new Date();
        const pad2 = (n) => String(n).padStart(2, '0');
        const makeBtn = (text, cls, onClick) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = text;
            b.className = cls;
            b.addEventListener('click', (e) => { e.stopPropagation(); onClick(); });
            return b;
        };

        const el           = (id) => document.getElementById(id);
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
        const btnAllPeriod = el('btnAllPeriod');
        const btnClose     = el('btnClosePanel');

        if (!picker || !btn || !panel) return;

        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        let view     = 'month';
        let viewYear = now.getFullYear();
        let selYear  = null;
        let selMonth = null;

        function filterTable(targetPeriod) {
            const rows = document.querySelectorAll('#riwayatTableBody tr.riwayat-row');
            let visibleCount = 0;
            rows.forEach(r => {
                const rowPeriod = r.getAttribute('data-periode');
                if (!targetPeriod || rowPeriod === targetPeriod) {
                    r.style.display = '';
                    visibleCount++;
                } else {
                    r.style.display = 'none';
                }
            });

            let emptyRow = document.getElementById('riwayatEmptyRow');
            if (!emptyRow && visibleCount === 0) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'riwayatEmptyRow';
                emptyRow.innerHTML = '<td colspan="4" class="p-8 text-center text-sm text-gray-400">Tidak ada berkas untuk periode yang dipilih.</td>';
                document.getElementById('riwayatTableBody').appendChild(emptyRow);
            } else if (emptyRow) {
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }

            const countEl = document.getElementById('totalRiwayatCount');
            if (countEl) countEl.textContent = String(visibleCount);
        }

        function setPeriod(y, m) {
            selYear = y; selMonth = m;
            periodLabel.textContent = `${monthShort[m]} ${y}`;
            const val = `${y}-${pad2(m + 1)}`;
            periodValue.value = val;
            filterTable(val);
        }

        function resetAllPeriod() {
            selYear = null; selMonth = null;
            periodLabel.textContent = 'Semua Periode';
            periodValue.value = '';
            filterTable('');
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
            viewYear = selYear || now.getFullYear();
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

        btnAllPeriod?.addEventListener('click', (e) => {
            e.stopPropagation();
            resetAllPeriod();
            closePanel();
        });

        btnClose?.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) closePanel();
        });
    })();

    // =====================
    // MOBILE TABLE SCROLL SYNC
    // =====================
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('tableContainer');
        const thumb = document.querySelector('.table-scroll-thumb');
        const pctText = document.querySelector('.table-scroll-pct');

        if (container && thumb) {
            container.addEventListener('scroll', function () {
                const maxScroll = container.scrollWidth - container.clientWidth;
                if (maxScroll <= 0) return;
                const scrollLeft = container.scrollLeft;
                const pct = Math.min(100, Math.max(0, Math.round((scrollLeft / maxScroll) * 100)));
                const thumbWidth = 30; // 30%
                const maxTranslate = 100 - thumbWidth;
                const translateVal = (pct / 100) * maxTranslate;
                thumb.style.transform = `translateX(${translateVal}%)`;
                if (pctText) pctText.textContent = `${pct}%`;
            });
        }
    });
</script>

@endsection

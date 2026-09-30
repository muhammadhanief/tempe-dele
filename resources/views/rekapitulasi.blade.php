@extends('layouts.app')

@section('title', 'Rekapitulasi')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Period Picker --}}
    <div class="mb-5 mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:w-auto" id="periodPicker">

            {{-- Trigger --}}
            <button
                type="button"
                id="periodBtn"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-full border border-[#faa938] bg-white px-4 text-sm font-semibold text-gray-900 transition-all hover:bg-[#faa938]/5 sm:w-auto"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-4 w-4 fill-current">
                    <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                </svg>

                <span id="periodLabel" class="leading-none">Periode</span>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current opacity-80">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <input type="hidden" id="periodValue" name="period" value="">

            {{-- Popup --}}
            <div
                id="periodPanel"
                class="absolute left-0 z-50 mt-2 hidden w-[calc(100vw-2rem)] max-w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-lg sm:w-80"
            >
                {{-- Header Tahun --}}
                <div class="mb-3 flex items-center justify-between">
                    <button
                        type="button"
                        id="yearPrev"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <div class="w-24 text-center">
                        <button
                            type="button"
                            id="yearDisplay"
                            class="w-full rounded-lg border border-transparent px-2 py-1 text-sm font-semibold text-gray-900 transition-all hover:border-gray-200 hover:bg-gray-50"
                        >
                            <span id="yearLabel">2026</span>
                        </button>

                        <input
                            id="yearInput"
                            type="number"
                            min="1900"
                            max="2100"
                            class="hidden w-full border-b border-transparent bg-transparent text-center text-sm font-semibold focus:border-[#faa938] focus:outline-none"
                        />
                    </div>

                    <button
                        type="button"
                        id="yearNext"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                {{-- Grid Bulan --}}
                <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                {{-- Actions --}}
                <div class="mt-3 flex items-center justify-between">
                    <button type="button" id="btnThisMonth" class="text-sm font-medium text-gray-600 transition-all hover:text-[#faa938]">
                        Bulan ini
                    </button>

                    <button
                        type="button"
                        id="btnClosePanel"
                        class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 transition-all hover:border-[#faa938] hover:text-[#faa938]"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
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
    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[1050px] rounded-xl">
            <thead>
                <tr class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">

                    <th scope="col" class="rounded-tl-xl px-4 py-4 text-left text-sm font-semibold capitalize leading-6 text-gray-900 lg:p-5">
                        Tanggal
                    </th>

                    <th scope="col" class="px-4 py-4 text-left text-sm font-semibold capitalize leading-6 text-gray-900 lg:p-5">
                        Jenis Hari
                    </th>

                    <th scope="col" class="px-4 py-4 text-center text-sm font-semibold capitalize leading-6 text-gray-900 lg:p-5">
                        Jam Disetujui
                    </th>

                    @php
                        $tooltips = [
                            'HB 2' => 'Hari Biasa, lembur 2 jam',
                            'HB 3' => 'Hari Biasa, lembur 3 jam',
                            'HB 4' => 'Hari Biasa, lembur 4 jam',
                            'HL 2' => 'Hari Libur, lembur 2 jam',
                            'HL 3' => 'Hari Libur, lembur 3 jam',
                            'HL 4' => 'Hari Libur, lembur 4 jam',
                            'HL 5' => 'Hari Libur, lembur 5 jam',
                            'HL 6' => 'Hari Libur, lembur 6 jam',
                        ];
                    @endphp

                    @foreach($tooltips as $label => $desc)
                        <th scope="col"
                            class="group relative px-4 py-4 text-center text-sm font-semibold capitalize leading-6 text-gray-900 lg:p-5 {{ $loop->last ? 'rounded-tr-xl' : '' }}">
                            {{ $label }}

                            <span class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 w-max -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-normal normal-case tracking-normal text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                {{ $desc }}
                            </span>
                        </th>
                    @endforeach

                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300">
                @forelse($rows as $r)
                    @php
                        $jam = $r['jam'];
                        $isHb = $r['hari'] == 0;
                        $isHl = $r['hari'] == 1;

                        $centang = '<svg class="mx-auto h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M530.8 134.1C545.1 144.5 548.3 164.5 537.9 178.8L281.9 530.8C276.4 538.4 267.9 543.1 258.5 543.9C249.1 544.7 240 541.2 233.4 534.6L105.4 406.6C92.9 394.1 92.9 373.8 105.4 361.3C117.9 348.8 138.2 348.8 150.7 361.3L252.2 462.8L486.2 141.1C496.6 126.8 516.6 123.6 530.9 134z"/></svg>';
                    @endphp

                    <tr class="bg-white transition-all duration-300 hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 lg:p-5">
                            {{ \Carbon\Carbon::parse($r['tanggal'])->translatedFormat('d F Y') }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 lg:p-5">
                            {{ $r['jenis_hari'] }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-gray-900 lg:p-5">
                            {{ $jam }}
                        </td>

                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHb && $jam == 2) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHb && $jam == 3) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHb && $jam == 4) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHl && $jam == 2) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHl && $jam == 3) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHl && $jam == 4) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHl && $jam == 5) ? $centang : '' !!}</td>
                        <td class="px-4 py-4 text-center lg:p-5">{!! ($isHl && $jam == 6) ? $centang : '' !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-400 lg:p-8">
                            Tidak ada data lembur untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr class="border-t border-gray-600 font-semibold">
                    <td colspan="3" class="px-4 py-4 text-right text-sm text-gray-900 lg:p-5">
                        Total
                    </td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hb2'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hb3'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hb4'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hl2'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hl3'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hl4'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hl5'] ?: '-' }}</td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">{{ $total['hl6'] ?: '-' }}</td>
                </tr>

                <tr class="border-t border-gray-600 font-semibold">
                    <td colspan="3" class="px-4 py-4 text-right text-sm text-gray-900 lg:p-5">
                        Jumlah Bekerja Hari Biasa
                    </td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">
                        {{ $jumlahHb ?: '-' }}
                    </td>
                    <td colspan="6" class="px-4 py-4 text-right text-sm text-gray-900 lg:p-5">
                        Jumlah Bekerja Hari Libur
                    </td>
                    <td class="px-4 py-4 text-center text-sm text-gray-900 lg:p-5">
                        {{ $jumlahHl ?: '-' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

<script>
(function () {
    const el = (id) => document.getElementById(id);

    const periodPicker = el("periodPicker");
    const btn = el("periodBtn");
    const panel = el("periodPanel");

    const monthGrid = el("monthGrid");
    const yearLabel = el("yearLabel");
    const yearDisplay = el("yearDisplay");
    const yearInput = el("yearInput");

    const periodLabel = el("periodLabel");
    const periodValue = el("periodValue");

    const yearPrev = el("yearPrev");
    const yearNext = el("yearNext");
    const btnThisMonth = el("btnThisMonth");
    const btnClosePanel = el("btnClosePanel");

    if (!periodPicker || !btn || !panel) return;

    const monthNames = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    const now = new Date();

    let selectedYear = {{ \Carbon\Carbon::parse($bulan . '-01')->year }};
    let selectedMonth = {{ \Carbon\Carbon::parse($bulan . '-01')->month }};
    let viewYear = selectedYear;

    function pad2(number) {
        return String(number).padStart(2, "0");
    }

    function setPeriod(year, month) {
        selectedYear = year;
        selectedMonth = month;

        const value = `${year}-${pad2(month)}`;

        periodLabel.textContent = `${monthNames[month - 1]} ${year}`;
        periodValue.value = value;
        periodValue.setAttribute("value", value);

        window.location.href = `?bulan=${value}`;
    }

    function syncYearUI() {
        if (yearLabel) yearLabel.textContent = String(viewYear);
        if (yearInput) yearInput.value = String(viewYear);
    }

    function renderMonths() {
        syncYearUI();

        monthGrid.innerHTML = "";

        for (let month = 1; month <= 12; month++) {
            const isSelected = month === selectedMonth && viewYear === selectedYear;

            const button = document.createElement("button");
            button.type = "button";
            button.className =
                "rounded-lg border px-2 py-2 text-sm transition-all " +
                (isSelected
                    ? "border-[#faa938] bg-[#faa938] text-white"
                    : "border-gray-200 bg-white text-gray-800 hover:border-[#faa938] hover:text-[#faa938]");

            button.textContent = monthNames[month - 1].slice(0, 3);

            button.addEventListener("click", (event) => {
                event.stopPropagation();
                setPeriod(viewYear, month);
                closePanel();
            });

            monthGrid.appendChild(button);
        }
    }

    function openPanel() {
        panel.classList.remove("hidden");
        viewYear = selectedYear;
        renderMonths();
    }

    function exitEditMode() {
        if (!yearInput || !yearDisplay) return;

        yearInput.classList.add("hidden");
        yearDisplay.classList.remove("hidden");
    }

    function closePanel() {
        panel.classList.add("hidden");
        exitEditMode();
    }

    function startYearEdit() {
        if (!yearInput || !yearDisplay) return;

        yearDisplay.classList.add("hidden");
        yearInput.classList.remove("hidden");
        yearInput.focus();
        yearInput.select();
    }

    function applyYearFromInput(commit) {
        if (!yearInput) return;

        if (commit) {
            const year = parseInt(yearInput.value, 10);

            if (!Number.isNaN(year) && year >= 1900 && year <= 2100) {
                viewYear = year;
            } else {
                yearInput.value = String(viewYear);
            }
        } else {
            yearInput.value = String(viewYear);
        }

        exitEditMode();
        renderMonths();
    }

    btn.addEventListener("click", (event) => {
        event.stopPropagation();

        if (panel.classList.contains("hidden")) {
            openPanel();
        } else {
            closePanel();
        }
    });

    yearDisplay?.addEventListener("click", (event) => {
        event.stopPropagation();
        startYearEdit();
    });

    yearInput?.addEventListener("click", (event) => {
        event.stopPropagation();
    });

    yearInput?.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            applyYearFromInput(true);
        }

        if (event.key === "Escape") {
            event.preventDefault();
            applyYearFromInput(false);
        }
    });

    yearInput?.addEventListener("blur", () => {
        applyYearFromInput(true);
    });

    yearPrev?.addEventListener("click", (event) => {
        event.stopPropagation();
        viewYear -= 1;
        renderMonths();
    });

    yearNext?.addEventListener("click", (event) => {
        event.stopPropagation();
        viewYear += 1;
        renderMonths();
    });

    btnThisMonth?.addEventListener("click", (event) => {
        event.stopPropagation();
        setPeriod(now.getFullYear(), now.getMonth() + 1);
        closePanel();
    });

    btnClosePanel?.addEventListener("click", (event) => {
        event.stopPropagation();
        closePanel();
    });

    document.addEventListener("click", (event) => {
        if (!periodPicker.contains(event.target)) {
            closePanel();
        }
    });

    periodLabel.textContent = `${monthNames[selectedMonth - 1]} ${selectedYear}`;

    const initialValue = `${selectedYear}-${pad2(selectedMonth)}`;
    periodValue.value = initialValue;
    periodValue.setAttribute("value", initialValue);

    renderMonths();
})();
</script>

@endsection

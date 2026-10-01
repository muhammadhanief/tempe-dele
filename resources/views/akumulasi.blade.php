@extends('layouts.app')

@section('title', 'Akumulasi')

@section('content')

<div class="w-9/10 mx-auto flex flex-col">
    <div class=" overflow-x-auto">
        <div class="w-full inline-block align-middle">
            <!-- Period Picker (Month-Year) -->
        <div class="flex items-center justify-between mb-5 mt-5">
            <div class="relative" id="periodPicker">
                <!-- Trigger -->
                <button type="button" id="periodBtn"
                        class="inline-flex items-center h-10 gap-2 px-4 text-sm font-semibold
                        border border-[#faa938] bg-white text-gray-900 rounded-full shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current">
                        <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                        </svg>
                    <span id="periodLabel" class="leading-none">Periode</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-80">
                        <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                        </svg>
                </button>

                <input type="hidden" id="periodValue" name="period" value="">

                <!-- Popup -->
                <div id="periodPanel"
                    class="hidden absolute z-50 mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-lg p-3">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" id="yearPrev"
                            class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                            </svg>
                        </button>

                        <!-- Tahun -->
                        <div class="w-20 text-center">
                            <button type="button" id="yearDisplay"
                                    class="w-full text-sm font-semibold text-gray-900 px-2 py-1 rounded-lg
                                    hover:bg-gray-50 border border-transparent hover:border-gray-200">
                                <span id="yearLabel">2026</span>
                            </button>

                            <input id="yearInput" type="number" min="1900" max="2100"
                            class="hidden w-full text-center text-sm font-semibold bg-transparent
                                    border-b border-transparent focus:border-[#faa938]
                                    focus:outline-none"
                            />
                        </div>

                            <button type="button" id="yearNext"
                                    class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                                <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Grid bulan -->
                        <div class="grid grid-cols-3 gap-2" id="monthGrid"></div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between mt-3">
                            <button type="button" id="btnThisMonth"
                                class="text-sm font-medium text-gray-600 hover:text-[#faa938]">
                                Bulan ini
                            </button>

                            <button type="button" id="btnClosePanel"
                                class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium rounded-full
                                    border border-gray-300 text-gray-700 hover:border-[#faa938] hover:text-[#faa938]">
                                Tutup
                            </button>
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

            <div class="overflow-x-auto rounded-xl">
                <table class="w-full min-w-[950px] rounded-xl">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize rounded-tl-xl"> Tanggal </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Hari</th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Jam diajukan</th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Jam disetujui</th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Golongan </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Uang Lembur </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Uang Makan </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> Jumlah </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize"> pph 21 </th>
                            <th scope="col" class="p-5 text-left text-sm leading-6 font-semibold text-gray-900 capitalize rounded-tr-xl"> Terima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300 ">
                        <tr class="bg-white transition-all duration-500 hover:bg-gray-50">
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 ">24 Februari 2026</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">Bekerja</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">III/b</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">300.000</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">37.000 </td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">337.000</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">16.850</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">320.150</td>
                        </tr>
                        <tr class="bg-white transition-all duration-500 hover:bg-gray-50">
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 ">24 Februari 2026</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">Bekerja</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">III/b</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">300.000</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">37.000 </td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">337.000</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">16.850</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">320.150</td>
                        </tr>
                        <tr class="bg-white transition-all duration-500 hover:bg-gray-50">
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 ">24 Februari 2026</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">Bekerja</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 text-center">5</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">III/b</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">300.000</td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">37.000 </td>
                            <td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">337.000</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">16.850</td>
                            <td class=" p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">320.150</td>
                        </tr>
                        <tfoot>
                            <tr class="border-t border-gray-600 font-semibold">
                                <td colspan="9" class="p-5 text-right">Total</td>
                                <td class="p-5 text-left text-[#faa938]">960.450</td>
                            </tr>
                        </tfoot>
                </table>
            </div>
        </div>
      </div>


    <div class="flex justify-center mt-6">
        <nav class="inline-flex items-center p-1 rounded bg-white space-x-2">
            <a class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]" href="#">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                </svg>
            </a>
            <p class="text-gray-500">Page 1 of 10</p>
            <a class="p-1 rounded border text-black bg-white hover:text-white hover:bg-[#faa938] hover:border-[#faa938]" href="#">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                </svg>
            </a>
        </nav>
    </div>
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
    "Januari","Februari","Maret","April","Mei","Juni",
    "Juli","Agustus","September","Oktober","November","Desember"
  ];

  const now = new Date();
  let selectedYear = now.getFullYear();
  let selectedMonth = now.getMonth() + 1; // 1-12
  let viewYear = selectedYear;

  function pad2(n) { return String(n).padStart(2, "0"); }

  function setPeriod(year, month) {
    selectedYear = year;
    selectedMonth = month;

    periodLabel.textContent = `${monthNames[month - 1]} ${year}`;
    periodValue.value = `${year}-${pad2(month)}`;

    // TODO: filter/fetch data based on periodValue.value
    // console.log("period selected:", periodValue.value);
  }

  function syncYearUI() {
    if (yearLabel) yearLabel.textContent = String(viewYear);
    if (yearInput) yearInput.value = String(viewYear);
  }

  function renderMonths() {
    syncYearUI();
    monthGrid.innerHTML = "";

    for (let m = 1; m <= 12; m++) {
      const isSelected = (m === selectedMonth && viewYear === selectedYear);

      const b = document.createElement("button");
      b.type = "button";
      b.className =
        "px-2 py-2 text-sm rounded-lg border transition " +
        (isSelected
          ? "bg-[#faa938] text-white border-[#faa938]"
          : "bg-white text-gray-800 border-gray-200 hover:border-[#faa938] hover:text-[#faa938]");

      b.textContent = monthNames[m - 1].slice(0, 3);

      b.addEventListener("click", (e) => {
        e.stopPropagation();
        setPeriod(viewYear, m);
        closePanel();
      });

      monthGrid.appendChild(b);
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
      const y = parseInt(yearInput.value, 10);
      if (!Number.isNaN(y)) {
        selectedYear = y;
        viewYear = y;
      } else {
        yearInput.value = String(viewYear);
      }
    } else {
      yearInput.value = String(viewYear);
    }

    exitEditMode();
    renderMonths();
  }

  // Trigger popup
  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    panel.classList.contains("hidden") ? openPanel() : closePanel();
  });

  // Klik tahun -> edit
  yearDisplay?.addEventListener("click", (e) => {
    e.stopPropagation();
    startYearEdit();
  });

  // Enter / Escape / blur pada input
  yearInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") { e.preventDefault(); applyYearFromInput(true); }
    if (e.key === "Escape") { e.preventDefault(); applyYearFromInput(false); }
  });

  yearInput?.addEventListener("blur", () => applyYearFromInput(true));

  // Prev / Next year
  yearPrev?.addEventListener("click", (e) => {
    e.stopPropagation();
    viewYear -= 1;
    selectedYear = viewYear;
    renderMonths();
  });

  yearNext?.addEventListener("click", (e) => {
    e.stopPropagation();
    viewYear += 1;
    selectedYear = viewYear;
    renderMonths();
  });

  // Bulan ini
  btnThisMonth?.addEventListener("click", (e) => {
    e.stopPropagation();
    setPeriod(now.getFullYear(), now.getMonth() + 1);
    closePanel();
  });

  // Tutup
  btnClosePanel?.addEventListener("click", (e) => {
    e.stopPropagation();
    closePanel();
  });

  // Klik luar -> tutup
  document.addEventListener("click", (e) => {
    if (!periodPicker.contains(e.target)) closePanel();
  });

  // Init default realtime
  setPeriod(selectedYear, selectedMonth);
})();
</script>

@endsection

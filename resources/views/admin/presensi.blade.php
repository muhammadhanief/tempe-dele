@extends('layouts.app')

@section('title', 'Presensi Pegawai')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-5 lg:pt-6 pb-2">

    {{-- Row 1: Period + Search + Actions --}}
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 sm:gap-3">

        {{-- Filter Periode --}}
        <div class="relative w-full sm:w-auto shrink-0" id="periodPicker">
            <button type="button" id="periodBtn"
                class="inline-flex w-full sm:w-auto items-center justify-between sm:justify-start h-10 gap-2 px-3 sm:px-4 text-sm font-medium border border-gray-200 bg-white text-gray-700 rounded-xl hover:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-4 h-4 fill-current shrink-0">
                    <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                </svg>
                <span id="periodLabel" class="leading-none truncate">Mar 2026</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current opacity-50 shrink-0">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>
            <input type="hidden" id="periodValue" name="period" value="">
            {{-- Panel: full-width on mobile, fixed width on larger --}}
            <div id="periodPanel" class="hidden absolute z-50 mt-2 left-0 right-0 sm:right-auto w-full sm:w-72 max-w-none rounded-xl border border-gray-200 bg-white shadow-lg p-3">
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

        {{-- Filter Pegawai: grows to fill space --}}
        <div class="relative w-full sm:flex-1 sm:min-w-[16rem]">
            <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                onclick="toggleDropdown()" oninput="filterDropdown()" autocomplete="off"
                class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                    <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                </svg>
            </div>
            <div id="dropdownPegawai" class="hidden absolute z-40 mt-1 w-full max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listPegawai"></ul>
            </div>
        </div>

        {{-- Divider (hidden on xs) --}}
        <div class="hidden sm:block h-6 self-center border-l border-gray-200"></div>

        {{-- Action buttons --}}
        <div class="flex w-full sm:w-auto items-center justify-end gap-2 shrink-0">
            {{-- Kelola Hari Libur --}}
            <button type="button" onclick="openModalHariLibur()"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:border-[#faa938] hover:text-[#faa938] transition-colors"
                title="Kelola Hari Libur">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </button>

            {{-- Upload Presensi --}}
            <button type="button" onclick="openUploadModal()"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#faa938] text-white hover:brightness-95 transition-all"
                title="Upload Presensi">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Riwayat Upload --}}
    <div class="flex justify-end mt-3 sm:mt-5">
        <a href="{{ route('admin.riwayat_presensi') }}"
            class="text-sm font-medium text-gray-500 hover:text-[#faa938] transition-colors whitespace-nowrap">
            Riwayat Upload
        </a>
    </div>
</div>

{{-- ===================== KALENDER ===================== --}}
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pb-6">
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-1.5 sm:p-3 lg:p-4 overflow-x-auto overscroll-x-contain">
        <div id="presensiGrid" class="min-w-[320px] sm:min-w-0"></div>
    </div>
</div>

{{-- ===================== MODAL DETAIL PRESENSI (Alpine) ===================== --}}
<div x-data="{ open: false, detail: null }"
     x-on:presensi-detail.window="open = true; detail = $event.detail;">
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
         style="display:none;">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
        {{-- Slide-up sheet on mobile, centered modal on sm+ --}}
        <div class="relative w-full sm:max-w-md max-h-[92vh] sm:max-h-[85vh] overflow-hidden flex flex-col rounded-t-2xl sm:rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
            {{-- Drag handle (mobile only) --}}
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm text-gray-500" x-text="detail?.date ?? '-'"></div>
                    <div class="text-base sm:text-lg font-semibold text-gray-900">Detail Presensi</div>
                </div>
                <button class="h-9 w-9 rounded-xl hover:bg-gray-50 text-gray-400" @click="open=false">✕</button>
            </div>
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-gray-100 p-3">
                        <div class="text-xs text-gray-500 mb-0.5">Status</div>
                        <div class="font-semibold text-gray-900" x-text="detail?.status ?? '-'"></div>
                    </div>
                    <div class="rounded-xl bg-gray-100 p-3">
                        <div class="text-xs text-gray-500 mb-0.5">Sumber</div>
                        <div class="font-semibold text-gray-900" x-text="detail?.source ?? 'Upload Admin'"></div>
                    </div>
                    <div class="rounded-xl bg-gray-100 p-3">
                        <div class="text-xs text-gray-500 mb-0.5">Masuk</div>
                        <div class="font-semibold text-gray-900" x-text="detail?.checkIn ?? '-'"></div>
                    </div>
                    <div class="rounded-xl bg-gray-100 p-3">
                        <div class="text-xs text-gray-500 mb-0.5">Pulang</div>
                        <div class="font-semibold text-gray-900" x-text="detail?.checkOut ?? '-'"></div>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-5 border-t border-gray-100 flex justify-end">
                <button class="h-10 px-4 rounded-xl border border-gray-200 hover:bg-gray-50 text-sm" @click="open=false">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL UPLOAD ===================== --}}
<div id="modalUpload" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/40">
    <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md mx-0 sm:mx-4 p-5 sm:p-6 max-h-[92vh] overflow-y-auto">
        <div class="flex justify-center pt-0 pb-3 sm:hidden">
            <div class="w-10 h-1 rounded-full bg-gray-200"></div>
        </div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800">Upload Presensi</h2>
            <button onclick="closeUploadModal()" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="uploadDropzone"
            class="border-2 border-dashed border-gray-200 rounded-xl p-6 sm:p-8 text-center cursor-pointer hover:border-[#faa938] transition-colors"
            onclick="document.getElementById('fileInput').click()"
            ondragover="event.preventDefault(); this.classList.add('border-[#faa938]')"
            ondragleave="this.classList.remove('border-[#faa938]')"
            ondrop="handleDrop(event)">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <p class="text-sm text-gray-500">Drag & drop file xlsx atau <span class="text-[#faa938] font-medium">browse</span></p>
            <p id="fileName" class="text-xs text-gray-400 mt-1">Belum ada file dipilih</p>
            <input type="file" id="fileInput" accept=".xlsx,.xls" class="hidden" onchange="handleFileSelect(this)"/>
        </div>
        <div id="uploadAlert" class="hidden mt-3 rounded-xl px-4 py-3 text-sm font-medium"></div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 mt-5">
            <button onclick="closeUploadModal()" class="w-full sm:w-auto px-4 py-2 rounded-lg text-sm text-gray-600 border border-gray-200 hover:bg-gray-50">Batal</button>
            <button onclick="submitUpload()" class="w-full sm:w-auto px-4 py-2 rounded-lg text-sm text-white bg-[#faa938] hover:brightness-95">Upload</button>
        </div>
    </div>
</div>

{{-- ===================== MODAL HARI LIBUR ===================== --}}
<div id="modalHariLibur" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalHariLibur()"></div>
    {{-- Slide-up on mobile, centered on sm+ --}}
    <div class="absolute bottom-0 left-0 right-0 sm:relative sm:flex sm:min-h-screen sm:items-center sm:justify-center sm:p-4">
        <div class="w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[92vh] sm:max-h-[88vh] overflow-y-auto">
            {{-- Drag handle mobile --}}
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>
            <div class="flex items-center justify-between border-b px-4 sm:px-6 py-3 sm:py-4 sticky top-0 bg-white z-10">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Kelola Hari Libur</h2>
                <button onclick="closeModalHariLibur()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            {{-- Kalender Range Picker --}}
            <div class="px-4 sm:px-6 py-4 border-b">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" id="hlPrev" class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>
                    <span id="hlNavLabel" class="text-sm font-medium text-gray-900"></span>
                    <button type="button" id="hlNext" class="p-2 rounded-lg border border-gray-200 hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-3 h-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>
                <div id="hlGrid"></div>
                <div id="hlRangeInfo" class="hidden mt-3 text-xs text-gray-500 text-center"></div>
                <form id="formHariLibur" method="POST" action="{{ route('admin.hari-libur.store') }}" class="mt-3 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-end">
                    @csrf
                    <input type="hidden" name="tanggal_mulai" id="hlInputMulai" />
                    <input type="hidden" name="tanggal_selesai" id="hlInputSelesai" />
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="hlKeterangan" placeholder="contoh: Cuti Bersama Idul Fitri"
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
            <div id="hlList" class="px-4 sm:px-6 py-4 max-h-48 sm:max-h-60 overflow-y-auto space-y-1">
                @php $grouped = $hariLibur->groupBy('grup_id'); @endphp
            
                @forelse($grouped as $grupId => $items)
                    <div
                        class="hl-list-item flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
                        data-dates="{{ $items->pluck('tanggal')->implode(',') }}"
                    >
                        <div class="min-w-0 mr-3">
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
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="hl-empty-default text-sm text-gray-400 text-center py-4">
                        Belum ada hari libur yang ditambahkan.
                    </p>
                @endforelse
            
                <p id="hlEmptyByMonth" class="hidden text-sm text-gray-400 text-center py-4">
                    Belum ada hari libur pada bulan ini.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
// =====================
// GLOBAL STATE
// =====================
const now = new Date();
let current = new Date(now.getFullYear(), now.getMonth(), 1);
let selectedEmployeeId = null;
let cachedTim      = [];
let cachedPegawai  = [];
let selYear        = now.getFullYear();
let selMonth       = now.getMonth();
let presensiData   = {};

const hariLiburSet = new Set(@json($hariLibur->pluck('tanggal')->toArray()));

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
// RESPONSIVE HELPER
// =====================
function isMobile() { return window.matchMedia('(max-width: 639px)').matches; }
function isTablet() { return window.matchMedia('(min-width: 640px) and (max-width: 1023px)').matches; }

// =====================
// FETCH DATA
// =====================
async function fetchTim() {
    const res = await fetch('/admin/presensi/tim');
    cachedTim = await res.json();
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
// DROPDOWN PEGAWAI
// =====================
function populateDropdown(filter = '', data = []) {
    const list = document.getElementById('listPegawai');
    list.innerHTML = '';

    const keyword = filter.toLowerCase().trim();
    const filtered = data.filter(e => `${e.nama} ${e.nip}`.toLowerCase().includes(keyword));

    if (!filtered.length) {
        const li = document.createElement('li');
        li.className = 'px-4 py-3 text-sm text-gray-400 text-center';
        li.textContent = 'Pegawai tidak ditemukan';
        list.appendChild(li);
        return;
    }

    filtered.forEach(emp => {
        const li = document.createElement('li');
        li.className = 'cursor-pointer px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100';
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
    fetchPresensi();
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

    function setPeriod(y, m) {
        selYear = y; selMonth = m;
        periodLabel.textContent = `${monthShort[m]} ${y}`;
        periodValue.value = `${y}-${pad2(m + 1)}`;
        current = new Date(y, m, 1);
        selectedEmployeeId ? fetchPresensi() : render();
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

    function openPanel()  { viewYear = selYear; renderMonth(); panel.classList.remove('hidden'); }
    function closePanel() { panel.classList.add('hidden'); }

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.contains('hidden') ? openPanel() : closePanel();
    });
    navLabel.addEventListener('click', (e) => { e.stopPropagation(); if (view === 'month') renderYear(); });
    btnPrev?.addEventListener('click', (e)  => { e.stopPropagation(); navigate(-1); });
    btnNext?.addEventListener('click', (e)  => { e.stopPropagation(); navigate(1); });
    btnThisMonth?.addEventListener('click', (e) => { e.stopPropagation(); setPeriod(now.getFullYear(), now.getMonth()); closePanel(); });
    btnClose?.addEventListener('click', (e) => { e.stopPropagation(); closePanel(); });
    document.addEventListener('click', (e) => { if (!picker.contains(e.target)) closePanel(); });

    setPeriod(selYear, selMonth);
})();

// =====================
// UPLOAD MODAL
// =====================
let selectedFile = null;

window.openUploadModal = function () {
    selectedFile = null;
    document.getElementById('fileName').textContent = 'Belum ada file dipilih';
    document.getElementById('uploadAlert').classList.add('hidden');
    const modal = document.getElementById('modalUpload');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.closeUploadModal = function () {
    const modal = document.getElementById('modalUpload');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

window.handleFileSelect = function (input) {
    if (input.files[0]) {
        selectedFile = input.files[0];
        document.getElementById('fileName').textContent = selectedFile.name;
    }
};

window.handleDrop = function (e) {
    e.preventDefault();
    document.getElementById('uploadDropzone').classList.remove('border-[#faa938]');
    const file = e.dataTransfer.files[0];
    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
        selectedFile = file;
        document.getElementById('fileName').textContent = file.name;
    }
};

function showUploadAlert(message, type = 'success') {
    const el = document.getElementById('uploadAlert');
    el.textContent = message;
    el.className = type === 'success'
        ? 'mt-3 rounded-xl px-4 py-3 text-sm font-medium bg-green-50 text-green-700 border border-green-200'
        : 'mt-3 rounded-xl px-4 py-3 text-sm font-medium bg-red-50 text-red-700 border border-red-200';
    el.classList.remove('hidden');
}

window.submitUpload = async function () {
    if (!selectedFile) { showUploadAlert('Pilih file xlsx terlebih dahulu.', 'error'); return; }

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('_token', '{{ csrf_token() }}');

    try {
        showUploadAlert('Sedang memproses...');
        const res  = await fetch('/admin/presensi/upload', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
            showUploadAlert(data.message ?? 'Upload berhasil!');
            setTimeout(() => closeUploadModal(), 1500);
        } else {
            showUploadAlert(data.message ?? 'Gagal upload.', 'error');
        }
    } catch {
        showUploadAlert('Gagal menghubungi server.', 'error');
    }
};

document.getElementById('modalUpload')?.addEventListener('click', function (e) {
    if (e.target === this) closeUploadModal();
});

// =====================
// FETCH PRESENSI
// =====================
async function fetchPresensi() {
    if (!selectedEmployeeId) return;

    const year    = current.getFullYear();
    const month   = String(current.getMonth() + 1).padStart(2, '0');
    const pegawai = cachedPegawai.find(p => p.id_pegawai === selectedEmployeeId);
    const niplama = pegawai?.nip_lama ?? '';

    if (!niplama) return;

    const url = `/admin/presensi/kalender?niplama=${niplama}&periode=${year}-${month}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    presensiData = await res.json();
    render();
}

// =====================
// RENDER KALENDER
// =====================
function statusColor(st) {
    return {
        WFO:  'text-green-600',
        WFA:  'text-blue-600',
        WFOL: 'text-sky-600',
        DL:   'text-amber-600',
        CUTI: 'text-rose-600',
        KN:   'text-gray-500',
    }[st] || 'text-gray-700';
}

function statusBg(st) {
    return {
        WFO:  'bg-green-50',
        WFA:  'bg-blue-50',
        WFOL: 'bg-sky-50',
        DL:   'bg-amber-50',
        CUTI: 'bg-rose-50',
        KN:   'bg-gray-50',
    }[st] || '';
}

function fmt(d) {
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function render() {
    const gridEl = document.getElementById('presensiGrid');
    if (!gridEl) return;

    const mobile  = isMobile();
    const tablet  = isTablet();
    const year    = current.getFullYear();
    const month   = current.getMonth();

    const start = new Date(year, month, 1);
    start.setDate(start.getDate() - ((start.getDay() + 6) % 7));

    const end = new Date(year, month + 1, 0);
    end.setDate(end.getDate() + (6 - (end.getDay() + 6) % 7));

    const today = fmt(new Date());
    const days  = [];
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        days.push(new Date(d));
    }

    // Day name labels: 1 char on mobile, 3 chars on tablet, full on desktop
    const dayNamesFull   = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const dayNamesMid    = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    const dayNamesShort  = ['Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb', 'Mg'];

    const dayLabels = mobile ? dayNamesShort : (tablet ? dayNamesMid : dayNamesFull);

    // Cell min-height
    const cellH = mobile ? 'min-h-[68px]' : (tablet ? 'min-h-[92px]' : 'min-h-[112px]');

    // Header padding
    const hPad = mobile ? 'px-0.5 py-2' : (tablet ? 'px-2 py-2.5' : 'px-4 py-3');
    const cPad = mobile ? 'px-0.5 py-1.5' : (tablet ? 'px-2 py-2'   : 'px-4 py-3');

    gridEl.innerHTML = `
        <div class="grid grid-cols-7 gap-px rounded-2xl overflow-hidden ring-1 ring-gray-200 bg-gray-200">
            ${dayLabels.map((n, i) => `
                <div class="bg-white ${hPad} text-xs font-semibold text-center ${i === 6 ? 'text-rose-500' : 'text-gray-500'}">
                    ${n}
                </div>
            `).join('')}
            ${days.map(d => {
                const key     = fmt(d);
                const inMonth = d.getMonth() === month;
                const isSun   = ((d.getDay() + 6) % 7) === 6;
                const isLibur = hariLiburSet.has(key);
                const p       = presensiData[key];

                // Build status content based on screen size
                let statusHtml = '';
                if (p) {
                    const ci = p.jam_mulai   ? p.jam_mulai.split(' ')[1].slice(0,5)   : '—';
                    const co = p.jam_selesai ? p.jam_selesai.split(' ')[1].slice(0,5) : '—';
                    if (mobile) {
                        // Mobile: just show status badge
                        statusHtml = `
                            <div class="mt-1 flex justify-center">
                                <span class="inline-block max-w-full truncate text-[9px] font-bold leading-none px-1 py-0.5 rounded ${statusColor(p.status)} ${statusBg(p.status)}">${p.status}</span>
                            </div>`;
                    } else if (tablet) {
                        // Tablet: status + one time
                        statusHtml = `
                            <div class="mt-1 text-[10px] font-semibold text-center ${statusColor(p.status)}">${p.status}</div>
                            <div class="text-[10px] text-gray-400 text-center">${ci}</div>`;
                    } else {
                        // Desktop: full
                        statusHtml = `
                            <div class="mt-1 text-xs font-medium text-center ${statusColor(p.status)}">${p.status}</div>
                            <div class="text-xs text-gray-400 text-center">${ci}</div>
                            <div class="text-xs text-gray-400 text-center">${co}</div>`;
                    }
                } else {
                    statusHtml = `<div class="flex-1 flex items-center justify-center text-xs text-gray-200 select-none">—</div>`;
                }

                const presensiAttr = p ? JSON.stringify(p).replace(/'/g, '&#39;') : '';
                const dateNum = d.getDate();
                const todayBadge = mobile
                    ? `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#faa938] text-white text-[10px] font-bold">${dateNum}</span>`
                    : `<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#faa938] text-white text-sm">${dateNum}</span>`;
                const normalNum = mobile
                    ? `<span class="text-[10px] font-bold ${isSun || isLibur ? 'text-rose-500' : 'text-gray-700'}">${dateNum}</span>`
                    : `<span class="text-sm font-semibold ${isSun || isLibur ? 'text-rose-600' : 'text-gray-900'}">${dateNum}</span>`;

                return `
                    <button type="button" data-date="${key}" data-presensi='${presensiAttr}'
                        class="bg-white ${cPad} text-left ${cellH} ${p ? 'hover:bg-gray-50 active:bg-gray-100 cursor-pointer' : 'cursor-default'} transition flex flex-col min-w-0 overflow-hidden ${inMonth ? '' : 'opacity-30'} focus:outline-none focus-visible:ring-2 focus-visible:ring-[#faa938]/30">
                        <div class="${mobile ? 'text-center w-full' : 'text-left'}">
                            ${key === today ? todayBadge : normalNum}
                        </div>
                        ${statusHtml}
                    </button>
                `;
            }).join('')}
        </div>
    `;
}

function dispatchDetail(date, p) {
    window.dispatchEvent(new CustomEvent('presensi-detail', {
        detail: {
            date,
            status:   p.status,
            checkIn:  p.jam_mulai   ? p.jam_mulai.split(' ')[1].slice(0,5)   : '—',
            checkOut: p.jam_selesai ? p.jam_selesai.split(' ')[1].slice(0,5) : '—',
        }
    }));
}

// =====================
// INIT
// =====================
document.addEventListener('DOMContentLoaded', function () {
    fetchTim();
    fetchPegawai();
    render();

    // Re-render on resize for responsive calendar
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(render, 150);
    });

    document.getElementById('presensiGrid').addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-presensi]');
        if (!btn || !btn.dataset.presensi) return;
        const p    = JSON.parse(btn.dataset.presensi);
        const date = btn.dataset.date;
        if (p && date) dispatchDetail(date, p);
    });

    document.addEventListener('click', function (e) {
        const wrapperPegawai = document.getElementById('searchPegawai')?.closest('.relative');
        if (wrapperPegawai && !wrapperPegawai.contains(e.target))
            document.getElementById('dropdownPegawai').classList.add('hidden');
    });
});

// =====================
// MODAL HARI LIBUR
// =====================
(function () {
    const monthNames    = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const dayNames      = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    const existingLibur = @json($hariLibur->pluck('tanggal')->toArray());
    const hariLiburSet  = new Set(existingLibur);

    let viewYear  = now.getFullYear();
    let viewMonth = now.getMonth();
    let state     = { start: null, end: null };

    function toDate(str) { const [y,m,d] = str.split('-').map(Number); return new Date(y,m-1,d); }
    function toStr(date) { return `${date.getFullYear()}-${pad2(date.getMonth()+1)}-${pad2(date.getDate())}`; }

    function getEffectiveRange(hoverStr) {
        if (!state.start) return { s: null, e: null };
        const end = state.end || hoverStr;
        if (!end) return { s: state.start, e: null };
        const sd = toDate(state.start), ed = toDate(end);
        return sd <= ed ? { s: toStr(sd), e: toStr(ed) } : { s: toStr(ed), e: toStr(sd) };
    }

    function applyHighlight(hoverStr) {
        const { s, e } = getEffectiveRange(hoverStr);
        const grid = document.getElementById('hlGrid');
        if (!grid) return;
        grid.querySelectorAll('[data-date]').forEach(btn => {
            const d          = btn.dataset.date;
            const isExisting = existingLibur.includes(d);
            const isS        = d === s;
            const isE        = d === e;
            const inR        = s && e && toDate(d) >= toDate(s) && toDate(d) <= toDate(e);
            btn.className    = 'text-sm h-9 sm:h-8 transition cursor-pointer w-full rounded-lg ';
            if      (isS || isE)  btn.className += 'bg-[#faa938] text-white';
            else if (inR)         btn.className += 'bg-[#faa938]/20 text-[#faa938]';
            else if (isExisting)  btn.className += 'bg-red-100 text-red-500';
            else                  btn.className += 'text-gray-700 hover:text-[#faa938]';
        });
    }

    function renderHlGrid() {
        const grid     = document.getElementById('hlGrid');
        const navLabel = document.getElementById('hlNavLabel');
        if (!grid || !navLabel) return;

        navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        grid.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'grid grid-cols-7 mb-1';
        dayNames.forEach(d => {
            const span = document.createElement('span');
            span.className = 'text-center text-xs text-gray-400 py-1.5';
            span.textContent = d;
            header.appendChild(span);
        });
        grid.appendChild(header);

        const dayGrid       = document.createElement('div');
        dayGrid.className   = 'grid grid-cols-7 gap-y-0.5';
        const daysInMonth   = new Date(viewYear, viewMonth + 1, 0).getDate();
        const firstDay      = new Date(viewYear, viewMonth, 1).getDay();
        const daysInPrev    = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = firstDay - 1; i >= 0; i--) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = daysInPrev - i;
            btn.className = 'text-sm h-9 sm:h-8 text-gray-200 cursor-default w-full';
            dayGrid.appendChild(btn);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${viewYear}-${pad2(viewMonth+1)}-${pad2(d)}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = d;
            btn.dataset.date = dateStr;
            btn.className = 'text-sm h-9 sm:h-8 transition cursor-pointer w-full rounded-lg text-gray-700 hover:text-[#faa938] active:bg-gray-100';
            btn.addEventListener('click', () => {
                if (!state.start || state.end) {
                    state.start = dateStr;
                    state.end   = null;
                } else {
                    state.end = dateStr;
                    if (toDate(state.start) > toDate(state.end))
                        [state.start, state.end] = [state.end, state.start];
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
        const info         = document.getElementById('hlRangeInfo');
        const btnAdd       = document.getElementById('hlBtnTambah');
        const inputMulai   = document.getElementById('hlInputMulai');
        const inputSelesai = document.getElementById('hlInputSelesai');

        if (state.start && state.end) {
            inputMulai.value   = state.start;
            inputSelesai.value = state.end;
            info.textContent   = state.start === state.end ? state.start : `${state.start} s/d ${state.end}`;
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

    window.submitHariLibur = function () {
        if (!state.start || !state.end) { alert('Pilih tanggal terlebih dahulu.'); return; }
        document.getElementById('formHariLibur').submit();
    };

    document.getElementById('hlPrev')?.addEventListener('click', () => {
        viewMonth--;
    
        if (viewMonth < 0) {
            viewMonth = 11;
            viewYear--;
        }
    
        renderHlGrid();
        filterHariLiburListByMonth();
    });
    
    document.getElementById('hlNext')?.addEventListener('click', () => {
        viewMonth++;
    
        if (viewMonth > 11) {
            viewMonth = 0;
            viewYear++;
        }
    
        renderHlGrid();
        filterHariLiburListByMonth();
    });

    window.openModalHariLibur = function () {
        state = { start: null, end: null };
        updateRangeInfo();
        renderHlGrid();
        filterHariLiburListByMonth();
        document.getElementById('modalHariLibur').classList.remove('hidden');
    };
    window.closeModalHariLibur = function () {
        document.getElementById('modalHariLibur').classList.add('hidden');
    };
    
    function filterHariLiburListByMonth() {
        const items = document.querySelectorAll('.hl-list-item');
        const empty = document.getElementById('hlEmptyByMonth');
    
        if (!items.length) return;
    
        const activeMonth = `${viewYear}-${pad2(viewMonth + 1)}`;
        let visibleCount = 0;
    
        items.forEach(item => {
            const dates = (item.dataset.dates || '').split(',');
    
            const belongsToActiveMonth = dates.some(date => {
                return date.startsWith(activeMonth);
            });
    
            item.classList.toggle('hidden', !belongsToActiveMonth);
    
            if (belongsToActiveMonth) {
                visibleCount++;
            }
        });
    
        if (empty) {
            empty.classList.toggle('hidden', visibleCount > 0);
        }
    }
})();
</script>
@endpush
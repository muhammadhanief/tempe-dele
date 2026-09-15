@extends('layouts.app')

@section('title', 'Pengajuan Lembur')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Flash success --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">

        {{-- Filter Tanggal --}}
        <div class="relative w-full lg:w-auto" id="datePicker">
            <button type="button" id="dateBtn"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 transition-colors hover:border-[#faa938] lg:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-4 w-4 fill-current">
                    <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                </svg>

                <span id="dateLabel" class="leading-none">Semua Tanggal</span>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current opacity-50">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <input type="hidden" id="dateValue" value="">

            <div id="datePanel"
                class="absolute left-0 z-50 mt-2 hidden w-[calc(100vw-2rem)] max-w-72 rounded-xl border border-gray-200 bg-white p-3 shadow-lg sm:w-72">
                <div class="mb-3 flex items-center justify-between">
                    <button type="button" id="datePrev"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <span id="dateNavLabel"
                        class="cursor-pointer select-none text-sm font-medium text-gray-900 hover:text-[#faa938]">
                    </span>

                    <button type="button" id="dateNext"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                <div id="dateGrid"></div>

                <div class="mt-3 flex items-center justify-between">
                    <button type="button" id="btnToday"
                        class="text-sm font-medium text-gray-500 transition-all hover:text-[#faa938]">
                        Hari ini
                    </button>

                    <button type="button" id="btnDateClose"
                        class="rounded-full border border-gray-200 px-3 py-1 text-sm font-medium text-gray-600 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Tim --}}
        <div class="relative w-full lg:w-[30rem]">
            <input type="text" id="searchTim" placeholder="Cari nama tim..."
                onclick="toggleDropdownTim()" oninput="filterDropdownTim()" autocomplete="off"
                class="h-10 w-full rounded-full border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">

            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                    <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                </svg>
            </div>

            <div id="dropdownTim"
                class="absolute z-40 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listTim"></ul>
            </div>
        </div>

        {{-- Reset Filter --}}
        <button type="button" id="btnResetFilter"
            class="hidden h-10 rounded-full border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 transition-all hover:border-[#faa938] hover:text-[#faa938]">
            Reset
        </button>

        <div class="hidden lg:block lg:flex-1"></div>

        {{-- Tombol Ajukan --}}
        <a href="javascript:void(0)" id="btnAjukan"
            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-sm font-medium text-white transition-all hover:brightness-95 lg:w-10 lg:rounded-full lg:px-0"
            title="Ajukan Lembur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <span class="lg:hidden">Ajukan Lembur</span>
        </a>

    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[1080px] table-auto rounded-xl">
            <thead>
                <tr class="bg-gray-100">
                    <th class="w-28 rounded-tl-xl px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Tanggal</th>
                    <th class="w-28 px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Jam Diajukan</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Uraian Kegiatan</th>
                    <th class="w-32 px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Ketua Tim</th>
                    <th class="w-36 px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Nama Tim</th>
                    <th class="w-24 px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Status</th>
                    <th class="w-32 px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Catatan</th>
                    <th class="w-28 rounded-tr-xl px-3 py-3 text-center text-xs font-semibold capitalize text-gray-900">Dokumentasi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300" id="tabelLembur">
                @forelse($transaksi as $t)
                    <tr class="bg-white transition-all duration-300 hover:bg-gray-50"
                        data-tanggal="{{ $t->date }}"
                        data-tim="{{ $t->tim_kode_tim }}">

                        <td class="px-3 py-3 text-xs text-gray-900 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($t->date)->translatedFormat('d F Y') }}
                        </td>

                        <td class="px-3 py-3 text-center text-xs text-gray-900 whitespace-nowrap">
                            @if($t->jam_mulai && $t->jam_selesai)
                                {{ substr($t->jam_mulai, 0, 5) }} - {{ substr($t->jam_selesai, 0, 5) }}
                            @elseif($t->jam_mulai)
                                {{ substr($t->jam_mulai, 0, 5) }} - <span class="italic text-gray-400">menunggu</span>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[280px] whitespace-normal break-words">
                                {{ $t->uraian ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[140px] whitespace-normal break-words">
                                {{ $t->nama_ketua ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[160px] whitespace-normal break-words">
                                {{ $t->nama_tim ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            @if($t->status === 'pending')
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Diproses</span>
                            @elseif($t->status === 'approved')
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Disetujui</span>
                            @elseif($t->status === 'rejected')
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-600">Ditolak</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="max-w-[160px] whitespace-normal break-words">
                                {{ $t->note ?? '-' }}
                            </div>
                        </td>

                        <td class="px-3 py-3 text-center text-xs">
                            @if($t->status === 'approved')
                                @if($t->file_dokumentasi)
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-blue-500 underline hover:text-blue-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            Lihat
                                        </a>

                                        <form action="{{ route('pegawai.lembur.destroyDoc', $t->id_transaksi) }}" method="POST"
                                            onsubmit="return confirm('Hapus dokumentasi ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="mt-1.5 text-red-400 hover:text-red-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <button type="button"
                                        onclick="openModalDok({{ $t->id_transaksi }})"
                                        class="text-xs text-[#faa938] underline hover:text-[#fd9a10]">
                                        + Tambah
                                    </button>
                                @endif
                            @else
                                <span class="text-gray-300">-</span>
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
    @if($transaksi->hasPages())
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex items-center gap-2 rounded bg-white p-1">

                {{-- Previous --}}
                @if($transaksi->onFirstPage())
                    <span class="cursor-not-allowed rounded border p-1 text-gray-300">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $transaksi->previousPageUrl() }}">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="whitespace-nowrap text-sm text-gray-500">
                    Page {{ $transaksi->currentPage() }} of {{ $transaksi->lastPage() }}
                </p>

                {{-- Next --}}
                @if($transaksi->hasMorePages())
                    <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
                        href="{{ $transaksi->nextPageUrl() }}">
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

{{-- Modal Dokumentasi --}}
<div id="modalDok" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModalDok()"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-xl">

            <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold text-gray-900">Tambah Dokumentasi</h2>
                <button type="button" onclick="closeModalDok()"
                    class="text-xl leading-none text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>

            <form id="formDok" method="POST" class="space-y-4 px-5 py-5 sm:px-6">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Link Google Drive</label>
                    <input type="url" name="file_path" required
                        placeholder="https://drive.google.com/..."
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModalDok()"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50 sm:w-auto">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black hover:bg-[#fd9a10] hover:text-white sm:w-auto">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal Ajukan Lembur --}}
<div id="modalAjukan" class="fixed inset-0 z-50 hidden">
    <div id="modalOverlay" class="fixed inset-0 bg-black/40"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center px-4 py-6 sm:py-8">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl">

                <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-gray-900 sm:text-lg">Ajukan Lembur</h2>
                    <button type="button" id="btnCloseModal"
                        class="text-xl leading-none text-gray-500 hover:text-gray-700">
                        &times;
                    </button>
                </div>

                <form id="formAjukan" action="{{ route('ketua-tim.lembur.store') }}" method="POST"
                    class="space-y-5 px-5 py-5 sm:px-6">
                    @csrf

                    <input type="hidden" name="kode_tim" id="kode_tim">

                    <div>
                        <label for="approver_id" class="mb-2 block text-sm font-medium text-gray-700">Ketua Tim</label>
                        <select id="approver_id" name="approver_id" required
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            <option value="">Pilih Ketua Tim</option>
                            @forelse($ketuaTim as $ketua)
                                <option value="{{ $ketua['nip'] }}" data-kode="{{ $ketua['kode_tim'] }}">
                                    {{ $ketua['nama'] }} ({{ $ketua['tim'] }})
                                </option>
                            @empty
                                <option disabled>Kamu tidak terdaftar di tim manapun</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <p id="infoHari" class="mt-1 hidden text-xs text-gray-400"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jam_mulai" class="mb-2 block text-sm font-medium text-gray-700">Jam Mulai</label>
                            <input type="time" id="jam_mulai" name="jam_mulai" required
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            <p id="infoJamMulai" class="mt-1 hidden text-xs text-black">Default hari kerja: 16:01</p>
                        </div>

                        <div id="wrapperJamSelesai">
                            <label for="jam_selesai" class="mb-2 block text-sm font-medium text-gray-700">Jam Selesai</label>
                            <input type="time" id="jam_selesai" name="jam_selesai" required
                                class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        </div>
                    </div>

                    <p id="previewDurasi" class="hidden text-xs text-gray-500">
                        Estimasi: <span id="durasiLabel" class="font-semibold text-gray-800"></span>
                    </p>

                    <div>
                        <label for="uraian" class="mb-2 block text-sm font-medium text-gray-700">Uraian Kegiatan</label>
                        <textarea id="uraian" name="uraian" rows="3" required
                            placeholder="Contoh: Penyusunan laporan bulanan..."
                            class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Tanda Tangan</label>

                        <div class="overflow-hidden rounded-md border border-gray-300 bg-gray-50">
                            <canvas id="signatureCanvas" class="h-40 w-full touch-none"></canvas>
                        </div>

                        <div class="mt-2 flex justify-end">
                            <button type="button" id="btnClearSignature"
                                class="text-xs text-gray-500 underline hover:text-red-500">
                                Hapus Tanda Tangan
                            </button>
                        </div>

                        <input type="hidden" name="signature" id="signatureData">

                        <p id="signatureError" class="mt-1 hidden text-xs text-red-500">
                            Tanda tangan wajib diisi.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" id="btnCancel"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 sm:w-auto">
                            Batal
                        </button>

                        <button type="submit"
                            class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black hover:bg-[#fd9a10] hover:text-white sm:w-auto">
                            Kirim
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const now = new Date();

    let selectedDate = null;
    let selectedTim = null;
    let cachedTim = [];

    function pad2(value) {
        return String(value).padStart(2, '0');
    }

    function updateResetBtn() {
        const btn = document.getElementById('btnResetFilter');
        if (!btn) return;

        if (selectedDate || selectedTim) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    function filterTabel() {
        document.querySelectorAll('#tabelLembur tr[data-tanggal]').forEach(row => {
            const cocokTanggal = !selectedDate || row.dataset.tanggal === selectedDate;
            const cocokTim = !selectedTim || row.dataset.tim === selectedTim;

            row.style.display = cocokTanggal && cocokTim ? '' : 'none';
        });
    }

    function makeBtn(text, className, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = text;
        button.className = className;
        button.addEventListener('click', event => {
            event.stopPropagation();
            onClick();
        });
        return button;
    }

    // =====================
    // FILTER TIM
    // =====================
    function renderDropdownTim(filter = '') {
        const list = document.getElementById('listTim');
        if (!list) return;

        list.innerHTML = '';

        const liSemua = document.createElement('li');
        liSemua.className = 'cursor-pointer px-4 py-2 text-sm text-gray-400 hover:bg-gray-50';
        liSemua.textContent = 'Semua tim';
        liSemua.onclick = () => pilihTim(null);
        list.appendChild(liSemua);

        const keyword = filter.toLowerCase();

        cachedTim
            .filter(tim => String(tim.nama_tim ?? '').toLowerCase().includes(keyword))
            .forEach(tim => {
                const li = document.createElement('li');
                li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
                li.textContent = tim.nama_tim ?? '-';
                li.onclick = () => pilihTim(tim);
                list.appendChild(li);
            });
    }

    window.toggleDropdownTim = function () {
        const dropdown = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');

        if (!dropdown || !search) return;

        dropdown.classList.toggle('hidden');
        renderDropdownTim(search.value);
    };

    window.filterDropdownTim = function () {
        const dropdown = document.getElementById('dropdownTim');
        const search = document.getElementById('searchTim');

        if (!dropdown || !search) return;

        renderDropdownTim(search.value);
        dropdown.classList.remove('hidden');
    };

    function pilihTim(tim) {
        selectedTim = tim ? tim.kode_tim : null;

        const input = document.getElementById('searchTim');
        const dropdown = document.getElementById('dropdownTim');

        if (input) input.value = tim ? tim.nama_tim : '';
        if (dropdown) dropdown.classList.add('hidden');

        filterTabel();
        updateResetBtn();
    }

    fetch('/lembur/tim')
        .then(response => response.json())
        .then(data => {
            cachedTim = Array.isArray(data) ? data : [];
            renderDropdownTim('');
        })
        .catch(() => {
            cachedTim = [];
        });

    // =====================
    // DATE PICKER
    // =====================
    (function initDatePicker() {
        const picker = document.getElementById('datePicker');
        const btn = document.getElementById('dateBtn');
        const panel = document.getElementById('datePanel');
        const grid = document.getElementById('dateGrid');
        const navLabel = document.getElementById('dateNavLabel');
        const dateLabel = document.getElementById('dateLabel');
        const dateValue = document.getElementById('dateValue');
        const btnPrev = document.getElementById('datePrev');
        const btnNext = document.getElementById('dateNext');
        const btnToday = document.getElementById('btnToday');
        const btnClose = document.getElementById('btnDateClose');

        if (!picker || !btn || !panel || !grid || !navLabel) return;

        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const monthShort = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const dayNames = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

        let view = 'day';
        let viewYear = now.getFullYear();
        let viewMonth = now.getMonth();
        let selYear = null;
        let selMonth = null;
        let selDay = null;

        function setDate(year, month, day) {
            selYear = year;
            selMonth = month;
            selDay = day;

            selectedDate = `${year}-${pad2(month + 1)}-${pad2(day)}`;

            dateLabel.textContent = `${day} ${monthShort[month]} ${year}`;
            dateValue.value = selectedDate;

            filterTabel();
            updateResetBtn();
        }

        function openPanel() {
            if (selYear !== null && selMonth !== null) {
                viewYear = selYear;
                viewMonth = selMonth;
            } else {
                viewYear = now.getFullYear();
                viewMonth = now.getMonth();
            }

            renderDay();
            panel.classList.remove('hidden');
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        function renderDay() {
            view = 'day';
            navLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;
            navLabel.className = 'cursor-pointer select-none text-sm font-medium text-gray-900 hover:text-[#faa938]';
            grid.innerHTML = '';

            const header = document.createElement('div');
            header.className = 'mb-1 grid grid-cols-7';

            dayNames.forEach(day => {
                const span = document.createElement('span');
                span.className = 'py-1 text-center text-xs text-gray-400';
                span.textContent = day;
                header.appendChild(span);
            });

            grid.appendChild(header);

            const dayGrid = document.createElement('div');
            dayGrid.className = 'grid grid-cols-7 gap-y-1';

            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const firstDay = new Date(viewYear, viewMonth, 1).getDay();
            const daysInPrev = new Date(viewYear, viewMonth, 0).getDate();
            const base = 'rounded-lg border py-1 text-sm transition-all ';

            for (let i = firstDay - 1; i >= 0; i--) {
                const day = daysInPrev - i;

                dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', () => {
                    let month = viewMonth - 1;
                    let year = viewYear;

                    if (month < 0) {
                        month = 11;
                        year--;
                    }

                    setDate(year, month, day);
                    closePanel();
                }));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const isSelected = day === selDay && viewMonth === selMonth && viewYear === selYear;
                const isToday = day === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isToday
                        ? 'border-transparent bg-[#faa938]/20 text-[#faa938]'
                        : 'border-transparent text-gray-700 hover:border-[#faa938] hover:text-[#faa938]';

                dayGrid.appendChild(makeBtn(day, base + className, () => {
                    setDate(viewYear, viewMonth, day);
                    closePanel();
                }));
            }

            const total = firstDay + daysInMonth;
            const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);

            for (let day = 1; day <= remaining; day++) {
                dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', () => {
                    let month = viewMonth + 1;
                    let year = viewYear;

                    if (month > 11) {
                        month = 0;
                        year++;
                    }

                    setDate(year, month, day);
                    closePanel();
                }));
            }

            grid.appendChild(dayGrid);
        }

        function renderMonth() {
            view = 'month';
            navLabel.textContent = String(viewYear);
            navLabel.className = 'cursor-pointer select-none text-sm font-medium text-gray-900 hover:text-[#faa938]';
            grid.innerHTML = '';

            const monthGrid = document.createElement('div');
            monthGrid.className = 'grid grid-cols-3 gap-2';

            monthNames.forEach((name, month) => {
                const isSelected = month === selMonth && viewYear === selYear;
                const isNow = month === now.getMonth() && viewYear === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isNow
                        ? 'border-[#faa938] bg-white text-[#faa938]'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';

                monthGrid.appendChild(makeBtn(name.slice(0, 3), 'rounded-lg border px-2 py-2 text-sm transition-all ' + className, () => {
                    viewMonth = month;
                    renderDay();
                }));
            });

            grid.appendChild(monthGrid);
        }

        function renderYear() {
            view = 'year';

            const startYear = Math.floor(viewYear / 12) * 12;

            navLabel.textContent = `${startYear} - ${startYear + 11}`;
            navLabel.className = 'cursor-default select-none text-sm font-medium text-gray-400';

            grid.innerHTML = '';

            const yearGrid = document.createElement('div');
            yearGrid.className = 'grid grid-cols-3 gap-2';

            for (let year = startYear; year < startYear + 12; year++) {
                const isSelected = year === selYear;
                const isNow = year === now.getFullYear();

                const className = isSelected
                    ? 'border-[#faa938] bg-[#faa938] text-white'
                    : isNow
                        ? 'border-[#faa938] bg-white text-[#faa938]'
                        : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';

                yearGrid.appendChild(makeBtn(year, 'rounded-lg border px-2 py-2 text-sm transition-all ' + className, () => {
                    viewYear = year;
                    renderMonth();
                }));
            }

            grid.appendChild(yearGrid);
        }

        btn.addEventListener('click', event => {
            event.stopPropagation();

            if (panel.classList.contains('hidden')) {
                openPanel();
            } else {
                closePanel();
            }
        });

        navLabel.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                renderMonth();
            } else if (view === 'month') {
                renderYear();
            }
        });

        btnPrev?.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                viewMonth--;

                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear--;
                }

                renderDay();
            } else if (view === 'month') {
                viewYear--;
                renderMonth();
            } else if (view === 'year') {
                viewYear -= 12;
                renderYear();
            }
        });

        btnNext?.addEventListener('click', event => {
            event.stopPropagation();

            if (view === 'day') {
                viewMonth++;

                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear++;
                }

                renderDay();
            } else if (view === 'month') {
                viewYear++;
                renderMonth();
            } else if (view === 'year') {
                viewYear += 12;
                renderYear();
            }
        });

        btnToday?.addEventListener('click', event => {
            event.stopPropagation();

            viewYear = now.getFullYear();
            viewMonth = now.getMonth();

            setDate(now.getFullYear(), now.getMonth(), now.getDate());
            closePanel();
        });

        btnClose?.addEventListener('click', event => {
            event.stopPropagation();
            closePanel();
        });

        document.addEventListener('click', event => {
            if (!picker.contains(event.target)) {
                closePanel();
            }

            const dropdownTim = document.getElementById('dropdownTim');
            const searchTim = document.getElementById('searchTim');

            if (dropdownTim && searchTim && !dropdownTim.contains(event.target) && !searchTim.contains(event.target)) {
                dropdownTim.classList.add('hidden');
            }
        });
    })();

    // =====================
    // RESET FILTER
    // =====================
    document.getElementById('btnResetFilter')?.addEventListener('click', () => {
        selectedDate = null;
        selectedTim = null;

        document.getElementById('dateLabel').textContent = 'Semua Tanggal';
        document.getElementById('dateValue').value = '';
        document.getElementById('searchTim').value = '';

        filterTabel();
        updateResetBtn();
    });

    // =====================
    // MODAL DOKUMENTASI
    // =====================
    window.openModalDok = function (idTransaksi) {
        const base = "{{ url('pegawai/lembur') }}";
        const modal = document.getElementById('modalDok');
        const form = document.getElementById('formDok');

        if (form) {
            form.action = `${base}/${idTransaksi}/dokumentasi`;
        }

        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalDok = function () {
        document.getElementById('modalDok')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // =====================
    // MODAL AJUKAN
    // =====================
    const modalAjukan = document.getElementById('modalAjukan');
    const btnAjukan = document.getElementById('btnAjukan');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancel');
    const overlay = document.getElementById('modalOverlay');
    const canvas = document.getElementById('signatureCanvas');

    let signaturePad = null;

    if (canvas && window.SignaturePad) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(0, 0, 0, 0)',
            penColor: 'rgb(17, 24, 39)',
        });
    }

    function resizeCanvas() {
        if (!canvas || !signaturePad) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();

        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;

        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);

        signaturePad.clear();
    }

    function resetFormAjukan() {
        document.getElementById('kode_tim').value = '';
        document.getElementById('approver_id').value = '';
        document.getElementById('tanggal').value = '';
        document.getElementById('jam_mulai').value = '';
        document.getElementById('jam_selesai').value = '';
        document.getElementById('uraian').value = '';

        document.getElementById('infoHari').classList.add('hidden');
        document.getElementById('infoJamMulai').classList.add('hidden');
        document.getElementById('previewDurasi').classList.add('hidden');
        document.getElementById('signatureError').classList.add('hidden');

        if (signaturePad) {
            signaturePad.clear();
        }
    }

    function openModalAjukan() {
        modalAjukan?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        resetFormAjukan();

        setTimeout(resizeCanvas, 80);
    }

    function closeModalAjukan() {
        modalAjukan?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    btnAjukan?.addEventListener('click', openModalAjukan);
    btnCloseModal?.addEventListener('click', closeModalAjukan);
    btnCancel?.addEventListener('click', closeModalAjukan);
    overlay?.addEventListener('click', closeModalAjukan);

    window.addEventListener('resize', resizeCanvas);

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            openModalAjukan();
        });
    @endif

    document.getElementById('btnClearSignature')?.addEventListener('click', () => {
        signaturePad?.clear();
    });

    document.getElementById('approver_id')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kode_tim').value = selected?.dataset.kode || '';
    });

    const hariLiburDB = @json($hariLibur);

    document.getElementById('tanggal')?.addEventListener('change', function () {
        const date = new Date(this.value);
        const dayOfWeek = date.getUTCDay();

        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const isFriday = dayOfWeek === 5;
        const isLibur = hariLiburDB.includes(this.value);

        const infoHari = document.getElementById('infoHari');
        const infoMulai = document.getElementById('infoJamMulai');
        const jamMulai = document.getElementById('jam_mulai');
        const jamSelesai = document.getElementById('jam_selesai');

        infoHari.classList.remove('hidden');

        if (isWeekend || isLibur) {
            infoHari.textContent = '📅 Hari libur — jam mulai bebas';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.classList.add('hidden');

            jamMulai.value = '';
            jamMulai.removeAttribute('min');
        } else if (isFriday) {
            infoHari.textContent = '📅 Hari Jumat — jam mulai default 16:31';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.textContent = 'Default hari Jumat: 16:31';
            infoMulai.classList.remove('hidden');

            jamMulai.value = '16:31';
            jamMulai.setAttribute('min', '16:31');
        } else {
            infoHari.textContent = '📅 Hari kerja — jam mulai default 16:01';
            infoHari.className = 'mt-1 text-xs text-black';

            infoMulai.textContent = 'Default hari kerja: 16:01';
            infoMulai.classList.remove('hidden');

            jamMulai.value = '16:01';
            jamMulai.setAttribute('min', '16:01');
        }

        jamSelesai.value = '';
        hitungDurasi();
    });

    function hitungDurasi() {
        const mulai = document.getElementById('jam_mulai').value;
        const selesai = document.getElementById('jam_selesai').value;
        const preview = document.getElementById('previewDurasi');
        const label = document.getElementById('durasiLabel');

        if (!mulai || !selesai) {
            preview.classList.add('hidden');
            return;
        }

        const [jm, mm] = mulai.split(':').map(Number);
        const [js, ms] = selesai.split(':').map(Number);

        const totalMenit = (js * 60 + ms) - (jm * 60 + mm);

        if (totalMenit <= 0) {
            preview.classList.add('hidden');
            return;
        }

        const jam = Math.floor(totalMenit / 60);
        const menit = totalMenit % 60;

        let info = `${jam} jam ${menit > 0 ? menit + ' menit' : ''} (dihitung ${jam} jam)`;
        let warna = 'text-gray-500';

        if (jam < 2) {
            info += ' — ⚠️ Pengajuan jam lembur minimal 2 jam';
            warna = 'text-amber-500';
        } else if (jam > 6) {
            info += ' — ⚠️ Maksimal lembur 6 jam';
            warna = 'text-amber-500';
        }

        label.textContent = info;
        preview.className = `text-xs ${warna}`;
        preview.classList.remove('hidden');
    }

    document.getElementById('jam_mulai')?.addEventListener('change', function () {
        const jamSelesai = document.getElementById('jam_selesai');

        if (jamSelesai.value && jamSelesai.value <= this.value) {
            alert('Jam selesai harus setelah jam mulai');
            jamSelesai.value = '';
        }

        if (this.value) {
            jamSelesai.setAttribute('min', this.value);
        }

        hitungDurasi();
    });

    document.getElementById('jam_selesai')?.addEventListener('change', function () {
        const jamMulai = document.getElementById('jam_mulai').value;

        if (jamMulai && this.value <= jamMulai) {
            alert('Jam selesai harus setelah jam mulai');
            this.value = '';
            return;
        }

        hitungDurasi();
    });

    document.getElementById('formAjukan')?.addEventListener('submit', function (event) {
        if (!signaturePad || signaturePad.isEmpty()) {
            event.preventDefault();
            document.getElementById('signatureError').classList.remove('hidden');
            return;
        }

        document.getElementById('signatureError').classList.add('hidden');
        document.getElementById('signatureData').value = signaturePad.toDataURL('image/png');
    });

})();
</script>
@endpush

@endsection

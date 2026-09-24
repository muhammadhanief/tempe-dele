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

    {{-- Page Header --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800">
                Pengajuan Lembur Pribadi
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola dan pantau riwayat pengajuan kegiatan lembur mandiri Anda.
            </p>
        </div>

        {{-- Tombol Ajukan Lembur --}}
        <button type="button" id="btnAjukan"
            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow-sm transition-all shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            <span>Ajukan Lembur</span>
        </button>
    </div>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">

        {{-- Filter Periode --}}
        <div class="relative" id="datePicker">
            <button type="button" id="dateBtn"
                class="inline-flex h-10 items-center justify-between gap-2.5 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 shadow-2xs hover:border-[#faa938] hover:text-[#faa938] transition-all">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3.5 w-3.5 fill-current text-[#faa938]">
                        <path d="M208 64c17.7 0 32 14.3 32 32v32h160V96c0-17.7 14.3-32 32-32s32 14.3 32 32v32h32c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H128c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64h32V96c0-17.7 14.3-32 32-32zm336 160H96v288c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V224z"/>
                    </svg>
                    <span id="dateLabel" class="truncate leading-none">Semua Tanggal</span>
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current opacity-40 shrink-0">
                    <path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0L160 301.5l119.1-119.1c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-136 136c-9.4 9.4-24.6 9.4-34 0z"/>
                </svg>
            </button>

            <input type="hidden" id="dateValue" value="">

            {{-- Date Panel --}}
            <div id="datePanel"
                class="absolute left-0 z-50 mt-2 hidden w-72 rounded-2xl border border-gray-200 bg-white p-3.5 shadow-xl">
                <div class="mb-3 flex items-center justify-between">
                    <button type="button" id="datePrev"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M41.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 256 246.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
                        </svg>
                    </button>

                    <span id="dateNavLabel"
                        class="cursor-pointer select-none text-xs font-bold text-gray-900 hover:text-[#faa938]">
                    </span>

                    <button type="button" id="dateNext"
                        class="rounded-lg border border-gray-200 p-2 transition-all hover:border-[#faa938] hover:text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="h-3 w-3 fill-current">
                            <path d="M278.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                        </svg>
                    </button>
                </div>

                <div id="dateGrid"></div>

                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2.5">
                    <button type="button" id="btnToday"
                        class="text-xs font-semibold text-gray-600 hover:text-[#faa938] transition-colors">
                        Hari ini
                    </button>

                    <button type="button" id="btnDateClose"
                        class="rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 hover:border-[#faa938] hover:text-[#faa938] transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Bulan --}}
        <select id="filterBulan" onchange="gantiFilter('bulan', this.value)"
            class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
            <option value="">Semua Bulan</option>
            @php $tahunBulan = now()->format('Y'); \Carbon\Carbon::setLocale('id'); @endphp
            @for($m = 1; $m <= 12; $m++)
                @php
                    $valBulan  = \Carbon\Carbon::create($tahunBulan, $m, 1)->format('Y-m');
                    $namaBulan = \Carbon\Carbon::create($tahunBulan, $m, 1)->translatedFormat('F');
                @endphp
                <option value="{{ $valBulan }}" @selected(($bulan ?? '') === $valBulan)>{{ ucfirst($namaBulan) }} {{ $tahunBulan }}</option>
            @endfor
        </select>

        {{-- Jumlah per Halaman --}}
        <select id="perHalaman" onchange="gantiFilter('perPage', this.value)"
            class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 shadow-2xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
            <option value="10"  @selected(($perPage ?? 10) == 10)>10 / hal</option>
            <option value="25"  @selected(($perPage ?? 10) == 25)>25 / hal</option>
            <option value="50"  @selected(($perPage ?? 10) == 50)>50 / hal</option>
            <option value="100" @selected(($perPage ?? 10) == 100)>100 / hal</option>
        </select>

        {{-- Reset Filter --}}
        <button type="button" id="btnResetFilter"
            class="hidden h-10 rounded-xl border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-500 shadow-2xs hover:border-red-300 hover:text-red-500 transition-colors">
            Reset
        </button>

    </div>

    {{-- Card Tabel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50/90 border-b border-gray-200">
                    <tr>
                        <th class="w-32 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tanggal</th>
                        <th class="w-32 px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Jam Diajukan</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Uraian Kegiatan</th>
                        <th class="w-40 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Ketua Tim</th>
                        <th class="w-44 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Nama Tim</th>
                        <th class="w-28 px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                        <th class="w-40 px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Catatan</th>
                        <th class="w-32 px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Dokumentasi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white" id="tabelLembur">
                    @forelse($transaksi as $t)
                        <tr class="transition-colors hover:bg-slate-50/80"
                            data-tanggal="{{ $t->date }}">

                            <td class="whitespace-nowrap px-4 py-3.5 text-xs font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($t->date)->translatedFormat('d F Y') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-center text-xs font-mono text-gray-700">
                                @if($t->jam_mulai && $t->jam_selesai)
                                    {{ substr($t->jam_mulai, 0, 5) }} - {{ substr($t->jam_selesai, 0, 5) }}
                                @elseif($t->jam_mulai)
                                    {{ substr($t->jam_mulai, 0, 5) }} - <span class="italic text-gray-400">menunggu</span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-xs text-gray-800">
                                <div class="max-w-[280px] whitespace-normal break-words leading-relaxed">
                                    {{ $t->uraian ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-xs text-gray-700">
                                <div class="max-w-[150px] whitespace-normal break-words font-medium">
                                    {{ $t->nama_ketua ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-xs text-gray-600">
                                <div class="max-w-[170px] whitespace-normal break-words">
                                    {{ $t->nama_tim ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-center text-xs">
                                @if($t->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Diproses
                                    </span>
                                @elseif($t->status === 'menunggu_kabag')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Menunggu Kabag
                                    </span>
                                @elseif($t->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($t->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-xs text-gray-600">
                                <div class="max-w-[160px] whitespace-normal break-words italic">
                                    {{ $t->note ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-center text-xs">
                                @if($t->status === 'approved')
                                    @if($t->file_dokumentasi)
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ $t->file_dokumentasi }}" target="_blank"
                                                class="inline-flex items-center gap-1 font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Lihat
                                            </a>

                                            <form action="{{ route('pegawai.lembur.destroyDoc', $t->id_transaksi) }}" method="POST"
                                                onsubmit="return confirm('Hapus dokumentasi ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Hapus dokumentasi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <button type="button"
                                            onclick="openModalDok(this)"
                                            data-action="{{ route('pegawai.lembur.storeDoc', $t->id_transaksi) }}"
                                            class="inline-flex items-center gap-1 font-medium text-[#faa938] hover:text-[#fd9a10] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            Dokumentasi
                                        </button>
                                    @endif
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-[#faa938] mb-3 border border-amber-100/80 shadow-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900 mb-1">Belum Ada Pengajuan Lembur</h3>
                                    <p class="text-xs text-gray-500 mb-4 text-center leading-relaxed">
                                        Anda belum memiliki riwayat pengajuan kegiatan lembur mandiri pada periode ini.
                                    </p>
                                    <button type="button" onclick="openModal()"
                                        class="inline-flex items-center gap-2 rounded-xl bg-[#faa938] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                        Ajukan Lembur Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($transaksi->hasPages())
        <div class="mt-6 flex justify-center">
            <nav class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xs">

                @if($transaksi->onFirstPage())
                    <span class="cursor-not-allowed rounded-lg border border-transparent p-1.5 text-gray-300">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                @else
                    <a class="rounded-lg border border-transparent p-1.5 text-gray-700 hover:border-gray-200 hover:bg-gray-50 transition-colors"
                        href="{{ $transaksi->previousPageUrl() }}">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </a>
                @endif

                <p class="whitespace-nowrap px-2 text-xs font-medium text-gray-600">
                    Halaman {{ $transaksi->currentPage() }} dari {{ $transaksi->lastPage() }}
                </p>

                @if($transaksi->hasMorePages())
                    <a class="rounded-lg border border-transparent p-1.5 text-gray-700 hover:border-gray-200 hover:bg-gray-50 transition-colors"
                        href="{{ $transaksi->nextPageUrl() }}">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-transparent p-1.5 text-gray-300">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
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
    <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" onclick="closeModalDok()"></div>

    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl border border-gray-100">

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 bg-gray-50/70">
                <h2 class="text-base font-bold text-gray-900">Tambah Dokumentasi</h2>

                <button type="button" onclick="closeModalDok()"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="formDok" method="POST" class="space-y-4 px-6 py-5">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl bg-red-50 p-3 text-xs text-red-600 border border-red-100">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $errorDok)
                                <li>{{ $errorDok }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Link Google Drive</label>
                    <input type="url" name="file_path" required
                        placeholder="https://drive.google.com/..."
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModalDok()"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        Batal
                    </button>

                    <button type="submit"
                        class="rounded-xl bg-[#faa938] px-5 py-2 text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal Ajukan Lembur --}}
<div id="modalAjukan" class="fixed inset-0 z-50 hidden">
    <div id="modalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"></div>

    <div class="fixed inset-0 z-10 flex items-center justify-center p-3 sm:p-4 md:p-6 overflow-hidden">
        <div class="relative flex flex-col w-full max-w-2xl max-h-[90vh] rounded-2xl bg-white shadow-2xl overflow-hidden border border-gray-100">

            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 bg-gray-50/70 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-[#faa938]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 leading-tight">Ajukan Lembur</h2>
                        <p class="text-xs text-gray-500">Isi formulir untuk mengajukan kegiatan lembur mandiri</p>
                    </div>
                </div>

                <button type="button" id="btnCloseModal"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form id="formAjukan" action="{{ route('ketua-tim.lembur.store') }}" method="POST"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf

                <div class="flex-1 overflow-y-auto px-6 pr-5 py-5 space-y-4">
                    <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                    <input type="hidden" name="kode_tim" id="kode_tim">

                    <div>
                        <label for="approver_id" class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Ketua Tim / Penyetuju</label>
                        <select id="approver_id" name="approver_id" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all cursor-pointer">
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
                        <label for="tanggal" class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal Lembur</label>
                        <input type="date" id="tanggal" name="tanggal" required
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm text-gray-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all">
                        <p id="infoHari" class="mt-1 hidden text-xs text-gray-500"></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jam_mulai" class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Jam Mulai</label>
                            <input type="time" id="jam_mulai" name="jam_mulai" required
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm text-gray-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all">
                            <p id="infoJamMulai" class="mt-1 hidden text-xs text-amber-600 font-medium">
                                Default hari kerja: 16:01
                            </p>
                        </div>

                        <div>
                            <label for="jam_selesai" class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Jam Selesai</label>
                            <input type="time" id="jam_selesai" name="jam_selesai" required
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm text-gray-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all">
                        </div>
                    </div>

                    <div id="previewDurasi" class="hidden rounded-lg bg-orange-50/70 border border-orange-100 px-3 py-2 text-xs text-orange-800">
                        ⏱️ Estimasi Durasi: <span id="durasiLabel" class="font-bold text-orange-950"></span>
                    </div>

                    <div>
                        <label for="uraian" class="mb-1.5 block text-xs font-semibold text-gray-700 uppercase tracking-wider">Uraian Kegiatan</label>
                        <textarea id="uraian" name="uraian" rows="3" required
                            placeholder="Contoh: Penyusunan laporan bulanan..."
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm text-gray-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all"></textarea>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanda Tangan</label>
                            <button type="button" id="btnClearSignature"
                                class="text-xs text-gray-500 hover:text-red-500 transition-colors underline">
                                Hapus Tanda Tangan
                            </button>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-300 bg-gray-50 hover:border-gray-400 transition-colors">
                            <canvas id="signatureCanvas" class="h-36 w-full touch-none"></canvas>
                        </div>

                        <input type="hidden" name="signature" id="signatureData">
                        <p id="signatureError" class="mt-1 hidden text-xs text-red-500 font-medium">
                            Tanda tangan wajib diisi.
                        </p>
                    </div>
                </div>

                {{-- Footer (Fixed) --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-3.5 bg-gray-50/70 shrink-0">
                    <button type="button" id="btnCancel"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-[#faa938] px-5 py-2 text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const now = new Date();

    function pad2(value) {
        return String(value).padStart(2, '0');
    }

    window.gantiFilter = function (key, value) {
        const url = new URL(window.location.href);
        const params = url.searchParams;

        if (value === '') {
            params.delete(key);
        } else {
            params.set(key, value);
        }

        params.delete('page');

        window.location.href = url.pathname + '?' + params.toString();
    };

    function makeBtn(text, className, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = text;
        button.className = className;
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            onClick();
        });
        return button;
    }

    // =====================
    // SIGNATURE
    // =====================
    const canvas = document.getElementById('signatureCanvas');
    let signaturePad = null;

    if (canvas && window.SignaturePad) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(0, 0, 0, 0)',
            penColor: 'rgb(17, 24, 39)'
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

    window.addEventListener('resize', resizeCanvas);

    document.getElementById('btnClearSignature')?.addEventListener('click', function () {
        signaturePad?.clear();
    });

    document.getElementById('formAjukan')?.addEventListener('submit', function (event) {
        if (!signaturePad || signaturePad.isEmpty()) {
            event.preventDefault();
            document.getElementById('signatureError')?.classList.remove('hidden');
            return;
        }

        document.getElementById('signatureError')?.classList.add('hidden');
        document.getElementById('signatureData').value = signaturePad.toDataURL('image/png');
    });

    // =====================
    // MODAL AJUKAN
    // =====================
    const modal = document.getElementById('modalAjukan');
    const btnAjukan = document.getElementById('btnAjukan');
    const btnClose = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancel');
    const overlay = document.getElementById('modalOverlay');

    window.openModal = function() {
        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(resizeCanvas, 80);
    };

    window.closeModal = function() {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    btnAjukan?.addEventListener('click', window.openModal);
    btnClose?.addEventListener('click', window.closeModal);
    btnCancel?.addEventListener('click', window.closeModal);
    overlay?.addEventListener('click', window.closeModal);

    // =====================
    // FORM
    // =====================
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

        const [jamMulai, menitMulai] = mulai.split(':').map(Number);
        const [jamSelesai, menitSelesai] = selesai.split(':').map(Number);

        const totalMenit = (jamSelesai * 60 + menitSelesai) - (jamMulai * 60 + menitMulai);

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

    // =====================
    // MODAL DOKUMENTASI
    // =====================
    window.openModalDok = function (el) {
        const form = document.getElementById('formDok');
        const modalDok = document.getElementById('modalDok');

        if (form && el) {
            form.action = el.getAttribute('data-action');
        }

        modalDok?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModalDok = function () {
        document.getElementById('modalDok')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    @if ($errors->has('file_path'))
        window.openModalDok(document.querySelector('#tabelLembur [data-action]') ?? document.querySelector('[data-action]'));
    @endif

    // =====================
    // FILTER TANGGAL
    // =====================
    let selectedDate = null;

    function filterTabel() {
        document.querySelectorAll('#tabelLembur tr[data-tanggal]').forEach(function (row) {
            row.style.display = !selectedDate || row.dataset.tanggal === selectedDate ? '' : 'none';
        });
    }

    function updateResetBtn() {
        const btn = document.getElementById('btnResetFilter');
        if (!btn) return;

        selectedDate ? btn.classList.remove('hidden') : btn.classList.add('hidden');
    }

    document.getElementById('btnResetFilter')?.addEventListener('click', function () {
        selectedDate = null;

        document.getElementById('dateLabel').textContent = 'Semua Tanggal';
        document.getElementById('dateValue').value = '';

        filterTabel();
        updateResetBtn();
    });

    // =====================
    // DATE PICKER
    // =====================
    const picker = document.getElementById('datePicker');
    const btnDate = document.getElementById('dateBtn');
    const panel = document.getElementById('datePanel');
    const grid = document.getElementById('dateGrid');
    const navLabel = document.getElementById('dateNavLabel');
    const dateLabel = document.getElementById('dateLabel');
    const dateValue = document.getElementById('dateValue');
    const btnPrev = document.getElementById('datePrev');
    const btnNext = document.getElementById('dateNext');
    const btnToday = document.getElementById('btnToday');
    const btnDateClose = document.getElementById('btnDateClose');

    if (!picker || !btnDate || !panel || !grid || !navLabel) return;

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const monthShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

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

        dayNames.forEach(function (day) {
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

        const base = 'rounded-lg border py-1 text-sm transition ';

        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrev - i;

            dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', function () {
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
            const isSelected = selDay === day && selMonth === viewMonth && selYear === viewYear;
            const isToday = day === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear();

            const className = isSelected
                ? 'border-[#faa938] bg-[#faa938] text-white'
                : isToday
                    ? 'border-transparent bg-[#faa938]/20 text-[#faa938]'
                    : 'border-transparent text-gray-700 hover:border-[#faa938] hover:text-[#faa938]';

            dayGrid.appendChild(makeBtn(day, base + className, function () {
                setDate(viewYear, viewMonth, day);
                closePanel();
            }));
        }

        const total = firstDay + daysInMonth;
        const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);

        for (let day = 1; day <= remaining; day++) {
            dayGrid.appendChild(makeBtn(day, base + 'border-transparent text-gray-300', function () {
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

        monthNames.forEach(function (name, month) {
            const isSelected = month === selMonth && viewYear === selYear;
            const isNow = month === now.getMonth() && viewYear === now.getFullYear();

            const className = isSelected
                ? 'border-[#faa938] bg-[#faa938] text-white'
                : isNow
                    ? 'border-[#faa938] bg-white text-[#faa938]'
                    : 'border-gray-200 text-gray-800 hover:border-[#faa938] hover:text-[#faa938]';

            monthGrid.appendChild(makeBtn(name.slice(0, 3), 'rounded-lg border px-2 py-2 text-sm transition ' + className, function () {
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

            yearGrid.appendChild(makeBtn(year, 'rounded-lg border px-2 py-2 text-sm transition ' + className, function () {
                viewYear = year;
                renderMonth();
            }));
        }

        grid.appendChild(yearGrid);
    }

    btnDate.addEventListener('click', function (event) {
        event.stopPropagation();

        panel.classList.contains('hidden') ? openPanel() : closePanel();
    });

    navLabel.addEventListener('click', function (event) {
        event.stopPropagation();

        if (view === 'day') {
            renderMonth();
        } else if (view === 'month') {
            renderYear();
        }
    });

    btnPrev?.addEventListener('click', function (event) {
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

    btnNext?.addEventListener('click', function (event) {
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

    btnToday?.addEventListener('click', function (event) {
        event.stopPropagation();

        viewYear = now.getFullYear();
        viewMonth = now.getMonth();

        setDate(now.getFullYear(), now.getMonth(), now.getDate());
        closePanel();
    });

    btnDateClose?.addEventListener('click', function (event) {
        event.stopPropagation();
        closePanel();
    });

    document.addEventListener('click', function (event) {
        if (picker && !picker.contains(event.target)) {
            closePanel();
        }
    });
});
</script>
@endpush

@endsection

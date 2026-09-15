@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- HERO --}}
<section class="mx-auto max-w-7xl py-8 px-4 sm:px-8 md:px-10 lg:px-20 mb-10">
    <div class="relative flex flex-col lg:flex-row items-center bg-[#f9b800]
                rounded-[30px] px-8 py-10 lg:py-12 overflow-visible">

        <div class="z-10 text-center lg:text-left lg:flex-1 lg:pr-[440px]">
            <h2 class="mb-3 text-2xl lg:text-3xl font-semibold leading-tight">
                Selamat Datang, {{ session('user')['nama'] }}
            </h2>

            <p class="text-[17px] leading-relaxed text-gray-900">
                Pantau pengajuan lembur seluruh pegawai BPS Jawa Tengah.
            </p>
        </div>

        <img
            class="mt-6 w-full max-w-[300px] object-contain drop-shadow-xl
                   lg:mt-0 lg:absolute lg:right-0 lg:max-w-[460px]"
            style="top: -75px"
            src="{{ asset('images/2.svg') }}"
            alt=""
        />
    </div>
</section>

{{-- MAIN CONTENT --}}
<section class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 md:px-10 lg:px-20">

    {{-- Metric Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Total pengajuan</p>
            <p class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Bulan ini</p>
        </div>

        {{-- Diproses — tidak bisa diklik --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Diproses</p>
            <p class="text-2xl font-semibold text-yellow-700 sm:text-3xl">{{ $stats['diproses'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Menunggu review</p>
        </div>

        {{-- Disetujui --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Disetujui</p>
            <p class="text-2xl font-semibold text-green-700 sm:text-3xl">{{ $stats['disetujui'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Pengajuan bulan ini</p>
        </div>

        {{-- Ditolak --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Ditolak</p>
            <p class="text-2xl font-semibold text-red-700 sm:text-3xl">{{ $stats['ditolak'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Pengajuan bulan ini</p>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 xl:gap-6">

        {{-- LEFT CONTENT --}}
        <div class="xl:col-span-3 rounded-2xl border border-slate-100 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-100
                px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-800 sm:text-lg">
                        Pengajuan Lembur
                    </h3>
                    <p class="text-xs text-slate-500 sm:text-sm">
                        5 pengajuan terbaru bulan ini dari seluruh tim
                    </p>
                </div>

                <a href="{{ route('pimpinan.pengajuan') }}"
                    class="inline-flex items-center justify-center rounded-full
                    border border-black/15 px-3 py-2 text-xs font-semibold
                    hover:bg-gray-50 sm:px-4 sm:text-sm">
                    Lihat Semua
                </a>
            </div>

            {{-- Table dengan vscroll --}}
            <div class="overflow-x-auto">
                <div class="max-h-[280px] overflow-y-auto">
                    <table class="min-w-[700px] w-full text-sm text-center">
                        <thead class="bg-slate-50 text-slate-500 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Nama Pegawai</th>
                                <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Tanggal</th>
                                <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Jam</th>
                                <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($pengajuan as $p)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-3 py-3 font-medium text-slate-800 sm:px-5 sm:py-4">
                                        {{ $p->nama_pegawai }}
                                    </td>
                                    <td class="px-3 py-3 sm:px-5 sm:py-4">
                                        {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="px-3 py-3 sm:px-5 sm:py-4">
                                        {{ $p->jam_mulai
                                            ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5)
                                            : '-' }}
                                    </td>
                                    <td class="px-3 py-3 sm:px-5 sm:py-4">
                                        @if ($p->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-600">Menunggu</span>
                                        @elseif ($p->status === 'approved')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">Disetujui</span>
                                        @elseif ($p->status === 'rejected')
                                            <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">
                                        Belum ada pengajuan bulan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT CONTENT --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5">
                <h3 class="text-base font-semibold text-slate-800 sm:text-lg">Lembur Hari Ini</h3>
                <p class="text-xs text-slate-500 sm:text-sm">Pegawai yang sedang / akan lembur hari ini</p>
            </div>
            <div class="space-y-4 p-5">
                @forelse ($lemburHariIni as $l)
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-slate-800">{{ $l->nama_pegawai }}</p>
                        <span class="whitespace-nowrap text-sm text-slate-500">
                            {{ $l->jam_mulai_disetujui
                                ? substr($l->jam_mulai_disetujui, 0, 5) . ' - ' . substr($l->jam_selesai_disetujui, 0, 5)
                                : '-' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada lembur hari ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

@endsection

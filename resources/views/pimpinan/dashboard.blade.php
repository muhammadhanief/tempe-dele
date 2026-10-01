@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- HERO --}}
<section class="mx-auto max-w-7xl pt-4 sm:pt-6 lg:pt-10 pb-2 px-4 sm:px-8 md:px-10 lg:px-20 mb-4 sm:mb-6 lg:mb-10">
    <div class="relative bg-gradient-to-r from-[#faa938] via-[#f9b800] to-[#f59e0b]
                rounded-2xl lg:rounded-[28px]
                p-4 sm:p-6 lg:px-10 lg:py-8
                shadow-sm
                overflow-hidden lg:overflow-visible">

        {{-- Ambient decorative background glows --}}
        <div class="absolute -top-12 -right-12 w-48 h-48 sm:w-64 sm:h-64 bg-white/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 sm:w-56 sm:h-56 bg-amber-700/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/[0.02] to-transparent pointer-events-none rounded-2xl lg:rounded-[28px]"></div>

        <div class="relative z-10 flex flex-row items-center sm:items-end justify-between gap-3 sm:gap-6 lg:gap-8">
            {{-- Text (flex-1 ensures it takes available space and NEVER collides with image) --}}
            <div class="flex-1 min-w-0 text-left py-1 sm:py-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 mb-2 sm:mb-3 rounded-full text-[11px] sm:text-xs font-bold bg-white/35 text-slate-950 backdrop-blur-md ring-1 ring-white/40 shadow-xs">
                    <span class="text-xs sm:text-sm">👋</span>
                    <span>Selamat Datang</span>
                </span>
                <h2 class="text-base sm:text-xl lg:text-2xl xl:text-3xl font-semibold text-slate-950 tracking-tight leading-snug mb-1 sm:mb-1.5 break-words">
                    {{ session('user')['nama'] }}
                </h2>
                <p class="text-[11px] sm:text-sm lg:text-[15px] font-medium leading-relaxed text-slate-900/85 line-clamp-2 sm:line-clamp-none max-w-xl">
                    Pantau pengajuan lembur seluruh pegawai BPS Jawa Tengah.
                </p>
            </div>

            {{-- Hero Image (shrink-0 with proportional scaling prevents collision at all widths) --}}
            <div class="shrink-0 flex items-end justify-end max-w-[32%] xs:max-w-[36%] sm:max-w-[40%] lg:max-w-[340px] xl:max-w-[380px]">
                <img
                    class="w-full h-auto object-contain object-bottom drop-shadow-md lg:drop-shadow-xl select-none pointer-events-none
                           lg:-mt-10 lg:-mb-4 transform transition-transform duration-300 hover:scale-105"
                    src="{{ asset('images/2.svg') }}"
                    alt="Hero"
                />
            </div>
        </div>
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

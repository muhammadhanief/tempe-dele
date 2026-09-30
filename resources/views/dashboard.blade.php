@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- HERO --}}
<section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 md:px-10 lg:px-20 lg:py-8">

    <div
        class="relative flex flex-col items-center overflow-visible
        rounded-[24px] bg-[#f9b800]
        px-5 py-6
        sm:px-8 sm:py-8
        lg:flex-row lg:items-center lg:px-10 lg:py-12">

        {{-- TEXT --}}
        <div class="z-10 text-center lg:flex-1 lg:pr-[420px] lg:text-left">

            <h2
                class="mb-3 text-xl font-semibold leading-snug
                sm:text-2xl lg:text-3xl">

                Selamat Datang, {{ session('user')['nama'] }}
            </h2>

            <p
                class="text-sm leading-relaxed text-gray-900
                sm:text-base lg:text-[17px]">

                Kelola pengajuan lembur dan pantau perkembangannya dengan lebih mudah.
            </p>
        </div>

        {{-- HERO IMAGE --}}
        <img
            class="mt-6 w-full max-w-[230px] object-contain drop-shadow-xl
            sm:max-w-[280px]
            md:max-w-[340px]
            lg:absolute lg:-right-6 lg:mt-0 lg:max-w-[450px]
            xl:max-w-[500px]"
            style="top: -48px"
            src="{{ asset('images/2.svg') }}"
            alt="Hero"
        />
    </div>
</section>

{{-- CONTENT --}}
<section class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 md:px-10 lg:px-20">

    {{-- HEADER --}}
    <div class="mb-6">

        <h1
            class="text-xl font-semibold tracking-tight
            sm:text-2xl">

            Aktivitas
        </h1>

        <p
            class="mt-1 text-sm leading-relaxed text-gray-600">

            Ringkasan aktivitas lembur dan pengajuan terbaru.
        </p>
    </div>

    {{-- METRIC --}}
    <div
        class="mb-6 grid grid-cols-1 gap-4
        sm:grid-cols-2
        xl:grid-cols-4">

        {{-- TOTAL --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">

            <p class="mb-2 text-xs text-gray-500">
                Total pengajuan
            </p>

            <p class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                {{ $stats['total'] }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Bulan ini
            </p>
        </div>

        {{-- DIPROSES --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">

            <p class="mb-2 text-xs text-gray-500">
                Diproses
            </p>

            <p class="text-2xl font-semibold text-yellow-700 sm:text-3xl">
                {{ $stats['diproses'] }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Menunggu review
            </p>
        </div>

        {{-- DISETUJUI --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">

            <p class="mb-2 text-xs text-gray-500">
                Disetujui
            </p>

            <p class="text-2xl font-semibold text-green-700 sm:text-3xl">
                {{ $stats['disetujui'] }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Pengajuan bulan ini
            </p>
        </div>

        {{-- DITOLAK --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">

            <p class="mb-2 text-xs text-gray-500">
                Ditolak
            </p>

            <p class="text-2xl font-semibold text-red-700 sm:text-3xl">
                {{ $stats['ditolak'] }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Pengajuan bulan ini
            </p>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div
        class="grid grid-cols-1 gap-5
        lg:grid-cols-2 lg:gap-6">

        {{-- PENGAJUAN TERBARU --}}
        <div
            class="overflow-hidden rounded-2xl
            border border-black/10 bg-white shadow-sm">

            {{-- HEADER --}}
            <div
                class="flex flex-col gap-4
                border-b border-black/10
                p-4
                sm:flex-row sm:items-center sm:justify-between sm:p-5">

                <div>

                    <h2 class="text-base font-semibold sm:text-lg">
                        Pengajuan terbaru
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        3 pengajuan lembur terakhir kamu.
                    </p>
                </div>

                <a
                    href="{{ route('lembur') }}"
                    class="inline-flex items-center justify-center
                    rounded-full border border-black/15
                    px-4 py-2 text-sm font-semibold
                    hover:bg-gray-50">

                    Lihat semua
                </a>
            </div>

            {{-- CONTENT --}}
            <div class="divide-y divide-black/5">

                @forelse ($pengajuanTerbaru as $item)

                <div
                    class="flex flex-col gap-3
                    p-4
                    sm:flex-row sm:items-center sm:justify-between sm:p-5">

                    {{-- LEFT --}}
                    <div class="min-w-0">

                        <p class="text-sm font-semibold">

                            {{ \Carbon\Carbon::parse($item->date)->translatedFormat('D, d M Y') }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">

                            @if ($item->status === 'approved' && $item->jam_mulai_disetujui)

                            {{ substr($item->jam_mulai_disetujui, 0, 5) }}
                            &ndash;
                            {{ substr($item->jam_selesai_disetujui, 0, 5) }}

                            @elseif ($item->jam_mulai)

                            {{ substr($item->jam_mulai, 0, 5) }}
                            &ndash;
                            {{ substr($item->jam_selesai, 0, 5) }}

                            @else
                            -
                            @endif
                        </p>
                    </div>

                    {{-- RIGHT --}}
                    <div class="flex items-center gap-3">

                        @if ($item->status === 'approved')

                        <span
                            class="rounded-full bg-green-100
                            px-3 py-1 text-xs font-semibold text-green-800">

                            Disetujui
                        </span>

                        @elseif ($item->status === 'pending')

                        <span
                            class="rounded-full bg-yellow-100
                            px-3 py-1 text-xs font-semibold text-yellow-800">

                            Diproses
                        </span>

                        @elseif ($item->status === 'rejected')

                        <span
                            class="rounded-full bg-red-100
                            px-3 py-1 text-xs font-semibold text-red-800">

                            Ditolak
                        </span>

                        @endif

                        <span class="text-sm text-gray-400">
                            ›
                        </span>
                    </div>
                </div>

                @empty

                <div class="p-5 text-center text-sm text-gray-400">

                    Belum ada pengajuan lembur.
                </div>

                @endforelse
            </div>
        </div>

        {{-- JADWAL --}}
        <div
            class="overflow-hidden rounded-2xl
            border border-black/10 bg-white shadow-sm">

            {{-- HEADER --}}
            <div
                class="flex flex-col gap-4
                border-b border-black/10
                p-4
                sm:flex-row sm:items-center sm:justify-between sm:p-5">

                <div>

                    <h2 class="text-base font-semibold sm:text-lg">
                        Jadwal lembur mendatang
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        Lembur disetujui yang belum terlaksana.
                    </p>
                </div>

                <a
                    href="{{ route('lembur') }}"
                    class="inline-flex items-center justify-center
                    rounded-full border border-black/15
                    px-4 py-2 text-sm font-semibold
                    hover:bg-gray-50">

                    Lihat semua
                </a>
            </div>

            {{-- CONTENT --}}
            <div class="divide-y divide-black/5">

                @forelse ($jadwalMendatang as $item)

                @php
                    $selisihHari =
                    \Carbon\Carbon::today()->diffInDays(
                        \Carbon\Carbon::parse($item->date),
                        false
                    );
                @endphp

                <div
                    class="flex flex-col gap-4
                    p-4
                    sm:flex-row sm:items-center sm:justify-between sm:p-5">

                    {{-- LEFT --}}
                    <div class="flex items-center gap-4 min-w-0">

                        {{-- DATE BOX --}}
                        <div
                            class="flex h-12 w-12 flex-shrink-0
                            flex-col items-center justify-center
                            rounded-xl bg-[#f9b800]/20 text-center">

                            <span
                                class="text-xs font-semibold
                                leading-none text-yellow-800">

                                {{ \Carbon\Carbon::parse($item->date)->translatedFormat('M') }}
                            </span>

                            <span
                                class="text-lg font-semibold
                                leading-tight text-yellow-900">

                                {{ \Carbon\Carbon::parse($item->date)->format('d') }}
                            </span>
                        </div>

                        {{-- TEXT --}}
                        <div class="min-w-0">

                            <p
                                class="text-sm font-semibold leading-relaxed">

                                {{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d F Y') }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">

                                @if ($item->jam_mulai_disetujui)

                                {{ substr($item->jam_mulai_disetujui, 0, 5) }}
                                &ndash;
                                {{ substr($item->jam_selesai_disetujui, 0, 5) }}

                                @else
                                -
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- BADGE --}}
                    <div class="flex-shrink-0">

                        @if ($selisihHari === 1)

                        <span
                            class="rounded-full bg-blue-100
                            px-3 py-1 text-xs font-semibold text-blue-800">

                            Besok
                        </span>

                        @elseif ($selisihHari <= 3)

                        <span
                            class="rounded-full bg-yellow-100
                            px-3 py-1 text-xs font-semibold text-yellow-800">

                            {{ $selisihHari }} hari lagi
                        </span>

                        @else

                        <span
                            class="rounded-full bg-gray-100
                            px-3 py-1 text-xs font-semibold text-gray-600">

                            {{ $selisihHari }} hari lagi
                        </span>

                        @endif
                    </div>
                </div>

                @empty

                <div class="p-5 text-center text-sm text-gray-400">

                    Tidak ada jadwal lembur mendatang.
                </div>

                @endforelse
            </div>
        </div>
    </div>
</section>

<script>
    function getGreeting(hour) {
        if (hour >= 4 && hour < 11) return "Selamat Pagi";
        if (hour >= 11 && hour < 15) return "Selamat Siang";
        if (hour >= 15 && hour < 18) return "Selamat Sore";
        return "Selamat Malam";
    }

    const el = document.getElementById("greeting");

    if (el) {
        el.textContent =
            `${getGreeting(new Date().getHours())},
            {{ session('user')['nama'] ?? 'User' }}`;
    }

    console.log("DEBUG TIMKERJA:",
        @json(session('debug_timkerja')));
</script>

@endsection

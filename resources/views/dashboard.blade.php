@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- HERO --}}
<section class="mx-auto max-w-7xl pt-4 sm:pt-6 lg:pt-10 pb-2 px-4 sm:px-6 md:px-10 lg:px-20">
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
            {{-- TEXT (flex-1 ensures it takes available space and NEVER collides with image) --}}
            <div class="flex-1 min-w-0 text-left py-1 sm:py-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 mb-2 sm:mb-3 rounded-full text-[11px] sm:text-xs font-bold bg-white/35 text-slate-950 backdrop-blur-md ring-1 ring-white/40 shadow-xs">
                    <span id="greetingIcon" class="text-xs sm:text-sm">👋</span>
                    <span id="greetingText">Selamat Datang</span>
                </span>
                <h2 class="text-base sm:text-xl lg:text-2xl xl:text-3xl font-semibold text-slate-950 tracking-tight leading-snug mb-1 sm:mb-1.5 break-words">
                    Hai, {{ session('user')['nama'] }}! 👋
                </h2>
                <p class="text-[11px] sm:text-sm lg:text-[15px] font-medium leading-relaxed text-slate-900/85 line-clamp-2 sm:line-clamp-none max-w-xl">
                    Kelola pengajuan lembur dan pantau perkembangannya dengan lebih mudah.
                </p>
            </div>

            {{-- HERO IMAGE (shrink-0 with proportional scaling prevents collision at all widths) --}}
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

            <p class="mt-1 text-xs text-slate-500">
                Tahun {{ date('Y') }}
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

            <p class="mt-1 text-xs text-slate-500">
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

            <p class="mt-1 text-xs text-slate-500">
                Tahun {{ date('Y') }}
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

            <p class="mt-1 text-xs text-slate-500">
                Tahun {{ date('Y') }}
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
                    </div>
                </div>

                @empty

                <div class="p-5 text-center text-sm text-slate-500 font-medium">

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

                <div class="p-5 text-center text-sm text-slate-500 font-medium">

                    Tidak ada jadwal lembur mendatang.
                </div>

                @endforelse
            </div>
        </div>
    </div>
</section>

<script>
    function updateGreeting() {
        const hour = new Date().getHours();
        let greeting = "Selamat Datang";
        let icon = "👋";

        if (hour >= 4 && hour < 11) {
            greeting = "Selamat Pagi";
            icon = "🌅";
        } else if (hour >= 11 && hour < 15) {
            greeting = "Selamat Siang";
            icon = "☀️";
        } else if (hour >= 15 && hour < 18) {
            greeting = "Selamat Sore";
            icon = "🌇";
        } else {
            greeting = "Selamat Malam";
            icon = "🌙";
        }

        const iconEl = document.getElementById("greetingIcon");
        const textEl = document.getElementById("greetingText") || document.getElementById("greeting");
        if (iconEl) iconEl.textContent = icon;
        if (textEl) textEl.textContent = greeting;
    }
    updateGreeting();
</script>

@endsection

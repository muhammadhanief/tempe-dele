<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEMPE DELE</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>

<body class="bg-white text-slate-900 antialiased">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex h-16 items-center justify-between">

                <a href="/" class="flex items-center gap-3 transition hover:opacity-80">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Logo TEMPE DELE"
                         class="h-10 w-10 object-contain">

                    <div class="flex flex-col leading-tight">
                        <span class="text-sm font-bold text-slate-900">
                            TEMPE DELE
                        </span>
                        <span class="hidden text-xs text-slate-500 sm:block">
                            Sistem Pengelolaan Dokumen Lembur
                        </span>
                    </div>
                </a>

                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-full bg-[#fd9a10] px-4 py-2 text-sm font-semibold text-white
                          shadow-[0_4px_14px_rgba(253,154,16,0.28)]
                          transition-all duration-300 hover:bg-[#f6960f] hover:shadow-[0_6px_20px_rgba(253,154,16,0.38)]">
                    Masuk
                </a>

            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section id="hero" class="mx-auto flex min-h-screen max-w-7xl flex-col items-center justify-center px-6 pt-24 text-center">
        <p class="animate-fade-up text-base font-medium tracking-tight text-slate-700 sm:text-lg">
            Badan Pusat Statistik Provinsi Jawa Tengah
        </p>

        <h1 class="animate-fade-up animation-delay-200 mx-auto mt-4 max-w-4xl text-5xl font-semibold tracking-tight text-slate-950 sm:text-7xl">
            <span class="inline-block">Sistem</span>

            <span class="relative inline-block whitespace-nowrap text-[#faa938]">
                <svg aria-hidden="true" viewBox="0 0 418 42"
                     class="absolute top-2/3 left-0 h-[0.58em] w-full fill-amber-300/70"
                     preserveAspectRatio="none">
                    <path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z" />
                </svg>
                <span class="relative">Pengelolaan</span>
            </span>

            <span class="block">Dokumen Lembur</span>
        </h1>

        <p class="animate-fade-up animation-delay-400 mx-auto mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
            Permudah pengajuan dan kelola berkas lembur dalam satu sistem terintegrasi.
        </p>

        <div class="animate-fade-up animation-delay-600 mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ asset('documents/panduan_pengguna.pdf') }}" target="_blank"
               class="group inline-flex items-center justify-center gap-2 rounded-full bg-[#fd9a10] px-5 py-2.5 text-sm font-semibold text-white
                      shadow-[0_4px_14px_rgba(253,154,16,0.35)]
                      transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#f6960f] hover:shadow-[0_6px_20px_rgba(253,154,16,0.45)]">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span>Panduan Pengguna</span>
            </a>

            <a href="#fitur"
               class="group inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-medium text-slate-700
                      ring-1 ring-slate-200 transition-all duration-300 hover:text-slate-900 hover:ring-slate-300 hover:shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor"
                     class="h-4 w-4 transition-transform duration-300 group-hover:translate-y-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
                <span>Lihat Fitur</span>
            </a>
        </div>
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="mx-auto max-w-6xl px-6 py-24">

        <!-- Header -->
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold text-[#fd9a10] tracking-wide">
                Fitur TEMPE DELE
            </p>

            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Semua administrasi lembur dalam satu sistem
            </h2>

            <p class="mt-4 text-base leading-7 text-slate-600">
                Mengelola pengajuan, aktivitas, dan dokumen lembur dalam satu alur yang terintegrasi.
            </p>
        </div>

        <!-- Cards -->
        <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2">

            <!-- Card -->
            <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-[#fd9a10]">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>

                <h3 class="mt-5 text-base font-semibold text-slate-900">
                    Pengajuan Lembur
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Ajukan lembur dengan alur yang lebih praktis dan terdokumentasi.
                </p>
            </div>

            <!-- Card -->
            <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-[#fd9a10]">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <h3 class="mt-5 text-base font-semibold text-slate-900">
                    Presensi
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Pantau data kehadiran sebagai dasar proses pengajuan lembur.
                </p>
            </div>

            <!-- Card -->
            <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-[#fd9a10]">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                </div>

                <h3 class="mt-5 text-base font-semibold text-slate-900">
                    Kelola Dokumen
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Simpan dan kelola berkas lembur secara rapi dalam satu tempat.
                </p>
            </div>

            <!-- Card -->
            <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-[#fd9a10]">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>

                <h3 class="mt-5 text-base font-semibold text-slate-900">
                    Laporan
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Buat rekap dan laporan lembur dengan lebih cepat dan terstruktur.
                </p>
            </div>

        </div>

    </section>


    {{-- CTA --}}
<section id="akses" class="mx-auto max-w-6xl px-6 py-40">

    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white px-8 py-16 text-center shadow-xl sm:px-16">

        <!-- subtle glow -->
        <div class="pointer-events-none absolute -top-16 left-1/2 h-60 w-60 -translate-x-1/2 rounded-full bg-orange-200 opacity-20 blur-3xl"></div>

        <div class="relative">

            <p class="text-sm font-semibold tracking-wide text-[#fd9a10]">
                Akses Sistem
            </p>

            <h2 class="mx-auto mt-4 max-w-2xl text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                Mulai kelola lembur dengan lebih mudah
            </h2>

            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-slate-600">
                Masuk ke sistem untuk mengajukan, mengelola, dan memantau seluruh proses lembur secara lebih efisien.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">

                <!-- Primary CTA -->
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-full bg-[#fd9a10] px-6 py-3 text-sm font-semibold text-white
                          shadow-[0_4px_14px_rgba(253,154,16,0.35)]
                          transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#f6960f] hover:shadow-[0_6px_20px_rgba(253,154,16,0.45)]">
                    Masuk ke Sistem
                </a>

            </div>

        </div>
    </div>

</section>

<footer class="px-8 py-5 flex items-center justify-between flex-wrap gap-3">

    {{-- Tengah: Copyright --}}
    <p class="text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Jawa Tengah. Hak cipta dilindungi.
    </p>

    {{-- Kanan: Tim SID --}}
    <div class="flex items-center gap-1.5">
        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
        <span class="text-xs text-slate-400">Tim SID &mdash; BPS Provinsi Jawa Tengah</span>
    </div>

</footer>

</body>
</html>

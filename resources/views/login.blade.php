<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEMPE DELE</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
</head>



<body class="min-h-screen bg-white text-slate-900 antialiased">

    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-6 py-12">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-200/70 lg:grid-cols-2">


        {{-- Left Illustration --}}
        <section class="hidden items-center justify-center bg-slate-50 px-10 py-12 lg:flex">
            <div class="w-full max-w-xl">
                <img src="{{ asset('images/login.jpg') }}"
                     alt="Ilustrasi TEMPE DELE"
                     class="w-full object-contain scale-125">
            </div>
        </section>

        {{-- Right Form --}}
        <section class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">

                {{-- Logo stacked --}}
                <div class="mb-6 flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}"
                        alt="Logo TEMPE DELE"
                        class="h-10 w-10 object-contain">

                    <div class="flex flex-col leading-tight">
                        <span class="text-sm font-bold text-slate-900">
                            TEMPE DELE
                        </span>
                        <span class="text-xs text-slate-500">
                            Sistem Pengelolaan Dokumen Lembur
                        </span>
                    </div>
                </div>

                <div class="mb-7 text-center">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-950">
                        Masuk ke Sistem
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Gunakan akun SSO BPS Anda untuk melanjutkan.
                    </p>
                </div>

                @if ($errors->has('login'))
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first('login') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.proses') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-slate-700">
                            Username
                        </label>

                        <input id="username"
                               type="text"
                               name="username"
                               value="{{ old('username') }}"
                               placeholder="Masukkan username"
                               autocomplete="username"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition
                                      placeholder:text-slate-400
                                      focus:border-[#fd9a10] focus:ring-4 focus:ring-orange-100">
                    </div>

                    <div>
                        <label for="passwordInput" class="mb-2 block text-sm font-medium text-slate-700">
                            Password
                        </label>

                        <div class="relative">
                            <input id="passwordInput"
                                   type="password"
                                   name="password"
                                   placeholder="Masukkan password"
                                   autocomplete="current-password"
                                   class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none transition
                                          placeholder:text-slate-400
                                          focus:border-[#fd9a10] focus:ring-4 focus:ring-orange-100">

                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700">

                                {{-- Eye --}}
                                <svg id="iconShow" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12 18 19.5 12 19.5 2.25 12 2.25 12Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                </svg>

                                {{-- Eye slash --}}
                                <svg id="iconHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.98 8.223A10.477 10.477 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.45 10.45 0 0 0 5.252-1.41M6.228 6.228A10.45 10.45 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a10.53 10.53 0 0 1-2.15 3.253M6.228 6.228 3 3m3.228 3.228 3.65 3.65m0 0a3 3 0 1 0 4.243 4.243m-4.243-4.243 4.243 4.243m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-[#fd9a10] px-5 py-3 text-sm font-semibold text-white
                                   shadow-[0_4px_14px_rgba(253,154,16,0.35)]
                                   transition-all duration-300
                                   hover:-translate-y-0.5 hover:bg-[#f6960f] hover:shadow-[0_6px_20px_rgba(253,154,16,0.45)]
                                   active:translate-y-0">
                        Masuk
                    </button>
                </form>

                @if (app()->isLocal())
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">⚡ Testing Auto-Login</span>
                            <span class="text-[10px] bg-amber-100 text-amber-800 font-semibold px-2 py-0.5 rounded-full">Dev Mode</span>
                        </div>

                        <div class="space-y-3 text-xs">
                            {{-- Kabag Umum --}}
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block mb-1">🏛️ Kabag Umum:</span>
                                <a href="{{ route('dev.login', '197106131993121001') }}"
                                   class="flex items-center justify-between px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 hover:bg-indigo-100 transition-colors font-medium">
                                    <span>Joko Suwarjo (Kabag Umum)</span>
                                    <span class="text-[10px] bg-indigo-200 px-1.5 py-0.5 rounded text-indigo-900 font-bold">Masuk &rarr;</span>
                                </a>
                            </div>

                            {{-- Tim SID --}}
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block mb-1">🌐 Tim SID (Alur Bertingkat):</span>
                                <div class="space-y-1.5">
                                    <a href="{{ route('dev.login', '197703081999011001') }}"
                                       class="flex items-center justify-between px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors font-medium">
                                        <span>Sumbodo Aji (Ketua Tim SID)</span>
                                        <span class="text-[10px] bg-blue-200 px-1.5 py-0.5 rounded text-blue-900 font-bold">Masuk &rarr;</span>
                                    </a>
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <a href="{{ route('dev.login', '198907112010122003') }}"
                                           class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-center font-medium truncate text-[11px]" title="Pristiana Diah Ariyantika (Pegawai SID 1)">
                                            Pegawai 1
                                        </a>
                                        <a href="{{ route('dev.login', '198501252006042001') }}"
                                           class="px-2 py-1.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 hover:bg-emerald-100 text-center font-semibold truncate text-[11px]" title="Indah Purnamasari (Pegawai SID 2)">
                                            Pegawai 2 (Indah)
                                        </a>
                                        <a href="{{ route('dev.login', '197205242006041002') }}"
                                           class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-center font-medium truncate text-[11px]" title="Herry Kusmaiwanto (Pegawai SID 3)">
                                            Pegawai 3
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Tim Bagian Umum --}}
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 block mb-1">📋 Pegawai Tim Bagian Umum (Alur Langsung):</span>
                                <div class="grid grid-cols-3 gap-1.5">
                                    <a href="{{ route('dev.login', '198110052006042035') }}"
                                       class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-center font-medium truncate text-[11px]" title="Ika Budi (Pegawai Umum)">
                                        Ika Budi
                                    </a>
                                    <a href="{{ route('dev.login', '197509231998032001') }}"
                                       class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-center font-medium truncate text-[11px]" title="Istiqomah (Pegawai Umum)">
                                        Istiqomah
                                    </a>
                                    <a href="{{ route('dev.login', '197412171998032004') }}"
                                       class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-center font-medium truncate text-[11px]" title="Aning Widiyatmi (Pegawai Umum)">
                                        Aning W.
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const iconShow = document.getElementById('iconShow');
            const iconHide = document.getElementById('iconHide');

            if (input.type === 'password') {
                input.type = 'text';
                iconShow.classList.add('hidden');
                iconHide.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconShow.classList.remove('hidden');
                iconHide.classList.add('hidden');
            }
        }
    </script>

</body>
</html>

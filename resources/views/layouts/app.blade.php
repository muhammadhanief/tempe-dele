<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tempe Dele')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@toast-ui/calendar@2.1.3/dist/toastui-calendar.min.css">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>

    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-white">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- MAIN AREA --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- NAVBAR --}}
            @include('partials.navbar')

            {{-- PAGE CONTENT --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main>

            {{-- FOOTER --}}
            <footer class="py-4 px-4 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} BPS Provinsi Jawa Tengah - Tim SID
            </footer>

        </div>
        {{-- end MAIN AREA --}}

    </div>
    {{-- end flex min-h-screen --}}

    {{-- LOADING OVERLAY --}}
    @if(!$__env->hasSection('hideLoading'))
        <div id="loadingOverlay"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm
                    opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="flex flex-col items-center gap-4 rounded-2xl bg-white border border-slate-200 shadow-xl px-10 py-8">
                <svg class="h-8 w-8 animate-spin text-[#fd9a10]" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div class="text-center">
                    <p class="text-sm font-semibold text-slate-800">Memuat data...</p>
                    <p class="text-xs text-slate-500 mt-1">Mohon tunggu sebentar</p>
                </div>
            </div>
        </div>
    @endif

    {{-- SCRIPTS --}}
    @stack('scripts')

    <script>
        // Global Keyboard Accessibility Handler (R-32)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const profileMenu = document.getElementById('profileDropdownMenu');
                if (profileMenu && !profileMenu.classList.contains('hidden')) {
                    profileMenu.classList.add('hidden');
                }
                if (typeof closeSidebar === 'function' && typeof isOpen !== 'undefined' && isOpen) {
                    closeSidebar();
                }
            }
        });
    </script>

</body>
</html>

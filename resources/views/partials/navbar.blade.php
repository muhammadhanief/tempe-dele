<nav class="flex h-16 items-center justify-between gap-3 bg-white px-6">
    @php
        $role = session('user')
            ? \DB::table('m_pegawai')->where('nip', session('user')['nip'])->value('role')
            : null;

        $roleLabel = match($role) {
            'superadmin' => 'Super Admin',
            'admin'      => 'Admin',
            'ketua_tim'  => 'Ketua Tim',
            'pimpinan'   => 'Pimpinan',
            'user'       => 'Pegawai',
            default      => 'User',
        };
    @endphp

    {{-- Page Title --}}
    <span class="text-xl font-bold tracking-tight text-slate-800">
        @yield('title')
    </span>

    {{-- Right side --}}
    <div class="flex items-center gap-2">

        {{-- Profile Dropdown --}}
        <div class="relative z-30" id="profileDropdown">
            <button type="button" id="profileDropdownBtn"
                onclick="toggleProfileDropdown(event)"
                class="flex h-10 items-center gap-2.5 rounded-xl px-3 hover:bg-slate-50">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange-100">
                    <svg class="h-4 w-4 stroke-current text-[#faa938]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>

                <div class="hidden flex-col text-left leading-tight sm:flex">
                    <span class="max-w-28 truncate text-sm font-semibold text-slate-700">
                        {{ session('user')['nama'] ?? 'Nama User' }}
                    </span>
                    <span class="text-xs leading-none text-slate-400">
                        {{ $roleLabel }}
                    </span>
                </div>

                <svg class="hidden h-3.5 w-3.5 text-slate-400 sm:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="profileDropdownMenu"
                class="hidden absolute right-0 top-full mt-2 w-52 origin-top-right rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
                <a href="{{ route('profile') }}"
                    class="flex items-center gap-3 rounded-t-xl border-b border-slate-100 px-3 py-3 transition-colors hover:bg-slate-50">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-100">
                        <svg class="h-4 w-4 stroke-current text-[#faa938]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <div class="flex min-w-0 flex-col leading-tight">
                        <span class="truncate text-sm font-semibold text-slate-700">
                            {{ session('user')['nama'] ?? 'Nama User' }}
                        </span>
                        <span class="truncate text-xs text-slate-400">
                            {{ $roleLabel }}
                        </span>
                    </div>
                </a>

                <div class="px-1.5 py-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-red-50 hover:text-red-500">
                            <svg class="h-4 w-4 stroke-current text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Custom Toggle Button — menggantikan hamburger (mobile only) --}}
        <div
            id="sidebar-toggle"
            onclick="toggleSidebar()"
            class="ml-0.5 lg:hidden group flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl hover:bg-slate-100 transition-colors duration-200 shrink-0"
        >
            <div class="space-y-1.5">
                <span id="bar-top" class="block h-[3px] w-6 origin-center rounded-full bg-slate-500 transition-all duration-300 ease-in-out"></span>
                <span id="bar-bot" class="block h-[3px] w-4 origin-center rounded-full bg-orange-400 transition-all duration-300 ease-in-out"></span>
            </div>
        </div>

    </div>
</nav>

<script>
function toggleProfileDropdown(event) {
    event.stopPropagation();

    const menu = document.getElementById('profileDropdownMenu');
    if (!menu) return;

    menu.classList.toggle('hidden');
}

document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('profileDropdown');
    const menu = document.getElementById('profileDropdownMenu');

    if (!dropdown || !menu) return;

    if (!dropdown.contains(event.target)) {
        menu.classList.add('hidden');
    }
});
</script>
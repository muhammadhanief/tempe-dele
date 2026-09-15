{{-- Overlay backdrop (mobile only) --}}
<div
    id="sidebar-overlay"
    class="fixed inset-0 z-55 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
    onclick="closeSidebar()"
></div>


{{-- Sidebar --}}
<aside
    id="main-sidebar"
    class="fixed lg:sticky top-0 left-0 z-55
           flex flex-col w-64 min-h-screen bg-slate-900 py-5 shrink-0
           -translate-x-full lg:translate-x-0
           transition-transform duration-300 ease-in-out"
>
    {{-- Brand --}}
    <a href="#" class="flex items-center gap-2 px-4 mb-8 mt-2 lg:mt-0">
        <div class="w-16 h-16 flex items-center justify-center shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="Logo OverTime" class="w-full h-auto" />
        </div>
        <div class="flex flex-col leading-tight">
            <span class="text-base font-bold text-white tracking-wide mb-1.5">TEMPE DELE</span>
            <span class="text-[12px] text-slate-300">
                sisTEM PEngelolaan <br>DokumEn LEmbur
            </span>
        </div>
    </a>

    @php
        $role = session('user')
            ? \DB::table('m_pegawai')->where('nip', session('user')['nip'])->value('role')
            : null;

        if ($role === 'ketua_tim') {
            $sections = [
                [
                    'title' => 'Menu',
                    'items' => [
                        [
                            'label' => 'Dashboard',
                            'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'route' => 'ketua-tim.dashboard',
                            'active' => request()->routeIs('ketua-tim.dashboard'),
                        ],
                        [
                            'label' => 'Pengajuan Lembur',
                            'path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                            'route' => 'ketua-tim.pengajuan',
                            'active' => request()->routeIs('ketua-tim.pengajuan'),
                        ],
                    ],
                ],
                [
                    'title' => 'Lainnya',
                    'items' => [
                        [
                            'label' => 'Lembur',
                            'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'route' => 'ketua-tim.lembur',
                            'active' => request()->routeIs('ketua-tim.lembur'),
                        ],
                    ],
                ],
            ];

        } elseif ($role === 'user') {
            $sections = [
                [
                    'title' => 'Menu',
                    'items' => [
                        [
                            'label' => 'Dashboard',
                            'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'route' => 'pegawai.dashboard',
                            'active' => request()->routeIs('pegawai.dashboard'),
                        ],
                        [
                            'label' => 'Lembur',
                            'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'route' => 'lembur',
                            'active' => request()->routeIs('lembur'),
                        ],
                        [
                            'label' => 'Rekapitulasi',
                            'path' => 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2',
                            'route' => 'rekapitulasi',
                            'active' => request()->routeIs('rekapitulasi'),
                        ],
                    ],
                ],
                [
                    'title' => 'Lainnya',
                    'items' => [],
                ],
            ];

        } elseif (in_array($role, ['admin', 'superadmin'])) {

            $lainnyaItems = [
                [
                    'label' => 'Rekapitulasi Lembur',
                    'path' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25',
                    'route' => 'admin.spkl',
                    'active' => request()->routeIs('admin.spkl'),
                ],
                [
                    'label' => 'Laporan',
                    'path' => 'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75',
                    'route' => 'admin.laporan',
                    'active' => request()->routeIs('admin.laporan'),
                ],
                [
                    'label' => 'Akumulasi',
                    'path' => 'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z',
                    'route' => 'admin.akumulasi',
                    'active' => request()->routeIs('admin.akumulasi'),
                ],
                [
                    'label' => 'Daftar Hadir',
                    'path' => 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75',
                    'route' => 'admin.daftar_hadir',
                    'active' => request()->routeIs('admin.daftar_hadir'),
                ],
            ];

            $masterItems = [
                [
                    'label' => 'Pengguna',
                    'path' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
                    'route' => 'admin.pengguna',
                    'active' => request()->routeIs('admin.pengguna'),
                ],
                [
                    'label' => 'Tim',
                    'path' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
                    'route' => 'admin.tim',
                    'active' => request()->routeIs('admin.tim'),
                ],
                [
                    'label' => 'Tarif',
                    'path' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                    'route' => 'admin.tarif',
                    'active' => request()->routeIs('admin.tarif'),
                ],
                [
                    'label' => 'Pejabat',
                    'path' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
                    'route' => 'admin.pejabat',
                    'active' => request()->routeIs('admin.pejabat'),
                ],
            ];

            $sections = [
                [
                    'title' => 'Menu',
                    'items' => [
                        [
                            'label' => 'Dashboard',
                            'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'route' => 'admin.dashboard',
                            'active' => request()->routeIs('admin.dashboard'),
                        ],
                        [
                            'label' => 'Presensi',
                            'path' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
                            'route' => 'admin.presensi',
                            'active' => request()->routeIs('admin.presensi'),
                        ],
                        [
                            'label' => 'Generate Dokumen',
                            'path' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z',
                            'route' => 'admin.dokumen',
                            'active' => request()->routeIs('admin.dokumen'),
                        ],
                        [
                            'label' => 'Lembur',
                            'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'route' => 'admin.lembur',
                            'active' => request()->routeIs('admin.lembur'),
                        ],
                        [
                            'label' => 'Pengajuan Lembur',
                            'path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                            'route' => 'admin.pengajuan',
                            'active' => request()->routeIs('admin.pengajuan'),
                        ],
                    ],
                ],
                [
                    'title' => 'Dokumen',
                    'items' => $lainnyaItems,
                ],
                [
                    'title' => 'Master',
                    'items' => $masterItems,
                ],
            ];
        } elseif ($role === 'pimpinan') {
            $sections = [
                [
                    'title' => 'Menu',
                    'items' => [
                        [
                            'label'  => 'Dashboard',
                            'path'   => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'route'  => 'pimpinan.dashboard',
                            'active' => request()->routeIs('pimpinan.dashboard'),
                        ],
                        [
                            'label'  => 'Pengajuan Lembur',
                            'path'   => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                            'route'  => 'pimpinan.pengajuan',
                            'active' => request()->routeIs('pimpinan.pengajuan'),
                        ],
                    ],
                ],
            ];
        } else {
            $sections = [];
        }
    @endphp

    {{-- Nav Items --}}
    <nav class="flex-1 overflow-y-auto px-3 space-y-4 scrollbar-thin scrollbar-thumb-slate-700">
        @foreach ($sections as $index => $section)
            @if(!empty($section['items']))
                <div class="flex flex-col">
                    <p class="px-3 mb-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        {{ $section['title'] }}
                    </p>

                    @foreach ($section['items'] as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            onclick="closeSidebar()"
                            class="relative flex items-center gap-3 w-full h-12 px-4 rounded-xl text-sm transition-colors
                                {{ $item['active']
                                    ? 'bg-[#faa938]/15 text-[#faa938] font-semibold'
                                    : 'text-slate-500 hover:bg-white/5 hover:text-slate-300 font-medium' }}"
                        >
                            @if ($item['active'])
                                <span class="absolute left-0 top-3 bottom-3 w-0.5 bg-[#faa938] rounded-full"></span>
                            @endif

                            <svg
                                class="w-5 h-5 stroke-current shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="{{ $item['active'] ? '2.25' : '1.75' }}"
                                    d="{{ $item['path'] }}"
                                />
                            </svg>

                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
</aside>

<script>
    const sidebar   = document.getElementById('main-sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const barTop    = document.getElementById('bar-top');
    const barBot    = document.getElementById('bar-bot');
    let isOpen = false;

    function openSidebar() {
        isOpen = true;
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        overlay.classList.add('opacity-100');
        // Animate to X
        barTop.classList.add('translate-y-[12px]', 'rotate-45', 'bg-orange-500');
        barTop.classList.remove('bg-slate-500');
        barBot.classList.add('w-8', '-translate-y-[0px]', '-rotate-45');
        barBot.classList.remove('w-6');
    }

    function closeSidebar() {
        isOpen = false;
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        overlay.classList.remove('opacity-100');
        // Reset to hamburger
        barTop.classList.remove('translate-y-[12px]', 'rotate-45', 'bg-orange-500');
        barTop.classList.add('bg-slate-500');
        barBot.classList.remove('w-8', '-translate-y-[0px]', '-rotate-45');
        barBot.classList.add('w-6');
    }

    function toggleSidebar() {
        isOpen ? closeSidebar() : openSidebar();
    }

    // Tutup sidebar kalau resize ke desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) closeSidebar();
    });
</script>
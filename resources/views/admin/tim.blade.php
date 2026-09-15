@extends('layouts.app')

@section('title', 'Tim Lembur')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 my-4 sm:my-5">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        {{-- Filter Tim --}}
        <div class="relative w-full sm:flex-1 sm:min-w-[260px]">
            <input type="text" id="searchTim" placeholder="Cari nama tim..."
                onclick="toggleDropdownTim()" oninput="filterDropdownTim()" autocomplete="off"
                value="{{ request('search') }}"
                class="w-full h-10 rounded-xl border border-gray-200 bg-white pl-4 pr-8 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>

            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="h-3 w-3 text-gray-400">
                    <path fill="currentColor" d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z"/>
                </svg>
            </div>

            <div id="dropdownTim"
                class="hidden absolute z-20 mt-1 w-full max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                <ul id="listTim"></ul>
            </div>
        </div>

        <div class="hidden sm:block h-6 self-center border-l border-gray-200"></div>

        {{-- Sinkron --}}
        <button type="button" title="Sinkron Tim dari API" onclick="syncTim()"
            class="inline-flex h-10 w-full sm:w-10 items-center justify-center gap-2 rounded-xl sm:rounded-full border border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>

            <span class="sm:hidden text-sm font-medium">Sinkron Tim</span>
        </button>

        {{-- Tambah Tim --}}
        <button type="button" title="Tambah Tim" onclick="openCreateModal()"
            class="inline-flex h-10 w-full sm:w-10 items-center justify-center gap-2 rounded-xl sm:rounded-full bg-[#faa938] text-white hover:brightness-95 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>

            <span class="sm:hidden text-sm font-medium">Tambah Tim</span>
        </button>

    </div>
</div>

{{-- Alert --}}
<div id="alertBox" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-3">
    <div id="alertInner" class="rounded-xl px-4 py-3 text-sm font-medium"></div>
</div>

{{-- Tabel --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">
    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[980px] rounded-xl">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-5 text-center text-sm font-semibold text-gray-900 rounded-tl-xl">Nama Tim</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900">Ketua Tim</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900">Penugasan Khusus</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900">Jumlah Anggota</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900 w-32">Status</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900">Tanggal Non-aktif</th>
                    <th class="p-5 text-center text-sm font-semibold text-gray-900 rounded-tr-xl">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300">
                @forelse ($tim as $t)
                    <tr class="bg-white hover:bg-gray-50" data-kode="{{ $t->kode_tim }}">

                        {{-- Nama Tim --}}
                        <td class="p-5 text-sm text-gray-900">
                            <span class="cell-display">{{ $t->nama_tim ?? '—' }}</span>
                            <input type="text" name="nama_tim" value="{{ $t->nama_tim }}"
                                class="cell-edit hidden w-full rounded-lg border border-gray-200 px-2 py-1 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                        </td>

                        {{-- Ketua Tim --}}
                        <td class="p-5 text-sm text-gray-900">
                            <span class="cell-display">{{ $t->nama_ketua ?? '—' }}</span>
                            <input type="text" name="nama_ketua" value="{{ $t->nama_ketua }}"
                                class="cell-edit hidden w-full rounded-lg border border-gray-200 px-2 py-1 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                        </td>

                        {{-- Penugasan Khusus --}}
                        <td class="p-5 text-center text-sm text-gray-900">
                            <span class="cell-display">{{ $t->is_penugasan_khusus == 1 ? 'Ya' : 'Tidak' }}</span>
                            <select name="is_penugasan_khusus"
                                class="cell-edit hidden w-full rounded-lg border border-gray-200 px-2 py-1 text-center text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                                <option value="0" {{ !$t->is_penugasan_khusus ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ $t->is_penugasan_khusus ? 'selected' : '' }}>Ya</option>
                            </select>
                        </td>

                        {{-- Jumlah Anggota --}}
                        <td class="p-5 text-center text-sm text-gray-900">
                            <span>{{ $t->jumlah_anggota ?? 0 }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="p-5 text-center text-sm text-gray-900">
                            <span class="cell-display whitespace-nowrap">
                                {{ strtolower($t->status) === 'aktif' ? 'Aktif' : 'Non-aktif' }}
                            </span>
                            <select name="status"
                                class="cell-edit hidden w-full rounded-lg border border-gray-200 px-2 py-1 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                                <option value="aktif" {{ strtolower($t->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ strtolower($t->status) !== 'aktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                        </td>

                        {{-- Tanggal Non-aktif --}}
                        <td class="p-5 text-center text-sm text-gray-900">
                            <span class="cell-display {{ !$t->tanggal_non_aktif ? 'text-gray-400' : '' }}">
                                {{ $t->tanggal_non_aktif ? \Carbon\Carbon::parse($t->tanggal_non_aktif)->format('d/m/Y') : '—' }}
                            </span>
                            <input type="date" name="tanggal_non_aktif" value="{{ $t->tanggal_non_aktif }}"
                                class="cell-edit hidden w-full rounded-lg border border-gray-200 px-2 py-1 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                        </td>

                        {{-- Aksi --}}
                        <td class="p-5 text-center text-sm text-gray-900">
                            <div class="flex items-center justify-center gap-1">

                                {{-- Detail Anggota --}}
                               <button type="button"
                                    onclick="openModalAnggota('{{ $t->kode_tim }}', '{{ $t->nama_tim }}')"
                                    class="btn-detail p-2 rounded-full transition-all duration-500 flex items-center hover:bg-blue-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <button class="btn-edit p-2 rounded-full group transition-all duration-500 flex items-center hover:bg-indigo-50">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="fill-indigo-500" d="M9.53414 8.15675L8.96459 7.59496L8.96459 7.59496L9.53414 8.15675ZM13.8911 3.73968L13.3215 3.17789V3.17789L13.8911 3.73968ZM16.3154 3.75892L15.7367 4.31126L15.7367 4.31127L16.3154 3.75892ZM16.38 3.82658L16.9587 3.27423L16.9587 3.27423L16.38 3.82658ZM16.3401 6.13595L15.7803 5.56438L16.3401 6.13595ZM11.9186 10.4658L12.4784 11.0374L11.9186 10.4658ZM11.1223 10.9017L10.9404 10.1226V10.1226L11.1223 10.9017ZM9.07259 10.9951L8.52556 11.5788L8.52556 11.5788L9.07259 10.9951ZM9.09713 8.9664L9.87963 9.1328V9.1328L9.09713 8.9664ZM9.05721 10.9803L8.49542 11.5498H8.49542L9.05721 10.9803ZM17.1679 4.99458L16.368 4.98075V4.98075L17.1679 4.99458ZM15.1107 2.8693L15.1171 2.06932L15.1107 2.8693ZM9.22851 8.51246L8.52589 8.12992L8.52452 8.13247L9.22851 8.51246ZM9.22567 8.51772L8.52168 8.13773L8.5203 8.1403L9.22567 8.51772ZM11.5684 10.7654L11.9531 11.4668L11.9536 11.4666L11.5684 10.7654ZM11.5669 10.7662L11.9507 11.4681L11.9516 11.4676L11.5669 10.7662ZM11.3235 3.30005C11.7654 3.30005 12.1235 2.94188 12.1235 2.50005C12.1235 2.05822 11.7654 1.70005 11.3235 1.70005V3.30005ZM18.3 9.55887C18.3 9.11705 17.9418 8.75887 17.5 8.75887C17.0582 8.75887 16.7 9.11705 16.7 9.55887H18.3ZM3.47631 16.5237L4.042 15.9581H4.042L3.47631 16.5237ZM16.5237 16.5237L15.958 15.9581L15.958 15.9581L16.5237 16.5237ZM10.1037 8.71855L14.4606 4.30148L13.3215 3.17789L8.96459 7.59496L10.1037 8.71855ZM15.7367 4.31127L15.8013 4.37893L16.9587 3.27423L16.8941 3.20657L15.7367 4.31127ZM15.7803 5.56438L11.3589 9.89426L12.4784 11.0374L16.8998 6.70753L15.7803 5.56438ZM10.9404 10.1226C10.3417 10.2624 9.97854 10.3452 9.72166 10.3675C9.47476 10.3888 9.53559 10.3326 9.61962 10.4113L8.52556 11.5788C8.9387 11.966 9.45086 11.9969 9.85978 11.9615C10.2587 11.9269 10.7558 11.8088 11.3042 11.6807L10.9404 10.1226ZM8.31462 8.8C8.19986 9.33969 8.09269 9.83345 8.0681 10.2293C8.04264 10.6393 8.08994 11.1499 8.49542 11.5498L9.619 10.4107C9.70348 10.494 9.65043 10.5635 9.66503 10.3285C9.6805 10.0795 9.75378 9.72461 9.87963 9.1328L8.31462 8.8ZM9.61962 10.4113C9.61941 10.4111 9.6192 10.4109 9.619 10.4107L8.49542 11.5498C8.50534 11.5596 8.51539 11.5693 8.52556 11.5788L9.61962 10.4113ZM15.8013 4.37892C16.0813 4.67236 16.2351 4.83583 16.3279 4.96331C16.4073 5.07234 16.3667 5.05597 16.368 4.98075L17.9678 5.00841C17.9749 4.59682 17.805 4.27366 17.6213 4.02139C17.451 3.78756 17.2078 3.53522 16.9587 3.27423L15.8013 4.37892ZM16.8998 6.70753C17.1578 6.45486 17.4095 6.21077 17.5876 5.98281C17.7798 5.73698 17.9607 5.41987 17.9678 5.00841L16.368 4.98075C16.3693 4.90565 16.4103 4.8909 16.327 4.99749C16.2297 5.12196 16.0703 5.28038 15.7803 5.56438L16.8998 6.70753ZM14.4606 4.30148C14.7639 3.99402 14.9352 3.82285 15.0703 3.71873C15.1866 3.62905 15.1757 3.66984 15.1044 3.66927L15.1171 2.06932C14.6874 2.06591 14.3538 2.25081 14.0935 2.45151C13.8518 2.63775 13.5925 2.9032 13.3215 3.17789L14.4606 4.30148ZM16.8941 3.20657C16.6279 2.92765 16.373 2.65804 16.1345 2.46792C15.8774 2.26298 15.5468 2.07273 15.1171 2.06932L15.1044 3.66927C15.033 3.66871 15.0226 3.62768 15.1372 3.71904C15.2704 3.82522 15.4387 3.999 15.7367 4.31126L16.8941 3.20657ZM8.96459 7.59496C8.82923 7.73218 8.64795 7.90575 8.5259 8.12993L9.93113 8.895C9.92075 8.91406 9.91465 8.91711 9.93926 8.88927C9.97002 8.85445 10.0145 8.80893 10.1037 8.71854L8.96459 7.59496ZM9.87963 9.1328C9.9059 9.00925 9.91925 8.94785 9.93124 8.90366C9.94073 8.86868 9.94137 8.87585 9.93104 8.89515L8.5203 8.1403C8.39951 8.36605 8.35444 8.61274 8.31462 8.8L9.87963 9.1328ZM8.52452 8.13247L8.52168 8.13773L9.92967 8.89772L9.9325 8.89246L8.52452 8.13247ZM11.3589 9.89426C11.27 9.98132 11.2252 10.0248 11.1909 10.055C11.1635 10.0791 11.1658 10.0738 11.1832 10.0642L11.9536 11.4666C12.1727 11.3462 12.3427 11.1703 12.4784 11.0374L11.3589 9.89426ZM11.3042 11.6807C11.4912 11.6371 11.7319 11.5878 11.9507 11.4681L11.1831 10.0643C11.2007 10.0547 11.206 10.0557 11.1697 10.0663C11.1248 10.0793 11.0628 10.0941 10.9404 10.1226L11.3042 11.6807ZM11.1837 10.064L11.1822 10.0648L11.9516 11.4676L11.9531 11.4668L11.1837 10.064ZM16.399 6.10097L13.8984 3.60094L12.7672 4.73243L15.2677 7.23246L16.399 6.10097ZM10.8333 16.7001H9.16667V18.3001H10.8333V16.7001ZM3.3 10.8334V9.16672H1.7V10.8334H3.3ZM9.16667 3.30005H11.3235V1.70005H9.16667V3.30005ZM16.7 9.55887V10.8334H18.3V9.55887H16.7ZM9.16667 16.7001C7.5727 16.7001 6.45771 16.6984 5.61569 16.5851C4.79669 16.475 4.35674 16.2728 4.042 15.9581L2.91063 17.0894C3.5722 17.751 4.40607 18.0369 5.4025 18.1709C6.37591 18.3018 7.61793 18.3001 9.16667 18.3001V16.7001ZM1.7 10.8334C1.7 12.3821 1.6983 13.6241 1.82917 14.5976C1.96314 15.594 2.24905 16.4279 2.91063 17.0894L4.042 15.9581C3.72726 15.6433 3.52502 15.2034 3.41491 14.3844C3.3017 13.5423 3.3 12.4273 3.3 10.8334H1.7ZM10.8333 18.3001C12.3821 18.3001 13.6241 18.3018 14.5975 18.1709C15.5939 18.0369 16.4278 17.751 17.0894 17.0894L15.958 15.9581C15.6433 16.2728 15.2033 16.475 14.3843 16.5851C13.5423 16.6984 12.4273 16.7001 10.8333 16.7001V18.3001ZM16.7 10.8334C16.7 12.4274 16.6983 13.5423 16.5851 14.3844C16.475 15.2034 16.2727 15.6433 15.958 15.9581L17.0894 17.0894C17.7509 16.4279 18.0369 15.594 18.1708 14.5976C18.3017 13.6241 18.3 12.3821 18.3 10.8334H16.7ZM3.3 9.16672C3.3 7.57275 3.3017 6.45776 3.41491 5.61574C3.52502 4.79674 3.72726 4.35679 4.042 4.04205L2.91063 2.91068C2.24905 3.57225 1.96314 4.40612 1.82917 5.40255C1.6983 6.37596 1.7 7.61798 1.7 9.16672H3.3ZM9.16667 1.70005C7.61793 1.70005 6.37591 1.69835 5.4025 1.82922C4.40607 1.96319 3.5722 2.24911 2.91063 2.91068L4.042 4.04205C4.35674 3.72731 4.79669 3.52507 5.61569 3.41496C6.45771 3.30175 7.5727 3.30005 9.16667 3.30005V1.70005Z" fill="#818CF8"/>
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button class="btn-delete p-2 rounded-full group transition-all duration-500 flex items-center hover:bg-red-50">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="fill-red-600" d="M4.00031 5.49999V4.69999H3.20031V5.49999H4.00031ZM16.0003 5.49999H16.8003V4.69999H16.0003V5.49999ZM17.5003 5.49999L17.5003 6.29999C17.9421 6.29999 18.3003 5.94183 18.3003 5.5C18.3003 5.05817 17.9421 4.7 17.5003 4.69999L17.5003 5.49999ZM9.30029 9.24997C9.30029 8.80814 8.94212 8.44997 8.50029 8.44997C8.05847 8.44997 7.70029 8.80814 7.70029 9.24997H9.30029ZM7.70029 13.75C7.70029 14.1918 8.05847 14.55 8.50029 14.55C8.94212 14.55 9.30029 14.1918 9.30029 13.75H7.70029ZM12.3004 9.24997C12.3004 8.80814 11.9422 8.44997 11.5004 8.44997C11.0585 8.44997 10.7004 8.80814 10.7004 9.24997H12.3004ZM10.7004 13.75C10.7004 14.1918 11.0585 14.55 11.5004 14.55C11.9422 14.55 12.3004 14.1918 12.3004 13.75H10.7004ZM4.00031 6.29999H16.0003V4.69999H4.00031V6.29999ZM15.2003 5.49999V12.5H16.8003V5.49999H15.2003ZM11.0003 16.7H9.00031V18.3H11.0003V16.7ZM4.80031 12.5V5.49999H3.20031V12.5H4.80031ZM9.00031 16.7C7.79918 16.7 6.97882 16.6983 6.36373 16.6156C5.77165 16.536 5.49093 16.3948 5.29823 16.2021L4.16686 17.3334C4.70639 17.873 5.38104 18.0979 6.15053 18.2013C6.89702 18.3017 7.84442 18.3 9.00031 18.3V16.7ZM3.20031 12.5C3.20031 13.6559 3.19861 14.6033 3.29897 15.3498C3.40243 16.1193 3.62733 16.7939 4.16686 17.3334L5.29823 16.2021C5.10553 16.0094 4.96431 15.7286 4.88471 15.1366C4.80201 14.5215 4.80031 13.7011 4.80031 12.5H3.20031ZM15.2003 12.5C15.2003 13.7011 15.1986 14.5215 15.1159 15.1366C15.0363 15.7286 14.8951 16.0094 14.7024 16.2021L15.8338 17.3334C16.3733 16.7939 16.5982 16.1193 16.7016 15.3498C16.802 14.6033 16.8003 13.6559 16.8003 12.5H15.2003ZM11.0003 18.3C12.1562 18.3 13.1036 18.3017 13.8501 18.2013C14.6196 18.0979 15.2942 17.873 15.8338 17.3334L14.7024 16.2021C14.5097 16.3948 14.229 16.536 13.6369 16.6156C13.0218 16.6983 12.2014 16.7 11.0003 16.7V18.3ZM2.50031 4.69999C2.22572 4.7 2.04405 4.7 1.94475 4.7C1.89511 4.7 1.86604 4.7 1.85624 4.7C1.85471 4.7 1.85206 4.7 1.851 4.7C1.05253 5.50059 1.85233 6.3 1.85256 6.3C1.85273 6.3 1.85297 6.3 1.85327 6.3C1.85385 6.3 1.85472 6.3 1.85587 6.3C1.86047 6.3 1.86972 6.3 1.88345 6.3C1.99328 6.3 2.39045 6.3 2.9906 6.3C4.19091 6.3 6.2032 6.3 8.35279 6.3C10.5024 6.3 12.7893 6.3 14.5387 6.3C15.4135 6.3 16.1539 6.3 16.6756 6.3C16.9364 6.3 17.1426 6.29999 17.2836 6.29999C17.3541 6.29999 17.4083 6.29999 17.4448 6.29999C17.4631 6.29999 17.477 6.29999 17.4863 6.29999C17.4909 6.29999 17.4944 6.29999 17.4968 6.29999C17.498 6.29999 17.4988 6.29999 17.4994 6.29999C17.4997 6.29999 17.4999 6.29999 17.5001 6.29999C17.5002 6.29999 17.5003 6.29999 17.5003 5.49999C17.5003 4.69999 17.5002 4.69999 17.5001 4.69999C17.4999 4.69999 17.4997 4.69999 17.4994 4.69999C17.4988 4.69999 17.498 4.69999 17.4968 4.69999C17.4944 4.69999 17.4909 4.69999 17.4863 4.69999C17.477 4.69999 17.4631 4.69999 17.4448 4.69999C17.4083 4.69999 17.3541 4.69999 17.2836 4.69999C17.1426 4.7 16.9364 4.7 16.6756 4.7C16.1539 4.7 15.4135 4.7 14.5387 4.7C12.7893 4.7 10.5024 4.7 8.35279 4.7C6.2032 4.7 4.19091 4.7 2.9906 4.7C2.39044 4.7 1.99329 4.7 1.88347 4.7C1.86974 4.7 1.86051 4.7 1.85594 4.7C1.8548 4.7 1.85396 4.7 1.85342 4.7C1.85315 4.7 1.85298 4.7 1.85288 4.7C1.85284 4.7 2.65253 5.49941 1.85408 6.3C1.85314 6.3 1.85296 6.3 1.85632 6.3C1.86608 6.3 1.89511 6.3 1.94477 6.3C2.04406 6.3 2.22573 6.3 2.50031 6.29999L2.50031 4.69999ZM7.05028 5.49994V4.16661H5.45028V5.49994H7.05028ZM7.91695 3.29994H12.0836V1.69994H7.91695V3.29994ZM12.9503 4.16661V5.49994H14.5503V4.16661H12.9503ZM12.0836 3.29994C12.5623 3.29994 12.9503 3.68796 12.9503 4.16661H14.5503C14.5503 2.8043 13.4459 1.69994 12.0836 1.69994V3.29994ZM7.05028 4.16661C7.05028 3.68796 7.4383 3.29994 7.91695 3.29994V1.69994C6.55465 1.69994 5.45028 2.8043 5.45028 4.16661H7.05028ZM2.50031 6.29999C4.70481 6.29998 6.40335 6.29998 8.1253 6.29997C9.84725 6.29996 11.5458 6.29995 13.7503 6.29994L13.7503 4.69994C11.5458 4.69995 9.84724 4.69996 8.12529 4.69997C6.40335 4.69998 4.7048 4.69998 2.50031 4.69999L2.50031 6.29999ZM13.7503 6.29994L17.5003 6.29999L17.5003 4.69999L13.7503 4.69994L13.7503 6.29994ZM7.70029 9.24997V13.75H9.30029V9.24997H7.70029ZM10.7004 9.24997V13.75H12.3004V9.24997H10.7004Z" fill="#F87171"/>
                                    </svg>
                                </button>

                                {{-- Simpan --}}
                                <button type="button"
                                    class="btn-save hidden h-9 w-9 shrink-0 items-center justify-center rounded-full transition-all duration-300 hover:bg-green-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>

                                {{-- Batal --}}
                                <button type="button"
                                    class="btn-cancel hidden h-9 w-9 shrink-0 items-center justify-center rounded-full transition-all duration-300 hover:bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-sm text-gray-400">Belum ada data tim.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="flex justify-center mt-6 px-4">
    <nav class="inline-flex items-center gap-2 rounded bg-white p-1">

        {{-- Prev --}}
        @if ($tim->onFirstPage())
            <span class="rounded border p-1 text-gray-300 cursor-not-allowed">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </span>
        @else
            <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
               href="{{ $tim->appends(request()->only('search'))->previousPageUrl() }}">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </a>
        @endif

        <p class="text-sm text-gray-500 whitespace-nowrap">
            Page {{ $tim->currentPage() }} of {{ $tim->lastPage() }}
        </p>

        {{-- Next --}}
        @if ($tim->hasMorePages())
            <a class="rounded border bg-white p-1 text-black hover:border-[#faa938] hover:bg-[#faa938] hover:text-white"
               href="{{ $tim->appends(request()->only('search'))->nextPageUrl() }}">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </a>
        @else
            <span class="rounded border p-1 text-gray-300 cursor-not-allowed">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </span>
        @endif

    </nav>
</div>

{{-- Modal Tambah Tim --}}
<div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
    <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-5 shadow-xl sm:p-6">

        <h2 class="mb-4 text-base font-semibold text-gray-800">Tambah Tim Baru</h2>

        <div class="space-y-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Kode Tim <span class="text-red-400">*</span>
                </label>
                <input type="text" id="new_kode_tim" placeholder="Contoh: TIM-001"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">
                    Nama Tim <span class="text-red-400">*</span>
                </label>
                <input type="text" id="new_nama_tim" placeholder="Nama tim"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Nama Ketua</label>
                <input type="text" id="new_nama_ketua" placeholder="Nama ketua tim"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">NIP Lama Ketua</label>
                    <input type="text" id="new_niplama_ketua"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">NIP Baru Ketua</label>
                    <input type="text" id="new_nipbaru_ketua"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                    <select id="new_status"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non-aktif</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Penugasan Khusus</label>
                    <select id="new_is_penugasan_khusus"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Tanggal Non-aktif</label>
                <input type="date" id="new_tanggal_non_aktif"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeCreateModal()"
                class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-all hover:bg-gray-50 sm:w-auto">
                Batal
            </button>

            <button type="button" onclick="submitCreateTim()"
                class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm text-white transition-all hover:brightness-95 sm:w-auto">
                Simpan
            </button>
        </div>

    </div>
</div>

{{-- Modal Anggota Tim --}}
<div id="modalAnggota" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
    <div class="flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl bg-white p-5 shadow-xl sm:p-6">

        {{-- Header --}}
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-gray-800" id="modalAnggotaTitle">Anggota Tim</h2>
                <p class="text-xs text-gray-400" id="modalAnggotaSubtitle"></p>
            </div>

            <button type="button" onclick="closeModalAnggota()"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition-all hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Panel Anggota --}}
        <div id="panelAnggota" class="flex flex-1 flex-col overflow-hidden">

            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" id="searchAnggota" placeholder="Cari nama anggota..."
                    oninput="filterAnggota()"
                    class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-4 pr-3 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>

                <button type="button" onclick="showPanelTambah()"
                    class="inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-xl bg-[#faa938] px-3 text-sm text-white transition-all hover:brightness-95 sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah
                </button>
            </div>

            <div class="flex-1 overflow-x-auto overflow-y-auto rounded-xl border border-gray-100">
                <table class="w-full min-w-[520px]">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-4 text-left text-xs font-semibold text-gray-600">Nama</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600">NIP</th>
                            <th class="p-4 text-center text-xs font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAnggota" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="3" class="p-6 text-center text-sm text-gray-400">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="paginasiAnggota" class="mt-3 flex items-center justify-center gap-2"></div>
        </div>

        {{-- Panel Tambah Anggota --}}
        <div id="panelTambah" class="hidden flex-1 flex-col overflow-hidden">

            <div class="mb-3 flex items-center gap-2">
                <button type="button" onclick="showPanelAnggota()"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition-all hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19 8 12l7-7"/>
                    </svg>
                </button>

                <input type="text" id="searchPegawai" placeholder="Cari nama pegawai..."
                    oninput="filterPegawai()"
                    class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-4 pr-3 text-sm text-gray-700 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"/>
            </div>

            <div class="flex-1 overflow-x-auto overflow-y-auto rounded-xl border border-gray-100">
                <table class="w-full min-w-[520px]">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-4 text-left text-xs font-semibold text-gray-600">Nama</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600">NIP</th>
                            <th class="p-4 text-center text-xs font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPegawai" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="3" class="p-6 text-center text-sm text-gray-400">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="paginasiPegawai" class="mt-3 flex items-center justify-center gap-2"></div>
        </div>

    </div>
</div>

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';

let cachedTim = [];
let currentKodeTim = null;
let anggotaPage = 1;
let pegawaiPage = 1;
let searchAnggotaTimer;
let searchPegawaiTimer;

// =====================
// HELPER
// =====================
function showAlert(message, type = 'success') {
    const box = document.getElementById('alertBox');
    const inner = document.getElementById('alertInner');

    inner.textContent = message;
    inner.className = type === 'success'
        ? 'rounded-xl px-4 py-3 text-sm font-medium bg-green-50 text-green-700 border border-green-200'
        : 'rounded-xl px-4 py-3 text-sm font-medium bg-red-50 text-red-700 border border-red-200';

    box.classList.remove('hidden');

    setTimeout(() => {
        box.classList.add('hidden');
    }, 3000);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatDateId(value) {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function showButton(button) {
    if (!button) return;
    button.classList.remove('hidden');
    button.classList.add('inline-flex');
}

function hideButton(button) {
    if (!button) return;
    button.classList.add('hidden');
    button.classList.remove('inline-flex');
}

// =====================
// DROPDOWN TIM
// =====================
async function fetchTim() {
    try {
        const res = await fetch('{{ route("admin.tim.store") }}', {
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();

        cachedTim = Array.isArray(data)
            ? data
            : Array.isArray(data.data)
                ? data.data
                : [];

        populateDropdownTim('', cachedTim);
    } catch (e) {
        console.error('Gagal fetch tim:', e);
    }
}

function populateDropdownTim(filter = '', data = []) {
    const list = document.getElementById('listTim');
    list.innerHTML = '';

    const filtered = data.filter(tim =>
        String(tim.nama_tim ?? '').toLowerCase().includes(filter.toLowerCase())
    );

    if (filtered.length === 0) {
        list.innerHTML = '<li class="px-4 py-2 text-sm text-gray-400">Tidak ditemukan</li>';
        return;
    }

    filtered.forEach(tim => {
        const li = document.createElement('li');
        li.className = 'cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-50';
        li.textContent = tim.nama_tim ?? '—';
        li.onclick = () => pilihTim(tim);
        list.appendChild(li);
    });
}

window.toggleDropdownTim = function () {
    const dropdown = document.getElementById('dropdownTim');
    dropdown.classList.toggle('hidden');

    populateDropdownTim(document.getElementById('searchTim').value, cachedTim);
};

window.filterDropdownTim = function () {
    const search = document.getElementById('searchTim').value;
    const dropdown = document.getElementById('dropdownTim');

    populateDropdownTim(search, cachedTim);
    dropdown.classList.remove('hidden');

    if (search === '') {
        window.location.href = '{{ route("admin.tim") }}';
    }
};

window.pilihTim = function (tim) {
    document.getElementById('searchTim').value = tim.nama_tim ?? '';
    document.getElementById('dropdownTim').classList.add('hidden');

    window.location.href = '{{ route("admin.tim") }}?search=' + encodeURIComponent(tim.nama_tim ?? '');
};

// =====================
// MODAL TAMBAH TIM
// =====================
window.openCreateModal = function () {
    const modal = document.getElementById('createModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.closeCreateModal = function () {
    const modal = document.getElementById('createModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

window.submitCreateTim = async function () {
    const payload = {
        kode_tim: document.getElementById('new_kode_tim').value.trim(),
        nama_tim: document.getElementById('new_nama_tim').value.trim(),
        nama_ketua: document.getElementById('new_nama_ketua').value.trim(),
        niplama_ketua: document.getElementById('new_niplama_ketua').value.trim(),
        nipbaru_ketua: document.getElementById('new_nipbaru_ketua').value.trim(),
        status: document.getElementById('new_status').value,
        is_penugasan_khusus: document.getElementById('new_is_penugasan_khusus').value,
        tanggal_non_aktif: document.getElementById('new_tanggal_non_aktif').value || null,
    };

    if (!payload.kode_tim || !payload.nama_tim) {
        showAlert('Kode Tim dan Nama Tim wajib diisi.', 'error');
        return;
    }

    try {
        const res = await fetch('{{ route("admin.tim.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (res.ok) {
            showAlert(data.message ?? 'Tim berhasil ditambahkan');
            closeCreateModal();
            setTimeout(() => location.reload(), 800);
            return;
        }

        const errors = data.errors
            ? Object.values(data.errors).flat().join(', ')
            : data.message ?? 'Terjadi kesalahan';

        showAlert(errors, 'error');
    } catch {
        showAlert('Gagal menghubungi server.', 'error');
    }
};

// =====================
// INLINE EDIT + DELETE
// =====================
function initInlineActions() {
    document.querySelectorAll('tbody tr[data-kode]').forEach(row => {
        const kode = row.dataset.kode;
        const btnDetail = row.querySelector('.btn-detail');
        const btnEdit = row.querySelector('.btn-edit');
        const btnSave = row.querySelector('.btn-save');
        const btnCancel = row.querySelector('.btn-cancel');
        const btnDelete = row.querySelector('.btn-delete');

        btnEdit?.addEventListener('click', () => {
            row.querySelectorAll('.cell-edit').forEach(input => {
                input.dataset.original = input.value;
            });

            row.querySelectorAll('.cell-display').forEach(display => {
                display.classList.add('hidden');
            });

            row.querySelectorAll('.cell-edit').forEach(input => {
                input.classList.remove('hidden');
            });

            hideButton(btnDetail);
            hideButton(btnEdit);
            hideButton(btnDelete);
            showButton(btnSave);
            showButton(btnCancel);
        });

        btnCancel?.addEventListener('click', () => {
            row.querySelectorAll('.cell-edit').forEach(input => {
                if (input.dataset.original !== undefined) {
                    input.value = input.dataset.original;
                }
            });

            exitEdit(row, btnDetail, btnEdit, btnSave, btnCancel, btnDelete);
        });

        btnSave?.addEventListener('click', async () => {
            const payload = {};

            row.querySelectorAll('.cell-edit').forEach(input => {
                if (input.name) payload[input.name] = input.value;
            });

            try {
                const res = await fetch(`/admin/tim/${kode}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok) {
                    showAlert(data.message ?? 'Gagal menyimpan.', 'error');
                    return;
                }

                row.querySelectorAll('.cell-edit').forEach(input => {
                    if (!input.name) return;

                    const display = input.closest('td')?.querySelector('.cell-display');
                    if (!display) return;

                    if (input.name === 'is_penugasan_khusus') {
                        display.textContent = input.value == '1' ? 'Ya' : 'Tidak';
                    } else if (input.name === 'status') {
                        display.textContent = input.value === 'aktif' ? 'Aktif' : 'Non-aktif';
                    } else if (input.name === 'tanggal_non_aktif') {
                        display.textContent = formatDateId(input.value);
                        display.classList.toggle('text-gray-400', !input.value);
                    } else {
                        display.textContent = input.value || '—';
                    }
                });

                exitEdit(row, btnDetail, btnEdit, btnSave, btnCancel, btnDelete);
                showAlert(data.message ?? 'Tim berhasil diupdate');
            } catch {
                showAlert('Gagal menghubungi server.', 'error');
            }
        });

        btnDelete?.addEventListener('click', async () => {
            const nama = row.querySelector('.cell-display')?.textContent.trim() || 'tim ini';

            if (!confirm(`Hapus tim "${nama}"?`)) return;

            try {
                const res = await fetch(`/admin/master/tim/${kode}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();

                if (res.ok) {
                    row.remove();
                    showAlert(data.message ?? 'Tim berhasil dihapus');
                    return;
                }

                showAlert(data.message ?? 'Gagal menghapus.', 'error');
            } catch {
                showAlert('Gagal menghubungi server.', 'error');
            }
        });
    });
}

function exitEdit(row, btnDetail, btnEdit, btnSave, btnCancel, btnDelete) {
    row.querySelectorAll('.cell-display').forEach(display => {
        display.classList.remove('hidden');
    });

    row.querySelectorAll('.cell-edit').forEach(input => {
        input.classList.add('hidden');
    });

    showButton(btnDetail);
    showButton(btnEdit);
    showButton(btnDelete);
    hideButton(btnSave);
    hideButton(btnCancel);
}

// =====================
// MODAL ANGGOTA
// =====================
window.openModalAnggota = function (kodeTim, namaTim) {
    currentKodeTim = kodeTim;
    anggotaPage = 1;
    pegawaiPage = 1;

    document.getElementById('modalAnggotaTitle').textContent = namaTim ?? 'Anggota Tim';

    const modal = document.getElementById('modalAnggota');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    showPanelAnggota();
    loadAnggota(1);
};

window.closeModalAnggota = function () {
    const modal = document.getElementById('modalAnggota');
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.getElementById('searchAnggota').value = '';
    document.getElementById('searchPegawai').value = '';

    currentKodeTim = null;
};

window.showPanelAnggota = function () {
    document.getElementById('panelAnggota').classList.remove('hidden');
    document.getElementById('panelAnggota').classList.add('flex');

    document.getElementById('panelTambah').classList.add('hidden');
    document.getElementById('panelTambah').classList.remove('flex');

    if (currentKodeTim) loadAnggota(anggotaPage);
};

window.showPanelTambah = function () {
    document.getElementById('panelTambah').classList.remove('hidden');
    document.getElementById('panelTambah').classList.add('flex');

    document.getElementById('panelAnggota').classList.add('hidden');
    document.getElementById('panelAnggota').classList.remove('flex');

    if (currentKodeTim) loadPegawaiTersedia(1);
};

async function loadAnggota(page = 1) {
    if (!currentKodeTim) return;

    anggotaPage = page;

    const search = document.getElementById('searchAnggota').value;
    const tbody = document.getElementById('tbodyAnggota');

    tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-gray-400">Memuat...</td></tr>';

    try {
        const res = await fetch(`/admin/tim/${currentKodeTim}/anggota?page=${page}&search=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();
        const anggota = data.data ?? [];

        if (anggota.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-gray-400">Belum ada anggota.</td></tr>';
        } else {
            tbody.innerHTML = anggota.map(a => `
                <tr class="hover:bg-gray-50">
                    <td class="p-4 text-sm text-gray-800">${escapeHtml(a.nama ?? '—')}</td>
                    <td class="p-4 text-sm text-gray-500">${escapeHtml(a.nip ?? '—')}</td>
                    <td class="p-4 text-center">
                        <button type="button"
                            data-pegawai-id="${escapeHtml(a.id_pegawai)}"
                            data-nama="${escapeHtml(a.nama ?? '')}"
                            class="btn-hapus-anggota inline-flex h-8 w-8 items-center justify-center rounded-full transition-all hover:bg-red-50">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="fill-red-600" d="M4.00031 5.49999V4.69999H3.20031V5.49999H4.00031ZM16.0003 5.49999H16.8003V4.69999H16.0003V5.49999ZM17.5003 5.49999L17.5003 6.29999C17.9421 6.29999 18.3003 5.94183 18.3003 5.5C18.3003 5.05817 17.9421 4.7 17.5003 4.69999L17.5003 5.49999ZM9.30029 9.24997C9.30029 8.80814 8.94212 8.44997 8.50029 8.44997C8.05847 8.44997 7.70029 8.80814 7.70029 9.24997H9.30029ZM7.70029 13.75C7.70029 14.1918 8.05847 14.55 8.50029 14.55C8.94212 14.55 9.30029 14.1918 9.30029 13.75H7.70029ZM12.3004 9.24997C12.3004 8.80814 11.9422 8.44997 11.5004 8.44997C11.0585 8.44997 10.7004 8.80814 10.7004 9.24997H12.3004ZM10.7004 13.75C10.7004 14.1918 11.0585 14.55 11.5004 14.55C11.9422 14.55 12.3004 14.1918 12.3004 13.75H10.7004ZM4.00031 6.29999H16.0003V4.69999H4.00031V6.29999ZM15.2003 5.49999V12.5H16.8003V5.49999H15.2003ZM11.0003 16.7H9.00031V18.3H11.0003V16.7ZM4.80031 12.5V5.49999H3.20031V12.5H4.80031ZM9.00031 16.7C7.79918 16.7 6.97882 16.6983 6.36373 16.6156C5.77165 16.536 5.49093 16.3948 5.29823 16.2021L4.16686 17.3334C4.70639 17.873 5.38104 18.0979 6.15053 18.2013C6.89702 18.3017 7.84442 18.3 9.00031 18.3V16.7ZM3.20031 12.5C3.20031 13.6559 3.19861 14.6033 3.29897 15.3498C3.40243 16.1193 3.62733 16.7939 4.16686 17.3334L5.29823 16.2021C5.10553 16.0094 4.96431 15.7286 4.88471 15.1366C4.80201 14.5215 4.80031 13.7011 4.80031 12.5H3.20031ZM15.2003 12.5C15.2003 13.7011 15.1986 14.5215 15.1159 15.1366C15.0363 15.7286 14.8951 16.0094 14.7024 16.2021L15.8338 17.3334C16.3733 16.7939 16.5982 16.1193 16.7016 15.3498C16.802 14.6033 16.8003 13.6559 16.8003 12.5H15.2003ZM11.0003 18.3C12.1562 18.3 13.1036 18.3017 13.8501 18.2013C14.6196 18.0979 15.2942 17.873 15.8338 17.3334L14.7024 16.2021C14.5097 16.3948 14.229 16.536 13.6369 16.6156C13.0218 16.6983 12.2014 16.7 11.0003 16.7V18.3ZM2.50031 4.69999C2.22572 4.7 2.04405 4.7 1.94475 4.7C1.89511 4.7 1.86604 4.7 1.85624 4.7C1.85471 4.7 1.85206 4.7 1.851 4.7C1.05253 5.50059 1.85233 6.3 1.85256 6.3C1.85273 6.3 1.85297 6.3 1.85327 6.3C1.85385 6.3 1.85472 6.3 1.85587 6.3C1.86047 6.3 1.86972 6.3 1.88345 6.3C1.99328 6.3 2.39045 6.3 2.9906 6.3C4.19091 6.3 6.2032 6.3 8.35279 6.3C10.5024 6.3 12.7893 6.3 14.5387 6.3C15.4135 6.3 16.1539 6.3 16.6756 6.3C16.9364 6.3 17.1426 6.29999 17.2836 6.29999C17.3541 6.29999 17.4083 6.29999 17.4448 6.29999C17.4631 6.29999 17.477 6.29999 17.4863 6.29999C17.4909 6.29999 17.4944 6.29999 17.4968 6.29999C17.498 6.29999 17.4988 6.29999 17.4994 6.29999C17.4997 6.29999 17.4999 6.29999 17.5001 6.29999C17.5002 6.29999 17.5003 6.29999 17.5003 5.49999C17.5003 4.69999 17.5002 4.69999 17.5001 4.69999C17.4999 4.69999 17.4997 4.69999 17.4994 4.69999C17.4988 4.69999 17.498 4.69999 17.4968 4.69999C17.4944 4.69999 17.4909 4.69999 17.4863 4.69999C17.477 4.69999 17.4631 4.69999 17.4448 4.69999C17.4083 4.69999 17.3541 4.69999 17.2836 4.69999C17.1426 4.7 16.9364 4.7 16.6756 4.7C16.1539 4.7 15.4135 4.7 14.5387 4.7C12.7893 4.7 10.5024 4.7 8.35279 4.7C6.2032 4.7 4.19091 4.7 2.9906 4.7C2.39044 4.7 1.99329 4.7 1.88347 4.7C1.86974 4.7 1.86051 4.7 1.85594 4.7C1.8548 4.7 1.85396 4.7 1.85342 4.7C1.85315 4.7 1.85298 4.7 1.85288 4.7C1.85284 4.7 2.65253 5.49941 1.85408 6.3C1.85314 6.3 1.85296 6.3 1.85632 6.3C1.86608 6.3 1.89511 6.3 1.94477 6.3C2.04406 6.3 2.22573 6.3 2.50031 6.29999L2.50031 4.69999ZM7.05028 5.49994V4.16661H5.45028V5.49994H7.05028ZM7.91695 3.29994H12.0836V1.69994H7.91695V3.29994ZM12.9503 4.16661V5.49994H14.5503V4.16661H12.9503ZM12.0836 3.29994C12.5623 3.29994 12.9503 3.68796 12.9503 4.16661H14.5503C14.5503 2.8043 13.4459 1.69994 12.0836 1.69994V3.29994ZM7.05028 4.16661C7.05028 3.68796 7.4383 3.29994 7.91695 3.29994V1.69994C6.55465 1.69994 5.45028 2.8043 5.45028 4.16661H7.05028ZM2.50031 6.29999C4.70481 6.29998 6.40335 6.29998 8.1253 6.29997C9.84725 6.29996 11.5458 6.29995 13.7503 6.29994L13.7503 4.69994C11.5458 4.69995 9.84724 4.69996 8.12529 4.69997C6.40335 4.69998 4.7048 4.69998 2.50031 4.69999L2.50031 6.29999ZM13.7503 6.29994L17.5003 6.29999L17.5003 4.69999L13.7503 4.69994L13.7503 6.29994ZM7.70029 9.24997V13.75H9.30029V9.24997H7.70029ZM10.7004 9.24997V13.75H12.3004V9.24997H10.7004Z" fill="#F87171"/>
                                    </svg>
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.querySelectorAll('.btn-hapus-anggota').forEach(button => {
                button.addEventListener('click', () => {
                    hapusAnggota(button.dataset.pegawaiId, button.dataset.nama);
                });
            });
        }

        renderPaginasi('paginasiAnggota', data, loadAnggota);
    } catch {
        tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-red-400">Gagal memuat data.</td></tr>';
    }
}

window.filterAnggota = function () {
    clearTimeout(searchAnggotaTimer);
    searchAnggotaTimer = setTimeout(() => loadAnggota(1), 400);
};

async function hapusAnggota(pegawaiId, nama) {
    if (!confirm(`Hapus "${nama}" dari tim ini?`)) return;

    try {
        const res = await fetch(`/admin/tim/${currentKodeTim}/anggota`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ pegawai_id: pegawaiId }),
        });

        const data = await res.json();

        if (res.ok) {
            showAlert(data.message ?? 'Anggota dihapus');
            loadAnggota(anggotaPage);
            return;
        }

        showAlert(data.message ?? 'Gagal menghapus.', 'error');
    } catch {
        showAlert('Gagal menghubungi server.', 'error');
    }
}

async function loadPegawaiTersedia(page = 1) {
    if (!currentKodeTim) return;

    pegawaiPage = page;

    const search = document.getElementById('searchPegawai').value;
    const tbody = document.getElementById('tbodyPegawai');

    tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-gray-400">Memuat...</td></tr>';

    try {
        const res = await fetch(`/admin/tim/${currentKodeTim}/pegawai-tersedia?page=${page}&search=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();
        const pegawai = data.data ?? [];

        if (pegawai.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-gray-400">Semua pegawai sudah jadi anggota.</td></tr>';
        } else {
            tbody.innerHTML = pegawai.map(p => `
                <tr class="hover:bg-gray-50">
                    <td class="p-4 text-sm text-gray-800">${escapeHtml(p.nama ?? '—')}</td>
                    <td class="p-4 text-sm text-gray-500">${escapeHtml(p.nip ?? '—')}</td>
                    <td class="p-4 text-center">
                        <button type="button"
                            data-pegawai-id="${escapeHtml(p.id_pegawai)}"
                            data-nama="${escapeHtml(p.nama ?? '')}"
                            class="btn-tambah-anggota rounded-lg bg-[#faa938] px-3 py-1 text-xs text-white transition-all hover:brightness-95">
                            Tambah
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.querySelectorAll('.btn-tambah-anggota').forEach(button => {
                button.addEventListener('click', () => {
                    tambahAnggota(button.dataset.pegawaiId, button.dataset.nama);
                });
            });
        }

        renderPaginasi('paginasiPegawai', data, loadPegawaiTersedia);
    } catch {
        tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-sm text-red-400">Gagal memuat data.</td></tr>';
    }
}

window.filterPegawai = function () {
    clearTimeout(searchPegawaiTimer);
    searchPegawaiTimer = setTimeout(() => loadPegawaiTersedia(1), 400);
};

async function tambahAnggota(pegawaiId, nama) {
    try {
        const res = await fetch(`/admin/tim/${currentKodeTim}/anggota`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ pegawai_id: pegawaiId }),
        });

        const data = await res.json();

        if (res.ok) {
            showAlert(data.message ?? 'Anggota ditambahkan');
            loadPegawaiTersedia(pegawaiPage);
            return;
        }

        showAlert(data.message ?? 'Gagal menambahkan.', 'error');
    } catch {
        showAlert('Gagal menghubungi server.', 'error');
    }
}

// =====================
// PAGINATION MODAL
// =====================
function renderPaginasi(elId, data, loadFn) {
    const el = document.getElementById(elId);
    el.innerHTML = '';

    const currentPage = data.current_page ?? 1;
    const lastPage = data.last_page ?? 1;

    if (lastPage <= 1) return;

    const prev = document.createElement('button');
    prev.type = 'button';
    prev.innerHTML = '&lt;';
    prev.disabled = currentPage <= 1;
    prev.className = currentPage <= 1
        ? 'h-7 w-7 rounded border text-xs text-gray-300 cursor-not-allowed'
        : 'h-7 w-7 rounded border text-xs text-gray-600 hover:bg-[#faa938] hover:text-white hover:border-[#faa938]';
    prev.onclick = () => loadFn(currentPage - 1);

    const info = document.createElement('span');
    info.className = 'text-xs text-gray-500';
    info.textContent = `${currentPage} / ${lastPage}`;

    const next = document.createElement('button');
    next.type = 'button';
    next.innerHTML = '&gt;';
    next.disabled = currentPage >= lastPage;
    next.className = currentPage >= lastPage
        ? 'h-7 w-7 rounded border text-xs text-gray-300 cursor-not-allowed'
        : 'h-7 w-7 rounded border text-xs text-gray-600 hover:bg-[#faa938] hover:text-white hover:border-[#faa938]';
    next.onclick = () => loadFn(currentPage + 1);

    el.appendChild(prev);
    el.appendChild(info);
    el.appendChild(next);
}

// =====================
// INIT
// =====================
document.addEventListener('DOMContentLoaded', function () {
    fetchTim();
    initInlineActions();

    document.addEventListener('click', function (e) {
        const searchWrapper = document.getElementById('searchTim')?.closest('.relative');
        const dropdown = document.getElementById('dropdownTim');

        if (searchWrapper && !searchWrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    document.getElementById('createModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeCreateModal();
    });

    document.getElementById('modalAnggota')?.addEventListener('click', function (e) {
        if (e.target === this) closeModalAnggota();
    });
});

window.syncTim = async function () {
    if (!confirm('Tarik data tim & anggota dari API? Proses ini bisa beberapa saat.')) return;
    showAlert('Memproses sinkronisasi...', 'success');
    try {
        const res = await fetch('{{ route("admin.tim.sync") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        const data = await res.json();
        showAlert(data.message, res.ok ? 'success' : 'error');
        if (res.ok) setTimeout(() => location.reload(), 1000);
    } catch {
        showAlert('Gagal menghubungi server.', 'error');
    }
};
</script>

@endsection
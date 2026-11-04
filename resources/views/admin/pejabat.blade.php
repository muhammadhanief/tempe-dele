@extends('layouts.app')

@section('title', 'Pejabat')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-5">

    {{-- Tombol Tambah --}}
    <div class="mb-5 flex justify-end">
        <button type="button" onclick="openModal()"
            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-sm font-medium text-white transition-all hover:brightness-95 sm:w-10 sm:rounded-full sm:px-0"
            title="Tambah Pejabat">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
            </svg>
            <span class="sm:hidden">Tambah Pejabat</span>
        </button>
    </div>

    {{-- Alert --}}
    <div id="alertContainer" class="mb-4">
        @if(session('success'))
            <div class="alert-inline rounded-lg border border-green-200 bg-green-100 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-inline rounded-lg border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Petunjuk Geser Horizontal Khusus Mobile --}}
    <div class="sm:hidden table-scroll-hint flex flex-col gap-1.5 px-1 mb-2.5 text-[11px] font-medium text-slate-500">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-[#faa938] shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Geser tabel ke kiri / kanan</span>
            </span>
            <span class="table-scroll-pct text-[10px] font-mono text-slate-400 shrink-0">Geser »</span>
        </div>
        <div class="table-scroll-track w-full h-1.5 bg-slate-200/90 rounded-full overflow-hidden cursor-pointer relative">
            <div class="table-scroll-thumb absolute top-0 left-0 h-full bg-[#faa938] rounded-full" style="width: 30%; transform: translateX(0px);"></div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white">
        <table class="w-full min-w-[1180px] lg:min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="rounded-tl-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">Tahun</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama PPK</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama Kepala BPS</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Nama Kepala Bagian Umum</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-900">Status</th>
                    <th class="rounded-tr-xl px-3 py-3 text-center text-xs font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-300">
                @forelse($pejabatPerTahun as $tahun => $group)
                    @php
                        $ppk = $group->firstWhere('jabatan', 'PPK');
                        $kbps = $group->firstWhere('jabatan', 'Kepala BPS');
                        $kbu = $group->firstWhere('jabatan', 'Kepala Bagian Umum');
                    @endphp

                    <tr class="bg-white hover:bg-gray-50" data-tahun="{{ $tahun }}">

                        {{-- Tahun --}}
                        <td class="px-3 py-3 text-center text-xs font-semibold text-gray-900 whitespace-nowrap">
                            {{ $tahun }}
                        </td>

                        {{-- PPK --}}
                        <td class="px-3 py-3 text-xs text-gray-900" data-id-ppk="{{ $ppk?->id_pejabat }}">
                            <span class="cell-display-ppk">{{ $ppk?->nama ?? '-' }}</span>

                            <select name="nama"
                                class="cell-edit-ppk hidden w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"
                                onchange="fillNipInline(this, 'ppk')">
                                @foreach($pejabatList['PPK'] ?? [] as $p)
                                    <option value="{{ $p->nama }}"
                                        data-nip-lama="{{ $p->nip_lama }}"
                                        {{ $ppk?->nama === $p->nama ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-0.5 text-xs text-gray-400">
                                <span class="cell-display-ppk-nip">{{ $ppk?->nip_lama ?? '-' }}</span>
                                <input type="text" name="nip_lama" value="{{ $ppk?->nip_lama }}"
                                    class="cell-edit-ppk hidden mt-1 w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            </div>
                        </td>

                        {{-- Status PPK --}}
                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            <span class="cell-display-ppk-status">{{ $ppk ? ucfirst($ppk->status) : '-' }}</span>

                            <select name="status"
                                class="cell-edit-ppk hidden rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                                <option value="aktif" {{ $ppk?->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ $ppk?->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </td>

                        {{-- Kepala BPS --}}
                        <td class="px-3 py-3 text-xs text-gray-900" data-id-kbps="{{ $kbps?->id_pejabat }}">
                            <span class="cell-display-kbps">{{ $kbps?->nama ?? '-' }}</span>

                            <select name="nama"
                                class="cell-edit-kbps hidden w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"
                                onchange="fillNipInline(this, 'kbps')">
                                @foreach($pejabatList['Kepala BPS'] ?? [] as $p)
                                    <option value="{{ $p->nama }}"
                                        data-nip-lama="{{ $p->nip_lama }}"
                                        {{ $kbps?->nama === $p->nama ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-0.5 text-xs text-gray-400">
                                <span class="cell-display-kbps-nip">{{ $kbps?->nip_lama ?? '-' }}</span>
                                <input type="text" name="nip_lama" value="{{ $kbps?->nip_lama }}"
                                    class="cell-edit-kbps hidden mt-1 w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            </div>
                        </td>

                        {{-- Status KBPS --}}
                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            <span class="cell-display-kbps-status">{{ $kbps ? ucfirst($kbps->status) : '-' }}</span>

                            <select name="status"
                                class="cell-edit-kbps hidden rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                                <option value="aktif" {{ $kbps?->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ $kbps?->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </td>

                        {{-- KBU --}}
                        <td class="px-3 py-3 text-xs text-gray-900" data-id-kbu="{{ $kbu?->id_pejabat }}">
                            <span class="cell-display-kbu">{{ $kbu?->nama ?? '-' }}</span>

                            <select name="nama"
                                class="cell-edit-kbu hidden w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20"
                                onchange="fillNipInline(this, 'kbu')">
                                @foreach($pejabatList['Kepala Bagian Umum'] ?? [] as $p)
                                    <option value="{{ $p->nama }}"
                                        data-nip-lama="{{ $p->nip_lama }}"
                                        {{ $kbu?->nama === $p->nama ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-0.5 text-xs text-gray-400">
                                <span class="cell-display-kbu-nip">{{ $kbu?->nip_lama ?? '-' }}</span>
                                <input type="text" name="nip_lama" value="{{ $kbu?->nip_lama }}"
                                    class="cell-edit-kbu hidden mt-1 w-full min-w-[180px] rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                            </div>
                        </td>

                        {{-- Status KBU --}}
                        <td class="px-3 py-3 text-center text-xs text-gray-900">
                            <span class="cell-display-kbu-status">{{ $kbu ? ucfirst($kbu->status) : '-' }}</span>

                            <select name="status"
                                class="cell-edit-kbu hidden rounded border border-gray-300 px-2 py-1 text-xs focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                                <option value="aktif" {{ $kbu?->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ $kbu?->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-3 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">

                                {{-- Edit --}}
                                <button type="button" class="btn-edit rounded-full p-2 transition-all hover:bg-indigo-50">
                                    {{-- icon pensil asli kamu --}}
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="fill-indigo-500" d="M9.53414 8.15675L8.96459 7.59496L8.96459 7.59496L9.53414 8.15675ZM13.8911 3.73968L13.3215 3.17789V3.17789L13.8911 3.73968ZM16.3154 3.75892L15.7367 4.31126L15.7367 4.31127L16.3154 3.75892ZM16.38 3.82658L16.9587 3.27423L16.9587 3.27423L16.38 3.82658ZM16.3401 6.13595L15.7803 5.56438L16.3401 6.13595ZM11.9186 10.4658L12.4784 11.0374L11.9186 10.4658ZM11.1223 10.9017L10.9404 10.1226V10.1226L11.1223 10.9017ZM9.07259 10.9951L8.52556 11.5788L8.52556 11.5788L9.07259 10.9951ZM9.09713 8.9664L9.87963 9.1328V9.1328L9.09713 8.9664ZM9.05721 10.9803L8.49542 11.5498H8.49542L9.05721 10.9803ZM17.1679 4.99458L16.368 4.98075V4.98075L17.1679 4.99458ZM15.1107 2.8693L15.1171 2.06932L15.1107 2.8693ZM9.22851 8.51246L8.52589 8.12992L8.52452 8.13247L9.22851 8.51246ZM9.22567 8.51772L8.52168 8.13773L8.5203 8.1403L9.22567 8.51772ZM11.5684 10.7654L11.9531 11.4668L11.9536 11.4666L11.5684 10.7654ZM11.5669 10.7662L11.9507 11.4681L11.9516 11.4676L11.5669 10.7662ZM11.3235 3.30005C11.7654 3.30005 12.1235 2.94188 12.1235 2.50005C12.1235 2.05822 11.7654 1.70005 11.3235 1.70005V3.30005ZM18.3 9.55887C18.3 9.11705 17.9418 8.75887 17.5 8.75887C17.0582 8.75887 16.7 9.11705 16.7 9.55887H18.3ZM3.47631 16.5237L4.042 15.9581H4.042L3.47631 16.5237ZM16.5237 16.5237L15.958 15.9581L15.958 15.9581L16.5237 16.5237ZM10.1037 8.71855L14.4606 4.30148L13.3215 3.17789L8.96459 7.59496L10.1037 8.71855ZM15.7367 4.31127L15.8013 4.37893L16.9587 3.27423L16.8941 3.20657L15.7367 4.31127ZM15.7803 5.56438L11.3589 9.89426L12.4784 11.0374L16.8998 6.70753L15.7803 5.56438ZM10.9404 10.1226C10.3417 10.2624 9.97854 10.3452 9.72166 10.3675C9.47476 10.3888 9.53559 10.3326 9.61962 10.4113L8.52556 11.5788C8.9387 11.966 9.45086 11.9969 9.85978 11.9615C10.2587 11.9269 10.7558 11.8088 11.3042 11.6807L10.9404 10.1226ZM8.31462 8.8C8.19986 9.33969 8.09269 9.83345 8.0681 10.2293C8.04264 10.6393 8.08994 11.1499 8.49542 11.5498L9.619 10.4107C9.70348 10.494 9.65043 10.5635 9.66503 10.3285C9.6805 10.0795 9.75378 9.72461 9.87963 9.1328L8.31462 8.8ZM9.61962 10.4113C9.61941 10.4111 9.6192 10.4109 9.619 10.4107L8.49542 11.5498C8.50534 11.5596 8.51539 11.5693 8.52556 11.5788L9.61962 10.4113ZM15.8013 4.37892C16.0813 4.67236 16.2351 4.83583 16.3279 4.96331C16.4073 5.07234 16.3667 5.05597 16.368 4.98075L17.9678 5.00841C17.9749 4.59682 17.805 4.27366 17.6213 4.02139C17.451 3.78756 17.2078 3.53522 16.9587 3.27423L15.8013 4.37892ZM16.8998 6.70753C17.1578 6.45486 17.4095 6.21077 17.5876 5.98281C17.7798 5.73698 17.9607 5.41987 17.9678 5.00841L16.368 4.98075C16.3693 4.90565 16.4103 4.8909 16.327 4.99749C16.2297 5.12196 16.0703 5.28038 15.7803 5.56438L16.8998 6.70753ZM14.4606 4.30148C14.7639 3.99402 14.9352 3.82285 15.0703 3.71873C15.1866 3.62905 15.1757 3.66984 15.1044 3.66927L15.1171 2.06932C14.6874 2.06591 14.3538 2.25081 14.0935 2.45151C13.8518 2.63775 13.5925 2.9032 13.3215 3.17789L14.4606 4.30148ZM16.8941 3.20657C16.6279 2.92765 16.373 2.65804 16.1345 2.46792C15.8774 2.26298 15.5468 2.07273 15.1171 2.06932L15.1044 3.66927C15.033 3.66871 15.0226 3.62768 15.1372 3.71904C15.2704 3.82522 15.4387 3.999 15.7367 4.31126L16.8941 3.20657ZM8.96459 7.59496C8.82923 7.73218 8.64795 7.90575 8.5259 8.12993L9.93113 8.895C9.92075 8.91406 9.91465 8.91711 9.93926 8.88927C9.97002 8.85445 10.0145 8.80893 10.1037 8.71854L8.96459 7.59496ZM9.87963 9.1328C9.9059 9.00925 9.91925 8.94785 9.93124 8.90366C9.94073 8.86868 9.94137 8.87585 9.93104 8.89515L8.5203 8.1403C8.39951 8.36605 8.35444 8.61274 8.31462 8.8L9.87963 9.1328ZM8.52452 8.13247L8.52168 8.13773L9.92967 8.89772L9.9325 8.89246L8.52452 8.13247ZM11.3589 9.89426C11.27 9.98132 11.2252 10.0248 11.1909 10.055C11.1635 10.0791 11.1658 10.0738 11.1832 10.0642L11.9536 11.4666C12.1727 11.3462 12.3427 11.1703 12.4784 11.0374L11.3589 9.89426ZM11.3042 11.6807C11.4912 11.6371 11.7319 11.5878 11.9507 11.4681L11.1831 10.0643C11.2007 10.0547 11.206 10.0557 11.1697 10.0663C11.1248 10.0793 11.0628 10.0941 10.9404 10.1226L11.3042 11.6807ZM11.1837 10.064L11.1822 10.0648L11.9516 11.4676L11.9531 11.4668L11.1837 10.064ZM16.399 6.10097L13.8984 3.60094L12.7672 4.73243L15.2677 7.23246L16.399 6.10097ZM10.8333 16.7001H9.16667V18.3001H10.8333V16.7001ZM3.3 10.8334V9.16672H1.7V10.8334H3.3ZM9.16667 3.30005H11.3235V1.70005H9.16667V3.30005ZM16.7 9.55887V10.8334H18.3V9.55887H16.7ZM9.16667 16.7001C7.5727 16.7001 6.45771 16.6984 5.61569 16.5851C4.79669 16.475 4.35674 16.2728 4.042 15.9581L2.91063 17.0894C3.5722 17.751 4.40607 18.0369 5.4025 18.1709C6.37591 18.3018 7.61793 18.3001 9.16667 18.3001V16.7001ZM1.7 10.8334C1.7 12.3821 1.6983 13.6241 1.82917 14.5976C1.96314 15.594 2.24905 16.4279 2.91063 17.0894L4.042 15.9581C3.72726 15.6433 3.52502 15.2034 3.41491 14.3844C3.3017 13.5423 3.3 12.4273 3.3 10.8334H1.7ZM10.8333 18.3001C12.3821 18.3001 13.6241 18.3018 14.5975 18.1709C15.5939 18.0369 16.4278 17.751 17.0894 17.0894L15.958 15.9581C15.6433 16.2728 15.2033 16.475 14.3843 16.5851C13.5423 16.6984 12.4273 16.7001 10.8333 16.7001V18.3001ZM16.7 10.8334C16.7 12.4274 16.6983 13.5423 16.5851 14.3844C16.475 15.2034 16.2727 15.6433 15.958 15.9581L17.0894 17.0894C17.7509 16.4279 18.0369 15.594 18.1708 14.5976C18.3017 13.6241 18.3 12.3821 18.3 10.8334H16.7ZM3.3 9.16672C3.3 7.57275 3.3017 6.45776 3.41491 5.61574C3.52502 4.79674 3.72726 4.35679 4.042 4.04205L2.91063 2.91068C2.24905 3.57225 1.96314 4.40612 1.82917 5.40255C1.6983 6.37596 1.7 7.61798 1.7 9.16672H3.3ZM9.16667 1.70005C7.61793 1.70005 6.37591 1.69835 5.4025 1.82922C4.40607 1.96319 3.5722 2.24911 2.91063 2.91068L4.042 4.04205C4.35674 3.72731 4.79669 3.52507 5.61569 3.41496C6.45771 3.30175 7.5727 3.30005 9.16667 3.30005V1.70005Z" fill="#818CF8"/>
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button type="button" class="btn-delete rounded-full p-2 transition-all hover:bg-red-50">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="fill-red-600" d="M4.00031 5.49999V4.69999H3.20031V5.49999H4.00031ZM16.0003 5.49999H16.8003V4.69999H16.0003V5.49999ZM17.5003 5.49999L17.5003 6.29999C17.9421 6.29999 18.3003 5.94183 18.3003 5.5C18.3003 5.05817 17.9421 4.7 17.5003 4.69999L17.5003 5.49999ZM9.30029 9.24997C9.30029 8.80814 8.94212 8.44997 8.50029 8.44997C8.05847 8.44997 7.70029 8.80814 7.70029 9.24997H9.30029ZM7.70029 13.75C7.70029 14.1918 8.05847 14.55 8.50029 14.55C8.94212 14.55 9.30029 14.1918 9.30029 13.75H7.70029ZM12.3004 9.24997C12.3004 8.80814 11.9422 8.44997 11.5004 8.44997C11.0585 8.44997 10.7004 8.80814 10.7004 9.24997H12.3004ZM10.7004 13.75C10.7004 14.1918 11.0585 14.55 11.5004 14.55C11.9422 14.55 12.3004 14.1918 12.3004 13.75H10.7004ZM4.00031 6.29999H16.0003V4.69999H4.00031V6.29999ZM15.2003 5.49999V12.5H16.8003V5.49999H15.2003ZM11.0003 16.7H9.00031V18.3H11.0003V16.7ZM4.80031 12.5V5.49999H3.20031V12.5H4.80031ZM9.00031 16.7C7.79918 16.7 6.97882 16.6983 6.36373 16.6156C5.77165 16.536 5.49093 16.3948 5.29823 16.2021L4.16686 17.3334C4.70639 17.873 5.38104 18.0979 6.15053 18.2013C6.89702 18.3017 7.84442 18.3 9.00031 18.3V16.7ZM3.20031 12.5C3.20031 13.6559 3.19861 14.6033 3.29897 15.3498C3.40243 16.1193 3.62733 16.7939 4.16686 17.3334L5.29823 16.2021C5.10553 16.0094 4.96431 15.7286 4.88471 15.1366C4.80201 14.5215 4.80031 13.7011 4.80031 12.5H3.20031ZM15.2003 12.5C15.2003 13.7011 15.1986 14.5215 15.1159 15.1366C15.0363 15.7286 14.8951 16.0094 14.7024 16.2021L15.8338 17.3334C16.3733 16.7939 16.5982 16.1193 16.7016 15.3498C16.802 14.6033 16.8003 13.6559 16.8003 12.5H15.2003ZM11.0003 18.3C12.1562 18.3 13.1036 18.3017 13.8501 18.2013C14.6196 18.0979 15.2942 17.873 15.8338 17.3334L14.7024 16.2021C14.5097 16.3948 14.229 16.536 13.6369 16.6156C13.0218 16.6983 12.2014 16.7 11.0003 16.7V18.3ZM2.50031 4.69999C2.22572 4.7 2.04405 4.7 1.94475 4.7C1.89511 4.7 1.86604 4.7 1.85624 4.7C1.85471 4.7 1.85206 4.7 1.851 4.7C1.05253 5.50059 1.85233 6.3 1.85256 6.3C1.85273 6.3 1.85297 6.3 1.85327 6.3C1.85385 6.3 1.85472 6.3 1.85587 6.3C1.86047 6.3 1.86972 6.3 1.88345 6.3C1.99328 6.3 2.39045 6.3 2.9906 6.3C4.19091 6.3 6.2032 6.3 8.35279 6.3C10.5024 6.3 12.7893 6.3 14.5387 6.3C15.4135 6.3 16.1539 6.3 16.6756 6.3C16.9364 6.3 17.1426 6.29999 17.2836 6.29999C17.3541 6.29999 17.4083 6.29999 17.4448 6.29999C17.4631 6.29999 17.477 6.29999 17.4863 6.29999C17.4909 6.29999 17.4944 6.29999 17.4968 6.29999C17.498 6.29999 17.4988 6.29999 17.4994 6.29999C17.4997 6.29999 17.4999 6.29999 17.5001 6.29999C17.5002 6.29999 17.5003 6.29999 17.5003 5.49999C17.5003 4.69999 17.5002 4.69999 17.5001 4.69999C17.4999 4.69999 17.4997 4.69999 17.4994 4.69999C17.4988 4.69999 17.498 4.69999 17.4968 4.69999C17.4944 4.69999 17.4909 4.69999 17.4863 4.69999C17.477 4.69999 17.4631 4.69999 17.4448 4.69999C17.4083 4.69999 17.3541 4.69999 17.2836 4.69999C17.1426 4.7 16.9364 4.7 16.6756 4.7C16.1539 4.7 15.4135 4.7 14.5387 4.7C12.7893 4.7 10.5024 4.7 8.35279 4.7C6.2032 4.7 4.19091 4.7 2.9906 4.7C2.39044 4.7 1.99329 4.7 1.88347 4.7C1.86974 4.7 1.86051 4.7 1.85594 4.7C1.8548 4.7 1.85396 4.7 1.85342 4.7C1.85315 4.7 1.85298 4.7 1.85288 4.7C1.85284 4.7 2.65253 5.49941 1.85408 6.3C1.85314 6.3 1.85296 6.3 1.85632 6.3C1.86608 6.3 1.89511 6.3 1.94477 6.3C2.04406 6.3 2.22573 6.3 2.50031 6.29999L2.50031 4.69999ZM7.05028 5.49994V4.16661H5.45028V5.49994H7.05028ZM7.91695 3.29994H12.0836V1.69994H7.91695V3.29994ZM12.9503 4.16661V5.49994H14.5503V4.16661H12.9503ZM12.0836 3.29994C12.5623 3.29994 12.9503 3.68796 12.9503 4.16661H14.5503C14.5503 2.8043 13.4459 1.69994 12.0836 1.69994V3.29994ZM7.05028 4.16661C7.05028 3.68796 7.4383 3.29994 7.91695 3.29994V1.69994C6.55465 1.69994 5.45028 2.8043 5.45028 4.16661H7.05028ZM2.50031 6.29999C4.70481 6.29998 6.40335 6.29998 8.1253 6.29997C9.84725 6.29996 11.5458 6.29995 13.7503 6.29994L13.7503 4.69994C11.5458 4.69995 9.84724 4.69996 8.12529 4.69997C6.40335 4.69998 4.7048 4.69998 2.50031 4.69999L2.50031 6.29999ZM13.7503 6.29994L17.5003 6.29999L17.5003 4.69999L13.7503 4.69994L13.7503 6.29994ZM7.70029 9.24997V13.75H9.30029V9.24997H7.70029ZM10.7004 9.24997V13.75H12.3004V9.24997H10.7004Z" fill="#F87171"/>
                                    </svg>
                                </button>

                                {{-- Simpan --}}
                                <button type="button" class="btn-save hidden rounded-full p-2 transition-all hover:bg-green-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>

                                {{-- Batal --}}
                                <button type="button" class="btn-cancel hidden rounded-full p-2 transition-all hover:bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-400">
                            Belum ada data pejabat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeModal()"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-6">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">

            <div class="flex items-center justify-between border-b px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold text-gray-900 sm:text-lg">Tambah Pejabat</h2>

                <button type="button" onclick="closeModal()"
                    class="text-xl text-gray-500 transition-all hover:text-gray-700">
                    &times;
                </button>
            </div>

            <form action="{{ route('admin.pejabat.store') }}" method="POST" class="space-y-4 px-5 py-5 sm:px-6">
                @csrf

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <input type="number" name="tahun" min="2020" max="2099" required placeholder="2026"
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Jabatan</label>
                    <select name="jabatan" required
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <option value="">Pilih Jabatan</option>
                        <option value="PPK">PPK (Pejabat Pembuat Komitmen)</option>
                        <option value="Kepala BPS">Kepala BPS Provinsi Jawa Tengah</option>
                        <option value="Kepala Bagian Umum">Kepala Bagian Umum</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" required
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">NIP Lama</label>
                    <input type="text" name="nip_lama"
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">NIP Baru</label>
                    <input type="text" name="nip"
                        class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" required
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeModal()"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm transition-all hover:bg-gray-50 sm:w-auto">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full rounded-lg bg-[#faa938] px-4 py-2 text-sm font-semibold text-black transition-all hover:bg-[#fd9a10] hover:text-white sm:w-auto">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

function openModal() {
    document.getElementById('modalTambah').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal() {
    document.getElementById('modalTambah').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function fillNipInline(select, prefix) {
    const opt = select.options[select.selectedIndex];
    const td = select.closest('td');
    const input = td?.querySelector(`input.cell-edit-${prefix}[name="nip_lama"]`);

    if (input) {
        input.value = opt.dataset.nipLama ?? '';
    }
}

function showInlineAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    if (!container) return;

    const alertClass = type === 'success'
        ? 'border-green-200 bg-green-100 text-green-700'
        : 'border-red-200 bg-red-100 text-red-700';

    container.innerHTML = `<div class="alert-inline rounded-lg border px-4 py-3 text-sm ${alertClass}">${message}</div>`;

    setTimeout(() => {
        const alertEl = container.querySelector('.alert-inline');
        if (!alertEl) return;

        alertEl.style.transition = 'opacity 0.3s ease';
        alertEl.style.opacity = '0';

        setTimeout(() => {
            container.innerHTML = '';
        }, 300);
    }, 3000);
}

function setEditMode(row, active, btnEdit, btnSave, btnCancel, btnDelete) {
    row.querySelectorAll('[class*="cell-display"]').forEach(el => {
        el.classList.toggle('hidden', active);
    });

    row.querySelectorAll('[class*="cell-edit"]').forEach(el => {
        el.classList.toggle('hidden', !active);
    });

    btnEdit?.classList.toggle('hidden', active);
    btnDelete?.classList.toggle('hidden', active);
    btnSave?.classList.toggle('hidden', !active);
    btnCancel?.classList.toggle('hidden', !active);
}

function saveOriginalValues(row) {
    row.querySelectorAll('[class*="cell-edit"]').forEach(el => {
        el.dataset.original = el.value;
    });
}

function restoreOriginalValues(row) {
    row.querySelectorAll('[class*="cell-edit"]').forEach(el => {
        if (el.dataset.original !== undefined) {
            el.value = el.dataset.original;
        }
    });
}

async function updatePejabat(id, payload) {
    const response = await fetch(`/admin/pejabat/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error('Gagal update pejabat.');
    }

    return response;
}

document.querySelectorAll('tbody tr[data-tahun]').forEach(row => {
    const btnEdit = row.querySelector('.btn-edit');
    const btnSave = row.querySelector('.btn-save');
    const btnCancel = row.querySelector('.btn-cancel');
    const btnDelete = row.querySelector('.btn-delete');

    const tdPpk = row.querySelector('[data-id-ppk]');
    const tdKbps = row.querySelector('[data-id-kbps]');
    const tdKbu = row.querySelector('[data-id-kbu]');

    const idPpk = tdPpk?.dataset.idPpk;
    const idKbps = tdKbps?.dataset.idKbps;
    const idKbu = tdKbu?.dataset.idKbu;

    btnEdit?.addEventListener('click', () => {
        saveOriginalValues(row);
        setEditMode(row, true, btnEdit, btnSave, btnCancel, btnDelete);
    });

    btnCancel?.addEventListener('click', () => {
        restoreOriginalValues(row);
        setEditMode(row, false, btnEdit, btnSave, btnCancel, btnDelete);
    });

    btnSave?.addEventListener('click', async () => {
        try {
            if (idPpk) {
                const nama = tdPpk.querySelector('select[name="nama"]').value;
                const nipLama = tdPpk.querySelector('input[name="nip_lama"]').value;
                const status = row.querySelector('td:nth-child(3) select[name="status"]').value;

                await updatePejabat(idPpk, {
                    nama: nama,
                    nip_lama: nipLama,
                    status: status,
                });

                row.querySelector('.cell-display-ppk').textContent = nama || '-';
                row.querySelector('.cell-display-ppk-nip').textContent = nipLama || '-';
                row.querySelector('.cell-display-ppk-status').textContent = status === 'aktif' ? 'Aktif' : 'Nonaktif';
            }

            if (idKbps) {
                const nama = tdKbps.querySelector('select[name="nama"]').value;
                const nipLama = tdKbps.querySelector('input[name="nip_lama"]').value;
                const status = row.querySelector('td:nth-child(5) select[name="status"]').value;

                await updatePejabat(idKbps, {
                    nama: nama,
                    nip_lama: nipLama,
                    status: status,
                });

                row.querySelector('.cell-display-kbps').textContent = nama || '-';
                row.querySelector('.cell-display-kbps-nip').textContent = nipLama || '-';
                row.querySelector('.cell-display-kbps-status').textContent = status === 'aktif' ? 'Aktif' : 'Nonaktif';
            }

            if (idKbu) {
                const nama = tdKbu.querySelector('select[name="nama"]').value;
                const nipLama = tdKbu.querySelector('input[name="nip_lama"]').value;
                const status = row.querySelector('td:nth-child(7) select[name="status"]').value;

                await updatePejabat(idKbu, {
                    nama: nama,
                    nip_lama: nipLama,
                    status: status,
                });

                row.querySelector('.cell-display-kbu').textContent = nama || '-';
                row.querySelector('.cell-display-kbu-nip').textContent = nipLama || '-';
                row.querySelector('.cell-display-kbu-status').textContent = status === 'aktif' ? 'Aktif' : 'Nonaktif';
            }

            setEditMode(row, false, btnEdit, btnSave, btnCancel, btnDelete);
            showInlineAlert('Pejabat berhasil diupdate', 'success');
        } catch {
            showInlineAlert('Gagal menyimpan perubahan.', 'error');
        }
    });

    btnDelete?.addEventListener('click', async () => {
        const tahun = row.dataset.tahun;

        if (!confirm(`Hapus data pejabat tahun ${tahun}?`)) return;

        const ids = [idPpk, idKbps, idKbu].filter(Boolean);

        if (ids.length === 0) {
            showInlineAlert('Data pejabat tidak ditemukan.', 'error');
            return;
        }

        try {
            await Promise.all(ids.map(id =>
                fetch(`/admin/pejabat/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                })
            ));

            row.remove();
            showInlineAlert('Pejabat berhasil dihapus', 'success');
        } catch {
            showInlineAlert('Gagal menghapus.', 'error');
        }
    });
});
</script>
@endpush

@endsection

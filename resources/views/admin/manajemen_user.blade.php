@extends('layouts.app')

@section('title', 'Manajemen User & Pejabat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-8">

    {{-- ==================== HEADER HALAMAN ==================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen User & Hak Akses</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                    </svg>
                    Superadmin Only
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat kendali penetapan pejabat Kepala Bagian Umum, wewenang Admin Lembur, dan penambahan Super Administrator.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pengguna') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                Kelola Seluruh Pengguna
            </a>
        </div>
    </div>

    {{-- ==================== ALERT NOTIFIKASI ==================== --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error') || (isset($errors) && $errors->any()))
        <div class="flex items-start gap-3 p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm space-y-1">
                @if(session('error'))
                    <p class="font-medium">{{ session('error') }}</p>
                @endif
                @if(isset($errors) && $errors->any())
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- SECTION 1: KEPALA BAGIAN UMUM (SUPERADMIN ONLY)            --}}
    {{-- ========================================================== --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Kepala Bagian Umum (Aktif)</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">
                    Pejabat aktif memiliki wewenang <span class="font-medium text-slate-700">persetujuan final (final approval)</span> seluruh pengajuan lembur dan memimpin Tim Kerja Bagian Umum.
                </p>
            </div>

            <button type="button" onclick="openModalGantiKabag()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#faa938] text-white text-xs sm:text-sm font-semibold hover:brightness-95 transition-all shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Ganti Kepala Bagian Umum
            </button>
        </div>

        {{-- Profil Kabag Aktif --}}
        <div class="mt-6">
            @if($kabagAktif)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 font-bold text-xl flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($kabagAktif->nama ?? 'K', 0, 2)) }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-bold text-slate-900">{{ $kabagAktif->nama }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Menjabat Aktif (Tahun {{ $kabagAktif->tahun ?? date('Y') }})
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span><strong class="text-slate-700">NIP:</strong> {{ $kabagAktif->nip ?? '-' }}</span>
                                <span><strong class="text-slate-700">NIP BPS:</strong> {{ $kabagAktif->nip_lama ?? '-' }}</span>
                                @if($detailKabagAktif && $detailKabagAktif->satker)
                                    <span><strong class="text-slate-700">Satker:</strong> {{ $detailKabagAktif->satker }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-slate-200 text-xs text-slate-500">
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 font-medium uppercase">Jabatan Struktural</span>
                            <span class="font-semibold text-slate-800">Kepala Bagian Umum</span>
                        </div>
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 font-medium uppercase">Role Sistem</span>
                            <span class="font-semibold text-amber-600">{{ ucfirst($detailKabagAktif->role ?? 'ketua_tim') }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200 text-slate-500">
                    <p class="text-sm font-medium">Belum ada pejabat Kepala Bagian Umum yang berstatus aktif.</p>
                    <p class="text-xs text-slate-400 mt-1">Klik tombol di atas untuk menunjuk Kepala Bagian Umum.</p>
                </div>
            @endif
        </div>

        {{-- Riwayat Pejabat Kabag Sebelumnya (Collapsible) --}}
        @if($riwayatKabag->count() > 1)
            <div class="mt-5 pt-4 border-t border-slate-100">
                <details class="group">
                    <summary class="flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 select-none py-1">
                        <span>Lihat Riwayat Pejabat Kabag Umum Sebelumnya ({{ $riwayatKabag->where('status', '!=', 'aktif')->count() }} arsip)</span>
                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2">Tahun</th>
                                    <th class="px-3 py-2">Nama Pejabat</th>
                                    <th class="px-3 py-2">NIP</th>
                                    <th class="px-3 py-2">NIP BPS</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($riwayatKabag as $rk)
                                    <tr class="{{ $rk->status === 'aktif' ? 'bg-amber-50/40 font-semibold' : '' }}">
                                        <td class="px-3 py-2">{{ $rk->tahun ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rk->nama ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rk->nip ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rk->nip_lama ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            @if($rk->status === 'aktif')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        @endif
    </section>

    {{-- ========================================================== --}}
    {{-- SECTION 2: PEJABAT PEMBUAT KOMITMEN (PPK AKTIF)            --}}
    {{-- ========================================================== --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Pejabat Pembuat Komitmen / PPK (Aktif)</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">
                    Pejabat yang berwenang menandatangani Surat Perintah Kerja Lembur (SPKL) dan dokumen anggaran. <span class="font-medium text-slate-700">Hanya 1 PPK aktif yang digunakan sebagai penandatangan dokumen.</span>
                </p>
            </div>

            <button type="button" onclick="openModalGantiPpk()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 text-white text-xs sm:text-sm font-semibold hover:bg-sky-700 transition-all shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Ganti PPK
            </button>
        </div>

        {{-- Profil PPK Aktif --}}
        <div class="mt-6">
            @if($ppkAktif)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 p-5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-700 font-bold text-xl flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($ppkAktif->nama ?? 'P', 0, 2)) }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-bold text-slate-900">{{ $ppkAktif->nama }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Menjabat Aktif (Tahun {{ $ppkAktif->tahun ?? date('Y') }})
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span><strong class="text-slate-700">NIP:</strong> {{ $ppkAktif->nip ?? '-' }}</span>
                                <span><strong class="text-slate-700">NIP BPS:</strong> {{ $ppkAktif->nip_lama ?? '-' }}</span>
                                @if($detailPpkAktif && $detailPpkAktif->satker)
                                    <span><strong class="text-slate-700">Satker:</strong> {{ $detailPpkAktif->satker }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-slate-200 text-xs text-slate-500">
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 font-medium uppercase">Jabatan Struktural</span>
                            <span class="font-semibold text-slate-800">Pejabat Pembuat Komitmen</span>
                        </div>
                        <div class="bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-2xs">
                            <span class="block text-[10px] text-slate-400 font-medium uppercase">Parameter Dokumen</span>
                            <span class="font-semibold text-sky-600">SPKL &amp; Anggaran</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200 text-slate-500">
                    <p class="text-sm font-medium">Belum ada Pejabat Pembuat Komitmen (PPK) yang berstatus aktif.</p>
                    <p class="text-xs text-slate-400 mt-1">Klik tombol di atas untuk menunjuk PPK.</p>
                </div>
            @endif
        </div>

        {{-- Riwayat PPK Sebelumnya (Collapsible) --}}
        @if($riwayatPpk->count() > 1)
            <div class="mt-5 pt-4 border-t border-slate-100">
                <details class="group">
                    <summary class="flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 select-none py-1">
                        <span>Lihat Riwayat PPK Sebelumnya ({{ $riwayatPpk->where('status', '!=', 'aktif')->count() }} arsip)</span>
                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2">Tahun</th>
                                    <th class="px-3 py-2">Nama Pejabat</th>
                                    <th class="px-3 py-2">NIP</th>
                                    <th class="px-3 py-2">NIP BPS</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($riwayatPpk as $rp)
                                    <tr class="{{ $rp->status === 'aktif' ? 'bg-sky-50/40 font-semibold' : '' }}">
                                        <td class="px-3 py-2">{{ $rp->tahun ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rp->nama ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rp->nip ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $rp->nip_lama ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            @if($rp->status === 'aktif')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        @endif
    </section>

    {{-- ========================================================== --}}
    {{-- SECTION 2: PENAMBAHAN & PENGHAPUSAN ADMIN                   --}}
    {{-- ========================================================== --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Admin Lembur</h2>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $admins->count() }} Admin
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">
                    Admin bertugas mengelola presensi, sinkronisasi tim kerja, generator dokumen, dan operasional lembur.
                    <span class="font-medium text-slate-700">Hanya Superadmin yang berhak menambah atau mencabut akses admin.</span>
                </p>
            </div>

            <button type="button" onclick="openModalTambahAdmin()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-semibold hover:bg-indigo-700 transition-all shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Admin
            </button>
        </div>

        {{-- Tabel Admin --}}
        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">Nama Admin</th>
                        <th class="px-4 py-3.5 font-semibold">NIP / NIP BPS</th>
                        <th class="px-4 py-3.5 font-semibold">Email</th>
                        <th class="px-4 py-3.5 font-semibold">Satuan Kerja</th>
                        <th class="px-4 py-3.5 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($admins as $adm)
                        @php
                            $sessUser = session('user');
                            $sessNip = is_array($sessUser) ? ($sessUser['nip'] ?? null) : ($sessUser->nip ?? null);
                            $isMe = $sessNip === $adm->nip;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                        {{ strtoupper(substr($adm->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 block">{{ $adm->nama }}</span>
                                        @if($isMe)
                                            <span class="inline-flex items-center text-[10px] text-amber-600 font-medium">Akun Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap">
                                <div>{{ $adm->nip ?? '—' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $adm->nip_lama ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $adm->email ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $adm->satker ?? 'BPS Provinsi Jawa Tengah' }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($isMe)
                                    <span class="text-xs text-slate-400 italic">Terkunci</span>
                                @else
                                    <form action="{{ route('admin.manajemen-user.hapus-admin', $adm->id_pegawai) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin mencabut wewenang Admin untuk {{ addslashes($adm->nama) }}? User akan dikembalikan ke peran biasa.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors"
                                            title="Cabut Akses Admin">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Cabut
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">
                                Belum ada pegawai yang ditunjuk sebagai Admin Lembur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================================== --}}
    {{-- SECTION 3: PENAMBAHAN SUPERADMIN                           --}}
    {{-- ========================================================== --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Super Administrator</h2>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        {{ $superadmins->count() }} Superadmin
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500">
                    Tingkat wewenang tertinggi dengan akses ke seluruh modul dan data.
                    <span class="font-bold text-rose-600">Perhatian: Hak Superadmin permanen dan tidak dapat dicabut oleh superadmin lain.</span>
                </p>
            </div>

            <button type="button" onclick="openModalTambahSuperadmin()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition-all shadow-xs shrink-0">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Superadmin
            </button>
        </div>

        {{-- Tabel Superadmin --}}
        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 font-semibold">Nama Superadmin</th>
                        <th class="px-4 py-3.5 font-semibold">NIP / Identitas</th>
                        <th class="px-4 py-3.5 font-semibold">Email</th>
                        <th class="px-4 py-3.5 font-semibold">Status Wewenang</th>
                        <th class="px-4 py-3.5 font-semibold text-center w-36">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($superadmins as $sa)
                        @php
                            $sessUser = session('user');
                            $sessNip = is_array($sessUser) ? ($sessUser['nip'] ?? null) : ($sessUser->nip ?? null);
                            $isMe = $sessNip === $sa->nip;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 font-bold flex items-center justify-center shrink-0 text-xs">
                                        {{ strtoupper(substr($sa->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 block">{{ $sa->nama }}</span>
                                        @if($isMe)
                                            <span class="inline-flex items-center text-[10px] text-amber-600 font-medium">Akun Anda</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap">
                                <div>{{ $sa->nip ?? '—' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $sa->nip_lama ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $sa->email ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Super Administrator
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Permanen (Kebijakan)
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">
                                Belum ada akun Superadmin terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

{{-- ========================================================== --}}
{{-- MODAL 1: GANTI KEPALA BAGIAN UMUM                          --}}
{{-- ========================================================== --}}
<div id="modalGantiKabag" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-slate-900">Pergantian Kepala Bagian Umum</h3>
            </div>
            <button type="button" onclick="closeModalGantiKabag()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.ganti-kabag') }}" method="POST" class="p-6 space-y-4">
            @csrf
            
            <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 leading-relaxed space-y-1">
                <p class="font-semibold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sistem Otomatisasi Terintegrasi:
                </p>
                <p>1. Pejabat Kabag Umum lama di database pejabat akan dinonaktifkan.</p>
                <p>2. Pejabat baru akan didaftarkan sebagai Kepala Bagian Umum aktif.</p>
                <p>3. Ketua Tim Kerja Bagian Umum akan otomatis dialihkan ke pejabat baru.</p>
                <p>4. Wewenang persetujuan lembur final langsung aktif untuk pejabat baru.</p>
            </div>

            <div>
                <label for="selectPegawaiKabag" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Pegawai Pengganti <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="filterPegawaiKabag" placeholder="Ketik nama atau NIP pegawai..."
                    oninput="filterSelectOptions('filterPegawaiKabag', 'selectPegawaiKabag')"
                    class="w-full text-xs h-9 px-3 mb-1.5 rounded-lg border border-slate-200 focus:border-[#faa938] focus:outline-none focus:ring-1 focus:ring-[#faa938]">
                <select name="id_pegawai" id="selectPegawaiKabag" required size="5"
                    class="w-full rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 p-2">
                    @foreach($semuaPegawai as $p)
                        <option value="{{ $p->id_pegawai }}" {{ ($kabagAktif && ($kabagAktif->nip === $p->nip || $kabagAktif->nip_lama === $p->nip_lama)) ? 'disabled class=text-slate-300' : '' }}>
                            {{ $p->nama }} — NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }} ({{ $p->satker ?? 'BPS' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan kotak di atas untuk memfilter daftar pegawai.</p>
            </div>

            <div>
                <label for="tahunKabag" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tahun Periode / SK <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="tahun" id="tahunKabag" value="{{ date('Y') }}" min="2020" max="2099" required
                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalGantiKabag()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-[#faa938] hover:brightness-95 rounded-xl transition-all shadow-xs">
                    Simpan Pergantian Kabag
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 1.5: GANTI PEJABAT PEMBUAT KOMITMEN (PPK)            --}}
{{-- ========================================================== --}}
<div id="modalGantiPpk" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-slate-900">Ganti Pejabat Pembuat Komitmen (PPK)</h3>
            </div>
            <button type="button" onclick="closeModalGantiPpk()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.ganti-ppk') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-900 leading-relaxed space-y-1">
                <p class="font-semibold flex items-center gap-1.5 text-sky-800">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sistem Otomatisasi Terintegrasi:
                </p>
                <p>1. Pejabat Pembuat Komitmen (PPK) lama di database pejabat akan dinonaktifkan.</p>
                <p>2. Pejabat baru akan didaftarkan sebagai PPK aktif.</p>
                <p>3. Dokumen Surat Perintah Kerja Lembur (SPKL) dan dokumen anggaran otomatis menggunakan pejabat baru.</p>
            </div>

            <div>
                <label for="selectPegawaiPpk" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Pegawai Pengganti <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="filterPegawaiPpk" placeholder="Ketik nama atau NIP pegawai..."
                    oninput="filterSelectOptions('filterPegawaiPpk', 'selectPegawaiPpk')"
                    class="w-full text-xs h-9 px-3 mb-1.5 rounded-lg border border-slate-200 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <select name="id_pegawai" id="selectPegawaiPpk" required size="5"
                    class="w-full rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 p-2">
                    @foreach($semuaPegawai as $p)
                        <option value="{{ $p->id_pegawai }}" {{ ($ppkAktif && ($ppkAktif->nip === $p->nip || $ppkAktif->nip_lama === $p->nip_lama)) ? 'disabled class=text-slate-300' : '' }}>
                            {{ $p->nama }} — NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }} ({{ $p->satker ?? 'BPS' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan kotak di atas untuk memfilter daftar pegawai.</p>
            </div>

            <div>
                <label for="tahunPpk" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Tahun Periode / SK <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="tahun" id="tahunPpk" value="{{ date('Y') }}" min="2020" max="2099" required
                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalGantiPpk()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-all shadow-xs">
                    Simpan Penetapan PPK
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 2: TAMBAH ADMIN LEMBUR                               --}}
{{-- ========================================================== --}}
<div id="modalTambahAdmin" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-slate-900">Tambah Admin Lembur</h3>
            </div>
            <button type="button" onclick="closeModalTambahAdmin()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.tambah-admin') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="p-3.5 rounded-xl bg-indigo-50/80 border border-indigo-100 text-xs text-indigo-900 leading-relaxed">
                Pilih pegawai yang akan diberikan wewenang sebagai Admin Lembur. Admin dapat mengelola presensi, riwayat lembur, tim kerja, dan pembuatan dokumen.
            </div>

            <div>
                <label for="selectPegawaiAdmin" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Pegawai <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="filterPegawaiAdmin" placeholder="Ketik nama atau NIP pegawai..."
                    oninput="filterSelectOptions('filterPegawaiAdmin', 'selectPegawaiAdmin')"
                    class="w-full text-xs h-9 px-3 mb-1.5 rounded-lg border border-slate-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <select name="id_pegawai" id="selectPegawaiAdmin" required size="6"
                    class="w-full rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 p-2">
                    @foreach($semuaPegawai->whereNotIn('role', ['admin', 'superadmin']) as $p)
                        <option value="{{ $p->id_pegawai }}">
                            {{ $p->nama }} — NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }} ({{ $p->satker ?? 'BPS' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Hanya menampilkan pegawai yang saat ini belum berstatus Admin / Superadmin.</p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalTambahAdmin()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-xs">
                    Angkat Sebagai Admin
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 3: TAMBAH SUPERADMIN (DENGAN PERINGATAN WAJIB)       --}}
{{-- ========================================================== --}}
<div id="modalTambahSuperadmin" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-rose-200 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose-100 bg-rose-50/50">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-rose-900">Penambahan Super Administrator</h3>
            </div>
            <button type="button" onclick="closeModalTambahSuperadmin()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.tambah-superadmin') }}" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- KOTAK PERINGATAN WAJIB SESUAI INSTRUKSI --}}
            <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-950 space-y-2">
                <div class="flex items-center gap-2 font-bold text-sm text-amber-900">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    PERINGATAN KONFIRMASI:
                </div>
                <p class="text-xs sm:text-sm font-semibold text-rose-700 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                    "beneran mau ngasih superadmin, nanti gabisa dicabut lagi"
                </p>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Super Administrator memiliki kendali penuh atas sistem dan basis data. Sesuai aturan keamanan sistem, <span class="font-bold underline">superadmin tidak dapat mencabut hak akses superadmin lain</span>.
                </p>
            </div>

            <div>
                <label for="selectPegawaiSuperadmin" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Pilih Pegawai / Admin yang Dipromosikan <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="filterPegawaiSuperadmin" placeholder="Ketik nama atau NIP..."
                    oninput="filterSelectOptions('filterPegawaiSuperadmin', 'selectPegawaiSuperadmin')"
                    class="w-full text-xs h-9 px-3 mb-1.5 rounded-lg border border-slate-200 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
                <select name="id_pegawai" id="selectPegawaiSuperadmin" required size="5"
                    class="w-full rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 p-2">
                    @foreach($semuaPegawai->where('role', '!=', 'superadmin') as $p)
                        <option value="{{ $p->id_pegawai }}">
                            {{ $p->nama }} — NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }} (Role saat ini: {{ ucfirst($p->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Checkbox Konfirmasi Ganda --}}
            <div class="pt-2">
                <label class="flex items-start gap-2.5 cursor-pointer p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <input type="checkbox" name="konfirmasi" value="1" id="chkKonfirmasiSuperadmin" required
                        onchange="toggleBtnSubmitSuperadmin(this)"
                        class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 h-4 w-4">
                    <span class="text-xs text-slate-700 leading-relaxed font-medium">
                        Saya memahami wewenang ini dan menyetujui pemberian hak Super Administrator secara <span class="text-rose-600 font-bold">permanen</span>.
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalTambahSuperadmin()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnSubmitSuperadmin" disabled
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 disabled:cursor-not-allowed rounded-xl transition-all shadow-xs">
                    Ya, Angkat Jadi Superadmin
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- JAVASCRIPT HELPER FOR MODALS & SEARCH FILTER               --}}
{{-- ========================================================== --}}
<script>
    // Modal Ganti Kabag
    function openModalGantiKabag() {
        document.getElementById('modalGantiKabag').classList.remove('hidden');
    }
    function closeModalGantiKabag() {
        document.getElementById('modalGantiKabag').classList.add('hidden');
    }

    // Modal Ganti PPK
    function openModalGantiPpk() {
        document.getElementById('modalGantiPpk').classList.remove('hidden');
    }
    function closeModalGantiPpk() {
        document.getElementById('modalGantiPpk').classList.add('hidden');
    }

    // Modal Tambah Admin
    function openModalTambahAdmin() {
        document.getElementById('modalTambahAdmin').classList.remove('hidden');
    }
    function closeModalTambahAdmin() {
        document.getElementById('modalTambahAdmin').classList.add('hidden');
    }

    // Modal Tambah Superadmin
    function openModalTambahSuperadmin() {
        document.getElementById('modalTambahSuperadmin').classList.remove('hidden');
    }
    function closeModalTambahSuperadmin() {
        document.getElementById('modalTambahSuperadmin').classList.add('hidden');
    }

    // Toggle button submit superadmin based on checkbox
    function toggleBtnSubmitSuperadmin(checkbox) {
        const btn = document.getElementById('btnSubmitSuperadmin');
        if (checkbox.checked) {
            btn.removeAttribute('disabled');
        } else {
            btn.setAttribute('disabled', 'disabled');
        }
    }

    // Filter select options in real-time
    function filterSelectOptions(inputId, selectId) {
        const search = document.getElementById(inputId).value.toLowerCase();
        const select = document.getElementById(selectId);
        const options = select.options;

        for (let i = 0; i < options.length; i++) {
            const text = options[i].text.toLowerCase();
            if (text.includes(search)) {
                options[i].style.display = '';
            } else {
                options[i].style.display = 'none';
            }
        }
    }

    // ESC to close any open modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModalGantiKabag();
            closeModalGantiPpk();
            closeModalTambahAdmin();
            closeModalTambahSuperadmin();
        }
    });
</script>
@endsection

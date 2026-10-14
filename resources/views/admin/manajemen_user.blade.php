@extends('layouts.app')

@section('title', 'Manajemen User & Pejabat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 space-y-7">

    {{-- ==================== HEADER HALAMAN ==================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-200">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pejabat & Hak Akses</h1>
            <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                Penetapan pejabat penandatangan aktif (Kepala Bagian Umum & PPK), pembagian wewenang Admin Lembur operasional, dan pengelolaan akun Super Administrator.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button type="button" onclick="openModalTambahAdmin()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-2xs shrink-0 cursor-pointer"
                title="Tambah wewenang Admin Lembur untuk pegawai">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Admin
            </button>
            <button type="button" onclick="openModalTambahSuperadmin()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-700/30 transition-all shadow-2xs shrink-0 cursor-pointer"
                title="Promosikan pegawai menjadi Super Administrator">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Superadmin
            </button>
            <a href="{{ route('admin.pengguna') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400/20 transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                Kelola Seluruh Pengguna
            </a>
        </div>
    </div>

    {{-- ==================== ALERT NOTIFIKASI ==================== --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 shadow-2xs" role="alert">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs sm:text-sm font-medium leading-relaxed">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error') || (isset($errors) && $errors->any()))
        <div class="flex items-start gap-3 p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 shadow-2xs" role="alert">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-xs sm:text-sm space-y-1">
                @if(session('error'))
                    <p class="font-semibold">{{ session('error') }}</p>
                @endif
                @if(isset($errors) && $errors->any())
                    <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-800">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- GRUP 1: PEJABAT PENANDATANGAN KEDINASAN AKTIF (2-KOLOM)     --}}
    {{-- ========================================================== --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Pejabat Penandatangan Kedinasan Aktif</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- KARTU 1: KEPALA BAGIAN UMUM --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden flex flex-col justify-between">
                <div class="p-5 sm:p-6 border-b border-slate-100 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Kepala Bagian Umum</h3>
                        </div>
                        <button type="button" onclick="openModalGantiKabag()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#faa938] text-white text-xs font-semibold hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#faa938]/30 transition-all shadow-2xs shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Ganti Kabag
                        </button>
                    </div>

                    @if($kabagAktif)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 border border-amber-200 font-bold text-base flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($kabagAktif->nama ?? 'K', 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm truncate">{{ $kabagAktif->nama }}</span>
                                </div>
                                <div class="text-[11.5px] text-slate-600 font-mono space-y-0.5">
                                    <div><span class="text-slate-400 font-sans">NIP:</span> {{ $kabagAktif->nip ?? '-' }}</div>
                                    @if($kabagAktif->nip_lama)
                                        <div><span class="text-slate-400 font-sans">NIP BPS:</span> {{ $kabagAktif->nip_lama }}</div>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 pt-0.5">
                                    <span class="text-slate-400">Satker:</span> {{ $detailKabagAktif->satker ?? 'BPS Provinsi Jawa Tengah' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                            <p class="font-medium">Belum ada pejabat Kepala Bagian Umum aktif.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol di atas untuk menunjuk pejabat baru.</p>
                        </div>
                    @endif
                </div>

                {{-- Riwayat Kabag Sebelumnya (Collapsible) --}}
                <div class="px-5 py-3 bg-slate-50/50">
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 select-none py-1">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Riwayat Arsip Kabag ({{ $riwayatKabag->where('status', '!=', 'aktif')->count() }})
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-2.5 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                            <table class="w-full text-[11px] text-left">
                                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                    <tr>
                                        <th class="px-2.5 py-1.5">Tahun</th>
                                        <th class="px-2.5 py-1.5">Nama Pejabat</th>
                                        <th class="px-2.5 py-1.5 font-mono">NIP</th>
                                        <th class="px-2.5 py-1.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse($riwayatKabag as $rk)
                                        <tr class="{{ $rk->status === 'aktif' ? 'bg-amber-50/50 font-medium' : '' }}">
                                            <td class="px-2.5 py-1.5">{{ $rk->tahun ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 truncate max-w-[140px]">{{ $rk->nama ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 font-mono text-[10.5px] text-slate-500">{{ $rk->nip ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 text-center">
                                                @if($rk->status === 'aktif')
                                                    <span class="inline-flex px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                                @else
                                                    <span class="inline-flex px-1.5 py-0.2 rounded text-[9.5px] font-medium bg-slate-100 text-slate-600">Arsip</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-2.5 py-3 text-center text-slate-400 italic">Belum ada riwayat arsip.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>

            {{-- KARTU 2: PEJABAT PEMBUAT KOMITMEN (PPK) --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden flex flex-col justify-between">
                <div class="p-5 sm:p-6 border-b border-slate-100 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Pejabat Pembuat Komitmen (PPK)</h3>
                        </div>
                        <button type="button" onclick="openModalGantiPpk()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500/30 transition-all shadow-2xs shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Ganti PPK
                        </button>
                    </div>

                    @if($ppkAktif)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-800 border border-sky-200 font-bold text-base flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($ppkAktif->nama ?? 'P', 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm truncate">{{ $ppkAktif->nama }}</span>
                                </div>
                                <div class="text-[11.5px] text-slate-600 font-mono space-y-0.5">
                                    <div><span class="text-slate-400 font-sans">NIP:</span> {{ $ppkAktif->nip ?? '-' }}</div>
                                    @if($ppkAktif->nip_lama)
                                        <div><span class="text-slate-400 font-sans">NIP BPS:</span> {{ $ppkAktif->nip_lama }}</div>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 pt-0.5">
                                    <span class="text-slate-400">Satker:</span> {{ $detailPpkAktif->satker ?? 'BPS Provinsi Jawa Tengah' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                            <p class="font-medium">Belum ada Pejabat Pembuat Komitmen aktif.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol di atas untuk menunjuk PPK baru.</p>
                        </div>
                    @endif
                </div>

                {{-- Riwayat PPK Sebelumnya (Collapsible) --}}
                <div class="px-5 py-3 bg-slate-50/50">
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 select-none py-1">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Riwayat Arsip PPK ({{ $riwayatPpk->where('status', '!=', 'aktif')->count() }})
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-2.5 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                            <table class="w-full text-[11px] text-left">
                                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                    <tr>
                                        <th class="px-2.5 py-1.5">Tahun</th>
                                        <th class="px-2.5 py-1.5">Nama Pejabat</th>
                                        <th class="px-2.5 py-1.5 font-mono">NIP</th>
                                        <th class="px-2.5 py-1.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @forelse($riwayatPpk as $rp)
                                        <tr class="{{ $rp->status === 'aktif' ? 'bg-sky-50/50 font-medium' : '' }}">
                                            <td class="px-2.5 py-1.5">{{ $rp->tahun ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 truncate max-w-[140px]">{{ $rp->nama ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 font-mono text-[10.5px] text-slate-500">{{ $rp->nip ?? '-' }}</td>
                                            <td class="px-2.5 py-1.5 text-center">
                                                @if($rp->status === 'aktif')
                                                    <span class="inline-flex px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                                @else
                                                    <span class="inline-flex px-1.5 py-0.2 rounded text-[9.5px] font-medium bg-slate-100 text-slate-600">Arsip</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-2.5 py-3 text-center text-slate-400 italic">Belum ada riwayat arsip.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- GRUP 2: HAK AKSES SISTEM & PENGELOLA OPERASIONAL          --}}
    {{-- ========================================================== --}}
    <div class="space-y-6 pt-2">
        {{-- SEKSI A: ADMIN LEMBUR (OPERASIONAL) --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-50 text-indigo-700 font-bold text-xs">
                            AL
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Admin Lembur</h2>
                        <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ $admins->count() }} Petugas
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                        Petugas pengelola presensi pegawai, sinkronisasi tim kerja, generator dokumen kedinasan, dan rekapitulasi lembur.
                    </p>
                </div>

                <button type="button" onclick="openModalTambahAdmin()"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all shadow-2xs shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah Admin
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama Petugas</th>
                            <th class="px-4 py-3 font-semibold">NIP / Identitas</th>
                            <th class="px-4 py-3 font-semibold">Email Dinas</th>
                            <th class="px-4 py-3 font-semibold">Satuan Kerja</th>
                            <th class="px-4 py-3 font-semibold text-center w-28">Wewenang</th>
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
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-200/80 text-indigo-700 font-bold flex items-center justify-center shrink-0 text-[10.5px]">
                                            {{ strtoupper(substr($adm->nama, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-900 block truncate">{{ $adm->nama }}</span>
                                            @if($isMe)
                                                <span class="inline-flex items-center text-[10px] text-amber-700 font-bold">Akun Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap text-[11px]">
                                    <div>{{ $adm->nip ?? '-' }}</div>
                                    @if($adm->nip_lama)
                                        <div class="text-[10px] text-slate-400">BPS: {{ $adm->nip_lama }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-[11.5px]">
                                    {{ $adm->email ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-[11.5px]">
                                    {{ $adm->satker ?? 'BPS Provinsi Jawa Tengah' }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($isMe)
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 italic">Terkunci</span>
                                    @else
                                        <form action="{{ route('admin.manajemen-user.hapus-admin', $adm->id_pegawai) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin mencabut wewenang Admin untuk {{ addslashes($adm->nama) }}? Pegawai akan dikembalikan ke peran biasa.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-colors cursor-pointer"
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
                                <td colspan="5" class="px-4 py-7 text-center text-xs text-slate-400 italic">
                                    Belum ada pegawai yang ditunjuk sebagai Admin Lembur.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SEKSI B: SUPER ADMINISTRATOR (GOVERNANCE UTAMA) --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-rose-50 text-rose-700 font-bold text-xs">
                            SA
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Super Administrator</h2>
                        <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            {{ $superadmins->count() }} Pengendali
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                        Tingkat wewenang tertinggi dengan akses ke seluruh basis data dan konfigurasi sistem.
                        <span class="font-semibold text-rose-700">Wewenang ini bersifat permanen dan tidak dapat dicabut antar sesama superadmin.</span>
                    </p>
                </div>

                <button type="button" onclick="openModalTambahSuperadmin()"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700/30 transition-all shadow-2xs shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah Superadmin
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama Superadmin</th>
                            <th class="px-4 py-3 font-semibold">NIP / Identitas</th>
                            <th class="px-4 py-3 font-semibold">Email Dinas</th>
                            <th class="px-4 py-3 font-semibold">Tingkat Hak Akses</th>
                            <th class="px-4 py-3 font-semibold text-center w-36">Kebijakan</th>
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
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-rose-50 border border-rose-200/80 text-rose-700 font-bold flex items-center justify-center shrink-0 text-[10.5px]">
                                            {{ strtoupper(substr($sa->nama, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-900 block truncate">{{ $sa->nama }}</span>
                                            @if($isMe)
                                                <span class="inline-flex items-center text-[10px] text-amber-700 font-bold">Akun Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap text-[11px]">
                                    <div>{{ $sa->nip ?? '-' }}</div>
                                    @if($sa->nip_lama)
                                        <div class="text-[10px] text-slate-400">BPS: {{ $sa->nip_lama }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-[11.5px]">
                                    {{ $sa->email ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Super Administrator
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Permanen
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-7 text-center text-xs text-slate-400 italic">
                                    Belum ada akun Superadmin terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================== --}}
{{-- MODAL 1: GANTI KEPALA BAGIAN UMUM                          --}}
{{-- ========================================================== --}}
<div id="modalGantiKabag" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="relative bg-white rounded-2xl max-w-3xl lg:max-w-4xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-orange-100 text-orange-600 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-tight">Pergantian Kepala Bagian Umum</h3>
                    <p class="text-[11px] text-slate-500">Pilih pegawai pengganti dan tetapkan tahun periode SK aktif</p>
                </div>
            </div>
            <button type="button" onclick="closeModalGantiKabag()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.ganti-kabag') }}" method="POST" onsubmit="return validateFormPegawai('Kabag', event)">
            @csrf
            <input type="hidden" name="id_pegawai" id="inputPegawaiKabag" value="">

            {{-- 2-Column Grid Layout (Landscape) --}}
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-6">
                {{-- Kolom Kiri (md:col-span-5): Info & Parameter & Preview --}}
                <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3.5">
                        {{-- Info Otomatisasi --}}
                        <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 leading-relaxed space-y-1">
                            <p class="font-bold flex items-center gap-1.5 text-amber-800 text-xs">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Sistem Otomatisasi Terintegrasi:
                            </p>
                            <ul class="text-[11px] space-y-0.5 text-amber-900/90 pl-1 list-disc list-inside">
                                <li>Pejabat lama akan dinonaktifkan.</li>
                                <li>Pejabat baru jadi Kabag Umum aktif.</li>
                                <li>Ketua Tim Bagian Umum dialihkan.</li>
                                <li>Wewenang approval final langsung aktif.</li>
                            </ul>
                        </div>

                        {{-- Input Tahun Periode / SK --}}
                        <div>
                            <label for="tahunKabag" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tahun Periode / SK <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="tahun" id="tahunKabag" value="{{ date('Y') }}" min="2020" max="2099" required
                                class="w-full h-9.5 px-3 rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all shadow-2xs">
                        </div>
                    </div>

                    {{-- Kotak Preview Pegawai Terpilih --}}
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pegawai Terpilih</span>
                        
                        <div id="placeholderPreviewKabag" class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada pegawai dipilih</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Klik salah satu pegawai di sebelah kanan</p>
                        </div>

                        <div id="previewContainerKabag" class="hidden p-3 rounded-xl border border-amber-300 bg-amber-50/90 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div id="previewAvatarKabag" class="w-9 h-9 rounded-full bg-amber-200 text-amber-900 font-bold text-xs flex items-center justify-center shrink-0 border border-amber-300 shadow-2xs">
                                        KG
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-[#faa938] text-white">TERPILIH</span>
                                            <span id="previewSatkerKabag" class="text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-medium">BPS</span>
                                        </div>
                                        <div id="previewNameKabag" class="text-xs font-bold text-slate-900 truncate mt-0.5"></div>
                                        <div id="previewNipKabag" class="text-[10.5px] text-slate-600 font-mono"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelection('Kabag')" class="text-slate-400 hover:text-rose-600 p-1 shrink-0 transition-colors cursor-pointer" title="Batalkan pilihan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan (md:col-span-7): Pencarian & Listbox Pegawai --}}
                <div class="md:col-span-7 flex flex-col min-w-0">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Pilih Pegawai Pengganti <span class="text-rose-500">*</span></span>
                        <span id="counterPegawaiKabag" class="text-[11px] font-normal text-slate-400"></span>
                    </label>

                    {{-- Search Input --}}
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchPegawaiKabag" placeholder="Cari nama atau NIP pegawai..."
                            oninput="filterPegawaiModern('Kabag')"
                            class="w-full text-xs h-9 pl-9 pr-8 rounded-xl border border-slate-200 bg-white placeholder-slate-400 text-slate-800 focus:border-[#faa938] focus:outline-none focus:ring-2 focus:ring-[#faa938]/20 transition-all shadow-2xs">
                        <button type="button" id="clearSearchKabag" onclick="clearSearchPegawai('Kabag')" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Listbox Kartu Interaktif --}}
                    <div id="listPegawaiKabag" class="h-64 sm:h-72 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/60 p-1.5 space-y-1 shadow-inner divide-y divide-slate-100/60">
                        @foreach($semuaPegawai as $p)
                            @php
                                $isAktif = $kabagAktif && ($kabagAktif->nip === $p->nip || $kabagAktif->nip_lama === $p->nip_lama);
                                $initials = strtoupper(substr($p->nama, 0, 2));
                            @endphp
                            @if($isAktif)
                                <div class="pegawai-item flex items-center justify-between p-2 rounded-lg opacity-50 bg-slate-100/70 cursor-not-allowed select-none border border-transparent"
                                    data-id="{{ $p->id_pegawai }}"
                                    data-name="{{ strtolower($p->nama) }}"
                                    data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-700 truncate">{{ $p->nama }}</div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-200 text-slate-600 font-medium shrink-0">{{ $p->satker ?? 'BPS' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md shrink-0 ml-2">
                                        Sedang Menjabat
                                    </span>
                                </div>
                            @else
                                <div class="pegawai-item group flex items-center justify-between p-2 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-white hover:border-slate-200 hover:shadow-2xs select-none"
                                    data-id="{{ $p->id_pegawai }}"
                                    data-name="{{ strtolower($p->nama) }}"
                                    data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}"
                                    data-display-name="{{ $p->nama }}"
                                    data-display-nip="{{ $p->nip ?? ($p->nip_lama ?? '-') }}"
                                    data-display-satker="{{ $p->satker ?? 'BPS' }}"
                                    onclick="selectPegawaiModern('Kabag', '{{ $p->id_pegawai }}', this)">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-bold text-xs flex items-center justify-center shrink-0 border border-amber-200 group-hover:scale-105 transition-transform">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-800 group-hover:text-slate-900 truncate">{{ $p->nama }}</div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-200/70 text-slate-600 font-medium shrink-0">{{ $p->satker ?? 'BPS' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="status-indicator shrink-0 ml-2">
                                        <div class="radio-circle w-4.5 h-4.5 rounded-full border border-slate-300 flex items-center justify-center group-hover:border-amber-400 transition-all">
                                            <svg class="check-icon hidden w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Empty state pencarian --}}
                        <div id="emptyPegawaiKabag" class="hidden py-8 text-center text-slate-400">
                            <svg class="w-7 h-7 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-600">Pegawai tidak ditemukan</p>
                            <p class="text-[11px] text-slate-400">Coba kata kunci nama atau NIP lain</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 bg-slate-50/80 border-t border-slate-100">
                <button type="button" onclick="closeModalGantiKabag()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-200/70 rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-[#faa938] hover:brightness-95 rounded-xl transition-all shadow-xs cursor-pointer">
                    Simpan Pergantian Kabag
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 1.5: GANTI PEJABAT PEMBUAT KOMITMEN (PPK)            --}}
{{-- ========================================================== --}}
<div id="modalGantiPpk" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="relative bg-white rounded-2xl max-w-3xl lg:max-w-4xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-sky-100 text-sky-600 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-tight">Ganti Pejabat Pembuat Komitmen (PPK)</h3>
                    <p class="text-[11px] text-slate-500">Pilih pejabat penandatangan SPKL dan dokumen anggaran aktif</p>
                </div>
            </div>
            <button type="button" onclick="closeModalGantiPpk()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.ganti-ppk') }}" method="POST" onsubmit="return validateFormPegawai('Ppk', event)">
            @csrf
            <input type="hidden" name="id_pegawai" id="inputPegawaiPpk" value="">

            {{-- 2-Column Grid Layout (Landscape) --}}
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-6">
                {{-- Kolom Kiri (md:col-span-5): Info & Parameter & Preview --}}
                <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3.5">
                        {{-- Info Otomatisasi --}}
                        <div class="p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-900 leading-relaxed space-y-1">
                            <p class="font-bold flex items-center gap-1.5 text-sky-800 text-xs">
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Sistem Otomatisasi Terintegrasi:
                            </p>
                            <ul class="text-[11px] space-y-0.5 text-sky-900/90 pl-1 list-disc list-inside">
                                <li>Pejabat PPK lama di database dinonaktifkan.</li>
                                <li>Pejabat baru didaftarkan sebagai PPK aktif.</li>
                                <li>Dokumen SPKL & anggaran otomatis pakai PPK baru.</li>
                            </ul>
                        </div>

                        {{-- Input Tahun Periode / SK --}}
                        <div>
                            <label for="tahunPpk" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tahun Periode / SK <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="tahun" id="tahunPpk" value="{{ date('Y') }}" min="2020" max="2099" required
                                class="w-full h-9.5 px-3 rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 transition-all shadow-2xs">
                        </div>
                    </div>

                    {{-- Kotak Preview Pegawai Terpilih --}}
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pegawai Terpilih</span>
                        
                        <div id="placeholderPreviewPpk" class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada pegawai dipilih</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Klik salah satu pegawai di sebelah kanan</p>
                        </div>

                        <div id="previewContainerPpk" class="hidden p-3 rounded-xl border border-sky-300 bg-sky-50/90 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div id="previewAvatarPpk" class="w-9 h-9 rounded-full bg-sky-200 text-sky-900 font-bold text-xs flex items-center justify-center shrink-0 border border-sky-300 shadow-2xs">
                                        PK
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-sky-600 text-white">TERPILIH</span>
                                            <span id="previewSatkerPpk" class="text-[10px] px-1.5 py-0.2 rounded bg-sky-100 text-sky-800 font-medium">BPS</span>
                                        </div>
                                        <div id="previewNamePpk" class="text-xs font-bold text-slate-900 truncate mt-0.5"></div>
                                        <div id="previewNipPpk" class="text-[10.5px] text-slate-600 font-mono"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelection('Ppk')" class="text-slate-400 hover:text-rose-600 p-1 shrink-0 transition-colors cursor-pointer" title="Batalkan pilihan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan (md:col-span-7): Pencarian & Listbox Pegawai --}}
                <div class="md:col-span-7 flex flex-col min-w-0">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Pilih Pegawai Pengganti <span class="text-rose-500">*</span></span>
                        <span id="counterPegawaiPpk" class="text-[11px] font-normal text-slate-400"></span>
                    </label>

                    {{-- Search Input --}}
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchPegawaiPpk" placeholder="Cari nama atau NIP pegawai..."
                            oninput="filterPegawaiModern('Ppk')"
                            class="w-full text-xs h-9 pl-9 pr-8 rounded-xl border border-slate-200 bg-white placeholder-slate-400 text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 transition-all shadow-2xs">
                        <button type="button" id="clearSearchPpk" onclick="clearSearchPegawai('Ppk')" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Listbox Kartu Interaktif --}}
                    <div id="listPegawaiPpk" class="h-64 sm:h-72 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/60 p-1.5 space-y-1 shadow-inner divide-y divide-slate-100/60">
                        @foreach($semuaPegawai as $p)
                            @php
                                $isAktif = $ppkAktif && ($ppkAktif->nip === $p->nip || $ppkAktif->nip_lama === $p->nip_lama);
                                $initials = strtoupper(substr($p->nama, 0, 2));
                            @endphp
                            @if($isAktif)
                                <div class="pegawai-item flex items-center justify-between p-2 rounded-lg opacity-50 bg-slate-100/70 cursor-not-allowed select-none border border-transparent"
                                    data-id="{{ $p->id_pegawai }}"
                                    data-name="{{ strtolower($p->nama) }}"
                                    data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-700 truncate">{{ $p->nama }}</div>
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-200 text-slate-600 font-medium shrink-0">{{ $p->satker ?? 'BPS' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md shrink-0 ml-2">
                                        Sedang Menjabat
                                    </span>
                                </div>
                            @else
                                <div class="pegawai-item group flex items-center justify-between p-2 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-white hover:border-slate-200 hover:shadow-2xs select-none"
                                    data-id="{{ $p->id_pegawai }}"
                                    data-name="{{ strtolower($p->nama) }}"
                                    data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}"
                                    data-display-name="{{ $p->nama }}"
                                    data-display-nip="{{ $p->nip ?? ($p->nip_lama ?? '-') }}"
                                    data-display-satker="{{ $p->satker ?? 'BPS' }}"
                                    onclick="selectPegawaiModern('Ppk', '{{ $p->id_pegawai }}', this)">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-800 font-bold text-xs flex items-center justify-center shrink-0 border border-sky-200 group-hover:scale-105 transition-transform">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-800 group-hover:text-slate-900 truncate">{{ $p->nama }}</div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                                <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-200/70 text-slate-600 font-medium shrink-0">{{ $p->satker ?? 'BPS' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="status-indicator shrink-0 ml-2">
                                        <div class="radio-circle w-4.5 h-4.5 rounded-full border border-slate-300 flex items-center justify-center group-hover:border-sky-400 transition-all">
                                            <svg class="check-icon hidden w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Empty state pencarian --}}
                        <div id="emptyPegawaiPpk" class="hidden py-8 text-center text-slate-400">
                            <svg class="w-7 h-7 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-600">Pegawai tidak ditemukan</p>
                            <p class="text-[11px] text-slate-400">Coba kata kunci nama atau NIP lain</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 bg-slate-50/80 border-t border-slate-100">
                <button type="button" onclick="closeModalGantiPpk()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-200/70 rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition-all shadow-xs cursor-pointer">
                    Simpan Penetapan PPK
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 2: TAMBAH ADMIN LEMBUR                               --}}
{{-- ========================================================== --}}
<div id="modalTambahAdmin" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="relative bg-white rounded-2xl max-w-3xl lg:max-w-4xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 leading-tight">Tambah Admin Lembur</h3>
                    <p class="text-[11px] text-slate-500">Pilih pegawai untuk diberikan wewenang pengelolaan modul lembur</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahAdmin()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.tambah-admin') }}" method="POST" onsubmit="return validateFormPegawai('Admin', event)">
            @csrf
            <input type="hidden" name="id_pegawai" id="inputPegawaiAdmin" value="">

            {{-- 2-Column Grid Layout (Landscape) --}}
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-6">
                {{-- Kolom Kiri (md:col-span-5): Info & Preview --}}
                <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3.5">
                        <div class="p-3.5 rounded-xl bg-indigo-50/80 border border-indigo-100 text-xs text-indigo-900 leading-relaxed space-y-1.5">
                            <p class="font-bold flex items-center gap-1.5 text-indigo-800 text-xs">
                                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Wewenang Akses Admin:
                            </p>
                            <p class="text-[11px] text-indigo-900/90 leading-relaxed">
                                Pegawai yang diangkat sebagai Admin Lembur memiliki wewenang untuk:
                            </p>
                            <ul class="text-[11px] space-y-0.5 text-indigo-900/90 pl-1 list-disc list-inside">
                                <li>Unggah & kelola data presensi harian.</li>
                                <li>Monitoring & koreksi transaksi lembur.</li>
                                <li>Penyusunan tim & generate dokumen SPKL.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Kotak Preview Pegawai Terpilih --}}
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pegawai Terpilih</span>
                        
                        <div id="placeholderPreviewAdmin" class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada pegawai dipilih</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Klik salah satu pegawai di sebelah kanan</p>
                        </div>

                        <div id="previewContainerAdmin" class="hidden p-3 rounded-xl border border-indigo-300 bg-indigo-50/90 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div id="previewAvatarAdmin" class="w-9 h-9 rounded-full bg-indigo-200 text-indigo-900 font-bold text-xs flex items-center justify-center shrink-0 border border-indigo-300 shadow-2xs">
                                        AD
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-indigo-600 text-white">TERPILIH</span>
                                            <span id="previewSatkerAdmin" class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 font-medium">BPS</span>
                                        </div>
                                        <div id="previewNameAdmin" class="text-xs font-bold text-slate-900 truncate mt-0.5"></div>
                                        <div id="previewNipAdmin" class="text-[10.5px] text-slate-600 font-mono"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelection('Admin')" class="text-slate-400 hover:text-rose-600 p-1 shrink-0 transition-colors cursor-pointer" title="Batalkan pilihan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan (md:col-span-7): Pencarian & Listbox Pegawai --}}
                <div class="md:col-span-7 flex flex-col min-w-0">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Pilih Pegawai <span class="text-rose-500">*</span></span>
                        <span id="counterPegawaiAdmin" class="text-[11px] font-normal text-slate-400"></span>
                    </label>

                    {{-- Search Input --}}
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchPegawaiAdmin" placeholder="Ketik nama atau NIP pegawai..."
                            oninput="filterPegawaiModern('Admin')"
                            class="w-full text-xs h-9 pl-9 pr-8 rounded-xl border border-slate-200 bg-white placeholder-slate-400 text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-2xs">
                        <button type="button" id="clearSearchAdmin" onclick="clearSearchPegawai('Admin')" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Listbox Kartu Interaktif --}}
                    <div id="listPegawaiAdmin" class="h-64 sm:h-72 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/60 p-1.5 space-y-1 shadow-inner divide-y divide-slate-100/60">
                        @foreach($semuaPegawai->whereNotIn('role', ['admin', 'superadmin']) as $p)
                            @php
                                $initials = strtoupper(substr($p->nama, 0, 2));
                            @endphp
                            <div class="pegawai-item group flex items-center justify-between p-2 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-white hover:border-slate-200 hover:shadow-2xs select-none"
                                data-id="{{ $p->id_pegawai }}"
                                data-name="{{ strtolower($p->nama) }}"
                                data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}"
                                data-display-name="{{ $p->nama }}"
                                data-display-nip="{{ $p->nip ?? ($p->nip_lama ?? '-') }}"
                                data-display-satker="{{ $p->satker ?? 'BPS' }}"
                                onclick="selectPegawaiModern('Admin', '{{ $p->id_pegawai }}', this)">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs flex items-center justify-center shrink-0 border border-indigo-200 group-hover:scale-105 transition-transform">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-semibold text-slate-800 group-hover:text-slate-900 truncate">{{ $p->nama }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                            <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-200/70 text-slate-600 font-medium shrink-0">{{ $p->satker ?? 'BPS' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="status-indicator shrink-0 ml-2">
                                    <div class="radio-circle w-4.5 h-4.5 rounded-full border border-slate-300 flex items-center justify-center group-hover:border-indigo-400 transition-all">
                                        <svg class="check-icon hidden w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Empty state pencarian --}}
                        <div id="emptyPegawaiAdmin" class="hidden py-8 text-center text-slate-400">
                            <svg class="w-7 h-7 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-600">Pegawai tidak ditemukan</p>
                            <p class="text-[11px] text-slate-400">Coba kata kunci nama atau NIP lain</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 bg-slate-50/80 border-t border-slate-100">
                <button type="button" onclick="closeModalTambahAdmin()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-200/70 rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-xs cursor-pointer">
                    Angkat Sebagai Admin
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL 3: TAMBAH SUPERADMIN (DENGAN PERINGATAN WAJIB)       --}}
{{-- ========================================================== --}}
<div id="modalTambahSuperadmin" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="relative bg-white rounded-2xl max-w-3xl lg:max-w-4xl w-full shadow-2xl border border-rose-200 overflow-hidden transform transition-all my-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose-100 bg-rose-50/70">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-100 text-rose-600 shadow-2xs">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-rose-900 leading-tight">Penambahan Super Administrator</h3>
                    <p class="text-[11px] text-rose-600/80">Promosi permanen hak akses tertinggi kendali sistem</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahSuperadmin()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('admin.manajemen-user.tambah-superadmin') }}" method="POST" onsubmit="return validateFormPegawai('Superadmin', event)">
            @csrf
            <input type="hidden" name="id_pegawai" id="inputPegawaiSuperadmin" value="">

            {{-- 2-Column Grid Layout (Landscape) --}}
            <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-6">
                {{-- Kolom Kiri (md:col-span-5): Peringatan & Konfirmasi & Preview --}}
                <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        {{-- KOTAK PERINGATAN WAJIB SESUAI INSTRUKSI --}}
                        <div class="p-3.5 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-950 space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-xs text-amber-900">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                PERINGATAN KONFIRMASI:
                            </div>
                            <p class="text-xs font-semibold text-rose-700 bg-rose-50 p-2 rounded-lg border border-rose-200">
                                "beneran mau ngasih superadmin, nanti gabisa dicabut lagi"
                            </p>
                            <p class="text-[11px] text-amber-800 leading-relaxed">
                                Super Administrator memiliki kendali penuh atas sistem dan basis data. Sesuai aturan keamanan sistem, <span class="font-bold underline">superadmin tidak dapat mencabut hak akses superadmin lain</span>.
                            </p>
                        </div>

                        {{-- Checkbox Konfirmasi Ganda --}}
                        <label class="flex items-start gap-2.5 cursor-pointer p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                            <input type="checkbox" name="konfirmasi" value="1" id="chkKonfirmasiSuperadmin" required
                                onchange="toggleBtnSubmitSuperadmin(this)"
                                class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 h-4 w-4 cursor-pointer">
                            <span class="text-[11.5px] text-slate-700 leading-snug font-medium">
                                Saya memahami wewenang ini dan menyetujui hak Super Administrator secara <span class="text-rose-600 font-bold">permanen</span>.
                            </span>
                        </label>
                    </div>

                    {{-- Kotak Preview Pegawai Terpilih --}}
                    <div>
                        <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pegawai Terpilih</span>
                        
                        <div id="placeholderPreviewSuperadmin" class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada pegawai dipilih</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Klik salah satu pegawai di sebelah kanan</p>
                        </div>

                        <div id="previewContainerSuperadmin" class="hidden p-3 rounded-xl border border-rose-300 bg-rose-50/90 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div id="previewAvatarSuperadmin" class="w-9 h-9 rounded-full bg-rose-200 text-rose-900 font-bold text-xs flex items-center justify-center shrink-0 border border-rose-300 shadow-2xs">
                                        SA
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-600 text-white">TERPILIH</span>
                                            <span id="previewSatkerSuperadmin" class="text-[10px] px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 font-medium">Role: User</span>
                                        </div>
                                        <div id="previewNameSuperadmin" class="text-xs font-bold text-slate-900 truncate mt-0.5"></div>
                                        <div id="previewNipSuperadmin" class="text-[10.5px] text-slate-600 font-mono"></div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelection('Superadmin')" class="text-slate-400 hover:text-rose-600 p-1 shrink-0 transition-colors cursor-pointer" title="Batalkan pilihan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan (md:col-span-7): Pencarian & Listbox Pegawai --}}
                <div class="md:col-span-7 flex flex-col min-w-0">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Pilih Pegawai / Admin yang Dipromosikan <span class="text-rose-500">*</span></span>
                        <span id="counterPegawaiSuperadmin" class="text-[11px] font-normal text-slate-400"></span>
                    </label>

                    {{-- Search Input --}}
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchPegawaiSuperadmin" placeholder="Ketik nama atau NIP..."
                            oninput="filterPegawaiModern('Superadmin')"
                            class="w-full text-xs h-9 pl-9 pr-8 rounded-xl border border-slate-200 bg-white placeholder-slate-400 text-slate-800 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all shadow-2xs">
                        <button type="button" id="clearSearchSuperadmin" onclick="clearSearchPegawai('Superadmin')" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Listbox Kartu Interaktif --}}
                    <div id="listPegawaiSuperadmin" class="h-64 sm:h-72 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/60 p-1.5 space-y-1 shadow-inner divide-y divide-slate-100/60">
                        @foreach($semuaPegawai->where('role', '!=', 'superadmin') as $p)
                            @php
                                $initials = strtoupper(substr($p->nama, 0, 2));
                            @endphp
                            <div class="pegawai-item group flex items-center justify-between p-2 rounded-lg cursor-pointer transition-all border border-transparent hover:bg-white hover:border-slate-200 hover:shadow-2xs select-none"
                                data-id="{{ $p->id_pegawai }}"
                                data-name="{{ strtolower($p->nama) }}"
                                data-nip="{{ $p->nip ?? ($p->nip_lama ?? '') }}"
                                data-display-name="{{ $p->nama }}"
                                data-display-nip="{{ $p->nip ?? ($p->nip_lama ?? '-') }}"
                                data-display-satker="Role: {{ ucfirst($p->role) }}"
                                onclick="selectPegawaiModern('Superadmin', '{{ $p->id_pegawai }}', this)">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-800 font-bold text-xs flex items-center justify-center shrink-0 border border-rose-200 group-hover:scale-105 transition-transform">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-semibold text-slate-800 group-hover:text-slate-900 truncate">{{ $p->nama }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                            <span class="font-mono text-[10.5px]">NIP: {{ $p->nip ?? ($p->nip_lama ?? '-') }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-rose-100 text-rose-700 font-medium shrink-0">Role: {{ ucfirst($p->role) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="status-indicator shrink-0 ml-2">
                                    <div class="radio-circle w-4.5 h-4.5 rounded-full border border-slate-300 flex items-center justify-center group-hover:border-rose-400 transition-all">
                                        <svg class="check-icon hidden w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Empty state pencarian --}}
                        <div id="emptyPegawaiSuperadmin" class="hidden py-8 text-center text-slate-400">
                            <svg class="w-7 h-7 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <p class="text-xs font-medium text-slate-600">Pegawai tidak ditemukan</p>
                            <p class="text-[11px] text-slate-400">Coba kata kunci nama atau NIP lain</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 bg-slate-50/80 border-t border-slate-100">
                <button type="button" onclick="closeModalTambahSuperadmin()"
                    class="px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-200/70 rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitSuperadmin" disabled
                    class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 disabled:cursor-not-allowed rounded-xl transition-all shadow-xs cursor-pointer">
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
        clearSearchPegawai('Kabag');
        setTimeout(() => document.getElementById('searchPegawaiKabag')?.focus(), 50);
    }
    function closeModalGantiKabag() {
        document.getElementById('modalGantiKabag').classList.add('hidden');
    }

    // Modal Ganti PPK
    function openModalGantiPpk() {
        document.getElementById('modalGantiPpk').classList.remove('hidden');
        clearSearchPegawai('Ppk');
        setTimeout(() => document.getElementById('searchPegawaiPpk')?.focus(), 50);
    }
    function closeModalGantiPpk() {
        document.getElementById('modalGantiPpk').classList.add('hidden');
    }

    // Modal Tambah Admin
    function openModalTambahAdmin() {
        document.getElementById('modalTambahAdmin').classList.remove('hidden');
        clearSearchPegawai('Admin');
        setTimeout(() => document.getElementById('searchPegawaiAdmin')?.focus(), 50);
    }
    function closeModalTambahAdmin() {
        document.getElementById('modalTambahAdmin').classList.add('hidden');
    }

    // Modal Tambah Superadmin
    function openModalTambahSuperadmin() {
        document.getElementById('modalTambahSuperadmin').classList.remove('hidden');
        clearSearchPegawai('Superadmin');
        setTimeout(() => document.getElementById('searchPegawaiSuperadmin')?.focus(), 50);
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

    // Pilih item pegawai pada listbox interaktif modern
    function selectPegawaiModern(type, id, element) {
        const input = document.getElementById('inputPegawai' + type);
        const list = document.getElementById('listPegawai' + type);
        const placeholder = document.getElementById('placeholderPreview' + type);
        const preview = document.getElementById('previewContainer' + type);
        const previewName = document.getElementById('previewName' + type);
        const previewNip = document.getElementById('previewNip' + type);
        const previewSatker = document.getElementById('previewSatker' + type);
        const previewAvatar = document.getElementById('previewAvatar' + type);

        if (!input || !list) return;

        // Set value input tersembunyi
        input.value = id;

        // Konfigurasi style aksen tema
        const themeConfig = {
            'Kabag': {
                selectedBg: 'bg-amber-50/90',
                selectedBorder: 'border-amber-300',
                ringClass: 'ring-amber-300/60',
                radioBg: 'bg-[#faa938]',
                radioBorder: 'border-[#faa938]'
            },
            'Ppk': {
                selectedBg: 'bg-sky-50/90',
                selectedBorder: 'border-sky-300',
                ringClass: 'ring-sky-300/60',
                radioBg: 'bg-sky-600',
                radioBorder: 'border-sky-600'
            },
            'Admin': {
                selectedBg: 'bg-indigo-50/90',
                selectedBorder: 'border-indigo-300',
                ringClass: 'ring-indigo-300/60',
                radioBg: 'bg-indigo-600',
                radioBorder: 'border-indigo-600'
            },
            'Superadmin': {
                selectedBg: 'bg-rose-50/90',
                selectedBorder: 'border-rose-300',
                ringClass: 'ring-rose-300/60',
                radioBg: 'bg-rose-600',
                radioBorder: 'border-rose-600'
            }
        };

        const theme = themeConfig[type] || themeConfig['Kabag'];

        // Bersihkan seleksi aktif pada semua baris di list ini
        const items = list.querySelectorAll('.pegawai-item');
        items.forEach(item => {
            item.classList.remove(
                'bg-amber-50/90', 'border-amber-300', 'ring-amber-300/60',
                'bg-sky-50/90', 'border-sky-300', 'ring-sky-300/60',
                'bg-indigo-50/90', 'border-indigo-300', 'ring-indigo-300/60',
                'bg-rose-50/90', 'border-rose-300', 'ring-rose-300/60',
                'ring-1', 'shadow-xs'
            );
            item.classList.add('border-transparent');
            const radio = item.querySelector('.radio-circle');
            if (radio) {
                radio.classList.remove(
                    'bg-[#faa938]', 'border-[#faa938]',
                    'bg-sky-600', 'border-sky-600',
                    'bg-indigo-600', 'border-indigo-600',
                    'bg-rose-600', 'border-rose-600'
                );
                radio.classList.add('border-slate-300');
            }
            const check = item.querySelector('.check-icon');
            if (check) check.classList.add('hidden');
        });

        // Terapkan seleksi aktif pada elemen yang diklik
        element.classList.remove('border-transparent');
        element.classList.add(theme.selectedBg, theme.selectedBorder, 'ring-1', theme.ringClass, 'shadow-xs');

        const activeRadio = element.querySelector('.radio-circle');
        if (activeRadio) {
            activeRadio.classList.remove('border-slate-300');
            activeRadio.classList.add(theme.radioBg, theme.radioBorder);
        }
        const activeCheck = element.querySelector('.check-icon');
        if (activeCheck) activeCheck.classList.remove('hidden');

        // Tampilkan kotak preview pegawai terpilih di kolom kiri
        const displayName = element.dataset.displayName || '';
        const displayNip = element.dataset.displayNip || '-';
        const displaySatker = element.dataset.displaySatker || 'BPS';
        const initials = displayName ? displayName.substring(0, 2).toUpperCase() : 'PG';

        if (previewName) previewName.textContent = displayName;
        if (previewNip) previewNip.textContent = 'NIP: ' + displayNip;
        if (previewSatker) previewSatker.textContent = displaySatker;
        if (previewAvatar) previewAvatar.textContent = initials;

        if (placeholder) placeholder.classList.add('hidden');
        if (preview) preview.classList.remove('hidden');
    }

    // Filter baris pegawai berdasarkan kata kunci pencarian
    function filterPegawaiModern(type) {
        const searchInput = document.getElementById('searchPegawai' + type);
        const clearBtn = document.getElementById('clearSearch' + type);
        const list = document.getElementById('listPegawai' + type);
        const emptyState = document.getElementById('emptyPegawai' + type);
        const counter = document.getElementById('counterPegawai' + type);

        if (!searchInput || !list) return;

        const query = searchInput.value.toLowerCase().trim();

        if (clearBtn) {
            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        const items = list.querySelectorAll('.pegawai-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = item.dataset.name || '';
            const nip = item.dataset.nip || '';

            if (query === '' || name.includes(query) || nip.includes(query)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        if (counter) {
            counter.textContent = query.length > 0 ? visibleCount + ' ditemukan' : '';
        }
    }

    // Bersihkan kotak pencarian
    function clearSearchPegawai(type) {
        const searchInput = document.getElementById('searchPegawai' + type);
        if (searchInput) {
            searchInput.value = '';
            filterPegawaiModern(type);
            searchInput.focus();
        }
    }

    // Batalkan pilihan pegawai
    function clearSelection(type) {
        const input = document.getElementById('inputPegawai' + type);
        const list = document.getElementById('listPegawai' + type);
        const placeholder = document.getElementById('placeholderPreview' + type);
        const preview = document.getElementById('previewContainer' + type);

        if (input) input.value = '';

        if (list) {
            const items = list.querySelectorAll('.pegawai-item');
            items.forEach(item => {
                item.classList.remove(
                    'bg-amber-50/90', 'border-amber-300', 'ring-amber-300/60',
                    'bg-sky-50/90', 'border-sky-300', 'ring-sky-300/60',
                    'bg-indigo-50/90', 'border-indigo-300', 'ring-indigo-300/60',
                    'bg-rose-50/90', 'border-rose-300', 'ring-rose-300/60',
                    'ring-1', 'shadow-xs'
                );
                item.classList.add('border-transparent');
                const radio = item.querySelector('.radio-circle');
                if (radio) {
                    radio.classList.remove(
                        'bg-[#faa938]', 'border-[#faa938]',
                        'bg-sky-600', 'border-sky-600',
                        'bg-indigo-600', 'border-indigo-600',
                        'bg-rose-600', 'border-rose-600'
                    );
                    radio.classList.add('border-slate-300');
                }
                const check = item.querySelector('.check-icon');
                if (check) check.classList.add('hidden');
            });
        }

        if (preview) preview.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
    }

    // Validasi submit form agar pegawai wajib dipilih
    function validateFormPegawai(type, e) {
        const input = document.getElementById('inputPegawai' + type);
        if (!input || !input.value) {
            if (e) e.preventDefault();
            const list = document.getElementById('listPegawai' + type);
            if (list) {
                list.classList.add('ring-2', 'ring-rose-500', 'border-rose-400');
                setTimeout(() => list.classList.remove('ring-2', 'ring-rose-500', 'border-rose-400'), 1500);
            }
            alert('Silakan pilih salah satu pegawai terlebih dahulu pada daftar.');
            return false;
        }
        return true;
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

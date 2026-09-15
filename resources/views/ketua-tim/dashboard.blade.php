@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- HERO --}}
<section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 md:px-10 lg:px-20 lg:py-8 mb-10">
    <div class="relative flex flex-col items-center overflow-visible rounded-[24px] bg-[#f9b800] px-5 py-6 sm:px-8 sm:py-8 lg:flex-row lg:items-center lg:px-10 lg:py-12">

        {{-- Text --}}
        <div class="z-10 text-center lg:flex-1 lg:pr-[420px] lg:text-left">
            <h2 class="mb-3 text-xl font-semibold leading-snug sm:text-2xl lg:text-3xl">
                Selamat Datang, {{ session('user')['nama'] }}
            </h2>
            <p class="text-sm leading-relaxed text-gray-900 sm:text-base lg:text-[17px]">
                Kelola pengajuan lembur dan pantau perkembangannya dengan lebih mudah.
            </p>
        </div>

        {{-- Hero Image --}}
        <img
            class="mt-6 w-full max-w-[230px] object-contain drop-shadow-xl sm:max-w-[280px] md:max-w-[340px] lg:absolute lg:-right-6 lg:mt-0 lg:max-w-[450px] xl:max-w-[500px]"
            style="top: -60px"
            src="{{ asset('images/2.svg') }}"
            alt="Hero"
        />
    </div>
</section>

{{-- MAIN CONTENT --}}
<section class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 md:px-10 lg:px-20">

    {{-- Metric Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Total pengajuan</p>
            <p class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Bulan ini</p>
        </div>

        {{-- Diproses --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5 cursor-pointer hover:bg-yellow-50 hover:ring-2 hover:ring-yellow-300 transition"
            onclick="bukaModalPending()">
            <p class="mb-2 text-xs text-gray-500">Diproses</p>
            <p class="text-2xl font-semibold text-yellow-700 sm:text-3xl" id="cardDiproses">{{ $stats['diproses'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Menunggu review · klik untuk lihat</p>
        </div>

        {{-- Disetujui --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Disetujui</p>
            <p class="text-2xl font-semibold text-green-700 sm:text-3xl">{{ $stats['disetujui'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Pengajuan bulan ini</p>
        </div>

        {{-- Ditolak --}}
        <div class="rounded-2xl bg-gray-50 p-4 sm:p-5">
            <p class="mb-2 text-xs text-gray-500">Ditolak</p>
            <p class="text-2xl font-semibold text-red-700 sm:text-3xl">{{ $stats['ditolak'] }}</p>
            <p class="mt-1 text-xs text-gray-400">Pengajuan bulan ini</p>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 xl:gap-6">

        {{-- LEFT CONTENT --}}
        <div class="xl:col-span-3 rounded-2xl border border-slate-100 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-800 sm:text-lg">Pengajuan Lembur</h3>
                    <p class="text-xs text-slate-500 sm:text-sm">Daftar pengajuan lembur terbaru dari anggota tim</p>
                </div>
                <a href="{{ route('ketua-tim.pengajuan') }}"
                    class="inline-flex items-center justify-center rounded-full border border-black/15 px-3 py-2 text-xs font-semibold hover:bg-gray-50 sm:px-4 sm:text-sm">
                    Lihat Semua
                </a>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-sm text-center">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Nama Pegawai</th>
                            <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Tanggal</th>
                            <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Jam</th>
                            <th class="px-3 py-3 font-medium sm:px-5 sm:py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pengajuan as $p)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-3 py-3 font-medium text-slate-800 sm:px-5 sm:py-4">{{ $p->nama_pegawai }}</td>
                                <td class="px-3 py-3 sm:px-5 sm:py-4">
                                    {{ \Carbon\Carbon::parse($p->date)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-3 py-3 sm:px-5 sm:py-4">
                                    {{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : '-' }}
                                </td>
                                <td class="px-3 py-3 sm:px-5 sm:py-4">
                                    @if ($p->status === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-600">Menunggu</span>
                                    @elseif ($p->status === 'approved')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">Disetujui</span>
                                    @elseif ($p->status === 'rejected')
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600">Ditolak</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">Belum ada pengajuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIGHT CONTENT --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5">
                <h3 class="text-base font-semibold text-slate-800 sm:text-lg">Lembur Hari Ini</h3>
                <p class="text-xs text-slate-500 sm:text-sm">Pegawai yang sedang / akan lembur hari ini</p>
            </div>
            <div class="space-y-4 p-5">
                @forelse ($lemburHariIni as $l)
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-slate-800">{{ $l->nama_pegawai }}</p>
                        <span class="whitespace-nowrap text-sm text-slate-500">
                            {{ $l->jam_mulai_disetujui ? substr($l->jam_mulai_disetujui, 0, 5) . ' - ' . substr($l->jam_selesai_disetujui, 0, 5) : '-' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada lembur hari ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- Modal Pending --}}
<div id="modalPending"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Daftar Pengajuan</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="modalSubtitle">Memuat data...</p>
            </div>
            <button onclick="tutupModalPending()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto max-h-80 px-6 py-3" id="modalBody"></div>
        <div class="px-6 py-3 bg-gray-50 border-t text-right">
            <button onclick="tutupModalPending()" class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
        </div>
    </div>
</div>

<script>
    const modalEl   = document.getElementById('modalPending');
    const modalBody = document.getElementById('modalBody');
    const modalSub  = document.getElementById('modalSubtitle');
    const cardCount = document.getElementById('cardDiproses');

    function bukaModalPending() {
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
        muatDataPending();
    }

    function tutupModalPending() {
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
    }

    modalEl.addEventListener('click', e => {
        if (e.target === modalEl) tutupModalPending();
    });

    async function muatDataPending() {
        modalBody.innerHTML = `<p class="text-sm text-gray-400 text-center py-8">Memuat...</p>`;
        try {
            const res  = await fetch('{{ route('ketua-tim.dashboard.pending') }}');
            const data = await res.json();

            if (data.length === 0) {
                modalSub.textContent = '0 pengajuan';
                modalBody.innerHTML  = `<p class="text-sm text-gray-400 text-center py-8">Belum ada pengajuan</p>`;
                return;
            }

            modalSub.textContent = `${data.length} pengajuan menunggu persetujuan`;
            modalBody.innerHTML = data.map(item => `
                <div class="flex items-center justify-between py-3 border-b last:border-0" id="row-${item.id_transaksi}">
                    <div>
                        <p class="text-sm font-medium text-gray-800">${item.nama}</p>
                        <p class="text-xs text-gray-400">${formatTanggal(item.date)}</p>
                    </div>
                    <button onclick="approveSatu(${item.id_transaksi})"
                        class="ml-4 text-xs bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg transition">
                        ✓ Setujui
                    </button>
                </div>
            `).join('');
        } catch (e) {
            modalBody.innerHTML = `<p class="text-sm text-red-400 text-center py-8">Gagal memuat data.</p>`;
        }
    }

    async function approveSatu(id) {
        const btn = document.querySelector(`#row-${id} button`);
        btn.disabled    = true;
        btn.textContent = '...';

        try {
            const res = await fetch(`{{ url('/ketua-tim/transaksi') }}/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();
            if (!res.ok || !data.success) throw new Error('Gagal');

            document.getElementById(`row-${id}`).remove();

            const sisa = modalBody.querySelectorAll('[id^="row-"]').length;
            modalSub.textContent = `${sisa} pengajuan menunggu persetujuan`;
            if (cardCount) cardCount.textContent = sisa;

            if (sisa === 0) {
                modalBody.innerHTML = `<p class="text-sm text-gray-400 text-center py-8">Semua pengajuan sudah disetujui.</p>`;
            }
        } catch (e) {
            btn.disabled    = false;
            btn.textContent = '✓ Setujui';
            alert('Gagal menyetujui. Coba lagi.');
        }
    }

    function formatTanggal(str) {
        return new Date(str).toLocaleDateString('id-ID', {
            day: 'numeric', month: 'long', year: 'numeric'
        });
    }
</script>

@endsection
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Hero --}}
<section class="mx-auto max-w-7xl pt-4 sm:pt-6 lg:pt-10 pb-2 px-4 sm:px-8 md:px-10 lg:px-20">
  <div class="relative bg-gradient-to-r from-[#faa938] via-[#f9b800] to-[#f59e0b]
              rounded-2xl lg:rounded-[28px]
              p-4 sm:p-6 lg:px-10 lg:py-8
              shadow-sm
              overflow-hidden lg:overflow-visible">

    {{-- Ambient decorative background glows --}}
    <div class="absolute -top-12 -right-12 w-48 h-48 sm:w-64 sm:h-64 bg-white/20 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-40 h-40 sm:w-56 sm:h-56 bg-amber-700/10 rounded-full blur-xl pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/[0.02] to-transparent pointer-events-none rounded-2xl lg:rounded-[28px]"></div>

    <div class="relative z-10 flex flex-row items-center sm:items-end justify-between gap-3 sm:gap-6 lg:gap-8">
      {{-- TEXT (flex-1 ensures it takes available space and NEVER collides with image) --}}
      <div class="flex-1 min-w-0 text-left py-1 sm:py-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 mb-2 sm:mb-3 rounded-full text-[11px] sm:text-xs font-bold bg-white/35 text-slate-950 backdrop-blur-md ring-1 ring-white/40 shadow-xs">
          <span class="text-xs sm:text-sm">👋</span>
          <span>Selamat Datang</span>
        </span>
        <h2 class="text-base sm:text-xl lg:text-2xl xl:text-3xl font-semibold text-slate-950 tracking-tight leading-snug mb-1 sm:mb-1.5 break-words">
          {{ session('user')['nama'] }}
        </h2>
        <p class="text-[11px] sm:text-sm lg:text-[15px] font-medium leading-relaxed text-slate-900/85 line-clamp-2 sm:line-clamp-none max-w-xl">
          Kelola pengajuan lembur dan pantau perkembangannya dengan lebih mudah.
        </p>
      </div>

      {{-- HERO IMAGE (shrink-0 with proportional scaling prevents collision at all widths) --}}
      <div class="shrink-0 flex items-end justify-end max-w-[32%] xs:max-w-[36%] sm:max-w-[40%] lg:max-w-[340px] xl:max-w-[380px]">
        <img
          class="w-full h-auto object-contain object-bottom drop-shadow-md lg:drop-shadow-xl select-none pointer-events-none
                 lg:-mt-10 lg:-mb-4 transform transition-transform duration-300 hover:scale-105"
          src="{{ asset('images/2.svg') }}"
          alt="Hero"
        />
      </div>
    </div>
  </div>
</section>

{{-- Konten Utama --}}
<section class="mx-auto max-w-7xl px-4 sm:px-8 md:px-10 lg:px-20 pb-10">

  {{-- Metric Cards --}}
  <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-2xl bg-gray-50 p-5">
      <p class="text-xs text-gray-500 mb-2">Total pengajuan</p>
      <p class="text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
      <p class="mt-1 text-xs text-slate-500">Bulan ini</p>
    </div>
    <div class="rounded-2xl bg-gray-50 p-5 cursor-pointer hover:bg-yellow-50 hover:ring-2 hover:ring-yellow-300 transition"
        onclick="bukaModalPending()">
      <p class="text-xs text-gray-500 mb-2">Diproses</p>
      <p class="text-3xl font-semibold text-yellow-700" id="cardDiproses">{{ $stats['diproses'] }}</p>
      <p class="mt-1 text-xs text-slate-500">Menunggu review · klik untuk lihat</p>
    </div>
    <div class="rounded-2xl bg-gray-50 p-5">
      <p class="text-xs text-gray-500 mb-2">Disetujui</p>
      <p class="text-3xl font-semibold text-green-700">{{ $stats['disetujui'] }}</p>
      <p class="mt-1 text-xs text-slate-500">Pengajuan bulan ini</p>
    </div>
    <div class="rounded-2xl bg-gray-50 p-5">
      <p class="text-xs text-gray-500 mb-2">Ditolak</p>
      <p class="text-3xl font-semibold text-red-700">{{ $stats['ditolak'] }}</p>
      <p class="mt-1 text-xs text-slate-500">Pengajuan bulan ini</p>
    </div>
  </div>

  {{-- Grid Utama --}}
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

    {{-- Kolom Kiri (8/12) --}}
    <div class="lg:col-span-8 flex flex-col gap-6">

      {{-- Lembur Hari Ini --}}
      <div class="rounded-2xl border border-black/10 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-black/10 p-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold">Lembur hari ini</h2>
            <p class="mt-1 text-sm text-gray-600">
              {{ now()->translatedFormat('l, d F Y') }}
               - {{ $lemburHariIni->count() }} pegawai
            </p>
          </div>
          <a href="{{ route('admin.daftar_hadir') }}"
             class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold border border-black/15 hover:bg-gray-50">
            Lihat semua
          </a>
        </div>
        <div class="divide-y divide-black/5">
          @forelse ($lemburHariIni as $item)
            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
              <div class="min-w-0">
                <p class="text-sm font-semibold">{{ $item->nama_pegawai }}</p>
                <p class="mt-1 text-sm text-gray-500">
                  {{ $item->nama_tim ?? '-' }} &middot;
                  @if ($item->status === 'approved' && $item->jam_mulai_disetujui)
                    {{ substr($item->jam_mulai_disetujui, 0, 5) }} &ndash; {{ substr($item->jam_selesai_disetujui, 0, 5) }}
                  @elseif ($item->jam_mulai)
                    {{ substr($item->jam_mulai, 0, 5) }} &ndash; {{ substr($item->jam_selesai, 0, 5) }}
                  @else
                    -
                  @endif
                </p>
              </div>
              <div class="flex items-center gap-3">
                @if ($item->status === 'approved')
                  <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Disetujui</span>
                @elseif ($item->status === 'menunggu_kabag')
                  <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">Menunggu Kabag</span>
                @elseif ($item->status === 'pending')
                  <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">Diproses</span>
                @elseif ($item->status === 'rejected')
                  <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Ditolak</span>
                @endif
              </div>
            </div>
          @empty
            <div class="p-5 text-sm text-slate-500 font-medium text-center">
              Tidak ada pengajuan lembur hari ini.
            </div>
          @endforelse
        </div>
      </div>

      {{-- Manajemen Dokumen --}}
      <div class="rounded-2xl border border-black/10 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-black/10 p-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-semibold">Manajemen dokumen</h2>
            <p class="mt-1 text-sm text-gray-600">SPKL &amp; laporan lembur</p>
          </div>
          <a href="{{ route('admin.dokumen') }}"
            class="inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold border border-black/15 hover:bg-gray-50">
            Kelola
          </a>
        </div>
        <div class="divide-y divide-black/5">
          @foreach ($types as $item)
            @php $dok = $item['doc']; @endphp
            <div class="flex items-center gap-4 p-5 {{ is_null($dok) ? 'bg-amber-50' : '' }}">
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl
                  {{ is_null($dok) ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}
                  text-xs font-semibold">
                PDF
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ $item['label'] }} - {{ now()->translatedFormat('F Y') }}
                </p>
                <p class="mt-0.5 text-xs {{ is_null($dok) ? 'text-amber-600' : 'text-gray-500' }}">
                  {{ is_null($dok)
                      ? 'Belum di-generate'
                      : 'Terbit: ' . \Carbon\Carbon::parse($dok->generated_at)->translatedFormat('d F Y') }}
                </p>
              </div>
              <div class="flex-shrink-0">
                @if (!is_null($dok))
                  <a href="{{ route('admin.dokumen.view', $dok->id_dokumen) }}"
                    class="inline-flex items-center justify-center rounded-full px-3 py-1.5 text-xs font-semibold border border-black/15 hover:bg-gray-50">
                    Preview
                  </a>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>

    </div>

    {{-- Kolom Kanan (4/12) --}}
    <div class="lg:col-span-4 flex flex-col gap-6">

      {{-- Notifikasi --}}
      <div class="rounded-2xl border border-black/10 bg-white shadow-sm">
        <div class="flex items-start justify-between border-b border-black/10 p-5">
          <div>
            <h2 class="text-lg font-semibold">Notifikasi</h2>
            <p class="mt-1 text-sm text-gray-600">Item yang perlu perhatian</p>
          </div>
          @if ($notifikasi->count() > 0)
            <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
              {{ $notifikasi->count() }}
            </span>
          @endif
        </div>
        <div class="divide-y divide-black/5">
          @forelse ($notifikasi as $notif)
            <div class="flex items-start gap-3 p-4">
              <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full
                @if ($notif['level'] === 'danger') bg-red-500
                @elseif ($notif['level'] === 'warning') bg-yellow-400
                @else bg-blue-400
                @endif">
              </span>
              <div>
                <p class="text-sm text-gray-800">{{ $notif['pesan'] }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $notif['waktu'] }}</p>
              </div>
            </div>
          @empty
            <div class="p-5 text-sm text-slate-500 font-medium text-center">
              Semua sudah beres!
            </div>
          @endforelse
        </div>
      </div>

      {{-- Ringkasan Bulan Ini --}}
      <div class="rounded-2xl border border-black/10 bg-white shadow-sm">
        <div class="border-b border-black/10 p-5">
          <h2 class="text-lg font-semibold">Ringkasan bulan ini</h2>
          <p class="mt-1 text-sm text-gray-600">{{ now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="divide-y divide-black/5 p-2">
          <div class="flex items-center justify-between px-3 py-3">
            <span class="text-sm text-gray-600">Presensi lengkap</span>
            @if ($ringkasan['presensi_lengkap'])
              <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Sudah</span>
            @else
              <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Belum</span>
            @endif
          </div>
          <div class="flex items-center justify-between px-3 py-3">
            <span class="text-sm text-gray-600">SPKL bulan ini</span>
            @if ($ringkasan['spkl_generated'])
              <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Sudah</span>
            @else
              <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Belum</span>
            @endif
          </div>
          <div class="flex items-center justify-between px-3 py-3">
            <span class="text-sm text-gray-600">Laporan bulan ini</span>
            @if ($ringkasan['laporan_generated'])
              <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Sudah</span>
            @else
              <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Belum</span>
            @endif
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Modal Pending --}}
  <div id="modalPending"
      class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <div>
          <h2 class="text-base font-semibold text-gray-800">Pengajuan Pending</h2>
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

</section>

<script>
  function getGreeting(hour) {
    if (hour >= 4 && hour < 11) return "Selamat Pagi";
    if (hour >= 11 && hour < 15) return "Selamat Siang";
    if (hour >= 15 && hour < 18) return "Selamat Sore";
    return "Selamat Malam";
  }
  const el = document.getElementById("greeting");
  if (el) el.textContent = `${getGreeting(new Date().getHours())}, {{ session('user')['nama'] ?? 'Admin' }}`;

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
  modalEl.addEventListener('click', e => { if (e.target === modalEl) tutupModalPending(); });

  async function muatDataPending() {
    modalBody.innerHTML = `<p class="text-sm text-gray-400 text-center py-8">Memuat...</p>`;
    try {
      const res  = await fetch('{{ route('admin.dashboard.pending') }}');
      const data = await res.json();
      if (data.length === 0) {
        modalSub.textContent = '0 pengajuan';
        modalBody.innerHTML  = `<p class="text-sm text-gray-400 text-center py-8">Tidak ada pengajuan pending.</p>`;
        return;
      }
      modalSub.textContent = `${data.length} pengajuan menunggu persetujuan`;
      modalBody.innerHTML = data.map(item => `
        <div class="flex items-center justify-between py-3 border-b last:border-0" id="row-${item.id_transaksi}">
          <div>
            <div class="flex items-center gap-2">
              <p class="text-sm font-medium text-gray-800">${item.nama}</p>
              ${item.status === 'menunggu_kabag' ? '<span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full font-medium">Menunggu Kabag</span>' : ''}
            </div>
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
            const res = await fetch(`/admin/transaksi/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            const data = await res.json();

            if (!res.ok || !data.success) throw new Error('Gagal');

            // Hapus baris dari daftar
            document.getElementById(`row-${id}`).remove();

            // Update counter
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

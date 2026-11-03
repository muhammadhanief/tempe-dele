@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="w-4/5 mx-auto py-8">

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Left: Profile Card -->
    <div class="lg:col-span-1">
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500">
            <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2-8 4.5A1.5 1.5 0 0 0 5.5 20h13A1.5 1.5 0 0 0 20 18.5C20 16 16.42 14 12 14Z"/>
            </svg>
          </div>

          <div class="min-w-0">
            <p class="break-words text-lg font-semibold text-gray-900">
              {{ $user->nama ?? 'Nama Lengkap' }}
            </p>
            <p class="break-words text-sm text-gray-500">
              {{ !empty($user->email) ? $user->email : '-' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Details -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Identitas Pegawai -->
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h2 class="text-base font-semibold text-gray-900">Identitas Pegawai</h2>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-gray-50 p-4">
            <p class="text-xs font-medium text-gray-500">NIP Lama</p>
            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->nip_lama ?? '-' }}</p>
          </div>

          <div class="rounded-xl bg-gray-50 p-4">
            <p class="text-xs font-medium text-gray-500">NIP Baru</p>
            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->nip ?? '-' }}</p>
          </div>
        </div>
      </div>

      <!-- Kepegawaian -->
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h2 class="text-base font-semibold text-gray-900">Kepegawaian</h2>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-gray-50 p-4">
            <p class="text-xs font-medium text-gray-500">Golongan Akhir</p>
            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->golongan ?? '-' }}</p>
          </div>

          <div class="rounded-xl bg-gray-50 p-4">
            <p class="text-xs font-medium text-gray-500">Satuan Kerja</p>
            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->satker ?? '-' }}</p>
          </div>
        </div>
      </div>

      <!-- Optional: Note -->
      <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-5">
        <p class="text-sm text-gray-600">
          Jika ada data yang tidak sesuai, hubungi admin untuk pembaruan.
        </p>
      </div>
    </div>
  </div>
</div>

@endsection

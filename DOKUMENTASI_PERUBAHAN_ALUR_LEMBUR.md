# 📄 Dokumentasi Perubahan Sistem: Alur Persetujuan Lembur Bertingkat (Kabag Umum)

Dokumen ini merangkum seluruh latar belakang, perubahan alur, daftar file yang diubah/dibuat, serta potongan kode (*source code*) yang diterapkan pada sistem **TEMPE DELE**.

---

## 📌 1. Latar Belakang & Alur Baru

### Alur Lama:
```
Pegawai ──► Ketua Tim (Setujui / Tolak) ──► Selesai
```

### Alur Baru:
```
[Pegawai Ajukan Lembur] 
       │
       ├──► Tim Bagian Umum (Ketua Tim = Kabag Umum) ─────────────► Status: Menunggu Kabag Umum
       │    (Langsung masuk ke antrean Persetujuan Kabag Umum)               │
       │                                                                     │
       └──► Tim Lain (SID, Humas, Sosial, dll.)                              │
                   │                                                         │
                   ▼                                                         │
            [Persetujuan Ketua Tim]                                          │
                   │                                                         │
                   ├─── Jika DITOLAK ────────────────────────────────────────┼───► Status: Ditolak (Selesai)
                   │                                                         │
                   └─── Jika DISETUJUI ──────────────────────────────────────┘
                               │
                               ▼
            [Menu Tunggal: Persetujuan Kabag Umum]
                               │
                       ┌───────┴───────┐
                       ▼               ▼
               Kabag SETUJU       Kabag TOLAK
                       │               │
                       ▼               ▼
               Disetujui Final      Ditolak
```

---

## 🗂️ 2. Daftar File yang Diubah & Dibuat

| No | Tipe | File | Keterangan |
|:---:|:---:|:---|:---|
| 1 | **Baru** | `database/migrations/2026_09_15_000001_add_kabag_approval_to_t_transaksi.php` | Migrasi penambahan kolom `note_kabag` dan `approved_kabag_at`. |
| 2 | **Baru** | `database/migrations/2026_09_15_000002_widen_status_column_in_t_transaksi.php` | Migrasi pelebaran tipe data kolom `status` dari `VARCHAR(10)` ke `VARCHAR(30)` agar menampung `menunggu_kabag`. |
| 3 | **Ubah** | `app/Models/Transaksi.php` | Menambahkan kolom baru ke `$fillable`. |
| 4 | **Ubah** | `app/Http/Controllers/ketuatim/PengajuanController.php` | Logika approval Ketua Tim (pengecualian Tim Bagian Umum vs Tim Lain). |
| 5 | **Ubah** | `app/Http/Controllers/ketuatim/DashboardController.php` | Penyesuaian quick approve di dashboard Ketua Tim. |
| 6 | **Baru** | `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` | Controller khusus Kabag Umum untuk meninjau & meng-ACC lembur tim lain. |
| 7 | **Ubah** | `routes/web.php` | Rute grup `/kabag-umum` dan rute pengujian `/dev-login/{nip}`. |
| 8 | **Ubah** | `resources/views/partials/sidebar.blade.php` | Deteksi wewenang Kabag Umum & penambahan menu **Persetujuan Kabag Umum**. |
| 9 | **Baru** | `resources/views/kabag-umum/pengajuan.blade.php` | Halaman utama persetujuan lembur Kabag Umum + modal keputusan & presensi. |
| 10 | **Ubah** | `resources/views/lembur.blade.php` | Tampilan status pegawai (`Menunggu Kabag`) & riwayat catatan terpisah. |
| 11 | **Ubah** | `resources/views/ketua-tim/pengajuan.blade.php` | Penyesuaian status badge `Menunggu Kabag`. |
| 12 | **Ubah** | `resources/views/admin/pengajuan.blade.php` | Penyesuaian status badge `Menunggu Kabag`. |
| 13 | **Ubah** | `.gitignore` | Mengabaikan folder `__MACOSX/`. |

---

## 💻 3. Rincian Perubahan & Potongan Kode (*Source Code*)

### 1. Migrasi Database: `database/migrations/2026_09_15_000001_add_kabag_approval_to_t_transaksi.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('t_transaksi', 'note_kabag')) {
                $table->text('note_kabag')->nullable()->after('note');
            }
            if (!Schema::hasColumn('t_transaksi', 'approved_kabag_at')) {
                $table->dateTime('approved_kabag_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('t_transaksi', 'note_kabag')) {
                $table->dropColumn('note_kabag');
            }
            if (Schema::hasColumn('t_transaksi', 'approved_kabag_at')) {
                $table->dropColumn('approved_kabag_at');
            }
        });
    }
};
```

---

### 2. Model: `app/Models/Transaksi.php`
Menambahkan `note_kabag`, `approved_at`, dan `approved_kabag_at` ke properti `$fillable`:
```php
    protected $fillable = [
        'submitted_by_NIP',
        'date',
        'jam_mulai',
        'jam_selesai',
        'jam_mulai_disetujui',
        'jam_selesai_disetujui',
        'uraian',
        'approver_employee_id',
        'tim_kode_tim',
        'status',
        'submitted_at',
        'hari',
        'note',
        'note_kabag',
        'approved_at',
        'approved_kabag_at',
    ];
```

---

### 3. Logika Approval Ketua Tim: `app/Http/Controllers/ketuatim/PengajuanController.php`
Pada method `approve()`, ditambahkan pengecekan apakah tim yang diajukan adalah Bagian Umum atau approver adalah Kabag Umum:
```php
        // Cek apakah tim adalah Tim Bagian Umum atau approver adalah Kabag Umum
        $isTimBagianUmum = false;
        if (!empty($transaksi->tim_kode_tim)) {
            $tim = DB::table('m_tim')->where('kode_tim', $transaksi->tim_kode_tim)->first();
            if ($tim && (str_contains(strtolower($tim->nama_tim), 'bagian umum') || $tim->kode_tim === 'QrBzgE3O3lEqVPjy')) {
                $isTimBagianUmum = true;
            }
        }

        $nipSess = session('user')['nip'] ?? null;
        $nipLamaSess = session('user')['nip_lama'] ?? null;
        $isApproverKabag = DB::table('m_pejabat')
            ->where('jabatan', 'Kepala Bagian Umum')
            ->where('status', 'aktif')
            ->where(function($q) use ($nipSess, $nipLamaSess) {
                if ($nipSess) $q->where('nip', $nipSess);
                if ($nipLamaSess) $q->orWhere('nip_lama', $nipLamaSess);
            })->exists();

        $finalStatus = $request->status;
        $approvedKabagAt = null;

        if ($request->status === 'approved') {
            if ($isTimBagianUmum || $isApproverKabag) {
                // Tim Bagian Umum langsung disetujui final
                $finalStatus = 'approved';
                $approvedKabagAt = now();
            } else {
                // Tim Lain naik ke persetujuan Kabag Umum
                $finalStatus = 'menunggu_kabag';
            }
        }

        $updateData = [
            'status'                => $finalStatus,
            'jam_mulai_disetujui'   => $jamMulaiDisetujui->format('H:i:s'),
            'jam_selesai_disetujui' => $jamSelesaiDisetujui->format('H:i:s'),
            'note'                  => $noteKetua !== '' ? $noteKetua : null,
            'eligible'              => null,
            'approved_at'           => now()->toDateString(),
        ];

        if ($approvedKabagAt) {
            $updateData['approved_kabag_at'] = $approvedKabagAt;
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);
```

---

### 4. Controller Kabag Umum: `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`
Controller baru dengan fungsi utama:
- `checkAccess()`: Memastikan hanya Kabag Umum (berdasarkan tabel `m_pejabat` / ketua tim umum / admin) yang dapat mengakses.
- `index()`: Mengambil pengajuan dari **seluruh tim lain** dengan status default `menunggu_kabag`.
- `approve()`: Menyimpan keputusan Kabag Umum (`approved` atau `rejected`), `note_kabag`, dan `approved_kabag_at`.
- `presensi()`: Menyediakan data kehadiran presensi pegawai.

```php
    public function approve(Request $request, $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status'                => 'required|in:approved,rejected',
            'jam_mulai_disetujui'   => 'nullable',
            'jam_selesai_disetujui' => 'nullable',
            'note_kabag'            => 'nullable|string',
        ]);

        $transaksi = DB::table('t_transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $noteKabag = trim($request->note_kabag ?? '');

        $updateData = [
            'status'            => $request->status,
            'note_kabag'        => $noteKabag !== '' ? $noteKabag : null,
            'approved_kabag_at' => now(),
        ];

        if ($request->status === 'approved') {
            $jamMulai = $request->filled('jam_mulai_disetujui') 
                ? $request->jam_mulai_disetujui 
                : ($transaksi->jam_mulai_disetujui ?? $transaksi->jam_mulai);

            $jamSelesai = $request->filled('jam_selesai_disetujui') 
                ? $request->jam_selesai_disetujui 
                : ($transaksi->jam_selesai_disetujui ?? $transaksi->jam_selesai);

            if ($jamMulai) {
                $dtMulai = Carbon::parse($transaksi->date . ' ' . $jamMulai);
                $updateData['jam_mulai_disetujui'] = $dtMulai->format('H:i:s');
            }

            if ($jamSelesai) {
                $dtSelesai = Carbon::parse($transaksi->date . ' ' . $jamSelesai);
                if (isset($dtMulai) && $dtSelesai->lessThan($dtMulai)) {
                    $dtSelesai->addDay();
                }
                $updateData['jam_selesai_disetujui'] = $dtSelesai->format('H:i:s');
            }
        }

        DB::table('t_transaksi')->where('id_transaksi', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => $request->status === 'approved' 
                ? 'Pengajuan lembur berhasil disetujui (Final).' 
                : 'Pengajuan lembur berhasil ditolak.'
        ]);
    }
```

---

### 5. Rute: `routes/web.php`
```php
    // ─── Kabag Umum (Persetujuan Tim Lain) ───────────────
    Route::prefix('kabag-umum')->name('kabag-umum.')->middleware('checksession')->group(function () {
        Route::get('/pengajuan', [KabagUmumPengajuanController::class, 'index'])->name('pengajuan');
        Route::post('/pengajuan/{id}/approve', [KabagUmumPengajuanController::class, 'approve'])->name('pengajuan.approve');
        Route::get('/pengajuan/{id}/presensi', [KabagUmumPengajuanController::class, 'presensi'])->name('pengajuan.presensi');
        Route::get('/pengajuan/pegawai', [KabagUmumPengajuanController::class, 'semuaPegawai'])->name('pengajuan.pegawai');
    });
```

---

### 6. Sidebar: `resources/views/partials/sidebar.blade.php`
Mendeteksi apakah NIP pengguna terdaftar sebagai Kabag Umum di `m_pejabat`, dan jika ya, menambahkan menu **Persetujuan Kabag Umum**:
```php
        $nipSess = session('user')['nip'] ?? null;
        $nipLamaSess = session('user')['nip_lama'] ?? null;

        $isKabagUmum = false;
        if ($nipSess || $nipLamaSess) {
            $isKabagUmum = \DB::table('m_pejabat')
                ->where('jabatan', 'Kepala Bagian Umum')
                ->where('status', 'aktif')
                ->where(function ($q) use ($nipSess, $nipLamaSess) {
                    if ($nipSess) $q->where('nip', $nipSess);
                    if ($nipLamaSess) $q->orWhere('nip_lama', $nipLamaSess);
                })->exists();

            if (!$isKabagUmum && $role === 'ketua_tim') {
                $isKabagUmum = \DB::table('m_tim')
                    ->where(function ($q) use ($nipSess, $nipLamaSess) {
                        if ($nipSess) $q->where('nipbaru_ketua', $nipSess);
                        if ($nipLamaSess) $q->orWhere('niplama_ketua', $nipLamaSess);
                    })
                    ->where(function ($q) {
                        $q->where('nama_tim', 'like', '%Bagian Umum%')
                          ->orWhere('kode_tim', 'QrBzgE3O3lEqVPjy');
                    })
                    ->exists();
            }
        }

        $pendingKabagCount = $isKabagUmum 
            ? \DB::table('t_transaksi')->where('status', 'menunggu_kabag')->count() 
            : 0;
```
Item menu yang ditambahkan pada `$menuItems`:
```php
            if ($isKabagUmum) {
                $menuItems[] = [
                    'label'  => 'Persetujuan Kabag Umum',
                    'path'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'route'  => 'kabag-umum.pengajuan',
                    'active' => request()->routeIs('kabag-umum.pengajuan*'),
                    'badge'  => $pendingKabagCount > 0 ? $pendingKabagCount : null,
                ];
            }
```

---

### 7. Tampilan Status di Pegawai: `resources/views/lembur.blade.php`
```blade
<td class="px-3 py-3 text-center text-xs text-gray-900">
    @if($t->status === 'pending')
        <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menunggu Ketua</span>
    @elseif($t->status === 'menunggu_kabag')
        <span class="whitespace-nowrap rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800">Menunggu Kabag</span>
    @elseif($t->status === 'approved')
        <span class="whitespace-nowrap rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Disetujui</span>
    @elseif($t->status === 'rejected')
        <span class="whitespace-nowrap rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-600">Ditolak</span>
    @else
        <span class="text-xs text-gray-400">-</span>
    @endif
</td>

<td class="px-3 py-3 text-xs text-gray-900">
    <div class="max-w-[180px] space-y-1 text-left">
        @if(!empty($t->note))
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Ketua Tim:</span>
                <div class="text-[11px] text-gray-700 italic break-words">{{ $t->note }}</div>
            </div>
        @endif
        @if(!empty($t->note_kabag))
            <div>
                <span class="text-[10px] font-bold uppercase text-blue-600">Kabag Umum:</span>
                <div class="text-[11px] text-blue-900 italic break-words">{{ $t->note_kabag }}</div>
            </div>
        @endif
        @if(empty($t->note) && empty($t->note_kabag))
            <span class="text-gray-400">-</span>
        @endif
    </div>
</td>
```

---

## 🚀 4. Cara Pengujian Lokal

1. **Jalankan Server**:
   ```bash
   php artisan serve --port=8000
   npm run dev
   ```
2. **Login Tanpa Password SSO**:
   - Sebagai Kabag Umum (Bpk. Joko Suwarjo): `http://127.0.0.1:8000/dev-login/197106131993121001`
   - Sebagai Ketua Tim Lain (Bpk. Subuh Sukmono): `http://127.0.0.1:8000/dev-login/197503151996121001`
   - Sebagai Pegawai Biasa: `http://127.0.0.1:8000/dev-login/196911261989031001`

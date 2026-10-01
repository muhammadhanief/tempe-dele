# 📄 Dokumentasi Perubahan Sistem: Alur Persetujuan Lembur Bertingkat (Kabag Umum)

Dokumen ini merangkum seluruh latar belakang, perubahan alur, daftar file yang diubah/dibuat, serta potongan kode (*source code*) yang diterapkan pada sistem **TEMPE DELE**.

---

> [!TIP]
> ### 🛑 Panduan Cepat: Cara Menghapus Total Bypass Login (Jika Ingin Opsi 2 Permanen)
> Saat ini fitur bypass login dan panel testing auto-login sudah **otomatis non-aktif di server produksi** via kondisi `app()->environment('local')` (**Opsi 1**). Fitur ini tetap aktif di laptop Anda untuk mempermudah testing.
> 
> Namun, jika sewaktu-waktu Anda ingin **menghapus total fitur ini secara permanen dari source code** (**Opsi 2**), cukup hapus 2 blok kode di file berikut:
> 
> #### 1. File `routes/web.php` (Sekitar Baris 27 s.d. 61)
> Hapus seluruh blok pembungkus dev login berikut:
> ```php
> if (app()->environment('local')) {
>     Route::get('/debug-session', function () {
>         dd(session('user'));
>     })->middleware('checksession');
> 
>     Route::get('/dev-login/{nip}', function ($nip) {
>         $pegawai = \DB::table('m_pegawai')->where('nip', $nip)->orWhere('id_pegawai', $nip)->orWhere('nip_lama', $nip)->first();
>         if (!$pegawai) {
>             return response("Pegawai dengan NIP/ID {$nip} tidak ditemukan di database m_pegawai.", 404);
>         }
>         session()->put('user', [
>             'nip'       => $pegawai->nip,
>             'nip_lama'  => $pegawai->nip_lama,
>             'nama'      => $pegawai->nama,
>             'email'     => $pegawai->email,
>             'role'      => $pegawai->role,
>             'satker'    => $pegawai->satker,
>             'kd_satker' => $pegawai->kd_satker,
>         ]);
>         session()->put('logged_in', true);
>         session()->put('id_pegawai', $pegawai->id_pegawai);
>         session()->put('role', $pegawai->role);
> 
>         if ($pegawai->role === 'superadmin' || $pegawai->role === 'admin') {
>             return redirect()->route('admin.dashboard');
>         } elseif ($pegawai->role === 'ketua_tim') {
>             return redirect()->route('ketua-tim.dashboard');
>         } elseif ($pegawai->role === 'pimpinan') {
>             return redirect()->route('pimpinan.dashboard');
>         } else {
>             return redirect()->route('pegawai.dashboard');
>         }
>     })->name('dev.login');
> }
> ```
> 
> #### 2. File `resources/views/login.blade.php` (Sekitar Baris 132 s.d. 213)
> Hapus seluruh blok panel tombol testing auto-login berikut:
> ```blade
> @if (app()->isLocal())
>     <div class="mt-6 pt-5 border-t border-slate-200">
>         <div class="flex items-center justify-between mb-2.5">
>             <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">⚡ Testing Auto-Login</span>
>             <span class="text-[10px] bg-amber-100 text-amber-800 font-semibold px-2 py-0.5 rounded-full">Dev Mode</span>
>         </div>
>         ... (seluruh isi tombol cepat akun per peran) ...
>     </div>
> @endif
> ```
> *Setelah 2 blok di atas dihapus, sistem akan 100% murni hanya dapat diakses melalui login autentikasi resmi SSO BPS.*

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
| 4 | **Ubah** | `app/Http/Controllers/ketuatim/PengajuanController.php` | Logika approval Ketua Tim, penguncian status (status locking), dan validasi hak koreksi jam/catatan. |
| 5 | **Ubah** | `app/Http/Controllers/ketuatim/DashboardController.php` | Penyesuaian quick approve di dashboard Ketua Tim (hanya untuk status pending). |
| 6 | **Baru** | `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` | Controller khusus Kabag Umum untuk meninjau, meng-ACC, penguncian status, dan sorting prioritas. |
| 7 | **Ubah** | `app/Http/Controllers/admin/PengajuanController.php` | Pengurutan prioritas status Admin (Menunggu Kabag > Menunggu Ketua > Disetujui > Ditolak) & filter status. |
| 8 | **Ubah** | `app/Http/Controllers/admin/LemburController.php` | Pengurutan prioritas status Admin pada monitoring lembur & filter status. |
| 9 | **Ubah** | `routes/web.php` | Rute grup `/kabag-umum` dan rute pengujian `/dev-login/{nip}`. |
| 10 | **Ubah** | `resources/views/partials/sidebar.blade.php` | Deteksi wewenang Kabag Umum, penambahan menu **Persetujuan Kabag**, serta perapihan label agar tidak terpotong elipsis (`...`). |
| 11 | **Baru** | `resources/views/kabag-umum/pengajuan.blade.php` | Halaman utama persetujuan lembur Kabag Umum + modal keputusan terkunci & presensi. |
| 12 | **Ubah** | `resources/views/lembur.blade.php` | Tampilan status pegawai (`Menunggu Kabag`) & riwayat catatan terpisah. |
| 13 | **Ubah** | `resources/views/ketua-tim/pengajuan.blade.php` | Pemisahan kolom Status & Aksi, banner gembok 🔒 status terkunci, tombol Koreksi, mode koreksi penolakan. |
| 14 | **Ubah** | `resources/views/admin/pengajuan.blade.php` | Filter status dropdown & hierarki prioritas status admin. |
| 15 | **Ubah** | `resources/views/admin/lembur.blade.php` | Filter status dropdown & hierarki prioritas status admin. |
| 16 | **Ubah** | `.gitignore` | Mengabaikan folder `__MACOSX/`. |
| 17 | **Ubah** | `resources/views/ketua-tim/lembur.blade.php` | Perapihan UI/UX pengajuan lembur pribadi Ketua Tim/Kabag Umum: Card container berbingkai, empty state interaktif, penyelarasan tabel, dan modal dialog dengan docked header/footer. |
| 18 | **Baru** | `app/Http/Middleware/CheckRole.php` | Middleware otorisasi hak akses peran (RBAC) untuk memblokir akses lintas peran yang tidak sah (Error 403). |
| 19 | **Ubah** | `bootstrap/app.php` | Pendaftaran alias middleware `role` ke dalam pipeline middleware Laravel 11/12. |
| 20 | **Ubah** | `routes/web.php` | Penerapan proteksi `role` pada grup rute Admin, Ketua Tim, & Pimpinan, penguncian rute dev-login hanya di local environment, serta eliminasi rute duplikat. |
| 21 | **Ubah** | `.gitattributes` | Perbaikan urutan deteksi bahasa Blade agar tidak tertimpa oleh rule `*.php` pada GitHub Linguist. |

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
Item menu yang ditambahkan pada `$menuItems` (menggunakan label ringkas `'Persetujuan Kabag'` agar badge antrean tidak memotong teks dengan elipsis):
```php
            if ($isKabagUmum) {
                $menuItems[] = [
                    'label'  => 'Persetujuan Kabag',
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
   - Sebagai Ketua Tim SID (Bpk. Sumbodo Aji Cahyono): `http://127.0.0.1:8000/dev-login/197703081999011001`
   - Sebagai Ketua Tim Lain (Bpk. Subuh Sukmono): `http://127.0.0.1:8000/dev-login/197503151996121001`
   - Sebagai Admin (Khaerul Anam): `http://127.0.0.1:8000/dev-login/199008262014031001`
   - Sebagai Pegawai Biasa: `http://127.0.0.1:8000/dev-login/196911261989031001`

---

## 🔒 5. Pembaharuan Fitur Lanjutan (Penguncian Status, Pemisahan Kolom, & Prioritas Admin)

### A. Penguncian Status & Hak Koreksi (Status Locking)
- **Latar Belakang**: Mencegah kesalahan operasional di mana pengajuan yang sudah di-ACC/disetujui dapat diubah sewaktu-waktu menjadi ditolak atau sebaliknya.
- **Aturan Penguncian**:
  - Jika pengajuan berstatus `menunggu_kabag`, `approved` (Disetujui Final), atau `rejected` (Ditolak), status keputusan **terkunci permanen**.
  - Pilihan tombol keputusan (`Tolak` / `Setujui`) otomatis disembunyikan.
  - Terdapat **Banner Terkunci (🔒)** dengan warna indikator:
    - **Biru**: Menunggu Kabag Umum (*"Status Menunggu Kabag terkunci. Pengajuan telah diteruskan ke Kabag Umum. Anda hanya dapat mengoreksi jam disetujui dan catatan."*).
    - **Hijau**: Disetujui Final (*"Status Disetujui Final terkunci. Anda hanya dapat mengoreksi jam disetujui dan catatan."*).
    - **Merah**: Ditolak (*"Status Ditolak terkunci. Anda dapat mengoreksi catatan alasan penolakan."* - input jam otomatis disembunyikan).
  - Tombol pada tabel berlabel **`Koreksi`** dan tombol simpan di modal bertuliskan **`Simpan Koreksi`**.
- **Proteksi Backend**:
  - `KabagUmumPengajuanController.php` dan `PengajuanController.php` memverifikasi status transaksi yang ada di database. Request yang mencoba membalikkan status yang telah diproses akan ditolak dengan respons HTTP 422.

### B. Pemisahan Kolom Status & Aksi pada Ketua Tim
- Kolom tabel pengajuan pada Ketua Tim (`ketua-tim/pengajuan`) dipisahkan menjadi 2 kolom terpisah:
  1. **Kolom `Status`**: Menampilkan badge status secara mandiri (`Menunggu`, `Menunggu Kabag`, `Disetujui`, `Ditolak`).
  2. **Kolom `Aksi`**: Menampilkan tombol interaktif:
     - Tombol **`Proses`** (Oranye `#faa938`) untuk pengajuan baru yang berstatus *Menunggu*.
     - Tombol **`Koreksi`** (Outlined netral dengan ikon pensil) untuk pengajuan yang telah diproses.

### C. Hierarki Prioritas Status Admin & Filter Terintegrasi
- Default pengurutan (sorting) data pengajuan di sisi Admin (`admin/pengajuan` dan `admin/lembur`):
  1. **Priority 0**: `Menunggu Kabag` (`menunggu_kabag`)
  2. **Priority 1**: `Menunggu Ketua` (`pending`)
  3. **Priority 2**: `Disetujui` (`approved`)
  4. **Priority 3**: `Ditolak` (`rejected`)
- Dilengkapi dengan filter status dropdown di toolbar dan filter sorting tanggal (Terbaru, Terlama, Prioritas Status) yang tersinkronisasi penuh dengan filter nama pegawai dan filter bulan periode.

---

## 🎨 6. Perapihan Antarmuka Pengajuan Lembur Mandiri Ketua Tim / Kabag Umum (`ketua-tim/lembur.blade.php`) & Sidebar

Pada tahap penyempurnaan lanjutan, dilakukan audit dan penataan ulang desain visual (UI/UX) pada halaman **Lembur Mandiri** (`resources/views/ketua-tim/lembur.blade.php`) yang digunakan oleh Ketua Tim dan Kepala Bagian Umum untuk mengajukan kegiatan lembur pribadinya, serta perbaikan estetika pada menu sidebar navigasi.

### A. Permasalahan Visual Sebelumnya
1. **Tabel Mengambang Tanpa Bingkai Kartu:** Tabel diletakkan langsung di atas latar belakang halaman putih polos tanpa kontainer kartu bergaris tepi (*border*) atau bayangan halus (*shadow*), sehingga header abu-abu tabel tampak seperti garis pita yang melayang tanpa batas visual yang jelas.
2. **Tombol Aksi Tambah Terisolasi:** Tombol tambah lembur menciut menjadi lingkaran kecil 40px oranye tanpa teks label di pojok kanan layar desktop, meninggalkan ruang kosong raksasa di tengah toolbar dan membingungkan pengguna baru.
3. **Tampilan Data Kosong (*Empty State*) Polos:** Saat belum memiliki pengajuan lembur pada periode terpilih, halaman hanya menampilkan teks abu-abu kecil monoton: *"Belum ada pengajuan lembur."* tanpa ilustrasi ataupun tombol aksi cepat.
4. **Ketidaksesuaian Alignment Kolom (*Misalignment*):** Seluruh judul kolom di `<thead>` dibuat rata tengah (*center*), sedangkan isi data teks di `<tbody>` (*Tanggal, Uraian Kegiatan, Ketua Tim, Nama Tim, Catatan*) rata kiri (*left*). Selain itu terdapat kesalahan atribut `colspan="9"` padahal jumlah kolom header hanya ada 8.
5. **Tombol Keputusan Modal Terpotong di Bawah Layar (*Off-Screen*):** Dialog modal "Ajukan Lembur" sangat panjang ke bawah tanpa batas tinggi (*max-height*), *fixed header*, ataupun *fixed footer*. Akibatnya tombol **"Batal"** dan **"Kirim"** berada di luar batas layar (*off-screen*) dan memaksa pengguna men-scroll jendela luar ke bawah. Saat di-scroll ke bawah, judul modal dan tombol silang `×` ikut tergulung hilang ke atas.
6. **Label Sidebar Terpotong Elipsis:** Teks menu `"Persetujuan Kabag Umum"` terpotong menjadi `"Persetujuan Kabag... [3]"` karena keterbatasan lebar kontainer sidebar saat berdampingan dengan lencana angka antrean.

### B. Solusi & Perubahan Desain yang Diterapkan

#### 1. Perapihan Label Menu Sidebar
Mengubah properti `label` pada `resources/views/partials/sidebar.blade.php` menjadi ringkas:
```php
'label' => 'Persetujuan Kabag',
```
Hal ini memastikan teks menu tetap utuh dan lencana angka (`[ 3 ]`) tampil rapi tanpa terpotong tanda titik-titik (`...`).

#### 2. Header Halaman & Tombol Primer Proporsional
Menambahkan header halaman yang jelas dan informatif, serta mengganti tombol tambah kecil dengan tombol aksi primer yang proporsional:
```blade
{{-- Page Header --}}
<div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-800">
            Pengajuan Lembur Pribadi
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Kelola dan pantau riwayat pengajuan kegiatan lembur mandiri Anda.
        </p>
    </div>

    {{-- Tombol Ajukan Lembur --}}
    <button type="button" id="btnAjukan"
        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#faa938] px-4 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow-sm transition-all shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        <span>Ajukan Lembur</span>
    </button>
</div>
```

#### 3. Pembungkus Tabel Berbentuk Kartu Modern (*Card Container*) & Alignment Rapi
Tabel data dibungkus dalam kartu berbingkai halus (`rounded-2xl border border-gray-200/80 bg-white shadow-xs overflow-hidden`) dengan header tabel berwarna `bg-gray-50/90 border-b border-gray-200`. Alignment kolom diselaraskan secara konsisten:
- **Rata Kiri (`text-left`)**: Tanggal, Uraian Kegiatan, Ketua Tim, Nama Tim, Catatan.
- **Rata Tengah (`text-center`)**: Jam Diajukan, Status, Dokumentasi.
- **Format Header**: Menggunakan tipografi modern `text-xs font-semibold uppercase tracking-wider text-gray-600`.

#### 4. Desain *Empty State* Interaktif Lengkap dengan Tombol CTA
Ketika riwayat pengajuan lembur belum ada, sistem menyajikan tampilan kartu kosong yang ramah dan interaktif:
```blade
@empty
    <tr>
        <td colspan="8" class="px-4 py-16 text-center">
            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-[#faa938] mb-3 border border-amber-100/80 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Belum Ada Pengajuan Lembur</h3>
                <p class="text-xs text-gray-500 mb-4 text-center leading-relaxed">
                    Anda belum memiliki riwayat pengajuan kegiatan lembur mandiri pada periode ini.
                </p>
                <button type="button" onclick="openModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#faa938] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#fd9a10] hover:shadow transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Ajukan Lembur Sekarang
                </button>
            </div>
        </td>
    </tr>
@endforelse
```

#### 5. Modal Dialog dengan *Docked Header & Docked Footer*
Struktur modal dialog "Ajukan Lembur" dirombak total menggunakan model *fixed header & docked footer*:
- **Header Dialog (Sticky/Fixed)**: Memuat ikon, judul, subjudul deskriptif, dan tombol tutup silang (`×`) yang selalu berada di atas.
- **Footer Dialog (Docked/Fixed)**: Tombol **"Batal"** dan **"Kirim Pengajuan"** selalu menempel di bagian bawah dialog dan berada di dalam *viewport* layar di berbagai resolusi monitor pengguna.
- **Body Formulir (Scrollable)**: Formulir, estimasi durasi lembur, dan kanvas tanda tangan dapat di-scroll secara mandiri di dalam kontainer (`max-h-[90vh] overflow-y-auto pr-5 scrollbar-thin`).
- **Styling Input**: Seluruh kolom input dan dropdown menggunakan border lembut dengan sudut `rounded-xl` dan fokus ring khas Tempe Dele (`#faa938/20`).

### 18. Middleware Otorisasi Hak Akses Peran: `app/Http/Middleware/CheckRole.php`
Dibuat middleware mandiri untuk memvalidasi peran pengguna (*Role-Based Access Control*) pada setiap permintaan HTTP:
- Memeriksa sesi aktif pengguna (`Session::get('role')` atau atribut `user.role`).
- Menyediakan *fallback* kueri ke tabel `m_pegawai` berdasarkan NIP sesi jika data peran dalam sesi belum termuat.
- Mengizinkan peran `superadmin` mengakses semua tingkatan rute (*superuser bypass*).
- Memblokir pengguna dengan peran yang tidak berwenang dengan respon `403 Forbidden` (baik respon JSON untuk request AJAX/API maupun tampilan halaman error 403 resmi).

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Session::get('logged_in')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $userRole = Session::get('role') ?? (Session::get('user')['role'] ?? null);

        if (!$userRole && Session::has('user.nip')) {
            $userRole = DB::table('m_pegawai')->where('nip', Session::get('user')['nip'])->value('role');
            if ($userRole) {
                Session::put('role', $userRole);
            }
        }

        if ($userRole === 'superadmin') {
            return $next($request);
        }

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.'
            ], 403);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses halaman ini.');
    }
}
```

### 19. Pendaftaran Alias Middleware: `bootstrap/app.php`
Mendaftarkan alias `role` ke konfigurasi pipeline middleware aplikasi (Laravel 11/12):
```php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'checksession' => \App\Http\Middleware\CheckSession::class,
            'role'         => \App\Http\Middleware\CheckRole::class,
        ]);
    })
```

### 20. Pengamanan Rute & Restriksi RBAC: `routes/web.php`
- **Kondisional Environment pada Rute Pengujian**: Rute `/dev-login/{nip}` dan `/debug-session` dibungkus dengan `if (app()->environment('local'))` sehingga tetap aktif di lingkungan pengembangan lokal pengembang, namun otomatis mati (*404 Not Found*) di server produksi.
- **Pemasangan Middleware Role pada Grup Rute**:
  - Grup Admin (`/admin/*`): `middleware(['checksession', 'role:admin,superadmin'])`
  - Grup Ketua Tim (`/ketua-tim/*`): `middleware(['checksession', 'role:ketua_tim,admin,superadmin'])`
  - Grup Pimpinan (`/pimpinan/*`): `middleware(['checksession', 'role:pimpinan,admin,superadmin'])`
- **Pembersihan Rute Duplikat**: Menghapus deklarasi rute ganda pada `/admin/rekapitulasi`, `/admin/tim`, dan blok CRUD `/admin/pengguna`.

### 21. Perbaikan Urutan Konfigurasi GitHub Linguist: `.gitattributes`
Memperbaiki urutan aturan pemetaan bahasa pada file `.gitattributes` agar file template `.blade.php` tidak ditimpa (*overridden*) oleh aturan `*.php`:
```gitattributes
*.css diff=css
*.html diff=html
*.md diff=markdown
*.php diff=php linguist-language=PHP
*.blade.php diff=html linguist-language=Blade linguist-detectable=true
```
*(Dengan meletakkan `*.blade.php` di bawah `*.php`, GitHub Linguist mengidentifikasi file tampilan sebagai bahasa Blade secara terpisah dan memulihkan diagram persentase bahasa di repositori GitHub).*

---

## 📌 4. Komparasi Komprehensif: Dokumen Panduan Pengguna (`panduan_pengguna.pdf`) vs Sistem Saat Ini

Bagian ini mendokumentasikan hasil komparasi antara spesifikasi fitur pada dokumen panduan awal (**`public/documents/panduan_pengguna.pdf`**) dengan implementasi sistem yang berjalan **saat ini**, serta menguraikan seluruh transformasi fitur, alur birokrasi, dan teknologi yang telah diterapkan.

### A. Fitur per Role Menurut `panduan_pengguna.pdf` (Versi Awal)

Pada buku panduan awal (34 halaman), sistem hanya dirancang dengan **3 peran (*role*)**, yaitu **Pegawai**, **Ketua Tim**, dan **Admin**:

#### 1. Fitur Umum (Semua Role)
* **Filter Data**: Filter Tanggal (*datepicker*), Filter Periode (*monthpicker*), Filter Pegawai (*dropdown text input*), dan Filter Tim.
* **Elemen Navigasi & UX**: Pagination tabel, pop-up loading indikator proses, tombol download berkas (PDF / Excel).
* **Profil Pegawai**: Tampilan informasi kepegawaian *read-only* (Nama, Email, NIP Lama, NIP Baru, Golongan Akhir, Bidang/Satker).

#### 2. Role Pegawai (`user`)
* **Dashboard Pegawai**:
  * Sapaan nama pengguna.
  * Ringkasan aktivitas pengajuan lembur (*Total*, *Diproses*, *Disetujui*, *Ditolak*).
  * Daftar pengajuan lembur terbaru.
  * Widget jadwal lembur mendatang yang telah disetujui.
* **Pengajuan Lembur**:
  * Tabel riwayat pengajuan lembur mandiri.
  * Form modal tambah pengajuan lembur (Tanggal, Jam Mulai/Selesai, Uraian Tugas, Ketua Tim).
  * **Dokumentasi Lembur**: Menginput tautan (*link*) Google Drive pada pengajuan yang telah disetujui.
* **Rekapitulasi Lembur Pegawai**:
  * Tabel matriks rekapitulasi lembur bulanan (klasifikasi hari biasa vs hari libur beserta durasi jam dan *tooltip* penjelas).

#### 3. Role Ketua Tim (`ketua_tim`)
* **Dashboard Ketua Tim**:
  * Ringkasan status pengajuan lembur anggota tim (Total, Diproses, Disetujui, Ditolak).
  * Daftar pengajuan terbaru anggota tim.
  * Widget daftar pegawai tim yang lembur hari ini.
* **Daftar Pengajuan Anggota Tim**:
  * Tabel pengajuan lembur anggota tim kerja.
  * **Modal Informasi Presensi**: Melihat jam masuk, jam pulang, dan status kehadiran aktual anggota tim.
  * **Modal Keputusan Lembur**: Form persetujuan (Setujui / Tolak), koreksi jam mulai & selesai disetujui, serta catatan dari Ketua Tim.
  * **Validasi Durasi**: Peringatan visual otomatis apabila durasi lembur melebihi batas ketentuan (maksimal 4 jam di hari kerja, 6 jam di hari libur).
* **Pengajuan Lembur Mandiri Ketua Tim**:
  * Pengajuan lembur untuk diri sendiri dengan mekanisme yang sama seperti pegawai biasa.

#### 4. Role Admin (`admin`)
* **Dashboard Admin**:
  * Ringkasan statistik pengajuan satker per bulan berjalan.
  * Monitoring pegawai yang lembur pada hari berjalan.
  * Widget pemantauan ketersediaan dokumen dinas (SPKL & Laporan).
  * Notifikasi peringatan sistem (presensi yang belum diinput atau laporan yang belum di-*generate*).
  * Ringkasan status administrasi bulanan.
* **Manajemen Presensi**:
  * Unggah berkas presensi pegawai format Excel (`.xlsx`).
  * Tabel riwayat unggah berkas presensi.
  * Tampilan kalender *grid* presensi pegawai bulanan beserta pop-up detail jam masuk/pulang.
* **Manajemen Dokumen Lembur**:
  * Pembuatan (*generate*) SPKL dengan input nomor dinas resmi.
  * Pembuatan (*generate*) Laporan Lembur (terpisah PNS dan PPPK).
  * Pratinjau (*preview*) dokumen PDF dan aksi hapus dokumen.
* **Monitoring & Rekapitulasi Satker**:
  * Monitoring pengajuan lembur seluruh pegawai se-kantor BPS Jawa Tengah.
  * Rekapitulasi Lembur Satker (tabel matriks bulanan & ekspor dokumen).
  * Laporan Lembur Satker (rincian kegiatan dan ekspor format Excel/PDF).
  * Akumulasi Lembur (perhitungan uang lembur, uang makan, potongan pajak PPh, dan total bersih).
  * Daftar Hadir Lembur (daftar kehadiran lembur dengan kolom tanda tangan fisik manual & ekspor PDF).
* **Master Data**:
  * **Data Pengguna**: CRUD pegawai, pendaftaran akun lokal lengkap dengan password, dan modal ganti password manual.
  * **Data Tim**: CRUD tim kerja, kelola anggota tim (tambah/hapus anggota), dan status aktif tim.
  * **Tarif Lembur**: Pengaturan tarif uang lembur hari kerja, hari libur, uang makan, dan persentase pajak per golongan (I, II, III, IV).
  * **Data Pejabat**: Penetapan pejabat struktural tahunan (PPK, Kepala BPS, Kepala Bagian Umum).

---

### B. Transformasi & Perluasan Fitur Saat Ini

Dalam implementasi sistem saat ini, terjadi ekspansi besar pada arsitektur, alur otorisasi, dan integrasi data:

#### 1. Perluasan Role Menjadi 5 Tingkat Wewenang
Sistem saat ini tidak lagi hanya mengenal 3 peran, melainkan telah berkembang menjadi **5 peran/tingkat akses**:
1. **Pegawai (`user`)**: Mengajukan lembur, memantau alur persetujuan bertingkat, tanda tangan digital, unggah dokumen hasil kegiatan, dan rekap mandiri.
2. **Ketua Tim (`ketua_tim`)**: Meninjau presensi riil, menyetujui tahap 1 (naik ke Kabag Umum), atau menolak pengajuan anggota tim.
3. **Kepala Bagian Umum (`ketua_tim` + Pejabat Aktif Kabag Umum)**: Memiliki hak istimewa dan menu eksklusif **Persetujuan Kabag Umum** (`/kabag-umum/pengajuan`) untuk memverifikasi dan memberikan persetujuan final (*final approval*) seluruh lembur di BPS Provinsi Jawa Tengah.
4. **Pimpinan (`pimpinan` / Kepala BPS)**: Dashboard eksekutif pemantauan makro seluruh lembur satker Jawa Tengah.
5. **Admin / Superadmin**: Operasional presensi, sinkronisasi KIPAPP, generator dokumen kedinasan, pengelolaan pejabat aktif, dan tarif.

#### 2. Alur Persetujuan Bertingkat (*Tiered Approval Workflow*)
* **Tim Fungsional Biasa (SID, Humas, Sosial, Distribusi, dll.)**:
  $$\text{Pegawai Mengajukan} \longrightarrow \text{Persetujuan Ketua Tim (Status: Menunggu Kabag)} \longrightarrow \text{Persetujuan Kabag Umum (Status: Approved Final)}$$
* **Tim Bagian Umum**:
  Pengajuan anggota Tim Bagian Umum **langsung masuk ke antrean Persetujuan Kabag Umum** (status otomatis `menunggu_kabag`), menghindari duplikasi persetujuan karena Ketua Tim Bagian Umum dijabat langsung oleh Kepala Bagian Umum.
* **Penguncian Status (*Status Locking*)**:
  Begitu Kabag Umum memutuskan persetujuan (status menjadi `approved` atau `rejected`), status terkunci secara permanen dan tidak dapat diubah sembarangan; hanya jam yang disetujui atau catatan yang dapat dikoreksi.

#### 3. Catatan Evaluasi Transparan Dua Arah
Sistem memisahkan catatan persetujuan menjadi 2 entitas kolom terpisah:
* `note`: Catatan evaluasi / alasan penolakan dari Ketua Tim.
* `note_kabag`: Catatan evaluasi / arahan penolakan dari Kepala Bagian Umum.
Kedua catatan ini ditampilkan secara transparan di dashboard pegawai agar pegawai mengetahui alasan detail jika lembur disesuaikan atau ditolak.

#### 4. Digital Signature Pad & Bukti Dokumentasi Berkas Nyata
* **Tanda Tangan Digital**: Pada panduan awal, dokumen dicetak kosong untuk ditandatangani pena basah secara manual. Pada sistem saat ini, pegawai membubuhkan tanda tangan digital langsung pada layar/kanvas (`signature_pad.umd.min.js`) saat mengajukan lembur, yang otomatis tersemat di formulir dan Daftar Hadir resmi.
* **Unggah Berkas Langsung**: Pada panduan awal hanya berupa input link Google Drive, sedangkan sekarang sistem mendukung **unggah berkas gambar/dokumen langsung** (`storeDoc`) yang disimpan aman di storage server.

#### 5. Integrasi SSO BPS & Sinkronisasi Otomatis KIPAPP
* **SSO BPS & API Connect**: Menghilangkan kebutuhan manajemen akun dan password manual lokal. Pegawai melakukan otentikasi terpusat dengan akun SSO BPS Jawa Tengah.
* **Penarikan Golongan Otomatis**: Golongan kepangkatan pegawai diambil otomatis melalui API Atribut SSO BPS untuk menentukan tarif uang lembur dan makan.
* **Sinkronisasi KIPAPP**: Struktur tim kerja dan anggota tim fungsional ditarik secara dinamis dari API KIPAPP BPS tanpa perlu input manual satu per satu oleh Admin.

#### 6. Otomasi Validasi Presensi Riil (`KoreksiLembur`)
Sistem saat ini menyematkan modul logika otomatisasi kelayakan lembur berdasarkan data presensi aktual:
* **Durasi Minimal 2 Jam**: Jika jam pulang aktual pada mesin presensi menghasilkan durasi lembur kurang dari 2 jam, sistem otomatis menolak (*reject*) pengajuan dengan catatan sistem.
* **Kepatuhan WFO / WFOL**: Hanya presensi dengan status WFO (*Work from Office*) atau WFOL (*Work from Office Lembur*) yang diperhitungkan.
* **Deteksi Keterlambatan Masuk Kantor**: Hari kerja biasa mensyaratkan jam kedatangan sebelum pukul 07:31 WIB.
* **Flag Kelayakan Bisnis (`eligible`)**: Menandai secara otomatis pengajuan yang sah untuk dibayarkan uang lembur dan uang makannya sesuai regulasi keuangan negara.

---

### C. Matriks Komparasi Rinci: Panduan Awal vs Sistem Saat Ini

| Dimensi Evaluasi | Spesifikasi di Panduan PDF (Awal) | Implementasi Sistem Saat Ini |
| :--- | :--- | :--- |
| **Hierarki Role** | 3 Role: Pegawai, Ketua Tim, Admin. | **5 Tingkat Role**: Pegawai, Ketua Tim, Kabag Umum, Pimpinan, Admin/Superadmin. |
| **Alur Approval** | 1 Tingkat: Pegawai $\rightarrow$ Ketua Tim $\rightarrow$ Selesai. | **Bertingkat (*Tiered*)**: Pegawai $\rightarrow$ Ketua Tim $\rightarrow$ Kabag Umum $\rightarrow$ Final (dengan *bypass* khusus Tim Bagian Umum). |
| **Variasi Status** | 3 Status: `Diproses`, `Disetujui`, `Ditolak`. | **4 Status Dinamis**: `pending`, `menunggu_kabag`, `approved` (Final), dan `rejected`. |
| **Integritas Keputusan** | Belum ada penguncian (keputusan dapat dibolak-balik). | **Status Locking**: Keputusan final Kabag Umum terkunci dari perubahan status sepihak. |
| **Pencatatan Evaluasi** | 1 kolom catatan tunggal (`note`). | **Dua Kolom Catatan Terpisah**: `note` (Ketua Tim) dan `note_kabag` (Kabag Umum). |
| **Format Dokumentasi** | Hanya tautan teks (*link Google Drive*). | **Unggah Berkas Langsung** ke penyimpanan server + manajemen dokumentasi. |
| **Tanda Tangan Kehadiran**| Cetak dokumen kosong untuk tanda tangan basah. | **Digital Signature Pad**: Pembubuhan tanda tangan elektronik langsung via kanvas digital. |
| **Mekanisme Login** | Form login lokal dengan manajemen password manual. | **Integrasi SSO BPS & API Connect**: Single Sign-On terpusat seluruh pegawai BPS Jawa Tengah. |
| **Manajemen Tim Kerja** | Input manual tim dan anggota oleh admin. | **Sinkronisasi Otomatis KIPAPP API**: Penarikan struktur tim kerja berkala via API KIPAPP. |
| **Koreksi Presensi** | Verifikasi visual manual oleh Ketua Tim. | **Otomasi `KoreksiLembur`**: Otomatisasi reject < 2 jam, cek WFO/WFOL, dan status keterlambatan. |
| **Perhitungan Hak Keuangan** | Berdasarkan jam yang disetujui manual. | Terhubung dengan flag kelayakan `eligible` hasil sinkronisasi presensi riil. |
| **Desain Antarmuka (UI)** | Tema gelap klasik (*dark navy sidebar*). | **Modern Enterprise UI**: Tailwind CSS bertema cerah, palet warna resmi BPS (`#fd9a10`), lencana status multi-tahap, dan modal interaktif dengan *docked header/footer*. |

---

## 📌 5. Rekomendasi Roadmap Peningkatan Sistem & UX di Masa Depan

Sebagai kelanjutan dari pengembangan alur persetujuan bertingkat dan penguatan keamanan sistem, berikut adalah rekomendasi strategis yang dapat diusulkan untuk tahap pengembangan selanjutnya (maupun dicantumkan sebagai **Bab Saran pada Laporan Magang**):

### A. Pengalaman Persetujuan Pejabat (*Approval Velocity UX*)
1. **Lencana Angka Pending (*Badge Counter*) pada Menu Sidebar**:
   Menampilkan indikator jumlah pengajuan yang menunggu tindakan (*pending review*) langsung di samping label menu sidebar (misalnya: `Persetujuan Kabag (5)` atau `Pengajuan Anggota (2)`). Fitur ini memudahkan pejabat memantau adanya tugas verifikasi tanpa harus membuka tabel pengajuan terlebih dahulu.
2. **Fitur Persetujuan Masal (*Batch / Bulk Approval*)**:
   Menambahkan kotak centang (*checkbox*) pilihan pada tabel pengajuan Kabag Umum dan Ketua Tim beserta tombol utama **"Setujui yang Dipilih"**. Fitur ini memangkas waktu operasional peninjauan pengajuan lembur dalam volume besar pada akhir periode pelaporan.
3. **Indikator Lampu Presensi Otomatis (🟢 / 🟡 / 🔴)**:
   Menyematkan lencana status visual kehadiran riil langsung pada baris tabel approval tanpa mengharuskan pejabat membuka modal popup presensi satu per satu:
   - 🟢 **Hijau**: Kehadiran WFO/WFOL valid dan jam pulang aktual $\ge 2$ jam lembur.
   - 🟡 **Kuning**: Kehadiran valid namun terdapat keterlambatan kedatangan kantor (> 07:30 WIB).
   - 🔴 **Merah**: Belum ada data presensi pulang atau durasi aktual $< 2$ jam.

### B. Transparansi & Kemudahan Pegawai (*Employee Experience*)
1. **Pelacak Progres Alur Bertingkat (*Visual Stepper Tracker*)**:
   Menyediakan komponen garis waktu interaktif (*timeline stepper*) pada modal detail pengajuan pegawai:
   $$\text{[Diajukan]} \longrightarrow \text{[Persetujuan Ketua Tim]} \longrightarrow \text{[Persetujuan Kabag Umum]} \longrightarrow \text{[Selesai (Disetujui Final)]}$$
   Memberikan visibilitas penuh kepada pegawai mengenai posisi terkini berkas pengajuan dan menampilkan catatan evaluasi secara kontekstual di tiap tahapan.
2. **Kalkulator Estimasi Hak Keuangan Real-Time pada Formulir Pengajuan**:
   Memberikan umpan balik instan saat pegawai memilih jam mulai dan selesai lembur: menampilkan durasi bersih, estimasi perolehan uang lembur sesuai tarif golongan, estimasi uang makan, serta peringatan interaktif jika durasi kurang dari batas minimum 2 jam.

### C. Tata Kelola Keuangan, Akuntabilitas & Kinerja Sistem
1. **Pencatatan Jejak Audit (*Audit Trail / Activity Log*)**:
   Mencatat setiap tindakan administratif penting (perubahan jam disetujui, penolakan pengajuan, pengubahan tarif uang lembur, dan penetapan pejabat) ke dalam tabel log khusus lengkap dengan identitas pengguna, cap waktu (*timestamp*), serta alamat IP untuk keperluan audit internal (Irwil BPS / BPK).
2. **Validasi Batas Maksimal Lembur Bulanan (Pagu / Regulasi SBM)**:
   Mengintegrasikan validasi kuota akumulasi jam lembur per pegawai dalam satu bulan kalender sesuai Standar Biaya Masukan (SBM) Kementerian Keuangan untuk mencegah kelebihan alokasi anggaran satker.
3. **Optimasi Kinerja & Caching Sinkronisasi API Eksternal**:
   Menerapkan *caching* pada data struktur tim KIPAPP dan atribut SSO BPS atau memindahkannya ke tugas terjadwal malam hari (*scheduled cron task*), sehingga proses login pegawai tetap responsif dan terbebas dari risiko *timeout* saat server API eksternal sedang mengalami beban tinggi.
4. **Tampilan Kartu Responsif (*Responsive Card View*) untuk Smartphone**:
   Mengadaptasi tata letak tabel lebar menjadi kartu informasi ringkas (*thumb-friendly card view*) pada layar ponsel di bawah lebar 640px, mendukung fleksibilitas pejabat dalam memberikan persetujuan saat sedang melakukan dinas luar.

---

## 📝 6. Fitur Audit Trail & Koreksi Uraian Kegiatan Bersyarat Presensi

Fitur ini melengkapi akuntabilitas pengelolaan lembur pegawai dan memfasilitasi Ketua Tim, Admin, serta Kabag Umum untuk mengoreksi uraian kegiatan secara fleksibel namun tetap terkontrol.

### A. Latar Belakang & Wewenang
1. **Pencatatan Audit Trail Kolom Baru:**
   - `user_edited`: Menyimpan nama/NIP pihak yang melakukan pengeditan data lembur.
   - `tanggal_edited`: Menyimpan waktu (*timestamp*) saat data lembur terakhir diedit.
2. **Koreksi Uraian Kegiatan Bersyarat Presensi:**
   - Input textarea **Uraian Kegiatan** ditambahkan tepat di atas kotak **Catatan (Opsional)** pada modal aksi Ketua Tim, Admin, dan Kabag Umum.
   - **Syarat Validasi Presensi:** Hanya dapat diedit apabila **data presensi pegawai pada tanggal lembur tersebut sudah ada** di tabel `t_presensi` (`has_presensi == true`). Jika presensi belum ada, kotak uraian terkunci (*read-only*) dengan badge kuning peringatan.
3. **Pemisahan Wewenang Antar-Peran:**
   - **Pegawai (User):** Dapat mengedit pengajuan mandirinya (jam mulai/selesai, uraian kegiatan, dan tim tujuan) selama status pengajuan masih *pending* (menunggu persetujuan ketua tim).
   - **Ketua Tim:** Dapat mengoreksi uraian kegiatan pengajuan anggota timnya jika data presensi sudah tersedia.
   - **Admin:** Memiliki wewenang mengoreksi uraian kegiatan seluruh pengajuan jika data presensi sudah tersedia.
   - **Kabag Umum:** Dapat mengoreksi uraian kegiatan khusus untuk anggota **Tim Bagian Umum** (tim yang diketuainya langsung) jika data presensi tersedia. Untuk tim lain, uraian bersifat *read-only* agar tidak mengganggu alur persetujuan akhir.

### B. Daftar Berkas yang Terlibat
| No | Tipe | File | Keterangan |
|:---:|:---:|:---|:---|
| 1 | **Baru** | `database/migrations/2026_09_25_000001_add_user_edited_to_t_transaksi.php` | Migrasi penambahan kolom `user_edited` (`VARCHAR(100)`) dan `tanggal_edited` (`DATETIME`) pada `t_transaksi`. |
| 2 | **Ubah** | `app/Models/Transaksi.php` | Menambahkan kolom `user_edited` dan `tanggal_edited` ke properti `$fillable`. |
| 3 | **Ubah** | `app/Http/Controllers/ketuatim/PengajuanController.php` | Validasi input `uraian`, verifikasi presensi, dan pencatatan audit trail pada persetujuan Ketua Tim. |
| 4 | **Ubah** | `resources/views/ketua-tim/pengajuan.blade.php` | Textarea Uraian di atas Catatan (Opsional), badge presensi, dan update DOM realtime. |
| 5 | **Ubah** | `app/Http/Controllers/admin/PengajuanController.php` | Validasi input `uraian`, verifikasi presensi, dan pencatatan audit trail pada Admin. |
| 6 | **Ubah** | `resources/views/admin/pengajuan.blade.php` | Textarea Uraian di atas Catatan pada modal Admin dan update DOM realtime. |
| 7 | **Ubah** | `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` | Deteksi Tim Bagian Umum, verifikasi presensi, dan update uraian tanpa mengganggu approval tim lain. |
| 8 | **Ubah** | `resources/views/kabag-umum/pengajuan.blade.php` | Textarea Uraian di atas Catatan Kabag Umum (editable untuk Bagian Umum jika presensi ada, readonly untuk tim lain). |
| 9 | **Ubah** | `app/Http/Controllers/LemburController.php` | Fitur koreksi pengajuan mandiri pegawai sebelum approval ketua tim + pencatatan audit trail `user_edited` & `tanggal_edited`. |
| 10 | **Ubah** | `resources/views/lembur.blade.php` | Modal dan tombol koreksi pengajuan mandiri pegawai. |
| 11 | **Ubah** | `app/Http/Controllers/admin/LemburController.php` | Pencatatan audit trail `user_edited` dan `tanggal_edited` pada method `updateUraian`. |

---

## 🚀 7. Panduan Deployment ke Server Produksi (*Production SOP*)

Panduan langkah demi langkah untuk menerapkan (*deploy*) pembaruan sistem ke server produksi BPS Jawa Tengah.

### A. Persiapan Sebelum Deploy (Komputer Lokal)
Pastikan semua perubahan pada branch kerja telah di-commit dan di-push ke remote repository:
```bash
git add .
git commit -m "feat: implementasi audit trail, koreksi uraian bersyarat presensi, dan panduan deploy"
git push origin <nama-branch>
```

### B. Prosedur Deploy di Server Produksi (Akses SSH)

1. **Masuk ke Direktori Aplikasi di Server:**
   ```bash
   cd /var/www/tempe-dele   # Sesuaikan dengan path direktori proyek di server
   ```

2. **Aktifkan Mode Pemeliharaan (*Maintenance Mode*) - Disarankan:**
   ```bash
   php artisan down --message="Sedang ada pembaruan sistem. Silakan coba beberapa saat lagi." --retry=60
   ```

3. **Tarik Kode Terbaru dari Git:**
   ```bash
   git fetch --all
   git pull origin <nama-branch>   # Misal: origin/main atau origin/update-alur-lembur
   ```

4. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate --force
   ```
   > **Catatan:** Flag `--force` wajib disertakan di environment production agar migrasi berjalan otomatis tanpa konfirmasi interaktif.

5. **Kompilasi Aset Frontend (Vite) - Jika Diperlukan:**
   ```bash
   npm run build
   ```

6. **Segarkan & Optimasi Cache Laravel:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Pastikan Izin Folder (*Permissions*) Tetap Aman:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

8. **Nyalakan Kembali Aplikasi (*Live Mode*):**
   ```bash
   php artisan up
   ```

### C. Rencana Pembatalan (*Rollback Plan*) Jika Terjadi Kendala
Apabila terjadi kendala tak terduga di server setelah deployment:
```bash
# Rollback migrasi database (menghapus kolom user_edited dan tanggal_edited)
php artisan migrate:rollback --step=1 --force

# Kembalikan commit kode ke commit sebelumnya
git checkout HEAD~1

# Segarkan cache dan nyalakan aplikasi kembali
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 8. Pembaruan Kapasitas Uraian Kegiatan (*Widen Column to TEXT & Max 2.000 Karakter*)

### A. Latar Belakang Masalah
* Sebelumnya, kolom `uraian` pada tabel `t_transaksi` bertipe `VARCHAR(255)`.
* Di controller `LemburController`, validasi membatasi `'uraian' => 'required|string|max:255'`.
* Ketika pegawai menuliskan rincian tugas lembur dalam format poin-poin bernomor atau narasi detail, sistem menolak atau memotong input teks karena melebihi 255 karakter.

### B. Solusi 3 Lapisan (*Three-Layer Solution*)
1. **Lapisan Database (`t_transaksi`):**
   * Mengubah tipe kolom `uraian` dari `VARCHAR(255)` menjadi **`TEXT`** (kapasitas hingga 65.535 karakter).
   * File migrasi: `database/migrations/2026_09_25_000002_widen_uraian_column_in_t_transaksi.php`.
   * Kueri SQL:
     ```sql
     ALTER TABLE t_transaksi MODIFY COLUMN uraian TEXT NULL;
     ```

2. **Lapisan Backend (Validasi Laravel):**
   * Memasang batas keamanan dan kerapian dokumen cetak SPKL sebesar **2.000 karakter** (`max:2000`).
   * Controller yang disesuaikan:
     * `app/Http/Controllers/LemburController.php` (Pengajuan baru & ubah pengajuan).
     * `app/Http/Controllers/admin/LemburController.php` (Pengajuan admin & update uraian).
     * `app/Http/Controllers/ketuatim/PengajuanController.php` (Koreksi uraian Ketua Tim).
     * `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` (Koreksi uraian Kabag).
     * `app/Http/Controllers/admin/PengajuanController.php` (Koreksi uraian Admin).

3. **Lapisan Frontend & UX (Tampilan Form):**
   * **Perluasan Visual:** Textarea dinaikkan dari `rows="3"` ke `rows="4"` dengan kemampuan tarik vertikal (`resize-y`).
   * **Live Character Counter:** Ditambahkan indikator jumlah karakter aktif (misal: `0 / 2000`) di atas textarea sehingga pengguna mengetahui batas maksimal dan panjang tulisan secara real-time.
   * Views yang disesuaikan:
     * `resources/views/lembur.blade.php` (Modal pengajuan & modal edit lembur).
     * `resources/views/ketua-tim/lembur.blade.php`.
     * `resources/views/admin/lembur.blade.php`.
     * `resources/views/ketua-tim/pengajuan.blade.php` (Modal aksi Ketua Tim).
     * `resources/views/admin/pengajuan.blade.php` (Modal aksi Admin).
     * `resources/views/kabag-umum/pengajuan.blade.php` (Modal aksi Kabag Umum).

---

## 9. Perbaikan Bug Otorisasi Form Pengajuan Lembur Pegawai (*403 Forbidden Fix*)

### A. Gejala Bug
* Ketika pegawai biasa (role: `user`/`pegawai`) mengajukan lembur pada halaman `/lembur`, setelah tombol simpan diklik, sistem langsung memunculkan halaman error **403 | AKSES DITOLAK: ANDA TIDAK MEMILIKI WEWENANG UNTUK MENGAKSES HALAMAN INI.** di URL `/ketua-tim/lembur`, dan data pengajuan tidak tersimpan.

### B. Akar Penyebab Masalah (*Root Cause*)
1. Pada file view pegawai [resources/views/lembur.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/lembur.blade.php), atribut action formulir pengajuan tertulis:
   `<form id="formAjukan" action="{{ route('ketua-tim.lembur.store') }}" method="POST">`
   yang mengarahkan kiriman POST ke URL `/ketua-tim/lembur`.
2. Di [routes/web.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/routes/web.php), grup rute dengan prefix `ketua-tim` dilindungi oleh middleware:
   `middleware(['checksession', 'role:ketua_tim,admin,superadmin'])`.
   Akibatnya, permohonan POST dari pegawai biasa ditolak oleh middleware sebelum kode controller dijalankan.
3. Di [app/Http/Controllers/LemburController.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/app/Http/Controllers/LemburController.php), baris redirect setelah penyimpanan di-hardcode ke:
   `return redirect()->route('ketua-tim.lembur', $params)`.

### C. Solusi & Perbaikan
1. Mengubah atribut action form pada [resources/views/lembur.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/lembur.blade.php) menjadi:
   `<form id="formAjukan" action="{{ route('lembur.store') }}" method="POST">`
   sehingga data dikirimkan melalui rute publik pegawai yang sah.
2. Memperbarui logika redirect di [app/Http/Controllers/LemburController.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/app/Http/Controllers/LemburController.php) agar dinamis:
   * Jika yang mengajukan adalah **Ketua Tim**: me-redirect ke `route('ketua-tim.lembur')`.
   * Jika yang mengajukan adalah **Pegawai Biasa**: me-redirect ke `route('lembur')`.
   * Memastikan pesan notifikasi `$message` terkirim dengan benar ke session flash message.

---

## 10. Validasi & Pembatasan Jam Selesai Lembur Berdasarkan Presensi Pulang Pegawai

### A. Latar Belakang & Urgensi
1. Berdasarkan regulasi kedinasan BPS dan pertanggungjawaban audit BPK/Inspektorat, hak uang lembur pegawai dibayarkan murni atas dasar **kehadiran fisik riil** di kantor yang dibuktikan melalui data presensi (*fingerprint/mesin presensi*).
2. Jika seorang pegawai tercatat presensi kepulangan (*clock-out*) pada pukul **18:30**, maka secara logika kedinasan pegawai tersebut sudah tidak berada di tempat kerja setelah pukul 18:30.
3. Menyetujui lembur melebihi jam kepulangan fisik (misal disetujui sampai pukul 19:00 atau 20:00) berpotensi menjadi **temuan lembur fiktif** saat audit.
4. Meskipun sistem telah memiliki pemotongan otomatis di background saat unggah berkas presensi via `app/Traits/KoreksiLembur.php`, antarmuka modal aksi sebelumnya masih memperbolehkan pejabat memasukkan jam bebas tanpa batas.

### B. Solusi & Implementasi Proteksi Ganda (Frontend & Backend)
1. **Lapisan Database Query (Mengambil Jam Pulang Presensi):**
   * Menambahkan subquery `jam_selesai_presensi` pada query `index` controller agar jam kepulangan fisik pegawai langsung tersedia di baris tabel:
     ```php
     DB::raw('(
         SELECT DATE_FORMAT(pr.jam_selesai, "%H:%i") FROM t_presensi pr
         WHERE pr.niplama = p.nip_lama
         AND DATE(pr.tanggal) = t.date
         LIMIT 1
     ) as jam_selesai_presensi')
     ```
   * Berkas:
     * `app/Http/Controllers/ketuatim/PengajuanController.php`
     * `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`
     * `app/Http/Controllers/admin/PengajuanController.php`

2. **Lapisan Validasi Backend (Pencegahan Mutlak di Sisi Server):**
   * Pada saat persetujuan/koreksi jam lembur, controller mengecek rekaman `t_presensi` pegawai pada tanggal terkait.
   * Jika data presensi tersedia dan `jam_selesai_disetujui > presensi->jam_selesai`, permohonan ditolak dengan HTTP Status 422:
     > *"Jam selesai disetujui (19:00) tidak boleh melebihi jam kepulangan presensi pegawai (18:30)."*
   * Berkas:
     * `app/Http/Controllers/ketuatim/PengajuanController.php` (method `approve`)
     * `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` (method `approve`)
     * `app/Http/Controllers/admin/PengajuanController.php` (method `approve`)

3. **Lapisan Frontend & User Experience (Modal Aksi Keputusan):**
   * Nilai `jam_selesai_presensi` dioper ke dalam fungsi JavaScript pembuka modal (`openModalKeputusan` / `openModalKabag`).
   * Jika data presensi ada:
     * Input `<input type="time">` jam selesai otomatis diberikan atribut batas: `max="HH:mm"` sesuai jam kepulangan presensi.
     * Ditampilkan teks bantuan informatif di bawah input jam:
       *📌 "Maksimal jam selesai: 18:30 (sesuai presensi pulang)"*.
   * Validasi JavaScript di sisi klien (`simpanKeputusan` / `simpanKeputusanKabag`) mendeteksi dan mencegah submit form jika pengguna memaksakan nilai di atas jam pulang presensi.
   * Berkas:
     * `resources/views/ketua-tim/pengajuan.blade.php`
     * `resources/views/kabag-umum/pengajuan.blade.php`
     * `resources/views/admin/pengajuan.blade.php`

---

## 11. Penyesuaian Nilai Default Jam Disetujui Pada Modal Persetujuan (Default Mengikuti Jam Pengajuan, Presensi Sebagai Batas Maksimal)

### A. Latar Belakang Masalah
1. Sebelumnya pada modal aksi persetujuan lembur (*"Keputusan Lembur"*), input **Jam Selesai Disetujui** sempat otomatis terisi default dengan jam kepulangan presensi pegawai (misalnya: `20:30`), meskipun pegawai bersangkutan hanya mengajukan lembur sampai pukul `20:00`.
2. Hal ini disebabkan oleh dua faktor:
   - Logika background pada `app/Traits/KoreksiLembur.php` dan `app/Http/Controllers/LemburController.php` method `koreksiDariPresensi()` sebelumnya langsung mengisi `jam_selesai_disetujui = min(jam_selesai_presensi, batas_maksimal_4_atau_6_jam)` tanpa mempertimbangkan jam selesai yang diajukan oleh pegawai (`jam_selesai`).
   - Tampilan modal persetujuan pada Blade mengutamakan nilai kolom `jam_selesai_disetujui` jika tidak kosong, sehingga nilai hasil presensi otomatis tersebut masuk ke dalam input form persetujuan.
3. Dampak Masalah:
   - Jam lembur yang disetujui tidak boleh memperluas jam yang diajukan oleh pegawai sendiri. Jika pegawai hanya mengajukan sampai pukul 20:00, maka persetujuan tidak boleh otomatis dinaikkan ke 20:30 hanya karena pegawai pulang jam 20:30.
   - Jam kepulangan fisik presensi (`20:30`) secara aturan hanya berfungsi sebagai **batas atas/maksimal (*upper bound/ceiling limit*)**, bukan sebagai nilai bawaan (*default value*).

### B. Solusi & Perbaikan
1. **Frontend Modal Persetujuan (`resources/views/ketua-tim/pengajuan.blade.php` & `resources/views/kabag-umum/pengajuan.blade.php`):**
   * Untuk transaksi yang berstatus `pending` atau belum memiliki jam persetujuan manual, nilai default input form `Jam Mulai Disetujui` dan `Jam Selesai Disetujui` **wajib** mengambil murni dari jam yang diajukan pegawai (`jam_mulai` dan `jam_selesai`).
   * Jam presensi kepulangan (`jam_selesai_presensi`, misal `20:30`) tetap dikirimkan ke JavaScript modal untuk:
     - Mengisi atribut pembatas: `max="20:30"`.
     - Menampilkan teks petunjuk informasi di bawah input: *📌 "Maksimal jam selesai: 20:30 (sesuai presensi pulang)"*.
   * Input value tidak lagi tertimpa oleh jam kepulangan presensi. Pejabat yang menyetujui akan melihat default jam sesuai pengajuan pegawai (`20:00`), dan hanya dapat menggeser/mengurangi waktu atau maksimal mentok di `20:30`.

2. **Backend Logic & Perhitungan Koreksi Presensi (`app/Traits/KoreksiLembur.php` & `app/Http/Controllers/LemburController.php`):**
   * Menambahkan pembatasan `$batasAtas` yang memperhitungkan jam selesai pengajuan pegawai:
     ```php
     $jamSelesaiPengajuan = Carbon::parse($transaksi->date . ' ' . $transaksi->jam_selesai);
     if ($jamSelesaiPengajuan->lessThan($jamMulaiPengajuan)) {
         $jamSelesaiPengajuan->addDay();
     }

     // Batas atas adalah nilai terkecil antara batas maksimal durasi (4/6 jam) dan jam selesai pengajuan
     $batasAtas = $jamSelesaiPengajuan->lessThan($batasMaksimal)
         ? $jamSelesaiPengajuan
         : $batasMaksimal;

     // Jam selesai final dibatasi oleh batasAtas dan jam kepulangan fisik presensi
     $jamSelesaiFinal = $jamSelesaiPresensi->lessThan($batasAtas)
         ? $jamSelesaiPresensi
         : $batasAtas;
     ```
   * Dengan logika ini, sistem di background tidak akan pernah mendongkrak jam selesai disetujui melebihi waktu yang diajukan pegawai.

---

## 12. Audit & Perbaikan Sinkronisasi Dashboard Ketua Tim Terhadap Alur Persetujuan Bertingkat

### A. Latar Belakang Masalah
1. Setelah diterapkannya alur persetujuan bertingkat (*multi-tier approval*), pengajuan anggota tim yang disetujui oleh Ketua Tim memiliki status antara `menunggu_kabag` sebelum nantinya menjadi `approved` (Disetujui Final oleh Kabag Umum).
2. Pada halaman Dashboard Ketua Tim sebelumnya terdapat inkonsistensi:
   - **Badge Kosong pada Tabel Pengajuan:** Pada tabel ringkasan pengajuan terbaru di `resources/views/ketua-tim/dashboard.blade.php`, kondisi status hanya membaca `pending`, `approved`, dan `rejected`. Pengajuan dengan status `menunggu_kabag` tampil tanpa badge (kosong).
   - **Metrik Card Disetujui Tidak Akurat:** Pada `DashboardController.php`, kartu metrik "Disetujui" hanya menghitung `where('status', 'approved')`. Akibatnya, pengajuan yang baru saja disetujui oleh ketua tim hilang dari kartu "Diproses" namun belum muncul di kartu "Disetujui" (karena masih berstatus `menunggu_kabag`).
   - **Pencegahan Jam pada Quick Approve Dashboard:** Fungsi quick-approve dari modal dashboard (`DashboardController@approve`) belum memiliki pembatasan terhadap jam kepulangan presensi fisik pegawai.

### B. Solusi & Perbaikan
1. **Pembaruan Tampilan Dashboard (`resources/views/ketua-tim/dashboard.blade.php`):**
   * Menambahkan badge biru **Menunggu Kabag** pada tabel pengajuan:
     ```blade
     @elseif ($p->status === 'menunggu_kabag')
         <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600">Menunggu Kabag</span>
     ```
2. **Pembaruan Metrik & Query (`app/Http/Controllers/ketuatim/DashboardController.php`):**
   * Metrik "Disetujui" kini menghitung seluruh pengajuan yang telah disetujui oleh ketua tim maupun kabag (`whereIn('status', ['approved', 'menunggu_kabag'])`), sehingga total pengajuan bulan berjalan selalu sinkron dengan rincian kartu.
   * Query widget "Lembur Hari Ini" menampilkan pegawai yang lemburnya telah disetujui oleh ketua tim (`whereIn('t.status', ['approved', 'menunggu_kabag'])`).

---

## 13. Audit & Penyempurnaan Alur Persetujuan Kepala Bagian Umum (*Kabag Umum Approval*)

### A. Latar Belakang Masalah
1. Pada proses persetujuan dan penolakan oleh Kepala Bagian Umum di `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`:
   - Jika permohonan lembur ditolak oleh Kabag Umum (`status = 'rejected'`), kolom `jam_mulai_disetujui` dan `jam_selesai_disetujui` bawaan dari persetujuan Ketua Tim sebelumnya belum otomatis di-reset menjadi `NULL`. Akibatnya, pada tampilan baris tabel masih memunculkan jam disetujui padahal statusnya telah berubah menjadi Ditolak.
   - Pada `resources/views/kabag-umum/pengajuan.blade.php`, setelah Kabag Umum menyimpan keputusan via AJAX, tombol aksi pada baris tabel bersangkutan masih berlabel *"Proses"* (tombol oranye) dan parameter modal belum diperbarui. Jika pengguna mengklik kembali tanpa me-refresh halaman, modal akan terbuka dengan status lama bukannya status terkunci (*locked state*).

### B. Solusi & Perbaikan
1. **Pembersihan Jam Lembur Saat Ditolak (`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`):**
   * Jika status keputusan akhir adalah `rejected`, sistem secara otomatis mengosongkan nilai `jam_mulai_disetujui` dan `jam_selesai_disetujui` menjadi `NULL`.
   * Respon JSON diperkaya dengan atribut `status`, `jam_mulai_disetujui`, `jam_selesai_disetujui`, dan `note_kabag` agar frontend dapat memperbarui DOM secara presisi.
2. **Pembaruan DOM Realtime Tanpa Reload (`resources/views/kabag-umum/pengajuan.blade.php`):**
   * Menambahkan identitas elemen `id="aksi-kabag-{{ $p->id_transaksi }}"` pada kolom aksi tabel.
   * Pada fungsi JavaScript `simpanKeputusanKabag`:
     - Kolom **Jam Disetujui** otomatis berubah menjadi tanda strip (`-`) jika ditolak, atau menampilkan rentang jam disetujui jika disetujui.
     - Tombol aksi otomatis bertransformasi dari tombol *"Proses"* menjadi tombol *"Koreksi"* dengan parameter status dan nilai terbaru, sehingga klik berikutnya akan langsung membuka modal dalam kondisi status terkunci (*locked state*).
     - Catatan Kabag Umum dan uraian kegiatan langsung ter-update di layar tanpa perlu melakukan muat ulang (*reload*) halaman.

---

## 14. Audit Menyeluruh & Penyempurnaan Integrasi Fitur Admin (Dashboard, Quick Approval, & Pengajuan Satker-Wide)

### A. Latar Belakang & Analisis Bug Tersembunyi
1. **Misrouting Rute Admin Approval:**
   - Sebelumnya, rute `POST /admin/pengajuan/{id}/approve` salah mengarah ke `AdminLemburController::approve`. Controller tersebut merupakan *legacy stub* yang tidak memiliki validasi presensi, tidak mendukung pengubahan uraian kegiatan, dan tidak mencatat audit trail `user_edited` / `tanggal_edited`.
   - Seharusnya rute tersebut mengarah ke `AdminPengajuanController::approve` yang memiliki logika validasi presensi dan audit trail yang lengkap.
2. **Crash Potensial pada Quick-Approve Dashboard Admin:**
   - Rute `POST /admin/transaksi/{id}/approve` di `routes/web.php` sebelumnya diarahkan ke `AdminLemburController::quickApprove`.
   - Namun, method `quickApprove` sama sekali tidak didefinisikan pada `app/Http/Controllers/admin/LemburController.php`, melainkan method `approve` sudah ada di `app/Http/Controllers/admin/DashboardController.php`. Jika admin menekan tombol *"✓ Setujui"* pada modal quick-approve di dashboard, sistem akan mengalami *fatal error*: `Method App\Http\Controllers\admin\LemburController::quickApprove does not exist`.
3. **Inkonsistensi Status & Metrik Dashboard:**
   - Kartu metrik "Diproses" di Admin Dashboard sebelumnya hanya menghitung `status = 'pending'`. Dalam alur bertingkat, pengajuan yang berstatus `menunggu_kabag` adalah pengajuan yang sedang dalam proses berjalan (menunggu persetujuan akhir Kabag), sehingga harus dihitung ke dalam metrik "Diproses".
   - Pada tabel "Lembur Hari Ini" di Dashboard Admin, penanganan badge untuk `status = 'menunggu_kabag'` belum tersedia sehingga kolom status tampil kosong tanpa badge.
   - Pada modal daftar pengajuan pending di dashboard (`getPending()`), pengajuan berstatus `menunggu_kabag` sebelumnya belum terangkum.
4. **Alur Persetujuan & Penolakan pada Admin Pengajuan (`AdminPengajuanController`):**
   - Ketika Admin menolak pengajuan (`status = 'rejected'`), kolom `jam_mulai_disetujui` dan `jam_selesai_disetujui` belum di-reset ke `NULL`.
   - Ketika Admin menyetujui pengajuan, status menjadi `approved` (Disetujui Final), sehingga cap waktu `approved_kabag_at` wajib diisi bersamaan dengan `approved_at` agar konsisten dengan status persetujuan akhir satker.
   - Pada tabel pengajuan Admin (`resources/views/admin/pengajuan.blade.php`), baris dengan status `menunggu_kabag`, `approved`, dan `rejected` sebelumnya tidak memiliki tombol aksi/koreksi, sehingga Admin tidak dapat melakukan koreksi jam atau menyetujui pengajuan yang sedang menunggu Kabag.

### B. Solusi & Perbaikan Komprehensif
1. **Perbaikan Pemetaan Rute (`routes/web.php`):**
   - Rute `admin.pengajuan.approve`: diarahkan ke `[AdminPengajuanController::class, 'approve']`.
   - Rute `admin.dashboard.approve`: diarahkan ke `[AdminDashboardController::class, 'approve']`.
2. **Penyempurnaan Dashboard Controller (`app/Http/Controllers/admin/DashboardController.php`):**
   - Metrik `diproses` pada kartu statistik menghitung `whereIn('status', ['pending', 'menunggu_kabag'])`.
   - Method `getPending()` menyertakan transaksi berstatus `pending` dan `menunggu_kabag`.
   - Method `approve($id)` dilengkapi dengan:
     - Pembatasan jam kepulangan fisik berdasarkan data presensi pegawai di `t_presensi`.
     - Pengisian `approved_kabag_at = now()` dan `approved_at = now()->toDateString()`.
     - Pencatatan jejak audit: `user_edited` dan `tanggal_edited`.
3. **Penyempurnaan Tampilan Dashboard Admin (`resources/views/admin/dashboard.blade.php`):**
   - Menambahkan badge biru **Menunggu Kabag** pada tabel "Lembur Hari Ini".
   - Menambahkan tag indikator `(Menunggu Kabag)` pada daftar modal quick-approve agar Admin mengetahui posisi persetujuan pengajuan.
4. **Penyempurnaan Admin Pengajuan Controller (`app/Http/Controllers/admin/PengajuanController.php`):**
   - Validasi `jam_mulai_disetujui` dan `jam_selesai_disetujui` bersifat `nullable` saat penolakan (`rejected`), dan wajib diisi saat persetujuan (`approved`).
   - Saat status `rejected`: `jam_mulai_disetujui = null`, `jam_selesai_disetujui = null`, `approved_kabag_at = null`.
   - Saat status `approved`: jam lembur divalidasi tidak boleh melebihi jam kepulangan presensi riil pegawai (`t_presensi`), serta mengisi `approved_kabag_at = now()`.
   - Pencatatan jejak audit `user_edited` dan `tanggal_edited` terintegrasi pada setiap tindakan persetujuan dan pengubahan uraian.
5. **Penyempurnaan View Pengajuan Admin (`resources/views/admin/pengajuan.blade.php`):**
   - Menambahkan tombol aksi/koreksi pada semua status (`pending`, `menunggu_kabag`, `approved`, dan `rejected`).
   - Admin dapat langsung menyetujui atau mengoreksi pengajuan yang berstatus `menunggu_kabag`.
   - Fungsi JavaScript `openModalKeputusan` mendukung parameter `initialStatus` untuk mempermudah pemilihan keputusan.
   - Fungsi JavaScript `simpanKeputusan` memperbarui baris tabel, badge status, dan teks jam disetujui (menjadi `-` jika ditolak) secara realtime tanpa perlu reload halaman.

---

## 15. Implementasi Fitur Pembatalan Pengajuan Lembur oleh Admin (*Admin Cancel Submission / Fase 1 No. 1*)

### A. Latar Belakang & Kebutuhan Fitur
1. **Pencegahan Data Duplikat & Salah Tanggal**:
   - Pegawai atau ketua tim terkadang mengalami kesalahan input, seperti pengajuan ganda (*double input*) atau salah memilih tanggal lembur.
   - Sebelumnya, belum tersedia mekanisme pembatalan pengajuan oleh Admin. Satu-satunya opsi adalah "Ditolak", padahal penolakan memiliki konotasi verifikasi yang tidak memenuhi syarat tugas kedinasan, bukan karena kesalahan input teknis atau dobel data.
   - Melakukan *hard delete* (penghapusan baris data dari database) sangat berisiko merusak integritas audit BPK/Inspektorat serta menghilangkan riwayat pengajuan dan tanda tangan digital pegawai.
2. **Kepatuhan Audit & Non-Destructive Soft Cancellation**:
   - Solusi terbaik adalah menerapkan status khusus `cancelled` (*Dibatalkan*).
   - Pengajuan yang berstatus `cancelled` tetap tersimpan di database dengan jejak audit lengkap (`user_edited`, `tanggal_edited`, dan alasan pembatalan di kolom `note`), namun seluruh hak kalkulasi uang lembur (`eligible`, `jam_mulai_disetujui`, `jam_selesai_disetujui`, `approved_at`, `approved_kabag_at`) secara tegas di-reset ke `NULL`.
   - Modul pelaporan dan rekapitulasi keuangan (`RekapitulasiController`) yang memfilter secara ketat `status = 'approved'` dan `eligible = 1` dijamin 100% aman dan bersih dari data yang dibatalkan.

### B. Rincian Implementasi & Perubahan Teknis
1. **Rute Baru (`routes/web.php`)**:
   - `POST /admin/lembur/{id}/cancel` ➔ `AdminLemburController@cancel` (nama rute: `admin.lembur.cancel`).
   - `POST /admin/pengajuan/{id}/cancel` ➔ `AdminPengajuanController@cancel` (nama rute: `admin.pengajuan.cancel`).
2. **Pembaruan Controller Admin**:
   - **`app/Http/Controllers/admin/LemburController.php`**:
     - Menambahkan hitungan `'cancelled' => ...->where('t.status', 'cancelled')->count()` pada `$statusCounts`.
     - Menambahkan method `cancel(Request $request, $id)`:
       - Memvalidasi alasan pembatalan wajib diisi (maksimal 500 karakter).
       - Menulis alasan ke dalam kolom `note` dengan prefix standar `[Dibatalkan Admin] ...`.
       - Mengosongkan `jam_mulai_disetujui = null`, `jam_selesai_disetujui = null`, `eligible = null`, `approved_at = null`, `approved_kabag_at = null`.
       - Mencatat jejak audit: `user_edited = session('user')['nama']` dan `tanggal_edited = now()`.
       - Mengembalikan respon JSON untuk AJAX maupun redirect back dengan flash message.
   - **`app/Http/Controllers/admin/PengajuanController.php`**:
     - Memperbarui validasi status pada method `approve`: mendukung `in:approved,rejected,cancelled`.
     - Menambahkan penanganan status `cancelled`: mengosongkan jam disetujui, mencatat alasan pembatalan pada `note`, serta mencatat jejak audit.
     - Menambahkan method dedicated `cancel(Request $request, $id)` serupa dengan `LemburController`.
   - **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`**:
     - Menambahkan `'cancelled'` pada array `$stats` agar Kepala Bagian Umum dapat memantau pengajuan yang telah dibatalkan oleh Admin.
3. **Pembaruan Tampilan UI/UX**:
   - **Tampilan Lembur Admin (`resources/views/admin/lembur.blade.php`)**:
     - Menambahkan tombol filter tab **Dibatalkan** dengan indikator counter dinamis.
     - Menambahkan kolom **Aksi** pada tabel lembur dengan tombol **"Batal"** (bergaya soft rose border).
     - Menambahkan modal konfirmasi pembatalan `#modalBatalAdmin`: menampilkan informasi pegawai, tanggal lembur, formulir alasan pembatalan wajib, dan tombol konfirmasi dengan indikator loading state.
     - Integrasi fungsi JavaScript `openModalBatalAdmin`, `closeModalBatalAdmin`, dan `submitBatalAdmin` via AJAX yang langsung memperbarui badge status, teks catatan, dan kolom aksi di tabel tanpa reload halaman.
   - **Tampilan Pengajuan Admin (`resources/views/admin/pengajuan.blade.php`)**:
     - Menambahkan opsi tombol keputusan ke-3: **"Batalkan"** (`#kBtnBatal`) pada modal keputusan `#modalKeputusan`.
     - Saat opsi "Batalkan" dipilih: input jam disetujui otomatis disembunyikan dan label catatan berubah menjadi "Alasan Pembatalan (Wajib)".
     - Menampilkan badge abu-abu border halus `Dibatalkan` pada tabel pengajuan.
   - **Tampilan Lembur Pegawai (`resources/views/lembur.blade.php`)**:
     - Menambahkan badge status abu-abu berlabel **`Dibatalkan Admin`**.
     - Kolom aksi otomatis tidak mengizinkan pengeditan pengajuan yang telah dibatalkan.
   - **Tampilan Pengajuan Ketua Tim (`resources/views/ketua-tim/pengajuan.blade.php`)**:
     - Menambahkan badge status `Dibatalkan`.
     - Kolom aksi menampilkan teks non-interaktif `Dibatalkan` untuk mencegah ketua tim memproses atau menyetujui transaksi yang telah dibatalkan Admin.
   - **Tampilan Pengajuan Kabag Umum (`resources/views/kabag-umum/pengajuan.blade.php`)**:
     - Menambahkan tab filter **Dibatalkan (n)** di navigasi status.
     - Menambahkan badge status `Dibatalkan` dan teks non-interaktif `Dibatalkan` pada kolom aksi.
   - **Tampilan Pengajuan Pimpinan (`resources/views/pimpinan/pengajuan.blade.php`)**:
     - Menambahkan penanganan badge status `Menunggu Kabag` dan `Dibatalkan`.

---

## 16. Otomatisasi Nilai Kelayakan (`eligible`) & Indikator Presensi Cepat pada Monitoring Admin (*Fase 1 No. 2 & No. 3*)

### A. Latar Belakang & Identifikasi Masalah
1. **Ketergantungan Eksekusi Kelayakan Bisnis (`eligible`)**:
   - Nilai kelayakan `eligible = 1` adalah syarat mutlak agar data pengajuan yang disetujui (`status = 'approved'`) masuk ke dalam perhitungan uang lembur dan tabel rekapitulasi pada `RekapitulasiController` dan ekspor `RekapitulasiExport`.
   - Sebelumnya, pengecekan kelayakan lembur (`koreksiDariPresensi` / `koreksiUntukTanggal`) hanya dipicu ketika:
     1. Pegawai membuka halaman pengajuannya sendiri (`/lembur`).
     2. Admin membuka halaman pemantauan (`/admin/lembur`).
     3. Admin mengunggah berkas presensi baru (`/admin/presensi`).
   - Akibatnya, jika Admin atau Kabag Umum menyetujui pengajuan lembur yang berkas presensinya sudah pernah diunggah sebelumnya, kolom `eligible` tetap bernilai `NULL`. Jika Admin langsung mengunduh rekapitulasi/SPKL di menu `/admin/rekapitulasi`, lembur yang baru disetujui tersebut tidak muncul di laporan keuangan sebelum ada yang memicu halaman lembur.
2. **Ketiadaan Indikator Presensi pada Monitoring Satker Admin (`/admin/lembur`)**:
   - Pada halaman pemantauan lembur seluruh satker (`/admin/lembur`), Admin sebelumnya hanya melihat nama pegawai dan NIP tanpa mengetahui apakah pegawai yang bersangkutan telah memiliki rekaman presensi fisik (jam masuk & jam pulang) pada tanggal lembur tersebut.
   - Admin harus membuka menu atau tab lain hanya untuk memeriksa kehadiran pegawai.

### B. Solusi & Rincian Implementasi
1. **Penyempurnaan Trait `App\Traits\KoreksiLembur` (`app/Traits/KoreksiLembur.php`)**:
   - Mengubah visibilitas `koreksiUntukTanggal` menjadi `public`.
   - Menambahkan method `koreksiUntukTransaksi(int $idTransaksi)`: mengevaluasi kelayakan satu transaksi secara instan berdasarkan tanggal dan presensi pegawai.
   - Menambahkan method `koreksiUntukBulan(int $tahun, int $bulan)`: menyapu (*sweep*) seluruh transaksi berstatus `approved` yang `eligible`-nya masih `NULL` pada bulan bersangkutan.
   - Menyempurnakan acuan jam selesai: menggunakan jam selesai yang disetujui (`jam_selesai_disetujui`) jika telah ditetapkan oleh pejabat penyetujui, sehingga jam yang disetujui tidak tertimpa jam pengajuan awal.
2. **Otomatisasi Pemicu `eligible` pada Seluruh Titik Persetujuan**:
   - **`app/Http/Controllers/admin/PengajuanController.php`**: memanggil `$this->koreksiUntukTransaksi($id)` sesaat setelah Admin menyetujui pengajuan.
   - **`app/Http/Controllers/admin/DashboardController.php`**: memanggil `$this->koreksiUntukTransaksi($id)` pada tombol *quick-approve* dashboard Admin.
   - **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`**: memanggil `$this->koreksiUntukTransaksi($id)` sesaat setelah Kabag Umum menyetujui pengajuan.
   - **`app/Http/Controllers/admin/RekapitulasiController.php`**: memanggil `$this->koreksiUntukBulan((int)$tahun, (int)$bln)` sebelum mengeksekusi kueri rekapitulasi maupun sebelum mengunduh Excel.
   - **`app/Exports/RekapitulasiExport.php`**: memanggil `$this->koreksiUntukBulan((int)$tahun, (int)$bln)` sebelum membangun koleksi data laporan ekspor.
   - *Hasil*: Begitu pengajuan disetujui dan data presensi sudah ada di sistem, `eligible = 1` langsung tercap seketika secara otomatis tanpa memerlukan intervensi pembukaan halaman oleh pegawai.
3. **Indikator Presensi Realtime pada Tabel Monitoring Admin (`/admin/lembur`)**:
   - **Kueri Controller (`app/Http/Controllers/admin/LemburController.php`)**:
     - Menambahkan subquery SQL `EXISTS` untuk mendeteksi ketersediaan presensi (`has_presensi`), serta memformat jam kepulangan (`jam_selesai_presensi`) dan jam masuk (`jam_masuk_presensi`).
   - **Tampilan Blade (`resources/views/admin/lembur.blade.php`)**:
     - Pada kolom **Pegawai**, ditambahkan badge indikator kehadiran:
       - 🟢 **Presensi Ada (Pulang HH:MM)**: Badge hijau dengan titik berdenyut (*pulsing dot*).
       - ⚪ **Belum Presensi**: Badge abu-abu lembut jika data presensi tanggal tersebut belum diunggah.
     - **Modal Presensi Terintegrasi (`#modalPresensiAdmin`)**:
       - Mengklik badge hijau presensi akan langsung membuka modal informasi presensi pegawai tanpa berpindah halaman, menampilkan status kehadiran (WFO/WFOL), jam masuk, dan jam pulang secara instan via endpoint `/admin/pengajuan/{id}/presensi`.

---

## 17. Audit & Remedi Antislop UI/UX, Aksesibilitas WCAG AA, dan Standarisasi Tampilan (29 September 2026)

> [!IMPORTANT]
> **Jaminan Integritas Alur & Logika Bisnis:**
> Pembaruan pada bagian ini **100% murni perbaikan tampilan (UI/UX), aksesibilitas pengguna, dan standarisasi visual**.
> **Tidak ada perubahan alur kerja bisnis, logika controller backend, skema migrasi database, kueri SQL, atau logika persetujuan pengajuan lembur yang dimodifikasi.**

### A. Latar Belakang & Audit Antislop v3.2.19
Dilakukan audit komprehensif tampilan menggunakan rulebook Antislop v3.2.19 (`antislop.md`) dan modul-modul pendukung (`antislop-ui`, `antislop-human`, `antislop-layoutmobile`, `antislop-copywriting`, `antislop-code`).
Seluruh teks dan rasio kontras warna divalidasi langsung menggunakan alat penguji kontras WCAG AA (`contrast-check.py`) dengan batas aman minimal rasio kontras 4.5:1 untuk teks normal. Laporan audit lengkap tersimpan pada berkas `anti-slop/audit-001-2026-09-29.md`.

### B. Rincian Remediasi Visual & Aksesibilitas

1. **Tata Letak & Responsivitas Mobile (`resources/views/layouts/app.blade.php`)**:
   - Menambahkan padding kontainer konten responsif (`px-4 sm:px-6 lg:px-8`) agar tampilan tabel dan form di layar smartphone tidak mepet ke tepi layar atau terpotong.
   - Mengganti teks keterangan kontras rendah dari `text-slate-400` (2.56:1 fail) menjadi `text-slate-500` (4.76:1 pass WCAG AA).
   - Menghapus karakter dekoratif em dash (`—`).
   - Mendaftarkan *global keyboard event listener* tombol `Escape` untuk menutup modal atau dropdown yang sedang terbuka secara aksesibel (memenuhi aturan R-32 Antislop).

2. **Pembersihan Footer Boilerplate (`resources/views/partials/footer.blade.php`)**:
   - Menghapus teks bawaan template pihak ketiga (*"Company Ltd. All rights reservered"*) yang tidak profesional.
   - Menggantikannya dengan identitas resmi instansi: *"Badan Pusat Statistik Provinsi Riau • Hak Cipta Dilindungi"*.

3. **Aksesibilitas Navbar & Avatar (`resources/views/partials/navbar.blade.php`)**:
   - Mengubah elemen pembungkus tombol toggle sidebar dari elemen non-interaktif `<div>` menjadi `<button>` semantik lengkap dengan atribut `aria-label="Toggle menu navigasi"`.
   - Memperbaiki kontras ikon avatar profil dan teks peran pengguna: mengubah latar dari kontras rendah menjadi `text-amber-800` pada `bg-amber-100` (rasio 4.52:1 pass) serta `text-slate-500` untuk label status peran.

4. **Perbaikan Tautan Mati & Kontras Sidebar (`resources/views/partials/sidebar.blade.php`)**:
   - Mengubah tautan mati (*ghost link*) `href="#"` pada header logo/brand menjadi tautan aktif ke rute dashboard dinamis pengguna (`{{ route(auth()->user()->role . '.dashboard') }}`).
   - Meningkatkan rasio kontras teks tajuk navigasi (*nav group headers*) dan teks item menu non-aktif dari `text-slate-500` (3.75:1 fail pada latar `bg-slate-900`) menjadi `text-slate-400` (6.96:1 pass WCAG AA).

5. **Form Autentikasi Bersih & Ergonomis (`resources/views/login.blade.php`)**:
   - Menghapus efek `scale-125` pada gambar ilustrasi halaman login yang berisiko menyebabkan *layout clipping* atau pergeseran tak terduga pada layar beresolusi sedang/kecil.
   - Menghilangkan efek *neon glowing box-shadow* berlebihan pada tombol utama, digantikan dengan elevasi bayangan Tailwind yang teratur dan bersih (`shadow-sm hover:shadow-md`).
   - Menambahkan atribut aksesibilitas `aria-label="Tampilkan atau sembunyikan kata sandi"` pada tombol intip sandi.
   - Merapikan gaya visual tombol bantuan login cepat pengembang agar lebih tenang dan serasi.

6. **Halaman Sambutan / Landing Page Bebas Efek Berlebihan (`resources/views/welcome.blade.php`)**:
   - Menghapus elemen dekoratif tanpa struktur (*radial blur blob* oranye mengambang).
   - Menghapus bayangan bercahaya warna-warni (*glow drop-shadows*) pada tombol navigasi utama.
   - Menyeragamkan radius sudut kartu (*border-radius*) dari campur aduk `rounded-3xl` menjadi `rounded-xl` yang konsisten di seluruh desain sistem.
   - Mengganti tanda hubung dekoratif em dash dengan tanda hubung bersih.

7. **Pembersihan Log Pengembang & Mikro-Interaksi Dashboard Pegawai (`resources/views/dashboard.blade.php`)**:
   - Menghapus sisa kode debug pengembang `console.log("DEBUG TIMKERJA:", ...)` dari skrip JavaScript halaman.
   - Menghilangkan karakter panah dekoratif chevron `›` yang tidak bernilai semantik pada kartu status.
   - Memperbaiki rasio kontras teks keadaan kosong (*empty state*) dari `text-gray-400` menjadi `text-slate-500`.

8. **Standarisasi Istilah & Tipografi Dashboard Admin (`resources/views/admin/dashboard.blade.php`)**:
   - Memperbaiki terminologi instansi dari "karyawan" menjadi "pegawai" sesuai standar baku Aparatur Sipil Negara / BPS.
   - Menghapus tanda em dash pada judul kartu.
   - Memperbaiki kontras teks keterangan metrik dari `text-slate-400` menjadi `text-slate-500`.

9. **Penyempurnaan Aksesibilitas Form Pengajuan Admin & Ketua Tim (`resources/views/admin/pengajuan.blade.php` & `resources/views/ketua-tim/pengajuan.blade.php`)**:
   - Meningkatkan keterbacaan teks nomor NIP dan ikon aksi tabel dari `text-gray-400` (2.56:1 fail) menjadi `text-slate-500` (4.76:1 pass).
   - Menyeragamkan radius kotak input pencarian dari `rounded-full` menjadi `rounded-xl` agar selaras dengan input form lainnya.

10. **Penyempurnaan Estetika & Ergonomi Tabel Monitoring Admin (`resources/views/admin/lembur.blade.php`)**:
    - **Kolom Tersendiri Data Presensi**: Memisahkan presensi dari kolom nama pegawai dan memberikan kolom mandiri **Data Presensi** seperti pada tabel Ketua Tim & Admin Pengajuan.
    - **Teks Indikator Presensi**: Mengganti teks pasif *"Informasi tersedia"* menjadi **"Lihat Presensi ↗"** (hijau dengan ikon tautan, dilengkapi tooltip jam kepulangan resmi pegawai) dan *"Belum ada presensi"* (abu-abu netral), sehingga maksud aksi langsung terbaca jelas oleh admin.
    - **Penyeragaman Style Tabel (100% Selaras)**:
      - Kolom **Pegawai** kini menampilkan Nama Pegawai (bold) dan NIP pegawai di bawahnya secara rapi.
      - Menyeragamkan padding sel tabel menjadi `px-3.5 py-3` yang lega dan proporsional (dari sebelumnya `px-2 py-2` yang sesak).
      - Garis pembatas baris menggunakan `divide-y divide-gray-200` yang lembut dan modern.
    - **Standarisasi Tombol Aksi**: Mengganti tombol mencolok merah *"✕ Batal"* dengan tombol netral berkelas *"[✏️ Aksi]"* bersimbol pensil (`border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 shadow-2xs`), selaras dengan tombol aksi pada Ketua Tim.
    - **Modal Aksi Terpadu**: Menyempurnakan pop-up aksi menjadi modal *Kelola Pengajuan Lembur* dengan dua tab terstruktur:
      - **Tab 1: Edit Uraian**: Memudahkan admin mengoreksi teks narasi lembur langsung dari modal.
      - **Tab 2: Batalkan Pengajuan**: Khusus pembatalan transaksi salah tanggal/dobel input dengan isian alasan pembatalan.

11. **Perapihan Rinci Isi Sel & Tata Letak Tabel Monitoring Admin (`resources/views/admin/lembur.blade.php`)**:
    - **Perataan Vertikal Selaras (`align-middle`)**: Menambahkan kelas `align-middle` pada seluruh elemen `<td>` sehingga teks dan lencana pada baris dengan konten bertingkat (seperti nama & NIP, atau uraian panjang) tetap berada di tengah secara simetris dan rapi.
    - **Modernisasi Badge Presensi**:
      - Status presensi hadir diubah menjadi chip/pill interaktif berkelas (`bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full px-2.5 py-1 text-xs`) lengkap dengan *status indicator dot* dan ikon panah mikro.
      - Status belum presensi dikemas seragam dalam bentuk pill abu-abu netral bertuliskan *"Belum Presensi"*, menjaga konsistensi tinggi baris tabel.
    - **Standardisasi Badge Status**:
      - Mengadopsi desain pill modern dengan *indicator dot* berdiameter 1.5 (`w-1.5 h-1.5 rounded-full`) dan palet warna lembut (`amber-50`, `blue-50`, `emerald-50`, `rose-50`, `gray-50`) bersanding dengan *border* senada, identik dengan halaman Ketua Tim.
      - Menyelaraskan injeksi DOM dinamis JavaScript pada fungsi `submitBatalAdmin` agar memiliki format badge yang sama persis setelah pembatalan pengajuan.
    - **Tipografi Jam & NIP**:
      - Jam Diajukan dan Jam Disetujui diformat dengan kontainer monospace halus (`font-mono text-[11px] bg-gray-50 border border-gray-200/80 px-2 py-0.5 rounded-md`) untuk keterbacaan scan instan.
      - NIP pegawai menggunakan font monospace (`font-mono text-[11px] text-gray-500`) yang rapi di bawah nama pegawai berbobot tebal (`font-semibold text-gray-900`).
    - **Kontainer Tabel Modern**: Mengganti pembungkus tabel menjadi `rounded-2xl border border-gray-200/80 bg-white shadow-xs` dengan `thead` bernuansa `bg-gray-50/90 border-b border-gray-200` yang sejuk dan tidak kaku.
    - **Penyempurnaan Empty State**: Menampilkan ilustrasi berkas kosong dengan pesan yang komunikatif dan tombol *Reset Semua Filter* jika filter aktif.

### C. Hasil Verifikasi & Uji Kompilasi
- **Aset Vite**: Dijalankan `npm run build`, sukses mengompilasi CSS (`app-CjM3llgO.css`, 90.96 kB) dan JS (`app-UVMdT4O_.js`, 37.50 kB) tanpa peringatan (*zero warnings/errors*).
- **Cache Blade**: `php artisan view:clear` dan `php artisan view:cache` berhasil tanpa kesalahan sintaks.
- **Integritas Sistem**: Tidak ada sintaks PHP, rute Laravel, maupun logika bisnis yang terganggu.

---

## 18. Sinkronisasi Otomatis Kelayakan (`eligible`) pada Rekapitulasi Pegawai (`app/Http/Controllers/pegawai/RekapitulasiController.php`)

### A. Latar Belakang Masalah
1. Sebelumnya, method `koreksiUntukBulan` baru terpasang pada modul Admin (`admin/RekapitulasiController.php` dan `app/Exports/RekapitulasiExport.php`).
2. Pada modul Pegawai (`app/Http/Controllers/pegawai/RekapitulasiController.php`), kueri langsung memfilter:
   `->where('submitted_by_NIP', $nip)->where('status', 'approved')->where('eligible', 1)`.
3. Jika pengajuan lembur pegawai telah disetujui (`status = 'approved'`) namun nilai `eligible`-nya masih `NULL` (karena belum dievaluasi oleh sistem), data tersebut tidak muncul di halaman rekapitulasi pegawai (`/rekapitulasi`).

### B. Solusi & Perbaikan
1. Mengintegrasikan trait `App\Traits\KoreksiLembur` pada `App\Http\Controllers\pegawai\RekapitulasiController`.
2. Menambahkan pemanggilan `$this->koreksiUntukBulan((int) $tahun, (int) $bln);` sesaat sebelum mengeksekusi kueri rekapitulasi pegawai.
3. *Hasil*: Begitu pegawai membuka menu `/rekapitulasi`, sistem secara otomatis menyapu (*sweep*) seluruh pengajuan `approved` pada bulan tersebut yang `eligible`-nya masih `NULL`, mengevaluasinya terhadap presensi riil, dan menampilkannya seketika pada tabel rekapitulasi.

---

## 19. Perbaikan Scrolling & Responsivitas Modal Persetujuan (Kabag Umum, Admin, & Ketua Tim)

### A. Latar Belakang Masalah
Pada layar monitor/laptop dengan tinggi viewport terbatas (misal 674px - 768px atau laptop dengan display scaling Windows 125%/150%), dialog modal persetujuan Kabag Umum (`#modalKabag`) sebelumnya tidak memiliki batas tinggi maksimal (`max-h-[...]`) dan tidak memiliki scrolling mandiri pada body (`overflow-y-auto`).
Akibatnya:
1. Kartu modal terpusat secara kaku (`items-center`) dengan tinggi melebihi tinggi layar.
2. Bagian header dialog terdorong ke atas keluar layar.
3. Bagian footer aksi ("Batal" dan "Simpan Keputusan") terpotong di bawah batas layar (*off-screen*).
4. Pengguna tidak dapat men-scroll tampilan dan terpaksa memperkecil ukuran layar / zoom out browser untuk menjangkau tombol aksi.

### B. Solusi & Perubahan Arsitektur Modal (*Docked Header & Footer*)
Menerapkan standar desain dialog enterprise responsif:
1. **Batas Tinggi & Flexbox Vertikal**:
   - Kontainer kartu modal menggunakan `max-h-[90vh] flex flex-col overflow-hidden my-auto`, menjamin dialog selalu muat di dalam viewport layar dengan margin simetris di atas dan bawah.
2. **Pinned / Docked Header (`shrink-0`)**:
   - Header modal (judul, subjudul deskriptif, dan tombol tutup silang `×`) tetap tersemat di bagian paling atas dan tidak pernah tergulung hilang saat pengguna memeriksa data.
3. **Scrollable Body (`flex-1 overflow-y-auto`)**:
   - Konten isian (ringkasan pegawai, jam disetujui, hint presensi pulang, textarea uraian kegiatan, catatan arahan, dan tombol opsi keputusan) diletakkan dalam kontainer `flex-1 overflow-y-auto` dengan scrollbar vertikal mandiri yang halus.
4. **Pinned / Docked Footer (`shrink-0`)**:
   - Footer tombol aksi ("Batal" dan "Simpan Keputusan") selalu terlihat (*always in view*) dan siap diklik di bagian bawah tanpa mengharuskan pengguna menggulung layar ke dasar.
5. **Safety Net Outer Scroll**:
   - Pembungkus modal terluar (`#modalKabag`, `#modalKeputusan`) ditambahkan kelas `overflow-y-auto` untuk memastikan fleksibilitas penuh jika diakses pada viewport ultra-kecil atau saat keyboard virtual mobile aktif.

### C. File yang Diperbarui
1. `resources/views/kabag-umum/pengajuan.blade.php`: Modal Keputusan Kabag Umum (`#modalKabag`) dan Modal Presensi (`#modalPresensi`).
2. `resources/views/admin/pengajuan.blade.php`: Modal Keputusan Admin (`#modalKeputusan`).
3. `resources/views/ketua-tim/pengajuan.blade.php`: Modal Keputusan Ketua Tim (`#modalKeputusan`).

---

## 20. Pelacakan Bundel Aset Produksi (`public/build`) dalam Repositori Git

### A. Latar Belakang Masalah
1. Secara default pada Laravel 11/Vite, direktori `/public/build` dimasukkan ke dalam berkas `.gitignore`.
2. Pada server cPanel / shared hosting institusi (BPS), umumnya tidak tersedia akses Node.js / NPM atau terminal shell untuk mengeksekusi perintah `npm run build` secara mandiri.
3. Dampaknya, jika aset terkompilasi tidak diikutsertakan ke dalam repositori Git:
   - Server produksi tidak akan menerima berkas CSS dan JavaScript terbaru saat `git pull` dijalankan.
   - Tampilan UI di server produksi dapat mengalami *broken layout* atau error pemanggilan manifest Vite (`Vite manifest not found at: .../public/build/manifest.json`).

### B. Solusi & Implementasi
1. **Penyesuaian `.gitignore`**:
   - Menghapus aturan `/public/build` dari file `.gitignore` agar bundel produksi Vite (`manifest.json`, CSS, dan JS) otomatis terlacak oleh Git.
   - **Tetap Mengabaikan `/public/hot`**: Aturan `/public/hot` tetap wajib diabaikan oleh Git. Berkas `public/hot` hanya tercipta saat lokal menjalankan `npm run dev` (Vite Hot Module Replacement). Jika berkas `public/hot` terbawa ke server produksi, directive `@vite` pada Blade akan keliru mengarahkan aset ke `http://localhost:5173` yang menyebabkan tampilan produksi rusak.
2. **Kompilasi Aset Produksi**:
   - Menjalankan `npm run build` di lingkungan lokal.
   - Hasil kompilasi:
     - `public/build/manifest.json` (peta aset Vite).
     - `public/build/assets/app-*.css` (91 kB berkas CSS terkompilasi TailwindCSS).
     - `public/build/assets/app-*.js` (37.5 kB berkas JavaScript terkompilasi).
3. **Hasil untuk Lingkungan Produksi**:
   - Begitu server produksi mengeksekusi `git pull`, seluruh aset tampilan yang sudah terkompilasi langsung terunduh secara instan.
   - Server tidak lagi memerlukan Node.js atau NPM, dan tampilan UI di server dipastikan 100% identik dengan tampilan lokal.

---

## 21. Rekayasa Ulang Modal Keputusan Lembur (*Zero-Scroll UX*) & Optimalisasi Responsivitas Mobile

### A. Latar Belakang Masalah
1. Sebelumnya pada modal aksi persetujuan lembur (`#modalKeputusan` di Ketua Tim & Admin, serta `#modalKabag` di Kabag Umum), opsi **Pilihan Keputusan (Setujui / Tolak)** diletakkan di bagian paling bawah formulir, di bawah textarea Uraian Kegiatan (`rows="4"`) dan Catatan (`rows="3"`).
2. Akibat penumpukan vertikal tersebut:
   - Tombol keputusan terdorong ke bawah batas tampilan (*below the fold*) dan tertutup oleh footer modal.
   - Muncul scrollbar vertikal pada modal. Pengguna sering kali tidak menyadari keberadaan tombol "Setujui" / "Tolak" karena harus menggulung layar ke bawah terlebih dahulu.
   - Saat pengguna langsung mengklik tombol "Simpan Keputusan" di footer, sistem memunculkan peringatan *"Pilih keputusan terlebih dahulu"*, membingungkan pejabat peninjau.
3. Pada layar smartphone sempit (< 400px), judul navbar yang panjang berpotensi mendorong tombol profil dan toggle drawer.

### B. Solusi Desain & Implementasi
1. **Penataan Alur Natural dari Atas ke Bawah (*Top-to-Bottom Natural Reading Flow*)**:
   - Berdasarkan hierarki UX kedinasan, pejabat membaca dan meninjau data terlebih dahulu (*Jam Disetujui* $\rightarrow$ *Uraian Tugas* $\rightarrow$ *Catatan*), baru kemudian menetapkan **Pilihan Keputusan (*Setujui* / *Tolak* / *Batalkan*) di bagian bawah sebelum tombol simpan**.
   - Blok pilihan keputusan (`Setujui` / `Tolak` / `Batalkan`) diletakkan tepat di bawah kolom Catatan (tepat di atas tombol aksi footer), dengan pembatas garis halus `border-t border-gray-100`.
   - Menggunakan komponen *segmented pill buttons* yang jelas, modern, dan mudah diklik/ditekan (kontras warna tegas: hijau emerald untuk Setujui, merah mawar untuk Tolak, dan amber untuk Batalkan).
   - **Default Otomatis `Setujui`**: Untuk pengajuan baru/pending, pilihan `Setujui` langsung aktif secara bawaan saat modal dibuka, sehingga pejabat dapat langsung memverifikasi jam/uraian dan menyimpannya dalam 1 klik tanpa kebingungan.
2. **Desain Kompak Bebas Gulir (*Zero-Scroll Fit*)**:
   - Input jam mulai dan jam selesai disetujui ditata berdampingan rapi dalam format `grid grid-cols-2`.
   - Textarea Uraian Kegiatan dan Catatan diatur ke `rows="2"` (kompak dan proporsional, namun tetap mempertahankan fleksibilitas tarik vertikal `resize-y`).
   - Pengetatan padding dan jarak vertikal (`space-y-2.5` / `space-y-3` dan `p-4 sm:p-5`), sehingga total tinggi modal hanya **~330px - 340px**.
   - **Hasil**: Bahkan di layar monitor dengan viewport terbatas (506px), seluruh isi modal dari atas sampai tombol Simpan di footer **100% langsung terlihat utuh tanpa ada scrollbar sama sekali**.
3. **Peningkatan Responsivitas Mobile (*Mobile-Friendly Touch*)**:
   - Seluruh tombol aksi modal memiliki tinggi sentuh ergonomis (*thumb-friendly tap targets* $\ge 42$px).
   - Judul halaman pada navbar ditambahkan kelas `truncate max-w-[190px] xs:max-w-[260px] sm:max-w-none` agar tidak meluap (*overflow*) pada layar smartphone 360px - 390px.
   - Seluruh tabel pengajuan telah terlindungi oleh pembungkus `overflow-x-auto` yang mulus.

### C. File yang Dimodifikasi
1. `resources/views/ketua-tim/pengajuan.blade.php`: Modal Keputusan Ketua Tim (`#modalKeputusan`).
2. `resources/views/kabag-umum/pengajuan.blade.php`: Modal Keputusan Kabag Umum (`#modalKabag`).
3. `resources/views/admin/pengajuan.blade.php`: Modal Keputusan Admin (`#modalKeputusan`).
4. `resources/views/partials/navbar.blade.php`: Responsivitas judul halaman pada layar mobile.

---

## 22. Optimalisasi Filter Periode "Semua Bulan (Tahun Berjalan)" & Urutan Bawaan Terbaru ke Terlama (Ketua Tim, Kabag Umum, & Admin)

### A. Latar Belakang Masalah & Alur Proses Bisnis
1. **Masalah Bulan Berjalan vs Approval Bulan Berjalan + 1**:
   - Berdasarkan proses bisnis riil di BPS, Ketua Tim melakukan persetujuan lembur pada bulan berikutnya (bulan berjalan + 1). Contohnya: di bulan Oktober, Ketua Tim perlu menyetujui pengajuan lembur yang dilakukan anggota pada bulan September.
   - Pada implementasi sebelumnya, saat halaman dibuka tanpa parameter, sistem secara otomatis mengunci kueri ke bulan berjalan saat itu (`$bulan = now()->format('Y-m')`), yaitu Oktober.
   - Akibatnya, saat Ketua Tim membuka akun di awal bulan Oktober, pengajuan lembur bulan September tidak muncul di tabel. Hal ini memicu kebingungan Ketua Tim yang mengira anggotanya belum mengajukan lembur (*"Kok tidak ada pengajuan kamu di akun saya"*).
2. **Karakteristik Volume Data**:
   - Volume lembur per bulan tidak terlalu banyak (hanya berkisar beberapa pengajuan per pegawai), sehingga pembatasan kaku per bulan kalender justru menyulitkan monitoring riwayat lembur.
3. **Kebutuhan Sorting**:
   - Pejabat peninjau membutuhkan pengajuan lembur bertanggal paling baru (*newest*) langsung tampil di baris paling atas agar tidak terlewatkan (*"sortnya dari terbaru ke terlama"*).

### B. Solusi Desain & Implementasi (Menerapkan Standar Anti-Slop & Human UX)
1. **Default Periode: Semua Bulan Tahun Berjalan (`now()->year`)**:
   - Jika parameter `bulan` tidak dikirim (atau bernilai `'all'`): Sistem memfilter data berdasarkan tahun kalender berjalan (`whereYear('t.date', $currentYear)`), tanpa menyaring bulan (`whereMonth` tidak dipanggil).
   - Label Period Picker pada toolbar menampilkan teks dinamis: **"Semua Bulan {Tahun}"** (contoh: **"Semua Bulan 2026"**).
   - Di dalam panel Period Picker, ditambahkan tombol utama yang menonjol dan ergonomis: **"Semua Bulan ({Tahun})"** di atas grid 12 bulan (Jan - Des). Tombol ini memiliki status aktif berwarna oranye amber BPS (`#faa938`) saat filter Semua Bulan aktif.
   - Pengguna tetap memiliki kebebasan penuh untuk memilih bulan spesifik (misal: "Sep 2026") kapan pun diperlukan, dan dapat kembali ke "Semua Bulan" hanya dengan 1 klik.
2. **Default Sorting: Tanggal Terbaru ke Terlama (`desc`)**:
   - Mengubah urutan bawaan (*default sort*) dari `priority` menjadi `desc` (`t.date desc, t.id_transaksi desc`).
   - Opsi `desc` ("Terbaru") menjadi pilihan pertama dan teratas pada dropdown kolom Tanggal Lembur di tabel.
   - Pejabat tetap dapat mengubah urutan ke "Terlama" (`asc`) atau "Prioritas Status" (`priority`) sesuai preferensi.
3. **Penerapan Serentak & Konsisten Lintas Peran Approval**:
   - Diterapkan secara simetris pada 3 controller dan view approval:
     1. **Ketua Tim**: `app/Http/Controllers/ketuatim/PengajuanController.php` & `resources/views/ketua-tim/pengajuan.blade.php`.
     2. **Kabag Umum**: `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` & `resources/views/kabag-umum/pengajuan.blade.php`.
     3. **Admin**: `app/Http/Controllers/admin/PengajuanController.php` & `resources/views/admin/pengajuan.blade.php`.
4. **Kepatuhan Prinsip Anti-Slop (Rule R-02, R-03, R-25, R-26, R-31)**:
   - **R-02 (Copywriting Alami)**: Membersihkan seluruh karakter em dash (`—`) pada teks pencarian pegawai dan modal presensi menjadi tanda kurung atau strip biasa (`Nama (NIP)`).
   - **R-03 (Mobile Responsiveness)**: Tombol "Semua Bulan" memiliki target sentuh min 42px dengan padding yang nyaman untuk jari jemari.
   - **R-25 (Color Contrast WCAG AA)**: Teks tombol aktif menggunakan kontras tinggi terhadap latar belakang amber (`text-white` / `text-slate-950`).
   - **R-26 (Interactive Elements)**: Semua tombol panel memiliki event handler riil tanpa dead control.
   - **R-31 (Articulated Reason)**: Logika filter secara eksplisit menyelesaikan kendala persetujuan lintas bulan kalender $N+1$.

### C. Berkas yang Diperbarui
| No | File | Keterangan |
| :---: | :--- | :--- |
| 1 | `app/Http/Controllers/ketuatim/PengajuanController.php` | Filter default semua bulan tahun berjalan (`$bulan = 'all'`) dan default sort `desc`. |
| 2 | `resources/views/ketua-tim/pengajuan.blade.php` | Label Period Picker dinamis, tombol Semua Bulan tahun berjalan di panel, default sort Terbaru, dan pembersihan em dash. |
| 3 | `app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php` | Filter default semua bulan tahun berjalan dan default sort `desc` untuk Kabag Umum. |
| 4 | `resources/views/kabag-umum/pengajuan.blade.php` | Label Period Picker dinamis, tombol Semua Bulan di panel, default sort Terbaru, dan pembersihan em dash. |
| 5 | `app/Http/Controllers/admin/PengajuanController.php` | Filter default semua bulan tahun berjalan dan default sort `desc` untuk Admin. |
| 6 | `resources/views/admin/pengajuan.blade.php` | Label Period Picker dinamis, tombol Semua Bulan di panel, default sort Terbaru, dan pembersihan em dash. |---

## 23. Penanganan HTTP 403 Forbidden Firewall BPS (WAF / ModSecurity) pada Form Dokumentasi Lembur

### A. Latar Belakang Masalah
1. **Error HTTP 403 Forbidden di Server BPS**:
   - Saat pengujian di server BPS (`...bps.go.id`), penambahan dokumentasi bukti lembur melalui modal "Tambah Dokumentasi" selalu gagal dan memunculkan error **HTTP 403 Forbidden** di browser DevTools (*Network Tab*).
2. **Penyebab Utama (WAF / ModSecurity URL Filter)**:
   - Server BPS dilindungi oleh Web Application Firewall (WAF) / ModSecurity dengan aturan OWASP CRS (seperti aturan deteksi RFI - *Remote File Inclusion* dan SSRF - *Server-Side Request Forgery*).
   - Firewall secara ketat memindai isi badan formulir (*POST request body*). Ketika parameter `file_path` memuat teks skema URL mentah (`https://`, `http://`, `drive.google.com/`, dll.), WAF memblokir koneksi secara langsung sebelum permintaan sampai ke aplikasi Laravel.
3. **Arahan Mentor BPS**:
   - Melakukan enkripsi/obfuskasi nilai link pada sisi frontend sebelum data dikirim melalui HTTP POST, lalu melakukan dekripsi kembali pada controller backend sebelum divalidasi dan disimpan ke basis data.

### B. Solusi Desain & Implementasi
1. **Frontend Client-Side Encoding (`b64:`)**:
   - Menambahkan *event listener submit* pada formulir modal dokumentasi (`#formDok`).
   - Sebelum formulir dikirim, link URL dibersihkan dan otomatis dilengkapi skema `https://` jika pengguna mengetik tanpa protokol.
   - Nilai input diubah menjadi format Base64 dengan awalan `b64:` menggunakan fungsi standar UTF-8:
     ```javascript
     input.type = 'text'; // Mencegah benturan validasi tipe URL bawaan browser terhadap string b64:...
     input.value = 'b64:' + btoa(unescape(encodeURIComponent(val)));
     ```
   - Payload yang dikirim melalui jaringan tidak lagi memuat karakter URL mentah (`https://` atau `drive.google.com/`), sehingga lolos dari filter WAF server BPS.
   - Menambahkan *event listener blur* pada kotak input agar otomatis menyematkan awalan `https://` jika pengguna hanya menyalin `drive.google.com/...`.
2. **Backend Automatic Decoding & Normalisasi**:
   - Pada method `storeDoc` di `LemburController` dan `admin/LemburController`:
     - Mendeteksi apakah input `file_path` memiliki awalan `b64:` atau `enc:`, atau merupakan string Base64.
     - Jika terenkripsi/Base64, dilakukan `base64_decode` untuk mengembalikan string URL asli yang bersih.
     - Melakukan normalisasi protokol jika diperlukan, lalu melakukan `$request->merge(['file_path' => $filePath])`.
     - Validasi Laravel (`$request->validate(['file_path' => 'required|url|max:255'])`) dijalankan terhadap URL asli yang sudah didekode.
     - Nilai URL asli yang tersimpan ke tabel `m_dokumentasi.file_path` tetap berupa tautan bersih (`https://drive.google.com/...`), sehingga tautan "Lihat" di tabel riwayat lembur tetap dapat diklik normal tanpa perubahan skema database.
3. **Kompatibilitas Penuh (*Backward Compatibility*)**:
   - Backend tetap menerima pengiriman URL biasa tanpa Base64 (misalnya pada pengujian lokal atau API), sehingga tidak merusak fungsionalitas yang sudah ada.

### C. Berkas yang Diperbarui
| No | File | Keterangan |
| :---: | :--- | :--- |
| 1 | `app/Http/Controllers/LemburController.php` | Penambahan dekripsi Base64 dan normalisasi URL pada method `storeDoc`. |
| 2 | `app/Http/Controllers/admin/LemburController.php` | Penambahan dekripsi Base64 dan normalisasi URL pada method `storeDoc` Admin. |
| 3 | `resources/views/lembur.blade.php` | Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi pegawai. |
| 4 | `resources/views/ketua-tim/lembur.blade.php` | Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi Ketua Tim. |
| 5 | `resources/views/admin/lembur.blade.php` | Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi Admin. |

---

## 24. Optimalisasi Tampilan Mobile: Pemindahan Hamburger Button ke Kiri Atas & Fitur Geser Horizontal Tabel (Swipe & Drag-to-Scroll ala SIMANTIK)

### A. Latar Belakang Masalah & Kebutuhan Pengguna Mobile
1. **Posisi Ikon Hamburger Button (Navigasi Mobile)**:
   - Sebelumnya, tombol *hamburger toggle* navigasi terletak di sisi kanan atas (berdampingan dengan dropdown profil pengguna).
   - Pengguna meminta agar tombol Hamburger dipindahkan ke **kiri atas** sesuai konvensi umum aplikasi mobile dan sistem administrasi BPS, mendampingi judul halaman secara natural.
2. **Keterbacaan Tabel Lembur pada Layar Smartphone**:
   - Tabel administrasi lembur memiliki banyak kolom penting (*Tanggal, Nama/NIP, Jam Diajukan, Jam Disetujui, Durasi, Presensi Pulang, Uraian Kegiatan, Status, Aksi*).
   - Pada layar smartphone (lebar 360px - 414px), beberapa tabel (terutama tabel Persetujuan Kabag Umum dan Akumulasi) belum memiliki lebar minimum (`min-w-[...]`) yang memadai, sehingga teks kolom terhimpit vertikal (*text squishing*) dan tidak nyaman dibaca.
   - Merujuk pada implementasi di aplikasi saudara (**SIMANTIK BPS**), pengguna menginginkan tabel dapat **digeser ke kiri dan ke kanan secara leluasa (*horizontal swipe & drag-to-scroll*)** sehingga seluruh data tabel dapat terlihat utuh dan lega.

### B. Solusi Desain & Implementasi (Menerapkan Standar Anti-Slop & Mobile UX)
1. **Pemindahan Hamburger Button ke Kiri Atas (`navbar.blade.php`)**:
   - Mereposisi tombol `#sidebar-toggle` ke sisi paling kiri pada bilah navbar, tepat sebelum komponen judul halaman (`@yield('title')`).
   - Struktur navbar mobile kini tersusun rapi: `[ Hamburger Icon ] [ Judul Halaman ] ...................... [ Avatar & Profil ]`.
   - Menambahkan tombol tutup silang `(X)` di dalam header drawer sidebar mobile (`sidebar.blade.php`) agar pengguna dapat menutup menu secara intuitif selain dengan menekan area latar belakang (*overlay backdrop*).
2. **Styling Geser Horizontal Universal (`app.css` & Standar SIMANTIK)**:
   - Mengadopsi konfigurasi CSS responsive table dari SIMANTIK ke `resources/css/app.css` untuk kelas `.table-responsive` dan `.overflow-x-auto`:
     - `-webkit-overflow-scrolling: touch !important` (akselerasi *hardware momentum scrolling* pada iOS dan Android).
     - `touch-action: pan-x pan-y !important` (mencegah konflik scroll vertikal halaman dengan gestur swipe horizontal tabel).
     - `overscroll-behavior-x: contain` (mencegah navigasi browser *back/forward gesture* terpicu saat swipe tabel).
     - Scrollbar ramping modern berukuran 6px bertema warna slate halus (`#cbd5e1` / `#f1f5f9`).
3. **Fitur Drag-to-Scroll Desktop & Touch Momentum (`app.js`)**:
   - Menambahkan utilitas `initDragAndSwipeScroll` pada `resources/js/app.js` yang otomatis mendeteksi semua kontainer tabel:
     - Di komputer/laptop (desktop), pengguna dapat menahan klik kiri mouse (*mouse drag*) untuk menggeser tabel ke kiri-kanan secara instan.
     - Otomatis mengabaikan elemen klik interaktif (*tombol, tautan, input, dropdown select*) agar fungsi tombol Aksi dan Lihat Dokumentasi tetap bekerja normal tanpa terpicu gestur drag.
4. **Standarisasi Lebar Minimum Tabel & Petunjuk Sentuh Visual**:
   - Menetapkan batas lebar minimum proporsional pada semua tabel (misal: `min-w-[1100px]` pada tabel persetujuan Kabag Umum, `min-w-[950px]` pada Daftar Hadir dan Akumulasi) agar kolom tabel tidak pernah termampatkan di layar kecil.
   - Menambahkan teks petunjuk visual ergonomis di atas tabel khusus layar ponsel (`sm:hidden`):
     $$\text{👉 "Geser tabel ke kiri / kanan untuk melihat kolom lengkap"}$$

### C. Berkas yang Diperbarui
| No | File | Keterangan |
| :---: | :--- | :--- |
| 1 | `resources/views/partials/navbar.blade.php` | Pemindahan tombol hamburger toggle ke kiri atas sebelum judul halaman. |
| 2 | `resources/views/partials/sidebar.blade.php` | Penambahan tombol tutup silang `(X)` pada header drawer sidebar mobile. |
| 3 | `resources/css/app.css` | Styling geser tabel horizontal ala SIMANTIK, touch momentum, dan scrollbar ramping. |
| 4 | `resources/js/app.js` | Script `initDragAndSwipeScroll` untuk drag-to-scroll mouse desktop dan swipe sentuh mobile. |
| 5 | `resources/views/kabag-umum/pengajuan.blade.php` | Penambahan `min-w-[1100px]` dan petunjuk geser mobile pada tabel Kabag Umum. |
| 6 | `resources/views/ketua-tim/pengajuan.blade.php` | Penambahan petunjuk geser mobile pada tabel persetujuan Ketua Tim. |
| 7 | `resources/views/ketua-tim/lembur.blade.php` | Penambahan petunjuk geser mobile pada tabel lembur Ketua Tim. |
| 8 | `resources/views/lembur.blade.php` | Penambahan petunjuk geser mobile pada tabel lembur pegawai. |
| 9 | `resources/views/admin/lembur.blade.php` | Penambahan petunjuk geser mobile pada tabel monitoring lembur Admin. |
| 10 | `resources/views/admin/pengajuan.blade.php` | Penambahan petunjuk geser mobile pada tabel persetujuan Admin. |
| 11 | `resources/views/admin/daftar_hadir.blade.php` | Penambahan `min-w-[950px]` dan petunjuk geser mobile pada tabel Daftar Hadir. |
| 12 | `resources/views/admin/riwayat_presensi.blade.php` | Penggantian wrapper `overflow-x-auto`, `min-w-[700px]`, dan petunjuk geser mobile. |
| 13 | `resources/views/akumulasi.blade.php` | Penggantian wrapper `overflow-x-auto`, `min-w-[950px]`, dan petunjuk geser mobile. |
| 14 | `resources/views/rekapitulasi.blade.php` | Penambahan petunjuk geser mobile pada tabel rekapitulasi pegawai. |
| 15 | `resources/views/pimpinan/pengajuan.blade.php` | Penambahan petunjuk geser mobile pada tabel persetujuan Pimpinan. |
| 16 | `resources/views/admin/akumulasi.blade.php` | Penambahan petunjuk geser mobile pada tabel akumulasi Admin. |

---

## 25. Penyeragaman Desain Header & Toolbar Halaman Lembur (Pegawai & Admin Mengikuti Pola Ketua Tim, Perbaikan Text-Wrapping "Semua Tanggal")

### A. Latar Belakang & Identifikasi Masalah
1. **Pemotongan Teks / Awkward Text-Wrapping pada Tombol Tanggal**:
   - Pada halaman **Pengajuan Lembur Pegawai** (`resources/views/lembur.blade.php`), tombol filter tanggal (`#dateBtn`) sebelumnya tidak memiliki kelas `whitespace-nowrap`.
   - Ketika halaman dibuka pada layar perangkat mobile, layar laptop beresolusi sedang, atau saat kontainer flexbox menyempit, teks label **"Semua Tanggal"** terpotong/terbungkus secara canggung ke 2 baris vertikal (*"Semua"* di baris atas dan *"Tanggal"* di baris bawah), merusak kerapian antarmuka.
2. **Inkonsistensi Pola Tata Letak Antar Peran (*Design Pattern Discrepancy*)**:
   - Pada halaman **Ketua Tim** (`resources/views/ketua-tim/lembur.blade.php`), telah diterapkan struktur **Page Header modern**: judul `<h1>` semantik + subjudul deskriptif + tombol aksi primer `+ Ajukan Lembur` di kanan atas header, serta tombol filter toolbar berbentuk kartu modern `rounded-xl` dengan bayangan lembut `shadow-2xs`.
   - Sebaliknya pada halaman **Pegawai** (`lembur.blade.php`) dan **Admin** (`admin/lembur.blade.php`), antarmuka masih menggunakan pola lawas: tombol filter berbentuk pil lonjong `rounded-full` dan tombol aksi utama (*"Ajukan Lembur"* dan *"Unduh Excel"*) diletakkan berdesakan di ujung kanan toolbar filter.
3. **Kebutuhan Pengguna**:
   - Memperbaiki teks tanggal agar tidak terpotong ke 2 baris.
   - Menyeragamkan desain antarmuka Pegawai dan Admin agar mengikuti pola modern Ketua Tim, menciptakan *single unified design system* di seluruh aplikasi.

### B. Rincian Implementasi & Perbaikan Teknis

1. **Halaman Pengajuan Lembur Pegawai (`resources/views/lembur.blade.php`)**:
   - **Page Header Terstruktur**:
     - Menambahkan judul `<h1>` semantik *"Pengajuan Lembur Pribadi"* dan subjudul *"Kelola dan pantau riwayat pengajuan kegiatan lembur mandiri Anda."*.
     - Menempatkan tombol utama **"+ Ajukan Lembur"** (`#btnAjukan`) di sisi kanan header dengan gaya oranye amber BPS (`bg-[#faa938] hover:bg-[#fd9a10] rounded-xl font-semibold shadow-xs`).
   - **Modernisasi Toolbar Filter**:
     - Mengubah seluruh elemen tombol kontrol (Date Picker, Filter Bulan, Per Halaman, Search Tim, dan Reset Filter) dari bentuk pil lonjong (`rounded-full`) menjadi kartu modern bersudut halus (`rounded-xl border border-gray-200 bg-white shadow-2xs`).
     - Menyematkan kelas `whitespace-nowrap` pada tombol `#dateBtn` dan elemen `<span id="dateLabel">`, menjamin teks *"Semua Tanggal"* atau tanggal spesifik yang dipilih selalu berada dalam 1 baris utuh di semua ukuran layar.
   - **Integritas Selektor & Fungsionalitas**:
     - Seluruh ID elemen DOM (`datePicker`, `dateBtn`, `dateLabel`, `dateValue`, `datePanel`, `filterBulan`, `perHalaman`, `searchTim`, `dropdownTim`, `listTim`, `btnResetFilter`, `btnAjukan`) dipertahankan 100% tanpa mengubah alur JavaScript maupun AJAX modal pengajuan.

2. **Halaman Monitoring & Pengajuan Lembur Admin (`resources/views/admin/lembur.blade.php`)**:
   - **Page Header Terstruktur**:
     - Menambahkan judul `<h1>` semantik *"Monitoring & Pengajuan Lembur"* dan subjudul *"Kelola, monitor status verifikasi, dan rekapitulasi data lembur seluruh pegawai."*.
     - Menyematkan sekelompok tombol aksi (*Action Buttons*) di kanan atas header:
       - **Unduh Excel** (`#btnExport`): tombol netral berkelas (`border border-gray-200 bg-white text-gray-700 hover:border-[#faa938] hover:text-[#faa938] rounded-xl shadow-2xs`).
       - **Ajukan Lembur** (`#btnAjukan`): tombol primer oranye amber BPS (`bg-[#faa938] rounded-xl shadow-xs`).
   - **Modernisasi Toolbar Filter**:
     - Mengubah Date Picker dan input pencarian Pegawai serta Tim menjadi `rounded-xl shadow-2xs` yang selaras.
     - Menyematkan `whitespace-nowrap` pada tombol `#dateBtn` dan `<span id="dateLabel">`.

3. **Halaman Pengajuan Lembur Ketua Tim (`resources/views/ketua-tim/lembur.blade.php`)**:
   - Menyematkan pengaman `whitespace-nowrap` pada tombol `#dateBtn` dan `<span id="dateLabel">` agar kebal terhadap pemotongan teks di layar mobile/responsif ultra-sempit.

### C. Berkas yang Diperbarui
| No | File | Keterangan |
| :---: | :--- | :--- |
| 1 | `resources/views/lembur.blade.php` | Page Header semantik (H1 + subjudul + tombol Ajukan Lembur), modernisasi filter `rounded-xl`, dan perbaikan text-wrap `whitespace-nowrap` pada `dateBtn`/`dateLabel`. |
| 2 | `resources/views/admin/lembur.blade.php` | Page Header semantik (H1 + subjudul + tombol Unduh Excel & Ajukan Lembur), modernisasi filter `rounded-xl`, dan perbaikan text-wrap `whitespace-nowrap`. |
| 3 | `resources/views/ketua-tim/lembur.blade.php` | Penambahan `whitespace-nowrap` pada `dateBtn` dan `dateLabel` untuk proteksi konsisten di layar kecil. |

---

## 26. Implementasi Scrollbar Visual Interaktif pada Tabel Mobile & Peningkatan Kontras Scrollbar Universal (Anti-Confusion UX)

### A. Latar Belakang & Kebutuhan Pengguna
1. **Kebiasaan OS Mobile Menyembunyikan Scrollbar (*Hidden Overlay Scrollbar*)**:
   - Pada perangkat smartphone (iOS Safari & Android Chrome), peramban web secara bawaan menyembunyikan scrollbar saat layar dalam keadaan diam (tidak sedang disentuh).
   - Pengguna menyampaikan masukan bahwa meskipun sudah ada teks *"Geser tabel ke kiri / kanan untuk melihat kolom lengkap"*, ketiadaan scrollbar visual tetap menimbulkan kebingungan bagi pengguna awam di ponsel.
2. **Keterbacaan Scrollbar Desktop**:
   - Pada layar laptop/PC, scrollbar 6px dengan warna abu-abu pudar sebelumnya kurang mencolok di atas latar belakang tabel putih.

### B. Rincian Solusi & Implementasi Teknis

1. **Scrollbar Visual Interaktif Khusus Mobile (`.table-scroll-hint`, `.table-scroll-track`, `.table-scroll-thumb`)**:
   - Menambahkan bilah scrollbar visual horizontal yang diletakkan tepat di atas tabel (pada blok helper mobile `sm:hidden`).
   - Struktur komponen:
     - **Header Bar**: Ikon gestur horizontal + teks *"Geser tabel ke kiri / kanan"* di sisi kiri, dan indikator dinamis di sisi kanan (*"Geser »"*, persentase posisi scroll $0\%-100\%$, atau *"« Geser kiri"*).
     - **Track Scrollbar**: Bilah `h-1.5` berwarna abu-abu lembut `bg-slate-200/90` bersudut membulat penuh (`rounded-full`).
     - **Thumb Scrollbar**: Indikator posisi oranye amber BPS `#faa938` dengan lebar proporsional terhadap rasio layar vs lebar tabel (`clientWidth / scrollWidth`).
   - **Interaktivitas Penuh**:
     - Saat tabel digeser dengan jari (touch swipe/momentum), thumb bar meluncur secara realtime mengikuti posisi scroll.
     - Pengguna juga dapat mengetuk (*tap*) atau menggeser (*drag*) langsung pada bilah scrollbar untuk melompat ke kolom tertentu secara instan.
     - Bilah scrollbar ini langsung terlihat sejak detik pertama halaman terbuka di smartphone tanpa harus menunggu pengguna menyentuh layar.

2. **Peningkatan Kontras & Dimensi Scrollbar Universal (`resources/css/app.css`)**:
   - Memperbesar ketebalan scrollbar tabel dari `6px` menjadi `8px`.
   - Memberikan kontras tinggi: track `background: #e2e8f0` (slate-200) dengan thumb `background: #94a3b8` (slate-400), border `1.5px solid #e2e8f0`, dan efek hover oranye amber BPS `#faa938`.
   - Menambahkan `padding-bottom: 2px` agar scrollbar tabel memiliki ruang gerak yang rapi dan tidak terpotong radius sudut tabel.

3. **Universal Auto-Initializer & Sinkronisasi Realtime (`resources/js/app.js`)**:
   - Fungsi `initTableScrollbars` otomatis mendeteksi setiap tabel di dalam `.overflow-x-auto` atau `.table-responsive`.
   - Menggunakan `requestAnimationFrame` untuk sinkronisasi posisi thumb yang mulus tanpa hambatan performa (*60fps smooth*).
   - Menggunakan `ResizeObserver` untuk mendeteksi perubahan orientasi layar smartphone (portrait/landscape) atau perubahan lebar tabel secara dinamis.
   - Tetap mendukung desktop mouse drag-to-scroll dengan mengabaikan elemen klik interaktif (tombol, tautan, input).

### C. Berkas yang Diperbarui
| No | File | Keterangan |
| :---: | :--- | :--- |
| 1 | `resources/css/app.css` | Peningkatan dimensi scrollbar 8px, kontras warna slate-400 / amber, dan styling track/thumb mobile. |
| 2 | `resources/js/app.js` | Fungsi `initTableScrollbars` untuk sinkronisasi realtime, seek interaktif, touch tracking, dan desktop drag. |
| 3 | `resources/views/lembur.blade.php` | Komponen visual scrollbar mobile pada tabel lembur pegawai. |
| 4 | `resources/views/admin/lembur.blade.php` | Komponen visual scrollbar mobile pada tabel monitoring lembur admin. |
| 5 | `resources/views/ketua-tim/lembur.blade.php` | Komponen visual scrollbar mobile pada tabel lembur ketua tim. |
| 6 | `resources/views/kabag-umum/pengajuan.blade.php` | Komponen visual scrollbar mobile pada tabel persetujuan kabag umum. |
| 7 | `resources/views/ketua-tim/pengajuan.blade.php` | Komponen visual scrollbar mobile pada tabel persetujuan ketua tim. |
| 8 | `resources/views/admin/pengajuan.blade.php` | Komponen visual scrollbar mobile pada tabel persetujuan admin. |
| 9 | `resources/views/rekapitulasi.blade.php` | Komponen visual scrollbar mobile pada tabel rekapitulasi pegawai. |
| 10 | `resources/views/admin/daftar_hadir.blade.php` | Komponen visual scrollbar mobile pada tabel daftar hadir admin. |
| 11 | `resources/views/pimpinan/pengajuan.blade.php` | Komponen visual scrollbar mobile pada tabel persetujuan pimpinan. |
| 12 | `resources/views/admin/akumulasi.blade.php` | Komponen visual scrollbar mobile pada tabel akumulasi admin. |
| 13 | `resources/views/akumulasi.blade.php` | Komponen visual scrollbar mobile pada tabel akumulasi pegawai. |
| 14 | `resources/views/admin/riwayat_presensi.blade.php` | Komponen visual scrollbar mobile pada tabel riwayat presensi admin. |

---

## 27. Perbaikan Menyeluruh Dropdown Pencarian Pegawai & Tim: Tampil Penuh Otomatis, Seleksi Teks Instan, dan Tombol Clear Cepat (Single-Step Switching)

### A. Latar Belakang & Masalah Pengguna
1. **Dropdown Terfilter Sendiri Saat Dibuka Kembali (*Self-Filtering Dropdown Trap*)**:
   - Ketika pengguna memfilter pegawai tertentu (misal: memilih *Pegawai A*), nama pegawai tersebut terisi ke dalam kotak input pencarian: `"Nama Pegawai A (NIP)"`.
   - Ketika pengguna ingin berpindah atau memilih pegawai lain (misal: *Pegawai C*), mereka mengklik kembali kotak input tersebut.
   - Pada implementasi sebelumnya, pemanggilan `toggleDropdownPegawai()` mengoper nilai yang sedang ada di dalam input (`search.value`) ke fungsi `renderDropdownPegawai(search.value)`.
   - Akibatnya, fungsi pencarian melakukan filter teks terhadap string lengkap `"Nama Pegawai A (NIP)"`. Semua pegawai lainnya tereliminasi dari daftar sehingga dropdown hanya menampilkan *Pegawai A* dan opsi *"Semua pegawai"*.
   - **Keluhan Pengguna**: Pengguna terpaksa harus mengklik *"Semua pegawai"* terlebih dahulu (yang me-refresh halaman), baru kemudian bisa mengklik dan mencari *Pegawai C*. Masalah ini terjadi pada desktop maupun mobile di seluruh aplikasi.
2. **Ketiadaan Fitur Auto-Select & Tombol Reset Cepat**:
   - Saat pengguna mengklik kotak input yang sudah berisi nama pegawai, kursor hanya diletakkan di akhir teks tanpa menyeleksi seluruh teks. Jika pengguna langsung mengetik huruf baru, teks lama tidak terhapus dan pencarian menjadi rusak.
   - Tidak ada tombol silang cepat `(×)` untuk mengosongkan pilihan dalam 1 klik tanpa harus menekan tombol backspace berulang kali.

### B. Solusi & Rincian Implementasi Teknis

1. **Prinsip Universal: Selalu Tampilkan Daftar Lengkap Saat Dropdown Dibuka**:
   - Seluruh fungsi pembuka dropdown (`openDropdownPegawai()`, `openDropdownTim()`, `openDropdown()`) diubah agar **selalu mengoper parameter kosong `''`** ke fungsi perender (`renderDropdownPegawai('')`, `populateDropdownTim('', ...)`).
   - Dengan begitu, saat pengguna mengklik input atau tombol panah, dropdown **selalu menyajikan seluruh daftar pegawai/tim secara lengkap**, memungkinkan pengguna langsung beralih ke pegawai/tim mana pun dalam 1 langkah mudah (*single-step switching*).

2. **Auto-Select Teks untuk Pengetikan Instan (`search.select()`)**:
   - Setiap kali input pencarian diklik atau difokuskan (`onclick` & `onfocus`), sistem secara otomatis menjalankan `setTimeout(() => search.select(), 10)`.
   - Seluruh teks nama pegawai/tim yang sedang aktif otomatis terblok/terseleksi. Begitu pengguna mengetik satu karakter baru (misalnya huruf `'C'`), teks lama langsung tergantikan dan daftar secara instan terfilter hanya untuk nama yang mengandung huruf tersebut via event `oninput`.

3. **Indikator Visual Elegan & Sorotan Pilihan Aktif**:
   - Opsi default (*"Semua pegawai"* / *"Semua tim"*) dan nama pegawai/tim yang sedang aktif kini diberi latar belakang oranye lembut (`bg-amber-50/70`), teks tebal berkarakter (`font-semibold text-amber-700`), serta **ikon centang resmi BPS** (`<svg> checkmark`).
   - Pengguna dengan mudah mengetahui item apa yang sedang aktif sambil tetap leluasa menelusuri seluruh opsi lainnya.
   - Jika hasil pengetikan tidak cocok dengan data mana pun, sistem menampilkan baris status informatif (*"Pegawai/Tim tidak ditemukan"*).

4. **Tombol Hapus Cepat Cerdas `(×)` (`#btnClearPegawai` & `#btnClearTim`)**:
   - Menyematkan tombol silang mini di sisi kanan kotak pencarian yang otomatis muncul ketika ada item yang terpilih atau ketika pengguna sedang mengetik.
   - Mengklik tombol silang langsung mereset pencarian ke *"Semua"* dalam 1 kali klik.

5. **Tombol Panah Interaktif & Penanganan Klik Luar (*Click-Outside Handler*)**:
   - Ikon chevron panah bawah diubah menjadi tombol yang dapat diklik (`toggleDropdown...()`) untuk membuka/menutup dropdown secara fleksibel.
   - Penanganan klik di luar elemen (`document.addEventListener('click', ...)`) menutup dropdown secara rapi dan otomatis mengembalikan teks input ke nama yang sedang aktif jika pengguna membatalkan pengetikan tanpa memilih.

### C. Berkas yang Diperbarui (10 File)
| No | File | Keterangan Perubahan |
| :---: | :--- | :--- |
| 1 | `resources/views/ketua-tim/pengajuan.blade.php` | Filter Pegawai: `openDropdownPegawai('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, clickable chevron. |
| 2 | `resources/views/pimpinan/pengajuan.blade.php` | Filter Pegawai: `openDropdownPegawai('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, clickable chevron. |
| 3 | `resources/views/admin/pengajuan.blade.php` | Filter Pegawai: `openDropdownPegawai('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, clickable chevron. |
| 4 | `resources/views/admin/lembur.blade.php` | Filter Pegawai & Filter Tim: `openDropdown('')` & `openDropdownTim('')`, auto-select teks, tombol clear `(×)` kedua input, sorotan centang aktif. |
| 5 | `resources/views/lembur.blade.php` | Filter Tim Pegawai: `openDropdownTim('')`, auto-select teks, tombol `#btnClearTim`, sorotan centang aktif, click-outside handler. |
| 6 | `resources/views/admin/tim.blade.php` | Filter Tim Admin: opsi *"Semua tim"*, `openDropdownTim('')`, auto-select teks, tombol `#btnClearTim`, sorotan centang aktif, click-outside. |
| 7 | `resources/views/admin/akumulasi.blade.php` | Filter Pegawai Akumulasi: `openDropdown('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, click-outside. |
| 8 | `resources/views/admin/presensi.blade.php` | Filter Pegawai Presensi: `openDropdown('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, click-outside. |
| 9 | `resources/views/admin/spkl.blade.php` | Filter Pegawai SPKL: `openDropdown('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, click-outside. |
| 10 | `resources/views/admin/pengguna.blade.php` | Filter Pegawai Pengguna: opsi *"Semua pegawai"*, `openDropdown('')`, auto-select teks, tombol `#btnClearPegawai`, sorotan centang aktif, click-outside. |

---

## 18. Modul Manajemen User, Suksesi Kepala Bagian Umum Dinamis & Proteksi Role Superadmin

### A. Latar Belakang & Analisis Permasalahan
1. **Status Jabatan Kepala Bagian Umum (Kabag Umum)**:
   - Sebelumnya, data pejabat Kepala Bagian Umum dicatat di tabel `m_pejabat`, namun data Ketua Tim Kerja Bagian Umum di `m_tim` masih mencatat nama Bpk. Joko Suwarjo S.Si, M.Si (`197106131993121001`).
   - Pengecekan wewenang di `KabagUmumPengajuanController` dan `sidebar.blade.php` mengandalkan fallback pencocokan ke `m_tim` Bagian Umum.
   - Apabila terjadi rotasi atau pergantian pejabat Kepala Bagian Umum, sistem belum memiliki antarmuka khusus untuk melakukan suksesi secara otomatis, sehingga jika hanya diubah di satu tabel, wewenang persetujuan lembur dan kepemimpinan tim internal Bagian Umum berisiko tidak sinkron.
2. **Kebutuhan Menu Baru Manajemen User (Superadmin Only)**:
   - Sesuai arahan Mas Hanief, dibuat satu pusat kendali baru khusus Superadmin untuk:
     - Pergantian pejabat Kepala Bagian Umum secara dinamis dan tersinkronisasi penuh.
     - Penambahan dan pencabutan hak akses Admin Lembur (Admin biasa tidak boleh menambah admin).
     - Penambahan Super Administrator baru dengan peringatan konfirmasi tegas dan proteksi hak permanen (*superadmin tidak bisa mencabut superadmin lain*).

### B. Solusi & Rincian Implementasi Teknis

1. **Menu Sidebar Baru: `Manajemen User` (`Master > Manajemen User`)**:
   - Menu disembunyikan sepenuhnya dari Admin biasa dan hanya tampil jika akun login memiliki peran `superadmin`.
   - Rute dilindungi di middleware `role:superadmin` dan divalidasi ganda di level controller.

2. **Fitur 1: Pergantian Kepala Bagian Umum Dinamis (`admin.manajemen-user.ganti-kabag`)**:
   - Menampilkan kartu profil pejabat aktif Kepala Bagian Umum (Nama, NIP, NIP BPS, Satker, Tahun SK).
   - Form modal pergantian pejabat dengan pencarian instan seluruh pegawai BPS.
   - **Logika Otomatisasi Terpadu**:
     1. Menonaktifkan pejabat Kabag Umum lama di `m_pejabat` (`status = 'nonaktif'`).
     2. Mendaftarkan pejabat baru di `m_pejabat` dengan status `aktif` untuk tahun berjalan.
     3. Menyinkronkan Ketua Tim Kerja Bagian Umum di `m_tim` ke NIP dan nama Kabag baru.
     4. Menyesuaikan role pegawai baru di `m_pegawai` menjadi `ketua_tim` jika sebelumnya berstatus `user` biasa, agar modul ketua tim/kabag terbuka sempurna.
     5. Menormalkan role pejabat lama kembali ke `user` apabila sudah tidak memimpin tim kerja aktif lainnya.
   - Menyediakan tabel akordeon riwayat pejabat Kabag Umum terdahulu untuk keperluan audit.

3. **Fitur 2: Penambahan & Pencabutan Admin Lembur (`admin.manajemen-user.tambah-admin` & `hapus-admin`)**:
   - Menampilkan daftar seluruh Admin Lembur aktif saat ini.
   - Modal penambahan Admin dari daftar pegawai yang saat ini bukan admin/superadmin.
   - Tombol pencabutan wewenang Admin dengan dialog konfirmasi, yang mengembalikan peran pegawai ke `user`.
   - Proteksi keamanan: Superadmin tidak dapat mencabut hak akunnya sendiri, dan Admin biasa tidak memiliki akses ke rute/menu ini.

4. **Fitur 3: Penambahan Super Administrator dengan Proteksi Permanen (`admin.manajemen-user.tambah-superadmin`)**:
   - Menampilkan daftar seluruh Super Administrator terdaftar.
   - Modal penambahan Superadmin dilengkapi **banner peringatan konfirmasi wajib**:
     > *"beneran mau ngasih superadmin, nanti gabisa dicabut lagi"*
     > *(Superadmin memiliki wewenang tertinggi di seluruh sistem dan tidak dapat dicabut kembali oleh superadmin mana pun).*
   - Dilengkapi *checkbox* konfirmasi ganda wajib centang sebelum tombol submit aktif.
   - **Aturan Proteksi Permanen**:
     - Tidak disediakan tombol/fitur pencabutan role Superadmin (*superadmin gabisa cabut superadmin lain*).
     - Proteksi di backend: seluruh percobaan manipulasi role terhadap superadmin ditolak keras.
     - Proteksi di tabel Akun Pengguna (`resources/views/admin/pengguna.blade.php` & `PenggunaController.php`): dropdown ubah role otomatis dinonaktifkan untuk baris akun `superadmin`.

5. **Dev Login Kabag Umum Dinamis (`resources/views/login.blade.php`)**:
   - Tombol shortcut pengujian login Kabag Umum di halaman login diubah menjadi dinamis membaca pejabat `Kepala Bagian Umum` yang sedang berstatus `aktif` di database `m_pejabat`, sehingga otomatis mengikuti pejabat terkini.

### C. Berkas yang Diubah / Dibuat
| No | File | Keterangan Perubahan |
| :---: | :--- | :--- |
| 1 | `app/Http/Controllers/admin/ManajemenUserController.php` | **[BARU]** Controller khusus Superadmin untuk index, ganti kabag, tambah/cabut admin, dan promosi superadmin permanen. |
| 2 | `resources/views/admin/manajemen_user.blade.php` | **[BARU]** Blade view manajemen user: profil kabag aktif, tabel admin, tabel superadmin, modal ganti kabag, modal admin, modal superadmin dengan pesan peringatan wajib. |
| 3 | `routes/web.php` | Menambahkan import `ManajemenUserController` dan grup rute `role:superadmin` untuk seluruh aksi manajemen user. |
| 4 | `resources/views/partials/sidebar.blade.php` | Menambahkan menu navigasi `Manajemen User` di bagian *Master* khusus untuk pengguna berstatus `superadmin`. |
| 5 | `app/Http/Controllers/admin/PenggunaController.php` | Menambahkan pengaman pada method `update()` agar akun `superadmin` tidak dapat diubah/didowngrade secara tidak sengaja. |
| 6 | `resources/views/admin/pengguna.blade.php` | Memproteksi dropdown role di tabel utama agar tidak merender select box untuk akun `superadmin`. |
| 7 | `resources/views/login.blade.php` | Mengubah tombol dev-login Kabag Umum agar otomatis mengarah ke pejabat aktif terkini di `m_pejabat`. |

---

## 📌 17. Penegakan Invarian Pejabat Aktif Tunggal, Suksesi Dinamis PPK, & Parameter Penandatangan Dokumen

### A. Latar Belakang & Arahan User
1. **Aturan Pejabat Aktif Tunggal (*"kabag umum cuma bisa 1 ya adik2"*)**:
   - Di tabel `m_pejabat`, hanya boleh ada **tepat 1 pejabat aktif** untuk jabatan struktural utama (`Kepala Bagian Umum`, `PPK`, dan `Kepala BPS`).
   - Apabila pejabat baru diangkat atau diaktifkan, seluruh pejabat lama dengan jabatan yang sama harus otomatis diubah statusnya menjadi `nonaktif` (*single active invariant*).
2. **Parameterisasi Pejabat Dokumen (*"terus di dokumen2 brrti kepala bagian umumnya jg jadi params ya adek2"*)**:
   - Generator dokumen (SPKL, Laporan Lembur, dan Daftar Hadir) harus menerima parameter penandatangan dinamis (`kbu`/`kbu_id` dan `ppk`/`ppk_id`).
   - Jika parameter tidak disertakan di URL/request, sistem otomatis mengambil pejabat yang berstatus `aktif` di database `m_pejabat` sesuai periode/tahun dokumen.
   - Hal ini memungkinkan aplikasi dapat dipakai secara berkelanjutan di masa depan meskipun pejabat silih berganti, serta memungkinkan dokumen masa lampau digenerate ulang sesuai pejabat yang menjabat pada saat itu.
3. **Manajemen Pejabat Pembuat Komitmen / PPK Dinamis (*"ohiya pejabat pembuat komitemenya jg dibuat di manajemen user juga ya. select dropdown cari pegawai aja..."*)**:
   - Ditambahkan modul penetapan Pejabat Pembuat Komitmen (PPK) pada menu Manajemen User Superadmin.
   - Superadmin dapat menunjuk pegawai mana pun dari daftar pegawai BPS melalui antarmuka pencarian instan dan menentukan tahun periode SK.

### B. Rincian Implementasi Teknis

1. **Integritas Basis Data & Pengendalian di PejabatController**:
   - Pada `app/Http/Controllers/admin/PejabatController.php` (method `store` dan `update`):
     Setiap kali status di-set ke `aktif` untuk jabatan `Kepala Bagian Umum`, `PPK`, atau `Kepala BPS`, query otomatis dijalankan untuk mengubah semua pejabat lain dengan jabatan yang sama menjadi `nonaktif`.
   - Data duplikat aktif pada database telah dibersihkan sehingga hanya ada tepat 1 Kepala Bagian Umum aktif (Ir. Joko Suwarjo S.Si, M.Si) dan tepat 1 PPK aktif (Suci Budi Utami SST, M.Si).

2. **Manajemen PPK di ManajemenUserController (`admin.manajemen-user.ganti-ppk`)**:
   - Pada `app/Http/Controllers/admin/ManajemenUserController.php`:
     - Method `index()` memuat `$ppkAktif`, `$detailPpkAktif`, dan `$riwayatPpk`.
     - Method `gantiPpk(Request $request)` melakukan transaksi DB yang aman: menonaktifkan seluruh PPK sebelumnya, mendaftarkan PPK baru dengan status `aktif` dan tahun periode SK.
   - Pada `resources/views/admin/manajemen_user.blade.php`:
     - Menambahkan seksi kartu profil PPK aktif dengan badge status, NIP, NIP BPS, dan satker.
     - Menambahkan akordeon riwayat pejabat PPK terdahulu.
     - Menambahkan modal dialog `#modalGantiPpk` dengan filter pencarian real-time pegawai BPS dan input tahun periode.

3. **Parameterisasi Penandatangan pada Generator Dokumen**:
   - **`DokumenGenerateController.php`**:
     - Method `getPejabat(?Request $request = null, ?int $tahun = null)` menerima objek `$request` dan `$tahun`.
     - Parameter `kbu`, `kbu_id`, `id_kbu` diuji terlebih dahulu: jika dikirim via request, dicari di `m_pejabat` atau `m_pegawai`. Jika tidak dikirim, otomatis mengambil Kabag Umum yang berstatus `aktif`.
     - Parameter `ppk`, `ppk_id`, `id_ppk` diproses dengan logika yang sama: jika dikirim via request, mengambil pejabat terkait; jika tidak, fallback ke PPK aktif.
     - Method `spkl(Request $request)` dan `laporan(Request $request, string $jenis)` meneruskan `$request` ke `getPejabat()`.
   - **`DaftarHadirController.php`**:
     - Method `download(Request $request)` membaca parameter `kbu` atau `kbu_id`, dengan fallback ke Kabag Umum berstatus `aktif`.
   - **`DokumenViewController.php` & `resources/views/admin/dokumen.blade.php`**:
     - Controller meneruskan `$semuaKbu` dan `$semuaPpk` ke tampilan.
     - Modal Generate Dokumen (`#modalGenerate`) dan Modal Nomor SPKL (`#modalNomor`) dilengkapi pilihan dropdown Pejabat Penandatangan dengan nilai terpilih bawaan (*default*) adalah pejabat yang berstatus aktif.
     - Nilai parameter `kbu` dan `ppk` diteruskan melalui form GET dan URL redirect.
   - **`resources/views/admin/daftar_hadir.blade.php`**:
     - Tautan unduh PDF PNS dan PPPK menyertakan parameter `kbu` dinamis jika terdapat filter request.

### C. Berkas yang Diubah / Diperbarui pada Bagian Ini
| No | File | Keterangan Perubahan |
| :---: | :--- | :--- |
| 1 | `app/Http/Controllers/admin/PejabatController.php` | Menambahkan penegakan otomatis *single active invariant* pada `store()` dan `update()`. |
| 2 | `app/Http/Controllers/admin/ManajemenUserController.php` | Menambahkan pemuatan data PPK aktif/riwayat pada `index()` dan method transaksi `gantiPpk()`, serta pengamanan tipe session user NIP. |
| 3 | `resources/views/admin/manajemen_user.blade.php` | Menambahkan seksi profil PPK aktif, riwayat akordeon, modal dialog `#modalGantiPpk`, dan JavaScript helper modal PPK. |
| 4 | `routes/web.php` | Menambahkan rute `POST /admin/manajemen-user/ganti-ppk` ke grup middleware `role:superadmin`. |
| 5 | `app/Http/Controllers/admin/DokumenGenerateController.php` | Memperbarui `getPejabat()`, `spkl()`, dan `laporan()` agar membaca parameter `kbu` dan `ppk` secara dinamis dengan fallback ke pejabat aktif. |
| 6 | `app/Http/Controllers/admin/DokumenViewController.php` | Mengirim data `$semuaKbu` dan `$semuaPpk` ke view `admin.dokumen`. |
| 7 | `resources/views/admin/dokumen.blade.php` | Menambahkan selector penandatangan KBU & PPK di `#modalNomor` dan `#modalGenerate`, serta mengintegrasikan parameter pada script `nextStep()` dan `openModalNomor()`. |
| 8 | `app/Http/Controllers/admin/DaftarHadirController.php` | Membaca parameter `kbu` pada method `download()`, dengan fallback ke Kabag Umum aktif. |
| 9 | `resources/views/admin/daftar_hadir.blade.php` | Meneruskan parameter `kbu` pada tautan unduh daftar hadir PDF. |

---

## 📌 18. Perbaikan Format Laporan Hasil Kerja Lembur: 1 Baris per Orang per Tanggal (PDF & Excel)

### A. Latar Belakang & Arahan Mentor BPS (Bu Yuli)
Berdasarkan arahan langsung dari Bu Yuli Mentor BPS:
> *"di menu generate dokumen kan ada laporan yg tergenerate, nah ini kan kmrn dibuat per orang... bisa diperbaiki ga jadi setiap orang setiap tanggal muncul masing2 1 baris. jadi dibuat gini dek contohnya"* *(disertai tangkapan layar contoh Excel rekapitulasi BPS)*.

1. **Format Lama**:
   - Data laporan dikelompokkan hanya berdasarkan NIP pegawai (`groupBy('nip')`).
   - Akibatnya, satu pegawai hanya menempati 1 baris, sedangkan seluruh tanggal lembur bulan berjalan digabung dalam satu sel (misal: `"1, 6"` atau `"9, 14, 15"`), dan seluruh uraian lembur digabung menjadi satu.
2. **Format Baru Sesuai Standar BPS**:
   - **Setiap orang setiap tanggal lembur muncul masing-masing 1 baris tersendiri** (dikelompokkan per kombinasi NIP dan Tanggal: `nip_date`).
   - Jika seorang pegawai (misal: Bpk. Joko Suwarjo) lembur di tanggal 1, 2, dan 6, maka muncul 3 baris berurutan:
     - Baris 1: Joko Suwarjo, Tanggal: 1, Uraian tanggal 1
     - Baris 2: Joko Suwarjo, Tanggal: 2, Uraian tanggal 2
     - Baris 3: Joko Suwarjo, Tanggal: 6, Uraian tanggal 6
     - Baris 4: Pegawai berikutnya, dst.
   - Kolom nomor urut (`No`) berlanjut urut: 1, 2, 3, 4, 5...

### B. Rincian Penyesuaian Kolom & Tampilan

| Kolom | Format Header | Format Nilai Data | Rata Baris / Alignment |
| :--- | :--- | :--- | :--- |
| **A** | `No` | Angka urut berlanjut (`1`, `2`, `3`, ...) | Tengah (*Center*) |
| **B** | `Nama Pegawai / NIP` | **PDF**: `{Nama}<br><small>{NIP BPS / NIP}</small>`<br>**Excel**: `{Nama} / {NIP BPS / NIP}` | Kiri (*Left, Wrap Text*) |
| **C** | `Tanggal` | Angka hari lembur saja (`1`, `2`, `6`, `14`, ...) | Tengah (*Center*) |
| **D** | `Uraian Kegiatan` | Rincian kegiatan pada tanggal tersebut (jika multiple kegiatan, diformat bullet `- ` per baris) | Kiri (*Left, Wrap Text*) |

### C. File yang Diubah
1. **`app/Http/Controllers/admin/DokumenGenerateController.php`** (method `laporan`):
   - Mengubah agregasi koleksi dari `groupBy('nip')` menjadi `groupBy(fn($item) => $item->nip . '_' . $item->date)`.
   - Mengambil angka tanggal hari (`(int) date('j', strtotime($first->date))`) untuk kolom `tanggal`.
   - Menghasilkan properti `nip_display` (mengutamakan NIP lama BPS 9 digit, fallback ke NIP baru) dan `nama_nip`.
   - Menyempurnakan filter query status `approved` dengan `eligible = 1` atau `eligible IS NULL`, serta penanganan email PNS/PPPK yang toleran terhadap nilai kosong/NULL.
2. **`resources/views/dokumen/laporan.blade.php`** (PDF Template):
   - Menyesuaikan header kolom tabel: `No`, `Nama Pegawai / NIP`, `Tanggal`, dan `Uraian Kegiatan`.
   - Mengatur lebar kolom proporsional (No: 5%, Nama/NIP: 32%, Tanggal: 12%, Uraian: 51%).
   - Merender tanggal sebagai angka hari di posisi tengah (*align center*).
3. **`app/Exports/LaporanExport.php`** (Excel XLSX Template):
   - Menyesuaikan method `collection()` agar mengelompokkan data per orang per tanggal (`nip_date`).
   - Format baris data: `[$no, $first->nama . ' / ' . $nipDisplay, $tanggal, $uraianFormatted]`.
   - Menyesuaikan header baris 3 pada event `AfterSheet`: `A3: No`, `B3: Nama Pegawai / NIP`, `C3: Tanggal`, `D3: Uraian Kegiatan`.
   - Menyesuaikan perataan kolom C (Tanggal) menjadi `HORIZONTAL_CENTER` dan lebar kolom proporsional (A: 6, B: 35, C: 12, D: 65).

---

## 19. FITUR TOMBOL & ICON CETAK A4 OTOMATIS PADA MENU DAFTAR HADIR (ROLE ADMIN)

### A. Latar Belakang & Kebutuhan
- **Kebutuhan Pengguna**: Pada menu Daftar Hadir (khusus role Admin), diperlukan tombol/icon cetak (*print*) yang dapat langsung membuka dokumen daftar hadir presensi lembur dan otomatis memicu dialog cetak peramban (*print dialog*) dengan standar ukuran kertas **A4** (Portrait).
- **Tujuan**: Memudahkan petugas admin BPS mencetak fisik lembar daftar hadir harian tanpa harus mengunduh file PDF terlebih dahulu lalu membukanya di PDF reader lokal, menghemat waktu dan langkah kerja operasional harian.

### B. Fitur & Penyesuaian Antarmuka
1. **Tombol "Cetak A4" di Header Daftar Hadir**:
   - Diletakkan berdampingan dengan tombol "Unduh PDF" pada header view `resources/views/admin/daftar_hadir.blade.php`.
   - Dilengkapi icon printer SVG elegan dan dropdown pilihan kategori:
     - **Cetak Hadir (PNS)**: Memicu cetak daftar hadir khusus pegawai ASN / PNS.
     - **Cetak Hadir (PPPK)**: Memicu cetak daftar hadir khusus pegawai PPPK.
   - Menggunakan tautan dengan `target="_blank"` sehingga lembar cetak terbuka di tab baru tanpa meninggalkan tabel kerja admin.
   - Parameter filter aktif (`tanggal`, `tim`, `nip`, `kbu`) otomatis dipertahankan saat mencetak.
2. **Template Cetak Khusus `dokumen.daftar_hadir_print`**:
   - Dibuat view baru `resources/views/dokumen/daftar_hadir_print.blade.php` dengan spesifikasi cetak resmi BPS:
     - Aturan CSS `@page { size: A4 portrait; margin: 15mm 15mm 15mm 15mm; }` menjamin peramban langsung memilih orientasi Potret dan ukuran A4 secara baku (*default*).
     - Aturan cetak `-webkit-print-color-adjust: exact !important;` agar garis tabel dan latar header tetap tajam saat dicetak.
     - Bilah aksi (*floating action bar*) di bagian atas layar (dilengkapi tombol "Cetak Sekarang" dan "Tutup") dengan class `.no-print-bar` yang otomatis disembunyikan saat dicetak (`@media print`).
     - Tanda tangan digital pegawai di-render langsung menggunakan Base64 data URI (`data:image/...;base64,...`) agar gambar TTD muncul 100% tanpa kendala perizinan path storage lokal peramban.
     - Mengetahui tanda tangan Kepala Bagian Umum dinamis dari database/parameter.
     - Otomatis memanggil JavaScript `window.print()` setelah halaman selesai dimuat (`window.onload` dengan jeda aman 450ms).

### C. Berkas yang Diubah / Ditambahkan
1. **`resources/views/dokumen/daftar_hadir_print.blade.php` (BARU)**:
   - Template lembar cetak standar A4 Portrait BPS, tabel kehadiran lengkap, penandatangan dinamis, bilah kontrol layar, dan skrip auto-print.
2. **`app/Http/Controllers/admin/DaftarHadirController.php`**:
   - Menambahkan method `print(Request $request)` yang mengambil data presensi terfilter, mengonversi gambar TTD menjadi Base64 data URI, mencari pejabat KBU aktif/terpilih, dan merender view `dokumen.daftar_hadir_print`.
   - Menyempurnakan filter query kategori pegawai PNS/PPPK pada method `download()` dan `print()` agar toleran terhadap variasi email.
3. **`routes/web.php`**:
   - Mendaftarkan rute: `Route::get('/daftar-hadir/print', [\App\Http\Controllers\admin\DaftarHadirController::class, 'print'])->name('daftar_hadir.print')->middleware('checksession');`.
4. **`resources/views/admin/daftar_hadir.blade.php`**:
   - Menambahkan kontainer `#printPicker` dengan tombol icon printer "Cetak A4" dan dropdown kategori (PNS & PPPK).
   - Menambahkan JavaScript pengendali dropdown toggle dan penutupan otomatis (*click-outside listener*).

---

## 20. PERBAIKAN FATAL ERROR RESOLUSI LOGO KOP SURAT SPKL (`ErrorException: Failed to open stream`)

### A. Gejala & Penyebab Bug
- **Gejala Error**: Saat admin melakukan *Generate Dokumen* SPKL (`/admin/dokumen/generate/spkl`), aplikasi mengalami *crash* dengan pesan:
  ```
  ErrorException
  resources\views\dokumen\spkl.blade.php:94
  file_get_contents(/home/lemburwe/public_html/images/LOGO BPS PROVINSI JATENG.png): Failed to open stream: No such file or directory
  ```
- **Akar Masalah**: Terdapat path direktori absolut server cPanel produksi (`/home/lemburwe/public_html/...`) yang di-hardcode langsung di dalam template Blade [resources/views/dokumen/spkl.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/dokumen/spkl.blade.php). Akibatnya, saat dijalankan di lingkungan lokal pengembang (Windows/Localhost) atau direktori cPanel yang berbeda, fungsi `file_get_contents()` gagal membaca berkas dan memicu fatal 500 Error.

### B. Solusi & Perbaikan Komprehensif
1. **Resolusi Path Multi-Environment Dinamis**:
   - Mengganti path statis dengan kandidat path berantai yang mencakup lingkungan lokal (`public_path('images/LOGO BPS PROVINSI JATENG.png')`), `base_path('public/images/...')`, `base_path('../public_html/images/...')`, serta fallback cPanel.
   - Menambahkan validasi `file_exists($cand)` sebelum fungsi pembaca berkas dijalankan, sehingga tidak akan pernah melempar fatal exception jika berkas tidak ditemukan.
2. **Proteksi Ekstensi GD (`extension_loaded('gd')`)**:
   - DomPDF membutuhkan ekstensi PHP GD untuk merender gambar format PNG/JPEG. Ditambahkan pengecekan `extension_loaded('gd')` sehingga dokumen tetap berhasil dibuat tanpa error jika ekstensi GD belum aktif di server/PHP CLI.
   - Menyediakan fallback kop teks resmi (*Badan Pusat Statistik Provinsi Jawa Tengah*) yang rapi jika gambar logo tidak tersedia.
3. **Penyempurnaan Proteksi Berkas TTD Daftar Hadir**:
   - Pada [resources/views/dokumen/daftar_hadir.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/dokumen/daftar_hadir.blade.php), ditambahkan verifikasi `file_exists(storage_path('app/public/' . $d->signature_path))` sebelum memuat tag `<img>` tanda tangan untuk mencegah potensi error serupa.

---

## 21. PERBAIKAN TYPO NIP KEPALA BAGIAN UMUM (15 DIGIT VS 18 DIGIT) & TOLERANSI DEV LOGIN

### A. Gejala & Penyebab Bug
- **Gejala Error**: Saat mencoba login cepat (Quick Dev Login) sebagai Kepala Bagian Umum di lingkungan lokal atau mengakses `/dev-login/197106131993121`, muncul pesan error 404:
  `Pegawai dengan NIP/ID 197106131993121 tidak ditemukan di database m_pegawai.`
- **Akar Masalah**:
  1. Pada tabel `m_pejabat` dan seeder lamanya (`MPejabatTableSeeder`), NIP Bpk. Joko Suwarjo S.Si, M.Si tercatat hanya **15 digit** (`197106131993121` — kurang digit `001` di belakangnya).
  2. Sedangkan pada master data pegawai resmi `m_pegawai`, NIP beliau tercatat dengan standar **18 digit lengkap** (`197106131993121001`).
  3. Ketika tombol login Kabag Umum di [resources/views/login.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/login.blade.php) mengambil NIP dari `m_pejabat`, tautan menghasilkan `/dev-login/197106131993121` yang gagal dicocokkan ke `m_pegawai`.

### B. Solusi yang Diterapkan
1. **Pembaruan Data NIP Pejabat**:
   - Memperbarui NIP Bpk. Joko Suwarjo di tabel database `m_pejabat` menjadi 18 digit lengkap: `197106131993121001`.
   - Memperbaiki data seeder di [database/seeders/MPejabatTableSeeder.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/database/seeders/MPejabatTableSeeder.php) agar tidak kembali ke 15 digit jika dilakukan seeder ulang.
2. **Sinkronisasi Otomatis Tautan Login Kabag Umum**:
   - Pada [resources/views/login.blade.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/resources/views/login.blade.php), tombol Kabag Umum kini secara cerdas melakukan *cross-reference* data `m_pejabat` ke `m_pegawai` (berdasarkan NIP, NIP lama, maupun nama) untuk menjamin parameter NIP yang dikirimkan selalu valid.
3. **Peningkatan Fleksibilitas Rute Dev Login (`routes/web.php`)**:
   - Rute `/dev-login/{nip}` ditambahkan logika toleransi:
     - Jika NIP persis tidak ditemukan, sistem mencoba pencarian awalan (*prefix matching*, misal input 15 digit akan otomatis menemukan pegawai dengan 18 digit NIP tersebut).
     - Menambahkan pencarian silang melalui tabel `m_pejabat` jika parameter yang dimasukkan berupa ID pejabat atau NIP pejabat.

---

## 22. PENGURUTAN KRONOLOGIS LAPORAN HASIL KERJA LEMBUR BERDASARKAN TANGGAL (1 S.D. AKHIR BULAN)

### A. Kebutuhan Pengguna
- Pada Laporan Hasil Kerja Lembur (baik format PDF maupun Excel XLSX), urutan baris data harus tersusun secara **kronologis berdasarkan tanggal** dari tanggal 1 sampai dengan tanggal terakhir pada bulan berjalan (`t.date ASC`, lalu `p.nama ASC`).
- Nomor urut (`No`) berjalan sekuensial (1, 2, 3, ...) mengikuti urutan tanggal lembur tersebut, sehingga pembaca dokumen dapat menelusuri kegiatan lembur harian kantor BPS secara runut dari awal hingga akhir bulan.

### B. Penyesuaian Kode
1. **`app/Http/Controllers/admin/DokumenGenerateController.php`** (method `laporan`):
   - Kueri pengurutan diubah dari `->orderBy('p.nama')->orderBy('t.date')` menjadi:
     ```php
     ->orderBy('t.date', 'asc')
     ->orderBy('p.nama', 'asc');
     ```
   - Pengelompokan baris menggunakan kombinasi tanggal dan NIP:
     ```php
     ->groupBy(function ($item) {
         return $item->date . '_' . $item->nip;
     })
     ```
2. **`app/Exports/LaporanExport.php`** (Ekspor Excel Laporan XLSX):
   - Mengubah kueri pengurutan koleksi agar berurutan berdasarkan tanggal terlebih dahulu:
     ```php
     ->orderBy('t.date', 'asc')
     ->orderBy('p.nama', 'asc');
     ```
   - Mengelompokkan data berdasarkan `$item->date . '_' . $item->nip`, sehingga baris Excel dan nomor urut di kolom A tersusun urut kronologis dari tanggal 1 hingga akhir bulan.
3. **`app/Http/Controllers/admin/LaporanController.php`** (Tabel Web Laporan Admin `/admin/laporan`):
   - Kueri tabel diselaraskan menjadi `->orderBy('t.date', 'asc')->orderBy('p.nama', 'asc')` agar tampilan tabel di layar peramban sejalan dengan dokumen unduhan.

> [!NOTE]
> **Cakupan Universal (Berlaku untuk Semua Laporan)**:  
> Perubahan ini **berlaku dinamis dan otomatis untuk seluruh periode/bulan** (Januari s.d. Desember pada tahun berapa pun), baik untuk kategori **PNS** maupun **PPPK**, serta format **PDF** dan **Excel (XLSX)**, tanpa ada pembatasan khusus pada bulan September.

---

## 23. PERBAIKAN FORMAT LAPORAN LEMBUR (NIP BARU & ANTI-CUT OFF PDF), PENYEDERHANAAN TOMBOL CETAK DAFTAR HADIR, JAM PULANG PRESENSI RIIL, DAN FILTER/UNDUH KATEGORI REKAPITULASI (PNS / PPPK / SEMUA)

### A. Latar Belakang & Kebutuhan Pengguna
Berdasarkan arahan dan diskusi tindak lanjut:
1. **Laporan Lembur:**
   - Menggunakan **NIP Baru (18 digit)** untuk setiap pegawai (dengan fallback NIP lama jika belum terisi).
   - Mempertahankan format per orang per tanggal kronologis ascending.
   - Memperbaiki tata letak PDF agar rapi dan tidak terpotong (*page-break clipping*) pada baris kegiatan lembur yang panjang atau ketika baris/tanda tangan berada di dekat batas bawah halaman.
2. **Daftar Hadir - Tombol Cetak:**
   - Menghilangkan kata "A4" pada tombol cetak, sehingga menjadi **icon + "Cetak"** saja.
3. **Daftar Hadir - Jam Pulang Presensi Riil:**
   - Jam kepulangan diambil langsung dari presensi riil pegawai (`t_presensi.jam_selesai`).
   - Jika pegawai belum melakukan presensi pulang atau data tidak ditemukan, ditampilkan tanda strip `-` (Opsi B).
4. **Rekapitulasi Lembur - Filter & Unduh Kategori:**
   - Menambahkan dropdown filter kategori pegawai (**Semua Pegawai**, **PNS**, **PPPK**) di toolbar.
   - Mengubah tombol unduh Excel menjadi dropdown dengan 3 opsi: **Unduh Semua Pegawai**, **Unduh Rekap PNS**, dan **Unduh Rekap PPPK**.
5. **Cek Formula Rekapitulasi:** Ditunda untuk pembahasan lebih lanjut sesuai permintaan pengguna.

---

### B. Rincian Penyesuaian Berkas

#### 1. Laporan Hasil Kerja Lembur (NIP Baru & Anti-Cut Off)
- **`app/Http/Controllers/admin/DokumenGenerateController.php`** (method `laporan`):
  - Mengambil data dengan prioritas NIP Baru 18 digit:
    ```php
    $nipDisplay = $first->nip ?: $first->nip_lama;
    ```
- **`app/Exports/LaporanExport.php`** (Ekspor Excel):
  - Menggunakan `$nipDisplay = $first->nip ?: $first->nip_lama;` untuk tampilan NIP di berkas Excel.
- **`resources/views/dokumen/laporan.blade.php`** (Cetak PDF Laporan):
  - Mengatur ukuran halaman dan margin cetak portrait standar:
    ```css
    @page { size: A4 portrait; margin: 15mm 15mm 20mm 15mm; }
    ```
  - Mengatur aliran teks alami baris tabel dan proteksi tanda tangan:
    ```css
    table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
    tr { page-break-inside: auto; } /* Baris uraian tetap dimulai di bawah hal 1, dan lanjutan kalimatnya menyambung di atas hal 2 */
    td { vertical-align: top; word-wrap: break-word; }
    .ttd-wrapper { page-break-inside: avoid; margin-top: 18px; }
    ```
  - Menampilkan NIP Baru 18 digit tepat di bawah nama pegawai pada tabel laporan.

#### 2. Tombol Cetak Daftar Hadir (Penyederhanaan Teks)
- **`resources/views/admin/daftar_hadir.blade.php`**:
  - Mengubah teks tombol cetak dari `Cetak A4` menjadi `Cetak` dengan icon print.
  - Memperbarui label dropdown cetak menjadi `Pilih Kategori`.

#### 3. Jam Pulang Presensi Riil pada Daftar Hadir
- **`app/Http/Controllers/admin/DaftarHadirController.php`**:
  - Pada method `index()`, `download()`, dan `print()`, menambahkan subquery SQL terpadu untuk mengambil jam kepulangan riil dari `t_presensi`:
    ```php
    DB::raw("(SELECT DATE_FORMAT(pr.jam_selesai, '%H:%i') 
              FROM t_presensi pr 
              WHERE pr.niplama = p.nip_lama 
                AND DATE(pr.tanggal) = DATE(t.date) 
              ORDER BY pr.id_presensi DESC 
              LIMIT 1) as jam_pulang")
    ```
- **`resources/views/admin/daftar_hadir.blade.php`**:
  - Mengubah kolom jam selesai menjadi `{{ $d->jam_pulang ?: '-' }}`.
- **`resources/views/dokumen/daftar_hadir.blade.php`** (PDF Landscape):
  - Menampilkan `{{ $d->jam_pulang ?: '-' }}` pada kolom jam pulang.
- **`resources/views/dokumen/daftar_hadir_print.blade.php`** (Print Browser):
  - Menampilkan `{{ $d->jam_pulang ?: '-' }}` pada kolom jam pulang.

#### 4. Filter Kategori dan Dropdown Unduh pada Rekapitulasi Lembur
- **`app/Http/Controllers/admin/RekapitulasiController.php`**:
  - Pada method `index()`:
    - Menerima parameter `jenis` (`pns` atau `pppk`).
    - Menyaring data transaksi berdasarkan domain email pegawai (PNS: email bukan pppk/kosong; PPPK: email like `%-pppk@bps.go.id`).
    - Memastikan upsert ke tabel cache `t_rekapitulasi` hanya dilakukan jika tidak ada filter NIP ataupun filter jenis (`if (!$nip && !$jenis)`).
    - Meneruskan variabel `$jenis` ke view `admin.spkl`.
  - Pada method `downloadExcel()`:
    - Menerima parameter `jenis` dan meneruskannya ke kelas `RekapitulasiExport`.
    - Menghasilkan nama file dinamis: `Rekapitulasi_Lembur_{suffix}_{bulan}.xlsx` (misal: `Rekapitulasi_Lembur_PNS_2026-09.xlsx` atau `Rekapitulasi_Lembur_PPPK_2026-09.xlsx`).
- **`app/Exports/RekapitulasiExport.php`**:
  - Mendukung filter `nip_lama` dan `jenis` (PNS / PPPK / Semua).
  - Memberikan judul worksheet (*sheet title*) dinamis: `Rekapitulasi`, `Rekapitulasi PNS`, atau `Rekapitulasi PPPK`.
- **`resources/views/admin/spkl.blade.php`**:
  - Menambahkan dropdown filter kategori pegawai (`#filterJenis`) di samping filter pegawai.
  - Memperbarui fungsi `updateURL()` agar tetap mempertahankan pilihan kategori, bulan, dan pegawai.
  - Mengubah tombol unduh single menjadi dropdown button dengan opsi:
    1. **Unduh Semua Pegawai**
    2. **Unduh Rekap PNS**
    3. **Unduh Rekap PPPK**
  - Menambahkan interaksi penutupan otomatis dropdown unduh saat area luar diklik.

---

## 24. PENANGANAN ERROR 419 PAGE EXPIRED SAAT LOGOUT & RESTORASI SESI RAMAH PENGGUNA

### A. Latar Belakang Masalah
Pada server produksi/deploy (`https://lembur-dev.jateng.pro/logout`), ketika pengguna menekan tombol **Logout** setelah beberapa waktu tidak aktif (*session idle/timeout*), peramban menampilkan halaman kesalahan gelap:
```
419 | PAGE EXPIRED
```
Dan pengguna tidak terarah kembali ke halaman login.

### B. Akar Masalah Teknis
1. **CSRF Token Mismatch pada Logout:**
   - Tombol logout di navbar mengirimkan formulir `POST /logout` dengan `@csrf`.
   - Ketika sesi kedaluwarsa atau token CSRF di peramban sudah basi (*expired*), middleware Laravel `VerifyCsrfToken` menggagalkan verifikasi request dengan `TokenMismatchException` (HTTP 419).
   - Pengguna yang berniat keluar dari sesi yang memang sudah kedaluwarsa malah terhalang oleh proteksi CSRF.
2. **Ketiadaan Toleransi Method GET:**
   - Jika pengguna me-refresh halaman `/logout` setelah terkena 419, peramban mengirim `GET /logout`, memicu error `405 Method Not Allowed` jika rute hanya menerima `POST`.

### C. Solusi & Penyesuaian Berkas
1. **`bootstrap/app.php` (Laravel 11 CSRF & Exception Config)**:
   - **Pengecualian CSRF untuk Logout**: Menambahkan `logout` ke daftar pengecualian CSRF:
     ```php
     $middleware->validateCsrfTokens(except: [
         'logout',
     ]);
     ```
     Dengan pengecualian ini, proses logout selalu berhasil membersihkan sesi tanpa terhadang token basi.
   - **Fallback Elegan untuk TokenMismatchException & HTTP 419**:
     Menangani kesalahan CSRF di seluruh form aplikasi agar tidak menampilkan layar hitam 419, melainkan otomatis mengarahkan ke halaman login dengan pesan ramah:
     ```php
     $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
         return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
     });
     $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
         if ($e->getStatusCode() === 419) {
             return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
         }
     });
     ```
2. **`routes/web.php`**:
   - Mendukung method ganda `GET` dan `POST` pada `/logout`:
     ```php
     Route::match(['get', 'post'], '/logout', function () {
         session()->flush();
         return redirect()->route('login');
     })->name('logout');
     ```
3. **`resources/views/login.blade.php`**:
   - Menambahkan kotak alert informasi berwarna kuning-amber jika terdapat `session('error')`, sehingga pengguna mengetahui bahwa mereka dialihkan karena sesi telah habis.

---

## 25. PENYELARASAN DATABASE REAL (lembur_real) DAN MODEL KODE ELOQUENT

### A. Latar Belakang & Analisis Masalah
Saat mengimpor basis data riil dari kantor (`lembur_real`), ditemukan beberapa inkonsistensi bawaan dari berkas dump:
1. Berkas dump kantor hanya menyertakan data master (`m_*`) dan sebagian tabel transaksi (`t_anggota_tim`, `t_akumulasi`). Tabel operasional seperti `t_transaksi`, `t_presensi`, `t_rekapitulasi`, `migrations`, `users`, dan `sessions` belum terbuat.
2. Seluruh tabel master bawaan dump (`m_pegawai`, `m_pejabat`, `t_dokumen`, `m_tim`, `m_rates`, `m_hari_libur`) **kehilangan definisi Primary Key dan Auto Increment**, yang memicu error `Field 'id_dokumen' doesn't have a default value` saat aksi insert dokumen baru.
3. Model Eloquent (`Transaksi.php`, `Pegawai.php`, `Dokumen.php`) belum memuat atribut `$fillable` lengkap sesuai skema database riil (seperti `eligible`, `nip_lama`, `nip`, `satker`, `tahun`).

### B. Tindakan Penyelarasan yang Telah Dilakukan
1. **Restorasi Primary Key & Auto Increment**:
   - `m_pegawai`: Menambahkan Primary Key & AUTO_INCREMENT pada `id_pegawai`.
   - `m_pejabat`: Menambahkan Primary Key & AUTO_INCREMENT pada `id_pejabat`.
   - `t_dokumen`: Menambahkan Primary Key & AUTO_INCREMENT pada `id_dokumen`.
   - `m_tim`: Menetapkan Primary Key pada `kode_tim`.
   - `m_rates`: Menambahkan Primary Key & AUTO_INCREMENT pada `id_rate`.
   - `m_hari_libur`: Menambahkan Primary Key & AUTO_INCREMENT pada `id`.
2. **Penyelarasan Tabel Operasional & Kolom Fitur Baru**:
   - Membuat struktur tabel `t_transaksi`, `t_presensi`, `t_rekapitulasi`, `migrations`, `users`, `sessions`, `cache` di `lembur_real`.
   - Memastikan kolom `note_kabag`, `approved_kabag_at`, `user_edited`, `tanggal_edited`, pelebaran `status` (VARCHAR 30), dan `uraian` (TEXT) aktif di `t_transaksi`.
3. **Penyelarasan Model Eloquent**:
   - [app/Models/Transaksi.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/app/Models/Transaksi.php): Menambahkan `'eligible'` ke dalam `$fillable`.
   - [app/Models/Pegawai.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/app/Models/Pegawai.php): Menambahkan `'nip_lama'`, `'nip'`, `'foto_url'`, `'satker'`, `'kd_satker'` ke dalam `$fillable`.
   - [app/Models/Dokumen.php](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/app/Models/Dokumen.php): Menambahkan `'tahun'` ke dalam `$fillable`.
4. **Verifikasi Integritas Keseluruhan**:
   - Seluruh modul (Login, Monitoring Admin, Persetujuan Ketua Tim, Rekapitulasi Ekspor PNS/PPPK/Semua, Daftar Hadir Cetak/PDF, dan Generator Dokumen Laporan) telah diuji via automated runner dan dinyatakan **100% Lulus Uji & Tersinkronisasi Penuh**.

---

## 26. PENYESUAIAN ROLE PAK JOKO SUWARJO MENJADI KETUA TIM & KABAG UMUM (NON-ADMIN)

### A. Latar Belakang & Analisis Alur Kerja Baru
1. Pada data awal migrasi basis data kantor (`lembur_real`), akun Bpk. **Joko Suwarjo S.Si, M.Si** (`197106131993121001`) tercatat dengan `role = 'admin'` di tabel `m_pegawai`.
2. Sesuai arsitektur alur kerja bertingkat baru (*Flow Baru 2.0*):
   - Wewenang **Admin Lembur** operasional didelegasikan kepada staf pengelola lembur (seperti Mbak Rizki Dianing Wardhani SST).
   - Bpk. Joko Suwarjo berperan sebagai **Kepala Bagian Umum** (pejabat struktural aktif di `m_pejabat`) sekaligus **Ketua Tim Kerja Bagian Umum** (di tabel `m_tim` kode `g2YxkEolkZ7qwrm6`).
   - Dengan role `ketua_tim`, beliau memiliki hak:
     - Mengakses Dashboard Ketua Tim (`/ketua-tim/dashboard`).
     - Melakukan persetujuan tingkat pertama untuk tim internal Bagian Umum.
     - Melakukan persetujuan final (Kedua) seluruh pengajuan lembur dari semua tim melalui modul **Persetujuan Kabag** (`/kabag-umum/pengajuan`).
   - Akun Pak Joko tidak lagi mengelola tugas teknis admin seperti manajemen user, upload presensi harian, atau edit tarif lembur.

### B. Perubahan Database & Kueri SQL
Kueri penyesuaian yang telah dieksekusi di database lokal dan disiapkan untuk server produksi:
```sql
UPDATE m_pegawai 
SET role = 'ketua_tim' 
WHERE nip = '197106131993121001' OR nip_lama = '340013741';
```

### C. Hasil & Verifikasi Pengujian
1. **Pemeriksaan Database (`m_pegawai`)**:
   - `nama`: Joko Suwarjo S.Si, M.Si
   - `nip`: 197106131993121001
   - `role`: **`ketua_tim`** (berhasil diperbarui dari `admin`).
2. **Pemeriksaan Daftar Admin Lembur**:
   - Daftar akun ber-role `admin` kini secara resmi terdiri atas **2 orang**:
     1. **Rizki Dianing Wardhani SST** (`199509102018022001`) &rarr; Admin Lembur Operasional harian (upload presensi, sinkron tim, rekapitulasi, cetak dokumen).
     2. **Suci Budi Utami SST, M.Si.** (`197811262000122001`) &rarr; Pejabat Pembuat Komitmen (PPK) yang memiliki akses admin untuk verifikasi rekapitulasi anggaran lembur sebelum penandatanganan SPKL.
   - Bpk. Joko Suwarjo (Kepala Bagian Umum) telah diposisikan secara tepat dengan role `ketua_tim` yang berfokus pada persetujuan lembur tingkat akhir (*Persetujuan Kabag*).
3. **Pemeriksaan Akses Aplikasi & Sesi**:
   - Login via bypass dev (`/dev-login/kabag`) berhasil masuk dengan role sesi `ketua_tim`.
   - Halaman **Persetujuan Kabag** (`/kabag-umum/pengajuan`) merespons HTTP **200 OK**.
   - Halaman **Dashboard Ketua Tim** (`/ketua-tim/dashboard`) merespons HTTP **200 OK**.
   - Pintasan login dev admin (`/dev-login/admin`) secara otomatis mengarahkan ke Mbak Rizki Dianing Wardhani SST dengan HTTP **302 Redirect** ke Dashboard Admin.
   - Pada halaman **Manajemen User Superadmin** (`/admin/manajemen-user`), profil Bpk. Joko Suwarjo tampil secara eksklusif dan terhormat pada Seksi Kepala Bagian Umum Aktif, dan tidak lagi bercampur di tabel Admin Lembur operasional.

---

## 27. PENYESUAIAN METRIK DASHBOARD SEMUA ROLE KE TAHUN BERJALAN & INTEGRASI ANTREAN AKTIF DIPROSES (MENUNGGU_KABAG)

### A. Latar Belakang & Masalah Bisnis
1. **Pergantian Bulan Kalender vs Waktu Verifikasi (Bulan $N+1$)**:
   - Sesuai proses bisnis riil di BPS, kegiatan lembur dilaksanakan pada akhir bulan (misal September), namun verifikasi dan persetujuan (*approval*) oleh Ketua Tim atau Kepala Bagian Umum sering kali baru dilakukan pada awal bulan berikutnya (Oktober).
   - Sebelumnya, dashboard seluruh role (Admin, Ketua Tim, Pegawai, Pimpinan) memfilter metrik statistik secara kaku menggunakan `whereMonth('date', Carbon::now()->month)` (bulan 10 / Oktober).
   - Akibatnya, begitu kalender berganti ke tanggal 1–2 Oktober, seluruh kartu dashboard mendadak bernilai **0**. Ketua Tim dan Admin mengira tidak ada pengajuan lembur yang masuk, dan saat kartu "Diproses" diklik, modal popup `getPending()` ikut kosong karena terhalang filter bulan Oktober.
2. **Celah Logika Status "Diproses"**:
   - Di Dashboard Pegawai dan Pimpinan, kartu "Diproses" sebelumnya hanya menghitung status `pending`. Pengajuan yang sedang berstatus `menunggu_kabag` (tahap 2) tidak terhitung di kartu Diproses.

### B. Solusi & Penyesuaian yang Diterapkan
1. **Cakupan Tahun Berjalan (Tahun 2026)**:
   - Mengubah kueri statistik pada `admin/DashboardController`, `ketuatim/DashboardController`, `pegawai/DashboardController`, dan `pimpinan/DashboardController` dari filter bulanan menjadi filter tahun berjalan: `whereYear('date', $tahunIni)`.
   - Memperbarui subtitle pada kartu metrik dari teks kaku *"Bulan ini"* menjadi **"Tahun 2026"** (atau `Tahun {{ date('Y') }}`).
2. **Kartu "Diproses" Sebagai Antrean Tugas Aktif**:
   - Menghitung seluruh pengajuan aktif tahun berjalan yang berstatus `pending` maupun `menunggu_kabag` (`whereIn('status', ['pending', 'menunggu_kabag'])`).
   - Menghapus pembatasan `whereMonth()` pada method AJAX `getPending()` (Admin dan Ketua Tim), sehingga pop-up persetujuan cepat langsung menampilkan seluruh daftar pengajuan yang memang butuh tindakan ACC dari bulan lalu tanpa terlewat.
3. **Toleransi NIP Baru & NIP Lama pada Sesi**:
   - Menggunakan filter pencarian fleksibel (`nip` dan `nip_lama`) pada relasi `approver_employee_id` dan `submitted_by_NIP` untuk menjamin integritas data antar-peran.

### C. Berkas yang Diubah
- `app/Http/Controllers/admin/DashboardController.php`: Metrik tahun berjalan & `getPending()` tanpa kunci bulan.
- `app/Http/Controllers/ketuatim/DashboardController.php`: Metrik tim tahun berjalan & `getPending()` tanpa kunci bulan.
- `app/Http/Controllers/pegawai/DashboardController.php`: Metrik pegawai tahun berjalan & inklusi `menunggu_kabag`.
- `app/Http/Controllers/pimpinan/DashboardController.php`: Metrik pimpinan tahun berjalan & inklusi `menunggu_kabag`.
- `resources/views/admin/dashboard.blade.php`: Label subtitle kartu metrik Tahun berjalan.
- `resources/views/ketua-tim/dashboard.blade.php`: Label subtitle kartu metrik Tahun berjalan.
- `resources/views/dashboard.blade.php`: Label subtitle kartu metrik Tahun berjalan.
- `resources/views/pimpinan/dashboard.blade.php`: Label subtitle kartu metrik Tahun berjalan.













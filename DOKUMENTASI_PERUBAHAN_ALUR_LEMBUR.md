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



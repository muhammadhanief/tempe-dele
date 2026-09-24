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


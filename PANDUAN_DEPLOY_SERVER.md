# 🚀 Panduan & Checklist Deploy ke Server Produksi

Dokumen ini berisi panduan teknis langkah demi langkah untuk menerapkan perubahan sistem lembur bertingkat (Kabag Umum), penguatan keamanan otorisasi (*Role-Based Access Control*), dan pembaruan sistem ke server produksi / live.

---

## ⚠️ 1. Update Database Produksi (Paling Krusial!)

Di database server produksi, tabel `t_transaksi` perlu diperbarui untuk mendukung kolom catatan Kabag, tanggal persetujuan Kabag, dan pelebaran kolom status.

### A. Cara Rekomendasi (Lewat phpMyAdmin / Database Client cPanel)
Buka phpMyAdmin di cPanel server produksi Anda, pilih database lembur, buka tab **SQL**, lalu jalankan kueri berikut:

```sql
-- 1. Pembaruan Alur Persetujuan Kabag Umum
ALTER TABLE t_transaksi ADD COLUMN note_kabag TEXT NULL AFTER note;
ALTER TABLE t_transaksi ADD COLUMN approved_kabag_at DATETIME NULL AFTER approved_at;
ALTER TABLE t_transaksi MODIFY COLUMN status VARCHAR(30) NULL;

-- 2. Pembaruan Audit Edit Uraian Kegiatan (Admin, Ketua Tim, Kabag Umum)
ALTER TABLE t_transaksi ADD COLUMN user_edited VARCHAR(100) NULL AFTER note_kabag;
ALTER TABLE t_transaksi ADD COLUMN tanggal_edited DATETIME NULL AFTER user_edited;

-- 3. Perpanjangan Kapasitas Uraian Kegiatan (Menjadi TEXT / Long Text)
ALTER TABLE t_transaksi MODIFY COLUMN uraian TEXT NULL;

-- 4. (Opsional Pembersihan) Reset Jam Disetujui Pada Status Pending Agar Mengikuti Jam Pengajuan
UPDATE t_transaksi SET jam_mulai_disetujui = NULL, jam_selesai_disetujui = NULL WHERE status = 'pending';
```

### B. Alternatif via Terminal SSH (Artisan Migration Spesifik)
Jika Anda memiliki akses terminal SSH di server dan ingin menjalankan migrasi via Laravel Migration:

```bash
# Migrasi Alur Kabag Umum & Pelebaran Status
php artisan migrate --path=database/migrations/2026_09_15_000001_add_kabag_approval_to_t_transaksi.php
php artisan migrate --path=database/migrations/2026_09_15_000002_widen_status_column_in_t_transaksi.php

# Migrasi Kolom Audit Edit (user_edited & tanggal_edited)
php artisan migrate --path=database/migrations/2026_09_25_000001_add_user_edited_to_t_transaksi.php

# Migrasi Perpanjangan Kapasitas Uraian Kegiatan (Menjadi TEXT)
php artisan migrate --path=database/migrations/2026_09_25_000002_widen_uraian_column_in_t_transaksi.php
```

> [!WARNING]
> **Hindari menjalankan `php artisan migrate` polosan tanpa `--path` di server!**  
> Karena ada beberapa migrasi lama bawaan repositori yang kolomnya sudah ada di database, menjalankan `php artisan migrate` secara umum berisiko memunculkan error *Duplicate column*.

---

## 📁 2. Pembaruan Berkas Kode ke Server

Pastikan seluruh berkas kode terbaru telah disinkronkan ke server produksi:

### Skenario A: Menggunakan Git (`git pull`)
Jika server produksi terhubung dengan repositori Git, cukup jalankan perintah di root project:
```bash
git pull origin main
```

### Skenario B: Unggah Manual (File Manager cPanel / FTP)
Jika melakukan unggah manual tanpa Git, **pastikan berkas-berkas baru dan penting berikut ikut terunggah**:
1. 📄 **`database/migrations/2026_09_25_000001_add_user_edited_to_t_transaksi.php`** *(File migrasi audit edit)*.
2. 📄 **`database/migrations/2026_09_25_000002_widen_uraian_column_in_t_transaksi.php`** *(File migrasi kapasitas uraian TEXT)*.
3. 📄 **`app/Models/Transaksi.php`** *(Update $fillable: user_edited, tanggal_edited)*.
4. 📄 **`app/Traits/KoreksiLembur.php`** *(Pencegahan kalkulasi lembur presensi melebihi jam pengajuan)*.
5. 📄 **`app/Http/Middleware/CheckRole.php`** *(Wajib ada, jika tertinggal aplikasi akan error Class Not Found)*.
6. 📄 **`bootstrap/app.php`** *(Memuat pendaftaran alias middleware `role`)*.
7. 📄 **`routes/web.php`** *(Memuat proteksi rute RBAC dan penguncian dev-login)*.
8. 📄 **`app/Http/Controllers/LemburController.php`** *(Form pegawai + validasi max:2000 + batas pengajuan presensi)*.
9. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Controller persetujuan Kabag + edit uraian + validasi presensi)*.
10. 📄 **`app/Http/Controllers/ketuatim/PengajuanController.php`** *(Controller persetujuan Ketua Tim + edit uraian + validasi presensi)*.
11. 📄 **`app/Http/Controllers/admin/PengajuanController.php`** *(Controller Admin + validasi presensi + reset jam rejected + approved_kabag_at + edit uraian)*.
12. 📄 **`app/Http/Controllers/admin/DashboardController.php`** *(Controller dashboard Admin + metrik diproses multi-status + quick approve presensi cap)*.
13. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Controller Admin lembur)*.
14. 📄 **`resources/views/lembur.blade.php`** *(View lembur pegawai + live counter)*.
15. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(View persetujuan Kabag + default jam pengajuan)*.
16. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(View persetujuan Ketua Tim + default jam pengajuan)*.
17. 📄 **`resources/views/ketua-tim/dashboard.blade.php`** *(View dashboard Ketua Tim + badge status menunggu_kabag)*.
18. 📄 **`app/Http/Controllers/ketuatim/DashboardController.php`** *(Controller dashboard Ketua Tim + sinkronisasi status disetujui & presensi)*.
19. 📄 **`resources/views/admin/dashboard.blade.php`** *(View dashboard Admin + badge menunggu_kabag + tag modal pending)*.
20. 📄 **`resources/views/admin/pengajuan.blade.php`** *(View persetujuan Admin + tombol aksi semua status + update realtime)*.
21. 📄 **`resources/views/partials/sidebar.blade.php`** *(Navigasi menu Kabag)*.
22. 📄 Seluruh file controller, model, dan views lainnya yang telah dimodifikasi.

---

## 🛡️ 3. Konfigurasi Lingkungan Server (`.env`)

Pastikan file `.env` di server produksi:
1. **Tidak tertimpa** oleh konfigurasi lokal komputer (file `.env` harus tetap mempertahankan kredensial database server kantor).
2. Menggunakan konfigurasi standar produksi yang aman:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
3. Menggunakan `APP_URL` sesuai domain resmi kantor:
   ```env
   APP_URL=https://lembur.web.bps.go.id
   ```

> [!NOTE]
> **Mekanisme Keamanan Otomatis:**  
> Karena `APP_ENV=production`, rute bypass `/dev-login/{nip}` dan `/debug-session` secara otomatis **hilang dan non-aktif (menghasilkan 404 Not Found)** di server live. Panel tombol pengujian di halaman login juga otomatis disembunyikan.

---

## 📦 4. Asset Frontend (`public/build`)

Aplikasi menggunakan Vite untuk mem-bundle aset CSS dan JavaScript (TailwindCSS). Karena folder `public/build` diabaikan oleh `.gitignore`, perhatikan metode berikut:

* **Skenario A: Server memiliki Terminal SSH & Node.js**
  Jalankan perintah berikut di direktori proyek server:
  ```bash
  npm install
  npm run build
  ```

* **Skenario B: Server cPanel / Shared Hosting (Tanpa Node.js)**
  Di komputer lokal Anda, jalankan `npm run build` terlebih dahulu. Setelah selesai, salin/upload folder lokal:
  ```
  public/build/
  ```
  ke dalam direktori:
  ```
  public/build/
  ```
  pada server cPanel produksi Anda.

---

## 🧹 5. Pembersihan & Optimasi Cache Laravel

Setelah file diperbarui, **wajib** membersihkan cache agar registrasi middleware baru, rute baru, dan template blade segera terbaca oleh sistem:

```bash
# Bersihkan cache lama
php artisan optimize:clear

# (Opsional di Produksi) Cache kembali untuk performa maksimal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Jika tidak memiliki akses terminal SSH di server cPanel, Anda dapat membuat rute sementara atau menghapus isi folder cache di `bootstrap/cache/*.php` secara manual via File Manager cPanel.

---

## 🏛️ 6. Pastikan Data Pejabat Kabag Umum Aktif di `m_pejabat`

Sistem membaca wewenang Kabag Umum secara dinamis dari tabel `m_pejabat`.

Pastikan di database server produksi pada tabel `m_pejabat`:
* Terdapat record dengan kolom `jabatan` bernilai: **`Kepala Bagian Umum`**
* Kolom `status` bernilai: **`aktif`**
* Kolom `nip` atau `nip_lama` terisi sesuai dengan NIP pejabat Kepala Bagian Umum yang sedang menjabat aktif (misal: Bpk. Joko Suwarjo).

*(Pengaturan ini juga dapat dikonfigurasi langsung oleh Superadmin melalui menu **Admin $\rightarrow$ Kelola Pejabat**).*

---

## ✅ 7. Checklist Verifikasi Akhir (Sanity Check & Security Test)

Lakukan pengujian cepat setelah proses deploy selesai untuk memastikan semuanya berjalan normal:

### A. Uji Tampilan & Keamanan
- [ ] Buka halaman login di browser: pastikan panel auto-login testing **tidak muncul** di layar.
- [ ] Uji celah bypass: buka URL `https://domain-bps/dev-login/197106131993121001` ➔ pastikan menghasilkan **404 Not Found**.
- [ ] Login sebagai Pegawai biasa (`user`), lalu coba ketik URL `https://domain-bps/admin/dashboard` ➔ pastikan sistem menolak dengan **403 Forbidden**.

### B. Uji Alur Persetujuan (Workflow)
- [ ] **Pengajuan Pegawai Biasa (`/lembur`)**: Isi form lembur dan kirim. Pastikan berhasil tersimpan dan kembali ke halaman `/lembur` dengan notifikasi sukses (tidak terjadi error 403 Forbidden).
- [ ] **Pegawai Tim Bagian Umum**: Buat pengajuan lembur baru, pastikan status awal langsung **`Menunggu Kabag`**.
- [ ] **Pegawai Tim Lain (misal Tim SID)**: Buat pengajuan lembur baru, pastikan status awal **`Diproses` (Menunggu Ketua Tim)**.
- [ ] **Ketua Tim**: Buka menu persetujuan, setujui pengajuan anggota tim ➔ pastikan status naik menjadi **`Menunggu Kabag`**.
- [ ] **Kepala Bagian Umum**:
  - Muncul menu tunggal **"Persetujuan Kabag Umum"**.
  - Buka menu tersebut: periksa pengajuan anggota, berikan persetujuan final ➔ pastikan status berubah menjadi **`Disetujui` (Disetujui Final)** dan status terkunci dari perubahan sepihak.

### C. Uji Fitur Edit Uraian Kegiatan (Admin, Ketua Tim, Kabag Umum)
- [ ] **Kondisi Tanpa Data Presensi**:
  - Buka modal aksi pada pengajuan lembur yang **belum** memiliki rekaman presensi.
  - Textarea **"Uraian Kegiatan"** harus dalam kondisi terkunci (*disabled*) dengan teks keterangan bantuan berwarna abu-abu: *"Uraian kegiatan hanya dapat diubah jika data presensi sudah tersedia"*.
- [ ] **Kondisi Dengan Data Presensi**:
  - Buka modal aksi pada pengajuan lembur yang **sudah** memiliki rekaman presensi (misal sudah diunggah oleh admin).
  - Textarea **"Uraian Kegiatan"** aktif dan dapat diedit.
  - Ubah teks uraian lalu klik Simpan/Setujui ➔ data uraian pada tabel langsung diperbarui.
  - Periksa database tabel `t_transaksi`: pastikan kolom `user_edited` terisi nama/NIP pengubah dan `tanggal_edited` terisi timestamp waktu perubahan.
- [ ] **Khusus Kepala Bagian Umum**:
  - Pada pengajuan anggota **Tim Bagian Umum** (tim yang dipimpinnya langsung) yang sudah memiliki presensi: textarea uraian **dapat diedit**.
  - Pada pengajuan anggota **Tim Lain**: textarea uraian berstatus **hanya-baca (*readonly*)** agar tidak mengganggu fokus verifikasi persetujuan akhir Kabag.

### D. Uji Kapasitas Uraian Kegiatan (Hingga 2.000 Karakter)
- [ ] Buka formulir pengajuan lembur (Pegawai) atau modal aksi koreksi (Ketua Tim / Admin / Kabag Umum).
- [ ] Periksa indikator teks: counter karakter tampil di atas textarea (misal: `0 / 2000`).
- [ ] Ketik atau tempel narasi uraian kegiatan yang panjang (> 255 karakter):
  - Kotak textarea dapat di-resize vertikal (*drag-down*).
  - Teks tersimpan dengan sukses tanpa error *Data too long* ataupun validasi gagal.
  - Tampilan pada tabel membungkus teks dengan rapi (*break-words*).

### E. Uji Pembatasan Jam Selesai Lembur Berdasarkan Presensi Pulang
- [ ] Buka modal aksi persetujuan pada data lembur yang **sudah memiliki data presensi** (misal presensi pulang: `18:30`).
- [ ] Periksa kolom input **"Jam Selesai Disetujui"**:
  - Muncul teks bantuan di bawah input: *📌 "Maksimal jam selesai: 18:30 (sesuai presensi pulang)"*.
  - Atribut input memiliki pembatas `max="18:30"`.
- [ ] Coba masukkan jam selesai melebihi jam kepulangan (misal: `19:00`), lalu klik Simpan:
  - Frontend langsung memblokir dengan peringatan: *"Jam selesai disetujui (19:00) tidak boleh melebihi jam kepulangan presensi pegawai (18:30)"*.
  - Backend controller juga menolak dengan response HTTP 422 jika request ditembak langsung.
- [ ] Masukkan jam selesai yang valid ($\le$ `18:30`, misal: `18:30`):
  - Data berhasil tersimpan tanpa kendala.

### F. Uji Nilai Default Jam Pada Modal Persetujuan (Default Mengikuti Jam Pengajuan)
- [ ] Buka modal persetujuan pada pengajuan lembur berstatus **Pending** (misal pegawai mengajukan lembur dari pukul `16:31` s.d. `20:00`, namun data presensi kepulangan tercatat pukul `20:30`).
- [ ] Periksa nilai awal input jam pada modal:
  - Input **Jam Mulai Disetujui** terisi default: **`16:31`** (sesuai pengajuan).
  - Input **Jam Selesai Disetujui** terisi default: **`20:00`** (sesuai pengajuan, **bukan** melonjak ke `20:30`).
  - Jam presensi pulang (`20:30`) hanya berfungsi sebagai teks petunjuk bantuan dan batas maksimum (`max="20:30"`).

### G. Uji Fitur & Integrasi Admin (Dashboard & Pengajuan Satker-Wide)
- [ ] **Dashboard Admin**:
  - Kartu statistik "Diproses" menghitung pengajuan dengan status `pending` dan `menunggu_kabag`.
  - Klik kartu "Diproses" membuka modal pengajuan: pengajuan `menunggu_kabag` muncul dengan label biru khusus.
  - Klik tombol "✓ Setujui" pada modal quick-approve: berhasil menyetujui transaksi (termasuk membatasi jam kepulangan fisik presensi jika ada) tanpa fatal error.
  - Pada tabel "Lembur Hari Ini", pengajuan berstatus `menunggu_kabag` tampil dengan badge biru *Menunggu Kabag*.
- [ ] **Pengajuan Lembur Satker-Wide (`/admin/pengajuan`)**:
  - Filter status dan urutan tanggal bekerja normal.
  - Tombol aksi/koreksi muncul pada baris tabel untuk semua status (`pending`, `menunggu_kabag`, `approved`, `rejected`).
  - Admin dapat menyetujui pengajuan yang berstatus `menunggu_kabag` secara langsung (menjadi `approved` dan mengisi `approved_kabag_at`).
  - Jika Admin menolak pengajuan (`rejected`), jam disetujui di-reset menjadi `-` dan disimpan `NULL` di database.
  - Data uraian dan jam lembur yang diedit oleh Admin tercatat rapi di kolom `user_edited` dan `tanggal_edited`.




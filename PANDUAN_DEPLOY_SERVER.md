# 🚀 Panduan & Checklist Deploy ke Server Produksi

Dokumen ini berisi panduan teknis langkah demi langkah untuk menerapkan perubahan sistem lembur bertingkat (Kabag Umum), penguatan keamanan otorisasi (*Role-Based Access Control*), dan pembaruan sistem ke server produksi / live.

---

## ⚠️ 1. Update Database Produksi (Paling Krusial!)

Di database server produksi, tabel `t_transaksi` perlu diperbarui untuk mendukung kolom catatan Kabag, tanggal persetujuan Kabag, dan pelebaran kolom status.

### A. Cara Rekomendasi (Lewat phpMyAdmin / Database Client cPanel)
Buka phpMyAdmin di cPanel server produksi Anda, pilih database lembur, buka tab **SQL**, lalu jalankan kueri berikut (atau impor berkas [database/update_v2.sql](file:///d:/TUGAS%20ITTP/BPS%20-%20MAGANG/lembur/database/update_v2.sql)):

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
4. 📄 **`app/Traits/KoreksiLembur.php`** *(Kalkulasi lembur presisi, penambahan method `koreksiUntukTransaksi` & `koreksiUntukBulan` untuk otomatisasi status `eligible=1`)*.
5. 📄 **`app/Exports/RekapitulasiExport.php`** *(Ekspor Excel rekapitulasi + auto-sweep evaluasi eligible lembur bulan berjalan)*.
6. 📄 **`app/Http/Controllers/admin/RekapitulasiController.php`** *(Tampilan rekapitulasi admin + auto-sweep evaluasi eligible lembur bulan berjalan)*.
7. 📄 **`app/Http/Controllers/pegawai/RekapitulasiController.php`** *(Tampilan rekapitulasi pegawai + auto-sweep evaluasi eligible lembur bulan berjalan)*.
8. 📄 **`app/Http/Middleware/CheckRole.php`** *(Wajib ada, jika tertinggal aplikasi akan error Class Not Found)*.
8. 📄 **`bootstrap/app.php`** *(Memuat pendaftaran alias middleware `role`)*.
9. 📄 **`routes/web.php`** *(Memuat rute pembatalan lembur admin: `/admin/lembur/{id}/cancel` dan `/admin/pengajuan/{id}/cancel`)*.
10. 📄 **`app/Http/Controllers/LemburController.php`** *(Form pegawai + validasi max:2000 + batas pengajuan presensi)*.
11. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Persetujuan Kabag + auto-trigger `koreksiUntukTransaksi` saat approved)*.
12. 📄 **`app/Http/Controllers/ketuatim/PengajuanController.php`** *(Controller persetujuan Ketua Tim + edit uraian + validasi presensi)*.
13. 📄 **`app/Http/Controllers/admin/PengajuanController.php`** *(Controller Admin + pembatalan + auto-trigger `koreksiUntukTransaksi` saat approved)*.
14. 📄 **`app/Http/Controllers/admin/DashboardController.php`** *(Dashboard Admin + quick-approve + auto-trigger `koreksiUntukTransaksi`)*.
15. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Monitoring lembur Admin + subquery indikator presensi pulang + pembatalan pengajuan)*.
16. 📄 **`resources/views/lembur.blade.php`** *(View lembur pegawai + badge Dibatalkan Admin)*.
17. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(View persetujuan Kabag + badge & tab filter Dibatalkan)*.
18. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(View persetujuan Ketua Tim + badge Dibatalkan + proteksi aksi)*.
19. 📄 **`resources/views/ketua-tim/dashboard.blade.php`** *(View dashboard Ketua Tim + badge status menunggu_kabag)*.
20. 📄 **`app/Http/Controllers/ketuatim/DashboardController.php`** *(Controller dashboard Ketua Tim + sinkronisasi status disetujui & presensi)*.
21. 📄 **`resources/views/admin/dashboard.blade.php`** *(View dashboard Admin + badge menunggu_kabag + tag modal pending + standarisasi istilah pegawai & kontras)*.
22. 📄 **`resources/views/admin/pengajuan.blade.php`** *(View persetujuan Admin + tombol keputusan Batalkan + badge Dibatalkan + kontras NIP & ikon tabel)*.
23. 📄 **`resources/views/admin/lembur.blade.php`** *(View monitoring Admin + perapihan tata letak isi sel tabel, kolom mandiri Data Presensi dengan pill badge hijau Lihat Presensi ↗ & abu-abu Belum Presensi, perataan vertikal align-middle, badge status modern dengan indicator dot, jam & NIP ber-font monospace, modal aksi terpadu)*.
24. 📄 **`resources/views/pimpinan/pengajuan.blade.php`** *(View persetujuan Pimpinan + badge Menunggu Kabag & Dibatalkan)*.
25. 📄 **`resources/views/partials/sidebar.blade.php`** *(Navigasi menu Kabag + rute brand aktif + kontras menu & header WCAG AA)*.
26. 📄 **`resources/views/layouts/app.blade.php`** *(Layout utama + padding kontainer mobile responsif + escape keyboard handler modal)*.
27. 📄 **`resources/views/partials/footer.blade.php`** *(Footer resmi BPS Provinsi Riau)*.
28. 📄 **`resources/views/partials/navbar.blade.php`** *(Aksesibilitas tombol sidebar toggle + kontras avatar)*.
29. 📄 **`resources/views/login.blade.php`** *(Form login bersih tanpa glow neon + penghapusan scale clipping)*.
30. 📄 **`resources/views/welcome.blade.php`** *(Landing page rapi + pembersihan blob dekoratif + penyetaraan rounded-xl)*.
31. 📄 **`resources/views/dashboard.blade.php`** *(Dashboard pegawai + pembersihan console.log debug + kontras empty state)*.
32. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Persetujuan Ketua Tim + standarisasi search bar rounded-xl & kontras NIP)*.
33. 📁 **`public/build/`** *(Folder bundle asset CSS & JS hasil npm run build terbaru)*.
34. 📄 **`app/Http/Controllers/LemburController.php`** *(Penanganan HTTP 403 Forbidden WAF server BPS pada `storeDoc` dengan dekripsi Base64 & normalisasi URL)*.
35. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Penanganan HTTP 403 Forbidden WAF server BPS pada `storeDoc` Admin dengan dekripsi Base64 & normalisasi URL)*.
36. 📄 **`resources/views/lembur.blade.php`** *(Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi pegawai)*.
37. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi Ketua Tim)*.
38. 📄 **`resources/views/admin/lembur.blade.php`** *(Listener submit & blur encoding Base64 dan reset input pada modal dokumentasi Admin)*.
39. 📄 Seluruh file controller, model, dan views lainnya yang telah dimodifikasi.

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

Aplikasi menggunakan Vite untuk mem-bundle aset CSS dan JavaScript (TailwindCSS). 

> [!TIP]
> **Otomatis Tersinkron via Git!**  
> Folder `public/build` (berisi `manifest.json`, CSS, dan JS terkompilasi) **sudah dimasukkan ke dalam tracking Git**.  
> Jadi, ketika server produksi menjalankan `git pull`, berkas aset terbaru **langsung otomatis terunduh ke server**. Anda **TIDAK PERLU** menginstal Node.js / NPM atau menjalankan `npm run build` di server produksi!

* **Jika Menggunakan Git (`git pull`)**:
  Cukup jalankan `git pull`, aset tampilan langsung sinkron 100%.

* **Jika Unggah Manual (File Manager cPanel / FTP)**:
  Unggah folder `public/build/` dari komputer lokal ke direktori `public/build/` di cPanel server.

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

### H. Uji Fitur Pembatalan Pengajuan oleh Admin (Fase 1 No. 1 - Anti Dobel Input / Salah Tanggal)
- [ ] **Pembatalan dari Menu Lembur Admin (`/admin/lembur`)**:
  - Buka menu **Lembur Admin**: periksa kolom baru **"Aksi"** pada setiap baris pengajuan.
  - Klik tombol **"Batal"** pada pengajuan yang ingin dibatalkan (misal: pengajuan dobel atau salah input tanggal).
  - Modal **"Batalkan Pengajuan Lembur"** muncul: periksa nama pegawai, tanggal lembur, dan kotak isian wajib **"Alasan Pembatalan"**.
  - Jika alasan dibiarkan kosong, form memunculkan pesan validasi error.
  - Masukkan alasan pembatalan (misal: *"Dobel input pengajuan lembur tanggal 28 September"*) lalu klik **"Ya, Batalkan Pengajuan"**.
  - Sistem memproses via AJAX: modal tertutup, toast sukses muncul, baris tabel langsung berubah:
    - Status menjadi badge abu-abu: **`Dibatalkan`**.
    - Catatan terisi: **`[Dibatalkan Admin] Dobel input pengajuan lembur...`**.
    - Kolom aksi berubah menjadi teks miring abu-abu: *Dibatalkan*.
  - Klik tab filter status **Dibatalkan**: pengajuan yang baru dibatalkan muncul dalam tab tersebut dengan jumlah counter yang sesuai.
- [ ] **Pembatalan dari Menu Persetujuan Admin (`/admin/pengajuan`)**:
  - Buka menu **Persetujuan Admin**: klik ikon koreksi/aksi pada baris lembur.
  - Pada modal keputusan, kini tersedia tombol ke-3: **"Batalkan"**.
  - Klik tombol **"Batalkan"**: input jam disetujui otomatis disembunyikan dan label catatan berubah menjadi *"Alasan Pembatalan (Wajib)"*.
  - Masukkan alasan dan simpan: status langsung ter-update menjadi **`Dibatalkan`** secara realtime.
- [ ] **Verifikasi Dampak & Keamanan Rekapitulasi (Audit & Keuangan)**:
  - Periksa database tabel `t_transaksi`: pastikan `status = 'cancelled'`, `jam_mulai_disetujui = NULL`, `jam_selesai_disetujui = NULL`, `eligible = NULL`, `approved_at = NULL`, `approved_kabag_at = NULL`, `user_edited` terisi nama Admin, dan `tanggal_edited` terisi timestamp.
  - Buka menu **Rekapitulasi / SPKL** (`/admin/rekapitulasi`): pastikan transaksi yang berstatus `cancelled` **sama sekali tidak masuk ke dalam perhitungan jam uang lembur maupun ekspor Excel/PDF**.
  - Buka tampilan **Pegawai** (`/lembur`): status tampil sebagai **`Dibatalkan Admin`** dan tombol edit terkunci.
  - Buka tampilan **Ketua Tim** & **Kabag Umum**: status tampil sebagai **`Dibatalkan`** dan tombol aksi terkunci (*disabled*).

### I. Uji Otomatisasi Nilai Eligible & Indikator Presensi Monitoring Admin (Fase 1 No. 2 & No. 3)
- [ ] **Otomatisasi Nilai `eligible` Saat Pengajuan Disetujui (Admin & Kabag Umum)**:
  - Cari pengajuan lembur yang sudah memiliki data presensi masuk & pulang, namun belum disetujui (misal status `menunggu_kabag` atau `pending`).
  - Lakukan persetujuan (baik melalui menu Admin `/admin/pengajuan`, Dashboard Admin quick-approve, ataupun menu Kabag Umum `/kabag-umum/pengajuan`).
  - Periksa database tabel `t_transaksi`: pastikan kolom `status = 'approved'` dan `eligible = 1` langsung terisi secara instan tanpa perlu menunggu pegawai/admin membuka halaman `/lembur`.
  - Cek kolom `jam_mulai_disetujui` dan `jam_selesai_disetujui`: durasi lembur dihitung presisi sesuai batasan jam persetujuan dan aturan bisnis lembur (hari kerja minimal 1 jam setelah jam kepulangan resmi, hari libur minimal durasi berlaku).
- [ ] **Auto-Sweep Rekapitulasi Keuangan & Ekspor Excel/PDF**:
  - Buka menu **Admin $\rightarrow$ Rekapitulasi** (`/admin/rekapitulasi`) untuk bulan terkait atau klik **Download Excel Rekapitulasi**.
  - Sistem secara otomatis menjalankan *sweep* batch (`koreksiUntukBulan`) untuk memastikan semua pengajuan `approved` yang belum memiliki nilai `eligible` langsung dihitung dan diperbarui menjadi `eligible = 1`.
  - Pastikan nominal uang lembur dan uang makan pada tabel rekapitulasi serta file Excel terhitung lengkap tanpa ada pengajuan sah yang tertinggal.
- [ ] **Indikator Presensi & Tampilan Tabel Monitoring Lembur Admin (`/admin/lembur`)**:
  - Buka halaman **Admin $\rightarrow$ Lembur** (`/admin/lembur`).
  - Periksa kolom **Data Presensi**:
    - Untuk pengajuan yang **sudah ada presensi**: muncul pill badge interaktif berkelas hijau 🟢 **`Lihat Presensi ↗`** lengkap dengan titik status dan ikon panah. Tooltip hover menampilkan jam kepulangan resmi pegawai.
    - Untuk pengajuan yang **belum ada presensi**: muncul pill badge abu-abu rapi ⚪ **`Belum Presensi`**.
  - Klik badge hijau 🟢 **`Lihat Presensi ↗`**:
    - Modal popup **"Detail Presensi Pegawai"** muncul menampilkan tanggal, status kehadiran, jam masuk, dan jam pulang.
    - Tutup modal menggunakan tombol tutup atau tekan tombol **`Escape`**.
  - Periksa keselarasan baris tabel: semua sel tersusun rapi di posisi tengah (`align-middle`), jam lembur & NIP tampil tajam dengan font monospace, badge status memiliki titik warna teratur, dan tombol **`[Aksi]`** tersaji rapi dan konsisten.

### J. Uji Aksesibilitas WCAG AA & Tampilan Responsif (Anti-Slop Visual & Mobile)
- [ ] **Responsivitas Tampilan Mobile**:
  - Buka aplikasi di layar smartphone atau gunakan *Device Mode* pada DevTools browser (lebar layar <= 420px).
  - Pastikan halaman memiliki margin/padding yang proporsional (`px-4 sm:px-6 lg:px-8`), tabel tidak meluap keluar layar secara berantakan, dan form nyaman diisi.
- [ ] **Navigasi Keyboard Aksesibel**:
  - Buka salah satu modal pop-up (misal: modal presensi, modal koreksi, atau modal pembatalan).
  - Tekan tombol **`Escape`** pada keyboard: modal harus langsung menutup secara halus tanpa harus mengklik tombol silang.
- [ ] **Keterbacaan & Rasio Kontras Teks (WCAG AA)**:
  - Periksa teks nomor NIP pegawai pada tabel pengajuan: warna teks abu-abu gelap (`text-slate-500`) kontras dan mudah terbaca di latar putih.
  - Periksa sidebar mode gelap: label grup navigasi dan menu non-aktif (`text-slate-400`) kontras jelas di atas latar biru gelap (`bg-slate-900`).
  - Periksa avatar profil di navbar: inisial huruf terlihat kontras dan tegas (`text-amber-800`).
- [ ] **Pembersihan Boilerplate & Console Debug**:
  - Periksa bagian bawah halaman (footer): tertulis identitas resmi *"Badan Pusat Statistik Provinsi Riau • Hak Cipta Dilindungi"*.
  - Buka Developer Console (F12 -> Tab Console): pastikan tidak ada log debug kotor seperti `DEBUG TIMKERJA`.

### K. Uji Filter Periode "Semua Bulan (Tahun Berjalan)" & Urutan Bawaan Terbaru (Ketua Tim, Kabag Umum, & Admin)
- [ ] **Buka Halaman Persetujuan Tanpa Parameter**:
  - Akses menu **Persetujuan Lembur** pada role Ketua Tim (`/ketua-tim/pengajuan`), Kabag Umum (`/kabag-umum/pengajuan`), atau Admin (`/admin/pengajuan`).
  - Pastikan tombol periode pada toolbar langsung menampilkan label **"Semua Bulan {Tahun}"** (contoh: *"Semua Bulan 2026"*).
- [ ] **Verifikasi Visibilitas Pengajuan Lintas Bulan Kalender ($N+1$)**:
  - Pastikan pengajuan lembur anggota tim di bulan sebelumnya (misal: lembur bulan September saat ditinjau pada bulan Oktober) langsung tampil utuh di tabel tanpa tersembunyi.
- [ ] **Verifikasi Urutan Bawaan (*Default Sort*)**:
  - Periksa kolom header **Tanggal Lembur**: dropdown urutan secara otomatis terpilih ke opsi **"Terbaru"** (`desc`).
  - Pengajuan dengan tanggal paling baru langsung berada di baris pertama tabel.
- [ ] **Uji Interaksi Panel Period Picker**:
  - Klik tombol periode untuk membuka panel: tombol **"Semua Bulan ({Tahun})"** berada di posisi atas dan ter-highlight aktif oranye amber.
  - Klik salah satu bulan spesifik (misal: *"Sep"*): halaman memfilter hanya untuk bulan September dan label berubah menjadi *"September {Tahun}"*.
### L. Uji Unggah Link Dokumentasi Lembur (Bypass WAF / ModSecurity 403 Forbidden Server BPS)
- [ ] **Buka Modal Tambah Dokumentasi**:
  - Masuk ke menu lembur berstatus **Disetujui** pada role Pegawai (`/lembur`), Ketua Tim, atau Admin (`/admin/lembur`).
  - Klik tautan **`+ Tambah`** pada kolom Dokumentasi untuk menampilkan modal pop-up.
- [ ] **Kirim Tautan Google Drive**:
  - Masukkan tautan Google Drive (contoh: `https://drive.google.com/drive/folders/...` atau format tanpa protokol `drive.google.com/...`).
  - Klik tombol **Simpan**.
- [ ] **Verifikasi Respon Jaringan (Network) & Hasil**:
  - Buka DevTools (F12 -> Tab Network): pastikan request POST `.../dokumentasi` berhasil dengan kode **HTTP 302 / 200 OK** (tidak lagi diblokir **HTTP 403 Forbidden** oleh WAF server BPS).
  - Muncul notifikasi sukses: *"Dokumentasi berhasil disimpan."*
  - Pada baris tabel, muncul tombol **"Lihat ↗"** berwarna biru yang ketika diklik membuka tautan Google Drive asli di tab baru (*target _blank*).
- [ ] **Uji Hapus Dokumentasi**:
  - Klik tombol silang (hapus) di samping tautan *Lihat* dan konfirmasi: data dokumentasi berhasil terhapus dan kolom kembali ke tombol *+ Tambah*.



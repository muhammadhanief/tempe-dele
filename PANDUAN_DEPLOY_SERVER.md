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

-- 5. Sanitasi Integritas Pejabat (Memastikan Tepat 1 Pejabat Aktif per Jabatan Struktural)
-- Pastikan tidak ada data duplikat aktif untuk Kepala Bagian Umum dan PPK:
UPDATE m_pejabat SET status = 'nonaktif' WHERE jabatan = 'Kepala Bagian Umum' AND id_pejabat != 12;
UPDATE m_pejabat SET status = 'aktif' WHERE id_pejabat = 12;
UPDATE m_pejabat SET status = 'nonaktif' WHERE jabatan = 'PPK' AND id_pejabat != 9;
UPDATE m_pejabat SET status = 'aktif' WHERE id_pejabat = 9;

-- 6. Sanitasi NIP Pejabat Kepala Bagian Umum (Bpk. Joko Suwarjo, pastikan 18 digit lengkap)
UPDATE m_pejabat SET nip = '197106131993121001' WHERE nip = '197106131993121' OR id_pejabat = 12;

-- 7. Penyesuaian Role Pak Joko Suwarjo (Flow Baru: Ketua Tim / Kabag Umum, Non-Admin)
UPDATE m_pegawai SET role = 'ketua_tim' WHERE nip = '197106131993121001' OR nip_lama = '340013741';
```

> [!NOTE]
> **Modul Manajemen User, Suksesi PPK & Parameter Dokumen Dinamis (Update Terkini)**:  
> Modul ini memanfaatkan tabel data master yang telah ada (`m_pejabat`, `m_tim`, `m_pegawai`) secara terintegrasi. **Tidak ada penambahan kolom atau perubahan skema database (`ALTER TABLE`) baru yang diperlukan**. Cukup pastikan sanitasi status pejabat aktif di atas telah dieksekusi agar sistem memiliki tepat 1 pejabat aktif sebagai fallback dokumen.

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
1. 📄 **`app/Http/Controllers/admin/ManajemenUserController.php`** *(Controller baru Superadmin: manajemen user, suksesi Kabag & PPK, tambah/hapus admin, dan promosi superadmin)*.
2. 📄 **`resources/views/admin/manajemen_user.blade.php`** *(View antarmuka Manajemen User, profil Kabag & PPK aktif, riwayat akordeon, modal suksesi Kabag & PPK, tabel admin, tabel superadmin)*.
3. 📄 **`app/Http/Controllers/admin/PejabatController.php`** *(Penegakan otomatis single active invariant: penetapan pejabat aktif baru otomatis menonaktifkan pejabat lama)*.
4. 📄 **`app/Http/Controllers/admin/DokumenGenerateController.php`** *(Parameterisasi penandatangan dinamis KBU & PPK pada generator SPKL dan Laporan Lembur)*.
5. 📄 **`app/Http/Controllers/admin/DokumenViewController.php`** *(Penyediaan data KBU & PPK untuk opsi penandatangan modal cetak)*.
6. 📄 **`resources/views/admin/dokumen.blade.php`** *(Form selector KBU & PPK di modal SPKL dan modal generate)*.
7. 📄 **`app/Http/Controllers/admin/DaftarHadirController.php`** *(Parameterisasi penandatangan KBU pada unduh daftar hadir PDF)*.
8. 📄 **`resources/views/admin/daftar_hadir.blade.php`** *(Meneruskan parameter kbu pada link download daftar hadir PDF)*.
9. 📄 **`routes/web.php`** *(Rute baru `/admin/manajemen-user/*` dengan middleware `role:superadmin`)*.
10. 📄 **`resources/views/partials/sidebar.blade.php`** *(Menu baru Manajemen User di Master, eksklusif untuk role `superadmin`)*.
11. 📄 **`app/Http/Controllers/admin/PenggunaController.php`** *(Pengamanan anti-downgrade akun superadmin pada aksi edit pengguna)*.
12. 📄 **`resources/views/admin/pengguna.blade.php`** *(Proteksi dropdown role tabel pengguna untuk akun superadmin)*.
13. 📄 **`resources/views/login.blade.php`** *(Shortcut dev-login Kabag Umum dinamis membaca database `m_pejabat`)*.
8. 📄 **`database/migrations/2026_09_25_000001_add_user_edited_to_t_transaksi.php`** *(File migrasi audit edit)*.
9. 📄 **`database/migrations/2026_09_25_000002_widen_uraian_column_in_t_transaksi.php`** *(File migrasi kapasitas uraian TEXT)*.
10. 📄 **`app/Models/Transaksi.php`** *(Update $fillable: user_edited, tanggal_edited)*.
11. 📄 **`app/Traits/KoreksiLembur.php`** *(Kalkulasi lembur presisi, penambahan method `koreksiUntukTransaksi` & `koreksiUntukBulan` untuk otomatisasi status `eligible=1`)*.
12. 📄 **`app/Exports/RekapitulasiExport.php`** *(Ekspor Excel rekapitulasi + auto-sweep evaluasi eligible lembur bulan berjalan)*.
13. 📄 **`app/Http/Controllers/admin/RekapitulasiController.php`** *(Tampilan rekapitulasi admin + auto-sweep evaluasi eligible lembur bulan berjalan)*.
14. 📄 **`app/Http/Controllers/pegawai/RekapitulasiController.php`** *(Tampilan rekapitulasi pegawai + auto-sweep evaluasi eligible lembur bulan berjalan)*.
15. 📄 **`app/Http/Middleware/CheckRole.php`** *(Wajib ada, jika tertinggal aplikasi akan error Class Not Found)*.
16. 📄 **`bootstrap/app.php`** *(Memuat pendaftaran alias middleware `role`)*.
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
39. 📄 **`resources/views/partials/navbar.blade.php`** *(Pemindahan hamburger toggle button ke pojok kiri atas bilah navigasi mendampingi judul)*.
40. 📄 **`resources/views/partials/sidebar.blade.php`** *(Penambahan tombol silang tutup mobile pada header drawer navigasi)*.
41. 📄 **`resources/css/app.css`** *(Styling scrollbar kontras tinggi 8px, track slate-200, thumb slate-400/amber, dan kelas scrollbar visual mobile)*.
42. 📄 **`resources/js/app.js`** *(Fungsi `initTableScrollbars` untuk sinkronisasi realtime thumb visual scrollbar mobile, seek interaktif sentuh, dan desktop drag)*.
43. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Penambahan `min-w-[1100px]` dan petunjuk visual geser tabel mobile)*.
44. 📄 Seluruh file blade tabel lainnya (`ketua-tim`, `lembur`, `admin`, `akumulasi`, `rekapitulasi`, `daftar_hadir`, `pimpinan`) yang dilengkapi petunjuk geser mobile.
45. 📄 Seluruh file controller, model, dan views lainnya yang telah dimodifikasi.
46. 📄 **`resources/views/lembur.blade.php`** *(Page Header terstruktur H1 + subjudul + tombol Ajukan Lembur, modernisasi filter rounded-xl, dan perbaikan text-wrap whitespace-nowrap pada dateBtn/dateLabel)*.
47. 📄 **`resources/views/admin/lembur.blade.php`** *(Page Header terstruktur H1 + subjudul + tombol Unduh Excel & Ajukan Lembur, modernisasi filter rounded-xl, dan perbaikan text-wrap whitespace-nowrap)*.
48. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Penambahan whitespace-nowrap pada dateBtn dan dateLabel untuk proteksi konsisten di layar kecil)*.
49. 📁 **`public/build/`** *(Bundel produksi Vite terbaru hasil npm run build: manifest.json, CSS, JS)*.
50. 📄 Seluruh view tabel (11 file blade) yang telah dilengkapi komponen bilah scrollbar visual interaktif (`.table-scroll-hint`, `.table-scroll-track`, `.table-scroll-thumb`).
51. 📄 **Seluruh View Filter Pencarian Pegawai & Tim (10 file)**: `resources/views/ketua-tim/pengajuan.blade.php`, `resources/views/pimpinan/pengajuan.blade.php`, `resources/views/admin/pengajuan.blade.php`, `resources/views/admin/lembur.blade.php`, `resources/views/lembur.blade.php`, `resources/views/admin/tim.blade.php`, `resources/views/admin/akumulasi.blade.php`, `resources/views/admin/presensi.blade.php`, `resources/views/admin/spkl.blade.php`, `resources/views/admin/pengguna.blade.php` *(Perbaikan dropdown tampil penuh otomatis saat dibuka, auto-select teks untuk pengetikan cepat, tombol clear `(×)`, sorotan centang pilihan aktif, dan clickable chevron)*.
52. 📁 **`public/build/`** *(Aset bundle frontend produksi Vite terbaru: manifest.json, app-vIdj2UmY.css, app-DbISuP7_.js)*.
53. 📄 **`resources/views/dokumen/daftar_hadir_print.blade.php`** *(Template cetak daftar hadir presensi lembur BPS standar A4 portrait, layout tabel bergaris resmi, tanda tangan base64 data URI, dan script auto-print)*.
54. 📄 **`resources/views/admin/daftar_hadir.blade.php`** *(Penambahan tombol/icon printer "Cetak A4" berdampingan dengan Unduh PDF, dropdown PNS/PPPK, dan listener click-outside)*.
55. 📄 **`app/Http/Controllers/admin/DaftarHadirController.php`** *(Method `print()` untuk render cetak A4 dan penyempurnaan query filter pegawai PNS/PPPK)*.
56. 📄 **`routes/web.php`** *(Pendaftaran rute `admin.daftar_hadir.print`)*.
57. 📄 **`resources/views/dokumen/spkl.blade.php`** *(Perbaikan resolusi path logo kop surat multi-environment dinamis, validasi file_exists, proteksi extension_loaded('gd'), dan fallback kop teks)*.
58. 📄 **`resources/views/dokumen/daftar_hadir.blade.php`** *(Proteksi file_exists sebelum render gambar tanda tangan pegawai di template PDF)*.
59. 📄 **`app/Exports/LaporanExport.php`** *(Pembaruan pengurutan kronologis tanggal dan prioritas NIP Baru 18 digit pada ekspor Laporan Hasil Kerja Lembur Excel XLSX)*.
60. 📄 **`app/Http/Controllers/admin/LaporanController.php`** *(Pembaruan pengurutan kronologis berdasarkan tanggal pada tampilan tabel menu Laporan Admin)*.
61. 📄 **`app/Http/Controllers/admin/DokumenGenerateController.php`** *(Penerapan prioritas NIP Baru 18 digit pegawai pada pembuatan Laporan Hasil Kerja Lembur)*.
62. 📄 **`resources/views/dokumen/laporan.blade.php`** *(Perapihan layout PDF A4 portrait, styling anti-cut off page-break tabel & tanda tangan, dan NIP Baru 18 digit)*.
63. 📄 **`resources/views/admin/daftar_hadir.blade.php`** *(Penyederhanaan tombol "Cetak" tanpa embel-embel A4 dan penayangan jam pulang riil presensi atau tanda strip)*.
64. 📄 **`app/Http/Controllers/admin/DaftarHadirController.php`** *(Penambahan correlated subquery jam kepulangan riil dari t_presensi untuk index, unduh PDF, dan print browser)*.
65. 📄 **`resources/views/dokumen/daftar_hadir.blade.php`** *(Penayangan jam pulang kepulangan riil pada template PDF landscape daftar hadir)*.
66. 📄 **`resources/views/dokumen/daftar_hadir_print.blade.php`** *(Penayangan jam pulang kepulangan riil pada template cetak browser daftar hadir)*.
67. 📄 **`app/Http/Controllers/admin/RekapitulasiController.php`** *(Penyaringan filter kategori PNS/PPPK, proteksi upsert cache agregat, dan penanganan unduh Excel per kategori)*.
68. 📄 **`app/Exports/RekapitulasiExport.php`** *(Penyaringan query PNS/PPPK dan judul worksheet dinamis pada ekspor rekapitulasi Excel)*.
69. 📄 **`resources/views/admin/spkl.blade.php`** *(Dropdown filter kategori pegawai di toolbar dan dropdown tombol unduh dengan opsi Semua, PNS, dan PPPK)*.
70. 📄 **`bootstrap/app.php`** *(Pengecualian CSRF untuk rute logout serta fallback otomatis pengalihan TokenMismatchException / HTTP 419 ke halaman login)*.
71. 📄 **`routes/web.php`** *(Dukungan method ganda GET dan POST pada rute `/logout`)*.
72. 📄 **`resources/views/login.blade.php`** *(Penambahan kotak notifikasi alert informasi session error jika sesi kedaluwarsa)*.
73. 📄 **`app/Http/Controllers/admin/DashboardController.php`** *(Pembaruan metrik statistik tahun berjalan & antrean pending aktif tanpa kunci bulan)*.
74. 📄 **`app/Http/Controllers/ketuatim/DashboardController.php`** *(Pembaruan metrik tim tahun berjalan & antrean pending tim)*.
75. 📄 **`app/Http/Controllers/pegawai/DashboardController.php`** *(Pembaruan metrik pegawai tahun berjalan & inklusi status menunggu_kabag pada kartu Diproses)*.
76. 📄 **`app/Http/Controllers/pimpinan/DashboardController.php`** *(Pembaruan metrik pimpinan tahun berjalan & inklusi status menunggu_kabag pada kartu Diproses)*.
77. 📄 **`resources/views/admin/dashboard.blade.php`**, **`resources/views/ketua-tim/dashboard.blade.php`**, **`resources/views/dashboard.blade.php`**, **`resources/views/pimpinan/dashboard.blade.php`** *(Pembaruan subtitle kartu metrik menjadi Tahun berjalan)*.

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

### M. Uji Tampilan Mobile & Geser Horizontal Tabel (Swipe & Drag-to-Scroll ala SIMANTIK)
- [ ] **Posisi Hamburger Button di Kiri Atas**:
  - Buka aplikasi di layar smartphone atau aktifkan *Device Mode* pada browser DevTools (lebar <= 420px).
  - Pastikan tombol Hamburger (garis tiga) berada di **pojok KIRI atas**, mendampingi judul halaman (bukan lagi di sisi kanan).
  - Sentuh/klik tombol Hamburger: drawer menu navigasi terbuka mulus dari kiri ke kanan.
  - Tekan tombol silang `(X)` di samping brand logo atau sentuh area gelap luar untuk menutup kembali navigasi.
- [ ] **Fitur Geser Tabel Horizontal (Swipe & Drag-to-Scroll)**:
  - Akses halaman pengajuan lembur (Pegawai, Ketua Tim, Kabag Umum, Admin, atau Akumulasi).
  - Di atas tabel muncul petunjuk sentuh: *"Geser tabel ke kiri / kanan untuk melihat kolom lengkap"*.
  - Geser tabel dengan jari ke kanan: tabel bergeser mulus tanpa patah-patah (*touch momentum scrolling*).
  - Kolom-kolom lebar (Nama, Tanggal, Jam, Uraian, Presensi Pulang, Status, dan Tombol Aksi) tersaji proporsional dan tidak saling menghimpit.
  - Pada browser desktop, pengguna dapat menahan klik kiri mouse pada ruang kosong tabel lalu menariknya (*drag-to-scroll*) untuk menggeser kolom secara instan.

### N. Uji Penyeragaman Header & Toolbar Halaman Lembur (Pegawai, Ketua Tim, & Admin)
- [ ] **Tampilan Page Header & Tombol Aksi**:
  - Akses menu Pengajuan Lembur Pegawai (`/lembur`), Ketua Tim (`/ketua-tim/lembur`), dan Admin (`/admin/lembur`).
  - Pastikan seluruh halaman memiliki Page Header terstruktur (`<h1>` semantik + subjudul deskriptif).
  - Tombol aksi utama (*Ajukan Lembur* dan *Unduh Excel*) berada rapi di pojok kanan atas header, bukan lagi terdesak di dalam baris toolbar filter.
- [ ] **Uji Tombol Tanggal Bebas Text-Wrapping**:
  - Periksa tombol filter tanggal pada toolbar: teks label **"Semua Tanggal"** tampil rapi dalam 1 baris utuh dan tidak terpotong ke 2 baris vertikal.
  - Uji pada tampilan layar sempit / mobile: tombol tanggal tetap mempertahankan teks 1 baris berkat kelas `whitespace-nowrap`.
  - Semua kontrol toolbar (Date Picker, Filter Bulan, Per Halaman, Search) seragam dalam bentuk kartu modern `rounded-xl shadow-2xs`.

### O. Uji Scrollbar Visual Interaktif pada Tabel Mobile
- [ ] **Bilah Scrollbar Visual di Layar HP**:
  - Buka menu lembur/pengajuan/rekapitulasi pada smartphone atau aktifkan Device Mode peramban (lebar <= 420px).
  - Di atas tabel terlihat bilah scrollbar horizontal (`h-1.5`) dengan track abu-abu lembut dan thumb oranye amber BPS `#faa938`.
  - Indikator teks di sebelah kanan menunjukkan status geser (*"Geser »"*, persentase posisi, atau *"« Geser kiri"*).
- [ ] **Sinkronisasi Gestur Sentuh & Seek Interaktif**:
  - Geser tabel dengan jari: bilah thumb oranye meluncur mulus mengikuti pergerakan tabel secara realtime.
  - Ketuk atau geser langsung bilah scrollbar: tabel langsung melompat (*smooth scroll*) ke posisi kolom yang dipilih.
  - Pada peramban desktop, scrollbar bawah tabel memiliki ketebalan 8px dengan kontras tinggi sehingga mudah dilihat dan digeser.

### P. Uji Fitur Dropdown Pencarian Pegawai & Tim (Tampil Penuh Otomatis & Ganti Cepat 1-Langkah)
- [ ] **Pembukaan Daftar Lengkap Otomatis Saat Dropdown Diklik (Single-Step Switching)**:
  - Buka halaman Persetujuan Pengajuan (Ketua Tim, Pimpinan, atau Admin), atau halaman Monitoring Lembur (`/admin/lembur`).
  - Pilih salah satu pegawai (misal: "Pegawai A"). Halaman me-reload dan kotak pencarian terisi nama Pegawai A.
  - Klik kembali kotak input pencarian: dropdown langsung terbuka menyajikan **SELURUH** nama pegawai secara lengkap (tidak terfilter sendiri hanya menjadi Pegawai A).
  - Pengguna dapat langsung mengklik *Pegawai C* dalam 1 kali klik tanpa harus mereset ke *"Semua pegawai"* terlebih dahulu.
- [ ] **Fitur Auto-Select Teks untuk Pengetikan Instan**:
  - Saat kotak input pencarian yang sudah terisi diklik/difokuskan, teks nama pegawai yang lama otomatis terblok/terseleksi (`select()`).
  - Ketik huruf baru (misal huruf `'C'`): teks nama lama langsung terhapus otomatis dan dropdown seketika memfilter nama yang mengandung huruf `'C'`.
- [ ] **Tombol Clear Cepat `(×)`**:
  - Saat ada nama pegawai/tim yang sedang terpilih, tombol silang `(×)` muncul di sebelah kanan input.
  - Klik tombol `(×)`: filter seketika ter-reset ke *"Semua"* dalam 1 kali klik.
- [ ] **Sorotan Visual Centang & Tombol Panah Buka/Tutup**:
  - Item pegawai/tim yang sedang aktif ditandai dengan latar belakang oranye lembut dan ikon centang resmi BPS.
  - Klik tombol panah bawah (chevron): dropdown dapat dibuka-tutup secara fleksibel.
  - Klik di luar area kotak pencarian: dropdown tertutup rapi dan nama aktif tetap utuh tanpa merusak pencarian.

### Q. Uji Modul Manajemen User & Hak Akses (Superadmin Only)
- [ ] **Visibilitas Menu Sidebar Berbasis Peran**:
  - Login sebagai `admin`: Pastikan menu **Manajemen User** di sidebar TIDAK MUNCUL. Coba akses langsung URL `/admin/manajemen-user`: sistem langsung memblokir dengan status `403 Forbidden`.
  - Login sebagai `superadmin`: Menu **Manajemen User** muncul di sidebar (kelompok *Master*). Akses halaman: antarmuka terbuka sempurna.
- [ ] **Pengujian Pergantian Kepala Bagian Umum**:
  - Buka menu Manajemen User, klik tombol **Ganti Kepala Bagian Umum**.
  - Pilih salah satu pegawai baru dan tentukan tahun SK/periode berjalan.
  - Klik simpan: periksa notifikasi sukses.
  - Verifikasi: profil Kabag Aktif langsung berganti ke nama pegawai baru, Tim Kerja Bagian Umum di `m_tim` ketuanya otomatis terupdate, dan menu shortcut login di halaman depan langsung menampilkan nama Kabag baru.
- [ ] **Pengujian Penambahan & Pencabutan Admin Lembur**:
  - Klik **Tambah Admin**, pilih pegawai berstatus `user`, klik konfirmasi: pegawai berhasil naik menjadi `admin`.
  - Klik tombol **Cabut** pada baris admin tersebut: muncul konfirmasi dialog. Setelah disetujui, hak admin dicabut dan status pegawai kembali ke `user`.
- [ ] **Pengujian Penambahan Superadmin & Proteksi Permanen**:
  - Klik **Tambah Superadmin**: modal terbuka dengan **kotak peringatan tegas**: *"beneran mau ngasih superadmin, nanti gabisa dicabut lagi"*.
  - Tombol simpan terkunci (disabled) hingga *checkbox* persetujuan permanen dicentang.
  - Setelah dicentang dan disimpan: pegawai sukses dipromosikan ke `superadmin`.
  - Verifikasi: tidak ada tombol hapus/cabut untuk superadmin (*superadmin tidak bisa mencabut sesama superadmin*), dan dropdown role di tabel `admin/pengguna` terkunci untuk akun superadmin.

### R. Uji Single Active Pejabat, Suksesi PPK Dinamis, & Parameter Dokumen
- [ ] **Uji Pejabat Aktif Tunggal (Invarian 1 Kabag Umum & 1 PPK Aktif)**:
  - Buka phpMyAdmin atau menu Master Pejabat: periksa status pejabat. Pastikan hanya ada 1 pejabat yang berstatus `aktif` untuk `Kepala Bagian Umum` dan 1 untuk `PPK`.
  - Jika seorang pejabat baru diaktifkan, pejabat lama otomatis ter-set `nonaktif`.
- [ ] **Uji Pergantian PPK di Manajemen User**:
  - Login sebagai `superadmin` dan buka `/admin/manajemen-user`.
  - Di seksi **Pejabat Pembuat Komitmen / PPK (Aktif)**, klik tombol **Ganti PPK**.
  - Pilih pegawai dari daftar pencarian dan masukkan tahun periode SK.
  - Klik simpan: profil PPK aktif langsung diperbarui, dan PPK terdahulu masuk ke daftar riwayat arsip.
- [ ] **Uji Parameter Penandatangan Dokumen (SPKL, Laporan, & Daftar Hadir)**:
  - Buka menu Dokumen (`/admin/dokumen`).
  - Klik tombol **Generate Dokumen**: modal menyajikan pilihan penandatangan Kepala Bagian Umum dan PPK (khusus SPKL) dengan default pejabat aktif.
  - Coba generate SPKL: periksa berkas PDF/XLSX yang dihasilkan, nama PPK di sisi kiri dan nama Kabag Umum di sisi kanan otomatis sesuai pejabat terpilih.
  - Coba generate Laporan Lembur: periksa nama Kabag Umum pada tanda tangan otomatis sesuai pejabat terpilih.
  - Buka menu Daftar Hadir (`/admin/daftar_hadir`) dan unduh PDF: tanda tangan mengetahui Kepala Bagian Umum otomatis mengikuti pejabat aktif (atau parameter URL `?kbu=...`).

### S. Uji Format Laporan Hasil Kerja Lembur (1 Baris per Orang per Tanggal Sesuai Standar BPS)
- [ ] **Uji Generate Laporan PDF (`dokumen.laporan`)**:
  - Buka menu Dokumen (`/admin/dokumen`).
  - Klik tombol **Generate** atau tautan *Generate PDF* pada kolom Laporan untuk salah satu periode yang memiliki data lembur approved.
  - Buka dokumen PDF hasil unduhan:
    - Periksa header tabel: `No`, `Nama Pegawai / NIP`, `Tanggal`, dan `Uraian Kegiatan`.
    - Pastikan jika seorang pegawai lembur beberapa hari dalam sebulan, setiap tanggal lembur muncul **1 baris tersendiri** (bukan digabung dalam 1 baris).
    - Kolom `Tanggal` menyajikan angka tanggal yang berpusat di tengah (*align center*).
    - Kolom `Nama Pegawai / NIP` menampilkan nama pegawai dan NIP BPS secara rapi.
- [ ] **Uji Generate Laporan Excel XLSX (`LaporanExport`)**:
  - Pada halaman dokumen, generate atau unduh Laporan format **XLSX**.
  - Buka berkas Excel:
    - Baris 3 menyajikan header kolom: `No`, `Nama Pegawai / NIP`, `Tanggal`, dan `Uraian Kegiatan`.
    - Format kolom B: `{Nama} / {NIP BPS}` (misal: `Joko Suwarjo,S.Si, M.Si / 340013741`).
    - Format kolom C: Angka tanggal (`1`, `2`, `6`, ...) dengan perataan tengah (*center*).
    - Uraian kegiatan tersusun rapi dengan wrap text dan bullet point.
    - Nomor urut berlanjut urut: 1, 2, 3, 4, 5...

### T. Uji Tombol & Icon Cetak A4 Otomatis di Menu Daftar Hadir Admin
- [ ] **Uji Tombol Cetak A4 di Header Daftar Hadir**:
  - Login sebagai `admin` atau `superadmin`, lalu buka menu **Daftar Hadir** (`/daftar-hadir`).
  - Periksa di samping tombol **Unduh PDF** terdapat tombol berikon printer **Cetak A4**.
  - Klik tombol **Cetak A4**: muncul dropdown pilihan kategori pegawai (*Cetak Hadir (PNS)* dan *Cetak Hadir (PPPK)*) dengan penanda *Auto Print*.
- [ ] **Uji Dialog Cetak Otomatis & Orientasi Kertas A4**:
  - Klik salah satu opsi (misal: *Cetak Hadir (PNS)*): tab peramban baru akan terbuka memuat tampilan dokumen resmi presensi lembur.
  - Periksa dialog cetak bawaan browser (*print dialog*) muncul **secara otomatis**.
  - Pastikan orientasi kertas default adalah **Portrait** dan ukuran kertas default adalah **A4**.
  - Periksa bahwa tanda tangan digital pegawai dan nama Kepala Bagian Umum aktif tercetak dengan jelas dan rapi.

### U. Uji Perbaikan Format Laporan Lembur, Tombol Cetak Daftar Hadir, Jam Pulang Riil, & Rekapitulasi (PNS/PPPK/Semua)
- [ ] **Uji NIP Baru 18 Digit & Layout PDF Anti-Cut Off Laporan Lembur**:
  - Buka menu Dokumen (`/admin/dokumen`), pilih periode dan generate **Laporan PDF**.
  - Periksa baris pegawai: nomor NIP yang tercetak di bawah nama adalah **NIP Baru (18 digit)** (dengan fallback NIP lama jika belum diisi).
  - Periksa bila uraian pekerjaan panjang atau halaman berganti: layout A4 portrait mengalir rapi tanpa terpotong di tepi bawah (*anti-cut off page break*), dan blok tanda tangan tetap utuh di lembar terakhir.
- [ ] **Uji Tombol Cetak & Jam Pulang Riil Daftar Hadir**:
  - Buka menu **Daftar Hadir** (`/daftar-hadir`): tombol printer bertuliskan **Cetak** (tanpa tulisan "A4").
  - Periksa kolom **Jam Pulang**: menampilkan jam scan kepulangan riil pegawai dari `t_presensi`. Apabila pegawai belum melakukan presensi pulang atau data tidak ada, tampil tanda strip `-`.
  - Verifikasi bahwa jam pulang riil ini tampil konsisten di tabel web admin, dokumen unduh PDF landscape, dan halaman cetak browser.
- [ ] **Uji Filter Kategori & Dropdown Unduh Rekapitulasi Lembur**:
  - Buka menu **Rekapitulasi Lembur** (`/admin/rekapitulasi`): toolbar menyajikan dropdown filter kategori (**Semua Pegawai**, **PNS**, **PPPK**).
  - Pilih **PNS**: tabel otomatis menyaring hanya pegawai PNS.
  - Pilih **PPPK**: tabel otomatis menyaring hanya pegawai PPPK.
  - Klik tombol **Unduh Rekap**: menu dropdown muncul menyajikan 3 opsi:
    1. **Unduh Semua Pegawai** ➔ Menghasilkan file `Rekapitulasi_Lembur_{periode}.xlsx`.
    2. **Unduh Rekap PNS** ➔ Menghasilkan file `Rekapitulasi_Lembur_PNS_{periode}.xlsx`.
    3. **Unduh Rekap PPPK** ➔ Menghasilkan file `Rekapitulasi_Lembur_PPPK_{periode}.xlsx`.
  - Buka berkas Excel hasil unduhan masing-masing opsi untuk memastikan data pegawai dan judul worksheet sesuai dengan kategori yang dipilih.

### V. Uji Logout Sesi Kedaluwarsa & Proteksi Anti-419
- [ ] **Uji Tombol Logout Bebas Error 419**:
  - Login ke aplikasi dan biarkan beberapa saat hingga token sesi basi, atau hapus cookie sesi di DevTools Application/Storage.
  - Klik tombol **Logout** di navbar profil kanan atas.
  - Verifikasi: pengguna **langsung dialihkan secara mulus ke halaman login** (`/login`) tanpa pernah memunculkan layar hitam `419 | PAGE EXPIRED`.
- [ ] **Uji Akses Langsung URL `/logout`**:
  - Ketik atau refresh alamat URL `https://domain-kantor/logout` di address bar browser.
  - Verifikasi: sistem mengeksekusi flush session dan mengarahkan kembali ke `/login` tanpa error `405 Method Not Allowed`.

### W. Uji Penyesuaian Role Pak Joko Suwarjo (Ketua Tim / Kabag Umum, Non-Admin)
- [ ] **Uji Role Pak Joko di Database & Sistem**:
  - Jalankan kueri `SELECT nama, nip, role FROM m_pegawai WHERE nip = '197106131993121001'`: role tercatat sebagai `ketua_tim`.
  - Login sebagai Pak Joko: sistem mengarahkan ke Dashboard Ketua Tim (`/ketua-tim/dashboard`).
  - Periksa sidebar Pak Joko: tampil menu khusus **Persetujuan Kabag** (`/kabag-umum/pengajuan`) beserta lencana notifikasi pengajuan masuk.
  - Periksa menu **Manajemen User** (`/admin/manajemen-user`): Pak Joko tercatat sebagai Kepala Bagian Umum aktif di Seksi 1, dan tidak lagi tercantum di daftar Admin Lembur operasional di Seksi 2.
- [ ] **Uji 2 Admin Aktif Terdaftar**:
  - Jalankan kueri `SELECT nama, nip, role FROM m_pegawai WHERE role = 'admin'`.
  - Pastikan yang terdaftar tepat **2 orang**: Mbak Rizki Dianing Wardhani SST (Admin Operasional) dan Ibu Suci Budi Utami SST, M.Si. (PPK dengan wewenang verifikasi admin).



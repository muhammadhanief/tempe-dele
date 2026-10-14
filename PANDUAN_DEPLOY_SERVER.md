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
14. 📄 **`app/Http/Controllers/admin/DokumenGenerateController.php`** *(Pengelompokan Laporan per orang per tanggal asc, NIP Baru, dan regenerasi update dinamis tanpa blok error)*.
15. 📄 **`resources/views/dokumen/laporan.blade.php`** *(Header `Tanggal`, nilai tanggal normal tanpa bold, dan NIP Baru 18 digit)*.
16. 📄 **`app/Exports/LaporanExport.php`** *(Ekspor Excel Laporan per orang per tanggal asc, header Tanggal)*.
17. 📄 **`resources/views/dokumen/spkl.blade.php`** *(SPKL PDF layout natural flow DomPDF 2 halaman, NIP Baru 18 digit, dan tanggal lembur terurut koma)*.
18. 📄 **`app/Exports/SpklExport.php`** *(SPKL XLSX dengan NIP Baru 18 digit dan tanggal terurut koma)*.
19. 📄 **`resources/views/dokumen/daftar_hadir.blade.php`** *(Unduh PDF Daftar Hadir dengan @page A4 dan penegasan NIP KBU)*.
20. 📄 **`app/Console/Commands/ResetTransaksiCommand.php`** *(Command artisan pembersih data pengajuan testing `php artisan lembur:reset-transaksi`)*.
21. 📄 **`app/Console/Commands/ImportCleanDbCommand.php`** *(Command artisan impor database riil mentor `php artisan lembur:import-clean-db`)*.
22. 📄 **`database/seeders/TestingLemburSeeder.php`** *(Seeder data transaksi testing realistis `php artisan db:seed --class=TestingLemburSeeder`)*.
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
52. 📄 **`resources/views/partials/navbar.blade.php`** *(Penambahan class `cursor-pointer` pada tombol trigger profil dan tombol logout, serta deteksi dinamis label Kabag Umum)*.
53. 📄 **`resources/views/login.blade.php`** *(Penambahan class `cursor-pointer` pada tombol Masuk dan tombol sakelar lihat sandi, serta penutupan tag div grid)*.
54. 📄 **`resources/css/app.css`** *(Penambahan aturan global universal button `cursor: pointer;`)*.
55. 📁 **`public/build/`** *(Aset bundle frontend produksi Vite terbaru: manifest.json, app-DjJofEQ_.css, app-DbISuP7_.js)*.
56. 📄 **`resources/views/admin/lembur.blade.php`** *(Penghapusan tag penutup div duplikat pada `#wrapSearchTim` yang sebelumnya menyebabkan footer melayang ke samping navbar)*.
57. 📄 **View Lain yang Dirapikan Tag Penutup Div-nya**: `resources/views/ketua-tim/lembur.blade.php`, `resources/views/akumulasi.blade.php`, `resources/views/admin/riwayat_presensi.blade.php`.
58. 📄 **`app/Http/Controllers/admin/DokumenViewController.php`** *(Dukungan filter query bulan opsional, flag `$isFiltered`, dan default menampilkan semua periode)*.
59. 📄 **`resources/views/admin/dokumen.blade.php`** *(Penyempurnaan label default 'Filter Bulan', tombol reset filter `(×)`, tombol 'Semua Bulan' di panel kalender, serta empty state tabel)*.
60. 📄 **`resources/views/partials/sidebar.blade.php`** *(Batasan tinggi viewport `h-[100dvh]`, `min-h-0`, padding bawah `pb-16`, cursor pointer tombol close, dan pengunci scroll body saat drawer terbuka)*.
61. 📄 **`resources/css/app.css`** *(Styling dark scrollbar `.sidebar-scroll` untuk scrolling halus sidebar di smartphone, isolasi flexbox `.overflow-x-auto:not(.flex):not(.inline-flex)`, dan utilitas `.no-scrollbar`)*.
62. 📄 **`resources/views/admin/lembur.blade.php`** *(Header kompak mobile, tombol accordion filter pencarian, implementasi Dropdown Status khusus mobile `sm:hidden`, dan proteksi badge pills desktop `hidden sm:flex`)*.
63. 📄 **`resources/views/admin/dashboard.blade.php`** *(Banner hero horizontal ramping di mobile `flex-row`, tinggi ~110px, dan proteksi layout desktop `lg:`)*.
64. 📄 **`resources/views/dashboard.blade.php`** *(Banner hero horizontal ramping di mobile untuk dashboard Pegawai)*.
65. 📄 **`resources/views/ketua-tim/dashboard.blade.php`** *(Banner hero horizontal ramping di mobile untuk dashboard Ketua Tim)*.
66. 📄 **`resources/views/pimpinan/dashboard.blade.php`** *(Banner hero horizontal ramping di mobile untuk dashboard Pimpinan)*.
67. 📄 **`resources/views/admin/lembur.blade.php`** *(Penyempurnaan menu popup dropdown status mobile kustom dengan tipografi modern Inter/Sans, badge counter, indikator pulsing dot, dan listener tunggal anti-double-click)*.
68. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Metode Full Isolation: Toolbar desktop asli `hidden sm:flex` 100% utuh tanpa perubahan, Toolbar mobile `block sm:hidden` 2 kolom 50%-50% Bulan & Status, dan pemisahan ID JavaScript)*.
69. 📁 **`public/build/`** *(Bundel produksi Vite: manifest.json, app-9gWrLdJH.css, app-DbISuP7_.js)*.
70. 📄 **`resources/views/dashboard.blade.php`** *(Redesain hero banner Dashboard Pegawai: tata letak Flexbox 2 kolom anti-tabrakan, ambient lighting glow, badge frosted glass, dan tipografi adaptif)*.
71. 📄 **`resources/views/admin/dashboard.blade.php`** *(Redesain hero banner Dashboard Admin: tata letak Flexbox 2 kolom anti-tabrakan, ambient lighting glow, badge frosted glass, dan tipografi adaptif)*.
72. 📄 **`resources/views/ketua-tim/dashboard.blade.php`** *(Redesain hero banner Dashboard Ketua Tim: tata letak Flexbox 2 kolom anti-tabrakan, ambient lighting glow, badge frosted glass, dan tipografi adaptif)*.
73. 📄 **`resources/views/pimpinan/dashboard.blade.php`** *(Redesain hero banner Dashboard Pimpinan: tata letak Flexbox 2 kolom anti-tabrakan, ambient lighting glow, badge frosted glass, dan tipografi adaptif)*.
74. 📁 **`public/build/`** *(Bundel produksi Vite terbaru: manifest.json, app-Bjr7u1DZ.css, app-DbISuP7_.js)*.
75. 📄 **`resources/views/dokumen/daftar_hadir_print.blade.php`** *(Template cetak daftar hadir presensi lembur BPS standar A4 portrait, layout tabel bergaris resmi, tanda tangan base64 data URI, dan script auto-print)*.
76. 📄 **`resources/views/admin/daftar_hadir.blade.php`** *(Penambahan tombol/icon printer "Cetak" berdampingan dengan Unduh PDF, dropdown PNS/PPPK, dan penayangan jam pulang riil presensi)*.
77. 📄 **`app/Http/Controllers/admin/DaftarHadirController.php`** *(Method `print()` untuk render cetak A4 dan subquery correlated jam pulang presensi riil `t_presensi`)*.
78. 📄 **`resources/views/dokumen/spkl.blade.php`** *(Perbaikan resolusi path logo kop surat multi-environment dinamis, validasi file_exists, proteksi extension_loaded('gd'), dan fallback kop teks)*.
79. 📄 **`resources/views/dokumen/daftar_hadir.blade.php`** *(Proteksi file_exists sebelum render gambar tanda tangan pegawai di template PDF)*.
80. 📄 **`app/Exports/LaporanExport.php`** *(Pembaruan pengurutan kronologis tanggal dan prioritas NIP Baru 18 digit pada ekspor Laporan Hasil Kerja Lembur Excel XLSX)*.
81. 📄 **`app/Http/Controllers/admin/LaporanController.php`** *(Pembaruan pengurutan kronologis berdasarkan tanggal pada tampilan tabel menu Laporan Admin)*.
82. 📄 **`app/Http/Controllers/admin/DokumenGenerateController.php`** *(Penerapan prioritas NIP Baru 18 digit pegawai pada pembuatan Laporan Hasil Kerja Lembur)*.
83. 📄 **`resources/views/dokumen/laporan.blade.php`** *(Perapihan layout PDF A4 portrait, styling anti-cut off page-break tabel & tanda tangan, dan NIP Baru 18 digit)*.
84. 📄 **`app/Http/Controllers/admin/RekapitulasiController.php`** *(Penyaringan filter kategori PNS/PPPK, proteksi upsert cache agregat, dan penanganan unduh Excel per kategori)*.
85. 📄 **`app/Exports/RekapitulasiExport.php`** *(Penyaringan query PNS/PPPK dan judul worksheet dinamis pada ekspor rekapitulasi Excel)*.
86. 📄 **`resources/views/admin/spkl.blade.php`** *(Dropdown filter kategori pegawai di toolbar dan dropdown tombol unduh dengan opsi Semua, PNS, dan PPPK)*.
87. 📄 **`bootstrap/app.php`** *(Pengecualian CSRF untuk rute logout serta fallback otomatis pengalihan TokenMismatchException / HTTP 419 ke halaman login)*.
88. 📄 **`routes/web.php`** *(Dukungan method ganda GET dan POST pada rute `/logout` serta rute cetak daftar hadir dan manajemen user)*.
89. 📄 **`resources/views/login.blade.php`** *(Penambahan kotak notifikasi alert informasi session error jika sesi kedaluwarsa & toleransi dev-login)*.
90. 📄 **`app/Http/Controllers/admin/ManajemenUserController.php`** *(Controller manajemen user superadmin, suksesi KBU dinamis, promosi admin, dan proteksi role superadmin)*.
91. 📄 **`resources/views/admin/manajemen_user.blade.php`** *(View antarmuka manajemen user superadmin, modal ganti kabag/ppk, dan tambah admin/superadmin)*.
92. 📄 **`app/Http/Controllers/admin/DashboardController.php`** *(Pembaruan metrik statistik tahun berjalan & antrean pending aktif tanpa kunci bulan)*.
93. 📄 **`app/Http/Controllers/ketuatim/DashboardController.php`** *(Pembaruan metrik tim tahun berjalan & antrean pending tim)*.
94. 📄 **`app/Http/Controllers/pegawai/DashboardController.php`** *(Pembaruan metrik pegawai tahun berjalan & inklusi status menunggu_kabag pada kartu Diproses)*.
95. 📄 **`app/Http/Controllers/pimpinan/DashboardController.php`** *(Pembaruan metrik pimpinan tahun berjalan & inklusi status menunggu_kabag pada kartu Diproses)*.
97. 📄 **`app/Http/Controllers/admin/DokumenGenerateController.php`** *(Pembaruan generator SPKL: adopsi NIP Baru 18 digit, deduplikasi tanggal lembur berurutan naik [contoh: "1, 30", "15, 25"], dan pengurutan daftar pegawai berdasarkan tanggal awal lembur)*.
98. 📄 **`resources/views/dokumen/spkl.blade.php`** *(Pembaruan tampilan tabel SPKL PDF: menampilkan NIP Baru 18 digit `{{ $p->nip ?? $p->nip_lama }}` di bawah nama pegawai)*.
99. 📄 **`app/Exports/SpklExport.php`** *(Pembaruan lembar ekspor SPKL Excel: menampilkan NIP Baru 18 digit `$p->nama . "\n" . ($p->nip ?? $p->nip_lama)`)*.
100. 📄 **`resources/views/dokumen/laporan.blade.php`** *(Pembaruan format Laporan Hasil Kerja Lembur PDF acuan resmi BPS: header Tanggal Lembur Bulan ..., nilai tanggal normal [tidak bold], dan styling nama normal weight)*.
101. 📄 **`app/Exports/LaporanExport.php`** *(Pembaruan ekspor Laporan Excel: 1 baris per pegawai, tanggal berurutan menaik, dan header kolom C berlebar proporsional)*.
102. 📄 **`app/Console/Commands/ResetTransaksiCommand.php`** *(Command artisan `lembur:reset-transaksi` untuk mengosongkan transaksi uji coba tanpa merusak data master)*.
103. 📄 **`database/seeders/TestingLemburSeeder.php`** *(Seeder data lembur uji coba dengan uraian dinas riil BPS seperti SE2026, Sakernas, SPJ, dll.)*.
104. 📄 **`app/Console/Commands/ImportCleanDbCommand.php`** *(Command artisan `lembur:import-clean-db` untuk mengimpor dump data riil bersih mentor dan auto-apply update struktur v2)*.
105. 📄 **`app/Http/Controllers/ketuatim/PengajuanController.php`** *(Validasi wajib isi catatan jika jam disetujui diubah/dipotong dari jam pengajuan & pencatatan audit trail `user_edited`/`tanggal_edited`)*.
106. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Validasi wajib isi catatan Kabag jika jam disetujui diubah/dipotong & pencatatan audit trail `user_edited`/`tanggal_edited`)*.
107. 📄 **`app/Http/Controllers/admin/PengajuanController.php`** *(Validasi wajib isi catatan Admin jika jam disetujui diubah & audit trail `user_edited`/`tanggal_edited`)*.
108. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Pencatatan audit trail `user_edited`/`tanggal_edited` pada koreksi jam dan pembatalan, serta validasi catatan)*.
109. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Label bintang merah dinamis dan validasi frontend wajib isi catatan saat jam lembur disesuaikan)*.
110. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Label bintang merah dinamis dan validasi frontend wajib isi catatan Kabag saat jam lembur disesuaikan)*.
111. 📄 **`resources/views/admin/pengajuan.blade.php`** *(Label bintang merah dinamis dan validasi frontend wajib isi catatan Admin saat jam lembur disesuaikan)*.
112. 📄 **`resources/views/lembur.blade.php`** *(Kolom Jam Disetujui, lencana Disesuaikan, dan transparansi catatan terstruktur di sisi pegawai)*.
113. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Kolom Jam Disetujui, lencana Disesuaikan, dan transparansi catatan terstruktur di sisi Ketua Tim)*.
114. 📄 **`resources/views/dashboard.blade.php`**, **`resources/views/admin/dashboard.blade.php`**, **`resources/views/ketua-tim/dashboard.blade.php`**, **`resources/views/pimpinan/dashboard.blade.php`** *(Pengaktifan id="greeting" pada badge hero dan fungsi sapaan waktu dinamis real-time: Selamat Pagi, Siang, Sore, Malam)*.
115. 📄 **`resources/views/admin/pengajuan.blade.php`**, **`resources/views/ketua-tim/pengajuan.blade.php`** *(Redesain toolbar mobile menjadi 2 baris kompak grid 50/50 dan penggantian native select filterStatus menjadi custom floating dropdown card dengan colored status dot & checkmark)*.
116. 📄 **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/lembur.blade.php`** *(Pembersihan tag div penutup ekstra yang menyebabkan teks hak cipta footer terapung di sebelah kanan navbar, dan perbaikan penataan kartu empty state)*.
117. 📄 **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/pengajuan.blade.php`**, **`resources/views/kabag-umum/pengajuan.blade.php`** *(Penyatuan struktur tabel selalu tampil dengan thead utuh, pemindahan empty state ke dalam tbody dengan true responsive centering, dan penerapan table-fixed layout anti auto-fit)*.
118. 📄 **`resources/views/welcome.blade.php`**, **`resources/views/layouts/app.blade.php`** *(Penyelarasan tampilan footer mobile terpusat simetris dan proteksi 100% tata letak desktop)*.
119. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Penerapan mode Visual Timeline Card Elegan: border-l-4 Accent Bar per status, Date Badge ala Kalender Modern ber-header banner gelap, Pill Status pastel ber-ikon SVG, Callout Card jam disetujui, dan animasi micro-hover)*.
120. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Redesain Visual Timeline Card gaya SaaS Minimalis ala Linear/Notion: layout super bersih tanpa garis batas tebal/banner warna, kotak tanggal bergaris melingkar `border border-slate-300 rounded-xl bg-white shadow-2xs` ala foto mentor, garis vertikal aksis menyambung utuh tanpa putus antar-item `-bottom-6`, ring aksis 14px, dan shadow halus)*.
121. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Restorasi Paket Visual Premium Polish: Badge Kalender Modern, Ring Node Glowing, Status Pill Vibrant Gradient, Callout Card disetujui, serta penyempurnaan garis aksis vertikal menyambung tanpa putus `w-0.5 absolute top-0 -bottom-5` dari item teratas hingga terbawah)*.
122. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Pembaruan Desain Visual Timeline Modern Glowing Node Masterpiece: Date Badge Kalender Digital ber-header gelap font mono, Ring Node Glowing 20px berbingkai pastel pulsing dot, Garis aksis menyambung 100% tanpa celah `-bottom-6`, Status Pill Vibrant Gradient, Callout Card jam disetujui, dan elevasi shadow-xl melayang)*.
123. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Penyempurnaan Visual Timeline Simple & Elegan Anti-Clutter: Kartu tanggal putih bersih ber-border halus tanpa header hitam berat, Ring Dot aksis smooth 14px ring-white, Status Pill pastel soft anti-gradient, dan tata letak kartu yang sangat tenang & sejuk di mata)*.
124. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`** *(Implementasi garis vertikal kontinu [`.timeline-stem`] penghubung titik bulatan status timeline [oranye, merah, hijau, biru] tanpa celah/putus, penutupan tag card yang presisi, adaptasi dinamis JavaScript `updateTimelineStems()`, dan pesan kosong `#emptyFilterTimeline`)*.
125. 📁 **`public/build/`** *(Bundel produksi Vite terbaru: `manifest.json`, `assets/app-6pbceLn9.css`, `assets/app-DbISuP7_.js`)*.
126. 📄 **`app/Http/Controllers/ketuatim/PengajuanController.php`** *(Izin pengubahan keputusan/penolakan oleh Ketua Tim selama status transaksi masih `menunggu_kabag` dan belum diproses Kabag Umum)*.
127. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Pembukaan tombol Setujui/Tolak pada modal koreksi saat `menunggu_kabag`, serta penyembunyian jam disetujui jika presensi belum diimport `!$p->has_presensi`)*.
128. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Penghapusan blokir error 422 untuk status `pending` sehingga Kabag Umum dapat langsung menyetujui final pengajuan anggota timnya)*.
129. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Penayangan tombol aksi Setujui untuk status `pending`, dan pembatasan kolom jam disetujui hanya tampil jika `has_presensi = 1`)*.
130. 📄 **`app/Exports/SpklExport.php`** *(Penambahan auto row height `setRowHeight(-1)` pada seluruh baris data SPKL Excel agar uraian kegiatan panjang dinamis dan tidak terpotong)*.
131. 📄 **`resources/views/dokumen/spkl.blade.php`** *(Penyesuaian wrapping teks dan line-height uraian pada SPKL PDF)*.
132. 📄 **`app/Http/Controllers/admin/DaftarHadirController.php`** *(Penyempurnaan subquery pencocokan NIP lama dan NIP baru untuk memastikan jam kepulangan daftar hadir murni presensi riil)*.
133. 📄 **`resources/views/lembur.blade.php`** *(Perbaikan logika direktif Blade `@selected` pada dropdown filter bulan dan perPage, serta penambahan atribut min/max tahun 4 digit pada input tanggal)*.
134. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Penyelarasan `@selected(empty($bulan))` pada opsi Semua Bulan, serta penambahan atribut min/max tahun 4 digit pada input tanggal)*.
135. 📄 **`resources/views/admin/lembur.blade.php`** *(Penambahan atribut min/max tahun 4 digit pada modal ajukan lembur admin)*.
136. 📄 **`resources/views/admin/tim.blade.php`** *(Penambahan atribut min/max tahun 4 digit pada tanggal non-aktif tim)*.
137. 📄 **`resources/views/layouts/app.blade.php`** *(Listener JavaScript global pemangkasan tahun > 4 digit dan auto-enforce min/max input tanggal)*.
138. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Penyempurnaan subquery presensi multi-format NIP 9 & 18 digit)*.
139. 📄 **`app/Http/Controllers/ketuatim/PengajuanController.php`** *(Penyempurnaan subquery presensi multi-format NIP 9 & 18 digit)*.
140. 📄 **`app/Http/Controllers/admin/PengajuanController.php`** *(Penyempurnaan subquery presensi multi-format NIP 9 & 18 digit)*.
141. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Penyempurnaan subquery presensi multi-format NIP 9 & 18 digit)*.
142. 📄 **`app/Http/Controllers/pimpinan/PengajuanController.php`** *(Penyempurnaan subquery presensi multi-format NIP 9 & 18 digit)*.
143. 📄 **`app/Http/Controllers/LemburController.php`** *(Pencocokan multi-format NIP pada koreksiDariPresensi)*.
144. 📄 **`app/Traits/KoreksiLembur.php`** *(Pencocokan multi-format NIP pada pemrosesan koreksi background)*.
145. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Proteksi update DOM AJAX kolom jam disetujui hanya tampil jika presensi sudah diimpor)*.
146. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Proteksi update DOM AJAX kolom jam disetujui hanya tampil jika presensi sudah diimpor)*.
147. 📄 **`resources/views/admin/pengajuan.blade.php`** *(Proteksi kolom tabel & update DOM AJAX jam disetujui berpatokan pada has_presensi)*.
148. 📄 **`app/Http/Controllers/admin/PresensiUploadController.php`** *(Ekstraksi regex tahun/bulan kebal spasi, dukungan override tahun, normalisasi CRLF, dan proteksi `$daysInMonth`)*.
149. 📄 **`resources/views/admin/presensi.blade.php`** *(Pilihan target tahun pada modal unggah presensi dan pengiriman payload tahun)*.
150. 📄 **`PENGUJIAN_BLACKBOX.md`** *(Dokumen pengujian black box komprehensif 66 kasus uji untuk seluruh modul sistem lembur)*.
151. 📄 **`resources/css/app.css`** *(Penetapan user-select: text untuk tabel dan pemulihan seleksi teks pengguna)*.
152. 📄 **`resources/js/app.js`** *(Penghapusan pembajakan mousedown/mousemove drag-scroll untuk pemulihan seleksi teks native)*.
153. 📁 **`public/build/`** *(Bundel hasil build aset Vite terbaru agar perubahan CSS dan JS langsung aktif di produksi)*.
154. 📄 **`app/Http/Controllers/LemburController.php`** *(Validasi backend durasi minimal 2 jam mengembalikan pesan error formulir `jam_selesai` tanpa auto-reject dan tanpa menyimpan entri ke DB)*.
155. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Validasi backend durasi minimal 2 jam pada pengajuan lembur Admin mengembalikan pesan error formulir)*.
156. 📄 **`resources/views/lembur.blade.php`** *(Validasi frontend modal ajukan & edit lembur: kalkulasi durasi < 2 jam memunculkan kotak peringatan merah, teks estimasi merah, tombol Kirim terkunci, serta perbaikan label catatan sistem `SISTEM:`)*.
157. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Penyempurnaan label catatan sistem transparan berbadge abu-abu netral `SISTEM:` pada tabel dan timeline)*.
158. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(Penyempurnaan label catatan sistem transparan `SISTEM:` pada tabel pengajuan Kabag)*.
159. 📄 **`PENGUJIAN_BLACKBOX.md`** *(Pembaruan skenario uji TC-PEG-03 untuk pencegahan durasi < 2 jam client & server side)*.
160. 📄 **`app/Http/Controllers/LemburController.php`** *(Passing query `$existingActiveDates` ke view dan validasi backend anti-dobel pengajuan lembur pada tanggal yang sama per pegawai)*.
161. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Passing query `$existingActiveDates` ke view dan validasi backend anti-dobel pengajuan lembur pada tanggal yang sama oleh admin)*.
162. 📄 **`resources/views/lembur.blade.php`** *(Pemeriksaan real-time tanggal dobel sisi klien, peringatan `#errorTanggalDobel`, disabling tombol `#btnSubmitAjukan`, dan submit guard)*.
163. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Pemeriksaan real-time tanggal dobel sisi klien, peringatan `#errorTanggalDobel`, disabling tombol `#btnSubmitAjukan`, dan submit guard)*.
164. 📄 **`resources/views/admin/lembur.blade.php`** *(Pemeriksaan real-time tanggal dobel sisi klien, peringatan `#errorTanggalDobel`, disabling tombol `#btnSubmitAjukan`, dan submit guard)*.
165. 📁 **`public/build/`** *(Bundel produksi Vite terbaru agar perubahan CSS dan JS langsung aktif di produksi)*.
166. 📄 **`app/Http/Controllers/LemburController.php`** *(Proteksi peladen ketat update pending-only, batas durasi <= 12 jam, dan inspeksi piksel goresan tanda tangan `isBlankSignature`)*.
167. 📄 **`app/Http/Controllers/admin/LemburController.php`** *(Batas durasi <= 12 jam dan inspeksi piksel tanda tangan `isBlankSignature` pada pengajuan Admin)*.
168. 📄 **`resources/views/lembur.blade.php`** *(Dukungan lembur lintas tengah malam, indikator 🌙, stroke retention saat kanvas di-resize, dan visual error highlight kanvas)*.
169. 📄 **`resources/views/ketua-tim/lembur.blade.php`** & **`resources/views/admin/lembur.blade.php`** *(Dukungan lembur lintas tengah malam dan penanganan kanvas tanda tangan)*.
170. 📄 **`PENGUJIAN_BLACKBOX.md`** *(Penambahan kasus uji TC-PEG-12, TC-PEG-13, dan TC-PEG-14, total 70 kasus uji 100% PASS)*.
171. 📄 **`resources/js/app.js`** *(Pencegahan layar pemuat beku/stuck overlay melalui evaluasi `e.defaultPrevented` & `checkValidity`, deferral submit microtask, pemaparan `window.hideLoading`, filter link download, dan auto-hide failsafe 12 detik)*.
172. 📄 **`resources/views/layouts/app.blade.php`** *(Penyediaan fungsi fallback inline `window.hideLoading` dan `window.showLoading` serta penutupan darurat pemuat melalui tombol keyboard Escape)*.
173. 📄 **`resources/views/lembur.blade.php`** *(Penghentian propagasi submit event dan pemanggilan eksplisit `hideLoading()` saat validasi tanda tangan/durasi/tanggal gagal)*.
174. 📄 **`resources/views/ketua-tim/lembur.blade.php`** & **`resources/views/admin/lembur.blade.php`** *(Penghentian propagasi submit event dan pemanggilan eksplisit `hideLoading()` saat validasi formulir sisi klien gagal)*.
175. 📁 **`public/build/`** *(Bundel produksi Vite terbaru agar pembaruan JavaScript langsung aktif di server produksi)*.
176. 📄 **`resources/views/lembur.blade.php`** *(Standardisasi tata letak modal `#modalAjukan`: fixed header dengan ikon oranye dan teks bantuan, scrollable form body `max-h-[90vh]` internal scrolling, compact width `max-w-2xl`, tombol hapus tanda tangan sejajar di samping label, dan fixed footer dengan tombol Batal dan Kirim Pengajuan selalu tampak di layar tanpa terpotong)*.
177. 📄 **`resources/views/admin/lembur.blade.php`** *(Standardisasi tata letak modal `#modalAjukan` Admin ke pola fixed header, scrollable body `max-h-[90vh]`, dan pinned footer)*.
178. 📄 **`resources/views/ketua-tim/lembur.blade.php`** *(Penyempurnaan kartu estimasi durasi berbadge warna dinamis pada form pengajuan lembur Ketua Tim)*.
179. 📁 **`public/build/`** *(Bundel produksi Vite terbaru: `app-BWQ3dq7l.css` & `app-DfUGke97.js`)*.
180. 📄 **`app/Http/Controllers/LemburController.php`** *(Penyaringan status aktif `where('mt.status', 'aktif')` & `distinct()` pada `timPegawai()` untuk mencegah nama tim dobel; penanganan parameter fleksibel `bulan` & `tahun`, kueri multi-tahun, dan ekstraksi `$daftarTahun` dinamis)*.
181. 📄 **`resources/views/lembur.blade.php`** *(Pemisahan dropdown `#filterBulan` hanya menampilkan nama bulan bersih tanpa tahun, dan penambahan dropdown mandiri `#filterTahun` dengan opsi default Semua Tahun)*.
182. 📄 **`resources/views/partials/sidebar.blade.php`** *(Arsitektur Collapsible Sidebar berbasis width-transition desktop `w-64` $\rightarrow$ `w-0`, container isolasi `#sidebar-inner`, Edge Tab kecil melengkung menempel di tepi kanan luar sidebar `#sidebar-edge-close-tab` dengan ikon `<` dan tooltip Tutup Menu, Floating Drawer Tab buka menu gaya Canvas LMS `#sidebar-floating-tab`, dan pemisahan bersih mobile drawer)*.
183. 📄 **`resources/views/partials/navbar.blade.php`** *(Pemulihan judul navbar `@yield('title')` untuk nama modul terstruktur dan mempertahankan tombol toggle hamburger khusus mobile `lg:hidden`)*.
184. 📄 **`resources/views/layouts/app.blade.php`** *(Skrip sinkron pre-render di tag `<head>` untuk deteksi `sidebar_collapsed` di `localStorage` anti-flicker)*.
185. 📄 **`resources/views/lembur.blade.php`** *(Pengembalian deklarasi tabel `min-w-[1280px]`, padding lega `px-4 py-3.5`, pembersihan tag penutup `</td>`, serta penyesuaian `@section('title', 'Lembur')` agar hierarki nama modul tidak dobel dengan header halaman)*.
186. 📄 **`resources/views/lembur.blade.php`** *(Penataan ulang proporsi kolom tabel riwayat lembur pegawai dengan persentase terkalibrasi 100% [9.4%, 10%, 10%, 15.6%, 12%, 12.5%, 10.6%, 9.9%, 5.2%, 4.8%], padding sel ramping rapi, perbaikan double plus tombol dokumentasi, dan penyesuaian status badge agar pas 100% tanpa celah renggang dan tanpa overlap)*.
187. 📄 **`resources/views/lembur.blade.php`** *(Penyeragaman tinggi seluruh baris tabel riwayat lembur pegawai menggunakan h-16 [64px] dan align-middle, line-clamp teks 2 baris pada Uraian, Ketua, Tim, dan Catatan, serta implementasi Modal Pop-up Detail Uraian Kegiatan lengkap dengan metadata tanggal, jam pengajuan & disetujui, ketua, tim kerja, status pengajuan, serta catatan terstruktur)*.
188. 📄 **`resources/views/lembur.blade.php`** *(Pembaruan kolom Dokumentasi lengkap tanpa singkatan, tombol + Dokumentasi oranye amber, tautan eksternal Lihat dengan hapus terproteksi, penyederhanaan tombol kolom Aksi menjadi ikon pensil mandiri 28x28px tanpa teks Ubah, serta proteksi guard anti double-encode Base64 b64: pada submit listener dokumen)*.
189. 📄 **`resources/views/lembur.blade.php`** *(Kalibrasi persentase kolom thead anti-tabrakan [8.5%, 9.5%, 9.5%, 15.0%, 12.0%, 13.0%, 10.5%, 8.5%, 9.0%, 4.5%], pembersihan baris ketiga teks lihat detail pada uraian kegiatan menjadi ikon pop-up inline tanpa baris ganjil, dan penataan font 11px tracking-tight agar tabel 100% fit dan rapi baik saat sidebar terbuka maupun tertutup)*.
190. 📄 **`resources/views/lembur.blade.php`** *(Penyempurnaan tampilan responsif mobile halaman lembur: reflow filter toolbar ke grid 2 kolom simetris pada layar seluler, batas lebar tabel min-width 1020px anti-tabrakan kolom jam dan teks uraian, sinkronisasi indikator geser horizontal real-time 0%-100%, serta inisialisasi default cerdas mode timeline pada layar smartphone < 640px)*.
191. 📄 **`resources/views/lembur.blade.php`** *(Penerapan kolom Tanggal sticky solid ber-border-separate yang terkunci di kiri layar saat tabel digeser, penambahan tombol navigasi cepat ‹ dan ›, mouse drag-to-scroll, dan draggable progress thumb)*.
192. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Redesign Tabel Persetujuan Pengajuan Ketua Tim: Layout Desktop Pas 100% tanpa scroll horizontal dengan colgroup proporsional [4%, 19%, 13%, 9%, 9%, 20%, 9%, 9%, 8%], penyederhanaan header Tanggal, Kolom No dan Nama Pegawai Sticky Solid di mobile [left-0 dan left-12] dengan batas vertikal dan bayangan elevasi halus, pembatasan uraian ke line-clamp-2 h-16 berseragam, implementasi modal pop-up interaktif detail uraian tugas lengkap dengan status dan catatan, serta sinkronisasi realtime atribut baris data setelah keputusan disimpan)*.
193. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Standardisasi tombol Aksi [Proses & Koreksi] di tabel dan linimasa menjadi tombol icon-only h-7 w-7 / h-8 w-8 tanpa teks label dengan tooltip title dan sinkronisasi AJAX real-time)*.
194. 📄 **`resources/views/admin/lembur.blade.php`** *(Standardisasi tombol Aksi di tabel monitoring lembur admin menjadi tombol icon-only h-7 w-7 terpusat tanpa teks label Aksi)*.
195. 📄 **`resources/views/admin/pengajuan.blade.php`** *(Standardisasi tombol aksi kartu linimasa dan script keputusan AJAX menjadi tombol icon-only h-8 w-8 dengan tooltip title dinamis)*.
196. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Standarisasi lebar nyaman tabel pengajuan ketua tim min-w-[1140px] dan rebalancing colgroup: eliminasi tabrakan lencana jam disetujui hijau dan uraian di halaman 2, pelonggaran nama pegawai dan presensi, serta pemisahan bersih lencana status dari tombol aksi)*.
197. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Kalibrasi layout tabel pengajuan ketua tim fit 100% desktop tanpa scroll horizontal [lg:min-w-full], distribusi colgroup presisi 100% [3.5%, 17%, 10.5%, 10.5%, 11.5%, 20%, 9%, 12.5%, 5.5%], padding sel rapat rapi px-1 / px-1.5 py-2, standarisasi lencana jam disetujui hijau text-[10.5px] px-1.5 py-0.5 bebas tabrakan, tombol presensi & badge status proporsional, serta sinkronisasi AJAX real-time)*.
198. 📄 **`resources/views/ketua-tim/pengajuan.blade.php`** *(Penataan bertingkat vertikal flex-col header kolom Tanggal: teks Tanggal di atas dan dropdown sortir [Terbaru ▾] di bawahnya, mengeliminasi tabrakan horizontal dengan header Jam Diajukan)*.
199. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/admin/lembur.blade.php`** *(Restrukturisasi tata letak modal #modalAjukan menjadi 2-kolom desktop md:grid-cols-2 zero-scroll: max-w-3xl lg:max-w-4xl max-h-[92vh], textarea uraian rows=3, kanvas tanda tangan h-28 touch-none, mengeliminasi scrollbar vertikal pada layar laptop/desktop dengan fallback responsif 1-kolom di mobile)*.
200. 📁 **`public/build/`** *(Bundel produksi Vite terbaru hasil `npm run build`: `manifest.json`, `assets/app-CgN_TPC-.css`, `assets/app-DfUGke97.js` memuat seluruh utility Tailwind CSS termutakhir dari 30+ commit terakhir)*.
201. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`**, **`resources/views/kabag-umum/pengajuan.blade.php`** *(Pencarian menyeluruh [nama, NIP, tim, ketua] melintasi seluruh data periode aktif, dukungan perPage 10/25/50/100, sinkronisasi tab status, dan restrukturisasi baris paginasi bawah 3 bagian: Tampilkan data, Page X of Y, Menampilkan X-Y dari Z data)*.
202. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`**, **`resources/views/kabag-umum/pengajuan.blade.php`** *(Peningkatan reaktivitas pencarian dan paginasi Persetujuan Kabag Umum menjadi ultra-smooth in-memory reactive engine 0ms: live keystroke filtering di sisi klien khusus Nama & NIP Pegawai [tanpa nama ketua/tim], paginasi tombol [<] [>] instan, sinkronisasi timeline card, dan silent URL replaceState).*
203. 📄 **`resources/views/dashboard.blade.php`**, **`resources/views/admin/dashboard.blade.php`**, **`resources/views/ketua-tim/dashboard.blade.php`**, **`resources/views/pimpinan/dashboard.blade.php`** *(Standarisasi tombol "Lihat semua" pada seluruh kartu dashboard dengan penambahan `shrink-0`, `whitespace-nowrap`, `min-w-0 flex-1`, dan padding proporsional `px-4 py-2 text-sm` untuk menjamin bentuk pil konsisten dan bebas wrapping/tergencet di berbagai ukuran layar dan kondisi sidebar terbuka/tertutup).*
204. 📄 **`app/Http/Controllers/LemburController.php`**, **`resources/views/lembur.blade.php`** *(Izin pengeditan mandiri pengajuan lembur anggota Tim Bagian Umum pada status `menunggu_kabag` selama belum disetujui Kabag Umum, dan sinkronisasi tampilan tombol aksi edit kuning di tabel & linimasa).*
205. 📄 **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/ketua-tim/pengajuan.blade.php`**, **`resources/views/kabag-umum/pengajuan.blade.php`**, **`resources/views/admin/pengajuan.blade.php`** *(Standarisasi ketebalan font teks uraian kegiatan pada kartu linimasa, label sub-seksi, dan judul modal detail dari `font-bold` menjadi `font-semibold` di seluruh role untuk meningkatkan kenyamanan visual dan keterbacaan antarmuka).*
206. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`**, **`resources/views/ketua-tim/pengajuan.blade.php`**, **`resources/views/admin/pengajuan.blade.php`**, **`resources/views/lembur.blade.php`**, **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/admin/lembur.blade.php`** *(Standarisasi format tampilan catatan transaksi dibatalkan oleh Admin dengan menyajikan label khusus 'Admin:' berkas warna rose lembut dan membersihkan prefix teks teknis `[Dibatalkan Admin]` pada tabel, linimasa, dan modal detail di seluruh role dengan tetap menjaga catatan historis Kabag Umum).*
207. 📄 **`app/Http/Controllers/LemburController.php`**, **`resources/views/ketua-tim/lembur.blade.php`**, **`resources/views/partials/sidebar.blade.php`** *(Standarisasi antarmuka pengajuan lembur pribadi Ketua Tim dan Kepala Bagian Umum menggunakan `lembur.blade.php` penuh dengan fitur multi-tahun, pencarian tim, modal ajukan 2-kolom zero-scroll, modal edit, dan timeline responsif, serta penyesuaian rute aktif bilah sisi).*
208. 📄 **`app/Http/Controllers/admin/LemburController.php`**, **`resources/views/admin/lembur.blade.php`**, **`app/Exports/LemburExport.php`** *(Perbaikan filter tab status cancelled/dibatalkan pada controller monitoring lembur admin, dukungan badge status cancelled dan dibatalkan pada tabel, serta pemetaan warna dan label status pada ekspor spreadsheet Excel).*



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

### Q. Uji Properti Kursor Pointer Tombol (Logout, Masuk, & Global Button)
- [ ] **Tombol Masuk & Toggle Sandi (`/login`)**:
  - Arahkan kursor mouse ke tombol utama **Masuk** ➔ pastikan kursor berubah menjadi **pointer** (ikon jari/tangan).
  - Arahkan kursor mouse ke ikon mata penampil sandi ➔ pastikan kursor berubah menjadi **pointer**.
- [ ] **Tombol Logout di Navbar**:
  - Klik foto/nama profil di pojok kanan atas untuk membuka dropdown.
  - Arahkan kursor mouse ke tombol **Logout** ➔ pastikan kursor berubah menjadi **pointer** (ikon jari/tangan) dan baris berubah menjadi latar kemerahan saat di-hover.

### R. Uji Posisi Footer di Fitur Lembur Admin (`/admin/lembur`)
- [ ] Buka menu **Admin $\rightarrow$ Lembur** (`/admin/lembur`).
- [ ] Periksa area pojok kanan atas di samping dropdown profil Admin Lembur: pastikan teks footer **tidak ada lagi** di area navbar.
- [ ] Scroll ke bagian paling bawah halaman: pastikan teks hak cipta `© 2026 BPS Provinsi Jawa Tengah - Tim SID` berada rapi di bagian bawah (*bottom footer*), berpusat di tengah, di bawah tabel dan pagination.

### S. Uji Filter Dropdown Bulan Default 'Filter Bulan' pada Menu Generate Dokumen Admin (`/admin/dokumen`)
- [ ] **Kondisi Default Saat Halaman Pertama Kali Dibuka**:
  - Buka menu **Admin $\rightarrow$ Generate Dokumen** (`/admin/dokumen`) tanpa parameter query.
  - Periksa tombol dropdown periode di atas tabel: teks label menampilkan **"Filter Bulan"** (bukan bulan berjalan seperti "Okt 2026").
  - Pastikan tombol silang reset `(×)` **tidak muncul** saat filter belum aktif.
  - Pastikan tabel menyajikan seluruh periode riwayat lembur yang ada di database.
- [ ] **Memilih Bulan Tertentu**:
  - Klik dropdown "Filter Bulan", pilih salah satu bulan (misal September 2026).
  - Halaman memuat ulang dengan URL `?bulan=2026-09`.
  - Label dropdown berubah menjadi **"Sep 2026"**.
  - Tombol silang reset `(×)` muncul di sebelah kanan dropdown.
  - Tabel hanya menampilkan baris dokumen/transaksi untuk bulan September 2026.
- [ ] **Mereset Kembali ke Semua Bulan**:
  - Klik tombol `(×)` di samping dropdown atau buka dropdown lalu klik **"Semua Bulan"**:
  - Halaman seketika kembali ke `/admin/dokumen`, label kembali bertuliskan **"Filter Bulan"**, dan seluruh bulan kembali tampil.

### T. Uji Scrolling Sidebar Drawer pada Layar Smartphone / Mobile Device
- [ ] **Tampilan Menu Lengkap & Buka/Tutup Drawer di HP**:
  - Akses aplikasi pada smartphone atau ubah peramban desktop ke mode responsive device (lebar layar $\le 420\text{px}$).
  - Login menggunakan akun Admin (yang memiliki jumlah menu terbanyak, yaitu 13 menu).
  - Ketuk tombol Hamburger di pojok kiri atas untuk membuka drawer menu navigasi.
  - Periksa header sidebar: logo, tulisan *TEMPE DELE*, dan tombol silang `(X)` tampil proporsional tanpa gepeng/tertekan (`shrink-0`).
- [ ] **Pengujian Scrolling Vertikal**:
  - Geser/swipe ke atas pada menu sidebar: menu bergeser mulus (*smooth touch scrolling*).
  - Menu-menu bagian bawah (Pengguna, Tim, Tarif, Pejabat) kini **terlihat lengkap 100%** dan dapat di-scroll sampai tuntas.
  - Menu paling bawah (Pejabat) memiliki ruang bebas yang cukup di atas gesture bar / navigation bar smartphone berkat padding `pb-16`.
- [ ] **Pengujian Penguncian Scroll Latar (*Backdrop Lock*)**:
  - Saat sidebar drawer sedang terbuka, coba gulir area gelap (backdrop/overlay) atau lakukan swipe pada latar belakang: pastikan halaman di balik drawer **terkunci diam dan tidak ikut bergeser**.
  - Ketuk area gelap atau tombol silang `(X)`: drawer tertutup kembali dengan mulus dan scrolling halaman utama kembali aktif seperti semula.
- [ ] **Verifikasi Tampilan Desktop / Web**:
  - Kembalikan ukuran layar ke desktop (lebar $\ge 1024\text{px}$):
  - Pastikan sidebar desktop tetap berada di posisi sticky kiri layar dengan tampilan penuh normal tanpa ada perubahan layout desktop.

### U. Uji Layout Mobile Ramping & Accordion Filter pada Monitoring & Pengajuan Lembur
- [ ] **Tampilan Header & Ruang Layar di Ponsel**:
  - Buka menu **Admin $\rightarrow$ Lembur** (`/admin/lembur`) pada browser ponsel atau mode responsive.
  - Periksa judul: tampil ringkas tanpa subjudul panjang 2 baris (`hidden sm:block`).
  - Tombol aksi (*Unduh Excel* dan *Ajukan Lembur*) tersaji kompak (`h-9`).
- [ ] **Uji Status Filter Dropdown Khusus Mobile (`sm:hidden`)**:
  - Pada layar smartphone, periksa filter status: ke-6 badge status kini digantikan dengan **1 tombol dropdown ramping** (tinggi 40px) berlabel dinamis `[ 🏷️ Status: Semua Status (21) ▾ ]`.
  - Ketuk tombol dropdown: menu popup muncul vertikal menyajikan ke-6 pilihan status lengkap dengan dot warna dan counter badge angka.
  - Pilih salah satu status (misal *Menunggu Kabag*): halaman memuat ulang data terfilter, tombol dropdown menampilkan dot biru dan teks `[ 🔵 Status: Menunggu Kabag (3) ▾ ]`.
  - Ketuk di luar dropdown: pastikan menu tertutup otomatis.
- [ ] **Uji Accordion Filter Pencarian Mobile**:
  - Pada kondisi awal (tanpa pencarian aktif), periksa toolbar: hanya menampilkan tombol Tanggal dan tombol **`[🔍 Cari ▾]`**.
  - Ketuk tombol **`[🔍 Cari ▾]`**: kotak input *Cari nama pegawai...* dan *Cari nama tim...* terbuka mulus ke bawah.
  - Pilih salah satu pegawai atau tim: halaman memuat data terfilter, tombol menampilkan indikator badge jumlah filter aktif (misal `1`), dan accordion tetap terbuka.
  - Ketuk kembali tombol **`[🔍 Cari ▴]`**: kotak pencarian menutup kembali dengan rapi.
- [ ] **Verifikasi Tampilan Desktop (Tetap Utuh 100%)**:
  - Buka halaman pada komputer/laptop (lebar $\ge 640\text{px}$):
  - Tombol accordion pencarian dan dropdown status mobile otomatis tersembunyi (`sm:hidden`).
  - Filter status di desktop **tetap 100% menggunakan jajaran Badge Pills warna-warni horizontal asli** (`hidden sm:flex`) tanpa perubahan layout.

### V. Uji Banner Hero Dashboard Horizontal Ramping di Layar Mobile
- [ ] **Tampilan Banner Hero di Layar Smartphone (iPhone/Android)**:
  - Buka halaman **Dashboard** (`/admin/dashboard`, `/dashboard`, `/ketua-tim/dashboard`, atau `/pimpinan/dashboard`) pada perangkat ponsel.
  - Periksa kartu hero sapaan (*Selamat Datang*):
    - Kartu tersusun secara **horizontal sejajar** (`flex-row`): teks di sisi kiri dan gambar ilustrasi meja/komputer di sisi kanan (`max-w-[92px]`).
    - Lencana sapaan `[ 👋 Selamat Datang ]` tampil rapi di atas nama pengguna.
    - Tinggi kartu sangat ramping (**hanya ~110px**, hemat 70% dibanding sebelumnya yang mencapai 450px).
  - Periksa keterlihatan kartu metrik statistik (*Total pengajuan*, *Diproses*, *Disetujui*, *Ditolak*):
    - Kartu-kartu statistik kini **langsung terlihat jelas di layar pertama smartphone tanpa perlu di-scroll ke bawah**.
- [ ] **Verifikasi Tampilan Desktop (Tetap Utuh 100%)**:
  - Buka dashboard di komputer/laptop (lebar $\ge 1024\text{px}$):
  - Pastikan banner kuning di desktop **tetap tampil besar, luas, dan ilustrasi tetap melayang bebas di kanan atas kartu** persis seperti semula (`lg:absolute lg:right-0 lg:-top-12 lg:max-w-[460px]`). Layout desktop tidak berubah sama sekali.

### W. Uji Filter Status Mobile Custom Popup Dropdown Modern & Anti-Double-Click
- [ ] **Uji Sentuh / Tap Dropdown Status di Layar Ponsel**:
  - Buka menu **Admin $\rightarrow$ Lembur** (`/admin/lembur`) di perangkat ponsel atau mode responsive device peramban.
  - Periksa tombol status visual: tampil elegan sesuai desain `[ ● Status: Semua Status (21) ⌄ ]`.
  - Ketuk tombol tersebut sekali:
    - Menu popup kustom langsung muncul mulus di bawah tombol (`#menuMobileStatus`).
    - Tipografi tampil bersih menggunakan font sans-serif modern (bukan font kaku OS bawaan).
    - Masing-masing pilihan memiliki titik status warna (dengan efek animasi pulsing dot untuk status *Menunggu*), teks status, dan counter badge angka di sisi kanan.
    - Ikon panah chevron berputar 180° dengan animasi halus.
  - Ketuk tombol status sekali lagi atau ketuk di luar menu:
    - Menu tertutup kembali dan chevron berputar kembali ke posisi awal.
  - Pilih status lain (misal **"Disetujui"**):
    - Halaman seketika memuat ulang data dengan parameter `?status=approved`.
    - Tombol status kini berubah menampilkan titik hijau, teks **"Status: Disetujui"**, dan badge angka pengajuan disetujui.
- [ ] **Verifikasi Desktop (Tetap Utuh 100%)**:
  - Buka halaman di komputer / desktop monitor ($\ge 640\text{px}$):
  - Pastikan filter desktop tetap menggunakan deretan Badge Pills warna-warni horizontal asli (`hidden sm:flex`).

### X. Uji Tata Letak Filter Mobile 2 Kolom Berdampingan & Jaminan 0% Perubahan Desktop (Kabag Umum $\rightarrow$ Pengajuan)
- [ ] **Verifikasi Tampilan Desktop / Web (Jaminan Mutlak 0% Perubahan)**:
  - Buka halaman `/kabag-umum/pengajuan` di komputer / laptop (MacBook / PC dengan lebar $\ge 640\text{px}$):
  - Toolbar desktop dirender melalui blok asli (`hidden sm:flex`):
    - Tombol filter bulan (`#periodBtn`) tetap berada di posisi aslinya.
    - Deretan tab status horizontal abu-abu (`Semua`, `Menunggu Kabag`, `Disetujui`, `Ditolak`, `Dibatalkan`) tetap tampil persis seperti semula.
    - Kotak pencarian pegawai tetap berada di sebelah kanan.
    - Toolbar mobile (`block sm:hidden`) 100% tidak aktif di desktop (`display: none`).
- [ ] **Uji Tampilan Filter di Layar Smartphone (Mobile View < 640px)**:
  - Buka halaman **Persetujuan & Monitoring Lembur Kabag Umum** (`/kabag-umum/pengajuan`) via ponsel atau responsive mode DevTools (lebar 360px - 414px):
  - Toolbar desktop otomatis tersembunyi (`hidden`).
  - Tampil **Baris Pertama (Grid 2 Kolom 50% - 50%)**:
    - **Kolom Kiri**: Tombol Filter Periode Bulan (`[ 📅 Semua Bulan 2026 ⌄ ]`), teks terpotong rapi dengan ellipsis jika ruang terbatas.
    - **Kolom Kanan**: Tombol Filter Status Mobile (`[ ● Status: Semua (12) ⌄ ]`), menampilkan titik status, label ringkas, dan counter badge.
  - Tampil **Baris Kedua**: Kotak pencarian pegawai (`[ 🔍 Cari nama pegawai / NIP... ]`) membentang penuh di bawah kedua tombol filter.
- [ ] **Uji Interaksi Dropdown Status Mobile**:
  - Ketuk tombol **Status** di ponsel:
    - Menu popup kustom melayang turun secara mulus (`#menuKabagMobileStatus`).
    - Chevron berputar 180°.
    - Pilihan berurutan rapi: *Semua Status*, *Menunggu Kabag* (dengan animasi pulsing dot biru), *Disetujui*, *Ditolak*, *Dibatalkan*.
  - Ketuk tombol **Bulan** di ponsel:
    - Menu status mobile otomatis menutup dan panel kalender bulan terbuka (saling bergantian tanpa tabrakan).
  - Ketuk salah satu status (misal *Menunggu Kabag*):
    - Halaman berpindah ke `?status=menunggu_kabag`.
    - Tombol status mobile kini menampilkan dot berkedip biru, teks `Status: Menunggu`, dan angka counter.

### Y. Uji Tampilan Hero Banner Dashboard (Bebas Tabrakan Teks-Ilustrasi & Estetika Elegan)
- [ ] **Verifikasi Bebas Tabrakan di Layar Komputer / Laptop (MacBook Air / Resolusi 1024px–1366px)**:
  - Buka halaman Dashboard utama (`/dashboard`) atau Dashboard Admin (`/admin/dashboard`):
  - Periksa kartu banner kuning-amber:
    - Teks "👋 Selamat Datang" tampil elegan dalam pill semi-transparan (*frosted glass*).
    - Nama pengguna tampil tegas dan rapi dalam 1 baris.
    - Paragraf deskripsi memiliki ruang baca yang lapang.
    - Ilustrasi meja kantor (`images/2.svg`) berada di sebelah kanan dengan jarak aman yang jelas (*gap*). **Sama sekali tidak menimpa atau menutupi teks, nama, maupun paragraf**.
    - Catatan memo kuning di atas monitor sedikit menonjol ke atas batas kartu (*breakout illustration*) memberikan efek kedalaman 3D modern.
- [ ] **Verifikasi Tampilan Responsif di Layar Ponsel (Mobile 360px–414px)**:
  - Buka halaman Dashboard di layar ponsel:
    - Kartu banner tampil melengkung halus mengikuti proporsi layar.
    - Ilustrasi meja kantor berskala otomatis (proporsional 32%–36% lebar kartu).
    - Teks tersusun rapi di sisi kiri dengan ruang baca nyaman tanpa potongan kata yang canggung.
    - Tidak ada garis batas hitam kaku maupun *horizontal scrollbar* yang bocor.

### Z. Uji Modul Manajemen User & Hak Akses (Superadmin Only)
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

### AA. Uji Single Active Pejabat, Suksesi PPK Dinamis, & Parameter Dokumen
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

### AB. Uji Format Laporan Hasil Kerja Lembur (1 Baris per Orang per Tanggal Sesuai Standar BPS)
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

### AC. Uji Tombol & Icon Cetak A4 Otomatis di Menu Daftar Hadir Admin
- [ ] **Uji Tombol Cetak A4 di Header Daftar Hadir**:
  - Login sebagai `admin` atau `superadmin`, lalu buka menu **Daftar Hadir** (`/daftar-hadir`).
  - Periksa di samping tombol **Unduh PDF** terdapat tombol berikon printer **Cetak A4**.
  - Klik tombol **Cetak A4**: muncul dropdown pilihan kategori pegawai (*Cetak Hadir (PNS)* dan *Cetak Hadir (PPPK)*) dengan penanda *Auto Print*.
- [ ] **Uji Dialog Cetak Otomatis & Orientasi Kertas A4**:
  - Klik salah satu opsi (misal: *Cetak Hadir (PNS)*): tab peramban baru akan terbuka memuat tampilan dokumen resmi presensi lembur.
  - Periksa dialog cetak bawaan browser (*print dialog*) muncul **secara otomatis**.
  - Pastikan orientasi kertas default adalah **Portrait** dan ukuran kertas default adalah **A4**.
  - Periksa bahwa tanda tangan digital pegawai dan nama Kepala Bagian Umum aktif tercetak dengan jelas dan rapi.

### AD. Uji Perbaikan Format Laporan Lembur, Tombol Cetak Daftar Hadir, Jam Pulang Riil, & Rekapitulasi (PNS/PPPK/Semua)
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

### AE. Uji Logout Sesi Kedaluwarsa & Proteksi Anti-419
- [ ] **Uji Tombol Logout Bebas Error 419**:
  - Login ke aplikasi dan biarkan beberapa saat hingga token sesi basi, atau hapus cookie sesi di DevTools Application/Storage.
  - Klik tombol **Logout** di navbar profil kanan atas.
  - Verifikasi: pengguna **langsung dialihkan secara mulus ke halaman login** (`/login`) tanpa pernah memunculkan layar hitam `419 | PAGE EXPIRED`.
- [ ] **Uji Akses Langsung URL `/logout`**:
  - Ketik atau refresh alamat URL `https://domain-kantor/logout` di address bar browser.
  - Verifikasi: sistem mengeksekusi flush session dan mengarahkan kembali ke `/login` tanpa error `405 Method Not Allowed`.

### AF. Uji Penyesuaian Role Pak Joko Suwarjo (Ketua Tim / Kabag Umum, Non-Admin)
- [ ] **Uji Role Pak Joko di Database & Sistem**:
  - Jalankan kueri `SELECT nama, nip, role FROM m_pegawai WHERE nip = '197106131993121001'`: role tercatat sebagai `ketua_tim`.
  - Login sebagai Pak Joko: sistem mengarahkan ke Dashboard Ketua Tim (`/ketua-tim/dashboard`).
  - Periksa sidebar Pak Joko: tampil menu khusus **Persetujuan Kabag** (`/kabag-umum/pengajuan`) beserta lencana notifikasi pengajuan masuk.
  - Periksa menu **Manajemen User** (`/admin/manajemen-user`): Pak Joko tercatat sebagai Kepala Bagian Umum aktif di Seksi 1, dan tidak lagi tercantum di daftar Admin Lembur operasional di Seksi 2.
- [ ] **Uji 2 Admin Aktif Terdaftar**:
  - Jalankan kueri `SELECT nama, nip, role FROM m_pegawai WHERE role = 'admin'`.
  - Pastikan yang terdaftar tepat **2 orang**: Mbak Rizki Dianing Wardhani SST (Admin Operasional) dan Ibu Suci Budi Utami SST, M.Si. (PPK dengan wewenang verifikasi admin).

### AG. Uji Standarisasi Dokumen SPKL (NIP Baru 18 Digit, Tanggal Terurut & Unik, Pegawai Kronologis)
- [ ] **Uji Tampilan NIP Baru 18 Digit di SPKL**:
  - Buka menu Dokumen Admin (`/admin/dokumen`), lalu cetak atau lihat SPKL (PDF maupun Excel).
  - Verifikasi: nomor identitas pegawai yang tertera di bawah nama pegawai adalah **NIP Baru (18 digit)** resmi (misal: `196911261989031001`), bukan lagi NIP lama 9 digit.
- [ ] **Uji Format Tanggal Lembur Terurut & Tanpa Duplikasi**:
  - Periksa kolom bulan lembur (misal: *Bulan September*):
    - Jika pegawai lembur di lebih dari satu tanggal (misal tanggal 1 dan 30), tanggal tertulis berurutan menaik: `1, 30` (atau `15, 25`).
    - Jika pegawai memiliki beberapa pekerjaan di tanggal yang sama (misal 2 kegiatan di tanggal 15), tanggal hanya tertulis satu kali `15` (tidak ada duplikasi `15, 15`).
- [ ] **Uji Pengurutan Baris Pegawai Berdasarkan Tanggal Awal Lembur**:
  - Periksa urutan baris pegawai di tabel SPKL: pegawai diurutkan berdasarkan tanggal lembur pertama mereka (pegawai yang lembur di awal bulan seperti tanggal 1 dan 7 otomatis berada di urutan atas). Apabila tanggal lembur pertamanya sama, diurutkan menurut abjad nama pegawai.

### AH. Uji Standar Dokumen Laporan Hasil Kerja Lembur (Per Orang Per Tanggal, Tanggal Ascending, Tanggal Tanpa Bold, & Regenerasi Dokumen)
- [ ] **Uji Tampilan Dokumen Laporan Lembur Acuan Resmi BPS (PNS & PPPK)**:
  - Buka menu Dokumen Admin (`/admin/dokumen`), pilih periode (misal: Juli atau September 2026), lalu lihat atau unduh **Laporan PDF / XLSX**.
  - Verifikasi:
    1. Tabel disusun **per orang per tanggal**: apabila pegawai lembur di beberapa tanggal, masing-masing tercatat di baris tersendiri dengan uraian pada tanggal tersebut.
    2. Data diurutkan secara **kronologis tanggal menaik (ascending)**, lalu nama pegawai ascending.
    3. Kolom tanggal bertuliskan header `Tanggal` dan nilainya dicetak normal (**tidak bold**).
    4. Nama pegawai dan nomor identitas menampilkan **NIP Baru (18 digit)** resmi.
- [ ] **Uji Regenerasi Dinamis Dokumen**:
  - Lakukan klik tombol *Generate* pada dokumen yang sudah pernah digenerate sebelumnya.
  - Verifikasi: sistem tidak lagi menampilkan pesan error *"Dokumen sudah pernah digenerate"*, melainkan otomatis memperbarui (*update*) isi berkas PDF/Excel di database secara real-time.
- [ ] **Uji Command Artisan Pembersih Data Testing (`php artisan lembur:reset-transaksi`)**:
  - Jalankan di terminal: `php artisan lembur:reset-transaksi`.
  - Verifikasi: muncul konfirmasi interaktif. Setelah dikonfirmasi, tabel transaksi (`t_transaksi`, `t_dokumen`, dll.) dikosongkan secara instan.
  - Periksa tabel `m_pegawai`, `m_pejabat`, dan `m_tim`: seluruh data master tetap 100% aman dan utuh.
- [ ] **Uji Seeder Uji Coba Realistis (`php artisan db:seed --class=TestingLemburSeeder`)**:
  - Jalankan di terminal: `php artisan db:seed --class=TestingLemburSeeder`.
  - Verifikasi: data transaksi uji coba kegiatan BPS (SE2026, Sakernas, SPJ) otomatis terisi untuk pengujian ulang kapan saja.
- [ ] **Uji Command Impor Data Riil Bersih (`php artisan lembur:import-clean-db`)**:
  - Jalankan di terminal: `php artisan lembur:import-clean-db`.
  - Verifikasi: mengimpor data transaksi riil dari dump mentor dan menyelaraskan seluruh skema database v2 secara otomatis.

### AI. Uji Aliran Alami Tata Letak SPKL (Natural Flow, Anti-Whitespace DomPDF, & Ringkas Tepat 2 Halaman)
- [ ] **Uji Tampilan Halaman 1 Bebas Ruang Kosong Melompong**:
  - Buka tautan dokumen SPKL (misal: `/admin/dokumen/view/151` periode September 2026 atau periode lainnya).
  - Verifikasi: Halaman 1 langsung terisi oleh baris data pegawai (seperti Pak Joko Suwarjo & Bu Meryanti) di bawah kop surat dan judul tabel. Tidak ada lagi ruang kosong melompong (*empty whitespace gap*) di halaman 1.
- [ ] **Uji Efisiensi Total Halaman (Menyusut Menjadi 2 Halaman)**:
  - Periksa total halaman dokumen SPKL: berkurang drastis dari sebelumnya 4 halaman menjadi **tepat 2 halaman**.
  - Halaman 2 memuat sisa baris pegawai dan ditutup oleh blok tanda tangan PPK dan KBU secara rapi tanpa terpotong.
- [ ] **Uji Pengulangan Header Tabel (`thead`) pada Halaman Lanjutan**:
  - Periksa bagian atas Halaman 2: baris judul kolom (*No | Nama Pegawai/NIP | Bulan ... | Uraian Kegiatan*) otomatis muncul kembali secara seragam (*repeating table header*).

### AJ. Uji Alur Lengkap End-to-End (Pegawai -> Ketua Tim -> Kabag Umum -> Admin -> Dokumen)
- [ ] **Uji Pengajuan Pegawai Tim Teknis**:
  - Login sebagai pegawai tim teknis (misal: Tim Statistik Sektoral), ajukan lembur.
  - Verifikasi: status awal pengajuan tercatat `pending` (menunggu persetujuan Ketua Tim).
- [ ] **Uji Persetujuan Ketua Tim Kerja**:
  - Login sebagai Ketua Tim terkait, buka menu Persetujuan Ketua Tim (`/ketua-tim/pengajuan`), setujui pengajuan.
  - Verifikasi: status pengajuan berpindah menjadi `menunggu_kabag`.
- [ ] **Uji Persetujuan Kepala Bagian Umum**:
  - Login sebagai Kabag Umum, buka menu Persetujuan Kabag (`/kabag-umum/pengajuan`), berikan persetujuan final.
  - Verifikasi: status pengajuan berpindah menjadi `approved`.
- [ ] **Uji Monitoring & Regenerasi Dokumen Admin**:
  - Buka menu Monitoring Lembur Admin (`/admin/lembur`): data transaksi tampil dengan status `approved`.
  - Buka menu Dokumen Admin (`/admin/dokumen`), klik generate SPKL dan Laporan pada periode bersangkutan.
  - Verifikasi: data transaksi pegawai otomatis masuk ke dalam tabel SPKL dan Laporan Hasil Lembur.

### AK. Uji Validasi Wajib Catatan Penyesuaian Jam Lembur, Universal Audit Trail, & Transparansi Jam Pegawai
- [ ] **Uji Validasi Wajib Isi Catatan Saat Jam Diubah (Ketua Tim, Kabag Umum, Admin)**:
  - Buka modal persetujuan pengajuan lembur pada salah satu menu: Ketua Tim (`/ketua-tim/pengajuan`), Kabag Umum (`/kabag-umum/pengajuan`), atau Admin (`/pengajuan`).
  - Ubah jam selesai atau jam mulai yang disetujui (misal dari jam 21:00 menjadi jam 20:00).
  - Periksa: label Catatan otomatis memunculkan tanda bintang merah dan keterangan dinamis: `* Wajib diisi karena jam lembur disesuaikan`.
  - Kosongkan kolom Catatan lalu klik tombol simpan: sistem mencegah pengiriman form dan memunculkan notifikasi peringatan: *"Catatan wajib diisi jika jam lembur yang disetujui berbeda dari jam pengajuan."*
  - Isi catatan (misal: "Disesuaikan dengan presensi riil pulang"), lalu simpan: keputusan berhasil tersimpan.
- [ ] **Uji Kolom Jam Disetujui & Lencana Disesuaikan di Sisi Pegawai**:
  - Login sebagai pegawai yang jam lemburnya disesuaikan, buka menu Pengajuan Lembur (`/lembur`).
  - Periksa tabel: terdapat kolom **Jam Disetujui** berdampingan dengan **Jam Diajukan**.
  - Jika jam disetujui berbeda dari jam pengajuan, tampil lencana kecil **`Disesuaikan`** di bawah jam disetujui.
  - Periksa kolom **Catatan**: rincian catatan Ketua Tim dan/atau Kabag Umum terpampang jelas dan terstruktur.
- [ ] **Uji Universal Audit Trail (`user_edited` & `tanggal_edited`)**:
  - Jalankan kueri di database: `SELECT id_transaksi, user_edited, tanggal_edited FROM t_transaksi WHERE id_transaksi = ...`.
  - Verifikasi: kolom `user_edited` terisi nama/NIP aktor penyetuju dan `tanggal_edited` terisi timestamp waktu perubahan.

### AL. Uji Sapaan Dinamis Waktu Nyata (Real-Time Greeting & Dynamic Icons) di Seluruh Dashboard
- [ ] **Uji Sapaan & Ikon Waktu Sesuai Jam Perangkat**:
  - Login sebagai Pegawai, Admin, Ketua Tim, atau Pimpinan.
  - Periksa badge di hero banner paling atas:
    - Pukul 04.00 - 10.59: Menampilkan **"🌅 Selamat Pagi"**.
    - Pukul 11.00 - 14.59: Menampilkan **"☀️ Selamat Siang"**.
    - Pukul 15.00 - 17.59: Menampilkan **"🌇 Selamat Sore"**.
    - Pukul 18.00 - 03.59: Menampilkan **"🌙 Selamat Malam"**.
  - Periksa judul utama `<h2>` di bawahnya: Menampilkan sapaan akrab dan hangat **"Hai, [Nama Pengguna]! 👋"** dengan rapi dan tegas.

### AM. Uji Custom Floating Status Dropdown & Toolbar Mobile Kompak pada Pengajuan Lembur (Admin & Ketua Tim)
- [ ] **Uji Tampilan & Interaksi di Smartphone (Mobile View < 640px)**:
  - Buka halaman `/admin/pengajuan` atau `/ketua-tim/pengajuan` pada perangkat HP atau mode responsive DevTools.
  - Periksa baris 1 toolbar: Pemilih Bulan dan Status Dropdown tersusun rapi berdampingan 50%-50%.
  - Periksa baris 2 toolbar: Kolom Pencarian Pegawai membentang penuh (didampingi tombol icon kompak Kelola Hari Libur pada role Admin).
  - Ketuk tombol Status: menu melayang *floating card* berpenampilan modern (`rounded-2xl`, bayangan halus, tanpa dialog native browser yang kaku atau seleksi biru kasar `#0066cc`).
  - Titik status warna tampil dinamis (pulse biru pada Menunggu Kabag, amber pada Menunggu Ketua, hijau pada Disetujui, merah pada Ditolak, abu-abu pada Semua Status).
  - Pilihan yang sedang aktif ditandai latar oranye lembut dan ikon centang.
  - Ketuk salah satu pilihan: halaman langsung memfilter data pengajuan sesuai status yang dipilih.
- [ ] **Verifikasi Tampilan Desktop ($\ge 640\text{px}$)**:
  - Buka halaman pada komputer/laptop: seluruh kontrol filter (Bulan, Search, Custom Status Dropdown, Hari Libur) kembali berjajar horizontal 1 baris yang luas dan proporsional.

### AN. Uji Perbaikan Empty State Centering & Posisi Footer pada Halaman Pengajuan Lembur Mandiri (Ketua Tim & Pegawai)
- [ ] **Uji Tampilan Kosong (Empty State) Terpusat Presisi**:
  - Buka halaman `/ketua-tim/lembur` (atau `/lembur` bagi pegawai) saat belum ada data pengajuan lembur pada periode bulan yang dipilih.
  - Verifikasi: Kotak "Belum Ada Pengajuan Lembur" (ikon jam amber, judul tebal, deskripsi ramah, tombol "+ Ajukan Lembur Sekarang") **berada tepat 100% di tengah layar** (true centering), baik di layar lebar laptop (MacBook Air/PC) maupun di layar kecil ponsel.
  - Verifikasi: Tidak ada tabel kosong 1080px dengan kolom header menggantung atau scrollbar horizontal yang tidak perlu ketika data kosong.
- [ ] **Uji Posisi Footer Rapi di Bawah & Navbar Bersih**:
  - Periksa bilah navigasi atas (navbar) sebelah kanan: Hanya menampilkan menu profil pengguna dengan aman dan bersih. Teks hak cipta **tidak lagi melayang ke pojok kanan atas navbar**.
  - Gulir ke bagian paling dasar halaman: Teks `© 2026 BPS Provinsi Jawa Tengah - Tim SID` tampil rapi dan tepat di dasar halaman sebagai footer standar.

### AO. Uji Penyatuan Struktur Tabel Selalu Tampil & Table-Fixed Layout (Ketua Tim, Kabag Umum, & Pegawai)
- [ ] **Uji Struktur Tabel & Header Kolom Tetap Tampil Saat Data Kosong**:
  - Buka halaman `/ketua-tim/lembur` atau `/lembur` pada bulan tanpa data pengajuan lembur.
  - Periksa: Kepala tabel (`<thead>`) tetap tampil rapi di atas kartu dengan pembatas garis halus `border-b border-gray-200` dan latar abu-abu lembut `bg-gray-50/90`.
  - Di bawah kepala tabel, baris kosong (`@empty`) menampilkan kartu empty state amber ikon jam dan tombol ajukan lembur yang **100% terpusat presisi di tengah layar** tanpa tergeser ke kanan.
- [ ] **Uji Table-Fixed Layout Bebas Goyang / Anti Auto-Fit**:
  - Buka halaman `/kabag-umum/pengajuan`, `/ketua-tim/pengajuan`, `/ketua-tim/lembur`, dan `/lembur`.
  - Periksa lebar kolom-kolom tabel tetap stabil (`table-fixed`) dan tidak lagi meregang / menyusut secara acak (*jittery auto-fit*) saat isi teks uraian kegiatan panjang.

### AP. Uji Tampilan Footer Mobile Terpusat (Welcome & Layout Utama)
- [ ] **Uji Tampilan Footer di Smartphone (Mobile View < 640px)**:
  - Buka halaman landing page utama (`/`) di browser smartphone atau mode responsive DevTools (lebar $\le 390\text{px}$).
  - Periksa footer di bagian bawah:
    - Teks hak cipta `© 2026 Badan Pusat Statistik Provinsi Jawa Tengah. Hak cipta dilindungi.` dan identitas `• Tim SID - BPS Provinsi Jawa Tengah` tersusun rapi secara vertikal (`flex-col`) dan berada tepat di tengah (*centered*).
    - Tidak ada ruang kosong timpang di sebelah kanan maupun pemotongan teks di tepi layar ponsel (`px-4`).

### AQ. Uji Header Navbar Edge-to-Edge Full Width (Welcome Page)
- [ ] **Uji Header Navbar Full Width (Pojok Kiri s.d. Pojok Kanan)**:
  - Buka halaman utama `http://127.0.0.1:8000/`.
  - Verifikasi: Kontainer header/navbar membentang penuh dari pojok paling kiri hingga pojok paling kanan (`w-full px-6`) meggunakan tinggi standar `h-16`.
  - Identitas TEMPE DELE berada di pojok kiri atas dan tombol Masuk berada di pojok kanan atas secara presisi.

### AR. Uji Toggle Switcher Mode Tampilan [📋 Tabel] vs [📍 Visual Timeline Card]
- [ ] **Uji Beralih Mode Tampilan di Halaman Lembur**:
  - Buka halaman **Pengajuan Lembur Pegawai** (`/lembur`) atau **Lembur Ketua Tim** (`/ketua-tim/lembur`).
  - Periksa di header halaman di samping tombol *Ajukan Lembur*: terdapat grup tombol toggle **`[ 📋 Tabel ]`** dan **`[ 📍 Timeline ]`**.
  - Klik tombol **`[ 📍 Timeline ]`**:
    - Kontainer tabel menyelesap halus dan digantikan oleh daftar **Visual Timeline Card** vertikal ala Gambar 1 mentor.
    - Setiap item memiliki titik indikator warna (*dot*) pada garis vertikal di sebelah kiri (`🟢 Disetujui`, `🔵 Menunggu Kabag`, `🟡 Diproses`, `🔴 Ditolak`).
    - Kartu menyajikan tanggal, status badge, uraian kegiatan, jam pengajuan vs disetujui, nama ketua tim, catatan, serta tombol dokumentasi & aksi.
  - Klik tombol **`[ 📋 Tabel ]`**:
    - Tampilan kembali secara instan ke bentuk tabel 2D standar.
- [ ] **Uji Persistensi Pilihan (`localStorage`)**:
  - Pilih mode **Timeline**, lalu lakukan refresh peramban (F5):
  - Halaman tetap mempertahankan tampilan mode **Timeline** tanpa kembali ke Tabel.
- [ ] **Uji Filter Tanggal & Tim pada Mode Timeline**:
  - Saat berada di mode **Timeline**, gunakan filter tanggal atau filter pencarian tim:
  - Kartu-kartu timeline secara otomatis ter-filter secara *real-time*.

### AS. Uji Presisi Garis Vertikal Aksis Tepat Melalui Bulatan Dot Status (Kuning, Merah, Hijau) pada Visual Timeline
- [ ] **Uji Posisi Garis Vertikal Aksis pada Mode Timeline**:
  - Buka halaman **Pengajuan Lembur Pegawai** (`/lembur`) atau **Lembur Ketua Tim** (`/ketua-tim/lembur`) dan aktifkan mode **Timeline**.
  - Periksa kolom bulatan dot status (kuning/amber `Diproses`, merah `Ditolak`, hijau `Disetujui`):
  - Pastikan garis vertikal aksis (`w-0.5 bg-slate-300`) berjalan lurus dan presisi melintasi pusat bulatan dot warna status (kuning, merah, hijau).
  - Pastikan garis dimulai dari pusat dot item pertama dan berakhir di pusat dot item terakhir, tanpa menjulur keluar ke atas/bawah area timeline.

### AT. Uji Kerapian Interior Kartu Visual Timeline (*Clean Borderless Layout*)
- [ ] **Uji Tampilan Interior Kartu Timeline**:
  - Buka halaman **Pengajuan Lembur Pegawai** (`/lembur`) atau **Lembur Ketua Tim** (`/ketua-tim/lembur`) dalam mode **Timeline**.
  - Periksa interior kartu:
    - Tidak ada garis bingkai kotak berlapik (`border border-slate-300` / `border border-blue-200`) yang menumpuk di dalam kartu.
    - Blok **Jam Lembur Disetujui** tampil berupa banner pastel lembut `bg-emerald-50/80` tanpa garis tepi hitam/biru yang kasar.
    - Blok **Catatan Ketua** dan **Catatan Kabag** tampil berupa *quote strip* ramping dengan aksen garis vertikal lembut di sisi kiri (`border-l-2`).
    - Tag nama tim dan nama ketua tim tersusun ringkas dalam 1 baris yang bersih dan sejuk dipandang.

### AU. Uji Garis Vertikal Kontinu Penghubung Bulatan Status Timeline (*Continuous Timeline Connector Line*)
- [ ] **Uji Keterhubungan Alur Garis Timeline**:
  - Buka halaman **Pengajuan Lembur Pegawai** (`/lembur`) atau **Lembur Ketua Tim** (`/ketua-tim/lembur`).
  - Alihkan ke mode tampilan **Timeline**.
  - Verifikasi: Di antara kotak tanggal (`17 SEP`, `16 SEP`, dst.) dan kartu rincian, terdapat **garis vertikal kontinu (`.timeline-stem`)** berwarna abu-abu rapi (`bg-slate-300`) selebar 2px yang menghubungkan seluruh titik bulatan indikator status pengajuan (oranye, biru, merah, hijau).
  - Verifikasi: Garis stem melintas mulus di belakang cincin putih (*halo ring*) bulatan status tanpa terputus oleh jarak spasi vertikal antar-kartu.
  - Verifikasi: Garis dimulai presisi dari titik pusat status dot teratas dan berakhir rapi di titik pusat status dot terbawah.
- [ ] **Uji Garis Stem Dinamis Saat Menggunakan Filter**:
  - Pada mode Timeline, pilih salah satu tim atau tanggal tertentu melalui filter.
  - Verifikasi: Garis timeline menyesuaikan secara otomatis secara *real-time*; jika hanya 1 pengajuan yang cocok, garis disembunyikan secara bersih, dan jika lebih dari 1, garis menyambung antar-item yang tampak.
  - Verifikasi: Jika filter tidak menemukan data sama sekali, muncul kotak pesan informatif `#emptyFilterTimeline` yang rapi.

### AV. Uji Koreksi Penolakan Ketua Tim, Approval Pending Kabag Umum, Jam Presensi Riil, dan Auto Row Height SPKL
- [ ] **Uji Koreksi Menjadi Ditolak oleh Ketua Tim Saat Status `menunggu_kabag`**:
  - Login sebagai Ketua Tim, buka `/ketua-tim/pengajuan`.
  - Cari pengajuan yang sebelumnya sudah disetujui (berstatus `menunggu_kabag` dan belum diproses Kabag Umum).
  - Klik tombol **Koreksi**:
    - Verifikasi: Tombol pilihan **Setujui** dan **Tolak** tetap aktif dan dapat diklik (tidak terkunci).
    - Pilih opsi **Tolak**, masukkan alasan penolakan pada catatan, lalu simpan.
    - Verifikasi: Status transaksi berhasil berubah menjadi `rejected` (Ditolak), jam disetujui dikosongkan (`null`), dan catatan penolakan tersimpan.
- [ ] **Uji Jam Disetujui Hanya Muncul Jika Presensi Sudah Diimport**:
  - Pada menu `/ketua-tim/pengajuan` dan `/kabag-umum/pengajuan`:
  - Periksa kolom **Jam Disetujui** pada tabel pengajuan:
    - Jika tanggal transaksi tersebut belum memiliki rekaman presensi (`has_presensi = 0`): kolom menampilkan strip `-`.
    - Jika presensi sudah diimport oleh Admin (`has_presensi = 1`) dan status bukan `rejected`: kolom menampilkan rentang jam disetujui.
- [ ] **Uji Tombol Aksi & Persetujuan Status Pending oleh Kabag Umum**:
  - Login sebagai Kabag Umum (Pak Joko Suwarjo), buka `/kabag-umum/pengajuan`.
  - Periksa baris pengajuan pegawai dari Tim Bagian Umum (misal: Hendro Deasmaran) yang berstatus `pending`:
    - Verifikasi: Tombol aksi **Setujui** berwarna hijau tampil (tidak lagi menampilkan `-`).
    - Klik tombol **Setujui**, lengkapi catatan jika perlu, lalu simpan:
    - Verifikasi: Transaksi langsung diproses menjadi `approved` (Disetujui Final) tanpa memicu error 422.
- [ ] **Uji Tinggi Baris Dinamis (*Auto Row Height*) SPKL Excel**:
  - Buka `/admin/dokumen`, unduh berkas **SPKL (XLSX)** periode bersangkutan.
  - Buka file di Microsoft Excel / LibreOffice:
  - Verifikasi: Baris pegawai dengan uraian kegiatan panjang secara otomatis memiliki tinggi baris yang dinamis (*auto row height*), teks uraian tampil lengkap dan rapi tanpa terpotong.
- [ ] **Uji Jam Pulang Murni Presensi BOS pada Daftar Hadir**:
  - Buka menu Admin `/admin/daftar-hadir`.
  - Verifikasi kolom jam pulang (pada web, unduh PDF, dan tombol cetak browser):
    - Jam kepulangan mencerminkan jam selesai riil dari data import presensi BOS (`t_presensi.jam_selesai`).
    - Jam kepulangan tidak dibatasi atau dipotong oleh batas 4 jam lembur weekday / 6 jam weekend.
- [ ] **Uji Dropdown Filter Bulan & Jumlah Baris per Halaman**:
  - Buka menu Pegawai `/lembur` dan coba pilih bulan (misal September 2026 atau `?bulan=2026-09`).
  - Verifikasi: Kotak dropdown periode bulan secara akurat menampilkan bulan yang dipilih (bukan selalu tertampil Desember).
  - Verifikasi: Kotak dropdown per halaman menampilkan 10 / hal secara default (bukan selalu 100 / hal).
  - Saat membuka `/lembur` tanpa parameter URL, dropdown periode bulan menampilkan "Semua Bulan".
- [ ] **Uji Pembatasan Tahun Input Tanggal Maksimal 4 Digit (Anti-Typo 5 Digit)**:
  - Buka modal "Ajukan Lembur" di menu Pegawai (`/lembur`), Ketua Tim (`/ketua-tim/lembur`), atau Admin (`/admin/lembur`).
  - Coba ketikkan tanggal lembur secara manual pada kolom input tanggal (`<input type="date">`).
  - Verifikasi: Kolom tahun menampilkan masker format 4 digit (`yyyy`) dan tidak dapat menerima pengetikan digit ke-5 (misal mengetik 2026 tidak dapat menjadi 20260).
  - Verifikasi: Jika dilakukan copy-paste tanggal dengan tahun 5 digit, skrip secara otomatis memangkas tahun menjadi 4 digit pertama.

### AW. Uji Validasi Durasi Lembur Minimal 2 Jam (Client-Side & Server-Side) Serta Label Catatan Sistem
- [ ] **Uji Pencegahan Client-Side di Modal Ajukan Lembur**:
  - Buka modal "Ajukan Lembur" di menu Pegawai (`/lembur`).
  - Masukkan Jam Mulai `17:00` dan Jam Selesai `18:00` (selisih hanya 1 jam):
    - Verifikasi: Teks estimasi durasi langsung berubah menjadi merah tebal: `1 jam (⚠️ Minimal pengajuan lembur adalah 2 jam sesuai ketentuan PMK/BPS.)`.
    - Verifikasi: Muncul kotak peringatan merah `#errorDurasiMinimal` dengan pesan bantuan ketentuan PMK/BPS.
    - Verifikasi: Tombol **"Kirim"** langsung non-aktif (`disabled`) dengan kursor not-allowed dan opacity rendah.
    - Jika tombol dipaksa submit via Enter: formulir dicegah dan muncul alert peringatan browser: *"Durasi lembur minimal 2 jam. Silakan sesuaikan jam selesai lembur."*.
  - Ubah Jam Selesai menjadi `19:00` (selisih 2 jam):
    - Verifikasi: Teks estimasi kembali normal abu-abu gelap, kotak peringatan merah otomatis hilang, dan tombol **"Kirim"** aktif kembali.
- [ ] **Uji Validasi Backend Server-Side (Tanpa Simpan Baris Ditolak ke DB)**:
  - Coba kirimkan formulir dengan durasi < 2 jam (misal 1 jam):
    - Verifikasi: Controller me-redirect kembali ke halaman form dengan pesan error formulir pada field `jam_selesai`: *"Durasi lembur minimal 2 jam sesuai ketentuan PMK/BPS."*.
    - Verifikasi Database `t_transaksi`: **TIDAK ADA** baris data baru berstatus `rejected` yang tersimpan (database tetap bersih dari transaksi sampah otomatis).
- [ ] **Uji Pelabelan Catatan Sistem (Transparansi Data Historis)**:
  - Buka tabel atau timeline pengajuan pada akun Pegawai, Ketua Tim, atau Kabag Umum.
  - Periksa data transaksi lama yang memiliki catatan otomatis sistem (seperti: *"Durasi lembur kurang dari 2 jam."*):
    - Verifikasi: Catatan dilabeli secara tepat dengan badge abu-abu netral **`SISTEM:`** (bukan dilabeli seolah-olah ditulis oleh Ketua Tim: `KETUA TIM:`).

### AX. Uji Penguncian 1 Pengajuan Lembur per Hari (Anti-Dobel Input Tanggal)
- [ ] **Uji Deteksi Real-Time di Modal Ajukan Lembur**:
  - Login sebagai Pegawai (misal: NIP Indah `198501252006042001` via dev-login).
  - Buka modal "Ajukan Lembur" di menu `/lembur`.
  - Pilih tanggal yang sudah pernah diajukan sebelumnya dan masih aktif (misal tanggal `02/10/2026` yang berstatus Menunggu Ketua / pending):
    - Verifikasi: Seketika muncul kartu peringatan merah `#errorTanggalDobel`:
      `⚠️ Anda sudah memiliki pengajuan lembur pada tanggal 2026-10-02 (Status: Menunggu Ketua). Ketentuan BPS hanya memperbolehkan 1 pengajuan lembur per hari.`
    - Verifikasi: Border input tanggal berubah menjadi merah rose (`border-rose-500 ring-rose-200`).
    - Verifikasi: Tombol **"Kirim Pengajuan"** (`#btnSubmitAjukan`) langsung terkunci non-aktif (`disabled`) dengan kursor not-allowed dan warna redup.
    - Verifikasi: Jika formulir dipaksa submit via Enter: aksi dibatalkan (`preventDefault`) dan muncul alert browser: *"Anda sudah memiliki pengajuan lembur pada tanggal tersebut (Status: ...). Ketentuan BPS hanya memperbolehkan 1 pengajuan lembur per hari."*.
  - Ganti tanggal ke tanggal lain yang belum pernah diajukan (misal `05/10/2026`):
    - Verifikasi: Kotak peringatan merah `#errorTanggalDobel` seketika menghilang.
    - Verifikasi: Border input tanggal kembali normal abu-abu.
    - Verifikasi: Tombol **"Kirim Pengajuan"** kembali aktif (jika durasi sudah >= 2 jam).
- [ ] **Uji Toleransi Pengajuan Ulang untuk Status Ditolak / Dibatalkan (Re-Apply)**:
  - Pilih tanggal lembur yang transaksinya berstatus Ditolak (`rejected`) atau Dibatalkan (`cancelled`):
    - Verifikasi: Kotak peringatan merah **TIDAK** muncul, pegawai diperbolehkan mengajukan ulang pada tanggal tersebut.
- [ ] **Uji Validasi Backend Server-Side (Anti-Bypass API/Postman)**:
  - Coba kirim request POST store dengan tanggal yang sudah memiliki transaksi aktif:
    - Verifikasi: Controller menolak request dan me-redirect dengan error session pada field `tanggal`: *"Anda sudah memiliki pengajuan lembur pada tanggal ini (Status: ...). Satu pegawai hanya dapat mengajukan 1 kali lembur per hari sesuai ketentuan BPS."*.
    - Verifikasi Database: Tidak ada rekaman baris duplikat yang tersimpan di tabel `t_transaksi`.

### AY. Uji 3 Skenario Kritis: Proteksi Backend Edit Pengajuan, Lembur Lintas Tengah Malam, & Anti-Tanda Tangan Kosong
- [ ] **Uji Proteksi Backend Edit Pengajuan (*Anti-Bypass Non-Pending Edit*)**:
  - Siapkan transaksi yang berstatus `menunggu_kabag` (sudah disetujui Ketua Tim) atau `approved`.
  - Pada antarmuka pengguna: Verifikasi tombol Ubah disembunyikan / strip `-`.
  - Coba tembak langsung request `POST /pegawai/lembur/{id}` via console peramban / Postman dengan parameter jam baru.
  - Verifikasi: Controller menolak aksi dengan kode 302 dan me-redirect dengan session error: *"Pengajuan lembur tidak dapat diubah karena sudah diproses atau disetujui..."*.
  - Verifikasi Database: Baris data di `t_transaksi` **TIDAK BERUBAH** sama sekali.
- [ ] **Uji Lembur Lintas Tengah Malam (*Cross-Midnight Overtime 21:00 s.d. 02:00*)**:
  - Buka modal "Ajukan Lembur" di menu Pegawai (`/lembur`).
  - Pilih Jam Mulai `21:00` dan Jam Selesai `02:00`:
    - Verifikasi: Antarmuka **TIDAK** memunculkan alert *"Jam selesai harus setelah jam mulai"*.
    - Verifikasi: Estimasi durasi menampilkan `5 jam (dihitung 5 jam) — 🌙 Lembur lintas tengah malam hingga dini hari`.
    - Verifikasi: Tombol **"Kirim"** aktif normal (karena 5 jam >= 2 jam minimal).
  - Simpan pengajuan lembur:
    - Verifikasi: Pengajuan berhasil terkirim, `jam_mulai` tersimpan `21:00:00` dan `jam_selesai` tersimpan `02:00:00`.
- [ ] **Uji Pencegahan Kanvas Tanda Tangan Digital Kosong / Polos**:
  - Buka modal "Ajukan Lembur", isi tim, tanggal, dan jam secara valid, namun biarkan kotak tanda tangan digital kosong.
  - Klik tombol "Kirim":
    - Verifikasi: Pengiriman dibatalkan secara instan.
    - Verifikasi: Kotak tanda tangan digital seketika berwarna merah tebal dengan highlight ring (`border-rose-500 ring-2 ring-rose-200`).
    - Verifikasi: Pesan merah muncul: `⚠️ Tanda tangan digital wajib digoreskan sebelum mengirim pengajuan.`
    - Verifikasi: Halaman menggulir otomatis (*smooth scroll*) ke kotak tanda tangan dan muncul alert peramban.
  - Coba kirim payload base64 gambar transparan/putih polos via request langsung ke endpoint `/lembur`:
    - Verifikasi: Backend via fungsi `isBlankSignature()` memeriksa piksel gambar citra nyata (GD) dan me-redirect kembali dengan error validasi: *"Tanda tangan digital tidak boleh kosong atau kanvas polos. Silakan goreskan tanda tangan Anda."*.
    - Verifikasi Database: Tidak ada baris data baru yang tersimpan di `t_transaksi`.

### AZ. Uji Deduplikasi Dropdown Pencarian Pegawai Ketua Tim (/ketua-tim/pengajuan)
- [ ] **Uji Dropdown Pencarian Pegawai Bebas Duplikasi**:
  - Login sebagai Ketua Tim (misal: Pak Sumbodo Aji Cahyono / SID via shortcut dev-login).
  - Buka menu **Pengajuan Lembur Tim** (`/ketua-tim/pengajuan`).
  - Klik kotak filter pencarian pegawai *"Cari nama pegawai..."* untuk membuka daftar dropdown.
  - Verifikasi:
    - Seluruh nama pegawai tim kerja tersusun rapi secara unik (tepat 1 entri per pegawai).
    - Tidak ada nama pegawai yang muncul berulang/dobel (seperti Yuli, Pristiana, Indah, dsb. hanya muncul 1 kali).
    - Daftar pegawai tersusun terurut berdasarkan nama A-Z.
  - Coba pilih salah satu nama pegawai (misal: Pristiana / Indah):
    - Tabel berhasil memfilter pengajuan khusus pegawai yang dipilih secara akurat.

### BA. Uji Mode Tampilan Linimasa (Timeline Card) Universal pada Persetujuan Lembur (Ketua Tim, Kabag Umum, & Admin)
- [ ] **Uji Sakelar Mode Tampilan (Tabel vs Timeline) & Persistensi Mandiri**:
  - **Ketua Tim (`/ketua-tim/pengajuan`):** Klik `[ 📍 Timeline ]`, verifikasi kartu linimasa vertikal muncul dan preferensi tersimpan di `localStorage`.
  - **Kabag Umum (`/kabag-umum/pengajuan`):** Periksa subheader banner header, klik `[ 📍 Timeline ]`, verifikasi linimasa lintas satker tampil elegan dan tersimpan di `localStorage`.
  - **Admin (`/admin/pengajuan`):** Klik `[ 📍 Timeline ]` di header atas, verifikasi linimasa pengajuan lembur seluruh satker aktif dan tersimpan di `localStorage`.
- [ ] **Uji Tata Letak 3-Kolom Bersih & Bebas Cacat Visual (*Zero Visual Glitch*)**:
  - **Kolom 1 (Date Box):** Kotak kalender berlatar putih bersih dengan border halus, menyajikan nama hari singkat, angka tanggal tebal proporsional, dan bulan/tahun. Pastikan **TIDAK ADA garis vertikal** yang memotong kotak tanggal.
  - **Kolom 2 (Aksis Garis & Dot Status):** Garis vertikal abu-abu kontinu berjalan presisi di sumbu tengah kolom, menembus tepat di belakang bulatan status dot dan ter-masking rapi oleh ring putih. Pastikan bulatan dot tidak melayang di ruang kosong.
  - **Kolom 3 (Kartu Manajerial Berkelas):**
    - Verifikasi: Identitas pegawai terpampang jelas (avatar monogram inisial tajam, nama lengkap, NIP 18 digit, badge tim kerja, dan ketua tim).
    - Verifikasi: Bilah ringkas horizontal menyatukan `Jam Diajukan` dan `Jam Disetujui` secara berdampingan tanpa pemisahan grid kaku.
    - Verifikasi: Uraian tugas tersaji dalam kotak halus dengan label kecil uppercase, menghilangkan ruang kosong melompong.
    - Verifikasi: Jam lembur disetujui hanya tampil jika rekaman presensi BOS telah ada (`has_presensi = 1`), konsisten dengan proteksi sistem.
- [ ] **Uji Sinkronisasi Real-Time Keputusan Tanpa Reload pada Kartu Linimasa**:
  - **Ketua Tim:** Buka modal keputusan dari kartu timeline, simpan perubahan, verifikasi kartu linimasa langsung ter-update seketika tanpa reload.
  - **Kabag Umum:** Klik tombol `Proses` atau `Koreksi` dari kartu timeline, simpan keputusan persetujuan akhir/penolakan, verifikasi kartu linimasa langsung diperbarui real-time.
  - **Admin:** Buka modal keputusan dari kartu timeline, ubah keputusan/jam/catatan, simpan, verifikasi elemen linimasa langsung tersinkronisasi instan.
- [ ] **Uji Filter Pencarian Pegawai Real-time pada Linimasa Kabag Umum**:
  - Pada mode Timeline di `/kabag-umum/pengajuan`, ketikkan nama pegawai pada input pencarian:
    - Verifikasi: Kartu linimasa otomatis tersaring secara instan sesuai kata kunci pencarian dan garis stem linimasa tetap rapi terhubung.

### BB. Uji Format Tampilan NIP Lama pada Header Kartu Linimasa (Ketua Tim, Kabag Umum, & Admin)
- [ ] **Uji Format NIP Lama dalam Kurung Font Monospace Setara**:
  - Buka halaman **Pengajuan Lembur** pada peran Ketua Tim (`/ketua-tim/pengajuan`), Kabag Umum (`/kabag-umum/pengajuan`), dan Administrator (`/admin/pengajuan`) dalam mode **Timeline**.
  - Temukan kartu pegawai yang memiliki data NIP lama (misal: Pak Herry Kusmaiwanto S.Si).
  - Verifikasi:
    - NIP Lama tampil dalam tanda kurung natural: `(340018283)`.
    - Ukuran font sama persis dengan NIP utama (`text-[11px]`).
    - Menggunakan tipografi monospace (`font-mono text-slate-400`) yang serasi dan sejajar presisi dengan NIP 18 digit (`text-[11px] font-mono text-slate-500`).

### BC. Uji Fleksibilitas Koreksi Penolakan Internal Ketua Tim
- [ ] **Uji Pembalikan Penolakan Internal Menjadi Disetujui**:
  - Login sebagai Ketua Tim, buka `/ketua-tim/pengajuan`.
  - Pilih pengajuan berstatus `pending`, lakukan aksi penolakan (**Tolak**).
  - Verifikasi status berubah menjadi `Ditolak` dan tombol aksi menjadi `Koreksi`.
  - Klik tombol **Koreksi** pada pengajuan yang baru saja ditolak tersebut:
    - Verifikasi: Modal keputusan menampilkan judul *"Koreksi Keputusan (Sebelumnya Ditolak)"* dan tombol pilihan keputusan (**Setujui** & **Tolak**) terbuka aktif (tidak terkunci).
    - Klik tombol **Setujui**:
      - Verifikasi: Bilah jam disetujui seketika muncul kembali dan otomatis terisi dengan jam pengajuan pegawai (`jam_mulai` & `jam_selesai`).
    - Simpan keputusan:
      - Verifikasi: Status berhasil beralih menjadi `Menunggu Kabag` (atau `Disetujui` untuk tim Bagian Umum) tanpa memunculkan error kuncian status.
- [ ] **Uji Proteksi Penolakan & Persetujuan Kabag Umum**:
  - Verifikasi: Pengajuan yang telah disetujui final oleh Kabag Umum (`approved`) tetap terkunci dari aksi penolakan Ketua Tim.
  - Verifikasi: Pengajuan yang ditolak oleh Kabag Umum (`approved_kabag_at IS NOT NULL`) tetap terkunci permanen bagi Ketua Tim.

### BD. Uji Penanganan Validasi Tanda Tangan & Pencegahan Loading Overlay Membeku
- [ ] **Uji Validasi Tanda Tangan Digital Kosong Tanpa Stuck Overlay**:
  - Buka halaman **Lembur** pada peran Pegawai (`/lembur`), Ketua Tim (`/ketua-tim/lembur`), atau Admin (`/admin/lembur`).
  - Klik tombol **+ Ajukan Lembur**.
  - Isi kolom tanggal, jam mulai, jam selesai (durasi $\ge 2$ jam), dan uraian tugas secara lengkap.
  - **Biarkan kotak tanda tangan digital kosong** (jangan goreskan kanvas).
  - Klik tombol **Kirim Pengajuan**:
    - Verifikasi: Muncul popup alert peringatan tanda tangan wajib diisi.
    - Tutup alert peringatan tersebut:
    - **Verifikasi Kritis:** Layar pemuat *"Memuat data... Mohon tunggu sebentar"* **TIDAK MUNCUL / TIDAK MACET**.
    - Verifikasi: Kotak tanda tangan mendapatkan sorotan garis merah (`border-rose-500 ring-rose-200`) dan teks pesan error merah tampil jelas.
    - Verifikasi: Pengguna dapat langsung menggoreskan tanda tangan di kanvas tanpa perlu me-refresh halaman.
- [ ] **Uji Failsafe & Penutupan Darurat via Tombol Escape**:
  - Tekan tombol **Escape** pada keyboard saat modal aktif, verifikasi bila pemuat sempat terpicu dapat langsung tertutup normal.

### BE. Uji Tata Letak Modal Ajukan Lembur Terbatas, Terpusat, & Pinned Footer (*Bounded Modal Container with Fixed Header & Pinned Footer*)
- [ ] **Uji Tampilan & Batas Tinggi Vertikal Modal (`/lembur` & `/admin/lembur`)**:
  - Buka halaman **Lembur** Pegawai (`/lembur`) atau Admin (`/admin/lembur`).
  - Klik tombol **+ Ajukan Lembur**:
    - Verifikasi: Modal muncul tepat di tengah layar (*center aligned*) berlatar bayangan gelap halus (`bg-black/50 backdrop-blur-xs`).
    - Verifikasi: Kontainer modal memiliki batas tinggi vertikal maksimal yang proporsional (`max-h-[90vh]`) dan lebar terukur (`max-w-2xl`).
    - Verifikasi: Bagian Header terpin di atas (`shrink-0`) dengan ikon plus oranye amber BPS, judul semantik tebal, dan tombol silang SVG modern.
- [ ] **Uji Scroll Internal Formulir & Aksibilitas Tombol Submit**:
  - Pada layar laptop standar (resolusi 1366×768) atau jendela browser berskala normal:
    - Verifikasi: Seluruh elemen input formulir berada dalam kontainer gulir internal (`flex-1 overflow-y-auto`). Pengguna dapat menggulir input secara mandiri di dalam modal tanpa membuat jendela browser utama bergulir.
    - **Verifikasi Kritis:** Bilah Footer modal dengan tombol **Batal** dan **Kirim Pengajuan** terpin kuat di dasar modal (`shrink-0`) dan **SELALU TERLIHAT JELAS 100%** di layar tanpa terdorong ke bawah atau terpotong ke luar layar monitor.
    - Verifikasi: Tombol *Hapus Tanda Tangan* terletak sejajar rapi di sudut kanan atas label *Tanda Tangan*.
    - Verifikasi: Kartu estimasi durasi lembur tampil elegan dengan indikator badge warna dinamis sesuai durasi dan ketentuan lembur.

### BF. Uji Pemisahan Filter Bulan & Tahun serta Penyaringan Tim Aktif Anti-Duplikasi
- [ ] **Uji Penyaringan Tim Aktif Tanpa Duplikasi Nama Tim (`/lembur`)**:
  - Buka halaman **Lembur** Pegawai (`/lembur`).
  - Klik kotak pencarian nama tim (`#searchTim`).
  - Verifikasi: Daftar nama tim yang tampil hanya berisi tim-tim yang berstatus aktif. Tidak ada lagi nama tim yang muncul dobel (seperti *"Tim Kerja Humas dan Protokol"* yang sebelumnya muncul dua kali karena terdapat data riwayat nonaktif di database).
- [ ] **Uji Dropdown Bulan & Tahun Terpisah (`/lembur`)**:
  - Buka halaman **Lembur** Pegawai (`/lembur`).
  - Verifikasi: Dropdown Bulan menampilkan pilihan *"Semua Bulan"* dan nama bulan bersih (*"Januari"*, *"Februari"*, ..., *"Desember"*) tanpa embel-embel tahun.
  - Verifikasi: Di samping dropdown bulan, terdapat dropdown Tahun mandiri yang menampilkan *"Semua Tahun"* (terpilih secara default) serta daftar tahun dari riwayat lembur.
  - Uji ganti pilihan Bulan (misal pilih *"Oktober"*): riwayat lembur terfilter untuk bulan Oktober.
  - Uji ganti pilihan Tahun (misal pilih *"2026"*): riwayat lembur terfilter untuk Oktober 2026.
  - Uji kembalikan Bulan ke *"Semua Bulan"*: riwayat lembur menampilkan seluruh transaksi pada tahun 2026.
  - Uji kembalikan Tahun ke *"Semua Tahun"*: riwayat lembur kembali menampilkan seluruh data transaksi tanpa filter tahun.

### BG. Uji Tabel Riwayat Lembur Pegawai Fit 100% & Kolom Dokumentasi Ringkas (Arsip Opsi 1)
- [ ] **Uji Tampilan Tabel Fit 100% Tanpa Geser Horizontal di Desktop (`/lembur`)**:
  - Buka halaman **Lembur** Pegawai (`/lembur`) pada layar laptop atau monitor desktop.
  - Verifikasi: Tabel 10 kolom pas 100% memenuhi lebar kontainer tanpa memunculkan bilah gulir geser horizontal (*no horizontal scrollbar*). Seluruh kolom dari Tanggal hingga Aksi berada dalam satu bidang pandang tanpa perlu menggeser layar ke kanan.
- [ ] **Uji Kompaksi Kolom Dokumentasi (*Fit & Truncated*)**:
  - Verifikasi: Kolom Dokumentasi tampil seramping mungkin (`w-[72px]`).
  - Untuk data dengan tautan bukti: tombol tampil dengan teks ringkas `Link` berpotongan (*truncate*) dilengkapi ikon tautan SVG dan tooltip URL lengkap saat kursor diarahkan.
  - Untuk data tanpa bukti: tombol penambahan tampil kompak berupa `+ Dok` yang pas dalam lebar kolom.

### BH. Uji Fitur Collapsible Sidebar Buka-Tutup (Desktop Drawer Toggle Tab) & Pengembalian Lebar Asli Tabel Lembur
- [ ] **Uji Tombol Sembunyikan Sidebar di Header Desktop**:
  - Buka halaman aplikasi di laptop/desktop (resolusi $\ge 1024\text{px}$).
  - Pada header sidebar (di samping judul TEMPE DELE), klik tombol chevron kiri / collapse (`M15.75 19.5L8.25 12l7.5-7.5`).
  - Verifikasi: Sidebar meluncur tertutup ke arah kiri dengan animasi transisi yang mulus (`transition-all duration-300`).
  - Verifikasi: Area utama (*main area*) dan tabel Pengajuan Lembur otomatis melebar penuh 100% mengisi seluruh layar monitor tanpa menyisakan ruang kosong di sisi kiri.
- [ ] **Uji Floating Drawer Toggle Tab (Ikon Bullet List Canvas LMS)**:
  - Saat sidebar dalam posisi tertutup di desktop:
    - Verifikasi: Tab mengambang (*floating drawer tab*) muncul rapi menempel di tepi kiri layar (`left-0`, `top-20` / `top-24`).
    - Verifikasi: Bentuk tab melengkung di sisi kanan (`rounded-r-2xl`), berlatar gelap elegan (`bg-slate-900`) dengan aksen oranye amber BPS (`text-amber-400`).
    - Verifikasi: Ikon di dalam tab berupa ikon daftar peluru (*bullet list*) yang identik dengan Canvas LMS (3 garis dan 3 bulatan titik terisi).
  - Arahkan kursor mouse (hover) ke tab mengambang:
    - Verifikasi: Muncul tooltip hitam berpanah kiri dengan teks *"Buka Menu"*.
  - Klik tab mengambang:
    - Verifikasi: Sidebar kembali meluncur terbuka ke kanan dengan animasi halus. Tab mengambang otomatis tersembunyi kembali.
- [ ] **Uji Ketahanan State Navigasi Halaman (`localStorage`) Tanpa Flicker**:
  - Tutup sidebar di desktop, kemudian klik tautan menu lain atau lakukan refresh halaman (F5):
    - Verifikasi: Posisi tertutup tetap dipertahankan secara persisten tanpa ada kedipan/flicker loncatan layout (*zero flicker*).
- [ ] **Uji Lebar Asli Tabel Pengajuan Lembur Saat Sidebar Terbuka vs Tertutup**:
  - Saat sidebar ditutup: tabel pengajuan lembur memiliki bidang pandang ekstra lebar 100% layar, muat secara nyaman, lega, dan leluasa.
  - Saat sidebar dibuka: tabel memiliki scroll horizontal dan tombol geser visual yang terlindungi tanpa merusak keterbacaan data.

### BI. Uji Penyeragaman Tinggi Baris Tabel (Uniform Row Height) & Modal Pop-Up Detail Uraian Kegiatan Lembur
- [ ] **Uji Kerapian dan Keseragaman Tinggi Seluruh Baris Tabel (`/lembur`)**:
  - Buka halaman Pengajuan Lembur Pegawai di browser laptop/desktop.
  - Periksa baris-baris pada `#tabelLembur`: seluruh baris tabel memiliki tinggi persis sama (64px) tanpa ada baris yang mencelat tinggi atau melorot pendek.
  - Teks pada Uraian Kegiatan, Ketua Tim, Nama Tim, dan Catatan terbatasi rapi maksimal 2 baris (`line-clamp-2`), dengan teks dan ikon vertikal di tengah (`align-middle`).
- [ ] **Uji Interaksi & Pembukaan Modal Pop-Up Uraian Kegiatan**:
  - Arahkan mouse ke teks Uraian Kegiatan pada sembarang baris: teks berubah menjadi warna amber dengan kursor pointer dan highlight latar belakang halus.
  - Klik teks Uraian Kegiatan: modal pop-up `#modalDetailUraian` seketika terbuka menyajikan informasi lengkap:
    - Metadata Tanggal Lembur, Jam Lembur (Jam Diajukan & Disetujui), Tim Kerja, Ketua Tim, dan Badge Status Pengajuan.
    - Kotak Uraian Pekerjaan Lengkap dengan scrolling teks utuh tanpa terpotong.
    - Seksi Catatan Ketua Tim dan Catatan Kabag jika ada catatan yang tersimpan di sistem.
- [ ] **Uji Penutupan Modal**:
  - Klik tombol **Tutup** atau tombol silang `(X)`: modal langsung tertutup kembali.
  - Buka kembali modal lalu tekan tombol keyboard `Escape` atau klik area backdrop gelap: modal tertutup secara instan.

### BJ. Uji Kolom Dokumentasi Lengkap, Enkripsi Base64 Client-Backend, & Tombol Aksi Ikon Pensil
- [ ] **Uji Teks Lengkap Kolom Dokumentasi**:
  - Buka halaman **Lembur** Pegawai (`/lembur`).
  - Verifikasi: Header kolom bertuliskan **"Dokumentasi"** utuh tanpa disingkat.
  - Untuk baris berstatus disetujui tanpa berkas bukti: tombol bertuliskan **"+ Dokumentasi"** lengkap dengan ikon plus dan warna teks oranye amber yang kontras.
  - Untuk baris yang sudah memiliki dokumentasi: tombol bertuliskan **"Lihat"** warna biru dengan ikon panah keluar dan tombol hapus silang merah di sampingnya.
- [ ] **Uji Alur Enkripsi Client & Dekripsi Backend (Bypass WAF BPS)**:
  - Klik tombol **+ Dokumentasi** pada pengajuan yang disetujui.
  - Masukkan URL link Google Drive (misal: `https://drive.google.com/drive/folders/contoh-link-dok`).
  - Klik **Simpan**: nilai URL secara otomatis dienkripsi menjadi `b64:...` di peramban sebelum dikirim via HTTP POST (mencegah blokir WAF 403 Forbidden).
  - Backend controller mendekripsi `b64:` dan menyimpannya sebagai URL valid di tabel `m_dokumentasi`. Muncul notifikasi *"Dokumentasi berhasil disimpan."*
- [ ] **Uji Kolom Aksi Hanya Ikon Pensil**:
  - Periksa baris pengajuan lembur yang masih berstatus **Menunggu Ketua** (`pending`).
  - Verifikasi: Kolom Aksi menampilkan tombol kotak rounded kuning lembut (`w-7 h-7`) dengan **hanya ikon pensil saja tanpa teks kata "Ubah"**.
  - Arahkan kursor mouse (hover): muncul tooltip *"Ubah pengajuan lembur"* dan ikon sedikit membesar (scale micro-interaction).
  - Klik ikon pensil: modal Ubah Pengajuan Lembur terbuka secara lancar dengan data terisi lengkap.

### BK. Uji Tabel Pengajuan Lembur Fit 100% Anti-Overlap (Sidebar Terbuka vs Tertutup)
- [ ] **Uji Tampilan Tabel Saat Sidebar Navigasi Desktop Terbuka**:
  - Buka halaman **Lembur** Pegawai (`/lembur`) di layar laptop/desktop dalam kondisi sidebar terbuka (lebar sidebar 256px / `w-64`).
  - Verifikasi: Seluruh 10 kolom tabel (Tanggal, Jam Diajukan, Jam Disetujui, Uraian Kegiatan, Ketua Tim, Nama Tim, Status, Catatan, Dokumentasi, Aksi) muat pas 100% di layar monitor tanpa memunculkan bilah gulir geser horizontal.
  - Verifikasi: Jam Diajukan dan Jam Disetujui terpisah rapi dengan jarak yang jelas, teks Status tidak menabrak kolom Catatan, serta tombol Dokumentasi dan Aksi terpusat rapi tanpa tumpang tindih.
  - Verifikasi: Seluruh baris tabel memiliki tinggi persis sama (64px / `h-16`). Teks uraian kegiatan terbatasi maksimal 2 baris dengan ikon pop-up inline `↗` (tanpa ada baris ketiga yang membuat baris melar vertikal).
- [ ] **Uji Tampilan Tabel Saat Sidebar Navigasi Desktop Ditutup**:
  - Klik tombol tutup sidebar di tepi kanan header sidebar.
  - Verifikasi: Sidebar meluncur tertutup dan area tabel otomatis melebar penuh 100% mengisi layar secara lega.
  - Verifikasi: Seluruh kolom data tersaji dengan ruang baca yang sangat luas, proporsional, dan nyaman dilihat.

### BL. Uji Tampilan Responsif Mobile Halaman Lembur Pegawai (Reflow, Grid Filter, & Smart Default Timeline)
- [ ] **Smart Default Timeline pada Smartphone**:
  - Akses `/lembur` melalui peramban ponsel pintar atau mode responsif peramban (viewport $\le 420\text{px}$).
  - Verifikasi: Halaman secara otomatis membuka **Timeline View** (bukan tabel spreadsheet yang lebar) secara cerdas dan elegan.
  - Verifikasi: Kartu pengajuan tersaji vertikal dengan badge tanggal besar, garis aksis dinamis berbulatan status, uraian pekerjaan, badge jam disetujui, dan tombol aksi yang nyaman disentuh tanpa memerlukan horizontal scrolling.
- [ ] **Kerapian Toolbar Filter 2 Kolom pada Layar Ponsel**:
  - Verifikasi: Kotak pencarian tim berada di bagian teratas mengisi lebar penuh (100% / `col-span-2`).
  - Verifikasi: Tombol filter tanggal berada di baris kedua mengisi lebar penuh secara simetris.
  - Verifikasi: Dropdown Bulan dan Tahun berada berdampingan di baris ketiga (masing-masing 50% lebar layar).
  - Verifikasi: Dropdown jumlah per halaman dan tombol Reset berada berdampingan di baris keempat tanpa ada elemen yang menggantung sendirian.
- [ ] **Tabel Anti-Tabrakan & Indikator Geser Interaktif saat Beralih ke Tabel**:
  - Ketuk tombol `[ Tabel ]` pada layar ponsel: mode tabel spreadsheet terbuka dengan batas minimum aman 1020px.
  - Verifikasi: Jam Diajukan dan Jam Disetujui terpisah rapi dengan spasi lega, tidak ada teks yang saling bertumpuk atau meluap ke kolom Uraian Kegiatan (`16:01 - 19:01` dan `rapat` terpisah bersih).
  - Geser tabel ke kanan: bilah thumb oranye pada indikator geser di atas tabel meluncur mulus dan teks persentase bertambah real-time dari `0%` hingga `100%`.
  - Ketuk langsung area track progress geser: tabel seketika melompat halus (*smooth scroll*) ke posisi kolom yang dipilih.

### BM. Uji Kolom Tanggal Sticky, Navigasi Langkah Kolom, dan Drag-to-Scroll Tabel Mobile
- [ ] **Uji Kolom Tanggal Sticky Solid Saat Tabel Digeser**:
  - Pada tampilan tabel di ponsel atau viewport sempit, geser tabel ke kanan atau ketuk tombol `›`.
  - Verifikasi: Kolom **Tanggal** tetap menempel stabil di sisi paling kiri (*sticky left-0*) dengan latar belakang putih/abu-abu solid bergaris pemisah dan bayangan elevasi.
  - Verifikasi: Tidak ada teks atau kolom yang tembus pandang atau bocor di belakang kolom Tanggal. Pengguna selalu mengetahui tanggal dari baris yang sedang ditinjau.
- [ ] **Uji Tombol Navigasi Langkah Kolom `‹` dan `›`**:
  - Ketuk tombol `›`: tabel otomatis meluncur halus ke kanan sejauh 260px menuju kolom status/catatan/aksi.
  - Ketuk tombol `‹`: tabel meluncur kembali ke posisi awal.
- [ ] **Uji Mouse Drag-to-Scroll & Draggable Progress Thumb**:
  - Tahan klik mouse pada sembarang area tabel dan seret ke kiri/kanan: tabel bergeser mulus tanpa memblokir teks.
  - Tahan dan seret bilah thumb oranye pada scrollbar indikator: tabel bergeser presisi mengikuti posisi tarikan.

### BN. Uji Redesign Tabel Persetujuan Pengajuan Ketua Tim (`/ketua-tim/pengajuan`): Layout Desktop Pas 100%, Mobile Sticky Column, dan Modal Detail Uraian
- [ ] **Uji Tampilan Desktop Pas 100% Tanpa Horizontal Scrollbar**:
  - Masuk sebagai akun Ketua Tim (misal: Bpk. Joko Suwarjo / `nip=197106131993121001`), buka halaman **Persetujuan Pengajuan Lembur** (`/ketua-tim/pengajuan`) di layar laptop/desktop (viewport $\ge 1024\text{px}$).
  - Verifikasi: Seluruh 9 kolom tabel (No, Nama Pegawai, Tanggal, Jam Diajukan, Jam Disetujui, Uraian Kegiatan, Data Presensi, Status, Aksi) muat pas 100% di dalam kontainer tabel tanpa memunculkan bilah geser horizontal.
  - Verifikasi: Kolom Status dan tombol Aksi (tombol "Proses Keputusan" atau "Koreksi Keputusan") langsung tampak jelas di sisi kanan tanpa perlu digeser.
  - Verifikasi: Header kolom Tanggal dan dropdown sortir (Terbaru/Terlama/Prioritas) tersaji rapi dalam batas kolom tanpa meluap ke kolom jam.
- [ ] **Uji Kolom No dan Nama Pegawai Sticky Solid di Layar Mobile**:
  - Buka halaman `/ketua-tim/pengajuan` di smartphone atau mode responsive browser (lebar 375px s.d. 420px).
  - Geser tabel ke arah kanan:
    - Verifikasi: Kolom **No** (`left-0`) dan **Nama Pegawai** (`left-12`) tetap membeku menempel stabil di sisi kiri layar (*sticky*).
    - Verifikasi: Latar belakang kedua kolom solid (`bg-white` pada baris ganjil, `bg-slate-50/70` pada baris genap, `bg-slate-50` pada header) dengan garis pemisah vertikal dan bayangan elevasi halus di sisi kanan kolom Nama Pegawai.
    - Verifikasi: Teks kolom Tanggal, Jam, Presensi, Status, dan Aksi meluncur mulus di belakang kolom Nama Pegawai tanpa ada teks yang bocor atau tembus pandang di celah antar-kolom.
- [ ] **Uji Kerapian Baris Tabel & Modal Pop-Up Detail Uraian Kegiatan**:
  - Periksa baris-baris pada tabel pengajuan ketua tim: tinggi baris seragam rapi (`h-16` / 64px) dengan teks uraian kegiatan terbatasi maksimal 2 baris (`line-clamp-2`) dan tanda ikon pop-up `↗`.
  - Klik teks uraian kegiatan pada sembarang baris:
    - Verifikasi: Modal pop-up `#modalDetailUraian` terbuka dengan transisi halus.
    - Verifikasi: Menampilkan informasi lengkap pegawai (Nama, NIP/NIP Lama), Tanggal Lembur, Jam Lembur, Jam Disetujui, Status Pengajuan, Kotak Uraian Pekerjaan utuh tanpa terpotong, dan Catatan Terstruktur (Catatan Ketua Tim dan Catatan Kabag Umum jika ada).
  - Tutup modal menggunakan tombol **Tutup**, tombol silang `(X)`, tombol keyboard `Escape`, atau klik di luar modal (backdrop).
- [ ] **Uji Sinkronisasi Real-Time Atribut Baris Setelah Keputusan Disimpan**:
  - Buka modal keputusan pengajuan, lakukan penyesuaian jam atau persetujuan, lalu klik **Simpan Keputusan**.
  - Verifikasi: Setelah AJAX berhasil tersimpan dan alert sukses tertutup, elemen baris tabel ter-update secara otomatis tanpa reload halaman, dan jika teks uraian diklik kembali, modal detail uraian langsung mencerminkan status, jam disetujui, serta catatan yang baru diperbarui.

### BO. Uji Standardisasi Tombol Aksi Icon-Only Tanpa Label Teks (Ketua Tim & Administrator)
- [ ] **Uji Tombol Kolom Aksi Icon-Only pada Tabel Ketua Tim (`/ketua-tim/pengajuan`)**:
  - Buka `/ketua-tim/pengajuan` di browser laptop/desktop.
  - Periksa kolom **Aksi**:
    - Baris berstatus `pending`: Tombol tampil berupa kotak rounded beraksen amber (`h-7 w-7 rounded-lg`) dengan **hanya ikon tindakan tanpa teks kata "Proses"**. Arahkan mouse (hover): muncul tooltip *"Proses Keputusan Pengajuan"*.
    - Baris selain `pending`: Tombol tampil berupa kotak rounded putih bergaris batas halus (`h-7 w-7 rounded-lg`) dengan **hanya ikon pensil tanpa teks kata "Koreksi"**. Arahkan mouse (hover): muncul tooltip *"Koreksi Keputusan Pengajuan"*.
    - Tombol terpusat rapi simetris di tengah kolom Aksi (`mx-auto`).
  - Lakukan simpan keputusan via modal AJAX: setelah modal tertutup, verifikasi tombol aksi pada baris tersebut tetap dirender ulang dalam format *icon-only* yang konsisten tanpa merusak tata letak.
- [ ] **Uji Tombol Kolom Aksi Icon-Only pada Tabel Monitoring Admin (`/admin/lembur`)**:
  - Masuk sebagai Administrator, buka menu **Kelola Lembur** (`/admin/lembur`).
  - Periksa kolom **Aksi**: tombol tindakan pengajuan berstatus aktif tampil berupa tombol kotak rounded (`h-7 w-7 rounded-lg`) dengan **hanya ikon pensil tindakan tanpa teks kata "Aksi"**.
  - Arahkan kursor mouse (hover): muncul tooltip *"Kelola & Aksi Pengajuan"* dan ikon membesar halus (`group-hover:scale-110`).
  - Klik tombol: modal aksi admin terbuka secara lancar.
- [ ] **Uji Tombol Aksi Icon-Only pada Mode Linimasa (Timeline Card Footer)**:
  - Pada halaman `/ketua-tim/pengajuan` dan `/admin/pengajuan`, alihkan mode tampilan ke **Timeline**.
  - Periksa footer setiap kartu pengajuan: tombol tindakan di sisi kanan kartu tersaji berupa tombol rounded-xl (`h-8 w-8`) icon-only yang ringkas dan proporsional tanpa tulisan yang memadati kartu.

### BP. Uji Bebas Tabrakan Kolom Status & Aksi pada Tabel Ketua Tim (`/ketua-tim/pengajuan`)
- [ ] **Uji Kerapian Badge Status Panjang & Jarak ke Tombol Aksi**:
  - Masuk sebagai akun Ketua Tim (misal: Bpk. Sumbodo Aji / Bpk. Joko Suwarjo), buka halaman **Persetujuan Pengajuan Lembur** (`/ketua-tim/pengajuan`) di laptop resolusi standar 1280×800.
  - Periksa baris berstatus `menunggu_kabag`:
    - Verifikasi: Badge status biru tampil utuh bertuliskan **"Menunggu Kabag"** tanpa ada huruf yang terpotong menjadi *"Menunggu Kaba"*.
    - Verifikasi: Terdapat jarak pemisah bebas yang lega (berkisar 54px) antara sisi kanan badge status dan tombol ikon pensil aksi di kolom Aksi. Tidak ada elemen yang saling bertumpuk atau menempel.
  - Periksa baris berstatus `pending`: badge lencana *"Menunggu"* berjarak lega dari tombol ikon aksi amber BPS.
- [ ] **Uji Halaman 2 (?page=2) Lencana Jam Disetujui & Uraian Bebas Tabrakan**:
  - Beralih ke halaman 2 (`/ketua-tim/pengajuan?page=2`) yang memiliki data jam persetujuan riil (misal `08:25 - 11:08`).
  - Verifikasi: Lencana hijau jam disetujui memiliki ruang lega (bobot 12.5% / 143px) dan **tidak bertabrakan atau menimpa teks kolom Jam Diajukan di sebelah kiri maupun Uraian Kegiatan di sebelah kanan**.
  - Verifikasi: Kolom **Nama Pegawai** terbaca utuh tanpa clipping nama agresif.
  - Verifikasi: Kolom **Data Presensi** (`● Tersedia ↗`) memiliki padding sel serasi tanpa terhimpit.
  - Verifikasi: Teks kolom **Uraian Kegiatan** tampil luas, rapi, dan nyaman dibaca didampingi tautan pop-up modal detail di sisi kanannya.
- [ ] **Uji Keterbacaan Header Tanggal & Jam**:
  - Header kolom **Tanggal** beserta dropdown sort *"Terbaru/Terlama/Prioritas"* tersaji rapi dan tidak mendesak header kolom **Jam Diajukan** dan **Jam Disetujui**.

### BQ. Uji Kalibrasi Tabel Persetujuan Pengajuan Ketua Tim Fit 100% Desktop (Rapat, Rapi, & Anti-Scrollbar Horizontal)
- [ ] **Uji Tampilan Fit 100% di Layar Desktop/Laptop (Anti-Scrollbar Horizontal)**:
  - Buka `/ketua-tim/pengajuan` atau `/ketua-tim/pengajuan?page=2` di peramban desktop (resolusi laptop standar 1280×800 atau 1366×768) dalam kondisi sidebar navigasi terbuka.
  - Verifikasi: Tabel secara presisi mengisi 100% bidang kontainer (`lg:min-w-full`, `scrollWidth === clientWidth` ~1045px) **tanpa memicu bilah geser horizontal (*no horizontal scrollbar*)**.
  - Verifikasi: Seluruh 9 kolom tabel (No, Nama Pegawai, Tanggal, Jam Diajukan, Jam Disetujui, Uraian Kegiatan, Data Presensi, Status, Aksi) tampil bersamaan di layar tanpa perlu digeser ke samping.
- [ ] **Uji Kerapian & Jarak Antar Kolom Rapat Proporsional**:
  - Verifikasi: Padding header dan body sel rapat kompak (`px-1 py-2` / `px-1.5 py-2`) menghilangkan ruang kosong/jarak renggang yang berlebihan antar-kolom.
  - Verifikasi: Lencana hijau Jam Disetujui (`08:25 - 11:08`) tampil elegan dengan ukuran `text-[10.5px] px-1.5 py-0.5` (~84px), memiliki sela bernapas aman ~36px dalam kolom 11.5% tanpa bertabrakan dengan Jam Diajukan di sebelah kiri maupun Uraian Kegiatan di sebelah kanan.
  - Verifikasi: Tombol presensi (`● Tersedia ↗`), badge status (`Menunggu Kabag`, dll), dan tombol aksi icon-only `h-7 w-7` terisolasi dengan rapi, proporsional, dan simetris.
- [ ] **Uji Sinkronisasi Keputusan AJAX Real-Time**:
  - Lakukan persetujuan pengajuan melalui modal keputusan: lencana jam disetujui yang diperbarui via JavaScript merender format badge hijau `text-[10.5px]` yang seragam tanpa merusak kerapian baris tabel.

### BR. Uji Penataan Bertingkat Vertikal Header Tanggal & Dropdown Sortir (`/ketua-tim/pengajuan`)
- [ ] **Uji Tampilan Bertingkat Vertikal (*Stacked Header*)**:
  - Buka `/ketua-tim/pengajuan` di peramban desktop.
  - Periksa kepala kolom Tanggal: teks **"Tanggal"** berada di baris atas, dan kotak dropdown sortir **`[Terbaru ▾]`** berada tepat di baris bawahnya secara simetris dan terpusat (`flex flex-col items-center justify-center gap-1`).
  - Verifikasi: Dropdown sortir tidak lagi berada sejajar horizontal di samping kata "Tanggal", sehingga memiliki sela ruang kosong yang luas (> 45px) dan **sama sekali tidak menempel atau menyenggol huruf header kolom "Jam Diajukan"**.
  - Verifikasi: Fungsi sortir dropdown (*Terbaru*, *Terlama*, *Prioritas*) tetap berfungsi normal saat dipilih.

### BS. Uji Restrukturisasi Modal Ajukan Lembur 2-Kolom Desktop Zero-Scroll
- [ ] **Uji Modal Ajukan Lembur Pegawai (`/lembur`)**:
  - Masuk sebagai Pegawai (misal: Ibu Indah Purnamasari), buka menu **Pengajuan Lembur** (`/lembur`).
  - Klik tombol **`+ Ajukan Lembur`**: dialog modal `#modalAjukan` terbuka di tengah layar.
  - Verifikasi: Pada resolusi layar laptop/desktop (viewport height $\ge 600\text{px}$), modal tersaji dalam format **2 kolom berdampingan** (`md:grid-cols-2`, `max-w-3xl lg:max-w-4xl`).
  - Verifikasi: **Zero Scroll**: Seluruh elemen formulir (Kolom kiri: Ketua Tim, Tanggal, Jam Mulai & Selesai, Estimasi Durasi; Kolom kanan: Uraian Kegiatan, Kotak Tanda Tangan Digital) tampil utuh secara simultan **tanpa memicu bilah geser internal (*no internal vertical scrollbar*)**.
  - Verifikasi: Kanvas tanda tangan digital (`h-28`) dapat digoreskan secara mulus dan tombol *"Hapus Tanda Tangan"* berfungsi membersihkan goresan.
- [ ] **Uji Modal Ajukan Lembur Ketua Tim (`/ketua-tim/lembur`)**:
  - Masuk sebagai Ketua Tim (misal: Bpk. Joko Suwarjo), buka menu **Pengajuan Lembur Mandiri** (`/ketua-tim/lembur`).
  - Klik tombol **`+ Ajukan Lembur`**: dialog modal tersaji dalam format 2-kolom desktop zero-scroll yang identik dan proporsional.
- [ ] **Uji Modal Ajukan Lembur Administrator (`/admin/lembur`)**:
  - Masuk sebagai Administrator (Ibu Rizki Dianing Wardhani), buka menu **Kelola Lembur** (`/admin/lembur`).
  - Klik tombol **`+ Ajukan Lembur`**: dialog modal tersaji dalam format 2-kolom desktop zero-scroll yang identik dan proporsional.
- [ ] **Uji Responsif Mobile (< 768px)**:
  - Buka dialog modal pada mode inspeksi mobile (lebar layar 375px atau 414px):
  - Verifikasi: Formulir secara otomatis mengalir kembali (*reflow*) menjadi 1 kolom vertikal yang dapat digulir secara wajar (`overflow-y-auto`) demi kenyamanan pengisian di layar sentuh.
- [ ] **Uji Alinyemen Atas-Bawah & Keseimbangan Kolom Kiri-Kanan**:
  - Verifikasi: Batas atas kolom kiri (Ketua Tim) sejajar rapi dengan kolom kanan (Uraian Kegiatan).
  - Verifikasi: Batas bawah kolom kiri (Jam Mulai/Selesai & Estimasi Durasi) sejajar horizontal dengan batas bawah kolom kanan (Kotak Tanda Tangan Digital).
  - Verifikasi: Jarak antar-elemen di kolom kiri lapang dan proporsional (`justify-between`, `py-2.5`), mengeliminasi ruang kosong canggung di sudut kiri bawah.

### BT. Uji Sinkronisasi Bundel Aset Produksi Vite (`public/build`) & Tampilan Real-Time
- [ ] **Uji Header HTML Memuat Bundel CSS Terbaru**:
  - Buka halaman login atau dashboard di peramban, buka inspect element / *View Page Source* (Ctrl + U).
  - Verifikasi: Tag stylesheet memuat berkas bundel CSS termutakhir `assets/app-CgN_TPC-.css` (bukan bundel lama `app-BWQ3dq7l.css` atau `app-vIdj2UmY.css`).
- [ ] **Uji Hard Refresh Peramban (Bypass Browser Cache)**:
  - Tekan kombinasi tombol **Ctrl + Shift + R** (atau **Ctrl + F5**) untuk memastikan cache CSS lokal peramban dibersihkan.
  - Verifikasi: Tampilan modal ajukan lembur 2 kolom, tabel responsif sticky column, collapsible sidebar, dan seluruh tata letak antarmuka di peladen sama persis 100% dengan tampilan di lingkungan lokal.

### BU. Uji Pencarian Menyeluruh & Standardisasi Paginasi 3 Bagian (`/kabag-umum/pengajuan`)
- [ ] **Uji Pencarian Menyeluruh Melintasi Seluruh Data Periode Aktif**:
  - Masuk ke akun Kepala Bagian Umum atau Administrator, buka menu **Persetujuan Kabag** (`/kabag-umum/pengajuan`).
  - Masukkan nama pegawai (misal: `"joko"`) pada input pencarian.
  - Verifikasi: Hasil pencarian menampilkan seluruh data transaksi pegawai bersangkutan dari keseluruhan periode aktif (bukan hanya 10 baris di halaman aktif).
- [ ] **Uji Navigasi Halaman Pada Hasil Pencarian**:
  - Klik tombol navigasi halaman berikutnya `[ > ]`.
  - Verifikasi: Halaman ke-2 tetap memuat hasil pencarian kata kunci terkait (parameter `search` tidak hilang dari URL).
- [ ] **Uji Tombol Pembersih Pencarian `(×)`**:
  - Klik tombol silang `(×)` pada sisi kanan input pencarian.
  - Verifikasi: Input dikosongkan dan tabel langsung memuat kembali seluruh transaksi pengajuan lembur tanpa filter pencarian.
- [ ] **Uji Baris Paginasi Bawah 3 Bagian**:
  - Periksa bagian bawah tabel:
    1. **Kiri:** Tampil dropdown `Tampilkan [ 10 v ] data`. Ubah pilihan menjadi 25 atau 50, dan verifikasi jumlah baris yang tampil langsung bertambah sesuai opsi.
    2. **Tengah:** Tampil navigasi kompak `[ < ] Page X of Y [ > ]` dengan tombol hover oranye `#faa938`.
    3. **Kanan:** Tampil teks ringkasan `Menampilkan X-Y dari Z data` yang sinkron dengan total data riil.

### BV. Uji Pencarian Ultra-Smooth (0ms Latency, Live Keystroke Filter Tanpa Reload) pada Persetujuan Kabag Umum (`/kabag-umum/pengajuan`)
- [ ] **Uji Live Keystroke Filter Instan (Tanpa Reload Halaman)**:
  - Buka `/kabag-umum/pengajuan`, ketik nama pegawai di kotak pencarian:
  - Verifikasi: Baris tabel dan linimasa terfilter seketika pada setiap ketukan tuts keyboard (**0ms latency**) tanpa layar berkedip (*no white flash/reload*) dan kursor tidak kehilangan fokus.
- [ ] **Uji Spesialisasi Pencarian Nama & NIP Pegawai (Bebas False-Positive Ketua Tim)**:
  - Ketik nama pegawai yang juga menjabat sebagai Ketua Tim (misal: `"hendro"`):
  - Verifikasi: Hasil pencarian **hanya menampilkan baris pengajuan lembur milik Hendro Deasmaran S.T.** (tidak mencantumkan baris milik anggota/bawahan tim seperti Muhammad Sodri atau Joko Suwarjo yang diketuai oleh beliau).
- [ ] **Uji Transisi Paginasi Mulus (*Instant Page Switching*)**:
  - Pada hasil pencarian atau tabel default, klik tombol `[ > ]` atau `[ < ]`:
  - Verifikasi: Halaman berpindah secara instan (0ms) tanpa reload peramban, dan penomoran kolom `No` otomatis berlanjut secara runtut (`1, 2, 3...`).
- [ ] **Uji Sinkronisasi State URL Senyap (*Silent URL ReplaceState*)**:
  - Periksa bilah alamat browser saat mengetik atau berpindah halaman: parameter `?search=...&page=...` otomatis diperbarui tanpa me-reload halaman.
  - Lakukan refresh peramban (F5): pastikan kata kunci pencarian dan posisi halaman tetap tersimpan utuh.
- [ ] **Uji Sinkronisasi Mode Tampilan Timeline**:
  - Beralih ke tampilan Timeline: verifikasi kartu linimasa dan garis aksis vertikal (`.timeline-stem`) terfilter dan terpaginasi secara rapi dan sinkron dengan filter pencarian.

### BW. Uji Standarisasi Dimensi & Responsivitas Tombol "Lihat Semua" pada Dashboard Cards
- [ ] **Uji Tombol "Lihat Semua" pada Dashboard Pegawai (`/dashboard`)**:
  - Masuk ke akun pegawai, buka menu **Dashboard** (`/dashboard`).
  - Periksa kartu *"Pengajuan terbaru"* dan *"Jadwal lembur mendatang"*.
  - Verifikasi: Tombol *"Lihat semua"* berada dalam 1 baris teks yang utuh (*no text-wrap*), berbentuk pil `rounded-full` simetris dan proporsional.
- [ ] **Uji Responsivitas Sidebar Desktop Buka / Tutup**:
  - Klik tombol tab tepi/toggle sidebar desktop untuk menutup sidebar (`w-0`), lalu buka kembali (`w-64`):
  - Verifikasi: Dimensi, tinggi, padding (`px-4 py-2`), dan ukuran font tombol *"Lihat semua"* tetap 100% konsisten, tidak gepeng/kempis, dan tidak terlipat menjadi 2 baris (bukan oval tinggi/telur).
- [ ] **Uji Responsivitas Layar Laptop Kecil (13"/14", Resolusi 1024px–1366px)**:
  - Uji halaman dashboard pada viewport 1024px atau 1280px dengan sidebar terbuka:
  - Verifikasi: Container teks judul (`min-w-0 flex-1`) menyesuaikan ruang secara dinamis tanpa menekan tombol *"Lihat semua"* (`shrink-0 whitespace-nowrap`).
- [ ] **Uji Konsistensi Lintas Dashboard Peran (Admin, Ketua Tim, Pimpinan)**:
  - Buka `/admin/dashboard`, `/ketua-tim/dashboard`, dan `/pimpinan/dashboard`:
  - Verifikasi: Seluruh tombol *"Lihat semua"* / *"Lihat Semua"* memiliki ukuran, padding, tinggi, dan perilaku anti-wrap yang seragam di semua peran pengguna.

### BX. Uji Izin Pengeditan Mandiri Pengajuan Lembur Tim Bagian Umum (`/lembur`)
- [ ] **Uji Munculnya Tombol Edit pada Pengajuan Tim Bagian Umum**:
  - Masuk sebagai anggota Tim Bagian Umum (misal: Aning Widiyati), buka menu **Lembur** (`/lembur`).
  - Periksa baris pengajuan lembur yang berstatus `Menunggu Kabag`.
  - Verifikasi: Tombol aksi edit kuning (ikon pensil) tampil di tabel maupun tampilan linimasa.
- [ ] **Uji Keberhasilan Penyimpanan Perubahan Data**:
  - Klik tombol edit kuning, ubah rincian uraian kegiatan atau jam pengajuan.
  - Klik tombol *"Simpan Perubahan"*:
  - Verifikasi: Perubahan berhasil disimpan dengan notifikasi sukses *"Pengajuan lembur berhasil diperbarui."*, tidak ditolak oleh peladen, dan pesan error *"tidak dapat diubah karena sudah diproses"* tidak muncul lagi.
- [ ] **Uji Penguncian Ketat untuk Tim Lain Setelah Disetujui Ketua Tim**:
  - Masuk sebagai anggota tim non-Bagian Umum (misal: Tim Sosial atau Nerwilis).
  - Saat pengajuan telah disetujui Ketua Tim (berstatus `Menunggu Kabag`):
  - Verifikasi: Tombol aksi edit kuning tidak muncul (terkunci rapat) sehingga integritas keputusan Ketua Tim tetap terlindungi.

### BY. Uji Standarisasi Ketebalan Font Teks Uraian Kegiatan (Semi-Bold) di Seluruh Role
- [ ] **Uji Tampilan Linimasa Pengajuan Lembur Pribadi (`/lembur` & `/ketua-tim/lembur`)**:
  - Akses menu **Lembur** pada role Pegawai, Kabag Umum, Admin, atau Pimpinan (`/lembur`), dan alihkan ke mode **Timeline**.
  - Akses menu **Lembur** pada role Ketua Tim (`/ketua-tim/lembur`), dan alihkan ke mode **Timeline**.
  - Verifikasi: Teks judul/uraian kegiatan lembur kini menggunakan ketebalan **semi-bold** (`font-semibold` / weight 600), proporsional dan nyaman dibaca untuk uraian paragraf panjang, tidak lagi terlalu pekat/tebal (*no bold weight 700*).
- [ ] **Uji Harmonisasi Linimasa Verifikasi & Modal Detail Uraian**:
  - Buka linimasa verifikasi pada role Ketua Tim (`/ketua-tim/pengajuan`), Kabag Umum (`/kabag-umum/pengajuan`), dan Admin (`/admin/pengajuan`):
  - Verifikasi: Label sub-seksi *Uraian Kegiatan Lembur:* tampil rapi dan tegas dengan `font-semibold`.
  - Klik baris uraian pada tabel atau buka modal detail uraian:
  - Verifikasi: Judul modal *Detail Uraian Kegiatan* menggunakan tipografi `font-semibold text-slate-900` yang elegan dan selaras.

### BZ. Uji Standarisasi Format Catatan Transaksi Dibatalkan oleh Admin (Label 'Admin:' & Pembersihan Prefix)
- [ ] **Uji Tampilan Tabel Persetujuan Kabag Umum (`/kabag-umum/pengajuan`)**:
  - Buka menu **Persetujuan Kabag**, klik tab filter **Dibatalkan**.
  - Periksa kolom **Catatan** pada transaksi yang dibatalkan oleh Admin:
    - Verifikasi: Catatan diawali dengan label/badge khusus **`ADMIN:`** berwarna merah mawar lembut (`text-rose-600 bg-rose-50`), bukan lagi label keliru `KETUA TIM:`.
    - Verifikasi: Teks catatan bersih dari awalan sistem (string `[Dibatalkan Admin]` telah dibersihkan otomatis).
    - Verifikasi: Jika sebelumnya transaksi memiliki catatan dari Kabag Umum, label dan catatan `KABAG UMUM:` tetap tampil rapi di bawahnya.
- [ ] **Uji Tampilan Linimasa & Modal Detail di Seluruh Role**:
  - Beralih ke mode **Timeline** pada halaman Kabag Umum, Ketua Tim, Pegawai, atau Admin.
  - Periksa kartu linimasa transaksi dibatalkan:
    - Verifikasi: Seksi catatan menampilkan label **`Catatan Admin:`** beraksen rose (`text-rose-600 font-semibold`) dengan teks alasan bersih.
  - Klik teks uraian atau buka modal detail uraian pada transaksi dibatalkan di halaman Pegawai (`/lembur`) atau Ketua Tim (`/ketua-tim/pengajuan`):
    - Verifikasi: Header catatan modal pop-up menampilkan **`Catatan Admin:`** berwarna merah mawar dengan isi teks bersih tanpa prefix teknis.
- [ ] **Uji Pembatalan Transaksi Real-Time oleh Admin (`/admin/lembur`)**:
  - Masuk sebagai Admin, lakukan pembatalan salah satu transaksi via modal konfirmasi aksi:
  - Verifikasi: Baris tabel langsung berubah menampilkan status `Dibatalkan` dan kolom Catatan langsung menyajikan badge `Admin:` beserta teks alasan secara real-time tanpa perlu me-reload peramban.

### CA. Uji Standarisasi Pengajuan Lembur Pribadi Ketua Tim & Kabag Umum (`/ketua-tim/lembur`)
- [ ] **Uji Tampilan & Fitur Lembur Pribadi Kabag Umum**:
  - Masuk ke akun Kepala Bagian Umum (Bpk. Joko Suwarjo) via `/dev-login/kabag`, klik menu **Lembur** di bilah sisi navigasi (`/ketua-tim/lembur`).
  - Verifikasi: Halaman memuat antarmuka **Pengajuan Lembur Pribadi** modern (`lembur.blade.php`), bukan lagi view usang.
  - Verifikasi: Toolbar filter lengkap: Pencarian Nama Tim, Pemilih Tanggal, Dropdown Filter Bulan & Multi-Tahun, serta tombol pengalih **Tabel / Timeline**.
  - Verifikasi: Tombol **`+ Ajukan Lembur`** membuka modal 2-kolom zero-scroll desktop yang proporsional.
- [ ] **Uji Tampilan & Fitur Lembur Pribadi Ketua Tim**:
  - Masuk ke akun Ketua Tim (misal: Bpk. Hendro Deasmaran) via `/dev-login/197904262002121002`, klik menu **Lembur** (`/ketua-tim/lembur`).
  - Verifikasi: Antarmuka identik dengan tampilan pegawai, item menu Lembur di sidebar tetap aktif (`active`), dan seluruh aksi (detail uraian, edit pending, manajemen bukti dokumentasi Drive) beroperasi sempurna.
- [ ] **Uji Alur Pengajuan Lembur Pribadi**:
  - Buat pengajuan lembur baru pada akun Ketua Tim / Kabag Umum:
  - Verifikasi: Setelah pengiriman formulir sukses, sistem mengarahkan kembali ke `/ketua-tim/lembur` dengan notifikasi sukses *"Pengajuan lembur berhasil dikirim."* dan baris pengajuan baru langsung tampak di tabel.

### CB. Uji Filter Tab Status Dibatalkan pada Halaman Monitoring Lembur Admin (`/admin/lembur`)
- [ ] **Uji Tampilan Data Transaksi pada Tab Dibatalkan**:
  - Masuk sebagai Administrator via `/dev-login/admin`, buka menu **Lembur** (`/admin/lembur`).
  - Klik tab filter status **Dibatalkan (1)**:
  - Verifikasi: Parameter URL menjadi `?status=cancelled`.
  - Verifikasi: Tabel secara presisi memfilter dan menampilkan transaksi yang berstatus dibatalkan saja (misal: pengajuan Hendro Deasmaran S.T. tanggal 19 Sep 2026).
  - Verifikasi: Transaksi berstatus lain (seperti *Menunggu Kabag*, *Disetujui*, dll) **tidak lagi bocor atau mendominasi tabel**.
- [ ] **Uji Format Badge Status & Catatan Pembatalan**:
  - Verifikasi: Kolom Status menampilkan lencana abu-abu rapi bertuliskan **`● Dibatalkan`**.
  - Verifikasi: Kolom Catatan menampilkan label **`ADMIN:`** berwarna merah mawar lembut dengan teks alasan pembatalan bersih tanpa string teknis `[Dibatalkan Admin]`.
- [ ] **Uji Keselarasan Ekspor Excel**:
  - Dalam kondisi filter tab Dibatalkan aktif, klik tombol **Unduh Excel**:
  - Buka file spreadsheet hasil unduhan:
  - Verifikasi: Kolom Status menyajikan teks **"Dibatalkan"** berlatar belakang abu-abu muda (`#F3F4F6`), bukan lagi strip `-`.








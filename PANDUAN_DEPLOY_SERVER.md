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





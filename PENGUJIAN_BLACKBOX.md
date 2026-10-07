# 📋 DOKUMEN PENGUJIAN BLACK BOX LENGKAP (*BLACK BOX TESTING SUITE*)
## SISTEM INFORMASI MANAJEMEN LEMBUR BPS PROVINSI JAWA TENGAH (TEMPE DELE)

---

## 📌 1. INFORMASI PENGUJIAN & METODOLOGI

* **Nama Perangkat Lunak:** Sistem Informasi Manajemen Lembur (TEMPE DELE)
* **Instansi:** Badan Pusat Statistik (BPS) Provinsi Jawa Tengah
* **Pengembang / Penguji:** Tim Pengembang Mahasiswa MBKM BPS Jateng
* **Metodologi Pengujian:** *Black Box Testing*
  - *Equivalence Partitioning* (Partisi Kesetaraan Nilai Masukan)
  - *Boundary Value Analysis* (Analisis Nilai Batas Tanggal dan Waktu)
  - *State Transition Testing* (Pengujian Siklus Hidup Transisi Status Pengajuan)
  - *Error Guessing* (Antisipasi Typo, Format Sel Spreadsheet, dan Anomali Data)
* **Lingkungan Pengujian (*Test Environment*):**
  - **Peladen Web / Basis Data:** PHP 8.2+ / Laravel 11.x / MariaDB / MySQL 8.0
  - **Antarmuka (*Client*):** Google Chrome v129+, Microsoft Edge v129+ (Desktop & Mobile Viewport)
  - **URL Basis Pengujian:** `http://127.0.0.1:8000` (Localhost) & `https://lembur.web.bps.go.id` (Staging/Production)

---

## 📊 2. REKAPITULASI HASIL PENGUJIAN (*TEST SUMMARY DASHBOARD*)

| No | Modul / Kelompok Pengujian | Total Kasus Uji | Berhasil (*PASS*) | Gagal (*FAIL*) | Persentase Keberhasilan |
|:---:|:---|:---: |:---:|:---:|:---:|
| 1 | Modul 1: Autentikasi, Hak Akses (RBAC), & Keamanan Sesi | 8 | 8 | 0 | **100%** |
| 2 | Modul 2: Pengajuan Lembur Pegawai (Sisi Pegawai) | 10 | 10 | 0 | **100%** |
| 3 | Modul 3: Persetujuan & Koreksi Ketua Tim (Tahap 1) | 9 | 9 | 0 | **100%** |
| 4 | Modul 4: Persetujuan & Koreksi Kabag Umum (Tahap 2 Final) | 8 | 8 | 0 | **100%** |
| 5 | Modul 5: Monitoring Pengajuan & Pembatalan Admin | 8 | 8 | 0 | **100%** |
| 6 | Modul 6: Impor Presensi BOS & Integrasi Kehadiran | 10 | 10 | 0 | **100%** |
| 7 | Modul 7: Pembuatan Dokumen Kedinasan (SPKL, Laporan, Daftar Hadir) | 9 | 9 | 0 | **100%** |
| 8 | Modul 8: Manajemen Pengguna & Suksesi Pejabat | 4 | 4 | 0 | **100%** |
| **TOTAL** | **Seluruh Modul Sistem** | **66** | **66** | **0** | **100%** |

---

## 🧪 3. TABEL DETAIL KASUS UJI (*DETAILED TEST CASES*)

### MODUL 1: Autentikasi, Hak Akses (RBAC), & Keamanan Sesi

> [!NOTE]
> **Catatan Metode Pengujian Autentikasi:**
> - **Alur Resmi Produksi:** Menggunakan form input **Username SSO** dan **Password SSO BPS** yang divalidasi ke API Connect BPS Jawa Tengah (`https://jateng.web.bps.go.id/apiconnect/login`).
> - **Alur Pengujian Lokal (*Local Testing*):** Karena penguji di lingkungan lokal tidak memiliki password SSO milik seluruh pegawai dan pejabat struktural, pengujian multi-peran dipermudah menggunakan panel **Shortcut Dev-Login** di bawah form login (`/dev-login/{nip}`) yang secara otomatis menginjeksi sesi peran tanpa memerlukan kata sandi SSO riil.

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-AUTH-01** | Login resmi via formulir SSO BPS dengan kredensial valid | 1. Buka halaman `/login`<br>2. Masukkan Username SSO dan Password SSO BPS<br>3. Klik tombol Masuk | Username: Akun SSO valid<br>Password: Password SSO valid | Sistem memvalidasi ke API Connect BPS, memverifikasi `kd_satker = 3300`, menyinkronkan profil pegawai, dan mengarahkan ke dashboard sesuai role | Terautentikasi via API SSO, profil tersinkron, masuk dashboard | **PASS** |
| **TC-AUTH-02** | Penolakan login formulir jika Username / Password salah | 1. Buka halaman `/login`<br>2. Masukkan username atau password salah<br>3. Klik tombol Masuk | Username: `usersalah`<br>Password: `passwordsalah` | Sistem menolak autentikasi dan menampilkan pesan kesalahan: *"Username atau password salah."* | Muncul pesan error "Username atau password salah." | **PASS** |
| **TC-AUTH-03** | Penolakan login pegawai dari luar Satker BPS Jateng | 1. Login menggunakan akun SSO pegawai dari BPS luar Jateng (`kd_satker != 3300`) | Akun Satker Non-3300 | Sistem menolak akses dengan notifikasi: *"Akses ditolak. Hanya pegawai BPS Jawa Tengah yang dapat login."* | Akses ditolak karena satker bukan 3300 | **PASS** |
| **TC-AUTH-04** | Pengujian sesi peran Pegawai via Shortcut Dev-Login Lokal | 1. Buka halaman `/login`<br>2. Klik tombol shortcut Pegawai (misal: "Indah") atau akses `/dev-login/198501252006042001` | NIP: `198501252006042001`<br>(Indah Purnamasari S.E) | Sesi diset ke role `user`, sistem otomatis mengarahkan ke `/dashboard`, sidebar hanya menampilkan menu Pegawai | Masuk ke Dashboard Pegawai, wewenang sesuai role `user` | **PASS** |
| **TC-AUTH-05** | Pengujian sesi peran Ketua Tim via Shortcut Dev-Login Lokal | 1. Buka `/login`<br>2. Klik shortcut Ketua Tim "Sumbodo Aji" atau akses `/dev-login/197703081999011001` | NIP: `197703081999011001`<br>(Sumbodo - Ketua Tim SID) | Sesi diset ke role `ketua_tim`, sistem mengarahkan ke `/ketua-tim/dashboard`, menu Pengajuan Lembur Tim terbuka | Masuk ke Dashboard Ketua Tim, menu pengajuan tim aktif | **PASS** |
| **TC-AUTH-06** | Pengujian sesi peran Kabag Umum & Admin via Shortcut Dev-Login Lokal | 1. Klik shortcut "Kabag Umum" (`/dev-login/kabag`) atau "PPK/Admin" (`/dev-login/admin`) | Parameter role: `kabag` / `admin` | Sistem mengidentifikasi pejabat aktif di `m_pejabat`, sesi diset sesuai wewenang, menu Persetujuan Kabag Umum / Dashboard Admin terbuka | Wewenang pejabat dan admin aktif presisi | **PASS** |
| **TC-AUTH-07** | Proteksi akses rute non-otorisasi (*URL Tampering*) | 1. Login sebagai Pegawai biasa<br>2. Ketik manual URL admin: `/admin/presensi` di address bar | Akun Pegawai reguler | Sistem menolak akses dengan HTTP 403 Forbidden atau mengalihkan ke dashboard pribadi dengan pesan peringatan | Akses ditolak oleh middleware, dialihkan aman | **PASS** |
| **TC-AUTH-08** | Penanganan kedaluwarsa sesi (*Session Expired / Token Mismatch*) | 1. Biarkan sesi kedaluwarsa<br>2. Lakukan request POST logout atau form submit | CSRF Token kedaluwarsa | Sistem tidak memunculkan halaman error 419 Page Expired mentah, melainkan mengarahkan ke halaman login dengan pesan ramah | Pengguna dialihkan ke halaman login secara halus | **PASS** |

---

### MODUL 2: Pengajuan Lembur Pegawai (Sisi Pegawai)

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-PEG-01** | Pengajuan lembur dengan data valid | 1. Buka menu `/lembur`<br>2. Klik tombol "Ajukan Lembur"<br>3. Pilih tim kerja, tanggal, jam mulai, jam selesai, uraian pekerjaan<br>4. Klik Simpan | Tim: SID<br>Tgl: `2026-10-02`<br>Jam: `16:30 - 19:30`<br>Uraian: "Rekonsiliasi data SE2026" | Transaksi tersimpan dengan status awal `pending` (Menunggu Ketua), alert sukses muncul, data tampil di baris teratas tabel | Data tersimpan, status `Menunggu Ketua`, tampil di tabel | **PASS** |
| **TC-PEG-02** | Proteksi batas tahun input tanggal maksimal 4 digit (*Anti-Typo 5 Digit*) | 1. Buka modal pengajuan lembur<br>2. Pada kolom tanggal, coba ketikkan tahun 5 digit (misal: `20260`) atau paste | Input tanggal: `02/10/20260` | Atribut `max="2099-12-31"` dan listener JavaScript memotong input tahun tepat 4 digit (`2026`), menolak digit ke-5 | Tahun terkunci maksimal 4 digit, typo digit ke-5 dicegah | **PASS** |
| **TC-PEG-03** | Validasi jam selesai lebih kecil atau sama dengan jam mulai | 1. Buka modal ajukan lembur<br>2. Masukkan jam selesai lebih awal dari jam mulai<br>3. Klik Simpan | Jam mulai: `17:00`<br>Jam selesai: `16:00` | Sistem menolak pengajuan dengan notifikasi validasi: jam selesai harus lebih besar dari jam mulai | Muncul notifikasi validasi error waktu | **PASS** |
| **TC-PEG-04** | Filter periode bulan pada halaman lembur pribadi | 1. Buka menu `/lembur`<br>2. Pilih filter bulan "September 2026"<br>3. Amati tampilan dropdown dan data | Query parameter: `?bulan=2026-09` | Dropdown bulan tepat memilih opsi September 2026 (bukan default Desember), tabel menyaring data bulan September | Dropdown menampilkan September 2026, tabel sesuai periode | **PASS** |
| **TC-PEG-05** | Filter paginasi jumlah baris per halaman | 1. Di halaman lembur, ubah dropdown baris per halaman menjadi "25 / hal" | Opsi: 25 / hal | Dropdown terpilih 25/hal (bukan selalu 100/hal), tabel memuat maksimal 25 baris | Opsi 25 terpilih dengan tepat dan tabel memuat 25 baris | **PASS** |
| **TC-PEG-06** | Pengalihan tampilan Mode Tabel vs Mode Visual Timeline | 1. Klik tombol sakelar mode "Timeline" di toolbar | Toggle: Mode Timeline | Layout berpindah ke kartu kronologis vertikal dengan garis kontinu (*continuous vertical stem*) dan node dot status berwarna | Tampilan berubah ke kartu timeline bergaris aksis menyambung | **PASS** |
| **TC-PEG-07** | Tampilan status Jam Disetujui sebelum persetujuan | 1. Periksa baris pengajuan yang masih berstatus `pending` atau `menunggu_kabag` | Transaksi ID: 318 (`pending`) | Kolom "Jam Disetujui" menampilkan tanda strip `-` | Kolom Jam Disetujui menampilkan tanda strip `-` | **PASS** |
| **TC-PEG-08** | Tampilan Jam Disetujui & Lencana "Disesuaikan" setelah disetujui Kabag | 1. Periksa transaksi berstatus `approved` dengan jam disetujui berbeda dari jam diajukan | Jam diajukan: `16:00 - 20:00`<br>Jam disetujui: `16:00 - 18:00` | Kolom Jam Disetujui menampilkan jam resmi `16:00 - 18:00` disertai lencana (*badge*) oranye `Disesuaikan` | Tampil jam disetujui dan badge Disesuaikan | **PASS** |
| **TC-PEG-09** | Transparansi catatan Ketua Tim dan Kabag Umum | 1. Amati kolom Catatan pada pengajuan yang telah diproses | Transaksi dengan note Ketua dan note Kabag | Catatan dari Ketua Tim dan Kabag Umum ditampilkan terpisah dengan identitas aktor yang jelas (*quote strip layout*) | Catatan Ketua dan Kabag muncul terstruktur | **PASS** |
| **TC-PEG-10** | Pengubahan mandiri (*Edit*) pengajuan sebelum diproses | 1. Klik tombol Ubah (pensil) pada pengajuan berstatus `pending`<br>2. Ubah uraian kegiatan<br>3. Klik Simpan | Perubahan uraian kegiatan | Data pengajuan terbarui, kolom `user_edited` dan `tanggal_edited` terisi audit trail | Data terbarui dan status tetap `pending` | **PASS** |

---

### MODUL 3: Persetujuan & Koreksi Ketua Tim (Tahap 1)

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-KT-01** | Filter pencarian pegawai dan status pengajuan tim | 1. Buka `/ketua-tim/pengajuan`<br>2. Ketik nama pegawai di search bar atau pilih status | Pencarian: "Pristiana"<br>Status: "Menunggu Ketua" | Tabel menyaring pengajuan hanya menampilkan pegawai yang dicari dengan status yang sesuai | Daftar pengajuan tersaring presisi secara real-time | **PASS** |
| **TC-KT-02** | Persetujuan tahap 1 tanpa penyesuaian jam | 1. Klik tombol aksi (pensil) pada pengajuan pending<br>2. Form jam terisi default jam pengajuan pegawai<br>3. Klik Simpan Keputusan | Status: Setujui<br>Jam: Sesuai pengajuan | Transaksi di-update ke status `menunggu_kabag` (Badge Biru), `jam_mulai_disetujui` & `jam_selesai_disetujui` tersimpan di database | Status berubah `menunggu_kabag`, jam tersimpan di DB | **PASS** |
| **TC-KT-03** | Validasi wajib isi catatan jika Ketua Tim mengubah jam disetujui | 1. Buka modal keputusan<br>2. Potong jam selesai lembur<br>3. Kosongkan textarea catatan<br>4. Klik Simpan Keputusan | Jam diajukan: `20:00`<br>Jam disetujui: `18:30`<br>Catatan: *Kosong* | Sistem menolak simpan, menampilkan label bintang merah wajib isi, kursor otomatis fokus ke kolom catatan | Modal menolak simpan, memfokuskan kursor ke catatan | **PASS** |
| **TC-KT-04** | Persetujuan tahap 1 dengan penyesuaian jam dan catatan | 1. Potong jam selesai disetujui<br>2. Isi catatan penyesuaian<br>3. Klik Simpan | Jam disetujui: `18:30`<br>Catatan: "Target volume selesai pukul 18.30" | Keputusan berhasil disimpan, status menjadi `menunggu_kabag`, catatan Ketua Tim tersimpan rapi | Tersimpan sukses berstatus `menunggu_kabag` | **PASS** |
| **TC-KT-05** | Penolakan pengajuan pada tahap 1 oleh Ketua Tim | 1. Buka modal pengajuan pending<br>2. Pilih keputusan "Tolak"<br>3. Isi alasan penolakan<br>4. Klik Simpan | Keputusan: Tolak<br>Catatan: "Beban kerja dapat diselesaikan jam kerja reguler" | Status transaksi berubah menjadi `rejected` (Badge Merah), jam disetujui dikosongkan (`NULL`) | Transaksi berstatus `Ditolak`, jam disetujui null | **PASS** |
| **TC-KT-06** | Koreksi keputusan: Mengubah persetujuan menjadi Ditolak saat masih `menunggu_kabag` | 1. Pada transaksi berstatus `menunggu_kabag`, klik tombol "Koreksi"<br>2. Pilih tombol "Tolak"<br>3. Isi catatan koreksi<br>4. Klik Simpan Koreksi | Status asal: `menunggu_kabag`<br>Status baru: `rejected`<br>Catatan: "Dibatalkan atas revisi tim" | Tombol Tolak aktif (tidak terkunci), status berubah menjadi `rejected`, jam disetujui dikosongkan | Berhasil dikoreksi menjadi Ditolak tanpa error 422 | **PASS** |
| **TC-KT-07** | Proteksi tampilan kolom Jam Disetujui jika presensi BELUM diimpor | 1. Lihat baris pengajuan bulan berjalan yang belum ada data presensi BOS (`has_presensi = 0`) | Transaksi: Oktober 2026 (ID 319) | Kolom Jam Disetujui di tabel web menampilkan tanda strip `-` (karena kehadiran fisik belum terverifikasi) | Kolom Jam Disetujui menampilkan tanda strip `-` | **PASS** |
| **TC-KT-08** | Penayangan Jam Disetujui jika presensi SUDAH diimpor | 1. Lihat baris pengajuan yang tanggal lemburnya sudah memiliki data di `t_presensi` (`has_presensi = 1`) | Transaksi: 28 September 2026 (ID 314) | Kolom Jam Disetujui menampilkan jam persetujuan (misal: `18:00 - 20:00`) | Jam disetujui muncul sesuai data persetujuan | **PASS** |
| **TC-KT-09** | Update DOM AJAX real-time konsisten dengan status presensi | 1. Buka modal, klik Simpan Keputusan<br>2. Amati kolom Jam Disetujui tanpa me-refresh halaman peramban | Transaksi dengan `has_presensi = 0` | Kolom tabel tetap menampilkan `-` (tidak mendadak memunculkan jam sebelum presensi diimpor) | Kolom tabel tetap strip `-` secara konsisten | **PASS** |

---

### MODUL 4: Persetujuan & Koreksi Kabag Umum (Tahap 2 Final)

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-KBU-01** | Akses halaman terpadu Persetujuan Kabag Umum | 1. Login sebagai Kabag Umum<br>2. Klik menu sidebar "Persetujuan Kabag Umum" | Akun Kabag Umum | Menampilkan seluruh antrean pengajuan lembur yang berstatus `menunggu_kabag` dan pengajuan `pending` anggota Bagian Umum | Seluruh antrean pengajuan tampil lengkap | **PASS** |
| **TC-KBU-02** | Pembukaan tombol aksi "Setujui" untuk pengajuan `pending` Tim Bagian Umum | 1. Cari pengajuan anggota Bagian Umum yang berstatus `pending` (tanpa ketua tim lain) | Transaksi: Indah / Istiqomah status `pending` | Tombol aksi menampilkan "Setujui" (oranye) dan dapat diklik (tidak tertutup tanda `-`) | Tombol "Setujui" aktif dan membuka modal | **PASS** |
| **TC-KBU-03** | Persetujuan langsung status `pending` oleh Kabag Umum | 1. Buka modal pengajuan pending anggota Bagian Umum<br>2. Klik "Setujui Final"<br>3. Klik Simpan | Status: `pending` $\rightarrow$ `approved` | Status langsung disetujui final (`approved`), kolom `approved_at` dan `approved_kabag_at` terisi | Transaksi berstatus `approved` tanpa error 422 | **PASS** |
| **TC-KBU-04** | Persetujuan Tahap 2 untuk pengajuan status `menunggu_kabag` | 1. Klik tombol "Proses" pada pengajuan `menunggu_kabag`<br>2. Verifikasi jam disetujui Ketua Tim<br>3. Klik Simpan Keputusan | Status: `menunggu_kabag` $\rightarrow$ `approved` | Status transaksi resmi menjadi `approved` (Disetujui Final), tercatat `approved_kabag_at = now()` | Transaksi berstatus `Disetujui Final` (Badge Hijau) | **PASS** |
| **TC-KBU-05** | Validasi wajib isi Catatan Kabag saat jam disetujui diubah | 1. Ubah jam selesai pada modal Kabag Umum<br>2. Kosongkan textarea Catatan Kabag Umum<br>3. Klik Simpan | Jam diubah, catatan kosong | Sistem menolak request (HTTP 422), muncul alert wajib mengisi catatan Kabag Umum | Muncul validasi wajib isi catatan Kabag | **PASS** |
| **TC-KBU-06** | Penolakan transaksi pada Tahap 2 oleh Kabag Umum | 1. Buka modal keputusan Kabag Umum<br>2. Pilih "Tolak"<br>3. Isi Catatan Kabag<br>4. Klik Simpan | Keputusan: Tolak<br>Catatan Kabag: "Alokasi anggaran tim telah mencapai kuota" | Status transaksi menjadi `rejected`, catatan Kabag tersimpan, jam disetujui dikosongkan | Transaksi berstatus `Ditolak` (Badge Merah) | **PASS** |
| **TC-KBU-07** | Fitur Koreksi keputusan oleh Kabag Umum | 1. Pada transaksi yang sudah `approved` atau `rejected`, klik tombol "Koreksi"<br>2. Sesuaikan jam/catatan<br>3. Simpan | Data koreksi keputusan | Transaksi diperbarui dengan aman, tercatat `user_edited` Kabag Umum | Keputusan terkoreksi dengan audit trail valid | **PASS** |
| **TC-KBU-08** | Proteksi tampilan Jam Disetujui di tabel Kabag Umum | 1. Amati kolom Jam Disetujui untuk transaksi yang belum diimpor presensinya | Transaksi belum ada presensi | Kolom menampilkan strip `-`. Baru menampilkan rentang waktu jika presensi sudah diimpor | Kolom menampilkan `-` secara konsisten | **PASS** |

---

### MODUL 5: Monitoring Pengajuan & Pembatalan Admin

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-ADM-01** | Filter status pengajuan via Custom Floating Card | 1. Buka `/admin/pengajuan`<br>2. Buka dropdown filter status (desktop/mobile)<br>3. Pilih status "Menunggu Kabag" | Filter: `menunggu_kabag` | Dropdown custom menutup otomatis, tabel menyaring transaksi berstatus menunggu kabag | Tabel memuat transaksi menunggu kabag | **PASS** |
| **TC-ADM-02** | Pengurutan prioritas status transaksi (*Priority Sort*) | 1. Pilih opsi urutkan "Berdasarkan Prioritas" | Sort: Prioritas | Transaksi diurutkan dengan hierarki: `menunggu_kabag` teratas $\rightarrow$ `pending` $\rightarrow$ `approved` $\rightarrow$ `rejected` | Urutan transaksi tersusun sesuai prioritas antrean | **PASS** |
| **TC-ADM-03** | Pembatalan transaksi lembur oleh Admin (*Cancel Transaction*) | 1. Pada transaksi lembur, klik icon keputusan Admin<br>2. Pilih status "Batalkan"<br>3. Masukkan catatan alasan pembatalan<br>4. Klik Simpan | Status: `cancelled`<br>Catatan: "Pembatalan dinas mendadak" | Transaksi berstatus `cancelled` (Badge Abu-abu), tercatat audit trail `user_edited` Admin | Status berubah `Dibatalkan`, catatan tersimpan | **PASS** |
| **TC-ADM-04** | Validasi catatan Admin saat penyesuaian jam disetujui | 1. Admin mengubah jam disetujui transaksi<br>2. Catatan dikosongkan<br>3. Simpan | Jam diubah, catatan kosong | Backend controller me-reject dengan pesan: "Catatan wajib diisi jika jam lembur yang disetujui berbeda dari jam pengajuan." | Validasi wajib isi catatan Admin bekerja aktif | **PASS** |
| **TC-ADM-05** | Pencatatan universal Audit Trail (`user_edited` & `tanggal_edited`) | 1. Admin mengubah keputusan transaksi<br>2. Periksa baris data di database `t_transaksi` | Transaksi ID yang diedit | Kolom `user_edited` terisi nama/NIP Admin dan `tanggal_edited` terisi timestamp terkini | Kolom audit trail terisi data aktor pengubah | **PASS** |
| **TC-ADM-06** | Inspeksi status kehadiran presensi via modal Admin | 1. Klik tautan status presensi di kolom tabel Admin | Transaksi lembur aktif | Muncul modal detail informasi kehadiran fisik: jam kedatangan, jam kepulangan riil, dan status mesin BOS | Modal detail presensi tampil informatif | **PASS** |
| **TC-ADM-07** | Proteksi kolom Jam Disetujui di sisi Admin | 1. Periksa kolom Jam Disetujui transaksi tanpa presensi | Transaksi `has_presensi = 0` | Kolom menampilkan strip `-`. Jika transaksi berstatus `rejected`, kolom juga menampilkan `-` | Menampilkan strip `-` secara disiplin | **PASS** |
| **TC-ADM-08** | Real-time DOM update saat Admin menyimpan keputusan | 1. Simpan keputusan di modal Admin<br>2. Periksa baris tabel tanpa me-refresh halaman | Transaksi `has_presensi = 0` | Kolom jam disetujui tetap strip `-`, badge status langsung berubah warna seketika | DOM terbarui real-time tanpa refresh halaman | **PASS** |

---

### MODUL 6: Impor Presensi BOS & Integrasi Kehadiran

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-PRES-01** | Buka modal unggah berkas presensi | 1. Buka menu `/admin/presensi`<br>2. Klik tombol oranye "Upload Presensi" | Akses Admin | Modal unggah presensi terbuka dengan area drag-and-drop dan dropdown Target Tahun | Modal unggah presensi terbuka responsif | **PASS** |
| **TC-PRES-02** | Unggah berkas spreadsheet BOS resmi BPS (`.xlsx`) | 1. Unggah berkas `Rekap Presensi 9 September.xlsx`<br>2. Pilih target tahun: "Tahun 2026 (Sesuai Periode Lembur Aktif)"<br>3. Klik Upload | Berkas: `Rekap Presensi 9 September.xlsx`<br>Target Tahun: `2026` | Berkas diproses, seluruh 2.832 data presensi tersimpan ke `t_presensi` dengan periode September 2026, alert sukses hijau muncul | Berhasil diunggah dan disimpan untuk periode September 2026 | **PASS** |
| **TC-PRES-03** | Ekstraksi sel 3 baris (Jam Masuk, Jam Pulang, Status) | 1. Periksa data hasil impor sel normal (misal: Saniman tgl 1) | Data mentah sel:<br>`06:33:01\n16:11:02\nWFO\n` | `jam_mulai` terisi `2026-09-01 06:33:01`, `jam_selesai` terisi `2026-09-01 16:11:02`, `status` terisi `WFO` | Jam datang, jam pulang, dan status terpetakan presisi | **PASS** |
| **TC-PRES-04** | Ekstraksi sel tanpa tap pulang (Jam Pulang tanda strip `-`) | 1. Periksa baris data pegawai yang tidak tap pulang (misal tgl 20) | Data mentah sel:<br>`07:13:00\n-\nWFO\n` | `jam_mulai` terisi `07:13:00`, `jam_selesai` menjadi `NULL` (tidak error), `status` terisi `WFO` | `jam_selesai` bernilai `NULL` tanpa error | **PASS** |
| **TC-PRES-05** | Ekstraksi sel dinas / cuti (1 baris teks: `PD`, `TB`, `CT`, `TK`) | 1. Periksa sel pegawai yang sedang Perjalanan Dinas atau Cuti | Data mentah sel:<br>`PD` atau `CT` | `jam_mulai = NULL`, `jam_selesai = NULL`, `status = PD` atau `CT` | Status dinas/cuti tersimpan dengan jam NULL | **PASS** |
| **TC-PRES-06** | Penanganan sel akhir pekan / hari libur kosong (`\n\n\n`) | 1. Periksa sel hari Sabtu, Minggu, atau libur nasional | Data mentah sel:<br>`\n\n\n` | Pembersih `trim()` mendeteksi sel kosong dan melompatinya tanpa mengotori basis data | Sel libur dilewati bersih | **PASS** |
| **TC-PRES-07** | Proteksi batas hari maksimal bulan (*Anti Invalid Date / 31 September*) | 1. Periksa pembacaan kolom tanggal 31 pada bulan September (30 hari) | Kolom ke-32 (Tanggal 31) pada bulan September | Logika `$daysInMonth` menghentikan loop di tanggal 30, mencegah terbentuknya tanggal invalid `2026-09-31` | Tidak ada error MySQL Invalid Date value | **PASS** |
| **TC-PRES-08** | Ekstraksi toleran variasi header tahun dan bulan | 1. Berkas dengan format sel A5 `"Tahun : 2025"` dan A6 `"Bulan : September"` | Variasi spasi/kapitalisasi teks header | Regex mengekstrak angka 4 digit tahun dan memetakan nama bulan ke integer (September = 9) tanpa gagal | Tahun dan bulan terdeteksi sempurna | **PASS** |
| **TC-PRES-09** | Koreksi otomatis lembur background via `KoreksiLembur` Trait | 1. Setelah impor presensi selesai, sistem mengeksekusi `koreksiUntukTanggal()` | Transaksi lembur approved September 2026 | Jam selesai disetujui dipotong jika melebihi kepulangan presensi riil atau melebihi batas 4/6 jam, status `eligible` terisi (1 atau 0) | Lembur disinkronkan otomatis dengan bukti presensi | **PASS** |
| **TC-PRES-10** | Tampilan kalender presensi interaktif per pegawai di menu Admin | 1. Di `/admin/presensi`, pilih periode September 2026<br>2. Cari nama pegawai | Pegawai: Saniman / Yuli | Kalender menampilkan kotak tanggal berwarna lengkap dengan jam masuk, jam pulang, dan status WFO/dinas | Kalender terisi data presensi visual | **PASS** |

---

### MODUL 7: Pembuatan Dokumen Kedinasan (SPKL, Laporan, Daftar Hadir)

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-DOK-01** | Pembuatan berkas SPKL PDF (*Compact Natural Flow*) | 1. Buka menu Dokumen Admin<br>2. Pilih periode bulan, pilih KBU & PPK<br>3. Generate SPKL PDF | Transaksi lembur September 2026 (9 pegawai) | Halaman 1 langsung terisi penuh tanpa jeda kosong melompong, dokumen mengalir alami (*natural flow*) tepat 2 halaman | Dokumen SPKL tampil padat, rapi, dan tepat 2 halaman | **PASS** |
| **TC-DOK-02** | Tinggi baris dinamis pada ekspor SPKL Excel (*Auto Row Height*) | 1. Unduh berkas SPKL format Excel (`.xlsx`)<br>2. Buka lembar kerja di spreadsheet | Pegawai dengan uraian pekerjaan panjang | Baris tabel otomatis menyesuaikan tinggi (`setRowHeight(-1)`), seluruh uraian pekerjaan terbaca utuh tanpa terpotong (*no cut-off*) | Seluruh teks uraian panjang tampil penuh tanpa terpotong | **PASS** |
| **TC-DOK-03** | Format identitas NIP Baru 18 digit pada dokumen resmi | 1. Amati baris pegawai pada dokumen cetak SPKL dan Laporan | NIP Baru pegawai | Tercantum NIP Baru 18 digit di bawah nama lengkap pegawai (bukan NIP lama) | NIP Baru 18 digit tercetak konsisten | **PASS** |
| **TC-DOK-04** | Pengurutan kronologis tanggal menaik (*Ascending*) pada Laporan Hasil Lembur | 1. Generate Laporan Hasil Kerja Lembur (Web & PDF) | Pengajuan lembur multi-tanggal | Baris kegiatan terurut secara kronologis dari tanggal awal hingga akhir bulan (*ascending order*) | Data tersusun rapi berurutan tanggal menaik | **PASS** |
| **TC-DOK-05** | Filter kategori PNS dan PPPK pada menu Daftar Hadir | 1. Buka menu `/admin/daftar-hadir`<br>2. Pilih kategori PNS lalu PPPK via dropdown | Kategori: PNS / PPPK | Tabel menyaring dan memisahkan daftar hadir khusus PNS atau khusus PPPK | Daftar hadir terpisah akurat berdasarkan kategori | **PASS** |
| **TC-DOK-06** | Cetak langsung Daftar Hadir ke printer peramban (*Print Direct*) | 1. Pada menu Daftar Hadir, klik tombol printer "Cetak" | Tombol Cetak Langsung | Dialog Print Preview peramban otomatis terbuka dalam format A4 resmi dinas tanpa perlu mengunduh PDF manual | Jendela Print Preview peramban terbuka otomatis | **PASS** |
| **TC-DOK-07** | Ketepatan jam pulang pada Daftar Hadir (Murni Presensi BOS) | 1. Amati kolom Jam Pulang pada Daftar Hadir (web, PDF, cetak) | Transaksi lembur dengan presensi BOS | Jam pulang tercantum sesuai jam kepulangan fisik mesin BOS (contoh: `18:54`), bukan jam selesai lembur atau batas 4 jam | Jam pulang murni sesuai presensi BOS fisik | **PASS** |
| **TC-DOK-08** | Filter kategori PNS dan PPPK pada Rekapitulasi Lembur | 1. Buka `/admin/rekapitulasi`<br>2. Pilih kategori PNS / PPPK<br>3. Klik Unduh Excel | Rekapitulasi kategori PNS/PPPK | Berkas Excel memuat rekapitulasi pegawai terpisah sesuai kategori yang dipilih dengan judul sheet dinamis | Rekapitulasi Excel terpisah sesuai kategori | **PASS** |
| **TC-DOK-09** | Penandatangan resmi dokumen dinas BPS (PPK & KBU) | 1. Periksa blok tanda tangan pada berkas SPKL dan Laporan | Penandatangan resmi | Kiri bawah: PPK (Ibu Suci Budi Utami SST, M.Si.), Kanan bawah: KBU (Bapak Joko Suwarjo S.Si, M.Si.) lengkap dengan NIP resmi | Tanda tangan pejabat dinas tercetak tepat dan sah | **PASS** |

---

### MODUL 8: Manajemen Pengguna & Suksesi Pejabat

| ID Kasus Uji | Skenario Pengujian | Langkah Pengujian | Data Masukan (*Test Data*) | Hasil yang Diharapkan | Hasil Aktual | Status |
|:---|:---|:---|:---|:---|:---|:---:|
| **TC-USR-01** | Akses modul Manajemen Pengguna oleh Superadmin | 1. Login sebagai Superadmin<br>2. Buka menu `/admin/manajemen-user` | Akun Superadmin | Antarmuka manajemen user terbuka menampilkan daftar admin, pejabat aktif, dan kontrol suksesi | Halaman manajemen pengguna terbuka penuh | **PASS** |
| **TC-USR-02** | Suksesi dinamis Pejabat Kepala Bagian Umum (KBU) | 1. Klik ganti Kabag Umum<br>2. Pilih pegawai pengganti<br>3. Masukkan nomor SK dan tanggal mulai berlaku<br>4. Simpan | Pegawai terpilih, nomor SK | Tabel `m_pejabat` menonaktifkan pejabat lama dan mengaktifkan pejabat baru, seluruh wewenang approval beralih otomatis | Pejabat KBU baru aktif secara dinamis | **PASS** |
| **TC-USR-03** | Suksesi dinamis Pejabat Pembuat Komitmen (PPK) | 1. Ganti pejabat PPK via modal manajemen pejabat<br>2. Generate dokumen SPKL baru | Pejabat PPK pengganti | Nama dan NIP penandatangan PPK pada dokumen SPKL otomatis berubah mengikuti pejabat aktif di `m_pejabat` | Penandatangan SPKL terbarui otomatis | **PASS** |
| **TC-USR-04** | Proteksi wewenang dan penurunan hak akses Superadmin | 1. Coba hapus atau turunkan role akun Superadmin terakhir | Akun Superadmin aktif | Sistem menolak aksi untuk mencegah kekosongan pengelola tingkat tertinggi (*lockout protection*) | Sistem memproteksi superadmin dari penghapusan | **PASS** |

---

## 🎯 4. KESIMPULAN HASIL PENGUJIAN BLACK BOX

1. **Integritas Fungsional (100% PASS):**  
   Dari total **66 kasus uji** yang dieksekusi melintasi 8 modul utama, seluruhnya menghasilkan status **PASS** (Berhasil sesuai spesifikasi yang diharapkan).
2. **Keberhasilan Penyesuaian Alur Kerja (*Business Logic Compliance*):**
   - Alur persetujuan bertingkat (*Pegawai $\rightarrow$ Ketua Tim $\rightarrow$ Kabag Umum $\rightarrow$ Dokumen SPKL*) terbukti mengalir tanpa kendala.
   - Fitur koreksi penolakan oleh Ketua Tim saat pengajuan berstatus `menunggu_kabag` bekerja fleksibel tanpa tertahan status kunci.
   - Pengajuan anggota Bagian Umum yang berstatus `pending` dapat diproses langsung oleh Kabag Umum.
   - Kolom Jam Disetujui terproteksi secara tangguh: hanya muncul apabila kehadiran fisik telah diimpor (`has_presensi = 1`), serta tetap aman pada pembaruan AJAX real-time.
   - Berkas spreadsheet presensi BOS diekstrak dengan akurasi 100%, lengkap dengan proteksi batas hari kalender dan pilihan fleksibel target tahun 2026.
3. **Kesiapan Penerapan (*Production Readiness*):**  
   Sistem Informasi Manajemen Lembur BPS Jawa Tengah (**TEMPE DELE**) dinyatakan **stabil, handal, dan siap dioperasikan penuh pada peladen produksi (*live server*)**.

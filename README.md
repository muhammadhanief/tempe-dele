<div align="center">

# 📋 TEMPE DELE
### Sistem Pengelolaan Dokumen Lembur Pegawai
**Badan Pusat Statistik (BPS) Provinsi Jawa Tengah**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

</div>

---

## 📖 Tentang Aplikasi

**TEMPE DELE** adalah sistem informasi berbasis web yang dirancang khusus untuk mengelola, memvalidasi, dan mengotomatisasi seluruh alur dokumen lembur pegawai di lingkungan **BPS Provinsi Jawa Tengah**.

Sistem ini mengintegrasikan otentikasi **Single Sign-On (SSO) BPS**, sinkronisasi struktur tim kerja **KIPAPP**, validasi data kehadiran presensi riil, tanda tangan digital, alur persetujuan bertingkat (*tiered approval*), serta penerbitan dokumen administrasi lembur resmi (SPKL, Daftar Hadir, Laporan Lembur, dan Rekapitulasi).

---

## ✨ Fitur-Fitur Utama

### 1. 🔐 Integrasi SSO & API Eksternal BPS
- **SSO BPS Authentication**: Autentikasi terpusat pegawai BPS Jawa Tengah via API Connect.
- **SSO Attribute Fetcher**: Penarikan golongan kepangkatan pegawai otomatis untuk perhitungan uang lembur dan makan.
- **KIPAPP Tim Kerja Sync**: Penarikan struktur tim fungsional dan penugasan anggota secara periodik.

### 2. ⚡ Alur Persetujuan Bertingkat (*Tiered Approval Workflow*)
- **Tim Fungsional Lain (SID, Humas, Sosial, Distribusi, dll.)**:
  - `Pegawai Mengajukan` $\rightarrow$ `Persetujuan Ketua Tim` $\rightarrow$ `Persetujuan Akhir Kabag Umum` $\rightarrow$ `Selesai (Disetujui Final)`.
- **Tim Bagian Umum**:
  - Pengajuan pegawai Bagian Umum langsung masuk ke antrean persetujuan **Kepala Bagian Umum** (menghindari duplikasi tahapan).
- **Penolakan Transparan**: Ketua Tim maupun Kabag Umum dapat menolak pengajuan lembur dengan catatan alasan yang terdokumentasi terpisah.

### 3. ⏱️ Koreksi Otomatis Presensi Riil
- Sinkronisasi data presensi harian pegawai untuk memverifikasi jam pulang aktual.
- Otomasi validasi durasi lembur (minimal 2 jam).
- Pengecekan kepatuhan status kehadiran kantor (WFO/WFOL) serta jam kehadiran pagi.

### 4. 📑 Generator Dokumen Resmi & Ekspor Laporan
- **Surat Perintah Kerja Lembur (SPKL)**: PDF otomatis berstandar kedinasan.
- **Daftar Hadir Lembur**: Rekap absensi lembur per penugasan tim.
- **Laporan Pelaksanaan Lembur**: Uraian output hasil kerja pegawai lembur.
- **Rekapitulasi Bulanan**: Ekspor rekapitulasi data lembur ke format Excel dan PDF.

### 5. 🖊️ Digital Signature & Upload Dokumentasi
- Pembubuhan tanda tangan langsung secara digital pada saat pengajuan lembur.
- Unggah berkas dokumentasi hasil lembur untuk pertanggungjawaban kegiatan.

### 6. 🏛️ Manajemen Pejabat Dinamis (Tanpa Hardcode)
- Pengaturan pejabat struktural (Kepala BPS, Kepala Bagian Umum, PPK) dikelola dinamis melalui database (`m_pejabat`), sehingga pergantian atau mutasi pejabat tidak memerlukan perubahan kode aplikasi.

---

## 👥 Struktur Role & Hak Akses

| Role | Cakupan Wewenang |
|:---|:---|
| **Pegawai (`user`)** | Mengajukan lembur, melihat status tahapan, tanda tangan digital, unggah dokumentasi, dan rekap lembur mandiri. |
| **Ketua Tim (`ketua_tim`)** | Dashboard tim, meninjau presensi anggota, menyetujui pengajuan (naik ke Kabag), atau menolak pengajuan anggota timnya. |
| **Kabag Umum (`ketua_tim` + Pejabat)** | Dashboard monitoring seluruh satker, menu tunggal **Persetujuan Kabag Umum** untuk persetujuan akhir seluruh lembur BPS. |
| **Pimpinan (`pimpinan`)** | Dashboard eksekutif pemantauan lembur seluruh kantor BPS Provinsi Jawa Tengah (Kepala BPS). |
| **Admin / Superadmin** | Manajemen data pegawai, sinkronisasi tim kerja, penetapan pejabat aktif, pengelolaan tarif lembur, dan rekapitulasi satker. |

---

## 🛠️ Prasyarat Sistem (*Requirements*)

- **PHP**: `>= 8.2`
  - Ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `zip`, `fileinfo`
- **Web Server**: Apache / Nginx
- **Database**: MySQL `>= 8.0` atau MariaDB `>= 10.4`
- **Dependency Manager**: Composer `>= 2.x`
- **Node.js**: `>= 18.x` & **NPM**: `>= 9.x`

---

## 💻 Panduan Instalasi Lokal (*Local Development Setup*)

### 1. Clone Repositori
```bash
git clone https://github.com/whoNann/tempe-dele-update.git
cd tempe-dele-update
```

### 2. Instalasi Dependency Backend & Frontend
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
Salin file konfigurasi contoh:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database dan API pada `.env`:
```env
APP_NAME="TEMPE DELE"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lembur
DB_USERNAME=root
DB_PASSWORD=
```

Generate application key:
```bash
php artisan key:generate
```

### 4. Migrasi Database
Jalankan migrasi tabel aplikasi:
```bash
php artisan migrate
```

Buat symbolic link untuk storage upload berkas & tanda tangan:
```bash
php artisan storage:link
```

### 5. Jalankan Server Lokal
Jalankan development server Laravel:
```bash
php artisan serve
```
Pada terminal terpisah, jalankan Vite compiler:
```bash
npm run dev
```
Akses aplikasi melalui browser di: **`http://127.0.0.1:8000`**

---

## 📚 Dokumentasi Pendukung

Informasi teknis dan panduan operasional lebih detail dapat dibaca pada dokumen berikut:

* 📄 **[DOKUMENTASI_PERUBAHAN_ALUR_LEMBUR.md](DOKUMENTASI_PERUBAHAN_ALUR_LEMBUR.md)**: Rincian latar belakang, arsitektur alur bertingkat, serta daftar kode yang diubah.
* 🚀 **[PANDUAN_DEPLOY_SERVER.md](PANDUAN_DEPLOY_SERVER.md)**: Panduan checklist teknis untuk proses deploy ke server produksi (cPanel / VPS).
* 🔑 **[AKUN_TESTING.md](AKUN_TESTING.md)**: Daftar akun pengujian lokal dan panduan skenario testing step-by-step.

---

<div align="center">
    <sub>Dikembangkan untuk <b>Badan Pusat Statistik (BPS) Provinsi Jawa Tengah</b></sub>
</div>

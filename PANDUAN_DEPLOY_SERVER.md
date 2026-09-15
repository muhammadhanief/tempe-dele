# 🚀 Panduan & Checklist Deploy ke Server Produksi

Dokumen ini berisi panduan teknis langkah demi langkah untuk menerapkan perubahan sistem lembur bertingkat (Kabag Umum) ke server produksi / live.

---

## ⚠️ 1. Update Database Produksi (Paling Krusial!)

Di database server produksi, tabel `t_transaksi` perlu diperbarui untuk mendukung kolom catatan Kabag, tanggal persetujuan Kabag, dan pelebaran status.

### A. Cara Rekomendasi (Lewat phpMyAdmin / Database Client)
Buka phpMyAdmin di cPanel server produksi Anda, pilih database lembur, buka tab **SQL**, lalu jalankan query berikut:

```sql
ALTER TABLE t_transaksi ADD COLUMN note_kabag TEXT NULL AFTER note;
ALTER TABLE t_transaksi ADD COLUMN approved_kabag_at DATETIME NULL AFTER approved_at;
ALTER TABLE t_transaksi MODIFY COLUMN status VARCHAR(30) NULL;
```

### B. Alternatif via Terminal SSH (Artisan Migration Spesifik)
Jika Anda memiliki akses terminal SSH di server dan ingin menjalankan via Laravel Migration:

```bash
php artisan migrate --path=database/migrations/2026_09_15_000001_add_kabag_approval_to_t_transaksi.php
php artisan migrate --path=database/migrations/2026_09_15_000002_widen_status_column_in_t_transaksi.php
```

> [!WARNING]
> **Hindari menjalankan `php artisan migrate` polosan tanpa `--path` di server!**
> Karena ada beberapa migrasi lama bawaan repository yang kolomnya sudah ada di database, menjalankan `php artisan migrate` secara umum berisiko memunculkan error *Duplicate column*.

---

## 🛡️ 2. File Konfigurasi `.env` di Server Produksi

Pastikan file `.env` di server produksi:
1. **Tidak tertimpa** oleh konfigurasi lokal (file `.env` sudah berada dalam `.gitignore`).
2. Menggunakan konfigurasi standar produksi:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
3. Menggunakan kredensial database server produksi yang benar.

> [!NOTE]
> **Keamanan Testing Mode:**
> Karena `APP_ENV=production`, fitur auto-login testing di halaman login (`login.blade.php`) dan route `/dev-login/{nip}` otomatis **hilang dan dinonaktifkan (404 Not Found)** di server live.

---

## 📦 3. Asset Frontend (`public/build`)

Aplikasi menggunakan Vite untuk mem-bundle aset CSS dan JavaScript (TailwindCSS). Karena folder `public/build` diabaikan oleh `.gitignore`, perhatikan metode berikut:

* **Skenario A: Server memiliki Terminal SSH & Node.js**
  Jalankan perintah berikut di direktori proyek server:
  ```bash
  npm install
  npm run build
  ```

* **Skenario B: Server cPanel / Shared Hosting (Tanpa Node.js)**
  Di komputer lokal Anda sudah berhasil dijalankan `npm run build`. Cukup upload / salin folder lokal:
  ```
  public/build/
  ```
  ke dalam direktori:
  ```
  public/build/
  ```
  pada server cPanel produksi Anda.

---

## 🧹 4. Bersihkan Cache Laravel di Server

Setelah menarik kode terbaru (`git pull`) atau mengunggah file baru, jalankan perintah pembersihan cache agar perubahan routing, view, dan konfigurasi segera terbaca:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🏛️ 5. Pastikan Data Pejabat Kabag Umum Aktif di `m_pejabat`

Sistem membaca wewenang Kabag Umum secara dinamis dari tabel `m_pejabat`.

Pastikan di database server produksi pada tabel `m_pejabat`:
* Terdapat record dengan kolom `jabatan` bernilai: **`Kepala Bagian Umum`**
* Kolom `status` bernilai: **`aktif`**
* Kolom `nip` atau `nip_lama` terisi sesuai dengan NIP pejabat Kepala Bagian Umum yang sedang menjabat (misal: Bpk. Joko Suwarjo).

*(Pengaturan ini juga dapat dikelola langsung oleh akun Admin melalui menu **Admin $\rightarrow$ Kelola Pejabat**).*

---

## ✅ 6. Checklist Verifikasi Akhir (Sanity Check)

Setelah langkah 1 s.d. 5 selesai:
- [ ] Buka halaman login di browser: pastikan panel auto-login testing tidak muncul di mode produksi.
- [ ] Login sebagai Pegawai Tim Bagian Umum: ajukan lembur, pastikan status awal langsung `Menunggu Kabag`.
- [ ] Login sebagai Ketua Tim lain (misal Tim SID): menu "Persetujuan Kabag Umum" tidak ada, yang ada adalah "Pengajuan Lembur".
- [ ] Login sebagai Kabag Umum:
  - Menu "Pengajuan Lembur" tidak ada (otomatis digantikan menu terpadu **"Persetujuan Kabag Umum"**).
  - Buka menu **Persetujuan Kabag Umum**: pengajuan dari seluruh tim satker dapat dipantau dan di-ACC.

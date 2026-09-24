# 🚀 Panduan & Checklist Deploy ke Server Produksi

Dokumen ini berisi panduan teknis langkah demi langkah untuk menerapkan perubahan sistem lembur bertingkat (Kabag Umum), penguatan keamanan otorisasi (*Role-Based Access Control*), dan pembaruan sistem ke server produksi / live.

---

## ⚠️ 1. Update Database Produksi (Paling Krusial!)

Di database server produksi, tabel `t_transaksi` perlu diperbarui untuk mendukung kolom catatan Kabag, tanggal persetujuan Kabag, dan pelebaran kolom status.

### A. Cara Rekomendasi (Lewat phpMyAdmin / Database Client cPanel)
Buka phpMyAdmin di cPanel server produksi Anda, pilih database lembur, buka tab **SQL**, lalu jalankan kueri berikut:

```sql
ALTER TABLE t_transaksi ADD COLUMN note_kabag TEXT NULL AFTER note;
ALTER TABLE t_transaksi ADD COLUMN approved_kabag_at DATETIME NULL AFTER approved_at;
ALTER TABLE t_transaksi MODIFY COLUMN status VARCHAR(30) NULL;
```

### B. Alternatif via Terminal SSH (Artisan Migration Spesifik)
Jika Anda memiliki akses terminal SSH di server dan ingin menjalankan migrasi via Laravel Migration:

```bash
php artisan migrate --path=database/migrations/2026_09_15_000001_add_kabag_approval_to_t_transaksi.php
php artisan migrate --path=database/migrations/2026_09_15_000002_widen_status_column_in_t_transaksi.php
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
1. 📄 **`app/Http/Middleware/CheckRole.php`** *(Wajib ada, jika tertinggal aplikasi akan error Class Not Found)*.
2. 📄 **`bootstrap/app.php`** *(Memuat pendaftaran alias middleware `role`)*.
3. 📄 **`routes/web.php`** *(Memuat proteksi rute RBAC dan penguncian dev-login)*.
4. 📄 **`app/Http/Controllers/ketuatim/KabagUmumPengajuanController.php`** *(Controller persetujuan Kabag)*.
5. 📄 **`resources/views/kabag-umum/pengajuan.blade.php`** *(View persetujuan Kabag)*.
6. 📄 **`resources/views/partials/sidebar.blade.php`** *(Navigasi menu Kabag)*.
7. 📄 Seluruh file controller, model, dan views lainnya yang telah dimodifikasi.

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
- [ ] **Pegawai Tim Bagian Umum**: Buat pengajuan lembur baru, pastikan status awal langsung **`Menunggu Kabag`**.
- [ ] **Pegawai Tim Lain (misal Tim SID)**: Buat pengajuan lembur baru, pastikan status awal **`Diproses` (Menunggu Ketua Tim)**.
- [ ] **Ketua Tim**: Buka menu persetujuan, setujui pengajuan anggota tim ➔ pastikan status naik menjadi **`Menunggu Kabag`**.
- [ ] **Kepala Bagian Umum**:
  - Muncul menu tunggal **"Persetujuan Kabag Umum"**.
  - Buka menu tersebut: periksa pengajuan anggota, berikan persetujuan final ➔ pastikan status berubah menjadi **`Disetujui` (Disetujui Final)** dan status terkunci dari perubahan sepihak.

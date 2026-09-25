# 🤖 Antigravity Agent Guidelines & Rules

Dokumen ini berisi aturan kerja (*rules*) wajib bagi AI Agent yang bekerja di repositori ini.

---

## 📌 Aturan Dokumentasi & Deployment (Wajib Dipatuhi)

1. **Selalu Perbarui `DOKUMENTASI_PERUBAHAN_ALUR_LEMBUR.md`**
   * Setiap kali ada perubahan fitur, perbaikan bug, penambahan logika alur kerja, modifikasi database, atau pembaruan UI/UX sekecil apa pun, **wajib** mencatat rincian perubahannya pada file `DOKUMENTASI_PERUBAHAN_ALUR_LEMBUR.md`.
   * Jangan membuat file dokumentasi baru yang terpisah. Selalu tambahkan bagian baru (*section*) di dalam file ini agar riwayat perubahan tetap terpusat dan rapi.

2. **Selalu Perbarui `PANDUAN_DEPLOY_SERVER.md`**
   * Jika ada perubahan yang perlu diterapkan ke server produksi (*production/live*), **wajib** memperbarui file `PANDUAN_DEPLOY_SERVER.md`.
   * Hal yang harus selalu diperbarui jika ada perubahan relevan:
     - **Bagian 1 (Database Produksi):** Tambahkan kueri SQL manual (`ALTER TABLE ...`) untuk phpMyAdmin dan perintah artisan spesifik (`php artisan migrate --path=database/migrations/...`).
     - **Bagian 2 (Pembaruan Berkas):** Daftarkan file baru atau file controller/view/migration yang perlu diunggah manual jika server menggunakan cPanel/FTP.
     - **Bagian 7 (Checklist Verifikasi Akhir):** Tambahkan poin pengujian fitur baru (*sanity check*).

3. **Integritas Database Produksi:**
   * Di server produksi, **hindari** menyarankan perintah `php artisan migrate` secara polosan tanpa parameter `--path`, karena berisiko memunculkan error *Duplicate column* dari migrasi lama bawaan repositori.
   * Selalu prioritaskan penyediaan kueri SQL langsung yang dapat dieksekusi di phpMyAdmin cPanel dan perintah artisan migration yang spesifik per berkas.

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

4. **Disiplin Git Commit Teratur (Jangan Menumpuk Perubahan):**
   * Lakukan commit secara berkala dan terpisah per unit fungsional (*discrete logical commits*) segera setelah suatu fitur, perbaikan bug, atau sub-task selesai dikerjakan dan diverifikasi.
   * **Hindari menumpuk perubahan** terlalu banyak berkas (*uncommitted changes*) secara sekaligus. Selalu pisahkan commit untuk kode fitur/logika, tampilan UI, berkas utilitas/dokumen, dan dokumentasi panduan.


<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `antislop.md` (core) and then the skill for the task:
- UI / visual: `.agents/skills/antislop-ui/SKILL.md`
- Copy & text: `.agents/skills/antislop-copywriting/SKILL.md`
- People: `.agents/skills/antislop-human/SKILL.md`
- Mobile / responsive: `.agents/skills/antislop-layoutmobile/SKILL.md`
- Code comments: `.agents/skills/antislop-code/SKILL.md`
Before starting, ask the user when antislop applies: during the work, or after it is done.
To update antislop later: download `antislop.md` again, or run `npx antislop-ai --update` if it was installed as skill folders.
<!-- antislop:end -->

---

## 📌 Konteks Magang MBKM & Laporan Bulanan Oktober 2026

1. **Kelanjutan Masa Magang BPS Jateng:**
   - Proyek sebelumnya adalah **SWARA JAGAD** (`D:\TUGAS ITTP\BPS - MAGANG\swara-jagad`) yang telah selesai dan resmi di-handover ke Bappeda Jateng pada **Minggu I Oktober 2026 (01 - 07 Oktober 2026)**.
   - Proyek **Sistem Lembur** ini merupakan penugasan lanjutan magang MBKM untuk **Minggu II - IV Oktober 2026 (08 - 31 Oktober 2026)**.

2. **Pembaruan Laporan Bulanan:**
   - Berkas laporan bulanan aktif tersimpan di root folder:
     `LAPORAN_BULANAN_OKTOBER_2026.md`
   - Setiap kali menyelesaikan fitur, perbaikan bug, atau tugas di proyek Lembur ini, AI Agent wajib membantu mencatatkan logbook harian resmi untuk **Minggu II, III, dan IV** serta memperbarui narasi mingguan, analisa hasil, kendala, dan dokumentasi pada berkas tersebut.

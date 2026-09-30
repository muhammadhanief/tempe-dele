-- =========================================================================
-- SKRIP UPDATE STRUKTUR DATABASE - TEMPE DELE (Versi 2.0)
-- BPS Provinsi Riau / BPS Provinsi Jawa Tengah
-- Tabel Target: `t_transaksi`
-- =========================================================================
-- Petunjuk Eksekusi:
-- 1. Buka phpMyAdmin di cPanel atau database client Anda (DBeaver/Navicat/MySQL CLI).
-- 2. Pilih database aplikasi lembur.
-- 3. Buka tab 'SQL', salin (copy) dan tempel (paste) seluruh query di bawah ini,
--    lalu klik 'Go' / 'Kirim' / 'Execute'.
-- =========================================================================

-- -------------------------------------------------------------------------
-- LANGKAH 1: Pembaruan Alur Persetujuan Bertingkat Kabag Umum
-- Menambahkan kolom catatan evaluasi Kabag Umum & tanggal persetujuan Kabag
-- -------------------------------------------------------------------------
ALTER TABLE `t_transaksi` 
    ADD COLUMN `note_kabag` TEXT NULL AFTER `note`,
    ADD COLUMN `approved_kabag_at` DATETIME NULL AFTER `approved_at`;

-- -------------------------------------------------------------------------
-- LANGKAH 2: Pelebaran Kolom Status Pengajuan Lembur
-- Sebelumnya VARCHAR(10) / ENUM, diperlebar menjadi VARCHAR(30)
-- untuk menampung status baru: 'menunggu_kabag' dan 'cancelled'
-- -------------------------------------------------------------------------
ALTER TABLE `t_transaksi` 
    MODIFY COLUMN `status` VARCHAR(30) NULL DEFAULT 'pending';

-- -------------------------------------------------------------------------
-- LANGKAH 3: Penambahan Kolom Audit Trail Koreksi Uraian & Jam Lembur
-- Mencatat identitas pengguna (user_edited) dan waktu (tanggal_edited)
-- ketika Admin / Ketua Tim / Pegawai melakukan pengeditan
-- -------------------------------------------------------------------------
ALTER TABLE `t_transaksi` 
    ADD COLUMN `user_edited` VARCHAR(100) NULL AFTER `note_kabag`,
    ADD COLUMN `tanggal_edited` DATETIME NULL AFTER `user_edited`;

-- -------------------------------------------------------------------------
-- LANGKAH 4: Pelebaran Kapasitas Uraian Kegiatan Lembur
-- Mengubah kolom `uraian` dari VARCHAR(255) menjadi TEXT
-- agar mampu menampung narasi tugas detail s.d. 2.000 karakter
-- -------------------------------------------------------------------------
ALTER TABLE `t_transaksi` 
    MODIFY COLUMN `uraian` TEXT NULL;

-- -------------------------------------------------------------------------
-- LANGKAH 5 (OPSIONAL & AMAN): Normalisasi Jam Disetujui pada Transaksi Pending
-- Mengosongkan jam_mulai_disetujui & jam_selesai_disetujui pada pengajuan
-- yang masih 'pending' agar nilai default di modal selalu mengikuti jam pengajuan pegawai
-- -------------------------------------------------------------------------
UPDATE `t_transaksi` 
SET 
    `jam_mulai_disetujui` = NULL, 
    `jam_selesai_disetujui` = NULL 
WHERE `status` = 'pending';

-- =========================================================================
-- SELESAI. Seluruh struktur database telah kompatibel 100% dengan v2.
-- =========================================================================

-- ================================================================
-- MIGRASI TAMBAHAN: Jadwal Ketersediaan Pejabat & Statistik Proses
-- Jalankan file ini di database `db_smart_guest` yang sudah ada.
-- Aman dijalankan berkali-kali (pakai pengecekan IF NOT EXISTS via
-- prosedur sederhana / abaikan error "Duplicate column" jika muncul).
-- ================================================================

-- 1. Kolom pencatat waktu admin menyetujui/menjadwalkan kunjungan.
--    Dipakai untuk menghitung "rata-rata waktu proses persetujuan"
--    pada Laporan Statistik Penggunaan Sistem.
ALTER TABLE `kunjungan`
  ADD COLUMN `waktu_verifikasi` TIMESTAMP NULL DEFAULT NULL AFTER `id_admin_verif`;

-- (Tabel `jadwal_pejabat` sudah ada di db_smart_guest.sql, tidak perlu dibuat ulang)

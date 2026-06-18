-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 18, 2026 at 05:44 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_smart_guest`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama_pengguna` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` enum('admin','petugas','pengunjung','keamanan') DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buku_tamu`
--

CREATE TABLE `buku_tamu` (
  `id_tamu` int NOT NULL,
  `id_kunjungan` int DEFAULT NULL,
  `nama_peserta` varchar(100) DEFAULT NULL,
  `asal_instansi` varchar(100) DEFAULT NULL,
  `jabatan_peserta` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL,
  `timestamp_ttd` timestamp NULL DEFAULT NULL,
  `status_ttd` enum('valid','invalid','kosong') DEFAULT 'kosong',
  `waktu_hadir` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disposisi_kunjungan`
--

CREATE TABLE `disposisi_kunjungan` (
  `id_disposisi` int NOT NULL,
  `id_kunjungan` int DEFAULT NULL,
  `diteruskan_kepada` varchar(200) DEFAULT NULL,
  `tgl_disposisi` date DEFAULT NULL,
  `instruksi_umum` text,
  `instruksi_khusus` text,
  `status_tte` enum('belum','selesai') DEFAULT 'belum',
  `qr_validasi_tte` varchar(255) DEFAULT NULL,
  `tgl_tte` timestamp NULL DEFAULT NULL,
  `id_penandatangan` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_kunjungan`
--

CREATE TABLE `feedback_kunjungan` (
  `id_feedback` int NOT NULL,
  `id_kunjungan` int DEFAULT NULL,
  `nama_pemberi` varchar(100) DEFAULT NULL,
  `jabatan_pemberi` varchar(100) DEFAULT NULL,
  `rating_pelayanan` tinyint(1) DEFAULT NULL,
  `rating_fasilitas` tinyint(1) DEFAULT NULL,
  `rating_ketepatan_waktu` tinyint(1) DEFAULT NULL,
  `rating_keseluruhan` float DEFAULT NULL,
  `komentar_saran` text,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_pejabat`
--

CREATE TABLE `jadwal_pejabat` (
  `id_jadwal` int NOT NULL,
  `id_pj` int DEFAULT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `status_tersedia` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_kunjungan`
--

CREATE TABLE `kategori_kunjungan` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) DEFAULT NULL,
  `kode_kategori` varchar(10) DEFAULT NULL,
  `deskripsi` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kunjungan`
--

CREATE TABLE `kunjungan` (
  `id_kunjungan` int NOT NULL,
  `kode_booking` varchar(20) DEFAULT NULL,
  `email_pemohon` varchar(100) DEFAULT NULL,
  `no_hp_pemohon` varchar(20) DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `jenis_pendaftaran` enum('online','walk-in') DEFAULT 'online',
  `id_kategori` int DEFAULT NULL,
  `tgl_surat_permohonan` date DEFAULT NULL,
  `tgl_kunjungan` date DEFAULT NULL,
  `waktu_kunjungan` time DEFAULT NULL,
  `nama_instansi_tamu` varchar(150) DEFAULT NULL,
  `alamat_instansi` text,
  `jumlah_peserta_rencana` int DEFAULT NULL,
  `materi_kunjungan` text,
  `file_surat_permohonan` varchar(255) DEFAULT NULL,
  `no_skk` varchar(100) DEFAULT NULL,
  `tgl_skk` date DEFAULT NULL,
  `instansi_pengirim_skk` varchar(150) DEFAULT NULL,
  `perihal_skk` varchar(255) DEFAULT NULL,
  `file_skk` varchar(255) DEFAULT NULL,
  `id_pj` int DEFAULT NULL,
  `id_ruangan` int DEFAULT NULL,
  `id_admin_verif` int DEFAULT NULL,
  `status_kegiatan` enum('pending','dijadwalkan','selesai','batal') DEFAULT 'pending',
  `alasan_pembatalan` text,
  `tgl_pembatalan` datetime DEFAULT NULL,
  `qr_code_data` varchar(500) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `status_kehadiran` enum('belum','hadir','selesai') DEFAULT 'belum',
  `waktu_scan` timestamp NULL DEFAULT NULL,
  `batas_waktu_kunjungan` datetime DEFAULT NULL,
  `waktu_checkout` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penanggung_jawab`
--

CREATE TABLE `penanggung_jawab` (
  `id_pj` int NOT NULL,
  `nama_pj` varchar(100) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `pangkat_golongan` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `file_ttd` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_scan_log`
--

CREATE TABLE `qr_scan_log` (
  `id_scan` int NOT NULL,
  `id_kunjungan` int DEFAULT NULL,
  `kode_booking` varchar(20) DEFAULT NULL,
  `waktu_scan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `petugas_scan` varchar(100) DEFAULT NULL,
  `status_scan` enum('invalid','valid','expired','duplicate') DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruangan` int NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `lantai` varchar(50) DEFAULT NULL,
  `kapasitas` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spt_tugas`
--

CREATE TABLE `spt_tugas` (
  `id_spt` int NOT NULL,
  `id_kunjungan` int DEFAULT NULL,
  `jenis_petugas` enum('dprd','pendamping') DEFAULT NULL,
  `no_spt` varchar(100) DEFAULT NULL,
  `tgl_spt` date DEFAULT NULL,
  `nama_pegawai` varchar(100) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `jumlah_ditugaskan` int DEFAULT NULL,
  `file_spt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistik_kunjungan`
--

CREATE TABLE `statistik_kunjungan` (
  `id_statistik` int NOT NULL,
  `bulan` int DEFAULT NULL,
  `tahun` int DEFAULT NULL,
  `total_kunjungan` int DEFAULT '0',
  `total_terlaksana` int DEFAULT '0',
  `total_dijadwalkan` int DEFAULT '0',
  `total_pending` int DEFAULT '0',
  `total_batal` int DEFAULT '0',
  `total_peserta` int DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `buku_tamu`
--
ALTER TABLE `buku_tamu`
  ADD PRIMARY KEY (`id_tamu`),
  ADD KEY `id_kunjungan` (`id_kunjungan`);

--
-- Indexes for table `disposisi_kunjungan`
--
ALTER TABLE `disposisi_kunjungan`
  ADD PRIMARY KEY (`id_disposisi`),
  ADD KEY `id_kunjungan` (`id_kunjungan`),
  ADD KEY `id_penandatangan` (`id_penandatangan`);

--
-- Indexes for table `feedback_kunjungan`
--
ALTER TABLE `feedback_kunjungan`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_kunjungan` (`id_kunjungan`);

--
-- Indexes for table `jadwal_pejabat`
--
ALTER TABLE `jadwal_pejabat`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_pj` (`id_pj`);

--
-- Indexes for table `kategori_kunjungan`
--
ALTER TABLE `kategori_kunjungan`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD PRIMARY KEY (`id_kunjungan`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD UNIQUE KEY `no_register` (`no_register`),
  ADD KEY `id_pj` (`id_pj`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_admin_verif` (`id_admin_verif`);

--
-- Indexes for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  ADD PRIMARY KEY (`id_pj`);

--
-- Indexes for table `qr_scan_log`
--
ALTER TABLE `qr_scan_log`
  ADD PRIMARY KEY (`id_scan`),
  ADD KEY `id_kunjungan` (`id_kunjungan`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id_ruangan`);

--
-- Indexes for table `spt_tugas`
--
ALTER TABLE `spt_tugas`
  ADD PRIMARY KEY (`id_spt`),
  ADD KEY `id_kunjungan` (`id_kunjungan`);

--
-- Indexes for table `statistik_kunjungan`
--
ALTER TABLE `statistik_kunjungan`
  ADD PRIMARY KEY (`id_statistik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buku_tamu`
--
ALTER TABLE `buku_tamu`
  MODIFY `id_tamu` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disposisi_kunjungan`
--
ALTER TABLE `disposisi_kunjungan`
  MODIFY `id_disposisi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_kunjungan`
--
ALTER TABLE `feedback_kunjungan`
  MODIFY `id_feedback` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_pejabat`
--
ALTER TABLE `jadwal_pejabat`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_kunjungan`
--
ALTER TABLE `kategori_kunjungan`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kunjungan`
--
ALTER TABLE `kunjungan`
  MODIFY `id_kunjungan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  MODIFY `id_pj` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_scan_log`
--
ALTER TABLE `qr_scan_log`
  MODIFY `id_scan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id_ruangan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spt_tugas`
--
ALTER TABLE `spt_tugas`
  MODIFY `id_spt` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistik_kunjungan`
--
ALTER TABLE `statistik_kunjungan`
  MODIFY `id_statistik` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku_tamu`
--
ALTER TABLE `buku_tamu`
  ADD CONSTRAINT `buku_tamu_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;

--
-- Constraints for table `disposisi_kunjungan`
--
ALTER TABLE `disposisi_kunjungan`
  ADD CONSTRAINT `disposisi_kunjungan_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE,
  ADD CONSTRAINT `disposisi_kunjungan_ibfk_2` FOREIGN KEY (`id_penandatangan`) REFERENCES `penanggung_jawab` (`id_pj`) ON DELETE SET NULL;

--
-- Constraints for table `feedback_kunjungan`
--
ALTER TABLE `feedback_kunjungan`
  ADD CONSTRAINT `feedback_kunjungan_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_pejabat`
--
ALTER TABLE `jadwal_pejabat`
  ADD CONSTRAINT `jadwal_pejabat_ibfk_1` FOREIGN KEY (`id_pj`) REFERENCES `penanggung_jawab` (`id_pj`) ON DELETE CASCADE;

--
-- Constraints for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD CONSTRAINT `kunjungan_ibfk_1` FOREIGN KEY (`id_pj`) REFERENCES `penanggung_jawab` (`id_pj`) ON DELETE SET NULL,
  ADD CONSTRAINT `kunjungan_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE SET NULL,
  ADD CONSTRAINT `kunjungan_ibfk_3` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_kunjungan` (`id_kategori`) ON DELETE SET NULL,
  ADD CONSTRAINT `kunjungan_ibfk_4` FOREIGN KEY (`id_admin_verif`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

--
-- Constraints for table `qr_scan_log`
--
ALTER TABLE `qr_scan_log`
  ADD CONSTRAINT `qr_scan_log_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;

--
-- Constraints for table `spt_tugas`
--
ALTER TABLE `spt_tugas`
  ADD CONSTRAINT `spt_tugas_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

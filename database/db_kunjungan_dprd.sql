-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 05:39 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kunjungan_dprd`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_pengguna` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` enum('admin','petugas') DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_pengguna`, `username`, `password`, `level`, `created_at`) VALUES
(1, 'Super Admin', 'admin', 'admin123', 'admin', '2025-12-09 06:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `buku_tamu`
--

CREATE TABLE `buku_tamu` (
  `id_tamu` int(11) NOT NULL,
  `id_kunjungan` int(11) DEFAULT NULL,
  `nama_peserta` varchar(100) DEFAULT NULL,
  `asal_instansi` varchar(100) DEFAULT NULL,
  `jabatan_peserta` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL,
  `waktu_hadir` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `kunjungan`
--

CREATE TABLE `kunjungan` (
  `id_kunjungan` int(11) NOT NULL,
  `kode_booking` varchar(20) DEFAULT NULL,
  `email_pemohon` varchar(100) DEFAULT NULL,
  `no_register` varchar(50) DEFAULT NULL,
  `tgl_surat_permohonan` date DEFAULT NULL,
  `tgl_kunjungan` date DEFAULT NULL,
  `waktu_kunjungan` time DEFAULT NULL,
  `nama_instansi_tamu` varchar(150) DEFAULT NULL,
  `alamat_instansi` text DEFAULT NULL,
  `jumlah_peserta_rencana` int(11) DEFAULT NULL,
  `materi_kunjungan` text DEFAULT NULL,
  `file_surat_permohonan` varchar(255) DEFAULT NULL,
  `no_skk` varchar(100) DEFAULT NULL,
  `tgl_skk` date DEFAULT NULL,
  `instansi_pengirim_skk` varchar(150) DEFAULT NULL,
  `perihal_skk` varchar(255) DEFAULT NULL,
  `file_skk` varchar(255) DEFAULT NULL,
  `id_pj` int(11) DEFAULT NULL,
  `id_ruangan` int(11) DEFAULT NULL,
  `status_kegiatan` enum('pending','dijadwalkan','selesai','batal') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kunjungan`
--

INSERT INTO `kunjungan` (`id_kunjungan`, `kode_booking`, `email_pemohon`, `no_register`, `tgl_surat_permohonan`, `tgl_kunjungan`, `waktu_kunjungan`, `nama_instansi_tamu`, `alamat_instansi`, `jumlah_peserta_rencana`, `materi_kunjungan`, `file_surat_permohonan`, `no_skk`, `tgl_skk`, `instansi_pengirim_skk`, `perihal_skk`, `file_skk`, `id_pj`, `id_ruangan`, `status_kegiatan`, `created_at`) VALUES
(1, 'REQ-2025-0055', 'dprdtala@mail.com', NULL, '2025-12-09', '2025-12-10', '18:11:00', 'DPRD Kab.Tala', 'pelaihari', 2, 'Becari Duit', 'SURAT_1765264496.png', NULL, NULL, NULL, NULL, NULL, 1, 1, 'selesai', '2025-12-09 07:14:56');

-- --------------------------------------------------------

--
-- Table structure for table `penanggung_jawab`
--

CREATE TABLE `penanggung_jawab` (
  `id_pj` int(11) NOT NULL,
  `nama_pj` varchar(100) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `pangkat_golongan` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `file_ttd` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `penanggung_jawab`
--

INSERT INTO `penanggung_jawab` (`id_pj`, `nama_pj`, `nip`, `pangkat_golongan`, `jabatan`, `no_hp`, `file_ttd`) VALUES
(1, 'Ahmad Saiduns, S.Kom.', '2210010264', NULL, 'Kasubbag Humas', '081222333444', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruangan` int(11) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `lantai` varchar(50) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id_ruangan`, `nama_ruangan`, `lantai`, `kapasitas`) VALUES
(1, 'Ruang Paripurna', 'Lantai 2', 200),
(2, 'Ruang Komisi 1', 'Lantai 1', 30),
(3, 'Ruang Komisi 2', 'Lantai 1', 30),
(4, 'Ruang Komisi 3', 'Lantai 1', 30),
(5, 'Ruang Komisi 4', 'Lantai 1', 30),
(6, 'Ruang Badan Anggaran', 'Lantai 2', 100);

-- --------------------------------------------------------

--
-- Table structure for table `spt_tugas`
--

CREATE TABLE `spt_tugas` (
  `id_spt` int(11) NOT NULL,
  `id_kunjungan` int(11) DEFAULT NULL,
  `jenis_petugas` enum('dprd','pendamping') DEFAULT NULL,
  `no_spt` varchar(100) DEFAULT NULL,
  `tgl_spt` date DEFAULT NULL,
  `nama_pegawai` varchar(100) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `jumlah_ditugaskan` int(11) DEFAULT NULL,
  `file_spt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Indexes for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD PRIMARY KEY (`id_kunjungan`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD UNIQUE KEY `no_register` (`no_register`),
  ADD KEY `id_pj` (`id_pj`),
  ADD KEY `id_ruangan` (`id_ruangan`);

--
-- Indexes for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  ADD PRIMARY KEY (`id_pj`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buku_tamu`
--
ALTER TABLE `buku_tamu`
  MODIFY `id_tamu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kunjungan`
--
ALTER TABLE `kunjungan`
  MODIFY `id_kunjungan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penanggung_jawab`
--
ALTER TABLE `penanggung_jawab`
  MODIFY `id_pj` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id_ruangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `spt_tugas`
--
ALTER TABLE `spt_tugas`
  MODIFY `id_spt` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku_tamu`
--
ALTER TABLE `buku_tamu`
  ADD CONSTRAINT `buku_tamu_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;

--
-- Constraints for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD CONSTRAINT `kunjungan_ibfk_1` FOREIGN KEY (`id_pj`) REFERENCES `penanggung_jawab` (`id_pj`) ON DELETE SET NULL,
  ADD CONSTRAINT `kunjungan_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE SET NULL;

--
-- Constraints for table `spt_tugas`
--
ALTER TABLE `spt_tugas`
  ADD CONSTRAINT `spt_tugas_ibfk_1` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungan` (`id_kunjungan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

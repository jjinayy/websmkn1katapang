-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2026 at 02:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `katapang_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `kelas` varchar(30) NOT NULL,
  `metode_bayar` varchar(50) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `status` enum('Menunggu Pembayaran','Diproses','Selesai','Dibatalkan') DEFAULT 'Menunggu Pembayaran',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `siswa_id`, `nama_pemesan`, `kelas`, `metode_bayar`, `total_harga`, `status`, `dibuat_pada`) VALUES
(1, 1, 'Cika Arumi', 'XII TKR 1', 'WhatsApp / Kasir Koperasi', 150000, 'Menunggu Pembayaran', '2026-09-05 19:12:32'),
(2, 2, 'Rivan Adiansyah', 'XII MESIN 3', 'WhatsApp / Kasir Koperasi', 165000, 'Menunggu Pembayaran', '2026-09-05 19:22:33'),
(3, 3, 'Aisyah Malika', 'XII RPL 1', 'WhatsApp / Kasir Koperasi', 295000, 'Menunggu Pembayaran', '2026-09-07 00:49:31');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_detail`
--

CREATE TABLE `pesanan_detail` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_produk` varchar(120) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan_detail`
--

INSERT INTO `pesanan_detail` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 3, 'Seragam Putih Abu (Setel)', 135000, 1, 135000),
(2, 1, 10, 'Set Badge & Logo Bordir', 15000, 1, 15000),
(3, 2, 4, 'Seragam Pramuka (Setel)', 145000, 1, 145000),
(4, 2, 9, 'Sabuk / Ikat Pinggang', 20000, 1, 20000),
(5, 3, 3, 'Seragam Putih Abu (Setel)', 135000, 1, 135000),
(6, 3, 6, 'Jas Almamater Sekolah', 160000, 1, 160000);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `harga` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `harga`, `icon`) VALUES
(1, 'Seragam Olahraga (Setel)', 150000, 'fa-shirt'),
(2, 'Batik Khusus K1', 120000, 'fa-user-tie'),
(3, 'Seragam Putih Abu (Setel)', 135000, 'fa-shirt'),
(4, 'Seragam Pramuka (Setel)', 145000, 'fa-campground'),
(5, 'Pakaian Praktikum / Wearpack', 175000, 'fa-vest-patches'),
(6, 'Jas Almamater Sekolah', 160000, 'fa-user-nurse'),
(7, 'Topi Sekolah', 20000, 'fa-graduation-cap'),
(8, 'Dasi Sekolah', 15000, 'fa-user-tie'),
(9, 'Sabuk / Ikat Pinggang', 20000, 'fa-ring'),
(10, 'Set Badge & Logo Bordir', 15000, 'fa-certificate'),
(11, 'Pin / Ring Pramuka', 10000, 'fa-award'),
(12, 'Kaos Kaki Resmi K1 (2 Pasang)', 25000, 'fa-socks');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nisn` varchar(30) NOT NULL COMMENT 'dipakai sebagai username login',
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'disimpan dalam bentuk hash (bcrypt)',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nisn`, `nama_lengkap`, `kelas`, `password`, `dibuat_pada`) VALUES
(1, 'ccyamyy', 'Cika Arumi', 'XII TKR 1', '$2y$10$MokXXsMPEYvzZ4keujyjJe.sageaR8JhaIUoxQ/c3oIQRNXk8m8FS', '2026-09-05 19:11:26'),
(2, 'navi_haysnaida', 'Rivan Adiansyah', 'XII MESIN 3', '$2y$10$iywIpZkudHWs0CisUnuJ..DRVDeLkLszJSFksb8arV7lGWCT5UGxO', '2026-09-05 19:21:46'),
(3, 'syahmalikaa', 'Aisyah Malika', 'XII RPL 1', '$2y$10$FWVi3jpxxZLH64Nred6H7u3DDyaVxAFoTf1rRcdSNMY0Ho1iF3vwW', '2026-09-07 00:48:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nisn` (`nisn`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan_detail`
--
ALTER TABLE `pesanan_detail`
  ADD CONSTRAINT `pesanan_detail_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

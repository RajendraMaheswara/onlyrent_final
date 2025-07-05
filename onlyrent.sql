-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 12:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `onlyrent`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `id_pemilik` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `gambar` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`gambar`)),
  `deskripsi` text NOT NULL,
  `harga_sewa` varchar(10) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `id_pemilik`, `nama_barang`, `gambar`, `deskripsi`, `harga_sewa`, `status`) VALUES
(23, 1, 'Porsche 911 Turbo S', '[\"120600-02072025-porsche-911-turbo-s-1.avif\"]', 'Baru beli kemarin sore', '10000000', 0),
(24, 1, 'Google Pixel 9 Pro', '[\"122713-02072025-google-pixel-9-pro-1.webp\"]', 'Google Pixel 9 Pro 256GB', '1000000', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pemilik_barang`
--

CREATE TABLE `pemilik_barang` (
  `id_pemilik` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_telp` varchar(25) NOT NULL,
  `id_pengguna` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemilik_barang`
--

INSERT INTO `pemilik_barang` (`id_pemilik`, `nama`, `no_telp`, `id_pengguna`) VALUES
(1, 'Jack', '081234567891', 3);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `username`, `email`, `password`, `role`) VALUES
(1, 'Kiing', 'kiing@gmail.com', '$2y$10$5E77VpVvpZjWkZAimoFsOOgY0F3iVR318v1wYcfzVAGcFQuUxzW1K', 1),
(2, 'Queen', 'queen@gmail.com', '$2y$10$cirqS8.gey.KK4q5hcvnVuJ12qaeCTtP68Tp.Gzw3AHKaqnEMHRYK', 2),
(3, 'Jack', 'jack@gmail.com', '$2y$10$saILztqkGtUnKAz8EakE/u7G1MGull6DM4rXhONsq1DIQEDOKN5ou', 3);

-- --------------------------------------------------------

--
-- Table structure for table `penyewa`
--

CREATE TABLE `penyewa` (
  `id_penyewa` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `no_telp` varchar(25) NOT NULL,
  `id_pengguna` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penyewa`
--

INSERT INTO `penyewa` (`id_penyewa`, `nama`, `alamat`, `no_telp`, `id_pengguna`) VALUES
(1, 'Queen', 'Jl. Menata Hati Bersama', '081234567890', 2);

-- --------------------------------------------------------

--
-- Table structure for table `sewa`
--

CREATE TABLE `sewa` (
  `id_sewa` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_penyewa` int(11) NOT NULL,
  `tanggalSewa` datetime NOT NULL,
  `tanggalKembali` datetime NOT NULL,
  `totalBayar` int(12) NOT NULL,
  `status` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sewa`
--

INSERT INTO `sewa` (`id_sewa`, `id_barang`, `id_penyewa`, `tanggalSewa`, `tanggalKembali`, `totalBayar`, `status`, `created_at`, `updated_at`) VALUES
(9, 23, 1, '2025-07-02 00:00:00', '2025-07-04 00:00:00', 20000000, 1, '2025-07-02 10:22:25', '2025-07-02 10:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_sewa` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `totalBayar` int(12) NOT NULL,
  `gambar` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`gambar`)),
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_sewa`, `jumlah`, `tanggal`, `totalBayar`, `gambar`, `status`) VALUES
(1, 9, 1, '2025-07-02 17:28:28', 22500000, '[\"20250702-122828-686509cc903f4.webp\"]', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `pemilik_barang`
--
ALTER TABLE `pemilik_barang`
  ADD PRIMARY KEY (`id_pemilik`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- Indexes for table `penyewa`
--
ALTER TABLE `penyewa`
  ADD PRIMARY KEY (`id_penyewa`);

--
-- Indexes for table `sewa`
--
ALTER TABLE `sewa`
  ADD PRIMARY KEY (`id_sewa`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pemilik_barang`
--
ALTER TABLE `pemilik_barang`
  MODIFY `id_pemilik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `penyewa`
--
ALTER TABLE `penyewa`
  MODIFY `id_penyewa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sewa`
--
ALTER TABLE `sewa`
  MODIFY `id_sewa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

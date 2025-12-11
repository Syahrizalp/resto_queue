-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2025 at 02:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resto_queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@warungmakan.com', '2025-12-11 13:29:27');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `menu_id`, `jumlah`, `harga`, `subtotal`) VALUES
(1, 1, 4, 2, 23000.00, 46000.00),
(2, 1, 12, 2, 10000.00, 20000.00),
(3, 2, 4, 5, 23000.00, 115000.00),
(4, 2, 6, 3, 20000.00, 60000.00),
(5, 2, 7, 8, 5000.00, 40000.00),
(6, 3, 6, 1, 20000.00, 20000.00),
(7, 3, 4, 1, 23000.00, 23000.00),
(8, 3, 8, 2, 7000.00, 14000.00);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `gambar` varchar(200) DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `nama`, `harga`, `kategori`, `gambar`, `status`) VALUES
(1, 'Nasi Goreng Special', 25000.00, 'Makanan', '693acad848aa2_1765460696.jpeg', 'tersedia'),
(2, 'Mie Ayam', 20000.00, 'Makanan', '693ac8fc97a3d_1765460220.jpg', 'tersedia'),
(3, 'Soto Ayam', 22000.00, 'Makanan', '693acaef36138_1765460719.png', 'tersedia'),
(4, 'Ayam Geprek', 23000.00, 'Makanan', '693ac9e4bbc24_1765460452.jpg', 'tersedia'),
(5, 'Nasi Uduk', 18000.00, 'Makanan', '693acae2945d8_1765460706.jpg', 'tersedia'),
(6, 'Gado-Gado', 20000.00, 'Makanan', '693ac9da8a4f0_1765460442.jpg', 'tersedia'),
(7, 'Es Teh Manis', 5000.00, 'Minuman', '693acc6280930_1765461090.jpeg', 'tersedia'),
(8, 'Es Jeruk', 7000.00, 'Minuman', '693acc5534536_1765461077.jpg', 'tersedia'),
(10, 'Kopi Hitam', 8000.00, 'Minuman', '693acc6f253a7_1765461103.jpg', 'tersedia'),
(11, 'Es Campur', 15000.00, 'Minuman', '693acc45a4065_1765461061.jpg', 'tersedia'),
(12, 'Teh Tarik', 10000.00, 'Minuman', '693acc7b9b842_1765461115.jpg', 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_toko`
--

CREATE TABLE `pengaturan_toko` (
  `id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan_toko`
--

INSERT INTO `pengaturan_toko` (`id`, `nama_toko`, `deskripsi`, `logo`, `updated_at`) VALUES
(1, 'Warung Makan Sederhana', 'Pesan makanan favoritmu dengan mudah!', NULL, '2025-12-11 13:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nomor_antrian` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('antri','diproses','selesai','diambil') DEFAULT 'antri',
  `waktu_pesan` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  `posisi_queue` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `nomor_antrian`, `nama_pelanggan`, `total_harga`, `status`, `waktu_pesan`, `waktu_selesai`, `posisi_queue`) VALUES
(1, 'Q251211001', 'Syahrizal', 66000.00, 'selesai', '2025-12-11 13:54:03', '2025-12-11 13:55:39', 1),
(2, 'Q251211002', 'Raha', 215000.00, 'selesai', '2025-12-11 13:54:38', '2025-12-11 13:55:49', 2),
(3, 'Q251211003', 'Dian', 57000.00, 'diambil', '2025-12-11 13:55:05', '2025-12-11 13:55:52', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan_toko`
--
ALTER TABLE `pengaturan_toko`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_antrian` (`nomor_antrian`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengaturan_toko`
--
ALTER TABLE `pengaturan_toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 12:39 PM
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
-- Database: `bank_iman_syariah`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nomor_rekening` varchar(30) NOT NULL,
  `jenis_akad` enum('Wadiah','Mudharabah') NOT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `nomor_rekening`, `jenis_akad`, `saldo`, `status`) VALUES
(1, 1, '1234567890', 'Wadiah', 13950000.00, 'Nonaktif'),
(2, 1, '8064889756', 'Mudharabah', 0.00, 'Aktif'),
(3, 4, '3788324677', 'Wadiah', 500000.00, 'Aktif'),
(4, 5, '8976790504', 'Wadiah', 1500000.00, 'Aktif'),
(5, 5, '4027993741', 'Mudharabah', 0.00, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(1, 1, '9bd22e9ec201af6abb220c319a0c00fa00ea153c8b7aaf70487f2a0be105e4ca', '2026-11-03 10:28:40', '2026-11-02 09:28:40'),
(2, 1, '1d1799446306f80b8be6ff8fc35cb1a8e9900b7f95bb5835c6cc57ec34d39cbc', '2026-11-01 16:54:01', '2026-11-02 09:40:04'),
(3, 1, '072bf988be8041d08f93ffd137a27c60caeefe319f1d2aefd2c3faca570dda1d', '2026-11-03 10:57:00', '2026-11-02 09:57:00');

-- --------------------------------------------------------

--
-- Table structure for table `financings`
--

CREATE TABLE `financings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_akad` varchar(50) DEFAULT NULL,
  `plafon` decimal(15,2) DEFAULT NULL,
  `margin` decimal(5,2) DEFAULT NULL,
  `tenor` int(11) DEFAULT NULL,
  `angsuran` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financings`
--

INSERT INTO `financings` (`id`, `user_id`, `jenis_akad`, `plafon`, `margin`, `tenor`, `angsuran`, `status`) VALUES
(1, 1, 'Murabahah', 50000000.00, 8.00, 24, 2416667.00, 'Aktif'),
(2, 5, 'Murabahah', 5000000.00, 8.00, 12, 450000.00, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `financing_applications`
--

CREATE TABLE `financing_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_akad` varchar(50) NOT NULL,
  `plafon` decimal(15,2) NOT NULL,
  `tenor` int(11) NOT NULL,
  `margin` decimal(5,2) NOT NULL DEFAULT 8.00,
  `estimasi_angsuran` decimal(15,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Persetujuan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financing_applications`
--

INSERT INTO `financing_applications` (`id`, `user_id`, `jenis_akad`, `plafon`, `tenor`, `margin`, `estimasi_angsuran`, `status`, `tanggal_pengajuan`) VALUES
(1, 1, 'Mudharabah', 3000000.00, 12, 8.00, 270000.00, 'Menunggu Persetujuan', '2026-11-04 05:33:26'),
(2, 5, 'Murabahah', 5000000.00, 12, 8.00, 450000.00, 'Disetujui', '2026-11-11 10:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `rekening_lawan_id` int(11) DEFAULT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `referensi_transfer` varchar(30) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `account_id`, `rekening_lawan_id`, `kode_transaksi`, `referensi_transfer`, `keterangan`, `jenis`, `nominal`, `tanggal`) VALUES
(1, 1, NULL, 'TRX-001', NULL, 'Setoran gaji Oktober', 'masuk', 8500000.00, '2026-10-27 17:00:00'),
(2, 1, NULL, 'TRX-002', NULL, 'Pembayaran zakat penghasilan', 'keluar', 212500.00, '2026-10-24 17:00:00'),
(3, 1, NULL, 'TRX-003', NULL, 'Angsuran Murabahah', 'keluar', 2416667.00, '2026-10-20 17:00:00'),
(4, 1, NULL, 'TRX-004', NULL, 'Transfer ke Siti Rahma', 'keluar', 500000.00, '2026-10-14 17:00:00'),
(5, 1, NULL, 'TRX-005', NULL, 'Tarik tunai ATM', 'keluar', 1000000.00, '2026-10-01 17:00:00'),
(6, 1, NULL, 'TRX-006', NULL, 'Bagi hasil Mudharabah', 'masuk', 45000.00, '2026-09-30 17:00:00'),
(7, 1, NULL, 'STR20261103132237356', NULL, 'Setor tunai', 'masuk', 500000.00, '2026-11-03 12:22:37'),
(8, 1, NULL, 'TRK20261103132828701', NULL, 'Tarik tunai', 'keluar', 5000000.00, '2026-11-03 12:28:28'),
(15, 4, NULL, 'ST20261109091733699', NULL, 'Setor tunai oleh admin', 'masuk', 1000000.00, '2026-11-09 08:17:33'),
(16, 4, NULL, 'ST20261109091802911', NULL, 'setoran tunai', 'masuk', 1000000.00, '2026-11-09 08:18:02'),
(17, 4, 3, 'OUT20261109165414696', 'TRF20261109165414996', 'pembayaran kuliah', 'keluar', 500000.00, '2026-11-09 15:54:14'),
(18, 3, 4, 'IN20261109165414773', 'TRF20261109165414996', 'pembayaran kuliah', 'masuk', 500000.00, '2026-11-09 15:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('admin','nasabah') NOT NULL DEFAULT 'nasabah',
  `pin_transaksi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `no_hp`, `alamat`, `created_at`, `foto_profil`, `role`, `pin_transaksi`) VALUES
(1, 'rian', 'rian', '$2y$10$ae2tj.95L93jnVwlkq/WTeCQMRfZivmlv867csBDArSNuwDjLK0z6', '081234567890', 'Depok', '2026-11-01 15:07:33', 'assets/uploads/profiles/6aea9e40f0bd535c6ceb8774f855010c.jpg', 'admin', NULL),
(3, 'Muhammad Rava', 'Rava', '$2y$10$tGgHG6KN/PVtUnYo7XO58uuO2.ODFZ1fdVNbvVomJ42VX3ztkHwiq', '087823095613', 'Citayam,Kp.Pabuaran, JL Departemen Agama NO 23', '2026-11-09 06:38:43', NULL, 'nasabah', NULL),
(4, 'Muhammad Ryza Mahameru', 'Ryza', '$2y$10$iwQzec5XzZHqM7Ha3PKjmej2IhG3W7QtrbB1boSBqCw249IvnXV5m', '087823095613', 'Citayam, Kp Pabuarana, JL Departemen Agama', '2026-11-09 06:46:38', NULL, 'nasabah', NULL),
(5, 'Muhammad Faishal', 'Faishal', '$2y$10$0yCUx4cU.yP4a.W2qJlFG.rbY2P49hrhhnjFNWXwW8wLpQuaZstHK', '087823095613', 'Citayam, Kp Pabuaran, BojongGede, JL Departemen Agama', '2026-11-09 06:54:15', 'assets/uploads/profiles/7ba55380d3d9f19126a613af2f33ebbe.jpg', 'nasabah', '$2y$10$N/5UgVs4h1grx0YO.6RtMOwcRxv4bEg8.JyC3BbxcwCTnbccPMBYS');

-- --------------------------------------------------------

--
-- Table structure for table `zakat_payments`
--

CREATE TABLE `zakat_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_zakat` varchar(100) DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Lunas',
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zakat_payments`
--

INSERT INTO `zakat_payments` (`id`, `user_id`, `jenis_zakat`, `nominal`, `status`, `tanggal`) VALUES
(1, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-10-25'),
(2, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-09-25'),
(3, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-08-25'),
(4, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-07-25'),
(5, 1, 'Zakat Penghasilan', 200000.00, 'Berhasil', '2026-06-25'),
(6, 1, 'Zakat Penghasilan', 200000.00, 'Berhasil', '2026-05-25'),
(7, 1, 'Zakat Penghasilan', 50000.00, 'Berhasil', '2026-11-01'),
(8, 1, 'Zakat Penghasilan', 2125000.00, 'Berhasil', '2026-11-02'),
(9, 1, 'Zakat Fitrah', 21250.00, 'Berhasil', '2026-11-04'),
(10, 5, 'Zakat Penghasilan', 12500.00, 'Berhasil', '2026-11-11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_rekening` (`nomor_rekening`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financings`
--
ALTER TABLE `financings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financing_applications`
--
ALTER TABLE `financing_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `idx_referensi_transfer` (`referensi_transfer`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `financings`
--
ALTER TABLE `financings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `financing_applications`
--
ALTER TABLE `financing_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `financings`
--
ALTER TABLE `financings`
  ADD CONSTRAINT `financings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  ADD CONSTRAINT `zakat_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2026 at 08:22 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manajemen_ukm`
--

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `mahasiswa_id` int NOT NULL,
  `mahasiswa_nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mahasiswa_npm` int DEFAULT NULL,
  `mahasiswa_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mahasiswa_password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user','ketua') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`mahasiswa_id`, `mahasiswa_nama`, `mahasiswa_npm`, `mahasiswa_email`, `mahasiswa_password`, `role`, `created_at`, `user_id`) VALUES
(2, 'asep', 1234567890, 'asep@Gmail.com', '$2y$10$7GO4lCUY7C1DYlY4eeOtFePIlGRXbQbgM9BgBK3IxnaUwQftOK2a.', 'user', '2026-05-24 14:38:02', NULL),
(3, 'vivi', 123345, 'vivi@Gmail.com', '$2y$10$..LDBYGTKtyl26Rz7OVLOOmRNr5GjaYp4WrN7kW44iYye1zkB0ilW', 'ketua', '2026-05-24 14:38:02', NULL),
(4, 'Nida', 25110604, 'nidanur1904@gmail.com', '$2y$10$F1r0q6cj02EX6zvOHJVEI.bVXw2nKfuBsPHWn9eBS9QOa5xhPMKRG', 'user', '2026-05-30 00:13:24', NULL),
(5, 'Marisa febriyanti', 251060495, 'marisa123@gmail.com', '$2y$10$GSoxhG0Z8sS8joIuDIk58uKteOwcLmDGEu3bUG3vwcwVDvQvE3gki', 'admin', '2026-05-30 01:17:00', NULL),
(6, 'Muhamad Aji', 251149609, 'aji123@gmail.com', '$2y$10$gRk5YXOuV/749ddyzgqTcerCZSFkY2fujCVzflLWgjmvOT9GMu69u', 'ketua', '2026-05-30 08:11:12', NULL),
(7, 'hasan', 12345, 'hasan@Gmail.com', '$2y$10$avv1JfyURu32Qrj.7VJCdeBePww4dv90LchcvH0vNOiahmHk/t7Wy', 'admin', '2026-06-01 04:07:25', NULL),
(9, 'hamid', 12213, 'hamid@gmail.com', '$2y$10$Lr/euNEIWNUYMyq.YR5AP.SJWhE52mKsHPGJ1JpkR1VQh/yjWGJ5O', 'ketua', '2026-06-01 04:38:35', 1),
(10, 'udin', 21212, 'user@gmail.com', '$2y$10$3qJJaypHBRKvFA8qsOZuOuw/ObbGeJdBJSm9Ise9YTdmFeSJVE3PW', 'user', '2026-06-03 07:15:22', 2),
(11, 'ketua', 123456, 'ketua@gmail.com', '$2y$10$E44vVy1Gd/0kaVHYPvTFquMPLbx/In4Gp0u8reV.Cv8i7nAcffmHm', 'user', '2026-06-22 03:15:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `session_id` int NOT NULL,
  `session_judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `session_deskripsi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `session_hari` date DEFAULT NULL,
  `ukm_id` int DEFAULT NULL,
  `narasumber` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `session_judul`, `session_deskripsi`, `session_hari`, `ukm_id`, `narasumber`, `created_at`) VALUES
(4, 'Pelatihan Soft skill canva', 'Pelatihan tentang soft skill yang dapat di lakukan di canva ', '2026-06-03', 4, 'Bang isal', '2026-05-31 05:06:15'),
(5, 'Perjuangan generasi muda menghadapi masa depan', 'Galau kedepannya gimana, galau kalian tidak takut tidak bisa apa apa? yuk ikut kami!', '2026-06-11', 1, 'mas joko anwar', '2026-06-08 14:10:52');

-- --------------------------------------------------------

--
-- Table structure for table `ukm`
--

CREATE TABLE `ukm` (
  `ukm_id` int NOT NULL,
  `ukm_nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ukm_slogan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ukm_nopengurus` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ukm`
--

INSERT INTO `ukm` (`ukm_id`, `ukm_nama`, `ukm_slogan`, `ukm_nopengurus`, `created_at`) VALUES
(1, 'HIPMI (Himpunan Pengusaha Muda Indonesia)', 'Pengusaha Pejuang, Pejuang Pengusaha', 8331246, '2026-05-30 01:35:15'),
(4, 'Commit', 'belajar teknologi', 8743214, '2026-06-08 11:57:27');

-- --------------------------------------------------------

--
-- Table structure for table `user_db`
--

CREATE TABLE `user_db` (
  `user_id` int NOT NULL,
  `mahasiswa_nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mahasiswa_npm` int DEFAULT NULL,
  `user_prodi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_fakultas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ukm_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_db`
--

INSERT INTO `user_db` (`user_id`, `mahasiswa_nama`, `mahasiswa_npm`, `user_prodi`, `user_fakultas`, `ukm_id`) VALUES
(1, NULL, 12213, 'Teknik Informatika', 'FTI', 4),
(2, NULL, 21212, 'Teknik Informatika', 'FTI', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`mahasiswa_id`),
  ADD UNIQUE KEY `mahasiswa` (`mahasiswa_npm`),
  ADD UNIQUE KEY `mahasiswa_nama` (`mahasiswa_nama`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `session_ibfk_1` (`ukm_id`);

--
-- Indexes for table `ukm`
--
ALTER TABLE `ukm`
  ADD PRIMARY KEY (`ukm_id`);

--
-- Indexes for table `user_db`
--
ALTER TABLE `user_db`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `mahasiswa_npm` (`mahasiswa_npm`),
  ADD KEY `mahasiswa_nama` (`mahasiswa_nama`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `mahasiswa_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ukm`
--
ALTER TABLE `ukm`
  MODIFY `ukm_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_db` (`user_id`);

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`ukm_id`) REFERENCES `ukm` (`ukm_id`);

--
-- Constraints for table `user_db`
--
ALTER TABLE `user_db`
  ADD CONSTRAINT `user_db_ibfk_1` FOREIGN KEY (`mahasiswa_npm`) REFERENCES `mahasiswa` (`mahasiswa_npm`),
  ADD CONSTRAINT `user_db_ibfk_2` FOREIGN KEY (`mahasiswa_nama`) REFERENCES `mahasiswa` (`mahasiswa_nama`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 07:38 PM
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
-- Database: `db_volcano`
--

-- --------------------------------------------------------

--
-- Table structure for table `sigmet_data`
--

CREATE TABLE `sigmet_data` (
  `id` int(11) NOT NULL,
  `transmisi_wib` datetime DEFAULT NULL,
  `valid_mulai_wib` datetime DEFAULT NULL,
  `valid_akhir_wib` datetime DEFAULT NULL,
  `area_penerbangan` varchar(100) DEFAULT NULL,
  `nama_gunung` varchar(50) DEFAULT NULL,
  `posisi_gunung` varchar(30) DEFAULT NULL,
  `obs_waktu` time DEFAULT NULL,
  `area_abu` text DEFAULT NULL,
  `ketinggian_meter` int(11) DEFAULT NULL,
  `pergerakan_abu` varchar(100) DEFAULT NULL,
  `intensitas_abu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sigmet_data`
--

INSERT INTO `sigmet_data` (`id`, `transmisi_wib`, `valid_mulai_wib`, `valid_akhir_wib`, `area_penerbangan`, `nama_gunung`, `posisi_gunung`, `obs_waktu`, `area_abu`, `ketinggian_meter`, `pergerakan_abu`, `intensitas_abu`) VALUES
(1, '2026-01-10 06:16:00', '2026-01-10 06:20:00', '2026-01-10 12:20:00', 'WAAA 092316 Waaf Sigmet 34 Valid 092320/100520 Waaa-waaf Ujung Pandang', 'Lewotolok', 'S0816 E12330', '06:00:00', 'WI S0815 E12330 - S0813 E12339 - S0818 E12339 - S0822 E12338 - S0817 E12329 - S0815 E12330', 1800, 'Timur 15 knot', 'Tidak berubah'),
(2, '2026-01-11 10:22:00', '2026-01-11 10:22:00', '2026-01-11 16:20:00', 'WAAA 110322 Waaf Sigmet 12 Valid 110322/110920 Waaa-waaf Ujung Pandang', 'Semeru', 'S0806 E11255', '10:00:00', 'WI S0808 E11253 - S0803 E11254 - S0806 E11324 - S0826 E11316 - S0808 E11253', 4500, 'Tenggara 10 knot', 'Tidak berubah'),
(3, '2026-01-11 20:15:00', '2026-01-11 20:15:00', '2026-01-12 02:13:00', 'WAAA 111315 Waaf Sigmet 13 Valid 111315/111913 Waaa-waaf Ujung Pandang', 'Raung', 'S0807 E11403', '19:50:00', 'WI S0811 E11403 - S0802 E11345 - S0749 E11347 - S0747 E11400 - S0809 E11408 - S0811 E11403', 3900, 'Barat Laut 05 knot', 'Menguat'),
(4, '2026-01-11 17:50:00', '2026-01-11 17:50:00', '2026-01-11 23:50:00', 'WAAA 111050 Waaf Sigmet 11 Valid 111050/111650 Waaa-waaf Ujung Pandang', 'Dukono', NULL, '17:30:00', 'WI N0146 E12757 - N0125 E12836 - N0101 E12810 - N0105 E12733 - N0146 E12749 - N0146 E12757', 3300, 'Tenggara 10 knot', 'Menguat'),
(5, '2026-01-11 18:30:00', '2026-01-11 18:30:00', '2026-01-12 00:30:00', 'WAAA 111130 Waaf Sigmet 12 Valid 111130/111730 Waaa-waaf Ujung Pandang', 'Semeru', 'S0806 E11255', '18:10:00', 'WI S0809 E11256 - S0807 E11246 - S0758 E11246 - S0757 E11254 - S0806 E11258 - S0809 E11256', 4500, 'Barat Laut 05 knot', 'Tidak berubah'),
(6, '2026-01-13 01:00:00', '2026-01-13 01:00:00', '2026-01-13 07:00:00', 'WAAA 121800 Waaf Sigmet 27 Valid 121800/130000 Waaa-waaf Ujung Pandang', 'Dukono', NULL, '00:40:00', 'WI N0202 E12751 - N0155 E12810 - N0124 E12826 - N0105 E12820 - N0101 E12750 - N0108 E12728 - N0123 E12720 - N0157 E12740 - N0202 E12751', 2700, 'Tenggara 05 knot', 'Tidak berubah'),
(7, '2026-01-13 14:35:00', '2026-01-13 14:35:00', '2026-01-13 20:10:00', 'WAAA 130735 Waaf Sigmet 06 Valid 130735/131310 Waaa-waaf Ujung Pandang', 'Dukono', NULL, '13:50:00', 'WI N0140 E12747 - N0151 E12753 - N0150 E12839 - N0118 E12839 - N0108 E12812 - N0140 E12747', 2700, 'Tenggara 10 knot', 'Menguat'),
(8, '2026-01-26 05:40:00', '2026-01-26 05:40:00', '2026-01-26 11:30:00', 'WAAF Ujung Pandang', 'Semeru', 'S0806 E11255', '05:10:00', 'WI S0810 E11253 - S0803 E11249 - S0743 E11301 - S0747 E11316 - S0801 E11321 - S0816 E11316 - S0810 E11253', 4500, 'Timur Laut 05 knot', 'Tidak berubah'),
(9, '2026-01-22 18:00:00', '2026-01-22 18:00:00', '2026-01-23 00:00:00', 'WAAF Ujung Pandang', 'Semeru', 'S0806 E11255', '17:40:00', 'WI S0807 E11256 - S0807 E11252 - S0802 E11249 - S0742 E11248 - S0744 E11306 - S0807 E11256', 4500, 'Utara 10 knot', 'Tidak berubah');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sigmet_data`
--
ALTER TABLE `sigmet_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sigmet_data`
--
ALTER TABLE `sigmet_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

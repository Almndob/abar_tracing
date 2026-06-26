-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 10 ديسمبر 2025 الساعة 20:03
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `abar_tracing`
--

-- --------------------------------------------------------

--
-- بنية الجدول `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `shipment_id` varchar(50) NOT NULL,
  `truck_id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `estimated_arrival` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `departure_date` datetime DEFAULT NULL,
  `arrival_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `shipments`
--

INSERT INTO `shipments` (`id`, `shipment_id`, `truck_id`, `origin`, `destination`, `estimated_arrival`, `status`, `departure_date`, `arrival_date`) VALUES
(1, '12', 1, 'd', 'l', '2025-10-08 17:23:00', 'In Transit', '2025-12-08 22:00:00', '2025-12-12 22:00:00'),
(2, 'SHP-1001', 1, 'University District - Hail', 'Industrial City - Hail', '2025-08-29 15:30:00', 'In Transit', '2025-09-22 22:00:00', '2025-12-11 22:00:00'),
(3, 'SHP-1002', 2, 'Al-Nuqra District - Hail', 'North Fuel Station - Hail', '2025-10-28 16:45:00', 'Pending', '2025-12-06 21:59:00', '2025-12-18 21:59:00'),
(4, 'SHP-1003', 3, 'Al-Badia District - Hail', 'Barzan Market - Hail', '2025-10-28 17:10:00', 'Pending', '2025-12-17 21:59:00', '2025-12-25 21:59:00'),
(5, 'SHP-1004', 4, 'Industrial City - Hail', 'Al-Zahra District - Hail', '2025-10-28 14:55:00', 'Pending', '2025-12-11 21:59:00', '2025-12-13 21:59:00'),
(6, 'SHP-1005', 5, 'West Al-Muntazah - Hail', 'Food Warehouse - Hail', '2025-10-28 18:00:00', 'Pending', '2025-12-18 21:59:00', '2025-12-23 21:59:00');

-- --------------------------------------------------------

--
-- بنية الجدول `tracking_history`
--

CREATE TABLE `tracking_history` (
  `id` int(11) NOT NULL,
  `truck_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tracking_history`
--

INSERT INTO `tracking_history` (`id`, `truck_id`, `latitude`, `longitude`, `timestamp`) VALUES
(1, 1, 24.99020870, 47.07180491, '2025-10-28 17:25:15'),
(2, 1, 24.75209200, 46.77616438, '2025-10-28 17:25:34'),
(3, 1, 24.79633380, 47.39742976, '2025-10-28 18:43:49'),
(4, 1, 25.48354468, 47.08277322, '2025-10-28 18:43:58'),
(5, 1, 25.00638868, 46.84367750, '2025-10-28 18:44:00'),
(6, 1, 25.22714050, 47.08778039, '2025-10-28 18:44:02'),
(7, 1, 25.02784023, 46.54641663, '2025-10-28 18:44:04'),
(8, 1, 24.84767363, 47.12586601, '2025-10-28 18:44:19'),
(9, 1, 27.64416922, 41.65057892, '2025-10-28 18:58:33'),
(10, 1, 27.46026896, 41.77799948, '2025-10-28 18:58:44'),
(11, 1, 27.54547015, 41.76186721, '2025-10-28 18:58:47'),
(12, 1, 27.59828373, 41.71397980, '2025-10-28 18:59:03'),
(13, 1, 27.52188000, 41.69067000, '2025-10-28 14:20:00'),
(14, 1, 27.52350000, 41.69590000, '2025-10-28 14:35:00'),
(15, 2, 27.50450000, 41.71620000, '2025-10-28 14:40:00'),
(16, 2, 27.51090000, 41.72080000, '2025-10-28 14:55:00'),
(17, 3, 27.50730000, 41.69090000, '2025-10-28 15:10:00'),
(18, 3, 27.51120000, 41.69340000, '2025-10-28 15:30:00'),
(19, 4, 27.49650000, 41.71120000, '2025-10-28 14:50:00'),
(20, 5, 27.52880000, 41.67840000, '2025-10-28 15:00:00'),
(21, 5, 27.53020000, 41.68270000, '2025-10-28 15:20:00'),
(22, 1, 27.68661767, 41.59050962, '2025-10-28 18:59:33'),
(23, 2, 27.57252425, 41.61616294, '2025-10-28 18:59:44'),
(24, 2, 27.68449705, 41.69270049, '2025-10-28 19:33:14'),
(25, 2, 27.48763653, 41.69100678, '2025-12-10 22:03:05');

-- --------------------------------------------------------

--
-- بنية الجدول `trucks`
--

CREATE TABLE `trucks` (
  `id` int(11) NOT NULL,
  `truck_number` varchar(50) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `trucks`
--

INSERT INTO `trucks` (`id`, `truck_number`, `driver_name`, `status`) VALUES
(1, 'a1', 'ahmed', 'On Route'),
(2, 'HA-001', 'Salem Al-Shammari', 'Active'),
(3, 'HA-002', 'Abdullah Al-Harbi', 'Loading'),
(4, 'HA-003', 'Mohammed Al-Otaibi', 'On Route'),
(5, 'HA-004', 'Fahad Al-Anzi', 'Stopped'),
(6, 'HA-005', 'Khalid Al-Mutairi', 'Active');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin', '$2y$10$zxIhg9B6VoLIQqFrYUqI2eis.fFDbXKIKqIYEmQNkSzmmGYvvUeui', 'admin@example.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `truck_id` (`truck_id`);

--
-- Indexes for table `tracking_history`
--
ALTER TABLE `tracking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `truck_id` (`truck_id`);

--
-- Indexes for table `trucks`
--
ALTER TABLE `trucks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tracking_history`
--
ALTER TABLE `tracking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `trucks`
--
ALTER TABLE `trucks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`);

--
-- قيود الجداول `tracking_history`
--
ALTER TABLE `tracking_history`
  ADD CONSTRAINT `tracking_history_ibfk_1` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

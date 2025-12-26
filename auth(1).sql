-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 03:33 PM
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
-- Database: `auth`
--

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `level` enum('certificate','diploma','bachelor','masters','phd') NOT NULL,
  `kuccps_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programmes`
--

INSERT INTO `programmes` (`id`, `code`, `prefix`, `name`, `faculty`, `level`, `kuccps_code`) VALUES
(1, 'cs', 'EB1', 'Bachelor of Science (Computer Science)', 'Science & Technology', 'bachelor', NULL),
(2, 'acs', 'EB2', 'Bachelor of Science (Applied Computer Science)', 'Science & Technology', 'bachelor', NULL),
(3, 'bit', 'EB3', 'Bachelor of Science (Business Information Technology)', 'Science & Technology', 'bachelor', NULL),
(4, 'bio', 'EB4', 'Bachelor of Science (Biology)', 'Science & Technology', 'bachelor', NULL),
(5, 'math', 'EB5', 'Bachelor of Science (Mathematics)', 'Science & Technology', 'bachelor', NULL),
(6, 'nursing', 'CB1', 'Bachelor of Science (Nursing)', 'Nursing & Public Health', 'bachelor', NULL),
(7, 'public_health', 'CB2', 'Bachelor of Public Health', 'Nursing & Public Health', 'bachelor', NULL),
(8, 'nutrition', 'CB3', 'Bachelor of Science (Human Nutrition & Dietetics)', 'Nursing & Public Health', 'bachelor', NULL),
(9, 'commerce', 'BB1', 'Bachelor of Commerce', 'Business Studies', 'bachelor', NULL),
(10, 'procurement', 'BB2', 'Bachelor of Procurement & Logistics Management', 'Business Studies', 'bachelor', NULL),
(11, 'entrepreneurship', 'BB3', 'Bachelor of Entrepreneurship & Enterprise Management', 'Business Studies', 'bachelor', NULL),
(12, 'agriculture', 'AG1', 'Bachelor of Science (Agriculture)', 'Agriculture', 'bachelor', NULL),
(13, 'horticulture', 'AG2', 'Bachelor of Science (Horticulture)', 'Agriculture', 'bachelor', NULL),
(14, 'animal_science', 'AG3', 'Bachelor of Science (Animal Science)', 'Agriculture', 'bachelor', NULL),
(15, 'cert_it', 'CST1', 'Certificate in Information Technology', 'Science & Technology', 'certificate', NULL),
(16, 'cert_business', 'CBU1', 'Certificate in Business Management', 'Business Studies', 'certificate', NULL),
(17, 'mba', 'MBA503', 'Master of Business Administration', 'Business Studies', 'masters', NULL),
(18, 'msc_cs', 'MSC501', 'M.Sc. Computer Science', 'Science & Technology', 'masters', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `reg_no` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fullname` varchar(100) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `campus` varchar(50) DEFAULT 'MAIN',
  `programme` varchar(150) DEFAULT NULL,
  `attempted_units` int(11) DEFAULT 0,
  `registered_units` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `reg_no`, `password`, `role`, `status`, `last_login`, `created_at`, `gender`, `dob`, `address`, `campus`, `programme`, `attempted_units`, `registered_units`) VALUES
(1, 'Prudence', 'Wasonga', 'prudencenereah@gmail.com', 'EB1/56145/21', '$2y$10$o2HuOBhj.dmMz1LHGo4c.OxuhM8gFErB/J7drQg9Wiz5NYj.kpK6y', 'user', 'active', NULL, '2025-10-04 05:57:20', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 58, 0),
(2, 'Admin', 'User', 'admin@berrywasonga.com', 'ADMIN001', '$2y$10$bdGgPTEYQ1VmcDw1Sck.lurur9DUjz2TdOhx3PE6CAtsjA.3gZVbG', 'admin', 'active', '2025-12-21 20:28:58', '2025-10-04 05:57:20', 'Female', NULL, NULL, 'MAIN', NULL, 0, 0),
(10, 'Beryl', 'Wasonga', 'berrylwasonga@gmail.com', 'EB1/56146/21', '$2y$10$ozwmDLv8vPDCdutMaeSirefuNeFQ5zF9QTIVBFDqkh1LCua3828Du', 'user', 'active', NULL, '2025-12-19 07:54:58', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 58, 0),
(11, 'Samson', 'Odhiambo', 'Samson@student.com', 'CS/12345/22', '$2y$10$ZWRa2pZORdAcNTdS3TguiO17P8mDAR9EcePHe79e5OCaizA8hxwOW', 'user', 'active', '2025-12-21 20:36:50', '2025-12-19 07:54:58', 'Male', '2002-08-15', '40600', 'MAIN', 'Bachelor of Information Technology', 2, 2),
(12, 'Jocinta', 'Oballa', 'Jocinta@student.com', 'EB1/56147/21', '$2y$10$D3zLsTwMx2n/f5a1AvpNn.tSCXpaLf0xI2sFUZEWcPFdVKVrkDsfG', 'user', 'active', NULL, '2025-12-19 10:08:30', 'Female', '1998-01-01', '40600', 'MAIN', 'BSc. Computer Science', 0, 0),
(13, 'Blessing', 'Wasonga', 'wasongaberryl3@gmail.com', 'EB1/00001/25', '$2y$10$eHUthtGWei8fEpW9OiJCE.eqoMUpXZHV9kxKs5T14rnUFEf1Cy2f6', 'user', 'active', '2025-12-20 07:51:57', '2025-12-20 07:30:52', 'Female', '2000-12-02', '40600', 'MAIN', 'BSc. Computer Science', 0, 0),
(14, 'Beula', 'Faith', 'Wasongaprudence@gmail.com', 'EB1/00002/25', '$2y$10$dhnmagKZaZPwf3UrSrWvweuex3VEj.XTr4OHwlxedvqE02Yx5cJFC', 'user', 'active', NULL, '2025-12-20 07:59:24', '', '1999-02-01', '40600', 'MAIN', 'BSc. Computer Science', 0, 0),
(15, 'Godpraise', 'Wasonga', 'wasongagodpraise@gmail.com', 'CB1/00001/25', '$2y$10$NlrNCUl3ffOTvPRo3gX0UOJYfEapgDNJwcl98bT/z0N8A4BOSfS9G', 'user', 'active', NULL, '2025-12-20 09:35:35', 'Male', '1986-01-07', '40607', 'MAIN', 'Bachelor of Science (Nursing)', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `code_2` (`code`),
  ADD KEY `level` (`level`,`faculty`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reg_no` (`reg_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 02:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+03:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auth`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_name`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, '2025/2026', '2025-09-01', '2026-08-31', 'active', '2025-01-20 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `academic_year_id`, `semester_name`, `start_date`, `end_date`, `is_current`, `status`, `created_at`) VALUES
(1, 1, 'Sep-Dec 2025', '2025-09-01', '2025-12-20', 0, 'inactive', '2025-01-20 08:00:00'),
(2, 1, 'Jan-Apr 2026', '2026-01-05', '2026-04-17', 1, 'active', '2025-01-20 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int(11) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `faculty_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `faculty_name`, `faculty_code`, `created_at`) VALUES
(1, 'Faculty of Science and Engineering and Technology', 'FSET', '2025-01-20 08:00:00'),
(2, 'Faculty of Business and Law', 'FBL', '2025-01-20 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `level` enum('Certificate','Diploma','Bachelor','Masters','PhD') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `faculty_id`, `course_name`, `course_code`, `level`, `created_at`) VALUES
(1, 1, 'Bachelor of Science (Computer Science)', 'EB1', 'Bachelor', '2025-01-20 08:00:00'),
(2, 1, 'Bachelor of Science (Applied Computer Science)', 'EB2', 'Bachelor', '2025-01-20 08:00:00'),
(3, 2, 'Bachelor of Commerce', 'DB1', 'Bachelor', '2025-01-20 08:00:00');

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
  `course_id` int(11) DEFAULT NULL,
  `attempted_units` int(11) DEFAULT 0,
  `registered_units` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `reg_no`, `password`, `role`, `status`, `last_login`, `created_at`, `gender`, `dob`, `address`, `campus`, `programme`, `course_id`, `attempted_units`, `registered_units`) VALUES
(1, 'Prudence', 'Wasonga', 'prudencenereah@gmail.com', 'EB1/56145/21', '$2y$10$o2HuOBhj.dmMz1LHGo4c.OxuhM8gFErB/J7drQg9Wiz5NYj.kpK6y', 'user', 'active', NULL, '2025-10-04 05:57:20', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 1, 58, 0),
(2, 'Admin', 'User', 'admin@berrywasonga.com', 'ADMIN001', '$2y$10$bdGgPTEYQ1VmcDw1Sck.lurur9DUjz2TdOhx3PE6CAtsjA.3gZVbG', 'admin', 'active', '2025-12-21 20:28:58', '2025-10-04 05:57:20', 'Female', NULL, NULL, 'MAIN', NULL, NULL, 0, 0),
(10, 'Beryl', 'Wasonga', 'berrylwasonga@gmail.com', 'EB1/56146/21', '$2y$10$ozwmDLv8vPDCdutMaeSirefuNeFQ5zF9QTIVBFDqkh1LCua3828Du', 'user', 'active', NULL, '2025-12-19 07:54:58', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 1, 58, 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_academic_info`
--

CREATE TABLE `student_academic_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_year_of_study` int(11) NOT NULL DEFAULT 1,
  `current_semester_of_study` int(11) NOT NULL DEFAULT 1,
  `academic_year_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_academic_info`
--

INSERT INTO `student_academic_info` (`id`, `user_id`, `current_year_of_study`, `current_semester_of_study`, `academic_year_id`) VALUES
(1, 1, 4, 1, 1),
(2, 10, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_units`
--

CREATE TABLE `course_units` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `unit_type` enum('core','elective') DEFAULT 'core',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_units`
--

INSERT INTO `course_units` (`id`, `unit_code`, `unit_name`, `description`, `credit_hours`, `unit_type`, `status`) VALUES
(1, 'COSC 101', 'Introduction to Computer Science', 'Basics of CS', 3, 'core', 'active'),
(2, 'COSC 102', 'Programming Fundamentals', 'Intro to Programming in C', 3, 'core', 'active'),
(3, 'MATH 101', 'Calculus I', 'Differential Calculus', 3, 'core', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `course_unit_assignments`
--

CREATE TABLE `course_unit_assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `is_compulsory` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_unit_assignments`
--

INSERT INTO `course_unit_assignments` (`id`, `course_id`, `unit_id`, `year_of_study`, `semester_id`, `is_compulsory`) VALUES
(1, 1, 1, 1, 1, 1),
(2, 1, 2, 1, 1, 1),
(3, 1, 3, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `registration_windows`
--

CREATE TABLE `registration_windows` (
  `id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `registration_type` enum('regular','supplementary','special') NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('open','closed') DEFAULT 'closed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_windows`
--

INSERT INTO `registration_windows` (`id`, `semester_id`, `registration_type`, `start_datetime`, `end_datetime`, `status`) VALUES
(1, 2, 'regular', '2026-01-01 00:00:00', '2026-02-28 23:59:59', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `amount_required` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','unpaid') GENERATED ALWAYS AS (case when `amount_paid` >= `amount_required` then 'paid' when `amount_paid` > 0 then 'partial' else 'unpaid' end) STORED,
  `last_payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_basket`
--

CREATE TABLE `registration_basket` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `registration_type` varchar(20) NOT NULL DEFAULT 'regular',
  `is_retake` tinyint(1) DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `registration_type` varchar(20) NOT NULL DEFAULT 'regular',
  `registration_status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `credit_hours` int(11) NOT NULL,
  `is_retake` tinyint(1) DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_code` (`faculty_code`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reg_no` (`reg_no`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_academic_info`
--
ALTER TABLE `student_academic_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indexes for table `course_units`
--
ALTER TABLE `course_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_code` (`unit_code`);

--
-- Indexes for table `course_unit_assignments`
--
ALTER TABLE `course_unit_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `registration_windows`
--
ALTER TABLE `registration_windows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `registration_basket`
--
ALTER TABLE `registration_basket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `academic_years` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `semesters` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `faculties` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `courses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `student_academic_info` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `course_units` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `course_unit_assignments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `registration_windows` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `fee_payments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `registration_basket` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `course_registrations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);

ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

ALTER TABLE `student_academic_info`
  ADD CONSTRAINT `student_academic_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_academic_info_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL;

ALTER TABLE `course_unit_assignments`
  ADD CONSTRAINT `course_unit_assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_unit_assignments_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_unit_assignments_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

ALTER TABLE `registration_windows`
  ADD CONSTRAINT `registration_windows_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

ALTER TABLE `registration_basket`
  ADD CONSTRAINT `registration_basket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_basket_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_basket_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE;

ALTER TABLE `course_registrations`
  ADD CONSTRAINT `course_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2026 at 09:39 PM
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
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year_name`, `start_date`, `end_date`, `is_current`, `status`, `created_at`, `updated_at`) VALUES
(1, '2025/2026', '2025-09-01', '2026-08-31', 1, 'active', '2026-01-22 13:03:48', '2026-01-22 13:03:48');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `level` enum('Certificate','Diploma','Bachelor','Masters','PhD') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `course_code`, `faculty_id`, `level`, `created_at`) VALUES
(1, 'Bachelor of Science (Computer Science)', 'EB1', 1, 'Bachelor', '2026-01-20 22:28:23'),
(2, 'Bachelor of Science (Applied Computer Science)', 'EB2', 1, 'Bachelor', '2026-01-20 22:28:23'),
(3, 'Bachelor of Science (Business Information Technology)', 'EB3', 1, 'Bachelor', '2026-01-20 22:28:23'),
(4, 'Bachelor of Science (Mathematics)', 'EB5', 1, 'Bachelor', '2026-01-20 22:28:23'),
(5, 'Certificate in Information Technology', 'CST1', 1, 'Certificate', '2026-01-20 22:28:23'),
(6, 'Diploma in Computer Science', 'DST2', 1, 'Diploma', '2026-01-20 22:28:23'),
(7, 'Bachelor of Science (Nursing)', 'CB1', 2, 'Bachelor', '2026-01-20 22:28:23'),
(8, 'Bachelor of Public Health', 'CB2', 2, 'Bachelor', '2026-01-20 22:28:23'),
(9, 'Bachelor of Science (Human Nutrition & Dietetics)', 'CB3', 2, 'Bachelor', '2026-01-20 22:28:23'),
(10, 'Diploma in Community Health Nursing', 'DHS1', 2, 'Diploma', '2026-01-20 22:28:23'),
(11, 'Bachelor of Commerce', 'BB1', 3, 'Bachelor', '2026-01-20 22:28:23'),
(12, 'Bachelor of Procurement & Logistics Management', 'BB2', 3, 'Bachelor', '2026-01-20 22:28:23'),
(13, 'Bachelor of Entrepreneurship & Enterprise Management', 'BB3', 3, 'Bachelor', '2026-01-20 22:28:23'),
(14, 'Master of Business Administration', 'MBA503', 3, 'Masters', '2026-01-20 22:28:23'),
(15, 'Diploma in Accounting', 'DBU1', 3, 'Diploma', '2026-01-20 22:28:23'),
(16, 'Bachelor of Science (Agriculture)', 'AG1', 4, 'Bachelor', '2026-01-20 22:28:23'),
(17, 'Bachelor of Science (Horticulture)', 'AG2', 4, 'Bachelor', '2026-01-20 22:28:23'),
(18, 'Bachelor of Science (Animal Science)', 'AG3', 4, 'Bachelor', '2026-01-20 22:28:23'),
(19, 'Diploma in Agriculture', 'DAG1', 4, 'Diploma', '2026-01-20 22:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `registration_type` enum('regular','supplementary','special') NOT NULL DEFAULT 'regular',
  `registration_status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `registration_date` datetime DEFAULT current_timestamp(),
  `approval_date` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `is_retake` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_units`
--

CREATE TABLE `course_units` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `unit_type` enum('core','elective','minor') DEFAULT 'core',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_units`
--

INSERT INTO `course_units` (`id`, `unit_code`, `unit_name`, `description`, `credit_hours`, `unit_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CSC101', 'Introduction to Computer Science', 'Basic concepts of programming and computer systems', 3, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(2, 'CSC102', 'Data Structures', 'Study of arrays, linked lists, stacks, queues, and trees', 3, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(3, 'MAT101', 'Calculus I', 'Differential and integral calculus', 4, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(4, 'PHY101', 'Physics I', 'Mechanics and thermodynamics', 3, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(5, 'ENG101', 'Communication Skills', 'Academic writing and presentation skills', 2, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(6, 'CSC201', 'Database Systems', 'Design and implementation of database systems', 3, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(7, 'CSC202', 'Web Programming', 'HTML, CSS, JavaScript, and PHP', 3, 'elective', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39'),
(8, 'MAT201', 'Linear Algebra', 'Matrices, vectors, and linear transformations', 3, 'core', 'active', '2026-01-22 13:04:39', '2026-01-22 13:04:39');

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

INSERT INTO `course_unit_assignments` (`id`, `course_id`, `unit_id`, `year_of_study`, `semester_id`, `is_compulsory`, `created_at`) VALUES
(1, 1, 1, 1, 1, 1, '2026-01-22 13:05:10'),
(2, 1, 3, 1, 1, 1, '2026-01-22 13:05:10'),
(3, 1, 4, 1, 1, 1, '2026-01-22 13:05:10'),
(4, 1, 5, 1, 1, 1, '2026-01-22 13:05:10'),
(5, 1, 2, 1, 2, 1, '2026-01-22 13:05:28'),
(6, 1, 8, 1, 2, 1, '2026-01-22 13:05:28'),
(7, 1, 6, 2, 1, 1, '2026-01-22 13:06:03'),
(8, 1, 7, 2, 1, 0, '2026-01-22 13:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int(11) NOT NULL,
  `faculty_name` varchar(255) NOT NULL,
  `faculty_code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `faculty_name`, `faculty_code`, `created_at`) VALUES
(1, 'Faculty of Science & Technology', 'FST', '2026-01-20 22:24:47'),
(2, 'School of Nursing & Public Health', 'SNP', '2026-01-20 22:24:47'),
(3, 'Faculty of Business Studies', 'FBS', '2026-01-20 22:24:47'),
(4, 'Faculty of Agriculture & Environmental Studies', 'FAES', '2026-01-20 22:24:47'),
(5, 'Faculty of Humanities & Social Sciences', 'FHSS', '2026-01-20 22:24:47'),
(6, 'Faculty of Education & Resources Development', 'FERD', '2026-01-20 22:24:47'),
(7, 'Faculty of Engineering', 'FENG', '2026-01-20 22:24:47'),
(8, 'School of Law', 'LAW', '2026-01-20 22:24:47');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_required` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid','overpaid') DEFAULT 'unpaid',
  `payment_date` datetime DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `user_id`, `semester_id`, `amount_paid`, `amount_required`, `payment_status`, `payment_date`, `receipt_number`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 45000.00, 50000.00, 'partial', NULL, NULL, '2026-01-22 13:06:49', '2026-01-22 13:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `registration_basket`
--

CREATE TABLE `registration_basket` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `registration_type` enum('regular','supplementary','special') NOT NULL DEFAULT 'regular',
  `is_retake` tinyint(1) DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `status` enum('upcoming','open','closed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_windows`
--

INSERT INTO `registration_windows` (`id`, `semester_id`, `registration_type`, `start_datetime`, `end_datetime`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'regular', '2025-08-20 00:00:00', '2025-09-10 23:59:59', 'open', '2026-01-22 13:06:24', '2026-01-22 13:06:24'),
(2, 1, 'supplementary', '2025-09-11 00:00:00', '2025-09-15 23:59:59', 'upcoming', '2026-01-22 13:06:24', '2026-01-22 13:06:24'),
(3, 1, 'special', '2025-09-11 00:00:00', '2025-09-20 23:59:59', 'upcoming', '2026-01-22 13:06:24', '2026-01-22 13:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL,
  `semester_code` varchar(10) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `status` enum('upcoming','active','closed','archived') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `academic_year_id`, `semester_name`, `semester_code`, `start_date`, `end_date`, `is_current`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Semester 1 - 2025/2026', 'SEM1', '2025-09-01', '2025-12-20', 1, 'active', '2026-01-22 13:04:08', '2026-01-22 13:04:08'),
(2, 1, 'Semester 2 - 2025/2026', 'SEM2', '2026-01-10', '2026-05-15', 0, 'upcoming', '2026-01-22 13:04:08', '2026-01-22 13:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `student_academic_info`
--

CREATE TABLE `student_academic_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_year_of_study` int(11) NOT NULL DEFAULT 1,
  `entry_year` varchar(10) NOT NULL,
  `expected_graduation_year` varchar(10) DEFAULT NULL,
  `academic_status` enum('active','suspended','withdrawn','graduated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_academic_info`
--

INSERT INTO `student_academic_info` (`id`, `user_id`, `current_year_of_study`, `entry_year`, `expected_graduation_year`, `academic_status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025', '2029', 'active', '2026-01-22 13:06:49', '2026-01-22 13:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Prudence', 'Wasonga', 'prudencenereah@gmail.com', 'EB1/56145/21', '$2y$10$o2HuOBhj.dmMz1LHGo4c.OxuhM8gFErB/J7drQg9Wiz5NYj.kpK6y', 'user', 'active', NULL, '2025-10-04 05:57:20', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', NULL, 58, 0),
(2, 'Admin', 'User', 'admin@berrywasonga.com', 'ADMIN001', '$2y$10$bdGgPTEYQ1VmcDw1Sck.lurur9DUjz2TdOhx3PE6CAtsjA.3gZVbG', 'admin', 'active', '2026-01-22 20:07:05', '2025-10-04 05:57:20', 'Female', NULL, NULL, 'MAIN', NULL, NULL, 0, 0),
(10, 'Beryl', 'Wasonga', 'berrylwasonga@gmail.com', 'EB1/56146/21', '$2y$10$ozwmDLv8vPDCdutMaeSirefuNeFQ5zF9QTIVBFDqkh1LCua3828Du', 'user', 'active', NULL, '2025-12-19 07:54:58', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', NULL, 58, 0),
(11, 'Samson', 'Odhiambo', 'Samson@student.com', 'CS/12345/22', '$2y$10$ZWRa2pZORdAcNTdS3TguiO17P8mDAR9EcePHe79e5OCaizA8hxwOW', 'user', 'active', '2026-01-21 21:37:06', '2025-12-19 07:54:58', 'Male', '2002-08-15', '40600', 'MAIN', 'Bachelor of Information Technology', NULL, 2, 2),
(12, 'Jocinta', 'Oballa', 'Jocinta@student.com', 'EB1/56147/21', '$2y$10$D3zLsTwMx2n/f5a1AvpNn.tSCXpaLf0xI2sFUZEWcPFdVKVrkDsfG', 'user', 'active', NULL, '2025-12-19 10:08:30', 'Female', '1998-01-01', '40600', 'MAIN', 'BSc. Computer Science', NULL, 0, 0),
(13, 'Blessing', 'Wasonga', 'wasongaberryl3@gmail.com', 'EB1/00001/25', '$2y$10$eHUthtGWei8fEpW9OiJCE.eqoMUpXZHV9kxKs5T14rnUFEf1Cy2f6', 'user', 'active', '2025-12-20 07:51:57', '2025-12-20 07:30:52', 'Female', '2000-12-02', '40600', 'MAIN', 'BSc. Computer Science', NULL, 0, 0),
(14, 'Beula', 'Faith', 'Wasongaprudence@gmail.com', 'EB1/00002/25', '$2y$10$dhnmagKZaZPwf3UrSrWvweuex3VEj.XTr4OHwlxedvqE02Yx5cJFC', 'user', 'active', NULL, '2025-12-20 07:59:24', '', '1999-02-01', '40600', 'MAIN', 'BSc. Computer Science', NULL, 0, 0),
(15, 'Godpraise', 'Wasonga', 'wasongagodpraise@gmail.com', 'CB1/00001/25', '$2y$10$eTBHYgB.XqGIlViEDo/xr..df8gEzBE7O.bckXZA71ZoMXEK0e.G.', 'user', 'active', '2026-01-22 11:35:22', '2025-12-20 09:35:35', 'Male', '1986-01-07', '40607', 'MAIN', 'Bachelor of Science (Nursing)', NULL, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_name` (`year_name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`user_id`,`semester_id`,`unit_id`,`registration_type`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_registrations_semester` (`semester_id`),
  ADD KEY `idx_registrations_unit` (`unit_id`),
  ADD KEY `idx_registrations_status` (`registration_status`);

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
  ADD UNIQUE KEY `unique_assignment` (`course_id`,`unit_id`,`year_of_study`,`semester_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `idx_unit_assignments_course` (`course_id`),
  ADD KEY `idx_unit_assignments_semester` (`semester_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_code` (`faculty_code`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_payment` (`user_id`,`semester_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `registration_basket`
--
ALTER TABLE `registration_basket`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_basket_item` (`user_id`,`semester_id`,`unit_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `idx_basket_user` (`user_id`);

--
-- Indexes for table `registration_windows`
--
ALTER TABLE `registration_windows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_window` (`semester_id`,`registration_type`),
  ADD KEY `idx_windows_semester` (`semester_id`),
  ADD KEY `idx_windows_status` (`status`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_semester` (`academic_year_id`,`semester_code`);

--
-- Indexes for table `student_academic_info`
--
ALTER TABLE `student_academic_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reg_no` (`reg_no`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_units`
--
ALTER TABLE `course_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `course_unit_assignments`
--
ALTER TABLE `course_unit_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registration_basket`
--
ALTER TABLE `registration_basket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_windows`
--
ALTER TABLE `registration_windows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_academic_info`
--
ALTER TABLE `student_academic_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`);

--
-- Constraints for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD CONSTRAINT `course_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_unit_assignments`
--
ALTER TABLE `course_unit_assignments`
  ADD CONSTRAINT `course_unit_assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_unit_assignments_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_unit_assignments_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registration_basket`
--
ALTER TABLE `registration_basket`
  ADD CONSTRAINT `registration_basket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_basket_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registration_basket_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registration_windows`
--
ALTER TABLE `registration_windows`
  ADD CONSTRAINT `registration_windows_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_academic_info`
--
ALTER TABLE `student_academic_info`
  ADD CONSTRAINT `student_academic_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Consolidated Database Schema for Mini-Auth Project
-- Includes Faculty → Programs → Courses → Units Hierarchy

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Drop existing tables
-- --------------------------------------------------------
DROP TABLE IF EXISTS `academic_years`;
DROP TABLE IF EXISTS `faculties`;
DROP TABLE IF EXISTS `programs`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `course_year_levels`;
DROP TABLE IF EXISTS `course_units`;
DROP TABLE IF EXISTS `semesters`;
DROP TABLE IF EXISTS `course_unit_assignments`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `faculty_staff`;
DROP TABLE IF EXISTS `unit_lecturer_assignments`;
DROP TABLE IF EXISTS `course_registrations`;
DROP TABLE IF EXISTS `grades`;
DROP TABLE IF EXISTS `fee_payments`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `registration_basket`;
DROP TABLE IF EXISTS `registration_windows`;
DROP TABLE IF EXISTS `requisitions`;
DROP TABLE IF EXISTS `student_academic_info`;
DROP TABLE IF EXISTS `student_documents`;

-- --------------------------------------------------------
-- Table structure for table `academic_years`
-- --------------------------------------------------------
CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `faculties`
-- --------------------------------------------------------
CREATE TABLE `faculties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_name` varchar(100) NOT NULL,
  `faculty_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_code` (`faculty_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `courses`
-- --------------------------------------------------------
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `level` enum('Certificate','Diploma','Bachelor','Masters','PhD') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`),
  CONSTRAINT `courses_ibfk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `course_year_levels`
-- --------------------------------------------------------
CREATE TABLE `course_year_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `year_level` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `course_year_levels_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `course_units`
-- --------------------------------------------------------
CREATE TABLE `course_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(20) NOT NULL,
  `unit_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `credit_hours` int(11) NOT NULL DEFAULT 3,
  `unit_type` enum('core','elective') DEFAULT 'core',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_code` (`unit_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `semesters`
-- --------------------------------------------------------
CREATE TABLE `semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `course_unit_assignments`
-- --------------------------------------------------------
CREATE TABLE `course_unit_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `year_of_study` int(11) NOT NULL,
  `year_level_id` int(11) DEFAULT NULL,
  `semester_id` int(11) NOT NULL,
  `is_compulsory` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `course_unit_assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_unit_assignments_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_unit_assignments_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `faculty_id` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `admission_year` int(11) DEFAULT NULL,
  `attempted_units` int(11) DEFAULT 0,
  `registered_units` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `reg_no` (`reg_no`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `faculty_staff`
-- --------------------------------------------------------
CREATE TABLE `faculty_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `faculty_staff_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `faculty_staff_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `unit_lecturer_assignments`
-- --------------------------------------------------------
CREATE TABLE `unit_lecturer_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `unit_lecturer_assignments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_lecturer_assignments_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `course_registrations`
-- --------------------------------------------------------
CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `rejection_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `course_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_registrations_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_registrations_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_registrations_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `grades`
-- --------------------------------------------------------
CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `marks` int(11) DEFAULT 0,
  `grade` varchar(2) DEFAULT 'F',
  `status` enum('Pass','Fail') DEFAULT 'Fail',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `fee_payments`
-- --------------------------------------------------------
CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `amount_required` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','unpaid') GENERATED ALWAYS AS (case when `amount_paid` >= `amount_required` then 'paid' when `amount_paid` > 0 then 'partial' else 'unpaid' end) STORED,
  `last_payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `password_resets`
-- --------------------------------------------------------
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `registration_basket`
-- --------------------------------------------------------
CREATE TABLE `registration_basket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `registration_type` varchar(20) NOT NULL DEFAULT 'regular',
  `is_retake` tinyint(1) DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `registration_basket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registration_basket_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registration_basket_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `registration_windows`
-- --------------------------------------------------------
CREATE TABLE `registration_windows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester_id` int(11) NOT NULL,
  `registration_type` enum('regular','supplementary','special') NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('open','closed') DEFAULT 'closed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `registration_windows_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `requisitions`
-- --------------------------------------------------------
CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `course_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `student_academic_info`
-- --------------------------------------------------------
CREATE TABLE `student_academic_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `current_year_of_study` int(11) NOT NULL DEFAULT 1,
  `current_semester_of_study` int(11) NOT NULL DEFAULT 1,
  `academic_year_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `student_academic_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_academic_info_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `student_documents`
-- --------------------------------------------------------
CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_documents_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_documents_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dumping initial data
-- --------------------------------------------------------

INSERT INTO `academic_years` (`id`, `year_name`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, '2025/2026', '2025-09-01', '2026-08-31', 'active', '2025-01-20 05:00:00');

INSERT INTO `semesters` (`id`, `academic_year_id`, `semester_name`, `start_date`, `end_date`, `is_current`, `status`, `created_at`) VALUES
(1, 1, 'Sep-Dec 2025', '2025-09-01', '2025-12-20', 0, 'inactive', '2025-01-20 05:00:00'),
(2, 1, 'Jan-Apr 2026', '2026-01-05', '2026-04-17', 1, 'active', '2025-01-20 05:00:00');

INSERT INTO `faculties` (`id`, `faculty_name`, `faculty_code`, `created_at`) VALUES
(1, 'Faculty of Science and Engineering and Technology', 'FSET', '2025-01-20 05:00:00'),
(2, 'Faculty of Business and Law', 'FBL', '2025-01-20 05:00:00');

INSERT INTO `courses` (`id`, `faculty_id`, `course_name`, `course_code`, `level`, `created_at`) VALUES
(1, 1, 'Bachelor of Science (Computer Science)', 'EB1', 'Bachelor', '2025-01-20 05:00:00'),
(2, 1, 'Bachelor of Science (Applied Computer Science)', 'EB2', 'Bachelor', '2025-01-20 05:00:00'),
(3, 2, 'Bachelor of Commerce', 'DB1', 'Bachelor', '2025-01-20 05:00:00');

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `reg_no`, `password`, `role`, `status`, `last_login`, `created_at`, `gender`, `dob`, `address`, `campus`, `programme`, `course_id`, `faculty_id`, `year_level`, `admission_year`) VALUES
(1, 'Prudence', 'Wasonga', 'prudencenereah@gmail.com', 'EB1/56145/21', '$2y$10$E7WUw3/UTK6gLXtsKykbZ.TIJgUWFNAys9Xgz.OCBXM93SFc2me6W', 'user', 'active', '2026-01-26 11:00:26', '2025-10-04 02:57:20', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 1, 1, 4, 2021),
(2, 'Admin', 'User', 'admin@berrywasonga.com', 'ADMIN001', '$2y$10$bdGgPTEYQ1VmcDw1Sck.lurur9DUjz2TdOhx3PE6CAtsjA.3gZVbG', 'admin', 'active', '2026-01-25 01:16:31', '2025-10-04 02:57:20', 'Female', NULL, NULL, 'MAIN', NULL, NULL, NULL, NULL, NULL),
(10, 'Beryl', 'Wasonga', 'berrylwasonga@gmail.com', 'EB1/56146/21', '$2y$10$ozwmDLv8vPDCdutMaeSirefuNeFQ5zF9QTIVBFDqkh1LCua3828Du', 'user', 'active', NULL, '2025-12-19 04:54:58', 'Female', '2003-04-03', '90 - 40600', 'MAIN', 'Bachelor of Science (Computer Science)', 1, 1, 4, 2021);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 26, 2025 at 05:53 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 8.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `NoteManegementSystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `AdminLogs`
--

CREATE TABLE `AdminLogs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Admins`
--

CREATE TABLE `Admins` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('superadmin','moderator') DEFAULT 'moderator',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Admins`
--

INSERT INTO `Admins` (`admin_id`, `user_id`, `role`, `created_at`) VALUES
(1, 7, 'superadmin', '2025-06-22 14:02:20'),
(3, 29, 'superadmin', '2025-06-23 12:56:02'),
(5, 39, 'superadmin', '2025-08-21 11:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `AISuggestionGoalLinks`
--

CREATE TABLE `AISuggestionGoalLinks` (
  `link_id` int(11) NOT NULL,
  `suggestion_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AISuggestions`
--

CREATE TABLE `AISuggestions` (
  `suggestion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `suggestion_type` enum('goal_breakdown','priority','completion_forecast') NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AutoRenewalHistory`
--

CREATE TABLE `AutoRenewalHistory` (
  `renewal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `renewal_date` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `failure_reason` text DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AutoRenewalSettings`
--

CREATE TABLE `AutoRenewalSettings` (
  `setting_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_auto_renewal` tinyint(1) DEFAULT 0,
  `renewal_plan_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `last_renewal_attempt` timestamp NULL DEFAULT NULL,
  `next_renewal_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `CourseGoalLinks`
--

CREATE TABLE `CourseGoalLinks` (
  `link_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Courses`
--

CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `EventGoalLinks`
--

CREATE TABLE `EventGoalLinks` (
  `link_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `EventGoalLinks`
--

INSERT INTO `EventGoalLinks` (`link_id`, `event_id`, `goal_id`, `created_at`) VALUES
(1, 5, 13, '2025-06-22 20:45:58'),
(2, 6, 14, '2025-06-22 20:46:26'),
(3, 7, 15, '2025-06-22 20:57:08'),
(4, 8, 16, '2025-06-23 13:22:31'),
(5, 9, 17, '2025-06-23 13:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Events`
--

INSERT INTO `Events` (`event_id`, `user_id`, `title`, `description`, `event_time`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 'Test Event', 'Test event description', '2025-06-23 11:07:42', '2025-06-22 04:07:42', '2025-06-22 04:07:42', NULL),
(2, 7, 'Test Event', 'Test event description', '2025-06-22 11:11:33', '2025-06-22 04:11:34', '2025-06-22 04:11:34', NULL),
(3, 7, 'Test Event 1750592699', 'This is a test event for API testing', '2025-06-23 11:44:59', '2025-06-22 04:44:59', '2025-06-22 04:44:59', NULL),
(4, 7, 'API Test Event 1750592987', 'Event for comprehensive API testing', '2025-06-23 11:49:47', '2025-06-22 04:49:47', '2025-06-22 04:49:47', NULL),
(5, 7, 'UPDATED - Comprehensive Test Event 1750625157', 'This event has been updated by the comprehensive API test script.', '2025-06-23 20:45:50', '2025-06-22 13:45:50', '2025-06-22 13:45:57', NULL),
(6, 7, 'UPDATED - Comprehensive Test Event 1750625185', 'This event has been updated by the comprehensive API test script.', '2025-06-23 20:46:18', '2025-06-22 13:46:18', '2025-06-22 13:46:25', NULL),
(7, 7, 'UPDATED - Comprehensive Test Event 1750625827', 'This event has been updated by the comprehensive API test script.', '2025-06-23 20:57:00', '2025-06-22 13:57:00', '2025-06-22 13:57:07', NULL),
(8, 7, 'UPDATED - Comprehensive Test Event 1750684948', 'This event has been updated by the comprehensive API test script.', '2025-06-24 13:22:15', '2025-06-23 06:22:16', '2025-06-23 06:22:28', NULL),
(9, 7, 'UPDATED - Comprehensive Test Event 1750685935', 'This event has been updated by the comprehensive API test script.', '2025-06-24 13:38:41', '2025-06-23 06:38:42', '2025-06-23 06:38:55', NULL),
(10, 29, 'đasad', 'safff', '2025-06-30 03:05:00', '2025-06-27 13:02:35', '2025-08-20 00:55:10', NULL),
(11, 29, 'dsgsdgsdgs', 'sdggggggggggg12r3424', '2025-06-28 03:06:00', '2025-06-27 13:03:20', '2025-08-16 20:36:36', NULL),
(12, 39, 'manhcho2', 'manhcho2', '2025-07-01 07:02:00', '2025-07-03 02:10:45', '2025-07-03 02:10:45', NULL),
(13, 43, 'Review dự án', 'Thầy Kha', '2025-07-06 09:00:00', '2025-07-05 19:31:55', '2025-07-05 19:31:55', NULL),
(14, 29, 'Đi họp DATN', 'abc', '2025-08-05 08:00:00', '2025-08-03 21:17:56', '2025-08-03 21:37:21', NULL),
(15, 29, 'Đi họp DATN 2', 'bdfb', '2025-08-04 14:44:00', '2025-08-03 21:45:20', '2025-08-03 21:45:20', NULL),
(16, 29, 'Đi họp DATN 3', '432423', '2025-08-05 16:49:00', '2025-08-03 21:49:28', '2025-08-03 21:50:42', NULL),
(18, 46, 'Hop 5h', 'Tai zonesix', '2025-08-04 12:50:00', '2025-08-03 22:49:36', '2025-08-03 22:49:36', NULL),
(19, 38, 'conmevy', 'ehehehhee', '2025-08-14 17:11:00', '2025-08-12 03:11:41', '2025-08-12 03:11:41', NULL),
(20, 38, 'ádg', 'jkfkadf', '2025-08-13 17:19:00', '2025-08-12 03:17:55', '2025-08-12 03:17:55', NULL),
(21, 39, 'ggg', 'ggg', '2025-08-13 15:46:00', '2025-08-13 01:23:09', '2025-08-13 01:46:17', NULL),
(22, 39, 'hh', 'hhh', '2025-08-13 15:47:00', '2025-08-13 01:46:58', '2025-08-13 01:46:58', NULL),
(23, 39, 'gggg', 'ggggg', '2025-08-14 00:29:00', '2025-08-13 10:28:24', '2025-08-13 10:28:24', NULL),
(24, 40, '55', '555', '2025-08-19 13:54:00', '2025-08-19 06:50:37', '2025-08-19 06:50:37', NULL),
(25, 40, 'LLLL', 'KKKKKK', '2025-08-20 14:13:00', '2025-08-20 00:12:39', '2025-08-20 00:12:39', NULL),
(26, 40, '111', '111', '2025-08-21 14:16:00', '2025-08-20 00:13:32', '2025-08-20 00:13:32', NULL),
(28, 29, '1221323', '12423423', '2025-08-21 06:35:00', '2025-08-21 04:32:12', '2025-08-21 05:03:10', '2025-08-21 05:03:10'),
(29, 39, 'sss', 'sss', '2025-08-23 14:00:00', '2025-08-22 01:00:00', '2025-08-22 01:00:00', NULL),
(30, 39, 'fffff', 'fff', '2025-08-22 18:23:00', '2025-08-22 04:22:27', '2025-08-22 04:22:27', NULL),
(31, 39, 'xxxx', 'xx', '2025-08-23 03:27:00', '2025-08-22 13:27:23', '2025-08-22 13:27:23', NULL),
(32, 29, '123', '123', '2025-08-25 19:15:00', '2025-08-25 01:16:02', '2025-08-25 01:16:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `FileGoalLinks`
--

CREATE TABLE `FileGoalLinks` (
  `link_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `FileNoteLinks`
--

CREATE TABLE `FileNoteLinks` (
  `link_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Files`
--

CREATE TABLE `Files` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(1000) NOT NULL,
  `file_type` varchar(200) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Files`
--

INSERT INTO `Files` (`file_id`, `user_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 39, 'Hhhhhhhhhhhhhhhhhh.docx', 'files/Hhhhhhhhhhhhhhhhhh_1755946349_68a99d6db97e9.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 11345, '2025-08-23 03:52:30', '2025-08-23 03:52:30', '2025-08-23 05:03:30', '2025-08-23 05:03:30'),
(2, 39, 'Hhhhhhhhhhhhhhhhhh.docx', 'files/Hhhhhhhhhhhhhhhhhh_1755950632_68a9ae28c3a58.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 11345, '2025-08-23 05:03:53', '2025-08-23 05:03:53', '2025-08-23 05:03:53', NULL),
(3, 39, 'z6923989033243_6fd13cd6d287b79a81a794eb4fc90427.jpg', 'uploads/z6923989033243_6fd13cd6d287b79a81a794eb4fc90427_1756022103_68aac55731033.jpg', 'image/jpeg', 327716, '2025-08-24 00:55:05', '2025-08-24 00:55:05', '2025-08-24 00:55:05', NULL),
(4, 29, 'z4774403311500_e109a5d6a7bd9267a1e8e1babc7cdcc1.jpg', 'uploads/z4774403311500_e109a5d6a7bd9267a1e8e1babc7cdcc1_1756022230_68aac5d6889c6.jpg', 'image/jpeg', 219817, '2025-08-24 00:57:10', '2025-08-24 00:57:10', '2025-08-24 00:57:10', NULL),
(5, 29, 'package-lock.json', 'uploads/package-lock_1756060774_68ab5c66a6bc2.json', 'application/json', 155183, '2025-08-24 11:39:35', '2025-08-24 11:39:35', '2025-08-24 11:39:35', NULL),
(6, 38, 'BÁO CÁO TÀI CHÍNH CÔNG TY TNHH KIẾN TRÚC.docx', 'uploads/BÁO CÁO TÀI CHÍNH CÔNG TY TNHH KIẾN TRÚC_1756109812_68ac1bf456ac0.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 23342, '2025-08-25 01:16:54', '2025-08-25 01:16:54', '2025-08-25 01:16:54', NULL),
(7, 46, '10.05-BMFPLHDCVFE-Bao cao thuc tap co xac nhan cua doanh nghiep.DOC', 'uploads/10.05-BMFPLHDCVFE-Bao cao thuc tap co xac nhan cua doanh nghiep_1756113674_68ac2b0a8d73b.DOC', 'application/msword', 419328, '2025-08-25 02:21:14', '2025-08-25 02:21:14', '2025-08-25 02:21:14', NULL),
(8, 29, 'HƯỚNG DẪN NỘP BÁO CÁO DỰ ÁN TỐT NGHIỆP.docx', 'uploads/HƯỚNG DẪN NỘP BÁO CÁO DỰ ÁN TỐT NGHIỆP_1756125208_68ac5818a9be6.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 18432, '2025-08-25 05:33:29', '2025-08-25 05:33:29', '2025-08-25 05:33:29', NULL),
(9, 29, 'test.docx', 'uploads/test_1756128587_68ac654b0e5bf.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 13281, '2025-08-25 06:29:47', '2025-08-25 06:29:47', '2025-08-25 06:29:47', NULL),
(10, 7, '10.05-BMFPLHDCVFE-Bao cao thuc tap co xac nhan cua doanh nghiep.DOC', 'uploads/10.05-BMFPLHDCVFE-Bao cao thuc tap co xac nhan cua doanh nghiep_1756191273_68ad5a29ec0f5.DOC', 'application/msword', 419328, '2025-08-25 23:54:34', '2025-08-25 23:54:34', '2025-08-25 23:54:34', NULL),
(11, 29, 'Untitled design.png', 'uploads/Untitled design_1756201915_68ad83bb0ed6b.png', 'image/png', 93781, '2025-08-26 02:51:55', '2025-08-26 02:51:55', '2025-08-26 02:51:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Friendships`
--

CREATE TABLE `Friendships` (
  `friendship_id` int(11) NOT NULL,
  `user_id_1` int(11) NOT NULL,
  `user_id_2` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Friendships`
--

INSERT INTO `Friendships` (`friendship_id`, `user_id_1`, `user_id_2`, `status`, `created_at`, `updated_at`) VALUES
(5, 46, 7, 'accepted', '2025-08-03 22:50:30', '2025-08-03 22:52:20'),
(6, 38, 40, 'accepted', '2025-08-05 23:19:31', '2025-08-10 02:08:09'),
(9, 38, 39, 'accepted', '2025-08-08 02:02:48', '2025-08-08 02:06:24'),
(14, 7, 38, 'accepted', '2025-08-18 03:45:32', '2025-08-18 03:46:52'),
(17, 40, 39, 'accepted', '2025-08-19 23:59:21', '2025-08-20 00:33:02'),
(20, 39, 7, 'accepted', '2025-08-20 00:38:54', '2025-08-21 05:07:04'),
(21, 40, 7, 'accepted', '2025-08-20 01:46:23', '2025-08-21 05:07:06'),
(22, 46, 39, 'accepted', '2025-08-20 04:24:15', '2025-08-21 04:27:00'),
(23, 29, 7, 'accepted', '2025-08-21 05:10:15', '2025-08-21 05:10:24'),
(24, 29, 39, 'accepted', '2025-08-22 02:12:06', '2025-08-22 03:59:44'),
(25, 38, 29, 'accepted', '2025-08-22 03:49:06', '2025-08-22 03:51:19'),
(31, 46, 29, 'accepted', '2025-08-25 02:57:59', '2025-08-25 02:58:44'),
(32, 46, 40, 'pending', '2025-08-25 02:58:04', '2025-08-25 02:58:04'),
(33, 29, 10, 'accepted', '2025-08-25 03:49:50', '2025-08-25 03:50:21'),
(34, 10, 39, 'pending', '2025-08-25 04:17:23', '2025-08-25 04:17:23');

-- --------------------------------------------------------

--
-- Table structure for table `GoalCollaboration`
--

CREATE TABLE `GoalCollaboration` (
  `collab_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','member') DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `GoalCollaboration`
--

INSERT INTO `GoalCollaboration` (`collab_id`, `goal_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 27, 29, 'member', '2025-07-05 08:36:18'),
(2, 30, 39, 'owner', '2025-07-31 08:35:16'),
(4, 31, 39, 'owner', '2025-07-31 14:54:39'),
(9, 34, 46, 'owner', '2025-08-04 06:08:22'),
(10, 35, 46, 'owner', '2025-08-04 06:10:57'),
(17, 40, 10, 'member', '2025-08-18 07:27:30'),
(18, 40, 39, 'member', '2025-08-18 07:28:27'),
(21, 31, 40, 'member', '2025-08-20 07:36:01'),
(22, 30, 29, 'member', '2025-08-20 08:15:16'),
(23, 31, 29, 'member', '2025-08-21 18:26:24'),
(24, 31, 7, 'member', '2025-08-22 08:03:05'),
(25, 31, 46, 'member', '2025-08-22 19:48:52'),
(26, 45, 39, 'owner', '2025-08-22 20:42:06'),
(27, 38, 7, 'member', '2025-08-24 08:32:14'),
(28, 46, 46, 'owner', '2025-08-25 09:39:53'),
(29, 42, 29, 'member', '2025-08-25 10:51:21'),
(31, 39, 10, 'member', '2025-08-25 11:42:53'),
(32, 39, 7, 'member', '2025-08-26 10:12:44');

-- --------------------------------------------------------

--
-- Table structure for table `GoalMembers`
--

CREATE TABLE `GoalMembers` (
  `member_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `GoalProgress`
--

CREATE TABLE `GoalProgress` (
  `progress_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `progress_value` float NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `GoalProgress`
--

INSERT INTO `GoalProgress` (`progress_id`, `goal_id`, `progress_value`, `updated_at`) VALUES
(1, 13, 0, '2025-06-22 20:45:49'),
(2, 14, 0, '2025-06-22 20:46:17'),
(3, 15, 0, '2025-06-22 20:57:00'),
(4, 16, 0, '2025-06-23 13:22:15'),
(5, 17, 0, '2025-06-23 13:38:41'),
(8, 20, 0, '2025-06-27 17:33:09'),
(9, 20, 0, '2025-06-27 18:09:02'),
(10, 20, 0, '2025-06-27 18:09:03'),
(11, 20, 0, '2025-06-27 18:09:05'),
(19, 27, 0, '2025-07-03 09:06:54'),
(20, 28, 0, '2025-07-06 02:30:29'),
(22, 30, 0, '2025-07-31 08:35:16'),
(23, 30, 100, '2025-07-31 14:51:49'),
(24, 30, 100, '2025-07-31 14:51:49'),
(25, 31, 0, '2025-07-31 14:54:39'),
(26, 31, 100, '2025-07-31 14:55:27'),
(27, 31, 100, '2025-07-31 14:55:27'),
(28, 31, 0, '2025-07-31 14:55:28'),
(29, 31, 100, '2025-07-31 14:55:29'),
(30, 31, 100, '2025-07-31 14:55:29'),
(31, 31, 0, '2025-07-31 14:55:32'),
(32, 31, 0, '2025-07-31 14:55:32'),
(33, 30, 0, '2025-07-31 14:55:35'),
(34, 31, 100, '2025-07-31 14:55:35'),
(35, 30, 100, '2025-07-31 15:40:43'),
(36, 30, 100, '2025-07-31 15:40:43'),
(43, 34, 0, '2025-08-04 06:08:22'),
(44, 35, 0, '2025-08-04 06:10:57'),
(45, 35, 50, '2025-08-04 06:11:01'),
(46, 35, 0, '2025-08-04 06:11:27'),
(56, 20, 100, '2025-08-07 05:50:32'),
(57, 20, 0, '2025-08-07 05:55:23'),
(58, 20, 100, '2025-08-07 05:55:29'),
(65, 20, 0, '2025-08-17 04:12:03'),
(66, 20, 0, '2025-08-17 04:12:35'),
(67, 20, 0, '2025-08-17 04:13:11'),
(68, 20, 100, '2025-08-17 04:13:47'),
(69, 20, 100, '2025-08-17 04:13:57'),
(70, 20, 100, '2025-08-17 04:14:52'),
(71, 37, 0, '2025-08-18 06:54:20'),
(72, 38, 0, '2025-08-18 07:21:16'),
(73, 39, 0, '2025-08-18 07:24:05'),
(74, 40, 0, '2025-08-18 07:24:52'),
(75, 41, 0, '2025-08-18 08:32:59'),
(76, 42, 0, '2025-08-18 09:52:23'),
(77, 42, 0, '2025-08-18 11:11:54'),
(78, 42, 100, '2025-08-18 11:12:05'),
(79, 42, 0, '2025-08-18 11:12:30'),
(80, 42, 100, '2025-08-18 11:13:01'),
(83, 41, 0, '2025-08-21 11:14:53'),
(84, 27, 100, '2025-08-22 07:54:37'),
(85, 27, 100, '2025-08-22 07:57:34'),
(86, 45, 0, '2025-08-22 20:42:06'),
(87, 38, 100, '2025-08-24 08:28:50'),
(88, 46, 0, '2025-08-25 09:39:53'),
(89, 39, 50, '2025-08-25 11:59:17'),
(90, 20, 100, '2025-08-25 12:01:21');

-- --------------------------------------------------------

--
-- Table structure for table `Goals`
--

CREATE TABLE `Goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('new','in_progress','completed','cancelled') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Goals`
--

INSERT INTO `Goals` (`goal_id`, `user_id`, `title`, `description`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 7, 'Test Goal 1750589553', 'This is a test goal', '2025-06-22', '2025-07-22', 'new', '2025-06-22 10:52:33', '2025-06-22 10:52:33', NULL),
(3, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 10:59:04', '2025-06-22 10:59:04', NULL),
(4, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:00:02', '2025-06-22 11:00:02', NULL),
(5, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:03:43', '2025-06-22 11:03:43', NULL),
(6, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:07:41', '2025-06-22 11:07:41', NULL),
(7, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:11:33', '2025-06-22 11:11:33', NULL),
(8, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:29:10', '2025-06-22 11:29:10', NULL),
(9, 7, 'Test Goal', 'Test description', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:33:02', '2025-06-22 11:33:02', NULL),
(10, 7, 'Test Goal 2025-06-22 11:39:27', 'Test description for API testing', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:39:27', '2025-06-22 11:39:27', NULL),
(11, 7, 'Test Goal 1750592698', 'This is a test goal for API testing', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:44:58', '2025-06-22 11:44:58', NULL),
(12, 7, 'API Test Goal 1750592986', 'Goal created for comprehensive API testing', '2025-06-22', '2025-07-22', 'new', '2025-06-22 11:49:46', '2025-06-22 11:49:46', NULL),
(13, 7, 'UPDATED - Comprehensive Test Goal 1750625156', 'This goal title and description have been updated by the comprehensive API test script.', '2025-06-22', '2025-07-22', 'new', '2025-06-22 13:45:49', '2025-06-22 13:45:56', NULL),
(14, 7, 'UPDATED - Comprehensive Test Goal 1750625184', 'This goal title and description have been updated by the comprehensive API test script.', '2025-06-22', '2025-07-22', 'new', '2025-06-22 13:46:17', '2025-06-22 13:46:24', NULL),
(15, 7, 'UPDATED - Comprehensive Test Goal 1750625826', 'This goal title and description have been updated by the comprehensive API test script.', '2025-06-22', '2025-07-22', 'new', '2025-06-22 13:56:59', '2025-06-22 13:57:06', NULL),
(16, 7, 'UPDATED - Comprehensive Test Goal 1750684946', 'This goal title and description have been updated by the comprehensive API test script.', '2025-06-23', '2025-07-23', 'new', '2025-06-23 06:22:15', '2025-06-23 06:22:27', NULL),
(17, 7, 'UPDATED - Comprehensive Test Goal 1750685934', 'This goal title and description have been updated by the comprehensive API test script.', '2025-06-23', '2025-07-23', 'new', '2025-06-23 06:38:40', '2025-06-23 06:38:54', NULL),
(20, 29, 'Learn Advanced SQLfds', 'dsgg', '2025-06-05', '2025-06-12', 'completed', '2025-06-27 10:33:05', '2025-08-25 05:01:19', '2025-08-25 05:01:19'),
(27, 39, 'manhcho3', 'manhcho3', '2025-07-01', '2025-07-03', 'completed', '2025-07-03 02:06:54', '2025-08-22 00:57:33', NULL),
(28, 43, 'Học Tiếng Anh', 'chỉ tiêu 6. IELTS', '2025-08-02', '2025-10-02', 'new', '2025-07-05 19:30:29', '2025-07-05 19:30:29', NULL),
(30, 39, 'manhcho33', 'manhcho33', '2025-08-01', '2025-08-03', 'completed', '2025-07-31 01:35:16', '2025-07-31 08:40:43', NULL),
(31, 39, 'manhcho333', 'manhcho333hhhhh', '2025-08-02', '2025-08-05', 'cancelled', '2025-07-31 07:54:39', '2025-08-22 01:04:37', NULL),
(34, 46, 'dự án', 'mục tiêu chi tiết\ncập nhật trang thái\nthông báo (all)\ntìm kiếm sổ dữ liệu tự động\nchat trong bạn bè\nlưu trữ file storage\n-admin: ban account, ban chức năng(phân quyền)\nnâng premium cấp khả năng lưu trữ 5,10gb-> xác định đúng dung lượng lưu trữ.\nkhi tạo tài khoản xác thực xong sẽ tự sinh ra 1 folder thì sẽ chứa tất cả dữ liệu của user', '2025-08-04', '2025-08-20', 'new', '2025-08-03 23:08:22', '2025-08-03 23:08:22', NULL),
(35, 46, 'df', 'gdfg', '2025-08-04', '2025-08-22', 'new', '2025-08-03 23:10:57', '2025-08-03 23:11:27', NULL),
(37, 29, 'AAA', 'manhcho123', '2025-08-18', '2025-08-20', 'new', '2025-08-17 23:54:13', '2025-08-19 23:52:30', '2025-08-19 23:52:30'),
(38, 29, 'AAA435', 'gfdddddddddddddd', '2025-08-18', '2025-08-20', 'completed', '2025-08-18 00:21:08', '2025-08-24 01:28:40', NULL),
(39, 29, 'geiguirig12', 'dmmmmmmmmmmmmmm', '2025-08-21', '2025-08-28', 'new', '2025-08-18 00:23:58', '2025-08-25 05:00:03', NULL),
(40, 29, 'jkk.ugkfgmfe3', 'g.ug.ùgmfgfgm', '2025-08-20', '2025-08-28', 'new', '2025-08-18 00:24:45', '2025-08-19 23:52:47', '2025-08-19 23:52:47'),
(41, 29, 'jkk.ugk123123', 'Chó Đuuuuuuuuuuuuuuu', '2025-08-19', '2025-08-27', 'new', '2025-08-18 01:32:52', '2025-08-21 04:14:51', '2025-08-21 04:14:51'),
(42, 10, 'Learn Advanced SQL', 'egerherh', '2025-08-18', '2025-08-21', 'completed', '2025-08-18 02:52:16', '2025-08-18 04:12:54', NULL),
(45, 39, 'kkk', 'ffffff', '2025-08-23', '2025-08-30', 'new', '2025-08-22 13:42:05', '2025-08-22 13:42:05', NULL),
(46, 46, '123123', '123123', '2025-08-25', '2025-08-27', 'new', '2025-08-25 02:39:53', '2025-08-25 02:59:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `GoalShares`
--

CREATE TABLE `GoalShares` (
  `share_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `share_type` enum('private','public','friends','collaboration') DEFAULT 'private',
  `shared_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `GoalShares`
--

INSERT INTO `GoalShares` (`share_id`, `goal_id`, `share_type`, `shared_at`) VALUES
(1, 13, 'private', '2025-06-22 20:45:49'),
(2, 14, 'private', '2025-06-22 20:46:17'),
(3, 15, 'private', '2025-06-22 20:57:00'),
(4, 16, 'private', '2025-06-23 13:22:15'),
(5, 17, 'private', '2025-06-23 13:38:41'),
(7, 27, 'public', '2025-07-05 07:19:20'),
(9, 20, 'private', '2025-07-05 08:05:03'),
(11, 30, 'public', '2025-07-31 08:35:16'),
(13, 31, 'friends', '2025-07-31 14:54:39'),
(16, 34, 'public', '2025-08-04 06:08:22'),
(17, 35, 'private', '2025-08-04 06:10:57'),
(19, 39, 'public', '2025-08-18 07:24:05'),
(20, 40, 'private', '2025-08-18 07:24:52'),
(21, 41, 'friends', '2025-08-18 08:32:59'),
(22, 42, 'public', '2025-08-18 09:52:23'),
(25, 45, 'private', '2025-08-22 20:42:06'),
(26, 46, 'public', '2025-08-25 09:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 38, 39, 'em yêu ơi', NULL, '2025-08-08 04:10:29', '2025-08-08 04:10:29'),
(2, 38, 39, 'alo em yêu', NULL, '2025-08-08 04:11:27', '2025-08-08 04:11:27'),
(3, 38, 39, 'hello', NULL, '2025-08-08 22:08:45', '2025-08-08 22:08:45'),
(4, 38, 39, 'hi', NULL, '2025-08-08 22:09:55', '2025-08-08 22:09:55'),
(5, 38, 39, 'alo', NULL, '2025-08-08 22:10:32', '2025-08-08 22:10:32'),
(6, 38, 39, 'hello', NULL, '2025-08-09 02:26:02', '2025-08-09 02:26:02'),
(7, 38, 39, 'heleeee', NULL, '2025-08-09 23:57:59', '2025-08-09 23:57:59'),
(8, 38, 39, 'hello', NULL, '2025-08-10 02:04:25', '2025-08-10 02:04:25'),
(9, 40, 38, 'hello', '2025-08-25 04:03:32', '2025-08-10 02:16:05', '2025-08-25 04:03:32'),
(10, 40, 38, 'how old are you', '2025-08-25 04:03:32', '2025-08-10 02:17:01', '2025-08-25 04:03:32'),
(11, 38, 40, 'ok', NULL, '2025-08-10 02:22:53', '2025-08-10 02:22:53'),
(12, 38, 40, 'xin chào', NULL, '2025-08-12 03:05:08', '2025-08-12 03:05:08'),
(13, 39, 38, 'kkkk', NULL, '2025-08-12 22:53:44', '2025-08-12 22:53:44'),
(14, 39, 38, 'kkkkkk', NULL, '2025-08-12 22:54:11', '2025-08-12 22:54:11'),
(15, 40, 38, 'alo', '2025-08-25 04:03:32', '2025-08-12 23:07:41', '2025-08-25 04:03:32'),
(16, 40, 38, 'alo', '2025-08-25 04:03:32', '2025-08-12 23:20:04', '2025-08-25 04:03:32'),
(17, 40, 38, 'hi', '2025-08-25 04:03:32', '2025-08-12 23:28:53', '2025-08-25 04:03:32'),
(18, 40, 38, 'helo', '2025-08-25 04:03:32', '2025-08-12 23:37:26', '2025-08-25 04:03:32'),
(19, 40, 38, 'alo', '2025-08-25 04:03:32', '2025-08-12 23:38:09', '2025-08-25 04:03:32'),
(20, 40, 38, 'helo', '2025-08-25 04:03:32', '2025-08-12 23:44:37', '2025-08-25 04:03:32'),
(21, 40, 38, 'da', '2025-08-25 04:03:32', '2025-08-12 23:50:36', '2025-08-25 04:03:32'),
(22, 40, 38, 'kakaaa', '2025-08-25 04:03:32', '2025-08-12 23:51:04', '2025-08-25 04:03:32'),
(23, 39, 29, 'hè lô', NULL, '2025-08-12 23:55:09', '2025-08-12 23:55:09'),
(24, 39, 38, 'hê', NULL, '2025-08-12 23:55:40', '2025-08-12 23:55:40'),
(25, 39, 38, 'Mồm như con Dộp', NULL, '2025-08-12 23:56:05', '2025-08-12 23:56:05'),
(26, 38, 39, 'kk', NULL, '2025-08-13 00:00:54', '2025-08-13 00:00:54'),
(27, 38, 40, 'hhh', NULL, '2025-08-13 00:02:13', '2025-08-13 00:02:13'),
(28, 39, 38, 'xxx', NULL, '2025-08-13 00:37:49', '2025-08-13 00:37:49'),
(29, 39, 38, 'kkk', NULL, '2025-08-13 01:19:53', '2025-08-13 01:19:53'),
(30, 38, 39, 'lô em yêu', NULL, '2025-08-15 04:21:42', '2025-08-15 04:21:42'),
(31, 38, 39, 'gửi anh cái ảnh thanh toán nào', NULL, '2025-08-15 04:22:30', '2025-08-15 04:22:30'),
(32, 39, 38, 'ê', NULL, '2025-08-15 04:46:11', '2025-08-15 04:46:11'),
(33, 39, 38, 'ê', NULL, '2025-08-15 04:46:11', '2025-08-15 04:46:11'),
(34, 40, 38, 'LO ANH EU', '2025-08-25 04:03:32', '2025-08-15 05:08:19', '2025-08-25 04:03:32'),
(35, 40, 38, 'cc nè ', '2025-08-25 04:03:32', '2025-08-18 00:14:12', '2025-08-25 04:03:32'),
(36, 46, 7, 'ok', NULL, '2025-08-18 01:48:10', '2025-08-18 01:48:10'),
(37, 46, 7, 'adada', NULL, '2025-08-18 01:48:13', '2025-08-18 01:48:13'),
(38, 46, 7, 'aaa', NULL, '2025-08-18 01:48:21', '2025-08-18 01:48:21'),
(39, 46, 38, 'aloalo', NULL, '2025-08-18 01:57:33', '2025-08-18 01:57:33'),
(40, 38, 46, 'cc', NULL, '2025-08-18 01:59:34', '2025-08-18 01:59:34'),
(41, 46, 38, 'c', NULL, '2025-08-18 02:00:24', '2025-08-18 02:00:24'),
(42, 46, 38, 'c', NULL, '2025-08-18 02:24:11', '2025-08-18 02:24:11'),
(43, 38, 46, 'dđ', NULL, '2025-08-18 02:24:12', '2025-08-18 02:24:12'),
(44, 46, 38, 'cc', NULL, '2025-08-18 02:24:13', '2025-08-18 02:24:13'),
(45, 46, 38, '1233', NULL, '2025-08-18 02:24:17', '2025-08-18 02:24:17'),
(46, 46, 38, '1234', NULL, '2025-08-18 02:24:21', '2025-08-18 02:24:21'),
(47, 38, 46, 'lll', NULL, '2025-08-18 03:13:52', '2025-08-18 03:13:52'),
(48, 38, 46, 'nooo', NULL, '2025-08-18 03:16:39', '2025-08-18 03:16:39'),
(49, 38, 46, 'kkk', NULL, '2025-08-18 03:30:34', '2025-08-18 03:30:34'),
(50, 46, 38, 'chó', NULL, '2025-08-18 03:41:29', '2025-08-18 03:41:29'),
(51, 7, 38, 'ngu', '2025-08-25 06:15:33', '2025-08-18 03:42:51', '2025-08-25 06:15:33'),
(52, 7, 38, 'wao', '2025-08-25 06:15:33', '2025-08-18 03:47:41', '2025-08-25 06:15:33'),
(53, 7, 38, 'waoo', '2025-08-25 06:15:33', '2025-08-18 03:47:44', '2025-08-25 06:15:33'),
(54, 38, 7, 'ok', NULL, '2025-08-18 03:48:42', '2025-08-18 03:48:42'),
(55, 38, 7, 'hêloo', NULL, '2025-08-18 03:58:12', '2025-08-18 03:58:12'),
(56, 46, 38, 'ccc', NULL, '2025-08-18 04:14:19', '2025-08-18 04:14:19'),
(57, 41, 38, 'alo 1234', NULL, '2025-08-18 04:16:30', '2025-08-18 04:16:30'),
(58, 40, 39, 'llll', NULL, '2025-08-20 02:47:28', '2025-08-20 02:47:28'),
(59, 40, 39, 'kkkkk', NULL, '2025-08-20 02:52:19', '2025-08-20 02:52:19'),
(60, 40, 39, 'kkkk', NULL, '2025-08-20 02:52:36', '2025-08-20 02:52:36'),
(61, 40, 39, 'hhh', NULL, '2025-08-20 02:53:20', '2025-08-20 02:53:20'),
(62, 40, 39, '111', NULL, '2025-08-20 02:53:37', '2025-08-20 02:53:37'),
(63, 40, 39, 'lll', NULL, '2025-08-20 02:53:37', '2025-08-20 02:53:37'),
(64, 40, 39, '11', NULL, '2025-08-20 02:53:57', '2025-08-20 02:53:57'),
(65, 40, 39, '11', NULL, '2025-08-20 02:54:01', '2025-08-20 02:54:01'),
(66, 40, 39, '1', NULL, '2025-08-20 02:54:08', '2025-08-20 02:54:08'),
(67, 40, 38, 'lll', '2025-08-25 04:03:32', '2025-08-20 03:06:53', '2025-08-25 04:03:32'),
(68, 40, 38, ';;', '2025-08-25 04:03:32', '2025-08-20 03:08:42', '2025-08-25 04:03:32'),
(69, 40, 38, 'll', '2025-08-25 04:03:32', '2025-08-20 03:18:59', '2025-08-25 04:03:32'),
(70, 40, 38, 'kk', '2025-08-25 04:03:32', '2025-08-20 03:21:44', '2025-08-25 04:03:32'),
(71, 40, 38, 'kkk', '2025-08-25 04:03:32', '2025-08-20 03:21:44', '2025-08-25 04:03:32'),
(72, 40, 38, 'kkk', '2025-08-25 04:03:32', '2025-08-20 03:21:44', '2025-08-25 04:03:32'),
(73, 39, 40, 'kkkk', NULL, '2025-08-21 04:27:45', '2025-08-21 04:27:45'),
(74, 39, 29, 'kệ mẹ tao', NULL, '2025-08-21 06:21:14', '2025-08-21 06:21:14'),
(75, 29, 39, 'Ôk em kệ ', NULL, '2025-08-21 06:22:10', '2025-08-21 06:22:10'),
(76, 40, 7, 'alo', NULL, '2025-08-21 09:59:40', '2025-08-21 09:59:40'),
(77, 40, 39, 'cười gì em', NULL, '2025-08-21 10:00:18', '2025-08-21 10:00:18'),
(78, 39, 29, 'ngủ chưa', NULL, '2025-08-21 12:32:03', '2025-08-21 12:32:03'),
(79, 39, 29, 'cc', NULL, '2025-08-22 00:19:27', '2025-08-22 00:19:27'),
(80, 39, 40, 'ê', NULL, '2025-08-22 01:12:21', '2025-08-22 01:12:21'),
(81, 39, 40, 'm làm cái minimize cho tin nhắn đi', NULL, '2025-08-22 01:12:50', '2025-08-22 01:12:50'),
(82, 38, 39, 'sao', NULL, '2025-08-22 01:17:56', '2025-08-22 01:17:56'),
(83, 29, 39, 'Cc t dậy rồi', NULL, '2025-08-22 01:23:47', '2025-08-22 01:23:47'),
(84, 39, 29, 'xóa ta khổi bạn bè rồi gửi lời mời lại cho ta', NULL, '2025-08-22 02:05:42', '2025-08-22 02:05:42'),
(85, 29, 38, 'hihi', '2025-08-25 03:57:24', '2025-08-22 03:53:06', '2025-08-25 03:57:24'),
(86, 38, 29, 'cc', NULL, '2025-08-22 03:55:24', '2025-08-22 03:55:24'),
(87, 29, 39, 'Sốt cà chua anh miền vy =))', NULL, '2025-08-22 04:12:04', '2025-08-22 04:12:04'),
(90, 38, 39, 'DDD', NULL, '2025-08-25 02:08:15', '2025-08-25 02:08:15'),
(91, 46, 39, 'ccc', NULL, '2025-08-25 02:56:22', '2025-08-25 02:56:22'),
(92, 46, 39, 'ccc', NULL, '2025-08-25 02:56:23', '2025-08-25 02:56:23'),
(93, 46, 39, 'cc', NULL, '2025-08-25 02:56:24', '2025-08-25 02:56:24'),
(94, 46, 39, 'cc', NULL, '2025-08-25 02:56:24', '2025-08-25 02:56:24'),
(95, 46, 39, 'c', NULL, '2025-08-25 02:56:25', '2025-08-25 02:56:25'),
(96, 46, 39, 'c', NULL, '2025-08-25 02:56:25', '2025-08-25 02:56:25'),
(97, 46, 39, 'c', NULL, '2025-08-25 02:56:25', '2025-08-25 02:56:25'),
(98, 46, 39, 'c', NULL, '2025-08-25 02:56:25', '2025-08-25 02:56:25'),
(99, 46, 39, 'c', NULL, '2025-08-25 02:56:25', '2025-08-25 02:56:25'),
(100, 46, 39, 'c', NULL, '2025-08-25 02:56:26', '2025-08-25 02:56:26'),
(101, 46, 39, 'c', NULL, '2025-08-25 02:56:26', '2025-08-25 02:56:26'),
(102, 46, 39, 'c', NULL, '2025-08-25 02:56:26', '2025-08-25 02:56:26'),
(103, 46, 39, 'c', NULL, '2025-08-25 02:56:26', '2025-08-25 02:56:26'),
(104, 46, 39, 'c', NULL, '2025-08-25 02:56:26', '2025-08-25 02:56:26'),
(105, 46, 39, 'c', NULL, '2025-08-25 02:56:27', '2025-08-25 02:56:27'),
(106, 46, 39, 'c', NULL, '2025-08-25 02:56:27', '2025-08-25 02:56:27'),
(107, 46, 39, 'c', NULL, '2025-08-25 02:56:27', '2025-08-25 02:56:27'),
(108, 46, 39, 'c', NULL, '2025-08-25 02:56:27', '2025-08-25 02:56:27'),
(109, 46, 39, 'c', NULL, '2025-08-25 02:56:27', '2025-08-25 02:56:27'),
(110, 46, 39, 'ccc', NULL, '2025-08-25 02:56:34', '2025-08-25 02:56:34'),
(111, 46, 39, 'c', NULL, '2025-08-25 02:56:34', '2025-08-25 02:56:34'),
(112, 46, 39, 'c', NULL, '2025-08-25 02:56:34', '2025-08-25 02:56:34'),
(113, 46, 39, 'c', NULL, '2025-08-25 02:56:34', '2025-08-25 02:56:34'),
(114, 46, 39, 'c', NULL, '2025-08-25 02:56:35', '2025-08-25 02:56:35'),
(115, 29, 38, 'alo', '2025-08-25 03:57:24', '2025-08-25 03:55:19', '2025-08-25 03:57:24'),
(116, 29, 38, 'ccccccccc', '2025-08-25 04:28:25', '2025-08-25 04:21:41', '2025-08-25 04:28:25'),
(117, 38, 29, 'lô', NULL, '2025-08-25 04:21:46', '2025-08-25 04:21:46'),
(118, 29, 38, 'má mày', '2025-08-25 04:41:40', '2025-08-25 04:40:36', '2025-08-25 04:41:40'),
(119, 29, 38, 'aloalo', '2025-08-25 05:08:42', '2025-08-25 05:07:57', '2025-08-25 05:08:42'),
(120, 7, 38, 'kee', '2025-08-25 06:15:33', '2025-08-25 05:20:51', '2025-08-25 06:15:33'),
(121, 38, 7, 'keeet', NULL, '2025-08-25 06:16:45', '2025-08-25 06:16:45'),
(122, 7, 38, 'qqq', '2025-08-25 06:22:24', '2025-08-25 06:19:20', '2025-08-25 06:22:24'),
(123, 38, 7, 'gì m', NULL, '2025-08-25 06:20:54', '2025-08-25 06:20:54');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_10_142621_create_personal_access_tokens_table', 1),
(5, '2025_08_08_093129_create_messages_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Milestones`
--

CREATE TABLE `Milestones` (
  `milestone_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `deadline` date DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Milestones`
--

INSERT INTO `Milestones` (`milestone_id`, `goal_id`, `title`, `deadline`, `is_completed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 27, 'KKKK', '2025-07-01', 1, '2025-07-05 00:19:20', '2025-08-22 00:57:33', NULL),
(18, 20, 'vy cho de', '2025-07-10', 1, '2025-07-05 01:05:27', '2025-08-25 05:01:19', '2025-08-25 05:01:19'),
(37, 30, 'ggg', '2025-08-01', 1, '2025-07-31 01:35:16', '2025-07-31 08:40:42', NULL),
(38, 31, 'manhcho333', '2025-08-02', 1, '2025-07-31 07:54:39', '2025-07-31 07:55:35', NULL),
(41, 35, 'd', '2025-08-12', 0, '2025-08-03 23:10:57', '2025-08-03 23:11:27', NULL),
(42, 35, 'f', '2025-08-20', 0, '2025-08-03 23:10:57', '2025-08-03 23:10:57', NULL),
(43, 42, '2332', '2025-08-18', 1, '2025-08-18 03:45:35', '2025-08-18 04:12:54', NULL),
(44, 41, '123', '2025-08-27', 0, '2025-08-21 04:14:17', '2025-08-21 04:14:51', '2025-08-21 04:14:51'),
(45, 38, '123', '2025-08-20', 1, '2025-08-24 01:27:49', '2025-08-24 01:28:40', NULL),
(46, 39, 'tutu', '2025-08-27', 1, '2025-08-24 05:21:11', '2025-08-25 04:59:15', NULL),
(47, 39, '123123', '2025-08-23', 0, '2025-08-24 05:22:18', '2025-08-24 05:22:18', NULL);

--
-- Triggers `Milestones`
--
DELIMITER $$
CREATE TRIGGER `after_milestone_update` AFTER UPDATE ON `Milestones` FOR EACH ROW BEGIN
    DECLARE total_milestones INT;
    DECLARE completed_milestones INT;
    DECLARE progress_percentage FLOAT;
    
    -- Đếm tổng số milestones của goal
    SELECT COUNT(*) INTO total_milestones
    FROM Milestones
    WHERE goal_id = NEW.goal_id;
    
    -- Đếm số milestones đã hoàn thành
    SELECT COUNT(*) INTO completed_milestones
    FROM Milestones
    WHERE goal_id = NEW.goal_id AND is_completed = TRUE;
    
    -- Tính phần trăm tiến độ
    IF total_milestones > 0 THEN
        SET progress_percentage = (completed_milestones / total_milestones) * 100;
    ELSE
        SET progress_percentage = 0;
    END IF;
    
    -- Cập nhật hoặc thêm mới vào bảng GoalProgress
    INSERT INTO GoalProgress (goal_id, progress_value)
    VALUES (NEW.goal_id, progress_percentage)
    ON DUPLICATE KEY UPDATE progress_value = progress_percentage;
    
    -- Nếu tất cả milestones đã hoàn thành, cập nhật trạng thái goal thành 'completed'
    IF completed_milestones = total_milestones AND total_milestones > 0 THEN
        UPDATE Goals SET status = 'completed' WHERE goal_id = NEW.goal_id;
    -- Nếu có ít nhất một milestone hoàn thành nhưng chưa hết, cập nhật thành 'in_progress'
    ELSEIF completed_milestones > 0 THEN
        UPDATE Goals SET status = 'in_progress' WHERE goal_id = NEW.goal_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `NoteGoalLinks`
--

CREATE TABLE `NoteGoalLinks` (
  `link_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `NoteGoalLinks`
--

INSERT INTO `NoteGoalLinks` (`link_id`, `note_id`, `goal_id`, `created_at`) VALUES
(9, 70, 27, '2025-07-05 06:33:50'),
(11, 69, 27, '2025-07-05 06:56:51'),
(41, 125, 27, '2025-08-23 14:38:08'),
(42, 128, 45, '2025-08-23 14:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `NoteMilestoneLinks`
--

CREATE TABLE `NoteMilestoneLinks` (
  `link_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `milestone_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Notes`
--

CREATE TABLE `Notes` (
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Notes`
--

INSERT INTO `Notes` (`note_id`, `user_id`, `title`, `content`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 'Test Note', 'This is a test note content', '2025-06-22 03:28:10', '2025-06-22 03:28:10', NULL),
(2, 7, 'Test Note', 'This is a test note content', '2025-06-22 03:42:29', '2025-06-22 03:42:29', NULL),
(3, 7, 'Test Note', 'This is a test note content', '2025-06-22 03:48:09', '2025-06-22 03:48:09', NULL),
(4, 7, 'Test Note 1750589553', 'This is a test note content', '2025-06-22 03:52:34', '2025-06-22 03:52:34', NULL),
(5, 7, 'Test Note', 'Test content', '2025-06-22 03:59:05', '2025-06-22 03:59:05', NULL),
(6, 7, 'Test Note', 'Test content', '2025-06-22 04:00:03', '2025-06-22 04:00:03', NULL),
(7, 7, 'Test Note', 'Test content', '2025-06-22 04:03:44', '2025-06-22 04:03:44', NULL),
(8, 7, 'Test Note', 'Test note content', '2025-06-22 04:07:42', '2025-06-22 04:07:42', NULL),
(9, 7, 'Test Note', 'Test note content', '2025-06-22 04:11:33', '2025-06-22 04:11:33', NULL),
(10, 7, 'Test Note', 'Test content', '2025-06-22 04:29:11', '2025-06-22 04:29:11', NULL),
(11, 7, 'Test Note', 'Test note content', '2025-06-22 04:33:03', '2025-06-22 04:33:03', NULL),
(12, 7, 'Test Note 2025-06-22 11:39:27', 'Test note content for API testing', '2025-06-22 04:39:28', '2025-06-22 04:39:28', NULL),
(13, 7, 'Test Note 1750592698', 'This is a test note content for API testing', '2025-06-22 04:44:59', '2025-06-22 04:44:59', NULL),
(15, 7, 'UPDATED - Comprehensive Test Note 1750625157', 'This note content has been updated by the comprehensive API test script.', '2025-06-22 13:45:50', '2025-06-22 13:45:57', NULL),
(16, 7, 'UPDATED - Comprehensive Test Note 1750625184', 'This note content has been updated by the comprehensive API test script.', '2025-06-22 13:46:18', '2025-06-22 13:46:25', NULL),
(17, 7, 'UPDATED - Comprehensive Test Note 1750625827', 'This note content has been updated by the comprehensive API test script.', '2025-06-22 13:57:00', '2025-06-22 13:57:07', NULL),
(18, 7, 'UPDATED - Comprehensive Test Note 1750684947', 'This note content has been updated by the comprehensive API test script.', '2025-06-23 06:22:15', '2025-06-23 06:22:28', NULL),
(19, 7, 'UPDATED - Comprehensive Test Note 1750685934', 'This note content has been updated by the comprehensive API test script.', '2025-06-23 06:38:41', '2025-06-23 06:38:55', NULL),
(56, 39, 'manhcho2', 'manhcho2', '2025-07-03 03:12:58', '2025-07-03 03:12:58', NULL),
(59, 40, 'thành', 'thành đẹp treai', '2025-07-03 03:47:51', '2025-07-03 03:47:51', NULL),
(60, 43, 'cccc', 'cccc', '2025-07-03 03:48:24', '2025-07-03 03:48:24', NULL),
(61, 40, 'memay', 'DSADJ', '2025-07-03 03:49:23', '2025-07-03 03:49:23', NULL),
(62, 40, 'memay', 'DSADJ', '2025-07-03 03:49:59', '2025-07-03 03:49:59', NULL),
(63, 40, 'memay', 'DSADJ', '2025-07-03 03:50:00', '2025-07-03 03:50:00', NULL),
(64, 40, 'memay', 'DSADJ', '2025-07-03 03:50:03', '2025-07-03 03:50:03', NULL),
(65, 40, 'memay', 'DSADJ', '2025-07-03 03:50:07', '2025-07-03 03:50:07', NULL),
(66, 40, 'memay', 'DSADJ', '2025-07-03 03:50:18', '2025-07-03 03:50:18', NULL),
(67, 40, 'memay', 'DSADJ', '2025-07-03 03:50:20', '2025-07-03 03:50:20', NULL),
(68, 40, 'SADFAS', 'ÁDA', '2025-07-03 03:50:22', '2025-07-03 03:50:22', NULL),
(69, 39, 'manhcho3', 'manhcho3', '2025-07-04 23:30:36', '2025-07-04 23:30:36', NULL),
(70, 39, 'manhcho4', 'manhcho4', '2025-07-04 23:33:37', '2025-07-04 23:33:37', NULL),
(73, 46, 'ok', '1234', '2025-08-03 22:35:11', '2025-08-03 22:35:11', NULL),
(74, 46, '1234', 'demo123', '2025-08-03 22:48:44', '2025-08-03 22:48:44', NULL),
(75, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:11', '2025-08-05 23:22:11', NULL),
(76, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:11', '2025-08-05 23:22:11', NULL),
(77, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:11', '2025-08-05 23:22:11', NULL),
(78, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:11', '2025-08-05 23:22:11', NULL),
(79, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:11', '2025-08-05 23:22:11', NULL),
(81, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:16', '2025-08-25 01:41:22', '2025-08-25 01:41:22'),
(82, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:16', '2025-08-05 23:22:16', NULL),
(83, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:16', '2025-08-05 23:22:16', NULL),
(84, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:16', '2025-08-05 23:22:16', NULL),
(85, 38, 'Bảo vệ Dự Án', 'Ngày 25/8/2025', '2025-08-05 23:22:21', '2025-08-25 01:04:44', '2025-08-25 01:04:44'),
(91, 40, 'Bảo vệ Dự Án', '<h2><i><strong>yyu</strong></i></h2>', '2025-08-19 06:12:54', '2025-08-21 09:56:13', NULL),
(94, 40, 'Y', 'UUUUUUU', '2025-08-20 00:07:11', '2025-08-20 00:07:11', NULL),
(95, 40, 'Y', 'UUUUUUU', '2025-08-20 00:07:26', '2025-08-20 00:07:26', NULL),
(97, 40, 'Y', 'UUUUUUU', '2025-08-20 00:07:29', '2025-08-20 00:07:29', NULL),
(98, 40, 'Y', 'UUUUUUU', '2025-08-20 00:07:29', '2025-08-20 00:07:29', NULL),
(99, 40, 'Y', 'UUUUUUU', '2025-08-20 00:07:29', '2025-08-20 00:07:29', NULL),
(101, 29, '423423', '324234342', '2025-08-20 00:55:33', '2025-08-21 22:02:22', '2025-08-21 22:02:22'),
(102, 29, '423423', 'hhhhhhhjkhg', '2025-08-20 00:55:33', '2025-08-23 23:50:20', NULL),
(103, 29, 'dfwe3232', '<p>ewwerwerwe3232</p>', '2025-08-20 00:56:27', '2025-08-23 00:46:16', NULL),
(104, 39, 'gzx', 'ccc', '2025-08-21 13:07:15', '2025-08-21 13:07:15', NULL),
(105, 39, 'gzx', 'ccc', '2025-08-21 13:07:16', '2025-08-21 13:07:16', NULL),
(106, 39, 'bb', 'bb', '2025-08-21 13:07:30', '2025-08-21 13:07:30', NULL),
(107, 39, 'vv', 'vv', '2025-08-21 13:07:51', '2025-08-21 13:07:51', NULL),
(108, 39, 'vv', 'vv', '2025-08-21 13:07:51', '2025-08-21 13:07:51', NULL),
(109, 39, 'vv', 'vv', '2025-08-21 13:07:51', '2025-08-21 13:07:51', NULL),
(110, 39, 'vv', 'vv', '2025-08-21 13:07:51', '2025-08-21 13:07:51', NULL),
(111, 39, 'vv', 'vv', '2025-08-21 13:07:51', '2025-08-21 13:07:51', NULL),
(112, 39, 'vv', 'vv', '2025-08-21 13:07:53', '2025-08-21 13:07:53', NULL),
(113, 39, 'vv', 'vv', '2025-08-21 13:07:53', '2025-08-21 13:07:53', NULL),
(114, 39, 'wwww', 'www', '2025-08-21 13:09:50', '2025-08-21 13:09:50', NULL),
(115, 39, 'wwww', 'www', '2025-08-21 13:09:50', '2025-08-21 13:09:50', NULL),
(116, 39, 'wwww', 'www', '2025-08-21 13:09:50', '2025-08-21 13:09:50', NULL),
(117, 39, 'wwww', 'www', '2025-08-21 13:09:51', '2025-08-21 13:09:51', NULL),
(118, 39, 'kk', 'kkk', '2025-08-21 23:51:33', '2025-08-21 23:51:33', NULL),
(119, 39, 'kk', 'kkk', '2025-08-21 23:51:33', '2025-08-21 23:51:33', NULL),
(120, 39, 'kk', 'kkk', '2025-08-21 23:51:33', '2025-08-21 23:51:33', NULL),
(121, 39, 'kk', 'kkk', '2025-08-21 23:51:33', '2025-08-21 23:51:33', NULL),
(122, 39, 'kk', 'kkk', '2025-08-21 23:51:40', '2025-08-21 23:51:40', NULL),
(123, 39, 'qqqqqqq', 'qqqqqq', '2025-08-22 00:59:22', '2025-08-22 00:59:22', NULL),
(124, 39, 'qqqqqqq', 'qqqqqq', '2025-08-22 00:59:22', '2025-08-22 00:59:22', NULL),
(125, 39, 'qqqqqqq', 'qqqqqq', '2025-08-22 00:59:25', '2025-08-22 00:59:25', NULL),
(126, 39, 'qqqqqqq', '<p>qqqqqqffffffffff</p>', '2025-08-22 00:59:25', '2025-08-22 13:35:36', NULL),
(127, 39, 'qqqqqqq', 'qqqqqq', '2025-08-22 00:59:25', '2025-08-22 00:59:25', NULL),
(128, 39, 'aaaafffllllllll', 'fffffffffff', '2025-08-22 13:37:53', '2025-08-23 07:38:41', NULL),
(129, 39, 'manhcho555', 'manhcho555', '2025-08-25 02:22:03', '2025-08-25 02:22:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Notifications`
--

CREATE TABLE `Notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('reminder','friend_update','goal_progress','ai_suggestion') NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Payments`
--

CREATE TABLE `Payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('success','failed','pending') DEFAULT 'success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Payments`
--

INSERT INTO `Payments` (`payment_id`, `user_id`, `plan_id`, `amount`, `payment_date`, `status`) VALUES
(1, 40, 1, 99000.00, '2025-07-10 07:05:26', 'pending'),
(2, 40, 1, 99000.00, '2025-07-10 07:14:27', 'pending'),
(3, 40, 1, 99000.00, '2025-07-10 07:45:12', 'pending'),
(4, 40, 1, 99000.00, '2025-07-10 09:37:46', 'pending'),
(5, 40, 1, 99000.00, '2025-07-10 09:40:43', 'pending'),
(6, 40, 1, 99000.00, '2025-07-10 09:45:53', 'success'),
(7, 38, 2, 950000.00, '2025-07-24 00:36:04', 'success'),
(8, 29, 1, 99000.00, '2025-07-26 00:05:48', 'pending'),
(9, 10, 2, 950000.00, '2025-07-26 00:16:24', 'pending'),
(10, 38, 1, 99000.00, '2025-07-26 01:02:24', 'failed'),
(13, 7, 2, 950000.00, '2025-07-26 19:40:40', 'pending'),
(14, 38, 2, 950000.00, '2025-07-26 19:42:01', 'success'),
(15, 38, 2, 950000.00, '2025-07-26 19:51:51', 'success'),
(16, 29, 1, 99000.00, '2025-08-03 08:55:40', 'pending'),
(17, 29, 1, 99000.00, '2025-08-03 08:56:14', 'pending'),
(18, 29, 2, 950000.00, '2025-08-03 08:56:41', 'pending'),
(19, 29, 2, 950000.00, '2025-08-03 09:17:38', 'pending'),
(20, 29, 2, 950000.00, '2025-08-03 19:25:36', 'success'),
(21, 29, 1, 99000.00, '2025-08-03 19:42:46', 'success'),
(22, 29, 1, 99000.00, '2025-08-03 20:27:34', 'success'),
(23, 29, 2, 950000.00, '2025-08-03 20:28:35', 'success'),
(24, 29, 2, 950000.00, '2025-08-03 20:58:19', 'success'),
(25, 30, 1, 99000.00, '2025-08-03 21:02:56', 'success'),
(29, 38, 1, 99000.00, '2025-08-03 21:55:59', 'success'),
(32, 29, 1, 99000.00, '2025-08-03 22:01:26', 'success'),
(36, 38, 1, 99000.00, '2025-08-12 03:22:26', 'success'),
(37, 38, 2, 950000.00, '2025-08-15 04:44:23', 'success'),
(38, 40, 1, 99000.00, '2025-08-19 06:23:29', 'success'),
(39, 40, 1, 99000.00, '2025-08-19 07:16:26', 'success'),
(40, 40, 1, 99000.00, '2025-08-20 00:01:51', 'success'),
(41, 40, 2, 950000.00, '2025-08-20 02:14:54', 'success'),
(42, 40, 1, 99000.00, '2025-08-20 02:38:26', 'success'),
(43, 46, 2, 950000.00, '2025-08-20 04:18:33', 'pending'),
(44, 46, 2, 950000.00, '2025-08-20 04:18:43', 'failed'),
(45, 46, 2, 950000.00, '2025-08-20 04:19:07', 'success'),
(46, 10, 2, 950000.00, '2025-08-21 01:15:52', 'success'),
(47, 46, 2, 950000.00, '2025-08-21 01:55:07', 'failed'),
(48, 46, 2, 950000.00, '2025-08-21 01:59:07', 'pending'),
(49, 46, 2, 950000.00, '2025-08-21 01:59:30', 'pending'),
(50, 46, 2, 950000.00, '2025-08-21 02:01:46', 'pending'),
(51, 46, 2, 950000.00, '2025-08-21 02:04:18', 'failed'),
(52, 46, 2, 950000.00, '2025-08-21 02:04:38', 'pending'),
(53, 46, 2, 950000.00, '2025-08-21 02:05:01', 'success'),
(54, 41, 2, 950000.00, '2025-08-21 05:12:54', 'failed'),
(55, 39, 1, 99000.00, '2025-08-21 05:16:08', 'failed'),
(56, 39, 2, 950000.00, '2025-08-21 05:17:18', 'success'),
(57, 40, 2, 950000.00, '2025-08-21 10:19:50', 'pending'),
(58, 40, 2, 950000.00, '2025-08-21 10:33:16', 'success'),
(59, 38, 2, 950000.00, '2025-08-22 01:55:42', 'success'),
(60, 29, 2, 950000.00, '2025-08-22 03:38:02', 'success'),
(62, 38, 1, 99000.00, '2025-08-25 01:54:02', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `PermissionHistory`
--

CREATE TABLE `PermissionHistory` (
  `history_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_role_id` int(11) DEFAULT NULL,
  `new_role_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Permissions`
--

CREATE TABLE `Permissions` (
  `permission_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'auth_token', 'a5e00939f7133ac7a83452f94fbc5a1734bd90d862aa566aa6ca34e6ef5719d7', '[\"*\"]', NULL, NULL, '2025-06-11 06:52:12', '2025-06-11 06:52:12'),
(2, 'App\\Models\\User', 1, 'auth_token', 'c6ae18850b9726bdc41584d99c9823bd2f18731fa6e54dc24bc33a8702554fc9', '[\"*\"]', NULL, NULL, '2025-06-11 07:08:01', '2025-06-11 07:08:01'),
(3, 'App\\Models\\User', 2, 'auth_token', 'ac0961b879649890fcafb712112b4f2586b9cffe5fb379b3f75f7e1485b969cc', '[\"*\"]', NULL, NULL, '2025-06-11 07:19:44', '2025-06-11 07:19:44'),
(4, 'App\\Models\\User', 1, 'auth_token', '1bcac53423e46c8709fb75b488ebb2b52168c33f05bd0070e2a56cbbeb93e5aa', '[\"*\"]', NULL, NULL, '2025-06-12 20:52:16', '2025-06-12 20:52:16'),
(5, 'App\\Models\\User', 1, 'auth_token', '879809bdc5904a493a71949b4aff92844c86df3241bccd6736b22613d120b190', '[\"*\"]', NULL, NULL, '2025-06-12 23:34:14', '2025-06-12 23:34:14'),
(6, 'App\\Models\\User', 1, 'auth_token', 'f72d3b154de730d2e0d10f410be8662144843584caa651000f29ef2aa061a278', '[\"*\"]', NULL, NULL, '2025-06-12 23:35:13', '2025-06-12 23:35:13'),
(7, 'App\\Models\\User', 1, 'auth_token', '8188e2cb71a535fd1f5498d18cbc6733511bad0e2078089337eb36e29d380a9d', '[\"*\"]', NULL, NULL, '2025-06-13 00:35:42', '2025-06-13 00:35:42'),
(8, 'App\\Models\\User', 1, 'auth_token', 'd7b57cc9e89d616ff80b6a82eda2ca37aa8766df28f91d35bfdd4ab4225820a7', '[\"*\"]', NULL, NULL, '2025-06-13 00:40:18', '2025-06-13 00:40:18'),
(9, 'App\\Models\\User', 1, 'auth_token', '2de87b625ceb50e970f093215e7a86344842b6fbf544f92ff4f5a5662d916baa', '[\"*\"]', NULL, NULL, '2025-06-13 00:49:28', '2025-06-13 00:49:28'),
(10, 'App\\Models\\User', 1, 'auth_token', '4374e54b546c7bd6b7cbf610e37599e48848e0e104a6f50d7860c5678caab899', '[\"*\"]', NULL, NULL, '2025-06-13 00:49:44', '2025-06-13 00:49:44'),
(11, 'App\\Models\\User', 1, 'auth_token', '34024c6e2c23b8f154d09ed0449d23e551d4e2f34f721f484138f711e26cdb05', '[\"*\"]', NULL, NULL, '2025-06-13 00:54:19', '2025-06-13 00:54:19'),
(12, 'App\\Models\\User', 3, 'auth_token', '8492566424b959cf8dbada05ff9944d1722ed9b2216add4bb696949827cfb4ed', '[\"*\"]', NULL, NULL, '2025-06-13 01:30:50', '2025-06-13 01:30:50'),
(13, 'App\\Models\\User', 4, 'auth_token', '1192395a7103c0aab76c004939588b46282c15e0d4be97923cd78c48d44a0d48', '[\"*\"]', NULL, NULL, '2025-06-13 04:27:04', '2025-06-13 04:27:04'),
(14, 'App\\Models\\User', 3, 'auth_token', '52437bd29bfd58ef2e8230ae7c61ddf987e1d1b844756e31f810acccf9641625', '[\"*\"]', NULL, NULL, '2025-06-13 04:27:31', '2025-06-13 04:27:31'),
(16, 'App\\Models\\User', 3, 'auth_token', '2d717f95dc2652154d50ade04524c2fa693d8664990ba98253c441ca79c0d48f', '[\"*\"]', NULL, NULL, '2025-06-16 02:08:40', '2025-06-16 02:08:40'),
(17, 'App\\Models\\User', 8, 'auth_token', '1b9557a9b3656138db0e94f6feb8281772c20b010abf63df8751c5db941e162c', '[\"*\"]', NULL, NULL, '2025-06-16 02:15:56', '2025-06-16 02:15:56'),
(18, 'App\\Models\\User', 3, 'auth_token', '746fb1fe5df545f439050b7ab2ee191af6e0abb9bd8d9f5c4cb8d8534c2a7dcd', '[\"*\"]', NULL, NULL, '2025-06-17 00:22:51', '2025-06-17 00:22:51'),
(19, 'App\\Models\\User', 1, 'auth_token', 'e34b946f4e6fdc98097dfff1e8dcf87e72250978c8b1d869112e58b37013b1ab', '[\"*\"]', NULL, NULL, '2025-06-17 01:26:52', '2025-06-17 01:26:52'),
(20, 'App\\Models\\User', 1, 'auth_token', 'b59ed9bed4695fdf9b78fd76fedf4b3121396a6250cfe6888760f31f51aa1773', '[\"*\"]', NULL, NULL, '2025-06-17 01:55:57', '2025-06-17 01:55:57'),
(21, 'App\\Models\\User', 4, 'auth_token', 'd19876f04f50ece93f484b0e5a5989a4afa4c9b732276219679dcd10e24796a7', '[\"*\"]', NULL, NULL, '2025-06-17 01:58:16', '2025-06-17 01:58:16'),
(22, 'App\\Models\\User', 1, 'auth_token', '90836711b125e673c9e9ef4431c67eda0a2b108c7a45c853c3105eb439270c2d', '[\"*\"]', NULL, NULL, '2025-06-18 07:34:10', '2025-06-18 07:34:10'),
(23, 'App\\Models\\User', 3, 'auth_token', '17e3b23d4034baaeb419803e2a46fd5c44dc7da6cc6e5b19d8826df523e6f095', '[\"*\"]', NULL, NULL, '2025-06-18 07:52:50', '2025-06-18 07:52:50'),
(24, 'App\\Models\\User', 3, 'auth_token', 'ef7c6dd4e463bca623aedc5f8bd3f5252b5f01345e4b3b3e8e289dc882266066', '[\"*\"]', NULL, NULL, '2025-06-18 07:56:26', '2025-06-18 07:56:26'),
(25, 'App\\Models\\User', 4, 'auth_token', 'fe882beda7ff9b1dc786dc8b55a764accfcccddbcc0b92a18304f8dcacba4bcd', '[\"*\"]', NULL, NULL, '2025-06-18 08:07:52', '2025-06-18 08:07:52'),
(26, 'App\\Models\\User', 1, 'auth_token', '3dfb460030f79f6bd00a9b00c14860cec3f89af465458fea6b66edb083411d91', '[\"*\"]', NULL, NULL, '2025-06-18 08:15:42', '2025-06-18 08:15:42'),
(27, 'App\\Models\\User', 4, 'auth_token', '341564d300bad2e61880388c44ffed799d2d90158b4e8fef449d5fc10ff29d7e', '[\"*\"]', NULL, NULL, '2025-06-18 08:16:04', '2025-06-18 08:16:04'),
(28, 'App\\Models\\User', 3, 'auth_token', 'd1c71c09c1d706f589409318d570339c61203d8e2d50391b4b9cb9952764329e', '[\"*\"]', NULL, NULL, '2025-06-18 08:16:32', '2025-06-18 08:16:32'),
(29, 'App\\Models\\User', 1, 'auth_token', '9630b790c2f92b2b69d195fbc688c81575ab2a2a03acb5acd2a480f2f4bd37e5', '[\"*\"]', NULL, NULL, '2025-06-18 08:25:05', '2025-06-18 08:25:05'),
(30, 'App\\Models\\User', 4, 'auth_token', '8d34ea786665581afd492345c8606aad12f5b85f1831d7887ae09eafd46fc3e4', '[\"*\"]', NULL, NULL, '2025-06-18 08:25:23', '2025-06-18 08:25:23'),
(31, 'App\\Models\\User', 4, 'auth_token', 'aa5a6590e6c5be81c9ac8a61424f36ab060b13e2664ae1751ba936882cb950d0', '[\"*\"]', NULL, NULL, '2025-06-18 08:25:46', '2025-06-18 08:25:46'),
(32, 'App\\Models\\User', 3, 'auth_token', 'c8ff6bc4a37667b523e4d0e0319e9ea3795e370580ced562d51a68cc66e1830e', '[\"*\"]', NULL, NULL, '2025-06-18 08:30:19', '2025-06-18 08:30:19'),
(33, 'App\\Models\\User', 11, 'auth_token', '19716097a0955ad43375341fc173c1666c17038111377221e836680543b62f85', '[\"*\"]', NULL, NULL, '2025-06-18 09:35:25', '2025-06-18 09:35:25'),
(35, 'App\\Models\\User', 1, 'auth_token', '4c476dcc97adb40fd08f9030eff6a7c2657c9cf0035fd00083b4f12e243c1d39', '[\"*\"]', NULL, NULL, '2025-06-21 21:41:48', '2025-06-21 21:41:48'),
(36, 'App\\Models\\User', 11, 'auth_token', '766b83c4f79af44e55c1c17ec2e8f60b93277b1e3eb6a718c55d57ca1f63778d', '[\"*\"]', NULL, NULL, '2025-06-21 21:45:05', '2025-06-21 21:45:05'),
(37, 'App\\Models\\User', 1, 'auth_token', '27eeea616fe06c6d620fcdb5a02a311960ef24a8d10e4eaeb81777529974e064', '[\"*\"]', NULL, NULL, '2025-06-21 21:48:44', '2025-06-21 21:48:44'),
(38, 'App\\Models\\User', 12, 'auth_token', '85787accda5280036532b8de8ca801cc60b13502f346ecbad781beb7a139a4ba', '[\"*\"]', NULL, NULL, '2025-06-21 22:15:41', '2025-06-21 22:15:41'),
(39, 'App\\Models\\User', 12, 'auth_token', '179d287f4b3528a1719a6d86d2bbd12a87d4324c62ec59aceaa1c469ce77b4c6', '[\"*\"]', NULL, NULL, '2025-06-21 22:16:23', '2025-06-21 22:16:23'),
(40, 'App\\Models\\User', 13, 'auth_token', '876b7126a8f968b3f7e3985eedd93d94bcfd05bf73f4ff07bd0f766a6d76add7', '[\"*\"]', NULL, NULL, '2025-06-21 22:21:26', '2025-06-21 22:21:26'),
(41, 'App\\Models\\User', 13, 'auth_token', 'ef9ac0a7a694e55c160d5a95e505283c14aa42d541161c7b5bcc8925564af349', '[\"*\"]', NULL, NULL, '2025-06-21 22:21:56', '2025-06-21 22:21:56'),
(42, 'App\\Models\\User', 14, 'auth_token', 'b2c02605e4c77882a834cd525318862614622a80d4f687e062886edc2a3890ae', '[\"*\"]', NULL, NULL, '2025-06-21 23:39:29', '2025-06-21 23:39:29'),
(43, 'App\\Models\\User', 1, 'auth_token', '992ca6464fe0c8a6563779af1616f74b2a6ad16bc444fb09877c66bacb8435d1', '[\"*\"]', NULL, NULL, '2025-06-21 23:42:14', '2025-06-21 23:42:14'),
(44, 'App\\Models\\User', 1, 'auth_token', '86878b3a37e32b1d5605982d3c505db5c47536d5f7b54d42eb3dc6303f2cf152', '[\"*\"]', NULL, NULL, '2025-06-22 03:19:56', '2025-06-22 03:19:56'),
(66, 'App\\Models\\User', 29, 'auth_token', '1e2b479f1d891ad367607cda36de9900f5452bd404a3a3b587077dfa74e3010c', '[\"*\"]', NULL, NULL, '2025-06-22 09:15:44', '2025-06-22 09:15:44'),
(67, 'App\\Models\\User', 30, 'auth_token', '7d13ea7e5ef2b167c7e844bb903600bbe1cb53493d0b3dc6bf36b1b29cf1052c', '[\"*\"]', NULL, NULL, '2025-06-22 09:24:20', '2025-06-22 09:24:20'),
(68, 'App\\Models\\User', 1, 'test_token', '8aea122b652e2b154fae0ef121396f8ae4ebf0b77060095cc286fe01adb79835', '[\"*\"]', '2025-06-22 13:34:45', NULL, '2025-06-22 13:34:38', '2025-06-22 13:34:45'),
(69, 'App\\Models\\User', 1, 'test_token', 'a029da380f065b8ec2c02454ea0ae270203190f990a75a38db4eb969a9ce61a2', '[\"*\"]', '2025-06-22 13:35:27', NULL, '2025-06-22 13:35:20', '2025-06-22 13:35:27'),
(78, 'App\\Models\\User', 7, 'comprehensive_test_token', 'ad777c16437ca4c31a50e2f485fbfc57483509143d65a8ea560b006b21e3a3d7', '[\"*\"]', '2025-06-23 06:38:59', NULL, '2025-06-23 06:38:37', '2025-06-23 06:38:59'),
(79, 'App\\Models\\User', 29, 'auth_token', '1bf3aa49204d412154f3d2f2e627dd5b8c2e3e7ca508299c180b97ece3abf7bf', '[\"*\"]', NULL, NULL, '2025-06-23 22:47:17', '2025-06-23 22:47:17'),
(80, 'App\\Models\\User', 29, 'auth_token', 'd7a882cfb30389981311de062a14fefaf9ca2aa75488309ef3aa2a59d1489963', '[\"*\"]', NULL, NULL, '2025-06-23 22:48:32', '2025-06-23 22:48:32'),
(81, 'App\\Models\\User', 29, 'auth_token', 'a97a173400526d160653e6d1ffda626562864073a6c6b5f99f2e69a1b9bbbfb1', '[\"*\"]', NULL, NULL, '2025-06-24 01:07:48', '2025-06-24 01:07:48'),
(82, 'App\\Models\\User', 30, 'auth_token', 'dde9126ed1b86b84e87a2c6b65118cf74ed55e905a7ac30b308c8905eafdee1f', '[\"*\"]', NULL, NULL, '2025-06-24 01:08:46', '2025-06-24 01:08:46'),
(83, 'App\\Models\\User', 29, 'auth_token', '0fabaf5cbd6980b0b0139238c18dcfadfc2bade6a8304702fef3de4670d7b757', '[\"*\"]', NULL, NULL, '2025-06-24 01:14:19', '2025-06-24 01:14:19'),
(84, 'App\\Models\\User', 30, 'auth_token', '451ddb18d7f7af1d2dc29df5f2588e730e74353e9c3ac19818cff59de5182ea9', '[\"*\"]', NULL, NULL, '2025-06-24 01:15:44', '2025-06-24 01:15:44'),
(85, 'App\\Models\\User', 7, 'auth_token', '1e43ef2613ab5c78da43350bdf303b62dd909b04d80a36c0f762db273e60927a', '[\"*\"]', NULL, NULL, '2025-06-24 01:18:26', '2025-06-24 01:18:26'),
(86, 'App\\Models\\User', 29, 'auth_token', '4d12240896597ef9d1ad47f2ace232e319f936c093f302822ce503c6aa858c86', '[\"*\"]', NULL, NULL, '2025-06-24 01:20:21', '2025-06-24 01:20:21'),
(87, 'App\\Models\\User', 7, 'auth_token', '5dee8d6e11b0dfb3c89b4ead6d30c9bf37c6caf9efef5c288883421b783ab82c', '[\"*\"]', NULL, NULL, '2025-06-24 01:21:08', '2025-06-24 01:21:08'),
(88, 'App\\Models\\User', 7, 'auth_token', 'b4672952d9a68e4feda295ecdd629f965cdb1614a4b47b2539570f54a0a62ed2', '[\"*\"]', NULL, NULL, '2025-06-24 02:40:23', '2025-06-24 02:40:23'),
(96, 'App\\Models\\User', 29, 'auth_token', '298826e5dcff131476f6565702f07fbf70586def7cc6cfe7bdcc86d0bebfb393', '[\"*\"]', '2025-06-25 02:44:15', NULL, '2025-06-25 01:51:39', '2025-06-25 02:44:15'),
(100, 'App\\Models\\User', 29, 'auth_token', '4ec87655338b01a6ee75151238b8ea116b7fb1ceefbb43deadc42e0180ee6829', '[\"*\"]', '2025-06-26 01:53:50', NULL, '2025-06-26 01:49:28', '2025-06-26 01:53:50'),
(102, 'App\\Models\\User', 29, 'auth_token', '9548f3149e0f899435c61a28865e803ec1ff9fa32084fba4b3cb9f343d0cadcb', '[\"*\"]', '2025-06-26 02:57:56', NULL, '2025-06-26 02:45:11', '2025-06-26 02:57:56'),
(105, 'App\\Models\\User', 14, 'auth_token', '3dab07579456d57c65162e69cbfb1661d696ed99bf3d3395f0c0f46c89797c12', '[\"*\"]', NULL, NULL, '2025-06-26 08:29:40', '2025-06-26 08:29:40'),
(106, 'App\\Models\\User', 14, 'auth_token', 'bdabafc2c15b27c119f0760089ee7dd37f2861bcc1b93017059ca42e4c6d37fb', '[\"*\"]', NULL, NULL, '2025-06-26 08:29:57', '2025-06-26 08:29:57'),
(107, 'App\\Models\\User', 1, 'auth_token', '386ce4396a3eee1be787d60f85a3c334d7e82c855b393f7e98cd7498665b260f', '[\"*\"]', NULL, NULL, '2025-06-26 08:30:11', '2025-06-26 08:30:11'),
(111, 'App\\Models\\User', 30, 'auth_token', '4834c9ba7631a794350c0978e2474f05bf22fa246fee08f42bacdcbfdf49aecd', '[\"*\"]', '2025-06-28 21:40:26', NULL, '2025-06-27 13:36:36', '2025-06-28 21:40:26'),
(115, 'App\\Models\\User', 14, 'auth_token', '127c6150897eb8af2bfe675b1aa0cbc558aa854e8265fbcd44f18372026279d8', '[\"*\"]', NULL, NULL, '2025-06-28 22:41:29', '2025-06-28 22:41:29'),
(116, 'App\\Models\\User', 14, 'auth_token', 'd8c60027a0edc00f71a03a1e026d7fd573c178585b7aaed9223dfef639478d24', '[\"*\"]', NULL, NULL, '2025-06-28 23:37:12', '2025-06-28 23:37:12'),
(117, 'App\\Models\\User', 14, 'auth_token', '4aeb0990f8faa3b4a9c50b2f2c35216b4770af103d4401d483fa2399982734d9', '[\"*\"]', NULL, NULL, '2025-06-28 23:42:24', '2025-06-28 23:42:24'),
(119, 'App\\Models\\User', 14, 'auth_token', 'f1d2a096e7923cbb5ad15950f242d0e439503a96baecc8aa5cb8f43a605c6764', '[\"*\"]', NULL, NULL, '2025-06-28 23:44:01', '2025-06-28 23:44:01'),
(120, 'App\\Models\\User', 14, 'auth_token', 'be2e25c8ea3ed6ea91b733ebf090cfee4e5c37ffacf338ab6e2dcc89c1833fb1', '[\"*\"]', NULL, NULL, '2025-06-28 23:46:10', '2025-06-28 23:46:10'),
(121, 'App\\Models\\User', 14, 'auth_token', 'af3c533b6b54fc9ec03a595cdeb49d5866629ea51a86135c0102858cb421550b', '[\"*\"]', NULL, NULL, '2025-06-28 23:52:33', '2025-06-28 23:52:33'),
(123, 'App\\Models\\User', 29, 'auth_token', 'a5eecc7fa888dae165ad4937f7811d7e3915c92690bd5c0a8b470e7fabf1262f', '[\"*\"]', '2025-06-29 00:08:29', NULL, '2025-06-29 00:03:46', '2025-06-29 00:08:29'),
(126, 'App\\Models\\User', 14, 'auth_token', 'fa5377f798525e300dcf87ba8945c628dc8105a1959fb8f2fba5458f9270749d', '[\"*\"]', NULL, NULL, '2025-06-30 01:32:16', '2025-06-30 01:32:16'),
(129, 'App\\Models\\User', 29, 'auth_token', '35709c7cf611f0c072d1d8a86ace4752e11e288aa63fac12d62065a997315183', '[\"*\"]', '2025-07-02 01:18:59', NULL, '2025-06-30 01:52:56', '2025-07-02 01:18:59'),
(130, 'App\\Models\\User', 14, 'auth_token', '039e13e689ffe11608b66c72c3209590c490534051d23bb27538ca9c706c46ec', '[\"*\"]', NULL, NULL, '2025-06-30 02:27:03', '2025-06-30 02:27:03'),
(131, 'App\\Models\\User', 14, 'auth_token', '633ca9bdd2cd04bccf7413ff2a4d427d0d1f0232b387367b0d3dfe6ec662f10e', '[\"*\"]', NULL, NULL, '2025-06-30 02:43:06', '2025-06-30 02:43:06'),
(132, 'App\\Models\\User', 14, 'auth_token', '3fcd0a9f10494d595d91abdc6f53e7958e6876e156e1b47ed742de2ebeb90378', '[\"*\"]', '2025-06-30 03:02:20', NULL, '2025-06-30 03:01:54', '2025-06-30 03:02:20'),
(133, 'App\\Models\\User', 14, 'auth_token', '413e2a1b12bc44ef4544eb50a323000c62c4900c2a033b4563b1985e647406dd', '[\"*\"]', NULL, NULL, '2025-06-30 03:05:27', '2025-06-30 03:05:27'),
(135, 'App\\Models\\User', 29, 'auth_token', 'ae3336d1117fe8b8e53213a2fdffa5ab592ae2c5ec1658e30f8138e87d267b59', '[\"*\"]', '2025-06-30 03:25:43', NULL, '2025-06-30 03:17:24', '2025-06-30 03:25:43'),
(136, 'App\\Models\\User', 14, 'auth_token', 'eda811933dabf25fd87a383dc43129b24312899127aa265143085e95e744cfe6', '[\"*\"]', '2025-06-30 03:34:37', NULL, '2025-06-30 03:31:26', '2025-06-30 03:34:37'),
(137, 'App\\Models\\User', 29, 'auth_token', 'b54271b07ae8d7fb46d0c33b2900ba35a76ad5de2be711dcbf4bdb802eb570a9', '[\"*\"]', '2025-07-01 06:53:17', NULL, '2025-07-01 06:42:54', '2025-07-01 06:53:17'),
(138, 'App\\Models\\User', 29, 'auth_token', 'd986e35b3176ab61c5607dfc47df32f8eba9cd742ac10e00b210bef9b96dc9a8', '[\"*\"]', '2025-07-01 08:13:09', NULL, '2025-07-01 06:58:33', '2025-07-01 08:13:09'),
(140, 'App\\Models\\User', 14, 'auth_token', '09884c68d09ff59a10ffec6458b7201f7873bbc6dbf30438e3db3f03cb39b39c', '[\"*\"]', NULL, NULL, '2025-07-01 07:24:06', '2025-07-01 07:24:06'),
(142, 'App\\Models\\User', 29, 'auth_token', '34222d8956cb3b5ff3d7c3686956c263ec22f3871ce5f6259a7f6a6f76a3807d', '[\"*\"]', '2025-07-01 09:22:55', NULL, '2025-07-01 08:18:41', '2025-07-01 09:22:55'),
(143, 'App\\Models\\User', 29, 'auth_token', '2795496cef816ae2e4932b1748e189150b2d392e164233a048509879a172932a', '[\"*\"]', '2025-07-01 10:22:36', NULL, '2025-07-01 09:27:03', '2025-07-01 10:22:36'),
(146, 'App\\Models\\User', 37, 'auth_token', '288830244b7d4ba1ad10b3fd54be0cd6d147a5f2ace4fe3d04a9a4d705e076f1', '[\"*\"]', '2025-07-01 10:58:04', NULL, '2025-07-01 10:38:20', '2025-07-01 10:58:04'),
(147, 'App\\Models\\User', 29, 'auth_token', 'd37819e962437271623d26727d5dda18816d1a5ac32a05fa373175bc8eb3f205', '[\"*\"]', '2025-07-01 22:31:50', NULL, '2025-07-01 22:20:04', '2025-07-01 22:31:50'),
(148, 'App\\Models\\User', 29, 'auth_token', '3aa70aa3412887e6597bc4e17606a374d6940b958c5f1a4fb5f5eb6eee944a5d', '[\"*\"]', NULL, NULL, '2025-07-02 00:31:52', '2025-07-02 00:31:52'),
(149, 'App\\Models\\User', 38, 'auth_token', 'ab460087b2fcfb30a3215d1abea8fe28f28065f44b9571d0a17374f51d8a2d24', '[\"*\"]', NULL, NULL, '2025-07-02 00:49:25', '2025-07-02 00:49:25'),
(151, 'App\\Models\\User', 38, 'auth_token', '85081b48cbf917378a09a0fd580df46a7be5ba6bff15abf4e706f9956879a290', '[\"*\"]', '2025-07-02 00:58:47', NULL, '2025-07-02 00:52:30', '2025-07-02 00:58:47'),
(152, 'App\\Models\\User', 29, 'auth_token', '2cfc1968bb6674c288f69a469ed6869c737ac795a58757830fcd8535dca7e1f8', '[\"*\"]', '2025-07-03 00:45:43', NULL, '2025-07-02 01:47:58', '2025-07-03 00:45:43'),
(155, 'App\\Models\\User', 39, 'auth_token', '80120deb9cc425d459a917794439e217e731b5e8dc750331e32cd7f1600255c0', '[\"*\"]', '2025-07-03 00:54:41', NULL, '2025-07-03 00:52:01', '2025-07-03 00:54:41'),
(157, 'App\\Models\\User', 39, 'auth_token', 'a5a323e26c624280682a374265bfd380a8bd57368f8c589244b4acb07981e8b7', '[\"*\"]', '2025-07-03 01:24:17', NULL, '2025-07-03 01:08:15', '2025-07-03 01:24:17'),
(158, 'App\\Models\\User', 29, 'auth_token', '61c708f40d125b1e93830cd248ff1abcdd9884cc44c6a1b9165d49374220653d', '[\"*\"]', NULL, NULL, '2025-07-03 01:09:22', '2025-07-03 01:09:22'),
(159, 'App\\Models\\User', 29, 'auth_token', 'edf4878844c7897b90d00596deaae1d50bec7eb599994ea532a40f3e844a0771', '[\"*\"]', NULL, NULL, '2025-07-03 01:10:19', '2025-07-03 01:10:19'),
(160, 'App\\Models\\User', 29, 'auth_token', 'c1f36d74961a576729ffb820554e862f12b6d8eaff6b451da52e42dfa4fa6bde', '[\"*\"]', '2025-07-03 01:31:47', NULL, '2025-07-03 01:12:28', '2025-07-03 01:31:47'),
(163, 'App\\Models\\User', 29, 'auth_token', 'fe818ea0a34f39440e478ae4b3b05b03b27328dd49badf6b2e708b8360f170b1', '[\"*\"]', '2025-07-05 11:39:58', NULL, '2025-07-03 01:28:47', '2025-07-05 11:39:58'),
(164, 'App\\Models\\User', 39, 'auth_token', 'e854a4f30c145c1b875b07f402e9ef65e3330ed77157f6251e7fb29ca9f3beba', '[\"*\"]', '2025-07-03 01:53:40', NULL, '2025-07-03 01:31:43', '2025-07-03 01:53:40'),
(165, 'App\\Models\\User', 39, 'auth_token', '8599b35b2b8c8e3b6d93f7ca9e31a73d2efc992ddd4d5224fec558ed802350e7', '[\"*\"]', '2025-07-03 03:15:32', NULL, '2025-07-03 02:00:16', '2025-07-03 03:15:32'),
(167, 'App\\Models\\User', 39, 'auth_token', '62ea19f875508d8dfe31011a8632de58b0e6c79ec9aba10fdfcd97c0c979bdb6', '[\"*\"]', '2025-07-03 03:24:48', NULL, '2025-07-03 03:16:27', '2025-07-03 03:24:48'),
(168, 'App\\Models\\User', 41, 'auth_token', '1780f30d48c93dd1f43b059f1569774284cd43f9698d359e35886dcc5297c00a', '[\"*\"]', NULL, NULL, '2025-07-03 03:27:11', '2025-07-03 03:27:11'),
(169, 'App\\Models\\User', 7, 'auth_token', 'efea68c1752c9d1dbc533883c8db46a49aec312ae0d4233112f43d336f55046d', '[\"*\"]', NULL, NULL, '2025-07-03 03:28:29', '2025-07-03 03:28:29'),
(170, 'App\\Models\\User', 39, 'auth_token', 'ffc8eff8b2bccc678e76d0ee88b60c151e9dfde10f1540c96990260bf81d7d51', '[\"*\"]', '2025-07-03 03:58:30', NULL, '2025-07-03 03:29:53', '2025-07-03 03:58:30'),
(174, 'App\\Models\\User', 43, 'auth_token', 'cd07041fcd8bf4229a05db17b1e94ccec0ac3835fd84f3f9c7e41a18be523c3d', '[\"*\"]', NULL, NULL, '2025-07-03 03:45:08', '2025-07-03 03:45:08'),
(175, 'App\\Models\\User', 43, 'auth_token', 'b98a9e53494260ba5be48668869f71deddb0f28e6e5cd43b136d631cca9b68ed', '[\"*\"]', '2025-07-03 03:50:07', NULL, '2025-07-03 03:45:15', '2025-07-03 03:50:07'),
(176, 'App\\Models\\User', 40, 'auth_token', 'cc16f9917c80dcda29345d5aae4f9c104fd18f117ae729c96a8d5abeca0e03b8', '[\"*\"]', '2025-07-03 03:56:00', NULL, '2025-07-03 03:45:54', '2025-07-03 03:56:00'),
(177, 'App\\Models\\User', 43, 'auth_token', '30d861235d30924bcdacbfe45e9fa910e3c28cad03bba75dba5d58316125bb47', '[\"*\"]', '2025-07-03 03:49:53', NULL, '2025-07-03 03:49:41', '2025-07-03 03:49:53'),
(178, 'App\\Models\\User', 40, 'auth_token', 'a9b0f9a0b063288a0abf789f7e1278dbb8735ecf747bfd701f693a2a4dc6e82c', '[\"*\"]', NULL, NULL, '2025-07-04 08:33:02', '2025-07-04 08:33:02'),
(179, 'App\\Models\\User', 40, 'auth_token', 'c964d0467106e6cca5ef250d5b30f5b86f29cc9f5419f5eac597126c0703d728', '[\"*\"]', '2025-07-04 08:49:02', NULL, '2025-07-04 08:45:02', '2025-07-04 08:49:02'),
(180, 'App\\Models\\User', 1, 'auth_token', '23ff9e531d7398a27e50bbb8509ea6287cb68f3ccee56971f5193d61f2bd59d2', '[\"*\"]', '2025-07-04 08:49:34', NULL, '2025-07-04 08:45:39', '2025-07-04 08:49:34'),
(181, 'App\\Models\\User', 40, 'auth_token', 'f0e0e685bbac325322206e6b4a24f9ee9d67c602b3ac6cffd1ea43cfd616a222', '[\"*\"]', '2025-07-04 10:37:27', NULL, '2025-07-04 08:51:48', '2025-07-04 10:37:27'),
(182, 'App\\Models\\User', 39, 'auth_token', '8fe916a2a128d26a0bb0ade1f37282740fe227f76fccbacfc9b56f853defe78c', '[\"*\"]', NULL, NULL, '2025-07-04 22:36:14', '2025-07-04 22:36:14'),
(183, 'App\\Models\\User', 39, 'auth_token', '5e11162d18d81bfe467c7aa1b8e69961ed36ea6fa6032e54daec932afbaab06a', '[\"*\"]', NULL, NULL, '2025-07-04 22:38:02', '2025-07-04 22:38:02'),
(184, 'App\\Models\\User', 39, 'auth_token', '1e3b676133ebdef850471e702e7f493909fd02016f37e2bc769dd6a5da1b9dc1', '[\"*\"]', NULL, NULL, '2025-07-04 23:02:49', '2025-07-04 23:02:49'),
(186, 'App\\Models\\User', 39, 'auth_token', 'a4a7c2d167b071e11332c78627c05f6c2aae4903ad9521afac62afc789485ed0', '[\"*\"]', '2025-07-05 02:41:08', NULL, '2025-07-05 00:58:00', '2025-07-05 02:41:08'),
(187, 'App\\Models\\User', 43, 'auth_token', '40b51c3a540032b621e97d420b95af9632c4a600ad54f321d1b3895db30a3688', '[\"*\"]', '2025-07-05 21:46:06', NULL, '2025-07-05 19:28:53', '2025-07-05 21:46:06'),
(188, 'App\\Models\\User', 40, 'auth_token', 'd3f645d1b42702aaf53dd132ac9353e53a12cce469984235edacce778d238e03', '[\"*\"]', NULL, NULL, '2025-07-05 20:52:32', '2025-07-05 20:52:32'),
(189, 'App\\Models\\User', 40, 'auth_token', '00d02ca36e8f799e6dfaae8229c636e262f05d95669fad98a0cfbd7ca1b1158b', '[\"*\"]', '2025-07-05 22:10:56', NULL, '2025-07-05 20:56:09', '2025-07-05 22:10:56'),
(190, 'App\\Models\\User', 39, 'auth_token', '9af20e407d3a38e48f1abf3663fb9acbdd6fc3695a1d29c22cdf9ee877149c64', '[\"*\"]', NULL, NULL, '2025-07-05 21:27:16', '2025-07-05 21:27:16'),
(191, 'App\\Models\\User', 39, 'auth_token', '65bb20b244e0bc7bdbda99db70100754c712b6452bb4f7d4efd33a0f95a9f0f2', '[\"*\"]', NULL, NULL, '2025-07-05 21:27:16', '2025-07-05 21:27:16'),
(192, 'App\\Models\\User', 39, 'auth_token', '794bf822ace02d97edfca14f3f066b9e03b9e45a21dc31472468aeeb47751d6e', '[\"*\"]', '2025-07-05 21:39:25', NULL, '2025-07-05 21:27:50', '2025-07-05 21:39:25'),
(193, 'App\\Models\\User', 39, 'auth_token', 'c660da3b2f74d20ea2f38b90a1808916bf4e01902c13f3483e2e40cb78ad1726', '[\"*\"]', '2025-07-05 21:56:04', NULL, '2025-07-05 21:49:37', '2025-07-05 21:56:04'),
(194, 'App\\Models\\User', 39, 'auth_token', '4bcb090b4a0c2e0e2ce577f91783f05a6f3157b1124c1cc6017d899264d8ea79', '[\"*\"]', '2025-07-05 23:30:09', NULL, '2025-07-05 21:59:41', '2025-07-05 23:30:09'),
(195, 'App\\Models\\User', 39, 'auth_token', '428107bfd897a06a455ba9d58565e0d834e4b5199b07e11b661f261fa87f580b', '[\"*\"]', '2025-07-07 00:11:11', NULL, '2025-07-06 04:14:51', '2025-07-07 00:11:11'),
(196, 'App\\Models\\User', 38, 'auth_token', '12732bbba0510eec620b735d0a649ddb4b951f8e8487bb0df878eafd5225db78', '[\"*\"]', NULL, NULL, '2025-07-06 04:41:58', '2025-07-06 04:41:58'),
(197, 'App\\Models\\User', 38, 'auth_token', '4025ab09a47feb0f2a709b4598eafdf167e3c8e058afb2d195ea9e5221bc4e99', '[\"*\"]', '2025-07-06 05:05:30', NULL, '2025-07-06 04:43:27', '2025-07-06 05:05:30'),
(198, 'App\\Models\\User', 39, 'auth_token', 'bbb3a927444dbb75dc913f7961982da77fcdfbe563186e6da71e9c5db929cc6b', '[\"*\"]', '2025-07-07 02:34:32', NULL, '2025-07-07 00:12:42', '2025-07-07 02:34:32'),
(199, 'App\\Models\\User', 39, 'auth_token', '55236d6896967525004f5e475b035eecc104ad59c2128741244d4a9f4cad036b', '[\"*\"]', NULL, NULL, '2025-07-07 23:07:55', '2025-07-07 23:07:55'),
(200, 'App\\Models\\User', 39, 'auth_token', '8b76137756208dc8d7d4ca0d1d08e0f1399d4390b993e8aa9cbb07446dd903f8', '[\"*\"]', '2025-07-07 23:56:33', NULL, '2025-07-07 23:09:46', '2025-07-07 23:56:33'),
(201, 'App\\Models\\User', 39, 'auth_token', '98f485d9be11e924a47370d7fbd1a852a6c570d8f0cc0904b84783d0d7c4a7f4', '[\"*\"]', '2025-07-08 04:16:27', NULL, '2025-07-07 23:56:17', '2025-07-08 04:16:27'),
(202, 'App\\Models\\User', 40, 'auth_token', '5a9b5ada3089f4f5cf962ab2d37846465aed8f688d809792558ac42265d3deb0', '[\"*\"]', '2025-07-10 07:37:19', NULL, '2025-07-10 06:45:27', '2025-07-10 07:37:19'),
(203, 'App\\Models\\User', 40, 'auth_token', 'dca4b65f611d4ad5109ce56296ee3c41fc0ca5de9ce86bd5541396853db7c967', '[\"*\"]', '2025-07-10 09:50:44', NULL, '2025-07-10 07:37:39', '2025-07-10 09:50:44'),
(204, 'App\\Models\\User', 29, 'auth_token', '0900ee2cda07aac042cf66e448c6b976b8e663a29b85afccf96f61c8233b5379', '[\"*\"]', '2025-07-16 04:49:01', NULL, '2025-07-12 13:56:52', '2025-07-16 04:49:01'),
(205, 'App\\Models\\User', 39, 'auth_token', '89d4ae886d00279e0f1f1cba6863ab170bf1c3626c4d8c2f4e9fd97f27e6fff6', '[\"*\"]', '2025-07-12 21:55:20', NULL, '2025-07-12 20:51:12', '2025-07-12 21:55:20'),
(207, 'App\\Models\\User', 39, 'auth_token', 'aa9e3ff9830ff0500190fc07a9ec8fefa81d9f3307ff7d9fc1fd20d9fdafa68f', '[\"*\"]', '2025-07-14 02:22:31', NULL, '2025-07-14 01:16:38', '2025-07-14 02:22:31'),
(209, 'App\\Models\\User', 29, 'auth_token', 'bdb37e5fc57240764c7a3b725552dfef1b5b34a6171fd1f7a7cea4c7ab6ae6fa', '[\"*\"]', '2025-07-16 05:08:16', NULL, '2025-07-16 04:59:54', '2025-07-16 05:08:16'),
(210, 'App\\Models\\User', 39, 'auth_token', '02671a741ab1f021844c547bc9fada8a763147e789bb692299e3eaf05ed02fa1', '[\"*\"]', '2025-07-17 20:49:31', NULL, '2025-07-17 20:48:27', '2025-07-17 20:49:31'),
(212, 'App\\Models\\User', 39, 'auth_token', 'a0befdc662d3199cb1b4fffd8f97715b73d70d69ec7f841926595357403409ef', '[\"*\"]', '2025-07-19 02:27:56', NULL, '2025-07-19 02:26:33', '2025-07-19 02:27:56'),
(214, 'App\\Models\\User', 40, 'auth_token', '84480368dd2085576fc80ed3c6c3dfe8d3d4e4faa8e8bb05ab2faabcdd47463d', '[\"*\"]', NULL, NULL, '2025-07-24 00:06:45', '2025-07-24 00:06:45'),
(215, 'App\\Models\\User', 38, 'auth_token', 'c4179897315d9e2f9a6b025ebe302a737b7303a2ebda818111a2c0270b091977', '[\"*\"]', NULL, NULL, '2025-07-24 00:08:41', '2025-07-24 00:08:41'),
(216, 'App\\Models\\User', 38, 'auth_token', '49db94ca261b7179df6bd506778aaa7eb982981803cc607a92679142d35b91ba', '[\"*\"]', NULL, NULL, '2025-07-24 00:09:49', '2025-07-24 00:09:49'),
(217, 'App\\Models\\User', 38, 'auth_token', 'fc70fe8ce53b38f1e6a9be24ce3e16d6cee361b3dcbca34a52320a7a11b0a851', '[\"*\"]', NULL, NULL, '2025-07-24 00:12:04', '2025-07-24 00:12:04'),
(218, 'App\\Models\\User', 38, 'auth_token', '2063e87b8f7d02cdbf14dc1ecc834d6317e7c97235f7cd0c21f84253d7cef8b5', '[\"*\"]', NULL, NULL, '2025-07-24 00:19:23', '2025-07-24 00:19:23'),
(219, 'App\\Models\\User', 38, 'auth_token', '903ef84e18f14d8e20b15d4bec6ed62ae8a9f08e957995c54e89547c668a68fd', '[\"*\"]', '2025-07-24 01:29:32', NULL, '2025-07-24 00:32:22', '2025-07-24 01:29:32'),
(220, 'App\\Models\\User', 38, 'auth_token', '99b13ea292ac1fd0c16dfc79c176bf944c2e4ba3d1d1dd79a4761959f66273cd', '[\"*\"]', NULL, NULL, '2025-07-25 23:41:34', '2025-07-25 23:41:34'),
(221, 'App\\Models\\User', 38, 'auth_token', 'ccc6a2563afe6efdcc962b0203aa206b1ab5a1b9a434896ee0f07b92e857653c', '[\"*\"]', '2025-07-26 01:19:38', NULL, '2025-07-25 23:43:28', '2025-07-26 01:19:38'),
(222, 'App\\Models\\User', 10, 'auth_token', '23c30357572e48cd02018e42c903e8f567810db4b4932f82bf908a79fa91d855', '[\"*\"]', '2025-07-26 01:41:25', NULL, '2025-07-26 00:15:08', '2025-07-26 01:41:25'),
(223, 'App\\Models\\User', 39, 'auth_token', 'abfada5b0d631ad4f4b021817a7febaaa5cd77745f9e60e8e14b642094ba7a18', '[\"*\"]', '2025-07-26 00:24:30', NULL, '2025-07-26 00:23:06', '2025-07-26 00:24:30'),
(224, 'App\\Models\\User', 29, 'auth_token', '86a62e6de77a11627c19eb19933906bf3af55176c68288ff4d38dc2c035a1bce', '[\"*\"]', '2025-07-26 01:46:28', NULL, '2025-07-26 01:41:56', '2025-07-26 01:46:28'),
(227, 'App\\Models\\User', 7, 'auth_token', '38f800abfeda90b70a8fc9716ec7fe1efcd147a35730223717630987303041b3', '[\"*\"]', '2025-07-26 19:27:23', NULL, '2025-07-26 19:27:15', '2025-07-26 19:27:23'),
(229, 'App\\Models\\User', 42, 'auth_token', '7d7cb2b21b9aa03ab9257d89a6c74b0793a0d4c672e849f4afa78a8c2ae9d63d', '[\"*\"]', '2025-07-26 19:36:59', NULL, '2025-07-26 19:36:09', '2025-07-26 19:36:59'),
(230, 'App\\Models\\User', 7, 'auth_token', '2b6bbc4c86c53d3bb66e204984e9b430f77913c16d7a17d57f58fd0c76c60e33', '[\"*\"]', '2025-07-26 19:40:39', NULL, '2025-07-26 19:40:26', '2025-07-26 19:40:39'),
(231, 'App\\Models\\User', 38, 'auth_token', '4ac3220e6be54089789141f68d7b1fc21cdc3a9db04d3dc09ab7ce687f4ef897', '[\"*\"]', NULL, NULL, '2025-07-26 19:58:44', '2025-07-26 19:58:44'),
(232, 'App\\Models\\User', 29, 'auth_token', '5eeaab561a87cd509531002ab40d2aeaea601b1c875d251c3ffd668655215572', '[\"*\"]', '2025-07-26 20:10:12', NULL, '2025-07-26 20:10:01', '2025-07-26 20:10:12'),
(233, 'App\\Models\\User', 39, 'auth_token', '6c9dce6769a393a7ca02a934a862b27925bab932becaed2b27c7fa7a0e6860ac', '[\"*\"]', '2025-07-29 00:42:30', NULL, '2025-07-29 00:41:59', '2025-07-29 00:42:30'),
(234, 'App\\Models\\User', 39, 'auth_token', '04b66ae9b52e89d0e99d64a7a226892da8b52410e4f055e1fd43dc30f794005b', '[\"*\"]', '2025-07-31 01:47:26', NULL, '2025-07-30 23:14:25', '2025-07-31 01:47:26'),
(235, 'App\\Models\\User', 39, 'auth_token', '4d89ea7362b2dbbed958cb26943b2ea7b4163a185592ec38f5361c11131d7df3', '[\"*\"]', '2025-07-31 11:59:54', NULL, '2025-07-31 07:41:17', '2025-07-31 11:59:54'),
(236, 'App\\Models\\User', 39, 'auth_token', '9c6b48538e0d965fcc44248d0f8b2a47fae7575cdbc7c052a1977b03f682da61', '[\"*\"]', '2025-07-31 23:04:44', NULL, '2025-07-31 22:20:05', '2025-07-31 23:04:44'),
(239, 'App\\Models\\User', 29, 'auth_token', '6195680c279b579876a0ffe5b699c8014ce698be5a8faa0b0b46d6b7696da1a4', '[\"*\"]', '2025-08-03 16:31:57', NULL, '2025-08-03 07:26:37', '2025-08-03 16:31:57'),
(240, 'App\\Models\\User', 29, 'auth_token', 'bd879e0a216edb1e494225d878cf002dc9d1588bc54741e9ddac24ed2b69ae43', '[\"*\"]', '2025-08-03 19:44:19', NULL, '2025-08-03 19:24:03', '2025-08-03 19:44:19'),
(242, 'App\\Models\\User', 7, 'auth_token', 'd7c85fa5e798b5eec80ecc029c5cca14080483fa2b930699a005575b5bd7e563', '[\"*\"]', '2025-08-03 19:29:19', NULL, '2025-08-03 19:27:35', '2025-08-03 19:29:19'),
(244, 'App\\Models\\User', 7, 'auth_token', '2100f1cf4fc8e91abcfd9d0230351da93bbd9d36b7a933847ed1e7194599896a', '[\"*\"]', '2025-08-03 19:43:00', NULL, '2025-08-03 19:42:47', '2025-08-03 19:43:00'),
(245, 'App\\Models\\User', 7, 'auth_token', 'dc455ca7c72f78dab0bf605879a7e8b3a8b0e003d2147e17a8a857bbdafcfaa7', '[\"*\"]', '2025-08-03 19:43:35', NULL, '2025-08-03 19:43:32', '2025-08-03 19:43:35'),
(246, 'App\\Models\\User', 7, 'auth_token', 'ab9209ecde1af4bdeb4d0d000ead44329114df08bf5d994a21f5d01dba676d34', '[\"*\"]', '2025-08-03 19:44:43', NULL, '2025-08-03 19:44:35', '2025-08-03 19:44:43'),
(249, 'App\\Models\\User', 38, 'auth_token', 'ab9fb170b9d92d70f0eb7626404f87537e50ad4f8e52043ce27a3abf11664794', '[\"*\"]', NULL, NULL, '2025-08-03 20:03:10', '2025-08-03 20:03:10'),
(250, 'App\\Models\\User', 38, 'auth_token', '1cec273ef4889d8879769e3d30fadd477821fec5faf76efe0de1cfc34295bc4f', '[\"*\"]', NULL, NULL, '2025-08-03 20:04:19', '2025-08-03 20:04:19'),
(251, 'App\\Models\\User', 40, 'auth_token', '4740cbb28cceeec00319fb785b2c6f452e0d98061770a2e792da624fcec8ae5c', '[\"*\"]', NULL, NULL, '2025-08-03 20:07:28', '2025-08-03 20:07:28'),
(256, 'App\\Models\\User', 29, 'auth_token', '048fa822f90215cbd06ea315343f476cdad59d6237feb6af1201985b22c4bb62', '[\"*\"]', '2025-08-18 04:10:43', NULL, '2025-08-03 21:16:11', '2025-08-18 04:10:43'),
(257, 'App\\Models\\User', 38, 'auth_token', '8763cb8e0fcf1bc66f09b9c6830e98d356a3e3626d478b009a8989ef0801d37e', '[\"*\"]', '2025-08-03 22:19:46', NULL, '2025-08-03 21:49:59', '2025-08-03 22:19:46'),
(258, 'App\\Models\\User', 42, 'auth_token', '65cad8d00d16d84fff382af465a62db80cc882d33e5cbdc9716f035b9d14fdf1', '[\"*\"]', '2025-08-03 21:59:29', NULL, '2025-08-03 21:51:56', '2025-08-03 21:59:29'),
(261, 'App\\Models\\User', 46, 'auth_token', 'e12a2aabf2557ffc3f77fc4e502a0dd8bc6692de56a4494c0bf5c81ab45f1264', '[\"*\"]', NULL, NULL, '2025-08-03 22:09:12', '2025-08-03 22:09:12'),
(262, 'App\\Models\\User', 46, 'auth_token', '8fe033ee51475192a55da2dba12f765a8a6232285e3c3d4e3361cc2e7fd5fdb8', '[\"*\"]', '2025-08-03 23:11:36', NULL, '2025-08-03 22:09:39', '2025-08-03 23:11:36'),
(263, 'App\\Models\\User', 40, 'auth_token', '7745ee9fce47db522d446de083428e24ef35db529191730e203bad9b29a34c91', '[\"*\"]', '2025-08-03 22:44:41', NULL, '2025-08-03 22:41:31', '2025-08-03 22:44:41'),
(264, 'App\\Models\\User', 7, 'auth_token', 'd16dc6efb04cd92d6f7a55b9e166308b12eccdf63df6bae8266bd1495ea739e7', '[\"*\"]', '2025-08-03 23:11:35', NULL, '2025-08-03 22:52:11', '2025-08-03 23:11:35'),
(266, 'App\\Models\\User', 38, 'auth_token', '17a1ff6cabd2948d94041240cc0eb84180e4d638a311d4576930f4f10fb3dfc7', '[\"*\"]', NULL, NULL, '2025-08-05 23:15:23', '2025-08-05 23:15:23'),
(267, 'App\\Models\\User', 38, 'auth_token', 'f724c073605b7a80e9fb61a2051b76214ff8000b1c33d59cd0f3377f61ff4a0b', '[\"*\"]', '2025-08-06 06:08:49', NULL, '2025-08-05 23:17:26', '2025-08-06 06:08:49'),
(268, 'App\\Models\\User', 39, 'auth_token', 'c43ceb140d78b01920f3b5ee07906dbef114d6451dcddf1f393bb725daaa45bc', '[\"*\"]', NULL, NULL, '2025-08-08 00:05:04', '2025-08-08 00:05:04'),
(270, 'App\\Models\\User', 39, 'auth_token', '0ee23a88185a386b43ce8f2e74db93fe4f740bacf8d21b7d73c1a048bacfe06a', '[\"*\"]', '2025-08-08 03:24:30', NULL, '2025-08-08 00:08:47', '2025-08-08 03:24:30'),
(271, 'App\\Models\\User', 38, 'auth_token', '60c795550962d76bce9bf294f6fecf268ac06dd2543b04f45acbcd0f15301212', '[\"*\"]', NULL, NULL, '2025-08-08 00:19:24', '2025-08-08 00:19:24'),
(272, 'App\\Models\\User', 38, 'auth_token', '11446b8da8b30e53909aac01baf7a9ac270c9eb017159a077c8c8f44582aa6bd', '[\"*\"]', '2025-08-08 02:17:37', NULL, '2025-08-08 00:21:45', '2025-08-08 02:17:37'),
(273, 'App\\Models\\User', 46, 'auth_token', 'a04c33236059271eb924494fbb4e31ceaf8099542b39fdceedc65aa9471e5bc7', '[\"*\"]', '2025-08-08 08:38:50', NULL, '2025-08-08 00:23:43', '2025-08-08 08:38:50'),
(274, 'App\\Models\\User', 29, 'auth_token', 'b00bd0eea167fad55ce1c3306e08e626413cb286868ce8d89103d18dfc1a8ed9', '[\"*\"]', '2025-08-16 21:32:06', NULL, '2025-08-08 00:26:54', '2025-08-16 21:32:06'),
(276, 'App\\Models\\User', 29, 'auth_token', 'c5f424053b988817676440e0c7f5c1d4ee3913e491563df11b22753c066fa446', '[\"*\"]', '2025-08-18 01:47:49', NULL, '2025-08-08 03:10:38', '2025-08-18 01:47:49'),
(277, 'App\\Models\\User', 38, 'auth_token', 'c9dd3f36a71783d748ee21acda0b98473c06967c57a4c8e00bba466ebc1a3c4c', '[\"*\"]', '2025-08-08 04:25:59', NULL, '2025-08-08 04:08:53', '2025-08-08 04:25:59'),
(278, 'App\\Models\\User', 38, 'auth_token', '6db02bb0f9768b62f7c94b49d8c0c4f192c9fd126b0e8404397c49b71173d0fd', '[\"*\"]', NULL, NULL, '2025-08-08 04:29:08', '2025-08-08 04:29:08'),
(279, 'App\\Models\\User', 38, 'auth_token', '15a0120e578914bfcbaf123d6654b3d7903f7e41ce23b4cbefc17d11118ad2a3', '[\"*\"]', '2025-08-08 04:34:42', NULL, '2025-08-08 04:30:00', '2025-08-08 04:34:42'),
(280, 'App\\Models\\User', 38, 'auth_token', 'c532210d835a1cb681651219e4a9b0afde1ea961355d6f9a2c2c6ef9ebef4d2e', '[\"*\"]', '2025-08-08 22:10:44', NULL, '2025-08-08 22:06:42', '2025-08-08 22:10:44'),
(281, 'App\\Models\\User', 38, 'auth_token', '35edb7d0e884670b4e7fda7d1af2899d2a7ee633add095242168d10765f6c9b8', '[\"*\"]', NULL, NULL, '2025-08-09 02:08:55', '2025-08-09 02:08:55'),
(282, 'App\\Models\\User', 38, 'auth_token', '7111c28715f42addd602ec5eb59939a7bf6dfb11a5db40200d1d25bd00d2ad61', '[\"*\"]', '2025-08-09 02:27:44', NULL, '2025-08-09 02:23:39', '2025-08-09 02:27:44'),
(284, 'App\\Models\\User', 46, 'auth_token', 'f9d9ada70380ff5543d681219575d3a9740ba717bb3cdb346d5305cf01af1a56', '[\"*\"]', '2025-08-09 23:30:00', NULL, '2025-08-09 23:29:59', '2025-08-09 23:30:00'),
(287, 'App\\Models\\User', 38, 'auth_token', 'f2925f75ffa3b89d42110b3143671937f2989e092d7494055daee90a0cc0d95e', '[\"*\"]', '2025-08-10 02:22:52', NULL, '2025-08-10 02:18:42', '2025-08-10 02:22:52'),
(288, 'App\\Models\\User', 38, 'auth_token', '0ab442b49135eb55d821c0dda70262e853aad92b4f42176d90aa90d20adcbb5e', '[\"*\"]', '2025-08-12 04:04:20', NULL, '2025-08-12 03:02:37', '2025-08-12 04:04:20'),
(290, 'App\\Models\\User', 7, 'auth_token', 'd7e6a895a955bc92ac89e4b087f0b571c4571d0830fbcf74cfa20415ab61ccb5', '[\"*\"]', '2025-08-12 03:31:24', NULL, '2025-08-12 03:22:47', '2025-08-12 03:31:24'),
(291, 'App\\Models\\User', 39, 'auth_token', '95eccca37f20391e8260af9eb06232b3d599170a2aaaa7657f5f2821d5d15130', '[\"*\"]', '2025-08-12 23:44:12', NULL, '2025-08-12 22:52:35', '2025-08-12 23:44:12'),
(292, 'App\\Models\\User', 38, 'auth_token', 'cddf603932ba766e613bb1decd070df7b5dd904aa060bee609021c91bf7fa58a', '[\"*\"]', NULL, NULL, '2025-08-12 23:04:42', '2025-08-12 23:04:42'),
(293, 'App\\Models\\User', 38, 'auth_token', 'cecc2dcd2bf5c0d2a25973821360ef8f1a67a848bacc695f51ee1d5b55ee2a8e', '[\"*\"]', NULL, NULL, '2025-08-12 23:06:17', '2025-08-12 23:06:17'),
(294, 'App\\Models\\User', 40, 'auth_token', '3f49c7e807d47b5346ad92ff9acafbc6e13ad6a4b33b1973c4f43a0a07a73258', '[\"*\"]', '2025-08-12 23:28:52', NULL, '2025-08-12 23:06:27', '2025-08-12 23:28:52'),
(296, 'App\\Models\\User', 39, 'auth_token', '3a1d3bb6df8d9819706e441236573a70604c1ecb1dfa2385053506390621fa75', '[\"*\"]', '2025-08-12 23:44:40', NULL, '2025-08-12 23:44:06', '2025-08-12 23:44:40'),
(297, 'App\\Models\\User', 39, 'auth_token', '832b4af306fabfcfbdebe6c91c475aee98868e320b856d32892cd29103dc7223', '[\"*\"]', '2025-08-12 23:47:35', NULL, '2025-08-12 23:47:05', '2025-08-12 23:47:35'),
(298, 'App\\Models\\User', 39, 'auth_token', '6f4f6660470910e8aeed9ba32371ee325ca1aafc2efaa2801b5e1852981605a4', '[\"*\"]', NULL, NULL, '2025-08-12 23:48:18', '2025-08-12 23:48:18'),
(299, 'App\\Models\\User', 39, 'auth_token', 'bc94c5a20b9c0034f244ec751f24242b04a973a40e0454af1f906f9518dbdfbf', '[\"*\"]', '2025-08-12 23:58:34', NULL, '2025-08-12 23:49:26', '2025-08-12 23:58:34'),
(301, 'App\\Models\\User', 38, 'auth_token', '3a3ffa96ff6df8eb7e817ccc7f4dc5d45bab69a001cf3ac12537a2aa89258335', '[\"*\"]', '2025-08-13 00:02:12', NULL, '2025-08-12 23:58:16', '2025-08-13 00:02:12'),
(302, 'App\\Models\\User', 39, 'auth_token', '69ce828d805e2d2b205af2d2005a2bf8389672daac008f996b76257ecc566dc3', '[\"*\"]', '2025-08-13 00:46:48', NULL, '2025-08-13 00:09:09', '2025-08-13 00:46:48'),
(303, 'App\\Models\\User', 39, 'auth_token', '1fde6fb62b3995e601cc7b9f2c9c72b5155428cdbc0161a10f2e6d9a9665eccc', '[\"*\"]', '2025-08-13 00:53:20', NULL, '2025-08-13 00:52:50', '2025-08-13 00:53:20'),
(304, 'App\\Models\\User', 39, 'auth_token', '20caabc8aec14c4a525a7baac8ba216148b1efd1d4caa73491ca5819c267c9f0', '[\"*\"]', '2025-08-13 01:51:00', NULL, '2025-08-13 00:54:59', '2025-08-13 01:51:00'),
(305, 'App\\Models\\User', 39, 'auth_token', 'e6c4ee1c7c1c5c3973c43e8eb692898b4f9dbc552aa2979cb3f65dfeae9e87e1', '[\"*\"]', '2025-08-13 10:48:01', NULL, '2025-08-13 09:05:14', '2025-08-13 10:48:01'),
(306, 'App\\Models\\User', 39, 'auth_token', '573bcf6d7901bdae1768bc5ef49787e08eeaeb9a4ffb48686bcfa3bcd6e9a642', '[\"*\"]', NULL, NULL, '2025-08-13 23:45:08', '2025-08-13 23:45:08'),
(307, 'App\\Models\\User', 39, 'auth_token', '6b69f6757a4ad54fd2e3d97eb7c533cb3b4f59df55dfb8cb9fbfc7e9a5571149', '[\"*\"]', NULL, NULL, '2025-08-13 23:45:08', '2025-08-13 23:45:08'),
(308, 'App\\Models\\User', 39, 'auth_token', '4a6a09f5b2205eef4a7919973b34fcbebc4ee4ed946f28ac0620816f8047f9eb', '[\"*\"]', '2025-08-13 23:47:27', NULL, '2025-08-13 23:45:17', '2025-08-13 23:47:27'),
(309, 'App\\Models\\User', 39, 'auth_token', 'e8cd84d63bec9426e78dc1b6a10dd7c1dc3478b0d1871788742a4ea9f46fa567', '[\"*\"]', '2025-08-14 04:00:32', NULL, '2025-08-14 03:20:28', '2025-08-14 04:00:32'),
(310, 'App\\Models\\User', 39, 'auth_token', '69e01e1948dde8783b6501912863eed7438cbbefa06b4a99f1805adddaecf1af', '[\"*\"]', NULL, NULL, '2025-08-15 02:50:40', '2025-08-15 02:50:40'),
(311, 'App\\Models\\User', 39, 'auth_token', '428232052fd9d68d383185a5d4fdd7ded5af71f5b417ba57edfcf49421dda4ca', '[\"*\"]', '2025-08-15 02:53:46', NULL, '2025-08-15 02:51:46', '2025-08-15 02:53:46'),
(312, 'App\\Models\\User', 39, 'auth_token', '76fd154c1dee55e82053d7b873b78b9f5886f8157a37c1d779d583ff73e20b10', '[\"*\"]', '2025-08-15 04:33:09', NULL, '2025-08-15 02:59:25', '2025-08-15 04:33:09'),
(313, 'App\\Models\\User', 40, 'auth_token', 'fffe6cc613aab1cc3d9f7119041c77b3fde23344405f2f5e10af2e0eed97fce7', '[\"*\"]', NULL, NULL, '2025-08-15 04:01:48', '2025-08-15 04:01:48'),
(316, 'App\\Models\\User', 39, 'auth_token', 'e8260b1ad1135fd29f37226f9562756480ee518a35e4f95fdc7cdc8b74efa953', '[\"*\"]', NULL, NULL, '2025-08-15 04:33:07', '2025-08-15 04:33:07'),
(317, 'App\\Models\\User', 39, 'auth_token', '3dfc5d20603a451074a9099cd7763509c6bb28709629815ebb2cd6637de4b63d', '[\"*\"]', NULL, NULL, '2025-08-15 04:33:18', '2025-08-15 04:33:18'),
(318, 'App\\Models\\User', 39, 'auth_token', 'cb379408d8cb6eb5a8db48e9affb73e266329377144acbc39811550662e33ced', '[\"*\"]', '2025-08-15 05:07:53', NULL, '2025-08-15 04:41:43', '2025-08-15 05:07:53'),
(319, 'App\\Models\\User', 40, 'auth_token', '0438927d9ede3611abe610f9e03fce0bb8951887445fce33a5531bd274d9e33c', '[\"*\"]', '2025-08-15 05:08:18', NULL, '2025-08-15 05:06:44', '2025-08-15 05:08:18'),
(320, 'App\\Models\\User', 40, 'auth_token', '0bf0a3b2fbb711fe2e9cde4c92704adf2daec276d9f63838f24bcdaa03b9fe3c', '[\"*\"]', NULL, NULL, '2025-08-17 23:12:27', '2025-08-17 23:12:27'),
(321, 'App\\Models\\User', 40, 'auth_token', '388a5cbf28871014e25e9981737c354c4ccfee5421f393a1a5aa25c65cceadd2', '[\"*\"]', '2025-08-18 01:03:25', NULL, '2025-08-17 23:13:04', '2025-08-18 01:03:25'),
(322, 'App\\Models\\User', 29, 'auth_token', 'b9f3ee8ca49b5806c759ca1d50765102227289807c320d5147dd8d86bfe0d92d', '[\"*\"]', '2025-08-18 01:05:07', NULL, '2025-08-17 23:38:11', '2025-08-18 01:05:07'),
(323, 'App\\Models\\User', 29, 'auth_token', '38ceb26814c8035a18833241bfb20c519694f9d2d1175f97710564e1c6e64570', '[\"*\"]', NULL, NULL, '2025-08-18 01:05:24', '2025-08-18 01:05:24'),
(324, 'App\\Models\\User', 29, 'auth_token', 'ac7dd612e0068647d7cc2c1a7d4e26557810f1338e67755bb0b6e613351031f5', '[\"*\"]', NULL, NULL, '2025-08-18 01:07:32', '2025-08-18 01:07:32'),
(325, 'App\\Models\\User', 29, 'auth_token', '11666c01cd5ecd7b2eed6d8741f6ef9cc9e3a96b5a5569877b6cad0c3ffcfc76', '[\"*\"]', NULL, NULL, '2025-08-18 01:14:04', '2025-08-18 01:14:04'),
(326, 'App\\Models\\User', 29, 'auth_token', '085c727013621ca69ec225d62861ea8c3453470011d21045b48bea71f63be31a', '[\"*\"]', '2025-08-18 01:37:10', NULL, '2025-08-18 01:29:43', '2025-08-18 01:37:10'),
(327, 'App\\Models\\User', 10, 'auth_token', '22567c048d233b2df19fa2e3e897457bf3a90588593363845e962b8f9ae114e8', '[\"*\"]', '2025-08-18 01:43:27', NULL, '2025-08-18 01:42:59', '2025-08-18 01:43:27'),
(328, 'App\\Models\\User', 46, 'auth_token', '64d9eaa24db5fe6d67b54e7ca23a57aeab2852a0884d5860b94de2975dbaf6c9', '[\"*\"]', '2025-08-18 02:25:50', NULL, '2025-08-18 01:46:53', '2025-08-18 02:25:50'),
(329, 'App\\Models\\User', 38, 'auth_token', '0afccca224e8a508f8650d09919745a7b734b04acb6592dc549ff936c3c67a38', '[\"*\"]', '2025-08-18 02:01:45', NULL, '2025-08-18 01:55:44', '2025-08-18 02:01:45'),
(330, 'App\\Models\\User', 38, 'auth_token', '12719acb2d08bafbab80c35c6fb54cc9caa186b6859dfcd0a3950f9822a0dc01', '[\"*\"]', '2025-08-18 02:29:56', NULL, '2025-08-18 02:21:42', '2025-08-18 02:29:56'),
(332, 'App\\Models\\User', 10, 'auth_token', 'c9e5c7463d0e17846053622fa168af60885ae25bdfeb313c30e50f75e0536ec3', '[\"*\"]', '2025-08-18 04:14:54', NULL, '2025-08-18 02:51:03', '2025-08-18 04:14:54'),
(333, 'App\\Models\\User', 38, 'auth_token', 'ae32c415a5546fee1675517c5faed786ad9746e5caf70467974875cb0cefe18d', '[\"*\"]', '2025-08-18 04:17:27', NULL, '2025-08-18 02:59:08', '2025-08-18 04:17:27'),
(334, 'App\\Models\\User', 7, 'auth_token', 'f619e7369364784eeb0cd46084728143ce9520679caa8e72b1d31a4d1477944a', '[\"*\"]', '2025-08-18 03:49:49', NULL, '2025-08-18 03:42:35', '2025-08-18 03:49:49'),
(336, 'App\\Models\\User', 41, 'auth_token', 'a277309459002b24b6c5bb2003d526e1d90781842a722a851a90858d4a9b9373', '[\"*\"]', '2025-08-19 01:04:33', NULL, '2025-08-18 04:15:09', '2025-08-19 01:04:33'),
(337, 'App\\Models\\User', 39, 'auth_token', 'b4a5d7ff8a8992562d58d3b187de33faa503acac6ff2b5ea34158f03a1c5785d', '[\"*\"]', '2025-08-18 20:43:43', NULL, '2025-08-18 19:09:23', '2025-08-18 20:43:43'),
(338, 'App\\Models\\User', 39, 'auth_token', '0b56adee852ba89cba01b5c02e4d000a0abd6352f9b5d41df0eaf160e0a5cf0b', '[\"*\"]', '2025-08-18 20:49:49', NULL, '2025-08-18 20:47:58', '2025-08-18 20:49:49'),
(339, 'App\\Models\\User', 40, 'auth_token', '44e0d9841879a9cf34119d8892cc7af05a28ce0fb160a8e1b7af863594ea2b49', '[\"*\"]', '2025-08-19 07:27:20', NULL, '2025-08-19 02:48:12', '2025-08-19 07:27:20'),
(340, 'App\\Models\\User', 29, 'auth_token', '40eb39d3a9167ae4404a6fadb88c40e22d8a82101943c2058a859e61c7eef722', '[\"*\"]', '2025-08-20 02:17:19', NULL, '2025-08-19 23:11:11', '2025-08-20 02:17:19'),
(343, 'App\\Models\\User', 40, 'auth_token', '2fbe443690a7f832f091e143f0e875fa2821139b8b13c57f7cef4f498eeb2444', '[\"*\"]', '2025-08-20 01:38:52', NULL, '2025-08-20 01:24:22', '2025-08-20 01:38:52'),
(344, 'App\\Models\\User', 40, 'auth_token', '9938689be6ece6c43bddfde677557a08c3882e78215ff44186dcfc99dc5847bb', '[\"*\"]', '2025-08-20 04:17:56', NULL, '2025-08-20 01:42:10', '2025-08-20 04:17:56'),
(345, 'App\\Models\\User', 46, 'auth_token', 'c4275758dad17b3ea7e98d53ff2b44eba13ff4892140797a22cafafa8dbc1264', '[\"*\"]', '2025-08-21 02:01:46', NULL, '2025-08-20 04:16:55', '2025-08-21 02:01:46'),
(346, 'App\\Models\\User', 10, 'auth_token', '14269563684e97713e6a9d3bde71fa772505db876d493da7d2f76af61c42aed0', '[\"*\"]', '2025-08-21 01:24:34', NULL, '2025-08-21 01:14:01', '2025-08-21 01:24:34'),
(347, 'App\\Models\\User', 38, 'auth_token', '5898ca8ae403f6c4652c77a4125dc511a8e06cc3fe10eae5e49e292c0de704ec', '[\"*\"]', '2025-08-21 01:37:21', NULL, '2025-08-21 01:26:40', '2025-08-21 01:37:21'),
(348, 'App\\Models\\User', 47, 'auth_token', '92fdcb89710a79feeb6e374cc40fee3b4a559ccfbdbb2464f7b8ebc0cecfaa0c', '[\"*\"]', NULL, NULL, '2025-08-21 01:53:15', '2025-08-21 01:53:15'),
(349, 'App\\Models\\User', 47, 'auth_token', 'fc29a9e98c96d138fc9ee61b43b18fc74e267a9086f1ee758f613c90fc1bf115', '[\"*\"]', NULL, NULL, '2025-08-21 01:56:53', '2025-08-21 01:56:53'),
(351, 'App\\Models\\User', 39, 'auth_token', 'b1dc73da63e052671ff58ee6fab43d0e46ce82ca14c576e76ffc3c9cfb7d3386', '[\"*\"]', NULL, NULL, '2025-08-21 03:40:52', '2025-08-21 03:40:52'),
(352, 'App\\Models\\User', 39, 'auth_token', 'c1c583d8f47eab52022c45ce327f076201952e34cbc7f78ef23da8f8658b2048', '[\"*\"]', '2025-08-21 03:41:03', NULL, '2025-08-21 03:40:52', '2025-08-21 03:41:03'),
(353, 'App\\Models\\User', 39, 'auth_token', 'ecd29e2d4b11c9289f91b2720bd6b0f5b17e82d4bd5f64b19bcec7b600d8311f', '[\"*\"]', '2025-08-21 03:41:34', NULL, '2025-08-21 03:41:20', '2025-08-21 03:41:34'),
(354, 'App\\Models\\User', 29, 'auth_token', '5e27dc873bbbdc5d051f55eee68a7c79e592d4e9198024cbeb7a8b2cd2c6d159', '[\"*\"]', NULL, NULL, '2025-08-21 04:09:22', '2025-08-21 04:09:22'),
(356, 'App\\Models\\User', 39, 'auth_token', '3bab28bdec420ae5a4304c05c48b1ee2bd46d5988c8c571737ccd75b564c83bb', '[\"*\"]', NULL, NULL, '2025-08-21 04:25:33', '2025-08-21 04:25:33'),
(360, 'App\\Models\\User', 39, 'auth_token', 'e201632d716e07aba454c81e1d75e9453780f2b4a51373c35a3b0d5781bb1a63', '[\"*\"]', '2025-08-21 05:26:10', NULL, '2025-08-21 05:09:06', '2025-08-21 05:26:10'),
(361, 'App\\Models\\User', 41, 'auth_token', 'ae563625204e3acb71fe591f3b330608c8b3013543763e682b0f1132383261d6', '[\"*\"]', '2025-08-21 06:10:25', NULL, '2025-08-21 05:12:21', '2025-08-21 06:10:25'),
(362, 'App\\Models\\User', 38, 'auth_token', 'd40ed19a46626ccb8101cef0891725b7ec491e7a99871aa916cd6983ee486466', '[\"*\"]', '2025-08-21 05:21:30', NULL, '2025-08-21 05:20:02', '2025-08-21 05:21:30'),
(363, 'App\\Models\\User', 39, 'auth_token', '234b301d40dc378acb5bf2d6881f1d5c2fbe078cec229042afa1076a5e73c50b', '[\"*\"]', '2025-08-21 13:09:51', NULL, '2025-08-21 06:15:08', '2025-08-21 13:09:51'),
(364, 'App\\Models\\User', 10, 'auth_token', '7b194b5c8e9cfe62e266029622c26aabc231c7a89ed9702c9765fc80d4218306', '[\"*\"]', NULL, NULL, '2025-08-21 06:34:58', '2025-08-21 06:34:58'),
(365, 'App\\Models\\User', 10, 'auth_token', '167337130b97312336e9d103c7101e070abc8870fca633e4401b17525ba9c973', '[\"*\"]', NULL, NULL, '2025-08-21 06:35:29', '2025-08-21 06:35:29'),
(366, 'App\\Models\\User', 38, 'auth_token', '677c178cae64459595ee541466f2c077d4c7c8a14cefd4874bf8f46ddfd0790d', '[\"*\"]', NULL, NULL, '2025-08-21 09:50:58', '2025-08-21 09:50:58'),
(367, 'App\\Models\\User', 40, 'auth_token', 'b4c2588c8a4dd0844e90448596bb412ade4bc686056fd29cbac2499108fc13c5', '[\"*\"]', '2025-08-21 11:25:45', NULL, '2025-08-21 09:51:52', '2025-08-21 11:25:45'),
(368, 'App\\Models\\User', 39, 'auth_token', '5f869c8cf8acc5105fd85140661ad83dab03a176612aa80b90f81663d4d97ae2', '[\"*\"]', '2025-08-22 04:28:53', NULL, '2025-08-21 23:49:05', '2025-08-22 04:28:53'),
(370, 'App\\Models\\User', 29, 'auth_token', '0c1026f8150469161656269c5621ebf26cc8e6648b1fc24558662e4e10096419', '[\"*\"]', NULL, NULL, '2025-08-22 01:29:55', '2025-08-22 01:29:55'),
(372, 'App\\Models\\User', 29, 'auth_token', '8c9b8ec8703a7ed1fc1b03fec2022cfe8410494d61bef332ea83bd814a306f81', '[\"*\"]', '2025-08-22 01:38:41', NULL, '2025-08-22 01:34:38', '2025-08-22 01:38:41'),
(373, 'App\\Models\\User', 29, 'auth_token', 'a595b5a076f2587cb163ae274ac247a17556e001ee6e05fa4522898efc3f4a1e', '[\"*\"]', NULL, NULL, '2025-08-22 01:42:05', '2025-08-22 01:42:05'),
(374, 'App\\Models\\User', 10, 'auth_token', 'ac6c5af0593255bea3f357341be3c1c74768af44664d9b668ff4778221619b0e', '[\"*\"]', NULL, NULL, '2025-08-22 01:43:56', '2025-08-22 01:43:56'),
(375, 'App\\Models\\User', 29, 'auth_token', '427fc4e2760d45b1ad64034cf4bcb4724fbf148ac05920e291bb99a1a672bdc2', '[\"*\"]', '2025-08-22 01:47:10', NULL, '2025-08-22 01:47:05', '2025-08-22 01:47:10');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(376, 'App\\Models\\User', 29, 'auth_token', '5813c053ff5782fd2d6913b8bf574cb1e34874f804ff7add7696e500f6403265', '[\"*\"]', '2025-08-22 03:41:55', NULL, '2025-08-22 01:49:29', '2025-08-22 03:41:55'),
(377, 'App\\Models\\User', 29, 'auth_token', '636b2ece5dc62ee2eb53733f02293272911e3c94f33ec2af47ec7f604ad74111', '[\"*\"]', '2025-08-22 03:40:37', NULL, '2025-08-22 03:35:09', '2025-08-22 03:40:37'),
(378, 'App\\Models\\User', 29, 'auth_token', '37e806eeb586709c426ceda233467faaa9edc9aa9dd1a3d8166e1e2b2986528e', '[\"*\"]', NULL, NULL, '2025-08-22 03:42:00', '2025-08-22 03:42:00'),
(379, 'App\\Models\\User', 48, 'auth_token', '45c2dca2d8ac702c13b69ff0124a7fd9a2d92e715c5fb52f3b8cff807b58d8bd', '[\"*\"]', NULL, NULL, '2025-08-22 03:42:48', '2025-08-22 03:42:48'),
(380, 'App\\Models\\User', 48, 'auth_token', '7b0b1256ba1c593ebe4ef2959bf43eaccd37c4242dfce4d434e2903df594a8d1', '[\"*\"]', NULL, NULL, '2025-08-22 03:43:51', '2025-08-22 03:43:51'),
(381, 'App\\Models\\User', 48, 'auth_token', 'a2b4b8e1592fe715abced0c11eeb4b02fe47c72237d11e4341ed21ccd3c1951c', '[\"*\"]', '2025-08-22 03:47:21', NULL, '2025-08-22 03:46:54', '2025-08-22 03:47:21'),
(382, 'App\\Models\\User', 29, 'auth_token', 'c969b2aa65f27e10e6c46abc14f2956dc1450baeceb6c327120b8545934ff57e', '[\"*\"]', '2025-08-22 03:57:14', NULL, '2025-08-22 03:49:07', '2025-08-22 03:57:14'),
(383, 'App\\Models\\User', 47, 'auth_token', '3b403ad4235e94855513946f670fce700fbe6fcc5dbdab73b1b160ad258f1eec', '[\"*\"]', '2025-08-22 04:08:22', NULL, '2025-08-22 03:59:34', '2025-08-22 04:08:22'),
(384, 'App\\Models\\User', 10, 'auth_token', '81129d5bb7bd72f85d73ddff5d4ff99e2ac53c3bd03653fec8fc43bd10778861', '[\"*\"]', '2025-08-22 04:09:41', NULL, '2025-08-22 04:09:14', '2025-08-22 04:09:41'),
(385, 'App\\Models\\User', 29, 'auth_token', 'b61e2e6940734e95e906001c6ab3774a5010e41515e8fe8a3c07b4d88d474302', '[\"*\"]', '2025-08-22 04:12:04', NULL, '2025-08-22 04:10:04', '2025-08-22 04:12:04'),
(386, 'App\\Models\\User', 47, 'auth_token', 'b0d8d643e5251f7d3c29b0505ca4af88b4e72277f324bafcd16b618b691c55e3', '[\"*\"]', '2025-08-22 04:27:49', NULL, '2025-08-22 04:12:13', '2025-08-22 04:27:49'),
(387, 'App\\Models\\User', 29, 'auth_token', 'aed77d7f528d45a7d9b3f0daad3701c29f8bdced32c8fd5a7a5535411e987ac4', '[\"*\"]', '2025-08-22 05:21:54', NULL, '2025-08-22 04:28:09', '2025-08-22 05:21:54'),
(389, 'App\\Models\\User', 39, 'auth_token', 'f1642e1ef157eaf776f2f7a7c58693e29453d8f53b734ac41830d73a7d45b3e4', '[\"*\"]', '2025-08-22 05:30:01', NULL, '2025-08-22 05:19:49', '2025-08-22 05:30:01'),
(390, 'App\\Models\\User', 39, 'auth_token', '901dfc81f64fa427635eec1f4cc6416c4d2d1d914e6e210515e416597094635a', '[\"*\"]', '2025-08-23 01:06:44', NULL, '2025-08-22 05:20:17', '2025-08-23 01:06:44'),
(391, 'App\\Models\\User', 29, 'auth_token', 'eb504b0bfd456bb28d73a248ee01ba49999e7cbd4c9bfb32391dd71574bbf711', '[\"*\"]', '2025-08-22 05:30:35', NULL, '2025-08-22 05:30:08', '2025-08-22 05:30:35'),
(392, 'App\\Models\\User', 29, 'auth_token', 'b3d6d020c5e642ab74a7fa3ae7e843069ec4d95c2028844c81ffcf5524e215da', '[\"*\"]', '2025-08-22 05:39:02', NULL, '2025-08-22 05:32:35', '2025-08-22 05:39:02'),
(393, 'App\\Models\\User', 10, 'auth_token', 'f3e20c6c8b59ee5335d6c14ae1ddbdc715f30cbd6f7e5152028511e74d2be8fd', '[\"*\"]', '2025-08-23 00:07:19', NULL, '2025-08-22 07:10:54', '2025-08-23 00:07:19'),
(394, 'App\\Models\\User', 29, 'auth_token', '0cef410c6bfccc9818c4abe5ea4c4587910c3ae0f523a2d5cef024ece364bc15', '[\"*\"]', '2025-08-22 07:23:07', NULL, '2025-08-22 07:17:50', '2025-08-22 07:23:07'),
(395, 'App\\Models\\User', 29, 'auth_token', '6d31169d459e754ab34a56f76bf06bdf1dee337ad24bceed37fb890acc0d799e', '[\"*\"]', '2025-08-23 00:12:06', NULL, '2025-08-23 00:07:43', '2025-08-23 00:12:06'),
(396, 'App\\Models\\User', 29, 'auth_token', 'a277fb2fb486ffa1bf0a1249c54385e2679e72405dea7a511ae277212943604f', '[\"*\"]', '2025-08-23 00:54:49', NULL, '2025-08-23 00:41:03', '2025-08-23 00:54:49'),
(397, 'App\\Models\\User', 39, 'auth_token', '80e276a4e907c4e9c5c81f87ef6cf9cbcc858de9f83df0825eaff7c2d26914c5', '[\"*\"]', '2025-08-23 01:21:15', NULL, '2025-08-23 01:20:31', '2025-08-23 01:21:15'),
(398, 'App\\Models\\User', 39, 'auth_token', '98cc3332e88ecefc0293d2cc7c2794d9512bb14c02f08a137540cbce796c864b', '[\"*\"]', '2025-08-23 01:38:39', NULL, '2025-08-23 01:26:58', '2025-08-23 01:38:39'),
(399, 'App\\Models\\User', 39, 'auth_token', '879bd2ca6dbee9753d70f7fc5348909a87466b6b2d724ea2b419550e3e81e1e7', '[\"*\"]', '2025-08-23 01:40:56', NULL, '2025-08-23 01:40:27', '2025-08-23 01:40:56'),
(400, 'App\\Models\\User', 39, 'auth_token', '347ef4f21b2bff93c29ca049d5e83594bbed03e0368aa266b2ce561c36088e18', '[\"*\"]', '2025-08-23 02:01:22', NULL, '2025-08-23 01:43:29', '2025-08-23 02:01:22'),
(401, 'App\\Models\\User', 39, 'auth_token', 'bf0faba1fed3d6e775b94ba6183001d30f9b51f12f742fca93cb0196d9522e7f', '[\"*\"]', '2025-08-23 07:55:27', NULL, '2025-08-23 02:08:18', '2025-08-23 07:55:27'),
(402, 'App\\Models\\User', 29, 'auth_token', '51d6e6f4e5e47cb687a22d0cfb9e3a68c03b3eebf152d903a7b389e18f1cee92', '[\"*\"]', '2025-08-23 22:49:51', NULL, '2025-08-23 22:48:34', '2025-08-23 22:49:51'),
(403, 'App\\Models\\User', 29, 'auth_token', 'a9f65a1f32d3a17040d3d4cbba073cc64399f19c8e1f91aaac60e83fb560bf4a', '[\"*\"]', '2025-08-24 01:21:37', NULL, '2025-08-23 23:48:28', '2025-08-24 01:21:37'),
(404, 'App\\Models\\User', 39, 'auth_token', '86f3b3150fc44686a5e5902c689f8d1ecbfc5d3d221dd85146b66ff84958753f', '[\"*\"]', '2025-08-24 00:26:21', NULL, '2025-08-24 00:26:04', '2025-08-24 00:26:21'),
(405, 'App\\Models\\User', 39, 'auth_token', 'e45a3e2b0a696651c49220888f3a4566c474add2c969a3dc0eb4cfc1412ea36e', '[\"*\"]', NULL, NULL, '2025-08-24 00:26:04', '2025-08-24 00:26:04'),
(406, 'App\\Models\\User', 39, 'auth_token', '4a8c06ebb8fdf48f845850eb74894348a7f222710899db249e93e91e25aa04da', '[\"*\"]', '2025-08-24 02:37:40', NULL, '2025-08-24 00:26:28', '2025-08-24 02:37:40'),
(407, 'App\\Models\\User', 29, 'auth_token', 'ae82d3125958c842cb1a8029a6348ce8ccd0a64b8bf425e926fdd5698cb9f5c4', '[\"*\"]', '2025-08-24 01:45:55', NULL, '2025-08-24 01:25:43', '2025-08-24 01:45:55'),
(408, 'App\\Models\\User', 29, 'auth_token', '7700e7908526fe8b81841172972ec97a272a746a231c4a02b14a7c55d1009eab', '[\"*\"]', '2025-08-25 03:03:20', NULL, '2025-08-24 01:49:23', '2025-08-25 03:03:20'),
(410, 'App\\Models\\User', 46, 'auth_token', 'eb0b797887cc025bb7e6998c387ce8303f78115f01a330847a20f542d930075b', '[\"*\"]', '2025-08-25 01:57:39', NULL, '2025-08-25 01:56:57', '2025-08-25 01:57:39'),
(411, 'App\\Models\\User', 39, 'auth_token', '2c36a11a413e6070649bc97943cd87754e58068bc810355734240f1c2f07fe26', '[\"*\"]', '2025-08-25 03:00:52', NULL, '2025-08-25 02:18:06', '2025-08-25 03:00:52'),
(412, 'App\\Models\\User', 46, 'auth_token', '9e5fdbfa180a6a593670dd43b199a9c0eda597736ccc8e551fb87d231164a9bd', '[\"*\"]', '2025-08-25 05:20:01', NULL, '2025-08-25 02:20:23', '2025-08-25 05:20:01'),
(413, 'App\\Models\\User', 38, 'auth_token', '3a89097034833c796dbc18180fb3055947eca7ad7b061fc4102610f8a249f07e', '[\"*\"]', '2025-08-25 06:22:44', NULL, '2025-08-25 02:44:17', '2025-08-25 06:22:44'),
(414, 'App\\Models\\User', 10, 'auth_token', 'bb6e977d8aeba89debb96de4d95fd1bd7dcba6100101b53ddb3edab2c9682704', '[\"*\"]', '2025-08-25 03:21:32', NULL, '2025-08-25 03:09:54', '2025-08-25 03:21:32'),
(415, 'App\\Models\\User', 29, 'auth_token', '5059ebc7d704645ddc3c4363c37af82da190240acde839df20b97a3159254e8d', '[\"*\"]', '2025-08-25 03:40:08', NULL, '2025-08-25 03:39:40', '2025-08-25 03:40:08'),
(416, 'App\\Models\\User', 10, 'auth_token', 'fb5b4d602f607705c8ed8667b549d911cbccdfebbc970ae3906041389c78b89b', '[\"*\"]', '2025-08-25 03:47:21', NULL, '2025-08-25 03:40:33', '2025-08-25 03:47:21'),
(417, 'App\\Models\\User', 29, 'auth_token', 'ec0c0e9fd49b5058f8468a4cb8918d32db00eff001bf773a66b33b1640d2b591', '[\"*\"]', '2025-08-25 03:49:49', NULL, '2025-08-25 03:47:41', '2025-08-25 03:49:49'),
(418, 'App\\Models\\User', 10, 'auth_token', 'a0961592721e68dc7670331aa8db7ecb23b8251f18088a9312f3edac8bd2f852', '[\"*\"]', '2025-08-25 03:52:08', NULL, '2025-08-25 03:50:06', '2025-08-25 03:52:08'),
(419, 'App\\Models\\User', 29, 'auth_token', '56b9839a1d92260b15532790231ed0f266278602ec514107592db3d1974734af', '[\"*\"]', '2025-08-25 03:53:18', NULL, '2025-08-25 03:52:27', '2025-08-25 03:53:18'),
(420, 'App\\Models\\User', 10, 'auth_token', '6a460d4e12db6bfd94e46ec3f03b90c4b1e0a741a01674519c104d7a627d2093', '[\"*\"]', '2025-08-25 03:54:04', NULL, '2025-08-25 03:53:40', '2025-08-25 03:54:04'),
(421, 'App\\Models\\User', 29, 'auth_token', 'bf96e1252b425331627df8421055cea1087538ab971e81a4c0da283a06d84f75', '[\"*\"]', '2025-08-25 04:16:14', NULL, '2025-08-25 03:54:42', '2025-08-25 04:16:14'),
(422, 'App\\Models\\User', 10, 'auth_token', '4468e88725b0f159eeafbe4294e3afac83d9f30e31c3b5f12037a91b054980d2', '[\"*\"]', '2025-08-25 04:17:22', NULL, '2025-08-25 04:16:45', '2025-08-25 04:17:22'),
(423, 'App\\Models\\User', 29, 'auth_token', '75568103a72f92a3b994e8870f3d7e78f6c0ecba47fb689ee6ffdea426276796', '[\"*\"]', '2025-08-25 04:19:58', NULL, '2025-08-25 04:17:38', '2025-08-25 04:19:58'),
(424, 'App\\Models\\User', 10, 'auth_token', '12e815edfc5a15b12ad05830aa66ea6a56b57f904b06b623cb5811ae3ef30fb0', '[\"*\"]', '2025-08-25 04:20:51', NULL, '2025-08-25 04:20:17', '2025-08-25 04:20:51'),
(425, 'App\\Models\\User', 29, 'auth_token', '5d480afce80815438a3215e34f2a809722c84e7ab37a55426bd7fd015286b7dc', '[\"*\"]', '2025-08-25 04:31:34', NULL, '2025-08-25 04:21:10', '2025-08-25 04:31:34'),
(426, 'App\\Models\\User', 10, 'auth_token', '4bbed50c1159975edcc29866f3c89f878456baa14521477568e8ece78c2771bc', '[\"*\"]', '2025-08-25 04:33:08', NULL, '2025-08-25 04:32:12', '2025-08-25 04:33:08'),
(427, 'App\\Models\\User', 29, 'auth_token', '1a9537a1e2ad9d17ccd09d03723f223919aca10865555dd8a92dc6de1a94af42', '[\"*\"]', '2025-08-25 04:35:09', NULL, '2025-08-25 04:33:20', '2025-08-25 04:35:09'),
(428, 'App\\Models\\User', 10, 'auth_token', 'f1edcd201c93cb5173b1191a4d4a07e5298f28db95844de7d38550c2ab54d5fd', '[\"*\"]', '2025-08-25 04:37:32', NULL, '2025-08-25 04:35:24', '2025-08-25 04:37:32'),
(429, 'App\\Models\\User', 29, 'auth_token', '05b50840a9ccc7ceb29b72a85e1b77a65f791fd659ed765c3e4d6fd3a5d09a84', '[\"*\"]', '2025-08-25 04:43:19', NULL, '2025-08-25 04:40:06', '2025-08-25 04:43:19'),
(430, 'App\\Models\\User', 10, 'auth_token', '7c9cf5fa043fc17d022d6a4cc01be1d03fb88e66ce78dffa6a8adf98d9956e94', '[\"*\"]', '2025-08-25 04:44:52', NULL, '2025-08-25 04:43:46', '2025-08-25 04:44:52'),
(431, 'App\\Models\\User', 29, 'auth_token', '8eff76d572cb178fc7bd70d06ec4c576fbcfc7d710bf6cd9201c5c283f642c7e', '[\"*\"]', '2025-08-25 05:53:05', NULL, '2025-08-25 04:45:22', '2025-08-25 05:53:05'),
(432, 'App\\Models\\User', 7, 'auth_token', '94ea1663e0038905bbfc97519f2571c9a7883fe8125c9ae224b1b9b4a03fe496', '[\"*\"]', '2025-08-25 05:24:47', NULL, '2025-08-25 05:20:19', '2025-08-25 05:24:47'),
(433, 'App\\Models\\User', 46, 'auth_token', '412ef47f11e703b20244c8169c218f024ae00a79d5fd7ff8f04e5d1fb2a47c3e', '[\"*\"]', '2025-08-25 06:18:42', NULL, '2025-08-25 05:25:00', '2025-08-25 06:18:42'),
(434, 'App\\Models\\User', 10, 'auth_token', 'cbd6fd662526f1453404fad9f323805b327157ff904e65681b0b37574652951d', '[\"*\"]', '2025-08-25 05:55:09', NULL, '2025-08-25 05:54:45', '2025-08-25 05:55:09'),
(435, 'App\\Models\\User', 29, 'auth_token', 'f964c31fcdb22da25f053b92a715f64b13880b9b9fb4aa86f6eff390b873d40a', '[\"*\"]', '2025-08-26 03:52:57', NULL, '2025-08-25 05:55:22', '2025-08-26 03:52:57'),
(436, 'App\\Models\\User', 7, 'auth_token', '96cd3c12004681919d55d43224e8981bbca21a2ff246dd73a9caec500691f472', '[\"*\"]', '2025-08-26 00:48:42', NULL, '2025-08-25 06:18:54', '2025-08-26 00:48:42'),
(437, 'App\\Models\\User', 29, 'auth_token', 'c40378829c4078734f48c5a0e3c7fb7bd8b7ec1826bfb51f83eeecccc363c317', '[\"*\"]', '2025-08-25 06:58:02', NULL, '2025-08-25 06:24:50', '2025-08-25 06:58:02'),
(438, 'App\\Models\\User', 39, 'auth_token', '7abb1b0c4be17b3ed11095e735129da3f1c2b3f29daea9c27d6819b10dcae51f', '[\"*\"]', '2025-08-25 11:22:54', NULL, '2025-08-25 11:18:55', '2025-08-25 11:22:54'),
(439, 'App\\Models\\User', 39, 'auth_token', 'cd6c890ca3dc700e6cbd0601c1f0563ce5492645db05678b868fdd8e29902048', '[\"*\"]', '2025-08-25 13:52:29', NULL, '2025-08-25 11:27:04', '2025-08-25 13:52:29'),
(440, 'App\\Models\\User', 39, 'auth_token', 'fddc3cfb19308f52638a6ff5219a2c0cb0c9720d63703433cec90cbae1aef452', '[\"*\"]', '2025-08-25 23:51:51', NULL, '2025-08-25 22:34:00', '2025-08-25 23:51:51'),
(441, 'App\\Models\\User', 39, 'auth_token', '7fefe7ab6c4f99b20e1d81906ffe0398aa6fdf302c6e76a7cf7815f898e52c14', '[\"*\"]', '2025-08-26 00:03:02', NULL, '2025-08-26 00:00:39', '2025-08-26 00:03:02'),
(442, 'App\\Models\\User', 39, 'auth_token', '4a7c540991392b967ef00230a89a5aaa139a00c52c46eff56acde98d7a06e817', '[\"*\"]', '2025-08-26 01:15:01', NULL, '2025-08-26 00:29:03', '2025-08-26 01:15:01'),
(443, 'App\\Models\\User', 7, 'auth_token', '0b8497be0ce0e93cae94d54155f85bbf0009b32e6f7eedc7bc37c6a1befd97e2', '[\"*\"]', '2025-08-26 03:08:17', NULL, '2025-08-26 00:49:42', '2025-08-26 03:08:17'),
(444, 'App\\Models\\User', 39, 'auth_token', '310281c2d741bea97b9d673074e2747b0efff8631a99ffad55b5724aa5226306', '[\"*\"]', NULL, NULL, '2025-08-26 01:18:36', '2025-08-26 01:18:36'),
(445, 'App\\Models\\User', 39, 'auth_token', '49a2d5e8923fb7d56cd000892a484cc2f1e7b62527e71b0877932b19e526551e', '[\"*\"]', NULL, NULL, '2025-08-26 01:21:54', '2025-08-26 01:21:54'),
(446, 'App\\Models\\User', 39, 'auth_token', '98899b601e51f280e8b96daf1559b5fcb966f0a267def2a118d273b723569151', '[\"*\"]', NULL, NULL, '2025-08-26 01:22:08', '2025-08-26 01:22:08'),
(447, 'App\\Models\\User', 39, 'auth_token', '7f602cce1b70950c2fea7563983a004a138a1a508f2c0e67f3c892c409d7177f', '[\"*\"]', '2025-08-26 01:54:26', NULL, '2025-08-26 01:23:27', '2025-08-26 01:54:26'),
(448, 'App\\Models\\User', 39, 'auth_token', 'f5e37959e2682a662b26fb5d6a3c79f0d6474db2aacbf8818bc61a7145f6f70a', '[\"*\"]', '2025-08-26 02:23:01', NULL, '2025-08-26 02:05:58', '2025-08-26 02:23:01'),
(449, 'App\\Models\\User', 39, 'auth_token', '51d94b582fa8ed499b4a19e34403d78aa41f79b3bedde5e2492281f14d819271', '[\"*\"]', '2025-08-26 02:27:53', NULL, '2025-08-26 02:23:23', '2025-08-26 02:27:53'),
(450, 'App\\Models\\User', 40, 'auth_token', '5b3177d942ee7560050d9c43883bcd6ccc7babc5399ae97e6cf89c4ace3752a1', '[\"*\"]', '2025-08-26 03:24:39', NULL, '2025-08-26 03:20:50', '2025-08-26 03:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `RecurringPatterns`
--

CREATE TABLE `RecurringPatterns` (
  `pattern_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `recurrence_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `recurrence_value` int(11) NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Reports`
--

CREATE TABLE `Reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `target_type` enum('goal','note','user') NOT NULL,
  `target_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RevenueStatistics`
--

CREATE TABLE `RevenueStatistics` (
  `revenue_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `total_revenue` decimal(15,2) DEFAULT 0.00,
  `total_transactions` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RolePermissions`
--

CREATE TABLE `RolePermissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `role_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SubscriptionHistory`
--

CREATE TABLE `SubscriptionHistory` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_plan_id` int(11) DEFAULT NULL,
  `new_plan_id` int(11) NOT NULL,
  `change_type` enum('upgrade','downgrade','renewal','cancellation') NOT NULL,
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SubscriptionPlans`
--

CREATE TABLE `SubscriptionPlans` (
  `plan_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `SubscriptionPlans`
--

INSERT INTO `SubscriptionPlans` (`plan_id`, `name`, `description`, `duration`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Premium Monthly', 'Full-featured monthly premium subscription', 1, 99000.00, '2025-05-20 03:05:07', '2025-08-20 09:08:50'),
(2, 'Premium Year', 'Premium annual subscription with full features, save 20%', 12, 950000.00, '2025-05-20 03:05:07', '2025-08-20 09:09:04');

-- --------------------------------------------------------

--
-- Table structure for table `UserLogs`
--

CREATE TABLE `UserLogs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `UserProfiles`
--

CREATE TABLE `UserProfiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `UserProfiles`
--

INSERT INTO `UserProfiles` (`profile_id`, `user_id`, `is_premium`, `created_at`, `updated_at`) VALUES
(1, 1, 0, '2025-06-11 13:52:12', '2025-06-11 13:52:12'),
(2, 2, 0, '2025-06-11 14:19:44', '2025-06-11 14:19:44'),
(6, 6, 0, '2025-06-16 08:49:30', '2025-06-16 08:49:30'),
(7, 7, 0, '2025-06-16 08:50:49', '2025-06-16 08:50:49'),
(9, 9, 0, '2025-06-16 09:20:02', '2025-06-16 09:20:02'),
(10, 10, 0, '2025-06-17 07:18:06', '2025-06-17 07:18:06'),
(24, 24, 0, '2025-06-22 11:17:57', '2025-06-22 11:17:57'),
(25, 25, 0, '2025-06-22 11:21:53', '2025-06-22 11:21:53'),
(26, 26, 0, '2025-06-22 11:24:38', '2025-06-22 11:24:38'),
(27, 27, 0, '2025-06-22 11:44:57', '2025-06-22 11:44:57'),
(28, 28, 0, '2025-06-22 11:49:45', '2025-06-22 11:49:45'),
(29, 29, 0, '2025-06-22 16:04:18', '2025-06-22 16:04:18'),
(30, 30, 0, '2025-06-22 16:22:19', '2025-06-22 16:22:19'),
(37, 38, 0, '2025-07-02 07:48:37', '2025-07-02 07:48:37'),
(38, 39, 0, '2025-07-03 07:52:01', '2025-07-03 07:52:01'),
(39, 40, 0, '2025-07-03 08:16:37', '2025-07-03 08:16:37'),
(40, 41, 0, '2025-07-03 10:27:11', '2025-07-03 10:27:11'),
(42, 43, 0, '2025-07-03 10:44:25', '2025-07-03 10:44:25'),
(45, 46, 0, '2025-08-04 05:08:53', '2025-08-04 05:08:53'),
(47, 48, 0, '2025-08-22 10:42:52', '2025-08-22 10:42:52'),
(52, 53, 0, '2025-08-25 07:48:17', '2025-08-25 07:48:17'),
(53, 54, 0, '2025-08-25 09:43:03', '2025-08-25 09:43:03');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `registration_type` enum('email','google','facebook') NOT NULL,
  `status` enum('active','banned','pending','unverified') DEFAULT 'unverified',
  `reset_token` varchar(255) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `display_name`, `email`, `password_hash`, `avatar_url`, `registration_type`, `status`, `reset_token`, `verification_token`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Người Dùng Test', 'test@example.com', '$2y$12$d7l7nj0/pzfzTRWXaMxYXua3KYPaeRCyJahLJmk1pRclGcRei.nE2', NULL, 'email', 'active', 'qj22WUHW4rDVxu7msm6AeVBkP1TvSCSRLlJb00zSCJzHkZkBhRo6y8ju9yGw', NULL, '2025-07-04 08:45:37', '2025-06-11 06:52:12', '2025-07-04 08:45:37'),
(2, 'Người Dùng Test', 'test2023@example.com', '$2y$12$k.w7UZKrQTt7gfgjUyRRyuFsVnpSJF/UrgNbAEqy4SiR99./iP0cy', NULL, 'email', 'active', NULL, NULL, NULL, '2025-06-11 07:19:44', '2025-06-11 07:19:44'),
(6, 'UserTest', 'email-test@example.com', '$2y$12$wgl2for8GBEGRqyMtx.kOeEjwl6cR8kzqxk4V4K3kDJrl34phVuAS', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-16 01:49:29', '2025-06-16 01:49:29'),
(7, 'Tranvietkhoa', 'tranvietkhoa2004@gmail.com', '$2y$12$kVoIacCwxYFMv6RjFR9uWeNOWYKaqWA8c9AZ8ks2VwWLP8I6B38sC', 'https://lh3.googleusercontent.com/a/ACg8ocJfDmh1Es-sMTHlWWbmIos4w_ev7eeciFvS83IzrZlwv-Biqw=s96-c', 'email', 'active', NULL, NULL, '2025-08-26 00:49:42', '2025-07-26 19:39:34', '2025-08-26 00:49:42'),
(9, 'vynu', 'tranvy0765687090@gmail.com', '$2y$12$HskPj4sdgOpoBaq5fZd0uuAV8qUd5qKop67NY/7hf4y07k2qsjqI6', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-16 02:20:02', '2025-06-16 02:20:02'),
(10, 'manh123', 'nduymanh11@gmail.com', '$2y$12$xKYd/WhiwcpBJhBOEnpgZeQzmXWshjJgbAJxEQlFlC0BAWxp7f1NG', 'http://localhost:8000/storage/avatars/8f6PxdhINPp59j4YFTU8SWyk8VGtpEJoayERfPXM.jpg', 'email', 'unverified', NULL, NULL, '2025-08-25 05:54:45', '2025-06-17 00:18:05', '2025-08-25 05:54:45'),
(24, 'Test', 'test1750591077@example.com', '$2y$12$ppS2v4wps/HNusvGKTi4ZudDTafaXShuxkyRwot5vxFsEgZTUXYyi', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-22 04:17:57', '2025-06-22 04:17:57'),
(25, 'Test', 'test1750591313@example.com', '$2y$12$MnSyN76ywIYWdUbU3mgxBOlOIq93Ag2OVt7/mF9ckg6kYkiqUZYnq', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-22 04:21:53', '2025-06-22 04:21:53'),
(26, 'Test', 'test1750591477@example.com', '$2y$12$aRe84VTnlJEGOa.iWWFEoOZj5GCsGs/EKwRhmgvdIF3tYuw/y0/H.', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-22 04:24:38', '2025-06-22 04:24:38'),
(27, 'Test User', 'test1750592696@example.com', '$2y$12$DRz9ZFBhVxVTowqZRndw2.PWOaU6IWZSZDXtsnuH4DHBynybptT1O', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-22 04:44:57', '2025-06-22 04:44:57'),
(28, 'API Test User 1750592985', 'apitest1750592985@example.com', '$2y$12$EcSfqSOdVtUfdA7SrIJcp.OJCdqiGPD9Vd1fMuzISv1g2TGdg1Foa', NULL, 'email', 'unverified', NULL, NULL, NULL, '2025-06-22 04:49:46', '2025-06-22 04:49:46'),
(29, 'Đại Tá Tép ', 'nduymanh112@gmail.com', '$2y$12$ivmXNyJ74WApkQX6A4yxF.abi2Nyor/ZgPYOVFVhQVdg8QB/c8JcC', 'http://localhost:8000/storage/avatars/SnU8ME2a2MshBvdZ5bYlmDc7gHv13CLlrx3xhODt.jpg', 'email', 'active', NULL, NULL, '2025-08-25 06:24:51', '2025-08-21 04:10:44', '2025-08-25 06:24:51'),
(30, 'Testtttttttt', 'wd19307manhnd@gmail.com', '$2y$12$OPkOU5jWaQLW6Xf.FDbtdehenm2QoXr/UVRBhJTK6Q2856Yls8.GG', 'https://lh3.googleusercontent.com/a/ACg8ocJgI3rYxJ4MEGpkamZs5CdM9arunUPKWlV6uYimHbLWjsQEFg=s96-c', 'email', 'active', NULL, NULL, '2025-08-03 21:02:15', '2025-06-22 09:24:02', '2025-08-03 21:06:56'),
(38, 'ndt', 'nguyennguyenthanh2201@gmail.com', '$2y$12$ANw2DH9LTAR3SVa1VGy4QeWPzbFKPpxPhp3a6O3YRDD6QSX7CMGJa', 'http://localhost:8000/storage/avatars/ZbUqc65cqSydVsDB7RHwDMK983JwpWAkkQvQ12mM.jpg', 'email', 'active', NULL, NULL, '2025-08-25 02:44:17', '2025-07-26 19:56:41', '2025-08-25 02:44:17'),
(39, 'Hạnh Vyyyy', 'nguyenvy01052005@gmail.com', '$2y$12$/NIE9Si3DBeZhRi8bSX5eunctHuQZeXEOxmzeEgogpGSjCevCVjlG', 'http://localhost:8000/storage/avatars/TUiOEmBqAEkXGpFvmWAyf9JiDCaCJGeAcNu4tLZX.jpg', 'google', 'active', NULL, NULL, '2025-08-26 02:23:23', '2025-07-03 00:52:01', '2025-08-26 02:23:23'),
(40, 'N. Đức Thành', 'thanhndpd11083@gmail.com', '$2y$12$vB.6YC7W3gGQoIPUQ68t9O3U9hxulwxVcBg/SiHPVBTdNqhNYaAJa', 'http://localhost:8000/storage/avatars/eukin1U9ECXsBqhaKezFNqHpwW104dmz18iFW7gZ.jpg', 'facebook', 'active', NULL, NULL, '2025-08-26 03:20:50', '2025-07-03 01:16:38', '2025-08-26 03:20:50'),
(41, 'Trần Viết Khang', 'nightmale28@gmail.com', '$2y$12$k5e8jLxKGdhFhLLgLKOjcemYLdqEDoW8f6ID2LK4D6AwrIPKtuA5C', 'http://localhost:8000/storage/avatars/ydM8mjiUN8yWDg2ey9Bk0r6Wul1SxqmiSl0TJBiE.jpg', 'google', 'active', NULL, NULL, '2025-08-21 05:12:21', '2025-07-03 03:27:11', '2025-08-21 05:12:35'),
(43, 'Admin01', 'Admin01@gmail.com', '$2y$12$QWsuX6jmo6gUDx5f/.hgv.mTBIjJu94FHogOvqhhVvuCsYnAxiGoC', NULL, 'email', 'active', NULL, NULL, '2025-07-05 19:28:53', '2025-07-03 03:44:25', '2025-07-05 19:28:53'),
(46, 'Demo1234', 'khoatvpd10117@fpt.edu.vn', '$2y$12$ACoeGOJwXPJCcacaIGeWPuZ5vBm9q5YoMu2O36rlSlohq621hcCxG', 'http://localhost:8000/storage/avatars/eAUHuqtNxNbBrzM4PtrjbRsGOA33KK6cVZjyP6Hs.jpg', 'email', 'active', NULL, NULL, '2025-08-25 05:25:00', '2025-08-03 22:08:53', '2025-08-25 05:25:00'),
(48, 'Tuu Tuuu', 'tuutuuu0702@gmail.com', '$2y$12$QDqavLaY/tqjndF.LcdumefL0W8d5SP/RaZBZacgwj/Dyf5n.0cw.', 'https://lh3.googleusercontent.com/a/ACg8ocLQNnAdnJA9ieNIvf2RYuRKZ3coe0iv3dPy-yun3BDvCa_NPg=s96-c', 'google', 'active', NULL, NULL, '2025-08-22 03:46:54', '2025-08-22 03:42:47', '2025-08-22 03:46:54'),
(53, '097545667', 'test123@example.com', '$2y$12$BeiYzW32txamaH50R8hxLe9ev2mDp8ylwmFV/xt7b7svaUKearOe6', NULL, 'email', 'unverified', NULL, '651305', NULL, '2025-08-25 00:48:17', '2025-08-25 00:48:17'),
(54, 'Nguyễn Đức Thành', 'test@example.comm', '$2y$12$tuzRheOs7Ph6AGCY0q6XWuaM3faQCKlZn6iVvWJ1Qtt/r.R2BRpG.', NULL, 'email', 'unverified', NULL, '490154', NULL, '2025-08-25 02:43:03', '2025-08-25 02:43:03');

--
-- Triggers `Users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `Users` FOR EACH ROW BEGIN
    INSERT INTO UserProfiles (user_id, is_premium)
    VALUES (NEW.user_id, FALSE);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `UserStatistics`
--

CREATE TABLE `UserStatistics` (
  `stat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_goals` int(11) DEFAULT 0,
  `total_notes` int(11) DEFAULT 0,
  `total_friends` int(11) DEFAULT 0,
  `is_premium` tinyint(1) DEFAULT 0,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `UserSubscriptions`
--

CREATE TABLE `UserSubscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_status` enum('active','cancelled','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `auto_renewal_id` int(11) DEFAULT NULL,
  `renewal_count` int(11) DEFAULT 0,
  `last_renewal_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `UserSubscriptions`
--

INSERT INTO `UserSubscriptions` (`subscription_id`, `user_id`, `plan_id`, `start_date`, `end_date`, `payment_status`, `created_at`, `updated_at`, `auto_renewal_id`, `renewal_count`, `last_renewal_date`) VALUES
(1, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 04:45:04', '2025-06-22 04:45:04', NULL, 0, NULL),
(2, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 04:49:53', '2025-06-22 04:49:53', NULL, 0, NULL),
(3, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 13:46:00', '2025-06-22 13:46:00', NULL, 0, NULL),
(4, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 13:46:27', '2025-06-22 13:46:27', NULL, 0, NULL),
(5, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 13:51:32', '2025-06-22 13:51:32', NULL, 0, NULL),
(6, 7, 1, '2025-06-22', '2025-07-22', 'active', '2025-06-22 13:57:10', '2025-06-22 13:57:10', NULL, 0, NULL),
(7, 7, 1, '2025-06-23', '2025-07-23', 'active', '2025-06-23 06:22:34', '2025-06-23 06:22:34', NULL, 0, NULL),
(8, 7, 1, '2025-06-23', '2025-07-23', 'active', '2025-06-23 06:38:59', '2025-06-23 06:38:59', NULL, 0, NULL),
(9, 40, 1, '2025-07-04', '2025-08-04', 'cancelled', '2025-07-04 09:40:16', '2025-07-04 10:21:37', NULL, 0, NULL),
(10, 40, 1, '2025-07-10', '2025-08-10', 'cancelled', '2025-07-10 09:49:42', '2025-08-19 03:57:28', NULL, 0, NULL),
(11, 38, 2, '2025-07-24', '2026-07-24', 'cancelled', '2025-07-24 00:38:43', '2025-07-25 23:47:16', NULL, 0, NULL),
(12, 38, 2, '2025-07-27', '2026-07-27', 'cancelled', '2025-07-26 19:48:11', '2025-07-26 19:50:05', NULL, 0, NULL),
(13, 38, 2, '2025-07-27', '2026-07-27', 'cancelled', '2025-07-26 19:52:55', '2025-08-03 21:57:14', NULL, 0, NULL),
(25, 30, 1, '2025-08-04', '2025-09-04', 'active', '2025-08-03 21:03:48', '2025-08-03 21:03:48', NULL, 0, NULL),
(28, 38, 1, '2025-08-04', '2025-09-04', 'cancelled', '2025-08-03 21:57:14', '2025-08-03 22:04:05', NULL, 0, NULL),
(29, 38, 1, '2025-08-04', '2025-09-04', 'cancelled', '2025-08-03 21:57:14', '2025-08-03 22:19:47', NULL, 0, NULL),
(30, 29, 1, '2025-08-04', '2025-09-04', 'cancelled', '2025-08-03 22:02:24', '2025-08-22 02:50:59', NULL, 0, NULL),
(31, 29, 1, '2025-08-04', '2025-09-04', 'cancelled', '2025-08-03 22:02:24', '2025-08-22 02:50:59', NULL, 0, NULL),
(34, 38, 1, '2025-08-12', '2025-09-12', 'cancelled', '2025-08-12 03:23:53', '2025-08-15 04:35:31', NULL, 0, NULL),
(35, 38, 1, '2025-08-12', '2025-09-12', 'cancelled', '2025-08-12 03:23:53', '2025-08-15 04:43:00', NULL, 0, NULL),
(36, 38, 2, '2025-08-15', '2026-08-15', 'cancelled', '2025-08-15 04:45:40', '2025-08-22 01:52:53', NULL, 0, NULL),
(37, 38, 2, '2025-08-15', '2026-08-15', 'cancelled', '2025-08-15 04:45:40', '2025-08-22 01:52:53', NULL, 0, NULL),
(38, 40, 1, '2025-08-19', '2025-09-19', 'cancelled', '2025-08-19 06:24:51', '2025-08-19 06:48:51', NULL, 0, NULL),
(39, 40, 1, '2025-08-19', '2025-09-19', 'cancelled', '2025-08-19 06:24:51', '2025-08-19 06:48:51', NULL, 0, NULL),
(40, 40, 1, '2025-08-19', '2025-09-19', 'cancelled', '2025-08-19 07:17:34', '2025-08-19 07:19:04', NULL, 0, NULL),
(41, 40, 1, '2025-08-19', '2025-09-19', 'cancelled', '2025-08-19 07:17:34', '2025-08-19 07:19:04', NULL, 0, NULL),
(42, 40, 1, '2025-08-20', '2025-09-20', 'cancelled', '2025-08-20 00:03:16', '2025-08-20 00:03:16', NULL, 0, NULL),
(43, 40, 1, '2025-08-20', '2025-09-20', 'cancelled', '2025-08-20 00:03:16', '2025-08-20 02:07:03', NULL, 0, NULL),
(44, 40, 2, '2025-08-20', '2026-08-20', 'cancelled', '2025-08-20 02:16:18', '2025-08-20 02:37:21', NULL, 0, NULL),
(45, 40, 2, '2025-08-20', '2026-08-20', 'cancelled', '2025-08-20 02:16:18', '2025-08-20 02:37:21', NULL, 0, NULL),
(46, 40, 1, '2025-08-20', '2025-09-20', 'cancelled', '2025-08-20 02:39:33', '2025-08-21 10:17:54', NULL, 0, NULL),
(47, 40, 1, '2025-08-20', '2025-09-20', 'cancelled', '2025-08-20 02:39:33', '2025-08-21 10:17:54', NULL, 0, NULL),
(48, 46, 2, '2025-08-20', '2026-08-20', 'cancelled', '2025-08-20 04:20:12', '2025-08-20 04:26:57', NULL, 0, NULL),
(49, 46, 2, '2025-08-20', '2026-08-20', 'cancelled', '2025-08-20 04:20:12', '2025-08-20 04:26:57', NULL, 0, NULL),
(50, 10, 2, '2025-08-21', '2026-08-21', 'cancelled', '2025-08-21 01:16:33', '2025-08-21 01:17:43', NULL, 0, NULL),
(51, 10, 2, '2025-08-21', '2026-08-21', 'cancelled', '2025-08-21 01:16:33', '2025-08-21 01:17:43', NULL, 0, NULL),
(52, 46, 2, '2025-08-21', '2026-08-21', 'active', '2025-08-21 02:05:33', '2025-08-21 02:05:33', NULL, 0, NULL),
(53, 46, 2, '2025-08-21', '2026-08-21', 'active', '2025-08-21 02:05:33', '2025-08-21 02:05:33', NULL, 0, NULL),
(54, 39, 2, '2025-08-21', '2026-08-21', 'active', '2025-08-21 05:18:45', '2025-08-21 05:18:45', NULL, 0, NULL),
(55, 40, 2, '2025-08-21', '2026-08-21', 'cancelled', '2025-08-21 10:35:35', '2025-08-21 10:45:11', NULL, 0, NULL),
(56, 40, 2, '2025-08-21', '2026-08-21', 'cancelled', '2025-08-21 10:35:35', '2025-08-21 10:45:11', NULL, 0, NULL),
(57, 38, 2, '2025-08-22', '2026-08-22', 'cancelled', '2025-08-22 01:58:28', '2025-08-25 01:51:15', NULL, 0, NULL),
(58, 29, 2, '2025-08-22', '2026-08-22', 'active', '2025-08-22 03:39:18', '2025-08-22 03:39:18', NULL, 0, NULL),
(59, 29, 2, '2025-08-22', '2026-08-22', 'active', '2025-08-22 03:39:18', '2025-08-22 03:39:18', NULL, 0, NULL),
(62, 38, 1, '2025-08-25', '2025-09-25', 'active', '2025-08-25 01:56:25', '2025-08-25 01:56:25', NULL, 0, NULL),
(63, 38, 1, '2025-08-25', '2025-09-25', 'active', '2025-08-25 01:56:25', '2025-08-25 01:56:25', NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AdminLogs`
--
ALTER TABLE `AdminLogs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `Admins`
--
ALTER TABLE `Admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `AISuggestionGoalLinks`
--
ALTER TABLE `AISuggestionGoalLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `suggestion_id` (`suggestion_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `AISuggestions`
--
ALTER TABLE `AISuggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `AutoRenewalHistory`
--
ALTER TABLE `AutoRenewalHistory`
  ADD PRIMARY KEY (`renewal_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `AutoRenewalSettings`
--
ALTER TABLE `AutoRenewalSettings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `renewal_plan_id` (`renewal_plan_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `CourseGoalLinks`
--
ALTER TABLE `CourseGoalLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `course_goal` (`course_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `Courses`
--
ALTER TABLE `Courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `EventGoalLinks`
--
ALTER TABLE `EventGoalLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `Events`
--
ALTER TABLE `Events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `FileGoalLinks`
--
ALTER TABLE `FileGoalLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `file_id` (`file_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `FileNoteLinks`
--
ALTER TABLE `FileNoteLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `file_id` (`file_id`,`note_id`),
  ADD KEY `note_id` (`note_id`);

--
-- Indexes for table `Files`
--
ALTER TABLE `Files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Friendships`
--
ALTER TABLE `Friendships`
  ADD PRIMARY KEY (`friendship_id`),
  ADD UNIQUE KEY `user_id_1` (`user_id_1`,`user_id_2`),
  ADD KEY `user_id_2` (`user_id_2`);

--
-- Indexes for table `GoalCollaboration`
--
ALTER TABLE `GoalCollaboration`
  ADD PRIMARY KEY (`collab_id`),
  ADD UNIQUE KEY `goal_user` (`goal_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `GoalMembers`
--
ALTER TABLE `GoalMembers`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `goal_user` (`goal_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `GoalProgress`
--
ALTER TABLE `GoalProgress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `Goals`
--
ALTER TABLE `Goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `GoalShares`
--
ALTER TABLE `GoalShares`
  ADD PRIMARY KEY (`share_id`),
  ADD UNIQUE KEY `goal_id` (`goal_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Milestones`
--
ALTER TABLE `Milestones`
  ADD PRIMARY KEY (`milestone_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `NoteGoalLinks`
--
ALTER TABLE `NoteGoalLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `note_id` (`note_id`,`goal_id`),
  ADD KEY `goal_id` (`goal_id`);

--
-- Indexes for table `NoteMilestoneLinks`
--
ALTER TABLE `NoteMilestoneLinks`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `note_id` (`note_id`,`milestone_id`),
  ADD KEY `milestone_id` (`milestone_id`);

--
-- Indexes for table `Notes`
--
ALTER TABLE `Notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `Payments`
--
ALTER TABLE `Payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `PermissionHistory`
--
ALTER TABLE `PermissionHistory`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `goal_id` (`goal_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `old_role_id` (`old_role_id`),
  ADD KEY `new_role_id` (`new_role_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  ADD PRIMARY KEY (`pattern_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `Reports`
--
ALTER TABLE `Reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `RevenueStatistics`
--
ALTER TABLE `RevenueStatistics`
  ADD PRIMARY KEY (`revenue_id`),
  ADD UNIQUE KEY `stat_date` (`stat_date`);

--
-- Indexes for table `RolePermissions`
--
ALTER TABLE `RolePermissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `SubscriptionHistory`
--
ALTER TABLE `SubscriptionHistory`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `old_plan_id` (`old_plan_id`),
  ADD KEY `new_plan_id` (`new_plan_id`);

--
-- Indexes for table `SubscriptionPlans`
--
ALTER TABLE `SubscriptionPlans`
  ADD PRIMARY KEY (`plan_id`);

--
-- Indexes for table `UserLogs`
--
ALTER TABLE `UserLogs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `UserProfiles`
--
ALTER TABLE `UserProfiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `UserStatistics`
--
ALTER TABLE `UserStatistics`
  ADD PRIMARY KEY (`stat_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `auto_renewal_id` (`auto_renewal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AdminLogs`
--
ALTER TABLE `AdminLogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Admins`
--
ALTER TABLE `Admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `AISuggestionGoalLinks`
--
ALTER TABLE `AISuggestionGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AISuggestions`
--
ALTER TABLE `AISuggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AutoRenewalHistory`
--
ALTER TABLE `AutoRenewalHistory`
  MODIFY `renewal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AutoRenewalSettings`
--
ALTER TABLE `AutoRenewalSettings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CourseGoalLinks`
--
ALTER TABLE `CourseGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Courses`
--
ALTER TABLE `Courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `EventGoalLinks`
--
ALTER TABLE `EventGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Events`
--
ALTER TABLE `Events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `FileGoalLinks`
--
ALTER TABLE `FileGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `FileNoteLinks`
--
ALTER TABLE `FileNoteLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Files`
--
ALTER TABLE `Files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `Friendships`
--
ALTER TABLE `Friendships`
  MODIFY `friendship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `GoalCollaboration`
--
ALTER TABLE `GoalCollaboration`
  MODIFY `collab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `GoalMembers`
--
ALTER TABLE `GoalMembers`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GoalProgress`
--
ALTER TABLE `GoalProgress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `Goals`
--
ALTER TABLE `Goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `GoalShares`
--
ALTER TABLE `GoalShares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Milestones`
--
ALTER TABLE `Milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `NoteGoalLinks`
--
ALTER TABLE `NoteGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `NoteMilestoneLinks`
--
ALTER TABLE `NoteMilestoneLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Notes`
--
ALTER TABLE `Notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `Notifications`
--
ALTER TABLE `Notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Payments`
--
ALTER TABLE `Payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `PermissionHistory`
--
ALTER TABLE `PermissionHistory`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Permissions`
--
ALTER TABLE `Permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=451;

--
-- AUTO_INCREMENT for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  MODIFY `pattern_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Reports`
--
ALTER TABLE `Reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `RevenueStatistics`
--
ALTER TABLE `RevenueStatistics`
  MODIFY `revenue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `SubscriptionHistory`
--
ALTER TABLE `SubscriptionHistory`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `SubscriptionPlans`
--
ALTER TABLE `SubscriptionPlans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `UserLogs`
--
ALTER TABLE `UserLogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `UserProfiles`
--
ALTER TABLE `UserProfiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `UserStatistics`
--
ALTER TABLE `UserStatistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AdminLogs`
--
ALTER TABLE `AdminLogs`
  ADD CONSTRAINT `AdminLogs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `Admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `Admins`
--
ALTER TABLE `Admins`
  ADD CONSTRAINT `Admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `AISuggestionGoalLinks`
--
ALTER TABLE `AISuggestionGoalLinks`
  ADD CONSTRAINT `aisuggestiongoallinks_ibfk_1` FOREIGN KEY (`suggestion_id`) REFERENCES `AISuggestions` (`suggestion_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `aisuggestiongoallinks_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `AISuggestions`
--
ALTER TABLE `AISuggestions`
  ADD CONSTRAINT `aisuggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `AutoRenewalHistory`
--
ALTER TABLE `AutoRenewalHistory`
  ADD CONSTRAINT `autorenewalhistory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `autorenewalhistory_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `autorenewalhistory_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `Payments` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `AutoRenewalSettings`
--
ALTER TABLE `AutoRenewalSettings`
  ADD CONSTRAINT `autorenewalsettings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `autorenewalsettings_ibfk_2` FOREIGN KEY (`renewal_plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `CourseGoalLinks`
--
ALTER TABLE `CourseGoalLinks`
  ADD CONSTRAINT `CourseGoalLinks_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `CourseGoalLinks_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `EventGoalLinks`
--
ALTER TABLE `EventGoalLinks`
  ADD CONSTRAINT `eventgoallinks_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventgoallinks_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `Events`
--
ALTER TABLE `Events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `FileGoalLinks`
--
ALTER TABLE `FileGoalLinks`
  ADD CONSTRAINT `filegoallinks_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `Files` (`file_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `filegoallinks_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `FileNoteLinks`
--
ALTER TABLE `FileNoteLinks`
  ADD CONSTRAINT `filenotelinks_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `Files` (`file_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `filenotelinks_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `Notes` (`note_id`) ON DELETE CASCADE;

--
-- Constraints for table `Files`
--
ALTER TABLE `Files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Friendships`
--
ALTER TABLE `Friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`user_id_1`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`user_id_2`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `GoalCollaboration`
--
ALTER TABLE `GoalCollaboration`
  ADD CONSTRAINT `GoalCollaboration_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `GoalCollaboration_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `GoalMembers`
--
ALTER TABLE `GoalMembers`
  ADD CONSTRAINT `goalmembers_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goalmembers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goalmembers_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE CASCADE;

--
-- Constraints for table `GoalProgress`
--
ALTER TABLE `GoalProgress`
  ADD CONSTRAINT `goalprogress_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `Goals`
--
ALTER TABLE `Goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `GoalShares`
--
ALTER TABLE `GoalShares`
  ADD CONSTRAINT `GoalShares_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Milestones`
--
ALTER TABLE `Milestones`
  ADD CONSTRAINT `milestones_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `NoteGoalLinks`
--
ALTER TABLE `NoteGoalLinks`
  ADD CONSTRAINT `notegoallinks_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `Notes` (`note_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notegoallinks_ibfk_2` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE;

--
-- Constraints for table `NoteMilestoneLinks`
--
ALTER TABLE `NoteMilestoneLinks`
  ADD CONSTRAINT `notemilestonelinks_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `Notes` (`note_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notemilestonelinks_ibfk_2` FOREIGN KEY (`milestone_id`) REFERENCES `Milestones` (`milestone_id`) ON DELETE CASCADE;

--
-- Constraints for table `Notes`
--
ALTER TABLE `Notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Payments`
--
ALTER TABLE `Payments`
  ADD CONSTRAINT `Payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `PermissionHistory`
--
ALTER TABLE `PermissionHistory`
  ADD CONSTRAINT `permissionhistory_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `Goals` (`goal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissionhistory_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissionhistory_ibfk_3` FOREIGN KEY (`old_role_id`) REFERENCES `Roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissionhistory_ibfk_4` FOREIGN KEY (`new_role_id`) REFERENCES `Roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissionhistory_ibfk_5` FOREIGN KEY (`changed_by`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  ADD CONSTRAINT `recurringpatterns_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `Reports`
--
ALTER TABLE `Reports`
  ADD CONSTRAINT `Reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `RolePermissions`
--
ALTER TABLE `RolePermissions`
  ADD CONSTRAINT `rolepermissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rolepermissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `Permissions` (`permission_id`) ON DELETE CASCADE;

--
-- Constraints for table `SubscriptionHistory`
--
ALTER TABLE `SubscriptionHistory`
  ADD CONSTRAINT `subscriptionhistory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptionhistory_ibfk_2` FOREIGN KEY (`old_plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptionhistory_ibfk_3` FOREIGN KEY (`new_plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserLogs`
--
ALTER TABLE `UserLogs`
  ADD CONSTRAINT `UserLogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserProfiles`
--
ALTER TABLE `UserProfiles`
  ADD CONSTRAINT `userprofiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserStatistics`
--
ALTER TABLE `UserStatistics`
  ADD CONSTRAINT `UserStatistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  ADD CONSTRAINT `usersubscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usersubscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`),
  ADD CONSTRAINT `usersubscriptions_ibfk_3` FOREIGN KEY (`auto_renewal_id`) REFERENCES `AutoRenewalSettings` (`setting_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

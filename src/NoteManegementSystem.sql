-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2025 at 03:04 PM
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
(4, '2025_06_10_142621_create_personal_access_tokens_table', 1);

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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

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
(14, 'App\\Models\\User', 3, 'auth_token', '52437bd29bfd58ef2e8230ae7c61ddf987e1d1b844756e31f810acccf9641625', '[\"*\"]', NULL, NULL, '2025-06-13 04:27:31', '2025-06-13 04:27:31');

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
(1, 'Premium Tháng', 'Gói đăng ký premium hàng tháng với đầy đủ tính năng', 1, 99000.00, '2025-05-20 03:05:07', '2025-05-20 03:05:07'),
(2, 'Premium Năm', 'Gói đăng ký premium hàng năm với đầy đủ tính năng, tiết kiệm 20%', 12, 950000.00, '2025-05-20 03:05:07', '2025-05-20 03:05:07');

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
(3, 3, 0, '2025-06-13 08:30:50', '2025-06-13 08:30:50'),
(4, 4, 0, '2025-06-13 11:27:04', '2025-06-13 11:27:04');

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
(1, 'Người Dùng Test', 'test@example.com', '$2y$12$d7l7nj0/pzfzTRWXaMxYXua3KYPaeRCyJahLJmk1pRclGcRei.nE2', NULL, 'email', 'active', NULL, NULL, '2025-06-13 00:54:19', '2025-06-11 06:52:12', '2025-06-13 00:54:19'),
(2, 'Người Dùng Test', 'test2023@example.com', '$2y$12$k.w7UZKrQTt7gfgjUyRRyuFsVnpSJF/UrgNbAEqy4SiR99./iP0cy', NULL, 'email', 'active', NULL, NULL, NULL, '2025-06-11 07:19:44', '2025-06-11 07:19:44'),
(3, 'N. Đức Thành', 'thanhndpd11083@gmail.com', '$2y$12$p5Cez.BhmsgET3jvk7G2lOPj20oOxTr32z5EIYMY8NicCmLrugT5G', 'https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=1378875216660203&width=200&ext=1752395449&hash=AT9ooWW0yj7--pN3PBznlNWD', 'facebook', 'active', NULL, NULL, '2025-06-13 04:27:31', '2025-06-13 01:30:50', '2025-06-13 04:27:31'),
(4, 'N. Đức Thành - FPL', 'nguyennguyenthanh2201@gmail.com', '$2y$12$ioiUVaTtvUz11uRC0AbBmecLfzrf06AZSE/QmZuy6ENr9ePWNWDSu', 'https://lh3.googleusercontent.com/a/ACg8ocLkCOkkdEoErqUAi60inOqx9_WCRHFYuEB3We6rFAkWJ2ozYoPO=s96-c', 'google', 'active', NULL, NULL, '2025-06-13 04:27:04', '2025-06-13 04:27:03', '2025-06-13 04:27:04');

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Events`
--
ALTER TABLE `Events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Friendships`
--
ALTER TABLE `Friendships`
  MODIFY `friendship_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GoalCollaboration`
--
ALTER TABLE `GoalCollaboration`
  MODIFY `collab_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GoalMembers`
--
ALTER TABLE `GoalMembers`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GoalProgress`
--
ALTER TABLE `GoalProgress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Goals`
--
ALTER TABLE `Goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `GoalShares`
--
ALTER TABLE `GoalShares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Milestones`
--
ALTER TABLE `Milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `NoteGoalLinks`
--
ALTER TABLE `NoteGoalLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `NoteMilestoneLinks`
--
ALTER TABLE `NoteMilestoneLinks`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Notes`
--
ALTER TABLE `Notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Notifications`
--
ALTER TABLE `Notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Payments`
--
ALTER TABLE `Payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `UserStatistics`
--
ALTER TABLE `UserStatistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT;

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

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 20, 2025 at 03:05 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `GoalManagementSystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `AISuggestionGoalLinks`
--

CREATE TABLE `AISuggestionGoalLinks` (
  `link_id` int(11) NOT NULL,
  `suggestion_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `AISuggestions`
--

CREATE TABLE `AISuggestions` (
  `suggestion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `suggestion_type` enum('goal_breakdown','priority','completion_forecast') NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `EventGoalLinks`
--

CREATE TABLE `EventGoalLinks` (
  `link_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `event_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `FileGoalLinks`
--

CREATE TABLE `FileGoalLinks` (
  `link_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `FileNoteLinks`
--

CREATE TABLE `FileNoteLinks` (
  `link_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Friendships`
--

CREATE TABLE `Friendships` (
  `friendship_id` int(11) NOT NULL,
  `user_id_1` int(11) NOT NULL,
  `user_id_2` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `GoalProgress`
--

CREATE TABLE `GoalProgress` (
  `progress_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `progress_value` float NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Goals`
--

CREATE TABLE `Goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('new','in_progress','completed','cancelled') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Milestones`
--

CREATE TABLE `Milestones` (
  `milestone_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `deadline` date DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `NoteMilestoneLinks`
--

CREATE TABLE `NoteMilestoneLinks` (
  `link_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `milestone_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Notes`
--

CREATE TABLE `Notes` (
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Notifications`
--

CREATE TABLE `Notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('reminder','friend_update','goal_progress','ai_suggestion') NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SubscriptionPlans`
--

CREATE TABLE `SubscriptionPlans` (
  `plan_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `SubscriptionPlans`
--

INSERT INTO `SubscriptionPlans` (`plan_id`, `name`, `description`, `duration`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Premium Tháng', 'Gói đăng ký premium hàng tháng với đầy đủ tính năng', 1, '99000.00', '2025-05-20 03:05:07', '2025-05-20 03:05:07'),
(2, 'Premium Năm', 'Gói đăng ký premium hàng năm với đầy đủ tính năng, tiết kiệm 20%', 12, '950000.00', '2025-05-20 03:05:07', '2025-05-20 03:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `UserProfiles`
--

CREATE TABLE `UserProfiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_premium` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `status` enum('active','banned','pending') DEFAULT 'active',
  `reset_token` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Table structure for table `UserSubscriptions`
--

CREATE TABLE `UserSubscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_status` enum('active','cancelled','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  ADD PRIMARY KEY (`pattern_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `SubscriptionPlans`
--
ALTER TABLE `SubscriptionPlans`
  ADD PRIMARY KEY (`plan_id`);

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
-- Indexes for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

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
-- AUTO_INCREMENT for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  MODIFY `pattern_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `SubscriptionPlans`
--
ALTER TABLE `SubscriptionPlans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `UserProfiles`
--
ALTER TABLE `UserProfiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `RecurringPatterns`
--
ALTER TABLE `RecurringPatterns`
  ADD CONSTRAINT `recurringpatterns_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserProfiles`
--
ALTER TABLE `UserProfiles`
  ADD CONSTRAINT `userprofiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserSubscriptions`
--
ALTER TABLE `UserSubscriptions`
  ADD CONSTRAINT `usersubscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usersubscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `SubscriptionPlans` (`plan_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- 1. Bảng Admins (sau Users)
CREATE TABLE `Admins` (
  `admin_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `role` ENUM('superadmin','moderator') DEFAULT 'moderator',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Bảng GoalShares (sau Goals)
CREATE TABLE `GoalShares` (
  `share_id` INT NOT NULL AUTO_INCREMENT,
  `goal_id` INT NOT NULL,
  `share_type` ENUM('private','public','friends','collaboration') DEFAULT 'private',
  `shared_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`share_id`),
  UNIQUE KEY `goal_id` (`goal_id`),
  FOREIGN KEY (`goal_id`) REFERENCES `Goals`(`goal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Bảng GoalCollaboration (sau GoalShares)
CREATE TABLE `GoalCollaboration` (
  `collab_id` INT NOT NULL AUTO_INCREMENT,
  `goal_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` ENUM('owner','member') DEFAULT 'member',
  `joined_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`collab_id`),
  UNIQUE KEY `goal_user` (`goal_id`,`user_id`),
  FOREIGN KEY (`goal_id`) REFERENCES `Goals`(`goal_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Bảng UserStatistics (sau UserProfiles, Friendships, Goals, Notes)
CREATE TABLE `UserStatistics` (
  `stat_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total_goals` INT DEFAULT 0,
  `total_notes` INT DEFAULT 0,
  `total_friends` INT DEFAULT 0,
  `is_premium` TINYINT(1) DEFAULT 0,
  `last_updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 5. Bảng RevenueStatistics (sau UserSubscriptions, Payments)
CREATE TABLE `RevenueStatistics` (
  `revenue_id` INT NOT NULL AUTO_INCREMENT,
  `stat_date` DATE NOT NULL,
  `total_revenue` DECIMAL(15,2) DEFAULT 0,
  `total_transactions` INT DEFAULT 0,
  PRIMARY KEY (`revenue_id`),
  UNIQUE KEY `stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 6. Bảng Payments (sau UserSubscriptions, SubscriptionPlans)
CREATE TABLE `Payments` (
  `payment_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('success','failed','pending') DEFAULT 'success',
  PRIMARY KEY (`payment_id`),
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `SubscriptionPlans`(`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 7. Bảng Reports (sau Goals, Notes, Users)
CREATE TABLE `Reports` (
  `report_id` INT NOT NULL AUTO_INCREMENT,
  `reporter_id` INT NOT NULL,
  `target_type` ENUM('goal','note','user') NOT NULL,
  `target_id` INT NOT NULL,
  `reason` TEXT,
  `status` ENUM('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  FOREIGN KEY (`reporter_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 8. Bảng UserLogs (sau Users)
CREATE TABLE `UserLogs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `target_type` VARCHAR(100),
  `target_id` INT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 9. Bảng AdminLogs (sau Admins)
CREATE TABLE `AdminLogs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `target_type` VARCHAR(100),
  `target_id` INT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `Admins`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 10. (Tùy chọn) Bảng Courses và CourseGoalLinks (nếu tích hợp khóa học)
CREATE TABLE `Courses` (
  `course_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `CourseGoalLinks` (
  `link_id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT NOT NULL,
  `goal_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `course_goal` (`course_id`,`goal_id`),
  FOREIGN KEY (`course_id`) REFERENCES `Courses`(`course_id`) ON DELETE CASCADE,
  FOREIGN KEY (`goal_id`) REFERENCES `Goals`(`goal_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

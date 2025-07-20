-- AI Microservice Database Structure
-- Database độc lập cho AI Analytics và Analysis
-- Không tham chiếu đến Laravel database

-- Create database
CREATE DATABASE IF NOT EXISTS `ai_analysis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ai_analysis`;

-- 1. User Behavior Analytics (chỉ lưu user_id reference)
CREATE TABLE `user_behavior_metrics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL, -- Reference to Laravel user ID (no foreign key)
  `session_duration` INT DEFAULT 0, -- phút
  `goals_created_count` INT DEFAULT 0,
  `goals_completed_count` INT DEFAULT 0,
  `milestones_achieved_count` INT DEFAULT 0,
  `avg_goal_completion_time` FLOAT DEFAULT 0, -- ngày
  `productivity_score` FLOAT DEFAULT 0, -- 0-10
  `last_activity_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Goal Analysis Results (lưu kết quả phân tích từ AI)
CREATE TABLE `goal_analysis_results` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `goal_id` INT NOT NULL, -- Reference to Laravel goal ID
  `user_id` INT NOT NULL, -- Reference to Laravel user ID
  `analysis_type` ENUM('goal_breakdown', 'priority', 'completion_forecast') NOT NULL,
  `complexity_score` FLOAT DEFAULT 0, -- 1-10
  `estimated_duration` INT DEFAULT 0, -- ngày
  `success_probability` FLOAT DEFAULT 0, -- 0-1
  `analysis_data` JSON, -- Kết quả phân tích chi tiết
  `confidence` FLOAT DEFAULT 0, -- 0-1
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `goal_user_idx` (`goal_id`, `user_id`),
  KEY `analysis_type_idx` (`analysis_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. User Insights (phân tích hành vi user)
CREATE TABLE `user_insights` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL, -- Reference to Laravel user ID
  `insight_type` VARCHAR(50) NOT NULL, -- 'productivity', 'patterns', 'recommendations'
  `insight_data` JSON, -- Dữ liệu insight chi tiết
  `confidence` FLOAT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_type_idx` (`user_id`, `insight_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. AI Model Performance Metrics
CREATE TABLE `ai_model_metrics` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `model_name` VARCHAR(100) NOT NULL,
  `model_type` ENUM('goal_breakdown', 'priority', 'completion_forecast', 'insights') NOT NULL,
  `accuracy` FLOAT DEFAULT 0,
  `precision_score` FLOAT DEFAULT 0,
  `recall_score` FLOAT DEFAULT 0,
  `f1_score` FLOAT DEFAULT 0,
  `last_trained` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `training_data_size` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `model_name` (`model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. AI Request Logs (monitoring và debugging)
CREATE TABLE `ai_request_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `endpoint` VARCHAR(200) NOT NULL,
  `user_id` INT,
  `request_data` JSON,
  `response_data` JSON,
  `processing_time_ms` INT,
  `status_code` INT,
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `endpoint_idx` (`endpoint`),
  KEY `user_id_idx` (`user_id`),
  KEY `status_code_idx` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Training Dataset (cho machine learning)
CREATE TABLE `training_dataset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `goal_data` JSON, -- Input features
  `outcome` ENUM('success', 'partial', 'failed') NOT NULL,
  `completion_time` INT, -- Thời gian thực tế hoàn thành
  `features` JSON, -- Các features được extract
  `label` VARCHAR(50), -- Label cho supervised learning
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_outcome_idx` (`user_id`, `outcome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default model metrics
INSERT INTO `ai_model_metrics` (`model_name`, `model_type`, `accuracy`, `is_active`) VALUES
('goal_breakdown_v1', 'goal_breakdown', 0.75, TRUE),
('priority_analyzer_v1', 'priority', 0.70, TRUE),
('completion_predictor_v1', 'completion_forecast', 0.68, TRUE),
('user_insights_v1', 'insights', 0.65, TRUE); 
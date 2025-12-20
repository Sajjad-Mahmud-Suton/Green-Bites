-- ============================================
-- Green Bites - Database Changes Log
-- ============================================
-- This file contains all database modifications
-- Run this file after importing green_bites_schema.sql
-- ============================================

-- Last Updated: December 20, 2025

-- ============================================
-- CHANGE LOG
-- ============================================

-- [2025-12-20] Added is_seen column to complaints table
ALTER TABLE `complaints` ADD COLUMN IF NOT EXISTS `is_seen` TINYINT(1) DEFAULT 0 AFTER `image_path`;

-- [2025-12-20] Password resets table (if not exists)
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- FUTURE CHANGES WILL BE ADDED BELOW
-- ============================================

-- [2025-12-20] Added bill_number column to orders table
ALTER TABLE `orders` ADD COLUMN `bill_number` VARCHAR(20) AFTER `id`;
ALTER TABLE `orders` ADD UNIQUE INDEX `idx_bill_number` (`bill_number`);

-- Update existing orders with bill numbers (run this to add bill numbers to all old orders)
UPDATE orders SET bill_number = CONCAT('GB-', DATE_FORMAT(order_date, '%Y%m%d'), '-', LPAD(id, 4, '0')) WHERE bill_number IS NULL;

-- ============================================
-- SECURITY ENHANCEMENTS - December 20, 2025
-- ============================================

-- [2025-12-20] Add login_attempts tracking table for brute force protection
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [2025-12-20] Security logs table for audit trail
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `log_type` (`log_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [2025-12-20] Add last_login column to users table
ALTER TABLE `users` ADD COLUMN `last_login` datetime DEFAULT NULL;

-- [2025-12-20] Add last_login column to admins table
ALTER TABLE `admins` ADD COLUMN `last_login` datetime DEFAULT NULL;

-- [2025-12-20] Create blocked_ips table
CREATE TABLE IF NOT EXISTS `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `blocked_until` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


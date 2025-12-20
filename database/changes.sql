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


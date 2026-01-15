-- ╔═══════════════════════════════════════════════════════════════════════════╗
-- ║  GREEN BITES - User Management & Manual Order Migration                    ║
-- ║  This migration adds:                                                      ║
-- ║  1. User status column (active/paused/suspended)                          ║
-- ║  2. added_by column to track who created the user                         ║
-- ║  3. status_changed_by to track who changed user status                    ║
-- ║  4. is_manual_order flag for admin-placed orders                          ║
-- ║  5. manual_order_by column for admin ID who placed the order              ║
-- ╚═══════════════════════════════════════════════════════════════════════════╝

-- ═══════════════════════════════════════════════════════════════════════════
-- SECTION 1: ADD USER STATUS COLUMNS
-- ═══════════════════════════════════════════════════════════════════════════

-- Add status column to users table
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'paused', 'suspended') NOT NULL DEFAULT 'active' AFTER `phone`;

-- Add added_by column (NULL for self-registered, admin_id for admin-created)
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `added_by` INT(11) DEFAULT NULL AFTER `status`;

-- Add status_changed_by column to track who changed the status
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `status_changed_by` INT(11) DEFAULT NULL AFTER `added_by`;

-- Add status_changed_at timestamp
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `status_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `status_changed_by`;


-- ═══════════════════════════════════════════════════════════════════════════
-- SECTION 2: ADD MANUAL ORDER COLUMNS TO ORDERS TABLE
-- ═══════════════════════════════════════════════════════════════════════════

-- Add is_manual_order flag
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `is_manual_order` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

-- Add manual_order_by column (admin_id who placed the order)
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `manual_order_by` INT(11) DEFAULT NULL AFTER `is_manual_order`;


-- ═══════════════════════════════════════════════════════════════════════════
-- SECTION 3: CREATE INDEXES FOR PERFORMANCE
-- ═══════════════════════════════════════════════════════════════════════════

-- Index for user status filtering
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_user_status` (`status`);

-- Index for manual orders filtering
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_manual_orders` (`is_manual_order`);


-- ═══════════════════════════════════════════════════════════════════════════
-- SECTION 4: UPDATE EXISTING USERS TO ACTIVE STATUS
-- ═══════════════════════════════════════════════════════════════════════════

UPDATE `users` SET `status` = 'active' WHERE `status` IS NULL OR `status` = '';

-- ╔═══════════════════════════════════════════════════════════════════════════╗
-- ║                    PROFIT TRACKING DATABASE MIGRATION                     ║
-- ╠═══════════════════════════════════════════════════════════════════════════╣
-- ║  This migration adds:                                                     ║
-- ║    1. buying_price column to menu_items table                             ║
-- ║    2. profits table for tracking profit per order item                    ║
-- ║    3. Backfill profits for existing delivered orders                      ║
-- ╚═══════════════════════════════════════════════════════════════════════════╝

-- ═══════════════════════════════════════════════════════════════════════════
-- STEP 1: Add buying_price column to menu_items table
-- ═══════════════════════════════════════════════════════════════════════════

ALTER TABLE `menu_items` 
ADD COLUMN `buying_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 
AFTER `price`;

-- Update existing items: Set buying_price to 80% of selling price (20% margin assumption)
UPDATE `menu_items` 
SET `buying_price` = ROUND(`price` * 0.8, 2) 
WHERE `buying_price` = 0;

-- ═══════════════════════════════════════════════════════════════════════════
-- STEP 2: Create profits table
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `profits` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `buying_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `profit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `revenue` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `investment` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `calculated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_calculated_at` (`calculated_at`),
  KEY `idx_order_product` (`order_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════════════════
-- STEP 3: Create profit_summary table for quick aggregations
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `profit_summary` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `period_type` ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `total_revenue` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_investment` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_profit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_orders` INT(11) NOT NULL DEFAULT 0,
  `total_items_sold` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_period_unique` (`period_type`, `period_start`),
  KEY `idx_period_type` (`period_type`),
  KEY `idx_period_start` (`period_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════════════════
-- END OF MIGRATION
-- ═══════════════════════════════════════════════════════════════════════════

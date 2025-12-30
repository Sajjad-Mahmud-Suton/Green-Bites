-- ═══════════════════════════════════════════════════════════════════════════
-- GREEN BITES - Add Quantity Column for Inventory Management
-- Run this SQL to add stock tracking functionality
-- ═══════════════════════════════════════════════════════════════════════════

-- Add quantity column to menu_items table
ALTER TABLE `menu_items` 
ADD COLUMN `quantity` INT(11) NOT NULL DEFAULT 10 AFTER `is_available`;

-- Update all existing menu items to have quantity of 10
UPDATE `menu_items` SET `quantity` = 10 WHERE `quantity` IS NULL OR `quantity` = 0;

-- Optional: Add index for faster stock queries
CREATE INDEX idx_menu_quantity ON menu_items(quantity);

-- Show updated table structure
DESCRIBE menu_items;

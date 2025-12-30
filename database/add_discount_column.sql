-- Add discount percentage column to menu_items table
ALTER TABLE menu_items ADD COLUMN discount_percent INT DEFAULT 0 AFTER price;

-- Example: To add 20% discount to an item
-- UPDATE menu_items SET discount_percent = 20 WHERE id = 1;

-- ALTER TABLE statements for complaints table
-- This script will modify the existing complaints table to match the required structure
-- Run this in phpMyAdmin or MySQL command line
-- 
-- IMPORTANT: Run these statements one at a time. If a statement fails because a column 
-- already exists or doesn't exist, that's okay - just skip to the next one.

USE green_bites;

-- Step 1: Create table if it doesn't exist
CREATE TABLE IF NOT EXISTS complaints (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: If table exists, add/modify columns
-- Run these one at a time and skip any that give errors

-- Add id column if missing (run only if table exists but has no id)
-- ALTER TABLE complaints ADD COLUMN id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT FIRST;

-- Modify id to be auto_increment if it exists but isn't auto_increment
-- ALTER TABLE complaints MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT;

-- Add name column (skip if already exists)
-- ALTER TABLE complaints ADD COLUMN name VARCHAR(100) NOT NULL AFTER id;

-- Add email column (skip if already exists)
-- ALTER TABLE complaints ADD COLUMN email VARCHAR(150) NOT NULL AFTER name;

-- Rename old complaint text columns to 'message' (run only the one that matches your column name)
-- Uncomment the line that matches your existing column name:
-- ALTER TABLE complaints CHANGE COLUMN complain message TEXT NOT NULL;
-- ALTER TABLE complaints CHANGE COLUMN complaint message TEXT NOT NULL;
-- ALTER TABLE complaints CHANGE COLUMN details message TEXT NOT NULL;
-- ALTER TABLE complaints CHANGE COLUMN text message TEXT NOT NULL;
-- ALTER TABLE complaints CHANGE COLUMN description message TEXT NOT NULL;

-- If message column doesn't exist at all, add it
-- ALTER TABLE complaints ADD COLUMN message TEXT NOT NULL AFTER email;

-- Add image_path column (skip if already exists)
-- ALTER TABLE complaints ADD COLUMN image_path VARCHAR(255) NULL AFTER message;

-- Add created_at column (skip if already exists)
-- ALTER TABLE complaints ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER image_path;

-- Step 3: Verify the final structure
DESCRIBE complaints;


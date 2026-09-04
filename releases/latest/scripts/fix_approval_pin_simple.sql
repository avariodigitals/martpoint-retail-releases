-- Simple fix: Run this in phpMyAdmin SQL tab for your martpoint database
-- This adds the missing approval_pin column safely

-- Step 1: Check if column exists (optional, for info)
-- SELECT COLUMN_NAME FROM information_schema.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_users' AND COLUMN_NAME = 'approval_pin';

-- Step 2: Add the column (run this)
ALTER TABLE db_users ADD COLUMN approval_pin VARCHAR(64) NULL DEFAULT NULL;

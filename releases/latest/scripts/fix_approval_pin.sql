-- Fix: Add approval_pin column to db_users if it doesn't exist
-- Run this in phpMyAdmin or MySQL CLI

-- Check if column exists first (safe for all MySQL versions)
SET @dbname = DATABASE();
SET @tablename = 'db_users';
SET @columnname = 'approval_pin';

SET @sql = CONCAT(
  'SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS',
  ' WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
);
PREPARE stmt FROM @sql;
SET @db = @dbname, @tbl = @tablename, @col = @columnname;
EXECUTE stmt USING @db, @tbl, @col;
DEALLOCATE PREPARE stmt;

SET @addcol = IF(@col_exists = 0,
  'ALTER TABLE db_users ADD COLUMN approval_pin VARCHAR(64) NULL DEFAULT NULL',
  'SELECT "Column already exists" as message'
);
PREPARE stmt FROM @addcol;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

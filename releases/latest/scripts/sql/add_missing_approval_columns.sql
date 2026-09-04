-- Add missing approval columns to existing db_approval_settings table
-- Run this in phpMyAdmin SQL tab

-- Check if columns exist before adding (safe for all MySQL versions)
SET @dbname = DATABASE();
SET @tablename = 'db_approval_settings';

-- Helper procedure to add column if not exists
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS AddColumnIfNotExists(
    IN pTable VARCHAR(64),
    IN pColumn VARCHAR(64),
    IN pDefinition VARCHAR(255)
)
BEGIN
    DECLARE colCount INT;
    SELECT COUNT(*) INTO colCount
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = pTable
      AND COLUMN_NAME = pColumn;
    
    IF colCount = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', pTable, ' ADD COLUMN ', pColumn, ' ', pDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('Added column: ', pColumn) AS result;
    ELSE
        SELECT CONCAT('Column already exists: ', pColumn) AS result;
    END IF;
END //

DELIMITER ;

-- Add negative_stock_sale columns (zero quantity / out-of-stock sale)
CALL AddColumnIfNotExists('db_approval_settings', 'negative_stock_sale_approval_enabled', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL AddColumnIfNotExists('db_approval_settings', 'negative_stock_sale_approval_method', 'VARCHAR(30) NOT NULL DEFAULT \'none\'');

-- Add hold_delete columns (delete held invoice)
CALL AddColumnIfNotExists('db_approval_settings', 'hold_delete_approval_enabled', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL AddColumnIfNotExists('db_approval_settings', 'hold_delete_approval_method', 'VARCHAR(30) NOT NULL DEFAULT \'none\'');

-- Clean up
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

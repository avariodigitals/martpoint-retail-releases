-- MySQL-safe fixes for MariaDB-only migration statements
-- Applies all missing columns/indexes that were skipped due to IF NOT EXISTS syntax errors

DELIMITER $$

DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists$$

CREATE PROCEDURE mp_add_column_if_not_exists(
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
        SELECT CONCAT('Added column: ', pTable, '.', pColumn) AS result;
    ELSE
        SELECT CONCAT('Column already exists: ', pTable, '.', pColumn) AS result;
    END IF;
END$$

DROP PROCEDURE IF EXISTS mp_add_index_if_not_exists$$

CREATE PROCEDURE mp_add_index_if_not_exists(
    IN pTable VARCHAR(64),
    IN pIndex VARCHAR(64),
    IN pDefinition VARCHAR(255)
)
BEGIN
    DECLARE idxCount INT;
    SELECT COUNT(*) INTO idxCount
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = pTable
      AND INDEX_NAME = pIndex;
    IF idxCount = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', pTable, ' ADD ', pDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('Added index: ', pTable, '.', pIndex) AS result;
    ELSE
        SELECT CONCAT('Index already exists: ', pTable, '.', pIndex) AS result;
    END IF;
END$$

DELIMITER ;

-- martpoint_approvals.sql
CALL mp_add_column_if_not_exists('db_users', 'approval_pin', 'VARCHAR(64) NULL DEFAULT NULL');

-- martpoint_auto_update_v1.sql
CALL mp_add_column_if_not_exists('db_sitesettings', 'update_channel_url', 'VARCHAR(500) DEFAULT NULL');

-- martpoint_bnpl_and_offline_po.sql
CALL mp_add_column_if_not_exists('db_approval_settings', 'bnpl_approval_enabled', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL mp_add_column_if_not_exists('db_approval_settings', 'bnpl_approval_method', 'VARCHAR(30) NOT NULL DEFAULT \'none\'');
CALL mp_add_column_if_not_exists('db_customers', 'nin_bvn', 'VARCHAR(50) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_customers', 'nin_verified', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL mp_add_column_if_not_exists('db_customers', 'nin_verified_at', 'DATETIME DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived_by', 'VARCHAR(50) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived_at', 'DATETIME DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_store', 'nin_api_enabled', 'TINYINT(1) NOT NULL DEFAULT 0');
CALL mp_add_column_if_not_exists('db_store', 'nin_api_url', 'VARCHAR(500) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_store', 'nin_api_key', 'VARCHAR(500) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_store', 'nin_api_provider', 'VARCHAR(50) DEFAULT \'ninbvnportal\'');
CALL mp_add_column_if_not_exists('db_store', 'nin_api_cost', 'DECIMAL(10,2) NOT NULL DEFAULT 50.00');

-- martpoint_payment_modes.sql
CALL mp_add_column_if_not_exists('db_salespayments', 'payment_mode_id', 'INT DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_salespayments', 'payment_reference', 'VARCHAR(255) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmation_status', 'TINYINT(1) DEFAULT 1');
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmed_by', 'VARCHAR(50) DEFAULT NULL');
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmed_date', 'DATETIME DEFAULT NULL');
CALL mp_add_index_if_not_exists('db_salespayments', 'idx_payment_mode', 'INDEX idx_payment_mode (payment_mode_id)');
CALL mp_add_index_if_not_exists('db_salespayments', 'idx_confirmation', 'INDEX idx_confirmation (confirmation_status)');

-- batch/lot: mfg_date on db_items (needed for migration)
CALL mp_add_column_if_not_exists('db_items', 'mfg_date', 'DATE NULL AFTER expire_date');

-- Clean up helper procedures
DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;
DROP PROCEDURE IF EXISTS mp_add_index_if_not_exists;

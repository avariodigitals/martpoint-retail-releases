-- ============================================================
-- MartPoint Retail — Payment Modes Module Migration
-- ============================================================

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
    END IF;
END$$
DELIMITER ;

-- 1. Create db_payment_modes table
CREATE TABLE IF NOT EXISTS db_payment_modes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  code VARCHAR(50) NOT NULL COMMENT 'Unique code slug e.g. cash, pos, bank_transfer',
  name VARCHAR(100) NOT NULL COMMENT 'Display name e.g. Cash, Bank Transfer',
  description TEXT DEFAULT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only one mode can be default',
  is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Built-in modes cannot be deleted',
  sort_order INT NOT NULL DEFAULT 0,
  requires_reference TINYINT(1) NOT NULL DEFAULT 0,
  requires_confirmation TINYINT(1) NOT NULL DEFAULT 0,
  affects_cash_in_hand TINYINT(1) NOT NULL DEFAULT 0,
  icon_class VARCHAR(100) DEFAULT NULL COMMENT 'FontAwesome icon class e.g. fa-money',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_date DATE DEFAULT NULL,
  created_time TIME DEFAULT NULL,
  created_by VARCHAR(50) DEFAULT NULL,
  system_ip VARCHAR(50) DEFAULT NULL,
  system_name VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uk_code_store (code, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed default payment modes
-- First clear any existing to avoid duplicates on re-run
-- We use INSERT IGNORE because of unique constraint

INSERT IGNORE INTO db_payment_modes (store_id, code, name, description, enabled, is_default, is_system, sort_order, requires_reference, requires_confirmation, affects_cash_in_hand, icon_class, status) VALUES
(1, 'cash', 'Cash', 'Physical cash collected at counter', 1, 1, 1, 1, 0, 0, 1, 'fa-money', 1),
(1, 'pos', 'POS', 'POS terminal / card reader payment', 1, 0, 1, 2, 1, 1, 0, 'fa-credit-card', 1),
(1, 'bank_transfer', 'Bank Transfer', 'Direct bank transfer from customer', 1, 0, 1, 3, 1, 1, 0, 'fa-university', 1),
(1, 'card', 'Card', 'Debit or credit card payment', 1, 0, 1, 4, 1, 1, 0, 'fa-credit-card-alt', 1),
(1, 'paystack', 'Paystack', 'Online payment via Paystack', 1, 0, 1, 5, 1, 1, 0, 'fa-link', 1),
(1, 'credit_sale', 'Credit Sale', 'Customer pays later / on credit', 1, 0, 1, 6, 0, 0, 0, 'fa-handshake-o', 1),
(1, 'split_payment', 'Split Payment', 'Multiple payment methods combined', 1, 0, 1, 7, 0, 0, 0, 'fa-columns', 1),
(1, 'wallet', 'Wallet', 'Customer wallet / store credit payment', 0, 0, 1, 8, 0, 0, 0, 'fa-google-wallet', 1),
(1, 'cheque', 'Cheque', 'Payment by cheque', 0, 0, 1, 9, 1, 1, 0, 'fa-file-text-o', 1),
(1, 'other', 'Other', 'Any other payment method', 0, 0, 1, 10, 0, 0, 0, 'fa-ellipsis-h', 1),
(1, 'flutterwave', 'Flutterwave', 'Online payment via Flutterwave', 1, 0, 1, 11, 1, 1, 0, 'fa-link', 1),
(1, 'moniepoint', 'Moniepoint', 'Payment via Moniepoint', 1, 0, 1, 12, 1, 1, 0, 'fa-mobile', 1),
(1, 'opay', 'Opay', 'Payment via Opay', 1, 0, 1, 13, 1, 1, 0, 'fa-mobile', 1);

-- 3. Update db_salespayments to support payment_mode_id and confirmation
-- These columns may already exist or not — add them safely (MySQL-safe)
CALL mp_add_column_if_not_exists('db_salespayments', 'payment_mode_id', "INT DEFAULT NULL AFTER payment_type");
CALL mp_add_column_if_not_exists('db_salespayments', 'payment_reference', "VARCHAR(255) DEFAULT NULL AFTER payment_note");
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmation_status', "TINYINT(1) DEFAULT 1 COMMENT '0=Pending, 1=Confirmed' AFTER payment_reference");
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmed_by', "VARCHAR(50) DEFAULT NULL AFTER confirmation_status");
CALL mp_add_column_if_not_exists('db_salespayments', 'confirmed_date', "DATETIME DEFAULT NULL AFTER confirmed_by");

-- 4. Map existing payment_type values to payment_mode_id
-- This runs once to link old data to new table
UPDATE db_salespayments sp
JOIN db_payment_modes pm ON sp.payment_type = pm.name
SET sp.payment_mode_id = pm.id
WHERE sp.payment_mode_id IS NULL;

-- If any old records don't match, try matching by lowercase
UPDATE db_salespayments sp
JOIN db_payment_modes pm ON LOWER(sp.payment_type) = LOWER(pm.code)
SET sp.payment_mode_id = pm.id
WHERE sp.payment_mode_id IS NULL;

-- 5. Create index for faster lookups (MySQL-safe)
CALL mp_add_index_if_not_exists('db_salespayments', 'idx_payment_mode', 'INDEX idx_payment_mode (payment_mode_id)');
CALL mp_add_index_if_not_exists('db_salespayments', 'idx_confirmation', 'INDEX idx_confirmation (confirmation_status)');

DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;
DROP PROCEDURE IF EXISTS mp_add_index_if_not_exists;

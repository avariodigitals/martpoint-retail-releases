-- 4.0.9.124 — SMS / OTP module
--
-- Two changes:
--  1. db_bulksmsng — BulkSMSNigeria credentials (per store). The table is
--     normally self-healed by Storefront_model's schema guard; this makes it
--     explicit for existing installs.
--  2. db_storefront_customer_otp.purpose — isolates OTP flows so a code
--     issued for 'admin_login' or 'pos_confirm' cannot be replayed against
--     'storefront'. Existing rows default to 'storefront' (their only
--     possible origin before this migration).
--
-- Idempotent: uses the information_schema + PREPARE guard because older MySQL
-- has no ADD COLUMN IF NOT EXISTS, and DO 0 (not SELECT 1) in the else branch
-- so re-runs do not litter the update log with bare "1" rows.

CREATE TABLE IF NOT EXISTS db_bulksmsng (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    store_id INT(11) NOT NULL,
    api_token TEXT NOT NULL,
    sender_id VARCHAR(50) NOT NULL DEFAULT 'BulkSMS',
    base_url VARCHAR(255) NOT NULL DEFAULT 'https://www.bulksmsnigeria.com/api',
    gateway VARCHAR(20) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @tbl := 'db_storefront_customer_otp';
SET @col := 'purpose';

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
    CONCAT('ALTER TABLE ', @tbl, ' ADD COLUMN purpose VARCHAR(32) NOT NULL DEFAULT ''storefront'' AFTER otp'),
    'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND INDEX_NAME = 'idx_otp_lookup') = 0,
    CONCAT('ALTER TABLE ', @tbl, ' ADD INDEX idx_otp_lookup (store_id, purpose, phone, email, expires_at)'),
    'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- One credential row per store per provider.
--
-- Without this, get_credentials() uses ->row(), which silently returns the
-- FIRST match when duplicates exist — an SMS could then be sent from the
-- wrong sender ID / wrong wallet with no error. The write path
-- (Sms_model::_provider_upsert) already checks before inserting, so this only
-- tightens what was previously convention. Guarded because older MySQL has no
-- ADD UNIQUE KEY IF NOT EXISTS, and a pre-existing duplicate would otherwise
-- abort the whole migration.
SET @tbl := 'db_bulksmsng';

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND INDEX_NAME = 'uk_store') = 0
    AND (SELECT COUNT(*) FROM (SELECT store_id FROM db_bulksmsng GROUP BY store_id HAVING COUNT(*) > 1) d) = 0,
    CONCAT('ALTER TABLE ', @tbl, ' ADD UNIQUE KEY uk_store (store_id)'),
    'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- B2B fields for Distributor / Wholesaler support
-- Customers: payment terms in days (e.g. 7, 30, 60)
-- Warehouses / depots: branch type and distribution center flag
-- Idempotent and safe to re-run (information_schema guards).
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'payment_terms_days');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_customers` ADD COLUMN `payment_terms_days` INT(5) NULL DEFAULT 0 AFTER `credit_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_warehouse' AND column_name = 'branch_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_warehouse` ADD COLUMN `branch_type` VARCHAR(50) NULL DEFAULT ''branch'' AFTER `warehouse_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_warehouse' AND column_name = 'is_distribution_center');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_warehouse` ADD COLUMN `is_distribution_center` TINYINT(1) NOT NULL DEFAULT 0 AFTER `branch_type`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Mark applied
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.6-b2b', NOW());

SET FOREIGN_KEY_CHECKS = 1;

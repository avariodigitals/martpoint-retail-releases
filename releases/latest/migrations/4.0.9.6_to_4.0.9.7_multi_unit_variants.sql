-- v4.0.9.7: Multi-Unit Selling for Variants
-- Adds is_template flag to db_item_selling_units so parent products can
-- define packaging templates that are cloned to child variants.
-- Idempotent and safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- 1. Add is_template column if missing
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND column_name = 'is_template');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD COLUMN `is_template` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_default`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Existing rows are real selling units, not templates
UPDATE `db_item_selling_units` SET `is_template` = 0 WHERE `is_template` IS NULL;

-- 3. Make it faster to distinguish templates from sellable rows
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND index_name = 'idx_is_template');
SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD KEY `idx_is_template` (`is_template`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Migration bookkeeping
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.7', NOW());

SET FOREIGN_KEY_CHECKS = 1;

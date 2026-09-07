-- MartPoint Online Excluded Flag Migration (v4.0.9)
-- Adds online_excluded flag to db_items so "Sync All" respects manual unpublish decisions.
-- When a user manually turns OFF a product, online_excluded=1 prevents sync from re-publishing it.
-- Idempotent. Safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'online_excluded');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `online_excluded` TINYINT(1) NOT NULL DEFAULT 0 AFTER `publish_online`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Record migration
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9-online-excluded', NOW());

SET FOREIGN_KEY_CHECKS = 1;

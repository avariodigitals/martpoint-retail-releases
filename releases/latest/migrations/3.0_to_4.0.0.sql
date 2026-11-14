-- ============================================================
-- MartPoint Retail v4.0.0 Migration
-- From: 3.0
-- To: 4.0.0
-- Tables: Auto-Update System, Schema Migrations Tracker
-- ============================================================

-- 1. Auto-Update Job Tracking Table
CREATE TABLE IF NOT EXISTS `db_system_updates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL DEFAULT 1,
  `from_version` VARCHAR(20) NOT NULL,
  `to_version` VARCHAR(20) NOT NULL,
  `status` ENUM('pending','running','success','failed','restored') NOT NULL DEFAULT 'pending',
  `current_step` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `total_steps` TINYINT UNSIGNED NOT NULL DEFAULT 8,
  `step_label` VARCHAR(100) DEFAULT NULL,
  `backup_db_path` VARCHAR(500) DEFAULT NULL,
  `backup_files_path` VARCHAR(500) DEFAULT NULL,
  `log` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Schema Migrations Tracker (prevents re-running same SQL)
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(20) NOT NULL,
  `filename` VARCHAR(200) NOT NULL,
  `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_file` (`version`,`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add Update Channel URL to Site Settings
-- MySQL 5.7 safe: check column exists first
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns 
  WHERE table_schema = DATABASE() 
  AND table_name = 'db_sitesettings' 
  AND column_name = 'update_channel_url'
);

SET @sql = IF(@col_exists = 0, 
  'ALTER TABLE `db_sitesettings` ADD COLUMN `update_channel_url` VARCHAR(500) DEFAULT NULL', 
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Mark this migration as applied (idempotent)
INSERT IGNORE INTO `db_schema_migrations` (`version`, `filename`) 
VALUES ('4.0.0', '3.0_to_4.0.0.sql');

-- 5. Update sitesettings version to 4.0.0
UPDATE `db_sitesettings` SET `version` = '4.0.0' WHERE `id` = 1;

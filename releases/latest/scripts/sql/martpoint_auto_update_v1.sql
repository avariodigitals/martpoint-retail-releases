-- ============================================================
-- MartPoint Retail — Auto-Update System (Phase 1)
-- Run this in phpMyAdmin once to add the update-tracking tables
-- ============================================================

-- Tracks every auto-update job (status, steps, logs, backups)
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

-- Tracks schema migrations that have already been applied
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(20) NOT NULL,
  `filename` VARCHAR(200) NOT NULL,
  `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_file` (`version`,`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
DELIMITER ;

-- Add update channel URL to sitesettings (optional, defaults to GitHub raw)
CALL mp_add_column_if_not_exists('db_sitesettings', 'update_channel_url', 'VARCHAR(500) DEFAULT NULL');
DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;

-- Seed update channel with default GitHub raw URL (change after you create your repo)
UPDATE `db_sitesettings` SET `update_channel_url` = 'https://raw.githubusercontent.com/YOUR_USERNAME/martpoint-retail-releases/main/releases/latest/' WHERE `id` = 1;

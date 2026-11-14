-- MartPoint Attribute-Driven Variants Migration (v4.0.7 patch)
-- Adds master attribute types/values and per-product attribute flags.
-- Used by new fashion/electronics/footwear/cosmetics workflows.
-- Idempotent. Safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ============================================================
-- 1. Master attribute table (one row per type/value combination)
--    e.g. attribute_type='size', attribute_value='S'
--         attribute_type='colour', attribute_value='Red'
-- ============================================================
CREATE TABLE IF NOT EXISTS `db_attributes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) NOT NULL,
  `attribute_type` VARCHAR(30) NOT NULL COMMENT 'size, colour, length, material, storage, shade',
  `attribute_value` VARCHAR(100) NOT NULL,
  `sort_order` INT(3) DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(50) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY `uk_store_attr` (`store_id`,`attribute_type`,`attribute_value`),
  KEY `store_id` (`store_id`),
  KEY `attribute_type` (`attribute_type`),
  KEY `attribute_value` (`attribute_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Product attribute flags — which attribute types a product uses
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'attribute_types_json');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `attribute_types_json` JSON NULL DEFAULT NULL COMMENT ''Attribute types this product uses: [\"size\",\"colour\",\"length\"]''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Record migration
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.7-attr', NOW());

SET FOREIGN_KEY_CHECKS = 1;

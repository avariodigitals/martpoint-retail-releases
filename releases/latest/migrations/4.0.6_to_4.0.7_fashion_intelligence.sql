-- MartPoint 4.0.6 -> 4.0.7 Migration — Fashion Intelligence Pack
-- Closes the gap between the /industries/fashion-stores marketing page and
-- the shipped product. Adds:
--   1. Multi-attribute variant matrix (size x colour) via db_variant_attributes
--   2. Centralised promotions with minimum-price / margin protection
--   3. Reorder suggestion engine parameters on db_items
--   4. Permissions for the new Fashion reports & modules
-- Idempotent: every statement guards against re-runs.
-- Uses PREPARE/EXECUTE pattern for conditional ALTERs (MySQL 5.7+ compatible).
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ============================================================
-- 1. VARIANT ATTRIBUTES (size, colour, material, pattern, fit)
-- ============================================================

-- 1a. Add attribute_type column to db_variants (if not present)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_variants' AND column_name = 'attribute_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_variants` ADD COLUMN `attribute_type` VARCHAR(30) NULL DEFAULT NULL AFTER `variant_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 1b. Add attribute_value column to db_variants (if not present)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_variants' AND column_name = 'attribute_value');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_variants` ADD COLUMN `attribute_value` VARCHAR(100) NULL DEFAULT NULL AFTER `attribute_type`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 1c. Create junction table for multi-attribute variants
CREATE TABLE IF NOT EXISTS `db_variant_attributes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) DEFAULT NULL,
  `variant_id` INT(5) NOT NULL,
  `attribute_type` VARCHAR(30) NOT NULL COMMENT 'size, colour, material, pattern, fit',
  `attribute_value` VARCHAR(100) NOT NULL,
  `sort_order` INT(3) DEFAULT 0,
  `created_date` DATE DEFAULT NULL,
  UNIQUE KEY `uk_variant_attr` (`variant_id`,`attribute_type`),
  KEY `store_id` (`store_id`),
  KEY `attribute_type` (`attribute_type`),
  KEY `attribute_value` (`attribute_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CENTRALISED PROMOTIONS (with min-price / margin protection)
-- ============================================================
CREATE TABLE IF NOT EXISTS `db_promotions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) NOT NULL,
  `promotion_code` VARCHAR(50) DEFAULT NULL COMMENT 'Human-readable code',
  `promotion_name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `discount_type` VARCHAR(20) NOT NULL DEFAULT 'Percentage' COMMENT 'Percentage or Fixed',
  `discount_value` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `min_price_rule` DECIMAL(20,4) NULL DEFAULT NULL COMMENT 'Never sell below this price (margin protection)',
  `min_margin_pct` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Never drop below this % margin over cost',
  `applies_to` VARCHAR(20) NOT NULL DEFAULT 'all' COMMENT 'all, category, brand, items',
  `category_id` INT(10) NULL DEFAULT NULL,
  `brand_id` INT(5) NULL DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` INT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(50) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  KEY `store_id` (`store_id`),
  KEY `status` (`status`),
  KEY `applies_to` (`applies_to`),
  KEY `date_range` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_promotion_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT NOT NULL,
  `item_id` INT(5) NOT NULL,
  `store_id` INT(11) DEFAULT NULL,
  KEY `promotion_id` (`promotion_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. REORDER ENGINE PARAMETERS on db_items
-- ============================================================

-- 3a. reorder_point
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'reorder_point');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `reorder_point` INT(10) NULL DEFAULT NULL AFTER `alert_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3b. reorder_qty
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'reorder_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `reorder_qty` INT(10) NULL DEFAULT NULL COMMENT ''Suggested reorder quantity'' AFTER `reorder_point`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3c. lead_time_days
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'lead_time_days');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `lead_time_days` INT(5) NULL DEFAULT NULL COMMENT ''Supplier lead time in days'' AFTER `reorder_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 4. PERMISSIONS — grant new fashion report perms to existing roles
-- ============================================================

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'variant_attribute_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'variant_attribute_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'reorder_suggestion_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'reorder_suggestion_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'promotions_manage'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'promotions_manage'
WHERE d.permissions = 'items_edit' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'sell_through_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'sell_through_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

-- ============================================================
-- 5. Record migration + bump version
-- ============================================================
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.7', NOW());

UPDATE `db_sitesettings` SET `version` = '4.0.7';

SET FOREIGN_KEY_CHECKS = 1;

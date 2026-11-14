-- ============================================================================
-- MartPoint 4.0.9.13 — Storefront column fixes
-- Fixes HTTP 500 on the online store for installs updated from <=4.0.9 that
-- never ran the CLI-only CI migrations: Storefront_model selects
-- db_items.product_type on every catalog page; the column was previously
-- created only by CI migration 018.
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- db_items.product_type (physical|service|digital|course|membership)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'product_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `product_type` VARCHAR(20) NOT NULL DEFAULT ''physical'' COMMENT ''physical | service | digital | course | membership''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_online_order_items may be absent on installs that skipped 4.0.2
CREATE TABLE IF NOT EXISTS `db_online_order_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `item_type` ENUM('product','service','digital','course','membership') NOT NULL DEFAULT 'product',
  `qty` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `total_price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  KEY `idx_item_type` (`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Widen item_type on installs where the table exists with the old 2-value enum
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_order_items' AND column_name = 'item_type');
SET @sql = IF(@col_exists > 0,
  'ALTER TABLE `db_online_order_items` MODIFY COLUMN `item_type` ENUM(''product'',''service'',''digital'',''course'',''membership'') NOT NULL DEFAULT ''product''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- MartPoint 4.0.9.22 — Manual Shipping (POS delivery charge)
-- Per-store shipping fee list (location + fee) selectable at POS checkout.
-- Shipping is stored on the sale and printed on the invoice as revenue.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_shipping_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `store_id` INT NOT NULL,
    `label` VARCHAR(160) NOT NULL,
    `location` VARCHAR(200) DEFAULT NULL,
    `fee` DOUBLE(20,2) NOT NULL DEFAULT 0,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_fee');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_fee` DOUBLE(20,2) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_label');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_label` VARCHAR(160) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_fee_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_fee_id` INT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Held invoices keep the shipping selection so it survives hold -> sale
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_fee');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_fee` DOUBLE(20,2) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_label');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_label` VARCHAR(160) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_fee_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_fee_id` INT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

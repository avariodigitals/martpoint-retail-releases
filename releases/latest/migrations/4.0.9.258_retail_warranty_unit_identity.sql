-- Per-device identity and a real retail warranty claim lifecycle.
-- Existing item-level warranty_months remains the default for new sales;
-- these sale-line values are the immutable terms snapshot for each sold unit.

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_item_barcodes' AND COLUMN_NAME='imei_number_2');
SET @s := IF(@c=0, 'ALTER TABLE `db_item_barcodes` ADD COLUMN `imei_number_2` VARCHAR(50) NULL AFTER `imei_number`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='sold_imei_number_2');
SET @s := IF(@c=0, 'ALTER TABLE `db_salesitems` ADD COLUMN `sold_imei_number_2` VARCHAR(50) NULL AFTER `sold_imei_number`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='warranty_months_snapshot');
SET @s := IF(@c=0, 'ALTER TABLE `db_salesitems` ADD COLUMN `warranty_months_snapshot` INT NOT NULL DEFAULT 0 AFTER `sold_imei_number_2`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='warranty_start');
SET @s := IF(@c=0, 'ALTER TABLE `db_salesitems` ADD COLUMN `warranty_start` DATE NULL AFTER `warranty_months_snapshot`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='warranty_end');
SET @s := IF(@c=0, 'ALTER TABLE `db_salesitems` ADD COLUMN `warranty_end` DATE NULL AFTER `warranty_start`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Preserve the only historical terms available on older sales. New sales
-- always snapshot the device's actual warranty duration at checkout.
UPDATE `db_salesitems` si
JOIN `db_sales` s ON s.id=si.sales_id
JOIN `db_items` i ON i.id=si.item_id
SET si.warranty_months_snapshot=i.warranty_months,
    si.warranty_start=CASE WHEN i.warranty_months>0 THEN s.sales_date ELSE NULL END,
    si.warranty_end=CASE WHEN i.warranty_months>0 THEN DATE_ADD(s.sales_date, INTERVAL i.warranty_months MONTH) ELSE NULL END
WHERE COALESCE(si.warranty_months_snapshot,0)=0 AND i.warranty_months>0;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitemsreturn' AND COLUMN_NAME='sold_imei_number_2');
SET @s := IF(@c=0, 'ALTER TABLE `db_salesitemsreturn` ADD COLUMN `sold_imei_number_2` VARCHAR(50) NULL AFTER `sold_imei_number`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_item_barcodes' AND INDEX_NAME='uk_imei_number_2');
SET @s := IF(@idx=0, 'ALTER TABLE `db_item_barcodes` ADD UNIQUE KEY `uk_imei_number_2` (`imei_number_2`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `db_warranty_claims` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `claim_code` VARCHAR(40) NOT NULL,
  `sales_id` INT NOT NULL,
  `salesitems_id` INT NOT NULL,
  `barcode_id` INT NULL,
  `customer_id` INT NULL,
  `item_id` INT NOT NULL,
  `status` ENUM('reported','inspecting','approved','rejected','awaiting_parts','repaired','replaced','refunded','resolved','cancelled') NOT NULL DEFAULT 'reported',
  `issue` TEXT NOT NULL,
  `decision_note` TEXT NULL,
  `resolution_note` TEXT NULL,
  `received_at` DATETIME NOT NULL,
  `closed_at` DATETIME NULL,
  `created_by` INT NULL,
  `updated_by` INT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_warranty_claim_code` (`store_id`,`claim_code`),
  KEY `idx_warranty_store_status` (`store_id`,`status`,`updated_at`),
  KEY `idx_warranty_sale_item` (`store_id`,`salesitems_id`),
  KEY `idx_warranty_barcode` (`store_id`,`barcode_id`),
  KEY `idx_warranty_customer` (`store_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_warranty_claim_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `claim_id` INT NOT NULL,
  `from_status` VARCHAR(30) NULL,
  `to_status` VARCHAR(30) NOT NULL,
  `note` TEXT NULL,
  `created_by` INT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_claim_events` (`store_id`,`claim_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

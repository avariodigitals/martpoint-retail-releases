-- Auto part identity + vehicle compatibility (v4.0.9.243).
--
-- Parts trade on references, not names: the manufacturer part number is what
-- a workshop asks for, OEM and cross-reference numbers are how it is found
-- across catalogues. These join the structured columns of db_items. The
-- compatibility table answers "which of our parts fit this car" — product→
-- vehicle fitment data, deliberately separate from the customer-owned
-- vehicle register (db_vehicles).

SET @db := DATABASE();

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=@db AND table_name='db_items' AND column_name='manufacturer_part_no');
SET @sql = IF(@col_exists=0, 'ALTER TABLE `db_items` ADD COLUMN `manufacturer_part_no` VARCHAR(100) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=@db AND table_name='db_items' AND column_name='oem_reference');
SET @sql = IF(@col_exists=0, 'ALTER TABLE `db_items` ADD COLUMN `oem_reference` VARCHAR(100) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=@db AND table_name='db_items' AND column_name='cross_reference');
SET @sql = IF(@col_exists=0, 'ALTER TABLE `db_items` ADD COLUMN `cross_reference` VARCHAR(255) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `db_item_vehicle_compat` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `make` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_from` INT(4) DEFAULT NULL,
  `year_to` INT(4) DEFAULT NULL,
  `engine` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ivc_item` (`store_id`,`item_id`),
  KEY `idx_ivc_match` (`store_id`,`make`,`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

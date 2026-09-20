-- ============================================================================
-- MartPoint 4.0.9.27 — Bottled-SKU link
-- Lets a sellable item know which bottle it fills and how much liquid it
-- takes, so a bottling run can resolve bottle + volume from the SKU alone.
-- Also lets a packaging item declare its physical capacity.
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- fill_qty: volume of bulk liquid one unit of this item takes (bulk base unit, e.g. ml)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'fill_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `fill_qty` DECIMAL(10,3) NULL DEFAULT NULL COMMENT ''Volume per unit when bottled (e.g. 150 ml)''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- bottle_item_id: the packaging item consumed once per unit sold/filled
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'bottle_item_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `bottle_item_id` INT(11) NULL DEFAULT NULL COMMENT ''db_items id of the bottle/packaging consumed per unit''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- capacity_ml: on packaging items, the max volume the bottle can hold
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'capacity_ml');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `capacity_ml` DECIMAL(10,3) NULL DEFAULT NULL COMMENT ''Bottle/packaging capacity in ml (packaging items only)''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

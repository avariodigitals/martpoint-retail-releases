-- ============================================================================
-- MartPoint 4.0.9.30 — db_stockadjustment(+items) schema repair
-- Fixes "stock adjustment saves nothing / details page 500" on installs whose
-- tables predate the store_id/warehouse_id/reference_no columns — they were
-- only ever in the fresh-install schema, no migration ever added them.
-- A missing column makes inserts fail silently and makes is_it_belong_to_store
-- fatal (member call on a failed query). Idempotent: safe to run more than once.
-- NOTE: no DELIMITER/stored procedures — this file runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Recreate the tables wholesale only if they are entirely absent.
CREATE TABLE IF NOT EXISTS `db_stockadjustment` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `warehouse_id` int(5) DEFAULT NULL,
  `reference_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjustment_date` date DEFAULT NULL,
  `adjustment_note` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_stockadjustmentitems` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_id` int(5) DEFAULT NULL,
  `warehouse_id` int(5) DEFAULT NULL,
  `adjustment_id` int(5) DEFAULT NULL,
  `item_id` int(5) DEFAULT NULL,
  `adjustment_qty` double(20,2) DEFAULT NULL,
  `status` int(5) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- db_stockadjustment columns -----------------------

SET @tbl = 'db_stockadjustment';

SET @col := 'store_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(11) NULL DEFAULT NULL AFTER `id`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'warehouse_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT NULL AFTER `store_id`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'reference_no';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL AFTER `warehouse_id`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'adjustment_date';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATE NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'adjustment_note';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` MEDIUMTEXT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'created_date';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATE NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'created_time';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'created_by';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(50) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'system_ip';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'system_name';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'status';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(1) NULL DEFAULT 1'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------------------- db_stockadjustmentitems columns ---------------------

SET @tbl = 'db_stockadjustmentitems';

SET @col := 'store_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT NULL AFTER `id`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'warehouse_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'adjustment_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'item_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'adjustment_qty';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DOUBLE(20,2) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'status';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT(5) NULL DEFAULT 1'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'description';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;

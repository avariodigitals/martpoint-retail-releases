-- ============================================================================
-- MartPoint 4.0.9.55 — City-based delivery fees for the online storefront
-- Store owners list the cities/states they deliver to (with a fee each);
-- customers pick their city at checkout and the fee is added to the order.
-- Controlled by db_storefront_settings.city_shipping_enabled (off by default).
-- Idempotent: safe to run more than once. MySQL 5.7+.
-- NOTE: no DELIMITER/stored procedures — this file runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @tbl = 'db_storefront_settings';

SET @col := 'city_shipping_enabled';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NULL DEFAULT 0'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'city_shipping_json';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- db_online_orders.shipping_method is written by Storefront::place_order but was
-- never part of the base CREATE TABLE; add it where missing so orders don't fail.
SET @tbl = 'db_online_orders';
SET @col := 'shipping_method';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;

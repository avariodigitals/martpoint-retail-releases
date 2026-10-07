-- 4.0.9.71 — Storefront shopping events, persisted carts, recovery settings.
--
-- db_storefront_events  : first-party funnel events (view_item, add_to_cart,
--                         begin_checkout, order_placed) recorded with the
--                         consent flag the client asserted at dispatch.
-- db_storefront_carts   : server-side cart snapshots keyed by a client token,
--                         so abandoned checkouts can be listed and recovered.
-- db_storefront_settings: cart_recovery_enabled + abandoned_after_hours let the
--                         merchant configure recovery without code changes.
-- Idempotent: CREATE TABLE IF NOT EXISTS / guarded ALTERs — safe to re-run.

CREATE TABLE IF NOT EXISTS `db_storefront_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `session_id` VARCHAR(100) NULL DEFAULT NULL,
  `event_type` VARCHAR(40) NOT NULL,
  `item_id` INT NULL DEFAULT NULL,
  `order_id` INT NULL DEFAULT NULL,
  `value` DECIMAL(15,4) NULL DEFAULT NULL,
  `meta` TEXT NULL,
  `consented` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sf_events_store` (`store_id`),
  KEY `idx_sf_events_type` (`event_type`),
  KEY `idx_sf_events_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_storefront_carts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `cart_token` VARCHAR(64) NOT NULL,
  `session_id` VARCHAR(100) NULL DEFAULT NULL,
  `customer_name` VARCHAR(191) NULL DEFAULT NULL,
  `customer_phone` VARCHAR(50) NULL DEFAULT NULL,
  `customer_email` VARCHAR(191) NULL DEFAULT NULL,
  `items_json` MEDIUMTEXT NULL,
  `subtotal` DECIMAL(15,4) NULL DEFAULT NULL,
  `coupon_code` VARCHAR(64) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `order_id` INT NULL DEFAULT NULL,
  `reminder_count` INT NOT NULL DEFAULT 0,
  `reminder_sent_at` DATETIME NULL DEFAULT NULL,
  `last_activity` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sf_cart_token` (`store_id`, `cart_token`),
  KEY `idx_sf_carts_status` (`status`),
  KEY `idx_sf_carts_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @tbl := 'db_storefront_settings';

SET @col := 'cart_recovery_enabled';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NOT NULL DEFAULT 1'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'abandoned_after_hours';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NOT NULL DEFAULT 24'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

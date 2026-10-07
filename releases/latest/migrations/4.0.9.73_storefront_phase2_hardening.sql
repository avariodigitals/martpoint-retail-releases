-- 4.0.9.73 — Phase 2 hardening for storefront events + persisted carts.
--
-- db_storefront_events   : event_id enables dedup across client pixels and
--                          server records (same event recorded once).
-- db_storefront_carts    : opt_out (customer asked not to be contacted),
--                          send_attempts/last_send_attempt_at (retry safety),
--                          expires_at (token lifetime).
-- db_storefront_settings : tiktok_pixel_id + automated recovery controls.
-- db_storefront_cart_reminders : audit log of every recovery nudge.
-- Idempotent: guarded CREATE/ALTER — safe to re-run.

SET @tbl := 'db_storefront_events';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='event_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_storefront_events` ADD COLUMN `event_id` VARCHAR(64) NULL DEFAULT NULL, ADD KEY `idx_sf_events_eid` (`event_id`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_carts';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='opt_out')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_storefront_carts` ADD COLUMN `opt_out` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='send_attempts')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_storefront_carts` ADD COLUMN `send_attempts` INT NOT NULL DEFAULT 0, ADD COLUMN `last_send_attempt_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='expires_at')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_storefront_carts` ADD COLUMN `expires_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings';
SET @col_sql := CONCAT('ALTER TABLE `', @tbl, '` ',
  'ADD COLUMN `tiktok_pixel_id` VARCHAR(50) NULL DEFAULT NULL, ',
  'ADD COLUMN `auto_recovery_enabled` TINYINT(1) NOT NULL DEFAULT 0, ',
  'ADD COLUMN `auto_recovery_channel` VARCHAR(20) NOT NULL DEFAULT \'email\', ',
  'ADD COLUMN `auto_recovery_test` TINYINT(1) NOT NULL DEFAULT 1');
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='tiktok_pixel_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  @col_sql, 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS `db_storefront_cart_reminders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `cart_id` INT NOT NULL,
  `channel` VARCHAR(20) NOT NULL,
  `mode` VARCHAR(10) NOT NULL DEFAULT 'manual',
  `recipient` VARCHAR(191) NULL DEFAULT NULL,
  `detail` VARCHAR(255) NULL DEFAULT NULL,
  `actor` VARCHAR(60) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sf_remind_cart` (`cart_id`),
  KEY `idx_sf_remind_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

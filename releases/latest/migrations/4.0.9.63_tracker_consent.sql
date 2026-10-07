-- ============================================================================
-- MartPoint 4.0.9.63 — Storefront tracker consent control
-- Adds db_storefront_settings.require_tracking_consent (default ON).
-- When ON, the storefront does not load GA4 / Meta Pixel / other configured
-- trackers until the visitor accepts the consent banner. When OFF the
-- merchant takes responsibility and trackers load immediately.
-- Idempotent: safe to run more than once. MySQL 5.7+.
-- NOTE: no DELIMITER/stored procedures — this file runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @tbl = 'db_storefront_settings';
SET @col := 'require_tracking_consent';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NULL DEFAULT 1 AFTER `facebook_pixel_id`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;

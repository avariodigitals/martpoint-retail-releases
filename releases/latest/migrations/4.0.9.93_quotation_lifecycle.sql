-- ============================================================================
-- MartPoint 4.0.9.93 — Quotation lifecycle: decline, cancel, expiry, reminders
--
-- A quotation that is never accepted, declined or expired currently sits as
-- "issued" forever, so jobs stay open indefinitely and the pipeline is
-- untrustworthy. This adds an explicit lifecycle and expiry tracking at BOTH
-- levels:
--   db_quotation        — the document itself (works for every business type)
--   db_print_jobs       — the print job's view of the same lifecycle
--
-- Reminders are recorded so the 3-day and 1-day nudges before expiry are sent
-- exactly once, even if cron runs repeatedly.
-- Idempotent.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---- document level (all business types) ----------------------------------

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'lifecycle_status') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `lifecycle_status` VARCHAR(24) NOT NULL DEFAULT ''issued'' COMMENT ''issued|accepted|declined|cancelled|expired''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'responded_at') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `responded_at` DATETIME NULL COMMENT ''when accepted/declined/cancelled''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'response_reason') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `response_reason` VARCHAR(255) NULL COMMENT ''why it was declined/cancelled''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'reminder_3d_sent_at') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `reminder_3d_sent_at` DATETIME NULL COMMENT ''3-day expiry reminder, sent once''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'reminder_1d_sent_at') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `reminder_1d_sent_at` DATETIME NULL COMMENT ''1-day expiry reminder, sent once''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'expired_at') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `expired_at` DATETIME NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---- print job view of the same lifecycle --------------------------------

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quote_lifecycle_status') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quote_lifecycle_status` VARCHAR(24) NOT NULL DEFAULT ''issued'' COMMENT ''mirrors db_quotation.lifecycle_status''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quote_response_reason') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quote_response_reason` VARCHAR(255) NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quote_expired_at') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quote_expired_at` DATETIME NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill: everything already issued keeps behaving as issued. Anything
-- already accepted (revision accepted) is marked accepted so the new
-- lifecycle agrees with the existing acceptance data.
UPDATE `db_quotation` SET `lifecycle_status` = 'issued' WHERE `lifecycle_status` IS NULL OR `lifecycle_status` = '';
UPDATE `db_print_jobs` SET `quote_lifecycle_status` = 'accepted'
  WHERE `quotation_revision_accepted` IS NOT NULL AND `quote_lifecycle_status` = 'issued';

-- ---- audit trail for the lifecycle ---------------------------------------

CREATE TABLE IF NOT EXISTS `db_quotation_lifecycle_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `quotation_id` INT(11) NOT NULL,
  `job_id` INT(11) NULL,
  `from_status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NULL,
  `to_status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `actor` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `channel` VARCHAR(20) COLLATE utf8mb4_unicode_ci NULL COMMENT 'ui|email|whatsapp|system|cron',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_quotation` (`quotation_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

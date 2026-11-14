-- ============================================================================
-- MartPoint 4.0.9.92 — Printing: line-level discount + tax on print lines
--
-- The shared quotation builder writes per-line discount and tax onto
-- db_quotationitems. The printing builder mirrors those onto the quotation,
-- but the print job line previously had nowhere to store them, so printing
-- lagged on financial capability. These columns bring print lines to parity so
-- the SAME calculation path produces the SAME numbers.
--
-- All nullable / defaulted: existing lines keep behaving exactly as before
-- (no discount, no line tax → falls back to the job-level tax).
-- Idempotent.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'discount_type') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `discount_type` VARCHAR(20) NULL COMMENT ''in_percentage | fixed''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'discount_input') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `discount_input` DECIMAL(15,4) NULL COMMENT ''percent value or fixed amount''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'discount_amt') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `discount_amt` DECIMAL(15,2) NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'tax_id') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `tax_id` INT(11) NULL COMMENT ''db_tax.id; NULL falls back to the job-level tax''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'tax_type') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `tax_type` VARCHAR(20) NULL COMMENT ''Inclusive | Exclusive''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Correction log: a general quotation must never authorize print production.
-- Record which quotations were raised outside the print workflow so they can
-- be reviewed (never auto-attached, never rewritten).
CREATE TABLE IF NOT EXISTS `db_print_quotation_review` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `quotation_id` INT(11) NOT NULL,
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `reviewed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `reviewed_at` DATETIME NULL,
  `decision` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|linked|lapsed|kept',
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quotation` (`store_id`,`quotation_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

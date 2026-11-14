-- ============================================================================
-- MartPoint 4.0.9.89 — Printing: controllable tax on quotations
--
-- Retail applies tax per item (db_items.tax_id + tax_type Inclusive/Exclusive).
-- Printing lines are services resolved to one non-stock carrier, so tax is
-- controlled at the JOB level instead, and defaults to EXEMPT (tax_on = 0) so
-- nothing becomes taxable by accident.
--
--   db_print_jobs.tax_on        → 0 = exempt (default), 1 = apply tax
--   db_print_jobs.tax_id        → db_tax.id used when tax_on = 1
--   db_print_jobs.tax_type      → Inclusive | Exclusive
--   db_print_jobs.tax_rate      → rate snapshot at issue time (rate changes
--                                 must not silently rewrite issued quotes)
--   db_print_jobs.tax_amount    → tax computed on the quotation subtotal
--
-- Idempotent.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'tax_on') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `tax_on` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''0 = exempt (default), 1 = apply tax to the quotation''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'tax_id') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `tax_id` INT(11) NULL COMMENT ''db_tax.id when tax_on = 1''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'tax_type') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `tax_type` VARCHAR(20) NULL COMMENT ''Inclusive | Exclusive''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'tax_rate') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `tax_rate` DECIMAL(10,4) NULL COMMENT ''Rate snapshot at issue time''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'tax_amount') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT ''Tax on the quotation subtotal''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

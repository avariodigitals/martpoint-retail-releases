-- ============================================================================
-- MartPoint 4.0.9.88 — Printing ⇄ existing quotation module integration
--
-- Printing does NOT get its own quotation engine. A print job links to the
-- existing db_quotation / db_quotationitems / db_quotation_revisions records.
--
--   db_print_jobs.quotation_id          → the authoritative quotation (exists)
--   db_print_job_lines.quotation_item_id→ which quotation line maps to this line
--   db_print_jobs.quotation_revision_accepted → revision the customer accepted
--
-- Conversion duplicate protection is shared (all business types): a quotation
-- may be converted to at most one sales invoice, enforced by a unique index.
--
-- Idempotent. Safe on installs that already have the columns.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---- printing side: stable line mapping + accepted-revision tracking --------

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_job_lines'
       AND COLUMN_NAME = 'quotation_item_id') = 0,
  'ALTER TABLE `db_print_job_lines` ADD COLUMN `quotation_item_id` INT(11) NULL COMMENT ''Maps this print line to its db_quotationitems row''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quotation_revision_accepted') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quotation_revision_accepted` INT(11) NULL COMMENT ''Revision number the customer accepted (NULL = none)''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quotation_accepted_at') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quotation_accepted_at` DATETIME NULL COMMENT ''When the customer accepted that revision''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quote_reaccept_required') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quote_reaccept_required` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Set when a new revision changes agreed specs/price/terms''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_jobs'
       AND COLUMN_NAME = 'quote_reaccept_reason') = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `quote_reaccept_reason` VARCHAR(255) NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---- shared side: conversion is one-to-one (backward compatible) ----------

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation'
       AND COLUMN_NAME = 'converted_sales_id') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `converted_sales_id` INT(11) NULL COMMENT ''db_sales.id this quotation was converted into (one-to-one guard)''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Legacy rows store 0 (and occasionally '') to mean "no quotation". MySQL
-- treats every NULL as distinct in a unique index but 0 counts as a real
-- value, so these must be normalised before the one-to-one index can apply.
UPDATE `db_sales` SET `quotation_id` = NULL WHERE `quotation_id` = 0;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_sales'
       AND INDEX_NAME = 'idx_quotation_sales_unique') = 0,
  'ALTER TABLE `db_sales` ADD UNIQUE KEY `idx_quotation_sales_unique` (`quotation_id`)',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

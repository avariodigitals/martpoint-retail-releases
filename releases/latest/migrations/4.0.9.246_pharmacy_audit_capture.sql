-- Audit capture for dispensing, stock and price changes (v4.0.9.246)
--
-- The audit trail screen already existed and already answered "who did what
-- and when", but only for logins, users, roles and settings. The movements a
-- pharmacy is actually accountable for — a price that changed, a batch that
-- was sealed, a course dispensed, stock written off — never reached it, and
-- when a similar record was caught there was no way to show what the value
-- was BEFORE the change. This migration gives the trail those two things.
--
--   ref_label  — the thing a human recognises (the medicine's name, the
--                invoice number, the batch number) next to the internal id it
--                already stored, so a reviewer reads "Paracetamol 500mg" and
--                not "reference 43812".
--   changes    — a short JSON list of the fields that actually moved, each
--                with its plain-English label and the before and after value.
--                Nullable: every older row keeps reading exactly as it does
--                today, and a write that stores nothing stays a plain
--                one-line event.
--
-- Nothing here changes what any screen does or any quantity holds. It is pure
-- recording: the columns default to NULL, no rows are touched, and the writer
-- degrades to the old four-column behaviour on a database that has not
-- received this file yet.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD COLUMN IF NOT EXISTS.

-- 1. The human-readable name of the record the event touched ---------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_audit_trail'
       AND COLUMN_NAME = 'ref_label') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_audit_trail') > 0,
  'ALTER TABLE `db_audit_trail` ADD COLUMN `ref_label` VARCHAR(191) NULL DEFAULT NULL AFTER `ref_id`',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Before / after values --------------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_audit_trail'
       AND COLUMN_NAME = 'changes') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_audit_trail') > 0,
  'ALTER TABLE `db_audit_trail` ADD COLUMN `changes` TEXT NULL DEFAULT NULL AFTER `ref_label`',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

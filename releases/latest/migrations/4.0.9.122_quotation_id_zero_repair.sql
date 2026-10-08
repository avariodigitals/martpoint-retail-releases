-- ============================================================================
-- MartPoint 4.0.9.122 — repair db_sales.quotation_id so "no quotation" is NULL
--
-- SYMPTOM (reported by clients on .106)
--
--   Failed to save sale (db_sales insert): Duplicate entry '0' for key
--   'idx_quotation_sales_unique'
--
-- Ordinary sales — no quotation involved — suddenly could not be saved at all.
--
-- CAUSE
--
-- Migration 4.0.9.88 added a one-to-one guard so a quotation can be converted
-- into at most one invoice:
--
--   ALTER TABLE `db_sales` ADD UNIQUE KEY `idx_quotation_sales_unique` (`quotation_id`)
--
-- It also normalised the legacy "no quotation" markers (0) to NULL first,
-- because MySQL treats every NULL as DISTINCT in a UNIQUE index but 0 as a real
-- value. That normalisation was a ONE-TIME fix. Nothing stopped new 0s:
--
--   * The sales save path did `if(isset($quotation_id))` on
--     `$this->input->post('quotation_id')`. A blank field posts '' — which is
--     SET — so '' was written and MySQL coerced it to the integer 0.
--     The FIRST such sale succeeded; every later one collided.
--   * Some installs carried `quotation_id INT(11) NULL DEFAULT 0`, so any
--     insert that omitted the column also produced a 0.
--
-- The write path is fixed in Sales_model::verify_save_and_update() (blank and
-- 0 now normalise to NULL, matching Pos_model's `> 0` guard). This migration
-- cleans up data already written, and removes the DEFAULT 0 that would
-- recreate the collision.
--
-- WHAT IT DOES
--
--   1. db_sales.quotation_id = 0  →  NULL   (any number of stranded rows)
--   2. db_sales.quotation_id DEFAULT 0 → DEFAULT NULL  (when it is 0)
--   3. re-asserts the UNIQUE index (idempotent; skipped if already present)
--
-- The index is only added when no duplicate NON-NULL quotation_id exists, so
-- an install with genuinely double-converted quotations is repaired (step 1)
-- rather than failed — the index add is the final consistency check.
--
-- Idempotent. Safe on fresh installs and safe to re-run.
-- ============================================================================

SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---- 1. Stranded 0 links become NULL --------------------------------------
-- NULL is what "this sale has no quotation" means; 0 is a value the unique
-- index enforces and therefore only ever causes collisions.

UPDATE `db_sales` SET `quotation_id` = NULL WHERE `quotation_id` = 0;

-- ---- 2. Remove DEFAULT 0 so omitted columns stop producing 0 --------------
-- A DEFAULT 0 silently re-creates the duplicate on every insert that leaves
-- the column out. Guarded on the current default so this is a no-op when the
-- column is already DEFAULT NULL.

SET @has_default_zero = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_sales'
    AND COLUMN_NAME = 'quotation_id'
    AND COLUMN_DEFAULT IS NOT NULL AND COLUMN_DEFAULT <> '');

SET @sql = (SELECT IF(@has_default_zero > 0,
  'ALTER TABLE `db_sales` MODIFY COLUMN `quotation_id` INT(11) NULL DEFAULT NULL',
  'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---- 3. Re-assert the one-to-one index ------------------------------------
-- Kept after the repair so the guarantee cannot be lost by a partial upgrade.
--
-- GUARDED ON DUPLICATES, not just on the index being absent.
--
-- Step 1 clears the 0-markers, which are the only duplicates MySQL ever
-- produced here (a UNIQUE index treats every NULL as distinct, so normalised
-- rows can never collide). But an install that has genuinely converted the same
-- quotation into two invoices — a real double-conversion — would still hold
-- duplicate NON-NULL values, and `ADD UNIQUE KEY` against those fails with
-- error 1062.
--
-- That failure is not fatal to the upgrade: Updater::applyMigrations() treats
-- any message containing "Duplicate" as benign and carries on, so the chain
-- does not stop. But it would leave the index missing while the migration was
-- recorded as applied — a silent loss of the guarantee. Checking first means
-- the statement is skipped deliberately and the reason is visible in the table
-- below, rather than an error being swallowed.
--
-- When duplicates ARE present the index is deliberately NOT added: it cannot be,
-- and quietly deleting one of the two invoices would destroy real financial
-- data. Those installs are reported instead — see the check at the end.
SET @dupes = (SELECT COUNT(*) FROM (
  SELECT `quotation_id` FROM `db_sales`
   WHERE `quotation_id` IS NOT NULL
   GROUP BY `quotation_id` HAVING COUNT(*) > 1
) d);

SET @sql = (SELECT IF(
  @dupes = 0 AND (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_sales'
       AND INDEX_NAME = 'idx_quotation_sales_unique') = 0,
  'ALTER TABLE `db_sales` ADD UNIQUE KEY `idx_quotation_sales_unique` (`quotation_id`)',
  'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---- 4. Make a genuine double-conversion visible ------------------------
-- Step 3 leaves the index off when duplicates exist. Without this, that state
-- is indistinguishable from "the index was never needed". The migration cannot
-- decide which of two invoices is the real one — that is a human call — so it
-- prints the ids and the shop resolves them, then re-runs (this file is
-- idempotent and adds the index on the next pass).
--
-- Emitted as a SELECT, deliberately: a SELECT returns a result object rather
-- than false, so it is harmless to the updater's statement loop, and anyone
-- running this file by hand actually sees the message. It must NOT be SIGNAL —
-- a custom SQLSTATE does not contain "Duplicate", so Updater::applyMigrations()
-- would treat it as fatal, roll back and halt the whole upgrade.
SET @sql = (SELECT IF(@dupes > 0,
  CONCAT('SELECT ''WARNING 4.0.9.122: ', @dupes,
         ' quotation_id value(s) link more than one sale. The one-to-one index was NOT ',
         'added. Review db_sales for those ids, reverse the duplicate conversion, ',
         'then re-run migrations to complete the index.'' AS migration_note'),
  'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

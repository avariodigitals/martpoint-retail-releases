-- Reason-coded wastage (v4.0.9.228) — Bar/Lounge breakage, expiry and loss.
--
-- A stock adjustment line already says HOW MUCH left the shelf and a free-text
-- note says roughly WHY. Without a code the numbers cannot be grouped, so a
-- lounge pouring away six bottles of beer and three of wine reports one line of
-- "9 wasted" and nobody can tell breakage from expiry or theft.
--
-- This adds one nullable code per adjustment LINE. It is deliberately nullable:
-- every movement recorded before this migration keeps standing on its own and
-- reads back as "reason not recorded" — no historical row is rewritten, and no
-- existing stock recalculation, transfer or report query changes shape.
--
-- Additive and repeatable, in the house style (information_schema guards,
-- PREPARE/EXECUTE) because MySQL 5.7 / MariaDB have no ADD COLUMN IF NOT
-- EXISTS. Fresh installs get the same shape from
-- setup/install/includes/db_install_extensions.sql.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustmentitems' AND COLUMN_NAME='reason_code')=0,
  'ALTER TABLE db_stockadjustmentitems ADD COLUMN `reason_code` VARCHAR(40) NULL DEFAULT NULL AFTER `description`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Wastage is read as "this store's losses, by reason, over a period", so the
-- code is indexed behind the store it belongs to. Ordinary index, not a foreign
-- key: reasons live in code, not in a table, so a store can never be stranded
-- by a missing lookup row.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustmentitems' AND INDEX_NAME='idx_adjustment_reason')=0,
  'ALTER TABLE db_stockadjustmentitems ADD INDEX `idx_adjustment_reason` (`store_id`,`reason_code`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

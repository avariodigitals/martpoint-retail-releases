-- MartPoint — Fleet install update-stage columns (v4.0.9.103)
-- ============================================================
-- Central could see WHICH installs were outdated, but never WHERE an update
-- had stopped. An install stuck mid-update looked identical to a healthy one
-- that simply had not checked in yet, so finding the broken ones meant opening
-- each install by hand.
--
-- These columns carry the install's own update state on every heartbeat:
--   update_stage  : machine-readable stage, e.g. 'downloading', 'migrating',
--                   'failed', 'idle'. Drives the colour + label in the fleet.
--   update_step   : numeric step 1-8 (0 when idle), for sorting/progress.
--   update_detail : short human sentence ("hash mismatch on 1 file") shown in
--                   the tooltip so the cause is readable without a login.
--
-- Written ONLY by Fleet::heartbeat() from fields the install reports; Central
-- never invents a stage. Installs too old to send these fields simply leave
-- the columns NULL, which the UI renders as "no data" rather than a false
-- "idle" — an old install is not the same as an up-to-date one.
--
-- Idempotent. Safe to re-run.

-- MySQL has no "ADD COLUMN IF NOT EXISTS" on older versions, so guard each
-- one via a prepared statement against information_schema.
--
-- The guard's else-branch is `DO 0` rather than `SELECT 1` so a re-run emits
-- nothing: the migration runner treats an unexpected result set as noise, and
-- eight stray "1" rows in the update log make real errors harder to spot.
SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'update_stage') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `update_stage` VARCHAR(32) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'update_step') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `update_step` TINYINT UNSIGNED NOT NULL DEFAULT 0',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'update_detail') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `update_detail` VARCHAR(255) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- The fleet view filters and sorts on stage — index it so a 500-install list
-- stays fast.
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND INDEX_NAME = 'idx_update_stage') = 0,
  'ALTER TABLE `db_fleet_installs` ADD INDEX `idx_update_stage` (`update_stage`)',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

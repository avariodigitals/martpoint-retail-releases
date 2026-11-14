-- MartPoint — Fleet install failure reporting (v4.0.9.105)
-- ============================================================
-- Central could see that an install was outdated, but never WHY it stayed
-- that way. Two live installs proved how costly that is:
--
--   anora      — update died at step 1, "Backup Database" (the dump ran out
--                of memory on a real dataset). Reported as a permissions
--                problem, which was wrong.
--   john tella — update died at step 6, migrations, stuck at 4.0.9.60 and
--                therefore 44 releases behind.
--
-- Both looked identical in the fleet: just "outdated". The reason lived in
-- the install's own db_system_updates table, reachable only by logging in.
--
-- These columns carry the install's last failed job on every heartbeat:
--   last_fail_label   : the step that failed, e.g. "Backup Database"
--   last_fail_message : the error text, so the cause is readable in the UI
--   last_fail_at      : when it failed
--
-- A recorded failure also marks the row as needing attention, because an
-- install whose backup or migration failed looks otherwise calm — that is
-- exactly how it stayed invisible.
--
-- Idempotent. Safe to re-run.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'last_fail_label') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `last_fail_label` VARCHAR(120) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'last_fail_message') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `last_fail_message` VARCHAR(200) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'last_fail_at') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `last_fail_at` VARCHAR(20) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

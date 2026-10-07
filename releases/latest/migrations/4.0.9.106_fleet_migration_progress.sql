-- MartPoint — Fleet migration-progress reporting (v4.0.9.106)
-- ============================================================
-- A stuck migration is the worst failure in the system. The chain breaks at
-- one file, every later migration queues behind it, and the install can never
-- advance — john tella sat 44 releases behind for exactly this reason.
--
-- From Central it looked identical to a healthy install that was merely
-- behind. The only way to find it was to log in and read Recent Jobs.
--
-- These columns put migration progress on the heartbeat so the stall is
-- visible at a glance:
--   migrations_applied : how many migrations this install has run
--   migration_newest   : the newest migration filename it recorded
--
-- A count that stops climbing while the release ships 103 migrations IS the
-- signal. The fleet shows it as a per-install progress bar.
--
-- Idempotent. Safe to re-run.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'migrations_applied') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `migrations_applied` INT UNSIGNED NOT NULL DEFAULT 0',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'migration_newest') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `migration_newest` VARCHAR(160) NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

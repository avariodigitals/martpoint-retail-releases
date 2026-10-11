-- ============================================================================
-- MartPoint 4.0.9.139 — guarantee the columns the fleet enrol path needs
--
-- WHY THIS EXISTS AS A MIGRATION
-- ------------------------------
-- The release manifest is published to a PUBLIC repository, so the shared
-- fleet_key can no longer travel inside it. Installs now receive it out of
-- band: at provisioning, or from Central's /fleet/enroll and the heartbeat
-- response, both authenticated by the install's own per-install install_key.
--
-- That path only works if all three columns are present, and the guards in the
-- code are `if (field_exists(...))` — a missing column is not an error, it is a
-- silent skip. Updater::installKey() in particular returns '' when the column
-- is absent rather than creating it, so an install upgraded from an older
-- schema silently loses the per-install credential: no install_key means no
-- enrolment, no key refresh after a rotation, and an install that never appears
-- in the fleet registry.
--
-- Idempotent. Safe to re-run.
--
-- MySQL has no "ADD COLUMN IF NOT EXISTS" on older versions, so guard each one
-- via a prepared statement against information_schema. The else-branch is
-- `DO 0` rather than `SELECT 1` so a re-run emits no result set.
-- ============================================================================

SET @db := DATABASE();

-- ---- db_sitesettings.fleet_url -------------------------------------------
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings'
       AND COLUMN_NAME = 'fleet_url') = 0,
  'ALTER TABLE `db_sitesettings` ADD COLUMN `fleet_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---- db_sitesettings.fleet_key -------------------------------------------
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings'
       AND COLUMN_NAME = 'fleet_key') = 0,
  'ALTER TABLE `db_sitesettings` ADD COLUMN `fleet_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---- db_sitesettings.install_key -----------------------------------------
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_sitesettings'
       AND COLUMN_NAME = 'install_key') = 0,
  'ALTER TABLE `db_sitesettings` ADD COLUMN `install_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---- db_fleet_installs.install_key ---------------------------------------
-- Central records the key an install presented so later calls can be matched.
--
-- GUARDED ON THE TABLE EXISTING FIRST. db_fleet_installs is a vendor-console
-- (Central) table that a client install does not have and does not need — the
-- fleet tables are created at runtime by Fleet.php, not by the installer or by
-- any earlier migration on a client. Without the table check the guard reads
-- "0 columns found", concludes the column is missing, and runs ALTER TABLE
-- against a table that is not there — error 1146, which Updater::applyMigrations()
-- does NOT treat as benign, so the whole migration chain would stop part-way on
-- every client install. With it, the statement no-ops on a client and behaves
-- as written on Central.
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_installs'
       AND COLUMN_NAME = 'install_key') = 0,
  'ALTER TABLE `db_fleet_installs` ADD COLUMN `install_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

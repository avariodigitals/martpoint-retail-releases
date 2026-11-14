-- MartPoint — Fleet command resume tracking (v4.0.9.104)
-- ============================================================
-- "Update now" is a multi-minute pipeline that cannot finish inside one PHP
-- request. The install ran a bounded slice, reported progress, and the command
-- was closed as done after 90 seconds — so Central showed a green tick while
-- the install was still thousands of files behind, and nothing ever re-queued
-- it. This is why a fleet pushed to a new version can sit unchanged for hours.
--
-- With the Updater now reporting a third outcome — 'resume' — Central re-queues
-- the next slice and wakes the install again, driving the update to completion
-- with no further clicking. These columns track that loop:
--
--   resume_count : how many slices this command has been through. Capped in
--                  Fleet::command_result() so a genuinely stuck install fails
--                  loudly instead of looping for ever.
--   resumed_at   : when the last slice was handed back, so the UI can show
--                  that an update is progressing rather than frozen.
--
-- Idempotent. Safe to re-run.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_commands') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_commands'
       AND COLUMN_NAME = 'resume_count') = 0,
  'ALTER TABLE `db_fleet_commands` ADD COLUMN `resume_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_commands') = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_fleet_commands'
       AND COLUMN_NAME = 'resumed_at') = 0,
  'ALTER TABLE `db_fleet_commands` ADD COLUMN `resumed_at` DATETIME NULL DEFAULT NULL',
  'DO 0'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

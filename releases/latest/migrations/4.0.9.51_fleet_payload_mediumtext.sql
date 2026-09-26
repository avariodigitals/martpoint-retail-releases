-- ============================================================
-- Fleet push_file: widen db_fleet_commands.payload to MEDIUMTEXT
-- (TEXT caps ~64KB; a pushed file's base64+JSON can exceed that).
-- Idempotent + guarded for installs without the fleet table.
-- ============================================================

SET @t = (SELECT COUNT(*) FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_fleet_commands');
SET @s = IF(@t > 0,
  'ALTER TABLE `db_fleet_commands` MODIFY COLUMN `payload` MEDIUMTEXT NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

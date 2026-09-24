-- ============================================================================
-- MartPoint 4.0.9.46 — Audit-trail on/off toggle on db_sitesettings
-- Central can disable audit recording per install (set_settings → audit).
-- mp_audit_log() reads this flag; a missing column means auditing stays on.
-- Idempotent, MySQL 5.7+. Runs via mysqli_multi_query.
-- ============================================================================

SET @tbl = 'db_sitesettings';
SET @col := 'audit_trail_enabled';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NOT NULL DEFAULT 1'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

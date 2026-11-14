-- ===========================================================================
-- 4.0.9.153 — The treatment chart says who gave the treatment, not only who
-- typed it in.
--
-- Any physio can be on duty, and the one who covers a session is not always
-- the one the course was booked to. Keying the chart to a single signature
-- therefore either hid the covering physio or pretended the typist had done
-- the treating. The sheet now carries both names, the way the paper one does:
--
--   * treated_by / treated_by_name — the physio who gave this session. It
--     starts from the clinician on the booking, then the clinician the visit is
--     assigned to, and the person charting can correct it.
--   * signed_by / signed_by_name   — unchanged: whoever first signed the chart.
--   * amended_by / amended_by_name / amended_at — a later change is credited
--     and dated instead of silently rewriting the original entry.
--
-- Idempotent: information_schema-guarded ALTER.
-- ===========================================================================

SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema=DATABASE() AND table_name='db_session_treatment_notes'
                  AND column_name='treated_by')=0
  AND (SELECT COUNT(*) FROM information_schema.tables
       WHERE table_schema=DATABASE() AND table_name='db_session_treatment_notes')>0,
  'ALTER TABLE `db_session_treatment_notes`
     ADD COLUMN `treated_by` INT(11) NULL DEFAULT NULL COMMENT ''Physio who gave this session'' AFTER `remarks`,
     ADD COLUMN `treated_by_name` VARCHAR(120) NULL DEFAULT NULL AFTER `treated_by`,
     ADD COLUMN `amended_by` INT(11) NULL DEFAULT NULL COMMENT ''Later editor of the chart'' AFTER `signed_by_name`,
     ADD COLUMN `amended_by_name` VARCHAR(120) NULL DEFAULT NULL AFTER `amended_by`,
     ADD COLUMN `amended_at` DATETIME NULL DEFAULT NULL AFTER `amended_by_name`',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

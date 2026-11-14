-- ---------------------------------------------------------------------------
-- Inpatient / Outpatient marking at billing (2026-10-12)
--
-- A patient is never "converted" between inpatient and outpatient. The care
-- TYPE belongs to the care being billed, and the record that carries it is the
-- CARE EPISODE — so a patient's history reads as a series of spells, some
-- inpatient and some outpatient, with nothing overwritten.
--
--   outpatient  the patient comes in, is seen and goes home. Assessment visits
--               and treatment sessions are outpatient.
--   inpatient   the patient is admitted and stays — the spell the ward module
--               (db_admissions) manages.
--
-- The physiotherapist chooses the type when raising the bill, because that is
-- the moment the care is being committed to a record. The choice is stored on
-- the BILL (db_sales.care_type) so the paperwork is unambiguous, and mirrored
-- onto the care episode (db_care_episodes.episode_type, an existing column
-- that has until now been hardcoded 'outpatient') so the patient profile can
-- group the history by type.
--
-- Adds:
--   db_sales.care_type
--     'inpatient' | 'outpatient'. NULL on bills raised before this migration —
--     those are shown as "Not marked" rather than guessed at.
--
-- Idempotent: ALTER guarded by information_schema checks.
-- ---------------------------------------------------------------------------

SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='care_type')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_sales')>0,
  'ALTER TABLE `db_sales` ADD COLUMN `care_type` VARCHAR(20) NULL DEFAULT NULL COMMENT ''inpatient|outpatient — chosen by the physio when the bill is raised'' AFTER `sales_note`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_sales' AND index_name='idx_store_care_type')=0
  AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='care_type')>0,
  'ALTER TABLE `db_sales` ADD KEY `idx_store_care_type` (`store_id`,`care_type`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
